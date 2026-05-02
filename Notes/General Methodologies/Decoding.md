

##### John the Ripper

If we have dumped hashes from memory using secretsdump.py, we can utilize the following syntax in order to bruteforce passwords out of it.

1. Store the WHOLE dump in an "hash" file.

2. Execute the following command:

```
john hash --wordlists=/usr/share/wordlists/rockyou.txt --format=NT
```



```

```