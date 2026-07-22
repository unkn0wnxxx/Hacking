
---
##### Windows

Kerberos Auth

1. In order to connect to the target system as this user I had to request an TGT again. 

```
impacket-getTGT frizz.htb/M.SchoolBus:'!suBcig@MehTed!R'
```

2. Exported it inside the Kerberos Variable.

```
export KRB5CCNAME=M.SchoolBus.ccache
```

3. Connected to the Target Domain Controller via Kerberos Auth and SSH.

**Note**: If it doesn't work we need to go into /etc/hosts and put the FQDN before the domain & hostname.

```
ssh -k frizz.htb/M.SchoolBus@frizz.htb
```

OR

**Note**: If it doesn't work we need to go into /etc/hosts and put the FQDN before the domain & hostname.

```
KRB5CCNAME=f.frizzle.ccache ssh -K f.frizzle@frizzdc.frizz.htb
```


