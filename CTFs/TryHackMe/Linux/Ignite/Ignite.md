# CTF Writeup: Ignite

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> only 80 open
- Step 2: enumerated directories with gobuster --> /fuel gave me a loginpage
- Step 3: intercepted traffic and saved package in req.txt --> ran sqlmap -r req.txt --dump.
--> no sql injections possible
- Step 4: found out on the webpage that basic creds are admin:admin --> tried those and logged in
- Step 5: no is working --> looked for cve's for fuel CMS --> CVE-2018-16763.
- Step 6: executed python script and gained a weak shell
- Step 7: created bash revshell locally. "shell.sh", started python server and made wget shell.sh onto target
- Step 8: started a listener locally and made bash shell.sh --> gained improved rce on target
- Step 9: retrieved user flag
- Step 10: made find / -perm /4000 2>/dev/null to find potentially exploitable SUID Binarys.
--> no results
- Step 11: after further enumeration I found creds in /var/www/html/fuel/application/config/database.php
- Step 12: root:mememe 
- Step 13: made su root --> and prompted password in
- Step 14: gained root and retrieved root flag.

---

## Key Learnings

- Increased Privige Escelation Methodology 
- Slightly increased Knowledge about potential CVE's
