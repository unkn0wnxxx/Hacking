
## Operating system, version and architecture
 
```
systeminfo
```

Improved Version

```
systeminfo | findstr /B /C:"Host Name" /C:"OS Name" /C:"OS Version" /C:"System Type" /C:"Network Card(s)" /C:"Hotfix(s)"
```

## Network information


```
ipconfig /all
```

Display routing table for pivoting.

```
route print
```