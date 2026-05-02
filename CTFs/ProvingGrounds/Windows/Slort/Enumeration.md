
```
nmap -A -p- --min-rate 10000 192.168.210.53
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-08 11:31 EST
Nmap scan report for 192.168.210.53
Host is up (0.023s latency).
Not shown: 65520 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
21/tcp    open  ftp           FileZilla ftpd 0.9.41 beta
| ftp-syst: 
|_  SYST: UNIX emulated by FileZilla
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
3306/tcp  open  mysql         MariaDB 10.3.24 or later (unauthorized)
4443/tcp  open  http          Apache httpd 2.4.43 ((Win64) OpenSSL/1.1.1g PHP/7.4.6)
|_http-server-header: Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.6
| http-title: Welcome to XAMPP
|_Requested resource was http://192.168.210.53:4443/dashboard/
5040/tcp  open  unknown
7680/tcp  open  pando-pub?
8080/tcp  open  http          Apache httpd 2.4.43 ((Win64) OpenSSL/1.1.1g PHP/7.4.6)
| http-title: Welcome to XAMPP
|_Requested resource was http://192.168.210.53:8080/dashboard/
|_http-server-header: Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.6
|_http-open-proxy: Proxy might be redirecting requests
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/8%OT=21%CT=1%CU=34299%PV=Y%DS=4%DC=T%G=Y%TM=690F711
OS:7%P=x86_64-pc-linux-gnu)SEQ(SP=100%GCD=1%ISR=10D%TI=I%CI=I%TS=U)SEQ(SP=1
OS:03%GCD=1%ISR=10C%TI=I%CI=I%TS=U)SEQ(SP=103%GCD=1%ISR=10D%TI=I%CI=I%TS=U)
OS:SEQ(SP=104%GCD=1%ISR=106%TI=I%CI=I%TS=U)SEQ(SP=F8%GCD=1%ISR=110%TI=I%CI=
OS:I%TS=U)OPS(O1=M578NW8NNS%O2=M578NW8NNS%O3=M578NW8%O4=M578NW8NNS%O5=M578N
OS:W8NNS%O6=M578NNS)WIN(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)ECN
OS:(R=Y%DF=Y%T=80%W=FFFF%O=M578NW8NNS%CC=N%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=A
OS:S%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R
OS:=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%F
OS:=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G%
OS:RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-11-08T16:34:15
|_  start_date: N/A

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   21.51 ms 192.168.45.1
2   21.46 ms 192.168.45.254
3   21.51 ms 192.168.251.1
4   21.57 ms 192.168.210.53

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 196.56 seconds
```

## FTP

- Anonymous Access [x]
- Write Permissions
- Valuable Files

## SMB

- Anonymous Access [x]
- Guest Access [x]
- Write Permissions
- Valuable Files

## MySQL



## HTTP

Running on port 4443
/phpmyadmin & phpinfo.php displayable
64-bit architecture
/phpmyadmin not accessible.


Enumerated user "rupert".

- GoBuster dirbuster --> /site directory (webpage)
- GoBuster File Extensions
- Subdomain Enum Ffuf
- Robots.txt
- nikto
- feroxbuster
- Manual Analysis

## HTTP

Running on port 8080
Xamppserver running PHP --> phpmyadmin & phpinfo.php

- GoBuster dirbuster
- GoBuster File Extensions
- Subdomain Enum Ffuf
- Robots.txt
- nikto
- feroxbuster
- Manual Analysis
