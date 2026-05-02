

Industry standard email server service.

---

## PoC

Connecting to IMAP 

```
telnet 192.168.145.140 143
```

Logging into IMAP / authenticating.

```
a1 login "jonas" "SicMundusCreatusEst"
```

Steps in order to view mails.

```
a1 list "" *


<<output>>

* LIST (\NoInferiors) "/" INBOX
```



```
a1 select INBOX

<<output>>

* 5 EXISTS
* 0 RECENT
```

Read mails manually.

```
a1 fetch 1 body[text]


<<output>>

* Mail get's displayed *
```