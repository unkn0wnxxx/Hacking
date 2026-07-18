
## Running Services

```
ss -tulnp
```

```
netstat -ano
```

Checking for running and stopping services.

```
Get-CimInstance -ClassName win32_service | Select Name,State,PathName
```

```
tasklist /v
```
#### Check Processes

```
wmic process get name,processid,executablepath
```
#### Check Services of an user

In this we search for "svcadmin" user.

```
wmic service get name,pathname,startname | findstr /i "svcadmin"
```

Run winPEAS and check registry services available.

"vss" and "AppReadiness" are services which we can usually utilize for priv esc in domain environmens.

```
sc.exe qc vss
sc.exe qc AppReadiness
```

In PowerShell

```
Get-Process
```

In evil-winrm

```
services
```