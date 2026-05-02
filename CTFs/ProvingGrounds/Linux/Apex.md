

## Reconaissance

```
nmap -A -p- --min-rate 10000 192.168.130.145
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-24 23:26 EST
Nmap scan report for 192.168.130.145
Host is up (0.043s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT     STATE SERVICE     VERSION
80/tcp   open  http        Apache httpd 2.4.29 ((Ubuntu))
|_http-title: APEX Hospital
|_http-server-header: Apache/2.4.29 (Ubuntu)
445/tcp  open  netbios-ssn Samba smbd 4.7.6-Ubuntu (workgroup: WORKGROUP)
3306/tcp open  mysql       MariaDB 5.5.5-10.1.48
| mysql-info: 
|   Protocol: 10
|   Version: 5.5.5-10.1.48-MariaDB-0ubuntu0.18.04.1
|   Thread ID: 33
|   Capabilities flags: 63487
|   Some Capabilities: Support41Auth, LongColumnFlag, ODBCClient, FoundRows, ConnectWithDatabase, SupportsCompression, LongPassword, Speaks41ProtocolNew, IgnoreSpaceBeforeParenthesis, Speaks41ProtocolOld, InteractiveClient, SupportsTransactions, SupportsLoadDataLocal, IgnoreSigpipes, DontAllowDatabaseTableColumn, SupportsMultipleResults, SupportsMultipleStatments, SupportsAuthPlugins
|   Status: Autocommit
|   Salt: fi]w`[)UQw$J*/O~METT
|_  Auth Plugin Name: mysql_native_password
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (97%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (97%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.19 - 5.15 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Host: APEX

Host script results:
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-12-25T04:26:41
|_  start_date: N/A
| smb-os-discovery: 
|   OS: Windows 6.1 (Samba 4.7.6-Ubuntu)
|   Computer name: apex
|   NetBIOS computer name: APEX\x00
|   Domain name: \x00
|   FQDN: apex
|_  System time: 2025-12-24T23:26:39-05:00
|_clock-skew: mean: 1h40m00s, deviation: 2h53m14s, median: 0s

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   30.24 ms 192.168.45.1
2   30.16 ms 192.168.45.254
3   30.26 ms 192.168.251.1
4   30.95 ms 192.168.130.145

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 64.34 seconds
```

## HTTP

- Enumerating Endpoints
- Enumerating Subdomains
- robots.txt
- Vulnerability Assessment

## SMB

- Anonymous Access
- Guest Access
- Writable Share
- Important Files
- enum4linux


## MYSQL

- Login unauthenticated
