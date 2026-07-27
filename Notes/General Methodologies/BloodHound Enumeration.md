
# BloodHound

can be utilized in order to map the whole domain hierarchy based and potentially elevated privs.

---
## PoC

Once we have user credentials and an domainname we can use them to gain information about the domain

```
bloodhound-python -u "SVC_TGS" -p "GPPstillStandingStrong2k18" -ns 10.129.161.230 -d active.htb -c all
```

NTLM Auth

```
bloodhound-python --username 'MediaAdmin$' --hashes :5900eed28abf42d0bac3cf431f11508e --domain dante.local --collectionmethod All -dc DANTE-DC01.DANTE.local -ns 172.16.1.20
```

Also run rusthound-ce for more detailled certificate information.

```
rusthound-ce --domain fluffy.htb -u j.fleischman -p 'J0elTHEM4n1990!'
```

```
rusthound-ce -d sequel.htb -u sql_svc -p 'REGGIE1234ronnie' -i 10.129.37.251 -P 636
```

Startup docker since it runs the backend of bloodhound

```
systemctl start docker
docker ps
```

Start Postgres

```
systemctl start postgresql
```

```
service postgresql start
```

Next step is to start up neo4j & bloodhound with credentials neo4j:HonorShard302!
Startup neo4j

```
neo4j console
```

Startup bloodhound

```
bloodhound
```

In order for bloodhound to work I had to reinstall it manually in an docker environment.
Logged into http://127.0.0.1:9090/ui
Logged in with admin:HonorShard302!

Navigated to the left tab > Quick Upload > Selected all the .json files we received earlier.