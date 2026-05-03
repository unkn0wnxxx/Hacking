# CTF Writeup: Cap

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 21,22 & 80 are open
- Step 2: analyzed webpage --> icmp scan functionality built into the website
- Step 3: URI is displayed as /6 --> tried 00 to check for older network scans --> downloaded it
- Step 4: made wireshark 0.pcap --> found user creds of nathan --> logged into ftp & ssh
- Step 5: retrieved user.txt flag 
- Step 6: checked linpeas output --> tried multiple stuff out, python3.8 has capabilities,
since the administrator added the network scan functionality into the webpage, he had to give
this capability to python3.8. So non-root users can also use the functionality.
- Step 7: executed the binary --> made /usr/bin/python3.8 went into python mode
- Step 8: made import os
- Step 9: made os.setuid(0)
- Step 10: made os.system("/bin/bash") --> gained root rce and retrieved root.txt

---

## Key Learnings

- Further strengthened URI Knowledge
- Further Increased Priv Esc Methodology
- Further Increased Python Knowledge
