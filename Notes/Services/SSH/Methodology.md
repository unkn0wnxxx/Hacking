
- Bruteforce with hydra
- [[Default Credentials]]
- Assuming we got an private ssh key of an user from the target server, we can file transfer git repositorys like that:[[Git Repo Download]]
- [[SSH Git Repo Push to Remote Target|RCE using Git and SSH Private Key]]
- Found SSH Private Key?
	- Always use curl, wget or burpsuite's response body, to avoid formatting errors!

If ssh complain about libcrypto or key format, normalize the file:

> dos2unix ~/.ssh/id_rsa  
> vim — clean ~/.ssh/id_rsa  
> (inside vim: type :wq then hit Return)


```
curl "[Vulnerable URL/Request]" -o id_rsa
```

![[Pasted image 20260303213937.png]]


