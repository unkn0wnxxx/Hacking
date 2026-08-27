
When we don't have access to domain information, specifically BloodHound.

We can either analyze if the current user is logged on to the target by doing:

The "SI" parameter reveals if there is an active session, when one of the numbers is higher than 0.

```
Get-Process
```

Also metasploit has an interesting tool called "qwinsta" which allows us to check current sessions, we were also retrieve that edavies has an active session here by that.

```
qwinsta /server:127.0.0.1
```
