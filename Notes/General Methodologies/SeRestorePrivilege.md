
## PoC

Utilized Tool "SeRestoreAbuse.exe" in /home/saitama/Desktop/Tools/SeRestorePrivilege/SeRestoreAbuse.exe in order to get nt authority\system shell.

1. Create payload with msfvenom
2. Download it to target machine
3. Download SeRestoreAbuse.exe onto target system
4. Start listener.
5. Execute the following command:
```
.\SeRestoreAbuse.exe shell.exe
```
