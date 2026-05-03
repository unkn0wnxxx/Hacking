# CTF Writeup: Whiterose

---

Step 1: nmap scan --> port 80 & 22 ist offen 
Step 2: accessed ip on browser --> no access 
Step 3: ran fuff on SecLists/Discovery/DNS/subdomains-top1million-110000.txt -> found top domain "admin.cyprusbank.thm"
Step 4: added it to /etc/hosts for an dns bypass.
Step 5: Logged in with given credentials on thm website
Step 6: Found Messages tab & changed parameter within URL to 1000, to potentially
display old messages --> found new credentials with higher rights 
Step 7: After loggin in with user acc Gayle Bev I was able to see the Settings page
in which I realised that the passwords which u chose are getting reflected from
the web server --> potential injection vulnerability?
Step 8: Intercepted traffic and stored data package within proxy repeater
and found possible server side template injection by making ' (only works if only the name field is viable)  --> EJS
Step 9: Since the password always gets updated onto the server, we can try
and put an rev shell script into the password parameter --> nc script gets blocked --> CVE 2022-29078 --> Found PoC Rev Shell Script on snyk
Step 10: After testing out multiple scripts, busybox worked out and I gained
rev shell access into the web server, I started by enhancing my shell using TTY scripts
Step 11: Navigated to /home/web and found user.txt flag
Step 12: Made sudo -l to list allowed and forbidden commands. Found NOPASSWD which means I can run the suggested
command without root. --> Did it --> Prompted me into an file.
Step 13: Looks like the file is an Nginx configuration file for a reverse proxy
setup -> It listens on port 80, handles requests for admin.cyprusbank.thm & proxies
traffic into http://localhost:8080
Step 13: Also inspected the sudoedit command and found out it is a stored file within the server --> made man sudoedit to check out what it does --> executes commands as another user --> made locate and found where it's stored
Step 14: Created an environment variable EDITOR with --> export EDITOR="nano -- /root/root.txt" to display root flag within nano environment / exploit sudoedit
Step 15: executed command again --> sudoedit /etc/nginx/sites-available/admin.cyprusbank.thm and it executed the variable --> gained root flag


---

## Key Learnings

- Further strengthened Linux Knowledge
- Further improved Privilege Escalation Methods
- Further improved shell strengthen methodologies
- Learned more about ffuf and wordlists
