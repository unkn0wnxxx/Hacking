# CTF Writeup: Light

---

- Step 1: Tried connecting to the server ran on port 1337, with cred "smokey", but couldn't log in, got password tho.
- Step 2: Tried ssh login with smokey & password, even tried base64 decoding --> echo "password" | base64 -i, but still wrong password.
- Step 3: gobuster scan is also not possible, since I never handled SQL Injections, I didn't know I was facing one.
- Step 4: Tried basic tests like ' and found out it's SQL based injection, after trying out SELECT, it prompted
blockage out, Tried not doing it case-sensitive and it worked.
- Step 5: Tried out Union to check if it's an SQLite based database, because SQLite has his own set of commands.
- Step 6: Made "'Union Select group_concat(sql) from sqlite_master'" to see a list of all the tables within the database.
- Step 7: made 'Union Select group_concat(username ||":"|| password) FROM admintable' and received flag & more. 


---

## Key Learnings

- First Contact with SQL Injections
- Learned about special SQLite commands
- Increased Knowledge about databases

