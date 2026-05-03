# CTF Writeup: Pyrat

Step 1: Port scanning der target ip --> port 22 & 8000 ist offen  
Step 2: curl -v http://<target IP> <port> verwendet um zu schauen ob man Informationen durch den webserver erhalten kann  
Step 3: "Try a more basic connection" was found via 8000 port http  
Step 4: used "nikto -h http://<target IP>:<port> to find vulnerabilities within the webserver --> found one  
"the x content type options header is not set"  
Step 5: created a reverse shell python3 script by using "revshells.com"   
Step 6: Abused the vulnerability in the http server and implemented the python Skript but didnt execute it yet.  
Step 6: added a listener "nc -lnvp 1234"  
Step 7: executed the script --> Reverse Shell is active now  
Step 8: check /var -> cd /mail --> ls -l --> cat think (einzige möglichkeit) --> mail wurde displayed.  
Anscheinend gibt es einen RTA auf jose's GitHub page  
Step 9: Since we have to gather information about Jose's GitHub Page, we will have to navigate back to .git within /opt/dev  
Step 10: ls -la --> cd .git --> cat config --> credentials username and pw noted and found jose's github credentials.  
Step 11: logged into ssh with his credentials and found userflag  
Step 11: within the ssh of user "think" --> cd / --> cd opt/dev --> git status--> found old versions of pyrat and restored them --> git restore pyrat.py.old --> cat pyrat.py.old --> found codeblock and compared with the source within github repo --> Read the readme file within the repo --> admin command allows u to write down password --> Passwort gefuzzed --> gained access and found root flag.  

## Key Learnings

### - Learned about new cli commands
### - Learned about nikto
### - Further improved my reverse shell knowledge
### - Further improved ssh knowledge 
### - Further improved directory & git knowledge /opt 
