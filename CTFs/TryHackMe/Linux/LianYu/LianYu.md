# CTF Writeup: LianYu

---

- Step 1: added target_ip to /etc/hosts and performed nmap scan --> 21,22,80,111 & 45459 are open
- Step 2: ran gobuster scan found hidden dir /island --> retrieved an password: vigilante
- Step 3: kept on enumerating this endpoint --> found hidden dir /island/2100/ and received hint that
there is possible .ticket file in source-code
- Step 4: kept on enumerating 
--> made gobuster dir -u http://lianyu.thm/island/2100 -x ticket -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
- Step 5: found "green_arrow.ticket" hidden file --> retrieved token for queens gambit ship RTy8yhBQdscX
- Step 6: tried all decode methods --> base58 worked --> !#th3h00d
- Step 7: logged into ftp and made get to every file --> analyzed pictures with steghide & exiftool, but no passphrase
- Step 8: after some research I got suggested the stegseek tool --> installed it & made stegseek -sf aa.jpg /usr/share/wordlists/rockyou.txt
- Step 9: --> gained extracted data --> M3tahuman --> retrieved more potential users "Slade" & "Oliver" 
- Step 10: Logged into ssh with slade & M3tahuman
- Step 11: retrieved user flag
- Step 12: made sudo -l
- Step 13: pkexec binary is runnable with root privs on user slade --> made sudo pkexec /bin/bash
- Step 14: gained root shell --> retrieved root flag

---

## Key Learnings

- Gained new tool to arsenal --> stegseek which brute forces passphrases for picture file extraction
- Further strengthened enumeration methodology
