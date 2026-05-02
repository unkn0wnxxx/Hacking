
```
nmap -A -p- --min-rate 10000 192.168.215.187
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-11 09:50 EST
Nmap scan report for 192.168.215.187
Host is up (0.023s latency).
Not shown: 65505 closed tcp ports (reset)
PORT      STATE    SERVICE       VERSION
53/tcp    open     domain        Simple DNS Plus
80/tcp    open     http          Apache httpd 2.4.48 ((Win64) OpenSSL/1.1.1k PHP/8.0.7)
|_http-title: Access The Event
|_http-server-header: Apache/2.4.48 (Win64) OpenSSL/1.1.1k PHP/8.0.7
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open     kerberos-sec  Microsoft Windows Kerberos (server time: 2025-11-11 14:51:02Z)
135/tcp   open     msrpc         Microsoft Windows RPC
139/tcp   open     netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open     ldap          Microsoft Windows Active Directory LDAP (Domain: access.offsec0., Site: Default-First-Site-Name)
443/tcp   open     ssl/http      Apache httpd 2.4.48 ((Win64) OpenSSL/1.1.1k PHP/8.0.7)
|_ssl-date: TLS randomness does not represent time
| tls-alpn: 
|_  http/1.1
| http-methods: 
|_  Potentially risky methods: TRACE
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
|_http-title: Access The Event
|_http-server-header: Apache/2.4.48 (Win64) OpenSSL/1.1.1k PHP/8.0.7
445/tcp   open     microsoft-ds?
464/tcp   open     kpasswd5?
593/tcp   open     ncacn_http    Microsoft Windows RPC over HTTP 1.0
3268/tcp  open     ldap          Microsoft Windows Active Directory LDAP (Domain: access.offsec0., Site: Default-First-Site-Name)
5985/tcp  open     http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open     mc-nmf        .NET Message Framing
47001/tcp open     http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open     msrpc         Microsoft Windows RPC
49665/tcp open     msrpc         Microsoft Windows RPC
49666/tcp open     msrpc         Microsoft Windows RPC
49668/tcp open     msrpc         Microsoft Windows RPC
49669/tcp open     msrpc         Microsoft Windows RPC
49670/tcp open     ncacn_http    Microsoft Windows RPC over HTTP 1.0
49671/tcp open     msrpc         Microsoft Windows RPC
49674/tcp open     msrpc         Microsoft Windows RPC
49679/tcp open     msrpc         Microsoft Windows RPC
49701/tcp open     msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/11%OT=53%CT=1%CU=32252%PV=Y%DS=4%DC=T%G=Y%TM=69134D
OS:A3%P=x86_64-pc-linux-gnu)SEQ(SP=101%GCD=1%ISR=10B%TI=I%CI=I%TS=U)SEQ(SP=
OS:104%GCD=2%ISR=10A%TI=I%CI=I%TS=U)SEQ(SP=105%GCD=1%ISR=10D%TI=I%CI=I%TS=U
OS:)SEQ(SP=106%GCD=1%ISR=10D%TI=I%CI=I%TS=U)SEQ(SP=FE%GCD=1%ISR=108%TI=I%CI
OS:=I%TS=U)OPS(O1=M578NW8NNS%O2=M578NW8NNS%O3=M578NW8%O4=M578NW8NNS%O5=M578
OS:NW8NNS%O6=M578NNS)WIN(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)EC
OS:N(R=Y%DF=Y%T=80%W=FFFF%O=M578NW8NNS%CC=Y%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=
OS:AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(
OS:R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%
OS:F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G
OS:%RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: Host: SERVER; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2025-11-11T14:52:12
|_  start_date: N/A

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   20.79 ms 192.168.45.1
2   20.77 ms 192.168.45.254
3   20.82 ms 192.168.251.1
4   20.86 ms 192.168.215.187

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 92.50 seconds
```


## HTTP

- GoBuster dirbuster
- GoBuster file extension fuzzing
- feroxbuster
- nikto
- ffuf subdomain enum
- robots.txt

#### Manual Analysis
 - The Nmap Scan provided us with the information that php is utilized.
- Template: TheEvent - v4.6.0
- Potential usernames --> created wordlist.
## LDAP 

DC Name: access.offsec0 --> Map it

## SMB [x]

 - Anonymous Access
 - Guest Access
 - Write Perms
 - Creds in files?

## HTTP

Running on port 5985

- GoBuster dirbuster
- GoBuster file extension fuzzing
- feroxbuster
- nikto
- ffuf subdomain enum
- robots.txt

## .NET Message Framing

Running on port 9389

- ?

## HTTP

Running on port 47001

- GoBuster dirbuster
- GoBuster file extension fuzzing
- feroxbuster
- nikto
- ffuf subdomain enum
- robots.txt