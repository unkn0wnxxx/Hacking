# CTF Writeup: Dreaming

---

- Step 1: Started with nmap specific scans on p1-p49500 --> no results --> made gobuster and found subdirectory /app
with outdated spluck page and forwarded another subdirectory --> login panel after pressing admin link
- Step 2: was able to login with "password" & also found an rce cve for the pluck-4.7.13 version, but doesn't work for me
- Step 3: found file upload option, tried .php shell upload --> worked, but after executing the file i didn't get rce on my listener.
- Step 4: after some research I tried it with php archiva ".phar" so I made --> mv test.php test.phar --> uploaded it &
gained rce
- Step 5: After navigating through a lot of directorys, I found 2 python scripts within the /opt dir and made 
--> cat test.py --> found password of Lucien HeyLucien#@1999! --> made su lucien --> logged in --> got luciens flag
- Step 6: decided to login with ssh in lucien --> made cat .bash_history --> got mysql creds of lucien mysql -u lucien -plucien42DBPASSWORD
made mysql -u lucien -p --> prompted in password of lucien --> gained access into mysql database
- Step 7: My knowledge is very limited so I had to watch a walkthrough how to navigate in mysql database --> made use information_schema; --> made show tables; --> USER_PRIVILEGES table looked interesting --> but didn't provide much information -> exited out
- Step 8: made sudo -l --> I am able to run getDreams.py script as death user without password --> made sudo -u death /usr/bin/python3 /home/death/getDreams.py
- Step 9: received some outputs which didn't help me, but confirmed the script ran. --> logged into mysql again
made --> show library; --> made use library; --> made show dreams; --> select * FROM dreams; --> to create more privs
I tried to use an sql query bypass 
--> made Insert into dreams (dreamer, dream) values ("voldemort;/tmp/voldemort", "n00b"); which creates an file with death rights, in there I will store an rce
- Step 10: made nano /tmp/voldemort and copied normal bash shell in there + added shebang statement "#!/bin/bash"
- Step 11: Started listener on port 1234 and added +x rights on voldemort file in /tmp, executed getDreams.py with sudo -u death /usr/bin/python3 /home/death/getDreams.py again and gained rce
- Step 12: Made shell hardening on new rce --> went into death user dir and retrieved user flag. also retrieved his password by displaying the getDreams.py script in his directory. !mementoMORI666!. logged in with ssh
- Step 13: navigated into morpheus dir and made cat .viminfo in which an interesting python file got executed 
--> /usr/lib/python3.8/shutil.py, it's owned by death but runs with root, so I will exploit it. 
--> made nano shutil.py and made ^+W to search for copy define function, added the line 
--> "os.system("chmod +rwx /home/morpheus/morpheus_flag.txt"); 
which would result into me getting rights on the last flag. The script itself is not executable by me, but it runs as an cronjob under user 1002 (morpheus).
- Step 14: retrieved last flag. 

---

## Key Learnings

- One of the hardest CTF's I've done so far. Learned a lot about mysql general knowledge and cli navigation. 
- Integrated new knowledge about manual mysql bypasses --> Insert into dreams(dreamer, dream) values ("voldemort;/tmp/voldemort","n00b") which allowed me to get rce in higher privs.
- Further strengthened RCE Knowledge
- Increased Linux CLI Knowledge
- Strengthened privilege escelation knowledge
