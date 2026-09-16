
When trying to perform an HTTP Request and trying to route traffic to the web browser we can utilize the following command without the need of using FoxyProxy.

```
curl --path-as-is -x http://127.0.0.1:8080 -i -s -k -X $'GET' \
-H $'Host:
```

