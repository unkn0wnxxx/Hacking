
---
## KeePass

When the File Version is not supported. Utilize "keepass2john.py", since it supports most of the versions

```
python3 /opt/arsenal/kdbx2john/keepass2john.py Database.kdbx > kdbx_hash
```

Now we need to utilize either john the ripper or an custom script. For me john the ripper also didn't work, but this would be the command to execute:

```
john kdbx_hash --format=KeePass --wordlist=/usr/share/wordlists/rockyou.txt
```

---
# Bruteforcing the Database directly

I utilized an bash script which I retrieved from GitHub.

```
keepass4brute.sh recovery.kdbx /usr/share/wordlists/rockyou.txt
```

---
##### Werkzeug Hashes

Use the script:

```
import base64
import codecs
import re
import sys


if len(sys.argv) != 2:
    print(f'usage: {sys.argv[0]} <werkzeug hash file>')
    print('Input file has Werkzeug hashes one per line')
    sys.exit(1)

with open(sys.argv[1], 'r') as f:
    hashes = f.readlines()

for h in hashes:
    m = re.match(r'pbkdf2:sha256:(\d*)\$([^\$]*)\$(.*)', h)
    iterations =  m.group(1)
    salt = m.group(2)
    hashe = m.group(3)
    print(f"sha256:{iterations}:{base64.b64encode(salt.encode()).decode()}:{base64.b64encode(codecs.decode(hashe,'hex')).decode()}")
```

The script can be found in /opt/arsenal

Run the following command:

```
python3 werkzeug_to_hashcat.py <( echo 'pbkdf2:sha256:600000$AMtzteQIG7yAbZIa$0673ad90a0b4afb19d662336f0fce3a9edd0b7b19193717be28ce4d66c887133' ) | tee admin.hash
```

---