
---
##### Linux

Enumerate Shared Folders, Clipboards and Drag&Drop functionality

```
mount | grep -iE "vboxsf|vmhgfs|fuse.vmhgfs"
ls -la /mnt /media 2>/dev/null
cat /proc/mounts | grep -iE "vboxsf|vmhgfs"
```

Enumerate VMware Backdoor / VMCI Devices

```
ls -la /dev/vmci /dev/vsock 2>/dev/null
lsmod | grep -iE "vmw_vmci|vsock"
```

Enumerate VirtualBox Shared Clipboard / Guest Properties

```
VBoxControl guestproperty enumerate 2>/dev/null
```

---
##### Windows

Enumerate Shared Folders / HGFS

```
Get-PSDrive | Where-Object { $_.DisplayRoot -match "vmware-host|VBOXSVR" }
net use
```

Enumerate VMCI Devices

```
Get-PnpDevice | Where-Object { $_.FriendlyName -match "VMCI" }
```