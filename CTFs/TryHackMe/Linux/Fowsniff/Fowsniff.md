# CTF Writeup: Fowsniff Corp.

---

Step 1: Analyzed Open Ports --> found open pop3 port (110/tcp) and gathered information --> Fowsniff Corp http server  
Step 2: Found there webserver from google --> Hacker hijacked there twitter  
Step 3: Made a twitter investigation --> Hacker posted the databank via pastebin.  
Step 4: Since the md5 was weak, I was able to crack the hashes which I found in the databank.  
Step 5: Selected Auxiliary Scanner "auxiliary/scanner/pop3/pop3_login" in metasploitable "msfconsole"  
Step 6: Created Useracc File and Password File  
Step 7: Within Metaspolitable: "set PASS_FILE fowsniff_passwords" and "set USER_FILE fowsniff_users"  
Step 8: "set RHOST to Target_IP" and typed in "exploit" to start the exploitation.  
Step 9: Unfortunately no proper result with the wordlist  
Step 10: Searched for a proper result and "seina:scoobdyoo2" worked "set PASSWORD scoobydoo2" "set USERNAME seina"  
Step 11: pressed "run" instead of "exploit" and --> Logged in succesfully  
Step 12: Used Netcat "MACHINE_IP <port>" to gain access to the mail server  
Step 13: Within the Mail Server I logged into  pop3 server with the credentials "USER seina" "PASS scoobydoo2"  
Step 14: Used "list" command to show mails and found two messages  
Step 15: Used "RETR 1" to display first message --> its an mail --> Found the SSH Password in the mail  
Step 16: Found SSH Password and logged into ssh of "baksteen"  
Step 17: Checked which groups baksteen belongs to with "id" command and used "find / -type f -group users 2>/dev/null"  
Step 18: Found directory path which lead to cube.sh "/opt/cube/cube.sh"  
Step 19: Before Implementing the Reverse Shell Script I added a listener using netcat "nc -lvnp 4444" -->
nc calls netcat, "-l" listens for active connections and port 4444 specifies port 4444 to listen on.  
Step 19: Implemented Python Reverse Shell into cube.sh file and added my tun0 (vm ip) and port 4444 into it.  
Step 20: Checked "id" if I am root --> I am root. "cd /root" and used "ls". Discovered the flag.txt and finished the CTF.  

---

## Key Learnings

### - Learned about OSINT Techniques, Google Dorking Techniques & Social Media Search Methods
### - Learned about Pastebin (didn't know what it was before, lol)
### - Learned new hash cracker pages for example crackstation
### - Learned about Metasploit & auxilary scanners
### - Learned about netcat & general linux navigations --> directorys like /opt
### - Learned about listeners, reverse shell script generators.
