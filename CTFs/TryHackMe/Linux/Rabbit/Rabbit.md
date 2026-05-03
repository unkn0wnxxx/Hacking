# CTF Writeup: Year of the Rabbit

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 21,22 & 80 are open
- Step 2: ran gobuster and retrieved /assets dir --> in styles.css source, I retrieved a hidden dir
- Step 3: it prompted me to turn off javascript, which I did. Once I reloaded the page --> the URL changed a bit and got the parameter hidden-dir --> copy & pasted the hidden dir
- Step 4: retrieved a new directory page with a .png file which I downloaded.
- Step 5: made strings <png> and retrieved "ftpuser" and a wordlist to brute-force
- Step 6: made hydra -l ftpuser -P /home/unkn0wn/Desktop/wordlist.txt ftp://rabbit.thm/
- Step 7: retrieved password and logged into ftp.
- Step 8: downloaded "Eli's Creds" locally, but it's encrypted with the "brainfuck programming language" --> decoded it on md5decrypt.net
- Step 9: gained eli's creds and logged into ssh --> Gwendoline User exists and s3cr3t place exists
- Step 10: made find / -iname "s3cr3t" and found it under /usr/games/s3cr3t --> gained gwendoline's credentials.
- Step 11: retrieved user.txt and made sudo -l --> /usr/bin/vi /home/gwendoline/user.txt is runnable with sudo rights and doesn't require password authentification
- Step 12: checked sudo --version --> older version than sudo 1.8.28 --> means it's exploitable with sudo -u#-1 /bin/bash
- Step 13: in this case I am only able to run sudo with a given binary set,
so I made sudo -u#-1 opened up the editor vi and made :!/bin/bash to overwrite the /bin/bash and execute the exploit 
- Step 14: gained rce and retrieved root.txt

---

## Key Learnings

- Improved Methodology in CTF-like environments
- Improved Steganography slightly due to strings
- Strengthened decoding skills using dcode.fr
- Immensly strengthened PrivEsc Knowledge.
