
Is an in-built MSSQL Procedure which allows reading files and directorys of the target server.

---

Enumerate Root File System

```
xp_dirtree \
```

Enumerate Webserver

```
xp_dirtree \inetpub\wwwroot
subdirectory                      depth   file   
-------------------------------   -----   ----   
about.html                            1      1   
contact.html                          1      1   
css                                   1      0   
images                                1      0   
index.html                            1      1   
js                                    1      0   
service.html                          1      1   
web.config                            1      1   
website-backup-27-07-23-old.zip       1      1
```