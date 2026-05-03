# CTF Writeup: Agent T

---

Step 1: Started basic enumeration --> nmap scan port 80 open & PHP 8.1.0 dev --> gobuster no results
Step 2: Analyzed webpage & source-code 
Step 3: After performing multiple methodologies until html injection, I was stuck & googled php 8.1.0 dev
Step 4: Apparently this php version has an backdoor related to the http header --> user agent which can be exploited
manually & with given Exploits on github
Step 5: Decided to check out multiple RCE's and decided to go with the one from flast101
Step 6: made wget on raw github file --> otherwise wouldn't work
Step 7: executed exploit with --> python3 a.py --> got rev shell into server --> made whoami --> root, but couldn't
navigate through server properly. I was stuck in path /var/www,
Step 8: made find / -iname "*.txt" to check if flag is within server --> /flag.txt there --> made cat /flag.txt 

---

## Key Learnings

- Further improved my methodology and strengthened my enumeration skills
- Slightly increased linux knowledge
- Slightly increased my RCE Knowledge
- Increased HTTP Header Knowledge & Packet Manipulation Knowledge

