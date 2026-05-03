# CTF Writeup: Netmon

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 21,80,135,139,445,5985,47001
- Step 2: accessed ftp anonymously --> retrieved user.txt flag 
- Step 3: googled where the default path for the configuration files of prtg is
--> programdata
- Step 4: retrieved the old.bak backup file and tried to enumerate user creds out of it.
- Step 5: grep -B5 -A5 -i password PRTG\ Configuration.old.bak | sed 's/ //g' | sort -u --> found user prtgadmin:PrTg@dmin2018 --> since this old.bak file is from 2018, and the other's are from 2019, it could be possible that the password get's itterated each year.
- Step 6: tried prtgadmin:PrTg@dmin2019 on web server login page --> it worked
- Step 7: opened up msfconsole -q --> search prtg --> used 3 exploit
- Step 8: configured it and gained an meterpreter session 
- Step 9: made shell & powershell --> navigated into Administrators Desktop
- Step 10: retrieved root.txt flag

---

## Key Learnings

- Immensly strengthened knowledge about prtg
- Strengthened Linux CLI Knowledge
