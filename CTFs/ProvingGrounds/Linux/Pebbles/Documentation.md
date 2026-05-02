# CTF Writeup: Pebbles

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.175.52
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-20 16:07 EST
Nmap scan report for 192.168.175.52
Host is up (0.026s latency).
Not shown: 65530 filtered tcp ports (no-response)
PORT     STATE SERVICE VERSION
21/tcp   open  ftp     vsftpd 3.0.3
22/tcp   open  ssh     OpenSSH 7.2p2 Ubuntu 4ubuntu2.8 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 aa:cf:5a:93:47:18:0e:7f:3d:6d:a5:af:f8:6a:a5:1e (RSA)
|   256 c7:63:6c:8a:b5:a7:6f:05:bf:d0:e3:90:b5:b8:96:58 (ECDSA)
|_  256 93:b2:6a:11:63:86:1b:5e:f5:89:58:52:89:7f:f3:42 (ED25519)
80/tcp   open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Pebbles
3305/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Apache2 Ubuntu Default Page: It works
8080/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-open-proxy: Proxy might be redirecting requests
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Tomcat
|_http-favicon: Apache Tomcat
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|phone|storage-misc
Running (JUST GUESSING): Linux 3.X|4.X|2.6.X (97%), Google Android 8.X (90%), Synology DiskStation Manager 7.X (88%)
OS CPE: cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:2.6 cpe:/o:google:android:8 cpe:/a:synology:diskstation_manager:7.1 cpe:/o:linux:linux_kernel:4.4
Aggressive OS guesses: Linux 3.10 - 4.11 (97%), Linux 3.13 - 4.4 (97%), Linux 3.2 - 4.14 (97%), Linux 3.8 - 3.16 (97%), Linux 2.6.32 - 3.13 (91%), Linux 4.4 (91%), Linux 2.6.32 - 3.10 (91%), Linux 3.11 - 4.9 (91%), Android 8 - 9 (Linux 3.18 - 4.4) (90%), Linux 3.13 or 4.2 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   27.06 ms 192.168.45.1
2   26.87 ms 192.168.45.254
3   27.31 ms 192.168.251.1
4   27.42 ms 192.168.175.52

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 38.19 seconds
```

Apparently we can get initial access to the server, by utilizing SQL Injection. Inspecting the webpage provides us with an login interface, which doesn't provide any functionality to backend. Which means it's not vulnerable to SQLi.

I therefore enumerated endpoints running on the target system.

```
feroxbuster -u http://192.168.175.52
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.175.52/
 🚩  In-Scope Url          │ 192.168.175.52
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.0
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
 🎉  New Version Available │ https://github.com/epi052/feroxbuster/releases/latest
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
403      GET        9l       28w      279c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
404      GET        9l       31w      276c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
301      GET        9l       28w      317c http://192.168.175.52/images => http://192.168.175.52/images/
301      GET        9l       28w      314c http://192.168.175.52/css => http://192.168.175.52/css/
200      GET       37l       78w     1134c http://192.168.175.52/index.php
200      GET       26l       47w      420c http://192.168.175.52/css/style.css
200      GET       74l      355w    32900c http://192.168.175.52/images/favicon.png
301      GET        9l       28w      321c http://192.168.175.52/javascript => http://192.168.175.52/javascript/
200      GET       37l       78w     1134c http://192.168.175.52/
200      GET     1219l     6734w   506725c http://192.168.175.52/images/pebbles.jpg
301      GET        9l       28w      328c http://192.168.175.52/javascript/jquery => http://192.168.175.52/javascript/jquery/
200      GET    10351l    43235w   284394c http://192.168.175.52/javascript/jquery/jquery
301      GET        9l       28w      330c http://192.168.175.52/javascript/mootools => http://192.168.175.52/javascript/mootools/
301      GET        9l       28w      313c http://192.168.175.52/zm => http://192.168.175.52/zm/
301      GET        9l       28w      316c http://192.168.175.52/zm/js => http://192.168.175.52/zm/js/
301      GET        9l       28w      317c http://192.168.175.52/zm/css => http://192.168.175.52/zm/css/
301      GET        9l       28w      318c http://192.168.175.52/zm/temp => http://192.168.175.52/zm/temp/
200      GET      133l      408w     4845c http://192.168.175.52/zm/js/overlay.js
200      GET       77l      302w     1958c http://192.168.175.52/zm/css/reset.css
200      GET       19l       35w      351c http://192.168.175.52/zm/css/spinner.css
200      GET       49l       87w      810c http://192.168.175.52/zm/css/overlay.css
301      GET        9l       28w      318c http://192.168.175.52/zm/ajax => http://192.168.175.52/zm/ajax/
301      GET        9l       28w      319c http://192.168.175.52/zm/tools => http://192.168.175.52/zm/tools/
301      GET        9l       28w      321c http://192.168.175.52/zm/cgi-bin => http://192.168.175.52/zm/cgi-bin/
500      GET        0l        0w        0c http://192.168.175.52/zm/ajax/zone.php
500      GET        0l        0w        0c http://192.168.175.52/zm/ajax/control.php
500      GET        0l        0w        0c http://192.168.175.52/zm/ajax/log.php
500      GET        0l        0w        0c http://192.168.175.52/zm/ajax/alarm.php
301      GET        9l       28w      322c http://192.168.175.52/zm/graphics => http://192.168.175.52/zm/graphics/
200      GET        1l        1w       98c http://192.168.175.52/zm/graphics/transparent.gif
200      GET       30l      170w     3969c http://192.168.175.52/zm/graphics/spinner.gif
200      GET        1l        2w      438c http://192.168.175.52/zm/graphics/favicon.ico
301      GET        9l       28w      318c http://192.168.175.52/zm/lang => http://192.168.175.52/zm/lang/
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/pt_br.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/es_ar.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/es_es.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/en_gb.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/cs_cz.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/hu_hu.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/pl_pl.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/big5_big5.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/ru_ru.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/he_il.php
500      GET        0l        0w        0c http://192.168.175.52/zm/skins/mobile/skin.php
200      GET      169l      638w     5324c http://192.168.175.52/zm/lang/updateLangs.php
200      GET    14345l    39675w   379351c http://192.168.175.52/zm/tools/mootools/mootools-more-1.5.1.js
301      GET        9l       28w      320c http://192.168.175.52/zm/events => http://192.168.175.52/zm/events/
200      GET     6447l    17775w   160495c http://192.168.175.52/zm/tools/mootools/mootools-core-1.4.5-compat.js
200      GET     6447l    17775w   160495c http://192.168.175.52/zm/tools/mootools/mootools-core.js
200      GET    14345l    39675w   379351c http://192.168.175.52/zm/tools/mootools/mootools-more.js
301      GET        9l       28w      319c http://192.168.175.52/zm/views => http://192.168.175.52/zm/views/
500      GET        0l        0w        0c http://192.168.175.52/zm/views/image.php
500      GET        0l        0w        0c http://192.168.175.52/zm/views/file.php
200      GET        0l        0w        0c http://192.168.175.52/zm/includes/actions.php
200      GET        1l        9w       48c http://192.168.175.52/zm/includes/Server.php
200      GET        0l        0w        0c http://192.168.175.52/zm/includes/lang.php
200      GET        0l        0w        0c http://192.168.175.52/zm/includes/control_functions.php
200      GET        0l        0w        0c http://192.168.175.52/zm/includes/functions.php
200      GET        0l        0w        0c http://192.168.175.52/zm/includes/logger.php
200      GET        1l        9w       48c http://192.168.175.52/zm/includes/Monitor.php
200      GET        1l        9w       48c http://192.168.175.52/zm/includes/database.php
200      GET        0l        0w        0c http://192.168.175.52/zm/includes/config.php
301      GET        9l       28w      322c http://192.168.175.52/zm/includes => http://192.168.175.52/zm/includes/
301      GET        9l       28w      317c http://192.168.175.52/zm/api => http://192.168.175.52/zm/api/
301      GET        9l       28w      321c http://192.168.175.52/zm/api/lib => http://192.168.175.52/zm/api/lib/
500      GET        0l        0w        0c http://192.168.175.52/zm/ajax/event.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/fr_fr.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/et_ee.php
500      GET        0l        0w        0c http://192.168.175.52/zm/lang/en_us.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/ro_ro.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/de_de.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/dk_dk.php
500      GET        0l        0w        0c http://192.168.175.52/zm/skins/xml/skin.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/se_se.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/nl_nl.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/cn_zh.php
301      GET        9l       28w      320c http://192.168.175.52/zm/images => http://192.168.175.52/zm/images/
500      GET        0l        0w        0c http://192.168.175.52/zm/ajax/stream.php
301      GET        9l       28w      319c http://192.168.175.52/zm/skins => http://192.168.175.52/zm/skins/
301      GET        9l       28w      321c http://192.168.175.52/zm/api/app => http://192.168.175.52/zm/api/app/
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/ja_jp.php
200      GET        0l        0w        0c http://192.168.175.52/zm/lang/it_it.php
500      GET        0l        0w        0c http://192.168.175.52/zm/skins/classic/skin.php
301      GET        9l       28w      328c http://192.168.175.52/zm/api/app/Config => http://192.168.175.52/zm/api/app/Config/
301      GET        9l       28w      325c http://192.168.175.52/zm/api/app/tmp => http://192.168.175.52/zm/api/app/tmp/
301      GET        9l       28w      328c http://192.168.175.52/zm/api/app/vendor => http://192.168.175.52/zm/api/app/vendor/
301      GET        9l       28w      325c http://192.168.175.52/zm/api/app/Lib => http://192.168.175.52/zm/api/app/Lib/
301      GET        9l       28w      326c http://192.168.175.52/zm/api/app/Test => http://192.168.175.52/zm/api/app/Test/
301      GET        9l       28w      329c http://192.168.175.52/zm/api/app/webroot => http://192.168.175.52/zm/api/app/webroot/
200      GET        1l     1586w    93914c http://192.168.175.52/javascript/mootools/mootools
301      GET        9l       28w      329c http://192.168.175.52/zm/api/app/Console => http://192.168.175.52/zm/api/app/Console/
301      GET        9l       28w      327c http://192.168.175.52/zm/api/app/Model => http://192.168.175.52/zm/api/app/Model/
301      GET        9l       28w      326c http://192.168.175.52/zm/api/app/View => http://192.168.175.52/zm/api/app/View/
301      GET        9l       28w      328c http://192.168.175.52/zm/api/app/Vendor => http://192.168.175.52/zm/api/app/Vendor/
301      GET        9l       28w      332c http://192.168.175.52/zm/api/app/Controller => http://192.168.175.52/zm/api/app/Controller/
[####################] - 70s   270173/270173  0s      found:93      errors:463    
[####################] - 37s    30000/30000   801/s   http://192.168.175.52/ 
[####################] - 6s     30000/30000   5124/s  http://192.168.175.52/images/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 6s     30000/30000   5148/s  http://192.168.175.52/css/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 45s    30000/30000   664/s   http://192.168.175.52/javascript/ 
[####################] - 38s    30000/30000   785/s   http://192.168.175.52/javascript/jquery/ 
[####################] - 46s    30000/30000   649/s   http://192.168.175.52/javascript/mootools/ 
[####################] - 47s    30000/30000   634/s   http://192.168.175.52/zm/ 
[####################] - 46s    30000/30000   653/s   http://192.168.175.52/zm/cgi-bin/ 
[####################] - 7s     30000/30000   4261/s  http://192.168.175.52/zm/js/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 3s     30000/30000   11732/s http://192.168.175.52/zm/includes/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 7s     30000/30000   4263/s  http://192.168.175.52/zm/css/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 47s    30000/30000   635/s   http://192.168.175.52/zm/temp/ 
[####################] - 0s     30000/30000   576923/s http://192.168.175.52/zm/tools/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 7s     30000/30000   4269/s  http://192.168.175.52/zm/ajax/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 1s     30000/30000   34443/s http://192.168.175.52/zm/tools/mootools/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   517241/s http://192.168.175.52/zm/graphics/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 6s     30000/30000   4864/s  http://192.168.175.52/zm/lang/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 6s     30000/30000   4884/s  http://192.168.175.52/zm/skins/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   229008/s http://192.168.175.52/zm/skins/mobile/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   526316/s http://192.168.175.52/zm/events/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   410959/s http://192.168.175.52/zm/views/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   468750/s http://192.168.175.52/zm/images/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 43s    30000/30000   698/s   http://192.168.175.52/zm/api/ 
[####################] - 0s     30000/30000   389610/s http://192.168.175.52/zm/api/lib/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 45s    30000/30000   664/s   http://192.168.175.52/zm/api/app/ 
[####################] - 0s     30000/30000   441176/s http://192.168.175.52/zm/skins/xml/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   410959/s http://192.168.175.52/zm/skins/classic/ => Directory listing (add --scan-dir-listings to scan)
```

## Vulnerability Assessment

The /zm endpoint looks very promising. It's an ZoneMinder Console Service running on version v1.29.0. Let's search up for some CVE's.

```
searchsploit ZoneMinder
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
ZoneMinder 1.24.3 - Remote File Inclusion                                                                   | php/webapps/17593.txt
Zoneminder 1.29/1.30 - Cross-Site Scripting / SQL Injection / Session Fixation / Cross-Site Request Forgery | php/webapps/41239.txt
ZoneMinder 1.32.3 - Cross-Site Scripting                                                                    | php/webapps/47060.txt
Zoneminder < v1.37.24 - Log Injection & Stored XSS & CSRF Bypass                                            | php/webapps/51071.py
ZoneMinder Snapshots < 1.37.33 - Unauthenticated RCE                                                        | php/webapps/51902.py
ZoneMinder Video Server - packageControl Command Execution (Metasploit)                                     | unix/remote/24310.rb
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

For our version there is SQL Injection Vulnerability for the ZoneMinder Application. Let's analyze it!


```

```



```

```


## Initial Access


```

```



```

```



```

```



```

```


## Privilege Escalation



```

```



```

```



```

```
