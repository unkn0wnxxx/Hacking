
E-Mail Service.

---
## Manual Enum

Check if user exists.

```
VRFY <user>
```

## User Enumeration

An tool called smtp-user-enum can be utilized in order to check for existing user acc's.

#### PoC

```
smtp-user-enum -M VRFY -U wordlist.txt -t 192.168.145.140
```

## In Windows PoweShell

```
Test-NetConnection -Port 25 192.168.50.8
```

With Admin Privs we can run, to download/enable telnet.

```
dism /online /Enable-Feature /FeatureName:TelnetClient
```

Or just download the telnet windows binary to the target system and run it.

```
telnet 192.168.50.8 25
VRFY <user>
```