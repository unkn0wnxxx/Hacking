

---

If the share is accessible through the webbrowser and we have write permissions, we can upload an malicious file.
When we successfully upload the file, but it still doesn't get showen e.G webshell, it could mean that it's missing permissions.

## PoC

After executing the following command, we can view the webshell on the browser.

```
ftp> chmod 755 wolfswebshell.php
200 SITE CHMOD command ok.
```