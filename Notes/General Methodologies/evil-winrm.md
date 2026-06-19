
```
evil-winrm -i 10.10.10.161 -u administrator -p aad3b435b51404eeaad3b435b51404ee:32693b11e6aa90eb43d32c72a07ceea6
```

```
evil-winrm -i 192.168.126.172 -u anirudh -p 'SecureHM'
```

## In-Built Function to enumerate services

```
services
```

## SSL Auth

Once we have an .cert file and an private key of sort we can remotely login to the target system.

```
evil-winrm -i timelapse.htb -c key.cert -k key.pem -S
```

## Command Substituion

If the password has weird signs in it and can't be used. Store the password in an .txt file and execute the following command:

```
evil-winrm -S -i timelapse.htb -u "Administrator" -p $(< admin_pwd.txt)
```
