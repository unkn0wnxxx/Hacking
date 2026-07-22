
Important utility when needing to install certain python modules to execute custom scripts. Why? Kali doesn't allow pip install anymore without being in an virtual environment. Since this could potentially destroy the VM.

```
python3 -m venv myenv
```

```
source myenv/bin/activate
```

Now we can install and run everything as needed.

```
pip3 install requests
```