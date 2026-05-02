
Connects via TLS to Server.

```
socat stdio ssl:TARGET_IP:<port>,cert=<CERT_FILE>,key=<KEY_FILE>,verify=0
```