
Assuming there is an upload functionality, which downloads the uploaded file and displays it somewhere.
Could we perhaps utilize LFI within the filename parameter to view system files?

## PoC

Intercept the file upload via burpsuite and utilize LFI inside the filename variable to view the passwd file.

```
Content-Disposition: form-data; name="file"; filename="../../../../../../../etc/passwd"
```