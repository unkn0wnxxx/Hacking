
---

If you want to auth via Kerberos and not via Password or NTLM Auth, you can request an TGT.

```
impacket-getTGT frizz.htb/f.frizzle:Jenni_Luvs_Magic23 -dc-ip 10.129.207.49
```

We got stored an .ccache file. Let's authenticate with SSH now.

```
KRB5CCNAME=f.frizzle.ccache ssh -K f.frizzle@frizzdc.frizz.htb
```

Note: If it doesn't work we need to go into /etc/hosts and put the FQDN before the domain & hostname.
