
```
nmap -A -p- --min-rate 10000 192.168.210.179
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-08 15:19 EST
Nmap scan report for 192.168.210.179
Host is up (0.023s latency).
Not shown: 65522 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
22/tcp    open  ssh           Bitvise WinSSHD 8.48 (FlowSsh 8.48; protocol 2.0; non-commercial use)
| ssh-hostkey: 
|   3072 21:25:f0:53:b4:99:0f:34:de:2d:ca:bc:5d:fe:20:ce (RSA)
|_  384 e7:96:f3:6a:d8:92:07:5a:bf:37:06:86:0a:31:73:19 (ECDSA)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
5040/tcp  open  unknown
7680/tcp  open  pando-pub?
8080/tcp  open  http-proxy
|_http-title: Argus Surveillance DVR
| fingerprint-strings: 
|   GetRequest, HTTPOptions: 
|     HTTP/1.1 200 OK
|     Connection: Keep-Alive
|     Keep-Alive: timeout=15, max=4
|     Content-Type: text/html
|     Content-Length: 985
|     <HTML>
|     <HEAD>
|     <TITLE>
|     Argus Surveillance DVR
|     </TITLE>
|     <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
|     <meta name="GENERATOR" content="Actual Drawing 6.0 (http://www.pysoft.com) [PYSOFTWARE]">
|     <frameset frameborder="no" border="0" rows="75,*,88">
|     <frame name="Top" frameborder="0" scrolling="auto" noresize src="CamerasTopFrame.html" marginwidth="0" marginheight="0"> 
|     <frame name="ActiveXFrame" frameborder="0" scrolling="auto" noresize src="ActiveXIFrame.html" marginwidth="0" marginheight="0">
|     <frame name="CamerasTable" frameborder="0" scrolling="auto" noresize src="CamerasBottomFrame.html" marginwidth="0" marginheight="0"> 
|     <noframes>
|     <p>This page uses frames, but your browser doesn't support them.</p>
|_    </noframes>
|_http-generator: Actual Drawing 6.0 (http://www.pysoft.com) [PYSOFTWARE]
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port8080-TCP:V=7.95%I=7%D=11/8%Time=690FA5DB%P=x86_64-pc-linux-gnu%r(Ge
SF:tRequest,451,"HTTP/1\.1\x20200\x20OK\r\nConnection:\x20Keep-Alive\r\nKe
SF:ep-Alive:\x20timeout=15,\x20max=4\r\nContent-Type:\x20text/html\r\nCont
SF:ent-Length:\x20985\r\n\r\n<HTML>\r\n<HEAD>\r\n<TITLE>\r\nArgus\x20Surve
SF:illance\x20DVR\r\n</TITLE>\r\n\r\n<meta\x20http-equiv=\"Content-Type\"\
SF:x20content=\"text/html;\x20charset=ISO-8859-1\">\r\n<meta\x20name=\"GEN
SF:ERATOR\"\x20content=\"Actual\x20Drawing\x206\.0\x20\(http://www\.pysoft
SF:\.com\)\x20\[PYSOFTWARE\]\">\r\n\r\n<frameset\x20frameborder=\"no\"\x20
SF:border=\"0\"\x20rows=\"75,\*,88\">\r\n\x20\x20<frame\x20name=\"Top\"\x2
SF:0frameborder=\"0\"\x20scrolling=\"auto\"\x20noresize\x20src=\"CamerasTo
SF:pFrame\.html\"\x20marginwidth=\"0\"\x20marginheight=\"0\">\x20\x20\r\n\
SF:x20\x20<frame\x20name=\"ActiveXFrame\"\x20frameborder=\"0\"\x20scrollin
SF:g=\"auto\"\x20noresize\x20src=\"ActiveXIFrame\.html\"\x20marginwidth=\"
SF:0\"\x20marginheight=\"0\">\r\n\x20\x20<frame\x20name=\"CamerasTable\"\x
SF:20frameborder=\"0\"\x20scrolling=\"auto\"\x20noresize\x20src=\"CamerasB
SF:ottomFrame\.html\"\x20marginwidth=\"0\"\x20marginheight=\"0\">\x20\x20\
SF:r\n\x20\x20<noframes>\r\n\x20\x20\x20\x20<p>This\x20page\x20uses\x20fra
SF:mes,\x20but\x20your\x20browser\x20doesn't\x20support\x20them\.</p>\r\n\
SF:x20\x20</noframes>\r")%r(HTTPOptions,451,"HTTP/1\.1\x20200\x20OK\r\nCon
SF:nection:\x20Keep-Alive\r\nKeep-Alive:\x20timeout=15,\x20max=4\r\nConten
SF:t-Type:\x20text/html\r\nContent-Length:\x20985\r\n\r\n<HTML>\r\n<HEAD>\
SF:r\n<TITLE>\r\nArgus\x20Surveillance\x20DVR\r\n</TITLE>\r\n\r\n<meta\x20
SF:http-equiv=\"Content-Type\"\x20content=\"text/html;\x20charset=ISO-8859
SF:-1\">\r\n<meta\x20name=\"GENERATOR\"\x20content=\"Actual\x20Drawing\x20
SF:6\.0\x20\(http://www\.pysoft\.com\)\x20\[PYSOFTWARE\]\">\r\n\r\n<frames
SF:et\x20frameborder=\"no\"\x20border=\"0\"\x20rows=\"75,\*,88\">\r\n\x20\
SF:x20<frame\x20name=\"Top\"\x20frameborder=\"0\"\x20scrolling=\"auto\"\x2
SF:0noresize\x20src=\"CamerasTopFrame\.html\"\x20marginwidth=\"0\"\x20marg
SF:inheight=\"0\">\x20\x20\r\n\x20\x20<frame\x20name=\"ActiveXFrame\"\x20f
SF:rameborder=\"0\"\x20scrolling=\"auto\"\x20noresize\x20src=\"ActiveXIFra
SF:me\.html\"\x20marginwidth=\"0\"\x20marginheight=\"0\">\r\n\x20\x20<fram
SF:e\x20name=\"CamerasTable\"\x20frameborder=\"0\"\x20scrolling=\"auto\"\x
SF:20noresize\x20src=\"CamerasBottomFrame\.html\"\x20marginwidth=\"0\"\x20
SF:marginheight=\"0\">\x20\x20\r\n\x20\x20<noframes>\r\n\x20\x20\x20\x20<p
SF:>This\x20page\x20uses\x20frames,\x20but\x20your\x20browser\x20doesn't\x
SF:20support\x20them\.</p>\r\n\x20\x20</noframes>\r");
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/8%OT=22%CT=1%CU=44072%PV=Y%DS=4%DC=T%G=Y%TM=690FA69
OS:B%P=x86_64-pc-linux-gnu)SEQ(SP=101%GCD=1%ISR=106%TI=I%CI=I%TS=U)SEQ(SP=1
OS:02%GCD=1%ISR=10A%TI=I%CI=I%TS=U)SEQ(SP=105%GCD=1%ISR=10C%TI=I%CI=I%TS=U)
OS:SEQ(SP=107%GCD=1%ISR=10E%TI=I%CI=I%TS=U)SEQ(SP=FE%GCD=1%ISR=108%TI=I%CI=
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
|   date: 2025-11-08T20:22:27
|_  start_date: N/A

TRACEROUTE (using port 554/tcp)
HOP RTT      ADDRESS
1   21.03 ms 192.168.45.1
2   20.98 ms 192.168.45.254
3   21.05 ms 192.168.251.1
4   21.13 ms 192.168.210.179

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 208.32 seconds
```


## SSH

- ?
## SMB

- Anonymous Access [x]
- Guest Access [x]
- Writable SMB Share
- Important Files

## HTTP

Running port 8080

- GoBuster Endpoint enum dirbuster
- GoBuster File Extension Fuzzing
- dirsearch
- feroxbuster
- subdomain enum ffuf
- nikto
- robots.txt
- Manual Analysis

The webpage seems to be running an application called "Argus Surveillance"


