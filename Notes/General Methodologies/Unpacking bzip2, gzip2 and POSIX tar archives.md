
I prompted the output of the password_backup file in AI and it told me it's a bzip2 compressed file.

Converted the hexdump back into binary format.

```
xxd -r hex_format > password_backup.bin
```

Changed file name to the file extension format.

```
mv password_backup.bin password_backup.bz2
```

Unpacked the file and received an "password_backup" file.

```
bunzip2 -k password_backup.bz2
```

As we can see the file is only compressed in gzip now, not in gzip2 anymore.

```
file password_backup    
password_backup: gzip compressed data, was "password", last modified: Tue May 22 19:16:20 2018, from Unix, original size modulo 2^32 141
```

Let's proceed with unpacking this one too. But first we'll need to change the filename to the correct file extension again.

```
mv password_backup password_backup.gz
```

Unpacked the file.

```
gunzip -k password_backup.gz
```

As we can see it now shows bzip2 compressed data, which is absolutely correct!

```
file password_backup
password_backup: bzip2 compressed data, block size = 900k
```

Switch the file extension again.

```
mv password_backup password_backup2.bz2
```

Unzip.

```
bunzip2 -k password_backup2.bz2
```

Now we received the last step, the POSIX tar archive.

```
file password_backup2
password_backup2: POSIX tar archive (GNU)
```

Unpack the file.

```
tar -xf password_backup2
```

Retrieved the password of the floris user.

```
cat password.txt
5d<wdCbdZu)|hChXll
```