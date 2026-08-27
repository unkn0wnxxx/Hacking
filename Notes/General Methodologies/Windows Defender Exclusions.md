
We can enumerate directories which have an exception on Windows Defender:

```
reg query "HKLM\SOFTWARE\Microsoft\Windows Defender\Exclusions\Paths"
```