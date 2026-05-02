
An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.158.55
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-07 10:30 EST
Warning: 192.168.158.55 giving up on port because retransmission cap hit (10).
Nmap scan report for 192.168.158.55
Host is up (0.023s latency).
Not shown: 65502 closed tcp ports (reset)
PORT      STATE    SERVICE       VERSION
21/tcp    open     ftp           FileZilla ftpd 0.9.41 beta
| ftp-syst: 
|_  SYST: UNIX emulated by FileZilla
80/tcp    open     http          Apache httpd 2.4.43 ((Win64) OpenSSL/1.1.1g PHP/7.4.6)
| http-title: Welcome to XAMPP
|_Requested resource was http://192.168.158.55/dashboard/
|_http-server-header: Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.6
135/tcp   open     msrpc         Microsoft Windows RPC
139/tcp   open     netbios-ssn   Microsoft Windows netbios-ssn
443/tcp   open     ssl/http      Apache httpd 2.4.43 ((Win64) OpenSSL/1.1.1g PHP/7.4.6)
|_ssl-date: TLS randomness does not represent time
|_http-server-header: Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.6
| tls-alpn: 
|_  http/1.1
| http-title: Welcome to XAMPP
|_Requested resource was https://192.168.158.55/dashboard/
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
445/tcp   open     microsoft-ds?
3306/tcp  open     mysql         MariaDB 10.3.24 or later (unauthorized)
5040/tcp  open     unknown
49664/tcp open     msrpc         Microsoft Windows RPC
49665/tcp open     msrpc         Microsoft Windows RPC
49666/tcp open     msrpc         Microsoft Windows RPC
49667/tcp open     msrpc         Microsoft Windows RPC
49668/tcp open     msrpc         Microsoft Windows RPC
49669/tcp open     msrpc         Microsoft Windows RPC
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 10|2019|7|2008|8.1|XP (98%)
OS CPE: cpe:/o:microsoft:windows_10 cpe:/o:microsoft:windows_server_2019 cpe:/o:microsoft:windows_7 cpe:/o:microsoft:windows_server_2008:r2 cpe:/o:microsoft:windows_8.1 cpe:/o:microsoft:windows_xp::sp3
Aggressive OS guesses: Microsoft Windows 10 1909 - 2004 (98%), Microsoft Windows 10 1909 (91%), Microsoft Windows Server 2019 (90%), Microsoft Windows 10 1903 - 21H1 (90%), Microsoft Windows 10 1709 - 21H2 (90%), Microsoft Windows 7 SP1 or Windows Server 2008 R2 or Windows 8.1 (89%), Microsoft Windows XP SP3 (88%), Microsoft Windows 10 20H2 (88%), Microsoft Windows 10 20H2 - 21H1 (88%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-11-07T15:33:07
|_  start_date: N/A

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   23.62 ms 192.168.45.1
2   23.54 ms 192.168.45.254
3   21.81 ms 192.168.251.1
4   22.06 ms 192.168.158.55

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 197.40 seconds
```

## FTP

Running on default port. Version: FileZilla ftpd 0.9.41 beta

- Anonymous Access [x]
- Write Permission [x]
- Enumerating Files [x]

## HTTP 80

Xampp Server, which runs php --> Check phpinfo.php
/dashboard exposed.

- GoBuster dirbuster wordlist [x]
- GoBuster file extension fuzzing [x]
- dirsearch [x]
- feroxbuster [x]
- nikto
- robots.txt [x]
- Enumerating subdomains [x]
- Manual Analysis

## HTTPS 443

Is it the same webserver as in HTTP? If no,  [x]

 GoBuster dirbuster wordlist
- GoBuster file extension fuzzing
- dirsearch
- feroxbuster
- nikto
- robots.txt
- Enumerating subdomains
- Manual Analysis

## SMB

- Anonymously enumerating shares
- Enumerating Shares with "guest" user.
- Writable Share Perms

## MYSQL

Version: MariaDB 10.3.24 or later (unauthorized)



