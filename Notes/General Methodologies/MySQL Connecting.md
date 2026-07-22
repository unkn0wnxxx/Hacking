
## Remotely Connecting

**Note**: No Space between -p parameter and actual password!!

```
mysql -u root -p'<password>' -h 192.168.50.16 -P 3306 --skip-ssl-verify-server-cert 
*prompt password*
```

## Internal Connecting

Linux

```
mysql -u <username> -p
*prompts for password*
```

Windows

WARNING: Don't have an space between the parameters and the credentials.

```
C:\xampp\mysql\bin\mysql.exe -uMrGibbonsDB -pMisterGibbs!Parrot!?1
```
#### Querying for Version

```
select version();
```

#### whoami in SQL

```
select system_user();
```


#### Enumerating database

```
show databases;
```


```
SELECT user, authentication_string FROM mysql.user WHERE user = 'offsec';
```