# CTF Writeup: Expose

---

Step 1: Found out website doesn't respond to http requests
Step 2: made nmap scan --> ftp open --> open to anoynmous users
Step 3: logged in with anonymous user in ftp connection
Step 4: user and pass required to execute commands --> how do I find out the creds?
Step 5: made more detailled nmap scan --> nmap -sS -sC -sV target_ip -p1-5000 --> port 1337 open for web server 
Step 6: made --> ffuf -w /usr/share/dirb/wordlists/big.txt:FUZZ -u http://target_ip:1337/FUZZ -mc 200,301 --> 
received multiple admin panels, 1 admin panel autofilled user "hacker@root.thm" 
Step 7: tried manual sql injections, but didn't work on admin_101 panel --> used sqlmap 
--> captured packet in burpsuite and copypasted into file "req.txt" --> made sqlmap -r req.txt --dump- --> found password
for user
Step 8: logged in with password VeryDifficultPassword!!#@#@!#!@#1231 --> no good information. Tried the other results
sqlmap gave me file1010111/index.php --> printed in password "easytohack" and got access. Now this webpage prompted me to check out file variables.
Step 9: Added "?file=" to the url and tried to display user acc's with "?file="../../../../etc/passwd" 
and got all users --> zeamkish is interesting because the third sql map output it provided me is that this file pathing
is only accessible with user with username "z" --> zeamkish
Step 10: after entering in the username, I got prompted to an admin panel with an file upload, after analyzing the source code script
I realised that you can upload a rev shell, the only exception handling which is done is that it checks if it has "jpg" or "png" inside it. so I will add it shell.jpg.php for example.
- Step 11: --> I guess it is not as easy as I thought. It needs to be integrated in a real jpg/png file. --> Steganography.
- Step 12: I fixxed it with making it to test.phpD.jpg and it became an image file with the stored revshell payload.
- Step 13: before actually uploading it, I intercepted traffic with burp and captured the request, hovered over the "D" between test.phpD.png" and made the hex value to 00, so it get's displayed as test.php.png
- Step 14: After that I uploaded it and in the source code it prompted me a hidden sub-folder upload-cv00101011/upload_thm_1001/
went in there and clicked on my test.php (created listener before on port 1337) and gained rev shell in the server
- Step 15: Started with shell hardening --> after finding zeamkish user --> cat ssh_creds.txt revealed SSH CREDS
zeamkish
easytohack@123
- Step 16: logged into zeamkish ssh --> made sudo -l --> no possibilities
 --> made find / -perm -04000 -type f -ls 2>/dev/null 
- Step 17: discovered in /usr/bin that /usr/bin/nano is runnable with zeamkish but root privs, I just made 
--> nano /root/root.txt didnt work --> made find / -iname "*.txt" --> root/flag.txt --> made nano /root/flag.txt --> got it.


---

## Key Learnings

- Further strengthened knowledge about sqlmap 
- Further strengthened knowledge about ffuf & enumeration methodologys
- Further strengthened knowledge about linux cli
- Improved manual privilege escalation methodologys.
