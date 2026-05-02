
## Remotely Connecting

```
mysql -u root -p -h 192.168.50.16 -P 3306 --skip-ssl-verify-server-cert 
*prompt password*
```

## Internaly Connecting

```
mysql -u <username> -p
*prompts for password*
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