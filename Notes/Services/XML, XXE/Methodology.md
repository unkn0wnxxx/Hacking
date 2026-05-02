
#### Command Injection

```
<template>
<command>dir</command>
<exec>whoami</exec>
<file>| cat /etc/passwd</file>
</template>
```