
```
nmap -A -p- --min-rate 10000 192.168.198.60
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-25 17:35 EST
Nmap scan report for 192.168.198.60
Host is up (0.030s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT      STATE  SERVICE           VERSION
22/tcp    open   ssh               OpenSSH 7.4p1 Debian 10+deb9u7 (protocol 2.0)
| ssh-hostkey: 
|   2048 75:4c:02:01:fa:1e:9f:cc:e4:7b:52:fe:ba:36:85:a9 (RSA)
|   256 b7:6f:9c:2b:bf:fb:04:62:f4:18:c9:38:f4:3d:6b:2b (ECDSA)
|_  256 98:7f:b6:40:ce:bb:b5:57:d5:d1:3c:65:72:74:87:c3 (ED25519)
|_auth-owners: root
113/tcp   open   ident             FreeBSD identd
|_auth-owners: nobody
5432/tcp  open   postgresql        PostgreSQL DB 9.6.0 or later
8080/tcp  open   http              WEBrick httpd 1.4.2 (Ruby 2.6.6 (2020-03-31))
|_http-title: Redmine
|_http-server-header: WEBrick/1.4.2 (Ruby/2.6.6/2020-03-31)
| http-robots.txt: 4 disallowed entries 
|_/issues/gantt /issues/calendar /activity /search
10000/tcp open   snet-sensor-mgmt?
|_auth-owners: eleanor
| fingerprint-strings: 
|   DNSStatusRequestTCP, DNSVersionBindReqTCP, Help, Kerberos, LANDesk-RC, LDAPBindReq, LDAPSearchReq, LPDString, RPCCheck, RTSPRequest, SIPOptions, SMBProgNeg, SSLSessionReq, TLSSessionReq, TerminalServer, TerminalServerCookie, X11Probe: 
|     HTTP/1.1 400 Bad Request
|     Connection: close
|   FourOhFourRequest: 
|     HTTP/1.1 200 OK
|     Content-Type: text/plain
|     Date: Thu, 25 Dec 2025 22:35:52 GMT
|     Connection: close
|     Hello World
|   GetRequest, HTTPOptions: 
|     HTTP/1.1 200 OK
|     Content-Type: text/plain
|     Date: Thu, 25 Dec 2025 22:35:46 GMT
|     Connection: close
|_    Hello World
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port10000-TCP:V=7.95%I=7%D=12/25%Time=694DBC41%P=x86_64-pc-linux-gnu%r(
SF:GetRequest,71,"HTTP/1\.1\x20200\x20OK\r\nContent-Type:\x20text/plain\r\
SF:nDate:\x20Thu,\x2025\x20Dec\x202025\x2022:35:46\x20GMT\r\nConnection:\x
SF:20close\r\n\r\nHello\x20World\n")%r(HTTPOptions,71,"HTTP/1\.1\x20200\x2
SF:0OK\r\nContent-Type:\x20text/plain\r\nDate:\x20Thu,\x2025\x20Dec\x20202
SF:5\x2022:35:46\x20GMT\r\nConnection:\x20close\r\n\r\nHello\x20World\n")%
SF:r(RTSPRequest,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20
SF:close\r\n\r\n")%r(RPCCheck,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nCo
SF:nnection:\x20close\r\n\r\n")%r(DNSVersionBindReqTCP,2F,"HTTP/1\.1\x2040
SF:0\x20Bad\x20Request\r\nConnection:\x20close\r\n\r\n")%r(DNSStatusReques
SF:tTCP,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20close\r\n
SF:\r\n")%r(Help,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20
SF:close\r\n\r\n")%r(SSLSessionReq,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\
SF:r\nConnection:\x20close\r\n\r\n")%r(TerminalServerCookie,2F,"HTTP/1\.1\
SF:x20400\x20Bad\x20Request\r\nConnection:\x20close\r\n\r\n")%r(TLSSession
SF:Req,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20close\r\n\
SF:r\n")%r(Kerberos,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\
SF:x20close\r\n\r\n")%r(SMBProgNeg,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\
SF:r\nConnection:\x20close\r\n\r\n")%r(X11Probe,2F,"HTTP/1\.1\x20400\x20Ba
SF:d\x20Request\r\nConnection:\x20close\r\n\r\n")%r(FourOhFourRequest,71,"
SF:HTTP/1\.1\x20200\x20OK\r\nContent-Type:\x20text/plain\r\nDate:\x20Thu,\
SF:x2025\x20Dec\x202025\x2022:35:52\x20GMT\r\nConnection:\x20close\r\n\r\n
SF:Hello\x20World\n")%r(LPDString,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r
SF:\nConnection:\x20close\r\n\r\n")%r(LDAPSearchReq,2F,"HTTP/1\.1\x20400\x
SF:20Bad\x20Request\r\nConnection:\x20close\r\n\r\n")%r(LDAPBindReq,2F,"HT
SF:TP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20close\r\n\r\n")%r(SI
SF:POptions,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConnection:\x20close
SF:\r\n\r\n")%r(LANDesk-RC,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\nConne
SF:ction:\x20close\r\n\r\n")%r(TerminalServer,2F,"HTTP/1\.1\x20400\x20Bad\
SF:x20Request\r\nConnection:\x20close\r\n\r\n");
Aggressive OS guesses: Linux 3.10 - 4.11 (96%), Linux 3.13 - 4.4 (96%), Linux 3.2 - 4.14 (94%), Linux 2.6.32 - 3.13 (93%), Linux 3.8 - 3.16 (92%), Linux 3.16 - 4.6 (92%), Linux 3.13 or 4.2 (90%), Linux 4.4 (90%), Linux 2.6.32 - 3.10 (90%), Linux 5.0 - 5.14 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OSs: Linux, FreeBSD; CPE: cpe:/o:linux:linux_kernel, cpe:/o:freebsd:freebsd

TRACEROUTE (using port 53/tcp)
HOP RTT      ADDRESS
1   30.65 ms 192.168.45.1
2   30.64 ms 192.168.45.254
3   30.71 ms 192.168.251.1
4   30.72 ms 192.168.198.60

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 60.51 seconds
```

## SSH

- Logging in with eleanor:elearnor [x]

--> Worked, but shell is too weak in order to do operational tasks.
## ident

- User Enum with "ident-user-enum".

```
ident-user-enum 192.168.130.60 10000
ident-user-enum v1.0 ( http://pentestmonkey.net/tools/ident-user-enum )

192.168.130.60:10000    eleanor
```


## Postgre (5432)

- Anonymous access
- Vulnerability Assessment --> PostgreSQL DB 9.6.0

## HTTP (8080)

- Enumerating endpoints [x]
- Enumerating subdomains
- Vulnerability Assessment --> WEBrick httpd 1.4.2 
- robots.txt & sitemap.xml

#### Login Panel

- Logged in with admin:admin [x]

Couldn't retrieve valuable information.
## Unknown Service (10000)

- Vulnerability Assessment

nmap script revealed author --> eleanor --> potential username.
