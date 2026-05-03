# CTF Writeup: Creative

---

- Step 1: Enumeration --> Tried accessing website through target ip, no access.
Made DNS Bypass --> Got access to website.
- Step 2: Nmap scan --> port 80 & 22 is open
- Step 3: gobuster scan no results
- Step 4: After not getting any results, I installed a vhost_fuzzer script, 
which found beta top domain
- Step 5: Accesing this domain, prompted me to enter an URL, after trying
some filter method bypassing I discovered that the mechanic acts as a proxy.
- Step 6: As result I was searching for a script which port scans loopback ipv4
--> found ssrfmap.py, installed this one and executed it --> found out that port 1337 is open.
- Step 7: After adding 127.0.0.1:1337 inside the prompt field, gained file listing
to the target file system.
- Step 8: Used my burp request in my repeater to enter the file system and
gather outputs from the target file system. --> e.G /etc/passwd
- Step 9: made url=http://127.0.0.1:1337/home/saad/.ssh/id_rsa to receive
private key from user saad
- Step 10: ssh login of saad requires me to have a passphrase, checked for
ssh passphrase crackers, found ssh2john. Used it and got the hash value of the
private key.
- Step 11: made "john id_rsa.hash /usr/share/wordlists/rockyou.txt and cracked
the ssh password "sweetness"
- Step 12: Gained Access on ssh saad made cat /home/saad/user.txt and found user flag.
- Step 13: Checked out .bash_history and found password of saad
- Step 14: made sudo -l, found potential privilege escalation,
googled it and followed the tutorial.
- Step 15: after executing it --> found root flag in root directory.

---

## Key Learnings

- Gained better fuzzing tools
- Learned more about proxys
- Gained SSRFmap script for loopback ipv4's/proxys
- Gained knowledge about john -> for cracking ssh passwords.
- Further strengthened knowledge about directorys.
- Further increased knowledge about request manipulations.
