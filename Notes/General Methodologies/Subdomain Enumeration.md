
```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://bounty.htb -H "Host: FUZZ.bounty.htb" -fs 630 -mc all
```

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bug-bounty-program-subdomains-trickest-inventory.txt -u http://thetoppers.htb -H "Host: FUZZ.thetoppers.htb" -fs 11952 -mc all
```

Gobuster

```
gobuster vhost -w /usr/share/wordlists/SecLists/Discovery/DNS/subdomains-top1million-5000.txt -u http://thetoppers.htb
```