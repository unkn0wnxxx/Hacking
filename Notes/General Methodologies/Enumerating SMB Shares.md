
Anonymous

```
smbmap -H <target_ip>
```

```
smbclient -L \\\\<target_ip>
```

Authenticated

```
smbmap -u mark -p OathDeeplyReprieve91 -H 192.168.139.247 -r
```

```
smbclient -L \\\\<target_ip> -U <username>
<password>
```

When password breaks out:

```
smbclient //172.16.160.21/scripts -U <domain>\\<username> --password=<password>
```

```
smbclient \\\\172.16.160.21/scripts -U <domain>//<username>%<password>
```

```
smbclient \\\\172.16.160.21/scripts -U relia//mountuser%DRtajyCwcbWvH/9
```

Authenticated with nxc

```
nxc smb 192.168.202.242 -u john -p 'dqsTwTpZPn#nL' --shares
SMB         192.168.202.242 445    MAILSRV1         [*] Windows Server 2022 Build 20348 x64 (name:MAILSRV1) (domain:beyond.com) (signing:False) (SMBv1:False)
SMB         192.168.202.242 445    MAILSRV1         [+] beyond.com\john:dqsTwTpZPn#nL 
SMB         192.168.202.242 445    MAILSRV1         [*] Enumerated shares
SMB         192.168.202.242 445    MAILSRV1         Share           Permissions     Remark
SMB         192.168.202.242 445    MAILSRV1         -----           -----------     ------
SMB         192.168.202.242 445    MAILSRV1         ADMIN$                          Remote Admin
SMB         192.168.202.242 445    MAILSRV1         C$                              Default share
SMB         192.168.202.242 445    MAILSRV1         IPC$            READ            Remote IPC
```