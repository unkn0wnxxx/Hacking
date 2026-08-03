
Let's first activate RDP & configure Firewall to allow RDP Connections. So we have better PowerShell.

This will enable RDP.

```
reg add "\\192.168.210.16\HKLM\SYSTEM\CurrentControlSet\Control\Terminal Server" /v fDenyTSConnections /t REG_DWORD /d 0 /f
```

2. Create a firewall rule to allow inbound RDP traffic on port 3389

```
netsh advfirewall firewall set rule group="remote desktop" new enable=Yes
```

or deactivate Firewall completly.

```
netsh advfirewall set allprofiles state off
```

3. Restart TermService

```
sc.exe stop TermService
sc.exe start TermService
```
