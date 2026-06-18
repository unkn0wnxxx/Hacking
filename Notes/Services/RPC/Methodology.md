
can reveal information about users on the target system.

---
## PoC

##### Anonymously

Logging in anonymously and viewing users & information.

```
rpcclient -U "" -N <target_ip>
```

```
rpcclient -U''%'' 192.168.248.175
rpcclient $> querydispinfo
```

```
-> srvinfo - Server information.
-> serverinfo - Server information.
-> lsaenumsid - RID cycling → real domain usernames
-> enumdomains - Enumerate all domains that are deployed in the network. 
-> querydominfo - Provides domain, server, and user information of deployed domains. 
-> netshareenumall - Enumerates all available shares.   
-> netsharegetinfo <share> - Provides information about a specific share. 
-> enumdomusers - Enumerates all domain users.
-> queryuser <RID> - Provides information about a specific user.   
-> querygroup <RID> - Provides information about a specific group
```
##### Authenticated

```
rpcclient -U "sequel.htb\rose%KxEPkKe6R8su" 10.129.232.128
```
