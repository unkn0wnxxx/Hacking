# CTF Writeup: Publisher

---

- Step 1: Made nmap scan -> ssh & http are open
- Step 2: Made gobuster --> found spip dir & login dir
- Step 3: Achieved new tool to find out service versions --> Wappalyzer --> spip = 4.2.0 version
- Step 4: Looked up for CVE's with RCE --> searchsploit spip 4.2.0 --> hit
- Step 5: wget the exploit on my Desktop, setup listener & made python3 CVE-2023-27372.py to execute file with proper target ip
--> received RCE in server
- Step 6: gained access on think user --> gathered user flag --> made ls -la --> found .ssh hidden dir --> retrieved privatek ey of user think onto my desktop
- Step 7: made chmod 600 id_rsa to limit permissions --> otherwise ssh automatically blocks it
-Step 8: logged in with ssh think@target ip -i id_rsa --> to bypass password
 Step 9: Given hint said to check out apparmor folder, so I navigated to /etc/apparmor.d
- Step 10: Here I was stuck, but walkthroughs made ls -la /bin/bash, which is a common technique to priv escalate
- Step 11: /bin/bash has root privileges, so this is the way to go, switched to /dev/shm because this dir always has
read/writing rights. copied /bin/bash into this dir --> cp /bin/bash and executed file ./bash -p --> we got access to the /opt folder now
- Step 12:  in this folder we found writeable root datasets. --> made nano run_container.sh and added cp /bin/bash /tmp/default chmod +s /tmp/default --> This copied the /bin/bash as root and having it as /tmp/default and 
also gave it +s permissions which gives privs of the previous file owner (root).
- Step 13: Ran the file with run_container --> made cd /tmp default is there --> made ./default -p --> got root --> got flag

---

## Key Learnings

- Drastically Improved Privilege Escalation Knowledge by checking Bash Binary ls -la /bin/bash
and root datasets, which can be abused to transfer those privileges in external scripts to get root privs. 
- Further strengthened linux cli knowledge
- Improved Methodology in general
- Improved CVE Knowledge --> searchsploit etc..

