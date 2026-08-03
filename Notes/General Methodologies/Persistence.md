
---
##### Create DA

Let's first create an Domain Admin User

Created user

```
net user /add saitama password123! /domain
```

Added to Domain Admins Group

```
net group "Domain Admins" saitama /add /domain
```

Reassure if change is successfull:

```
net group "Domain Admins"
```
