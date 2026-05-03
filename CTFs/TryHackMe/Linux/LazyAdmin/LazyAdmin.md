# CTF Writeup: LazyAdmin

---

- Step 1: added target_ip to /etc/hosts & enumerated open ports --> 22,80
- Step 2: ran gobuster scan and found /content hidden dir 
- Step 3: enumerated /content further and found /as --> login page & /inc where
I retrieved an SQL database --> retrieved hash encrypted string --> decoded it with crackstation
Password123 and username: manager
- Step 4: logged in and checked out media center --> uploaded php rev shell and started listener
- Step 5: gained rce and retrieved user flag
- Step 6: made sudo -l --> backup.pl is runnable without password on root privileges
- Step 7: made cat backup.pl is an rev shell, but ip is external one in /etc/copy.sh
- Step 8: made ls -la /etc/copy.sh is writable too
- Step 9: went into /etc dir & made echo "/bin/bash" > copy.sh
- Step 10: executed sudo /usr/bin/perl /home/itguy/backup.sh --> gained root shell
- Step 11: retrieved root flag

---

## Key Learnings

- Further strengthened enumeration skills
- Further improved upload filter bypassing --> .php7
- Further improved recon skills --> which attack abuser can be abused to gain RCE
