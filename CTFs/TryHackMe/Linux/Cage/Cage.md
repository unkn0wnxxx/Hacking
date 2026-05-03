# CTF Writeup: Break out the Cage

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 21,22 & 80 are open
- Step 2: accessed ftp anonymously and retrieved hash encrypted file
- Step 3: decoded the hash with base64 -d and went to boxentriq webpage to findout what kind
of cipher it is --> vigenere 
- Step 4: went to dcode.fr, searched up viginere cipher and copy & pasted the strings inside it
- Step 5: retrieved password of weston. --> logged in with ssh
- Step 6: since I did not had access to cage's dir I had to find a way in.
- Step 7: in the /opt directory I found a hidden dir and a hidden python script, which acts as an cronjob. 
- Step 8: since I had write access in this directory, I changed the .quotes and added an python rev shell on port 
- Step 9: started up listener & gained rce as cage,after some time and retrieved user.txt flag 
- Step 10: read his emails and found another viginere cipher and the Key/password "FACE" got mentioned a lot of times.
- Step 11: --> gained password from Sean/Root --> "cageisnotalegend"
- Step 12: made "su" and gained root rce --> retrieved root flag in email_2

---

## Key Learnings

- Immenly strengthened Knowledge about Vi/Vim
- Learned about ciphers
- Immensly strenthened decoding knowledge.
