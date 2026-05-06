
# CTF Writeup: Dig Dug

---
## Reconaissance

Mapped the Domain "givemetheflag.com" to the target ip in our local cns cache.

```
echo "10.113.134.105 givemetheflag.com" | tee -a /etc/hosts                 
10.113.134.105 givemetheflag.com
```

I then utilized the tool called "dnsrecon" to get DNS information on the domain.

```
dnsrecon -d givemetheflag.com
2026-05-06T15:23:51.988397-0500 INFO Starting enumeration for domain: givemetheflag.com
2026-05-06T15:23:51.990181-0500 INFO std: Performing General Enumeration against: givemetheflag.com...
2026-05-06T15:23:52.030883-0500 ERROR No answer for DNSSEC query for givemetheflag.com
2026-05-06T15:23:52.076986-0500 INFO     SOA ns2.afternic.com 173.201.66.69
2026-05-06T15:23:52.077249-0500 INFO     SOA ns2.afternic.com 2603:5:2226::45
2026-05-06T15:23:52.144257-0500 INFO     NS ns1.afternic.com 97.74.98.69
2026-05-06T15:23:52.180474-0500 INFO     Bind Version for 97.74.98.69 -all"
2026-05-06T15:23:52.181032-0500 INFO     NS ns1.afternic.com 2603:5:2126::45
2026-05-06T15:23:52.181786-0500 INFO     NS ns2.afternic.com 173.201.66.69
2026-05-06T15:23:52.212436-0500 INFO     Bind Version for 173.201.66.69 -all"
2026-05-06T15:23:52.212825-0500 INFO     NS ns2.afternic.com 2603:5:2226::45
2026-05-06T15:23:52.297431-0500 INFO     A givemetheflag.com 76.223.54.146
2026-05-06T15:23:52.297823-0500 INFO     A givemetheflag.com 13.248.169.48
2026-05-06T15:23:52.351127-0500 INFO     TXT givemetheflag.com v=spf1 -all
2026-05-06T15:23:52.351532-0500 INFO     TXT _dmarc.givemetheflag.com v=spf1 -all
2026-05-06T15:23:52.386757-0500 INFO     TXT _domainkey.givemetheflag.com v=spf1 -all
2026-05-06T15:23:52.387021-0500 INFO     TXT _dmarc._domainkey.givemetheflag.com v=spf1 -all
2026-05-06T15:23:52.387094-0500 INFO Enumerating SRV Records
2026-05-06T15:23:52.650331-0500 ERROR No SRV Records Found for givemetheflag.com
2026-05-06T15:23:52.650761-0500 INFO Completed enumeration for domain: givemetheflag.com
```

Utilized the following dns query in order to get back content from the TXT file.

```
dig +short @10.113.134.105 givemetheflag.com
```



```
flag{0767ccd06e79853318f25aeb08ff83e2}
```

