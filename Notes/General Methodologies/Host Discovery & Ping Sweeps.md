
---
#### Powerful Enumeration Technique

After comprimising an target, run fscan.exe to enumerate hosts alive connected to the comprimised machine, for internal pivoting.

```
.\fscan.exe -h 172.16.2.0/24 -m icmp
[+] alive 172.16.2.101
[+] alive 172.16.2.5
```

Enumerating ports from the comprimised machine to the discovered hosts:

```
.\fscan.exe -h 172.16.2.101 -p 1-65535 -nobr -nopoc
```

Linux

```
./fping -a -g 172.16.2.0/24 2>/dev/null
```

---
#### Ping Sweeping
##### Linux

Hosts

```
for i in {1..254} ;do (ping -c 1 172.16.1.$i | grep "bytes from" &) ;done
```

Subnets

```
for i in {1..254}; do for j in {1..254}; do (ping -c 1 -W 1 172.16.$i.$j | grep -q "bytes from" && echo "172.16.$i.$j is up" &); done; done
```
##### Windows

Hosts

```
(for /L %a IN (1,1,254) DO ping /n 1 /w 1 172.16.2.%a) | find "Reply"
```

PowerShell

```
1..254 | % {"192.168.210.$($_): $(Test-Connection -count 1 -comp 192.168.210.$($_) -quiet)"}
```

Efficient nmap scan

```
nmap -sn 172.0.0.0/24 --min-rate 10000
```

---
#### Check Hosts Files

Windows

```
C:\Windows\System32\drivers\etc\hosts
```

Linux

```
/etc/hosts
```

---
#### Check Routing Tables

of all comprimised hosts (preferably Domain Controllers)

Windows CMD

```
ip route
```

Windows PowerShell

```
Get-NetAdapter Get-NetIPAddress
```

Linux

```
ip route 
ip addr 
cat /etc/networks
```

---
#### Check Established Connections

Foreign Addresses specifically, for unknown IP Addresses outside of the subnet.

```
netstat -ano
```