
File

```
find / -writable 2>/dev/null | grep -v "^/proc\|^/sys\|^/run"
```

Directory

```
find / -type d -writable 2>/dev/null | grep -v "^/proc\|^/sys\|^/run"
```

---

1. Check writable system configuration files.

```
find /etc -writable 2>/dev/null
```

Writable Configuration Files should always be investigated because they are frequently processed by privileged services.
