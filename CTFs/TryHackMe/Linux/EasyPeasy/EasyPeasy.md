# CTF Writeup: EasyPeasy

---

- Step 1: added target_ip into /etc/hosts and made nmap scan --> 80, 6498 ssh & 65524 is open
- Step 2: ran gobuster scan and found /hidden/whatever --> first flag in source-code
- Step 3: found hash encrypted file in target_ip:65524/robots.txt decoded it on
md5hashing website --> gained 2nd flag
- Step 4: found third flag on :65524 source code and found basexx encoded payload again
- Step 5: went to cyberchef and decoded it --> hidden path --> went to it --> retrieved hash value
- Step 6: checked out hint --> gost format --> made john hash.txt --format=gost --wordlist=/home/unkn0wn/Desktop/easypeasy_1596838725703.txt > hash_results1.txt --> got password: mypasswordforthatjob
- Step 7: After finding this password, I downloaded the .jpg file which was in the hidden dir too& 
made steghide extract -sf Untitled.jpg --> entered passphrase --> retrieved creds.
user: boring and binary encrypted password 
--> decoded it on cyberchef with "From Binary": iconvertedmypasswordtobinary
- Step 8: logged into ssh and made cat user.txt --> is rotated --> after some research
--> made echo "synt{a0jvgf33zfa0ez4y}" | rot13" retrieved correct flag.
- Step 9: found hidden crontab which will run as root apparently, but doesnt display it
--> nano'd into it and made "sudo root cat /root/root.txt > /tmp/flag.txt" --> didn't work
- Step 10: tried implementing bash rev shell script and started listener
--> gained rce as root --> retrieved .root.txt flag --> that's why my initial script didn't work.

---

## Key Learnings

- Further Strengthened Enumeration Skills
- Further strengthened linux cli Knowledge --> | rot13
- Improved decryption and general hash knowledge
