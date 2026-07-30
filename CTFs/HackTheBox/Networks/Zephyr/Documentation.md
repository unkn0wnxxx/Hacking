
Subnet: 10.10.110.0/24

10.10.110.2 is out-of-scope because it represents the firewall.

---
## ZEPHYR-MAIL

Started off with host disocvery on the subnet.

```
nmap -sn 10.10.110.0/24 --min-rate 1000
```

Discovered the initial entry point 10.10.110.35

Let's start an port scan on it.

```
nmap -n -Pn -sSCV -p- -oA nmap/target 10.10.110.35
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-29 11:25 -0500
Nmap scan report for 10.10.110.35
Host is up (0.098s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT    STATE SERVICE  VERSION
22/tcp  open  ssh      OpenSSH 8.2p1 Ubuntu 4ubuntu0.13 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 91:ca:e7:7e:99:03:a9:78:e8:86:2e:e8:cc:2b:9f:08 (RSA)
|   256 b1:7f:c0:06:9b:e7:08:b4:6a:ab:bd:c2:96:04:23:49 (ECDSA)
|_  256 0d:3b:89:bc:d5:a4:35:e0:dd:c4:22:14:7a:48:ad:7c (ED25519)
80/tcp  open  http     nginx 1.18.0 (Ubuntu)
|_http-server-header: nginx/1.18.0 (Ubuntu)
|_http-title: Did not follow redirect to https://painters.htb/home
443/tcp open  ssl/http nginx 1.18.0 (Ubuntu)
|_ssl-date: TLS randomness does not represent time
|_http-title: Did not follow redirect to https://painters.htb/home
|_http-server-header: nginx/1.18.0 (Ubuntu)
| ssl-cert: Subject: commonName=painters.htb/countryName=GB
| Subject Alternative Name: DNS:mail.painters.htb, IP Address:192.168.110.51
| Not valid before: 2022-04-04T10:00:52
|_Not valid after:  2032-04-01T10:00:52
| tls-nextprotoneg: 
|   h2
|_  http/1.1
| tls-alpn: 
|   h2
|_  http/1.1
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 189.86 seconds
```

The scan revealed that the target server seems to be an Linux Environment. It also reveals the SAN "mail.painters.htb". The target is probably ZEPHYR-MAIL. Which makes sense since Mailservers are most of the times inside the DMZ. It also reveals an redirect to painters.htb. Let's map the SAN & the domain to the target ip address in our local dns file. 

```
echo "10.10.110.35 mail.painters.htb painters.htb" | tee -a /etc/hosts
```

Upon inspecting the webpage we found 3 potential usernames.

```
Thomas Bishop
James Ray
Toby Harlington
```

Enumerated endpoints using gobuster and found interesting endpoints. Including /administration which seems to be an login panel & /vacancies endpoint which reveals information about another username.

```
Ralph Davies
```

Let's create an users wordlist out of them using the following username generator:

```
git clone https://github.com/florianges/UsernameGenerator
```

Stored all of the users in newusers.txt and then ran the following command to generate multiple usernames for bruteforcing:

```
UsernameGenerator.py newusers.txt users.txt
```

Since we don't got any passwords yet. Let's create an passwords.txt wordlist by utilizing an tool called cewl which crawls the whole website.

```
cewl http://painters.htb -x 15 -o -w passwords.txt
```

Before starting up bruteforcing the /administration endpoint. Let's try & enumerate subdomains with ffuf.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://painters.htb -H "Host: FUZZ.painters.htb" -fs 0
```

Couldn't find anything interesting let's proceed enumeration of endpoints with dirsearch & feroxbuster.

```
dirsearch -u http://painters.htb
```

Running feroxbuster was a bit more promising, since it discovered another admin panel or the same, but on another endpoint!

```
http://painters.htb/views/admin/
```

Alright let's start with bruteforcing the webpanel using hydra. We'll need to intercept traffic with Our Web-Proxy Tool BurpSuite first in order to prepare necessary stuff for hydra.

```
username=&pass=
```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```