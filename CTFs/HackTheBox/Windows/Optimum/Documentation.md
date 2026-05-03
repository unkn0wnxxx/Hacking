# CTF Writeup: Optimum

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 80 is open
- Step 2: ran nmap with --script=http-vuln* --> retrieved some informations, but didn't find a good cve. 
- Step 3: made msfconsole -q && search HttpFileServer 2.3 --> found Rejetto exploit.
- Step 4: configured it and ran it --> retrieved rce as kosta user.
- Step 5: made search local_exploit_suggester & ran it --> retrieved some possible
privilege escelation vectors, tried some of those
- Step 6: ms16_032_secondary_logon_handle_privesc --> worked
- Step 7: gained nt authority & retrieved root.txt flag.

---

## Key Learnings

- Slightly increased Metasploit Knowledge.
