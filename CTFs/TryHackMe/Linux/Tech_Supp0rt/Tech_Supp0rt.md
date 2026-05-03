# CTF Writeup: Tech_Supp0rt

---

- Step 1: Started with basic enumeration --> gained dir /wordpress --> made nmap scan 139,445 open --> smb windows server
- Step 2: Analyzed webpage and ran wpscan, but no results 
- Step 3: made "smbmap -H target ip to gather information about running smb instances
--> "websvr" seems to be read only --> made smbclient \\\\10.10.164.12\\websvr --> gained access to server --> made get enter.txt --> gained creds: admin:7sKvntXdPEJaxazce9PXi24zaFrLiKWCk 
- Step 4: had to cyberchef the encrypted password --> Scam2021 real pw --> logged in into subrion --> but didn't get much information out of it 
- Step 5: had to research now --> since the subrion version is 4.2.1 I looked for exploits --> found one and installed it. ran it with --> sudo python3 SubrionRCE.py -u http://10.10.164.12/subrion/panel/ -l admin -p Scam2021 
- Step 6: After gaining RCE I tried various types of shell hardening, but didn't work 
- Step 7: found user creds in ls /var/www/html/wordpress/wp-config.php --> support:ImAScammerLOL!123! --> since there is only a scamsite user I logged in with ssh scamsite@target ip and password which I just retrieved.
- Step 8: gained ssh scamsite user access --> made sudo -l which prompted me to the binary /usr/bin/iconv --> 
went to gftobins and searched for .iconv --> reads an file --> if sudo rights it executes it with the privileged rights made --> sudo /usr/bin/iconv -f 8859_1 -t 8859_1 /root/root.txt and gained flag

---

## Key Learnings

- Further strenghtened privilege escalation knowledge
- Strenghtened binary exploit knowledge
- Strengthened Methodology and mindset
- Gained new toolstack smbmap & smbclient enumeration tools for smb servers
