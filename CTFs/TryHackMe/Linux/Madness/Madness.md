# CTF Writeup: Madness

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 22 & 80 are open
- Step 2: ran gobuster & ffuf, but didn't receive any results for sub-domains or hidden dir's
- Step 3: analyzed default apache webpage source code --> retrieved "thm.jpg" made wget http://<target_ip>/thm.jpg
- Step 4: jpg file was not viewable, hex binarys were messed up. made python3 magicbytes -i thm.jpg -m jpg --> fixxed it and gained hidden dir
- Step 5: secret between 0-99 --> 73 --> added ?secret=73 parameter to URL and gained passphrase
- Step 6: gained username 
- Step 7: extracted picture on tryhackme --> steghide extract -sf .jpg --> gained password.txt
- Step 8: made "wbxre" | rot13 --> to get proper username --> joker
- Step 9: logged in via ssh and retrived user.txt flag 
- Step 10: made find / -perm /4000 2>/dev/null and found weird binarys --> screen-4.5.0
tried to analyze them, but didn't retrieve anything. googled them and found exploit
- Step 11: copied exploit.sh into /tmp directory an ran it --> gained root and retrieved flag.

---

## Key Learnings

- Increased Knowledge with hex values and magicbytes
- Slightly increased methodology with priv esc and binarys
