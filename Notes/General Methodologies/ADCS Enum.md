
---

Enumerating if an internal CA / Certificate Templates are vulnerable to ADCS Attacks.

Plaintext Auth

```
certipy-ad find -u sql_svc -p 'REGGIE1234ronnie' -dc-ip 10.129.37.251 -target sequel.htb -vulnerable -enabled
```

NTLM Auth

```
certipy-ad find -username 'ca_svc@fluffy.htb' -hashes :ca0f4f9e9eb8a092addf53bb03fc98c8 -dc-ip 10.129.232.88 -vulnerable -target FLUFFY-DC01-CA
```

We have to do the next step aswell to get the proper result:
## Important Step: Internal Enum

Utilize Certipy.exe. Transfer it to the target system and run it with the following command:

1. Check which CA's are in place:

```
.\Certify.exe cas
```

2. Enumerate vulnerable certificates

```
.\Certify.exe find /vulnerable
```
