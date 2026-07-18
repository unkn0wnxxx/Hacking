

## Paths

```
C:\Windows\Tasks
```

## Commands

CMD

```
schtasks /query
```

PowerShell

```
Get-ScheduledTask
```

```
schtasks /query /fo LIST /v
```

If we found an service which got created by an higher perm user & we got write permissions on the .exe, we can replace it with an binary & it should be executing automatically!

```
icacls C:\Users\steve\Pictures\BackendCacheCleanup.exe
```

Now replace the original file, with an malicious binary either created with malicious.c or msfvenom! Add it onto the system & it will execute automatically.