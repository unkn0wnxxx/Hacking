# CTF Writeup: Legacy

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 135,139 & 445 are open
- Step 2: made nmap --script=smb-vuln* <target_ip> --> to check for vulnerabilities
- Step 3: made msfconsole -q
- Step 4: made search <cve> 
- Step 5: configured the options and ran the exploit --> gained meterpreter session
- Step 6: made shell and retrieved user.txt & root.txt flags.

---


## Key Learnings

- Slightly increased cve enumeration skills with nmap
