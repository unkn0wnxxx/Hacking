

## .Compiled

Use Ghidra to decompile.
## .exe
In order to perform forensics on .exe files in order to potentially find passwords we can utilize the following tool:
# ILSpy

In order to decompile .Net executable we can use Avalonia ILspy, which is a cross-platform version of ILSpy that works on Linux. First let's download it from the releases page

```
wget https://github.com/icsharpcode/AvaloniaILSpy/releases/download/v7.2-
rc/Linux.x64.Release.zip
```



```
unzip Linux.x64.Release.zip
```

```
unzip ILSpy-linux-x64-Release.zip
```

Execute the tool.

```
cd artifacts/linux-arm64
sudo ./ILSpy
```

Let's now load the executable in order to decompile it. Click on File , select Open , find the target binary in the file browser and select it.

After the binary has been imported, ILSpy will take care the decompilation and we will be able to view the source code. Taking a look at the code we quickly notice a function called LdapQuery as well as two other functions called FindUser and GetUser .

1. Search for hardcoded encoded passwords
2. Check application logic? Connection to LDAP or smth else?
3. Check encoding algorithm!
