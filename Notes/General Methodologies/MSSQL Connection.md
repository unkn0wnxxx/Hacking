
## Linux

Kali linux offers us an tool called impacket-mssqlclient in order to login into an Microsoft SQL Server.

##### NTLM / Domain Authentication

```
impacket-mssqlclient Administrator:password@192.168.50.18 -windows-auth
```
##### Local Auth

```
impacket-mssqlclient Administrator:password@192.168.50.18
```

---
#### Version Enumeration

```
SELECT @@version;
```

#### Enumerating sysusers (special)

```
SELECT * FROM sysusers;
```

#### Enumerating databases

```
SELECT name FROM sys.databases;
```

#### Table Enumeration in database "offsec"

```
SELECT * FROM offsec.information_schema.tables;
```
#### Column Enumeration in "users" table

```
SELECT * FROM offsec.dbo.users;
username     password     
----------   ----------   
admin        lab        

guest        guest
```

## Windows 

Windows has a built-in command-line tool named SQLCMD

Enumerating databases

```
sqlcmd -q "SELECT name FROM sys.databases;"
```