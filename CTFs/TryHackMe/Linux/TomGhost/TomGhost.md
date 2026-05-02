# CTF Writeup: TomGhost

---

- Step 1: added target_ip to /etc/hosts & ran nmap scan --> 22,53, 8009, 8080 open
- Step 2: ran gobuster scan and analyzed website on :8080 --> /manager is restricted
and retrieved login creds of tomcat
- Step 3: port 53 is blocked tcp stream --> made stealth nmap scan and foundout that it is a domain
- Step 4: searched for ajp cve's on metasploit --> found lfi vuln --> made use 0
- Step 5: gained creds --> skyfuck:8730281lkjlkjdqlksalks
- Step 6: retrieved user flag 
- Step 7: retrieved tryhackme.asc & credentials.pgp from directory
--> went on local machine and made scp skyfuck@target_ip:/home/skyfuck/* .
- Step 8: converted tryhackme.asc (private gpg key) to hash --> made gpg2john tryhackme.asc --> hash
- Step 9: made john hash --wordlist=/usr/share/wordlists/rockyou.txt --> password: alexandru
- Step 10: made gpg --import tryhackme.asc and gained private gpg key 
- Step 11: made gpg --decrypt credential.gpg --> retrieved merlin:asuyusdoiuqoilkda312j31k2j123j1g23g12k3g12kj3gk12jg3k12j3kj123j
- Step 12: logged in with user merlin
- Step 13: made sudo -l and saw /zip binary --> went to gtfobins and ran the exploit
- Step 14: gained root and retrieved root flag

---

## Key Learnings

- Immensly strengthened Methodology
- Learned about .pgp files and gpg private keys 
- Added new tool to the arsenal gpg2john 
