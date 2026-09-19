
When connected to an SMB Share and having struggles with corrupted files, we can simply mount the entire smb share to our local machine aswell.

This command mounted all the files inside the /backups SMB Share onto our local /mnt directory.

```
mount -t cifs //10.129.136.29/backups /mnt -o user=,password=
```