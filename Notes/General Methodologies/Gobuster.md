
```
gobuster dir -u https://fire.windcorp.thm -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -k 
```
##### Unappropriate Statuscode Fix

```
gobuster dir -u http://<target_ip>/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -b 403,404
```

```
feroxbuster -u http://<target_ip>/
```

```
dirsearch -u http://<target_ip>/
```
