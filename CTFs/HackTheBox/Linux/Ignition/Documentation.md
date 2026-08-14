
## CTF Writeup: Ignition

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.46.65
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-13 09:04 -0500
Nmap scan report for 10.129.46.65
Host is up (0.022s latency).
Not shown: 65534 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
80/tcp open  http    nginx 1.14.2
|_http-title: Did not follow redirect to http://ignition.htb/
|_http-server-header: nginx/1.14.2

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 21.08 seconds
```

The target seems to be an Webserver which is running an HTTP Website using nginx 1.14.2. The TCP Scan provides us information about an redirect to an domain called "ignition.htb". In order to view the webpage, let's map the domain to the target ip address in our local dns file.

```
echo "10.129.46.65 ignition.htb" | tee -a /etc/hosts
```

There seems to be an CMS running called "Luma". 

I enumerated endpoints using feroxbuster & identified an /admin panel.

```
feroxbuster --url http://ignition.htb
```

The Admin Panel is ran by Magento. Let's search up for password policies & default passwords of 2023.

Utilized the following credentials to login as the admin user.

```
admin:qwerty123
```

Retrieved flag.txt

```
797d6c988d9dc5865e010b9410f247e0
```