
```
#include <stdio.h> 
#include <stdlib.h> 
#include <sys/types.h> 
#include <unistd.h> 

int main(int argc, char **argv) 
{ 
	setreuid(0,0); 
	system("/usr/bin/touch /w00t"); 
	return(0); 
}
```

Compile

```
gcc -Wall program.c -o program
```

##### Make it work as root, if possible

```
sudo chown root:root program
sudo chmod 4755 program
```