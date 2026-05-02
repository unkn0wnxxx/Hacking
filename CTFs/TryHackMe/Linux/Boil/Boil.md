# CTF Writeup: Boil

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 21, 80, 10000 and 55007 are open
- Step 2: ran gobusterscan & found /joomla hidden dir --> enumerated further with dirsearch 
- Step 3: retrieved joomla version 3.9 --> /joomla/README.txt
- Step 4: ran dirsearch on /joomla and retrieved hidden dir _test --> sar2html --> possible cve's?
- Step 5: CVE says there is command injection on when u add the ?plot=; parameter behind the index.php
- Step 6: which is getting done automatically, when clicking on the "New" functionality --> made ls --> works
- Step 7: displayed a log.txt --> made cat log.txt --> ssh creds --> basterd:superduperp@$$
- Step 8: made ssh basterd@boil.thm -p 55007 --> gained ssh --> made cat backup.sh --> gained creds
from user stoner
- Step 9: logged in via ssh and retrieved .secret flag 
- Step 10: made find / -perm /4000 2>/dev/null to check potentially exploitable SUID Binaries
--> /find looks interesting -- checked out gtfobins
- Step 11: made ./find . -exec /bin/sh -p \; -quit
- Step 12: gained root rce and retrieved root flag

---

## Key Learnings

- Further Strengthened Web Pen Methodology
