
Once we got access to the CMS as Low Priv User, we can utilize semantic errors in order to potentially get Administrator Privileges.

One Strategy is by navigating to Profile and make any change for example changing the layout color. Opening BurpSuite and intercepting the package when pressing Update.

We will then add the following parameter to the already provided parameters and forward the package.

```
&ure_other_roles=administrator
```

After we should be Administrator.

We can proceed by navigating to Plugins > Plugin Editor and change e.G akismet.php and replace it with an wolfswebshell.php to get command execution / RCE.
