
hURL & xxd are powerful tools, which allows decoding and encoding of strings.

---
## Decoding

##### xxd

Hex into Binary/Original Format

```
cat hype_key | xxd -r -p 
```

---
###### hURL

Hex

```
hURL -x "<hexcode>"
```

ROT13

```
hURL -8 "<rot13_encoded_string>"
```

URL Decoding

```
hURL -u "<url_encoded_string>"
```

Base64

```
hURL -b "<base64_encoded_string>"
```

## Encode

Hex

```
hURL -x "hello world"
```

ROT13


```
hURL -8 "hello world"
```

Base64

```
hURL -B "hello world"
```
