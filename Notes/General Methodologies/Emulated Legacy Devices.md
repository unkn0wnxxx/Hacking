If an old/outdated emulated / virtual devices has been discovered like Sound Cards, old NIC-Emulations or Floppy-Controllers they can be abused to potentially breakout of an VM Environment.

---
##### Linux

Enumerate PCI-Devices connected to the VM

```
lspci -vnn
lspci -k                      
ls -la /sys/bus/pci/devices/
```

Is USB-Emulation active?

```
lsusb
cat /sys/kernel/debug/usb/devices 2>/dev/null
```

Enumerate Legacy-Devices

```
dmesg | grep -iE "floppy|fdc|serial|parport|i8042"
ls /dev/fd* /dev/ttyS* 2>/dev/null
```

---
##### Windows

Enumerate Vendor & Device ID's

```
Get-CimInstance Win32_PnPEntity | Where-Object { $_.DeviceID -match 'PCI\\VEN' } |
  Select-Object Name, DeviceID, Manufacturer, Service | Format-Table -Auto
```

Enumerate Hardware ID for the device

```
Get-PnpDevice -PresentOnly | Where-Object { $_.InstanceId -match 'PCI' } |
  ForEach-Object { $_ | Get-PnpDeviceProperty -KeyName DEVPKEY_Device_HardwareIds }
```
###### Vendor-IDs

```
VEN_15AD = VMware
VEN_80EE = VirtualBox (innotek)
VEN_1AF4 = Red Hat / virtio
VEN_1414 = Microsoft (Hyper-V)
```

Enumerate Storage Controller (emulated SCSI/SATA)

```
Get-CimInstance Win32_SCSIController | Select Name, Manufacturer, DriverName 
Get-CimInstance Win32_IDEController | Select Name
```

Enumerate Graphic (VirtualBox VGA/SVGA & VMware SVGA)

```
Get-CimInstance Win32_VideoController | Select Name, DriverVersion, AdapterCompatibility
```

Enumerate Network Adapter Model

```
Get-NetAdapter | Select Name, InterfaceDescription, DriverName, DriverVersion
Get-CimInstance Win32_NetworkAdapter | Where-Object { $_.PhysicalAdapter } |
  Select Name, Manufacturer, ServiceName
```

Enumerate Legacy Devices

```
Get-CimInstance Win32_FloppyController
Get-CimInstance Win32_SerialPort
Get-CimInstance Win32_SoundDevice | Select Name, Manufacturer
Get-CimInstance Win32_PnPEntity |
  Where-Object { $_.Name -match 'floppy|serial|parallel|PS/2|i8042' } | Select Name
```

Enumerate PCI via Registry

```
Get-ChildItem "HKLM:\SYSTEM\CurrentControlSet\Enum\PCI" | Select Name
```
