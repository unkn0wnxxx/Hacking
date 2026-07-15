
When this instance is running it means it's Network Shares and similiar to smb.
## PoC

Enumerating

```
showmount -e 10.129.37.29
Export list for 10.129.37.29:
/site_backups (everyone)
```

Mounting the share

1. Create a folder on local machine: /mnt/site_backups

```
mkdir /mnt/site_backups
```

2. Mount the remote nfs share to our local machine.

```
mount -t nfs 10.129.37.29:/site_backups /mnt/site_backups
```

```
mount -t nfs <target_ip>:/<mnt_sharename> /mnt/<sharename> -o nolock
```

Now we can access the share and the files from the remote nfs share are mounted to our local /mnt/site_backups!