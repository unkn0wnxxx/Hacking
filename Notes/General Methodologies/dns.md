
##### dns reverse lookup

```
dig +noall +answer @10.129.227.180 -x 10.129.227.180
```

or the same with nslookup:

```
nslookup 10.129.227.180 10.129.227.180
```

---

1. Find out NS

```
dig @10.112.173.144 windcorp.thm -t NS
```

```
dnsrecon -d <domain -n 192.168.112.1
```

```
dnsrecon -d windcorp.thm
```

2. Utilize dig to check DNS Entries.

```
dig @10.112.173.144 windcorp.thm -t TXT
```


```
dog @10.112.173.144 windcorp.thm -t MX
```