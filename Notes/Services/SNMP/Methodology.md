
responsible for managing and monitoring devices on networks, like routers, switches, servers, and printers.

It acts as a messenger system.

--> A manager (monitoring pc) asks for information. 
--> An agent (software of the network device) answers. 
usually in pre-defined stats like "Printer is out of paper" or "Network port is down"

A tool which we can utilize to enumerate SNMP-enabled devices is onesixtyone and give us the community string of the SNMP Server (password).

```
onesixtyone -c /usr/share/seclists/Discovery/SNMP/snmp-onesixtyone.txt <target_ip>
```

The best tool to enumerate information out of an running SNMP Server is snmp-check.

```
snmp-check -c openview <target_ip>
```

## Analyze Running Processes for FootHold!

![[Pasted image 20260220154239.png]]

Analyzing the Screenshot we can see "Mouse Server Luminati.exe" running. Google it and you will already find some exploits for the Mouse Application. 

Ran WiFI Mouse RCE Exploit from GitHub --> Gained RCE!

---

Since often times snmp-check doesn't work.
Another tool which displays us actual information about queries and responses is snmpwalk.

##### RUN ALL!

```
snmpwalk -v2c -c openview 10.10.38.124 1.3.6.1.4.1.77.1.2.25
snmpwalk -v [VERSION_SNMP] -c [COMM_STRING] [DIR_IP]
snmpwalk -v [VERSION_SNMP] -c [COMM_STRING] [DIR_IP] 1.3.6.1.2.1.4.34.1.3 #Get IPv6, needed dec2hex
snmpwalk -v [VERSION_SNMP] -c [COMM_STRING] [DIR_IP] NET-SNMP-EXTEND-MIB::nsExtendObjects #get extended
snmpwalk -v [VERSION_SNMP] -c [COMM_STRING] [DIR_IP] .1 #Enum all
```

```
snmpwalk -v2c -c public 192.168.234.156 NET-SNMP-EXTEND-MIB::nsExtendObjects
```

- snmpwalk utility for queuering multiple OIDs sequentially 
- -v 2c uses SNMP version 2c 
- -c openview stands for the Community string (password) is "openview"
- 1.3.6.1.4.1.77.1.2.25 is the OID for Windows user account enumeration

In order to enumerate more potential community strings, we can utilize an tool called "snmpbrute.py".

#### List Processes

```
snmpwalk -c public -v1 192.168.145.151 1.3.6.1.2.1.25.4.2.1.2
```

On Windows Machines

```
snmpwalk -c public -v1 $ip .1.3.6.1.4.1.77.1.2.25 # Users  
snmpwalk -c public -v1 $ip .1.3.6.1.4.1.77.1.2.3.1.1 # Processes
```

```
python3 snmpbrute.py -t 10.129.228.102 -f /usr/share/wordlists/SecLists/Discovery/SNMP/common-snmp-community-strings.txt
```

# SNMP Subnet Enum

---
Mapping community strings to variable

```
echo public > community
echo private >> community
echo manager >> community
```

Mapping network scan to ips variable

```
for ip in $(seq 1 254); do echo 192.168.145.$ip; done > ips
```

Enumerating snmp services on the entire subnet.

```
onesixtyone -c community -i ips
```