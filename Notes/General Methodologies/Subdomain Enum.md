
```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://bounty.htb -H "Host: FUZZ.bounty.htb" -fs 630
```