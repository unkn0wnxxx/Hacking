
- [[MSSQL Connection|Authenticated Connection possible?]]
- If we got access:
	- [[xp_cmdshell]]
		- If xp_cmdshell isn't activated we can try to [[xp_cmdshell activating|activate]] it.
	- Check if we can impersonate an high priv user with this [[MSSQL Impersonate]]
	- [[MITM Attack with xp_dirtree|NTLM Theft with xp_dirtree]]
	- If an website is vulnerable to SQLi abuse this: [[SQLI NTLM Theft with xp_dirtree]]