
##### Linux

```
lspci -k        # PCI-Geräte + zugehörige Kernel-Treiber
lsmod           # geladene Kernel-Module
modinfo <name>  # Details zu einem spezifischen Treiber
```
##### Windows

```
Get-WindowsDriver -Online -All
Get-CimInstance Win32_SystemDriver | Select-Object Name, DisplayName, State, PathName
Get-CimInstance Win32_PnPEntity | Where-Object { $_.Name -match "VMware|VirtualBox|Virtio|Hyper-V|QEMU" } | Select Name Get-PnpDevice | Where-Object { $_.FriendlyName -match "VMware|VirtualBox|Virtio|Hyper-V" }
Get-PnpDevice | Where-Object { $_.FriendlyName -match "VMware|VirtualBox|Virtio|Hyper-V" }
```

Drivers with Details to Signature/Certificate.

```
Get-CimInstance Win32_SystemDriver | ForEach-Object {
    Get-AuthenticodeSignature "C:\Windows\System32\drivers\$($_.Name).sys" -ErrorAction SilentlyContinue
} | Select-Object Path, Status, SignerCertificate
```

Compact Command to enumerate all Driver Data in /System32 Directory.

```
Get-ChildItem C:\Windows\System32\drivers\*.sys | Select-Object Name, LastWriteTime
```

CMD

```
driverquery /v
driverquery /fo table
driverquery /v /fo csv | Select-String -Pattern "kernel"
```

After enumerating driver names we can checkout [loldrivers.io](https://www.loldrivers.io/) to see an List of known Drivers which are exploitable.
