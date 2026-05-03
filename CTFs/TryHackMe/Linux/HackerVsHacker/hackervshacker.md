# CTF Writeup: Hacker vs Hacker

---

Step 1: Started off with basic enumeration --> made nmap -sS -sC -sV <target_ip> --> 22 & 80 are open
Step 2: After accessing webpage I analyzed it and tried to upload my rev shell --> Got prompt to an script where the hacker explained what he did
Step 3: Informations I got out of this --> he installed his shell into /cvs directory and bypassed it with adding '.pdf
 into the file "shell.pdf.php".
Step 4: After that I made a gobuster command to filter out which hidden dir's/files are within the webserver and to make sure, that my assumption
is correct. made --> gobuster dir -u http://10.10.92.205/cvs/ -w /usr/share/SecLists/Discovery/Web-Content/raft-small-words.txt -x '.pdf.php'
Step 5: Got a hit on /cvs/shell.pdf.php --> my assumption was right --> accessed website --> found his payload
Step 6: Added "?cmd=" to the url to execute commands --> it works. --> possibility of injecting my own rev shell
Step 7: Added listener --> made nc -lvnp 1337 --> Copied bash rev shell script into filter, but didnt work
Step 8: Tried bypassing by encoding the payload with base64 --> Didn't work because there was special symbols, had to adjust the string properly & it worked after.
Step 9: After gaining a shell in the web server I strengthened it with pty.spawn; & immediatly made sudo -l, no commands which are runnable without root privs
Step 10: went into home directory --> lachlan user --> found user flag --> checked out .bash_history out
Step 11: Found out scripts which were executed and checked everything out --> Hacker tried to delete scripts with cronjobs in cron.d
Step 12: Found password of lachlan --> made su lachlan & logged in
Step 13: Checked out the "persistence" cronjob in /cron.d out --> made ls -la --> Found out nope script --> Analyzed script --> my assumptions is that it blocks the ssh access
Step 14: After trying it out --> I gained access for 3-4 seconds and after it prompted "nope" and I got kicked out of the ssh. --> My assumption was right
Step 15: I analyzed his scripts further more within -> /etc/cron.d/persistence and found out that the script 
basically gets forwarded into my active cli sessions by forwarding it into dev/pts/
while the nope he echo's get's displayed the pkill file doesn't at all, so I am assuming we can hijack this file and change what it is executing.
Step 16: went into path of the script --> cd /home/lachlan/bin --> made touch pkill --> for testing purposes I will add an payload into my pkill file
Step 17: Made --> echo 'ls -la > /tmp/out' > pkill -> made cat pkill to check if it was forwarded --> outputted correctly 
Step 18: Checked ssh access again, should not be kicking me out anymore. --> Got kicked --> After further testing I realised that I didn't add executable rights to other users --> made chmod +x pkill
Step 19: Still prompts out nope, but doesn't killswitch my ssh connection anymore.
Step 20: Went into the /tmp folder and wasn't able to go into out, since I do not have root privs, but --> made cat /tmp/out and saw the root.txt there.
Step 21: My assumption is since I can execute the pkill file within the web server on lachlan user now, I will add another php shell in there and execute the file to get root access.
--> base64 encoded my php shell script & forwarded the echo base64 string in to the pkill file --> made cat pkill --> got rev shell root access on my listener
--> found flag
  

---

## Key Learnings

- Strengthened Rev Shell Knowledge & hardening of the rev shell
- Further strengthened gobuster knowledge by searching for integrated file types, which I've never done before.
- Further improved php payload knowledge & base64 knowledge "+" must be deleted out of payload
- Improved linux knowledge --> cronjobs = killscripts
- Improved Knowledge of file upload bypasses --> shell.pdf.php
