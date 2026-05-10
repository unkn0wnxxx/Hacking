
Command curls e-mails in the source code of the webpage.

```
curl -s http://windcorp.thm | grep -oE '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}' | sort -u
```

![[Pasted image 20260510154832.png]]

This command removes redundant inputs & also pastes the length of the file out.

```
cat users.txt | sort -u | wc -l
```