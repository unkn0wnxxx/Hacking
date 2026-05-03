# CTF Writeup: GamingServer

---

- Step 1: added target_ip to /etc/hosts and made nmap scan --> 22, 80 open
- Step 2: analyzed source, found potential user in source code --> user john
- Step 3: made gobuster scan --> found /secret dir --> which gave me private key
- Step 4: added id_rsa8 key and made chmod 600 to it --> tried logging in
into john with ssh -i id_rsa8 john@target_ip, but still checked me for passphrase/password.
- Step 5: retrieved dict.lst file which payload shows a lot of passwords --> wordlist
- Step 6: converted encrypted rsa key to hash --> made ssh2john id_rsa8 > hash
- Step 7: and tried to find out passphrase 
--> made john hash --wordlist=~/.ssh/dict.lst
found passphrase: letmein
- Step 8: made ssh -id_rsa8 john@target_ip and entered passphrase --> gained ssh --> retrieved user flag. 
- Step 9: checked /opt & html dir but didnt found any creds, ran sudo -l but prompts for pw, looked for
suid binaries. --> no results  
- Step 10: 

---

## Key Learnings

- Further strengthened hash converting knowledge with ssh2john
- Further strenghtened enumeration methodology knowledge
-
