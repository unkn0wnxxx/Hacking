
# Linux

If an password contains special characters which aren't getting recognized as characters by our zsh shell. We can store the password in an .txt file. In order for it to work we can utilize the attribute as an command to show the password.txt file.
## Example

```
nxc rdp thm.local -u='cody_roy' -p $(cat password.txt)
```
