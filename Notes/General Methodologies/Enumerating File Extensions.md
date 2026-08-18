
```
feroxbuster --url http://intelligence.htb -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
```

```
feroxbuster --url http://intelligence.htb -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
```

Authenticated

```
feroxbuster --url https://streamio.htb/admin -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -k -x php -b "PHPSESSID=h0pkknlltm6lcg94gu8uas8muh"
```

```
gobuster dir -u http://<target_ip>/ -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi
```
###### Redirect

```
gobuster dir -u http://oscp:20000/ -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi --exclude-length 201
```