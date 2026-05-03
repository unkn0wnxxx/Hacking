# CTF Writeup: U.A High School

---

- Step 1: Started my enumeration methodology --> nmap -sS -sC -sV <target_ip> --> ssh & http open --> analyzed web source, web functionalities
intercepted traffic with burp and tried multiple injection methods --> all failed, gobuster scan revealed /assets dir, after being hardstuck
- Step 2: Learned about "dirsearch" and made --> dirsearch -u http://target_ip/assets/index.php and found path traversal cmd --> tested it with whoami, but website outputted base64 code --> encoded it with "echo '' | base64 -d" 
- Step 3: Started nc listener on port 1234 & Added python3 rev shell script into cmd input --> gained shell on server --> whoami --> www-data 
- Step 4: Went to home dir, but don't have enough strong rights for user.txt, made ls -la on ubuntu and saw .ssh, which I will need to retrieve later on most likely since I do not have enough rights for it.
- Step 5: went into www dir --> found Hidden_Content dir --> passphrase.txt in it --> displayed it with cat and received base64 code --> decrypted it with "echo 'code' | base64 -d" potential password "AllmightForEver!!!" --> tried with signing into deku, but didn't work went to /etc and displayed passwd --> user daemon exists --> made su daemon and tried logging in, but didn't work.
- Step 6: went into assets/images and made wget oneforall.jpg on my local machine --> file is corrupted, not displayable, tried multiple methodologies for example editing hexdump manually with correct hexcodes from wikipedia, but didn't work. After some research I tried the stegnographic tool "magicbytes" --> made python3 magicbytes.py -i <imagefile> -m png --> and it instantly worked.
- Step 7: After having the proper file image, I used "steghide" and made --> steghide extract -sf oneforall.jpg and entered passphrase "AllmightForEver!!!" and gained "creds.txt" --> deku's password: One?For?All_!!one1/A
- Step 8: retrieved user flag.txt in deku dir
- Step 9: made sudo -l to retrieve the list of sudo services that user deku can run. --> feedback.sh file can be run, 
analyzed this file. Which is basically asks for feedback and if the feedback doesn't have certain characters in it, it will be added to the feedback variable. Otherwise it won't be accepted, possible bypasses
- Step 10: Multiple bypasses possible with <, { etc.. decided to create an cronjob on root rights --> executed script with --> sudo ./feedback.sh entered "feedback" made --> "* * * * * root cat /root/root.txt > /tmp/rootreal.txt" >> /etc/crontab 
- Step 11: Waited 1 min for the crontab to execute & displayed rootreal.txt in /tmp dir and found root flag

---

## Key Learnings

- Strengthened enumeration methodology by adding new tool "dirsearch"
- Learned about stegnography and hexdump
- Added multiple new tools "steghide" to extract data out of an image file
"magicbyte" to automatically fix corrupted hex dumps in image files
"hexedit" to display hexdump properly and be able to edit it manually
- Further Increased Privilege Escalation Methodologys
