
---

Steps in order to portforward with Ligolo-Ng.

Assuming u have downloaded the linux agent onto the target server and got ligolo-ng downloaded on ur local machine u can use the following steps in order to portforward ports.

## Linux

On Local machine

1. Setting up Ligolo on Kali:

```
sudo ip tuntap add user saitama mode tun ligolo
sudo ip link set ligolo up
```

2. Start the proxy.

```
ligolo-proxy -selfcert
```

On Target Server

3. Target connect back to local machine.

```
www-data@zab:~$ ./ligolo_agent_linux_amd64 -connect 192.168.45.241:11601 -ignore-cert
```

4. Now on the Ligolo CLI on local machine:

Selected an agent

```
ligolo-ng » session
? Specify a session : 1 - www-data@zab - 192.168.124.210:53058 - 0050569e5b4d
```

Started tunnel.

```
[Agent : www-data@zab] » start
INFO[0341] Starting tunnel to www-data@zab (0050569e5b4d)
```

5. Then, add the magic Ligolo IP to the IP route table on Kali since we’re trying to access a localhost port.

```
sudo ip route add 240.0.0.1/32 dev ligolo
```

Now, we should be able to access any local port by using the magic IP 240.0.0.1.