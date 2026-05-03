# CTF Writeup: Colddbox

---

- Step 1: nmap scan revealed only 80 open
- Step 2: webpage based on wordpress + added ip to /etc/hosts
- Step 3: made gobuster scan --> revealed /hidden dir --> which revealed 3 user names --> C0ldd, Hugo & Philip
- Step 4: Created users.txt file with all 3 names revealed and made 
--> wpscan --url 10.10.69.155 -U users.txt -P /usr/share/wordlists/rockyou.txt found pw for C0ldd
- Step 5: Uploading pentestmonkey php rev shell into Plugins --> akismet/class.akismet.php file
searched up akismet tool file path --> found it coldbox.thm/wp-content/plugins/akismet/class.akismet.php
- Step 6: gained rce --> navigated into wp-config.php and gained c0ldd creds: c0ldd:cybersecurity
- Step 7: made su c0ldd -> retrieved user flag
- Step 8: made sudo -l --> multiple commands runnable with root privs
- Step 9: decided to go for /bin/chmod 
- Step 10: made sudo -u root /bin/chmod 777 /root
- Step 11: retrieved root.txt

---

## Key Learnings

- Learned more about Wordpress in General and what to look for specifically
- Strengthened wpscan knowledge

