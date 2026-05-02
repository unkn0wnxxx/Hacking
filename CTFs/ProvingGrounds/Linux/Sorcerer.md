

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


## HTTP

- Enumerating Endpoints
- Enumerating Subdomains
- robots.txt
- Any Services Running? --> Version Information
- Vulnerability Assessment

## RPC

- ?

## HTTP (7742)

- Enumerating Endpoints
- Enumerating Subdomains
- robots.txt
- Any Services Running? --> Version Information
- Vulnerability Assessment

## HTTP (8080)

- Enumerating Endpoints
- Enumerating Subdomains
- robots.txt
- Any Services Running? --> Apache Tomcat 7.0.4
- Vulnerability Assessment