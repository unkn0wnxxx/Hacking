
## Linux

Kali linux offers us an tool called impacket-mssqlclient in order to login into an Microsoft SQL Server.

```
impacket-mssqlclient Administrator:password@192.168.50.18 -windows-auth
```

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
master

tempdb

model

msdb

offsec
```

#### Table Enumeration in database "offsec"

```
SELECT * FROM offsec.information_schema.tables;
TABLE_CATALOG   TABLE_SCHEMA   TABLE_NAME   TABLE_TYPE   
-------------   ------------   ----------   ----------   
offsec          dbo            users        b'BASE TABLE'
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