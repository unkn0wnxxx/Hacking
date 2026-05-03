# CTF Writeup: Blue

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 135,139 & 445 are open
- Step 2: made smbclient -L \\\\<target_ip>\\ gained all shares
- Step 3: made msfconsole -q --> search eternalblue --> use 0
- Step 4: configured it and gained meterpreter session
- Step 5: made shell
- Step 6: navigated to haris/Desktop dir and retrieved user.txt
- Step 7: navigated to Administrator/Desktop/ dir and retrieved root.txt, since I got 
nt authority/system

---

## Key Learnings

- Learned some Knowledge about the MS017-10 Exploit/WannaCry Worm.
