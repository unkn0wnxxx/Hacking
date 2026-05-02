
1. Download NordVPN for Kali Linux

2. Prompt the following command:

```
nordvpn login
```

3. Press on the api link and authenticate yourself. If there is no link to copy paste, copy paste the exchange token in the url and paste in the following command in your CLI to authenticate:

```
nordvpn login --callback "nordvpn://login?action=login&exchange_token=XXXXX&status=done"
```

4. Connect to VPN

```
nordvpn connect -group dedicated_ip
```