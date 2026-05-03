# CTF Writeup: Team

---

- Step 1: webpage is just the default apache one --> made nmap scan port 21,22, 80 open
- Step 2: After analyzing the webpage source with curl -v --> I found the phrase "add team.thm to hosts"
--> made sudo nano /etc/hosts and added it --> can display the real webpage now
- Step 3: after viewing the /robots.txt dir I found an information --> dale
- Step 4: downloaded all .jpg pictures to extract metadata with exiftool to potentially find smth, but no result
- Step 5: made more directory fuzzing "gobuster dir -u http://team.thm/scripts/ -x php,txt -w /usr/share/wordlists/dirb/common.txt" 
--> found /scripts/script.txt --> made  also subdomain fuzzing ("wfuzz -c -w /usr/share/SecLists/Discovery/DNS/subdomains-top1million-5000.txt -u http://10.10.145.35/ -H "Host: FUZZ.team.thm" --hw 977") and got results this time --> subdomain dev.team.thm 
--> the hidden script displayed a bash script with redacted creds and lead me to script.old --> which gave me creds 
logged into ftp with creds
- Step 6: navigated into workshare directory where I found a msg abt the .dev sub-domain I found. -->
navigated in there and pressed on link --> after analyzing url I saw there I can retrieve the server file system.
retrieved --> made "wfuzz -c -w /usr/share/SecLists/Fuzzing/LFI/LFI-gracefulsecurity-linux.txt -u http://dev.team.thm/script.php?page=FUZZ --hw=0" --> put in the filter --> etc/ssh/sshd_config and it gave me the private key of dale. 
- Step 7: logged into dale via ssh --> made sudo -l --> admin_checks can be ran with user gyles --> made sudo -u gyles /home/gyles/admin-checks --> this script can be exploited to get gyles privs if you inject /bin/bash -i into the date prompt --> did this and got gyles rights --> hardened the shell with import pty.
went into opt/admin_stuff/script.sh I cat'd --> showed me 2 scripts with root owner, 1 was writable for gyles.
- Step 8: made "cat /root/root.txt > /tmp/flag.txt". Since this script runs every minute like a cronjob
I got the flag displayed.

---

## Key Learnings

- Further strengthened my knowledge in enumeration, especially file enumeration with -x txt
- Increased enumeration techstack with adding wfuzz for subdomain enumeration
