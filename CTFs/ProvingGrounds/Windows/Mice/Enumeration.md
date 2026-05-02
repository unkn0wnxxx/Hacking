

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

## Emote Remote Mouse

Running on port 1978

- Public CVE's
- Manual Analysis

## unisql java

Running on port 1979

- Public CVE's
- Manual Analysis

## pearldoc-xact?

Running on port 1980

- Public CVE's
- Manual Analysis

## RDP

- ?

## Pando-pub

Running on default port.

- ?