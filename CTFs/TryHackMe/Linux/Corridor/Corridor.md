
In this challenge, you will explore potential vulnerabilities. Examine the URL endpoints you access as you navigate the website and note the hexadecimal values you find (they look an awful lot like a hash, don't they?). This could help you uncover website locations you were not expected to access.

---

I analyzed all of the endpoints and they seemed to be md5 hashes. I converted them and found out that they are numerical values from 1-13. 

I tried to generate md5 hashes for 14,15,16 but it didn't work and then proceeded with generating an md5 hash for 0

```
echo -n "0" | md5sum 
cfcd208495d565ef66e7dff9f98764da  -
```

Retrieved flag.

```
 flag{2477ef02448ad9156661ac40a6b8862e} 
```