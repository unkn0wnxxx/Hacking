# CTF Writeup: MrRobot

---

- Step 1: added target_ip to /etc/hosts and ran nmap scan --> 22, 80, 443, 
- Step 2: ran gobuster and retrieved a lot of hidden dir's --> robots.txt revealed the 1st key
- Step 3: and gave me a wordlist, which I decremented --> it repeated to often 
made sed -i '11443,$d' wordlists.txt
- Step 4: went to /license hidden dir and found base64 decoded string --> made | base64 -d
--> gained creds elliot:ER28-0652
- Step 5: logged in and changed 404.php under themes/editor section into pentest monkey revshell
- Step 6: made nc -lvnp 1234 to start listener 
& visited http://mrrobot.thm/wp-includes/themes/TwentyFifteen/404.php --> gained rce
- Step 7: went into robot dir and retrieved creds robot:abcdefghijklmnopqrstuvwxyz
- Step 8: made find / -perm /4000 2>/dev/null --> unusual nmap binary --> since it's executable
with root I ran it 
- Step 9: made "bash" and gained root bash shell.
- Step 10: retrieved third key.

---

## Key Learnings

- Slightly strengthened WordPress Knowledge
- Slightly strenghtened Enumeration Knowledge
