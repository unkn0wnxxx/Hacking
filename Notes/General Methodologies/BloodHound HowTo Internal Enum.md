
## Custom Queries

##### List all computers on domain

Let's enter this query in the Raw Query section and build the graph.

```
MATCH (m:Computer) RETURN m
```

There seems to be 4 computer's running on the whole domain.

![[Pasted image 20260129214744.png]]

Let's find out the IP-Address of INTERNALSRV1.beyond.com

```
nslookup INTERNALSRV1.beyond.com
nslookup INTERNALSRV1.beyond.com
DNS request timed out.
    timeout was 2 seconds.
Server:  UnKnown
Address:  172.16.158.240

Name:    INTERNALSRV1.beyond.com
Address:  172.16.158.241
```
## List users on domain

```
MATCH (m:User) RETURN m
```

Next step is to list all domain admins

--> beccy is domain admin.

## List all active sessions

```
MATCH p = (c:Computer)-[:HasSession]->(m:User) RETURN p
```
## Important Pre-Built Queries
Next, let's use some of the pre-built queries to find potential vectors to elevate our privileges or gain access to other systems. We'll run the following pre-built queries:

    Workstations where Domain Users can RDP
    Find Servers where Domain Users can RDP
    Find Computers where Domain Users are Local Admin
    Shortest Path to Domain Admins from Owned Principals

## Kerberoastable users

Next step is to identify all kerberoastable users in the domain.

Use this query.

```
All Kerberoastable Accounts
```

User daniela is kerberoastable.

If krbtgt user --> ignore, because password attacks (hash cracking) is impossible.

Examine SPN of user daniela.

![[Pasted image 20260129223231.png]]

Based of this SPN we can assume that the web server is running on INTERNALSRV1 computer.


