
Stealthy ALL Scan

```
nmap -n -Pn -sS -p- -T5 --min-rate 1500 --max-rtt-timeout 500ms --max-retries 3 <target_ip>
```

CTF

```
nmap -A -p- --min-rate 10000 <target_ip>
```

Standard TCP

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.136.40
```

Standard UDP

```
nmap -sU --top-ports 100 -oN nmap_udp.txt 10.129.136.40
```

