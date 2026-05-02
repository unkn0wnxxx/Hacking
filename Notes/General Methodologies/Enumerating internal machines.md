
1. Use winPEAS

if IP's get listed, but no domains, do this:

```
PS C:\Windows\System32\WindowsPowerShell\v1.0> nbtstat -A 172.16.158.240
nbtstat -A 172.16.158.240
    
Ethernet0:
Node IpAddress: [172.16.158.243] Scope Id: []

           NetBIOS Remote Machine Name Table

       Name               Type         Status
    ---------------------------------------------
    DCSRV1         <20>  UNIQUE      Registered 
    DCSRV1         <00>  UNIQUE      Registered 
    BEYOND         <00>  GROUP       Registered 
    BEYOND         <1C>  GROUP       Registered 
    BEYOND         <1B>  UNIQUE      Registered 

    MAC Address = 00-50-56-9E-CA-8F
```

```
nbtstat -A <ip>
```

this will provide us with the domain --> in our case it's

dcsrv1.beyond.com

Second IP:

```
PS C:\Windows\System32\WindowsPowerShell\v1.0> nbtstat -A 172.16.158.254
nbtstat -A 172.16.158.254
    
Ethernet0:
Node IpAddress: [172.16.158.243] Scope Id: []

           NetBIOS Remote Machine Name Table

       Name               Type         Status
    ---------------------------------------------
    MAILSRV1       <00>  UNIQUE      Registered 
    MAILSRV1       <20>  UNIQUE      Registered 
    BEYOND         <00>  GROUP       Registered 

    MAC Address = 00-50-56-9E-EE-DC
```

mailsrv1.beyond.com

If nbtstat isn't installed, do this:

```
ping -a 172.16.158.240
Pinging DCSRV1 [172.16.158.240] with 32 bytes of data:
```

--> dcsrv1.beyond.com