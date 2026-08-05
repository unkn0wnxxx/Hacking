
## Linux

Kali linux offers us an tool called impacket-mssqlclient in order to login into an Microsoft SQL Server.

---
##### NTLM / Domain Authentication

```
impacket-mssqlclient Administrator:password@192.168.50.18 -windows-auth
```
##### Local Auth

```
impacket-mssqlclient Administrator:password@192.168.50.18
```

---
#### IsAdmin?

```
SELECT IS_SRVROLEMEMBER('sysadmin');
```
#### Impersonate

```
enum_impersonate
execute as   database   permission_name   state_desc   grantee    grantor                        
----------   --------   ---------------   ----------   --------   ----------------------------   
b'USER'      msdb       IMPERSONATE       GRANT        dc_admin   MS_DataCollectorInternalUser
```
#### Logins

Can reveal which accounts are admin!

```
enum_logins
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

---
## Windows 

Windows has a built-in command-line tool named SQLCMD

Enumerating databases

```
sqlcmd -q "SELECT name FROM sys.databases;"
```