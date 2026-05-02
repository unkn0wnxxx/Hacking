
Assuming there is some ports behind an running proxy service, we can search up for proxy port scanners like "spose".

```
python3 spose.py --proxy http://192.168.122.189:3128 --target 192.168.122.189           
Scanning default common ports
Using proxy address http://192.168.122.189:3128
192.168.122.189:3306 seems OPEN
192.168.122.189:8080 seems OPEN
```

After we found out which ports are running, we can access them by configuring an proxy with "FoxyProxy" Extension.

It's important to specify the port 3128 (proxy port) because only then we are able to connect to 3306 & 8080.

```
Opened up FoxyProxy Options > Selected HTTP > Target IP of Box 192.168.187.189 & proxy port 3128
```

Now we are able to see the ports behind the proxy 3306 (mysql) & 8080 (webserver)