 # CTF Writeup: SilverPlatter

---
 
- Step 1: Enumeration -> nmap scan to check which ports are open -> ssh, http, http-proxy is open
- Step 2: Was able to get access to the website --> via port 80, 
gathered information about a software named "Silverpeas" and username "scr1ptkiddy"
- Step 3: used gobuster to find hidden directorys, on port 80 and 8080 
- Step 4: found silverpeas on port 8080 -> defaultlogin page.
- Step 5: Looked up SilverPeas documentation and found out that u can login with default credentials 
"SilverAdmin/SilverAdmin". Login failed tho
- Step 6: Login Page displayed current Version of SilverPeas "2001-2022".Looked up CVE's for SilverPeas 
- Step 7: Found CVE-2024-36042 on GitHub, apparently you can bypass authentification by capturing
login request within burp suite and removing the password field from the request. System thinks
it's like an ssh entry.
- Step 8: Gained access to the admin panel, but didn't find any good information
- Step 9: Since I don't have a shell I continued looking for some other CVE's 
and found CVE-2023-47323, which allows us to see all messages,
- Step 10: send a request to http://localhost:8080/silverpeas/RSILVERMAIL/jsp/ReadMessage.jsp?ID=1,2,3,4,5
and got some messages displayed by 5 I found a message in which ssh creds were displayed, ssh username = tim and password
- Step 11: Checked out which privileges we have and checked out which users exist and which privileges they own
by using "cat /etc/passwd | grep sh$" --> root, tyler and tim
- Step 12: by using "id" I realised that current user (tim) is a part of the adm group. Which gives
read access to log files on the system. 
- Step 13: made cat /var/log/auth* | grep -i pass, to see all password logs, found tyler's password
and logged in via ssh
- Step 14: checked sudo privilges of current user (tyler) -> able to run any command on root
- Step 15: made sudo su to get admin privileges and checked out the root flag.
 
---

## Key Learnings

- Learned about CVE's and that I should always check for versions.
- Further increased burp suite knowledge.
- Learned about message ID = ,,,
- Further improved directory knowledge and linux cli knowledge with using "cat /etc/passwd | grep sh$" and "id" 
