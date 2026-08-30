
---
### Admin Permissions

Just execute commands in the Script console

OR

Create an Project and start a build with command execution.

---
### Low Permissions

There is two ways to gain command execution when we are lacking permissions in Jenkins to start the build.
##### Trigger periodically

1. When creating a freestyle project, we first chose the payload.

In our case we scroll down to "Build" and select "Execute Windows batch command" since the target server is an windows server 2019.

```
cmd /c whoami
```

2. Scroll up to "Build Triggers" and paste the following inside so the build is getting periodically executed every minute.

```
* * * * *
```

3. Save the build and refresh the page after one minute, we should see one build in the history, hovering over the build reveals an dropdown. Select "Console Output" to see if the whoami command executed.

---
##### Trigger remotely

Running different commands waiting one minute between each one is a bit exhausting. I’ll disable the scheduled trigger. Looking at the other options for “Build Triggers”, “Trigger builds remotely (e.g., from scripts)” seems interesting. Checking it expands out asking for an “Authentication Token”:

1. Press on your profile > Configure then navigate to the section where we can add API Tokens and press "Add new Token", name it to smth random and it should generate an token.

```
saitamawashere:11983737aa1405fab2ed643467f4953e75
```

2. New Item > Freestyle Project

3. Scroll to "Build Triggers" and select "Trigger builds remotely". Checking it expands out asking for an "Authentication Token", paste your token name inside.

```
saitamawashere
```

4. Chose payload. Scroll down to "Build" and utilize Windows batch commands, since target is an Windows Server 2019.

```
cmd /c "whoami"
```

5. Send API Request.

```
curl "http://saitama2:11983737aa1405fab2ed643467f4953e75@object.htb:8080/job/hacked4/build?token=saitamawashere"
```

6. Reload page and the build should be there. We can now hover over it and chose "Console Output", to check if the command executed.

```
C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>cmd /c "whoami" 
object\oliver

C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>exit 0 
Finished: SUCCESS
```

