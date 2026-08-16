
## CTF Writeup: DanglingTree

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.56.170                          
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-15 03:15 -0500
Nmap scan report for 10.129.56.170
Host is up (0.030s latency).
Not shown: 65510 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: IIS Windows Server
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-15 08:17:49Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: danglingtree.htb, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.danglingtree.htb, DNS:danglingtree.htb, DNS:DANGLINGTREE
| Not valid before: 2026-08-03T16:32:53
|_Not valid after:  2106-08-03T16:32:53
443/tcp   open  ssl/https?
| tls-alpn: 
|   h2
|_  http/1.1
| ssl-cert: Subject: commonName=danglingtree-DC-CA
| Not valid before: 2026-03-26T05:34:19
|_Not valid after:  2114-03-26T05:44:18
|_ssl-date: TLS randomness does not represent time
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: danglingtree.htb, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.danglingtree.htb, DNS:danglingtree.htb, DNS:DANGLINGTREE
| Not valid before: 2026-08-03T16:32:53
|_Not valid after:  2106-08-03T16:32:53
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: danglingtree.htb, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.danglingtree.htb, DNS:danglingtree.htb, DNS:DANGLINGTREE
| Not valid before: 2026-08-03T16:32:53
|_Not valid after:  2106-08-03T16:32:53
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: danglingtree.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.danglingtree.htb, DNS:danglingtree.htb, DNS:DANGLINGTREE
| Not valid before: 2026-08-03T16:32:53
|_Not valid after:  2106-08-03T16:32:53
|_ssl-date: TLS randomness does not represent time
3389/tcp  open  ms-wbt-server
| rdp-ntlm-info: 
|   Target_Name: DANGLINGTREE
|   NetBIOS_Domain_Name: DANGLINGTREE
|   NetBIOS_Computer_Name: DC
|   DNS_Domain_Name: danglingtree.htb
|   DNS_Computer_Name: dc.danglingtree.htb
|   DNS_Tree_Name: danglingtree.htb
|   Product_Version: 10.0.26100
|_  System_Time: 2026-08-15T08:19:29+00:00
| ssl-cert: Subject: commonName=dc.danglingtree.htb
| Not valid before: 2026-03-25T05:48:29
|_Not valid after:  2026-09-24T05:48:29
|_ssl-date: TLS randomness does not represent time
6600/tcp  open  ssl/mshvlm?
| ssl-cert: Subject: commonName=dc.danglingtree.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:dc.danglingtree.htb
| Not valid before: 2026-03-26T05:41:20
|_Not valid after:  2027-03-26T05:41:20
| fingerprint-strings: 
|   GetRequest: 
|     HTTP/1.1 403 Forbidden
|     Connection: close
|     Date: Sat, 15 Aug 2026 08:18:06 GMT
|     Cache-Control: no-store
|     Cache-Control: max-age=0
|     Pragma: no-cache
|     Set-Cookie: .AspNetCore.Antiforgery.7Eyhia2WOxE=CfDJ8HsozULo80ZBsxvkNAKguomKoFCxtRFcNhq8nZ4NAKTL42p2ERryX7R9_1vXWmaPmDgWGKRXSQOQ_ocI6Nsu8KbPrL9UgVHQbzVBxFUyN8t2fY4SjYK28YvxfOg0YJUadH16e44Gc_wV1OL6yHwohg8; path=/; secure; samesite=none; Partitioned
|     Set-Cookie: WAC-SESSION=93cab296fe8d450ca2f8c1d10e9adcda; expires=Sun, 16 Aug 2026 08:18:06 GMT; path=/; secure; samesite=lax; httponly
|     Set-Cookie: WAC-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/
|     Set-Cookie: WAC-AAD=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/
|     Set-Cookie: XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/
|     Strict-Transport-Security: max-age=5184000; includeSubDomains; preload
|     <!DOCTYPE html>
|     <html lang="en" xmlns="http://www.w3.org/1999/xhtml">
|     <head
|   HTTPOptions: 
|     HTTP/1.1 403 Forbidden
|     Connection: close
|     Date: Sat, 15 Aug 2026 08:18:06 GMT
|     Cache-Control: no-store
|     Cache-Control: max-age=0
|     Pragma: no-cache
|     Set-Cookie: .AspNetCore.Antiforgery.7Eyhia2WOxE=CfDJ8HsozULo80ZBsxvkNAKguonh2EVrovQCYOuC1cDo2mFvlKG7FhvqyXe5z9ntjcVrANvUyq3fOJUWXP3PaOobORfLR289Q8oGDbjw8zdO1fj8XaQWnnoufsEw-iLmblSJj2z4uHwbHkhoOTPoUv2vwuY; path=/; secure; samesite=none; Partitioned
|     Set-Cookie: WAC-SESSION=122b2003bc1648f18b7a97ac351ef921; expires=Sun, 16 Aug 2026 08:18:06 GMT; path=/; secure; samesite=lax; httponly
|     Set-Cookie: WAC-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/
|     Set-Cookie: WAC-AAD=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/
|     Set-Cookie: XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/
|     Strict-Transport-Security: max-age=5184000; includeSubDomains; preload
|     <!DOCTYPE html>
|     <html lang="en" xmlns="http://www.w3.org/1999/xhtml">
|_    <head
| tls-alpn: 
|   h2
|_  http/1.1
|_ssl-date: TLS randomness does not represent time
9389/tcp  open  mc-nmf        .NET Message Framing
49664/tcp open  msrpc         Microsoft Windows RPC
49677/tcp open  msrpc         Microsoft Windows RPC
49679/tcp open  msrpc         Microsoft Windows RPC
49681/tcp open  msrpc         Microsoft Windows RPC
49682/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49690/tcp open  msrpc         Microsoft Windows RPC
49709/tcp open  msrpc         Microsoft Windows RPC
49723/tcp open  msrpc         Microsoft Windows RPC
49757/tcp open  msrpc         Microsoft Windows RPC
2 services unrecognized despite returning data. If you know the service/version, please submit the following fingerprints at https://nmap.org/cgi-bin/submit.cgi?new-service :
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port3389-TCP:V=7.99%I=7%D=8/15%Time=6A8020B1%P=x86_64-pc-linux-gnu%r(Te
SF:rminalServerCookie,13,"\x03\0\0\x13\x0e\xd0\0\0\x124\0\x02\?\x08\0\x02\
SF:0\0\0");
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port6600-TCP:V=7.99%T=SSL%I=7%D=8/15%Time=6A8020BD%P=x86_64-pc-linux-gn
SF:u%r(GetRequest,1000,"HTTP/1\.1\x20403\x20Forbidden\r\nConnection:\x20cl
SF:ose\r\nDate:\x20Sat,\x2015\x20Aug\x202026\x2008:18:06\x20GMT\r\nCache-C
SF:ontrol:\x20no-store\r\nCache-Control:\x20max-age=0\r\nPragma:\x20no-cac
SF:he\r\nSet-Cookie:\x20\.AspNetCore\.Antiforgery\.7Eyhia2WOxE=CfDJ8HsozUL
SF:o80ZBsxvkNAKguomKoFCxtRFcNhq8nZ4NAKTL42p2ERryX7R9_1vXWmaPmDgWGKRXSQOQ_o
SF:cI6Nsu8KbPrL9UgVHQbzVBxFUyN8t2fY4SjYK28YvxfOg0YJUadH16e44Gc_wV1OL6yHwoh
SF:g8;\x20path=/;\x20secure;\x20samesite=none;\x20Partitioned\r\nSet-Cooki
SF:e:\x20WAC-SESSION=93cab296fe8d450ca2f8c1d10e9adcda;\x20expires=Sun,\x20
SF:16\x20Aug\x202026\x2008:18:06\x20GMT;\x20path=/;\x20secure;\x20samesite
SF:=lax;\x20httponly\r\nSet-Cookie:\x20WAC-TOKEN=;\x20expires=Thu,\x2001\x
SF:20Jan\x201970\x2000:00:00\x20GMT;\x20path=/\r\nSet-Cookie:\x20WAC-AAD=;
SF:\x20expires=Thu,\x2001\x20Jan\x201970\x2000:00:00\x20GMT;\x20path=/\r\n
SF:Set-Cookie:\x20XSRF-TOKEN=;\x20expires=Thu,\x2001\x20Jan\x201970\x2000:
SF:00:00\x20GMT;\x20path=/\r\nStrict-Transport-Security:\x20max-age=518400
SF:0;\x20includeSubDomains;\x20preload\r\n\r\n<!DOCTYPE\x20html>\r\n<html\
SF:x20lang=\"en\"\x20xmlns=\"http://www\.w3\.org/1999/xhtml\">\r\n\r\n<hea
SF:d")%r(HTTPOptions,1000,"HTTP/1\.1\x20403\x20Forbidden\r\nConnection:\x2
SF:0close\r\nDate:\x20Sat,\x2015\x20Aug\x202026\x2008:18:06\x20GMT\r\nCach
SF:e-Control:\x20no-store\r\nCache-Control:\x20max-age=0\r\nPragma:\x20no-
SF:cache\r\nSet-Cookie:\x20\.AspNetCore\.Antiforgery\.7Eyhia2WOxE=CfDJ8Hso
SF:zULo80ZBsxvkNAKguonh2EVrovQCYOuC1cDo2mFvlKG7FhvqyXe5z9ntjcVrANvUyq3fOJU
SF:WXP3PaOobORfLR289Q8oGDbjw8zdO1fj8XaQWnnoufsEw-iLmblSJj2z4uHwbHkhoOTPoUv
SF:2vwuY;\x20path=/;\x20secure;\x20samesite=none;\x20Partitioned\r\nSet-Co
SF:okie:\x20WAC-SESSION=122b2003bc1648f18b7a97ac351ef921;\x20expires=Sun,\
SF:x2016\x20Aug\x202026\x2008:18:06\x20GMT;\x20path=/;\x20secure;\x20sames
SF:ite=lax;\x20httponly\r\nSet-Cookie:\x20WAC-TOKEN=;\x20expires=Thu,\x200
SF:1\x20Jan\x201970\x2000:00:00\x20GMT;\x20path=/\r\nSet-Cookie:\x20WAC-AA
SF:D=;\x20expires=Thu,\x2001\x20Jan\x201970\x2000:00:00\x20GMT;\x20path=/\
SF:r\nSet-Cookie:\x20XSRF-TOKEN=;\x20expires=Thu,\x2001\x20Jan\x201970\x20
SF:00:00:00\x20GMT;\x20path=/\r\nStrict-Transport-Security:\x20max-age=518
SF:4000;\x20includeSubDomains;\x20preload\r\n\r\n<!DOCTYPE\x20html>\r\n<ht
SF:ml\x20lang=\"en\"\x20xmlns=\"http://www\.w3\.org/1999/xhtml\">\r\n\r\n<
SF:head");
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-08-15T08:19:30
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 271.51 seconds
```

The target seems to be an DC. The TCP Scan reveals interesting information about the FQDN of the target dc.danglingtree.htb, the domain danglingtree.htb & the hostname dc. Let's map them to the target ip address in our local dns file.

```
echo "10.129.56.170 dc.danglingtree.htb danglingtree.htb dc" | tee -a /etc/hosts
```

Started with enumerating if anonymous or guest user auth is enabled. Guest user wasn't disabled and I was able to enumerate SMB Shares. Found one non-default SMB Share called "IT" for which I have read permissions.

```
nxc smb danglingtree.htb -u 'guest' -p '' --shares
```

Connected to the IT SMB Share & downloaded all information onto my local machine.

```
smbclient \\\\danglingtree.htb/IT -U guest        
Password for [WORKGROUP\guest]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Sat Apr  4 20:05:09 2026
  ..                                  D        0  Sat Apr  4 19:57:30 2026
  Security                            D        0  Sat Apr  4 20:05:20 2026

                7062015 blocks of size 4096. 2248415 blocks available
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

Opened up the .pdf file.

```
evince DanglingTree_RoE_Assessment.pdf
```

The .PDF seems to be an KickOff-Protocol for an Security Audit of "DanglingTree Enterprise". We get new domain credentials provided.

```
anderson.w:R3dT3am@Acc3ss#01
```

Enumerated domain users and stored the output in an newusers.txt file on my local machine.

```
nxc smb danglingtree.htb -u 'anderson.w' -p 'R3dT3am@Acc3ss#01' --rid-brute > newusers.txt
```

Formatted the output accordingly for future bruteforcing purposes.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Downloaded domain information using BloodHound.

```
bloodhound-python -u anderson.w -p 'R3dT3am@Acc3ss#01' -ns 10.129.56.170 -d danglingtree.htb -c all
```

Started up bloodhound on my local machine.

```
bloodhound-start
```

Unfortunately I can't enumerate LDAP/authenticate against LDAP due to lack of permissions.

```
ldapsearch -H "ldap://10.129.56.170" -D anderson.w@danglingtree.htb -w 'R3dT3am@Acc3ss#01' -b "dc=danglingtree,dc=htb" "*" > ldapsearch.txt
ldap_bind: Strong(er) authentication required (8)
        additional info: 00002028: LdapErr: DSID-0C09035A, comment: The server requires binds to turn on integrity checking if SSL\TLS are not already active on the connection, data 0, v65f4
```

Decided to proceed with uploading bloodhound information.

Marked my current user "anderson.w" as owned & identified that he seems to be part of the Remote Management Group, this group can partitially connect to the DC via WinRM or RDP.

Tried connecting to the DC via RDP, but this didn't work. Since it uses kerberos authentication instead of normal password or ntlm auth.

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:anderson.w /p:'R3dT3am@Acc3ss#01' /v:10.129.56.170 /w:1600 /h:800 /drive:test,/home/saitama/Desktop
```

Let's request an TGT for our current user.

```
impacket-getTGT danglingtree.htb/anderson.w@10.129.56.170
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
[*] Saving ticket in anderson.w@10.129.56.170.ccache
```

Exported the ticket in our kerberos cache.

```
export KRB5CCNAME=$(pwd)/anderson.w@10.129.56.170.ccache
```

Let's now utilize kerberos auth and connect to the DC via RDP.

This didn't work. Spraying his credentials against RDP also showed us that it's not possible to connect to the DC via RDP.

```
nxc rdp danglingtree.htb -u anderson.w -p 'R3dT3am@Acc3ss#01'   
RDP         10.129.56.170   3389   DC               [*] Windows 10 or Windows Server 2016 Build 26100 (name:DC) (domain:danglingtree.htb) (nla:True)
RDP         10.129.56.170   3389   DC               [+] danglingtree.htb\anderson.w:R3dT3am@Acc3ss#01
```

Let's check out the webpages on port 80,443 & 6600.

Upon accessing the webpage on port 6600 it seems to be an Windows Admin Panel.

Logged in with the credentials.

This looks like an Azure Control Panel.

Identified an Public RCE Exploit for the Windows Admin Center: CVE-2026-32196

```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```




```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```