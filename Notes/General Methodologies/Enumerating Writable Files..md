
```
find / -type d -writable 2>/dev/null | grep -v "^/proc\|^/sys\|^/run"
```
