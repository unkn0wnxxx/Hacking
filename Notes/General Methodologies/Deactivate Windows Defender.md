
1. Check Status

```
Get-MpComputerStatus | Select IsTamperProtected
```

2. If it says false, we can disable it

```
Set-MpPreference -DisableRealtimeMonitoring $true
```