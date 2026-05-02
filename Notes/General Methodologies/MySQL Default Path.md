
For configuration files and sensitive files which store credentials.

## Windows

```
C:\xampp\mysql\bin\my.ini
```

```
C:\ProgramData\MySQL\MySQL Server X.X\my.ini
```

Log file

```
C:\ProgramData\MySQL\MySQL Server X.X\Data\*.err
```

## Linux

```
/etc/mysql/my.cnf
```

Debian

```
/etc/mysql/mysql.conf.d/mysqld.cnf
```

Log file

```
sudo grep 'temporary password' /var/log/mysqld.log
```