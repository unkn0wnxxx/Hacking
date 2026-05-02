
a command-line utility that allows users to execute commands with the privileges of another user (typically root)

Default Path

```
/etc/doas.conf
/usr/local/etc/doas.conf
```

Search for configuration file. This will show what our user can do similiar to sudo -l

```
find / -iname "doas.conf" 2>/dev/null
```

If there is an webserver running internally on 127.0.0.1 9000, but we can't view it, we could potentially start it up, if our user has the perms on doas.conf.

```
permit nopass andrew as root cmd service args apache24 onestart
```

```
permit: The action to allow the command



nopass: The user will not be prompted for a password



andrew: The username this rule applies to



as root: Specifies that the command should be run as the root user. This is actually the default if as is omitted, so it's optional but good for clarity



cmd service: Restricts the permission to the specific executable /usr/sbin/service (or wherever service is located). It's good practice to use the full path to the executable for security, though the short name often works



args apache24 onestart: Restricts the command to only these specific arguments. This means andrew can only run doas service apache24 onestart and not doas service apache24 stop or any other variation

```

Start up apache24.

```
doas service apache24 onestart
```

After starting the apache server up, we can access it just by accessing it via the browser on port 80.

![[Pasted image 20260216194718.png]]

If an user can run commands as root with doas, we can do the following:

![[Pasted image 20260216211309.png]]

