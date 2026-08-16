
## Connecting to RDP

```
rdesktop -u offsec -p lab -a 16 -P -z -b -g 1280x860 192.168.116.196
```

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:offsec /p:lab /v:192.168.116.196 /w:1600 /h:800 /drive:test,/home/saitama/Desktop
```

Kerberos Auth

```
xfreerdp3 /cert:ignore /clipboard /compression /auto-reconnect /u:offsec /p:lab /v:192.168.116.196 /w:1600 /h:800 /drive:test,/home/saitama/Desktop /kerberos:cache:anderson.w@10.129.56.170.ccache
```