
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

## Running processes


```
tasklist /v
```

More efficient 

```
wmic process get name,processid,executablepath
```

In PowerShell

```
Get-Process
```