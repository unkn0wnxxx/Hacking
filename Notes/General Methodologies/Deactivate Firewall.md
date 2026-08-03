
Create a firewall rule to allow inbound RDP traffic on port 3389

```
netsh advfirewall firewall set rule group="remote desktop" new enable=Yes
```

or deactivate Firewall completly.

```
netsh advfirewall set allprofiles state off
```
