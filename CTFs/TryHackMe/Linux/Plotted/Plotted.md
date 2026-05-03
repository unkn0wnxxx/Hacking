# CTF Writeup: Plotted

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 22,80 & 445 --> another http open
- Step 2: enumerated 2. http server and ran gobuster on it to retrieve hidden dir's
- Step 3: found login page on /management/admin/login.php
- Step 4: captures package with burpsuite proxy & ran sqlmap on it. --> didnt work
- Step 5: tried manual SQL Injection --> this payload worked ' OR 1 -- - 
- Step 6: uploaded rev shell into avatar and gained rce as www-data user
- Step 7: found active cronjob with privileged user, but wasn't able to edit it tho.
- Step 8: /var/www/scripts dir is writable, so I created a rev shell script named backup.sh
on my local machine
- Step 9: created a python server and wget the script into the /scripts dir. removed the old one
- Step 10: made chmod +x on it, so it executes and started up listener
- Step 11: gained rce as privileged user and retrieved user.txt
- Step 12: made find / -perm /4000 2>/dev/null to check for SUID Binaries --> doas
- Step 13: doas binary is exploitable with openssl --> file read as root user
- Step 14: made doas -u root openssl enc -in /root/root.txt
- Step 15: gained root flag

---

## Key Learnings

- Further strengthened enumeration skills
- Strengthened privilege escelation methodology.
