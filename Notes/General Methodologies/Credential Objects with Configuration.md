
Sometimes in order to utilize Credential Objects they need to authenticate with specialised Configurations. These Configurations are here in order to follow the Least-Privilege-Principle in which only Administrator User can auth against the endpoint without using this configuration file.
Most of the times those configuration files come with restricted access to functions.

In this example the Configuration Name is "dc_manage".

---
###### RCE

```
Enter-PSSession -ComputerName ATSSERVER -Credential $cred -ConfigurationName dc_manage
```
###### Command Execution

```
Invoke-Command -ScriptBlock { whoami } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

Enumerated Commands which we can use.

```
Invoke-Command -ScriptBlock { Get-Command } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred

Get-Alias
Get-ChildItem
Get-Command
Get-Content
Get-Location
Set-Content
Set-Location
Write-Output
```

Enumerated Aliases aswell for easy usability.

```
Invoke-Command -ScriptBlock { Get-Alias } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred

cat -> Get-Content
cd -> Set-Location
ls -> Get-ChildItem
pwd -> Get-Location
sc -> Set-Content
type -> Get-Content
```

Enumerated in which directory we are right now.

```
Invoke-Command -ScriptBlock { Get-Location } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred      

Path                      PSComputerName
----                      --------------
C:\Users\imonks\Documents ATSSERVER
```

