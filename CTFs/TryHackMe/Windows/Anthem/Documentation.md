# CTF Writeup: Anthem

---

## Reconaissance

An Intial Port Scan reveals that only port 80 (HTTP) & 3389 (RDP) are open.

```
nmap -n -Pn -sS -p- 10.10.167.98
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-09 05:25 CDT
Nmap scan report for 10.10.167.98
Host is up (0.038s latency).
Not shown: 65533 filtered tcp ports (no-response)
PORT     STATE SERVICE
80/tcp   open  http
3389/tcp open  ms-wbt-server

Nmap done: 1 IP address (1 host up) scanned in 246.59 seconds
```

A Service Version detection scan reveals following information.

```
nmap -n -Pn -sSCV -p 80,3389 10.10.167.98                          
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-09 05:41 CDT
Nmap scan report for 10.10.167.98
Host is up (0.060s latency).

PORT     STATE SERVICE       VERSION
80/tcp   open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
3389/tcp open  ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2025-09-09T10:42:54+00:00; +1s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: WIN-LU09299160F
|   NetBIOS_Domain_Name: WIN-LU09299160F
|   NetBIOS_Computer_Name: WIN-LU09299160F
|   DNS_Domain_Name: WIN-LU09299160F
|   DNS_Computer_Name: WIN-LU09299160F
|   Product_Version: 10.0.17763
|_  System_Time: 2025-09-09T10:41:47+00:00
| ssl-cert: Subject: commonName=WIN-LU09299160F
| Not valid before: 2025-09-08T10:23:36
|_Not valid after:  2026-03-10T10:23:36
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 90.28 seconds
```

Since there is nothing to enumerate beside an webpage, I will start there, and potentially later scan
for UDP Services, if nothing can be retrieved.

Mapped 10.10.167.98 in /etc/hosts to domain: anthem.thm

```
sudo echo "10.10.167.98 anthem.thm" | sudo tee -a /etc/hosts
```

After Enumerating the webpage for some time, I was able to retrieve a potential password in robots.txt

```
UmbracoIsTheBest!
```
and which CMS the website is using. --> Umbraco

I was also able to retrieve 2 Flags. Flag 2 is hidden in the source-code of the webpage.

```
THM{G!T_G00D}
```

Flag 3 is in the /authors/ directory, which can be navigated to when pressing on the first comment and on the author Jane Doe

```
THM{L0L_WH0_D15}
```

On the 2nd blog post, where the admin is mentioned, the only input we get from the Author is a poem.
So I googled the poem and the creator's name was "Solomon Grundy" --> name of admin
Judging by jane doe's mail, I phrased the mail of the admin like the following:

```
SG@anthem.com:UmbracoIsTheBest!
```

I was able to retrive flag 1 in the metadata of http://anthem.thm/archive/we-are-hiring/ source code

```
THM{L0L_WH0_US3S_M3T4}
```

Discovered Flag 4 in Metadata of http://anthem.thm/archive/a-cheers-to-our-it-department/

```
THM{AN0TH3R_M3TA}
```

## Initial Access 


Since we have the credentials of the admin and we know RDP is open, we should try and log into the system via RDP.


```
xfreerdp3 /v:anthem.thm /u:SG /p:UmbracoIsTheBest!
```

Retrieved user.txt flag on Desktop

```
THM{N00T_NO0T}
```

## Privilege Escalation

Can we spot the admin password? Hint says it is hidden.

Went into the root directory / and made dir/a. Found backup folder with an file called "restore.txt".
Unfortunately access is denied, so I opened it up in the file explorer and went to Properties > Security > Change Permissions and add > win-lu09299160f\sg

Retrieved password of Administrator

```
Administrator:ChangeMeBaby1MoreTime
```

Logged in via RDP 

```
xfreerdp3 /v:anthem.thm /u:Administrator /p:ChangeMeBaby1MoreTime
```

Retrieved root.txt on Desktop

```
THM{Y0U_4R3_1337}
```
