
---
## User Wordlist

Upon inspecting the webpage we found 3 potential usernames.

```
Thomas Bishop
James Ray
Toby Harlington
```

Enumerated endpoints using gobuster and found interesting endpoints. Including /administration which seems to be an login panel & /vacancies endpoint which reveals information about another username.

```
Ralph Davies
```

Let's create an users wordlist out of them using the following username generator:

```
git clone https://github.com/florianges/UsernameGenerator
```

Stored all of the users in newusers.txt and then ran the following command to generate multiple usernames for bruteforcing:

```
UsernameGenerator.py newusers.txt users.txt
```

---
## Password Wordlist

Since we don't got any passwords yet. Let's create an passwords.txt wordlist by utilizing an tool called cewl which crawls the whole website.

```
cewl http://painters.htb -x 15 -o -w passwords.txt
```
