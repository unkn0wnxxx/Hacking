
People ignore this port.

Many times:

- indices are open
- credentials/hashes stored in documents
- full data leakage without authentication

```
curl http://$ip:9200/_cat/indices?v  
curl http://$ip:9200/_search?q=password
```

Unauth clusters leak credentials straight from documents.