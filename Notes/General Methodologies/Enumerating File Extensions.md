
```
feroxbuster --url http://intelligence.htb -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi,pdf
```



```
gobuster dir -u http://<target_ip>/ -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi
```
###### Redirect

```
gobuster dir -u http://oscp:20000/ -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip,json,docx,aspx,asp,cgi --exclude-length 201
```