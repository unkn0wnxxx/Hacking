# CTF Writeup: Agent Sudo

---

- Step 1: added target_ip to /etc/hosts & enumerated open ports --> 21,22 & 80 are open
- Step 2: website displayed me that I need to use my codename as user-agent,
since Agent R is named by the alphabet --> made curl -A "R" -L target_ip
and found out 25 more users 25 + 1 = 26 like on the alphabet, so I tried all
- Step 3: made curl -A "C" -L target_ip and retrieved information about potential user "chris"
- Step 4: made hydra -l chris -P /usr/share/wordlists/rockyou.txt target_ip ftp
--> retrieved password from chris --> crystal
- Step 5: logged into ftp and get'd both pictures & .txt file
- Step 6: cat'd .txt --> password is stored in pictures 
- Step 7: ran steghide on both --> cute-alien.jpg requires a passphrase
- Step 8: made stegseek -sf cute-alien.jpg /usr/share/wordlists/rockyou.txt 
--> found passphrase: Area51
- Step 9: made steghide extract -sf cute-alien.jpg --> retrieved messages.txt
- Step 10: retrieved creds "james:hackerrules"
- Step 11: made binwalk -e cutie.png --> retrieved hidden binaries in new dir 
- Step 12: made zip2john .zip file > zip.hash
- Step 13: john zip.hash --wordlist=/usr/share/wordlists/rockyou.txt
- Step 14: retrieved password for zip --> alien --> extracted msg --> picture: "QXJlYTUx"
- Step 11: logged into ssh james --> retrieved user flag.
- Step 12: made sudo -l and saw that every bash is runnable only root privs arent --> checked for 
exploit for this 
- Step 13: found CVE-2019-14287 --> made sudo -u#-1 /bin/bash --> gained root shell
--> retrieved root flag

---

## Key Learnings

- Further strengthened brute force knowledge
- Further strengthened Steganography methodology
- Added new tool to the arsenal --> binwalk
- Learned about new privilege escelation --> sudo -l !root
- Further Strengthened Reverse Image Search Knowledge --> TinEye
