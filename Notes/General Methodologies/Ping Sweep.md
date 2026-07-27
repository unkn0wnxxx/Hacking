
Hosts

```
for i in {1..254} ;do (ping -c 1 172.16.1.$i | grep "bytes from" &) ;done
```

Subnets

```
for i in {1..254}; do for j in {1..254}; do (ping -c 1 -W 1 172.16.$i.$j | grep -q "bytes from" && echo "172.16.$i.$j is up" &); done; done
```

Efficient nmap scan

```
nmap -sn 172.0.0.0/24 --min-rate 10000
```