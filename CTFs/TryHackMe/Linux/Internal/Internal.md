# CTF Writeup: Internal

---

- Step 1: added <target_ip> to /etc/hosts & ran nmap scan: 22 & 80 open
- Step 2: ran gobuster & ffuf for hidden dir & sub-domain enumeration --> wordpress 5.4.2
- Step 3: enumerated further and found /blog/wp-login.php
- Step 4: found username admin, just by testing it out
- Step 5: made wpscan --url http://internal.thm/blog/ --username admin --passwords /usr/share/wordlists/rockyou.txt --> retrieved password: my2boys
- Step 6: logged into wordpress and went to templates --> index.php and replaced it with
--> pentest revshell
- Step 7: started listener and called url: http.//internal.thm/blog
- Step 8: gained rce
- Step 9: went into /opt directory and retrieved user creds aubreanna:bubb13guM!@#123 from .txt file
- Step 10: logged into aubreanna with ssh --> retrieved user.txt
- Step 11: made netstat -ano to check which services are running, since in the file it says
there is jenkins running on 172.17.0.2:8080 is true.
- Step 12: ssh -L 8080:172.17.0.2:8080 aubreanna@internal.thm to create an ssh tunnel
with service running on port 8080 --> can display jenkis website now
- Step 13: brute-forced password of admin user --> spongebob
- Step 14: logged in and went to "Manage Jenkins", scrolled down to "Script Console" and added
groovy rev shell 
- Step 15: started listener and gained rce with jenkins user.
- Step 16: went to /opt directory and made cat notes.txt
--> retrieved root creds "root:tr0ub13guM!@#123" 
- Step 17: retrieved root.txt in /root dir

---

## Key Learnings

- Strengthened WordPress Enumeration Knowledge
- Strengthened wpscan Knowledge
- Strengthened Privelege Escalation Knowledge --> Discovering hidden services and how to unlock them
with ssh tunnels
- Strengthened Knowledge about Brute-Forcing
- Strengthened Jenkins Knowledge
