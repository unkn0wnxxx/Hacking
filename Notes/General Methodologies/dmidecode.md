
---
##### Linux

Hypervisor Enumeration

```
sudo dmidecode -s system-manufacturer      # → "VMware, Inc."
sudo dmidecode -s system-product-name      # → "VMware Virtual Platform" / "VMware7,1"
sudo dmidecode -s bios-vendor              # → "Phoenix Technologies LTD"
sudo dmidecode -s bios-version
```

VM Enumeration

```
systemd-detect-virt          # → "vmware"
sudo virt-what
lscpu | grep -i <hypervisor>
grep -o hypervisor /proc/cpuinfo
cat /proc/cpuinfo | grep -iE "model name|hypervisor"
dmesg 2>/dev/null | grep -iE "vmware|virtualbox|qemu|kvm|xen|hyper-v|hypervisor|virtual"
ps aux | grep -iE "vmtoolsd|vboxservice|vboxclient|qemu-ga|xe-daemon|hv_"
```

VMware

```
vmware-toolbox-cmd -v
dpkg -l | grep -i open-vm-tools
```

VMware guest additions

```
modinfo vboxguest | grep -i version 
VBoxControl --version 2>/dev/null 
cat /var/log/vboxadd-* 2>/dev/null
```

QEMU Guest Agent

```
qemu-ga --version 2>/dev/null 
modinfo virtio_net | grep -i version
```

Xen

```
xenstore-read domid 2>/dev/null 
cat /sys/hypervisor/version/* 2>/dev/null
```

System Enumeration

```
sudo dmidecode -t system
sudo dmidecode -t bios
sudo dmidecode -t baseboard
```

Low privileged:

```
cat /sys/class/dmi/id/product_name
cat /sys/class/dmi/id/sys_vendor
cat /sys/class/dmi/id/bios_vendor
cat /sys/block/*/device/model 2>/dev/null lsblk -o NAME,MODEL
lsblk -o NAME,MODEL
```

MAC Prefix Enum 

```
ip link | grep -i link/ether
```

---
##### Windows

Hypervisor Enumeration

```
(Get-CimInstance Win32_ComputerSystem).Manufacturer
(Get-CimInstance Win32_ComputerSystem).Model
Get-Service | Where-Object { $_.Name -match "vmtools|VBoxService|vmci|vmhgfs|vmmouse|hv_|vmicheartbeat" }
```

Hardware & BIOS Enumeration

```
Get-CimInstance Win32_BIOS
Get-CimInstance Win32_ComputerSystem
Get-CimInstance Win32_BaseBoard
Get-CimInstance Win32_PhysicalMemory
Get-CimInstance Win32_Processor
```

Enumerate CPU for Hypervisor-Bit

```
Get-CimInstance Win32_Processor | Select Name, Manufacturer
```

With systeminfo

```
systeminfo | findstr /i "Manufacturer Model System"
```

Enumerate Registry

```
Get-ItemProperty "HKLM:\HARDWARE\DESCRIPTION\System\BIOS" | Select SystemManufacturer, SystemProductName reg query "HKLM\SOFTWARE\Oracle\VirtualBox Guest Additions" 2>$null reg query "HKLM\SOFTWARE\VMware, Inc.\VMware Tools" 2>$null
```

VirtualBox Guest Additions

```
Get-ItemProperty "HKLM:\SOFTWARE\Oracle\VirtualBox Guest Additions" | Select Version
```