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
```

VMware

```
vmware-toolbox-cmd -v
dpkg -l | grep -i open-vm-tools
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
```

