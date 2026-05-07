
## Normal Scan

```
wpscan --url http://192.168.199.229 
```

## User Enumeration


## Brute Forcing 

```
wpscan --url http://<domain> --usernames <username_wordlist> --passwords <password_wordlist>
```

```
wpscan --url http://jack.thm -U users.txt -P /usr/share/wordlists/fasttrack.txt
```
## Insane Scan

```
wpscan --url http://192.168.50.244 --enumerate p --plugins-detection aggressive
```


## nmap plugin enumeration


```
sudo nmap -T4 -Pn -sC --script http-wordpress-enum --script-args http-wordpress-enum.root="/webservices/wp/",http-wordpress-enum.search-limit="all",http-wordpress-enum.check-latest="true" -p80 <domain>
```