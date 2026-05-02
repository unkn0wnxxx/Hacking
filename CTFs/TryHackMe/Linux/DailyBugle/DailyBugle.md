# CTF Writeup: DailyBugle

---

- Step 1: added <target_ip> to /etc/hosts & made nmap scan --> 22,80 & 3306 are open
- Step 2: ran gobuster scan to enumerate hidden dir's --> found jommla login page
- Step 3: researched how to find out the joomla version --> accessing the following url:
http://dailybugle.thm/administrator/manifests/files/joomla.xml --> version 3.7.0 
- Step 4: searched for cve's for joomla 3.7.0 -> found python script and ran it
- Step 5: python3 joomblah.py http://target_ip --> gained user jonah:$2y$10$0veO/JSFh4389Lluc4Xya.dfy2MF.bZhz0jVMw.V.d3p12kBtZutm
- Step 6: decrypted hash password with john --> made john password --> spiderman123
- Step 7: logged into Joomla CMS & copy & pasted pentest monkey php shell under templates --> index.php
- Step 8: reloaded target ip website + started listener --> gained rce --> went to /var/www/html
- Step 9: configuration.php and found password and "secret"
- Step 10: made su jjameson and logged in with password.
- Step 11: retrieved user flag
- Step 12: made sudo -l --> /usr/bin/yum is runnable with root privelges and without password
- Step 13: went to gtfobins and copy-pasted option b)
- Step 14: gained root
- Step 15: retrieved root flag

---

## Key Learnings

- Slightly strenghtened Enumeration Knowledge
- Strengthened Knowledge about Gaining Access Methodology
- Slightly strengthened priv esc knowledge
-
