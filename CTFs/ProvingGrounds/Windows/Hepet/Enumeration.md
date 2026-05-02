

```
nmap -A -p- --min-rate 10000 192.168.145.140
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-10 05:28 EST
Nmap scan report for 192.168.145.140
Host is up (0.024s latency).
Not shown: 65512 closed tcp ports (reset)
PORT      STATE SERVICE        VERSION
25/tcp    open  smtp           Mercury/32 smtpd (Mail server account Maiser)
|_smtp-commands: localhost Hello nmap.scanme.org; ESMTPs are:, TIME
79/tcp    open  finger         Mercury/32 fingerd
| finger: Login: Admin         Name: Mail System Administrator\x0D
| \x0D
|_[No profile information]\x0D
105/tcp   open  ph-addressbook Mercury/32 PH addressbook server
106/tcp   open  pop3pw         Mercury/32 poppass service
110/tcp   open  pop3           Mercury/32 pop3d
|_pop3-capabilities: USER UIDL APOP EXPIRE(NEVER) TOP
135/tcp   open  msrpc          Microsoft Windows RPC
139/tcp   open  netbios-ssn    Microsoft Windows netbios-ssn
143/tcp   open  imap           Mercury/32 imapd 4.62
|_imap-capabilities: IMAP4rev1 CAPABILITY X-MERCURY-1A0001 complete OK AUTH=PLAIN
443/tcp   open  ssl/http       Apache httpd 2.4.46 ((Win64) OpenSSL/1.1.1g PHP/7.3.23)
| tls-alpn: 
|_  http/1.1
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
|_http-server-header: Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.3.23
|_http-title: Time Travel Company Page
| http-methods: 
|_  Potentially risky methods: TRACE
445/tcp   open  microsoft-ds?
2224/tcp  open  http           Mercury/32 httpd
|_http-title: Mercury HTTP Services
5040/tcp  open  unknown
7680/tcp  open  pando-pub?
8000/tcp  open  http           Apache httpd 2.4.46 ((Win64) OpenSSL/1.1.1g PHP/7.3.23)
|_http-open-proxy: Proxy might be redirecting requests
|_http-server-header: Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.3.23
|_http-title: Time Travel Company Page
| http-methods: 
|_  Potentially risky methods: TRACE
11100/tcp open  vnc            VNC (protocol 3.8)
| vnc-info: 
|   Protocol version: 3.8
|   Security types: 
|_    Unknown security type (40)
20001/tcp open  ftp            FileZilla ftpd 0.9.41 beta
| ftp-syst: 
|_  SYST: UNIX emulated by FileZilla
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
| -r--r--r-- 1 ftp ftp            312 Oct 20  2020 .babelrc
| -r--r--r-- 1 ftp ftp            147 Oct 20  2020 .editorconfig
| -r--r--r-- 1 ftp ftp             23 Oct 20  2020 .eslintignore
| -r--r--r-- 1 ftp ftp            779 Oct 20  2020 .eslintrc.js
| -r--r--r-- 1 ftp ftp            167 Oct 20  2020 .gitignore
| -r--r--r-- 1 ftp ftp            228 Oct 20  2020 .postcssrc.js
| -r--r--r-- 1 ftp ftp            346 Oct 20  2020 .tern-project
| drwxr-xr-x 1 ftp ftp              0 Oct 20  2020 build
| drwxr-xr-x 1 ftp ftp              0 Oct 20  2020 config
| -r--r--r-- 1 ftp ftp           1376 Oct 20  2020 index.html
| -r--r--r-- 1 ftp ftp         425010 Oct 20  2020 package-lock.json
| -r--r--r-- 1 ftp ftp           2454 Oct 20  2020 package.json
| -r--r--r-- 1 ftp ftp           1100 Oct 20  2020 README.md
| drwxr-xr-x 1 ftp ftp              0 Oct 20  2020 src
| drwxr-xr-x 1 ftp ftp              0 Oct 20  2020 static
|_-r--r--r-- 1 ftp ftp            127 Oct 20  2020 _redirects
|_ftp-bounce: bounce working!
33006/tcp open  mysql          MariaDB 10.3.24 or later (unauthorized)
49664/tcp open  msrpc          Microsoft Windows RPC
49665/tcp open  msrpc          Microsoft Windows RPC
49666/tcp open  msrpc          Microsoft Windows RPC
49667/tcp open  msrpc          Microsoft Windows RPC
49668/tcp open  msrpc          Microsoft Windows RPC
49669/tcp open  msrpc          Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/10%OT=25%CT=1%CU=38458%PV=Y%DS=4%DC=T%G=Y%TM=6911BF
OS:09%P=x86_64-pc-linux-gnu)SEQ(SP=101%GCD=1%ISR=10B%TI=I%CI=I%TS=U)SEQ(SP=
OS:101%GCD=1%ISR=10E%TI=I%CI=I%TS=U)SEQ(SP=103%GCD=1%ISR=10A%TI=I%CI=I%TS=U
OS:)SEQ(SP=10A%GCD=1%ISR=10C%TI=I%CI=I%TS=U)SEQ(SP=FC%GCD=1%ISR=108%TI=I%CI
OS:=I%TS=U)OPS(O1=M578NW0NNS%O2=M578NW0NNS%O3=M578NW0%O4=M578NW0NNS%O5=M578
OS:NW0NNS%O6=M578NNS)WIN(W1=4000%W2=4000%W3=4000%W4=4000%W5=4000%W6=4000)EC
OS:N(R=Y%DF=Y%T=80%W=4000%O=M578NW0NNS%CC=N%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=
OS:AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(
OS:R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%
OS:F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G
OS:%RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: Host: localhost; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-11-10T10:31:23
|_  start_date: N/A

TRACEROUTE (using port 3306/tcp)
HOP RTT      ADDRESS
1   22.05 ms 192.168.45.1
2   21.60 ms 192.168.45.254
3   22.09 ms 192.168.251.1
4   22.20 ms 192.168.145.140

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 195.49 seconds
```


## SMTP

Mercury/32 smtpd (Mail server account Maiser)

- Connecting to SMTP and see if default credentials work.
- Analyse mails --> Creds?

## Finger

Admin Panel of the SMTP Server
- Connecting to SMTP and see if default credentials work.
- Analyse mails --> Creds?

## IMAP 

- Connect to IMAP
- Checking Emails

## SMB

- Anonymous Access [x]
- Guest Access [x]
- Write Perms
- Important Files

## HTTPS

- GoBuster Endpoint Fuzzing dirbuster
- GoBuster File Extensions
- feroxbuster
- subdomain enum
- dirsearch
- nikto
- robots.txt
- Manual Analysis

## HTTP

Running on port 2224

- GoBuster Endpoint Fuzzing dirbuster
- GoBuster File Extensions
- feroxbuster
- subdomain enum
- dirsearch
- nikto
- robots.txt
- Manual Analysis

## HTTP

Running on port 8000

- GoBuster Endpoint Fuzzing dirbuster
- GoBuster File Extensions
- feroxbuster
- subdomain enum
- dirsearch
- nikto
- robots.txt
- Manual Analysis
## VNC

- Connecting with vncviewer possible?

## FTP

Running on port 20001

- Anonymous Access possible?
- Write Perms?
- Check for important files

## MySQL

Running on port 33006

- ?