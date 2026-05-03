# CTF Writeup: TakeOver

---

- Step 1: added ip to my /etc/hosts and was able to view webpage
- Step 2: since this is an subdomain enumeration flag --> made ffuf -w /usr/share/SecLists/Discovery/DNS/bitquark-subdomains-top1million.txt -u https://futurevera.thm -H "Host: FUZZ.futurevera.thm" -fs 4605
--> gained support & blog subdomains
- Step 3: Added both to /etc/hosts and analyzed pages --> issues with certificate on support subdomain
--> after checking out certificate I was able to retrieve a hidden subdomain "secrethelpdesk934752.support.futurevera.thm" after opening it
--> displayed flag.

---

## Key Learnings

- Improved my subdomain enumerating by filtering out sizings --> To remove false positives
- Increased Knowledge when it comes to SSL Certificates.

