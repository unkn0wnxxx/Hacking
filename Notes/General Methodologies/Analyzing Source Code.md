
One mistake many people make is seeing a web server and immediately starting `gobuster` or `dirsearch` with huge wordlists for 30–40 minutes.

In OSCP-style machines, important files are often sitting in plain sight because directory listing or sloppy backups are enabled.

First, just look at the page source quickly:

```
curl -s http://$ip/ | grep -Ei "bak|old|conf|sql|zip|~"
```

You’ll often find files like:

- `config.bak`
- `backup.zip`
- `db.sql`
- `settings.old`

Download them and inspect:

```
wget http://$ip/config.bak
```

Many times, credentials are sitting inside these files. No brute force, no exploits needed.

Also check directly for Apache auth files before hunting for vulnerabilities:

```
curl http://$ip/.htaccess  
gobuster dir -u http://$ip -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -x bak,old,conf,htpasswd
```

If you find a `.htpasswd` file, crack it offline with John.