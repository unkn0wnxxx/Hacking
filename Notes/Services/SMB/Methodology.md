
- Only Kerberos Auth? [SMB with Kerberos Auth](SMB%20with%20Kerberos%20Auth.md)
- Anonymous Access
- [[Enumerating SMB Shares with guest access]]
- [[Enumerating SMB Shares]]
- enum4linux
- Write Perms?
	- SMB Share == Web-Root? if yes, put reverse shell inside
	- [[NTLM Theft]]
- Any sensitive files?
- Authenticated? --> [[SMB NXC User Enum]]

##### Download whole SMB Share

```
smb: \> recurse ON
smb: \> prompt OFF
smb: \> mget *
```