

## FTP

- Anonymous Access [x]
- Important Files [x]
- Write Access
- Bruteforcing ftp

## HTTP (80) 

- Enumerating endpoints [x]
- Enumerating subdomains
- robots.txt [x]
- Enumerating server side language --> php 7.3.22
- Any Services ran? [x]
- Vulnerability Assessment

#### login page

Reflects if users exist

admin,administrator & root don't exist!

/humans.txt endpoint could offer us with users, although they seem to be just contributors.

There is also an interesting /system endpoint, but we can't access it. We get an 403 server response.
## REDIS

- Anonymous Access
- Vulnerability Assessment --> Redis store 5.0.9

