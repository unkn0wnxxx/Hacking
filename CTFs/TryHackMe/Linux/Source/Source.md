# CTF Writeup: Source

---

- Step 1: added <target_ip> to /etc/hosts and made nmap scan --> port 22, 10000 
- Step 2: accessed webpage on target_ip:10000/ --> login page
- Step 3: since the webpage blocks you after to many login attempts, I couldn't brute force
- Step 4: tried sql injection, but also didnt work
- Step 5: looked for cve's of webmin --> found cve-2019-15107
- Step 6: executed script --> gained root shell and retrieved flags

---

## Key Learnings

- Slightly improved methodology
