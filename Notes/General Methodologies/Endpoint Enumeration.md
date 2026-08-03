
```
feroxbuster -u http://<target_ip>/
```

```
feroxbuster -u http://<target_ip>/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

```
feroxbuster -u http://<target_ip>/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
```

```
dirsearch -u http://<target_ip>/
```



##### Unappropriate Statuscode Fix

```
gobuster dir -u http://<target_ip>/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -b 403,404
```

```
gobuster dir -u https://fire.windcorp.thm -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -k 
```