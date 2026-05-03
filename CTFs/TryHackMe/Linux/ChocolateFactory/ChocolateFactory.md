# CTF Writeup: Chocolate Factory

---

- Step 1: made nmap scan -->
- Step 2: made gobuster scan to find hidden dirs --> 
- Step 3: my nmap scan revealed hidden_key dir /key_rev_key after opening it with target_ip it downloaded
an file with encrypted strings
- Step 4: made strings key_rev_key > key.txt --> found a key 
- Step 5: entered ftp connection as anonymous user and installed the .jpg file locally 
- Step 6: made exiftool gum_room.jpg --> to analyze metadata but nothing
- Step 7: made steghide extract -sf gum_room.jpg and pressed enter on passphrase --> "wrote extracted data
to 'b64.txt'"
- Step 8: made cat b64.txt | base64 -d > pass.txt to decrypt strings --> last line was creds from charlie,
but the password was hash encrypted
- Step 9: made john --wordlist=/usr/share/wordlists/rockyou.txt pass.txt --> charlie's creds user:cn7824
- Step 10: logged into webpage --> which prompted me for an command --> made ls to check if it works 
it works.
- Step 11: started up listener and copypasted php rev shell into executable command --> gained rce as www-data
- Step 12: made shell hardening --> went into /charlie dir and made cat teleport --> retrieved private key
- Step 13: Went into my local machine and added id_rsa6 and copypasted his private key in there
- Step 14: made chmod 600 id_rsa6 & ssh -i id_rsa6 charlie@target_ip --> gained ssh access of charlie
- Step 15: retrieved user flag.
- Step 16: made sudo -l --> /usr/bin/vi binary is runnable with root 
- Step 17: researched in GTFOBins for vi binary --> "sudo vi -c ':!/bin/sh' /dev/null" allows you to escelate privileges if it is runnable as superuser by sudo
- Step 18: made it --> root access --> went into root dir --> root.py --> made python root.py --> retrieved root flag.

---

## Key Learnings

- Further Strengthened Privilege Escelation Knowledge
- Increased Knowledge about Binary Exploits
- Slightly increased file extracting knowledge
- Further Strengthened Hash Decoding Knowledge
